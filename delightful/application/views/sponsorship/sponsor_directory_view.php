<?php

global $genumDateFormat, $glUserID;

echoT('<div class="directoryLetters">'.$strDirTitle.'</div><br>');

if (isset($strSponProgDDL)){
   $attributes = array('name' => 'frmSelectProg', 'id' => 'frmSelProg');
   echoT(form_open('sponsors/spon_directory/setProg/'
                        .($bShowInactive ? 'true' : 'false').'/A/0/'.$lRecsPerPage, $attributes));

   echoT($strSponProgDDL);
   echoT('
      <input type="submit" name="cmdSubmit" value="Refresh"
      style=" text-align: center; width: 70pt;"
      onclick="this.disabled=1; this.form.submit();"
      class="btn"
      onmouseover="this.className=\'btn btnhov\'"
      onmouseout="this.className=\'btn\'">'); 
}

echoT(form_close().'<br>');

if ($lNumSponProgs > 0){
   if ($lNumRecsTot > 0){   
      openUserDirectory(
                    $strDirLetter,
                    $strLinkBase,          $strImgBase,        $lNumRecsTot,
                    $directoryRecsPerPage, $directoryStartRec, $lNumDisplayRows);
   }else {
      echoT('There are no sponsors that meet your search criteria.<br>');
   }
   echoT($strLinkToggleActive);
}else {
   echoT('There are no sponsorship programs defined! Please configure your sponsorship programs.<br>');
}

function openUserDirectory(
                    $strDirLetter,
                    &$strLinkBase, &$strImgBase, &$lTotRecs,
                    $lRecsPerPage, $lStartRec,   $lNumThisPage){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if ($strDirLetter.''==''){
      $strUsers = 'All Sponsors';
   }else {
      $strUsers = '"'.$strDirLetter.'"';
   }

   echoT(
      '<br>
       <table class="enpRpt" id="myLittleTable">
           <tr>
              <td class="enpRptLabel">
                 Sponsor Directory: <b>'.$strUsers.'</b>
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
   echoT(
      '<i>Showing records '.($lStartRec+1).' to '.($lStartRec+$lNumThisPage)
         ." ($lTotRecs total)</i>");

   echoT('</td></tr></table><br>');
 
}
