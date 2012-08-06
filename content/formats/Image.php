<?php

require_once("content/FormatStatus.php");

require_once("element/defines.php");

class TImageFormat extends TFormatStatus
  {
  // $x is FALSE if the image is local (IMG), TRUE otherwise (XIMG)
  public function __construct($x)
    {
    $this->x = $x;
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
    return TRUE;
    }

  const SRC = "SRC";
  const LABEL = "LABEL";
  const LEFT = "LEFT";   // floating images
  const RIGHT = "RIGHT";
  private function ParseAttribs($attribs,&$name,&$label,&$float)
    {
    $name = FALSE;
    $label = FALSE;

    // parse the attributes
    $attribcount = count($attribs);
    for ($i = 1; $i < $attribcount; $i++)
      switch (strtoupper($attribs[$i]))
        {
        case self::SRC:
          if (isset($attribs[$i+1]));
            $name = $attribs[++$i];
          break;
        case self::LABEL:
          if (isset($attribs[$i+1]))
            $label = $attribs[++$i];
          break;
        case self::LEFT:
          $float = self::LEFT;
          break;
        case self::RIGHT:
          $float = self::RIGHT;
          break;
        }
    }

  public function Pulse($info,$attribs)
    {
    $name = FALSE;
    $label = FALSE;
    $float = FALSE;
    $this->ParseAttribs($attribs,$name,$label,$float);

    if ($name === FALSE || $name === "")
      return ""; // no name

    $result = "";

    switch ($float)
      {
      case self::LEFT:
        $result .= "<div class=\"bodytextimgfloatleft\">";
        break;
      case self::RIGHT:
        $result .= "<div class=\"bodytextimgfloatright\">";
        break;
      }

    $result .= "<img src=\"";
    if (!$this->x)
      $result .= htmlspecialchars(IMAGE_ROOT);
    $result .= htmlspecialchars($name)."\"";

    if ($label !== FALSE)
      $result .= " alt=\"".htmlspecialchars($label)."\"";

    $result .= " />";

    if ($float !== FALSE)
      $result .= "</div>\r\n";

    return $result;
    }

  private $x;
  }

?>
