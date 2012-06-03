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
  public function NeedChild($info,$attribs) {return FALSE; }

  // start child processing
  public function Child($info,$attribs) {}

  // add a subsymbol to the symbol (useful only for Symbols)
  // a subsymbol may be the name of any TFormatStatus
  public function AddSubSymbol($name,$value)
    {
    error_log("TFormatStatus::AddSubSymbol called.");
    }
  }

?>
