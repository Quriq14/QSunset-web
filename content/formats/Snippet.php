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
    return $info->produceSource !== FALSE;
    }

  public function Pulse($info,$status)
    {
    if (!isset($status[1]) || !is_string($status[1]))
      return ""; // snippet name not set

    $key = self::DATA_KEY_PREFIX.$status[1];
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

  public function OnAddedProducer($info,$producer,$status)
    {
    if ($producer === FALSE)
      return;
    if (!isset($status[1]) || !is_string($status[1]))
      return; // snippet name not set

    $key = self::DATA_KEY_PREFIX.$status[1];

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
