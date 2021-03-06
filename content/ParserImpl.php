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
      $nextSpecialChar = NCommandParser::FindFirstOf($info->content,$info->processed,$info->GetSpecialChars());
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
          // it's a common character. Is it a shortcut?
          $specialFinding = $info->FindSpecialString($info->processed,$info->content);
          $scsymbol = FALSE;
          if ($specialFinding !== FALSE) // a special string is found
            {
            $scsymbolname = $specialFinding[1]; // the full shortcut symbol name
            if ($info->IsSymbolEnabled($scsymbolname))
              $scsymbol = $info->GetTopActiveSymbol($scsymbolname);
            }
              
          if ($scsymbol !== FALSE) // the symbol exists
            {
            self::ProduceText($info,$buffer); // flush the buffer and clear it (a command is being executed)
            $buffer = "";
            $info->processed += strlen($specialFinding[0]); // in [0] is the shortcut string
            if (($sccommand = $scsymbol->ShortcutPulse($info)) !== FALSE)
              self::ExecuteSymbol($sccommand,$info);
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
    if (count($paramArray) === 0 || !isset($paramArray[0][0]))
      return;

    $symbolName = $paramArray[0][0];
    if ($symbolName === "")
      return; // symbol name is empty

    $symbolName = strtoupper($symbolName); // symbol name is case-insensitive

    if (NActionParameters::Is($symbolName))
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::SYMBOL_RESERVED,array(0 => $symbolName));
      return; // can't create symbols with same name of actions
      }

    $symbol = $info->GetOrCreateFormatByName($symbolName);
    if (!$info->IsSymbolEnabled($symbolName))
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::SYMBOL_DISABLED,array(0 => $symbolName));
      return; // disabled
      }

    $symbolattr = new TFormatAttribs($symbolName,$paramArray[0],$symbol);

    $paramCount = count($paramArray);

    $actionParam = NActionParameters::DEF; // default action

    for ($i = 1; $i < $paramCount; $i++)
      {
      $subsymbolname = strtoupper($paramArray[$i][0]);

      if (NActionParameters::Is($subsymbolname))
        {
        $actionParam = $subsymbolname;
        continue; // is an action
        }

      if (!$info->IsSymbolEnabled($subsymbolname))
        {
        NParseError::Error($info,NParseError::ERROR,NParseError::SYMBOL_DISABLED,array(0 => $subsymbolname));
        continue;
        }

      $subsymbolattr = new TFormatAttribs($subsymbolname,$paramArray[$i]);
      if (count($subsymbolattr->GetSubSymbolsWithName($info,$symbolName)) === 0)   // prevent circular nesting
        $symbolattr->AddSubSymbol($subsymbolattr);
        else
          NParseError::Error($info,NParseError::ERROR,NParseError::CIRCULAR_DEFINITION,
            array(0 => $subsymbolattr->GetName(),1 => $symbolName));
      }

    // execute the command depending on the command type
    switch ($actionParam)
      {
      case NActionParameters::END:
        if ($info->IsSymbolActive($symbolName,$symbolName))
          $symbolattr->OnEnd($info,$symbolName);
        break;
      case NActionParameters::TOGGLE:
        if ($info->IsSymbolActive($symbolName,$symbolName)) // if the symbol is active, deactivate it
          {
          $symbolattr->OnEnd($info,$symbolName);
          break;
          } // else, continue execution into BEGIN
      case NActionParameters::BEGIN:
        if ($symbolattr->NeedChildProc($info,array(),$symbolattr)) // needs child processing?
          $symbolattr->ChildProc($info,array(),$symbolattr);
          else
            if (!$info->IsSymbolActive($symbolName,$symbolName))
              $symbolattr->OnBegin($info,array(),$symbolattr);
        break;
      case NActionParameters::DECL:
        // nothing to do
        break;
      case NActionParameters::PULSE:
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

    NInclude::DoInclude($info,$include,$part);
    }
  }
?>
