<?php

require_once("content/BoldItalicUnderline.php");
require_once("content/Paragraph.php");
require_once("content/Hidden.php");
require_once("content/Comment.php");
require_once("content/Language.php");

// FALSE if failed
// get or create an element
function GetFormatByName($name)
  {
  if (!isset($name) || $name === "")
    return FALSE;

  static $status = array(); // cache the formats (they MUST be all stateless)

  if (!isset($status[$name]))
    {
    $newobj = FormatFactory($name);
    if ($newobj === FALSE)
      return FALSE;

    $status[$name] = $newobj;
    }

  return $status[$name];
  }

function FormatFactory($name)
  {
  if (!isset($name) || !is_string($name) || $name === "")
    return FALSE;

  switch (strtoupper($name))
    {
    case PARAMETER_BOLD:
      return new TBoldFormat();
    case PARAMETER_ITALIC:
      return new TItalicFormat();
    case PARAMETER_UNDERLINE:
      return new TUnderlineFormat();
    case PARAMETER_LINEBREAK:
      return new TLineBreakFormat();
    case PARAMETER_HIDDEN:
      return new THiddenFormat();
    case PARAMETER_COMMENT:
      return new TCommentScriptFormat();
    case PARAMETER_LANGUAGE:
      return new TLanguageFormat();
    default:
      return FALSE;
    }
  }

?>
