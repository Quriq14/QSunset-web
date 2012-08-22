<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");

class TBoldFormat extends TFormatStatus
  {
  public function __construct()
    {
    }

  public function Apply($info,$content,$status)
    {
    return "<span class=\"bodytextbold\">";
    }
   
  public function UnApply($info,$content,$status)
    {
    return "</span>";
    }

  public function IsVisible($info,$content,$status)
    {
    return TRUE;
    }

  public function Pulse($info,$status)
    {
    return "";
    }

  public function GetName()
    {
    return PARAMETER_BOLD;
    }
  }

class TItalicFormat extends TFormatStatus
  {
  public function __construct()
    {
    }

  public function Apply($info,$content,$status)
    {
    return "<span class=\"bodytextitalic\">";
    }
   
  public function UnApply($info,$content,$status)
    {
    return "</span>";
    }

  public function IsVisible($info,$content,$status)
    {
    return TRUE;
    }

  public function Pulse($info,$status)
    {
    return "";
    }

  public function GetName()
    {
    return PARAMETER_ITALIC;
    }
  }

class TUnderlineFormat extends TFormatStatus
  {
  public function __construct()
    {
    }

  public function Apply($info,$content,$status)
    {
    return "<span class=\"bodytextunderline\">";
    }
   
  public function UnApply($info,$content,$status)
    {
    return "</span>";
    }

  public function IsVisible($info,$content,$status)
    {
    return TRUE;
    }

  public function Pulse($info,$status)
    {
    return "";
    }

  public function GetName()
    {
    return PARAMETER_UNDERLINE;
    }
  }

?>
