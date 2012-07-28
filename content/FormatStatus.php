<?php

// all implemented TFormatStatus are STATELESS except for symbols
// add all the TFormatStatuses to the FormatFactory (except symbols, obviously)
abstract class TFormatStatus
  {
  // returns a string that will be added to result when applied (opening tag)
  abstract public function Apply($info,$content,$attribs);
  
  // returns a string that will be added to result when unapplied (closing tag)
  abstract public function UnApply($info,$content,$attribs);

  // if FALSE, the contained text will be hidden (tags will still be shown)
  abstract public function IsVisible($info,$content,$attribs);

  // returns a string that will be added to the result when called as PULSE
  abstract public function Pulse($info,$attribs);

  // set this to TRUE if special processing is needed when the effect of the symbol begins
  // this will cause the call of ChildProc instead of the activation of the symbol
  public function NeedChildProc($info,$attribs) {return FALSE; }

  // start special processing.
  // $origsymbattr is the TFormatAttribs of the top-level symbol which triggered this ChildProc
  public function ChildProc($info,$attribs,$origsymbattr) {}

  // add a subsymbol to the symbol (useful only for Symbols)
  // $symbolattr is an object of type TFormatAttribs
  public function AddSubSymbol($symbolattr)
    {
    }

  // EVENTS
  // a producer is created within BEGIN-END of this symbol
  public function OnAddedProducer($info,$producer,$attribs)
    {
    }

  // this is called JUST AFTER the symbol is added to the active symbols
  public function OnBegin($info,$attribs)
    {
    }

  // this is called JUST BEFORE the symbol is removed from the active symbols
  public function OnEnd($info,$attribs)
    {
    }
  }

?>
