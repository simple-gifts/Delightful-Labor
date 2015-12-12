<?php
   global $genumDateFormat;

      // reports for other users
   echoT(form_open('creports/custom_directory/viewUserSel'));
   echoT(
      '<table border="0">
           <tr>
              <td>'
                 .strLinkAdd_CReport('Add new report', true) .'&nbsp'
                 .strLinkAdd_CReport('Add new report', false).'
              </td>
              <td width="30px">&nbsp;</td>
              <td>
                 View reports for <select name="ddlUser">');

   foreach ($cUserRpts as $cU){
      echoT('<option value="'.$cU->lUserID.'" '.($cU->lUserID==$userRec[0]->us_lKeyID ? 'SELECTED' : '').'>'
                 .$cU->strSafeNameFL.' ('.$cU->lRptCnt.')</option>'."\n");
   }

   echoT('
         </select>
         <input type="submit" name="cmdSearch" value="View"
              style="width: 47pt;"
              onclick="this.disabled=1; this.form.submit();"
              class="btn"
                 onmouseover="this.className=\'btn btnhov\'"
                 onmouseout="this.className=\'btn\'">
              </td>
           </tr>
        </table>'.form_close('<br>'));

   if ($lNumRpts==0){
      echoT('<i>There are no custom reports for <b>'.$userRec[0]->strSafeName.'</b>.</i><br><br>');
      return;
   }

   echoT('
      <table class="enpRptC" border="0">
         <tr>
            <td class="enpRptTitle" colspan="8">
               Custom reports for '.$userRec[0]->strSafeName.'
            </td>
         </tr>
         <tr>
            <td class="enpRptLabel" style="width: 25pt;">
               ReportID
            </td>
            <td class="enpRptLabel" >
               &nbsp;
            </td>
            <td class="enpRptLabel" style="width: 120pt;">
               Name
            </td>
            <td class="enpRptLabel" style="width: 120pt;">
               Type
            </td>
            <td class="enpRptLabel" style="width: 60pt;">
               View Status
            </td>
            <td class="enpRptLabel" style="width: 60pt;">
               Display Fields
            </td>
            <td class="enpRptLabel" style="width: 160pt;">
               Notes
            </td>
            <td class="enpRptLabel" >
               Created On
            </td>
         </tr>');

   foreach ($cRpts as $report){
      $lReportID = $report->lKeyID;

      if ($report->bUserHasWriteAccess){
         $strRem = strLinkRem_CReport($lReportID, 'Remove report',  true, true);
      }else {
         $strRem = '&nbsp;';
      }
      echoT('
            <tr class="makeStripe">
               <td class="enpRpt" nowrap>'
                  .str_pad($lReportID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_CReportRec($lReportID, 'View report record', true).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .$strRem.'
               </td>
               <td class="enpRpt" >'
                  .htmlspecialchars($report->strName).'
               </td>
               <td class="enpRpt" >'
                  .$cRptTypes[$report->enumRptType]->strLabel.'
               </td>
               <td class="enpRpt" >'
                  .($report->bPrivate ? 'Private' : 'Public') .'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .$report->lNumFields.' '
                  .strLinkView_CReportFields($lReportID, 'View report fields', true).'
               </td>
               <td class="enpRpt" >'
                  .nl2br(htmlspecialchars($report->strNotes)).'
               </td>
               <td class="enpRpt" >'
                  .date($genumDateFormat, $report->dteOrigin).'
               </td>
            </tr>');
   }

   echoT('
      </table><br>');
