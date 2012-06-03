<?php

require_once("content/FormatStatus.php");
require_once("content/FormatFactory.php");
require_once("content/Producer.php");

class TSymbol extends TFormatStatus
  {
  public function __construct($symbolName)
    {
    $this->name = $symbolName;
    }

  public function AddSubSymbol($name,$value)
    {
    $this->subsymbols[$name] = $value;
    }

  public function Apply($info,$content,$attribs)
    {
    $result = "";

    foreach($this->subsymbols as $name => $attr)
      {
      $format = GetFormatByName($name);
      if ($format !== FALSE)
        $result .= $format->Apply($info,$content,$attr);
      }

    return $result;
    }

  public function UnApply($info,$content,$attribs)
    {
    $result = "";

    $reverse = array();
    $reversecount = count($this->subsymbols) - 1;
    foreach($this->subsymbols as $name => $attr)
      $reverse[$reversecount--] = $name;

    for ($i = 0; $i < count($reverse); $i++)
      {
      $format = GetFormatByName($reverse[$i]);
      if ($format !== FALSE)
        $result .= $format->UnApply($info,$content,$this->subsymbols[$reverse[$i]]);
      }

    return $result;
    }

  public function IsVisible($info,$content,$attribs)
    {
    foreach($this->subsymbols as $name => $attr)
      {
      $format = GetFormatByName($name);
      if ($format !== FALSE)
        if (!$format->IsVisible($info,$content,$attr))
          return FALSE;
      }

    return TRUE;
    }

  // one-shot (when symbol is called but area of effect does not begin)
  public function Pulse($info,$attribs)
    {
    $result = "";

    foreach($this->subsymbols as $name => $attr)
      {
      $format = GetFormatByName($name);
      if ($format !== FALSE)
        $result .= $format->Pulse($info,$attr);
      }

    return $result;
    }

  public function NeedChild($info,$attribs)
    {
    foreach($this->subsymbols as $name => $attr)
      {
      $format = GetFormatByName($name);
      if ($format !== FALSE)
        if ($format->NeedChild($info,$attr))
          return TRUE;
      }

    return FALSE;
    }

  public function Child($info,$attribs)
    {
    foreach($this->subsymbols as $name => $attr)
      {
      $format = GetFormatByName($name);
      if ($format !== FALSE)
        if ($format->NeedChild($info,$attr))
          {
          $format->Child($info,$attr);
          return; // Avoid multiple child calls.
          }
      }
    }

  private $name = "";
  private $subsymbols = array(); // an array attribute_name => attribute_params[]
  }

// this class holds a Symbol. When it will be Produce()d, the symbol's Pulse() will be called
class TSymbolHolder extends THtmlProducer
  {
  public function TSymbolHolder($symbol)
    {
    if (!isset($symbol))
      {
      error_log("TSymbolHolder: symbol not set.");
      return;
      }

    $this->mySymbol = $symbol;
    }

  public function Produce($info)
    {
    $result = "";

    if (!$this->VisibleAll($info,""))
      return ""; // invisibility

    $result .= $this->ApplyAll($info,"");

    if ($this->mySymbol !== FALSE)
      $result .= $this->mySymbol->Pulse($info,array());

    $result .= $this->UnApplyAll($info,"");

    return $result;
    }

  private $mySymbol = FALSE;
  }

?>
