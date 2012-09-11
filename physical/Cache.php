<?php

require_once("element/defines.php");

// this cache is saved on disk
class NPermanentCache
  {
  const PREFIX = SYSTEM_CACHE_ROOT;
  const SUFFIX = ".txt";

  const CACHE_ENABLED = TRUE;

  static private function KeysToArray($keys)
    {
    $result = array();

    $keyscount = count($keys);
    for ($i = 0; $i < $keyscount; $i++)
      $result[$i] = md5($keys[$i]);

    return $result;
    }

  // FALSE if failed, TRUE otherwise
  // $object must be a string
  static public function PutObject($keys,$object)
    {
    if (count($keys) === 0)
      return FALSE;

    if (!self::CACHE_ENABLED)
      return TRUE; // success: all the available caches are updated

    $k = self::KeysToArray($keys);
    $kdircount = count($k) - 1; // only the first n-1 keys will be translated into directories

    $prefix = self::PREFIX;

    // go through directories
    for ($i = 0; $i < $kdircount; $i++)
      {
      $prefix .= SYSTEM_PATH_SEP.$k[$i];
      if (!is_dir($prefix))
        if (!mkdir($prefix))
          return FALSE; // error
      $prefix .= SYSTEM_PATH_SEP;
      }

    // write the file
    $filename = $prefix.SYSTEM_PATH_SEP.$k[$kdircount].self::SUFFIX;
    return file_put_contents($filename,$object);
    }

  // FALSE if failed, a string otherwise
  // $keys is an array of strings
  // $ifnotbefore must be a timestamp (or FALSE if not required). 
  //   FALSE will be returned if the content was cached before the $ifnotbefore
  static public function GetObject($keys,$ifnotbefore = FALSE)
    {
    if (count($keys) === 0)
      return FALSE;

    if (!self::CACHE_ENABLED)
      return FALSE;

    $k = self::KeysToArray($keys);
    $kdircount = count($k) - 1;

    $prefix = self::PREFIX;

    for ($i = 0; $i < $kdircount; $i++)
      {
      $prefix .= SYSTEM_PATH_SEP.$k[$i];
      if (!is_dir($prefix))
        return FALSE; // not found
      $prefix .= SYSTEM_PATH_SEP;
      }

    // read the file
    $filename = $prefix.SYSTEM_PATH_SEP.$k[$kdircount].self::SUFFIX;
    if (!is_file($filename))
      return FALSE; // file not found
    if ($ifnotbefore !== FALSE)
      {
      clearstatcache();
      if (filemtime($filename) < $ifnotbefore)
        return FALSE; // cache too old
      }
    return file_get_contents($filename);
    }
  }

?>
