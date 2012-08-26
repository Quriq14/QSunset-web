<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");

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
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::REF_PARAM_NOT_SPECIFIED,array());
      return "";
      }

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

  public function GetName()
    {
    return PARAMETER_REF;
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

  public function Apply($info,$content,$attribs)
    {
    if (!isset($attribs[1]))
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::REF_PARAM_NOT_SPECIFIED,array());
      return "";
      }

    if ($info->TopCurrentElement() === FALSE)
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::RREF_CELEMENT_NOT_SET,array(0 => $attribs[1]));
      return ""; // current element not set
      }

    $relativePath = trim($attribs[1]);
    $currentElement = $info->TopCurrentElement();
    $finished = FALSE;

    while (!$finished)
      {
      if (!isset($relativePath[0]))
        {
        NParseError::Error($info,NParseError::ERROR,NParseError::RREF_INVALID_SINTAX,array(0 => $relativePath));
        return ""; // string too short!
        }

      switch ($relativePath[0])
        {
        case self::SYMBOL_ABSOLUTE:
          $relativePath = substr($relativePath,1);
          $currentElement = ElementFactory($relativePath);
          if (!$currentElement->IsValid())
            {
            NParseError::Error($info,NParseError::ERROR,NParseError::REF_ELEM_NOT_FOUND,array(0 => $relativePath));
            return ""; // not found
            }
          $finished = TRUE;
          break;

        case self::SYMBOL_APPEND:
          $relativePath = substr($relativePath,1);
          $newAddr = $currentElement->GetAddress().$relativePath;
          $currentElement = ElementFactory($newAddr);
          if (!$currentElement->IsValid())
            {
            NParseError::Error($info,NParseError::ERROR,NParseError::REF_ELEM_NOT_FOUND,array(0 => $relativePath));
            return ""; // not found
            }
          $finished = TRUE;
          break;

        case self::SYMBOL_THIS:
          $finished = TRUE;
          break;

        case self::SYMBOL_REMOVELAST:
          $relativePath = substr($relativePath,1); // remove the minus
          $currentElement = $currentElement->GetParent(); // go to parent
          if ($currentElement === FALSE)
            {
            NParseError::Error($info,NParseError::ERROR,NParseError::REF_ELEM_NOT_FOUND,array(0 => $relativePath));
            return ""; // not found
            }
          break;

        default:
          NParseError::Error($info,NParseError::ERROR,NParseError::RREF_INVALID_SINTAX,array(0 => $relativePath));
          return ""; // invalid symbol
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

  public function GetName()
    {
    return PARAMETER_RELATIVE_REF;
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
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::REF_PARAM_NOT_SPECIFIED,array());
      return "";
      }

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

  public function GetName()
    {
    return PARAMETER_FAR_REF;
    }
  }

?>
