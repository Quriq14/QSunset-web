<?php

require_once("content/defines.php");

class TEnableFormat
  {
  public function __construct($name)
    {
    $this->name = $name;
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

  public function Pulse($info,$attribs)
    {
    return "";
    }

  const ALL       = "all";       // [ENABLE=all] enable all the disabled symbols
  const ALLEXCEPT = "allexcept"; // [ENABLE=allexcept=symbol1=symbol2] enable all the disabled symbol
                                 // symbol1 and symbol2 will be left disabled if disabled
  const SYMBOL    = "symbols";   // [ENABLE=symbols=symbol1=symbol2] enable symbol1 and symbol2

  public function OnPulse($info,$attribs,$topsymbattr)
    {
    if (!isset($attribs[1]))
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::ENABLE_NOT_ENOUGH_PARAM,array(0 => $this->GetName()));
      return;
      }

    $enOrNotDis = $this->GetName() === PARAMETER_ENABLE;

    switch (strtolower($attribs[1]))
      {
      case self::ALL:
        $enOrNotDis ? $info->EnableAllSymbolsExcept() : $info->DisableAllSymbolsExcept();
        break;
      case self::ALLEXCEPT:
        if (!isset($attribs[2])) // exception list is empty, send a warning but continue
          NParseError::Error($info,NParseError::WARNING,NParseError::ENABLE_EXCEPTION_EMPTY,array());

        $attribscount = count($attribs);
        $exceptions = array();
        for ($i = 2; $i < $attribscount; $i++)
          $exceptions[strtoupper($attribs[$i])] = TRUE;

        $enOrNotDis ? $info->EnableAllSymbolsExcept($exceptions) : $info->DisableAllSymbolsExcept($exceptions);
        break;
      case self::SYMBOL:
        if (!isset($attribs[2])) // symbol list is empty
          NParseError::Error($info,NParseError::ERROR,NParseError::ENABLE_LIST_EMPTY,array(0 => $this->GetName()));

        $attribscount = count($attribs);
        for ($i = 2; $i < $attribscount; $i++)
          $enOrNotDis ? $info->EnableSymbol(strtoupper($attribs[$i])) : $info->DisableSymbol(strtoupper($attribs[$i]));
        break;
      default:
        NParseError::Error($info,NParseError::ERROR,NParseError::ENABLE_UNKNOWN_SUBCMD,array(0 => $attribs[1]));
        break;
      }
    }

  public function GetName()
    {
    return $this->name;
    }

  private $name;
  }

NFormatFactory::Register(new TEnableFormat(PARAMETER_ENABLE));
NFormatFactory::Register(new TEnableFormat(PARAMETER_DISABLE));

?>
