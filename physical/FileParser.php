<?php
require_once("element/defines.php");
require_once("element/secutils.php");
require_once("element/HeaderParameters.php");

class TSectionInfo
  {
  public $id      = "";
  public $title   = "";
  public $params  = array();  // named parameters:   [key]=value
  public $uparams = array();  // unnamed parameters: [random_number]=value
  public $content = FALSE;    // the content in string format, FALSE if not yet cached
  public $contentArray = array(); // the content in array format
  }

class TFileParser
  {
  function __construct($filename)
    {
    $this->fileName = $filename;
    if (!is_file($filename) || !is_readable($filename))
      return;

    $handler = fopen($filename,"r");
    if ($handler === FALSE)
      return;

    $this->Load($handler);

    fclose($handler);
    }

  public function IsValid()
    {
    return $this->cache !== FALSE;
    }

  // returns an array with a progressive index as key and an object of type TSectionInfo as value.
  // if $search is specified, only the section named $search will be returned (if exists)
  public function GetSectionHeaders($search = FALSE)
    {
    if (!$this->IsValid())
      return array();

    if (is_string($search))
      {
      $id = CompressSectionId($search);
      if (isset($this->cache[$id]))
        return array(0 => $this->cache[$id]);
      return array();
      }

    $result = array();
    $resultcount = 0;
    foreach ($this->cache as $c)
      $result[$resultcount++] = $c;

    return $result;
    }

  // $section is the section id
  // returns a string, empty if failed
  public function GetContent($section)
    {
    if (!$this->IsValid())
      return "";

    if (!isset($this->cache[$section]))
      return ""; // not found

    if ($this->cache[$section]->content === FALSE)
      // if not cached yet, build it from contentArray
      $this->cache[$section]->content = implode(CHAR_INTERNAL_LINEBREAK,
        $this->cache[$section]->contentArray);

    return $this->cache[$section]->content;
    }

  public function GetContentArray($section)
    {
    if (!$this->IsValid())
      return array();

    if (!isset($this->cache[$section]))
      return array(); // not found

    return $this->cache[$section]->contentArray;
    }

  // TRUE or FALSE
  public function HasSection($section)
    {
    if (!$this->IsValid())
      return FALSE;

    return isset($this->cache[$section]);
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

  const CONTENT_SEPARATOR = "----------------------------------------";
  const MAX_LINE_LENGTH   = 1024;

  const COMMENT_PREFIX = "::FP_COMMENT::"; // when parsing, lines starting with ::FP_COMMENT:: will be completely ignored
  const WRITE_PREFIX   = "::FP_WRITE::";   // ::FP_WRITE:: at the beginning of a line will be removed
    /* example: ::FP_WRITE::---------------------------------------- 
                will become
                ----------------------------------------
                and won't count as content separator line */

  private function Load($handler)
    {
    $csection = "";
    $linecounter = 0;
    $inHeaderArea = FALSE;

    $this->cache = array();

    while (($line = fgets($handler,self::MAX_LINE_LENGTH)) !== FALSE)
      {
      $line = rtrim($line,"\n\r"); // remove the separator

      if (substr($line,0,strlen(self::CONTENT_SEPARATOR)) === self::CONTENT_SEPARATOR)
        {
        $inHeaderArea = !$inHeaderArea;
        $linecounter = 0;
        continue;
        }

      if (substr($line,0,strlen(self::COMMENT_PREFIX)) === self::COMMENT_PREFIX)
        continue; // ignore the comments

      if (substr($line,0,strlen(self::WRITE_PREFIX)) === self::WRITE_PREFIX)
        {
        // remove the write prefix if provided
        $line = substr($line,strlen(self::WRITE_PREFIX));
        if (!is_string($line))
          $line = ""; // ignore errors of substr
        }

      if ($inHeaderArea)
        {
        $line = trim($line);

        if ($line === "")
          continue; // ignore empty lines in headers

        switch ($linecounter)
          {
          case 0: // ID
            $csection = CompressSectionId($line); // new current section

            if (!isset($this->cache[$csection]))
              $this->cache[$csection] = new TSectionInfo();

            $this->cache[$csection]->id = $csection;
            break;

          case 1: // TITLE
            $this->cache[$csection]->title = $line;
            break;

          default: // generic parameter

            // parameters are in the form "name: content" or simply "content". Subdivide if first form.
            $spos = strpos($line,":");

            if ($spos !== FALSE && $spos > 0)
              $this->cache[$csection]->params[trim(substr($line,0,$spos))] = trim(substr($line,$spos+1));
              else
                $this->cache[$csectiond]->uparams[count($this->cache[$csection]->uparams)] = 
                  trim(substr($line,$spos+1));

            break;
          }

        $linecounter++;
        }
        else
          if (isset($this->cache[$csection]))
            $this->cache[$csection]->contentArray[$linecounter++] = $line;
      }
    }

  private $fileName;

  private $cache = FALSE;
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
