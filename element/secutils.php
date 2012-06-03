<?php
// use ONLY require/include_once for this file

define("SPACES_REGEX","/\s/");

// returns the section name of a SUBSECTION, returns the whole section name if error or a SECTION is provided
function ExtractSection($id)
  {
  $index = -1;
  if (preg_match(FILE_SUBSECTION_REGEX, $id, $matches, PREG_OFFSET_CAPTURE)) // this gets the index of the first match
    $index = $matches[0][1];                                                 // it's ugly, but the only way to go
    
  if ($index === -1 || $index === 0) // no match
    return $id;

  return substr($id,0,$index);
  }

function CompressSectionId($id) // section ids may or may not contain spaces in original file, they must be removed
  {
  return preg_replace(SPACES_REGEX,"",$id);
  }
?>
