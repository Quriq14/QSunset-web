<?php

require_once("content/formats/BoldItalicUnderline.php");
require_once("content/formats/Paragraph.php");
require_once("content/formats/Hidden.php");
require_once("content/formats/Comment.php");
require_once("content/formats/Language.php");
require_once("content/formats/Write.php");
require_once("content/formats/Ref.php");
require_once("content/formats/Html.php");
require_once("content/formats/Snippet.php");
require_once("content/formats/HorizontalLine.php");
require_once("content/formats/List.php");
require_once("content/formats/Image.php");
require_once("content/formats/Jump.php");
require_once("content/formats/DisplayIf.php");
require_once("content/formats/TerminateIf.php");
require_once("content/formats/Datetime.php");
require_once("content/formats/Box.php");

function FormatFactory($name)
  {
  if (!isset($name) || !is_string($name) || $name === "")
    return FALSE;

  static $status = array();  // cache the formats (they MUST be all stateless)

  if (isset($status[$name]))
    {
    return $status[$name];
    }

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
    case PARAMETER_WRITE_CHARS:
      return new TCharWriterFormat();
    case PARAMETER_REF:
      return new TRefFormat();
    case PARAMETER_RELATIVE_REF:
      return new TRelativeRefFormat();
    case PARAMETER_FAR_REF:
      return new TFarRefFormat();
    case PARAMETER_HTML:
      return new THtmlFormat();
    case PARAMETER_SNIPPET:
      return new TSnippetFormat();
    case PARAMETER_HORIZONTAL_LINE:
      return new THorizontalLineFormat();
    case PARAMETER_LIST:
      return new TListFormat();
    case PARAMETER_LISTITEM:
      return new TListItemFormat();
    case PARAMETER_OLISTCLASS:
      return new TListClassFormat(PARAMETER_OLISTCLASS);
    case PARAMETER_ULISTCLASS:
      return new TListClassFormat(PARAMETER_ULISTCLASS);
    case PARAMETER_IMAGE:
      return new TImageFormat(FALSE);
    case PARAMETER_IMAGE_FAR:
      return new TImageFormat(TRUE);
    case PARAMETER_JUMP:
      return new TJumpFormat();
    case PARAMETER_DISPLAYIF:
      return new TDisplayIfFormat();
    case PARAMETER_TERMINATEIF:
      return new TTerminateIfFormat();
    case PARAMETER_DATETIME:
      return new TDateTimeFormat();
    case PARAMETER_BOX:
      return new TBoxFormat();
    default:
      return FALSE;
    }
  }

?>
