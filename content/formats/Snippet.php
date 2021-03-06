<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");
require_once("content/ParseError.php");

// every time this format is called with BEGIN-END
// some text is added to the snippet
// when the PULSE mode is used, the added text is written to output
// requires a parameter: the name of the snippet
class TSnippetFormat extends TFormatStatus
  {
  const DATA_KEY_PREFIX = "TSnippetFormat:data:"; // an array of producers
  const STACK_KEY = "TSnippetFormat:stack"; // stack (array 0 .. n => name) of currently producing snippets

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
    return TRUE; // some snippet is producing
    }

  public function OnBegin($info,$attribs,$topsymbattr)
    {
    parent::OnBegin($info,$attribs,$topsymbattr);

    if (!isset($attribs[1]) || $attribs[1] === "")
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::UNNAMED_SNIPPET,array());
      return;
      }

    $key = self::DATA_KEY_PREFIX.$attribs[1];
    $info->PushProduceRedirect($key,new TParamFormatAttribs($this,$attribs,$topsymbattr));
      // start redirecting created objects to this object

    NParserTreeStack::IncDepth($info,$this->GetName(),$topsymbattr->GetName());
    }

  public function OnEnd($info,$topsymbname)
    {
    if (!NParserTreeStack::DecDepth($info,$this->GetName(),$topsymbname))
      return;

    $attribs = $info->GetActiveSymbol($this->GetName(),$topsymbname); // find the symbol
    if ($attribs === FALSE)
      return;
    $attribs = $attribs->attribs;

    if (!isset($attribs[1]) || $attribs[1] === "")
      return; // snippet name not set

    $key = self::DATA_KEY_PREFIX.$attribs[1];
    $info->RemoveProduceRedirect($key); // stop redirecting

    parent::OnEnd($info,$topsymbname);
    }

  public function Pulse($info,$attribs)
    {
    if (!isset($attribs[1]) || $attribs[1] === "")
      return ""; // snippet name not set

    $key = self::DATA_KEY_PREFIX.$attribs[1];
    $plist = $info->GetFormatData($key);
    if (!is_array($plist)) // not created
      return "";
    
    $stack = $info->GetFormatData(self::STACK_KEY,array());

    // check for circular calls
    $foundcircular = FALSE;
    foreach ($stack as $nm)
      if ($attribs[1] === $nm)
        {
        $foundcircular = TRUE;
        break;
        }

    if ($foundcircular)
      {
      $namestr = "\"".$attribs[1]."\"";
      // prepare error message
      $stackstr = "";
      for ($i = 0; $i < count($stack); $i++)
        $stackstr .= "\"".$stack[$i]."\", ";
      $stackstr .= $namestr;
      NParseError::Error($info,NParseError::ERROR,NParseError::CIRCULAR_SNIPPET,array(0 => $namestr,1 => $stackstr));
      return;
      }

    unset($foundcircular);

    $stack[count($stack)] = $attribs[1];
    $info->SetFormatData(self::STACK_KEY,$stack);

    $result = "";
    $plistcount = count($plist);
    for ($i = 0; $i < $plistcount; $i++)
      $result .= $plist[$i]->Produce($info);

    $stack = $info->GetFormatData(self::STACK_KEY,array());
    unset($stack[count($stack) - 1]);
    $info->SetFormatData(self::STACK_KEY,$stack);

    return $result;
    }

  public function OnAddedProducer($info,$producer,$paramformatattr)
    {
    $attribs = $paramformatattr->attribs;

    if (!isset($attribs[1]) || $attribs[1] === "")
      return FALSE; // snippet name not set

    $key = self::DATA_KEY_PREFIX.$attribs[1];

    $plist = $info->GetFormatData($key,array());

    $plist[count($plist)] = $producer; // store the new producer

    $info->SetFormatData($key,$plist);

    return FALSE;
    }

  public function GetName()
    {
    return PARAMETER_SNIPPET;
    }
  }

NFormatFactory::Register(new TSnippetFormat());

?>
