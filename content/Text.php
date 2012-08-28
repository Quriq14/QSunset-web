<?php

require_once("content/FormatStatus.php");
require_once("content/Producer.php");

require_once("html/htmlutils.php");

class TTextHolder extends THtmlProducer
  {
  public function __construct($text)
    {
    $this->text = $text;
    $this->symbols = array();
    }

  public function Produce($info)
    {
    $result = "";

    if (!$this->VisibleAll($info))
      return "";

    $result .= $this->ApplyAll($info);

    $result .= TrueHtmlEntities($this->text);

    $result .= $this->UnApplyAll($info);

    return $result;
    }

  private $text;
  }

?>
