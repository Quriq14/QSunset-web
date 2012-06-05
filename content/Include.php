<?php

// include a file <filename>

require_once("element/ElementFactory.php");

require_once("content/ParserImpl.php");

class NInclude
  {
  static public function DoInclude($info,$path)
    {
    if (self::$recursive >= self::MAX_RECURSIVE)
      return; // recursion depth exceeded
    self::$recursive++;

    $element = ElementFactory($path);
    if ($element->IsValid() && $element->HasContent())
      {
      $content = $element->GetContent();

      $oldContent = $info->content;     // TODO: use PUSH and POP here?
      $oldProcessed = $info->processed;
      
      $info->processed = 0;
      $info->content = NContentParser::ArrayToString($content);
      
      NParserImpl::Parse($info);

      $info->processed = $oldProcessed;
      $info->content = $oldContent;
      }

    self::$recursive--;
    }  


  const MAX_RECURSIVE = 20; // recursion safety check
  private static $recursive = 0;
  }

?>
