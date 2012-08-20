<?php

require_once("content/FormatStatus.php");

require_once("content/ParseError.php");

// contains a format name and its attributes
class TFormatAttribs
  {
  public function __construct($name,$attribs,$alreadyCreatedSymbol = FALSE)
    {
    $this->name = $name;
    $this->attribs = $attribs;
    $this->symbol = $alreadyCreatedSymbol; // cache it if already provided
    }

  private static function ProcessSymAttribs($symattribs)
    {
    $procsymattribs = array();
    $symattribscount = count($symattribs) - 1;
    for ($i = 1; $i < $symattribscount; $i += 2)
      $procsymattribs[$symattribs[$i]] = $symattribs[$i + 1];

    return $procsymattribs;
    }

  private static function ReplaceAttrib($attr,$symattribs,&$cache)
    {
    $offset = 0;
    $length = strlen($attr);
    $result = "";

    while ($offset < $length)
      {
      $first = strpos($attr,"%",$offset);
      if ($first === FALSE)
        $first = $length;
      $firstpart = substr($attr,$offset,$first - $offset);
      if ($firstpart !== FALSE)
        $result .= $firstpart;
      $offset = $first + 1;
      if ($offset >= $length)
        continue;

      $second = strpos($attr,"%",$offset);
      if ($second === FALSE)
        $second = $length;
      $secondpart = substr($attr,$offset,$second - $offset);
      if ($secondpart === FALSE || $secondpart === "")
        $result .= "%"; // %% => %
        else
          {
          if ($cache === FALSE)
            $cache = self::ProcessSymAttribs($symattribs);

          if (isset($cache[$secondpart]))
            $result .= $cache[$secondpart];
          }
      $offset = $second + 1;
      }

    return $result;
    }

  // $attribs is an array of strings, with key 0 (reserved), 1 .. n
  // that contains the attributes of this symbol
  // $symattribs is an array of strings, with key 0 (reserved) 1 .. n
  // that contains the attributes of the parent symbol (or empty if none)
  // every construct like %string% inside any $attribs will be detected
  // and substituted with $symattribs[string] (or empty string if !isset)
  // %% in $attribs will be changed to single %
  // returns the new attribs created this way
  private static function SymbolParameterReplace($attribs,$symattribs)
    {
    $result = array(0 => $attribs[0]); // copy the parameter name

    $cache = FALSE;

    $attribscount = count($attribs);
    for ($i = 1; $i < $attribscount; $i++) // replace all the parameters inside the attributes
      $result[$i] = self::ReplaceAttrib($attribs[$i],$symattribs,$cache);

    return $result;
    }

  public function Apply($info,$content,$symattribs)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      return $this->symbol->Apply($info,$content,self::SymbolParameterReplace($this->attribs,$symattribs));

    return "";
    }

  public function UnApply($info,$content,$symattribs)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      return $this->symbol->UnApply($info,$content,self::SymbolParameterReplace($this->attribs,$symattribs));
    return "";
    }

  public function Pulse($info,$symattribs)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      return $this->symbol->Pulse($info,self::SymbolParameterReplace($this->attribs,$symattribs));
    return "";
    }

  public function IsVisible($info,$content,$symattribs)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      return $this->symbol->IsVisible($info,$content,self::SymbolParameterReplace($this->attribs,$symattribs));
    return TRUE;
    }

  public function ChildProc($info,$origsymbattr,$symattribs)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->ChildProc($info,self::SymbolParameterReplace($this->attribs,$symattribs),$origsymbattr);
    }

  public function NeedChildProc($info,$symattribs)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      return $this->symbol->NeedChildProc($info,self::SymbolParameterReplace($this->attribs,$symattribs));

    return FALSE; // error
    }

  public function OnAddedProducer($info,$producer,$symattribs)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->OnAddedProducer($info,$producer,self::SymbolParameterReplace($this->attribs,$symattribs));
    }

  public function OnBegin($info,$symattribs)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->OnBegin($info,self::SymbolParameterReplace($this->attribs,$symattribs));
    }

  public function OnEnd($info,$symattribs)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->OnEnd($info,self::SymbolParameterReplace($this->attribs,$symattribs));
    }

  public function OnPulse($info,$symattribs)
    {
    if ($this->symbol === FALSE)
      $this->symbol = $info->GetFormatByName($this->name);

    if ($this->symbol !== FALSE)
      $this->symbol->OnPulse($info,self::SymbolParameterReplace($this->attribs,$symattribs));
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
