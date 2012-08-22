<?php

abstract class THtmlProducer
  {
  abstract public function Produce($info);

  // add a symbol that was active when the item was created
  // $symbolattr is a TParamFormatAttribs
  public function AddSymbol($info,$symbolattr)
    {
    $this->symbols[count($this->symbols)] = $symbolattr;
    $symbolattr->OnAddedProducer($info,$this);
    }

  public function VisibleAll($info,$text)
    {
    foreach($this->symbols as $s)
      if (!($s->IsVisible($info,$text)))
        return FALSE; // invisibility

    return TRUE;
    }

  public function ApplyAll($info,$text)
    {
    $result = "";

    $symbolcount = count($this->symbols);
    for ($i = 0; $i < $symbolcount; $i++)
      $result .= $this->symbols[$i]->Apply($info,$text);

    return $result;
    }

  public function UnApplyAll($info,$text)
    {
    $result = "";

    $symbolcount = count($this->symbols);
    for ($i = ($symbolcount-1); $i >= 0; $i--) // reverse order
      $result .= $this->symbols[$i]->UnApply($info,$text);

    return $result;
    }

  // automatically links all the active symbols from the TContentParserInfo
  public function ActiveSymbolsFromInfo($info)
    {
    $sl = $info->GetActiveSymbolList();
    foreach($sl as $symb)
      $this->AddSymbol($info,$symb);
    }

  // an array of TParamFormatAttribs
  protected $symbols = array();
  }

?>
