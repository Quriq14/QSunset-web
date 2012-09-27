<?php

require_once("content/FormatStatus.php");
require_once("content/Producer.php");
require_once("content/ParseError.php");
require_once("content/defines.php");

class TTableFormatData
  {
  const KEY = "TTableFormat";

  // since column ids and rowids may be changed without dropping the top row format
  // they will be saved here
  public $columnids   = array(); // indexed by $currentdepth, 1..n => int
  public $rowids      = array(); // as above
  public $columnspans = array();
  public $rowspans    = array();

  public $tables      = array(); // indexed by table top symbol name, string => TTableFormatTableData

  public $currentdepth = 0; // nesting depth of the current active table

  static public function Get($info)
    {
    $data = $info->GetFormatData(self::KEY);
    if ($data === FALSE)
      {
      $data = new TTableFormatData();
      $info->SetFormatData(self::KEY,$data);
      }

    return $data;
    }
  }

// contains information about a cell
class TTableFormatCellData
  {
  public $producers = array(); // array 0..n-1 => producers
  public $colspan = 1;
  public $rowspan = 1;
  public $covered = FALSE; // if TRUE, this cell is covered under another cell with rowspan or colspan > 1

  public function AddProducer($producer,$rowspan,$colspan)
    {
    $this->producers[count($this->producers)] = $producer;

    // use the higher possible span
    if ($rowspan > $this->rowspan)
      $this->rowspan = $rowspan;

    if ($colspan > $this->colspan)
      $this->colspan = $colspan;
    }
  }

// contains information about a whole table
class TTableFormatTableData
  {
  private $data; // array rowid => columnid => TTableFormatCellData

  // rows and columns are integer 1-based
  public function AddProducer($row,$column,$producer,$rowspan,$colspan)
    {
    if (!isset($this->data[$row]))
      $this->data[$row] = array();

    if (!isset($this->data[$row][$column]))
      $this->data[$row][$column] = new TTableFormatCellData();

    $this->data[$row][$column]->AddProducer($producer,$rowspan,$colspan);

    // if rowspan or colspan, we must cover nearby cells
    for ($r = 0; $r < $rowspan; $r++)
      for ($c = 0; $c < $colspan; $c++)
        if ($r !== 0 || $c !== 0) // cover all except current cell
          $this->SetCovered($row + $r,$column + $c);
    }

  public function SetCovered($row,$column)
    {
    if (!isset($this->data[$row]))
      $this->data[$row] = array();

    if (!isset($this->data[$row][$column]))
      $this->data[$row][$column] = new TTableFormatCellData();

    $this->data[$row][$column]->covered = TRUE;
    }

  public function IsCovered($row,$column)
    {
    if (!isset($this->data[$row]))
      return FALSE;

    if (!isset($this->data[$row][$column]))
      return FALSE;

    return $this->data[$row][$column]->covered;
    }

  public function GetProducers($row,$column)
    {
    if (!isset($this->data[$row]))
      return array();

    if (!isset($this->data[$row][$column]))
      return array();

    return $this->data[$row][$column]->producers;
    }

  public function GetMaxRow()
    {
    $max = 0;

    foreach ($this->data as $k => $useless)
      if ($k > $max)
        $max = $k;

    return $max;
    }

  public function GetMaxColumn()
    {
    $max = 0;

    foreach ($this->data as $row)
      foreach ($row as $k => $useless)
        if ($k > $max)
          $max = $k;

    return $max;
    }

  public function GetRowSpan($row,$column)
    {
    if (!isset($this->data[$row]))
      return 1;

    if (!isset($this->data[$row][$column]))
      return 1;

    return $this->data[$row][$column]->rowspan;
    }

  public function GetColumnSpan($row,$column)
    {
    if (!isset($this->data[$row]))
      return 1;

    if (!isset($this->data[$row][$column]))
      return 1;

    return $this->data[$row][$column]->colspan;
    }
   }

// holds generic html code
class TTableGenericHolder extends THtmlProducer
  {
  public function __construct($info,$content)
    {
    $this->content = $content;
    $this->ActiveSymbolsFromInfo($info);
    }

  public function Produce($info)
    {
    if (!$this->VisibleAll($info))
      return "";

    return $this->content;
    }

  private $content;
  }

class TTableFormat extends TFormatStatus
  {
  const LISTENER_KEY_PREFIX = "TTableFormat:";

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

    $info->PushProduceRedirect(self::LISTENER_KEY_PREFIX.($topsymbattr->GetName()),
      new TParamFormatAttribs($this,$attribs,$topsymbattr));

    // increase depth
    $data = TTableFormatData::Get($info);
    $data->currentdepth++;
    // initialize row and column indexes
    $data->columnids[$data->currentdepth] = 1;
    $data->rowids[$data->currentdepth] = 1;
    $data->columnspans[$data->currentdepth] = 1;
    $data->rowspans[$data->currentdepth] = 1;
    }

  public function OnEnd($info,$topsymbname)
    {
    $data = TTableFormatData::Get($info);

    $info->RemoveProduceRedirect(self::LISTENER_KEY_PREFIX.$topsymbname);

    parent::OnEnd($info,$topsymbname);

    if (!isset($data->tables[$topsymbname]))
      return; // table is empty

    // produce the table here
    $tabledata = $data->tables[$topsymbname];

    $maxrow = $tabledata->GetMaxRow();
    $maxcol = $tabledata->GetMaxColumn();

    $info->AddToResultChain(new TTableGenericHolder($info,"<table class=\"bodytexttable\">"));

    for ($r = 1; $r <= $maxrow; $r++)
      {
      $info->AddToResultChain(new TTableGenericHolder($info,"<tr>"));

      for ($c = 1; $c <= $maxcol; $c++)
        if (!$tabledata->IsCovered($r,$c)) // do not display if covered by other cells
          {
          $tdopen = "<td class=\"bodytexttd\"";
          if (($cs = $tabledata->GetColumnSpan($r,$c)) !== 1) // add columspan if needed
            $tdopen .= " colspan=\"".$cs."\"";

          if (($rs = $tabledata->GetRowSpan($r,$c)) !== 1) // add rowspan if needed
            $tdopen .= " rowspan=\"".$rs."\"";

          $info->AddToResultChain(new TTableGenericHolder($info,$tdopen.">"));

          // store all the producers
          $producers = $tabledata->GetProducers($r,$c);
          $producerscount = count($producers);
          for ($p = 0; $p < $producerscount; $p++)
            $info->AddToResultChain($producers[$p]);

          $info->AddToResultChain(new TTableGenericHolder($info,"</td>"));
          }

      $info->AddToResultChain(new TTableGenericHolder($info,"</tr>\r\n"));
      }

    $info->AddToResultChain(new TTableGenericHolder($info,"</table>\r\n"));

    $data->currentdepth--;
    }

  public function OnAddedProducer($info,$producer,$paramformatattribs)
    {
    $data = TTableFormatData::Get($info);

    $topname = $paramformatattribs->GetTopSymbName();

    if ($data->currentdepth <= 0)
      return FALSE;

    if (!isset($data->tables[$topname]))
      $data->tables[$topname] = new TTableFormatTableData();

    $row = $data->rowids[$data->currentdepth];
    $column = $data->columnids[$data->currentdepth];
    $rowspan = $data->rowspans[$data->currentdepth];
    $colspan = $data->columnspans[$data->currentdepth];

    $data->tables[$topname]->AddProducer($row,$column,$producer,$rowspan,$colspan);

    return FALSE;
    }

   public function GetName()
    {
    return PARAMETER_TABLE;
    }
  }

NFormatFactory::Register(new TTableFormat());

class TTableRowColumnFormat extends TFormatStatus
  {
  public function __construct($name)
    {
    $this->name = $name;
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

  private function &GetIdRef($info)
    {
    $data = TTableFormatData::Get($info);

    static $err; // the reference to this variable will be returned if error
    $err = 1;

    if ($data->currentdepth <= 0)
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::TABLE_COLROW_OUT_TABLE,array(0 => $this->GetName()));
      return $err;
      }

    if ($this->GetName() === PARAMETER_TABLE_COLUMN)
      return $data->columnids[$data->currentdepth];

    return $data->rowids[$data->currentdepth];
    }

  private function &GetSpanRef($info)
    {
    $data = TTableFormatData::Get($info);

    static $err;
    $err = 1;

    if ($data->currentdepth <= 0)
      return $err; // do not send a NParseError here: GetIdRef already sent it

    if ($this->GetName() === PARAMETER_TABLE_COLUMN)
      return $data->columnspans[$data->currentdepth];

    return $data->rowspans[$data->currentdepth];
    }

  public function OnPulse($info,$attribs,$topsymbattr)
    {
    $topname = $topsymbattr->GetName();

    $idvar = &$this->GetIdRef($info);
    $spanvar = &$this->GetSpanRef($info);

    // if new value !isset, simply increment
    if (!isset($attribs[1]))
      {
      $idvar++;
      return;
      }

    // if isset but invalid, send error
    if (($newid = intval($attribs[1])) <= 0)
      {
      NParseError::Error($info,NParseError::WARNING,NParseError::TABLE_COLROW_INTEGER,
        array(0 => $this->GetName(), 1 => $attribs[1]));
      return;
      }

    // if all is right, update
    $idvar = $newid;
    $spanvar = 1;

    // is the span defined?
    if (isset($attribs[2]) && ($newspan = intval($attribs[2])) > 0)
      $spanvar = $newspan;
    }

   public function GetName()
    {
    return $this->name;
    }

  private $name;
  }

NFormatFactory::Register(new TTableRowColumnFormat(PARAMETER_TABLE_COLUMN));
NFormatFactory::Register(new TTableRowColumnFormat(PARAMETER_TABLE_ROW));

?>
