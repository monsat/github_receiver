#!/bin/sh
# This is an example.
# Copy to use this file.
MYDIR=$(cd $(dirname $0);pwd)
APPDIR="$MYDIR/../../../"
READYFILE="$APPDIR/tmp/cache/github_pull_basic"
GITHOMEDIR="$APPDIR/../../../../"
if [ -f $READYFILE ]
then
  cd $GITHOMEDIR && git --git-dir=.git pull origin && rm $READYFILE
fi
exit
