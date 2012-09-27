<?php

require_once("content/defines.php");
require_once("content/FormatStatus.php");

class TTerminateIfFormat extends TFormatStatus
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
    return "";
    }

  public function OnPulse($info,$attribs,$topsymbattr)
    {
    $ci = 1;
    if (NFormatCondition::Evaluate($info,$attribs,$ci,array()))
      $info->RequestEndOfParsing(); // terminate processing of the current file
    }

  public function GetName()
    {
    return PARAMETER_TERMINATEIF;
    }
  }

NFormatFactory::Register(new TTerminateIfFormat());

?>
