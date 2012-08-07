<?php

class NParseError
  {
  // error severity
  const NOTICE  = 0; // just a notice
  const WARNING = 1; // unexpected, but executed anyway
  const ERROR   = 2; // the operation couldn't be executed
  const FATAL   = 3; // the error was so big that the parser can't continue

  // error ids
  const CIRCULAR_DEFINITION   = 0; // [A B][B A] this makes a symbol parent of itself
  const INCLUDE_NOT_CLOSED    = 1; // < without corresponding >
  const LISTITEM_OUTSIDE_LIST = 2; 

  private static $typestrings = array(
    self::NOTICE  => "Notice: ",
    self::WARNING => "Warning: ",
    self::ERROR   => "Error: ",
    self::FATAL   => "Fatal: ",
    );

  private static $errorstrings = array(
    self::CIRCULAR_DEFINITION   => "Cannot define symbol \"#1\" as symbol \"#0\": circular definition.",
    self::INCLUDE_NOT_CLOSED    => "Include not closed.",
    self::LISTITEM_OUTSIDE_LIST => "LISTITEM used outside LIST.",
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
      if ($info->cElement !== FALSE)
        $err .= "Element: ".$info->cElement->GetAddress()." ";

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
    $tobereplaced = array(); // build an array containing #0, #1, #2 etc
    foreach ($parameters as $k => $useless)
      $tobereplaced[$k] = "#".((string)$k);

    $errorwithparams = str_replace($tobereplaced,$parameters,self::$errorstrings[$errorid]); 
      // replace all #1 #2 with the parameters

    $err .= $errorwithparams;

    error_log($err); // TODO: send the error somewhere else
                     // TODO: add a loglevel of some sort
    }
  }

?>
