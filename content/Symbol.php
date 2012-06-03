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
    if (!$this->TryLock($this->applyLock))
      return "";

    $result = "";

    foreach($this->subsymbols as $name => $attr)
      {
      $format = $info->GetFormatByName($name);
      if ($format !== FALSE)
        $result .= $format->Apply($info,$content,$attr);
      }

    $this->UnLock($this->applyLock);

    return $result;
    }

  public function UnApply($info,$content,$attribs)
    {
    if (!$this->TryLock($this->unapplyLock))
      return "";

    $result = "";

    $reverse = array();
    $reversecount = count($this->subsymbols) - 1;
    foreach($this->subsymbols as $name => $attr)
      $reverse[$reversecount--] = $name;

    for ($i = 0; $i < count($reverse); $i++)
      {
      $format = $info->GetFormatByName($reverse[$i]);
      if ($format !== FALSE)
        $result .= $format->UnApply($info,$content,$this->subsymbols[$reverse[$i]]);
      }

    $this->UnLock($this->unapplyLock);

    return $result;
    }

  public function IsVisible($info,$content,$attribs)
    {
    if (!$this->TryLock($this->visibleLock))
      return TRUE;

    foreach($this->subsymbols as $name => $attr)
      {
      $format = $info->GetFormatByName($name);
      if ($format !== FALSE)
        if (!$format->IsVisible($info,$content,$attr))
          {
          $this->UnLock($this->visibleLock);
          return FALSE;
          }
      }

    $this->UnLock($this->visibleLock);
    return TRUE;
    }

  // one-shot (when symbol is called but area of effect does not begin)
  public function Pulse($info,$attribs)
    {
    if (!$this->TryLock($this->pulseLock))
      return "";

    $result = "";

    foreach($this->subsymbols as $name => $attr)
      {
      $format = $info->GetFormatByName($name);
      if ($format !== FALSE)
        $result .= $format->Pulse($info,$attr);
      }

    $this->UnLock($this->pulseLock);
    return $result;
    }

  public function NeedChild($info,$attribs)
    {
    if (!$this->TryLock($this->needChildLock))
      return FALSE;

    foreach($this->subsymbols as $name => $attr)
      {
      $format = $info->GetFormatByName($name);
      if ($format !== FALSE)
        if ($format->NeedChild($info,$attr))
          {
          $this->UnLock($this->needChildLock);
          return TRUE;
          }
      }

    $this->UnLock($this->needChildLock);
    return FALSE;
    }

  public function Child($info,$attribs)
    {
    if (!$this->TryLock($this->childLock))
      return;

    foreach($this->subsymbols as $name => $attr)
      {
      $format = $info->GetFormatByName($name);
      if ($format !== FALSE)
        if ($format->NeedChild($info,$attr))
          {
          $format->Child($info,$attr);
          $this->UnLock($this->childLock);
          return; // Avoid multiple child calls.
          }
      }

    $this->UnLock($this->childLock);
    }

  private $name = "";
  private $subsymbols = array(); // an array attribute_name => attribute_params[]

  // locks to prevent symbol recursion (a symbol inside a symbol with the same name)
  private $childLock = FALSE;
  private $needChildLock = FALSE;
  private $pulseLock = FALSE;
  private $applyLock = FALSE;
  private $unapplyLock = FALSE;
  private $visibleLock = FALSE;

  private function TryLock(&$var)
    {
    if ($var)
      return FALSE;
    $var = TRUE;
    return TRUE;
    }

  private function UnLock(&$var)
    {
    $var = FALSE;
    }
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
