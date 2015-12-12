<?php
//---------------------------------------------------------------------
// copyright (c) 2011-2013
// Austin, Texas 78759
//
// Serving the Children of India
// 
// author: John Zimmerman
//---------------------------------------------------------------------
// strDBValueConvert_SNG       ($sngValue, $lNumDecimals)
// strDBValueConvert_INT       ($lValue)
// strDBValueConvert_BOOL      ($bValue)
// strDBValueConvert_Date      ($dteValue)
// strDBValueConvert_DateTime  ($dteValue)
// strDBValueConvert_String    ($strValue)
/*---------------------------------------------------------------------
      $this->load->helper('dl_util/util_db');
---------------------------------------------------------------------*/

function strDBValueConvert_SNG($sngValue, $lNumDecimals){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if (is_null($sngValue)){
      $strValue = 'null';
   }else {
      $strValue = number_format($sngValue, $lNumDecimals, '.', '');
   }
   return($strValue);
}

function strDBValueConvert_INT($lValue){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if (is_null($lValue)){
      $strValue = 'null';
   }else {
      $strValue = number_format($lValue, 0, '.', '');
   }
   return($strValue);
}

function strDBValueConvert_BOOL($bValue){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if (is_null($bValue)){
      $strValue = 'null';
   }else {
      $strValue = $bValue ? '1' : '0';
   }
   return($strValue);
}

function strDBValueConvert_Date($dteValue){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if (is_null($dteValue)){
      $strValue = 'null';
   }else {
      $strValue = strPrepDate($dteValue);
   }
   return($strValue);
}

function strDBValueConvert_DateTime($dteValue){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if (is_null($dteValue)){
      $strValue = 'null';
   }else {
      $strValue = strPrepDateTime($dteValue);
   }
   return($strValue);
}

function strDBValueConvert_String($strValue){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if (is_null($strValue)){
      $strValue = 'null';
   }else {
      $strValue = strPrepStr($strValue);
   }
   return($strValue);
}


?>