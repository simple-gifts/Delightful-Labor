<?php
   global $gbDateFormatUS, $gstrFormatDatePicker;

   if ($lNumQuests == 0){
      echoT('<br><i>There are no questions in this test!</i><br><br>');
      return;
   }
   
   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;


   $attributes = array('name' => 'frmEditPPTest', 'id' => 'frmAddEdit');
   echoT(form_open('cpre_post_tests/client_test_results/addEditTestResults/'
                          .$lTestLogID.'/'.$lClientID.'/'.$lPPTestID, $attributes));

   openBlock('Pre/Post Test Results: '.htmlspecialchars($pptest->strTestName), '');
   echoT('<table class="enpView">');
   
      //--------------------------
      // Test name
      //--------------------------
   echoT($clsForm->strLabelRow('Test Name', htmlspecialchars($pptest->strTestName), 1));

      //--------------------------
      // Pre/Post-test score
      //--------------------------
   $lNumCorrectPre = $lNumCorrectPost = 0;
   foreach ($QandA as $QA){
      if ($QA->bPreTestRight) ++$lNumCorrectPre;
      if ($QA->bPostTestRight) ++$lNumCorrectPost;
   }
   $strPreTestScore  = number_format(($lNumCorrectPre /$lNumQuests)*100, 1).' %';
   $strPostTestScore = number_format(($lNumCorrectPost/$lNumQuests)*100, 1).' %';
   
   echoT($clsForm->strLabelRow('Pre-test score', $strPreTestScore, 1));
   echoT($clsForm->strLabelRow('Post-test score', $strPostTestScore, 1));
  
   showTest($clsForm, 'datepicker1', 'txtPretestDate',  'Pre-test Date',  $txtPretestDate,  'frmEditPPTest', $QandA, true);
   showTest($clsForm, 'datepicker2', 'txtPosttestDate', 'Post-test Date', $txtPosttestDate, 'frmEditPPTest', $QandA, false);
   
   echoT($clsForm->strSubmitEntry('Save Test Results', 1, 'cmdSubmit', 'text-align: center;'));
   echoT('</table>'.form_close('<br>'));
   closeBlock();
   

   function showTest(&$clsForm, $strDatePickerName, $strDateFN, 
                     $strDateLabel, $strDateVal, $strFormName,
                     &$QandA, $bPretest){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strPrePostLabel = ($bPretest ? 'Pre' : 'Post');   
      $strDivName = 'divChk'.$strPrePostLabel;
      $strOut = '';
      
         //--------------------------
         // Pre/post-test date
         //--------------------------
      $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
      echoT(strDatePicker($strDatePickerName, true));
      $clsForm->strExtraFieldText = form_error($strDateFN);
      echoT($clsForm->strGenericDatePicker(
                         $strDateLabel, $strDateFN,      true,
                         $strDateVal, $strFormName, $strDatePickerName));
                         
      $strOut .= '<div id="'.$strDivName.'">';
      
      $strOut .= '<table width="500">
          <tr>
             <td style="text-align: center; width: 40pt;">
                Check if<br>
                Correct
             </td>
             <td style="width: 20pt;">&nbsp;</td>
             <td>
               <input type="button" value="Check All" onclick="checkByParent(\''.$strDivName.'\', true);">
               <input type="button" value="Uncheck All" onclick="checkByParent(\''.$strDivName.'\', false);">
          </tr>';
          
      $idx = 1;
      foreach ($QandA as $QA){
         $lQuestID = $QA->lKeyID;
         if ($bPretest){
            $bCorrect = $QA->bPreTestRight;
         }else {
            $bCorrect = $QA->bPostTestRight;
         }
         $strOut .=
            '<tr class="makeStripe">
               <td style="text-align: center; vertical-align: top;">
                  <input type="checkbox" name="chk'.$strPrePostLabel.$lQuestID.'"
                         value="true" '.($bCorrect ? 'checked' : '').'>
               </td>
               <td style="text-align: center; vertical-align: top;">'
                  .$idx.'
               </td>
               <td style="text-align: left; vertical-align: top;"><b>Q: </b>'
                  .nl2br(htmlspecialchars($QA->strQuestion)).'<br><br><b>A: </b><i>'
                  .nl2br(htmlspecialchars($QA->strAnswer)).'</i><br><br>
               </td>
             </tr>';
          ++$idx;
      }                      
   
      $strOut .= '</table></div>';
      echoT($clsForm->strLabelRow($strPrePostLabel.'-test', $strOut, 1));      
   }

