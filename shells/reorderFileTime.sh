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
if [ -f $jsonConfig ]; then
  if [ $DEBUGGING -eq 1 ]; then
    config=$(cat $jsonConfig | $JQ '.development')
  else
    config=$(cat $jsonConfig | $JQ '.production')
  fi
  PREFIX=$(echo $config | $JQ '.outputPrefix')
fi
LIST=$(find ${TARGET} -name "*.json")
for jsonFile in $LIST; do
    fileDirname=$(dirname ${jsonFile})
    fileBasename=${jsonFile#"${fileDirname}/"}
    fileBasename=${fileBasename%".json"}
    htmlFile="${fileBasename}.html"
    timeToBe=$(cat ${jsonFile} | jq '.buildTime' | tr -d '". :')
    realHtml=$(find "${fileDirname}" -name "*${htmlFile}*")
    for x in $realHtml; do
      echo "touch -t ${timeToBe} ${realHtml}"
      if [ -f ${realHtml} ]; then
        touch -t ${timeToBe} "${realHtml}"
      fi
      realJson="${realHtml%.html}.json"
      realJson="${realJson/zzz_/}"
      if [ -f ${realJson} ]; then
        touch -t ${timeToBe} "${realJson}"
      fi
    done
done
