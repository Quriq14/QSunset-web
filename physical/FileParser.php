<?php
require_once("element/defines.php");
require_once("element/secutils.php");
require_once("element/HeaderParameters.php");

class TFileParseInfo
  {
  public $type; 
  public $name;    // field name, empty if none, a string or a line number if type is CONTENT
  public $content; // field content
  public $section; // current section or subsection

  // for $type
  const ERROR      = -1;
  const EOF        = 0;
  const ID         = 1;
  const TITLE      = 2;
  const PARAM      = 3;
  const CONTENT    = 4;
  }

class TSectionHeader
  {
  public $id      = "";
  public $title   = "";
  public $params  = array();  // named parameters:   [key]=value
  public $uparams = array();  // unnamed parameters: [random_number]=value
  }

class TFileParser
  {
  function __construct($filename)
    {
    $this->handler = FALSE;
    $this->fileName = $filename;
    if (is_file($filename) && is_readable($filename))
      $this->handler = fopen($filename,"r");
    }

  public function IsValid()
    {
    return $this->handler !== FALSE;
    }

  public function __destruct()
    {
    if ($this->handler !== FALSE)
      fclose($this->handler);
    $this->handler = FALSE;
    }

  // returns an array of sections NAMES, empty if failed
  public function ScanSections($mode)
    {
    $result = array();
    $rescounter = 0;

    if (!$this->IsValid())
      return $result;

    $this->Reset();

    do
      {
      $info = $this->Scan();

      if ($info->type === TFileParseInfo::ID && ($info->level & $mode) !== 0)
        {
        $result[$rescounter] = $info->content;
        $rescounter++;
        }

      } while ($info->type !== TFileParseInfo::ERROR && $info->type !== TFileParseInfo::EOF);

    return $result;
    }

  // returns an array with a progressive index as key and an object of type TSectionHeader as value.
  public function ScanSectionHeaders($mode) 
    // mode may be:
    // a string: in this case, the returned array will (should) return only one result: the one with that id
    {
    $result = array();
    $paramcounter = 0;
    $rcount = -1; // result counter starts at -1, so ++$rcount starts at 0 (see below)

    $knownsections = array(); // contains the names of all found sections as key

    if (is_string($mode))
      $compressMode = CompressSectionId($mode);

    if (!$this->IsValid())
      return $result;

    $this->Reset();

    do
      {
      $info = $this->Scan();
      $id = CompressSectionId($info->section);

      if ($id === "")
        continue; // should never happen, but...

      if (is_string($mode) && ($id !== $compressMode))
        continue; // wrong section id

      if (!isset($knownsections[$id]))
        {
        $result[++$rcount] = new TSectionHeader();
        $knownsections[$id] = 1; // remember it was found
        $paramcounter = 0;       // reset param counter
        }

      switch ($info->type)
        {
        case TFileParseInfo::ID:
          $result[$rcount]->id = $info->content;
          break;
        case TFileParseInfo::TITLE:
          $result[$rcount]->title = $info->content;
          break;
        case TFileParseInfo::PARAM:
          if ($info->name !== "")
            $result[$rcount]->params[$info->name] = $info->content;        // named parameter
            else
              $result[$rcount]->uparams[$paramcounter++] = $info->content; // unnamed parameter
          break;
        default:
          break; // do nothing
        }

      } while ($info->type !== TFileParseInfo::ERROR && $info->type !== TFileParseInfo::EOF);

    return $result;
    }

  // $section may be: a section ID, a subsection ID, "" if file content outside sections
  // returns an array of strings, empty if failed
  public function GetContent($section)
    {
    if (!isset($section) || !$this->IsValid())
      return array();

    if (isset($this->contentCache[$section]))
      return $this->contentCache[$section]; // was cached

    if ($this->contentCache !== FALSE) // cache is already initialized, but the section is not found.
      return array();

    // Not cached and it may exist. We need to load it, then.
    $counters = array(); // number of lines for each section
    $this->contentCache = array();

    $this->Reset();

    do {
      $info = $this->Scan();

      if ($info->type === TFileParseInfo::CONTENT)
        {
        if (!isset($this->contentCache[$info->section])) // new section, create new cache entry
          {
          $this->contentCache[$info->section] = array();
          $counters[$info->section] = 0;
          }

        $this->contentCache[$info->section][$counters[$info->section]++] = $info->content; 
        }     

      } while ($info->type !== TFileParseInfo::ERROR && $info->type !== TFileParseInfo::EOF);

    if (!isset($this->contentCache[$section]))
      return array(); // requested section is not found

    return $this->contentCache[$section];
    }

  // TRUE or FALSE
  public function HasSection($section,$mode)
    {
    if (!$this->IsValid())
      return FALSE;

    $this->Reset();
    
    do
      {
      $info = $this->Scan();

      if ($info->type === TFileParseInfo::ID && ($info->level & $mode) !== 0 && $info->content === $section)
        return TRUE; // FOUND!

      } while ($info->type !== TFileParseInfo::ERROR && $info->type !== TFileParseInfo::EOF);

    return FALSE;
    }

  // returns an int (unix timestamp) or FALSE if failed
  public function GetCreationTime()
    {
    if (!$this->IsValid())
      return FALSE;

    return filectime($this->fileName); // this is not the true creation time
                                       // but creation time should be set by parameter in most cases
    }

  // returns an int (unix timestamp) or FALSE if failed
  public function GetLastEditTime()
    {
    if (!$this->IsValid())
      return FALSE;

    return filemtime($this->fileName); // returns FALSE if failed
    }

  // returns a TFileParseInfo object
  // with type EOF if ended
  // or type ERROR if error
  private function Scan()
    {
    $result = new TFileParseInfo();
    $result->name = "";
    $result->content = "";
    $result->type = TFileParseInfo::ERROR;
    $result->section = $this->csection;
    
    if (!$this->IsValid())
      return $result; // error

    while (($line = fgets($this->handler,FILE_MAX_LINE_LENGTH)) !== FALSE)
      {
      $line = str_replace("\n","",$line); // remove the separator (usually the last character, but it depends on the locale)
      $line = str_replace("\r","",$line); // this is not very efficient, but stream_get_line is not reliable

      if (substr($line,0,strlen(FILE_CONTENT_SEPARATOR)) === FILE_CONTENT_SEPARATOR)
        {
        $this->inHeaderArea = !$this->inHeaderArea;
        $this->linecounter = 0;
        continue;
        }

      if ($this->inHeaderArea)
        {
        $line = trim($line);

        if ($line === "")
          continue; // ignore empty lines in headers

        switch ($this->linecounter)
          {
          case 0: // ID
            $line = CompressSectionId($line);

            $result->type = TFileParseInfo::ID;
            $result->content = $line;
            
            $result->section = $this->csection = $line; // save last section name
            break;

          case 1: // TITLE
            $result->type = TFileParseInfo::TITLE;
            $result->content = $line;
            break;

          default: // generic parameter
            $result->name = "";
            $result->content = $line;
            $result->type = TFileParseInfo::PARAM;

            // parameters are in the form "name: content" or simply "content". Subdivide if first form.
            $spos = strpos($line,":");

            if ($spos !== FALSE && $spos > 0)
              {
              $result->name = trim(substr($line,0,$spos));
              $result->content = trim(substr($line,$spos+1));
              }

            break;
          }

        $this->linecounter++;
        return $result;
        }
        else
          {
          $result->type = TFileParseInfo::CONTENT;
          $result->content = $line;
          $result->name = $this->linecounter;

          $this->linecounter++;
          return $result;
          }
      }

    $result->type = TFileParseInfo::EOF; // if we're here, EOF reached
    return $result;
    }

  private function Reset()
    {
    $this->inHeaderArea = FALSE;
    $this->linecounter = 0;
    $this->csection = "";
    rewind($this->handler);
    }

  private $handler;
  private $fileName;

  private $inHeaderArea; // only Scan and Reset can access this
  private $linecounter;  // only Scan and Reset can access this
  private $csection;     // only Scan and Reset can access this

  private $contentCache = FALSE; // cache for the content: array(section_id => array(0 => line1, 1 => line2...), ...)
                                 // false when not initialized yet
  }

function FileParserFactory($absoluteFile)
  {
  static $cache = array();

  if (isset($cache[$absoluteFile]))
    return $cache[$absoluteFile];

  $maybeFile = new TFileParser($absoluteFile);

  if ($maybeFile->IsValid())
    $cache[$absoluteFile] = $maybeFile;

  return $maybeFile;
  }

?>
