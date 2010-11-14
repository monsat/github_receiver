#!/bin/sh
# This is an example.
# Copy to use this file.
MYDIR=$(cd $(dirname $0);pwd)
APPDIR="$MYDIR/../../../"
if [ -f $APPDIR/tmp/cache/github_pull_ready_basic ]
then
  cd $APPDIR/../../../../ && git --git-dir=.git pull origin
fi
exit
