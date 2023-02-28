#!/bin/bash
#
# Written by EungShik Kim, 2021.10.08
#
#####
jsonConfig="../../config/config.json"
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
if test -z $INPUT_OS; then
  echo "Usage: $SCRIPT_NAME [-p {ios,android}] [-f input_file]"
  echo ""
  echo "Error: ios 또는 android 인자 없음"
  exit
fi
#####
JQ=$(which jq)
if [[ "$JQ" == "" ]]; then
  if [ -f "/usr/local/bin/jq" ]; then
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
elif [[ "${INPUT_OS}" == "android" ]]; then
    TARGET="../android_distributions"
    if [ ! -d $TARGET ]; then
        TARGET="./android_distributions"
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
      touch -t ${timeToBe} "${realHtml}"
    done
done
