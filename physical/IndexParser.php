<?php
require_once("physical/FileParser.php");
require_once("physical/dirutils.php");

require_once("element/secutils.php");
require_once("element/defines.php");

define("INDEX_FILE_SEP","||");

class TIndexEntry
  {
  public $type = 0;
  public $id = "";
  public $addition = "";
  public $childs = array();
  public $parent = FALSE; // FALSE = root, a TIndexEntry otherwise
  public $next = FALSE;   // next object in index, FALSE if none (the object next to a section is usually his first child subsection)
  public $prev = FALSE;   // previous object in index, FALSE if none

  const TYPE_SUBDIR     = 1;
  const TYPE_SECTION    = 2;
  const TYPE_SUBSECTION = 3;
  const TYPE_INDEX      = 4;
  const TYPE_INFO       = 5;

  const COMMAND_END     = "END";

  static public function TypeFromString($str)
    {
    switch (strtoupper(trim($str)))
      {
      case "SUBDIR":
        return TIndexEntry::TYPE_SUBDIR;
      case "SECTION":
        return TIndexEntry::TYPE_SECTION;
      case "SUBSECT":
        return TIndexEntry::TYPE_SUBSECTION;
      case "INDEX":
        return TIndexEntry::TYPE_INDEX;
      case "INFO":
        return TIndexEntry::TYPE_INFO;
      default:
        return FALSE;
      }
    }
  }

// FILE STRUCTURE:
// each line is of type Type<tab>Id<tab>Addition
// lines of type Section start a new sub-index
// lines starting with END close the sub-index
class TIndexParser
  {
  function __construct($dir,$section)
    {
    $this->dir = "";
    $this->info = FALSE;
    $this->section = "";
    $this->content = array();

    $this->index = array();

    $this->addmap = array();
    $this->idmap = array();

    if (!CheckDirectory(dirname($dir)))
      return;

    if (!isset($section) || !is_string($section))
      return;

    // load immediately the whole file on creation
    $scanner = FileParserFactory($dir);
    if (!$scanner->IsValid())
      return;

    $rawcontent = array();
    $headers = $scanner->ScanSectionHeaders($section);
    if (count($headers) > 0)
      {
      $this->info = $headers[0];

      $rawcontent = $scanner->GetContent($this->info->id);
      }

    // everything went ok
    $this->dir = $dir;
    $this->section = $section;

    // now, load the index from the raw content
    $rawcontentidx = 0;
    $prev = FALSE;      // previous object (initialize to FALSE)
    $rawcontentcount = count($rawcontent);
    $this->ProcessSection($rawcontent,$rawcontentcount,$rawcontentidx,FALSE,$this->index,$prev);

    // everything after the last EndSec is content
    $contentidx = 0;
    for ($rawcontentidx; $rawcontentidx < $rawcontentcount; $rawcontentidx++)
      $this->content[$contentidx++] = $rawcontent[$rawcontentidx];

    // save timestamp information
    $this->created = $scanner->GetCreationTime();
    $this->lastedit = $scanner->GetLastEditTime();
    }

  // process a sub-index (recursive)
  private function ProcessSection($rawcontent,$rawcontentcount,&$rawcontentidx,$parent,&$dest,&$prev)
    {
    $indexcount = count($dest);

    $additioncounters = array(); // holds the counters for each element of $this->addmap

    for ($rawcontentidx; $rawcontentidx < $rawcontentcount; $rawcontentidx++)
      {
      $line = trim($rawcontent[$rawcontentidx]);
      if ($line === "") // ignore empty lines
        continue;

      $exploded = explode(INDEX_FILE_SEP,$line);
      if (count($exploded) === 3 && ($numtype = TIndexEntry::TypeFromString($exploded[0])) !== FALSE)
        {
        $ie = new TIndexEntry();
        $ie->type = $numtype;
        $ie->id = CompressSectionId($exploded[1]);
        $ie->addition = trim($exploded[2]);
        $ie->parent = $parent;

        $ie->prev = $prev;
        if ($prev !== FALSE) // "next" field of previous entry should point to this entry
          $prev->next = $ie;
        $prev = $ie;         // new previous entry is this entry

        if ($ie->type === TIndexEntry::TYPE_SECTION)
          {
          $rawcontentidx++; // point to next line
          $this->ProcessSection($rawcontent,$rawcontentcount,$rawcontentidx,$ie,$ie->childs,$prev);
          $rawcontentidx--; // this will be incremented by the for loop, anyway
          }

        $this->idmap[$ie->id] = $ie;
        $dest[$indexcount++] = $ie;
        }

      if (count($exploded) === 1 && (strtoupper(trim($exploded[0])) === TIndexEntry::COMMAND_END))
        {
        $rawcontentidx++;
        return;
        }
      }
    }

  // TRUE or FALSE
  public function IsValid()
    {
    return $this->dir !== "";
    }

  public function GetInfo()
    {
    return $this->info;
    }

  // returns a TIndexEntry object by id
  // returns FALSE if failed
  public function GetEntryById($id)
    {
    $compid = CompressSectionId($id);

    if (!isset($id) || !isset($this->idmap[$compid]))
      return FALSE;

    return $this->idmap[$compid];
    }

  public function GetIndexCount()
    {
    return count($this->index);
    }

  // array-like access: $n must be between 0 and GetIndexCount()-1
  // FALSE if failed
  public function GetIndexEntry($n)
    {
    if (!isset($n) || !isset($this->index[$n]))
      return FALSE;

    return $this->index[$n];
    }

  public function GetExtraContent()
    {
    return $this->content;
    }

  public function GetCreationTime()
    {
    return $this->created;
    }

  public function GetLastEditTime()
    {
    return $this->lastedit;
    }

  private $dir;
  private $section;

  private $index;  // array of TIndexEntry
  private $info;   // TSectionHeader object

  private $idmap;  // id map for fast association id->indexentry

  private $content; // everything between an Info and [EndInfo] directives

  private $created; // creation timestamp
  private $lastedit;// last edit timestamp
  }

// constructs a cached IndexParser
// WARNING: absolut path required
function IndexParserFactory($dir,$section)
  {
  if (!isset($dir) || !isset($section) || !is_string($section))
    return FALSE;

  static $cache = array();
  $cacheid = $dir.SYSTEM_PATH_SEP.SYSTEM_PATH_SEP.$section;

  if (isset($cache[$cacheid]))
    return $cache[$cacheid];

  $cache[$cacheid] = new TIndexParser($dir,$section);
  return $cache[$cacheid];
  }

?>
