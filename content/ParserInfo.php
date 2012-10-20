<?php

require_once("content/FormatFactory.php");
require_once("content/defines.php");
require_once("content/SpecialString.php");
require_once("content/FormatAttribs.php");
require_once("content/FormatStack.php");
require_once("content/Shortcut.php");

// this interface must be implemented by every class that can be used as parameter in PushProduceRedirect
interface IProduceRedirect
  {
  // $info is the info that is adding the producer
  // $producer is the producer that is being added
  // if TRUE is returned, the next registered ProduceRedirect will be called
  public function OnAddedProducer($info,$producer);
  }

// send this info to the parser, its output variables will be changed
class TContentParserInfo implements IProduceRedirect
  {
  // OUTPUT
  public $processed = 0;          // number of characters processed
  public $result = "";            // resulting HTML

  // STATUS (internal use only)
  public $symbols = array();        // defined symbols: name => TSymbol
  private $activeSymbols = array(); // an array of stacks of active symbols: name => TFormatStack
  private $topActiveSymbols = array(); // always equal to activeSymbols[..]->Top(): name => TParamFormatAttribs
  public $resultChain = array();    // array of objects, $result = cat($resultChain->Pulse())
  public $producedObjects = 0;      // length of the resultChain

  private $produceRedirectStack;    // array 0..n-1 => object
                                    // every time AddToResultChain(obj) is used, 
                                    // the method OnAddedProducer of the top object is called
  private $produceRedirectTop;      // index of the top of $produceRedirectStack
  private $produceRedirectNames;    // string => id_in_$produceRedirectStack

  private $specialChars;            // every characters not in here will be skipped 
                                    // and considered text even before processing (see NParserImpl::Parse)
  private $specialStrings;          // a TSpecialStringTree for multi-character shortcuts

  private $enabledSymbols = array();// name => TRUE / FALSE. Symbols not in this set are enabled by default.

  private $data = array();          // custom data inserted by the formats, use GetFormatData and SetFormatData to access
  private $endOfParsingRequest = 0;

  public $content = "";             // raw content
  
  // INPUT
  public $language = NLanguages::LANGUAGE_DEFAULT;
  public $cElementStack = array(); // at position 0, the current TElementData
                                   // <includes> will push new elements
  public $cPartStack    = array(); // part of the element (see NElementParts), for every entry in $cElementStack

  public $cacheKey = FALSE;       // FALSE is "no cache", otherwise a string that will be used as cache key

  public function __construct($cElement,$part,$language = FALSE,$cacheKey = FALSE)
    {
    $this->specialStrings = new TSpecialStringTree();
    $this->specialChars = CHAR_SPECIAL_DEFAULT;

    $this->produceRedirectStack = array(0 => $this);
    $this->produceRedirectTop = 0;
    $this->produceRedirectNames = array();

    if ($language !== FALSE)
      $this->language = $language;
    $this->PushCurrentElement($cElement,$part);
    $this->cacheKey = $cacheKey;
    }

  // returns a TFormatStatus or FALSE if not existing
  public function GetFormatByName($name)
    {
    if ($name === "")
      return FALSE;

    $stateless = NFormatFactory::GetByName($name); // see if a default stateless format status exists
    if ($stateless !== FALSE)
      return $stateless;

    if (isset($this->symbols[$name]))
      return $this->symbols[$name]; // a symbol was defined with this name

    return FALSE;
    }

  // returns a TFormatStatus or FALSE if invalid name
  public function GetOrCreateFormatByName($name)
    {
    if ($name === "")
      return FALSE;

    $maybeExists = $this->GetFormatByName($name);
    if ($maybeExists !== FALSE)
      return $maybeExists;

    // if not existing, create it
    $maybeshortcut = self::GetShortcutPart($name);
    if ($maybeshortcut !== FALSE) // is a shortcut
      $this->symbols[$name] = new TShortcut($name,$maybeshortcut);
      else
        $this->symbols[$name] = new TSymbol($name);

    $this->EnableSymbol($name); // enable all custom symbols upon creation

    return $this->symbols[$name];
    }

  // returns TRUE if a symbol named $name exists, FALSE otherwise
  public function IsSymbol($name)
    {
    if (isset($this->symbols[$name]))
      return TRUE;

    $defaultformats = NFormatFactory::GetNameSet();
    if (isset($defaultformats[$name]))
      return TRUE;

    return FALSE;
    }

  // SYMBOL ACTIVATION and DEACTIVATION
  public function ActivateSymbol($name,$topname,$data)
    {
    if ($name === "" || $topname === "")
      return;

    if (!isset($this->activeSymbols[$name]))
      $this->activeSymbols[$name] = new TFormatStack();

    $this->activeSymbols[$name]->Push($topname,$data);
    $this->topActiveSymbols[$name] = $data;
    }

  public function DeActivateSymbol($name,$topname)
    {
    if (!isset($this->activeSymbols[$name]))
      return;

    $this->activeSymbols[$name]->Remove($topname);

    // update the top symbol if any, or remove its entry
    if (($top = $this->activeSymbols[$name]->Top()) !== FALSE)
      $this->topActiveSymbols[$name] = $top;
      else
        unset($this->topActiveSymbols[$name]);
    }

  // FALSE if failed
  public function GetActiveSymbol($name,$topname)
    {
    if (!isset($this->activeSymbols[$name]))
      return FALSE;

    return $this->activeSymbols[$name]->Find($topname);
    }

  // FALSE if failed
  public function GetTopActiveSymbol($name)
    {
    if (!isset($this->topActiveSymbols[$name]))
      return FALSE;

    return $this->topActiveSymbols[$name];
    }

  public function IsSymbolActive($name,$topname)
    {
    return $this->GetActiveSymbol($name,$topname) !== FALSE;
    }

  public function IsAnySymbolActive($name)
    {
    return $this->GetTopActiveSymbol($name) !== FALSE;
    }

  // returns an array of TParamFormatAttribs, ordered by name
  public function GetActiveSymbolList()
    {
    return $this->topActiveSymbols;
    }

  // MANAGE PRODUCERS
  // default listener
  public function OnAddedProducer($info,$obj)
    {
    $this->resultChain[$this->producedObjects++] = $obj;
    return FALSE;
    }

  // this will be called to add a producer to the output
  public function AddToResultChain($obj)
    {
    $i = $this->produceRedirectTop;
    while ((!isset($this->produceRedirectStack[$i]) || // if !isset simply skip
      $this->produceRedirectStack[$i]->OnAddedProducer($this,$obj)) && $i > 0)
      $i--;
    }

  // $obj is something implementing IProduceRedirect
  public function PushProduceRedirect($name,$obj)
    {
    if (isset($this->produceRedirectNames[$name]))
      return; // avoid duplicates

    $this->produceRedirectStack[++$this->produceRedirectTop] = $obj;
    $this->produceRedirectNames[$name] = $this->produceRedirectTop;
    }

  public function RemoveProduceRedirect($name)
    {
    if (!isset($this->produceRedirectNames[$name]))
      return; // not found

    unset($this->produceRedirectStack[$this->produceRedirectNames[$name]]);
    unset($this->produceRedirectNames[$name]);

    while (!isset($this->produceRedirectStack[$this->produceRedirectTop]))
      $this->produceRedirectTop--; // find the next valid index
    }

  // MANAGE SYMBOL ENABLE/DISABLE

  // enables a symbol
  public function EnableSymbol($name)
    {
    if (!$this->IsSymbol($name))
      return;

    if (!isset($this->enabledSymbols[$name]))
      return; // already enabled by default

    $this->enabledSymbols[$name] = TRUE;
    }

  public function DisableSymbol($name)
    {
    if (!$this->IsSymbol($name))
      return;

    $this->enabledSymbols[$name] = FALSE;
    }

  public function IsSymbolEnabled($name)
    {
    // a symbol is enabled if it's not set in the array or it's set to TRUE
    return !isset($this->enabledSymbols[$name]) || ($this->enabledSymbols[$name]);
    }

  // disables all enabled symbols that are not keys in $nameset
  public function DisableAllSymbolsExcept($nameset = array())
    {
    $defaultformats = NFormatFactory::GetNameSet();

    // disable formats
    foreach ($defaultformats as $k => $useless)
      if (!isset($nameset[$k]) && $this->IsSymbolEnabled($k))
        $this->DisableSymbol($k);

    // disable custom symbols
    foreach ($this->symbols as $k => $useless)
      if (!isset($nameset[$k]) && $this->IsSymbolEnabled($k))
        $this->DisableSymbol($k);
    }

  // enables all the disabled symbols that are not keys in $nameset
  public function EnableAllSymbolsExcept($nameset = array())
    {
    $defaultformats = NFormatFactory::GetNameSet();

    // enable formats
    foreach ($defaultformats as $k => $useless)
      if (!isset($nameset[$k]) && !$this->IsSymbolEnabled($k))
        $this->EnableSymbol($k);

    // enable custom symbols
    foreach ($this->symbols as $k => $useless)
      if (!isset($nameset[$k]) && !$this->IsSymbolEnabled($k))
        $this->EnableSymbol($k);
    }

  // MANAGE SHORTCUTS
  static private function GetShortcutPart($name)
    {
    if (!isset($name[PREFIX_SHORTCUT_LENGTH]))
      return FALSE; // too short

    $scprefix = strtoupper(substr($name,0,PREFIX_SHORTCUT_LENGTH));
    if ($scprefix !== PREFIX_SHORTCUT)
      return FALSE;

    $sc = substr($name,PREFIX_SHORTCUT_LENGTH);
    if ($sc === FALSE || $sc === "")
      return FALSE; // shortcut string is empty

    return $sc;
    }

  public function UpdateShortcutStatus($name)
    {
    $sc = self::GetShortcutPart($name);
    if ($sc === FALSE)
      return; // not a shortcut or error

    if ($this->IsAnySymbolActive($name))
      {
      $data = array(0 => $sc,1 => $name);
      $this->specialStrings->Add($sc,$data);
      }
      else
        $this->specialStrings->Remove($sc);

    // update the specialChars
    $this->specialChars = CHAR_SPECIAL_DEFAULT.implode($this->specialStrings->GetFirstLettersArray());
    }

  public function GetSpecialChars()
    {
    return $this->specialChars;
    }

  public function FindSpecialString($startfrom,$content)
    {
    return $this->specialStrings->Find($startfrom,$content);
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

  // retrieve data, the value of $def if undefined
  public function GetFormatData($key,$def = FALSE)
    {
    if (isset($this->data[$key]))
      return $this->data[$key];

    $this->data[$key] = $def;
    return $def;
    }

  // CURRENT ELEMENT STACK
  // to be used by the include system
  // $elem is a TElementData
  public function PushCurrentElement($elem,$part = NElementParts::DEFAULT_PART)
    {
    $pos = count($this->cElementStack);
    $this->cElementStack[$pos] = $elem;
    $this->cPartStack[$pos] = $part;
    }

  public function PopCurrentElement()
    {
    $count = count($this->cElementStack);
    if ($count > 0)
      {
      unset($this->cElementStack[--$count]);
      unset($this->cPartStack[$count]);
      }
    }

  // FALSE if empty
  // returns the element on top of the stack
  public function TopCurrentElement()
    {
    $count = count($this->cElementStack);
    if ($count === 0)
      return FALSE;

    return $this->cElementStack[$count - 1];
    }

  // FALSE if empty
  // returns the element at the bottom of the stack
  public function BaseCurrentElement()
    {
    if (!isset($this->cElementStack[0]))
      return FALSE;
    return $this->cElementStack[0];
    }

  public function TopCurrentPart()
    {
    $count = count($this->cElementStack);
    if ($count === 0)
      return FALSE;

    return $this->cPartStack[$count - 1];
    }

  // PARSING END REQUESTS
  // asks the current Parse invocation to end
  // (useful for child processing)
  public function RequestEndOfParsing()
    {
    if ($this->endOfParsingRequest !== FALSE)
      $this->endOfParsingRequest++; // multiple requests will quit many invocations
    }

  public function IsEndOfParsingRequested()
    {
    return ($this->endOfParsingRequest !== 0);
    }

  public function ClearEndOfParsingRequest()
    {
    if ($this->endOfParsingRequest !== FALSE && $this->endOfParsingRequest > 0)
      $this->endOfParsingRequest--; 
    }

  // Aborts processing. All invocations of the Parser will exit.
  public function AbortRequest()
    {
    $this->endOfParsingRequest = FALSE; // if $endOfParsingRequest is FALSE, ALL invocations will be ended
                                        // and ClearEndOfParsingRequest won't have any effect
    }
  }

?>
