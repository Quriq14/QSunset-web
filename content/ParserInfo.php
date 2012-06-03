<?php

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
  }

?>
