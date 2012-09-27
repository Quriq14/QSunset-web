<?php

class NFormatFactory
  {
  public static function Register($formatstatus)
    {
    $name = $formatstatus->GetName();
    self::$formats[$name] = $formatstatus;
    self::$nameset[$name] = TRUE;
    }

  // FALSE if not existing
  public static function GetByName($name)
    {
    if ($name === "")
      return FALSE;

    if (!isset(self::$formats[$name]))
      return FALSE;

    return self::$formats[$name];
    }

  // returns a set format_names => TRUE
  public static function GetNameSet()
    {
    return self::$nameset;
    }

  private static $nameset = array(); // is a format registered? name => TRUE
  private static $formats = array(); // registered formats: name => TFormatStatus
  }

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
require_once("content/formats/Textsize.php");
require_once("content/formats/Table.php");

?>
