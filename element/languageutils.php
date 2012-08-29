<?php
// include with require_once

require_once("content/ParseContent.php");

require_once("element/defines.php");

function IsLanguageInParam($string,$language) // to read Lang-orig and Lang-avail
  {
  if (!isset($string) || !isset($language))
    return FALSE;

  $exploded = explode(" ",$string);

  foreach ($exploded as $e)
    if ($e === $language)
      return TRUE;

  return FALSE;
  }

class TAutoLangResult
  {
  public $result = NLanguages::LANGUAGE_DEFAULT;
  public $reason = self::FAILED_DETECT;  

  const FAILED_DETECT   = 0; // detection failed
  const REQUESTED_LANG  = 1; // reason: was requested by user (with GET parameter)
  const ACCEPT_LANG     = 2; // reason: was inferred through Accept-Languages
  const NOT_AVAIL_LANG  = 3; // reason: language is not available, using original
  }

function IsLang($maybeLang)
  {
  if (!isset($maybeLang))
    return FALSE;

  foreach (NLanguages::$LANGUAGE_ARRAY as $l)
    if ($maybeLang === $l)
      return TRUE;

  return FALSE;
  }

// -1 if failed
// gets a language id to reference NLanguage::$LANGUAGE_ARRAY
function GetLangId($lang)
  {
  foreach (NLanguages::$LANGUAGE_ARRAY as $k => $l)
    if ($lang === $l)
      return $k;

  return -1;
  }

// warning: some magic here
function AutoLang($maybeLanguage,$availLanguages,$origLanguages)
  {
  $result = new TAutoLangResult();

  if (!isset($maybeLanguage) || !isset($availLanguages) || !isset($origLanguages))
    return $result;

  $singleoL = strstr($origLanguages," ",FALSE); // finds the first original language, to be returned on failure
  if ($singleoL === FALSE)
    $singleoL = $origLanguages;
  if ($singleoL === "")
    $singleoL = NLanguages::LANGUAGE_DEFAULT;

  // see if we can use the requested language
  if (is_string($maybeLanguage))
    {
    foreach (NLanguages::$LANGUAGE_ARRAY as $l)
      if ($l === $maybeLanguage)
        {
        if (!IsLanguageInParam($availLanguages,$l))
          {
          $result->reason = TAutoLangResult::NOT_AVAIL_LANG;
          $result->result = $singleoL;
          return $result;
          }

        $result->reason = TAutoLangResult::REQUESTED_LANG;
        $result->result = $l;
        return $result;
        }

    $result->reason = TAutoLangResult::NOT_AVAIL_LANG;
    $result->result = $singleoL;
    return $result;
    }

  // language not requested, autodetect it
  $headers = getallheaders();

  $languages = FALSE;

  foreach ($headers as $k => $v) // HTTP headers are case-insensitive
    if (strtoupper($k) === "ACCEPT-LANGUAGE")
      $languages = $v;

  if ($languages !== FALSE) // parse Accept-language
    {
    $singleLanguages = explode(",",$languages);

    $singleLanguagesCount = count($singleLanguages);
    for ($i = 0; $i < $singleLanguagesCount; $i++) // from the most preferred to the least
      foreach (NLanguages::$LANGUAGE_ARRAY as $l)
        if (IsLanguageInParam($availLanguages,$l) && substr($singleLanguages[$i],0,strlen($l)) === $l) // check if language is available and if it matches
          {
          $result->reason = TAutoLangResult::ACCEPT_LANG;
          $result->result = $l;
          return $result;
          }
    }

  $result->reason = TAutoLangResult::FAILED_DETECT;  
  $result->result = $singleoL;
  return $result;
  }

?>
