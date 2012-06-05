<?php

include_once("html/htmlutils.php");

require_once("element/defines.php");

class TIndexTreeElem
  {
  public $name = "";
  public $href = "";
  public $comment = "";
  public $marked = FALSE;
  public $titleonly = FALSE; // if TRUE, the title won't be a link
  public $directory = FALSE; // if TRUE, the link will be highlighted as directory
  public $childs = array(); // array of TIndexTreeElem
  public $linklanguage = NLanguages::LANGUAGE_DEFAULT; // language parameters for the links
  }

define("WRITE_INDEX_MAX_DEPTH",10);    // prevent infinite recursion
function WriteIndexPivot($tree,$depth) // recursive version of WriteIndex
  {
  if ($depth > WRITE_INDEX_MAX_DEPTH)
    return; // max depth exceeded

  $treecount = count($tree);
  if ($treecount === 0)
    return;

  ?>
      <ul class="bodyindexul bodyindexul<?php echo (string)$depth ?>">
        <?php
        for ($i = 0; $i < $treecount; $i++)
          {
          echo "<li";
          if ($tree[$i]->directory)
            echo " class=\"bodyindexdirli\"";
          echo ">";
          if (!$tree[$i]->titleonly)
            echo AHrefBegin("bodyindexhref".($tree[$i]->marked ? " bodyindexselhref" : ""),$tree[$i]->href,$tree[$i]->linklanguage);
            else
              echo $tree[$i]->marked ? "<span class=\"bodyindexsel\">" : ""; // even titles may be marked


          echo $tree[$i]->name;

          if (!$tree[$i]->titleonly)
            echo ANCHOR_END;
              else
                echo $tree[$i]->marked ? "</span>" : "";        

          if ($tree[$i]->comment !== "")
            {
            echo LINEBREAK."<span class=\"bodyindexcomment\">";
            echo $tree[$i]->comment;
            echo "</span>";
            }

          WriteIndexPivot($tree[$i]->childs,$depth+1);
             
          echo "</li>\r\n";
          }  
        ?>
      </ul>
  <?php
  }

function WriteIndex($tree) // $tree is an array of TIndexTreeElem
  {
  if (count($tree) === 0)
    return;

  echo "<div class=\"bodyindex\">\r\n<div class=\"bodyindexborder\">\r\n";

  WriteIndexPivot($tree,1);
  
  echo "</div>\r\n</div>\r\n";
  }
?>
