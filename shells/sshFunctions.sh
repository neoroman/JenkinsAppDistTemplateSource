#!/bin/sh
#
# Written by EungShik Kim, 2021.08.01
#
################################################################################
if [ -f "../../config/config.json" ]; then
  jsonConfig="../../config/config.json"
elif [ -f "../config/config.json" ]; then
  jsonConfig="../config/config.json"
fi
if [[ "$JQ" == "" ]]; then
  if [ -f "/usr/local/bin/jq" ]; then
    JQ="/usr/local/bin/jq"
  elif [ -f "/usr/bin/jq" ]; then
    JQ="/usr/bin/jq"
  else
    JQ="/bin/jq"
  fi
fi
GIT=$(which git)
SSH=$(which ssh)
SCP=$(which scp)
SFTP_PORT=$(cat $jsonConfig | $JQ '.ssh.port' | tr -d '"')
SFTP_ENDPOINT=$(cat $jsonConfig | $JQ '.ssh.endpoint' | tr -d '"')
SFTP_TARGET=$(cat $jsonConfig | $JQ '.ssh.target' | tr -d '"')
function checkDirExist() {
  DIR="$1"
  $SSH -p ${SFTP_PORT} ${SFTP_ENDPOINT} <<+
test -d "${SFTP_TARGET}/${DIR}" && echo 1 || echo 0
+
  ## Example
  # if [ $(checkDirExist ios_distributions) -eq 1 ]; then
  #   echo "Dir exist: ios_distributions"
  # else
  #   echo "Dir **NOT** exist: ios_distributions"
  # fi
}
function checkFileExist() {
  FILE="$1"
  $SSH -p ${SFTP_PORT} ${SFTP_ENDPOINT} <<+
test -f "${SFTP_TARGET}/${FILE}" && echo 1 || echo 0
+
  ## Example
  # if [ $(checkFileExist ios_distributions/ExportOptions.plist) -eq 1 ]; then
  #   echo "File exist: ios_distributions/ExportOptions.plist"
  # else
  #   echo "File **NOT** exist: ios_distributions/ExportOptions.plist"
  # fi
}
function sendFile() {
  FILE="$1"
  DEST="$2"
  DEST=${DEST/(/\\(/}
  DEST=${DEST/)/\\)/}
  $SCP -pq -P ${SFTP_PORT} "${FILE}" ${SFTP_ENDPOINT}:${SFTP_TARGET}/${DEST}/ && echo 1 || echo 0
  ## Example
  # if [ $(sendFile $0 ios_distributions) -eq 1 ]; then
  #   echo "Successfully send file $0 to ios_distributions"
  # else
  #   echo "Failed to send file"
  # fi
}
function removeFile() {
  FILE="$1"
  if [ $(checkFileExist ${FILE}) -eq 1 ]; then
    $SSH -p ${SFTP_PORT} ${SFTP_ENDPOINT} <<+
rm "${SFTP_TARGET}/${FILE}" && echo 1 || echo 0
+
  else
    echo 0
  fi
  ## Example
  # if [ $(removeFile ios_distributions/$0) -eq 1 ]; then
  #   echo "Successfully remove $0"
  # else
  #   echo "Fail to remove $0"
  # fi
}
function renameFile() {
  SRC="$1"
  TARGET="$2"
  if [ $(checkFileExist ${SRC}) -eq 1 ]; then
    $SSH -p ${SFTP_PORT} ${SFTP_ENDPOINT} <<+
mv "${SFTP_TARGET}/${SRC}" "${SFTP_TARGET}/${TARGET}" && echo 1 || echo 0
+
  else
    echo 0
  fi
  ## Example
  # if [ $(renameFile $0 $1) -eq 1 ]; then
  #   echo "Successfully rename from $0 to $1"
  # else
  #   echo "fail to rename from $0 to $1"
  # fi
}
function makeDir() {
  DIR="$1"
  if [ $(checkDirExist ${DIR}) -eq 0 ]; then
    $SSH -p ${SFTP_PORT} ${SFTP_ENDPOINT} <<+
mkdir "${SFTP_TARGET}/${DIR}" && echo 1 || echo 0
+
  else
    echo 1
  fi
  ## Example
  # if [ $(makeDir ios_distributions/abc) -eq 1 ]; then
  #   echo "Successfully make dir ios_distributions/abc"
  # else
  #   echo "Fail to make dir ios_distributions/abc"
  # fi
}
function remoteGitPull() { # It's not working...;-(, 2021.08.01
  DIR="$1"
  if [ $(checkDirExist ${DIR}) -eq 1 ]; then
    $SSH -p ${SFTP_PORT} ${SFTP_ENDPOINT} \
cd ${SFTP_TARGET}/${DIR} && $GIT fetch >/dev/null 2>&1 && $GIT pull >/dev/null 2>&1 && echo 1 || echo 0
  else
    echo 0
  fi
}
################################################################################
##
# Git push & remote pull
#$GIT config --global user.name "AppDevAccount"
#$GIT config --global user.email "appdev.svc@company.com"
#$GIT add . && $GIT commit -a -m "[release] update site" && $GIT push
#
#if [ $(remoteGitPull ${TOP_PATH}) -eq 1 ]; then
#  echo "Successfully sync site on $SFTP_ENDPOINT"
#else
#  echo "Fail to sync on $SFTP_ENDPOINT"
#fi
