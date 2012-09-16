<?php

class NParseError
  {
  // error severity
  const NOTICE  = 0; // just a notice
  const WARNING = 1; // unexpected, but executed anyway
  const ERROR   = 2; // the operation couldn't be executed
  const FATAL   = 3; // the error was so big that the parser can't continue

  // error ids
  const CIRCULAR_DEFINITION      = 0; // [A B][B A] this makes a symbol parent of itself
  const INCLUDE_NOT_CLOSED       = 1; // < without corresponding >
  const LISTITEM_OUTSIDE_LIST    = 2; 
  const UNKNOWN_LIST_CLASS       = 3; // LISTTYPE command with unknown class specified
  const CIRCULAR_SNIPPET         = 4; // a snippet is called by itself
  const UNNAMED_SNIPPET          = 5; // snippet name not defined
  const INCLUDE_DEPTH_EXCEEDED   = 6; // probably recursive include, sanity check
  const RREF_CELEMENT_NOT_SET    = 7; // TContentParserInfo does not provide a current element, so TRelativeRefFormat failed
  const REF_PARAM_NOT_SPECIFIED  = 8;
  const RREF_INVALID_SINTAX      = 9;
  const REF_ELEM_NOT_FOUND       = 10; // referenced element not found
  const INCLUDE_NOT_FOUND        = 11;
  const CONDITION_EXPECTED       = 12;
  const CONDITION_UNKNOWN        = 13;
  const CONDITION_NOPARAM        = 14; // a condition required a parameter
  const CONDITION_WRONG_TYPE     = 15; // a statement returned a result of the wrong type
  const JUMP_STH_EXPECTED        = 16;
  const DATETIME_INVALID_FORMAT  = 17;
  const DATETIME_UNKNOWN_SOURCE  = 18;
  const BOX_UNKNOWN_ATTRIB       = 19;
  const TEXTSIZE_NOPARAM         = 20;
  const INCLUDE_UNKNOWN_PART     = 21;

  private static $typestrings = array(
    self::NOTICE  => "Notice: ",
    self::WARNING => "Warning: ",
    self::ERROR   => "Error: ",
    self::FATAL   => "Fatal: ",
    );

  private static $errorstrings = array(
    self::CIRCULAR_DEFINITION      => "Cannot define symbol \"#1#\" as symbol \"#0#\": circular definition.",
    self::INCLUDE_NOT_CLOSED       => "Include not closed.",
    self::LISTITEM_OUTSIDE_LIST    => "LISTITEM used outside LIST.",
    self::UNKNOWN_LIST_CLASS       => "Unknown list class: \"#0#\".",
    self::CIRCULAR_SNIPPET         => "Cannot produce snippet #0#: circular call. Full stack: #1#.",
    self::UNNAMED_SNIPPET          => "Unnamed snippet.",
    self::INCLUDE_DEPTH_EXCEEDED   => "Include maximum depth (#1#) exceeded (Included: \"#0#\")",
    self::RREF_CELEMENT_NOT_SET    => "Couldn't generate relative reference #0#: current element not set here.",
    self::REF_PARAM_NOT_SPECIFIED  => "Reference parameter not specified.",
    self::RREF_INVALID_SINTAX      => "Invalid sintax for relative reference: \"#0#\".",
    self::REF_ELEM_NOT_FOUND       => "Referenced element not found: \"#0#\".",
    self::INCLUDE_NOT_FOUND        => "Included element not found: \"#0#\".",
    self::CONDITION_EXPECTED       => "Condition expected or unfinished condition.",
    self::CONDITION_UNKNOWN        => "Unknown operator in condition: \"#0#\".",
    self::CONDITION_NOPARAM        => "Parameter not specified for condition \"#0#\", \"#1#\" returned.",
    self::CONDITION_WRONG_TYPE     => "A statement of type \"#0#\" is expected for condition \"#1#\".",
    self::JUMP_STH_EXPECTED        => "Jump: #0# expected.",
    self::DATETIME_INVALID_FORMAT  => "Could not parse the format string for DATETIME: \"#0#\".",
    self::DATETIME_UNKNOWN_SOURCE  => "Unknown or undefined date for DATETIME: \"#0#\".",
    self::BOX_UNKNOWN_ATTRIB       => "Box: unknown attribute: \"#0#\"",
    self::TEXTSIZE_NOPARAM         => "Parameter not found for textsize.",
    self::INCLUDE_UNKNOWN_PART     => "Include: Unknown part: #0#",
    );

  // sends an error
  // set $info to FALSE if not provided
  static function Error($info,$severity,$errorid,$parameters)
    {
    if (!isset(self::$typestrings[$severity]) || !isset(self::$errorstrings[$errorid]))
      {
      error_log("NParseError: Error: unknown severity or id.");
      return;
      }

    $err = "";

    $linenumber = FALSE;
    if ($info !== FALSE)
      {
      // ELEMENT NAME
      if ($info->TopCurrentElement() !== FALSE)
        {
        $err .= "Element: ".$info->TopCurrentElement()->GetAddress()." ";
      
        // report include stack
        $elementnestcount = count($info->cElementStack) - 1;
        if ($elementnestcount > 0)
          {
          $err .= "Included by: \"";
          for ($i = 0; $i < $elementnestcount; $i++)
            $err .= ($i === 0 ? "" : "->").$info->cElementStack[$i]->GetAddress();
          $err .= "\" ";
          }
        }

      // LINE NUMBER
      $linenumber = substr_count($info->content,"\n",0,$info->processed); 
        // count the number of lines processed
        // this is inefficient, but errors should be very rare
      if ($linenumber === FALSE) // error
        $linenumber = 0;
      $linenumber++;
      $err .= "At line: ".((string)$linenumber)." ";
      }

    // ERROR SEVERITY
    $err .= self::$typestrings[$severity];

    // ERROR PARAMETERS
    $tobereplaced = array(); // build an array containing #0#, #1#, #2# etc
    foreach ($parameters as $k => $useless)
      $tobereplaced[$k] = "#".((string)$k)."#";

    $errorwithparams = str_replace($tobereplaced,$parameters,self::$errorstrings[$errorid]); 
      // replace all #1# #2#... with the parameters

    $err .= $errorwithparams;

    error_log($err); // TODO: send the error somewhere else
                     // TODO: add a loglevel of some sort
    }
  }

?>
