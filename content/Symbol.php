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

  public function AddSubSymbol($formatattribs)
    {
    $this->subsymbols[$this->subsymbolscount++] = $formatattribs;
    }

  public function Apply($info,$content,$attribs)
    {
    $result = "";

    for ($i = 0; $i < $this->subsymbolscount; $i++)
      $result .= $this->subsymbols[$i]->Apply($info,$content,$attribs);

    return $result;
    }

  public function UnApply($info,$content,$attribs)
    {
    $result = "";

    // symbols must be unapplied in the reverse order (HTML DOM is a tree)
    for ($i = ($this->subsymbolscount - 1); $i >= 0; $i--)
      $result .= $this->subsymbols[$i]->UnApply($info,$content,$attribs);

    return $result;
    }

  public function IsVisible($info,$content,$attribs)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++)
      if (!$this->subsymbols[$i]->IsVisible($info,$content,$attribs))
        return FALSE;

    return TRUE;
    }

  // one-shot (when symbol is called but area of effect does not begin)
  public function Pulse($info,$attribs)
    {
    $result = "";

    for ($i = 0; $i < $this->subsymbolscount; $i++)
      $result .= $this->subsymbols[$i]->Pulse($info,$attribs);

    return $result;
    }

  public function NeedChildProc($info,$attribs)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++)
      if ($this->subsymbols[$i]->NeedChildProc($info,$attribs))
        return TRUE;

    return FALSE;
    }

  public function ChildProc($info,$attribs,$origsymbattr)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++)
      if ($this->subsymbols[$i]->NeedChildProc($info,$attribs))
        {
        $this->subsymbols[$i]->ChildProc($info,$origsymbattr,$attribs);
        return; // multiple calls are illegal, return
        }
    }

  public function OnAddedProducer($info,$producer,$attribs)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++) // propagate to subsymbols
      $this->subsymbols[$i]->OnAddedProducer($info,$producer,$attribs);
    }

  public function OnBegin($info,$attribs)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++) // propagate to subsymbols
      $this->subsymbols[$i]->OnBegin($info,$attribs);
    }

  public function OnEnd($info,$attribs)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++) // propagate to subsymbols
      $this->subsymbols[$i]->OnEnd($info,$attribs);
    }

  public function OnPulse($info,$attribs)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++) // propagate to subsymbols
      $this->subsymbols[$i]->OnPulse($info,$attribs);
    }

  public function GetSubSymbols()
    {
    return $this->subsymbols;
    }

  private $name = "";
  private $subsymbolscount = 0;  // the number of subsymbols in 
  private $subsymbols = array(); // an array (int) => FormatAttribs
  }

// this class holds a symbol with attribute information. When it will be Produce()d, the symbol's Pulse() will be called
class TSymbolHolder extends THtmlProducer
  {
  // $symbolattr is a TFormatAttribs
  public function TSymbolHolder($symbolattr)
    {
    if (!isset($symbolattr))
      {
      error_log("TSymbolHolder: symbolattr not set.");
      return;
      }

    $this->mySymbol = $symbolattr;
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
