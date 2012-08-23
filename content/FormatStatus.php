<?php

require_once("content/PulseProducer.php");
require_once("content/FormatAttribs.php");

// all implemented TFormatStatus are STATELESS except for symbols
// add all the TFormatStatuses to the FormatFactory (except symbols, obviously)
// parameters:
//   $info: the current TContentParserInfo
//   $content: the content to which the format will be applied
//   $attribs: an array int => string. At 0 is the format name, attributes start at 1.
//   $topsymbattr: the TFormatAttribs of the top-level symbol which triggered the function call
//     i.e. the command parsed from the source file
//   $topsymbname: the name of the top symbol
abstract class TFormatStatus
  {
  // returns a string that will be added to result when applied (opening tag)
  abstract public function Apply($info,$content,$attribs);
  
  // returns a string that will be added to result when unapplied (closing tag)
  abstract public function UnApply($info,$content,$attribs);

  // if FALSE, the content will be hidden
  abstract public function IsVisible($info,$content,$attribs);

  // returns a string that will be added to the result when called as PULSE
  abstract public function Pulse($info,$attribs);

  abstract public function GetName();

  // set this to TRUE if special processing is needed when the effect of the symbol begins
  // this will cause the call of ChildProc instead of the activation of the symbol
  public function NeedChildProc($info,$attribs,$topsymbattr) {return FALSE; }

  // start special processing.
  public function ChildProc($info,$attribs,$topsymbattr) {}

  // add a subsymbol to the symbol (useful only for Symbols)
  // $symbolattr is an object of type TFormatAttribs
  public function AddSubSymbol($symbolattr)
    {
    }

  // this is called when the symbol must be added to the active symbols
  public function OnBegin($info,$attribs,$topsymbattr)
    {
    $info->ActivateSymbol($this->GetName(),$topsymbattr->GetName(),new TParamFormatAttribs($this,$attribs,$topsymbattr));
    }

  // this is called when the symbol must be removed from the active symbols
  public function OnEnd($info,$topsymbname)
    {
    $info->DeActivateSymbol($this->GetName(),$topsymbname);
    }

  public function OnPulse($info,$attribs,$topsymbattr)
    {
    $prod = new TPulseProducer(new TParamFormatAttribs($this,$attribs,$topsymbattr));
    $prod->ActiveSymbolsFromInfo($info);
    $info->AddToResultChain($prod);
    }

  // returns an array of TFormatAttribs (empty if none)
  public function GetSubSymbols()
    {
    return array();
    }
  }

?>
