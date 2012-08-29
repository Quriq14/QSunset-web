<?php

require_once("content/ParseError.php");
require_once("content/Producer.php");

class NFormatCondition
  {
  const COND_BOOL      = "bool"    ; // bool=string casts string to bool (everything except "FALSE" evaluates to TRUE)
  const COND_DECLARED  = "declared"; // declared=obj returns TRUE if a symbol or a format named obj exists in DATA_INFO
  const COND_NOT       = "not"     ; // not=statement negates statement
  const COND_ACTIVE    = "active"  ; // active=obj returns TRUE if an object named "obj" is active (if DATA_PRODUCER undefined)
                                     // or exists in the producer (if DATA_PRODUCER is a producer)

  const DATA_PRODUCER  = 0         ; // in the data array, the index of a THtmlProducer

  // evaluates the condition $condition (an array of strings) starting from $condindex
  // may return TRUE, FALSE or some other type of result
  // it returns FALSE by default (if error)
  // $condindex will be set to the first element not yet evaluated
  // $data is an array with extra data that can be useful (see DATA_* keys)
  public static function Evaluate($info,$condition,&$condindex,$data)
    {
    if (!isset($condition[$condindex]))
      {
      NParseError::Error($info,NParseError::ERROR,NParseError::CONDITION_EXPECTED,array());
      return FALSE;
      }

    $current = $condition[$condindex];
    $condindex++;

    switch (strtolower($current))
      {
      case self::COND_BOOL:
        if (!isset($condition[$condindex]))
          {
          NParseError::Error($info,NParseError::WARNING,NParseError::CONDITION_NOPARAM,
            array(0 => self::COND_BOOL,1 => "TRUE"));
          return TRUE;
          }
        $cparam = $condition[$condindex];
        $condindex++;
        if (strtoupper($cparam) === "FALSE")
          return FALSE;
        return TRUE;
 
      case self::COND_DECLARED:
        if (!isset($condition[$condindex]))
          {
          NParseError::Error($info,NParseError::WARNING,NParseError::CONDITION_NOPARAM,
            array(0 => self::COND_DECLARED,1 => "FALSE"));
          return FALSE;
          }
        $cparam = $condition[$condindex];
        $condindex++;
        if ($cparam === "")
          return FALSE; // empty is invalid
        $result = $info->GetFormatByName(strtoupper($cparam));
        if ($result === FALSE)
          return FALSE;
        return TRUE;

      case self::COND_NOT:
        $result = self::Evaluate($info,$condition,$condindex,$data);
        if (is_bool($result))
          return !$result;

        NParseError::Error($info,NParseError::WARNING,NParseError::CONDITION_WRONG_TYPE,
          array(0 => "boolean",1 => self::COND_NOT));
        return FALSE;

      case self::COND_ACTIVE:
        if (!isset($condition[$condindex]))
          {
          NParseError::Error($info,NParseError::WARNING,NParseError::CONDITION_NOPARAM,
            array(0 => self::COND_DECLARED,1 => "FALSE"));
          return FALSE;
          }

        $name = strtoupper($condition[$condindex++]);

        if (isset($data[self::DATA_PRODUCER])) // if a producer is defined, search in it
          return $data[self::DATA_PRODUCER]->HasActiveFormat($name);

        return $info->IsAnySymbolActive($name);   // else, search in the status
        break;

      default:
        NParseError::Error($info,NParseError::ERROR,NParseError::CONDITION_UNKNOWN,array(0 => $current));
        return FALSE;
      }
    }
  }

?>
