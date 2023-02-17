#!/bin/bash
# (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
# 
# All Rights Reserved. See copyright.txt for details and a complete list of authors.
# Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
#
# Reactivate locked accounts

usage()
{
    echo "Usage: $0 userlogin"
}

if [[ "$1" = "" ]]
then
    usage
    exit 1
else
    USERLOGIN=$1
fi
echo "Re-activate account: $USERLOGIN"

if [[ -f db/local.php ]]
then
    LOCALFILE="db/local.php"
    echo "Detected: $LOCALFILE"
else
  echo "ERROR: db/local.php file not found"
    exit 2
fi

echo "Using: $LOCALFILE"
if [[ -f "$LOCALFILE" ]]
then
  DB_DETECTED=$(grep "^\$dbs_tiki" "$LOCALFILE" | cut -d"'" -f2| tail -n 1);
  USER_DETECTED=$(grep "^\$user_tiki" "$LOCALFILE" | cut -d"'" -f2| tail -n 1);
  PASS_DETECTED=$(grep "^\$pass_tiki" "$LOCALFILE" | cut -d"'" -f2| tail -n 1);
else
  echo "ERROR: local.php file not found"
    usage
  exit 1
fi

FEEDBACK=$(mysql -u "${USER_DETECTED}" --password="${PASS_DETECTED}" "${DB_DETECTED}" -e "UPDATE users_users SET provpass = \"\", unsuccessful_logins = 0, waiting = NULL  WHERE login = \"${USERLOGIN}\";")

echo "Feedback : $FEEDBACK"

