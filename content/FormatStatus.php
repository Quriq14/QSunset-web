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

  // child processing is needed?
  public function NeedChildProc($info,$attribs) {return FALSE; }

  // start child processing
  public function ChildProc($info,$attribs) {}

  // add a subsymbol to the symbol (useful only for Symbols)
  // $symbolattr is an object of type TFormatAttribs
  public function AddSubSymbol($symbolattr)
    {
    }

  // a producer is created within BEGIN-END of this symbol
  public function AddedProducer($info,$producer,$attribs)
    {
    }
  }

?>
