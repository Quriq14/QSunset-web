<?php

require_once("element/defines.php");
require_once("element/secutils.php");
require_once("element/DirectoryData.php");

require_once("physical/FileParser.php");
require_once("physical/IndexParser.php");
require_once("physical/dirutils.php");

class TSectionData extends TElementData
  {
  // all other parameters are required
  public function __construct($path = NULL,$section = NULL,$physicalPath = FALSE)
    {
    $this->sinfo = FALSE;
    $this->section = "";
    $this->sPhysicalPath = FALSE;
    $this->myDir = FALSE;
    $this->childs = FALSE;
    $this->nextAddr = FALSE;
    $this->prevAddr = FALSE;
    $this->myIndexEntry = FALSE;

    if (!isset($path) || !isset($section) || !isset($physicalPath))
      return; // null input

    $myDir = ElementFactory($path,($physicalPath !== FALSE) ? $physicalPath->GetParent() : FALSE);

    if (!$myDir->IsValid() || $myDir->GetAddress() !== $path)
      return; // directory lookup failure 
    
    // now, we've 3 possibilities
    if ($physicalPath !== FALSE)          // 1) physical path has been provided
      $physical = $physicalPath;
    else                                  // 2) directory found out the physical path by itself
      {
      $physical = $myDir->GetPhysicalPath();

      $index = IndexParserFactory($physical->Get(),$myDir->GetInfo()->id); // get parent directory index
      if (!$index->IsValid())
        return;

      $sectentry = $index->GetEntryById($section);
      if ($sectentry === FALSE)
        return;

      if ($sectentry->type !== TIndexEntry::TYPE_SECTION && $sectentry->type !== TIndexEntry::TYPE_SUBSECTION
        && $sectentry->type !== TIndexEntry::TYPE_INFO)
        return; // wrong type

      $physical = $physical->GetConcat($sectentry->addition); // navigate to the section
      if ($physical === FALSE)
        return;                            // 3) the section does not exists
      }   

    // get header
    $scanner = FileParserFactory($physical->Get());
    if (!$scanner->IsValid())
      return;

    $headers = $scanner->GetSectionHeaders($section);
    if (count($headers) < 1)
      return;

    $this->sinfo = $headers[0]; // got info
    $this->section = $section;  // save section name
    $this->sPhysicalPath = $physical; // save physical path
    $this->myDir = $myDir;      // save my parent directory
    }

  public function GetType()
    {
    return TElementType::SECTION;
    }

  public function GetSection()
    {
    if (!$this->IsValid())
      return "";

    return $this->section;
    }

  public function GetAddress()
    {
    if (!$this->IsValid())
      return "";

    return $this->myDir->GetAddress().PATH_SEP.$this->section;
    }

  public function GetTitle()
    {
    if (!$this->IsValid())
      return "";

    if (isset($this->sinfo->title) && $this->sinfo->title !== "") // get title if can
      return $this->sinfo->title;

    return $this->section;
    }

  // returns a TSectionInfo object, FALSE if invalid
  public function GetInfo()
    {
    if (!$this->IsValid())
      return FALSE;

    return $this->sinfo;
    }

  public function IsRoot()
    {
    return FALSE; // a section is never root
    }

  public function HasIndex()
    {
    return $this->IsValid();
    }

  private function GetPrevNextAddressImpl($prevOrNotNext)
    {
    if (!$this->IsValid())
      return FALSE;

    // get the previous section from the directory index
    $myIndexEntry = $this->GetIndexEntry();
    if ($myIndexEntry === FALSE)
      return FALSE;

    $myIndexEntryPN = $prevOrNotNext ? $myIndexEntry->prev : $myIndexEntry->next;
    if ($myIndexEntryPN === FALSE)
      return FALSE;

    switch ($myIndexEntryPN->type)
      {
      case TIndexEntry::TYPE_SECTION:
      case TIndexEntry::TYPE_SUBSECTION:
        return $this->myDir->GetAddress().PATH_SEP.$myIndexEntryPN->id;
      case TIndexEntry::TYPE_SUBDIR:
        return $this->myDir->GetAddress().DIR_PATH_SEP.$myIndexEntryPN->id;
      }

    return FALSE;
    }

  public function GetPrevAddress()
    {
    return $this->GetPrevNextAddressImpl(TRUE);
    }

  public function GetNextAddress()
    {
    return $this->GetPrevNextAddressImpl(FALSE);
    }

  // returns false if root
  public function GetParent()
    {
    return $this->myDir; 
      // build a file or a section element with this name
    }

  // empty if failed
  public function GetContent()
    {    
    if (!$this->IsValid())
      return "";

    $scanner = FileParserFactory($this->sPhysicalPath->Get());
    if (!$scanner->IsValid())
      return "";

    return $scanner->GetContent($this->section);
    }

  public function IsValid()
    {
    return $this->section !== "" && $this->sinfo !== FALSE && $this->myDir !== FALSE && $this->sPhysicalPath !== FALSE;
    }

  public function HasContent()
    {
    return $this->IsValid();
    }

  public function HasParam($name)
    {
    if (!isset($name))
      return FALSE;

    if (!$this->IsValid())
      return FALSE;

    return isset($this->sinfo->params[$name]);
    }

  public function GetParam($name)
    {
    if ($this->HasParam($name))
      return $this->sinfo->params[$name];

    return "";
    }

  public function GetIndex()
    {
    if (!$this->IsValid())
      return array();

    return $this->myDir->GetIndex(); // get the index from the directory
    }

  public function GetChildSections()
    {
    if (is_array($this->childs))
      return $this->childs; // return cached version

    $this->childs = array();

    // not cached: load it
    $sectentry = $this->GetIndexEntry();
    if ($sectentry === FALSE)
      return array();   

    $childcount = 0;
    foreach ($sectentry->childs as $childentry)
      {
      $maybeChild = ElementFactory($this->myDir->GetPath().PATH_SEP.$childentry->id,
        $this->myDir->GetPhysicalPath()->GetConcat($childentry->addition));

      if ($maybeChild->IsValid())
        $this->childs[$childcount++] = $maybeChild;
      }

    return $this->childs;
    }

  // FALSE if failed
  private function GetIndexEntry()
    {
    if ($this->myIndexEntry !== FALSE)
      return $this->myIndexEntry; // cached: return it

    $index = IndexParserFactory($this->myDir->GetPhysicalPath()->Get(),$this->myDir->GetInfo()->id); // get parent directory index
    if (!$index->IsValid())
      return FALSE;

    $sectentry = $index->GetEntryById($this->section);
    if ($sectentry === FALSE)
      return FALSE;

    $this->myIndexEntry = $sectentry; // update cache
    return $sectentry;
    }

  protected function GetLastEditTimePhys()
    {
    if (!$this->IsValid())
      return FALSE;

    $scanner = FileParserFactory($this->sPhysicalPath->Get());
    if (!$scanner->IsValid())
      return FALSE;

    return $scanner->GetLastEditTime();
    }

  protected function GetCreationTimePhys()
    {
    if (!$this->IsValid())
      return FALSE;

    $scanner = FileParserFactory($this->sPhysicalPath->Get());
    if (!$scanner->IsValid())
      return FALSE;

    return $scanner->GetCreationTime();
    }

  private $section; // a string or "" if invalid
  private $sinfo;    // a TSectionHeader object or FALSE if invalid

  private $sPhysicalPath; // a physical path or FALSE if invalid

  private $myDir;  // pointer to the parent directory
  private $childs; // array of child sections (FALSE if "not loaded yet")

  private $myIndexEntry; // index entry form the directory element. FALSE if not loaded yet
  }
?>
