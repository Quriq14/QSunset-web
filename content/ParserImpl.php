<?php

require_once("content/defines.php");
require_once("content/Symbol.php");
require_once("content/ParserInfo.php");
require_once("content/Text.php");
require_once("content/Include.php");
require_once("content/CommandParser.php");
require_once("content/ParseError.php");
require_once("content/PulseProducer.php");

require_once("html/htmlutils.php");

class NParserImpl
  {
  // creates a TTextHolder object and appends it to $info->resultChain
  public static function ProduceText($info,$text)
    {
    if ($text === "")
      return;

    $tf = new TTextHolder($text);
    $tf->ActiveSymbolsFromInfo($info);
    $info->AddToResultChain($tf);
    }

  // returns an array if success (a chainDOM)
  public static function Parse($info)
    {
    if ($info->IsEndOfParsingRequested())
      {
      $info->ClearEndOfParsingRequest();
      return array();
      }

    $contentlength = strlen($info->content);
    if ($info->processed >= $contentlength || $info->processed < 0)
      return array(); // starting from invalid index

    $buffer = "";

    for ($info->processed; $info->processed < $contentlength; $info->processed)
      {
      // skip characters (and put to the buffer) until a special character is found
      $nextSpecialChar = NCommandParser::FindFirstOf($info->content,$info->processed,$info->specialChars);
      if ($nextSpecialChar >= $contentlength) // no more special chars found
        {
        $ma = substr($info->content,$info->processed);
        if ($ma !== FALSE)
          $buffer .= $ma;
        $info->processed = $contentlength; // this will cause the for to exit
        continue; 
        }

      if ($nextSpecialChar > $info->processed) // if $nextSpecialChar = $info->processed, no characters were skipped (speedup test)
        {
        $buffer .= substr($info->content,$info->processed,$nextSpecialChar - $info->processed);
        $info->processed = $nextSpecialChar;
        }

      switch ($info->content[$info->processed])// are we going inside some bracket?
        {
        case CHAR_OPEN_SQUARE: // going inside squares: beginning command
          self::ProduceText($info,$buffer); // flush the buffer and clear it
          $buffer = "";
          $info->processed++;
          self::ParseCommand($info);  // parse and execute the command
          break;
        case CHAR_OPEN_ANGLED:
          self::ProduceText($info,$buffer); // flush the buffer and clear it
          $buffer = "";
          $info->processed++;
          self::ParseInclude($info);
          break;
        default:
          // it's a common character. What action will be taken?
          $specialFinding = $info->specialStrings->Find($info->processed,$info->content);
          $actionParam = FALSE;
          if ($specialFinding !== FALSE) // a special string is found
            {
            switch ($specialFinding[1])  // find the action parameter from the shortcut prefix
              {
              case PREFIX_TOGGLE_SHORTCUT:
                $actionParam = PARAMETER_TOGGLE;
                break;
              case PREFIX_PULSE_SHORTCUT:
                $actionParam = PARAMETER_PULSE;
                break;
              case PREFIX_BEGIN_SHORTCUT:
                $actionParam = PARAMETER_BEGIN;
                break;
              case PREFIX_END_SHORTCUT:
                $actionParam = PARAMETER_END;
                break;
              }
            }
              
          if ($actionParam !== FALSE) // the action exists
            {
            self::ProduceText($info,$buffer);          // flush the buffer and clear it (a command is being executed)
            $buffer = "";
            $info->processed += strlen($specialFinding[0]);
            self::ExecuteSymbol(array(0 => array(0 => $specialFinding[1].$specialFinding[0]),1 => array(0 => $actionParam)),$info);
              // simulate simple command
            }
            else
              $buffer .= $info->content[$info->processed++]; // it was a special character, but no action triggered. 
                                                             // Output it to the buffer.
          break;
        }

      if ($info->IsEndOfParsingRequested())
        {
        $info->ClearEndOfParsingRequest();
        break;
        }
      }

    self::ProduceText($info,$buffer);                   // flush the buffer

    return $info->resultChain;
    }

  public static function ParseCommand($info)
    {
    if ($info->processed >= strlen($info->content))
      return; // end of file reached

    $splitCommand = NCommandParser::Parse($info->content,$info->processed);

    self::ExecuteSymbol($splitCommand,$info);
    }

  // returns a string to be added to the buffer (because of a PULSE command) or an empty string
  public static function ExecuteSymbol($paramArray,$info)
    {
    if (!is_array($paramArray) || count($paramArray) === 0 || !isset($paramArray[0][0]))
      return;

    $symbolName = $paramArray[0][0];
    if ($symbolName === "")
      return; // symbol name is empty

    $symbolName = strtoupper($symbolName); // symbol name is case-insensitive

    $symbol = $info->GetOrCreateFormatByName($symbolName);
    $symbolattr = new TFormatAttribs($symbolName,$paramArray[0],$symbol);

    $paramCount = count($paramArray);

    $lastParam = "";

    // if the last parameter is an action, save it and remove it from the parameter array
    if ($paramCount > 1) // do it only if it's not the symbol name
      switch (strtoupper($paramArray[$paramCount-1][0]))
        {
        case PARAMETER_END:
        case PARAMETER_BEGIN:
        case PARAMETER_TOGGLE:
        case PARAMETER_PULSE:
        case PARAMETER_DECL:
          $lastParam = $paramArray[$paramCount-1][0];
          unset($paramArray[$paramCount-1]);
          $paramCount--;
          break;
        }

    for ($i = 1; $i < $paramCount; $i++)
      {
      $subsymbolattr = new TFormatAttribs(strtoupper($paramArray[$i][0]),$paramArray[$i]);
      if (count($subsymbolattr->GetSubSymbolsWithName($info,$symbolName)) === 0)   // prevent circular nesting
        $symbolattr->AddSubSymbol($subsymbolattr);
        else
          NParseError::Error($info,NParseError::ERROR,NParseError::CIRCULAR_DEFINITION,
            array(0 => $subsymbolattr->GetName(),1 => $symbolName));
      }

    // execute the command depending on the command type
    switch (strtoupper($lastParam))
      {
      case PARAMETER_END:
        if ($info->IsSymbolActive($symbolName,$symbolName))
          $symbolattr->OnEnd($info,$symbolName);
        break;
      case PARAMETER_TOGGLE:
        if ($info->IsSymbolActive($symbolName,$symbolName)) // if the symbol is active, deactivate it
          {
          $symbolattr->OnEnd($info,$symbolName);
          break;
          } // else, continue execution into BEGIN
      case PARAMETER_BEGIN:
        if ($symbolattr->NeedChildProc($info,array(),$symbolattr)) // needs child processing?
          $symbolattr->ChildProc($info,array(),$symbolattr);
          else
            if (!$info->IsSymbolActive($symbolName,$symbolName))
              $symbolattr->OnBegin($info,array(),$symbolattr);
        break;
      case PARAMETER_DECL:
        // nothing to do
        break;
      case PARAMETER_PULSE:
      default: // the pulse is the default
        $symbolattr->OnPulse($info,array(),$symbolattr);
        break;
      }
    }

  public static function ParseInclude($info)
    {
    $includeendidx = strpos($info->content,CHAR_CLOSE_ANGLED,$info->processed);
    
    if ($includeendidx === FALSE)
      {
      NParseError::Error($info,NParseError::FATAL,NParseError::INCLUDE_NOT_CLOSED,array());
      $info->processed = strlen($info->content);
      $info->AbortRequest();
      return;
      }

    $include = substr($info->content,$info->processed,$includeendidx - $info->processed); // get the path
    $include = trim($include);

    $info->processed = $includeendidx + 1;

    $part = FALSE;
    $includepartidx = strpos($include,INCLUDE_PART_SEPARATOR);
    if ($includepartidx !== FALSE) // the part is defined, split the string
      {
      $part = substr($include,$includepartidx + strlen(INCLUDE_PART_SEPARATOR));
      if ($part === FALSE)
        $part = "";
      $include = substr($include,0,$includepartidx);
      if ($include === FALSE)
        $include = "";
      }

    if ($part === FALSE)
      NInclude::DoInclude($info,$include);
      else NInclude::DoInclude($info,$include,$part);
    }
  }
?>
