<?php

require_once("content/defines.php");

// string utility to parse a command
class NCommandParser
  {
  const CHAR_CMD_END = CHAR_CLOSE_SQUARE; // end of command
  const CHAR_PAR_SEP = " "; // parameter separator
  const CHAR_VAL_SEP = "="; // value separator

  const CHAR_SPEC_BEGIN = "\""; // begin a special substring of a string
  const CHAR_SPEC_END = "\"";   // ends a special substring of a string
  const CHAR_SPEC_ESC = "\\";   // escape character
  const CHAR_SPEC_END_SUBST = "\""; // CHAR_SPEC_ESC.CHAR_SPEC_END_SUBST will write CHAR_SPEC_END
  const CHAR_SPEC_ESC_SUBST = "\\"; // CHAR_SPEC_ESC.CHAR_SPEC_ESC_SUBST will write CHAR_SPEC_ESC
  const CHAR_SPEC_EOL_SUBST = "n";  // CHAR_SPEC_ESC.CHAR_SPEC_EOL_SUBST will write an EOL character
  const CHAR_SPEC_TAB_SUBST = "t";  // CHAR_SPEC_ESC.CHAR_SPEC_TAB_SUBST will write a TAB character

  // parses a command in $string starting at $offset
  // returns an array of arrays of strings
  // $offset is advanced past the closing ] or at strlen($str) if not found
  // example: aa=3 k"k\"\n"=bb=4 cc] produces:
  // $result[0][0]        aa
  // $result[0][1]        3
  // $result[1][0]        kk" followed by an EOL
  // $result[1][1]        bb
  // $result[1][2]        4
  // $result[3][0]        cc
  // The result is always an empty array or an array of array. 
  // An array of empty arrays is never returned.
  public static function Parse($str,&$offset)
    {
    if (!is_string($str) || !is_int($offset))
      {
      error_log("NCommandParser::Parse (CommandParser.php) called with bad parameters.");
      return array();
      }

    $resultcount = 0;
    $result = array();

    $len = strlen($str);
    for ($offset; $offset < $len; )
      {
      switch ($str[$offset])
        {
        case self::CHAR_PAR_SEP:
          break;
        case self::CHAR_CMD_END:
          $offset++;
          return $result;
        default:
          $result[$resultcount++] = self::ParseParam($str,$len,$offset);
          break;
        }

      $offset = self::FindFirstNotOf($str,$offset,self::CHAR_PAR_SEP); // skip all the spaces
      }
    return $result;
    }

  private static function ParseParam($str,$len,&$offset)
    {
    $resultcount = 0;
    $result = array();

    for ($offset; $offset < $len; )
      {
      switch ($str[$offset])
        {
        case self::CHAR_PAR_SEP: // end of parameter
        case self::CHAR_CMD_END:
          return $result;
        default:
          $result[$resultcount++] = self::ParseValue($str,$len,$offset);
          break;
        }
      }
    return $result;
    }

  // self::CHAR_CMD_END.self::CHAR_VAL_SEP.self::CHAR_PAR_SEP.self::CHAR_SPEC_BEGIN
  const PARSEVALUE_END = "]= \"";
  private static function ParseValue($str,$len,&$offset)
    {
    $buffer = "";
    for ($offset; $offset < $len; )
      {
      $endOfValue = self::FindFirstOf($str,$offset,self::PARSEVALUE_END);
      if (($add = substr($str,$offset,$endOfValue - $offset)) !== FALSE)
        $buffer .= $add;
      $offset = $endOfValue;

      if ($offset >= $len)
        return $buffer; // end of file

      switch ($str[$offset])
        {
        case self::CHAR_VAL_SEP:
          $offset++;
          return $buffer;
        case self::CHAR_SPEC_BEGIN:
          $offset++;
          $buffer .= self::ParseSpecial($str,$len,&$offset);
          break;
        default:
          return $buffer;
        }
      }
    return $buffer;
    }

  private static function ParseSpecial($str,$len,&$offset)
    {
    $buffer = "";

    for ($offset; $offset < $len; )
      {
      $endOfSpecial = self::FindFirstOf($str,$offset,self::CHAR_SPEC_END.self::CHAR_SPEC_ESC);
      if (($add = substr($str,$offset,$endOfSpecial - $offset)) !== FALSE)
        $buffer .= $add;
      $offset = $endOfSpecial;

      if ($offset >= $len)
        return $buffer; // end of file

      switch ($str[$offset])
        {
        case self::CHAR_SPEC_ESC:
          $offset++;
          if ($offset < $len)
            {
            switch($str[$offset])
              {
              case self::CHAR_SPEC_END_SUBST:
                $buffer .= CHAR_SPEC_END;
                break;
              case self::CHAR_SPEC_ESC_SUBST:
                $buffer .= self::CHAR_SPEC_ESC;
                break;
              case self::CHAR_SPEC_EOL_SUBST:
                $buffer .= "\n";
                break;
              case self::CHAR_SPEC_TAB_SUBST:
                $buffer .= "\t";
                break;
              }
            $offset++;
            }
          break;
        case self::CHAR_SPEC_END:
          $offset++;
          return $buffer;
        }
      }
    return $buffer;
    }

  // finds the first character in $string starting from $firstpos that matches
  // one of the characters in $chars
  // returns an absolute position or strlen($string) if not found
  public static function FindFirstOf($string,$firstpos,$chars)
    {
    return $firstpos + strcspn($string,$chars,$firstpos);
    }

  // same as above, but does NOT matches $chars
  public static function FindFirstNotOf($string,$firstpos,$chars)
    {
    return $firstpos + strspn($string,$chars,$firstpos);
    }
  }

?>
