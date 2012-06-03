<?php

require_once("content/FormatStatus.php");

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
  }

?>
