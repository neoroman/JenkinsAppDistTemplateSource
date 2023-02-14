#!/bin/sh
##
jsonConfig="config/config.json"
configPath="config.php"
my_dir="$(dirname "$0")"
if [ -f $my_dir/sshFunctions.sh ]; then
    . $my_dir/sshFunctions.sh #> /dev/null 2>&1
fi
SCRIPT_NAME=$(basename $0)
DEBUGGING=0
INPUT_OS=""
USING_MAIL=0
IS_RESEND=0
IS_SENDING_MAIL=0
## Parsing arguments, https://stackoverflow.com/a/14203146
while [[ $# -gt 0 ]]; do
  key="$1"
  case $key in
    resend)
      IS_RESEND=1
      shift # past argument
      ;;
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
    -m|--mail)
      IS_SENDING_MAIL=$2
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
      echo "Usage: $SCRIPT_NAME [-p {ios,android}] [-f input_file] [-r document_root] [-iu inbound_url] [-ou outbound_url] [-tp top_path] [-d]"
      echo ""
      echo "optional arguments:"
      echo "   -h, --help        show this help message and exit:"
      echo "   -p {ios,android,both}, --platform {ios,android,both}"
      echo "                     assign platform as iOS or Android to processing"
      echo "   -f, --file        assign input_file"
      echo "   -r, --root        assign document root of web server"
      echo "   -iu, --inUrl      assign host url of web site for inbound"
      echo "   -ou, --outUrl     assign host url of web site for outbound"
      echo "   -tp, --topPath    assign host path of web site for distribution top folder"
      echo "   -d, --debug       debugging mode"
      echo ""
      exit
      ;;
  esac
done
if test -z $INPUT_OS; then
  echo "Usage: $SCRIPT_NAME [-p {ios,android,both}] [-f input_file]"
  echo ""
  echo "Error: ios 또는 android 인자 없음"
  exit
elif test -z $INPUT_FILE; then
  echo "Usage: $SCRIPT_NAME [-p {ios,android,both}] [-f input_file]"
  echo ""
  echo "Error: no input file"
  exit
fi
###################
CURL=$(which curl)
JQ=$(which jq)
if [[ -z "$JQ" ]]; then
  if [ -f "/usr/local/bin/jq" ]; then
    JQ="/usr/local/bin/jq"
  elif [ -f "/usr/bin/jq" ]; then
    JQ="/usr/bin/jq"
  else
    JQ="/bin/jq"
  fi
fi
###################
##### from config php
FRONTEND_POINT="${IN_URL}"
frontEndPoint=$(echo $FRONTEND_POINT | sed -e 's/^.*:\/\/\(.*\)/\1/g')
frontEndProtocol=$(echo $FRONTEND_POINT | sed -e 's/^\(.*\):\/\/.*/\1/g')
XCODE=`which xcodebuild`
POD="/usr/local/bin/pod"
#####
if [ -f "lang/default.json" ]; then
  language=$(cat "lang/default.json" | $JQ '.LANGUAGE' | tr -d '"')
  lang_file="lang/lang_${language}.json"
  APP_NAME=$(cat $lang_file | $JQ '.app.name' | tr -d '"')
  APP_VERSION=$(cat $lang_file | $JQ '.app.version' | tr -d '"')
  RELEASE_KEY=$(cat $lang_file | $JQ '.mail.releaseKeyword' | tr -d '"')
  DEVELOP_KEY=$(cat $lang_file | $JQ '.mail.developKeyword' | tr -d '"')
fi
##### from config php
NEO2UA_POINT="${OUT_URL}"
if test -z $NEO2UA_POINT; then
  outBoundPoint=$(grep 'outBoundPoint' ${configPath} | tail -1  | sed -e 's/.*"\(.*\)";/\1/g')
  outBoundProtocol=$(grep 'outBoundProtocol' ${configPath} | tail -1  | sed -e 's/.*"\(.*\)";/\1/g')
  NEO2UA_POINT="${outBoundProtocol}://${outBoundPoint}"
else
  outBoundPoint=$(echo $NEO2UA_POINT | sed -e 's/^.*:\/\/\(.*\)/\1/g')
  outBoundProtocol=$(echo $NEO2UA_POINT | sed -e 's/^\(.*\):\/\/.*/\1/g')
fi
if [ -f $jsonConfig ]; then
  if [ $DEBUGGING -eq 1 ]; then
    config=$(cat $jsonConfig | $JQ '.development')
  else
    config=$(cat $jsonConfig | $JQ '.production')
  fi
  if [ $IS_SENDING_MAIL -eq 1 ]; then
    USING_MAIL=$(test $(cat $jsonConfig | $JQ '.mail.enabled') = true && echo 1 || echo 0)
  fi
  USING_SCP=$(test $(cat $jsonConfig | $JQ '.ssh.enabled') = true && echo 1 || echo 0)
  USING_HTML=$(test $(echo $config | $JQ '.usingHTML') = true && echo 1 || echo 0)
fi
###############################################################################################
###############################################################################################
WORKING_PATH="${DOC_ROOT}/${TOP_PATH}"
cd $WORKING_PATH
###################

DevEnvPrefix="<div style=\"background: ghostwhite; font-size: 12px; padding: 10px; border: 1px solid lightgray; margin: 10px;\">"
DevEnvSuffix="</div>"

function readJsonAndSetVariables() {
  APP_ROOT="${DOC_ROOT}/${TOP_PATH}/${APP_ROOT_SUFFIX}"
  FILE_PATH=$(find ${WORKING_PATH}/${PROCESS_OS}* -name "$INPUT_FILE")
  if [ $USING_SCP -eq 1 ]; then
    remoteFilePath=${TOP_PATH}${FILE_PATH#"${WORKING_PATH}"}
    remotePath=$(dirname ${remoteFilePath})
    RESULT=$(sendFile ${FILE_PATH} ${remotePath})
    if [ $RESULT -eq 0 ]; then
        echo "Send $remoteFilePath failed"
    fi
    RESULT=$(checkFileExist ${remotePath}/"zzz_$INPUT_FILE")
    if [ $RESULT -eq 1 ]; then
      RESULT=$(removeFile ${remotePath}/"zzz_$INPUT_FILE")
      if [ $RESULT -eq 0 ]; then
        echo "Fail to remove ${remotePath}/zzz_$INPUT_FILE"
      fi
    fi
  fi

  #####################
  INPUT_FILENAME_ONLY="$(basename $INPUT_FILE .html)"
  INPUT_FILENAME_ONLY="${INPUT_FILENAME_ONLY#"zzz_"}"
  incPhpFile="${INPUT_FILENAME_ONLY}.inc.php"
  incPhpFilePath=$(find ${WORKING_PATH}/${PROCESS_OS}* -name "${incPhpFile}")
  if [ -f $incPhpFilePath ]; then
    if [ $USING_SCP -eq 1 ]; then
      remoteIncPhpFilePath=${TOP_PATH}${incPhpFilePath#"${WORKING_PATH}"}
      remotePath=$(dirname ${remoteIncPhpFilePath})
      RESULT=$(sendFile ${incPhpFilePath} ${remotePath})
      if [ $RESULT -eq 0 ]; then
          echo "Send $incPhpFilePath failed"
      fi
    fi
  fi

  JSON_FILE=$(find $APP_ROOT -name "${INPUT_FILENAME_ONLY}.json")
  if [ -f $JSON_FILE ]; then
    if [ $USING_SCP -eq 1 ]; then
      remoteJsonFilePath=${TOP_PATH}${JSON_FILE#"${WORKING_PATH}"}
      remotePath=$(dirname ${remoteJsonFilePath})
      RESULT=$(sendFile ${JSON_FILE} ${remotePath})
      if [ $RESULT -eq 0 ]; then
          echo "Send $remoteJsonFilePath failed"
      fi
    fi
  fi
  #####################
  TEMP_URLS=""
  if [ -f $jsonConfig ]; then
    BIN[0]=$(cat $jsonConfig | $JQ -c '.ios.AppStore')
    BIN[1]=$(cat $jsonConfig | $JQ -c '.ios.Adhoc')
    BIN[2]=$(cat $jsonConfig | $JQ -c '.ios.Enterprise')
    BIN[3]=$(cat $jsonConfig | $JQ -c '.android.GoogleStore')
    BIN[4]=$(cat $jsonConfig | $JQ -c '.android.OneStore')
    BIN[5]=$(cat $jsonConfig | $JQ -c '.android.LiveServer')
    BIN[6]=$(cat $jsonConfig | $JQ -c '.android.TestServer')
  fi
  IFS=$'\n'
  if [ -f $JSON_FILE ]; then
    #####################
    appVersion=$(cat $JSON_FILE | $JQ '.appVersion ' | tr -d '"')
    buildVersion=$(cat $JSON_FILE | $JQ '.buildVersion ' | tr -d '"')
    gitLastLog=$(cat $JSON_FILE | $JQ '.gitLastLog' | tr -d '"' | sed -e 's/\\n//g')
    buildTime=$(cat $JSON_FILE | $JQ '.buildTime' | tr -d '"')
    urlPrefix=$(cat $JSON_FILE | $JQ '.urlPrefix' | tr -d '"' | sed -e "s/${frontEndProtocol}/${outBoundProtocol}/g" | sed -e "s/${frontEndPoint}/${outBoundPoint}/g")
    jenkinsBuildNumber=$(cat $JSON_FILE | $JQ '.buildNumber' | tr -d '"')
    releaseType=$(cat $JSON_FILE | $JQ '.releaseType' | tr -d '"')
    #####################
    FILE[0]=$(cat $JSON_FILE | $JQ -c '.files[0]')
    FILE[1]=$(cat $JSON_FILE | $JQ -c '.files[1]')
    FILE[2]=$(cat $JSON_FILE | $JQ -c '.files[2]')
    FILE[3]=$(cat $JSON_FILE | $JQ -c '.files[3]')
    FILE[4]=$(cat $JSON_FILE | $JQ -c '.files[4]')
    FILE[5]=$(cat $JSON_FILE | $JQ -c '.files[5]')

    for aFile in ${FILE[@]}; do
      if [[ $aFile == "null" ]]; then
        aFile='{"title":"","size":"","file":"","plist":""}'
      fi
      aFileLink=$(echo $aFile | $JQ -c '.file' | tr -d '"')
      if [[ ! -z "$aFileLink" ]]; then
        aFileTitle="$(echo $aFile | $JQ -c '.title' | tr -d '"')"
        CONTINUE=0
        for t in ${BIN[@]}; do
          aTitle=$(echo ${t} | $JQ '.title' | tr -d '"')
          if [[ "${aTitle}" == "${aFileTitle}" ]]; then
            yesOrNo=$(test $(echo ${t} | $JQ '.showToClient') = true && echo 1 || echo 0)
            if [ $yesOrNo -eq 0 ]; then
                CONTINUE=1
            fi
          fi
        done
        if [ $CONTINUE -eq 1 ]; then
            continue
        fi
        aFileSize=$(echo $aFile | $JQ -c '.size' | tr -d '"')
        TEMP_URLS="${TEMP_URLS}<li>"
        TEMP_URLS="${TEMP_URLS}<em class=\"txt1\">${aFileTitle}</em>"
        TEMP_URLS="${TEMP_URLS}<span class=\"bar\">&nbsp;(</span>"
        TEMP_URLS="${TEMP_URLS}<span class=\"txt2\">${aFileSize}</span>"
        TEMP_URLS="${TEMP_URLS}<span class=\"bar\">)&nbsp;</span>"
        TEMP_URLS="${TEMP_URLS}<a href=\"${urlPrefix}/${aFileLink}\">"
        TEMP_URLS="${TEMP_URLS}<span class=\"hide\">${aFileLink}</span></a>"
        TEMP_URLS="${TEMP_URLS}</li>"
      fi
    done
    #####################
  fi
  IFS=$' '

  DOWNLOAD_URLS="${DevEnvPrefix}
    <p><strong class=\"point_c\"><span>v${appVersion}.${buildVersion}&nbsp;&nbsp;&nbsp;
    <font size=\"2\" color=\"silver\">jenkins(<b>${jenkinsBuildNumber}</b>)</font></span></strong>
      <span>${buildTime}</span>
    <ul>
    ${TEMP_URLS}
    </ul>
    </p>
  ${DevEnvSuffix}"
  
  if [ -f $incPhpFilePath ]; then
    DESCRIPTION=$(cat ${incPhpFilePath} | grep '$version_target' | sed -e 's/.*"\(.*\)".*/\1/')
    DETAIL_DESC=$(cat ${incPhpFilePath} | grep '$version_details' | sed -e 's/.*"\(.*\)".*/\1/')
    DETAIL_DESC="(요약) ${DETAIL_DESC}"
  fi
} #function readJsonAndSetVariables

function sendingEmail() {
  if [ $USING_MAIL -eq 1 ]; then
    if [[ "$releaseType" == "release" ]]; then
      subjectText="[${APP_NAME} ${APP_VERSION} > ${OS_NAME}] ${RELEASE_KEY} ${APP_NAME} 배포 -"
      messageHeader="${OS_NAME} ${RELEASE_KEY} ${APP_NAME} v${appVersion} 전달합니다."
    else
      subjectText="[${APP_NAME} ${APP_VERSION} > ${OS_NAME}] '$DESCRIPTION' ${DEVELOP_KEY} ${APP_NAME} 배포 -"
      messageHeader="${OS_NAME} ${DEVELOP_KEY} ${APP_NAME} v${appVersion} 전달합니다.<br /></br />${DETAIL_DESC}"
    fi
    if [ $DEBUGGING -eq 1 ]; then
      mailApp="$FRONTEND_POINT/${TOP_PATH}/.test/testmail_release.php"
    else
      mailApp="$FRONTEND_POINT/${TOP_PATH}/sendmail_release.php"
    fi
    ##
    $CURL --data-urlencode "subject1=${subjectText}" \
      --data-urlencode "subject2=version ${appVersion}.${buildVersion}" \
      --data-urlencode "html_header=${HTML_HEADER}" \
      --data-urlencode "message_header=<br />${messageHeader}<br /><br /><H2><b>배포 파일 정보</b></H2>$DOWNLOAD_URLS" \
      --data-urlencode "message_description=<pre>${DEV_ENV}</pre><br />" \
      --data-urlencode "message_html=${DevEnvPrefix}${gitLastLog}${DevEnvSuffix}" \
      ${mailApp}
  fi

  ##
  # Sync files to Neo2UA (Synology NAS)
  if [ -f ./syncToNasNeo2UA.sh ]; then
    ./syncToNasNeo2UA.sh
  fi
} # function sendingEmail

function getDevToolInfo() {
  if [ -f ${jsonConfig} ]; then
    isFlutterEnabled=$(test $(cat $jsonConfig | $JQ '.Flutter.enabled') = true && echo 1 || echo 0)
    FlutterBin=$(cat $jsonConfig | $JQ '.Flutter.path' | tr -d '"')
    isReactNativeEnabled=$(test $(cat $jsonConfig | $JQ '.ReactNative.enabled') = true && echo 1 || echo 0)
    if [ $isReactNativeEnabled -eq 1 ]; then
      NodePath=$(cat $jsonConfig | $JQ '.ReactNative.path' | tr -d '"')
      ReactNativeBin="${NodePath}/npm"
      export PATH=${NodePath}:$PATH
    else
      NodePath="node"
      ReactNativeBin="npm"
    fi
    OTHER_BUILD_ENV=""
    if [ $isFlutterEnabled -eq 1 ]; then
      BUILD_COMMAND=$FlutterBin
    elif [ $isReactNativeEnabled -eq 1 ]; then
      BUILD_COMMAND="./gradlew"
      OTHER_BUILD_ENV="node "$(node --version)
      OTHER_BUILD_ENV="${OTHER_BUILD_ENV}<BR />npm v"$(npm --version)
      OTHER_BUILD_ENV="${OTHER_BUILD_ENV}<BR />"
    else
      BUILD_COMMAND="./gradlew"
    fi
  fi
}

function handlingSendMailOrNot() {
  if [[ "$INPUT_OS" != "both" ]]; then
    sendingEmail
  else
    BothDevEnv="${BothDevEnv}${BothDevEnvPrefix}<B>${OS_NAME}</B><BR />${DEV_ENV}<BR /><BR />"
    BothDevEnv="${BothDevEnv}${BothDevEnvSuffix}"
  fi
}

## for iOS / Android both
BothDevEnv=""
BohtDownloadURLs=""
BothDevEnvPrefix=""
BothDevEnvSuffix=""
##

if [[ "$INPUT_OS" == "android" || "$INPUT_OS" == "both" ]]; then
  ##
  ANDROID_PATH="android"
  INPUT_ANDROID="${ANDROID_PATH}/dist_android.html"
  OUTPUT_ANDROID="${ANDROID_PATH}/index.html"
  ##
  APP_ROOT_SUFFIX="android_distributions"
  OS_NAME="Android"
  ##
  PROCESS_OS="android"
  readJsonAndSetVariables
  BohtDownloadURLs="${BohtDownloadURLs}<B>${OS_NAME}</B><BR />${DOWNLOAD_URLS}<BR />"

  if [ -f ${jsonConfig} ]; then
    WORKSPACE=$(cat ${jsonConfig} | $JQ '.android.jenkinsWorkspace' | tr -d '"')
    AOS_APPPATH=$(cat ${jsonConfig} | $JQ '.android.appPath' | tr -d '"')
    AOS_APPPATH=${AOS_APPPATH%"app"}
    ##
    getDevToolInfo
    DEV_ENV=$(${WORKSPACE}/${AOS_APPPATH}/${BUILD_COMMAND} --version)
    if [ -z $(echo $DEV_ENV | xargs) ]; then
      DEV_ENV="$(cd $WORKSPACE && $BUILD_COMMAND --version)"
    fi
    DEV_ENV="${OTHER_BUILD_ENV}<BR />${DEV_ENV}"
    DEV_ENV="${DevEnvPrefix}${DEV_ENV}${DevEnvSuffix}"
    ##
  fi

  if [ $IS_RESEND -eq 1 ]; then
    if [ -d ${WORKSPACE} ]; then
      handlingSendMailOrNot
    fi
  else
    if [[ "$releaseType" == "release" ]]; then
      tempFilename="temp_release.html"
    else
      tempFilename="temp.html"
    fi
    if [ $USING_HTML -eq 1 ]; then
      if [ -f $OUTPUT_ANDROID ]; then
          cp -f $OUTPUT_ANDROID $OUTPUT_ANDROID.bak
      fi
      if [ -f $INPUT_ANDROID ]; then
          cat $INPUT_ANDROID | \
          sed -e 's/<font color=red>A<\/font>/A/' | \
          sed -e 's/iOS\/dist_ios/iOS\/index/' | \
          sed -e 's/dist_domestic/dist_client/' |\
          sed -e 's/\(<div class="large-4 columns">\)<a href.*$/\1/g' > $OUTPUT_ANDROID
      fi
    fi
    ###################
    HTML_SNIPPET="${WORKING_PATH}/${ANDROID_PATH}/${tempFilename}"
    if [ -f $HTML_SNIPPET -a $USING_HTML -eq 1 ]; then
      if [ -n "$FILE_PATH" ] && [ -f $FILE_PATH ]; then
        if cmp -s $HTML_SNIPPET $FILE_PATH; then
          if [ -d ${WORKSPACE} ]; then
            handlingSendMailOrNot
            rm -f $HTML_SNIPPET
          fi
        else
          echo "Differ file between $HTML_SNIPPET and $FILE_PATH"
        fi
      else
        echo "File not found => $FILE_PATH"
      fi
    else
      if [ -d ${WORKSPACE} ]; then
        handlingSendMailOrNot
      fi
    fi # html snippet exist
  fi # is_resending
fi # Android


if [[ "$INPUT_OS" == "ios" || "$INPUT_OS" == "both" ]]; then
  ##
  IOS_PATH="ios"
  INPUT_IOS="${IOS_PATH}/dist_ios.html"
  OUTPUT_IOS="${IOS_PATH}/index.html"
  ##
  APP_ROOT_SUFFIX="ios_distributions"
  OS_NAME="iOS"
  ##
  PROCESS_OS="ios"
  readJsonAndSetVariables
  BohtDownloadURLs="${BohtDownloadURLs}<B>${OS_NAME}</B><BR />${DOWNLOAD_URLS}<BR />"
  ##
  getDevToolInfo
  if [[ -z "$XCODE" ]]; then
    DEV_ENV="No Xcode.app installed...!"
  else
    DEV_ENV="$($XCODE -version)<BR />CocoaPod $($POD --version)"
    if [ -f $(which sw_vers) ]; then
      DEV_ENV="$DEV_ENV <BR />Hostname: $(hostname)<BR />$(sw_vers)"
    fi
  fi
  DEV_ENV="${OTHER_BUILD_ENV}<BR />${DEV_ENV}"
  DEV_ENV="${DevEnvPrefix}${DEV_ENV}${DevEnvSuffix}"
  ##
  if [ $IS_RESEND -eq 1 ]; then
    handlingSendMailOrNot
  else
    #####
    if [[ "$releaseType" == "release" ]]; then
      tempFilename="temp_release.html"
    else
      tempFilename="temp.html"
    fi
    if [ $USING_HTML -eq 1 ]; then
      if [ -f $OUTPUT_IOS ]; then
          cp -f $OUTPUT_IOS $OUTPUT_IOS.bak
      fi
      if [ -f $INPUT_IOS ]; then
          cat $INPUT_IOS | \
          sed -e 's/<font color=red>i<\/font>/i/' | \
          sed -e 's/android\/dist_android/android\/index/' | \
          sed -e 's/dist_domestic/dist_client/' | \
          sed -e 's/\(<div class="large-4 columns">\)<a href.*$/\1/g' > $OUTPUT_IOS
      fi
    fi
    HTML_SNIPPET="${WORKING_PATH}/${IOS_PATH}/${tempFilename}"
    if [ -f $HTML_SNIPPET -a $USING_HTML -eq 1 ]; then
      if [ -n "$FILE_PATH" ] && [ -f $FILE_PATH ]; then
        if cmp -s $HTML_SNIPPET $FILE_PATH; then
          handlingSendMailOrNot
          rm -f $HTML_SNIPPET
        else
          echo "Differ file between $HTML_SNIPPET and $FILE_PATH"
        fi
      else
        echo "File not found => $FILE_PATH"
      fi
    else
      handlingSendMailOrNot
    fi # html snippet exist
  fi # is_resending
fi

if [[ "$INPUT_OS" == "both" ]]; then
  if [[ "$INPUT_FILE" == *"android"* ]]; then
    OS_NAME="Android+iOS"
  else  
    OS_NAME="iOS+Android"
  fi
  DEV_ENV="${BothDevEnv}"
  DOWNLOAD_URLS="${BohtDownloadURLs}"
  sendingEmail
fi
##
# Push distribution result to git repository
if [[ "$(git fetch --all)" == "Fetching origin" ]]; then
  git add .
  git commit -a -m "[release] $INPUT_FILE"
  git push
else
  git add .
  git commit -a -m "[release] $INPUT_FILE"
  git pull
  git push
fi
