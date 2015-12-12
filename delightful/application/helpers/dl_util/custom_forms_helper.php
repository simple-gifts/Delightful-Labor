<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2013 by Database Austin
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('dl_util/custom_forms');
//---------------------------------------------------------------------*/

   function addNewCustomForm($enumContext, &$dataRec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $CI =& get_instance();

      $fContext = formViaContext($enumContext);

      $sqlStr =
           "INSERT INTO $fContext->strTable
            SET ".sqlCommonCustomFormAddEdit($fContext, $dataRec, $enumContext).",
              $fContext->strFNPrefix"."bRetired = 0,
              $fContext->strFNPrefix"."dteOrigin = NOW(),
              $fContext->strFNPrefix"."lOriginID = $glUserID;";

      $query  = $CI->db->query($sqlStr);
      $lKeyID = $CI->db->insert_id();

         // create the hash key for the url
      if ($fContext->bVolReg){
         $strHash = do_hash($lKeyID.'V');
         $sqlStr =
            'UPDATE vol_reg
             SET vreg_strURLHash='.strPrepStr($strHash)."
             WHERE vreg_lKeyID=$lKeyID;";
         $query = $CI->db->query($sqlStr);
      }
      return($lKeyID);
   }

   function updateCustomForm($enumContext, $lFormID, &$dataRec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $CI =& get_instance();

      $fContext = formViaContext($enumContext);
      $sqlStr =
           "UPDATE $fContext->strTable
            SET ".sqlCommonCustomFormAddEdit($fContext, $dataRec, $enumContext)."
            WHERE $fContext->strFNPrefix"."lKeyID=$lFormID;";

      $query = $CI->db->query($sqlStr);
   }

   function sqlCommonCustomFormAddEdit(&$fContext, &$rRec, $enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

            // internal fields
      $sqlStr =
          $fContext->strFNPrefix.'strFormName      = '.strPrepStr($rRec->strFormName).', '."\n"
         .$fContext->strFNPrefix.'strDescription   = '.strPrepStr($rRec->strDescription).', '."\n";

      if ($fContext->bVolReg){
         $sqlStr .=
             $fContext->strFNPrefix.'strContactEmail  = '.strPrepStr($rRec->strContactEmail).', '."\n";
         $sqlStr .= '
            vreg_strCSSFN         = '.strPrepStr($rRec->strCSSFN).',
            vreg_lLogoImageID     = '.(is_null($rRec->lLogoImageID) ? 'null' : $rRec->lLogoImageID).',
            vreg_lVolGroupID      = '.(is_null($rRec->lVolGroupID)  ? 'null' : $rRec->lVolGroupID).',
            vreg_bCaptchaRequired = '.($rRec->bCaptchaRequired      ? '1' : '0').', '."\n";

               // form registration permissions
         $sqlStr .= '
            vreg_bPermEditContact     = '.($rRec->bPermEditContact     ? '1' : '0').',
            vreg_bPermPassReset       = '.($rRec->bPermPassReset       ? '1' : '0').',
            vreg_bPermViewGiftHistory = '.($rRec->bPermViewGiftHistory ? '1' : '0').',
            vreg_bPermEditJobSkills   = '.($rRec->bPermEditJobSkills   ? '1' : '0').',
            vreg_bPermViewHrsHistory  = '.($rRec->bPermViewHrsHistory  ? '1' : '0').',
            vreg_bPermAddVolHours     = '.($rRec->bPermAddVolHours     ? '1' : '0').',
            vreg_bVolShiftSignup      = '.($rRec->bVolShiftSignup      ? '1' : '0').',
            vreg_strBannerOrg         = '.strPrepStr($rRec->strBannerOrg).",\n";

               // Standard Fields - show
         $sqlStr .=
             $fContext->strFNPrefix.'bShowFName = '.($rRec->bShowFName   ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'bShowLName = '.($rRec->bShowLName   ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'bShowAddr  = '.($rRec->bShowAddr    ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'bShowEmail = '.($rRec->bShowEmail   ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'bShowPhone = '.($rRec->bShowPhone   ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'bShowCell  = '.($rRec->bShowCell    ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'bShowBDay  = '.($rRec->bShowBDay    ? '1' : '0').",\n";

               // Standard Fields - required
         $sqlStr .=
             $fContext->strFNPrefix.'bFNameRequired = '.($rRec->bFNameRequired  ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'bLNameRequired = '.($rRec->bLNameRequired  ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'bAddrRequired  = '.($rRec->bAddrRequired   ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'bEmailRequired = '.($rRec->bEmailRequired  ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'bPhoneRequired = '.($rRec->bPhoneRequired  ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'bCellRequired  = '.($rRec->bCellRequired   ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'bBDateRequired = '.($rRec->bBDateRequired  ? '1' : '0').",\n";

               // Disclaimer
         $sqlStr .=
             $fContext->strFNPrefix.'bShowDisclaimer    = '.($rRec->bShowDisclaimer  ? '1' : '0')   .",\n"
            .$fContext->strFNPrefix.'bDisclaimerAckRqrd = '.($rRec->bDisclaimerAckRqrd  ? '1' : '0').",\n"
            .$fContext->strFNPrefix.'strDisclaimerAck   = '.strPrepStr($rRec->strDisclaimerAck)     .",\n"
            .$fContext->strFNPrefix.'strDisclaimer      = '.strPrepStr($rRec->strDisclaimer)        .",\n";
      }else {
         $sqlStr .=
             $fContext->strFNPrefix.'enumContextType       = '.strPrepStr($enumContext)                .",\n"
            .$fContext->strFNPrefix.'strVerificationModule = '.strPrepStr($rRec->strVerificationModule).",\n"
            .$fContext->strFNPrefix.'strVModEntryPoint     = '.strPrepStr($rRec->strVModEntryPoint)    .",\n";
      }

            // top banner
      $sqlStr .=
          $fContext->strFNPrefix.'strBannerTitle    = '.strPrepStr($rRec->strBannerTitle)   .",\n"
         .$fContext->strFNPrefix.'strSubmissionText = '.strPrepStr($rRec->strSubmissionText).",\n"
         .$fContext->strFNPrefix.'strIntro          = '.strPrepStr($rRec->strIntro)         .",\n";

            // timestamp
      $sqlStr .=
         $fContext->strFNPrefix.'lLastUpdateID = '.$glUserID.",\n"
        .$fContext->strFNPrefix."dteLastUpdate=NOW() \n";

      return($sqlStr);
   }

   function formViaContext($enumFormContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $formContext = new stdClass;

      $formContext->bVolReg = $formContext->bCustomForm = false;
      switch ($enumFormContext){
         case CENUM_CONTEXT_VOLUNTEER:
            $formContext->bVolReg = true;
            $formContext->strTable = 'vol_reg';
            $formContext->strFNPrefix = 'vreg_';
            break;
         case CENUM_CONTEXT_CLIENT:
            $formContext->bCustomForm = true;
            $formContext->strTable = 'custom_forms';
            $formContext->strFNPrefix = 'cf_';
            break;
         default:
            screamForHelp($enumFormContext.': Invalid context<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($formContext);
   }

   function updateFormPTableFields($enumContext, $lFormID, $lNumTables, &$userTables){
   //---------------------------------------------------------------------
   // table vol_reg_uf is used for all custom forms
   //---------------------------------------------------------------------
      $fContext = formViaContext($enumContext);

         // out with the old
      deleteCustomFormPTableFields($enumContext, $lFormID);

         // in with the new
      if ($lNumTables > 0){
         foreach ($userTables as $utable){
            if ($utable->lNumFields > 0){
               foreach ($utable->fields as $field){
                  if ($field->enumFieldType!=CS_FT_LOG){
                     if ($field->bShow){
                        insertFormPTableField($enumContext, $lFormID, $utable->lKeyID, $field->pff_lKeyID, $field->bRequired);
                     }
                  }
               }
            }
         }
      }
   }

   function insertFormPTableField($enumContext, $lFormID, $lTableID, $lFieldID, $bRequired){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $CI =& get_instance();
      if ($enumContext==CENUM_CONTEXT_VOLUNTEER){
         $sqlStr =
           "INSERT INTO vol_reg_uf
            SET
               vruf_lRegFormID=$lFormID,
               vruf_lTableID=$lTableID,
               vruf_lFieldID=$lFieldID,
               vruf_bRequired=".($bRequired ? '1' : '0').',
               vruf_strLabel=\'\';';
      }else {
         $sqlStr =
           "INSERT INTO custom_form_uf
            SET
               cfuf_lCFormID=$lFormID,
               cfuf_lTableID=$lTableID,
               cfuf_lFieldID=$lFieldID,
               cfuf_bRequired=".($bRequired ? '1' : '0').',
               cfuf_strLabel=\'\';';
      }
      $query = $CI->db->query($sqlStr);
   }

   function deleteCustomFormPTableFields($enumContext, $lFormID){
      $CI =& get_instance();
      if ($enumContext==CENUM_CONTEXT_VOLUNTEER){
         $sqlStr =
             "DELETE FROM vol_reg_uf
              WHERE vruf_lRegFormID=$lFormID;";
      }else {
         $sqlStr =
             "DELETE FROM custom_form_uf
              WHERE cfuf_lCFormID=$lFormID;";
      }
      $query = $CI->db->query($sqlStr);
   }

   function removeCustomForm($enumContext, $lFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $CI =& get_instance();

      $fContext = formViaContext($enumContext);

      $sqlStr =
        "UPDATE $fContext->strTable
         SET
            $fContext->strFNPrefix"."bRetired = 1,
            $fContext->strFNPrefix"."lLastUpdateID = $glUserID,
            $fContext->strFNPrefix"."dteLastUpdate = NOW()
         WHERE $fContext->strFNPrefix"."lKeyID=$lFormID;";
      $query = $CI->db->query($sqlStr);

      deleteCustomFormPTableFields($enumContext, $lFormID);
   }

   function setValidationUTables(&$js, $lNumTables, &$utables){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $CI =& get_instance();
      if ($lNumTables == 0) return;
      foreach ($utables as $utable){
         foreach ($utable->ufields as $ufield){
            $enumType   = $ufield->enumFieldType;
            $strFN      = $ufield->strFieldNameInternal;
            $strFNLabel = htmlspecialchars($ufield->strFieldNameUser);
            $bRequired  = $ufield->bRequired;

            switch ($enumType){
               case CS_FT_CHECKBOX:
                  $CI->form_validation->set_rules($strFN, $strFNLabel, 'trim');
                  $ufield->bChecked = false;
                  break;

               case CS_FT_DATE:
                  cusFormSetValDate(true,  $bRequired, $strFN,  $strFNLabel, true);
                  break;

               case CS_FT_TEXT255:
               case CS_FT_TEXT80:
               case CS_FT_TEXT20:
               case CS_FT_TEXTLONG:
                  $CI->form_validation->set_rules($strFN,
                               $strFNLabel, 'trim'.($bRequired ? '|required' : ''));
                  $ufield->txtValue = '';
                  break;

               case CS_FT_INTEGER:
                  $CI->form_validation->set_rules($strFN, $strFNLabel, 'trim|integer'.($bRequired ? '|required' : ''));
                  $ufield->txtValue = '';
                  break;

               case CS_FT_CURRENCY:
                  $CI->clsACO->loadCountries(true, true, true, $ufield->lCurrencyACO);
                  $country = $CI->clsACO->countries[0];
                  $ufield->strCurrencySymbol = $country->strCurrencySymbol;
                  $ufield->strFlagImg        = $country->strFlagImg;
                  $CI->form_validation->set_rules($strFN, $strFNLabel,
                         'callback_verifyCurrency['.($bRequired ? 'true' : 'false')
                                    .', '.$strFN.', '.$strFNLabel.']');
                  break;

               case CS_FT_DDL:
                  $CI->form_validation->set_rules($strFN, $strFNLabel,
                         'callback_verifyDDLSelect['.($bRequired ? 'true' : 'false')
                                    .', '.$strFN.', '.$strFNLabel.']');
                  break;

               case CS_FT_DDLMULTI:
//echo(__FILE__.' '.__LINE__.'<br>'."\n");               
                  $CI->form_validation->set_rules($strFN, $strFNLabel,
                         'callback_verifyDDLMultiSelect['.($bRequired ? 'true' : 'false')
                                    .', '.$strFN.', '.$strFNLabel.']');
                  break;
            }
         }
      }
   }

   function initUTableDates(&$js, $lNumTables, &$utables){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumTables == 0) return;
      foreach ($utables as $utable){
         foreach ($utable->ufields as $ufield){
            $enumType = $ufield->enumFieldType;
            if ($enumType==CS_FT_DATE){
               $strFN = $ufield->strFieldNameInternal;
               $js .= strDatePicker($strFN, true);
               $ufield->txtValue = '';
            }
         }
      }
   }

   function cusFormSetValDate($bShow,  $bRequired,   $strDateFN,  $strLabel, $bAllowFuture){
      if ($bShow){
         $CI =& get_instance();
         $CI->form_validation->set_rules($strDateFN, $strLabel,
                     'callback_verifyCustomDate['.$strDateFN.','.$strLabel
                              .','.($bAllowFuture ? 'true' : 'false').','.($bRequired ? 'true' : 'false').']');
      }
   }

   function initUTableDDLs($lNumTables, &$utables){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumTables == 0) return;
      foreach ($utables as $utable){
         foreach ($utable->ufields as $ufield){
            $enumType = $ufield->enumFieldType;
            if ($enumType==CS_FT_DDL){
               $strFN = $ufield->strFieldNameInternal;
               $ufield->lMatch = -1;
               $ufield->strDDL = '';
            }elseif ($enumType==CS_FT_DDLMULTI){
               $strFN = $ufield->strFieldNameInternal;
               $ufield->lMatch = array();
               $ufield->strDDL = '';
            }
         }
      }
   }

   function verifyCustomFormDate($strFieldValue, $strOpts){
   //---------------------------------------------------------------------
   //       $this->load->helper('dl_util/time_date');
   //---------------------------------------------------------------------
      $strFieldValue = trim($strFieldValue);
      $opts = explode(',', $strOpts);
      $strFN        = trim($opts[0]);
      $strLabel     = trim($opts[1]);
      $bAllowFuture = $opts[2]=='true';
      $bRequired    = $opts[3]=='true';

      if ($bRequired){
         if ($strFieldValue==''){
//            $ErrMessages[$strFN] = '<div class="formError">'.'The '.$strLabel.' field is required.</div>';
            setCFormErrorMessage($strFN, 'The '.$strLabel.' field is required.');
            return(false);
         }
      }elseif ($strFieldValue=='') {
         return(true);
      }

      if (!bValidVerifyDate($strFieldValue)){
//         $ErrMessages[$strFN] = '<div class="formError">'.'The '.$strLabel.' field is not valid.</div>';
            setCFormErrorMessage($strFN, 'The '.$strLabel.' field is not valid.');
         return(false);
      }

      if (!$bAllowFuture){
         if (!bValidVerifyNotFuture($strFieldValue)){
//            $ErrMessages[$strFN] = '<div class="formError">'.'The '.$strLabel.' is in the future.</div>';
            setCFormErrorMessage($strFN, 'The '.$strLabel.' field is in the future.');
            return(false);
         }
      }
      return(true);
   }

   function verifyCustomFormDDL($strVal, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lDDLVal = (int)trim($strVal);
      $opts = explode(',', $strOpts);
      $bRequired    = $opts[0]=='true';
      $strFN        = trim($opts[1]);
      $strLabel     = trim($opts[2]);

      if (!$bRequired) return(true);
      if ($lDDLVal <= 0){
         setCFormErrorMessage($strFN, 'Please make a selection for '.$strLabel.'.');
         return(false);
      }else {
         return(true);
      }
   }

   function verifyCustomFormMultiDDL($strVal, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $opts = explode(',', $strOpts);
      $bRequired    = $opts[0]=='true';
      $strFN        = trim($opts[1]);
      $strLabel     = trim($opts[2]);

      if (!$bRequired) return(true);
      if (!is_array($strVal)){
         setCFormErrorMessage($strFN, 'Please select one or more entries for '.$strLabel.'.');
         return(false);
      }
      if (count($strVal)==1 && ((int)$strVal[0])<=0){
         setCFormErrorMessage($strFN, 'Please select one or more entries for '.$strLabel.'.');
         return(false);
      }else {
         return(true);
      }
   }

   function verifyCustomFormCurrency($strVal, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strCurrency = trim($strVal);
      $strCurrency = stripCustomCommas($strCurrency);

      $opts = explode(',', $strOpts);
      $bRequired    = $opts[0]=='true';
      $strFN        = trim($opts[1]);
      $strLabel     = trim($opts[2]);

      if ($bRequired){
         if ($strCurrency==''){
//            $ErrMessages[$strFN] = '<div class="formError">'.'The '.$strLabel.' field is required.</div>';
            setCFormErrorMessage($strFN, 'The '.$strLabel.' field is required.');
            return(false);
         }
      }elseif ($strVal=='') {
         return('');
      }

      if (!is_numeric($strCurrency)){
//         $ErrMessages[$strFN] = '<div class="formError">'.'The '.$strLabel.' amount is not valid.</div>';
         setCFormErrorMessage($strFN, 'The '.$strLabel.' amount is not valid.');
         return(false);
      }else {
         return($strCurrency);
      }
   }

   function stripCustomCommas($strValue){
      return(str_replace (',', '', $strValue));
   }

   function repopulateCustomTables($lNumTables, &$utables){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumTables == 0) return;
      foreach ($utables as $utable){
         foreach ($utable->ufields as $ufield){
            $enumType = $ufield->enumFieldType;
            $strFN    = $ufield->strFieldNameInternal;

            switch ($enumType){
               case CS_FT_CHECKBOX:
                  $ufield->bChecked = set_value($strFN)=='true';
                  break;
               case CS_FT_DATE:
                  $ufield->txtValue = set_value($strFN);
                  break;

               case CS_FT_TEXT255:
               case CS_FT_TEXT80:
               case CS_FT_TEXT20:
               case CS_FT_TEXTLONG:
                  $ufield->txtValue = set_value($strFN);
                  break;
               case CS_FT_INTEGER:
                  $ufield->txtValue = set_value($strFN);
                  break;
               case CS_FT_CURRENCY:
                  $ufield->txtValue = set_value($strFN);
                  break;
               case CS_FT_DDL:
                  $ufield->lMatch = (int)$_POST[$strFN];
                  break;
               case CS_FT_DDLMULTI:
                  $ufield->lMatch = array();
                  if (isset($_POST[$strFN])){
                     foreach ($_POST[$strFN] as $strDDLVal){
                        $lIDX = (int)$strDDLVal;
                        if ($lIDX > 0) $ufield->lMatch[] = $lIDX;
                     }
                  }
                  break;
               case CS_FT_HEADING:
                  break;
               default:
                  screamForHelp($enumType.': invalid field type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
         }
      }

      setCustomUTableDDLs($lNumTables, $utables);
   }

   function setCustomUTableDDLs($lNumTables, &$utables){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumTables == 0) return;

      $CI =& get_instance();
      foreach ($utables as $utable){
         foreach ($utable->ufields as $ufield){
            $enumType = $ufield->enumFieldType;
            $lFieldID = $ufield->lFieldID;
            $strFN    = $ufield->strFieldNameInternal;
            if ($enumType==CS_FT_DDL){
               $strFN = $ufield->strFieldNameInternal;
               $ufield->strDDL =
                      '<select name="'.$strFN.'">
                       <option value="-1">&nbsp;</option>'."\n"
                     .$CI->clsUF->strDisplayUF_DDL($lFieldID, $ufield->lMatch)
                     .'</select>'."\n";
            }elseif ($enumType==CS_FT_DDLMULTI){
               $strFN = $ufield->strFieldNameInternal;
               $ufield->strDDL =
                      '<select multiple size="6" name="'.$strFN.'[]">
                       <option value="-1">&nbsp;</option>'."\n"
                     .$CI->clsUF->strDisplayUF_DDL($lFieldID, $ufield->lMatch)
                     .'</select>'."\n";
            }
         }
      }
   }

   function saveCustomPTables($lFID, $lNumTables, &$utables){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      if ($lNumTables == 0) return;
      $CI =& get_instance();

      $CI->clsUFD->lForeignID = $lFID;
      $CI->clsUFD->fields = array();
      $CI->clsUFD->fields[0] = new stdClass;
      $vfield = &$CI->clsUFD->fields[0];
      foreach ($utables as $utable){
         $bMultiEntry = $utable->bMultiEntry;
         if (!isset($utable->bCProg)) $utable->bCProg = false;

         if ($bMultiEntry){
            $utable->lKeyID = $utable->lTableID;
            $lRecID = $CI->clsUFD->lSaveMultiRecViaPost(true, $lFID, $lRecID, $utable);
         }else {
            $CI->clsUFD->lTableID = $lTableID = $utable->lTableID;
            foreach ($utable->ufields as $ufield){
               $vfield->enumFieldType = $enumType = $ufield->enumFieldType;
               $vfield->pff_lKeyID = $ufield->lFieldID;
               $strFN = $ufield->strFieldNameInternal;
               $varUserVal = null;

               switch ($enumType){

                  case CS_FT_CHECKBOX:
                     $varUserVal = @$_POST[$strFN]=='true';
                     break;
                  case CS_FT_DATE:
                     $varUserVal = trim($_POST[$strFN]);
                     if ($varUserVal==''){
                        $varUserVal = ' null ';
                     }else {
                        MDY_ViaUserForm($varUserVal, $lMon, $lDay, $lYear, $gbDateFormatUS);
                        $varUserVal = ' "'.strMoDaYr2MySQLDate($lMon, $lDay, $lYear).'" ';
                     }
                     break;
                  case CS_FT_TEXTLONG:
                  case CS_FT_TEXT255:
                  case CS_FT_TEXT80:
                  case CS_FT_TEXT20:
                     $varUserVal = trim($_POST[$strFN]);
                     break;
                  case CS_FT_INTEGER :
                     $varUserVal = trim($_POST[$strFN]);
                     $varUserVal = (int)stripCustomCommas($varUserVal);
//                     $varUserVal = (integer)trim($varUserVal);
                     break;
                  case CS_FT_HEADING:
                     break;
                  case CS_FT_CURRENCY:
                     $varUserVal = trim($_POST[$strFN]);
                     $varUserVal = stripCustomCommas($varUserVal);
                     $varUserVal = number_format((float)$varUserVal, 2, '.', '');
                     break;
                  case CS_FT_DDL:
                     $varUserVal = (integer)$_POST[$strFN];
                     if ($varUserVal <= 0) $varUserVal = ' null ';
                     break;
                  case CS_FT_DDLMULTI:
                     $multiDDL = new stdClass;
                     $multiDDL->lFieldID = $ufield->lFieldID;
                     $multiDDL->lTableID = $utable->lTableID;
                     $multiDDL->entries  = array();
                     $multiDDL->lNumEntries = 0;
                     
                     if (isset($_POST[$strFN])){
                        foreach ($_POST[$strFN] as $strDDL){
                           $lID = (int)$strDDL;
                           if ($lID > 0){
                              $multiDDL->entries[] = $lID;
                              ++$multiDDL->lNumEntries;
                           }
                        }
                     }
                     $CI->clsUFD->addDDLMultiEntries($lFID, $multiDDL);
                     break;
                  default:
                     screamForHelp($enumType.': invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                     break;
               }
               if ($enumType != CS_FT_HEADING && $enumType != CS_FT_DDLMULTI){
                  $CI->clsUFD->updateUserField($varUserVal);
               }
            }
         }
      }
   }

   function populateCustomTables($lNumTables, &$utables, $lFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      if ($lNumTables == 0) return;
      $CI =& get_instance();

      foreach ($utables as $utable){
         $lTableID = $utable->lTableID;
         if ($utable->lNumFields > 0){
            if ($utable->bMultiEntry){
               populateCustomTableViaDefault($utable);
            }else {
               $CI->clsUF->loadSingleDataRecord($lTableID, $lFID, $recInfo);
               $dataFields = &$CI->clsUF->fields;

               foreach ($utable->ufields as $ufield){
                  $enumType = $ufield->enumFieldType;
                  $strFN    = $ufield->strFieldNameInternal;
                  switch ($enumType){
                     case CS_FT_CHECKBOX:
                        $ufield->bChecked = getCheckStateViaFName($dataFields, $strFN);
                        break;

                     case CS_FT_DATE:
                        $vVal = getUserValueViaFName($dataFields, $strFN);
                        if (is_null($vVal)){
                           $ufield->txtValue = '';
                        }else {
                           $ufield->txtValue = strNumericDateViaMysqlDate($vVal, $gbDateFormatUS);
                        }
                        break;

                     case CS_FT_TEXT255:
                     case CS_FT_TEXT80:
                     case CS_FT_TEXT20:
                     case CS_FT_TEXTLONG:
                        $ufield->txtValue = htmlspecialchars(getUserValueViaFName($dataFields, $strFN));
                        break;

                     case CS_FT_LOG:
                     case CS_FT_HEADING:
                        break;

                     case CS_FT_INTEGER:
                        $ufield->txtValue = (int)getUserValueViaFName($dataFields, $strFN);
                        break;

                     case CS_FT_CURRENCY:
                        $ufield->txtValue = number_format((float)getUserValueViaFName($dataFields, $strFN), 2);
                        break;

                     case CS_FT_DDL:
                        $ufield->lMatch = (int)getUserValueViaFName($dataFields, $strFN);
                        break;

                     case CS_FT_DDLMULTI:
                        $CI->clsUF->loadMultiIDs($ufield->lFieldID, $lTableID, $recInfo->lRecID, $lNumEntries, $ufield->lMatch);
                        break;

                     default:
                        screamForHelp($enumType.': unexpected field type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                        break;
                  }
               }
            }
         }
      }
   }

   function populateCustomTableViaDefault($utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($utable->ufields as $ufield){
         $enumType = $ufield->enumFieldType;
         switch ($enumType){
            case CS_FT_CHECKBOX:
               $ufield->bChecked = (boolean)$ufield->bCheckDef;
               break;

            case CS_FT_DATE:
               $ufield->txtValue = '';
               break;

            case CS_FT_TEXT255:
            case CS_FT_TEXT80:
            case CS_FT_TEXT20:
            case CS_FT_TEXTLONG:
               $ufield->txtValue = htmlspecialchars($ufield->strTxtDef);
               break;

            case CS_FT_LOG:
            case CS_FT_HEADING:
               break;

            case CS_FT_INTEGER:
               $ufield->txtValue = (int)$ufield->lDef;
               break;

            case CS_FT_CURRENCY:
               $ufield->txtValue = number_format((float)$ufield->curDef, 2);
               break;

            case CS_FT_DDL:
               $ufield->lMatch = (int)$ufield->lDDLDefault;
               break;

            case CS_FT_DDLMULTI:
               $ufield->lMatch = '#error#'.__FILE__.' '.__LINE__.'<br>';
               break;

            default:
               screamForHelp($enumType.': unexpected field type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
      }
   }

   function getUserValueViaFName(&$dataFields, $strFN){
      $strOut = '#error#';
      foreach ($dataFields as $dfield){
         if ($dfield->strFieldNameInternal == $strFN){
            $strOut = $dfield->userValue;
            break;
         }
      }
      return($strOut);
   }

   function getCheckStateViaFName(&$dataFields, $strFN){
      $bOut = '#error#';
      foreach ($dataFields as $dfield){
         if ($dfield->strFieldNameInternal == $strFN){
            $bOut = (boolean)$dfield->userValue;
            break;
         }
      }
      return($bOut);
   }



