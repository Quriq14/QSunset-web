<?php
// use ONLY require/include_once for this file

function WriteHead($title)
  {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="UTF-8" />
    <title><?php echo $title ?></title>
    <link rel="shortcut icon" href="aux/q.ico" />
    <link rel="stylesheet" type="text/css" href="aux/ric.css" />
  </head>
<?php
  }

function BeginBody()
  {
?>
  <body class="body">
<?php
  }

function WriteTail()
  {
?>
  </body>
</html>
<?php
  }

function BeginBodyBody()
  {
  echo "<div class=\"bodybody\">\r\n";
  }

function EndBodyBody()
  {
  echo "</div>\r\n";
  }
?>
