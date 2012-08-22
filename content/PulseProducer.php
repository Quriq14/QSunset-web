<?php

require_once("content/Producer.php");

// this class holds a TParamFormatAttribs. When it will be Produce()d, the Pulse method will be called
class TPulseProducer extends THtmlProducer
  {
  public function __construct($formatattr)
    {
    $this->formatattr = $formatattr;
    }

  public function Produce($info)
    {
    $result = "";

    if (!$this->VisibleAll($info,""))
      return ""; // invisibility

    $result .= $this->ApplyAll($info,"");

    if ($this->formatattr !== FALSE)
      $result .= $this->formatattr->Pulse($info,array());

    $result .= $this->UnApplyAll($info,"");

    return $result;
    }

  private $formatattr = FALSE;
  }

?>
