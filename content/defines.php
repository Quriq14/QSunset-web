<?php

abstract class NActionParameters
  {
  const BEGIN  = "BEGIN";   // begin effect span of the symbol
  const END    = "END";     // end effect span of the symbol
  const TOGGLE = "TOGGLE";  // toggle area of effect (BEGIN if not active, END if active)
  const PULSE  = "PULSE";   // send a pulse to the symbol (useful for displaying text) DEFAULT
  const DECL   = "DECL";    // declaration only (no effect, but defines the symbol)

  const DEF    = self::PULSE; // default action

  // returns TRUE if $string is an action name
  public static function Is($string)
    {
    switch ($string)
      {
      case self::END:
      case self::BEGIN:
      case self::TOGGLE:
      case self::PULSE:
      case self::DECL:
        return TRUE;
      default:
        return FALSE;
      }
    }
  }  

define("PARAMETER_BOLD","BOLD");
define("PARAMETER_ITALIC","ITALIC");
define("PARAMETER_UNDERLINE","UNDERLINE");

define("PARAMETER_LINEBREAK","LINEBREAK");
define("PARAMETER_HIDDEN","HIDDEN");
define("PARAMETER_COMMENT","COMMENT");
define("PARAMETER_LANGUAGE","LANG");         // show if language equals lang1 or lang2 or... : lang=lang1=lang2=...
define("PARAMETER_WRITE_CHARS","WRITECHAR"); // writechar=ab writes "ab" when his PULSE event is called

define("PARAMETER_REF","REF");               // reference to other page on same site
define("PARAMETER_RELATIVE_REF","RREF");     // same as REF but if attribute is:
                                             //   :PATH absolute path
                                             //   +PATH append to current path
                                             //   . means "current path"
                                             //   -PATH go to parent, remove the minus sign and evaluate PATH again
                                             // EXAMPLES: . (current path), -. (parent), +|EX (section EX if current is a directory),
                                             //           -+|EX (go to parent and enter section EX)
define("PARAMETER_FAR_REF","XREF");          // reference to other site

define("PARAMETER_HTML","RAWHTML");          // raw HTML inside [HT rawhtml=-end-]<i>raw</i>-end-
define("PARAMETER_SNIPPET","SNIPPET");       // parts of (formatted) text within BEGIN-END of a snippet can be written with
                                             // the PULSE of the same snippet. A snippet requires an unique name: snippet=name
                                             // example: [LOREM snippet=lorem BEGIN]Lorem ipsum[LOREM END] [LOREM] [LOREM]
                                             //          will print "Lorem ipsum" two times.
define("PARAMETER_HORIZONTAL_LINE","HORIZONTALLINE");

define("PARAMETER_LIST","LIST");
define("PARAMETER_LISTITEM","LISTITEM");     // [LISTITEM] starts a new item in current active list
define("PARAMETER_ULISTCLASS","ULISTTYPE");  // appearance of ordered lists
define("PARAMETER_OLISTCLASS","OLISTTYPE");  // appearance of unordered lists

define("PARAMETER_IMAGE","IMG");             // [IMG=src="name"=label="label"] local image
define("PARAMETER_IMAGE_FAR","XIMG");        // [XIMG=src="url"=label="label"] image from a remote URL

define("PARAMETER_JUMP","JUMP");             // [JUMP=if=condition=to=label BEGIN] jumps to label if the condition is true
define("PARAMETER_DISPLAYIF","DISPLAYIF");   // [DISPLAYIF=condition BEGIN] the content is hidden if the condition is not met
define("PARAMETER_TERMINATEIF","TERMINATEIF"); // [TERMINATEIF=condition] terminates processing of current file if contition is true

define("PARAMETER_DATETIME","DATETIME");     // [DATETIME=source=format] writes the date "source", formatted with "format"
                                             // Available dates: created (file creation), lastedit

define("PARAMETER_BOX","BOX");

define("PARAMETER_TEXTSIZE","TEXTSIZE");     // [TEXTSIZE=npixels BEGIN]

define("PARAMETER_TABLE","TABLE");
define("PARAMETER_TABLE_ROW","ROW");         // [ROW=n BEGIN] sets current row. [ROW] increments current row. [ROW=n] resets to n
define("PARAMETER_TABLE_COLUMN","COLUMN");   // like row, but for columns

define("PARAMETER_ENABLE","ENABLE");
define("PARAMETER_DISABLE","DISABLE");

define("PARAMETER_CODE","CODE");             // [CODE=lang="none"=inline/box=until="\n" BEGIN] \n

define("PREFIX_SHORTCUT_LENGTH",3);
define("PREFIX_SHORTCUT","SC:");

define("CHAR_OPEN_SQUARE","[");     // [tag]
define("CHAR_CLOSE_SQUARE","]");
define("CHAR_OPEN_ANGLED","<");     // <angled>
define("CHAR_CLOSE_ANGLED",">");
define("CHAR_SPECIAL_DEFAULT",CHAR_OPEN_SQUARE.CHAR_OPEN_ANGLED);

define("INCLUDE_PART_SEPARATOR","::"); // <path::part>

?>
