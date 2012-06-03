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

    if (!$this->VisibleAll($info,$this->text))
      return "";

    $result .= $this->ApplyAll($info,$this->text);

    $result .= $this->text;

    $result .= $this->UnApplyAll($info,$this->text);

    return $result;
    }

  private $text;
  }

?>
