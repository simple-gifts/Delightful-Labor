<?php
/*-------------------------------------------------------------------------------
      $this->load->helper('reports/date_range');
-------------------------------------------------------------------------------*/

function tf_initDateRangeMenu(&$opts, $strCTRL_Prefix=''){
/*---------------------------------------------------------------

---------------------------------------------------------------*/
   global $gdteNow, $gbDateFormatUS;

   $opts = new stdClass;

   $opts->strCTRL_Prefix  = $strCTRL_Prefix;
   $opts->strFormName     = 'frmTFRpt';
   $opts->strID           = 'idTFRpt';
   
   $opts->strLabel        = '';
   $opts->bDatePreDefined = false;
   $opts->dteStart = $opts->dteEnd = $gdteNow;
   $opts->strDatePredefinedID = '';
   
   $opts->bDateRange             = true;
   $opts->bShowNoDateRangeOption = true;
   $opts->bNoDateRange           = false;

   $opts->bOptShow_NoDateRange  =
   $opts->bOptShow_Today        =
   $opts->bOptShow_Yesterday    =
   $opts->bOptShow_ThisWeek     =
   $opts->bOptShow_ThisMonth    =
   $opts->bOptShow_LastMonth    =
   $opts->bOptShow_Past30       =
   $opts->bOptShow_Past60       =
   $opts->bOptShow_Past90       =
   $opts->bOptShow_PastYear     =
   $opts->bOptShow_YearToDate   =
   $opts->bOptShow_LastYear     =

   $opts->bOptShow_QtrsThisYear =
   $opts->bOptShow_QtrsLastYear = true;
   
   $opts->strSDateFN = 'txtSDate';
   $opts->strEDateFN = 'txtEDate';
   
   $dte2000 = strtotime('1/1/2000');
   if ($gbDateFormatUS){
      $opts->txtSDate = date('m/d/Y', $dte2000);
      $opts->txtEDate = date('m/d/Y', $gdteNow);
   }else {
      $opts->txtSDate = date('d/m/Y', $dte2000);
      $opts->txtEDate = date('d/m/Y', $gdteNow);
   }
   
   $opts->strRadioFN = $opts->strCTRL_Prefix.'rdo_TimePeriod';
   
}

function tf_strDateRangeMenu(&$opts){
//-------------------------------------------------------------------------
// assumes that the form has already been defined
//
// requires the following include files:
//
//   require_once('../util/util_js_RadioSetDateTime.php');
//   require_once('../dbAustinLib/util_TimeDate.php');
//   require_once('../java/util_JavaJoe_Radio.php');
//
// ----------------------------------
//   bShowNoDateRangeOption
//                       - if true, give the user the option of specifying no report period
//   strFormName         - the name of the form already defined by the caller
//   strLabel            - label at the top of the selection area
//   strCTRL_Prefix      - a string to uniquely identify controls; used if multiple instances of
//                         the date range are used within a single form
//   bDatePreDefined     - if true, make the drop-down of predefined date ranges the default
//   strDatePredefinedID - default selection within the list of predefined dates
//                         see constants in util_TimeDate.asp
//
//   bDateRange   - if true, make the manual date range the default
//   dteStart     - starting date of the manual date range
//   dteEnd       - ending date of the manual date range
//   bNoDateRange - if bShowNoDateRangeOption is true and this field is true, make
//       this the default selection
//
//-------------------------------------------------------------------------
   global $gdteNow;   

   $strOut = '';   
   $strRadioFN = $opts->strRadioFN;
   
   if ($opts->strLabel != ''){
      $strOut .=
          '<span>
           <b><u>'.$opts->strLabel.'</u><br></b></font>
           </span>'."\n";
   }

   $strOut .=
    '<table  cellpadding="1" cellspacing="0" style="border: 1px solid #aaa;">';
    
   $strOut .= tf_strRowUserDefined($opts);
   $strOut .= tf_strRowPredefined($opts);
   $strOut .= tf_strRowSpacer();
              
   if ( $opts->bShowNoDateRangeOption ){
      $strOut .= tf_strNowRangeRow($opts);
   }
   
   $strOut .= '</table><br>'."\n";
   return($strOut);
}

function tf_strNowRangeRow(&$opts){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strRadioFN = $opts->strRadioFN;
   return('
           <tr>
              <td valign="top">
                 <input type="radio"
                    name="'.$strRadioFN.'" value="None" '
                  .($opts->bNoDateRange ? 'checked' : '').' >
              </td>
              <td>
                 <span onClick=setRadioOther('.$opts->strFormName.'.'.$strRadioFN.',\'None\')>
                 Don\'t use a time period for this report</span>
              </td>
           </tr>'."\n");   
}

function tf_strRowSpacer(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   return('
        <tr>
           <td colspan="3">
              <img border="0" src="'.base_url().'images/misc/1px_clear.gif" width="100%" height="5">
           </td>
        </tr>');
}        

function tf_strRowUserDefined(&$opts){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 90pt;';
   $clsForm->bValueEscapeHTML = false;   

   $strRadioFN = $opts->strRadioFN;
   $strOut = '';
   $strOut .= '        
        <tr>
           <td valign="top">
              <input type="radio" name="'.$strRadioFN.'" value="User" '
               .($opts->bDateRange ? 'checked':'').' >
           </td>
           <td valign="middle">
              <span onClick=setRadioOther('.$opts->strFormName.'.'.$strRadioFN.',\'User\')>
              define your report range:</span> <br>'."\n";
              
      //----------------------
      // report start
      //----------------------
   $strOut .= '<table >';
   $strOut .= strDatePicker('datepicker1', false);
   $clsForm->strStyleExtraLabel = 'padding-top: 8px;';
   $clsForm->strExtraFieldText = form_error($opts->strSDateFN);
   
   $clsForm->calOptsTextOnFocus   =       
   $clsForm->calOptsTextOnChange  = ' setDateRadio('.$opts->strFormName.'.'.$strRadioFN.',\'User\') ';
   $strOut .= $clsForm->strGenericDatePicker(
                      'Start',   $opts->strSDateFN,  true,
                      $opts->txtSDate, $opts->strFormName, 'datepicker1', '');

      //----------------------
      // report end
      //----------------------
   $strOut .= strDatePicker('datepicker2', true);
   $clsForm->strExtraFieldText = form_error($opts->strEDateFN);
   $strOut .= $clsForm->strGenericDatePicker(
                      'End',     $opts->strEDateFN,  true,
                      $opts->txtEDate, $opts->strFormName, 'datepicker2');
                      
   $clsForm->calOptsTextOnFocus   =       
   $clsForm->calOptsTextOnChange  = '';                     
                      
   $strOut .= '</table>';
   return($strOut);
}

function tf_strRowPredefined(&$opts){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gdteNow;
   
   $strOut = '';
   
   $strRadioFN = $opts->strRadioFN;
   $iDay    = (integer)date('j', $gdteNow);
   $iMonth  = (integer)date('n', $gdteNow);
   $iYear   = (integer)date('Y', $gdteNow);
   $iYearM1 = $iYear - 1;
   
   $strOut .= '
        <tr>
           <td valign="middle">
              <input type="radio" name="'.$opts->strCTRL_Prefix.'rdo_TimePeriod" value="DDL" '
                    .($opts->bDatePreDefined ? 'checked' : '').'>
           </td>'."\n";

   $strOut .=
          '<td valign="middle">
              <span onClick=setRadioOther('.$opts->strFormName.'.'.$strRadioFN.',\'DDL\')>
              predefined time periods:</span>
              <select size="1" name="'.$opts->strCTRL_Prefix.'ddl_TimeFrame"
                   onChange=setDateRadio('.$opts->strFormName.'.'.$strRadioFN.',\'DDL\')>'."\n";

    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_Today,        (string)(CI_DATERANGE_TODAY),      "Today");
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_Yesterday,    (string)(CI_DATERANGE_YESTERDAY),  "Yesterday");
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_ThisWeek,     (string)(CI_DATERANGE_THIS_WEEK),  "This Week");
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_ThisMonth,    (string)(CI_DATERANGE_THIS_MONTH), "Month To Date");
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_LastMonth,    (string)(CI_DATERANGE_LAST_MONTH), "Last Month");
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_Past30,       (string)(CI_DATERANGE_PAST_30),    "Past 30 Days");
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_Past60,       (string)(CI_DATERANGE_PAST_60),    "Past 60 Days");
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_Past90,       (string)(CI_DATERANGE_PAST_90),    "Past 90 Days");
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_PastYear,     (string)(CI_DATERANGE_PAST_YEAR),  "Past Year");
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_YearToDate,   (string)(CI_DATERANGE_THIS_YEAR),  "Year To Date");
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_LastYear,     (string)(CI_DATERANGE_LAST_YEAR),  "Last Year");

    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_QtrsThisYear, (string)(CI_DATERANGE_1ST_Q) . $iYear,   "1st Quarter " . $iYear);
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_QtrsThisYear, (string)(CI_DATERANGE_2ND_Q) . $iYear,   "2nd Quarter " . $iYear);
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_QtrsThisYear, (string)(CI_DATERANGE_3RD_Q) . $iYear,   "3rd Quarter " . $iYear);
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_QtrsThisYear, (string)(CI_DATERANGE_4TH_Q) . $iYear,   "4th Quarter " . $iYear);
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_QtrsLastYear, (string)(CI_DATERANGE_1ST_Q) . $iYearM1, "1st Quarter " . $iYearM1);
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_QtrsLastYear, (string)(CI_DATERANGE_2ND_Q) . $iYearM1, "2nd Quarter " . $iYearM1);
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_QtrsLastYear, (string)(CI_DATERANGE_3RD_Q) . $iYearM1, "3rd Quarter " . $iYearM1);
    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_QtrsLastYear, (string)(CI_DATERANGE_4TH_Q) . $iYearM1, "4th Quarter " . $iYearM1);

    $strOut .= add_DR_Option($opts->strDatePredefinedID, $opts->bOptShow_NoDateRange,  (string)(CI_DATERANGE_NONE), "All");

   $strOut .=
            '</select>
           </td>
        </tr>'."\n";
   return($strOut);
}

function add_DR_Option($strMatch, $bAddToDLL, $strValue, $strLabel){
//-------------------------------------------------------------------------
//
//-------------------------------------------------------------------------
   if ($bAddToDLL ){
      return(
         '<option value="'.$strValue.'" '.($strMatch==$strValue?'selected':'').' >'.$strLabel.'</option>'."\n");
   }
}

function tf_getDateRanges($opts, &$formDates){
//-------------------------------------------------------------------------
//
//-------------------------------------------------------------------------
   global $gdteNow, $gbDateFormatUS;

   $formDates = new stdClass;
   
   $formDates->bError = false;
   $formDates->strRptRange  = $_REQUEST[$opts->strCTRL_Prefix.'rdo_TimePeriod'];
   $formDates->strDateRange = '';
   
   $bAddTextDate = true;

   switch ($formDates->strRptRange) {
      case 'DDL':
         $bAddTextDate = false;
         $formDates->bUseDateRange = true;
         $formDates->sDDL = $_REQUEST[$opts->strCTRL_Prefix.'ddl_TimeFrame'];
         determineDateRange((integer)($formDates->sDDL), $gdteNow, $formDates->dteStart, $formDates->dteEnd,
                          $formDates->strDateRange);
         break;

      case 'User':
         $formDates->bUseDateRange = true;
         $strDate   = trim($_POST[$opts->strSDateFN]);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $formDates->dteStart = strtotime($lMon.'/'.$lDay.'/'.$lYear);
         
         $strDate   = trim($_POST[$opts->strEDateFN]);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $formDates->dteEnd = strtotime($lMon.'/'.$lDay.'/'.$lYear.' 23:59:59');
         break;

      case 'None':
         $formDates->bUseDateRange = false;
         
         $formDates->dteStart  = strtotime('1/1/1970');
         $formDates->dteEnd    = strtotime('1/18/2038');  // end of Unix epoch
         $formDates->strDateRange = "No Date Range";       
         $bAddTextDate = false;
         break;

      default:
         $formDates->bError = true;
         echoT('
            <br><font color="red">Please click on a <b><i>radio button</b></i> (<input type="radio">)
            from the data selection list to run your search.<br><br></font>
            You can click on your browser\'s <b><i>back</b></i> button and try again.<br><br>');
         break;
   }

   
   if ($formDates->bUseDateRange){
      $formDates->strBetween = ' BETWEEN '.strPrepDate($formDates->dteStart).' AND '.strPrepDateTime($formDates->dteEnd).' ';
   }else {
      $formDates->strBetween = ' BETWEEN "0000-00-00" AND "9999-00-00" ';
   }
   if ($bAddTextDate) {
      if ($gbDateFormatUS){
         $formDates->strDateRange = date('m/d/Y', $formDates->dteStart).' - '.date('m/d/Y', $formDates->dteEnd);
      }else {
         $formDates->strDateRange = date('d/m/Y', $formDates->dteStart).' - '.date('d/m/Y', $formDates->dteEnd);
      }
   }
}



?>
