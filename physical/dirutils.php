<?php
// use ONLY require/include_once for this file

require_once("element/defines.php");

// returns TRUE if the directory is readable by the application, else FALSE
function CheckDirectory($dirName)
  {
  if (is_null($dirName) || !is_string($dirName) || !is_dir($dirName))
    return FALSE;

  $edirName = realpath($dirName); // canonicalize path
  if ($edirName === FALSE)
    return FALSE;

  $rdirName = realpath(SYSTEM_PATH_ROOT); // canonicalize root path
  if ($rdirName === FALSE)
    return FALSE;

  $volumeRoot = realpath(SYSTEM_PATH_SEP); // get volume root
  if ($volumeRoot === FALSE)
    return FALSE;

  $i = 0; // iteration counter for safety (no infinite loop here)

  while ($edirName !== "." && $edirName !== "" && $edirName !== $volumeRoot && isset($edirName))
    {
    if ($edirName === $rdirName) // root found in the path, path is valid
      return TRUE;

    $edirName = dirname($edirName); // analyze parent

    $i++;
    if ($i >= MAX_PATH_DEPTH) // safety check: prevent loop
      return FALSE;
    }

  return FALSE; // reached / and no root found, invalid
  }

// removes duplicated SYSTEM_PATH_SEP
function NormalizePath($dir)
  {
  $dir2 = $dir;
  
  $replcount = 1;
  while ($replcount !== 0)
    $dir2 = str_replace(DIR_PATH_SEP.DIR_PATH_SEP,DIR_PATH_SEP,$dir2,$replcount);

  return $dir2;
  }
?>
