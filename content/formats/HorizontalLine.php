<?php

require_once("content/FormatStatus.php");
require_once("html/htmlutils.php");
require_once("content/defines.php");

// writes to the output the characters passed in the first attribute
class THorizontalLineFormat extends TFormatStatus
  {
  public function __construct()
    {
    }

  public function Apply($info,$content,$attribs)
    {
    return "";
    }
   
  public function UnApply($info,$content,$attribs)
    {
    return "";
    }

  public function IsVisible($info,$content,$attribs)
    {
    return TRUE;
    }

  public function Pulse($info,$attribs)
    {
    return "<hr class=\"bodytexthr\" />\r\n";
    }

  public function GetName()
    {
    return PARAMETER_HORIZONTAL_LINE;
    }
  }

?>
