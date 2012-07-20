<?php

require_once("content/FormatStatus.php");

define("SNIPPET_FORMAT_DATA_KEY_PREFIX","TSnippetFormat:");

// every time this format is called with BEGIN-END
// some text is added to the snippet
// when the PULSE mode is used, the added text is written to output
// requires a parameter: the name of the snippet
class TSnippetFormat extends TFormatStatus
  {
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
    return TRUE;
    }

  public function Pulse($info,$status)
    {
    if (!isset($status[1]) || !is_string($status[1]))
      return ""; // snippet name not set

    $key = SNIPPET_FORMAT_DATA_KEY_PREFIX.$status[1];
    $plist = $info->GetFormatData($key);
    if (!is_array($plist)) // not created
      return "";
    
    $result = "";
    $plistcount = count($plist);
    for ($i = 0; $i < $plistcount; $i++)
      $result .= $plist[$i]->Produce($info);

    return $result;
    }

  public function AddedProducer($info,$producer,$status)
    {
    if ($producer === FALSE)
      return;
    if (!isset($status[1]) || !is_string($status[1]))
      return; // snippet name not set

    $key = SNIPPET_FORMAT_DATA_KEY_PREFIX.$status[1];

    $plist = $info->GetFormatData($key);
    if (!is_array($plist)) // not created yet
      $plist = array();

    $plist[count($plist)] = $producer; // store the new producer

    $info->SetFormatData($key,$plist);
    }
  }

?>
