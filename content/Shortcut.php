<?php

require_once("content/FormatStatus.php");
require_once("content/Producer.php");

class TShortcut extends TFormatStatus
  {
  public function __construct($symbolName,$shortcutstr)
    {
    $this->name = $symbolName;
    $this->sstr = $shortcutstr;
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

  // one-shot (when symbol is called but area of effect does not begin)
  public function Pulse($info,$attribs)
    {
    return "";
    }

  public function NeedChildProc($info,$attribs,$topsymbattr)
    {
    return FALSE;
    }

  public function OnBegin($info,$attribs,$topsymbattr)
    {
    parent::OnBegin($info,$attribs,$topsymbattr);

    // update shortcuts if OnBegin created one
    $info->UpdateShortcutStatus($this->GetName());

    if (!isset($attribs[1]))
      NParseError::Error($info,NParseError::WARNING,NParseError::SHORTCUT_WITHOUT_CONTENT,
       array(0 => $this->GetName()));

    // if the action is specified, is it valid?
    if (isset($attribs[2]) && !NActionParameters::Is(strtoupper($attribs[2]))) 
      NParseError::Error($info,NParseError::WARNING,NParseError::SHORTCUT_UNKNOWN_ACTION,
        array(0 => $this->GetName(), 1 => $attribs[2]));
    }

  public function OnEnd($info,$topsymbname)
    {
    parent::OnEnd($info,$topsymbname);

    // update shortcuts if OnEnd destroyed one
    $info->UpdateShortcutStatus($this->GetName());
    }

  public function ShortcutPulse($info,$attribs,$topsymbattr)
    {
    if (!isset($attribs[1]) || $attribs[1] === 0)
      return FALSE; // error already sent by OnBegin

    $action = NActionParameters::DEF;
    if (isset($attribs[2]))
      {
      $maybeaction = strtoupper($attribs[2]);
      if (NActionParameters::Is($maybeaction))
        $action = $maybeaction; // otherwise, error already sent by OnBegin
      }

    // build a simple command and return it
    return array(0 => array(0 => strtoupper($attribs[1])),1 => array(0 => $action));
    }

  public function GetName()
    {
    return $this->name;
    }

  private $name = "";
  private $sstr = "";
  }

?>
