<?php

abstract class THtmlProducer
  {
  abstract public function Produce($info);

  // add a symbol that was active when the item was created
  // $symbolattr is a TFormatAttribs
  public function AddSymbol($info,$symbolattr)
    {
    $this->symbols[count($this->symbols)] = $symbolattr;
    $symbolattr->AddedProducer($info,$this);
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

  // an array of TFormatAttribs (formats with attribute information)
  protected $symbols = array();
  }

?>
