<?php

require_once("highlighter/hyperlight/hyperlight.php");

require_once("html/htmlutils.php");

/* this class is a wrapper for the third-party code highlighter
 * - a constructor with parameters:
 *   - $lang: the language (a string)
 *   - $code: the text that must be highlighted
 *
 * - a method ProduceHTML()
 *   returns a string with valid HTML, containing the highlighted code
 *   valid HTML must be returned even if the language is unknown
 */

/* the class is currently configured to use Hyperlight
 * http://code.google.com/p/hyperlight/source/checkout
 */

class TContentHighlighter
  {
  private static $LANGUAGES = array(
    "c++"     => "cpp",
    "cpp"     => "cpp",
    "c#"      => "csharp",
    "csharp"  => "csharp",
    "css"     => "css",
    "iphp"    => "iphp",
    "phpcode" => "iphp",
    "php"     => "php",
    "phppage" => "php",
    "python"  => "python",
    "py"      => "python",
    "vb"      => "vb",
    "visualbasic" => "vb",
    "xml"     => "xml",
    "html"    => "xml",
    );

  public function __construct($lang,$code)
    {
    $llang = strtolower($lang);
    $this->code = $code;

    if (isset(self::$LANGUAGES[$llang]))
      $this->hl = new HyperLight(self::$LANGUAGES[$llang]);
    }

  public function ProduceHTML()
    {
    if ($this->hl === FALSE)
      return TrueHtmlEntities($this->code);

    return $this->hl->render($this->code);
    }

  private $code;
  private $hl = FALSE;
  }

?>
