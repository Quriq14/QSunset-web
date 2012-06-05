<?php
require_once("html/htmlutils.php");
require_once("html/language.php");

require_once("element/defines.php");

class THeaderTreeElem
  {
  public $names = array();
  public $paths = array();
  public $marked = -1; // -1 is "none"
  public $titleonly = array(); // if true, no link will be added
  }

class THeaderData
  {
  public $title = "";          // title of current element
  public $rootName = "";       // name of the root directory
  public $rootPath = "";       // path of the root directory
  public $fileLists = array(); // array of THeaderTreeElem

  public $linklanguage = NLanguages::LANGUAGE_DEFAULT;
  }

function WriteHeader($data,$langdata)
  {
  if (!isset($data))
    return;

  echo "<div class=\"bodyheader\">\r\n";

  if (isset($langdata))
    WriteLanguageSelector($langdata);

  if ($data->title !== "")
    {
    ?>
    <h1 class="bodyheadertitle"><?php echo $data->title ?></h1>
    <?php
    }

  // vertical for
  $isFirstLine = TRUE;
  for ($depth = count($data->fileLists)-1; $depth >= 0; $depth--)
    {
    $fl = $data->fileLists[$depth];
    if (count($fl->names) === count($fl->paths) && count($fl->paths) > $fl->marked)
      {
      ?>
        <div class="bodyheaderdirdiv<?php if (!$isFirstLine) echo " bodyheaderdirdiv2"; ?>">
          <span class="bodyheaderdirgroupspan">
            <?php
            // horizontal for
            for ($i = 0; $i < count($fl->names); $i++)
              {
              $marked = $i === $fl->marked;

              echo "<span class=\"bodyheaderdir";
              if ($marked)
                echo " bodyheaderdirmarked";
              echo "\">";

              if (!$fl->titleonly[$i])
                echo AHrefBegin("bodyheaderhref".($marked ? " bodyheaderselhref" : ""),$fl->paths[$i],$data->linklanguage);

              echo TrueHtmlEntities($fl->names[$i]);

              if (!$fl->titleonly[$i])
                echo ANCHOR_END;
      
              echo "</span>";
              }
            echo "\r\n";
            ?>
          </span>
        </div>
      <hr class="bodyheaderhr" />
      <?php
      $isFirstLine = FALSE; // no first line anymore
      }
    }

  echo "</div>\r\n";
  }
?>
