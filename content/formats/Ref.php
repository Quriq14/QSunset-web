<?php

require_once("content/FormatStatus.php");

require_once("html/htmlutils.php");

require_once("element/defines.php");
require_once("element/ElementFactory.php");

class TRefFormat extends TFormatStatus
  {
  public function __construct()
    {
    }

  public function Apply($info,$content,$attribs)
    {
    if (!isset($attribs[1]))
      return "";

    return AHrefBegin("bodytexta",$attribs[1],$info->language);
    }
   
  public function UnApply($info,$content,$attribs)
    {
    if (!isset($attribs[1]))
      return "";

    return "</a>";
    }

  public function IsVisible($info,$content,$attribs)
    {
    return TRUE;
    }

  public function Pulse($info,$attribs)
    {
    return "";
    }
  }

class TRelativeRefFormat extends TFormatStatus
  {
  public function __construct()
    {
    }

  const SYMBOL_ABSOLUTE   = ":"; // prefix for an absolute path
  const SYMBOL_APPEND     = "+"; // prefix for appending a string to path
  const SYMBOL_REMOVELAST = "-"; // removes last element and eveluate again
  const SYMBOL_THIS       = "."; // reference to current element (may be combined with - to obtain parent)

  const ERROR = " TRelativeRefFormat(RREF): Invalid sintax ";

  public function Apply($info,$content,$attribs)
    {
    if (!isset($attribs[1]))
      return "";

    if ($info->cElement === FALSE || !$info->cElement->IsValid())
      return self::ERROR; // current element not set

    $relativePath = trim($attribs[1]);
    $currentElement = $info->cElement;
    $finished = FALSE;

    while (!$finished)
      {
      if (!isset($relativePath[0]))
        return self::ERROR; // string too short!

      switch ($relativePath[0])
        {
        case self::SYMBOL_ABSOLUTE:
          $relativePath = substr($relativePath,1);
          $currentElement = ElementFactory($relativePath);
          if (!$currentElement->IsValid())
            return self::ERROR;
          $finished = TRUE;
          break;

        case self::SYMBOL_APPEND:
          $relativePath = substr($relativePath,1);
          $newAddr = $currentElement->GetAddress().$relativePath;
          $currentElement = ElementFactory($newAddr);
          if (!$currentElement->IsValid())
            return self::ERROR;
          $finished = TRUE;
          break;

        case self::SYMBOL_THIS:
          $finished = TRUE;
          break;

        case self::SYMBOL_REMOVELAST:
          $relativePath = substr($relativePath,1); // remove the minus
          $currentElement = $currentElement->GetParent(); // go to parent
          if ($currentElement === FALSE)
            return self::ERROR; // something went horribly wrong
          break;

        default:
          return self::ERROR; // invalid symbol
        }
      }

    return AHrefBegin("bodytexta",$currentElement->GetAddress(),$info->language);
    }
   
  public function UnApply($info,$content,$attribs)
    {
    return "</a>";
    }

  public function IsVisible($info,$content,$attribs)
    {
    return TRUE;
    }

  public function Pulse($info,$attribs)
    {
    return "";
    }
  }

class TFarRefFormat extends TFormatStatus
  {
  public function __construct()
    {
    }

  public function Apply($info,$content,$attribs)
    {
    if (!isset($attribs[1]))
      return "";

    return "<a class=\"bodytextafar\" href=\"".htmlspecialchars($attribs[1])."\">";
    }
   
  public function UnApply($info,$content,$attribs)
    {
    if (!isset($attribs[1]))
      return "";

    return "</a>";
    }

  public function IsVisible($info,$content,$attribs)
    {
    return TRUE;
    }

  public function Pulse($info,$attribs)
    {
    return "";
    }
  }

?>
