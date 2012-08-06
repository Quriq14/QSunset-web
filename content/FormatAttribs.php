<?php

require_once("content/FormatStatus.php");

// contains a format name and its attributes
class TFormatAttribs
  {
  public function __construct($name,$attribs,$alreadyCreatedSymbol = FALSE)
    {
    $this->name = $name;
    $this->attribs = $attribs;
    $this->symbol = $alreadyCreatedSymbol; // cache it if already provided
    }

  public function Apply($info,$content)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      return $this->symbol->Apply($info,$content,$this->attribs);

    return "";
    }

  public function UnApply($info,$content)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      return $this->symbol->UnApply($info,$content,$this->attribs);
    return "";
    }

  public function Pulse($info)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      return $this->symbol->Pulse($info,$this->attribs);
    return "";
    }

  public function IsVisible($info,$content)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      return $this->symbol->IsVisible($info,$content,$this->attribs);
    return TRUE;
    }

  public function ChildProc($info,$origsymbattr)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->ChildProc($info,$this->attribs,$origsymbattr);
    }

  public function NeedChildProc($info)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      return $this->symbol->NeedChildProc($info,$this->attribs);

    return FALSE; // error
    }

  public function OnAddedProducer($info,$producer)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->OnAddedProducer($info,$producer,$this->attribs);
    }

  public function OnBegin($info)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->OnBegin($info,$this->attribs);
    }

  public function OnEnd($info)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->OnEnd($info,$this->attribs);
    }

  public function OnPulse($info)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->OnPulse($info,$this->attribs);
    }

  // $symbolattr is a TFormatAttribs
  public function AddSubSymbol($symbolattr)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->AddSubSymbol($symbolattr);
    }

  public function Validate($info) // attempts to translate the symbol name to a symbol.
                                  // FALSE if failed, TRUE if success
    {
    if ($this->symbol !== FALSE)
      return TRUE;

    $this->symbol = $info->GetFormatByName($this->name);
    if ($this->symbol !== FALSE)
      return TRUE;
    return FALSE;
    }

  public function GetName()
    {
    return $this->name;
    }

  public function GetAttribs()
    {
    return $this->attribs;
    }

  // FALSE if failed
  public function GetSymbol()
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    return $this->symbol;
    }

  // searches recursively among the subsymbols and finds an array of TFormatAttribs with name $subname
  public function GetSubSymbolsWithName($info,$subname)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    $result = array();
    $resultcount = 0;
    if ($this->name === $subname)
      $result[$resultcount++] = $this;

    if ($this->symbol === FALSE)
      return $result; // symbol does not exists yet

    $subsymbols = $this->symbol->GetSubSymbols();
    foreach ($subsymbols as $ss)
      {
      $subsub = $ss->GetSubSymbolsWithName($info,$subname); // recursive call
      foreach ($subsub as $sss)
        $result[$resultcount++] = $sss;
      }

    return $result;
    }

  private $name = "";
  private $attribs = array();
  private $symbol = FALSE; // this is a cache: once the symbol object is discovered,
                           // a pointer to it is stored here
  } 


?>
