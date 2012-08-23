<?php

require_once("content/FormatStatus.php");
require_once("content/defines.php");

// every time this format is called with BEGIN-END
// some text is added to the snippet
// when the PULSE mode is used, the added text is written to output
// requires a parameter: the name of the snippet
class TSnippetFormat extends TFormatStatus
  {
  const DATA_KEY_PREFIX = "TSnippetFormat:";

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
    return $info->produceSource !== FALSE;
    }

  public function OnBegin($info,$attribs,$topsymbattr)
    {
    parent::OnBegin($info,$attribs,$topsymbattr);

    if (!isset($attribs[1]) || $attribs[1] === "")
      return; // snippet name not set

    $key = self::DATA_KEY_PREFIX.$attribs[1];
    $info->AddProducerListener($key,new TParamFormatAttribs($this,$attribs,$topsymbattr));
      // start listening for created objects
    }

  public function OnEnd($info,$topsymbname)
    {
    $attribs = $info->GetActiveSymbol($this->GetName(),$topsymbname); // find the symbol
    if ($attribs === FALSE)
      return;
    $attribs = $attribs->attribs;

    if (!isset($attribs[1]) || $attribs[1] === "")
      return; // snippet name not set

    $key = self::DATA_KEY_PREFIX.$attribs[1];
    $info->RemoveProducerListener($key); // stop listening

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
    
    $storedSource = $info->produceSource;
    $info->produceSource = $this;

    $result = "";
    $plistcount = count($plist);
    for ($i = 0; $i < $plistcount; $i++)
      $result .= $plist[$i]->Produce($info);

    $info->produceSource = $storedSource;

    return $result;
    }

  public function OnAddedProducer($info,$producer,$attribs)
    {
    if (!isset($attribs[1]) || $attribs[1] === "")
      return; // snippet name not set

    $key = self::DATA_KEY_PREFIX.$attribs[1];

    $plist = $info->GetFormatData($key,array());

    $plist[count($plist)] = $producer; // store the new producer

    $info->SetFormatData($key,$plist);
    }

  public function GetName()
    {
    return PARAMETER_SNIPPET;
    }
  }

?>
