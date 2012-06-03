<?php
// use require_once to include this file

require_once("element/defines.php");

// a representation of an array of physical paths
class TPhysicalPath
  {
  public function __construct($paths = FALSE) 
    {
    $this->paths = array(0 => SYSTEM_FILE_ROOT); // default to root

    if (!is_array($paths))
      return; // must be an array

    $this->paths = $paths;
    }

  // returns false if failed
  public function GetParent()
    {
    $pathcount = count($this->paths);
    
    if ($pathcount <= 1)
      return FALSE; // there is no parent

    $pathcount--; // the parent has one path less

    $result = array();
    for ($i = 0; $i < $pathcount; $i++)
      $result[$i] = $this->paths[$i];

    return new TPhysicalPath($result);
    }

  public function Get() // gets current physical path (a string)
    {
    $pathcount = count($this->paths);

    return $this->paths[$pathcount-1];
    }

  public function GetConcat($newPath) // creates a new TPhysicalPath with one more path and returns it
    // $newPath must be a string in one of the following formats:
    // path (absolute path)
    // :path (absolute path)
    // +path (relative path)
    // ~path (subst last directory or file)
    // -path (remove last directory or file and call recursive)
    //   example: -~path removes the last element and substitutes the new last element with path
    {
    if (!is_string($newPath) || $newPath === "")
      return FALSE;

    $pathcount = count($this->paths);

    $npath = $newPath;                     // path addition command
    $oripath = $this->paths[$pathcount-1]; // original path
    $complete = FALSE;

    while (!$complete)
      {
      switch ($npath[0])
        {
        case self::RELATIVE_PREFIX:
          $npath = substr($npath,1);
          $npath = $oripath.SYSTEM_PATH_SEP.$npath;
          $complete = TRUE;
          break;

        case self::ABSOLUTE_PREFIX:
          $npath = substr($npath,1);
          $npath = SYSTEM_PATH_ROOT.SYSTEM_PATH_SEP.$npath;
          $complete = TRUE;
          break;

        case self::SUBST_PREFIX:
          $npath = substr($npath,1);
          $oripath = dirname($oripath);
          $npath = $oripath.SYSTEM_PATH_SEP.$npath;
          $complete = TRUE;
          break;

        case self::REMOVE_PREFIX:
          $npath = substr($npath,1);
          $oripath = dirname($oripath); // remove last element and run another cycle
          break;

        case self::NOP_PREFIX:
          $npath = $oripath;
          $complete = TRUE;
          break;

        default: // default to absolute, but do not remove the first letter
          $npath = SYSTEM_PATH_ROOT.SYSTEM_PATH_SEP.$npath;
          $complete = TRUE;
          break;
        }
      }

    $result = array();
    for ($i = 0; $i < $pathcount; $i++)
      $result[$i] = $this->paths[$i];

    $result[$i] = $npath;

    return new TPhysicalPath($result);
    }

  private $paths; // path is an array of physical paths, each is a complete path (starting with ./...)

  const RELATIVE_PREFIX = "+"; // +path means "add to current"
  const ABSOLUTE_PREFIX = ":"; // :path means "absolute path"
  const SUBST_PREFIX    = "~"; // ~path means "substitute last file or folder"
  const REMOVE_PREFIX   = "-"; // -path removes the first part and re-runs GetConcat
  const NOP_PREFIX      = "."; // means "same file"
  }


?>
