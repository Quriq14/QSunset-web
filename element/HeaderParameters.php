<?php
// use ONLY require/include_once for this file

require_once("element/defines.php");

class TParam
  {
  public function __construct($sourceElement,$paramName,$value = FALSE)
    {
    $this->data = "";
    
    if (!isset($paramName))
      return;

    $this->data = NParams::GetDefault($paramName);

    if (!is_string($value))
      return; // no custom value

    $this->data = trim($value);
    }

  public function ToString() {return $this->data; }
  public function ToBool() {return strtoupper($this->data) === strtoupper(NParams::TRUE_STR); }

  // convert the content to a date/time (FALSE if failed)
  public function ToDate() {return strtotime($this->data); }

  public function ToLowercaseArray($separator) // produce a lowercase string array (empty elements are removed)
    {
    if (!isset($separator) || !is_string($separator))
      return array(); // invalid

    $temp = strtolower($this->data);

    $strarray = explode($separator,$temp);

    $result = array();
    $resultcount = 0;

    // remove empty strings
    foreach ($strarray as $k => $l)
      if ($l !== "")
        $result[$resultcount++] = $l;
        
    return $result;
    }

  public function IsTrue() {return strtoupper($this->data) === strtoupper(NParams::TRUE_STR); }
  public function IsFalse() {return strtoupper($this->data) === strtoupper(NParams::FALSE_STR); }
  public function IsEmpty() {return strtoupper($this->data) === strtoupper(NParams::EMPTY_STR); }
  public function IsAuto() {return strtoupper($this->data) === strtoupper(NParams::AUTO_STR); }

  public function IsCustom() {return strtoupper(substr($this->data,0,strlen(NParams::CUSTOM_PREFIX_STR))) === strtoupper(NParams::CUSTOM_PREFIX_STR); }
  public function GetCustom() // "" if not IsCustom
    {
    if (!$this->IsCustom())
      return "";

    $value = substr($this->data,strlen(NParams::CUSTOM_PREFIX_STR));
    if ($value === FALSE)
      $value = "";

    return trim($value);
    }

  private $data;

  static public function NewCustom($sourceElement,$paramName,$value = "") // creates a new custom parameter with this data
    {
    return new self($sourceElement,$paramName,NParams::CUSTOM_PREFIX_STR.$value);
    }
  }

class NParams
  {
  // language
  const LANG_ORIG          = "Lang-orig";   // original language of the element
  const LANG_AVAIL         = "Lang-avail";  // available languages for the element (format: it en fr)
  const LANG_NOT_AVAIL_ERR = "Lang-not-available-error";
  const LANG_NOT_FOUND_ERR = "Lang-not-found-error";

  // custom footer
  const FOOTER             = "Footer";      // custom footer for the page, "" for none

  // display title and subtitle
  const SHOW_CONT_TITLE    = "Display-content-title";    // "YES" or "NO" about the title automatically displayed above content
  const SHOW_CONT_SUBTITLE = "Display-content-subtitle"; // same as above, but for the subtitle
  const CONT_SUBTITLE      = "Subtitle";                 // custom subtitle for the content
  const HEADER_TITLE       = "Header-title";             // custom title on the header of the page

  // visibility
  const REACHABLE          = "Reachable";                // if set to NO, section won't be reachable unless explicitly typed in address bar
  const READABLE           = "Readable";                 // if set to NO, section content will be hidden
  const READABLE_ERROR     = "Readable-error";           // if unreadable section is attempted, this message will be returned
  const VISIBLE            = "Visible";                  // if set to NO, section will return 404 on access

  // redirect
  const REDIRECT_NEAR      = "Redirect-near";            // if set, the page will redirect to other page
  const REDIRECT_SILENT    = "Redirect-silent";          // if set, the page will silently redirect to other page in same website
  const REDIRECT_FAR       = "Redirect-far";             // if set, the page will redirect to a web address

  // error code
  const HTTP_STATUS_CODE   = "HTTP-status-code";         // if set, page will send this status code instead of 200 OK ("" for default)

  // section links
  const NEXT               = "Next-section";             // defines the next section for this section
    // FALSE_STR: if no next section should be shown (quotes not needed)
    // "": if section should be selected automatically (by reading the index)
    // "CUSTOM=address": if "address" has to be used
    // "address": if address has to be used and is different from previous forms
  const PREV               = "Prev-section";             // defines the previous section for this section
    // same as above

  const CREATED            = "Created";                  // date/time of the creation of the element
  const LAST_EDIT          = "Last-modified";            // date/time of the last modification of the element

  // values
  const TRUE_STR           = "YES";
  const FALSE_STR          = "NO";
  const EMPTY_STR          = "";
  // for SECTION_NEXT and SECTION_PREV
  // (see NParams:GetCustom below)
  const CUSTOM_PREFIX_STR  = "CUSTOM=";

  const AUTO_STR           = "AUTO";

  private static $DEFAULTS = array(
    self::LANG_ORIG                => NLanguages::LANGUAGE_DEFAULT,
    self::LANG_AVAIL               => NLanguages::LANGUAGE_DEFAULT,
    self::LANG_NOT_AVAIL_ERR       => DEFAULT_LANG_NOT_AVAIL_ERROR_CONTENT,
    self::LANG_NOT_FOUND_ERR       => DEFAULT_LANG_NOT_FOUND_ERROR_CONTENT,
    self::FOOTER                   => DEFAULT_FOOTER_CONTENT,
    self::SHOW_CONT_TITLE          => self::TRUE_STR,
    self::SHOW_CONT_SUBTITLE       => self::FALSE_STR,
    self::HEADER_TITLE             => DEFAULT_HEADER_TITLE_CONTENT,
    self::CONT_SUBTITLE            => "",
    self::REDIRECT_NEAR            => "",
    self::REDIRECT_SILENT          => "",
    self::REDIRECT_FAR             => "",
    self::READABLE                 => self::TRUE_STR,
    self::REACHABLE                => self::TRUE_STR,
    self::VISIBLE                  => self::TRUE_STR,
    self::NEXT                     => self::AUTO_STR,
    self::PREV                     => self::AUTO_STR,
    self::HTTP_STATUS_CODE         => "",
    self::READABLE_ERROR           => DEFAULT_READABILITY_ERROR_CONTENT,
    self::CREATED                  => self::AUTO_STR, // if set to AUTO, the date will be obtained from the operating system
    self::LAST_EDIT                => self::AUTO_STR, // same as above
    );

  // returns "" if failed
  public static function GetDefault($param) // returns a default for a parameter
    {
    if (isset(self::$DEFAULTS[$param]))
      return self::$DEFAULTS[$param];

    return "";
    }
  }
?>
