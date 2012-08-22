<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");

class THiddenFormat extends TFormatStatus
  {
  public function __construct()
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
    return FALSE;
    }

  public function Pulse($info,$status)
    {
    return "";
    }

  public function GetName()
    {
    return PARAMETER_HIDDEN;
    }
  }

?>
