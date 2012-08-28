<?php

abstract class THtmlProducer
  {
  abstract public function Produce($info);

  // add a symbol that was active when the item was created
  // $symbolattr is a TParamFormatAttribs
  public function AddSymbol($info,$symbolattr)
    {
    $this->symbols[count($this->symbols)] = $symbolattr;
    }

  public function VisibleAll($info)
    {
    foreach($this->symbols as $s)
      if (!($s->IsVisible($info,$this)))
        return FALSE; // invisibility

    return TRUE;
    }

  public function ApplyAll($info)
    {
    $result = "";

    $symbolcount = count($this->symbols);
    for ($i = 0; $i < $symbolcount; $i++)
      $result .= $this->symbols[$i]->Apply($info,$this);

    return $result;
    }

  public function UnApplyAll($info)
    {
    $result = "";

    $symbolcount = count($this->symbols);
    for ($i = ($symbolcount-1); $i >= 0; $i--) // reverse order
      $result .= $this->symbols[$i]->UnApply($info,$this);

    return $result;
    }

  // automatically links all the active symbols from the TContentParserInfo
  public function ActiveSymbolsFromInfo($info)
    {
    $sl = $info->GetActiveSymbolList();
    foreach($sl as $symb)
      $this->AddSymbol($info,$symb);
    }

  // returns the array of formats active when the producer was created
  // [0..n-1] => TParamFormatAttribs
  public function GetActiveFormats()
    {
    return $this->symbols;
    }

  // an array of TParamFormatAttribs
  private $symbols = array();
  }

?>
