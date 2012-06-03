<?php
xdebug_disable();

require_once("element/defines.php");
require_once("element/ElementFactory.php");
require_once("element/HeaderParameters.php");
require_once("element/languageutils.php");

require_once("html/head.php");
require_once("html/header.php");
require_once("html/index.php");
require_once("html/content.php");
require_once("html/footer.php");

require_once("content/ParseContent.php");

require_once("presentation/HeaderConv.php");
require_once("presentation/IndexConv.php");
require_once("presentation/ContentConv.php");
require_once("presentation/ElementViewer.php");

$celement = ElementFactory(PATH_ROOT);

// get the path if can
if (isset($_GET[PATH_FIELD]))
  {
  $maybePath = (string)$_GET[PATH_FIELD];

  $celement = ElementFactory($maybePath);
  }
unset($maybePath);

$view = new TElementViewer($celement);

if (!$view->IsValid()) // CATACLYSM: the viewer also performs error handling. If it fails, everything fails.
  {
  echo "<html><head><title>General error</title></head><body><p>General error while displaying an error message.</p></body></html>";
  die();
  }

// get language if can
$autoLang = AutoLang(isset($_GET[LANGUAGE_FIELD]) ? strtolower($_GET[LANGUAGE_FIELD]) : FALSE,
  $view->GetAvailableLanguages(),
  $view->GetOriginalLanguages());

$view->SetLanguage($autoLang->result);

// redirect if needed
if ($view->HasRedirect())
  {
  header("Location: ".$view->GetRedirect());
  die();
  }

if ($view->HasHTTPStatusCode())
  {
  header("HTTP/1.0 ".$view->GetHTTPStatusCode());
  }

$ctitle = $view->GetTitle();

$pagesetuptime = xdebug_time_index() * 1000;

// BEGIN DOCUMENT
WriteHead($ctitle);
BeginBody();

// PREPARE HEADER DATA
$chdata = PrepareHeaderData($view);

// PREPARE LANGUAGE DATA
$langdata = PrepareLanguageData($view,$autoLang);

// OUTPUT HEADER (WITH LANGUAGE) DATA
WriteHeader($chdata,$langdata);

// BEGIN BODY BODY
BeginBodyBody();

// OUTPUT LANGUAGE ERROR (IF ANY)
WriteLanguageErr($langdata);

// PREPARE INDEX
$indextree = PrepareIndexData($view);
// OUTPUT INDEX DATA
WriteIndex($indextree);

// PREPARE CONTENT DATA
$contentdata = PrepareContentData($view,count($indextree) !== 0);
// OUTPUT CONTENT DATA
WriteContent($contentdata);

// PREPARE FOOTER
$customFooter = $view->GetFooter();
// OUTPUT FOOTER
WriteFooter($customFooter,xdebug_time_index() * 1000,$pagesetuptime);

// END DOCUMENT
EndBodyBody();

WriteTail();
//phpinfo();
?>
