<?php
// use ONLY require/include_once for this file

require_once("element/defines.php");
require_once("element/HeaderParameters.php");

require_once("physical/dirutils.php");

class TElementType
  {
  const NONE       = 0;
  const DIRECTORY  = 1;
  const SECTION    = 2;
  const MAX_ELEMENT_TYPE = 3;
  }

abstract class TElementData
  {
  abstract public function GetAddress();   // gets the full address of the element (to be used in URLS)
  abstract public function GetTitle();     // gets a name for the element (its title)

  abstract public function GetIndex();         // returns an array of subdirectories or sections, relative to directory
  abstract public function GetChildSections(); // returns an array of child sections, relative to current section (or all the sections of a directory)

  abstract public function GetParent();     // build the parent element
  abstract public function IsValid();       // returns FALSE if error occurred during creation, TRUE otherwise
  abstract public function GetContent();    // returns a string, empty if no content

  abstract public function HasParam($name); // returns TRUE or FALSE
  abstract public function GetParam($name); // returns a string or "" if failed WARNING: use GetParamDefault to obtain a TParam object

  // if FALSE, other object won't be allowed to link to this object
  // this still allows to reach the object if a direct link is typed in the browser bar
  public function IsReachable()
    {
    return $this->GetParamDefault(NParams::REACHABLE)->ToBool() && $this->IsVisible();
    }

  // if FALSE, the content (and only the content) will be hidden
  public function IsReadable()
    {
    return $this->GetParamDefault(NParams::READABLE)->ToBool() && $this->IsVisible();
    }

  // the page behaves as if it does not exist
  public function IsVisible()
    {
    return $this->GetParamDefault(NParams::VISIBLE)->ToBool();
    }

  // "" if none
  public function GetSubTitle()
    {
    return $this->GetParamDefault(NParams::CONT_SUBTITLE)->ToString();
    }

  public function GetParamDefault($name) // returns the parameter OR its default if failed (so it never fails)
                                         // returns a TParam
    {
    if ($this->HasParam($name))
      return new TParam($this,$name,$this->GetParam($name));

    return new TParam($this,$name);
    }

  public function GetRedirectNear() // a string or "" if none
    {
    return $this->GetParamDefault(NParams::REDIRECT_NEAR)->ToString();
    }
    
  public function GetRedirectSilent() // a string or "" if none
    {
    return $this->GetParamDefault(NParams::REDIRECT_SILENT)->ToString();
    }

  public function GetHeaderTitle() // a string or "" if none
    {
    return $this->GetParamDefault(NParams::HEADER_TITLE)->ToString();
    }

  public function GetFooter() // a string
    {
    return $this->GetParamDefault(NParams::FOOTER)->ToString();
    }

  abstract public function GetNextAddress(); // addresses of prev and next element read from the directory index
  abstract public function GetPrevAddress(); // returns a string or FALSE if none

  public function GetType()
    {
    return TElementType::NONE;
    }

  public function IsRoot() // returns TRUE if root element, FALSE otherwise
    {
    return FALSE;
    }

  public function HasContent()
    {
    return FALSE;
    }

  public function HasIndex()
    {
    return FALSE;
    }

  // time functions to query the operating system
  abstract protected function GetLastEditTimePhys(); // returns an int (unix timestamp): the file last edit time
  abstract protected function GetCreationTimePhys();  // returns an int (unix timestamp): the file creation time

  // time functions (return FALSE if failed, but should never happen unless !IsValid or filesystem failure)
  public function GetLastEditTime()
    {
    $result = $this->GetParamDefault(NParams::LAST_EDIT);
    if ($result->IsAuto()) // obtain automatically from the operating system
      return $this->GetLastEditTimePhys();

    if ($result->IsFalse())
      return FALSE; // disabled

    $result = $result->ToDate();
    if ($result === FALSE) // conversion error
      return $this->GetLastEditTimePhys();

    return $result;
    }

  public function GetCreationTime()
    {
    $result = $this->GetParamDefault(NParams::CREATED);
    if ($result->IsAuto()) // obtain automatically from the operating system
      return $this->GetCreationTimePhys();

    if ($result->IsFalse())
      return FALSE;

    $result = $result->ToDate();
    if ($result === FALSE) // conversion error
      return $this->GetCreationTimePhys();

    return $result;
    }
  }

require_once("element/ElementFactory.php");
?>
