<?php

require_once("content/FormatStatus.php");
require_once("content/Producer.php");

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

    $result .= $this->ApplyAll($info,$this->content);

    $result .= $this->content;

    $result .= $this->UnApplyAll($info,$this->content);

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

  public function NeedChild($info,$attribs) 
    {
    return TRUE; 
    }

  public function Child($info,$attribs) 
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
      $info->AddToResultChain(new THtmlHolder($content));

    $info->processed = $scriptend + $emlength; // advance past the comment ending tag
    }
  }

?>
