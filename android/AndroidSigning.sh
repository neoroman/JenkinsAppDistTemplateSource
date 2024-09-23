#!/bin/sh
#
# Written by Henry Kim on 2018.06.21
# Modified by Henry Kim on 2021.08.05
# Normalized by Henry Kim on 2021.09.01
#
SCRIPT_PATH="$(dirname "$0")"
SCRIPT_NAME=$(basename $0)
HOSTNAME=$(hostname)
relativePathPrefix=".."
jsonConfig="$relativePathPrefix/config/config.json"
if [ ! -f $jsonConfig ]; then
  relativePathPrefix="../.."
  jsonConfig="$relativePathPrefix/config/config.json"
fi
if [ -f $jsonConfig ]; then
  jsonConfig=$SCRIPT_PATH/$jsonConfig
fi
if [ ! -f $jsonConfig ]; then
  echo "$HOSTNAME > Error: no config.json in $jsonConfig"
  exit 1
fi
##############
if test -z "$JQ"; then
  if command -v jq >/dev/null; then
    JQ=$(command -v jq)
  elif [ -f "/usr/local/bin/jq" ]; then
    JQ="/usr/local/bin/jq"
  elif [ -f "/usr/bin/jq" ]; then
    JQ="/usr/bin/jq"
  else
    JQ="/bin/jq"
  fi
fi
##############
defaultLanguagePath="$relativePathPrefix/lang"
if [ -d $defaultLanguagePath ]; then
  defaultLanguagePath=$SCRIPT_PATH/$defaultLanguagePath
  if [ -f "$defaultLanguagePath/default.json" ]; then
    language=$(cat "$defaultLanguagePath/default.json" | $JQ '.LANGUAGE' | tr -d '"')
    lang_file="$defaultLanguagePath/lang_$language.json"
  fi
fi
DEBUGGING=0
## Parsing arguments, https://stackoverflow.com/a/14203146
while [[ $# -gt 0 ]]; do
  key="$1"
  case $key in
    -p|--platform)
      INPUT_OS="$2"
      shift # past argument
      shift # past value
      ;;
    -f|--file)
      INPUT_FILE="$2"
      shift # past argument
      shift # past value
      ;;
    -r|--root)
      DOC_ROOT="$2"
      shift # past argument
      shift # past value
      ;;
    -iu|--inUrl)
      IN_URL="$2"
      shift # past argument
      shift # past value
      ;;
    -ou|--outUrl)
      OUT_URL="$2"
      shift # past argument
      shift # past value
      ;;
    -tp|--topPath)
      TOP_PATH="$2"
      shift # past argument
      shift # past value
      ;;
    -d|--debug)
      DEBUGGING=1
      shift # past argument
      ;;
    *|-h|--help)  # unknown option
      shift # past argument
      echo "Usage: $SCRIPT_NAME [-p {ios,android}] [-f input_file] [-r document_root] [-u domain] [-d]"
      echo ""
      echo "optional arguments:"
      echo "   -h, --help        show this help message and exit:"
      echo "   -p {ios,android}, --platfor {ios,android}"
      echo "                     assign platform as iOS or Android to processing"
      echo "   -f, --file        assign input_file"
      echo "   -r, --root        assign document root of web server"
      echo "   -iu, --inUrl      assign host url of web site for inbound"
      echo "   -ou, --outUrl     assign host url of web site for outbound"
      echo "   -d, --debug       debugging mode"
      echo ""
      exit
      ;;
  esac
done
####### DEBUG or Not #######
if [ $DEBUGGING -eq 1 ]; then
  config=$(cat $jsonConfig | $JQ '.development')
else
  config=$(cat $jsonConfig | $JQ '.production')
fi
############################
USING_MAIL=$(test $(cat $jsonConfig | $JQ '.mail.domesticEnabled') = true && echo 1 || echo 0)
USING_SLACK=$(test $(cat $jsonConfig | $JQ '.slack.enabled') = true && echo 1 || echo 0)
USING_HTML=$(test $(echo $config | $JQ '.usingHTML') = true && echo 1 || echo 0)
USING_JSON=1
if test -z "$JAR_SIGNER"; then
  JAVA_HOME=$(cat $jsonConfig | $JQ '.android.javaHome' | tr -d '"')
  if test -n "$JAVA_HOME"; then
    JAR_SIGNER="$JAVA_HOME/bin/jarsigner"
  else
    JAR_SIGNER="/usr/local/opt/openjdk@8/bin/jarsigner"
  fi
fi
if test -z "$JAR_SIGNER"; then
  if command -v jarsigner >/dev/null; then
    JAR_SIGNER=$(command -v jarsigner) #"/usr/bin/jarsigner"
  fi
fi
if test -z "$JAR_SIGNER"; then
    echo "$HOSTNAME > Error: jarsigner 명령어 없음 in $JAR_SIGNER"
    exit
elif [ ! -f "$JAR_SIGNER" ]; then
    echo "$HOSTNAME > Error: jarsigner 명령어 없음 in $JAR_SIGNER"
    exit
fi
##### Using Teams or Not, 0=Not Using, 1=Using Teams
USING_TEAMS_WEBHOOK=$(test $(cat $jsonConfig | $JQ '.teams.enabled') = true && echo 1 || echo 0)
TEAMS_WEBHOOK=$(cat $jsonConfig | $JQ '.teams.webhook' | tr -d '"')
############################
USING_APKSIGNING=1   # 1 이면 사용, 0 이면 미사용
ANDROID_HOME=$(cat $jsonConfig | $JQ '.android.androidHome' | tr -d '"')
if test -z $ANDROID_HOME; then
  if command -v android >/dev/null; then
      AOS_EXEC=$(command -v android)
      ANDROID_HOME="$(dirname ${AOS_EXEC%android})"
  fi
fi
OUTPUT_PREFIX=$(echo $config | $JQ '.outputPrefix' | tr -d '"')
ANDROID_BUILDTOOLS="${ANDROID_HOME}/build-tools"
if [ -d $ANDROID_BUILDTOOLS ]; then
  if [ $USING_APKSIGNING -eq 1 ]; then
    APKSIGNER="$(find ${ANDROID_BUILDTOOLS} -name 'apksigner' | sort -r | head -1 | sed -e 's/^\.\/\(.*\)$/\1/')"
    if test -z "$APKSIGNER"; then
        echo "$HOSTNAME > Error: no apksigner execute"
        exit 1
    fi
  fi
  ZIP_ALIGN="$(find ${ANDROID_BUILDTOOLS} -name 'zipalign' | sort -r | head -1 | sed -e 's/^\.\/\(.*\)$/\1/')"
  if test -z "$ZIP_ALIGN"; then
      echo "$HOSTNAME > Error: no zipalign execute"
      exit 1
  fi
fi
if test -z "$ZIP_ALIGN"; then
    echo "$HOSTNAME > Error: zipalign 명령어 없음 in $ZIP_ALIGN"
    exit
fi
if test -z "$APKSIGNER"; then
    echo "$HOSTNAME > Error: apksigner 명령어 없음 in $APKSIGNER"
    exit
fi
############################
if test -z "$INPUT_FILE"; then
    echo "$HOSTNAME > Error: 1차 난독화 버전 파일명(확장자 제외) 없음"
    exit
fi
#####
if [ $USING_SLACK -eq 1 ]; then
  SLACK=$(command -v slack) #"/usr/local/bin/slack"
  if [ ! -f $SLACK ]; then
    USING_SLACK=0
  else
    SLACK_CHANNEL=$(cat $jsonConfig | $JQ '.slack.channel' | tr -d '"')
  fi
fi
CURL=$(command -v curl) #"/usr/bin/curl"
####
##### from config.php
frontEndProtocol=$(echo $config | $JQ '.frontEndProtocol' | tr -d '"')
frontEndPoint=$(echo $config | $JQ '.frontEndPoint' | tr -d '"')
TOP_PATH=$(echo $config | $JQ '.topPath' | tr -d '"')
FRONTEND_POINT="${frontEndProtocol}://${frontEndPoint}"
#####
if test -z "$DOC_ROOT"; then
  APP_ROOT="../.."
else
  APP_ROOT="${DOC_ROOT}/${TOP_PATH}"
fi
AOS_DIR="../android_distributions"
if [ ! -d $SCRIPT_PATH/$AOS_DIR ]; then
  AOS_DIR="../../android_distributions"
fi
APP_VERSION=$(find $AOS_DIR -name "$INPUT_FILE.json" | xargs dirname $1  | tail -1 |  sed -e 's/.*\/\(.*\)$/\1/')
APP_FOLDER="android_distributions/${APP_VERSION}"
OUTPUT_FOLDER="${APP_ROOT}/${APP_FOLDER}"
HTTPS_PREFIX="${FRONTEND_POINT}/${TOP_PATH}/${APP_FOLDER}/"
#####
APK_GOOGLESTORE="${INPUT_FILE}$(cat $jsonConfig | $JQ '.android.outputGoogleStoreSuffix' | tr -d '"')"
USING_BUNDLE_GOOGLESTORE=$(test $(cat $jsonConfig | $JQ '.android.GoogleStore.usingBundleAAB') = true && echo 1 || echo 0)
if [ $USING_BUNDLE_GOOGLESTORE -eq 1 ]; then
  AAB_GOOGLESTORE="${APK_GOOGLESTORE%.apk}.aab"
fi
APK_ONESTORE="${INPUT_FILE}$(cat $jsonConfig | $JQ '.android.outputOneStoreSuffix' | tr -d '"')"
##### for debug APK
if [ -f "${OUTPUT_FOLDER}/${APK_GOOGLESTORE%release.*}debug.apk" ]; then
  APK_DEBUG="${APK_GOOGLESTORE%release.*}debug.apk"
elif [ -f "${OUTPUT_FOLDER}/${APK_ONESTORE%release.*}debug.apk" ]; then
  APK_DEBUG="${APK_ONESTORE%release.*}debug.apk"
fi
if test -z "${APK_DEBUG}"; then
  APK_DEBUG=$(find ${OUTPUT_FOLDER} -name "*${INPUT_FILE}*-debug.*" | head -1 | xargs basename $1)
fi
##### for debugging
if [ $DEBUGGING -eq 1 ]; then
  USING_HTML=0
  USING_MAIL=0
  USING_SLACK=0
  USING_JSON=1
fi
#####
JENKINS_WORKSPACE=$(cat $jsonConfig | $JQ '.android.jenkinsWorkspace' | tr -d '"')
STOREPASS=$(cat $jsonConfig | $JQ '.android.keyStorePassword' | tr -d '"')
KEYSTORE_FILE=$(cat $jsonConfig | $JQ '.android.keyStoreFile' | tr -d '"')
KEYSTORE_ALIAS=$(cat $jsonConfig | $JQ '.android.keyStoreAlias' | tr -d '"')
if [ ! -f $JENKINS_WORKSPACE/$KEYSTORE_FILE ]; then
  echo "$HOSTNAME > Error: cannot find keystore file in $KEYSTORE_FILE"
  exit 1
else 
  KEYSTORE_FILE="$JENKINS_WORKSPACE/$KEYSTORE_FILE"
fi
if [ -f "$lang_file" ]; then
  CLIENT_NAME=$(cat $lang_file | $JQ '.client.full_name' | tr -d '"')
  TITLE_GOOGLE_STORE=$(cat $lang_file | $JQ '.title.distribution_2nd_signing_google_store' | tr -d '"')
  TITLE_ONE_STORE=$(cat $lang_file | $JQ '.title.distribution_2nd_signing_one_store' | tr -d '"')
  APP_NAME=$(cat $lang_file | $JQ '.app.name' | tr -d '"')
  SITE_URL=$(cat $lang_file | $JQ '.client.short_url' | tr -d '"')
  SITE_ID=$(cat $jsonConfig | $JQ '.users.app.userId' | tr -d '"')
  SITE_PW=$(cat $jsonConfig | $JQ '.users.app.password' | tr -d '"')
  SITE_ID_PW="${SITE_ID}/${SITE_PW}"
fi
#####
outputUnsignedPrefix=$(cat $jsonConfig | $JQ '.android.outputUnsignedPrefix' | tr -d '"')
outputSignedPrefix=$(cat $jsonConfig | $JQ '.android.outputSignedPrefix' | tr -d '"')
#####
# Step 1.1: For Google Store
UNSIGNED_GOOGLE_FILE="${outputUnsignedPrefix}${APK_GOOGLESTORE}"
if [ -f $OUTPUT_FOLDER/$UNSIGNED_GOOGLE_FILE ]; then
    UNZIPALIGNED_GOOGLESTORE="unzipaligned_$APK_GOOGLESTORE"
    SIGNED_FILE_GOOGLESTORE="${outputSignedPrefix}${APK_GOOGLESTORE}"
    if [ -f $OUTPUT_FOLDER/$SIGNED_FILE_GOOGLESTORE ]; then
        rm -f $OUTPUT_FOLDER/$SIGNED_FILE_GOOGLESTORE
    fi
    $JAR_SIGNER -sigalg SHA1withRSA \
                -digestalg SHA1 \
                -keystore $KEYSTORE_FILE \
                -storepass "$STOREPASS" \
                $OUTPUT_FOLDER/$UNSIGNED_GOOGLE_FILE "$KEYSTORE_ALIAS" \
                -signedjar $OUTPUT_FOLDER/$UNZIPALIGNED_GOOGLESTORE

    if [ -f $OUTPUT_FOLDER/$UNZIPALIGNED_GOOGLESTORE ]; then
      $ZIP_ALIGN -p -f -v 4 $OUTPUT_FOLDER/$UNZIPALIGNED_GOOGLESTORE $OUTPUT_FOLDER/$SIGNED_FILE_GOOGLESTORE
      if [ -f $OUTPUT_FOLDER/$UNZIPALIGNED_GOOGLESTORE ]; then
          rm -f $OUTPUT_FOLDER/$UNZIPALIGNED_GOOGLESTORE
      fi
      if [ $USING_APKSIGNING -eq 1 ]; then
        echo "${STOREPASS}" | $APKSIGNER sign -ks $KEYSTORE_FILE $OUTPUT_FOLDER/$SIGNED_FILE_GOOGLESTORE
        $APKSIGNER verify --verbose $OUTPUT_FOLDER/$SIGNED_FILE_GOOGLESTORE
      fi
    else
      echo "$HOSTNAME > Error: $JAR_SIGNER 에러 발생하여 $OUTPUT_FOLDER/$UNZIPALIGNED_GOOGLESTORE 생성 불가, 1차 난독화 파일($UNSIGNED_GOOGLE_FILE)이 올바르지 않음!"
      exit
    fi
else
    echo "$HOSTNAME > Error: 1차 난독화 버전 파일($OUTPUT_FOLDER/$UNSIGNED_GOOGLE_FILE) 없음"
    exit
fi
#####
# Step 1.2: For One Store
UNSIGNED_ONE_FILE="${outputUnsignedPrefix}${APK_ONESTORE}"
if [ -f $OUTPUT_FOLDER/$UNSIGNED_ONE_FILE ]; then
    UNZIPALIGNED_ONESTORE="unzipaligned_$APK_ONESTORE"
    SIGNED_FILE_ONESTORE="${outputSignedPrefix}${APK_ONESTORE}"
    if [ -f $OUTPUT_FOLDER/$SIGNED_FILE_ONESTORE ]; then
        rm -f $OUTPUT_FOLDER/$SIGNED_FILE_ONESTORE
    fi
    $JAR_SIGNER -sigalg SHA1withRSA \
                -digestalg SHA1 \
                -keystore $KEYSTORE_FILE \
                -storepass "$STOREPASS" \
                $OUTPUT_FOLDER/$UNSIGNED_ONE_FILE "$KEYSTORE_ALIAS" \
                -signedjar $OUTPUT_FOLDER/$UNZIPALIGNED_ONESTORE

    if [ -f $OUTPUT_FOLDER/$UNZIPALIGNED_ONESTORE ]; then
      $ZIP_ALIGN -p -f -v 4 $OUTPUT_FOLDER/$UNZIPALIGNED_ONESTORE $OUTPUT_FOLDER/$SIGNED_FILE_ONESTORE
      if [ -f $OUTPUT_FOLDER/$UNZIPALIGNED_ONESTORE ]; then
          rm -f $OUTPUT_FOLDER/$UNZIPALIGNED_ONESTORE
      fi
      if [ $USING_APKSIGNING -eq 1 ]; then
        echo "${STOREPASS}" | $APKSIGNER sign -ks $KEYSTORE_FILE $OUTPUT_FOLDER/$SIGNED_FILE_ONESTORE
        $APKSIGNER verify --verbose $OUTPUT_FOLDER/$SIGNED_FILE_ONESTORE
      fi
    else
      echo "$HOSTNAME > Error: $JAR_SIGNER 에러 발생하여 $OUTPUT_FOLDER/$UNZIPALIGNED_ONESTORE 생성 불가, 1차 난독화 파일($UNSIGNED_ONE_FILE)이 올바르지 않음!"
    fi
else
    echo "$HOSTNAME > Error: 1차 난독화 버전 파일($OUTPUT_FOLDER/$UNSIGNED_ONE_FILE) 없음"
    # exit
fi
#####
# Step 1.3: For Google Store (AAB)
if [ $USING_BUNDLE_GOOGLESTORE -eq 1 ]; then
    UNSIGNED_GOOGLE_BUNDLE="${outputUnsignedPrefix}${AAB_GOOGLESTORE}"
    if [ -f $OUTPUT_FOLDER/$UNSIGNED_GOOGLE_BUNDLE ]; then
        SIGNED_BUNDLE_GOOGLESTORE="${outputSignedPrefix}${AAB_GOOGLESTORE}"
        if [ -f $OUTPUT_FOLDER/$SIGNED_BUNDLE_GOOGLESTORE ]; then
            rm -f $OUTPUT_FOLDER/$SIGNED_BUNDLE_GOOGLESTORE
        fi
        $JAR_SIGNER -sigalg SHA1withRSA \
                    -digestalg SHA1 \
                    -keystore $KEYSTORE_FILE \
                    -storepass "$STOREPASS" \
                    $OUTPUT_FOLDER/$UNSIGNED_GOOGLE_BUNDLE "$KEYSTORE_ALIAS" \
                    -signedjar $OUTPUT_FOLDER/$SIGNED_BUNDLE_GOOGLESTORE

        if [ ! -f $OUTPUT_FOLDER/$SIGNED_BUNDLE_GOOGLESTORE ]; then
          echo "$HOSTNAME > Error: $JAR_SIGNER 에러 발생, 1차 난독화 파일($SIGNED_BUNDLE_GOOGLESTORE)이 올바르지 않음!"
        fi
    fi
fi

######################################################
if [ $USING_SLACK -eq 1 ]; then
  #####
  # Step 2.1: Send message via Slack for ERROR
  if [ ! -f $OUTPUT_FOLDER/$SIGNED_FILE_GOOGLESTORE ]; then
    $SLACK chat send --text "${HOSTNAME} > 안드로이드 2차 난독화 signing 버전 생성오류!\n\n\n\n${HOSTNAME} > 구글Store - ${OUTPUT_FOLDER}/${SIGNED_FILE_GOOGLESTORE}\n\n" --channel "${SLACK_CHANNEL}" --pretext "${HOSTNAME} > Android 2차 난독화 Signing 오류 for ${APK_GOOGLESTORE}" --color good
    exit
  fi
  if [ -f $OUTPUT_FOLDER/$UNSIGNED_ONE_FILE ]; then
    if [ ! -f $OUTPUT_FOLDER/$SIGNED_FILE_ONESTORE ]; then
      $SLACK chat send --text "${HOSTNAME} > 안드로이드 2차 난독화 signing 버전 생성오류!\n\n\n\n${HOSTNAME} > 원Store - ${OUTPUT_FOLDER}/${SIGNED_FILE_ONESTORE}\n\n" --channel "${SLACK_CHANNEL}" --pretext "${HOSTNAME} > Android 2차 난독화 Signing 오류 for ${APK_ONESTORE}" --color good
      exit
    fi
  fi
  #####
  # Step 2.2: Send message via Slack for Success !!!
  $SLACK chat send --text "${HOSTNAME} > 안드로이드 2차 난독화 signing 버전 전달합니다.\n\n\n첨부파일: \n\n${HOSTNAME} > 구글Store - ${HTTPS_PREFIX}${SIGNED_FILE_GOOGLESTORE}\n${HOSTNAME} > 원Store - ${HTTPS_PREFIX}${SIGNED_FILE_ONESTORE}\n" --channel "${SLACK_CHANNEL}" --pretext "${HOSTNAME} > Android 2차 난독화 Signing 성공 for ${INPUT_FILE}" --color good
fi
######################################################


FILENAME_TODAY=$(echo $INPUT_FILE | sed -e 's/^.*_\([0-9][0-9][0-9][0-9][0-9][0-9]\)$/\1/')
if [ $USING_HTML -eq 1 ]; then
  # Step 3: Change HTML(index.html) file
  OUTPUT_FILENAME_HTML="${OUTPUT_PREFIX}${APP_VERSION}(${VERSION_CODE})_${FILENAME_TODAY}.html"
  HTML_DIST_FILE=${APP_ROOT}/dist_android.html
  HTML_OUTPUT="       <span style=\"position: relative;margin: 0px;right: 0px;float: center;font-size:0.5em;\"><a class=\"button secondary radius\" style=\"white-space:nowrap; height:25px; padding-left:10px; padding-right:10px;\" href=\"${HTTPS_PREFIX}${SIGNED_FILE_GOOGLESTORE}\">Google Playstore 등록용(2차 난독화)<img src=\"../../../download-res/img/icons8-downloading_updates.png\" style=\"position: relative;margin-top: -6px;margin-left: 18px;right:0px;float:right;width:auto;height:1.5em\"></a></span><span style=\"position: relative;margin: 0px;right: 0px;float: center;font-size:0.5em;\"><a class=\"button secondary radius\" style=\"white-space:nowrap; height:25px; padding-left:10px; padding-right:10px;\" href=\"${HTTPS_PREFIX}${SIGNED_FILE_ONESTORE}\">One Store 등록용(2차 난독화)<img src=\"../../../download-res/img/icons8-downloading_updates.png\" style=\"position: relative;margin-top: -6px;margin-left: 18px;right: 0px;float:right;width:auto;height:1.5em\"></a></span>"
  HTML_FOR_SED=$(echo $HTML_OUTPUT | sed -e 's/\//\\\//g' | sed -e 's/\./\\\./g')

  if [ -f $OUTPUT_FOLDER/$OUTPUT_FILENAME_HTML ]; then
    cp -f $OUTPUT_FOLDER/$OUTPUT_FILENAME_HTML $OUTPUT_FOLDER/$OUTPUT_FILENAME_HTML.bak
    cd $OUTPUT_FOLDER
    sed "s/^.*title=${INPUT_FILE}.*$/${HTML_FOR_SED}/" $OUTPUT_FOLDER/$OUTPUT_FILENAME_HTML > $OUTPUT_FOLDER/$OUTPUT_FILENAME_HTML.new
    mv -f $OUTPUT_FOLDER/$OUTPUT_FILENAME_HTML.new $OUTPUT_FOLDER/$OUTPUT_FILENAME_HTML
    chmod 777 $OUTPUT_FOLDER/$OUTPUT_FILENAME_HTML
  fi
  cp -f $HTML_DIST_FILE $HTML_DIST_FILE.bak
  cd $OUTPUT_FOLDER
  sed "s/^.*title=${INPUT_FILE}.*$/${HTML_FOR_SED}/" $HTML_DIST_FILE > $HTML_DIST_FILE.new
  if [ ! -s $HTML_DIST_FILE.new ]; then
    echo "Something ** WRONG ** !!!"
    exit
  fi
  mv -f $HTML_DIST_FILE.new $HTML_DIST_FILE
  chmod 777 $HTML_DIST_FILE
else
  touch $OUTPUT_FOLDER/$OUTPUT_FILENAME_HTML
fi

if [ $USING_MAIL -eq 1 ]; then
  # Step 7: Send download page url to Slack
  SHORT_GIT_LOG="$(/bin/date "+%m")월 검증 버전 Android 2차 난독화"
  if [ $USING_SLACK -eq 1 ]; then
    $SLACK chat send --text "${HOSTNAME} > ${FRONTEND_POINT}/${TOP_PATH}/dist_domestic.php > Go Android > 등록용(2차 난독화)" --channel "${SLACK_CHANNEL}" --pretext "${HOSTNAME} > Android Download Web Page for ${SHORT_GIT_LOG}" --color good
  fi
  if [ -f "../lang/default.json" ]; then
    language=$(cat "../lang/default.json" | $JQ '.LANGUAGE' | tr -d '"')
    lang_file="../lang/lang_${language}.json"
    APP_NAME=$(cat $lang_file | $JQ '.app.name' | tr -d '"')
  fi
  $CURL -k --data-urlencode "subject1=[${APP_NAME} > ${HOSTNAME}] Android 자동 2차 난독화 -" \
      --data-urlencode "subject2=Google Playstore, OneStore 등록용 버전 생성 알림" \
      --data-urlencode "message_header=안드로이드 2차 난독화 signing 버전 전달합니다.<br /><br /><br />첨부파일: <br /><br />구글Store - <a href=${HTTPS_PREFIX}${SIGNED_FILE_GOOGLESTORE}>${HTTPS_PREFIX}${SIGNED_FILE_GOOGLESTORE}</a><br />원Store - <a href=${HTTPS_PREFIX}${SIGNED_FILE_ONESTORE}>${HTTPS_PREFIX}${SIGNED_FILE_ONESTORE}</a><br />" \
      --data-urlencode "message_description=${SHORT_GIT_LOG}<br /><br /><br />" \
      --data-urlencode "message_html=<br />" \
  ${FRONTEND_POINT}/${TOP_PATH}/phpmodules/sendmail_domestic.php
fi

if [ $USING_JSON -eq 1 ]; then
  # Step: Find out size of app files
  SIZE_GOOGLESTORE_APK_FILE=$(du -sh ${OUTPUT_FOLDER}/${SIGNED_FILE_GOOGLESTORE} | awk '{print $1}')
  if [ -f "$OUTPUT_FOLDER/$SIGNED_FILE_ONESTORE" ]; then
    SIZE_ONESTORE_APK_FILE=$(du -sh ${OUTPUT_FOLDER}/${SIGNED_FILE_ONESTORE} | awk '{print $1}')
  fi
  if [ -f "$OUTPUT_FOLDER/$APK_DEBUG" ]; then
    SIZE_DEBUG_APK_FILE=$(du -sh ${OUTPUT_FOLDER}/${APK_DEBUG} | awk '{print $1}')
  fi

  OUTPUT_FILENAME_JSON="${INPUT_FILE}.json"
  JSON_FILE=$OUTPUT_FOLDER/$OUTPUT_FILENAME_JSON
  ##################################
  ##### Read from JSON  START ######
  HTML_TITLE=$(cat $JSON_FILE | $JQ -r '.title')
  APP_VERSION=$(cat $JSON_FILE | $JQ -r '.appVersion')
  BUILD_VERSION=$(cat $JSON_FILE | $JQ -r '.buildVersion')
  BUILD_NUMBER=$(cat $JSON_FILE | $JQ -r '.buildNumber')
  BUILD_TIME=$(cat $JSON_FILE | $JQ -r '.buildTime')
  VERSION_KEY=$(cat $JSON_FILE | $JQ -r '.versionKey')
  HTTPS_PREFIX=$(cat $JSON_FILE | $JQ -r '.urlPrefix')
  RELEASE_TYPE=$(cat $JSON_FILE | $JQ -r '.releaseType')
  FILES_ARRAY=$(cat $JSON_FILE | $JQ -r '.files')
  # 2nd APKSigner for Google Play Store
  TITLE[0]="${TITLE_GOOGLE_STORE}"
  SIZE[0]="${SIZE_GOOGLESTORE_APK_FILE}B"
  URL[0]="${SIGNED_FILE_GOOGLESTORE}"
  PLIST[0]=""
  if [ -f "$OUTPUT_FOLDER/$SIGNED_FILE_ONESTORE" ]; then
    # 2nd APKSigner for One Store
    TITLE[1]="${TITLE_ONE_STORE}"
    SIZE[1]="${SIZE_ONESTORE_APK_FILE}B"
    URL[1]="${SIGNED_FILE_ONESTORE}"
    PLIST[1]=""
  else
    TITLE[1]=""
    SIZE[1]=""
    URL[1]=""
    PLIST[1]=""
  fi
  # 난독화파일 스크린샷
  TITLE[2]=$(echo $FILES_ARRAY | $JQ -r '.[2].title')
  SIZE[2]=$(echo $FILES_ARRAY | $JQ -r '.[2].size')
  URL[2]=$(echo $FILES_ARRAY | $JQ -r '.[2].file')
  PLIST[2]=$(echo $FILES_ARRAY | $JQ -r '.[2].plist')
  # 난독화스크립트 증적자료
  TITLE[3]=$(echo $FILES_ARRAY | $JQ -r '.[3].title')
  SIZE[3]=$(echo $FILES_ARRAY | $JQ -r '.[3].size')
  URL[3]=$(echo $FILES_ARRAY | $JQ -r '.[3].file')
  PLIST[3]=$(echo $FILES_ARRAY | $JQ -r '.[3].plist')
  # 검증용 DEBUG 버전
  if [ -f "$OUTPUT_FOLDER/$APK_DEBUG" ]; then
    TITLE[4]="검증용 DEBUG 버전"
    SIZE[4]="${SIZE_DEBUG_APK_FILE}B"
    URL[4]=${APK_DEBUG}
    PLIST[4]=""
  else
    TITLE[4]=""
    SIZE[4]=""
    URL[4]=""
    PLIST[4]=""
  fi
  # 1st APKSigner for Google Play Store
  TITLE[5]=$(echo $FILES_ARRAY | $JQ -r '.[0].title')
  SIZE[5]=$(echo $FILES_ARRAY | $JQ -r '.[0].size')
  URL[5]=$(echo $FILES_ARRAY | $JQ -r '.[0].file')
  PLIST[5]=$(echo $FILES_ARRAY | $JQ -r '.[0].plist')
  # 1st APKSigner for One Store
  TITLE[6]=$(echo $FILES_ARRAY | $JQ -r '.[1].title')
  SIZE[6]=$(echo $FILES_ARRAY | $JQ -r '.[1].size')
  URL[6]=$(echo $FILES_ARRAY | $JQ -r '.[1].file')
  PLIST[6]=$(echo $FILES_ARRAY | $JQ -r '.[1].plist')
  ##
  GIT_LAST_LOG=$(cat $JSON_FILE | $JQ -r '.gitLastLog | gsub("[\\n\\t]"; "")')
  ##### Read from JSON  E N D ######
  ##################################

  ##################################
  ##### JSON Generation START ######
  if [ -f $JSON_FILE ]; then
    cp -f $JSON_FILE $JSON_FILE.bak
  fi
  JSON_STRING=$( $JQ -n \
  --arg title "$HTML_TITLE" \
  --arg av "$APP_VERSION" \
  --arg bv "$BUILD_VERSION" \
  --arg bn "$BUILD_NUMBER" \
  --arg bt "$BUILD_TIME" \
  --arg vk "$VERSION_KEY" \
  --arg rt "${RELEASE_TYPE}" \
  --arg url_prefix "$HTTPS_PREFIX" \
  --arg file1_title "${TITLE[0]}" \
  --arg file1_size "${SIZE[0]}" \
  --arg file1_binary "${URL[0]}" \
  --arg file1_plist "${PLIST[0]}" \
  --arg file2_title "${TITLE[1]}" \
  --arg file2_size "${SIZE[1]}" \
  --arg file2_binary "${URL[1]}" \
  --arg file2_plist "${PLIST[1]}" \
  --arg file3_title "${TITLE[2]}" \
  --arg file3_size "${SIZE[2]}" \
  --arg file3_binary "${URL[2]}" \
  --arg file3_plist "${PLIST[2]}" \
  --arg file4_title "${TITLE[3]}" \
  --arg file4_size "${SIZE[3]}" \
  --arg file4_binary "${URL[3]}" \
  --arg file4_plist "${PLIST[3]}" \
  --arg file5_title "${TITLE[4]}" \
  --arg file5_size "${SIZE[4]}" \
  --arg file5_binary "${URL[4]}" \
  --arg file5_plist "${PLIST[4]}" \
  --arg file6_title "${TITLE[5]}" \
  --arg file6_size "${SIZE[5]}" \
  --arg file6_binary "${URL[5]}" \
  --arg file6_plist "${PLIST[5]}" \
  --arg file7_title "${TITLE[6]}" \
  --arg file7_size "${SIZE[6]}" \
  --arg file7_binary "${URL[6]}" \
  --arg file7_plist "${PLIST[6]}" \
  --arg git_last_log "$GIT_LAST_LOG" \
'{"title": $title, "appVersion": $av, "buildVersion": $bv, "versionKey": $vk,'\
' "buildNumber": $bn, "buildTime": $bt, "urlPrefix": $url_prefix,  "releaseType": $rt, '\
'"files": [ { "title": $file1_title, "size": $file1_size, "file": $file1_binary, "plist": $file1_plist} , '\
'{ "title": $file2_title, "size": $file2_size, "file": $file2_binary, "plist": $file2_plist} , '\
'{ "title": $file3_title, "size": $file3_size, "file": $file3_binary, "plist": $file3_plist} , '\
'{ "title": $file4_title, "size": $file4_size, "file": $file4_binary, "plist": $file4_plist} , '\
'{ "title": $file5_title, "size": $file5_size, "file": $file5_binary, "plist": $file5_plist} , '\
'{ "title": $file6_title, "size": $file6_size, "file": $file6_binary, "plist": $file6_plist} , '\
'{ "title": $file7_title, "size": $file7_size, "file": $file7_binary, "plist": $file7_plist} ], '\
'"gitLastLog": $git_last_log}')
  echo "${JSON_STRING}" > $JSON_FILE
  ##### JSON Generation END ########
  ##################################
fi


if [ $USING_TEAMS_WEBHOOK -eq 1 ]; then
    ########
    BINARY_TITLE="Android 검증용"
    BINARY_FACTS="{
                      \"name\": \"Google Playstore 배포용\",
                      \"value\": \"v${APP_VERSION}(${BUILD_VERSION}) [GoogleStore 2차 난독화 다운로드](${HTTPS_PREFIX}${SIGNED_FILE_GOOGLESTORE}) (${SIZE_GOOGLESTORE_APK_FILE}B)\"
              }"
    if [ -f $OUTPUT_FOLDER/$SIGNED_FILE_ONESTORE ]; then
      BINARY_FACTS=", {
                        \"name\": \"One Store 배포용\",
                        \"value\": \"v${APP_VERSION}(${BUILD_VERSION}) [OneStore 2차 난독화 다운로드](${HTTPS_PREFIX}${SIGNED_FILE_ONESTORE}) (${SIZE_ONESTORE_APK_FILE}B)\"
                }"
    fi
    ########
    THEME_COLOR="619FFA"
    QC_ID=$(cat $jsonConfig | $JQ '.users.qc.userId' | tr -d '"')
    QC_PW=$(cat $jsonConfig | $JQ '.users.qc.password' | tr -d '"')
    ICON=$(cat $jsonConfig | $JQ '.teams.iconImage' | tr -d '"')
    JSON_ALL="{
          \"@type\": \"MessageCard\",
          \"@context\": \"${FRONTEND_POINT}/${TOP_PATH}/dist_domestic.php\",
          \"themeColor\": \"${THEME_COLOR}\",
          \"summary\": \"Android 2nd signing completed\",
          \"sections\": [
              {
                  \"heroImage\": {
                      \"image\": \"${FRONTEND_POINT}/${TOP_PATH}/${ICON}\"
                  }
              },
              {
                  \"activityTitle\": \"${HOSTNAME} > ${BINARY_TITLE} ${APP_NAME}.App\",
                  \"activitySubtitle\": \"$(/bin/date '+%Y.%m.%d %H:%M')\",
                  \"activityImage\": \"${FRONTEND_POINT}/${TOP_PATH}/${ICON}\",
                  \"text\": \"${CLIENT_NAME} ${APP_NAME} 앱\",
                  \"facts\": [${BINARY_FACTS}, {
                          \"name\": \"설치 및 다운로드 사이트\",
                          \"value\": \"${CLIENT_NAME} 배포 사이트 [${SITE_URL}](${SITE_URL}) (ID/PW: ${SITE_ID_PW})\"
                  }, {
                          \"name\": \"배포 웹사이트 (내부 QA용)\",
                          \"value\": \"Domestic QA 사이트 [바로가기](${FRONTEND_POINT}/${TOP_PATH}/android/dist_android.php) (ID/PW: ${QC_ID}/${QC_PW})\"
                  }],
                  \"markdown\": true
          }]
        }"
    $CURL -k -H "Content-Type: application/json" -d "${JSON_ALL}" $TEAMS_WEBHOOK
    ##
    # Sync files to Neo2UA (Synology NAS)
    # if [ -f ../shells/syncToNasNeo2UA.sh ]; then
    #   ../shells/syncToNasNeo2UA.sh
    # fi
fi
