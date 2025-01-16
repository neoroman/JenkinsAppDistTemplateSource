#!/bin/bash
#
# Written by EungShik Kim, 2021.10.08
#
#####
SCRIPT_PATH="$(dirname "$0")"
jsonConfig="../config/config.json"
if [ ! -f $jsonConfig ]; then
  jsonConfig="../../config/config.json"
fi
if [ -f $jsonConfig ]; then
  jsonConfig=$SCRIPT_PATH/$jsonConfig
fi
SCRIPT_NAME=$(basename $0)
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
    -d|--debug)
      DEBUGGING=1
      shift # past argument
      ;;
    *|-h|--help)  # unknown option
      shift # past argument
      echo "Usage: $SCRIPT_NAME [-p {ios,android}] [-d]"
      echo ""
      echo "optional arguments:"
      echo "   -h, --help        show this help message and exit:"
      echo "   -p {ios,android}, --platfor {ios,android}"
      echo "                     assign platform as iOS or Android to processing"
      echo "   -d, --debug       debugging mode"
      echo ""
      exit
      ;;
  esac
done
if test -z "$INPUT_OS"; then
  echo "Usage: $SCRIPT_NAME [-p {ios,android}] [-f input_file]"
  echo ""
  echo "Error: ios 또는 android 인자 없음"
  exit
fi
#####
JQ=$(which jq)
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
#####
if [[ "${INPUT_OS}" == "ios" ]]; then
    TARGET="../ios_distributions"
    if [ ! -d $TARGET ]; then
        TARGET="./ios_distributions"
    fi
    if test ! -d $TARGET && command -v realpath >/dev/null; then
        TARGET="$(realpath $SCRIPT_PATH)/../../ios_distributions"
        TARGET=$(realpath $TARGET)
    fi
elif [[ "${INPUT_OS}" == "android" ]]; then
    TARGET="../android_distributions"
    if [ ! -d $TARGET ]; then
        TARGET="./android_distributions"
    fi
    if test ! -d $TARGET && command -v realpath >/dev/null; then
        TARGET="$(realpath $SCRIPT_PATH)/../../android_distributions"
        TARGET=$(realpath $TARGET)
    fi
fi
if [ -f "$jsonConfig" ]; then
  if [ $DEBUGGING -eq 1 ]; then
    config=$(cat $jsonConfig | $JQ '.development')
  else
    config=$(cat $jsonConfig | $JQ '.production')
  fi
  PREFIX=$(echo $config | $JQ '.outputPrefix')
fi
LIST=$(find "${TARGET}" -name "*.json")

# Iterate through each JSON file
for jsonFile in $LIST; do
  # Extract directory and basename (filename without path)
  fileDirname=$(dirname "${jsonFile}")
  fileBasename=$(basename "${jsonFile}" .json)

  # Define the corresponding HTML file
  htmlFile="${fileBasename}.html"

  # Extract the build time from the JSON file
  timeToBe=$(jq -r '.buildTime' "${jsonFile}" | tr -d '". :')

  # Find all matching HTML files in the same directory
  realHtml=$(find "${fileDirname}" -name "*${htmlFile}*")

  # Process each matching HTML file
  for x in $realHtml; do
    echo "Touching file: ${x} with build time: ${timeToBe}"

    # Update the timestamp of the HTML file if it exists
    if [ -f "${x}" ]; then
      touch -t "${timeToBe}" "${x}"
    fi

    # Update timestamp for the corresponding JSON file, if it exists
    realJson="${x%.html}.json"
    realJson="${realJson/zzz_/}"
    if [ -f "${realJson}" ]; then
      touch -t "${timeToBe}" "${realJson}"
    fi

    # Remove "zzz_" version if both "zzz_" and normal HTML files exist
    zzzHtml="${x}"
    normalHtml="${x/zzz_/}"
    if [[ "${zzzHtml}" != "${normalHtml}" && -f "${zzzHtml}" && -f "${normalHtml}" ]]; then
      echo "Removing redundant file: ${zzzHtml}"
      rm -f "${zzzHtml}"
    fi

    # Get the base filename for further matching
    filenameOnly=${fileBasename/zzz_/}

    # Touch files matching $filenameOnly*.{ipa, plist, apk, aab, png, zip, inc.php}
    matchingFiles=$(find "${fileDirname}" -type f \( \
      -name "${filenameOnly}*.ipa" -o \
      -name "${filenameOnly}*.plist" -o \
      -name "${filenameOnly}*.apk" -o \
      -name "${filenameOnly}*.aab" -o \
      -name "${filenameOnly}*.png" -o \
      -name "${filenameOnly}*.zip" -o \
      -name "${filenameOnly}*.inc.php" \))

    for file in $matchingFiles; do
      echo "Touching file: ${file} with build time: ${timeToBe}"

      # Update the timestamp of the file
      if [ -f "${file}" ]; then
        touch -t "${timeToBe}" "${file}"
      fi
    done
  done
done