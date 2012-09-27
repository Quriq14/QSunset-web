<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");

class TLanguageFormat extends TFormatStatus
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
    foreach($status as $k => $lang)
      if ($k !== 0) // skip parameter name
        if (strtoupper($lang) === strtoupper($info->language))
          return TRUE;

    return FALSE;
    }

  public function Pulse($info,$status)
    {
    return "";
    }

  public function GetName()
    {
    return PARAMETER_LANGUAGE;
    }
  }

NFormatFactory::Register(new TLanguageFormat());

?>
