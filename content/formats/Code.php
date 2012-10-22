<?php

require_once("content/FormatStatus.php");
require_once("content/Producer.php");
require_once("content/defines.php");

require_once("html/htmlutils.php");

require_once("highlighter/Adapter.php");

class TCodeHolder extends THtmlProducer
  {
  // $hl: a TContentHighlighter
  // $pre: if TRUE the code will be surrounded by <pre> </pre>
  public function __construct($code,$lang,$pre)
    {
    $this->pre = $pre;
    $this->hl = new TContentHighlighter($lang,$code);
    }

  public function Produce($info)
    {
    $result = "";

    if (!$this->VisibleAll($info))
      return $result;
   
    $tag = $this->pre ? "pre" : "span";

    $result .= "<".$tag." class=\"bodytextcode ".$tag."\">";

    $result .= $this->hl->ProduceHTML();

    $result .= "</".$tag.">";

    return $result;
    }

  private $hl;
  private $pre;
  }

class TCodeFormat extends TFormatStatus
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

  const LANG = "lang";   // the next parameter is the language
  const SPAN = "inline"; // inline code
  const PRE  = "box";    // code in a box (surrounded by <pre> tag)
  const MARK = "until";  // the next parameter is the ending mark

  private function ParseParams($info,$attribs,&$ending,&$pre,&$lang)
    {
    // initialize to default
    $ending = "\n";
    $pre    = TRUE;
    $lang   = "";

    for ($i = 1; isset($attribs[$i]); $i++)
      switch (strtolower($attribs[$i]))
        {
        case self::LANG:
          if (isset($attribs[$i + 1]))
            $lang = $attribs[++$i];
            else 
              NParseError::Error($info,NParseError::ERROR,NParseError::CODE_UNDEFINED_LANGUAGE,array());
          break;
        case self::SPAN:
          $pre = FALSE;
          break;
        case self::PRE:
          $pre = TRUE;
          break;
        case self::MARK:
          if (isset($attribs[$i + 1]) && $attribs[$i + 1] !== "")
            $ending = $attribs[++$i];
          break;
        default:
          NParseError::Error($info,NParseError::ERROR,NParseError::CODE_INVALID_PARAM,array(0 => $attribs[$i]));
        }
    }

  public function ChildProc($info,$attribs,$orig) 
    {
    $contentidx = $info->processed;

    $this->ParseParams($info,$attribs,$em,$pre,$lang);

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
      $holder = new TCodeHolder($content,$lang,$pre);
      $info->AddToResultChain($holder);
      $holder->ActiveSymbolsFromInfo($info);
      }

    $info->processed = $scriptend + $emlength; // advance past the comment ending tag
    }

  public function GetName()
    {
    return PARAMETER_CODE;
    }
  }

NFormatFactory::Register(new TCodeFormat());

?>
