<?php

require_once("content/defines.php");
require_once("content/FormatStatus.php");

class TDisplayIfFormat extends TFormatStatus
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
    $ci = 1;
    return NFormatCondition::Evaluate($info,$attribs,$ci,array(NFormatCondition::DATA_PRODUCER => $content));
    }

  public function Pulse($info,$attribs)
    {
    return "";
    }

  public function GetName()
    {
    return PARAMETER_DISPLAYIF;
    }
  }

?>
