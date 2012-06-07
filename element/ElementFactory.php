<?php
require_once("element/DirectoryData.php");
require_once("element/SectionData.php");
require_once("element/secutils.php");

// produces an invalid object if failed
// set $maybeInfo to FALSE if not needed
// CHECK the created element with IsValid before use
function ElementFactory($string,$physicalPath = FALSE)
  {
  // if cached, return it
  if (isset($string))
    if (USE_APC)
      {
      $success = FALSE;
      $cached = apc_fetch(ELEMENT_CACHE_PREFIX.$string,$success);
    
      if ($success)
        return $cached;
      }
      else
        {
        static $cache = array();
  
        if (isset($cache[$string]))
        return $cache[$string];
        }

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
    if (USE_APC)
      apc_add(ELEMENT_CACHE_PREFIX.$string,$maybeElement);
      else
        $cache[$string] = $maybeElement;
    
  return $maybeElement;
  }
?>
