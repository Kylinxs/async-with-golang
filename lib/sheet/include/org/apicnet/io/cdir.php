<?php

//this script may only be included - so its better to die if called directly.
if (strpos($_SERVER["SCRIPT_NAME"],basename(__FILE__)) !== false) {
  header("location: index.php");
  exit;
}

//
// $Archive: /iPage/V1.1/include/dir.php $
// $Date: 2005-05-18 11:01:38 $
// $Revision: 1.3 $
//
// $History: dir.php $
// 
// 
// *****************  Version 5  *****************
// User: @PICNet      Date: 18.03.04   Time: 14:12
// User: Hannesd      Date: 28.11.00   Time: 14:12
// Updated in $/iPage/V1.1/include
//

if ( !defined( "INCLUCDED_DIR" ) ) {
    define( "INCLUCDED_DIR", TRUE  );

/**
 * CDir
* Class for reading a directory structure.
* aFiles contains multiple aFile entries
* aFile:   Path        => relative path eg. ../xx/yy/
*          File        => filename eg. filename (without extension)
*          Extension   => ext
*          IsDirectory => true/false
*          FullName    => Path . File . "." . Extension
*          FileName    => File . "