<?php

require_once("content/FormatStatus.php");
require_once("content/Producer.php");
require_once("content/ParseError.php");
require_once("content/defines.php");

class TListFormatData
  {
  const KEY = "TListFormat";
  const ORDERED = "ORDERED";
  const UNORDERED = "UNORDERED";
  const REVERSED = "DEC";
  const REVERSED2 = "REVERSED";
  const NOT_REVERSED = "INC";
  const NAME = "NAME";

  static public function Get($info)
    {
    $data = $info->GetFormatData(self::KEY);
    if ($data === FALSE)
      {
      $data = new TListFormatData();
      $info->SetFormatData(self::KEY,$data);
      }

    return $data;
    }

  public $depth = 0;                    // current depth for nested lists
  public $unnamedlistcounter = 0;       // an int, will be incremented at every new list defined

  public $itemcounter = array();        // item counter for lists, indexed by name (for named) or by id (for unnamed)
  public $currentname = array();        // an id if unnamed, a string otherwise, indexed by depth

  public $ordered = array();            // TRUE if the list is ordered, indexed by name

  const DEFAULT_LIST_CLASS_UL = "bodytextulstd";
  const DEFAULT_LIST_CLASS_OL = "bodytextolstd";
  }

// holds generic html code
class TListGenericHolder extends THtmlProducer
  {
  public function __construct($info,$content)
    {
    $this->content = $content;
    $this->ActiveSymbolsFromInfo($info);
    }

  public function Produce($info)
    {
    if (!$this->VisibleAll($info,$this->content))
      return "";

    return $this->content;
    }

  private $content;
  }

// holds <ol> and <ul> tags
class TListHolder extends THtmlProducer
  {
  // if $name is an int, the list is unnamed
  public function __construct($info,$name,$reversed,$ordered,$start)
    {
    $this->start = $start;       // start from this item number (not reversed yet)
    $this->name = $name;
    $this->reversed = $reversed;
    $this->ordered = $ordered;
    $this->ActiveSymbolsFromInfo($info);
    }

  public function Produce($info)
    {
    if (!$this->VisibleAll($info,""))
      return "";

    $data = TListFormatData::Get($info);

    if (!isset($data->itemcounter[$this->name]) || $data->itemcounter[$this->name] === 0)
      return ""; // list is empty

    $tagname = $this->ordered ? "ol" : "ul";
    $result = "<".$tagname;
    if ($this->ordered)
      {
      $realstart = $this->start;
      if ($this->reversed)
        {
        $result .= " reversed=\"reversed\"";

        // the list may have been split, correct the starting value
        $realstart = 1 - $this->start + $data->itemcounter[$this->name];
        }
      $result .= " start=\"".(string)($realstart)."\"";
      }
    $result .= ">";
    return $result;
    }

  private $start;
  private $name;
  private $reversed;
  private $ordered;
  }

// holds <li> tags
class TListItemHolder extends THtmlProducer
  {
  public function __construct($info,$cla)
    {
    $this->cla = $cla;
    $this->ActiveSymbolsFromInfo($info);
    }

  public function Produce($info)
    {
    if (!$this->VisibleAll($info,""))
      return "";

    return "<li class=\"".htmlspecialchars($this->cla)."\">";
    }

  private $cla;
  }

class TListFormat extends TFormatStatus
  {
    public function __construct()
    {
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

  public function Pulse($info,$attribs)
    {
    return "";
    }

  private function ParseParams($attribs,&$reversed,&$ordered,&$name)
    {
    // possible attributes, with default value
    $reversed = FALSE;
    $ordered = FALSE;
    $name = FALSE;

    // parse the attributes
    $attribcount = count($attribs);
    for ($i = 1; $i < $attribcount; $i++)
      switch (strtoupper($attribs[$i]))
        {
        case TListFormatData::REVERSED:
        case TListFormatData::REVERSED2:
          $reversed = TRUE;
          break;
        case TListFormatData::NOT_REVERSED:
          $reversed = FALSE;
          break;
        case TListFormatData::ORDERED:
          $ordered = TRUE;
          break;
        case TListFormatData::UNORDERED:
          $ordered = FALSE;
          break;
        case TListFormatData::NAME:
          if (isset($attribs[$i+1])) // =name=aa sets the name to aa
            $name = $attribs[++$i];
          break;
        }
    }

  public function OnBegin($info,$attribs,$topsymbattr)
    {
    parent::OnBegin($info,$attribs,$topsymbattr);

    $data = TListFormatData::Get($info);
    $data->depth++;

    $reversed = FALSE;
    $ordered = FALSE;
    $name = FALSE;
    $this->ParseParams($attribs,$reversed,$ordered,$name);

    $start = 1; // first item number

    if ($name === FALSE || $name === "")
      $name = $data->unnamedlistcounter++;  // if unnamed, use a new list id

    if (!isset($data->itemcounter[$name]))
      $data->itemcounter[$name] = 0; // it's a new list: initialize counter to 0
    $start = 1 + $data->itemcounter[$name];
    $data->currentname[$data->depth] = $name;
    $data->ordered[$name] = $ordered;

    
    $info->AddToResultChain(new TListHolder($info,$name,$reversed,$ordered,$start));
    }

  public function OnEnd($info,$topsymbname)
    {
    $attribs = $info->GetActiveSymbol($this->GetName(),$topsymbname);
    if ($attribs === FALSE)
      return; // error
    $attribs = $attribs->attribs;

    $data = TListFormatData::Get($info);

    $reversed = FALSE;
    $ordered = FALSE;
    $name = FALSE;
    $this->ParseParams($attribs,$reversed,$ordered,$name);

    unset($data->currentname[$data->depth]);
    if ($data->depth > 0)
      $data->depth--;

    $info->AddToResultChain(new TListGenericHolder($info,$ordered ? "</ol>\r\n" : "</ul>\r\n"));

    parent::OnEnd($info,$topsymbname);
    }

  public function GetName()
    {
    return PARAMETER_LIST;
    }
  }

class TListItemFormat extends TFormatStatus
  {
    public function __construct()
    {
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

  public function Pulse($info,$attribs)
    {
    return "";
    }

  public function OnBegin($info,$attribs,$topsymbattr)
    {
    parent::OnBegin($info,$attribs,$topsymbattr);

    $data = TListFormatData::Get($info);

    if (!isset($data->currentname[$data->depth]))
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::LISTITEM_OUTSIDE_LIST,array());
      return;
      }
    $name = $data->currentname[$data->depth];

    if (!isset($data->itemcounter[$name]))
      return; // integrity check
    $data->itemcounter[$name]++;

    // get the class on the top of the stack
    $olulname = $data->ordered[$name] ? PARAMETER_OLISTCLASS : PARAMETER_ULISTCLASS;
    $class = $data->ordered[$name] ? TListFormatData::DEFAULT_LIST_CLASS_OL : TListFormatData::DEFAULT_LIST_CLASS_UL;
    $symbattr = $info->GetTopActiveSymbol($olulname);
    if ($symbattr !== FALSE)
      $class = $symbattr->format->GetListClass($info,$symbattr->attribs);

    $info->AddToResultChain(new TListItemHolder($info,$class));
    }

  public function OnEnd($info,$topsymbname)
    {
    $info->AddToResultChain(new TListGenericHolder($info,"</li>\r\n"));

    parent::OnEnd($info,$topsymbname);
    }

  public function GetName()
    {
    return PARAMETER_LISTITEM;
    }
  }

class TListClassFormat extends TFormatStatus
  {
  public function __construct($name)
    {
    $this->name = $name;

    // find the default value
    // and the prefix
    switch ($this->name)
      {
      case PARAMETER_OLISTCLASS:
        $this->def = TListFormatData::DEFAULT_LIST_CLASS_OL;
        $this->prefix = "ol";
        break;
      case PARAMETER_ULISTCLASS:
        $this->def = TListFormatData::DEFAULT_LIST_CLASS_UL;
        $this->prefix = "ul";
        break;
      default:
        error_log("TListClassFormat created with unknown name.");
        return; // ??
      }
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

  public function Pulse($info,$attribs)
    {
    return "";
    }

  static private $validclasses = array( // defined in the css
    "bodytextulstd" => TRUE,
    "bodytextulnone" => TRUE,
    "bodytextuldisc" => TRUE,
    "bodytextulcircle" => TRUE,
    "bodytextulsquare" => TRUE,

    "bodytextolstd" => TRUE,
    "bodytextolnone" => TRUE,
    "bodytextoldecimal" => TRUE,
    "bodytextolupper-alpha" => TRUE,
    "bodytextolupper-roman" => TRUE,
    );

  const PREFIX = "bodytext";

  public function OnBegin($info,$attribs,$topsymbattr)
    {
    if (isset($attribs[1]) && $attribs[1] !== "")
      {
      $maybeclass = self::PREFIX.$this->prefix.$attribs[1];
      if (!isset(self::$validclasses[$maybeclass])) // unknown class
        {
        NParseError::Error($info,NParseError::ERROR,NParseError::UNKNOWN_LIST_CLASS,array(0 => $attribs[1]));
        return; // do not activate the format with invalid params
        }
      }

    parent::OnBegin($info,$attribs,$topsymbattr);
    }

  public function GetListClass($info,$attribs)
    {
    if (isset($attribs[1]) && $attribs[1] !== "")
      return self::PREFIX.$this->prefix.$attribs[1];

    return $this->def;
    }

  public function GetName()
    {
    return $this->name;
    }

  private $name;
  private $prefix = "";
  private $def = "";
  }
?>
