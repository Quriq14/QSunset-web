<?php

require_once("content/ParserInfo.php");
require_once("content/defines.php");
require_once("content/formats/TreeStack.php");

// these static functions are used by the formats with strict container behavior
// and must be closed in reverse order
// so that [OBJ1 BEGIN][OBJ2 BEGIN][OBJ2 END][OBJ1 END] is legal
// but [OBJ1 BEGIN][OBJ2 BEGIN][OBJ1 END][OBJ2 END] is not
abstract class NParserTreeStack
  {
  const KEY = "NParserTreeStack";

  // increase the level of the stack by one with a new symbol
  // $name is the name of the symbol
  // $topsymbname is the name of the top symbol
  public static function IncDepth($info,$name,$topsymbname)
    {
    $cstack = $info->GetFormatData(self::KEY,array());
    $cstack[count($cstack)] = array(0 => $name, 1 => $topsymbname);
    $info->SetFormatData(self::KEY,$cstack);
    }

  // decrease the level of the stack by one if the top of the stack contains the right symbol name and topsymbol
  // returns FALSE if not matching, TRUE otherwise
  public static function DecDepth($info,$expectedname,$topsymbname)
    {
    $cstack = $info->GetFormatData(self::KEY,array());
    $cstacktopidx = count($cstack) - 1;

    if ($cstacktopidx < 0) // the stack is empty
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::TREESTACK_STACK_EMPTY,array(0 => $expectedname,1 => $topsymbname));
      return FALSE;
      }

    if ($cstack[$cstacktopidx][0] !== $expectedname || // the top of the stack does not match expectations
      $cstack[$cstacktopidx][1] !== $topsymbname)
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::TREESTACK_NOT_MATCH,array(0 => $expectedname,1 => $topsymbname,
        2 => $cstack[$cstacktopidx][0],3 => $cstack[$cstacktopidx][1]));
      return FALSE;
      }

    unset($cstack[$cstacktopidx]);
    $info->SetFormatData(self::KEY,$cstack);
    return TRUE;
    }

  public static function GetDepth($info)
    {
    $cstack = $info->GetFormatData(self::KEY,array());
    return count($cstack);
    }

  public static function GetExpected($info)
    {
    $cstack = $info->GetFormatData(self::KEY,array());
    $cstackcount = count($cstack);

    if ($cstackcount === 0)
      return FALSE;

    return $cstack[$cstackcount - 1];
    }
  }

?>
