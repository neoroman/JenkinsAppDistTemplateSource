#!/bin/bash
SCRIPT_PATH=$(dirname $0)
jsonConfig="${SCRIPT_PATH}/config/config.json"
####### DEBUG or Not #######
if [[ "$JQ" == "" ]]; then
  if [ -f "/usr/local/bin/jq" ]; then
    JQ="/usr/local/bin/jq"
  elif [ -f "/usr/bin/jq" ]; then
    JQ="/usr/bin/jq"
  else
    JQ="/bin/jq"
  fi
fi
############################
config=$(cat $jsonConfig | $JQ '.production')
TOP_PATH=$(echo $config | $JQ '.topPath' | tr -d '"')
PATHs=( ${TOP_PATH//\// })
TOP_FOLDER=${PATHs[0]}
############################
SSH_ENDPOINT=$(cat $jsonConfig | $JQ '.ssh.endpoint' | tr -d '"')
USER_DOMAIN=( ${TOP_PATH//@/ })
RUSER=${USER_DOMAIN[0]}
RHOST=$(echo $config | $JQ '.outBoundPoint' | tr -d '"')
RPATH="/Volumes/miniWebDocs/localDocuments/${TOP_FOLDER}"
LPATH="/Library/WebServer/localDocuments/${TOP_PATH}"
############################
RSYNC=/usr/bin/rsync
if [ -d $RPATH ]; then
    $RSYNC -rt --progress --delete-after "$LPATH" "$RPATH" --exclude ".git"
else
    echo "$RPATH not exist...!"
fi
