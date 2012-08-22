<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");

require_once("html/htmlutils.php");

// writes to the output the characters passed in the first attribute
class TCharWriterFormat extends TFormatStatus
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
    if (!isset($attribs[1]))
      return "";

    return TrueHtmlEntities($attribs[1]);
    }

  public function GetName()
    {
    return PARAMETER_WRITE_CHARS;
    }
  }

?>
