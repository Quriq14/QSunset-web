<?php

// this is a strange stack class
// object are pushed from the top
// but can be removed in any order, using their name
class TFormatStack
  {
  public function Push($name,$data)
    {
    $this->stack[++$this->max] = $data;
    $this->names[$this->max] = $name;
    $this->pos[$name] = $this->max;
    }

  // FALSE if empty
  public function Top()
    {
    if ($this->max === 0)
      return FALSE;

    return $this->stack[$this->max];
    }

  // FALSE if not found
  public function Find($name)
    {
    if (!isset($this->pos[$name]))
      return FALSE;

    return $this->stack[$this->pos[$name]];
    }
    
  public function Remove($name)
    {
    if (!isset($this->pos[$name]))
      return;

    $pos = $this->pos[$name];

    unset($this->stack[$pos]);
    unset($this->names[$pos]);
    unset($this->pos[$name]);

    // decrease max to the first valid value
    while ($this->max > 0 && !isset($this->stack[$this->max]))
      $this->max--;
    }

  private $max = 0;
  private $stack = array();
  private $names = array();
  private $pos = array();
  }

?>
