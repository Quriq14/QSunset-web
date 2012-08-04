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
    if (!$this->TryLock($this->applyLock))
      return "";

    $result = "";

    for ($i = 0; $i < $this->subsymbolscount; $i++)
      $result .= $this->subsymbols[$i]->Apply($info,$content);

    $this->UnLock($this->applyLock);

    return $result;
    }

  public function UnApply($info,$content,$attribs)
    {
    if (!$this->TryLock($this->unapplyLock))
      return "";

    $result = "";

    // symbols must be unapplied in the reverse order (HTML DOM is a tree)
    for ($i = ($this->subsymbolscount - 1); $i >= 0; $i--)
      $result .= $this->subsymbols[$i]->UnApply($info,$content);

    $this->UnLock($this->unapplyLock);

    return $result;
    }

  public function IsVisible($info,$content,$attribs)
    {
    if (!$this->TryLock($this->visibleLock))
      return TRUE;

    for ($i = 0; $i < $this->subsymbolscount; $i++)
      if (!$this->subsymbols[$i]->IsVisible($info,$content))
        {
        $this->UnLock($this->visibleLock);
        return FALSE;
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

    for ($i = 0; $i < $this->subsymbolscount; $i++)
      $result .= $this->subsymbols[$i]->Pulse($info);

    $this->UnLock($this->pulseLock);
    return $result;
    }

  public function NeedChildProc($info,$attribs)
    {
    if (!$this->TryLock($this->needChildLock))
      return FALSE;

    for ($i = 0; $i < $this->subsymbolscount; $i++)
      if ($this->subsymbols[$i]->NeedChildProc($info))
        {
        $this->UnLock($this->needChildLock);
        return TRUE;
        }

    $this->UnLock($this->needChildLock);
    return FALSE;
    }

  public function ChildProc($info,$attribs,$origsymbattr)
    {
    if (!$this->TryLock($this->childLock))
      return;

    for ($i = 0; $i < $this->subsymbolscount; $i++)
      if ($this->subsymbols[$i]->NeedChildProc($info))
        {
        $this->subsymbols[$i]->ChildProc($info,$origsymbattr);
        $this->UnLock($this->childLock);
        return; // multiple calls are illegal, return
        }

    $this->UnLock($this->childLock);
    }

  public function OnAddedProducer($info,$producer,$attr)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++) // propagate to subsymbols
      $this->subsymbols[$i]->OnAddedProducer($info,$producer);
    }

  public function OnBegin($info,$attr)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++) // propagate to subsymbols
      $this->subsymbols[$i]->OnBegin($info);
    }

  public function OnEnd($info,$attr)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++) // propagate to subsymbols
      $this->subsymbols[$i]->OnEnd($info);
    }

  public function OnPulse($info,$attr)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++) // propagate to subsymbols
      $this->subsymbols[$i]->OnPulse($info);
    }

  private $name = "";
  private $subsymbolscount = 0;  // the number of subsymbols in 
  private $subsymbols = array(); // an array (int) => TFormatAttribs

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
      $result .= $this->mySymbol->Pulse($info);

    $result .= $this->UnApplyAll($info,"");

    return $result;
    }

  private $mySymbol = FALSE;
  }

?>
