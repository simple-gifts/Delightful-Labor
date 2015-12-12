<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
// 
/*---------------------------------------------------------------------
      $this->load->helper('dl_util/recurring');
---------------------------------------------------------------------*/

   function strRecurringOpts(
                      &$displayData,  &$clsForm,      $selects, 
                      &$strForm,      &$javaJoeRadio, $strDate,
                      &$clsRecurring, $strDateField,  $strDateLabel, 
                      $strRecurLabel, $strRecurringLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      $strOut = '';
      $clsForm->bValueEscapeHTML = false;
      $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
      $clsForm->strEntryClass = 'enpView';
      $clsForm->strStyleExtraLabel = 'width: 100pt;';


      $displayData['js'] .= strDatePicker('datepickerFuture', true, 2010);
      $displayData['js'] .= $javaJoeRadio->insertJavaJoeRadio();

         //-----------------------------
         // hide/show recurring opts
         //-----------------------------
/*         
      $displayData['js'] .= '
         <script type="text/javascript">
         function unhide(divID) {
            var item = document.getElementById(divID);
            if (item) item.style.display=\'block\';
            var item2 = document.getElementById(\'hrefShow\');
            if (item2) item2.style.display=\'none\';
         }
         </script>
      ';
*/
      $clsForm->strExtraFieldText = form_error('txtStart');
      $strOut .=
             $clsForm->strGenericDatePicker(
                         $strDateLabel, 'txtStart', true,
                         $strDate, $strForm,
                         'datepickerFuture',
                         '');

      $clsRecurring->strRecurLabel = $strRecurringLabel;  //'Display';
      $strOut .=
           $clsForm->strLabelRow(
                             $strRecurLabel,
//                             '<a id="hrefShow" href="javascript:unhide(\'recurringOpts\');"><i>show recurring options</i></a>'.
                             $clsRecurring->strRecurringTable($strForm, $selects),
                             1,
                             '  ');
      return($strOut);
   }
