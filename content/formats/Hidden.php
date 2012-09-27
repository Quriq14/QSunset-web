<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");

class THiddenFormat extends TFormatStatus
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
    return FALSE;
    }

  public function Pulse($info,$attribs)
    {
    return "";
    }

  public function GetName()
    {
    return PARAMETER_HIDDEN;
    }
  }

NFormatFactory::Register(new THiddenFormat());

?>
