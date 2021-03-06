<?php
// use ONLY require/include_once for this file

define("PATH_FIELD","path"); // GET request field
define("PATH_SEP","|");      // separator between directory and section
define("DIR_PATH_SEP","/");  // separator for directories
define("LANGUAGE_FIELD","pl"); // language preferred by user
define("PATH_ROOT","");      // path of the root directory

define("CHAR_INTERNAL_LINEBREAK","\n"); // this character will be used internally to subdivide a string into lines
                                        // it has no relation with the current encoding (CRLF, LF, etc)

define("SYSTEM_PATH_SEP","/");                          // separator for folders (from the operating system)
define("SYSTEM_PATH_ROOT",".".SYSTEM_PATH_SEP."root");  // ROOT folder: directories outside this should NEVER be accessed
define("SYSTEM_FILE_ROOT",SYSTEM_PATH_ROOT.SYSTEM_PATH_SEP."index.txt"); // FILE in which there is the root folder data
define("SYSTEM_CACHE_ROOT",".".SYSTEM_PATH_SEP."cache");

define("MAX_PATH_DEPTH",20); // max depth of directory tree

define("IMAGE_ROOT","img/");

define("DEFAULT_HEADER_TITLE_CONTENT","<INCLUDE".PATH_SEP."DEFAULTHEADER>");
define("DEFAULT_FOOTER_CONTENT","<INCLUDE".PATH_SEP."DEFAULTFOOTER>");
define("DEFAULT_READABILITY_ERROR_CONTENT","<ERRORS".DIR_PATH_SEP."VISIBILITY".PATH_SEP."HIDDEN>");
define("DEFAULT_LANG_NOT_FOUND_ERROR_CONTENT","<ERRORS".DIR_PATH_SEP."LANGUAGE".PATH_SEP."NOTFOUND>");
define("DEFAULT_LANG_NOT_AVAIL_ERROR_CONTENT","<ERRORS".DIR_PATH_SEP."LANGUAGE".PATH_SEP."NOTAVAIL>");

define("ERROR_404_PATH","ERRORS".DIR_PATH_SEP."HTTP".PATH_SEP."404"); // logical path of the 404 error
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
