<?php

require_once("html/htmlutils.php");

class TLanguageSelectorData
  {
  public $status   = self::NOT_FOUND;
  public $hrefs    = array(); // array of strings
  public $labels   = array(); // array of strings
  public $ids      = array(); // language ids (NLanguages::$LANGUAGE_ARRAY)
  public $selected = -1; // -1 means "nothing", else it's an index in $hrefs/$labels

  // for $status
  const AUTO_DETECTED    = 0;
  const SELECTED_BY_USER = 1;
  const NOT_FOUND        = 2; // language does not exists
  const NOT_AVAILABLE    = 3; // not available for current document
  }

function WriteLanguageSelector($data)
  {
  if (!isset($data) || count($data->hrefs) !== count($data->labels))
    return;

  $borderclass = ""; // class for the border (see CSS)
  switch ($data->status)
    {
    case TLanguageSelectorData::AUTO_DETECTED:
      $borderclass = " headerlanguageauto";
      break;
    case TLanguageSelectorData::SELECTED_BY_USER:
      $borderclass = " headerlanguagesel";
      break;
    case TLanguageSelectorData::NOT_FOUND:
      $borderclass = " headerlanguagenotf";
      break;
    case TLanguageSelectorData::NOT_AVAILABLE:
      $borderclass = " headerlanguagenota";
      break;
    }

  ?>
  <div class="headerlanguage<?php echo $borderclass; ?>">
    <table class="headerlanguagetable">
      <tr>
        <?php
          $langcount = count($data->hrefs);
          for ($i = 0; $i < $langcount; $i++)
            {
            $selclass = ""; // add this class if it's selected
            if ($data->selected === $i)
              $selclass = " headerlanguagelinksel";

            echo "<td>".AHrefBegin("headerlanguagelink".$selclass,$data->hrefs[$i],
              NLanguages::$LANGUAGE_ARRAY[$data->ids[$i]]).$data->labels[$i].ANCHOR_END."</td>";
            }
        ?>
      </tr>
    </table>
  </div>
  <?php
  }

// write language error on top of the body body (if needed)
// uses the same data format of WriteLanguageSelector
function WriteLanguageErr($data)
  {
  $errString = "";

  switch ($data->status)
    {
    case TLanguageSelectorData::NOT_FOUND:
      $errString = "This site was unable to determine your preferred language.".
                   " You may leave it or select another language from the box in the upper right corner, if available.<br />".
                   "Il sito non &egrave; stato capace di determinare la vostra lingua preferita.".
                   " Potete abbandonarla o selezionare un'altra lingua dal riquadro in alto a destra, se disponibile.";
      break;
    case TLanguageSelectorData::NOT_AVAILABLE:
      $errString = "We're sorry, but this page is not available in the language you requested.".
                   " You may leave it or select another language from the box in the upper right corner.<br />".
                   "Siamo spiacenti, ma questa pagina non &egrave; disponibile nella lingua che avete richiesto.".
                   " Potete abbandonarla o selezionare un'altra lingua dal riquadro in alto a destra.";
      break;
    }

  if ($errString !== "")
    {
    ?>
    <div class="bodylangerr">
      <div class="bodylangerrborder">
        <p class="bodylangerrp">
          <?php echo $errString; ?>
        </p>
      </div>
    </div>
    <?php
    }
  }
?>
