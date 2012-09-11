<?php
// use require_once for this file

require_once("html/htmlutils.php");
require_once("content/ParseContent.php");
require_once("element/HeaderParameters.php");

// set $toRight to TRUE only if the content should leave space for the index
function PrepareContentData($view,$toRight)
  {
  $contentdata = new TContentData();
  $contentdata->toRight = $toRight;
  $contentdata->linklanguage = $view->GetLanguage();

  if (!$view->IsReadable()) // content not readable
    {
    $contentdata->HScontent = PARAGRAPH_BEGIN.$view->GetNotReadableError().PARAGRAPH_END;
    return $contentdata;
    }

  // content is readable, build it
  $HScontent = "";
  if ($view->HasContent())
    {
    $HScontent .= "<div>";

    $HScontent .= $view->GetContent();

    $HScontent .= "</div>";
    }
  $contentdata->HScontent = $HScontent;

  // title if needed
  if ($view->IsDisplayTitle())
    $contentdata->title = $view->GetTitle();

  if ($view->IsDisplaySubTitle())
    $contentdata->subtitle = $view->GetSubTitle();

  if (($prev = $view->GetPrevElem()) !== FALSE)
    {
    if ($prev->IsReachable())
      {
      $contentdata->prevLabel = NContentParser::Parse($prev->GetTitle(),
        new TContentParserInfo($view->GetLanguage(),$prev,NPresCacheKeys::TITLE));
      $contentdata->prevAddr = $prev->GetAddress();
      }
    }

  if (($next = $view->GetNextElem()) !== FALSE)
    {
    if ($next->IsReachable()) // check reachability
      {
      $contentdata->nextLabel = NContentParser::Parse($next->GetTitle(),
        new TContentParserInfo($view->GetLanguage(),$next,NPresCacheKeys::TITLE));
      $contentdata->nextAddr = $next->GetAddress();
      }
    }

  return $contentdata;
  }
