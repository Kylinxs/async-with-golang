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
USE_CASES_PATH=${PER