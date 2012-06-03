<?php
// use require_once

require_once("html/htmlutils.php");

// $data MUST be a string (empty for none)
function WriteFooter($data,$timer1,$timer2)
  {
  ?>
  <div class="bodyfooter">
    <div class="bodyfooterborder">
      <p class="bodyfooterp">
        <?php
          if (isset($data) && is_string($data) && $data !== "")
            echo $data.LINEBREAK;
        ?>
        Site Designed by Me<br />
        Response time: <?php echo (string)($timer2); ?> milliseconds.<br />
        Page generated in: <?php echo (string)($timer1); ?> milliseconds.
      </p>
    </div>
  </div>
  <?php
  }

?>
