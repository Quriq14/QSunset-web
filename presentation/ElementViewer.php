<?php
// use require_once for this file

require_once("element/ElementData.php");
require_once("element/ElementFactory.php");
require_once("element/HeaderParameters.php");
require_once("element/defines.php");

require_once("content/ParseContent.php");

require_once("html/htmlutils.php");

define("ELEMENT_VIEWER_MAX_REDIRECT","10");

// this class holds one (or more, if error or silent redirect) TElement object, but transformed so that it is safely viewable
// all the method results are HTML safe
class TElementViewer
  {
  function __construct($celement)
    {
    if (!isset($celement) || !($celement instanceof TElementData))
      return;

    $this->SetElement($celement,0);
    }

  private function SetElement($newelement,$depth)
    {
    $this->Push($newelement);

    if ($depth > MAX_CONSECUTIVE_REDIRECT)
      return; // redirect depth exceeded

    if (!$newelement->IsValid() || // if not valid, silently redirect to 404
      !$newelement->IsVisible())   // or if it's invisible
      {
      $error404 = ElementFactory(ERROR_404_PATH);
      $this->SetElement($error404,$depth+1); // recursive call: attempt to evaluate the error as element
      }

    $silent = $newelement->GetRedirectSilent();
    if ($silent !== "") // silent redirect requested
      {
      $elem = ElementFactory($silent);
      $this->SetElement($elem,$depth+1); // recursive call: execute the redirect
      }
    }

  // always use this after constructor call
  // if this IsValid returns false, a general failure occurred
  // ie a catastrophical event (file corrupted, misplaced root directory, failed authorization...)
  public function IsValid()
    {
    return $this->Top() !== FALSE && $this->Top()->IsValid();
    }

  // *** VISIBILTY FUNCTIONS ***
  // if false, the page will be accessible, but the content won't be displayed
  public function IsReadable()
    {
    return $this->Top()->IsReadable();
    }

  public function GetNotReadableError()
    {
    return $this->Top()->GetParamDefault(NParams::READABLE_ERROR)->ToString();
    }

  // no links to this page should be created
  public function IsReachable()
    {
    return $this->Top()->IsReachable();
    }

  // *** REDIRECT FUNCTIONS ***
  // these functions report visible redirects only (not silent or errors)
  public function HasRedirectNear()
    {
    return $this->Top()->GetRedirectNear() !== "";
    }

  public function HasRedirectFar()
    {
    return $this->Top()->GetParamDefault(NParams::REDIRECT_FAR)->ToString() !== "";
    }

  public function HasRedirect()
    {
    return $this->HasRedirectNear() || $this->HasRedirectFar();
    }

  public function GetRedirectNear()
    {
    return $this->Top()->GetRedirectNear();
    }

  public function GetRedirectFar()
    {
    return $this->Top()->GetParamDefault(NParams::REDIRECT_FAR)->ToString();
    }

  // "" if none
  public function GetRedirect()
    {
    if ($this->HasRedirectNear())
      return BuildNearHref($this->GetRedirectNear(),NPresGlobals::GetCurrentLanguage()); // build full path

    if ($this->HasRedirectFar())
      return $this->GetRedirectFar();

    return "";
    }

  public function HasHTTPStatusCode()
    {
    return $this->GetHTTPStatusCode() !== "";
    }

  // "" if none
  // returns a STRING, not a number
  public function GetHTTPStatusCode()
    {
    return $this->Top()->GetParamDefault(NParams::HTTP_STATUS_CODE)->ToString();
    }

  // *** CONTENT ACCESS FUNCTIONS ***
  public function GetTitle()
    {
    return NContentParser::Parse($this->Top()->GetTitle(),$this->GetContentParserInfo());
    }

  public function GetType()
    {
    return $this->Top()->GetType();
    }

  public function GetSubTitle()
    {
    return NContentParser::Parse($this->Top()->GetParamDefault(NParams::CONT_SUBTITLE)->ToString(),$this->GetContentParserInfo());
    }

  public function IsDisplaySubTitle()
    {
    return ($this->GetSubTitle() !== "") && $this->Top()->GetParamDefault(NParams::SHOW_CONT_SUBTITLE)->ToBool();
    }

  // get the complete path of the current element (BEFORE redirects)
  public function GetOriginalAddress()
    {
    return $this->Root()->GetAddress();
    }

  // get the complete path of the current element (AFTER redirects)
  public function GetCurrentAddress()
    {
    return $this->Top()->GetAddress();
    }

  // returns FALSE if none
  public function GetNextElem()
    {
    $maybeAddr = $this->GetNextAddress();
    if ($maybeAddr === FALSE)
      return FALSE; // address not found

    $maybeElement = ElementFactory($maybeAddr);
    if (!$maybeElement->IsValid())
      return FALSE; // invalid element

    return $maybeElement;
    }

  // returns FALSE if none
  public function GetNextAddress()
    {
    $maybeAddr = $this->Top()->GetParamDefault(NParams::NEXT);

    if ($maybeAddr->IsFalse())
      return FALSE; // disabled by param

    if ($maybeAddr->IsAuto()) // auto-detect is selected
      return $this->Top()->GetNextAddress(); // might return false on its own

    if ($maybeAddr->IsCustom())
      return $maybeAddr->GetCustom(); // is custom

    return FALSE; // not valid
    }

  // returns FALSE if none
  public function GetPrevElem()
    {
    $maybeAddr = $this->GetPrevAddress();
    if ($maybeAddr === FALSE)
      return FALSE; // address not found

    $maybeElement = ElementFactory($maybeAddr);
    if (!$maybeElement->IsValid())
      return FALSE; // invalid element

    return $maybeElement;
    }

  // returns FALSE if none
  public function GetPrevAddress()
    {
    $maybeAddr = $this->Top()->GetParamDefault(NParams::PREV);

    if ($maybeAddr->IsFalse())
      return FALSE; // disabled by param

    if ($maybeAddr->IsAuto()) // auto-detect is selected
      return $this->Top()->GetPrevAddress(); // might return false on its own

    if ($maybeAddr->IsCustom())
      return $maybeAddr->GetCustom(); // is custom

    return FALSE; // not valid
    }

  public function HasContent()
    {
    return $this->Top()->HasContent();
    }

  public function GetContent()
    {
    return NContentParser::ParseArray($this->Top()->GetContent(),$this->GetContentParserInfo());
    }

  public function GetFooter()
    {
    return NContentParser::Parse($this->Top()->GetParamDefault(NParams::FOOTER)->ToString(),$this->GetContentParserInfo());
    }

  public function IsDisplayTitle()
    {
    return $this->Top()->GetParamDefault(NParams::SHOW_CONT_TITLE)->ToBool();
    }

  public function GetHeaderTitle()
    {
    return NContentParser::Parse($this->Top()->GetParamDefault(NParams::HEADER_TITLE)->ToString(),$this->GetContentParserInfo());
    }

  // an array
  public function GetAvailableLanguagesArray()
    {
    return $this->Top()->GetParamDefault(NParams::LANG_AVAIL)->ToLowercaseArray(" ");
    }

  public function GetOriginalLanguages()
    {
    return $this->Top()->GetParamDefault(NParams::LANG_ORIG)->ToString();
    }

  public function GetAvailableLanguages()
    {
    return $this->Top()->GetParamDefault(NParams::LANG_AVAIL)->ToString();
    }

  // *** INDEX FUNCTIONS ***
  // all these functions return a TElementData object
  // if needed, build another viewer for them
  public function GetIndex()
    {
    return $this->Top()->GetIndex();
    }

  public function GetChildSections()
    {
    return $this->Top()->GetChildSections();
    }

  public function GetSections()
    {
    return $this->Top()->GetSections();
    }

  public function IsRoot()
    {
    return $this->Top()->IsRoot();
    }

  public function GetParentDirectory()
    {
    if ($this->Top()->IsRoot())
      return $this->Top(); // root don't have a parent

    $cparent = $this->Top()->GetParent();

    while ($cparent->GetType() !== TElementType::DIRECTORY)
      {
      $oldelement = $cparent;
      $cparent = $cparent->GetParent();
      }
 
    return $cparent;
    }

  public function GetElement()
    {
    return $this->Top();
    }

  // *** PARSER HELPER FUNCTIONS ***
  // produce a new TContentParserInfo to be sent to the parser
  // should never fail, if the object is valid
  public function GetContentParserInfo()
    {
    $result = new TContentParserInfo();
    $result->language = $this->GetLanguage();
    $result->cElement = $this->GetElement();
    return $result;
    }

  public function SetLanguage($lang)
    {
    if (!isset($lang) || !is_string($lang))
      return;

    $this->currentLanguage = $lang;
    }

  public function GetLanguage()
    {
    return $this->currentLanguage;
    }
  
  // *** STACK MANIPULATION FUNCTIONS ***
  // FALSE if invalid
  // get element on top of the stack
  private function Top()
    {
    if ($this->elementcount === 0 || count($this->elementstack) !== $this->elementcount)
      return FALSE;

    return $this->elementstack[$this->elementcount-1];
    }

  // FALSE if invalid
  // get base element of the stack
  private function Root()
    {
    if (count($this->elementstack) === 0)
      return FALSE;

    return $this->elementstack[0];
    }

  // push an element on top of the stack
  // FALSE if failed
  private function Push($element)
    {
    if (!isset($element))
      return FALSE;

    if ($this->elementcount >= self::MAX_ELEMENT_STACK_SIZE)
      return FALSE; // stack too big

    $this->elementstack[$this->elementcount++] = $element;
    return TRUE;
    }

  const MAX_ELEMENT_STACK_SIZE = 10; // prevent infinite recursion
  private $elementstack = array();   // this is a stack: the original element is at 0, the current element is at $elementcount-1
  private $elementcount = 0;

  private $currentLanguage   = NLanguages::LANGUAGE_DEFAULT;
  }

function ElementViewerFactory($element)
  {
  static $cache = array();
  $str = $element->GetAddress();

  if (!isset($cache[$str]))
    $cache[$str] = new TElementViewer($element);

  return $cache[$str];
  }
?>
