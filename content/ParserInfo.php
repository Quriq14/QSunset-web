<?php

require_once("content/FormatFactory.php");

// send this info to the parser, its output variables will be changed
class TContentParserInfo
  {
  // OUTPUT
  public $processed = 0;          // number of characters processed
  public $result = "";            // resulting HTML

  // STATUS (internal use only)
  public $symbols = array();        // defined symbols: name => TSymbol
  public $activeSymbols = array();  // a set of symbol names
  public $resultChain = array();    // array of objects, $result = cat($resultChain->Pulse())
  public $producedObjects = 0;      // length of the resultChain
  
  // INPUT
  public $language = NLanguages::LANGUAGE_DEFAULT;
  public $cElement = FALSE;
  public $content  = "";

  // returns a TFormatStatus or FALSE if not existing
  public function GetFormatByName($name)
    {
    if (!isset($name) || $name === "")
      return FALSE;

    $stateless = FormatFactory($name); // see if a default stateless format status exists
    if ($stateless !== FALSE)
      return $stateless;

    if (isset($this->symbols[$name]))
      return $this->symbols[$name]; // a symbol was defined with this name

    return FALSE;
    }

  // returns a TFormatStatus or FALSE if invalid name
  public function GetOrCreateFormatByName($name)
    {
    if (!isset($name) || $name === "")
      return FALSE;

    $maybeExists = $this->GetFormatByName($name);
    if ($maybeExists !== FALSE)
      return $maybeExists;

    $this->symbols[$name] = new TSymbol($name); // if not existing, create it
    return $this->symbols[$name];
    }

  public function ActivateSymbol($name)
    {
    if (!is_string($name) || $name === "")
      return;

    $this->activeSymbols[$name] = TRUE;
    }

  public function DeActivateSymbol($name)
    {
    if (isset($this->activeSymbols[$name]))
      unset($this->activeSymbols[$name]);
    }

  public function IsSymbolActive($name)
    {
    return isset($this->activeSymbols[$name]);
    }

  // returns an array of TFormatStatus, ordered from 0 to n
  public function GetActiveSymbolList()
    {
    $result = array();
    $resultidx = 0;

    foreach($this->activeSymbols as $k => $useless)
      if (($symb = $this->GetFormatByName($k)) !== FALSE)
        $result[$resultidx++] = $symb;

    return $result;
    }

  public function AddToResultChain($obj)
    {
    $this->resultChain[$this->producedObjects++] = $obj;
    }
  }

?>
