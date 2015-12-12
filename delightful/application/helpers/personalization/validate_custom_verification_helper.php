<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('personalization/validate_custom_verification');
---------------------------------------------------------------------*/

namespace customVal;

   function verifyVerMod($strMod, &$strErr){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      $strErr = '';
      $strMod = trim($strMod);
      $lLen = strlen($strMod);

      if ($lLen == 0) return(true);

         // must end with "_helper.php"
      if ($lLen <= 11){
         $strErr = 'Your validation file name must end in "_helper.php".';
         return(false);
      }
      if (strpos($strMod, '/')!==false || strpos($strMod, '\\')!==false){
         $strErr = 'Please don\'t specify a path.';
         return(false);
      }
      if (substr(strrev($strMod), 0, 11) != 'php.repleh_'){
         $strErr = 'Your validation file name must end in "_helper.php".';
         return(false);
      }
      return(true);
   }

   function verifyVModEntry($strEntry, $strMod, &$strErr){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strErr = '';
      $strEntry  = trim($strEntry);
      $lLenEntry = strlen($strEntry);
      $lLenMod   = strlen($strMod);

      if ($lLenEntry == 0){
         if ($lLenMod == 0){
            return(true);
         }else {
            $strErr = 'If you specify a validation file, you must also specify an entry point.';
            return(false);
         }
      }else {
         if ($lLenMod == 0){
            $strErr = 'If you specify an entry point, you must also specify a validation file.';
            return(false);
         }else {
            return(true);
         }
      }
   }

   function hiddenVerify($strVal, $strOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbShowHiddenVerifyError;

      $CI =& get_instance();

      $gbShowHiddenVerifyError = true;

      $aOpts = explode(',', $strOpts);

      $strVerificationModule = trim($aOpts[0]);
      $strVModEntryPoint     = trim($aOpts[1]);

         // does the file exist?
      $strFullFN = './application/helpers/custom_verification/'.$strVerificationModule;
      $fArray = get_file_info($strFullFN);
      if ($fArray===false){
         $CI->form_validation->set_message('hiddenVerify',
                   'Unable to find custom validation file! Please check with your system administrator.');
         return(false);
      }

         // extract the helper name, then load helper
      $strHelperName = substr($strVerificationModule, 0, strlen($strVerificationModule)-11);
      $CI->load->helper('custom_verification/'.$strHelperName);

         // does the entry point exist?
      if (!is_callable($strVModEntryPoint)){
         $CI->form_validation->set_message('hiddenVerify',
                   'Unable to find the entry point for custom validation file! Please check with your system administrator.');
         return(false);
      }
      $gbShowHiddenVerifyError = false;
      return(call_user_func($strVModEntryPoint));
   }

   function setErrorMessage($strFieldName, $strErrorMessage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gErrMessages;

      if (isset($gErrMessages[$strFieldName])){
         $gErrMessages[$strFieldName] .= '<br>';
      }else {
         $gErrMessages[$strFieldName] = '';
      }
      $gErrMessages[$strFieldName] .= '<div class="formError">'.$strErrorMessage.'</div>';
   }

