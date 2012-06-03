<?php

require_once("content/FormatStatus.php");

class TBoldFormat extends TFormatStatus
  {
  public function TBoldFormat()
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
  }

class TItalicFormat extends TFormatStatus
  {
  public function TItalicFormat()
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
  }

class TUnderlineFormat extends TFormatStatus
  {
  public function TUnderlineFormat()
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
  }

?>
