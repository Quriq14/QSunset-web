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
    return "";
    }

  public function UnApply($info,$content,$attribs)
    {
    return "";
    }

  public function IsVisible($info,$content,$attribs)
    {
    return TRUE;
    }

  // one-shot (when symbol is called but area of effect does not begin)
  public function Pulse($info,$attribs)
    {
    return "";
    }

  public function NeedChildProc($info,$attribs,$topsymbattr)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++)
      if ($this->subsymbols[$i]->NeedChildProc($info,$attribs,$topsymbattr))
        return TRUE;

    return FALSE;
    }

  public function ChildProc($info,$attribs,$topsymbattr)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++)
      if ($this->subsymbols[$i]->NeedChildProc($info,$attribs,$topsymbattr))
        {
        $this->subsymbols[$i]->ChildProc($info,$attribs,$topsymbattr);
        return; // multiple calls are illegal, return
        }
    }

  public function OnAddedProducer($info,$producer,$attribs)
    {
    }

  public function OnBegin($info,$attribs,$topsymbattr)
    {
    parent::OnBegin($info,$attribs,$topsymbattr);

    for ($i = 0; $i < $this->subsymbolscount; $i++) // propagate to subsymbols
      $this->subsymbols[$i]->OnBegin($info,$attribs,$topsymbattr);
    }

  public function OnEnd($info,$topsymbname)
    {
    parent::OnEnd($info,$topsymbname);

    for ($i = 0; $i < $this->subsymbolscount; $i++) // propagate to subsymbols
      $this->subsymbols[$i]->OnEnd($info,$topsymbname);
    }

  public function OnPulse($info,$attribs,$topsymbattr)
    {
    for ($i = 0; $i < $this->subsymbolscount; $i++) // propagate to subsymbols
      $this->subsymbols[$i]->OnPulse($info,$attribs,$topsymbattr);
    }

  public function GetSubSymbols()
    {
    return $this->subsymbols;
    }

  public function GetName()
    {
    return $this->name;
    }

  private $name = "";
  private $subsymbolscount = 0;  // the number of subsymbols in 
  private $subsymbols = array(); // an array (int) => FormatAttribs
  }

?>
