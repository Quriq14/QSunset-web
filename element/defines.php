<?php
// use ONLY require/include_once for this file

define("PATH_FIELD","path"); // GET request field
define("PATH_SEP","|");      // separator between directory and section
define("DIR_PATH_SEP","/");  // separator for directories
define("LANGUAGE_FIELD","pl"); // language preferred by user
define("PATH_ROOT","");      // path of the root directory

define("SYSTEM_PATH_SEP","/");                          // separator for folders (from the operating system)
define("SYSTEM_PATH_ROOT",".".SYSTEM_PATH_SEP."root");  // ROOT folder: directories outside this should NEVER be accessed
define("SYSTEM_FILE_ROOT",SYSTEM_PATH_ROOT.SYSTEM_PATH_SEP."index.txt"); // FILE in which there is the root folder data

define("MAX_PATH_DEPTH",20); // max depth of directory tree

define("USE_APC",FALSE);
define("ELEMENT_CACHE_PREFIX","E");
define("INDEX_CACHE_PREFIX","I");

define("IMAGE_ROOT","img/");

define("FILE_CONTENT_SEPARATOR","----------------------------------------"); // trailing characters on same line will be ignored
define("FILE_MAX_LINE_LENGTH",1024);

define("ERROR_404_PATH","ERRORS".DIR_PATH_SEP."HTTP".PATH_SEP."404"); // logical path of the 404 error
define("DEFAULT_HEADER_TITLE_CONTENT","<INCLUDE".PATH_SEP."DEFAULTHEADER>");
define("DEFAULT_READABILITY_ERROR_CONTENT","<ERRORS".DIR_PATH_SEP."VISIBILITY".PATH_SEP."HIDDEN>");
define("MAX_CONSECUTIVE_REDIRECT",10);

class NLanguages
  {
  const LANGUAGE_IT = "it";
  const LANGUAGE_EN = "en";
  const LANGUAGE_FR = "fr";
  const LANGUAGE_DEFAULT = self::LANGUAGE_EN;

  static $LANGUAGE_ARRAY = array(0 => self::LANGUAGE_IT,1 => self::LANGUAGE_EN,2 => self::LANGUAGE_FR); // this should be CONST
  }

?>
