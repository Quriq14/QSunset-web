<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");
require_once("content/formats/Condition.php");

class TJumpFormat extends TFormatStatus
  {
  function __construct()
    {
    }

  const TO_PREF = "to"; // JUMP=to=label=if=condition or JUMP=if=condition=to=label or JUMP=to=label
  const IF_PREF = "if";

  public function Apply($info,$content,$status)
    {
    return "";
    }
  
  public function UnApply($info,$content,$status)
    {
    return "";
    }

  public function IsVisible($info,$content,$status)
    {
    return FALSE;
    }

  public function Pulse($info,$status)
    {
    return "";
    }

  public function NeedChildProc($info,$attribs,$orig) 
    {
    return TRUE; 
    }

  public function ProcessIfPart($info,$attribs,&$idx)
    {
    if (!isset($attribs[$idx]))
      return TRUE; // no condition provided, always true

    if (strtolower($attribs[$idx]) !== self::IF_PREF)
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::JUMP_STH_EXPECTED,array(0 => '"'.self::IF_PREF.'"'));
      return FALSE;
      }

    $idx++;

    return NFormatCondition::Evaluate($info,$attribs,$idx);
    }

  public function ProcessToPart($info,$attribs,&$idx)
    {
    if (!isset($attribs[$idx]) || strtolower($attribs[$idx]) !== self::TO_PREF)
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::JUMP_STH_EXPECTED,array(0 => '"'.self::TO_PREF.'"'));
      return FALSE;
      }

    $idx++;

    if (!isset($attribs[$idx]) || $attribs[$idx] === "")
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::JUMP_STH_EXPECTED,array(0 => "closing label"));
      return FALSE;
      }

    return $attribs[$idx++];
    }

  public function ChildProc($info,$attribs,$orig) 
    {
    $idx = 1;

    if (!isset($attribs[$idx]))
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::JUMP_STH_EXPECTED,array(0 => "parameters"));
      return;
      }

    $tolabel = "";
    $ifcond = TRUE;

    // see if it was placed first "to" or "if"
    switch (strtolower($attribs[$idx]))
      {
      case self::TO_PREF:
        if (($tolabel = $this->ProcessToPart($info,$attribs,$idx)) === FALSE)
          return; // label not found
        $ifcond = $this->ProcessIfPart($info,$attribs,$idx);
        break;
      case self::IF_PREF:
        $ifcond = $this->ProcessIfPart($info,$attribs,$idx);
        if (($tolabel = $this->ProcessToPart($info,$attribs,$idx)) === FALSE)
          return; // label not found
        break;
      default:
        NParseError::Error($info,NParseError::ERROR,NParseError::JUMP_STH_EXPECTED,array(0 => (self::TO_PREF." or ".self::IF_PREF)));
        return;
      }

    if ($ifcond === FALSE)
      return; // condition not met, no jump

    $contentidx = $info->processed;
    $em = $tolabel;
    $emlength = strlen($em);

    $scriptend = strpos($info->content,$em,$contentidx);

    if ($scriptend === FALSE) // it's all a script
      {
      $scriptend = strlen($info->content);
      $emlength = 0; // there is no script ending
      }

    $info->processed = $scriptend + $emlength; // advance past the ending tag
    }

  public function GetName()
    {
    return PARAMETER_JUMP;
    }
  }

?>
