#!/bin/bash
SCRIPT_PATH=$(dirname $0)
jsonConfig="${SCRIPT_PATH}/../../config/config.json"
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
if [[ $(dirname $PWD) = */test ]]; then
  TOP_FOLDER="test"
  TOP_PATH="${TOP_FOLDER}/${PATHs[1]}"
fi
############################
PROCESS_NAME="${TOP_PATH//\//_}"
PIDFILE="/var/tmp/${PROCESS_NAME}.pid"
if [ -f $PIDFILE ]; then
  PID=$(cat $PIDFILE)
  ps -p $PID > /dev/null 2>&1
  if [ $? -eq 0 ]; then
    echo "Process already running"
    exit 1
  else
    ## Process not found assume not running
    echo $$ > $PIDFILE
    if [ $? -ne 0 ]; then
      echo "Could not create PID file"
      exit 1
    fi
  fi
else
  echo $$ > $PIDFILE
  if [ $? -ne 0 ]; then
    echo "Could not create PID file"
    exit 1
  fi
fi
############################
SSH_ENDPOINT=$(cat $jsonConfig | $JQ '.ssh.endpoint' | tr -d '"')
USER_DOMAIN=( ${TOP_PATH//@/ })
RUSER=${USER_DOMAIN[0]}
RHOST=$(echo $config | $JQ '.outBoundPoint' | tr -d '"')
RPATH="/Volumes/miniWebDocs/localDocuments/${TOP_FOLDER}"
LPATH="/Library/WebServer/localDocuments/${TOP_PATH}"
echo "left = $LPATH"
echo "right = $RPATH"
############################
RSYNC=/usr/bin/rsync
if [ -d $RPATH ]; then
    $RSYNC -rt --progress --delete-after "$LPATH" "$RPATH" --exclude ".git" --exclude ".DS_Store" --omit-dir-times
else
    echo "$RPATH not exist...!"
fi
############################
sleep 5
rm $PIDFILE
