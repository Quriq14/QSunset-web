<?php

require_once("element/HeaderParameters.php");
require_once("element/ElementData.php");

require_once("html/language.php");
require_once("html/header.php");

require_once("content/ParseContent.php");

function PrepareLanguageData($view,$autoLang)
  {
  $langdata = new TLanguageSelectorData();

  $langParam = $view->GetAvailableLanguagesArray();

  foreach ($langParam as $k => $l)
    if (IsLang($l))
      {
      $langdata->hrefs[$k] = $view->GetCurrentAddress();
      $langdata->labels[$k] = strtoupper($l);
      $langdata->ids[$k] = GetLangId($l);
  
      if ($view->GetLanguage() === $l)
        $langdata->selected = $k;
      }

  switch ($autoLang->reason)
    {
    case TAutoLangResult::FAILED_DETECT:
      $langdata->status = TLanguageSelectorData::NOT_FOUND;
      $langdata->errorStr = NContentParser::Parse("<ERRORS/LANGUAGE|NOTFOUND>",$view->GetContentParserInfo());
      break;
    case TAutoLangResult::REQUESTED_LANG:
      $langdata->status = TLanguageSelectorData::SELECTED_BY_USER;
      break;
    case TAutoLangResult::ACCEPT_LANG:
      $langdata->status = TLanguageSelectorData::AUTO_DETECTED;
      break;
    case TAutoLangResult::NOT_AVAIL_LANG:
      $langdata->status = TLanguageSelectorData::NOT_AVAILABLE;
      $langdata->errorStr = NContentParser::Parse("<ERRORS/LANGUAGE|NOTAVAIL>",$view->GetContentParserInfo());
      break;
    }

  return $langdata;
  }

function PrepareHeaderData($view)
  {
  $chdata = new THeaderData();
  $chdata->title = $view->GetHeaderTitle();
  $chdata->linklanguage = $view->GetLanguage();

  if (!$view->IsRoot())
    {
    $cparent = $view->GetParentDirectory();
    $oldelement = $view->GetElement();
    // at each cycle, $oldelement becomes $cparent
    // and $cparent becomes his parent
    // this way, comparing $oldelement with the elements in the index of $cparent
    // we may find which element should be highlighted in the index

    $pcounter = 0;
    do
      {
      $hte = new THeaderTreeElem();
      // fill the tree element with the index
      $cparentindex = $cparent->GetIndex();
      $i = 0;
      foreach ($cparentindex as $cpv)
        {
        if ($cpv->GetType() !== TElementType::DIRECTORY)
          continue; // only directories go to header

        $iscurrentelem = $cpv->GetAddress() === $oldelement->GetAddress();  // it's the current element

        $hte->titleonly[$i] = FALSE;
        if (!($cpv->IsReachable())) // unreachable object
          {
          if ($iscurrentelem) // if current, show it but disable link
            $hte->titleonly[$i] = TRUE;
            else
              continue; // if not current, skip it
          }

        $hte->names[$i] = NContentParser::Parse($cpv->GetTitle(),$view->GetContentParserInfo());
        $hte->paths[$i] = $cpv->GetAddress();
        if ($iscurrentelem)                
          $hte->marked = $i; // mark the current element
        $i++;
        }

      if ($i > 0 && $hte->marked !== -1) // do not add a file list if it's empty or nothing is marked
        $chdata->fileLists[$pcounter++] = $hte;

      if ($cparent->IsRoot())
        $cparent = FALSE; // exit the cycle
        else
          {
          $oldelement = $cparent;
          $cparent = $cparent->GetParent();
          }
      }
      while ($cparent !== FALSE);
    }

  return $chdata;
  }

?>
