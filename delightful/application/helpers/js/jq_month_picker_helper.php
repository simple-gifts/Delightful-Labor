<?php
/*-------------------------------------------------
 copyright (c) 2014 John Zimmerman
      $this->load->helper('js/jq_month_picker');
--------------------------------------------------*/

function strMonthPicker($bIncludeLoadJQMP, $strID = 'month1'){
//---------------------------------------------------------------------
// month picker
//---------------------------------------------------------------------
   $strOut = '';
   if ($bIncludeLoadJQMP){
      $strOut .= '<script src="'.base_url().'js/jquery.ui.monthpicker.js"></script>'."\n";
   }
   $strOut .= '                        
      <script type="text/javascript">
         jQuery(document).ready(function() {
            jQuery("#'.$strID.'").monthpicker({
               showOn:     "both",
               buttonText: "Select month",
               buttonImage: "'.base_url().'images/layout/calendarDatePick01.png",
               buttonImageOnly: true
            });
         });
      </script>';
   return($strOut);
}

function bValidPickerMonth($strMonth, &$strErr){
//---------------------------------------------------------------------
// companion validation for the above routine
//---------------------------------------------------------------------
   $strErr = '';
   
   $startMo = explode('/', $strMonth);
   if (count($startMo)!=2){
      $strErr = 'The reporting date is not valid.';
      return(false);
   }
   $lMo = (integer)$startMo[0];
   $lYear = (integer)$startMo[1];
   if ($lMo <= 0 || $lMo > 12){
      $strErr = 'The reporting date is not valid.';
      return(false);
   }
   if ($lYear <= 1999 || $lYear > 2100){
      $strErr = 'The reporting date is out of range.';
      return(false);
   }
   return(true);
}
