<?php
// use only require/include_only for this file

require_once("content/defines.php");
require_once("content/ParserImpl.php");
require_once("content/ParserInfo.php");
require_once("content/Include.php");

require_once("element/defines.php");
require_once("element/ElementData.php");

require_once("physical/Cache.php");

abstract class NContentParser
  {
  static function Parse($info)
    {
    // if cache is enabled
    if ($info->cacheKey !== FALSE)
      {
      // generate cache keys
      $keycount = 0;
      $cacheKeys = array();

      // cache is indexed by element address, language and external key
      $cacheKeys[$keycount++] = $info->TopCurrentElement() !== FALSE ? $info->TopCurrentElement()->GetAddress() : "";
      $cacheKeys[$keycount++] = $info->language;
      $cacheKeys[$keycount++] = $info->cacheKey;

      $maybeobject = NPermanentCache::GetObject($cacheKeys,
        $info->TopCurrentElement() !== FALSE ? $info->TopCurrentElement()->GetLastEditTime() : FALSE);
      if ($maybeobject !== FALSE)
        return $maybeobject; // result already cached
      }

    // load the content from the element
    $info->content = NElementParts::GetPartOfElement($info->TopCurrentElement(),$info->TopCurrentPart());

    $chainDOM = NParserImpl::Parse($info);
    $result = "";
    for ($i = 0; $i < count($chainDOM); $i++)
      $result .= $chainDOM[$i]->Produce($info); // NOTE: if you don't want this processing, use NParserImpl::Parse instead

    $info->result = $result;

    if ($info->cacheKey !== FALSE)
      NPermanentCache::PutObject($cacheKeys,$result);

    return $result;
    }
  }
?>
