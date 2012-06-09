<?php

define("PARAMETER_BEGIN","BEGIN");   // begin area of effect of the symbol
define("PARAMETER_END","END");       // close area of effect of the symbol
define("PARAMETER_TOGGLE","TOGGLE"); // toggle area of effect
define("PARAMETER_PULSE","PULSE");   // send a pulse to the symbol (useful for displaying text) DEFAULT
define("PARAMETER_DECL","DECL");     // declaration only (no effect, but defines the symbol)

define("PARAMETER_SEPARATOR"," ");
define("PARAMETER_VALUE_BEGIN","="); // the equal in "parameter=value" (value can't contain PARAMETER_SEPARATOR)
                                     // multiple values may be added: parameter=value1=value2=value3=...

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

define("PREFIX_SHORTCUT","SC");
define("PREFIX_SHORTCUT_TOTAL_LENGTH",4); // number of character of the prefixes below (to drop the prefix faster)
define("PREFIX_TOGGLE_SHORTCUT",PREFIX_SHORTCUT."T:"); // [SCT:/ TOGGLE] is equivalent to /, [SCT:| TOGGLE] is equivalent to |
                                      // you may use [SCT:symbol params] to define new shortcuts. They always trigger the action TOGGLE.
define("PREFIX_PULSE_SHORTCUT", PREFIX_SHORTCUT."P:"); // like the previous, for action PULSE.
define("PREFIX_BEGIN_SHORTCUT", PREFIX_SHORTCUT."B:"); // action BEGIN
define("PREFIX_END_SHORTCUT",   PREFIX_SHORTCUT."E:"); // action END

define("CHAR_OPEN_SQUARE","[");     // [tag]
define("CHAR_CLOSE_SQUARE","]");
define("CHAR_OPEN_ANGLED","<");     // <angled> (TODO)
define("CHAR_CLOSE_ANGLED",">");

?>
