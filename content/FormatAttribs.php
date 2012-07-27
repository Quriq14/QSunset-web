<?php

require_once("content/FormatStatus.php");

// contains a format name and its attributes
class TFormatAttribs
  {
  public function __construct($name,$attribs,$alreadyCreatedSymbol = FALSE)
    {
    if (!isset($name) || !isset($attribs))
      {
      error_log("TFormatAttribs: Error: name or attribs not set.");
      return;
      }

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

  public function ChildProc($info)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->ChildProc($info,$this->attribs);
    }

  public function NeedChildProc($info)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      return $this->symbol->NeedChildProc($info,$this->attribs);

    return FALSE; // error
    }

  public function AddedProducer($info,$producer)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->AddedProducer($info,$producer,$this->attribs);
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

  private $name = "";
  private $attribs = array();
  private $symbol = FALSE; // this is a cache: once the symbol object is discovered,
                           // a pointer to it is stored here
  } 


?>
