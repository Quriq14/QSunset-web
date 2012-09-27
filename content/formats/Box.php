<?php

require_once("content/Producer.php");
require_once("content/defines.php");
require_once("content/FormatStatus.php");

class TBoxHolder extends THtmlProducer
  {
  public function __construct($info,$content)
    {
    $this->content = $content;
    $this->ActiveSymbolsFromInfo($info);
    }

  public function Produce($info)
    {
    if (!$this->VisibleAll($info))
      return "";

    return $this->content;
    }

  private $content;
  }

class TBoxFormat extends TFormatStatus
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

  const RIGHT  = "right";   // float to right
  const LEFT   = "left";    // float to left
  const BORDER = "border";  // add border
  const NOBOR  = "noborder";// remove border (if added)
  const NOLR   = "clearlr"; // remove float (if added)

  public function OnBegin($info,$attribs,$topsymbattr)
    {
    parent::OnBegin($info,$attribs,$topsymbattr);

    // parse params to get the style
    $leftorright = "";
    $withborder = "";

    for ($i = 1; isset($attribs[$i]); $i++)
      switch (strtolower($attribs[$i]))
        {
        case self::RIGHT:
          $leftorright = " bodytextboxfloatright";
          break;
        case self::LEFT:
          $leftorright = " bodytextboxfloatleft";
          break;
        case self::BORDER:
          $withborder = " bodytextboxborder";
          break;
        case self::NOBOR:
          $withborder = "";
          break;
        case self::NOLR:
          $leftorright = "";
          break;
        default:
          NParseError::Error($info,NParseError::WARNING,NParseError::BOX_UNKNOWN_ATTRIB,array(0 => $attribs[$i]));
          break;
        }

    $info->AddToResultChain(new TBoxHolder($info,"<div class=\"bodytextbox".$leftorright.$withborder."\">\r\n"));
    }

  public function OnEnd($info,$topsymbname)
    {
    $info->AddToResultChain(new TBoxHolder($info,"</div>\r\n"));

    parent::OnEnd($info,$topsymbname);
    }

  public function GetName()
    {
    return PARAMETER_BOX;
    }
  }

NFormatFactory::Register(new TBoxFormat());

?>
