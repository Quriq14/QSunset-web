<?php

// this contains a tree of strings
// all starting with the same character
class TSpecialStringTree
  {
  public function __construct()
    {
    $this->data = FALSE;
    }

  public function Add($str,$data)
    {
    if (!is_string($str) || !isset($data))
      return; // error

    if ($str === "") // the string is empty, all characters processed
      {
      $this->data = $data; // save data
      return;
      }

    $letter = $str[0];
    $newstr = substr($str,1);
    if ($newstr === FALSE)
      $newstr = ""; // for some reason PHP returns FALSE on empty substr result

    if (!isset($this->childs[$letter]))
      $this->childs[$letter] = new TSpecialStringTree();

    $this->childs[$letter]->Add($newstr,$data);
    }

  // find a known string inside $str starting from offset $offset
  // returns the $data Added or FALSE if not found
  public function Find($offset,$str,$depth = 0)
    {
    if ($offset >= strlen($str)) // processing ended: end of string
      return $this->data;

    $letter = $str[$offset];
    if (!isset($this->childs[$letter])) // processing ended: not found
      return $this->data;

    return $this->childs[$letter]->Find($offset + 1,$str,$depth + 1);
    }

  // removes a string from the tree
  public function Remove($str,$offset = 0)
    {
    if ($offset >= strlen($str))
      {
      $this->data = FALSE; // set to FALSE
      return; // processing ended: end of string
      }

    $letter = $str[$offset];
    if (!isset($this->childs[$letter])) // processing ended: not found
      return;

    $this->childs[$letter]->Remove($offset + 1,$str);
    if ($this->childs[$letter]->IsEmpty())
      unset($this->childs[$letter]); // if child is now empty, free some memory
    }

  public function IsEmpty()
    {
    return count($this->childs) === 0 && $this->data === FALSE;
    }

  private $data;
  private $childs = array(); // indexed by letters
  }


?>
