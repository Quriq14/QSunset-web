<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");

class TTextsizeFormat extends TFormatStatus
  {
  public function __construct()
    {
    }

  public function Apply($info,$content,$attribs)
    {
    if (!isset($attribs[1]) || $attribs[1] === "")
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::TEXTSIZE_NOPARAM,array());
      return;
      }

    $style = (string)((int)($attribs[1])); // be sure it's an integer

    return "<span style=\"font-size: ".$style."px; \">";
    }
   
  public function UnApply($info,$content,$attribs)
    {
    return "</span>";
    }

  public function IsVisible($info,$content,$attribs)
    {
    return TRUE;
    }

  public function Pulse($info,$attribs)
    {
    return "";
    }

  public function GetName()
    {
    return PARAMETER_TEXTSIZE;
    }
  }

?>
