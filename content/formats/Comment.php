<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");

class TCommentFormat extends TFormatStatus
  {
  function __construct()
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

  public function NeedChildProc($info,$attribs) 
    {
    return TRUE; 
    }

  public function ChildProc($info,$attribs,$orig) 
    {
    $contentidx = $info->processed;

    $em = "\n"; // end marker is, by default, end of line
    if (isset($attribs[1]) && $attribs[1] !== "")
      $em = $attribs[1];
    $emlength = strlen($em);

    $scriptend = strpos($info->content,$em,$contentidx);

    if ($scriptend === FALSE) // it's all a script
      {
      $scriptend = strlen($info->content);
      $emlength = 0; // there is no script ending
      }

    $info->processed = $scriptend + $emlength; // advance past the comment ending tag
    }

  public function GetName()
    {
    return PARAMETER_COMMENT;
    }
  }

NFormatFactory::Register(new TCommentFormat());

?>
