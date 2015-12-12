<?php
   global $genumDateFormat;

   $lETableID = $cprog->lEnrollmentTableID;
   $lATableID = $cprog->lAttendanceTableID;

   foreach ($cprog->erecs as $erec){
      $lERecID = $erec->lKeyID;

      $attributes = array('id' => 'xferA_'.$lERecID);
      $hidden = array('lERecID' => $lERecID);
      echoT(form_open('cprograms/cprog_util/moveARecs/'.$lClientID.'/'.$lCProgID, $attributes, $hidden));

      $strTableLabel = '<span style="font-size: 9pt; font-weight: normal;">'
           .strLinkView_UFMFRecordViaRecID($lETableID, $lClientID, $lERecID, 'View '.$cprog->strSafeEnrollLabel.' record', true).'&nbsp;'
           .$cprog->strSafeEnrollLabel.': '.date($genumDateFormat, $erec->dteStart).' - '
           .(is_null($erec->dteMysqlEnd) ? 'ongoing' : date($genumDateFormat, $erec->dteEnd)).'</span>';

      echoT('<br>
         <table class="enpRptC" style="width: 340pt;">
            <tr>
               <td colspan="8" class="enpRptTitle">'
                  .htmlspecialchars($cprog->strProgramName).'<br>'.$strTableLabel.'
               </td>
            </tr>
            <tr>
               <td class="enpRptLabel">
                  Attendance
               </td>
               <td class="enpRptLabel">
                  Transfer to....
               </td>
            </tr>');
      if ($erec->lNumARecs == 0){
         echoT('
            <tr>
               <td class="enpRpt" colspan="2">
                  <i>No '.$cprog->strSafeAttendLabel.' records under this '.$cprog->strSafeEnrollLabel.'</i>
               </td>
            </tr>');
      }else {
         foreach ($erec->arecs as $arec){
            $lARecID = $arec->lKeyID;
            echoT(
              '<tr>
                  <td class="enpRpt" style="text-align: center;">'
                     .strLinkView_UFMFRecordViaRecID($lATableID, $lClientID, $lARecID, 'View attendance record', true).'&nbsp;'
                     .date($genumDateFormat, $arec->dteAttendance).'
                  </td>
                  <td class="enpRpt" style="text-align: center;">'
                     .strARecXferDDL($cprog->erecs, $lERecID, $lARecID).'
                  </td>
               </tr>');
         }
      }
      echoT('
         <tr>
            <td class="enpRpt"
                style="  text-align: center; "
                colspan="2">
               <input type="submit" name="cmdSubmit" value="Transfer Attendance"
                  style=" text-align: center; "
                  onclick=" this.disabled=1; this.form.submit(); "
                  class="btn"
                  onmouseover="this.className=\'btn btnhov\'"
                  onmouseout="this.className=\'btn\'">
            </td>
         </tr>');
      
      echoT('</table><br>'.form_close());
   }

/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$cprog   <pre>');
echo(htmlspecialchars( print_r($cprog, true))); echo('</pre></font><br>');
// ------------------------------------- */

   function strARecXferDDL($erecs, $lERecID, $lARecID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strOut =
           '<select name="ddlXfer_'.$lARecID.'">
              <option value="-1">(no transfer)</option>'."\n";

      foreach ($erecs as $erec){
         $lEID = $erec->lKeyID;
            // don't transfer to source enrollment
         if ($lEID != $lERecID){
            $strOut .= '
                 <option value="'.$lEID.'">'
                    .date($genumDateFormat, $erec->dteStart).' - '
                    .(is_null($erec->dteMysqlEnd) ? 'ongoing' : date($genumDateFormat, $erec->dteEnd))
                    .'&nbsp;(eID: '.str_pad($lEID, 5, '0', STR_PAD_LEFT).')
                 </option>';
         }
      }

      $strOut .= '</select>'."\n";
      return($strOut);
   }




