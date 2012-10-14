<?php
require_once("element/defines.php");
require_once("element/ElementData.php");

require_once("physical/FileParser.php");
require_once("physical/IndexParser.php");
require_once("physical/PhysicalPath.php");

define("DIRECTORY_ROOT_ID","<root>");

class TDirectoryData extends TElementData
  {
  // if $physicalPath is not provided, it will be obtained automatically
  public function __construct($path = NULL,$physicalPath = FALSE)
    {
    $this->path = $path;
    $this->info = FALSE;
    $this->physicalpath = new TPhysicalPath();
    
    if (!isset($path) || !isset($physicalPath))
      return;

    $path = NormalizePath($path);

    $exploded = explode(DIR_PATH_SEP,$path);
    $explodecount = count($exploded);
    if ($explodecount === 1 && $exploded[0] === "")
      $explodecount = 0; // this is root, no steps needed

    if ($physicalPath !== FALSE) // if physical path is provided, use it
      $cbpath = $physicalPath;
      else
        {
        // else build path and check each step
        $cbpath = new TPhysicalPath();

        for ($ec = 0; $ec < $explodecount; $ec++)
          {
          $directoryId = ($ec === 0) ? DIRECTORY_ROOT_ID : $exploded[$ec-1]; // handle special root cas

          $index = IndexParserFactory($cbpath->Get(),$directoryId);
          if (!$index->IsValid())
            return;

          $indexentry = $index->GetEntryById($exploded[$ec]);
          if ($indexentry === FALSE || $indexentry->type !== TIndexEntry::TYPE_SUBDIR)
            return; // there are still directories in path, but index doesn't contain them

          $cbpath = $cbpath->GetConcat($indexentry->addition);
          if ($cbpath === FALSE)
            return; // error
          }
      }

    if (!CheckDirectory(dirname($cbpath->Get())))
      return;

    $directoryId = ($explodecount === 0) ? DIRECTORY_ROOT_ID : $exploded[$explodecount-1];
    $index = IndexParserFactory($cbpath->Get(),$directoryId);
    if (!$index->IsValid())
      return;

    $this->info = $index->GetInfo();
    if ($this->info === FALSE)
      return;

    // everything OK
    $this->physicalpath = $cbpath;
    }

  public function GetType()
    {
    return TElementType::DIRECTORY;
    }

  public function GetPath()
    {
    return $this->path;
    }

  public function GetAddress()
    {
    return $this->path;
    }

  public function GetTitle()
    {
    if ($this->info !== FALSE)
      if (isset($this->info->title) && $this->info->title !== "")
        return $this->info->title;

    if ($this->IsRoot())
      return DIRECTORY_ROOT_ID;

    return basename($this->path);
    }

  public function IsRoot()
    {
    return $this->path === PATH_ROOT;
    }

  // returns an empty array if failed
  // $limitedTo may be TIndexEntry::TYPE_SUBDIR, TIndexEntry::TYPE_SECTION, TIndexEntry::TYPE_SUBSECTION
  //   or FALSE for no limit
  public function GetIndex($limitedTo = FALSE)
    {
    if (!$this->IsValid())
      return array();

    $index = IndexParserFactory($this->physicalpath->Get(),$this->info->id);
    if (!$index->IsValid())
      return array();

    $result = array();
    $resultidx = 0;

    $indexcount = $index->GetIndexCount();
    for ($i = 0; $i < $indexcount; $i++)
      { 
      $indexentry = $index->GetIndexEntry($i);
      if ($indexentry === FALSE)
        continue; // error

      if (($limitedTo !== FALSE) && $indexentry->type !== $limitedTo)
        continue; // type has been constrained
   
      switch ($indexentry->type)
        {
        case TIndexEntry::TYPE_SUBDIR:
          $buildPath = "";
          if ($this->path !== "") // root is represented by an empty string, not /
            $buildPath = $this->path.DIR_PATH_SEP;

          // build the directory starting from the physicalPath, since it's known
          $maybeDir = ElementFactory($buildPath.$indexentry->id,$this->physicalpath->GetConcat($indexentry->addition));

          if ($maybeDir->IsValid()) // do not include if invalid
            $result[$resultidx++] = $maybeDir;
          break;

        case TIndexEntry::TYPE_SECTION:
        case TIndexEntry::TYPE_SUBSECTION:
          $maybeSection = ElementFactory($this->path.PATH_SEP.$indexentry->id,$this->physicalpath->GetConcat($indexentry->addition));
          if ($maybeSection->IsValid())
            $result[$resultidx++] = $maybeSection;

        default:
          break;
        }
      }
    
    return $result;
    }

  public function GetChildSections()
    {
    return $this->GetIndex(TIndexEntry::TYPE_SECTION);
    }

  // returns false if root
  public function GetParent()
    {
    if ($this->IsRoot())
      return FALSE;

    // directory alias
    $pName = dirname($this->path);
    if ($pName === "." || $pName === DIR_PATH_SEP) // dirname returns "." if no slash is present
      $pName = "";

    // directory physical name
    $phPName = $this->physicalpath->GetParent();
    /*if ($phPName === FALSE)
      $phPName = FALSE;*/ // already done :)
    
    return ElementFactory($pName,$phPName);
    }

  public function IsValid()
    {
    return $this->info !== FALSE && $this->physicalpath !== FALSE;
    }

  public function HasIndex()
    {
    return TRUE;
    }

  public function HasContent()
    {
    return TRUE;
    }

  public function GetContent() // a string
    {
    if (!$this->IsValid())
      return "";

    $index = IndexParserFactory($this->physicalpath->Get(),$this->info->id);
    if (!$index->IsValid())
      return "";

    return $index->GetExtraContent();
    }

  public function GetInfo()
    {
    return $this->info;
    }

  public function GetPhysicalPath() // WARNING: never, NEVER, N-E-V-E-R show this to users!
    // returns the object
    {
    return $this->physicalpath;
    }

  // address of next object (FALSE if none)
  public function GetNextAddress()
    {
    if (!$this->IsValid())
      return FALSE;

    // else, get the first child section from the index
    $index = IndexParserFactory($this->physicalpath->Get(),$this->info->id);
    if (!$index->IsValid())
      return FALSE;

    $indexcount = $index->GetIndexCount();
    for ($i = 0;$i < $indexcount; $i++)
      {
      $indexentry = $index->GetIndexEntry($i);
      if ($indexentry === FALSE)
        continue;

      switch ($indexentry->type)
        {
        case TIndexEntry::TYPE_SECTION:
        case TIndexEntry::TYPE_SUBSECTION:
          return $this->path.PATH_SEP.$indexentry->id;
        case TIndexEntry::TYPE_SUBDIR:
          return $this->path.DIR_PATH_SEP.$indexentry->id;
        }  
      }

    return FALSE;
    }

  public function GetPrevAddress() // previous address of a directory is not defined for now
    {
    return FALSE;
    }

  public function HasParam($name)
    {
    if (!isset($name))
      return FALSE;

    if (!$this->IsValid())
      return FALSE;

    return isset($this->info->params[$name]);
    }

  public function GetParam($name)
    {
    if ($this->HasParam($name))
      return $this->info->params[$name];

    return "";
    }

  protected function GetLastEditTimePhys()
    {
    if (!$this->IsValid())
      return FALSE;

    $index = IndexParserFactory($this->physicalpath->Get(),$this->info->id);
    if (!$index->IsValid())
      return FALSE;

    return $index->GetLastEditTime();
    }

  protected function GetCreationTimePhys()
    {
    if (!$this->IsValid())
      return FALSE;

    $index = IndexParserFactory($this->physicalpath->Get(),$this->info->id);
    if (!$index->IsValid())
      return FALSE;

    return $index->GetCreationTime();
    }

  private $path;
  private $physicalpath;
  private $info;
  }

require_once("element/SectionData.php");
?>
