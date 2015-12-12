<?php
   global $genumDateFormat;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '160pt';

   echoT(ts_util\strTSTLogOverviewBlock($clsRpt, $tst, $logRec, false, false));
   
   openBlock(($bNew ? 'New' : 'Edit').' Time Sheet Enries for '.date('l F jS, Y', $dteEntry), '');

   echoT(form_open('staff/timesheets/ts_log_edit/add_edit_ts_entry/'
                     .$lEntryID.'/'.$lTSLogID.'/'.$lUserID.'/'.$dteEntry));
                     
   echoT(form_hidden('lNumEntries', $lNumEntries));                     
   echoT(form_hidden('lEntryID',    $lEntryID));                     
   echoT(form_hidden('dteEntry',    $dteEntry));                     
   echoT(form_hidden('lTSLogID',    $lTSLogID));                     

   echoT('
      <table>
         <tr>
            <td>
               &nbsp;
            </td>
            <td class="enpRptLabel">
               Time In
            </td>
            <td class="enpRptLabel">
               Time Out
            </td>
            <td class="enpRptLabel">
               Location
            </td>
            <td class="enpRptLabel">
               Notes
            </td>
         </tr>');
   for ($idx=0; $idx<$lNumEntries; ++$idx){
//      echoT($formData->timeIn[$idx].'<br>');
      echoT('      
         <tr>
            <td >'
               .($idx+1).'
            </td>
            <td class="enpRpt">'
               .$formData->timeIn[$idx].'
            </td>
            <td class="enpRpt">'
               .$formData->timeOut[$idx].'
            </td>
            <td class="enpRpt">'
               .$formData->location[$idx].'
            </td>
            <td class="enpRpt">
               <textarea name="txtNotes'.$idx.'" rows="1" cols="30">'
                  .$formData->notes[$idx].'
               </textarea>            
            </td>
         </tr>');
   }   
   echoT('</table>');
   echoT(form_error('lNumEntries'));
   
   echoT(
             '<input type="submit" name="cmdSubmit" value="Save"
                 style="font-size: 9pt; width: 54pt;"
                 class="btn"
                    onmouseover="this.className=\'btn btnhov\'"
                    onmouseout="this.className=\'btn\'">');
   echoT(form_close('<br>'));

   
   closeBlock();
