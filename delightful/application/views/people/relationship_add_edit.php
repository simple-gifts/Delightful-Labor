<?php
   if (is_null($relInfo->lRelID_A2B))$relInfo->lRelID_A2B = 0;
   if (is_null($relInfo->lRelID_B2A))$relInfo->lRelID_B2A = 0;
   $attributes = array('name' => 'frmLoc', 'id' => 'frmAddEdit');
   
   echoT("\n\n"
         .form_open('people/relationships/setRelType/'.$lPeople_A_ID.'/'.$lPeople_B_ID.'/'
                   .$relInfo->lRelID_A2B.'/'.$relInfo->lRelID_B2A.'/'
                   .($bShowA ? '1' : '0').'/'
                   .($bShowB ? '1' : '0'),
                   $attributes)."\n\n");
   $bNew_A = $relInfo->lRelID_A2B <= 0;
   $bNew_B = $relInfo->lRelID_B2A <= 0;

      //---------------------
      // wrapper table
      //---------------------
   echoT(
      '<table style="width: 550pt;">
           <tr>
              <td>');

   if ($bShowA) {
      $strLabel =
            ($bNew_A ? 'Create New ' : 'Edit Existing ').'Relationship:<br>'
           .'<b>'.$strSafeName_A.'</b> <i>and</i> <b>'.$strSafeName_B.'</b>';

      showSingleRelationshipTable(
                     $strLabel,     $strSafeName_A,      $strSafeName_B,
                     'A',           $relInfo->lRelNameID_A2B, $relInfo->bSoftMoneyShare_A2B,
                     $relInfo->strNotes_A2B, ($bShowB ? '<br><br>' : ''), $strRelDDL_A, form_error('ddlRel_A'));
   }

   if ($bShowB) {
      $strLabel =
            'Reciprocal relationship:<br>'
           .'<b>'.$strSafeName_B.'</b> <i>and</i> <b>'.$strSafeName_A.'</b>'
           .($bNew_B ? ' (optional)' : '');

      showSingleRelationshipTable(
                     $strLabel,     $strSafeName_B, $strSafeName_A,
                     'B',           $relInfo->lRelNameID_B2A, $relInfo->bSoftMoneyShare_B2A,
                     $relInfo->strNotes_B2A, '', $strRelDDL_B, 
                     form_error('ddlRel_B').form_error('chkSoftCash_B'));
   }
   
   echoT(
      '<tr>
           <td align="left" colspan="5" style="text-align: center">
              <input type="submit" name="cmdAdd"
                    onclick="this.disabled=1; this.form.submit();"
                    value="Save Relationship Info"
                    class="btn"
                       onmouseover="this.className=\'btn btnhov\'"
                       onmouseout="this.className=\'btn\'"  >
           </td>
        </tr>');

   echoT('</table></form>');

function showSingleRelationshipTable(
                $strLabel,  $strName_A,    $strName_B,
                $strObjTag, $lRelNameID,   $bSoftMoney,
                $strNotes,  $strAfterText, $strRelDDL,
                $strError){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT(
    '<table class="enpRpt" width="100%">
        <tr>
            <td class="enpRptTitle" colspan="4">'
               .$strLabel.'
            </td>
        </tr>');

      //------------------------------------------
      // Relationship A -> B
      //------------------------------------------
   echoT(
       '<tr>
            <td class="enpRpt" width="30%" style="text-align: right; border-width: 1px 0px 1px 1px;">
               <b>'.$strName_A.'</b> is
            </td>

            <td class="enpRpt" style="text-align: center; border-width: 1px 0px 1px 0px;">
               <select name="ddlRel_'.$strObjTag.'">');

   echoT($strRelDDL);
   echoT(
              '</select>'.$strError.'
            </td>

            <td class="enpRpt"  width="30%" style="text-align: left; border-width:1px 1px 1px 0px;">
               to <b>'.$strName_B.'</b>
            </td>
        </tr>');

      //------------------------------------------
      // soft cash connection
      //------------------------------------------
   echoT(
       '<tr>
            <td class="enpRpt" >
               Soft Donation Connection?
            </td>

            <td class="enpRpt" colspan="2">
               <input type="checkbox" name="chkSoftCash_'.$strObjTag.'"
                   value="TRUE" '.($bSoftMoney ? 'checked' : '').'>
               (check if '.$strName_B.' receives "soft donation"<br>
               credit for '.$strName_A.'\'s donations)
            </td>
        </tr>');

      //------------------------------------------
      // relationship notes
      //------------------------------------------
   echoT(
       '<tr>
            <td class="enpRpt" >
               Relationship Notes:
            </td>

            <td class="enpRpt" colspan="2">
               <textarea name="txtNotes_'.$strObjTag.'" rows="3" cols="55">'
                  .$strNotes.'
               </textarea>
            </td>
        </tr>');

   echoT(
      "</table>$strAfterText");
}

