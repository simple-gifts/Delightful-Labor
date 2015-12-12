<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*---------------------------------------------------------------------
// copyright (c) 2012-2015
// Austin, Texas 78759
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
  __construct          ()

  strTitleRow             ($strLabel, $lColSpan, $strStyleExtra)
  strLabelRow             ($strLabel, $strNote,  $lColSpanNote)
  strGenericTextEntry     ($strLabel, $strFieldName, $bRequired, ...
  strGenericDatePicker    ($strLabel, $strFieldName, $bRequired, ...
  strGenericDDLEntry      ($strLabel, $strFieldName, $strDDLOptions)
  strGenderRadioEntry     ($strLabel, $strFieldName, $bRequired, $enumSelect)
  strGenericPasswordEntry ($strLabel, $strFieldName, $bRequired, ...
  strGenericCheckEntry    ($strLabel, $strFieldName, $strCheckValue, ...
  strNotesEntry           ($stLabel,  $strFieldName, $bRequired, ...
  strSubmitEntry          ($strLabel, $lColSpan, $strFieldName, ...

-----------------------------------------------------------------------
   $this->load->library('generic_form');

   echoT('<table class="enpRpt">');
   echoT($clsForm->strTitleRow('Your data', 2, ''));
   echoT($clsForm->strGenericTextEntry('My little info', 'txtMyField', true, 'My field value', 40, 50));
   echoT($clsForm->strSubmitEntry('Submit', 2, 'cmdSubmit', ''));
   echoT('</table></form>');

---------------------------------------------------------------------*/

class generic_form {

   public $strLabelClass,         $strLabelClassRequired, $strEntryClass,
          $strLabelRowLabelClass, $strTitleClass,
          $bBGClearOnFocus,       $strTxtControlExtra, $strExtraFieldText,
          $bDisabled, $bPassword,
          $strID,                 $bValueEscapeHTML, $bAddLabelColon;

   public $strStyleExtraLabel, $strStyleExtraValue, $strExtraSelect,
          $strTextBoxPrefix, $strEntryTagExtra;

   public $calOptsTextOnChange, $calOptsTextOnFocus, $calOptsImgOnClick,
          $calOptsAfterCalImg;

   function __construct(){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      $this->strLabelClass         = 'enpRptLabel';
      $this->strLabelClassRequired = 'enpRptRequired';
      $this->strLabelRowLabelClass = 'enpRptLabel';
      $this->strTitleClass         = 'enpRptTitle';
      $this->strEntryClass         = 'enpRpt';
      $this->strExtraFieldText     =
      $this->strTxtControlExtra    =
      $this->strID                 =
      $this->strEntryTagExtra      =
      $this->strTextBoxPrefix      = '';

      $this->strStyleExtraLabel    = $this->strStyleExtraValue =
      $this->strExtraSelect = '';

         // calendar javascript options
      $this->calOptsTextOnChange   = 
      $this->calOptsTextOnFocus    = 
      $this->calOptsImgOnClick     = 
      $this->calOptsAfterCalImg    = '';

      $this->bBGClearOnFocus       = true;

      $this->bValueEscapeHTML      = $this->bAddLabelColon = true;

      $this->bDisabled             = $this->bPassword = false;
   }

   function strTitleRow($strLabel, $lColSpan, $strStyleExtra){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      return(
          '<tr>
              <td class="'.$this->strTitleClass.'" colspan="'.$lColSpan.'" style="'.$strStyleExtra.'"><b>'
                 .$strLabel.'
              </td>
           </tr>');
   }

   function strLabelRow($strLabel, $strNote, $lColSpanNote, $strTRExtra=''){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      if ($this->bValueEscapeHTML) $strNote = htmlspecialchars($strNote);
      $strHold =
          '<tr '.$strTRExtra.'>
              <td class="'.$this->strLabelRowLabelClass.'" style="'.$this->strStyleExtraLabel.'">'
                 .$strLabel.($this->bAddLabelColon ? ':' : '').'
              </td>
              <td class="'.$this->strEntryClass.'" style="padding-bottom: 6px;'.$this->strStyleExtraValue.'" '
                 .$this->strEntryTagExtra.'>'
                 .$strNote.$this->strExtraFieldText.'
              </td>
           </tr>';
      $this->strExtraFieldText = '';
      return($strHold);
   }
   
   function strLabelRowOneCol($strNote, $lColSpanNote, $strTRExtra='', $lUnderscoreWidth=0){
   //---------------------------------------------------------------
   // spans two columns
   //---------------------------------------------------------------
      if ($this->bValueEscapeHTML) $strNote = htmlspecialchars($strNote);
      
      if ($lUnderscoreWidth > 0){
         $strUnderscore = '<br>'.strImgTagBlackLine($lUnderscoreWidth, 2);
      }else {
         $strUnderscore = '';
      }
      $strHold =
          '<tr '.$strTRExtra.'>
              <td colspan="2" class="'.$this->strEntryClass.'" style="padding-bottom: 6px;'.$this->strStyleExtraValue.'">'
                 .$strNote.$this->strExtraFieldText.$strUnderscore.'
              </td>
           </tr>';
      $this->strExtraFieldText = '';
      return($strHold);
   }

   function strGenericTextEntry($strLabel, $strFieldName, $bRequired, $strValue, $lLen, $lMaxLen){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      $this->setRequired($bRequired, $strClass, $strStar);

      if ($this->bBGClearOnFocus){
         $strOnFocus = ' onFocus="'.$strFieldName.'.style.background=\'#fff\';" ';
      }else {
         $strOnFocus = '';
      }

      if ($this->bValueEscapeHTML) $strValue = htmlspecialchars($strValue);

      $strHold =
          '<tr>
              <td class="'.$strClass.'" style="'.$this->strStyleExtraLabel.'">'
                 .$strLabel.$strStar.($this->bAddLabelColon ? ':' : '').'
              </td>
              <td class="'.$this->strEntryClass.'" style="padding-bottom: 6px;'.$this->strStyleExtraValue.'">'
                 .$this->strTextBoxPrefix.'
                 <input type="'.($this->bPassword ? 'password' : 'text').'" name="'.$strFieldName.'" '
                      .($this->bDisabled ? ' disabled="disabled" ' : '').'
                      size="'.$lLen.'"
                      maxlength="'.$lMaxLen.'" '
                      .$this->strTxtControlExtra.' '
                      .$strOnFocus."\n".$this->strSetID().'
                      value="'.$strValue.'">'.$this->strExtraFieldText.'
              </td>
           </tr>';
      $this->strExtraFieldText = $this->strTxtControlExtra = '';
      $this->bPassword = false;
      return($strHold);
   }

   function strGenericDatePicker(
                      $strLabel, $strFieldName, $bRequired,
                      $strValue, $strForm,      $strJSPickerName,
                      $strExtraCell='', $bOnlyCalCell = false){
   //---------------------------------------------------------------
   // sample call:
   //   echoT($clsForm->strGenericDatePicker(
   //                   'Birthdate', 'txtBDate',      true,
   //                   $strBDay,    'frmEditPerson', 'datepicker'));
   //---------------------------------------------------------------
      $this->setRequired($bRequired, $strClass, $strStar);

      if ($this->bBGClearOnFocus){
         $strOnFocus = ' onFocus="'.$strFieldName.'.style.background=\'#fff\';" ';
      }else {
         $strOnFocus = '';
      }
      if ($this->bValueEscapeHTML) $strValue = htmlspecialchars($strValue);

      initCalendarOpts($calOpts);
      $calOpts->strDate         = $strValue;
      $calOpts->strForm         = $strForm;
      $calOpts->strTextObjName  = $strFieldName;
      $calOpts->strTextBoxID    = $strJSPickerName;
      $calOpts->strTextOnChange = $this->calOptsTextOnChange;
      $calOpts->strTextOnFocus  = $this->calOptsTextOnFocus;
      $calOpts->strImgOnClick   = $this->calOptsImgOnClick;
      $calOpts->strAfterCalImg  = $this->calOptsAfterCalImg;

      $strCalCell = strCalendarCell($calOpts).$strExtraCell.$this->strExtraFieldText;

      if ($bOnlyCalCell){
         $strHold = $strCalCell;
      }else {
         $strHold =
          '<tr>
              <td class="'.$strClass.'" style="'.$this->strStyleExtraLabel.'">'
                 .$strLabel.$strStar.($this->bAddLabelColon ? ':' : '').'
              </td>
              <td class="'.$this->strEntryClass.'" style="padding-bottom: 4px;'.$this->strStyleExtraValue.'">'
                 .$strCalCell.'
              </td>
           </tr>';
      }
      $this->strExtraFieldText = '';
      return($strHold);
   }

   function strGenericDDLEntry($strLabel, $strFieldName, $bRequired, $strDDLOptions){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      $this->setRequired($bRequired, $strClass, $strStar);
      $strHold =
          '<tr>
              <td class="'.$strClass.'"
                  style="'.$this->strStyleExtraLabel.'"
              >'
                 .$strLabel.$strStar.($this->bAddLabelColon ? ':' : '').'
              </td>
              <td class="'.$this->strEntryClass.'" style="padding-bottom: 0px;'.$this->strStyleExtraValue.'">
                 <select name="'.$strFieldName.'" '
                   .($this->bDisabled ? ' disabled="disabled" ' : '').' '
                  .$this->strExtraSelect.$this->strSetID().'
                 >'
                   .$strDDLOptions.'
                 </select>'.$this->strExtraFieldText.'
              </td>
           </tr>';
      $this->strExtraFieldText = '';
      return($strHold);
   }

   function strGenderRadioEntry($strLabel, $strFieldName, $bRequired, $enumSelect){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      $this->setRequired($bRequired, $strClass, $strStar);
      $strHold =
          '<tr>
              <td class="'.$strClass.'" style="'.$this->strStyleExtraLabel.'">'
                 .$strLabel.$strStar.($this->bAddLabelColon ? ':' : '').'
              </td>
              <td class="'.$this->strEntryClass.'" style="padding-bottom: 6px;'.$this->strStyleExtraValue.'">
                 <input type="radio" name="'.$strFieldName.'" value="Male" '   .($enumSelect=='Male'    ? 'checked' : '').'>Male
                 <input type="radio" name="'.$strFieldName.'" value="Female" ' .($enumSelect=='Female'  ? 'checked' : '').'>Female
                 <input type="radio" name="'.$strFieldName.'" value="Unknown" '.($enumSelect=='Unknown' ? 'checked' : '').'>Unknown'
                 .$this->strExtraFieldText.'
              </td>
           </tr>';
      $this->strExtraFieldText = '';
      return($strHold);
   }

   function strGenericPasswordEntry($strLabel,  $strFieldName, $bRequired,
                                 $strValue,  $lLen,            $lMaxLen,
                                 $bAgainDup, $strFieldName2, $strValue2=''){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      $this->setRequired($bRequired, $strClass, $strStar);
      $strIDField = $this->strSetID();

      if ($this->bBGClearOnFocus){
         $strOnFocus = ' onFocus="'.$strFieldName.'.style.background=\'#fff\';" ';
      }else {
         $strOnFocus = '';
      }

      $strHold =
          '<tr>
              <td class="'.$strClass.'" style="'.$this->strStyleExtraLabel.'">'
                 .$strLabel.$strStar.($this->bAddLabelColon ? ':' : '').'
              </td>
              <td class="'.$this->strEntryClass.'" style="'.$this->strStyleExtraValue.'">
                 <input type="password" '.$strIDField.'
                      name="'.$strFieldName.'"
                      size="'.$lLen.'"
                      maxlength="'.$lMaxLen.'" '
                      .$strOnFocus.'
                      value="'.$strValue.'">'.$this->strExtraFieldText.'
              </td>
           </tr>';
      if ($bAgainDup){
         $strHold .=
             '<tr>
                 <td class="'.$strClass.'" style="'.$this->strStyleExtraLabel.'">'
                    .$strLabel.' <font style="font-weight: normal;">(again)</font>'.$strStar.($this->bAddLabelColon ? ':' : '').'
                 </td>
                 <td class="'.$this->strEntryClass.'" style="'.$this->strStyleExtraValue.'">
                    <input type="password" name="'.$strFieldName2.'"
                         size="'.$lLen.'"
                         maxlength="'.$lMaxLen.'" '
                         .$strOnFocus.'
                         value="'.$strValue2.'">
                 </td>
              </tr>';
      }
      $this->strExtraFieldText = '';
      return($strHold);
   }

   private function strSetID(){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      if ($this->strID==''){
         return('');
      }else {
         $strHoldID = ' id="'.$this->strID.'" ';
         $this->strID = '';
         return($strHoldID);
      }
   }

   function strGenericCheckEntry($strLabel, $strFieldName, $strCheckValue, $bRequired, $bChecked){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      $this->setRequired($bRequired, $strClass, $strStar);

      if ($this->bBGClearOnFocus){
         $strOnFocus = ' onFocus="'.$strFieldName.'.style.background=\'#fff\';" ';
      }else {
         $strOnFocus = '';
      }
      $strHold =
          '<tr>
              <td class="'.$strClass.'" align="left" style="'.$this->strStyleExtraLabel.'">'
                 .$strLabel.$strStar.($this->bAddLabelColon ? ':' : '').'
              </td>
              <td class="'.$this->strEntryClass.'" align="left" style="padding-bottom: 0px;'.$this->strStyleExtraValue.'">
                 <input type="checkbox" name="'.$strFieldName.'" '
                     .($this->bDisabled ? ' disabled="disabled" ' : '').' '
                     .$this->strSetID().'                      
                      value="'.$strCheckValue.'" '.($bChecked ? 'checked' : '').'> '.$this->strExtraFieldText.'
              </td>
           </tr>';
      $this->strExtraFieldText = '';
      return($strHold);
   }

   function strNotesEntry($strLabel, $strFieldName, $bRequired, $strValue, $lRows, $lCols){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      $this->setRequired($bRequired, $strClass, $strStar);
      if ($this->bValueEscapeHTML) $strValue = htmlspecialchars($strValue);
      $strOut =
          '<tr>
              <td class="'.$strClass.'" align="left" style="'.$this->strStyleExtraLabel.'">'
                 .$strLabel.$strStar.($this->bAddLabelColon ? ':' : '').'
              </td>
              <td class="'.$this->strEntryClass.'" align="left" style="padding-bottom: 0px;'.$this->strStyleExtraValue.'">
                 <textarea name="'.$strFieldName.'" '
                     .$this->strSetID().' '
                     .$this->strTxtControlExtra.' 
                     rows="'.$lRows.'"
                     cols="'.$lCols.'">'.$strValue.'</textarea>'.$this->strExtraFieldText.'
              </td>
           </tr>';
      $this->strExtraFieldText = $this->strTxtControlExtra = '';
      return($strOut);
   }

   function strSubmitEntry($strLabel, $lColSpan, $strFieldName, $strExtraStyle, $strOnClick=''){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      if ($strOnClick==''){
         $strOnClick = ' this.disabled=1; this.form.submit(); ';
      }
      
      if ($this->strStyleExtraLabel==''){
         $strTDStyle = ' text-align: center; ';
      }else {
         $strTDStyle = '';
      }
         /* the clause
               onclick="this.disabled=1; this.form.submit();"
            prevents the form from being submitted multiple times by eager clickers
         */
      return('
           <tr>
              <td class="'.$this->strEntryClass.'"
                  style=" '.$this->strStyleExtraLabel.$strTDStyle.'"
                  colspan="'.$lColSpan.'">
                 <input type="submit" name="'.$strFieldName.'" value="'.$strLabel.'"
                    style="'.$strTDStyle.$strExtraStyle.'"
                    onclick="'.$strOnClick.'"
                    class="btn"
                       onmouseover="this.className=\'btn btnhov\'"
                       onmouseout="this.className=\'btn\'">
              </td>
           </tr>');
   }

   public function strSubmitButton($strLabel, $strFieldName, $strExtraStyle){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->strStyleExtraLabel==''){
         $strTDStyle = ' text-align: center; ';
      }else {
         $strTDStyle = '';
      }
      return('
              <input type="submit" name="'.$strFieldName.'" value="'.$strLabel.'"
                 style="'.$strTDStyle.$strExtraStyle.'"
                 class="btn"
                    onmouseover="this.className=\'btn btnhov\'"
                    onmouseout="this.className=\'btn\'">
             ');
   }

   private function setRequired($bRequired, &$strClass, &$strStar){
   //---------------------------------------------------------------
   //
   //---------------------------------------------------------------
      if ($bRequired){
         $strClass = $this->strLabelClassRequired;
         $strStar  = '*';
      }else {
         $strClass = $this->strLabelClass;
         $strStar  = '';
      }
   }

   function strRecCreateModInfo(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      if ($this->bNewRec){
         $strNew = $strMod = 'n/a';
      }else {
         $strNew = htmlspecialchars($this->strStaffCFName.' '.$this->strStaffCLName).' on '
                             .date($genumDateFormat.' H:i:s', $this->dteOrigin);
         $strMod = htmlspecialchars($this->strStaffLFName.' '.$this->strStaffLLName).' on '
                             .date($genumDateFormat.' H:i:s', $this->dteLastUpdate);
      }
      return($this->strLabelRow('Created By',       $strNew, 1)
            .$this->strLabelRow('Last Modified By', $strMod, 1));

   }


}

?>