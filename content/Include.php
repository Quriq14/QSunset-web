<?php

// include a file <filename>

require_once("element/ElementData.php");
require_once("element/ElementFactory.php");

require_once("content/ParserImpl.php");
require_once("content/ParseError.php");

abstract class NElementParts
  {
  const NOT_READABLE_ERROR = "NRE";
  const LANG_NOT_FOUND     = "LNF";
  const LANG_NOT_AVAIL     = "LNA";
  const TITLE              = "TTL";
  const SUBTITLE           = "STL";
  const CONTENT            = "CNT";
  const FOOTER             = "FTR";
  const HEADER             = "HDR";

  // returns a string
  public static function GetPartOfElement($element,$part)
    {
    switch ($part)
      {
      case self::NOT_READABLE_ERROR:
        return $element->GetParamDefault(NParams::READABLE_ERROR)->ToString();
      case self::LANG_NOT_FOUND:
        return $element->GetParamDefault(NParams::LANG_NOT_FOUND_ERR)->ToString();
      case self::LANG_NOT_AVAIL:
        return $element->GetParamDefault(NParams::LANG_NOT_AVAIL_ERR)->ToString();
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
      }

    return "";
    }
  }

abstract class NInclude
  {
  const TITLE     = "TITLE";
  const SUBTITLE  = "SUBTITLE";
  const CONTENT   = "CONTENT";
  const HEADER    = "HEADER";
  const FOOTER    = "FOOTER";

  const DEFAULT_PART = self::CONTENT;

  // convert the part to a NElementPart
  private static $CONVERT = array(
    self::FOOTER   => NElementParts::FOOTER,
    self::TITLE    => NElementParts::TITLE,
    self::SUBTITLE => NElementParts::SUBTITLE,
    self::HEADER   => NElementParts::HEADER,
    self::CONTENT  => NElementParts::CONTENT,
    );

  // $info is a TContentParseInfo
  // $path is the Address of the element to include
  // $part is the part of the element (set to FALSE for DEFPART)
  static public function DoInclude($info,$path,$part = FALSE)
    {
    if ($part === FALSE)
      $part = self::DEFAULT_PART;

    $uppercasePart = strtoupper($part);

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

    if (!isset(self::$CONVERT[$uppercasePart]))
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::INCLUDE_UNKNOWN_PART,array(0 => $part));
      return;
      }

    $oldContent = $info->content;
    $oldProcessed = $info->processed;
    
    $convertedPart = self::$CONVERT[$uppercasePart];
    $info->processed = 0;
    $info->content = NElementParts::GetPartOfElement($element,$convertedPart);
    $info->PushCurrentElement($element,$convertedPart);
      
    NParserImpl::Parse($info);

    $info->processed = $oldProcessed;
    $info->content = $oldContent;
    $info->PopCurrentElement();
    }

  const MAX_RECURSIVE = 20; // recursion safety check
  }

?>
