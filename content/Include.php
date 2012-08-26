<?php

// include a file <filename>

require_once("element/ElementFactory.php");

require_once("content/ParserImpl.php");
require_once("content/ParseError.php");

class NInclude
  {
  static public function DoInclude($info,$path)
    {
    // check recursion depth
    if (count($info->cElementStack) >= self::MAX_RECURSIVE)
      {
      NParseError::Error($info,NParseError::FATAL,NParseError::INCLUDE_DEPTH_EXCEEDED,
        array(0 => $path,1 => ((string)(self::MAX_RECURSIVE))));
      $info->AbortRequest();
      return; // recursion depth exceeded
      }

    $element = ElementFactory($path);
    if ($element->IsValid() && $element->HasContent())
      {
      $content = $element->GetContent();

      $oldContent = $info->content;     // TODO: use PUSH and POP here?
      $oldProcessed = $info->processed;
      
      $info->processed = 0;
      $info->content = NContentParser::ArrayToString($content);
      $info->PushCurrentElement($element);
      
      NParserImpl::Parse($info);

      $info->processed = $oldProcessed;
      $info->content = $oldContent;
      $info->PopCurrentElement();
      }
      else
        NParseError::Error($info,NParseError::ERROR,NParseError::INCLUDE_NOT_FOUND,array(0 => $path));
    }  


  const MAX_RECURSIVE = 20; // recursion safety check
  }

?>
