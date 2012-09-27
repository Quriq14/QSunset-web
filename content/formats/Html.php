<?php

require_once("content/FormatStatus.php");
require_once("content/Producer.php");
require_once("content/defines.php");

// this class contains some raw HTML to be produced
class THtmlHolder extends THtmlProducer
  {
  public function __construct($content)
    {
    $this->content = $content;
    $this->symbols = array();
    }

  public function Produce($info)
    {
    $result = "";

    if (!$this->VisibleAll($info))
      return $result;

    $result .= $this->ApplyAll($info);

    $result .= $this->content;

    $result .= $this->UnApplyAll($info);

    return $result;
    }

  private $content;
  }

class THtmlFormat extends TFormatStatus
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

  public function NeedChildProc($info,$attribs,$orig) 
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

    $content = substr($info->content,$contentidx,$scriptend - $contentidx);
    if ($content !== FALSE && $content !== "") // if there is content
      {
      $holder = new THtmlHolder($content);
      $info->AddToResultChain($holder);
      $holder->ActiveSymbolsFromInfo($info);
      }

    $info->processed = $scriptend + $emlength; // advance past the comment ending tag
    }

  public function GetName()
    {
    return PARAMETER_HTML;
    }
  }

NFormatFactory::Register(new THtmlFormat());

?>
