#! /bin/sh

# (c) Copyright by authors of the Tiki Wiki CMS Groupware Project
#
# All Rights Reserved. See copyright.txt for details and a complete list of authors.
# Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
# $Id$

# This file sets permissions and creates relevant folders for Tiki.
#

# part 0 - choose production mode or verbose debugging mode
# ---------------------------------------------------------

DEBUG=0 # production mode
#DEBUG=1 # debugging mode
DEBUG_PATH=0 # production mode
#DEBUG_PATH=1 # debugging mode
DEBUG_UNIX=0 # production mode
#DEBUG_UNIX=1 # debugging mode
DEBUG_PREFIX='D>'
ECHOFLAG=1 # one empty line before printing used options in debugging mode
PATCHCOMPOSERFLAG="0" # patch composer.phar to avoid the warnings
                      # unfortunately, this file checks its own signature
                      # and thus does not allow modifications
# log composer instead of screen out# log composer instead of screen outputput
LOGCOMPOSERFLAG="0" # default for composer output 
TIKI_COMPOSER_INSTALL_LOG=tiki-composer-install.log
TIKI_COMPOSER_SELF_UPDATE_LOG=tiki-composer-self-update.log

# part 1 - preliminaries
# ----------------------

PERMISSIONCHECK_DIR="permissioncheck"
SEARCHPATH="/bin /usr/bin /sbin /usr/sbin /usr/local/bin /usr/local/sbin /opt/bin /opt/sbin /opt/local/bin /opt/local/sbin"
#USE_CASES_FILE="usecases.txt"
USE_CASES_FILE="usecases.bin"
USE_CASES_PATH=${PERMISSIONCHECK_DIR}
USE_CASES_NAME=${USE_CASES_PATH}/${USE_CASES_FILE}
WHAT_NEXT_AFTER_c='f'
WHAT_NEXT_AFTER_f='x'

# Composer: If you are installing via a released Tiki package (zip, tar.gz,
# tar.bz2, 7z), you can and should skip using Composer. If you are installing and
# upgrading via GIT, you need to run Composer after 'git clone' and 'git pull'.
# More info at https://doc.tiki.org/Composer

if [ -d ".svn" ] || [ -d ".git" ]; then
    DEFAULT_WHAT='c'
else
    DEFAULT_WHAT='f'
fi

define_path() {
# define PATH for executable mode
if [ ${DEBUG_PATH} = '1' ] ; then
    echo ${DEBUG_PREFIX}
    echo ${DEBUG_PREFIX} old path: ${PATH}
    echo ${DEBUG_PREFIX}
fi
#PATH="${PATH}:/bin:/usr/bin:/sbin:/usr/sbin:/usr/local/bin:/usr/local/sbin:/opt/bin:/opt/sbin:/opt/local/bin:/opt/local/sbin"
#for ADDPATH in `echo /bin /usr/bin /sbin /usr/sbin /usr/local/bin /usr/local/sbin /opt/bin /opt/sbin /opt/local/bin /opt/local/sbin` ; do
for ADDPATH in ${SEARCHPATH} ; do
    if [ -d ${ADDPATH} ] ; then
        PATH="${PATH}:${ADDPATH}"
        if [ ${DEBUG_PATH} = '1' ] ; then
             echo ${DEBUG_PREFIX} ${ADDPATH} exists
        fi
    else
        if [ ${DEBUG_PATH} = '1' ] ; then
            echo ${DEBUG_PREFIX} ${ADDPATH} does not exist
        fi
    fi
done
if [ ${DEBUG_PATH} = '1' ] ; then
    echo ${DEBUG_PREFIX}
    echo ${DEBUG_PREFIX} new path: ${PATH}
fi
}

define_path

# set used commands
if [ ${DEBUG_UNIX} = '1' ] ; then
    echo ${DEBUG_PREFIX}
    echo ${DEBUG_PREFIX} before:
    echo ${DEBUG_PREFIX} CAT=${CAT}
    echo ${DEBUG_PREFIX} CHGRP=${CHGRP}
    echo ${DEBUG_PREFIX} CHMOD=${CHMOD}
    echo ${DEBUG_PREFIX} CHOWN=${CHOWN}
    echo ${DEBUG_PREFIX} FIND=${FIND}
    echo ${DEBUG_PREFIX} ID=${ID}
    echo ${DEBUG_PREFIX} MKDIR=${MKDIR}
    echo ${DEBUG_PREFIX} MV=${MV}
    echo ${DEBUG_PREFIX} RM=${RM}
    echo ${DEBUG_PREFIX} SORT=${SORT}
    echo ${DEBUG_PREFIX} TOUCH=${TOUCH}
    echo ${DEBUG_PREFIX} UNIQ=${UNIQ}
fi
# list of commands
CAT=`which cat`
CHGRP=`which chgrp`
CHMOD=`which chmod`
CHOWN=`which chown`
CUT=`which cut`
FIND=`which find`
GREP=`which grep`
ID=`which id`
MKDIR=`which mkdir`
MV=`which mv`
PHPCLI=`which php`
RM=`which rm`
SORT=`which sort`
TOUCH=`which touch`
UNIQ=`which uniq`
if [ ${DEBUG_UNIX} = '1' ] ; then
    echo ${DEBUG_PREFIX}
    echo ${DEBUG_PREFIX} after:
    echo ${DEBUG_PREFIX} CAT=${CAT}
    echo ${DEBUG_PREFIX} CHGRP=${CHGRP}
    echo ${DEBUG_PREFIX} CHMOD=${CHMOD}
    echo ${DEBUG_PREFIX} CHOWN=${CHOWN}
    echo ${DEBUG_PREFIX} FIND=${FIND}
    echo ${DEBUG_PREFIX} ID=${ID}
    echo ${DEBUG_PREFIX} MKDIR=${MKDIR}
    echo ${DEBUG_PREFIX} MV=${MV}
    echo ${DEBUG_PREFIX} RM=${RM}
    echo ${DEBUG_PREFIX} SORT=${SORT}
    echo ${DEBUG_PREFIX} TOUCH=${TOUCH}
    echo ${DEBUG_PREFIX} UNIQ=${UNIQ}
fi

# hint for users
#POSSIBLE_COMMANDS='open|fix|nothing'
POSSIBLE_COMMANDS="composer|fix|insane|mixed|morepain|moreworry|nothing|open|pain|paranoia|paranoia-suphp|risky|sbox|sboxworkaround|suphpworkaround|worry"
#HINT_FOR_USER="Type 'fix', 'nothing' or 'open' as command argument."
HINT_FOR_USER="\nType 'fix', 'nothing' or 'open' as command argument.
\nIf you used Tiki Permission Check via PHP, you know which of the following commands will probably work:
\ninsane mixed morepain moreworry pain paranoia paranoia-suphp risky sbox worry
\nMore documentation: https://doc.tiki.org/Permission+Check\n"

hint_for_users() {
    ${CAT} <<EOF
Type 'fix', 'nothing' or 'open' as command argument.
If you used Tiki Permission Check via PHP, you know which of the following commands will probably work:
insane mixed morepain moreworry pain paranoia paranoia-suphp workaround risky sbox worry

There are some other commands recommended for advanced users only.
More documentation about this: https://doc.tiki.org/Permission+Check
EOF
}

usage() {
#usage: $0 [<switches>] open|fix
    #cat <<EOF
    ${CAT} <<EOF
usage: sh `basename $0` [<switches>] ${POSSIBLE_COMMANDS}
or if executable
usage: $0 [<switches>] ${POSSIBLE_COMMANDS}
-h           show help
-u user      owner of files (default: $AUSER)
-g group     group of files (default: $AGROUP)
-v virtuals  list of virtuals (for multitiki, example: "www1 www2")
-p php       alternate PHP command (default: php)
-n           not prompt for user and group, assume current
-k           don't guess user and group from context, keep same user and group as web root
-d off|on    disable|enable debugging mode (override script default)
-q           quiet (workaround to silence composer, e.g. in cron scripts)

There are some other commands recommended for advanced users only.
More documentation about this: https://doc.tiki.org/Permission+Check

Example: sh `basename $0` -n fix
EOF
}

# evaluate command line options (cannot be done inside a function)
set_debug() {
    case ${OPTARG} in
        off) DEBUG=0 ;;
        on) DEBUG=1 ;;
        *) DUMMY="no override, default remains active" ;;
    esac
}

OPT_AUSER=
OPT_AGROUP=
OPT_VIRTUALS=
OPT_PHPCLI=
OPT_USE_CURRENT_USER_GROUP=
OPT_QUIET=

while getopts "hu:g:v:p:nkd:q" OPTION; do
    case $OPTION in
        h) usage ; exit 0 ;;
        u) OPT_AUSER=$OPTARG ;;
        g) OPT_AGROUP=$OPTARG ;;
        v) OPT_VIRTUALS=$OPTARG ;;
        p) OPT_PHPCLI=$OPTARG ;;
        n) OPT_USE_CURRENT_USER_GROUP=1 ;; # Actually guess from context for historical reasons
        k) OPT_GUESS_USER_GROUP_FROM_ROOT=1 ;; # Overrides -n user and group values
        d) set_debug ;;
        q) OPT_QUIET="-q" ;;
        ?) usage ; exit 1 ;;
    esac
    if [ -n "$OPT_PHPCLI" ]; then
        PHPCLI=`which "${OPT_PHPCLI}"`
        if [ ! -n "$PHPCLI" ]; then
            echo "PHP command: ${OPT_PHPCLI} not found. Please provide an existing command."
            exit 1
        fi
        #echo "PHP command: ${PHPCLI}"
    fi
    if [ ${DEBUG} = '1' ] ; then
        if [ ${ECHOFLAG} = '1' ] ; then
            ECHOFLAG=0
            echo ${DEBUG_PREFIX}
        fi
        OUTPUT="option: -${OPTION}"
        if [ -n ${OPTARG} ] ; then
            OUTPUT="${OUTPUT} ${OPTARG}"
        fi
        echo ${DEBUG_PREFIX} ${OUTPUT}
    fi
done
shift $(($OPTIND - 1))

# define command to execute for main program
if [ -z $1 ]; then
    COMMAND="default"
    EXITONFAIL="n"
else
    COMMAND=$1
    EXITONFAIL="y"
fi

if [ ${DEBUG} = '1' ] ; then
    echo ${DEBUG_PREFIX}
    echo ${DEBUG_PREFIX} COMMAND: ${COMMAND}
fi

if [ ${DEBUG} = '1' ] ; then
    echo ${DEBUG_PREFIX}
    echo ${DEBUG_PREFIX} usage output: begin
    usage
    echo ${DEBUG_PREFIX} usage output: end
    #echo ${DEBUG_PREFIX}
fi

# part 2 - distribution check
# ---------------------------

AUSER=nobody
AGROUP=nobody
VIRTUALS=""
USER=`whoami`

check_distribution() {
if [ -f /etc/debian_version ]; then
    AUSER=www-data
    AGROUP=www-data
elif [ -f /etc/redhat-release ]; then
    AUSER=apache
    AGROUP=apache
elif [ -f /etc/gentoo-release ]; then
    AUSER=apache
    AGROUP=apache
elif [ -f /etc/SuSE-release ]; then
    AUSER=wwwrun
    AGROUP=wwwrun
else
    UNAME=`uname | cut -c 1-6`
    if [ "$UNAME" = "CYGWIN" ]; then
        AUSER=SYSTEM
        AGROUP=SYSTEM
    elif [ "$UNAME" = "Darwin" ]; then
        AUSER=_www
        AGROUP=_www
    elif [ "$UNAME" = "FreeBS" ]; then
        AUSER=www
        AGROUP=www
    fi
fi
}

check_webroot() {
    AUSER=`stat -c "%U" .`
    AGROUP=`stat -c "%G" .`
}

if [ -z "${OPT_GUESS_USER_GROUP_FROM_ROOT}" ]; then
    check_distribution
else
    check_webroot
fi

# part 3 - default and writable subdirs
# -------------------------------------

DIR_LIST_DEFAULT="admin db doc dump files img installer lang lib modules permissioncheck storage temp templates tests themes tiki_tests vendor vendor_extra whelp"
DIR_LIST_WRITABLE="db dump img/wiki img/wiki_up img/trackers storage storage/public storage/fgal temp temp/cache temp/public temp/templates_c templates themes whelp mods files tiki_tests/tests temp/unified-index vendor"
DIRS=${DIR_LIST_WRITABLE}

# part 4 - several functions
# --------------------------

# part 4.1 - several functions as permission settings for different usecases

dec2oct() {
    #DEC_IN=85
    #
    #
    #
    R8=$(( ${DEC_IN} % 8 ))
    O1=${R8}
    IN=$(( ${DEC_IN} - ${R8} ))
    #
    #echo foo ${IN}
    #
    DEC_IN=${IN}
    R64=$(( ${DEC_IN} % 64 ))
    O2=$(( ${R64} / 8 ))
    IN=$(( ${DEC_IN} - ${R64} ))
    #
    #echo bar ${IN}
    #
    DEC_IN=${IN}
    R512=$(( ${DEC_IN} % 512 ))
    O3=$(( ${R512} / 64 ))
    #
    #echo ${R512} ${R64} ${R8}
    #
    OCT_OUT=${O3}${O2}${O1}
}

dec2oct_test() {
    DEC_IN=$(( 0500 | 0220 ))
    dec2oct
    echo ${OCT_OUT}
    echo break
    exit 1
}
#dec2oct_test

debug_breakpoint() {
    echo
    echo "debug breakpoint"
    exit 1

}

# debug exit
debug_exit() {
if [ ${DEBUG} = '1' ] ; then
    echo
    echo "Exiting... for execution mode use option '-d off' or set DEBUG=0 at the beginning of this script"
    echo
    exit 1
fi
}

get_permission_data() {
    if [ ${DEBUG} = '1' ] ; then
        echo ${DEBUG_PREFIX}
        echo ${DEBUG_PREFIX} permissioncheck subdir: ${PERMISSIONCHECK_DIR}
    fi
    if [ -d ${USE_CASES_PATH} ] ; then
        if [ -f ${USE_CASES_NAME} ] ; then
            NO_MATCH=999
            MODEL_NAME=${NO_MATCH}
            MODEL_PERMS_SUBDIRS=${NO_MATCH}
            MODEL_PERMS_FILES=${NO_MATCH}
            while read ONE_USE_CASE_PER_LINE ; do
                USE_CASE=`echo ${ONE_USE_CASE_PER_LINE} | cut -d: -f1`
                if [ ${USE_CASE} = ${COMMAND} ] ; then
                    MODEL_NAME=${USE_CASE}
                    MODEL_PERMS_SUBDIRS=`echo ${ONE_USE_CASE_PER_LINE} | cut -d: -f2`
                    MODEL_PERMS_FILES=`echo ${ONE_USE_CASE_PER_LINE} | cut -d: -f3`
                    MODEL_PERMS_WRITE_SUBDIRS=`echo ${ONE_USE_CASE_PER_LINE} | cut -d: -f4`
                    MODEL_PERMS_WRITE_FILES=`echo ${ONE_USE_CASE_PER_LINE} | cut -d: -f5`
                    if [ ${DEBUG} = '1' ] ; then
                        echo ${DEBUG_PREFIX}
                        echo ${DEBUG_PREFIX} MODEL_NAME=${MODEL_NAME}
                        echo ${DEBUG_PREFIX} MODEL_PERMS_SUBDIRS=${MODEL_PERMS_SUBDIRS}
                        echo ${DEBUG_PREFIX} MODEL_PERMS_FILES=${MODEL_PERMS_FILES}
                        echo ${DEBUG_PREFIX} MODEL_PERMS_WRITE_SUBDIRS=${MODEL_PERMS_WRITE_SUBDIRS}
                        echo ${DEBUG_PREFIX} MODEL_PERMS_WRITE_FILES=${MODEL_PERMS_WRITE_FILES}
                    fi
                fi
            done < ${USE_CASES_NAME}
            if [ ${MODEL_NAME} = ${NO_MATCH} ] ; then
                    echo no matching use case found
                    exit 1
            fi
        else
            echo ${USE_CASES_NAME} does not exist
            exit 1
        fi
    else
        echo ${USE_CASES_PATH} does not exist
        exit 1
    fi
}

set_permission_dirs_special_write() {
    # function must be defined before set_permission_data
    for WRITABLE in $DIRS ; do
        if [ -d ${WRITABLE} ] ; then
            if [ ${DEBUG} = '1' ] ; then
                echo ${DEBUG_PREFIX}
                echo ${DEBUG_PREFIX} "${FIND} ${WRITABLE} -type d -exec ${CHMOD} ${MODEL_PERMS_WRITE_SUBDIRS} {} \;"
                echo ${DEBUG_PREFIX} "${FIND} ${WRITABLE} -type f -exec ${CHMOD} ${MODEL_PERMS_WRITE_FILES} {} \;"
            fi
            ${FIND} ${WRITABLE} -type d -exec ${CHMOD} ${MODEL_PERMS_WRITE_SUBDIRS} {} \;
            ${FIND} ${WRITABLE} -type f -exec ${CHMOD} ${MODEL_PERMS_WRITE_FILES} {} \;
        fi
    done
}

set_permission_data() {
    if [ ${DEBUG} = '1' ] ; then
        echo ${DEBUG_PREFIX} 'for PHP_FILES in "./*.php" ; do'
        echo ${DEBUG_PREFIX} "    ${CHMOD} ${MODEL_PERMS_FILES}" '${PHP_FILES}'
        echo ${DEBUG_PREFIX} "done"
        echo ${DEBUG_PREFIX} "${CHMOD} ${MODEL_PERMS_SUBDIRS} ."
    fi
    for PHP_FILES in "./*.php" ; do
        ${CHMOD} ${MODEL_PERMS_FILES} ${PHP_FILES}
    done
    ${CHMOD} ${MODEL_PERMS_SUBDIRS} .
    for DEFAULT_DIR in ${DIR_LIST_DEFAULT} ; do
        if [ ${DEBUG} = '1' ] ; then
            echo ${DEBUG_PREFIX}
            echo ${DEBUG_PREFIX} "${FIND} ${DEFAULT_DIR} -type d -exec ${CHMOD} ${MODEL_PERMS_SUBDIRS} {} \;"
            echo ${DEBUG_PREFIX} "${FIND} ${DEFAULT_DIR} -type f -exec ${CHMOD} ${MODEL_PERMS_FILES} {} \;"
        fi
        #debug_breakpoint
        ${FIND} ${DEFAULT_DIR} -type d -exec ${CHMOD} ${MODEL_PERMS_SUBDIRS} {} \;
        ${FIND} ${DEFAULT_DIR} -type f -exec ${CHMOD} ${MODEL_PERMS_FILES} {} \;
        #set_permission_dirs_special_write
    done
    for WRITABLE in $DIRS ; do
        if [ -d ${WRITABLE} ] ; then
            if [ ${DEBUG} = '1' ] ; then
                echo ${DEBUG_PREFIX}
                echo ${DEBUG_PREFIX} "${FIND} ${WRITABLE} -type d -exec ${CHMOD} ${MODEL_PERMS_WRITE_SUBDIRS} {} \;"
                echo ${DEBUG_PREFIX} "${FIND} ${WRITABLE} -type f -exec ${CHMOD} ${MODEL_PERMS_WRITE_FILES} {} \;"
            fi
            ${FIND} ${WRITABLE} -type d -exec ${CHMOD} ${MODEL_PERMS_WRITE_SUBDIRS} {} \;
            ${FIND} ${WRITABLE} -type f -exec ${CHMOD} ${MODEL_PERMS_WRITE_FILES} {} \;
        fi
    done
}

permission_via_php_check() {
    # model was chosen by Tiki Permission Check (TPC)
    get_permission_data
    # set permissions
#    if [ ${DEBUG} = '2' ] ; then
#        echo
#        ${FIND} . -type d -exec echo ${CHMOD} ${MODEL_PERMS_SUBDIRS} {} \;
#        ${FIND} . -type f -exec echo ${CHMOD} ${MODEL_PERMS_FILES} {} \;
#    fi
    set_permission_data
}

set_permission_data_workaround_general() {
    for DEFAULT_DIR in ${DIR_LIST_DEFAULT} ; do
        # this is quick 'n dirty
        ${CHMOD} -R o+r ${DEFAULT_DIR}/
        ${FIND} ${DEFAULT_DIR} -name "*.php" -exec ${CHMOD} o-r {} \;
        ${FIND} ${DEFAULT_DIR} -type d -exec ${CHMOD} o-r {} \;
    done
}

set_permission_data_workaround_sbox() {
    # 500 might not work with .css and images, not yet observed
    #
    # first: classic sbox
    COMMAND="sbox"
    permission_via_php_check
    #
    # second: fix permissions of none-PHP files , really quick 'n dirty
    set_permission_data_workaround_general
    #
    # reset $COMMAND , not really necessary
    COMMAND="sboxworkaround"
}

set_permission_data_workaround_suphp() {
    # 600/601 does not work with .css and images, as observed on Debian Wheezy
    #
    # first: classic paranoia-suphp
    COMMAND="paranoia-suphp"
    permission_via_php_check
    #
    # second: fix permissions of none-PHP files , really quick 'n dirty
    set_permission_data_workaround_general
    #
    # reset $COMMAND , not really necessary
    COMMAND="suphpworkaround"
}

yet_unused_permission_default() {
    ${CHMOD} -fR u=rwX,go=rX .
}

yet_unused_permission_exceptions() {
    ${CHMOD} o-rwx db/local.php
    ${CHMOD} o-rwx db/preconfiguration.php
}

# part 4.2 - composer

# Set-up and execute composer to obtain dependencies
exists()
{
    if type $1 &>/dev/null
    then
        return 0
    else
        return 1
    fi
}

composer_core()
{
    if [ -f temp/composer.phar ];
    then
        # todo : if exists php;
        if [ ${LOGCOMPOSERFLAG} = "0" -o ${LOGCOMPOSERFLAG} = "2" ] ; then
            "${PHPCLI}" temp/composer.phar self-update --2 ${OPT_QUIET}
            RETURNVAL=$?
        fi
        if [ ${LOGCOMPOSERFLAG} = "1" ] ; then
            "${PHPCLI}" temp/composer.phar self-update --2 ${OPT_QUIET} > ${TIKI_COMPOSER_SELF_UPDATE_LOG}
            RETURNVAL=$?
        fi
        if [ ${RETURNVAL} -eq 0 ];
        then
            NEED_NEW_COMPOSER="0"
        else
            echo "Composer self-update failed. Reinstalling composer"
            NEED_NEW_COMPOSER="1"
            rm temp/composer.phar
        fi
        # remove previous container.php in case of incompatibility
        rm -f temp/cache/container.php
    else
        NEED_NEW_COMPOSER="1"
    fi

    if [ ${NEED_NEW_COMPOSER} = "1" ];
    then
        if exists curl;
        then
            curl -s https://getcomposer.org/installer | "${PHPCLI}" -- --install-dir=temp --2
        else
            echo "CURL command not found. Trying to obtain the composer executable using PHP."
            # todo : if exists php;
            "${PHPCLI}" -r "eval('?>'.file_get_contents('https://getcomposer.org/installer'));" -- --install-dir=temp --2
        fi
        # if PATCHCOMPOSERFLAG then modify temp/composer.phar to avoid the warnings
        # this hack is not yet possible because of a self signature check in temp/composer.phar
    fi

    if [ ! -f temp/composer.phar ];
    then
        echo "We have failed to obtain the composer executable."
        echo "NB: Maybe you are behind a proxy, just export https_proxy variable and relaunch setup.sh"
        echo "1) Download it from http://getcomposer.org"
        echo "2) Store it in temp/"
        if [ ${EXITONFAIL} = "y" ]; then
            exit 1
        else
            return
        fi
    fi

    N=0
    # todo : move "if exists php;" to function composer

    # check if we are in development mode so we can prevent uninstalling of development files
    DEVELOPMENT="--no-dev"
        if [ -d vendor_bundled/vendor/phpunit ]; then
            DEVELOPMENT=""
        fi

    if exists php;
    then
        if [ ${LOGCOMPOSERFLAG} = "0" ] ; then
            #until php -dmemory_limit=-1 temp/composer.phar install --working-dir vendor_bundled --prefer-dist --no-dev
            until "${PHPCLI}" -dmemory_limit=-1 temp/composer.phar install --working-dir vendor_bundled --prefer-dist --optimize-autoloader --no-interaction ${DEVELOPMENT} 2>&1 | sed '/Warning: Ambiguous class resolution/d'
            # setting memory_limit here prevents suhosin ALERT - script tried to increase memory_limit to 536870912 bytes
            do
                if [ $N -eq 7 ];
                then
                    if [ ${EXITONFAIL} = "y" ]; then
                        exit 1
                    else
                        return
                    fi
                else
                    echo "Composer failed, retrying in 5 seconds, for a few times. Hit Ctrl-C to cancel."
                    sleep 5
                fi
                N=$((N+1))
            done
        fi
        if [ ${LOGCOMPOSERFLAG} = "1" ] ; then
            until "${PHPCLI}" -dmemory_limit=-1 temp/composer.phar install --working-dir vendor_bundled --prefer-dist --optimize-autoloader --no-interaction ${DEVELOPMENT} > ${TIKI_COMPOSER_INSTALL_LOG}
            # setting memory_limit here prevents suhosin ALERT - script tried to increase memory_limit to 536870912 bytes
            do
                if [ $N -eq 7 ];
                then
                    if [ ${EXITONFAIL} = "y" ]; then
                        exit 1
                    else
                        return
                    fi
                else
                    echo "Composer failed, retrying in 5 seconds, for a few times. Hit Ctrl-C to cancel."
                    sleep 5
                fi
                N=$((N+1))
            done
        fi
        if [ ${LOGCOMPOSERFLAG} = "2" ] ; then
            echo "Suppress output lines with 'Warning: Ambiguous class resolution'\n..."
            #until php -dmemory_limit=-1 temp/composer.phar install --working-dir vendor_bundled --prefer-dist --no-dev | sed '/Warning: Ambiguous class resolution/d'
            until "${PHPCLI}" -dmemory_limit=-1 temp/composer.phar install --working-dir vendor_bundled --prefer-dist --optimize-autoloader --no-interaction ${DEVELOPMENT}
            # setting memory_limit here prevents suhosin ALERT - script tried to increase memory_limit to 536870912 bytes
            do
                if [ $N -eq 7 ];
                then
                    if [ ${EXITONFAIL} = "y" 