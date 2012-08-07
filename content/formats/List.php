<?php

require_once("content/FormatStatus.php");
require_once("content/Producer.php");
require_once("content/ParseError.php");

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
  }

// holds generic html code
class TListGenericHolder extends THtmlProducer
  {
  public function __construct($content)
    {
    $this->content = $content;
    }

  public function Produce($info)
    {
    return $this->content;
    }

  private $content;
  }

// holds <ol> and <ul> tags
class TListHolder extends THtmlProducer
  {
  // if $name is an int, the list is unnamed
  public function __construct($name,$reversed,$ordered,$start)
    {
    $this->start = $start;       // start from this item number (not reversed yet)
    $this->name = $name;
    $this->reversed = $reversed;
    $this->ordered = $ordered;
    }

  public function Produce($info)
    {
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
  public function __construct()
    {
   
    }

  public function Produce($info)
    {
    return "<li>";
    }
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

  public function OnBegin($info,$attribs)
    {
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
      
    $info->AddToResultChain(new TListHolder($name,$reversed,$ordered,$start));
    }

  public function OnEnd($info,$attribs)
    {
    $data = TListFormatData::Get($info);

    $reversed = FALSE;
    $ordered = FALSE;
    $name = FALSE;
    $this->ParseParams($attribs,$reversed,$ordered,$name);

    unset($data->currentname[$data->depth]);
    if ($data->depth > 0)
      $data->depth--;

    $info->AddToResultChain(new TListGenericHolder($ordered ? "</ol>\r\n" : "</ul>\r\n"));
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

  public function OnBegin($info,$attribs)
    {
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

    $info->AddToResultChain(new TListItemHolder());
    }

  public function OnEnd($info,$attribs)
    {
    $info->AddToResultChain(new TListGenericHolder("</li>\r\n"));
    }
  }

?>
