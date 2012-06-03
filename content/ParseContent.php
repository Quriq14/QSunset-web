<?php
// use only require/include_only for this file

require_once("content/defines.php");
require_once("content/ParserImpl.php");
require_once("content/ParserInfo.php");

require_once("element/defines.php");

define("CONTENT_PARSER_MAX_DEPTH",20);

class NContentParser
  {
  static function Parse($content,$info)
    {
    if (!isset($content) || !isset($info))
      return ""; // invalid

    $info->content = $content; // TODO: this would be unnecessary in a perfect world

    $chainDOM = NParserImpl::Parse($info);
    $result = "";
    for ($i = 0; $i < count($chainDOM); $i++)
      $result .= $chainDOM[$i]->Produce($info); // TODO: a way to disable this?

    $info->result = $result;

    return $result;
    }

  static function ParseArray($carray,$info)
    {
    $carraycount = count($carray);
    $content = "";

    if ($carraycount > 0)
      {
      for ($i = 0; $i < ($carraycount-1); $i++)
        $content .= $carray[$i]."\n";
      $content .= $carray[$i]; // do not add \n at the end of the content
      }

    return self::Parse($content,$info);
    }

  static private $infoStackCount = 0;
  static private $infoStack = array();
  }
?>
