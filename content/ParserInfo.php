<?php

require_once("content/FormatFactory.php");
require_once("content/defines.php");
require_once("content/SpecialString.php");

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
  public $specialChars;             // every characters not in here will be skipped 
                                    // and considered text even before processing (see NParserImpl::Parse)
  public $specialStrings;           // for multi-character shortcuts
  private $data = array();          // custom data inserted by the formats, use GetFormatData and SetFormatData to access
  public $produceSource = FALSE;    // this a pointer to a symbol. A symbol that requires something to be Produce()d,
                                    // must set this variable to himself and then reset it to the previous value afterwards
  
  // INPUT
  public $language = NLanguages::LANGUAGE_DEFAULT;
  public $cElement = FALSE;
  public $content  = "";

  public function __construct()
    {
    $this->specialStrings = new TSpecialStringTree();
    $this->specialChars = CHAR_OPEN_SQUARE.CHAR_OPEN_ANGLED;
    }

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

    $this->AddIfShortcutSymbol($name); // add to the special strings if it's a shortcut

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

  // if $name is a shortcut symbol, it will be added to the special strings, otherwise nothing happens
  public function AddIfShortcutSymbol($name)
    {
    if (!isset($name[PREFIX_SHORTCUT_TOTAL_LENGTH]))
      return; // too short: not a shortcut

    $scprefix = strtoupper(substr($name,0,PREFIX_SHORTCUT_TOTAL_LENGTH));
    switch ($scprefix)
      {
      case PREFIX_TOGGLE_SHORTCUT:
      case PREFIX_PULSE_SHORTCUT:
      case PREFIX_BEGIN_SHORTCUT:
      case PREFIX_END_SHORTCUT:
        break;
      default:
        return; // not a valid shortcut prefix
      }

    $sc = substr($name,PREFIX_SHORTCUT_TOTAL_LENGTH);
    if ($sc === FALSE || $sc === "")
      return; // shortcut string is empty
    
    $this->AddSpecialString($sc,$scprefix);
    }

  // shortcuts
  public function AddSpecialString($sc,$type)
    {
    if ($sc === "")
      return;

    // add first char to specialchars
    if (strpos($this->specialChars,$sc[0]) === FALSE) // avoid duplicates
      $this->specialChars .= $sc[0];

    $data = array(0 => $sc,1 => $type);
    
    $this->specialStrings->Add($sc,$data);
    }

  // FORMAT DATA ACCESS
  // store data in key "key"
  // NULL not allowed
  public function SetFormatData($key,$value)
    {
    if ($value === NULL)
      return;

    $this->data[$key] = $value;
    }

  // remove data
  public function UnSetFormatData($key)
    {
    if (isset($this->data[$key]))
      unset($this->data[$key]);
    }

  // check data existence
  public function IsFormatData($key)
    {
    return isset($this->data[$key]);
    }

  // retrieve data, FALSE if failed
  public function GetFormatData($key)
    {
    if (isset($this->data[$key]))
      return $this->data[$key];

    return FALSE;
    }
  }

?>
