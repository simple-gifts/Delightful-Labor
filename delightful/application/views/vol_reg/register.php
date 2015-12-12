<?php
   global $gclsChapterVoc;
   global $glLabelWidth;

   $glLabelWidth = 130;

   htmlHeader($title, $rRec->strCSSFN, $js);

   startBody();

   $clsForm = new generic_form;
   openForm($clsForm, $strHash);
   $clsForm->strExtraFieldText = '';
   formHeading($rRec, $strErrOnForm, $info);
/*
*/
   standardFields($basicInfo, $rRec, $bCreateAcct, $formData, $clsForm, $errMessages);

   if ($lNumSkills > 0){
      jobSkills($clsForm, $skills);
   }

   if ($lNumTables > 0){
      userTables($clsForm, $lNumTables, $utables, $errMessages);
   }
   
   
   if ($rRec->bShowDisclaimer){
      showDisclaimer($clsForm, $rRec);
   }
   
   if ($rRec->bCaptchaRequired){
      showCaptcha($clsForm, $imgCaptcha);
   }
   
   buttonAndClose($clsForm);

   endBody($this);

   function openForm(&$clsForm, $strHash){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
      $clsForm->strTitleClass = 'enpViewTitle';
      $clsForm->strEntryClass = 'enpView';
      $clsForm->bValueEscapeHTML = false;

      $attributes = array('name' => 'frmVolReg', 'id' => 'frmVolReg');
      echoT(form_open('vol_reg/vol/regForm/'.$strHash, $attributes));
   }

   function formHeading(&$rRec, &$strErrOnForm, &$info){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // logo image
      if (!is_null($rRec->strLogoImageTag)){
         echoT($rRec->strLogoImageTag.'<br>');
      }

      echoT('<div class="orgName">'.$rRec->strBannerOrg.'</div>');
      echoT('<div class="pageBanner">'.$rRec->strBannerTitle.'</div><br>');

      if (isset($strErrOnForm)){
         echo('<div class="error">'.$strErrOnForm.'</div>');
      }
      if (isset($info)){
         echo('<div class="info">'.$info.'</div><br>');
      }


      echoT('<div class="intoText">'
                 .nl2br(htmlspecialchars($rRec->strIntro)).'</div><br>');
      echoT('<hr style="width: 90%;">');
   }

   function jobSkills(&$clsForm, &$skills){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glLabelWidth;
      global $gclsChapterVoc;

      openBlock(htmlspecialchars($gclsChapterVoc->vocJobSkills), '');  echoT('<table class="enpView" >');
      $clsForm->strStyleExtraLabel = 'width: '.$glLabelWidth.'pt; padding-top: 4px;';

      foreach ($skills as $skill){
         echoT($clsForm->strGenericCheckEntry(htmlspecialchars($skill->strSkill), $skill->checkFN, 'true', false, $skill->bChecked));
      }

      echoT('</table>'); closeBlock();
   }

   function htmlHeader($title, $strCSSFN, &$js){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
              "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
         <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
         <head>
           <meta http-equiv="content-type" content="text/html; charset=utf-8" />
           <title>'.$title.'</title>
         <link href="'.base_url().'css/vol_reg/'.$strCSSFN.'" rel="stylesheet" type="text/css" />
         <link type="text/css"          href="'.base_url().'css/shady/jquery-ui-1.8.2.custom.css" rel="stylesheet" />
         <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery.ui.all.css">

         <noscript>
         Javascript is not enabled! Please turn on Javascript to use this site.
         </noscript>

         <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
         <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
         <script type="text/javascript">
         //<![CDATA[
         base_url = \''.base_url().'\';
         //]]>
         </script>');
      if (isset($js)){
         echoT($js);
      }
      echoT('</head>');
   }

   function startBody(){
      global $gbDev;
/*      
      if ($gbDev){
         echo('<font color="red"><b>'.__FILE__.' '.__LINE__.': Developer mode: php errors/warnings will display.</b></font><br>'."\n");
      }
*/      
      echoT('
         <body>
         <div id="wrapper">');
   }

   function standardFields(&$basicInfo, &$rRec, $bCreateAcct, &$formData, &$clsForm, &$errMessages){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glLabelWidth;

      openBlock('Basic Info', '');  echoT('<table class="enpView" >');

      $clsForm->strStyleExtraLabel = 'width: '.$glLabelWidth.'pt; padding-top: 6px;';
      foreach ($basicInfo as $bRec){
         $clsForm->strExtraFieldText = '';
         if ($bRec->bShow){
            if ($bRec->strFormFN=='txtEmail' && $bCreateAcct){
               $clsForm->strExtraFieldText .= '<br><i>Your email address will be used as your log-in</i><br>';
            }
            $clsForm->strExtraFieldText .= form_error($bRec->strFormFN);
            echoT($clsForm->strGenericTextEntry($bRec->strLabel, $bRec->strFormFN, $bRec->bRequired,
                      $bRec->value,  $bRec->width, $bRec->maxLength));
         }
      }

         // birth date
      if ($rRec->bShowBDay){
         if (isset($errMessages['txtBDate'])){
            $clsForm->strExtraFieldText .= $errMessages['txtBDate'];
         }
         echoT($clsForm->strGenericDatePicker(
                            'Birthdate', 'txtBDate', $rRec->bBDateRequired,
                            $formData->txtBDate,    'frmVolReg', 'datepickerBDate'));
      }
      
      if ($bCreateAcct){
         echoT($clsForm->strLabelRowOneCol('&nbsp;', 1));
         $clsForm->strExtraFieldText = '&nbsp;<i>6-20 characters</i>';
         $strErr = form_error('txtPWord1');
         if ($strErr !='') $clsForm->strExtraFieldText .= '<br>'.$strErr;
         echoT($clsForm->strGenericPasswordEntry('Password',  'txtPWord1', true,
                                 $formData->txtPWord1,  20,  20,
                                 true, 'txtPWord2', $formData->txtPWord1));
         $clsForm->bAddLabelColon = false;
         echoT($clsForm->strLabelRow('', '<i>After successfully registering, you will be able to manage<br>
                                             and update your volunteer information.</i>', 1));
         $clsForm->bAddLabelColon = true;
      }
      
      echoT($clsForm->strLabelRowOneCol('<i>* Required fields</i>', 1));

      echoT('</table>'); closeBlock();
   }

   function endBody(&$cThis){
      echoT('
         <div id="footer">');
       $cThis->load->view('vol_reg/footer');
       echoT('
               </div>
            </div>

            </body>
            </html>');
   }

   function buttonAndClose(&$clsForm){
      echoT('<i>* required fields</i><br><br>');
      echoT($clsForm->strSubmitEntry('Register',
                    2, 'cmdSubmit', 'text-align: center; width: 150px;'));
      echoT(form_close('<br>'));
      echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');
   }

   function userTables(&$clsForm, $lNumTables, &$utables, &$errMessages){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glLabelWidth;

      if ($lNumTables == 0) return;

      foreach ($utables as $utable){
         openBlock(htmlspecialchars($utable->strUserTableName), '');  echoT('<table class="enpView" >');
         $clsForm->strStyleExtraLabel = 'width: '.$glLabelWidth.'pt; padding-top: 6px;';

         foreach ($utable->ufields as $ufield){
            showUserField($clsForm, $ufield, $errMessages);
         }
         echoT($clsForm->strLabelRowOneCol('<i>* Required fields</i>', 1));
         echoT('</table>'); closeBlock();
      }
   }

   function showUserField(&$clsForm, &$ufield, &$errMessages){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $enumType = $ufield->enumFieldType;
      $strFN    = $ufield->strFieldNameInternal;
      $clsForm->strExtraFieldText = '';
      $strFieldLabel = htmlspecialchars($ufield->strFieldNameUser);
      $bRequired     = $ufield->bRequired;
      $strValue      = @$ufield->txtValue;

      if ($ufield->strFieldNotes != ''){
         $clsForm->strExtraFieldText .= '<br><i>'.nl2br(htmlspecialchars($ufield->strFieldNotes)).'</i>';
      }
      
      $bText = ($enumType==CS_FT_TEXT255) || ($enumType==CS_FT_TEXT80)
            || ($enumType==CS_FT_TEXT20)  || ($enumType==CS_FT_TEXTLONG);
      if ($bText){
         $clsForm->strExtraFieldText .= form_error($strFN);
      }

      switch ($enumType){
         case CS_FT_CHECKBOX:
            echoT($clsForm->strGenericCheckEntry($strFieldLabel,
                            $strFN, 'true', false, $ufield->bChecked));
            break;
         case CS_FT_DATE:
            if (isset($errMessages[$strFN])){
               $clsForm->strExtraFieldText .= $errMessages[$strFN];
            }
            echoT($clsForm->strGenericDatePicker(
                               $strFieldLabel, $strFN, $bRequired,
                               $strValue,    'frmVolReg', $strFN));
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
            echoT($clsForm->strLabelRowOneCol('<b><u>'.$strFieldLabel.'</u></b>', 1));
            break;
            
         case CS_FT_TEXTLONG:
            echoT($clsForm->strNotesEntry($strFieldLabel, $strFN, $bRequired, $strValue, 3, 56));
            break;

         case CS_FT_INTEGER:
            $clsForm->strExtraFieldText .= form_error($strFN);
            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  3, 10));
            break;

         case CS_FT_CURRENCY:
            $clsForm->strExtraFieldText .= '';
            $clsForm->strTextBoxPrefix  = $ufield->strCurrencySymbol;
            $clsForm->strExtraFieldText .= $ufield->strFlagImg;
            if (isset($errMessages[$strFN])){
               $clsForm->strExtraFieldText .= $errMessages[$strFN];
            }
            echoT($clsForm->strGenericTextEntry(
                      $strFieldLabel, $strFN, $bRequired, $strValue,  8, 14));
            $clsForm->strTextBoxPrefix  =  $clsForm->strExtraFieldText = '';
            break;

         case CS_FT_DDL:
            if (isset($errMessages[$strFN])){
               $clsForm->strExtraFieldText .= $errMessages[$strFN];
            }
            echoT($clsForm->strLabelRow($strFieldLabel, $ufield->strDDL, 1));
            break;
            
         case CS_FT_DDLMULTI:
            $clsForm->strExtraFieldText .= '<br><font style="font-size: 7pt;"><i>CTRL-Click to select more than one entry</i></font>';
            if (isset($errMessages[$strFN])){
               $clsForm->strExtraFieldText .= $errMessages[$strFN];
            }
            echoT($clsForm->strLabelRow($strFieldLabel.($bRequired ? '*' : ''), $ufield->strDDL, 1));
            break;
            

         default:
            screamForHelp($enumType.': unexpected field type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function showDisclaimer(&$clsForm, &$rRec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      global $glLabelWidth;

      openBlock('Disclaimer', '');  echoT('<table class="enpView" >');
      $clsForm->strStyleExtraLabel = 'width: '.$glLabelWidth.'pt; padding-top: 4px;';
      
      $clsForm->bAddLabelColon = false;
      $strAckCheck =
          "\n"
         .'<input type="checkbox" name="chkDisclaimer" value="true" '.($rRec->bDisclaimerChecked ? 'checked' : '').'>&nbsp;'."\n"
         .htmlspecialchars($rRec->strDisclaimerAck).'<br>'."\n"
         .form_error('chkDisclaimer')."\n"
         .'<div id="scrollCB" style="height:170px;   width:500px; overflow:auto;  border: 1px solid black;">'."\n"
         .nl2br(htmlspecialchars($rRec->strDisclaimer))."\n"
         .'</div>'."\n";

      echoT($clsForm->strLabelRowOneCol($strAckCheck, 1));

      $clsForm->bAddLabelColon = true;
      echoT('</table>'); closeBlock();   
   }
   
   function showCaptcha($clsForm, $strImgCaptcha){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------  
      global $glLabelWidth;
      
      openBlock('Verification', '');  echoT('<table class="enpView" >');
      
      $clsForm->strStyleExtraLabel = 'width: '.$glLabelWidth.'pt; padding-top: 4px;';      
      echoT($clsForm->strLabelRowOneCol($strImgCaptcha, 1));
      
      $strError = form_error('txtCaptcha');
      $clsForm->strExtraFieldText = '<br><i>Enter the word and numbers you see above (not case sensitive).</i>'
                         .$strError;
      if ($strError != '') $strError = '<br>'.$strError;
      echoT($clsForm->strGenericTextEntry('Verification', 'txtCaptcha', true,
                '',  20, 40));
      echoT('</table>'); closeBlock();      
   }

