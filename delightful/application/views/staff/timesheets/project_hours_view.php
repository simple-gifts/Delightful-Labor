<?php
   global $genumDateFormat;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '160pt';

   $attributes = array('name' => 'frmTSProjects', 'id' => 'frmAddEdit');
   echoT(form_open('staff/timesheets/ts_projects/assignHrsToProject/'.$lTSLogID, $attributes));
   
   $bSubmitted = !is_null($logRec->dteSubmitted);

   echoT(ts_util\strTSTLogOverviewBlock($clsRpt, $tst, $logRec, false, false));
   $b24Hr = $logRec->b24HrTime;
   
   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;
   $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
   $clsForm->strStyleExtraValue = 'vertical-align: middle;';
   
   openBlock('Assign Times to Projects', ''); echoT('<table class="enpView" >');
   $idx = 0;
   $clsForm->strID = 'addEditEntry';   
   foreach ($projects as $project){
      writeProjectRow($idx, $project);
      ++$idx;
   }
   echoT('</table>'); closeBlock();

   echoT($clsForm->strSubmitEntry('Save', 2, 'cmdSubmit', 'text-align: center; width: 90pt;'));
   echoT(form_close('<br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

   
   function writeProjectRow($idx, $project){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_numeric($project->lAssignedMin)){
         $strValue = strDurationViaMinutes($project->lAssignedMin);
      }else {
         $strValue = $project->lAssignedMin;
      }
   
      echoT('
         <tr>
            <td class="enpViewLabel" style="padding-top: 8px;">'
               .htmlspecialchars($project->strGroupName).':
            </td>
            <td class="enpView" style="vertical-align: top;">
               <input type="text" name="txtPMin'.$idx.'"
                    size="5"
                    maxlength="8"  
                    style="text-align: right;"   onFocus="txtPMin'.$idx.'.style.background=\'#fff\';"
                    id="addEditEntry"
                    value="'.$strValue.'">
            </td>
            <td style="vertical-align: top; padding-top: 6pt;">
               hh:mm 
            </td>
            <td style="width: 45pt; text-align: right;vertical-align: top; padding-top: 6pt;">
               Notes:
            </td>
            <td style="text-align: top;">
               <textarea name="txtNotes'.$idx.'" rows="2" cols="30">'
               .$project->notes.'</textarea>'.form_error('txtPMin'.$idx).'
            </td>
         </tr>');   
   }