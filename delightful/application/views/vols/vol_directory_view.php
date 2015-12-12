<?php

global $genumDateFormat, $glUserID;

echoT('<div class="directoryLetters">'.$strDirTitle.'</div><br>');

if ($lNumRecsTot > 0){   
   openUserDirectory(
                 $strDirLetter,
                 $strLinkBase,          $strImgBase,        $lNumRecsTot,
                 $directoryRecsPerPage, $directoryStartRec, $lNumDisplayRows);
}

function openUserDirectory(
                    $strDirLetter,
                    &$strLinkBase, &$strImgBase, &$lTotRecs,
                    $lRecsPerPage, $lStartRec,   $lNumThisPage){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if ($strDirLetter.''==''){
      $strUsers = 'All Users';
   }else {
      $strUsers = '"'.$strDirLetter.'"';
   }

   echoT(
      '<br>
       <table class="enpRpt" id="myLittleTable">
           <tr>
              <td class="enpRptLabel">
                 Volunteer Directory: <b>'.$strUsers.'</b>
              </td>
           </tr>
           <tr>
              <td class="recSel">');

   $strLinkBase = $strLinkBase.(is_null($strDirLetter) ? 'showAll' : $strDirLetter).'/';
   $strImgBase = base_url().'images/dbNavigate/rs_page_';
   
   $opts = new stdClass;
   $opts->strLinkBase       = $strLinkBase;
   $opts->strImgBase        = $strImgBase;
   $opts->lTotRecs          = $lTotRecs;
   $opts->lRecsPerPage      = $lRecsPerPage;
   $opts->lStartRec         = $lStartRec;
   echoT(set_RS_Navigation($opts));
   
//   echoT(set_RS_Navigation($strLinkBase, $strImgBase, $lTotRecs,
//                    $lRecsPerPage, $lStartRec));
   echoT(
      '<i>Showing records '.($lStartRec+1).' to '.($lStartRec+$lNumThisPage)
         ." ($lTotRecs total)</i>");

   echoT('</td></tr></table><br>');
 
}
