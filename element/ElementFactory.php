<?php
require_once("element/DirectoryData.php");
require_once("element/SectionData.php");
require_once("element/secutils.php");

// produces an invalid object if failed
// CHECK the created element with IsValid before use
// $string is the object path
// $physicalPath is the TPhysicalPath, if known, or FALSE
function ElementFactory($string,$physicalPath = FALSE)
  {
  static $cache = array();

  // if cached, return it
  if (isset($string) && isset($cache[$string]))
    return $cache[$string];

  $maybeElement = FALSE;

  $exploded = array();
  if (!is_null($string))
    $exploded = explode(PATH_SEP,$string);

  switch (count($exploded))
    {
    case 1: // one field: it's a directory
      $maybeElement = new TDirectoryData($exploded[0],$physicalPath);
      break;
    case 2: // two fields: it's a section
      $maybeElement = new TSectionData($exploded[0],$exploded[1],$physicalPath);
      break;
    default:
      $maybeElement = new TDirectoryData(); // this is an invalid directory
    }

  // cache it
  if (isset($string) && ($maybeElement !== FALSE))
    $cache[$string] = $maybeElement;
    
  return $maybeElement;
  }
?>
