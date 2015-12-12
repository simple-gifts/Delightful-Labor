<?php
//---------------------------------------------------------------------
// copyright (c) 2012-2014 by Database Austin
/*---------------------------------------------------------------------
      $this->load->helper('dl_util/directory');
---------------------------------------------------------------------*/
// screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
//traceFilepath(__FILE__);

function strDisplayDirectory($strLinkBase,       $strClassExtra, $strMatchAlpha,
                             $bIncludeRecLimits, $lStart,        $lPerPage,
                             $strExtra=''){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strOut = '';
   $strMatchAlpha = strtoupper($strMatchAlpha);
   $strOut .= strDisplayDirSingleChar(
                   $strLinkBase, $strMatchAlpha, '*', $strClassExtra.' title="Show All" ',
                   $bIncludeRecLimits, $lStart, $lPerPage).'&nbsp;&nbsp;&nbsp;'
             .strDisplayDirSingleChar(
                   $strLinkBase, $strMatchAlpha, '#', $strClassExtra.' title="Names that start with a non-alphabetic character" ',
                   $bIncludeRecLimits, $lStart, $lPerPage);
   $strOut .= '&nbsp;&nbsp;&nbsp;&nbsp;';
   for ($idx=65; $idx<=90; ++$idx){
      $strAlpha = chr($idx);
      $strOut .= strDisplayDirSingleChar(
                      $strLinkBase, $strMatchAlpha, $strAlpha, $strClassExtra,
                      $bIncludeRecLimits, 0, $lPerPage);
   }
   return($strOut.$strExtra);
}

function strDisplayDirSingleChar(
                &$strLinkBase, $strMatchAlpha, $strAlpha, $strClassExtra,
                $bIncludeRecLimits, $lStart, $lPerPage){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if ($strMatchAlpha==$strAlpha){
      $strStartBold = '<b><font style="font-size: 11pt;">';
      $strEndBold = '</b></font>';
   }else {
      $strStartBold = $strEndBold = '';
   }
   if ($bIncludeRecLimits){
      $strAnchorLimits = '/'.$lStart.'/'.$lPerPage.'/';
   }else {
      $strAnchorLimits = '';
   }
   
   return(
         $strStartBold.anchor($strLinkBase.urlencode($strAlpha).$strAnchorLimits, $strAlpha, $strClassExtra)
        .$strEndBold.'&nbsp;&nbsp;');
}

function strNameWhereClauseViaLetter($strFieldName, $strDirLetter){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if ($strDirLetter=='*'){
      $strWhereName = ' ';
   }elseif ($strDirLetter=='#'){
      $strWhereName = ' AND ((LEFT('.$strFieldName.',1) < "A") OR (LEFT('.$strFieldName.',1) > "Z")) ';
   }else {
      $strWhereName = ' AND (LEFT('.$strFieldName.',1)='.strPrepStr($strDirLetter).') ';
   }   
   return($strWhereName);
}

function strSanitizeLetter($strLookupLetter){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strLookupLetter = strtoupper(substr($strLookupLetter, 0, 1));
   if (($strLookupLetter < 'A' || $strLookupLetter > 'Z') && ($strLookupLetter != '#')) $strLookupLetter = '*';
   return($strLookupLetter);
}



?>