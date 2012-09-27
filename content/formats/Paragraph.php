<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");

class TLineBreakFormat extends TFormatStatus
  {
  public function TLineBreakFormat()
    {
    }

  public function Apply($info,$content,$status)
    {
    return "";
    }
   
  public function UnApply($info,$content,$status)
    {
    return "";
    }

  public function IsVisible($info,$content,$status)
    {
    return TRUE;
    }

  public function Pulse($info,$status)
    {
    return "<br />\r\n";
    }

  public function GetName()
    {
    return PARAMETER_LINEBREAK;
    }
  }

NFormatFactory::Register(new TLineBreakFormat());

?>
