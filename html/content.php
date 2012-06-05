<?php

require_once("html/htmlutils.php");

require_once("element/defines.php");

class TContentData
  {
  public $toRight = FALSE;        // if TRUE, the class bodycontentright wil be added to the div "bodycontent"

  public $title = "";             // document title
  public $subtitle = "";          // document subtitle

  public $nextAddr = FALSE;       // change to a string to make the "next" link appear
  public $nextLabel = "";
  public $prevAddr = FALSE;
  public $prevLabel = "";

  public $HScontent = "<p></p>";  // a string, that will be written directly inside the div (must be HTML-SAFE)
  public $HSautointros = array(); // automatic index introduction, ignored if not set (index is TElementType::SECTION, TElementType::DIRECTORY)

  public $linklanguage = NLanguages::LANGUAGE_DEFAULT;
  }

function WriteContent($data)
  {
  if (!isset($data))
    return;

  ?>
  <div class="bodycontent<?php if ($data->toRight) echo " bodycontentright"?>">
    <?php // this is needed to shift the left border 20px to the right, otherwise we have some strange behavior
    ?>
    <div class="bodycontentborder<?php if ($data->toRight) echo " bodycontentborderright"?>">
      <?php
        if ($data->title !== "")
          {
          echo "<h1 class=\"bodycontenttitle\">".$data->title;
          if ($data->subtitle !== "")
            echo LINEBREAK."<span class=\"bodycontentsubtitle\">".$data->subtitle."</span>\r\n";
          echo "</h1>\r\n";
          }
      ?>

      <div class="bodycontentcontent">
        <?php echo $data->HScontent; ?>
      </div>
      
      <?php // content footer: add NEXT and PREV buttons if needed
        if (($data->nextAddr !== FALSE) || ($data->prevAddr !== FALSE))
          {
          ?>
          <div style="clear: both;">&nbsp;</div>
          <div class="bodycontentfooter">
            <?php
              if ($data->prevAddr !== FALSE)
                {
                ?>
                <div class="bodycontentfooterprev">
                  <div class="bodycontentfooterborder">
                    <img src="aux/prevsh.png" alt="" />
                    <?php
                      echo AHrefBegin("bodytexta",$data->prevAddr,$data->linklanguage).$data->prevLabel.ANCHOR_END;
                    ?>
                  </div>
                </div>
                <?php
                }

              if ($data->nextAddr !== FALSE)
                {
                ?>
                <div class="bodycontentfooternext">
                  <div class="bodycontentfooterborder">
                    <img src="aux/nextsh.png" alt="" style="float: right;" />
                    <?php
                      echo AHrefBegin("bodytexta",$data->nextAddr,$data->linklanguage).$data->nextLabel.ANCHOR_END;
                    ?>
                  </div>
                </div>
                <?php
                }
              ?>
          </div>
          <div style="clear: both;">&nbsp;</div>
        <?php
          }
      ?>
    </div>
  </div>
  <?php
  }

?>
