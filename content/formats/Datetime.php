<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");

require_once("element/ElementData.php");

class TDateTimeFormat extends TFormatStatus
  {
  public function __construct()
    {
    } 

  public function Apply($info,$content,$attribs)
    {
    return "";
    }
   
  public function UnApply($info,$content,$attribs)
    {
    return "";
    }

  public function IsVisible($info,$content,$attribs)
    {
    return TRUE;
    }

  private function GetDateTimestamp($info,$source)
    {
    $cElem = $info->BaseCurrentElement();
    if ($cElem === FALSE)
      return FALSE; // element not set, for some reason

    switch (strtolower($source))
      {
      case self::SRC_LASTEDIT:
        return $cElem->GetLastEditTime();
      case self::SRC_CREATED:
        return $cElem->GetCreationTime();
      default:
        return FALSE;
      }
    }

  const DEFAULT_FORMAT = "d-m-Y H:i:s";

  const SRC_LASTEDIT = "lastedit";
  const SRC_CREATED  = "created";

  public function Pulse($info,$attribs)
    {
    if (!isset($attribs[1]) || ($timestamp = $this->GetDateTimestamp($info,$attribs[1])) === FALSE)
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::DATETIME_UNKNOWN_SOURCE,
        array(0 => (isset($attribs[1]) ? $attribs[1] : "NULL")));
      return "";
      }

    $format = self::DEFAULT_FORMAT;
    if (isset($attribs[2]))
      $format = $attribs[2];

    $result = date($format,$timestamp); // convert the timestamp to a string using the format
    if ($result === FALSE)
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::DATETIME_INVALID_FORMAT,array(0 => $format));
      return "";
      }
      
    return $result;
    }

  public function GetName()
    {
    return PARAMETER_DATETIME;
    }
  }

?>
