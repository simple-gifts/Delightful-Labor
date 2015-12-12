<?php
   if ($bSuppressNavBr){
      $strBR = '';
   }else {
      $strBR = '<br />';
   }

   if ($lTotRecs > 0){
      if (!is_null($strExport)){
         echoT(
            $strExport.$strBR);
      }      
      
      if ($bShowRecNav){
         echoT($strBR.'
                <table class="enpRpt" id="peopleDirTable">
                    <tr>
                       <td class="enpRptLabel">'
                          .$strNavRptTitle.'
                       </td>
                    </tr>
                    <tr>
                       <td class="recSel">');

         $strLinkBase = $strLinkBase.'/';
         $strImgBase = base_url().'images/dbNavigate/rs_page_';
         $opts = new stdClass;
         $opts->strLinkBase       = $strLinkBase;
         $opts->strImgBase        = $strImgBase;
         $opts->lTotRecs          = $lTotRecs;
         $opts->lRecsPerPage      = $lRecsPerPage;
         $opts->lStartRec         = $lStartRec;
         $opts->strNavLinkExtra   = $strNavLinkExtra;
         echoT(set_RS_Navigation($opts));
         
         echoT(
            '<i>Showing records '.($lStartRec+1).' to '.($lStartRec+$lNumThisPage)
               ." ($lTotRecs total)</i>");

         echoT('</td></tr></table><br>');
      }
   }
