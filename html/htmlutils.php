<?php

require_once("content/ParseContent.php");

define("PARAGRAPH_BEGIN","<p>\r\n");
define("PARAGRAPH_END","</p>\r\n");
define("LINEBREAK","<br />\r\n");
define("ANCHOR_END","</a>");

function BuildNearHref($path,$language)
  {
  return "/index.php?".PATH_FIELD."=".urlencode($path)."&".LANGUAGE_FIELD."=".urlencode($language);
  }

function AHrefBegin($class,$path,$language)
  {
  if (!isset($path) || !isset($class) || !isset($language))
    return "";

  return "<a class=\"".$class."\" href=\"".htmlspecialchars(BuildNearHref($path,$language))."\">";
  }

// without all these explicit parameters htmlentities does not really work
function TrueHtmlEntities($utf8string)
  {
  return htmlentities($utf8string,ENT_COMPAT,'UTF-8',TRUE);
  }

?>
