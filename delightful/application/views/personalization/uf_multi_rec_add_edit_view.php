<?php
   global $glLabelWidth, $gbInDiv, $gdivAtt;

      // setup for collapsable headers
   $gbInDiv = false;
   $gdivAtt = new stdClass;
   $gdivAtt->lTableWidth      = 900;
   $gdivAtt->lUnderscoreWidth = 300;
   $gdivAtt->divID            = 'groupDiv';
   $gdivAtt->divImageID       = 'groupDivImg';
   $gdivAtt->bStartOpen       = true;
   $gdivAtt->bAddTopBreak     = true;
   $gdivAtt->bCloseDiv        = false;
   
   echoT($strHTMLSummary);

   if ($utable->lNumEditableFields == 0 && !$bCProg){
      echoT('<br><i>There are no editable fields in table <b>'.htmlspecialchars($utable->strUserTableName).'.</b></i><br>');
      return;
   }
   
   $glLabelWidth = 150;

   $clsForm = new generic_form;
   openForm($clsForm, $lTableID, $lFID, $lRecID, $lEnrollRecID, $bUseReturnPath, $bCusVerification);
   
   userTable($clsForm, $utable, $errMessages, $strSafeAttendLabel, $strSafeEnrollLabel);
   
   buttonAndClose($clsForm);
   
   
   
   function openForm(&$clsForm, $lTableID, $lFID, $lRecID, $lEnrollRecID, $bUseReturnPath, $bCusVerification){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbShowHiddenVerifyError;
      
      $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
      $clsForm->strTitleClass = 'enpViewTitle';
      $clsForm->strEntryClass = 'enpView';
      $clsForm->bValueEscapeHTML = false;

      $attributes = array('name' => 'frmPTable', 'id' => 'frmPTable');
      echoT(form_open('admin/uf_multirecord/addEditMultiRecord/'
               .$lTableID.'/'.$lFID.'/'.$lRecID.'/'.$lEnrollRecID
               .'/'.($bUseReturnPath ? 'true' : 'false'), $attributes));
      if ($bCusVerification){
         echoT(form_hidden('hVerify', 'true')."\n");
         if ($gbShowHiddenVerifyError) echoT(form_error('hVerify'));
      }
               
      echoT(form_hidden('hidForceSubmit', 'force'));
   }
   
   function buttonAndClose(&$clsForm){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      echoT($clsForm->strSubmitEntry('Save',
                    2, 'cmdSubmit', 'text-align: center; width: 150px;'));
      echoT(form_close('<br>'));
      echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
   }
   
   function userTable(&$clsForm, &$utable, &$errMessages, $strSafeAttendLabel, $strSafeEnrollLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glLabelWidth, $gbInDiv, $gdivAtt;

      openBlock($utable->strTableLabel, '');  echoT('<table class="enpView" >');
      $clsForm->strStyleExtraLabel = 'width: '.$glLabelWidth.'pt; padding-top: 6px;';
      
      if ($utable->bCProg){
         if ($utable->bEnrollment){
            cProgramEDefaultFields($clsForm, $utable, $errMessages, $strSafeEnrollLabel);
         }else {
            cProgramADefaultFields($clsForm, $utable, $errMessages, $strSafeAttendLabel);
         }
      }

      if (isset($utable->ufields)){
         foreach ($utable->ufields as $ufield){
            showUserField($clsForm, $ufield, $errMessages);
         }
      }
      echoT($clsForm->strLabelRowOneCol('<i>* Required fields</i>', 1));
      
      if ($gbInDiv){      
         $gdivAtt->bCloseDiv = true;
         echoT('</table>'."\n".strCloseBlock($gdivAtt));
         closeBlock();
      }else {
         echoT('</table>'); closeBlock();
      }     
   }
   
   function cProgramADefaultFields(&$clsForm, &$utable, &$errMessages, $strSafeAttendLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         //------------------------
         // Attendance Date
         //------------------------
      echoT(strDatePicker('datepickerE1', true));
      $clsForm->strExtraFieldText = form_error('txtADate');
      if (isset($errMessages['txtADate'])) $clsForm->strExtraFieldText .= $errMessages['txtADate'];
      echoT($clsForm->strGenericDatePicker(
                         $strSafeAttendLabel.' Date', 'txtADate',      true,
                         $utable->cProgA->txtADate,    'frmPTable', 'datepickerE1'));
                         
         //------------------------
         // Duration DDL
         //------------------------
      if (!$utable->bHideDuration){
         $clsForm->strExtraFieldText = form_error('ddlDuration');            
         if (isset($errMessages['ddlDuration'])) $clsForm->strExtraFieldText .= $errMessages['ddlDuration'];
         echoT($clsForm->strLabelRow('Duration', $utable->cProgA->strDDLDuration, 1));
      }
      
         //------------------------
         // Case Notes
         //------------------------
      echoT($clsForm->strNotesEntry('Case Notes', 'txtCaseNotes', false, $utable->cProgA->strCaseNotes, 3, 50));                         
   }   
      
   function cProgramEDefaultFields(&$clsForm, &$utable, &$errMessages, $strSafeEnrollLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         //------------------------
         // Enrollment Start Date
         //------------------------
         
      echoT(strDatePicker('datepickerE1', true));
      $clsForm->strExtraFieldText = form_error('txtEStart');
      if (isset($errMessages['txtEStart'])) $clsForm->strExtraFieldText .= $errMessages['txtEStart'];
      echoT($clsForm->strGenericDatePicker(
                         $strSafeEnrollLabel.' Start Date', 'txtEStart',      true,
                         $utable->cProgE->txtEStart,    'frmPTable', 'datepickerE1'));
                         
         //------------------------
         // Enrollment End Date
         //------------------------
      echoT(strDatePicker('datepickerE2', true));
      $clsForm->strExtraFieldText = '<br><i>Leave blank for on-going enrollment</i>'.form_error('txtEEnd');
      if (isset($errMessages['txtEEnd'])) $clsForm->strExtraFieldText .= $errMessages['txtEEnd'];
      echoT($clsForm->strGenericDatePicker(
                         $strSafeEnrollLabel.' End Date', 'txtEEnd',      false,
                         $utable->cProgE->txtEEnd,    'frmPTable', 'datepickerE2'));
                         
         //------------------------
         // Currently active?
         //------------------------
      echoT($clsForm->strGenericCheckEntry('Active in Client Program?', 
                               'chkEnrolled', 'true', false, $utable->cProgE->bEnrolled));
   
   }

   function showUserField(&$clsForm, &$ufield, &$errMessages){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbInDiv, $gdivAtt;
   
      $enumType = $ufield->enumFieldType;
      $strFN    = $ufield->strFieldNameInternal;
      $clsForm->strExtraFieldText = '';
      $strFieldLabel = htmlspecialchars($ufield->pff_strFieldNameUser);
      $bRequired     = $ufield->pff_bRequired;
      $strValue      = @$ufield->txtValue;

      $bText = ($enumType==CS_FT_TEXT255) || ($enumType==CS_FT_TEXT80)
            || ($enumType==CS_FT_TEXT20)  || ($enumType==CS_FT_TEXTLONG);
            
//      $clsForm->strExtraFieldText = '';
      if ($ufield->strFieldNotes != ''){
         $clsForm->strExtraFieldText .= '<br><i>'.nl2br(htmlspecialchars($ufield->strFieldNotes)).'</i>';
      }
      if ($bText){
         $clsForm->strExtraFieldText .= form_error($strFN);
//         if (isset($errMessages[$strFN])) $clsForm->strExtraFieldText .= $errMessages[$strFN];
      }
      if (isset($errMessages[$strFN])) $clsForm->strExtraFieldText .= $errMessages[$strFN];

      switch ($enumType){
         case CS_FT_CHECKBOX:
//            if (isset($errMessages[$strFN])) $clsForm->strExtraFieldText .= $errMessages[$strFN];
            echoT($clsForm->strGenericCheckEntry($strFieldLabel,
                            $strFN, 'true', false, $ufield->bChecked));
            break;
         case CS_FT_DATE:
//            if (isset($errMessages[$strFN])) $clsForm->strExtraFieldText .= $errMessages[$strFN];
            
            echoT($clsForm->strGenericDatePicker(
                               $strFieldLabel, $strFN, $bRequired,
                               $strValue,    'frmPTable', $strFN));
            break;

         case CS_FT_TEXT255:
            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  60, 255));
            break;

         case CS_FT_TEXT80:
            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  60, 80));
            break;

         case CS_FT_TEXT20:
            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  20, 20));
            break;

         case CS_FT_HEADING:
            $strBlock = '</table>';
            if ($gbInDiv){
               $gdivAtt->bCloseDiv = true;
               $strBlock .= "\n".strCloseBlock($gdivAtt);
            }
            $gdivAtt->bCloseDiv = false;
               //</div><!-- hello, world! --><table class="enpView" >'."\n"); 
            $gbInDiv = true;
            $gdivAtt->divID            = $strFN.'Div';
            $gdivAtt->divImageID       = $strFN.'DivImg';
            $strBlock .= strOpenBlock($strFieldLabel, '', $gdivAtt)."\n".'<table class="enpView">';
            echoT($strBlock);
            break;
            
         case CS_FT_TEXTLONG:
            echoT($clsForm->strNotesEntry($strFieldLabel, $strFN, $bRequired, $strValue, 3, 56));
            break;
            
         case CS_FT_LOG:
            break;

         case CS_FT_CLIENTID:
//            if (isset($errMessages[$strFN])) $clsForm->strExtraFieldText .= $errMessages[$strFN];

            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  3, 10));
            break;
         case CS_FT_INTEGER:
            $clsForm->strExtraFieldText .= form_error($strFN);
//            if (isset($errMessages[$strFN])) $clsForm->strExtraFieldText .= $errMessages[$strFN];
            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  3, 10));
            break;

         case CS_FT_CURRENCY:
            $clsForm->strExtraFieldText .= '';
            $clsForm->strTextBoxPrefix  = $ufield->pff_strCurrencySymbol;
            $clsForm->strExtraFieldText .= $ufield->pff_strFlagImg;
//            if (isset($errMessages[$strFN])) $clsForm->strExtraFieldText .= $errMessages[$strFN];
            
            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  8, 14));
            $clsForm->strTextBoxPrefix  =  $clsForm->strExtraFieldText = '';
            break;

         case CS_FT_DDL:
//            if (isset($errMessages[$strFN])) $clsForm->strExtraFieldText .= $errMessages[$strFN];
            
            echoT($clsForm->strLabelRow($strFieldLabel.($bRequired ? '*' : ''), $ufield->strDDL, 1));
            break;

         case CS_FT_DDLMULTI:
            $clsForm->strExtraFieldText .= '<br><font style="font-size: 7pt;"><i>CTRL-Click to select more than one entry</i></font>';
//            if (isset($errMessages[$strFN]))  $clsForm->strExtraFieldText .= $errMessages[$strFN];
            
            echoT($clsForm->strLabelRow($strFieldLabel.($bRequired ? '*' : ''), $ufield->strDDLMulti, 1));
            break;

         default:
            screamForHelp($enumType.': unexpected field type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

   }



