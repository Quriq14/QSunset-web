<?php

require_once("content/defines.php");
require_once("content/Symbol.php");
require_once("content/ParserInfo.php");
require_once("content/Text.php");
require_once("content/Include.php");

require_once("html/htmlutils.php");

class NParserImpl
  {
  // same as "explode", but removes empty parameters
  public static function ExplodeParams($string)
    {
    if (!isset($string))
      return array();

    $exploded = explode(PARAMETER_SEPARATOR,$string);
    
    // remove empty params
    $necount = 0;
    $noempty = array();
    foreach ($exploded as $t)
      if ($t !== "")
        $noempty[$necount++] = $t;
        
    return $noempty;
    }

  // returns an array with the name in position 0 and the values in position 1..n
  public static function ExplodeSingleParam($string)
    {
    if (!isset($string))
      return array();

    return explode(PARAMETER_VALUE_BEGIN,$string);
    }

  // creates a TTextHolder object and appends it to $info->resultChain
  public static function ProduceText($info,$text)
    {
    if ($text === "")
      return;

    $tf = new TTextHolder($text);
    
    foreach($info->activeSymbols as $k => $useless)
      if (($symb = $info->GetFormatByName($k)) !== FALSE)
        $tf->AddSymbol($symb);
    
    $info->resultChain[$info->producedObjects++] = $tf;
    }

  public static function ProducePulse($info,$symbol)
    {
    $tf = new TSymbolHolder($symbol);
    
    foreach($info->activeSymbols as $k => $useless)
      if (($symb = $info->GetFormatByName($k)) !== FALSE)
        $tf->AddSymbol($symb);
    
    $info->resultChain[$info->producedObjects++] = $tf;
    }

  // returns an array if success (a chainDOM)
  public static function Parse($info)
    {
    $contentlength = strlen($info->content);
    if ($info->processed >= $contentlength || $info->processed < 0)
      return array(); // starting from invalid index

    $buffer = "";

    for ($info->processed; $info->processed < $contentlength; $info->processed)
      {
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
          // it's a common character. See if it's a TOGGLE SHORTCUT:
          $strangechar = $info->content[$info->processed];
          if (isset($info->symbols[PREFIX_TOGGLE_SHORTCUT.$strangechar]))
            {
            self::ProduceText($info,$buffer);          // flush the buffer and clear it (like a command)
            $buffer = "";
            $info->processed++;
            self::ExecuteSymbol(array(0 => PREFIX_TOGGLE_SHORTCUT.$strangechar,1 => PARAMETER_TOGGLE),$info);
              // simple TOGGLE command
            break;
            }

          // is it a PULSE SHORTCUT?
          if (isset($info->symbols[PREFIX_PULSE_SHORTCUT.$strangechar]))
            {
            self::ProduceText($info,$buffer);          // flush the buffer and clear it (like a command)
            $buffer = "";
            $info->processed++;
            self::ExecuteSymbol(array(0 => PREFIX_PULSE_SHORTCUT.$strangechar,1 => PARAMETER_PULSE),$info);
              // simple PULSE command
            break;
            }

          // is it a BEGIN SHORTCUT?
          if (isset($info->symbols[PREFIX_BEGIN_SHORTCUT.$strangechar]))
            {
            self::ProduceText($info,$buffer);          // flush the buffer and clear it (like a command)
            $buffer = "";
            $info->processed++;
            self::ExecuteSymbol(array(0 => PREFIX_BEGIN_SHORTCUT.$strangechar,1 => PARAMETER_BEGIN),$info);
              // BEGIN command
            break;
            }

          // is it an END SHORTCUT?
          if (isset($info->symbols[PREFIX_END_SHORTCUT.$strangechar]))
            {
            self::ProduceText($info,$buffer);          // flush the buffer and clear it (like a command)
            $buffer = "";
            $info->processed++;
            self::ExecuteSymbol(array(0 => PREFIX_END_SHORTCUT.$strangechar,1 => PARAMETER_END),$info);
              // END command
            break;
            }

          $buffer .= $info->content[$info->processed++]; // else add to buffer              
          break;
        }
      }

    self::ProduceText($info,$buffer);                   // flush the buffer

    return $info->resultChain;
    }

  public static function ParseCommand($info)
    {
    if ($info->content === "")
      return;

    $commandendidx = strpos($info->content,CHAR_CLOSE_SQUARE,$info->processed);
    
    if ($commandendidx === FALSE)
      {
      error_log("END OF COMMAND not found.");
      $info->processed = strlen($info->content);
      return; // command not closed
      }

    $command = substr($info->content,$info->processed,$commandendidx - $info->processed); // get the command
    $command = trim($command);

    $splitCommand = self::ExplodeParams($command);

    $info->processed = $commandendidx + 1; // continue from the end of the command

    if (count($splitCommand) !== 0)
      self::ExecuteSymbol($splitCommand,$info);
    }

  // returns a string to be added to the buffer (because of a PULSE command) or an empty string
  public static function ExecuteSymbol($paramArray,$info)
    {
    if (!isset($paramArray) || count($paramArray) === 0)
      return;

    $paramArray[0] = strtoupper($paramArray[0]); // symbol name is case-insensitive

    $symbol = $info->GetOrCreateFormatByName($paramArray[0]);

    $paramCount = count($paramArray);

    $lastParam = "";

    // if the last parameter is an action, save it and remove it from the parameter array
    if ($paramCount > 1) // do it only if it's not the command name
      switch (strtoupper($paramArray[$paramCount-1]))
        {
        case PARAMETER_END:
        case PARAMETER_BEGIN:
        case PARAMETER_TOGGLE:
        case PARAMETER_PULSE:
        case PARAMETER_DECL:
          $lastParam = $paramArray[$paramCount-1];
          unset($paramArray[$paramCount-1]);
          $paramCount--;
          break;
        }

    for ($i = 1; $i < $paramCount; $i++)
      {
      $values = self::ExplodeSingleParam($paramArray[$i]);

      $symbol->AddSubSymbol($values[0],$values);
      }

    // execute the command depending on the command type
    switch (strtoupper($lastParam))
      {
      case PARAMETER_END:
        if (isset($info->activeSymbols[$paramArray[0]]))
          unset($info->activeSymbols[$paramArray[0]]);
        break;
      case PARAMETER_BEGIN:
        // is this a script?
        if ($symbol->NeedChild($info,array()))
          $symbol->Child($info,array());
          else 
            $info->activeSymbols[$paramArray[0]] = TRUE;
        break;
      case PARAMETER_TOGGLE:
        if (isset($info->activeSymbols[$paramArray[0]]))
          unset($info->activeSymbols[$paramArray[0]]);
          else
            $info->activeSymbols[$paramArray[0]] = TRUE;
        break;
      case PARAMETER_DECL:
        // nothing to do
        break;
      case PARAMETER_PULSE:
      default: // the pulse is the default
        self::ProducePulse($info,$symbol); // add to the result chain
        break;
      }
    }

  public function ParseInclude($info)
    {
    $includeendidx = strpos($info->content,CHAR_CLOSE_ANGLED,$info->processed);
    
    if ($includeendidx === FALSE)
      {
      error_log("END OF INCLUDE not found.");
      $info->processed = strlen($info->content);
      return;
      }

    $include = substr($info->content,$info->processed,$includeendidx - $info->processed); // get the command
    $include = trim($include);

    $info->processed = $includeendidx + 1;

    NInclude::DoInclude($info,$include);
    }
  }
?>
