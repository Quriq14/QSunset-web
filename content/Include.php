<?php

// include a file <filename>

require_once("element/ElementFactory.php");

require_once("content/ParserImpl.php");
require_once("content/ParseError.php");

class NInclude
  {
  // parts of an element that can be included
  const TITLE     = "TITLE";
  const SUBTITLE  = "SUBTITLE";
  const CONTENT   = "CONTENT";
  const HEADER    = "HEADER";
  const FOOTER    = "FOOTER";

  // $info is a TContentParseInfo
  // $path is the Address of the element to include
  // $part is the part of the element (CONTENT by default)
  static public function DoInclude($info,$path,$part = self::CONTENT)
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
    if (!$element->IsValid())
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::INCLUDE_NOT_FOUND,array(0 => $path));
      return;
      }

    $content = self::GetPartOfElement($element,$part);
    if ($content === FALSE)
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::INCLUDE_UNKNOWN_PART,array(0 => $part));
      return;
      }

    $oldContent = $info->content;     // TODO: use PUSH and POP here?
    $oldProcessed = $info->processed;
      
    $info->processed = 0;
    $info->content = $content;
    $info->PushCurrentElement($element);
      
    NParserImpl::Parse($info);

    $info->processed = $oldProcessed;
    $info->content = $oldContent;
    $info->PopCurrentElement();
    }

  // returns a string or FALSE if failed
  static private function GetPartOfElement($element,$part)
    {
    switch (strtoupper($part))
      {
      case self::TITLE:
        return $element->GetTitle();
      case self::SUBTITLE:
        return $element->GetSubTitle();
      case self::CONTENT:
        return $element->GetContent();
      case self::FOOTER:
        return $element->GetFooter();
      case self::HEADER:
        return $element->GetHeaderTitle();
      default:
        return FALSE;
      }
    }

  const MAX_RECURSIVE = 20; // recursion safety check
  }

?>
