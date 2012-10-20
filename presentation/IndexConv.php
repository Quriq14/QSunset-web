<?php
// use require_once for this file

require_once("element/ElementData.php");
require_once("element/HeaderParameters.php");

require_once("html/index.php");

require_once("content/ParseContent.php");

// $markname is the string of the current element
function IndexTreeGenPivot($view,$dirindex,$depth,$markname)
  {
  $dirindexcount = count($dirindex);
  $indextreeidx = 0;
  $indextree = array();
  
  for ($i = 0; $i < $dirindexcount; $i++)
    {
    $treeelem = new TIndexTreeElem();
    $treeelem->name = NContentParser::Parse(new TContentParserInfo(
      $dirindex[$i],NElementParts::TITLE,$view->GetLanguage(),NElementParts::TITLE));
    $treeelem->href = $dirindex[$i]->GetAddress();
    $treeelem->marked = $treeelem->href === $markname; // it's the current element
    $treeelem->titleonly = !($dirindex[$i]->IsReachable());
    $treeelem->directory = ($dirindex[$i]->GetType() === TElementType::DIRECTORY);
    $treeelem->comment = NContentParser::Parse(new TContentParserInfo(
      $dirindex[$i],NElementParts::SUBTITLE,$view->GetLanguage(),NElementParts::SUBTITLE));
    $treeelem->linklanguage = $view->GetLanguage();

    if ($treeelem->directory && $treeelem->titleonly)
      continue; // do not show unreachable directories

    // recursive: get childs (if not a directory)
    if (!$treeelem->directory && $depth < WRITE_INDEX_MAX_DEPTH) // avoid call if index can't display this depth
      {
      $secindex = $dirindex[$i]->GetChildSections(); 
      $treeelem->childs = IndexTreeGenPivot($view,$secindex,$depth+1,$markname);
      }

    $indextree[$indextreeidx++] = $treeelem;
    }
    
  return $indextree;
  }

function PrepareIndexData($view)
  {
  $indextree = array();
  if (!isset($view))
    return $indextree; // error: invalid view

  // add the sections
  $dirindex = $view->GetIndex(); // retrieve index, using directory as root
  $indextree = IndexTreeGenPivot($view,$dirindex,0,$view->GetCurrentAddress());
  
  return $indextree;
  }

?>
