<?php
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2012-2013 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
 Functions to support the User-Defined Personalized Fields
---------------------------------------------------------------------
      $this->load->model('personalization/muser_fields_display', 'clsUFD');
---------------------------------------------------------------------*/


class muser_fields_display extends muser_fields{

   public $lFieldSetWidth, $lLabelWidth, $lFieldValWidth, $lDispTableID,
          $strViewAllLogLinkBase, $bShowViewAllLog, $lMaxLogDisplayLen,
          $bShowLogEditLinks, $strUpdateReturn, $strFormName;

   public $lNumMRRecs, $mrRecs, $mrSQLWhereExtra, $mrSQLOrder, $mrSQLSelect;

   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->initBaseClass();

      $this->lLabelWidth    = 150;
      $this->lFieldValWidth = 400;
      $this->lFieldSetWidth = 550;

      $this->lDispTableID = $this->strViewAllLogLinkBase = null;

      $this->bShowViewAllLog = true;
      $this->bShowLogEditLinks = false;
      $this->lMaxLogDisplayLen = CS_UF_MAXLOGDISPLAYLEN;

      $this->strUpdateReturn = '';
      $this->strFormName = 'frmUpdateField';

      $this->lNumMRRecs = $this->mrRecs = null;
      $this->mrSQLWhereExtra = $this->mrSQLOrder = $this->mrSQLSelect = '';
   }

   public function strEditUserTableEntries($lEditFieldID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat, $gbDateFormatUS;
      $strOut = '';

      $clsACO = new madmin_aco();

      if (is_null($this->lForeignID)) screamForHelp('Class not initialized!<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);

      $bAnyEdit = $lEditFieldID > 0;
      $this->loadSingleDataRecord($this->lTableID, $this->lForeignID, $recInfo);

      $utable = &$this->userTables[0];
      $enumTType = $utable->enumTType;

      $this->tableContext(0);
      $this->tableContextRecView(0);

      $strOut .= '
         <fieldset class="enpFS" style="width: '.$this->lFieldSetWidth.'pt; align: left;">
              <legend class="enpLegend">
                 <b><i>'.htmlspecialchars($utable->strUserTableName).'</b></i>
              </legend>';
      $strOut .=
            'Entry for: '.$this->strContextName.' <i>('.$this->strContextLabel.')</i> '.$this->strContextViewLink.'<br>';

      if ($bAnyEdit) {
         $strOut .= strDatePicker('datepickerFuture', true);
      }

      $strOut .= '
            <table class="enpRpt">';
      for ($idx=0; $idx < $this->lNumFields; ++$idx){
         $enumType = $this->fields[$idx]->enumFieldType;

         if ($enumType==CS_FT_HEADING){
            $strOut .= '
                     <tr>
                        <td colspan="2" class="enpRptLabel">'
                           .htmlspecialchars($this->fields[$idx]->pff_strFieldNameUser).'
                        </td>';
         }else {

            if ($enumType != CS_FT_LOG){
               $userValue  = $this->fields[$idx]->userValue;
               $lFieldID   = $this->fields[$idx]->pff_lKeyID;
               $strFName   = 'var'.$lEditFieldID;
               $bEditField = $lEditFieldID==$lFieldID;

               $strOut .= '
                     <tr>
                        <td width="'.$this->lLabelWidth.'" class="enpRptLabel">'
                           .htmlspecialchars($this->fields[$idx]->pff_strFieldNameUser).'
                        </td>
                        <td class="enpRpt" width="'.$this->lFieldValWidth.'" valign="left">';
               if ($bEditField){
                  $strUserErr = form_error($strFName);  // did user make an invalid entry?
                  $bUserEntryErr = $strUserErr.'' != '';
                  $attributes = array('name' => $this->strFormName, 'id' => 'frmAddEdit');
                  $strOut .= form_open('admin/uf_user_edit/userAddEdit/'
                                .$this->lTableID.'/'.$this->lForeignID.'/'.$lFieldID, $attributes);
                  switch ($enumType){

                     case CS_FT_CHECKBOX:
                        $strOut .= '
                           <input type="checkbox" value="TRUE" id="addEditEntry"
                                  name="'.$strFName.'" '.((boolean)$userValue ? 'checked' : '').'>&nbsp;';
                        break;
                     case CS_FT_DATE:
                        if ($bUserEntryErr){
                           $strDate = set_value($strFName);
                        }else {
                           if (is_null($userValue)){
                              $strDate = '';
                           }else {
                              $strDate = strNumericDateViaMysqlDate($userValue, $gbDateFormatUS);
                           }
                        }

                        initCalendarOpts($calOpts);
                        $calOpts->strDate         = $strDate;
                        $calOpts->strForm         = $this->strFormName;
                        $calOpts->strTextObjName  = $strFName;
                        $calOpts->strTextBoxID    = 'datepickerFuture';

                        $strOut .= strCalendarCell($calOpts).'&nbsp;';    //$strDate, $this->strFormName, $strFName, 'datepickerFuture')

                        break;
   //                  case CS_FT_DATETIME:
   //                     echoT(date($genumDateFormat.' H:i:s', $userValue));
   //                     break;
                     case CS_FT_TEXTLONG:
                        $strOut .= '<textarea name="'.$strFName.'" rows=6 cols=64 id="addEditEntry" >'
                                .htmlspecialchars($userValue).'</textarea><br>';
                        break;
                     case CS_FT_TEXT255:
                        $strOut .= '<input type="text" size="50" maxlength="255" name="'.$strFName.'"
                                      value="'.htmlspecialchars($userValue).'" id="addEditEntry" >&nbsp;';
                        break;
                     case CS_FT_TEXT80:
                        $strOut .= '<input type="text" size="50" maxlength="80" name="'.$strFName.'"
                                      value="'.htmlspecialchars($userValue).'" id="addEditEntry" >&nbsp;';
                        break;
                     case CS_FT_TEXT20:
                        $strOut .= '<input type="text" size="20" maxlength="20" name="'.$strFName.'"
                                      value="'.htmlspecialchars($userValue).'" id="addEditEntry" >&nbsp;';
                        break;
                     case CS_FT_INTEGER :
                        if ($bUserEntryErr){
                           $strNumber = set_value($strFName);
                        }else {
                           $strNumber = number_format($userValue);
                        }
                        $strOut .= '<input type="text" size="10" maxlength="10" name="'.$strFName.'"
                                   value="'.$strNumber.'" id="addEditEntry" >&nbsp;';
                        break;
                     case CS_FT_CURRENCY:
                        $clsACO->loadCountries(false, false, true, $this->fields[$idx]->pff_lCurrencyACO);
                        if ($bUserEntryErr){
                           $strNumber = set_value($strFName);
                        }else {
                           $strNumber = number_format($userValue, 2, '.', '');
                        }
                        $strOut .=
                            $clsACO->countries[0]->strCurrencySymbol.' '
                           .'<input type="text" size="10" maxlength="20" name="'.$strFName.'"
                                   value="'.$strNumber.'" id="addEditEntry" >&nbsp;'
                                   .$clsACO->countries[0]->strFlagImg;
                        break;
                     case CS_FT_DDL:
                        $strOut .= '<select name="'.$strFName.'" id="addEditEntry" >';
                        $strOut .= $this->strDisplayUF_DDL($lEditFieldID, $userValue);
                        $strOut .= '</select>&nbsp;';
                        break;
                     default:
                        screamForHelp($enumType.': invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                        break;
                  }
                          // note: including the following line in the submit button prevents
                          // the form from being submitted when unchecking a single check box ?????
//                           onclick="this.disabled=1; this.form.submit();"
                  $strOut .= '<input type="submit"
                           name="cmdAdd"
                           value="Save"
                           style="font-size: 8pt; height: 13pt; text-align: text-top;"
                           class="btn"
                              onmouseover="this.className=\'btn btnhov\'"
                              onmouseout="this.className=\'btn\'">'.$strUserErr;
               }else {
                  $strOut .= strLinkEdit_UFTableEntries($enumTType, $this->lTableID, $this->lForeignID,
                                 $lFieldID, true, 'edit field', '').'&nbsp;';

                  if (is_null($userValue)){
                     $strOut .= '&nbsp';
                  }else {
                     switch ($enumType){

                        case CS_FT_CHECKBOX:
                           $strOut .= ((boolean)$userValue ? 'Yes' : 'No');
                           break;
                        case CS_FT_DATE    :
                           $strOut .= strNumericDateViaMysqlDate($userValue, $gbDateFormatUS);
                           break;
   //                     case CS_FT_DATETIME:
   //                        echoT(date($genumDateFormat.' H:i:s', $userValue));
   //                        break;
                        case CS_FT_TEXTLONG:
                           $strOut .= nl2br(htmlspecialchars($userValue));
                           break;
                        case CS_FT_TEXT255:
                        case CS_FT_TEXT80:
                        case CS_FT_TEXT20:
                           $strOut .= htmlspecialchars($userValue).'&nbsp;';
                           break;
                        case CS_FT_INTEGER :
                           $strOut .= number_format($userValue);
                           break;
                        case CS_FT_CURRENCY:
                           $clsACO->loadCountries(false, false, true, $this->fields[$idx]->pff_lCurrencyACO);
                           $strOut .=
                              $clsACO->countries[0]->strCurrencySymbol.' '
                             .number_format($userValue, 2).' '.$clsACO->countries[0]->strFlagImg;
                           break;
                        case CS_FT_DDL:
                           $strOut .= htmlspecialchars($this->strDDLValue($userValue));
                           break;
                        default:
                           screamForHelp($enumType.': invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                           break;
                     }
                  }
               }
            }
         }
      }
      $strOut .= '</table>';
      if ($bAnyEdit) $strOut .= '</form><script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>';
      $strOut .= '</fieldset>';
      return($strOut);
   }

   public function updateUserField($varUserVal){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strFN       = $this->strGenUF_FieldName($this->lTableID, $this->fields[0]->pff_lKeyID);
      $strTable    = $this->strGenUF_TableName($this->lTableID);
      $strFKeyFN   = $this->strGenUF_KeyFieldPrefix($this->lTableID).'_lForeignKey';
//      $strRecSetFN = $this->strGenUF_KeyFieldPrefix($this->lTableID).'_bRecordEntered';
      $enumType    = $this->fields[0]->enumFieldType;
      switch ($enumType){

         case CS_FT_CHECKBOX:
            $strSet = $strFN.' = '.($varUserVal ? '1' : '0').' ';
            break;
         case CS_FT_DATE    :
            $strSet = $strFN.' = '.$varUserVal.' ';
            break;
         case CS_FT_DATETIME:
            break;
         case CS_FT_TEXTLONG:
         case CS_FT_TEXT255:
         case CS_FT_TEXT80:
         case CS_FT_TEXT20:
            $strSet = $strFN.' = '.strPrepStr($varUserVal).' ';
            break;
         case CS_FT_INTEGER :
            $strSet = $strFN.' = '.$varUserVal.' ';
            break;
         case CS_FT_CURRENCY:
            $strSet = $strFN.' = '.$varUserVal.' ';
            break;
         case CS_FT_DDL:
            $strSet = $strFN.' = '.$varUserVal.' ';
            break;
         default:
            screamForHelp($enumType.': invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      $sqlStr =
         "UPDATE $strTable
          SET $strSet   -- , $strRecSetFN=1
          WHERE $strFKeyFN=$this->lForeignID;";
/*
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__);
$zzzstrFile=substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1)));
echoT('<font class="debug">'.$zzzstrFile.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br(htmlspecialchars($sqlStr))."<br><br></font>\n");
echo(__FILE__.' '.__LINE__.'<br>'."\n"); die;
*/
      $this->db->query($sqlStr);
      $this->markSingleEntryRecRecorded($this->lTableID, $this->lForeignID);

   }

   public function strDiplayUserTable(&$clsUserTable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_null($this->lForeignID)) screamForHelp('Class not initialized!<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      if ($clsUserTable->bMultiEntry){
         return($this->strDisplayUserTableMulti($clsUserTable));
      }else {
         return($this->strDisplayUserTableSingle($clsUserTable));
      }
   }

   function strDisplayUserTableMulti(&$uTable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      $lTableID = $uTable->lKeyID;
      $strTable = $uTable->strDataTableName;
      $lFID     = $this->lForeignID;

      $bCollapsibleHeadings = $uTable->bCollapsibleHeadings;
      $bCollapseDefaultHide = $uTable->bCollapseDefaultHide;
      $strSafeTableName = htmlspecialchars($uTable->strUserTableName);
      $strOut .= $this->strBeginCollapsibleHeading($lTableID, $strSafeTableName, false);

      $strOut .= '<b><i>'.htmlspecialchars($uTable->strUserTableName).'</b> (multi-record table)</i> ';
      $lNumRecs = $this->lNumMultiRecsViaFID($uTable, $lFID);

         // it's the little things that make a house a home.
      $strOut .= '&nbsp;&nbsp;&nbsp;<b>'.$lNumRecs.'</b> record'.($lNumRecs==1 ? '' : 's');
      if ($lNumRecs > 0){
         $strOut .= '&nbsp;'.strLinkView_UFMFRecordsViaFID($uTable->enumTType, $lTableID, $lFID, 'View', true).'&nbsp;'
                            .strLinkView_UFMFRecordsViaFID($uTable->enumTType, $lTableID, $lFID, 'View', false);
      }

      $strOut .= '<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                .strLinkAdd_UFMultiRecEntry($uTable->enumTType, $lTableID, $lFID, true,  'Add record', ' id="mptabAddRec_'.$lTableID.'" ').'&nbsp;'
                .strLinkAdd_UFMultiRecEntry($uTable->enumTType, $lTableID, $lFID, false, 'Add record', ' id="mptabAddRec1_'.$lTableID.'" ').'&nbsp;';

      $strOut .= $this->strEndCollapsibleHeading();

      return($strOut);
   }

   private function strBeginCollapsibleHeading($lTableID, $strSafeTableName, $bSingleEntry){
      $strOut = '';
      $attributes = new stdClass;
      $attributes->lTableWidth  = null;
      $attributes->lMarginLeft  = 15;
      $attributes->divID        = 'ut_'.$lTableID.'Div';
      $attributes->divImageID   = 'ut_'.$lTableID.'ImgDiv';
      $strOut .= strOpenBlock($strSafeTableName.' <i>('.($bSingleEntry ? 'Single ' :'Multi-').'entry)</i>', '', $attributes);
      
      return($strOut);
   }

   private function strEndCollapsibleHeading(){
      $strOut = '';
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      $strOut .= strCloseBlock($attributes).'<br>';
      return($strOut);
   }

   function strDisplayUserTableSingle(&$clsUserTable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat, $gbDateFormatUS;
      $clsACO = new madmin_aco();

      $strOut = '';

      $lTableID = $clsUserTable->lKeyID;

      $bCollapsibleHeadings = $clsUserTable->bCollapsibleHeadings;
      $bCollapseDefaultHide = $clsUserTable->bCollapseDefaultHide;
      $strSafeTableName = htmlspecialchars($clsUserTable->strUserTableName);
      $strOut .= $this->strBeginCollapsibleHeading($lTableID, $strSafeTableName, true);

      $this->loadSingleDataRecord($lTableID, $this->lForeignID, $recInfo);
      $lRecID = $recInfo->lRecID;

      $enumTType = $clsUserTable->enumTType;

      $strOut .=
        '<fieldset class="enpFS" style="width: '.$this->lFieldSetWidth.'pt; align: left;">
              <legend class="enpLegend">
                 <b><i>'.$strSafeTableName.'</b></i> '
                .strLinkView_UFMFRecordViaRecID($lTableID, $this->lForeignID, $lRecID,
                               'View record', true, ' id="viewSingle_'.$lTableID.'" ').'&nbsp;&nbsp;'."\n"
                .strLinkEdit_UFMultiRecEntry($enumTType, $lTableID, $this->lForeignID, $lRecID,
                               true, 'Edit table entries', ' id="editSingle_'.$lTableID.'" ').'&nbsp;&nbsp;'
                .($recInfo->bRecordEntered ? '' : '(not written) ').'

              </legend>';

      $strOut .= '<table class="enpRpt">';
      for ($idx=0; $idx < $this->lNumFields; ++$idx){
         $uf = &$this->fields[$idx];
         $enumType  = $uf->enumFieldType;

         $userValue = $uf->userValue;
         $lFieldID  = $uf->pff_lKeyID;
         if ($enumType == CS_FT_HEADING){
            $strOut .= '
                  <tr>
                     <td colspan="2" class="enpRptLabel">'
                        .htmlspecialchars($this->fields[$idx]->pff_strFieldNameUser).'
                     </td>
                  </tr>';
         }else {
            $strOut .= '
                  <tr>
                     <td width="'.$this->lLabelWidth.'" class="enpRptLabel">'
                        .htmlspecialchars($this->fields[$idx]->pff_strFieldNameUser).'
                     </td>
                     <td class="enpRpt" width="'.$this->lFieldValWidth.'" valign="center">'."\n";

            if (is_null($userValue) && !(($enumType==CS_FT_LOG) || ($enumType==CS_FT_DDLMULTI))){
               $strOut .= '&nbsp';
            }else {
               switch ($enumType){

                  case CS_FT_CHECKBOX:
                     $strOut .= ((boolean)$userValue ? 'Yes' : 'No');
                     break;
                  case CS_FT_DATE    :
                     $strOut .= strNumericDateViaMysqlDate($userValue, $gbDateFormatUS);
                     break;
//                  case CS_FT_DATETIME:
//                     echoT(strNumericDateViaMysqlDate($userValue, $gbDateFormatUS)   date($genumDateFormat.' H:i:s', $userValue));
//                     break;
                  case CS_FT_TEXTLONG:
                     $strOut .= nl2br(htmlspecialchars($userValue)).'&nbsp;';
                     break;
                  case CS_FT_TEXT255:
                  case CS_FT_TEXT80:
                  case CS_FT_TEXT20:
                     $strOut .= htmlspecialchars($userValue).'&nbsp;';
                     break;
                  case CS_FT_INTEGER :
                     $strOut .= number_format($userValue);
                     break;
                  case CS_FT_CURRENCY:
                     $clsACO->loadCountries(false, false, true, $this->fields[$idx]->pff_lCurrencyACO);
                     $strOut .=
                         $clsACO->countries[0]->strCurrencySymbol.' '
                        .number_format($userValue, 2).'&nbsp;'
                        .$clsACO->countries[0]->strFlagImg;
                     break;
                  case CS_FT_DDL:
                     $strOut .= htmlspecialchars($this->strDDLValue($userValue));
                     break;
                  case CS_FT_DDL:
                     $strOut .= htmlspecialchars($this->strDDLValue($userValue));
                     break;
                  case CS_FT_LOG:
                     $strOut .= $this->logDisplay($enumTType, $lFieldID, $lTableID, $this->lForeignID, 5);
                     break;
                  case CS_FT_DDLMULTI:
                     $strDDLMultiFN = $uf->strFieldNameInternal.'_ddlMulti';
                     $this->loadMultiDDLSelects($lTableID, $lFieldID, $lRecID, $uf->$strDDLMultiFN);
                     $strOut .= $this->strMultiDDLUL($uf->$strDDLMultiFN);
                     break;
                  default:
                     screamForHelp($enumType.': invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                     break;
               }
            }
         }
      }
      $strOut .= '
            </table>'."\n";

      $strOut .= '
         </fieldset><br>'."\n";
//      if ($bCollapsibleHeadings)
      $strOut .= $this->strEndCollapsibleHeading();
      return($strOut);
   }

   function logDisplay($enumTType, $lFieldID, $lTableID, $lForeignID, $lNumLoad){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat, $gstrSeed;

      $strOut = '';

      $clsUsers = new muser_accts;

      $this->lNumTotLogEntries = $this->lCntLogEntries($lFieldID, $lForeignID);
      $this->lNumLogEntriesDisplayed = min($this->lNumTotLogEntries, $lNumLoad);

      $strTableName = $this->strGenUF_Log_TableName($lTableID);

      $strOut .= strLinkAdd_UFLogEntry($enumTType, $lFieldID, $lTableID,    $lForeignID,
                                       true, 'Add new log entry', '')
            ." Displaying $this->lNumLogEntriesDisplayed of $this->lNumTotLogEntries log entries";

      if ($this->lNumLogEntriesDisplayed > 0){
            //------------------------
            // more to display?
            //------------------------
         if ($this->bShowViewAllLog){
            $strOut .= ' '
                .strLinkView_UFAllLogEntries($lTableID, $lFieldID, $lForeignID, 'View all', true).'&nbsp;'
                .strLinkView_UFAllLogEntries($lTableID, $lFieldID, $lForeignID, 'View all', false, 'valign="top";');
         }
         $strOut .= '<br><br>';

         $this->loadLogEntries(
                        $lFieldID,  $lForeignID,
                        $this->lNumLogEntriesDisplayed, $this->lMaxLogDisplayLen);

         foreach ($this->logEntries as $logEntry){
            $lLogEntryID = $logEntry->lKeyID;

            if ($this->bShowLogEditLinks){
               $strOut .= strLinkEdit_UFLogEntry(
                                   $enumTType,    $lTableID,    $lFieldID,
                                   $lForeignID,   $lLogEntryID, 'Edit log entry',
                                   true,       '').'&nbsp;&nbsp;'."\n"

                 .strLinkRem_UFLogEntry(
                           $enumTType,  $lTableID,    $lFieldID,
                           $lForeignID, $lLogEntryID, 'remove log entry',
                           true,        true);
//                           ' onClick="return(confirm(\'Are you sure you want to remove this log entry? '
//                           .'The action can not be undone.\'));" ');
            }

            $strOut .= '<table class="ufLogs"><tr><td>';

            if ($logEntry->strTitle.'' != ''){
               $strOut .= '<b>'.htmlspecialchars($logEntry->strTitle).'</b> ';
            }
            $strOut .= '('.date($genumDateFormat, $logEntry->dteOrigin).')<br>';
            $strElipsis = $logEntry->lEntryLen > $this->lMaxLogDisplayLen ? '....' : '';
            $strOut .= nl2br(htmlspecialchars($logEntry->strLogEntry)).$strElipsis.'<br><br>';

            $strOut .= '<small><u>logged by '.$clsUsers->strSafeUserNameViaID($logEntry->lOriginID).'
                   on '.date($genumDateFormat.' H:i:s', $logEntry->dteOrigin).'</u><br></small>';
            $strOut .= '<small><u>last updated by '.$clsUsers->strSafeUserNameViaID($logEntry->lLastUpdateID).'
                   on '.date($genumDateFormat.' H:i:s', $logEntry->dteLastUpdate).'</u><br></small>';

            $strOut .= '</td></tr></table><br>';
         }
      }
      return($strOut);
   }



       /* -------------------------------------------------------------------------
                     M u l t i  -  R e c   T a b l e s
        ------------------------------------------------------------------------- */
   function lNumMultiRecsViaFID(&$uTable, $lFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT COUNT(*) AS lNumRecs
         FROM '.$uTable->strDataTableName.'
         WHERE '.$uTable->strFieldPrefix.'_lForeignKey='.(int)$lFID.'
            AND NOT '.$uTable->strFieldPrefix.'_bRetired;';

      $query = $this->db->query($sqlStr);

      if ($query->num_rows() == 0){
         return(0);
      }else {
         $row = $query->row();
         return($row->lNumRecs);
      }
   }

   function lSaveMultiRecViaPost($bNew, $lFID, &$lRecID, &$utable){
   //---------------------------------------------------------------------
   // note: with the new personalized table gui (1/2014), single record
   // personalized table records go thru here also.
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      $bMultiEntry = $utable->bMultiEntry;

         // special case for client program tables: add additional default fields
      if ($utable->bCProg){
         $strFP = $utable->strFieldPrefix;
         if ($utable->bEnrollment){
            $strEStart = trim($_POST['txtEStart']);
            MDY_ViaUserForm($strEStart, $lMon, $lDay, $lYear, $gbDateFormatUS);
            $strEStartMysql = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

            $strEEnd = trim($_POST['txtEEnd']);
            if ($strEEnd == ''){
               $strEEndMysql = 'NULL';
            }else {
               MDY_ViaUserForm($strEEnd, $lMon, $lDay, $lYear, $gbDateFormatUS);
               $strEEndMysql = strPrepStr(strMoDaYr2MySQLDate($lMon, $lDay, $lYear));
            }

            $strCProgFields =
               $strFP.'_dteStart = '.strPrepStr($strEStartMysql).", \n"
              .$strFP.'_dteEnd   = '.$strEEndMysql.", \n"
              .$strFP.'_bCurrentlyEnrolled = '.(@$_POST['chkEnrolled']=='true' ? '1' : '0')."\n";
         }else {
            $strAttend = trim($_POST['txtADate']);
            MDY_ViaUserForm($strAttend, $lMon, $lDay, $lYear, $gbDateFormatUS);
            $strADateMysql = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

            if (isset($_POST['ddlDuration'])){
               $strDuration = (float)trim($_POST['ddlDuration']);
            }else {
               $strDuration = '0';
            }
            $strCProgFields =
               $strFP.'_dteAttendance = '.strPrepStr($strADateMysql).", \n"
              .$strFP.'_strCaseNotes  = '.strPrepStr(trim($_POST['txtCaseNotes'])).", \n"
              .$strFP.'_dDuration     = '.$strDuration." \n";
            if ($bNew){
               $strCProgFields .= ', '.$strFP.'_lEnrollID     = '.$utable->lEnrollRecID."\n";
            }
         }
      }else {
         $strCProgFields = '';
      }

      $this->lForeignID = $lFID;

      $this->lTableID = $lTableID = $utable->lKeyID;
      $this->userTables[0] = &$utable;
      $idx = 0;
      if (isset($utable->ufields)){   // special case for CProg tables w/only base fields
         foreach ($utable->ufields as $ufield){

            $this->fields[$idx] = $vfield = &$ufield;

            $enumType = $ufield->enumFieldType;
            $strFN = $ufield->strFieldNameInternal;
            $vfield->varUserVal = null;

            switch ($enumType){
               case CS_FT_CHECKBOX:
                  $vfield->varUserVal = @$_POST[$strFN]=='true';
                  break;
               case CS_FT_DATE:
                  $vfield->varUserVal = trim($_POST[$strFN]);
                  if ($vfield->varUserVal==''){
                     $vfield->varUserVal = ' null ';
                  }else {
                     MDY_ViaUserForm($vfield->varUserVal, $lMon, $lDay, $lYear, $gbDateFormatUS);
                     $vfield->varUserVal = ' "'.strMoDaYr2MySQLDate($lMon, $lDay, $lYear).'" ';
                  }
                  break;
               case CS_FT_TEXTLONG:
               case CS_FT_TEXT255:
               case CS_FT_TEXT80:
               case CS_FT_TEXT20:
                  $vfield->varUserVal = trim($_POST[$strFN]);
                  break;
               case CS_FT_CLIENTID :
               case CS_FT_INTEGER :
                  $vfield->varUserVal = trim($_POST[$strFN]);
                  $this->stripCommas(trim($vfield->varUserVal));
                  $vfield->varUserVal = (integer)$vfield->varUserVal;
                  break;
               case CS_FT_HEADING:
                  break;
               case CS_FT_CURRENCY:
                  $vfield->varUserVal = trim($_POST[$strFN]);
                  $this->stripCommas($vfield->varUserVal);
                  $vfield->varUserVal = number_format((float)$vfield->varUserVal, 2, '.', '');
                  break;
               case CS_FT_DDL:
                  $vfield->varUserVal = (integer)$_POST[$strFN];
                  if ($vfield->varUserVal <= 0) $vfield->varUserVal = ' null ';
                  break;
               case CS_FT_DDLMULTI:
               case CS_FT_LOG:
                  break;
               default:
                  screamForHelp($enumType.': invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
            ++$idx;
         }
      }

      if ($bNew){
         $lRecID = $this->insertMRRecord($lFID, $strCProgFields, $bMultiEntry);
      }else {
         $this->updateMRRecord($lRecID, $strCProgFields, $bMultiEntry);
      }
      return($lRecID);
   }

   private function stripCommas(&$strValue){
      $strValue = str_replace (',', '', $strValue);
      return(true);
   }

   private function strCombineSelects($select1, $select2){
      $sqlCommon = $select1;
      if ($sqlCommon == ''){
         $sqlCommon = $select2;
      }else {
         if ($select2 != '') $sqlCommon .= ", $select2 ";
      }
      return($sqlCommon);
   }

   function insertMRRecord($lFID, $strCProgFields, $bMulti){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      
      $utable = &$this->userTables[0];
      $lTableID    = $utable->lKeyID;
      $strTable    = $utable->strDataTableName;
      $strFNPrefix = $utable->strFieldPrefix;

      $strMulti =
           "
            $strFNPrefix"."_bRecordEntered = 1,
            $strFNPrefix"."_bRetired       = 0,
            $strFNPrefix"."_lLastUpdateID  = $glUserID,
            $strFNPrefix"."_lOriginID      = $glUserID,
            $strFNPrefix"."_dteOrigin      = NOW() ";

      $sqlCommon = $this->sqlCommonMRAddEdit($lTableID, $lNumDDLMulti, $multiDDL);

      $sqlCommon = $this->strCombineSelects($sqlCommon, $strCProgFields);
      $sqlCommon = $this->strCombineSelects($sqlCommon, $strMulti);
      $sqlCommon = $this->strCombineSelects($sqlCommon, " $strFNPrefix"."_lForeignKey = $lFID ");

      $sqlStr =
           "INSERT INTO $strTable
            SET $sqlCommon ;";

      $query = $this->db->query($sqlStr);
      $lRecID = $this->db->insert_id();
      
      if ($lNumDDLMulti > 0){
         foreach ($multiDDL as $md){
            $this->addDDLMultiEntries($lRecID, $md);
         }
      }
      return($lRecID);
   }

   function updateMRRecord($lRecID, $strCProgFields, $bMulti){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $utable      = &$this->userTables[0];
      $lTableID    = $utable->lKeyID;
      $strTable    = $utable->strDataTableName;
      $strFNPrefix = $utable->strFieldPrefix;

      $this->markSingleEntryRecRecordedViaRecID($this->lTableID, $lRecID);

      $strMulti = " $strFNPrefix"."_lLastUpdateID=$glUserID ";

      $sqlCommon = $this->sqlCommonMRAddEdit($lTableID, $lNumDDLMulti, $multiDDL);
      $sqlCommon = $this->strCombineSelects($sqlCommon, $strCProgFields);
      $sqlCommon = $this->strCombineSelects($sqlCommon, $strMulti);
      $sqlStr =
           "UPDATE $strTable
            SET
               $sqlCommon
            WHERE $strFNPrefix"."_lKeyID = $lRecID;";

      $query = $this->db->query($sqlStr);
      if (!$bMulti){
         $this->markSingleEntryRecRecordedViaRecID($lTableID, $lRecID);
      }

      if ($lNumDDLMulti > 0){
         foreach ($multiDDL as $md){
            $this->addDDLMultiEntries($lRecID, $md);
         }
      }
   }

   private function sqlCommonMRAddEdit($lTableID, &$lNumDDLMulti, &$ddlMulti){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $lNumDDLMulti = 0; $ddlMulti = array();

      $strOut = '';
      if ($this->userTables[0]->lNumFields == 0) return('');

      foreach ($this->userTables[0]->ufields as $ufield){
         $strFN      = $ufield->strFieldNameInternal;
         $varUserVal = $ufield->varUserVal;
         $enumFType  = $ufield->enumFieldType;

         if (!($enumFType==CS_FT_HEADING || $enumFType==CS_FT_LOG)){

            switch ($enumFType){

               case CS_FT_CHECKBOX:
                  $strSet = $strFN.' = '.($varUserVal ? '1' : '0').' ';
                  break;
               case CS_FT_DATE    :
                  $strSet = $strFN.' = '.$varUserVal.' ';
                  break;
               case CS_FT_DATETIME:
                  break;
               case CS_FT_TEXTLONG:
               case CS_FT_TEXT255:
               case CS_FT_TEXT80:
               case CS_FT_TEXT20:
                  $strSet = $strFN.' = '.strPrepStr($varUserVal).' ';
                  break;
               case CS_FT_CLIENTID :
               case CS_FT_INTEGER :
                  $strSet = $strFN.' = '.$varUserVal.' ';
                  break;
               case CS_FT_CURRENCY:
                  $strSet = $strFN.' = '.$varUserVal.' ';
                  break;
               case CS_FT_DDL:
                  $strSet = $strFN.' = '.$varUserVal.' ';
                  break;
               case CS_FT_DDLMULTI:
                  $strSet = '';
                  $ddlMulti[$lNumDDLMulti] = new stdClass;
                  $mddl = &$ddlMulti[$lNumDDLMulti];
                  $mddl->lNumEntries          = 0;
                  $mddl->lTableID             = $lTableID;
                  
                     // different paths that get here have a different variable for the field ID
                  if (isset($ufield->pff_lKeyID)){
                     $mddl->lFieldID             = $ufield->pff_lKeyID;
                  }else {
                     $mddl->lFieldID             = $ufield->lFieldID;
                  }
                  $mddl->strFieldNameInternal = $ufield->strFieldNameInternal;
                  
                  if (isset($_POST[$strFN])){
                     $mddl->entries = array();
                     foreach ($_POST[$strFN] as $lEntryID){
                        $mddl->entries[] = $lEntryID;
                        ++$mddl->lNumEntries;
                     }
                  }
                  ++$lNumDDLMulti;
                  break;
               default:
                  screamForHelp($enumFType.': invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
            if ($strSet != '') $strOut .= $strSet.",\n";
         }
      }
      if ($strOut != '') $strOut = substr($strOut, 0, strlen($strOut)-2);
      return($strOut);
   }

   function loadMRRecsViaFID($lFID, $lEnrollRecID=null){
   //---------------------------------------------------------------------
   // if $lEnrollRecID is not null, then the personalized table is
   // a Client Program Attendance table, and the lookup must
   // be qualified by the enrollment record ID
   //---------------------------------------------------------------------
      $utable = &$this->userTables[0];
      $strFNPrefix = $utable->strFieldPrefix;
      $this->mrSQLWhereExtra = " AND $strFNPrefix"."_lForeignKey=$lFID ";
      if (!is_null($lEnrollRecID)){
         $this->mrSQLWhereExtra .= " AND $strFNPrefix"."_lEnrollID=$lEnrollRecID ";

      }
      $this->loadMRRecs($utable->bCProg, $utable->bEnrollment);
   }

   function loadMRRecsViaRID($lRID){
   //---------------------------------------------------------------------
   // load single record
   //---------------------------------------------------------------------
      $utable = &$this->userTables[0];
      $strFNPrefix = $utable->strFieldPrefix;
      $this->mrSQLWhereExtra = " AND $strFNPrefix"."_lKeyID=$lRID ";
      $this->loadMRRecs();
   }

   function removeMRRecord($lRecID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $utable = &$this->userTables[0];
      $strFNPrefix = $utable->strFieldPrefix;
      $sqlStr =
            "UPDATE $utable->strDataTableName
             SET
                $strFNPrefix"."_bRetired=1,
                $strFNPrefix"."_lLastUpdateID=$glUserID
             WHERE $strFNPrefix"."_lKeyID=$lRecID;";
      $query = $this->db->query($sqlStr);
   }

   function loadMRRecs($bCProg = false, $bEnrollment = false){
   //---------------------------------------------------------------------
   //  sets the following:
   //    $this->lNumMRRecs  - number of loaded records
   //    $this->mrRecs      - array of data records
   //---------------------------------------------------------------------
      $utable = &$this->userTables[0];
      $lTableID = $utable->lKeyID;
      $bMultiEntry = $utable->bMultiEntry;

      $strFNPrefix = $utable->strFieldPrefix;
      $strFNKeyID  = $strFNPrefix.'_lKeyID';
      $strFNFID    = $strFNPrefix.'_lForeignKey';
      if ($this->mrSQLOrder==''){
         if ($bCProg){
            if ($bEnrollment){
               $strOrder = 'ORDER BY '.$strFNPrefix.'_dteStart, '.$strFNKeyID.' ';
            }else {
               $strOrder = 'ORDER BY '.$strFNPrefix.'_dteAttendance, '.$strFNKeyID.' ';
            }
         }else {
            $strOrder = 'ORDER BY '.$strFNKeyID.' DESC';
         }
      }else {
         $strOrder = 'ORDER BY '.$this->mrSQLOrder;
      }

      $strSelectExtra = '';
      if ($bCProg){
          $strSelectExtra .= ', ';
         if ($bEnrollment){
             $strSelectExtra .=
                      $strFNPrefix.'_dteStart                   AS dteMysqlStart,
                    '.$strFNPrefix.'_dteEnd                     AS dteMysqlEnd,
                    '.$strFNPrefix.'_bCurrentlyEnrolled         AS bCurrentlyEnrolled ';
         }else {
             $strSelectExtra .=
                      $strFNPrefix.'_dteAttendance                   AS mdteAttendance,
                    '.$strFNPrefix.'_lEnrollID                       AS lEnrollRecordID,
                    '.$strFNPrefix.'_dDuration                       AS dDuration,
                    '.$strFNPrefix.'_strCaseNotes                    AS strCaseNotes ';
         }
      }

         // join the ddl values
      $strFields = $strJoinFields = $strJoin = '';

      if ($utable->lNumFields > 0){
         foreach ($utable->ufields as $ufield){
            $enumType = $ufield->enumFieldType;
            if (!($enumType==CS_FT_LOG || $enumType==CS_FT_HEADING)){
               $strFN = $ufield->strFieldNameInternal;
               if ($enumType==CS_FT_DDL){
                  $strASTable = 'uf_ddl_'.$strFN;
                  $strASField = 'strDDL'.$strFN;
                  $strJoin .= "LEFT JOIN uf_ddl AS $strASTable ON $strFN= $strASTable.ufddl_lKeyID \n";
                  $strJoinFields .= ", $strASTable.ufddl_strDDLEntry AS $strASField ";
               }
               $strFields .= ", $strFN \n";
            }
         }
      }
      $strFields = $strFNPrefix.'_lKeyID, '.$strFNPrefix.'_lForeignKey '.$strFields."\n".$strJoinFields;

      $strSelectExtra .= ', '
        .$strFNPrefix.'_lOriginID      AS lOriginID, '
        .$strFNPrefix.'_lLastUpdateID  AS lLastUpdateID, '
        .$strFNPrefix.'_bRecordEntered AS bRecordEntered,
         UNIX_TIMESTAMP('.$strFNPrefix.'_dteOrigin)     AS dteOrigin,
         UNIX_TIMESTAMP('.$strFNPrefix.'_dteLastUpdate) AS dteLastUpdate,
         uc.us_strFirstName  AS strUCFName,  uc.us_strLastName  AS strUCLName,
         ul.us_strFirstName  AS strULFName,  ul.us_strLastName  AS strULLName ';
      $strJoin .= '
         LEFT JOIN admin_users   AS uc  ON uc.us_lKeyID  = '.$strFNPrefix.'_lOriginID
         LEFT JOIN admin_users   AS ul  ON ul.us_lKeyID  = '.$strFNPrefix.'_lLastUpdateID '."\n";

      if ($bMultiEntry){
         $strCheckRetired = ' AND NOT '.$strFNPrefix.'_bRetired ';
      }else {
         $strCheckRetired = '';
      }

      $sqlStr =
          "SELECT $strFields $this->mrSQLSelect $strSelectExtra
           FROM $utable->strDataTableName
              $strJoin
           WHERE 1 $strCheckRetired $this->mrSQLWhereExtra
           $strOrder;";

      $query = $this->db->query($sqlStr);
      $this->lNumMRRecs = $numRows = $query->num_rows();
      $this->mrRecs = array();
      if ($numRows==0) {
         $this->mrRecs[0] = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->mrRecs[$idx] = new stdClass;
            $mrRec = &$this->mrRecs[$idx];
            $mrRec->$strFNKeyID = $lDataRecID = $row->$strFNKeyID;
            $mrRec->$strFNFID   = $row->$strFNFID;

            if ($bCProg){
               if ($bEnrollment){
                  $mrRec->dteStart           = dteMySQLDate2Unix($row->dteMysqlStart);
                  $mrRec->dteMysqlEnd        = $row->dteMysqlEnd;
                  $mrRec->dteEnd             = dteMySQLDate2Unix($row->dteMysqlEnd);
                  $mrRec->bCurrentlyEnrolled = (boolean)$row->bCurrentlyEnrolled;
               }else {
                  $mrRec->dteAttendance      = dteMySQLDate2Unix($row->mdteAttendance);
                  $mrRec->lEnrollRecordID    = (int)$row->lEnrollRecordID;
                  $mrRec->dDuration          = (float)$row->dDuration;
                  $mrRec->strCaseNotes       = $row->strCaseNotes;
               }
            }

            $mrRec->bRecordEntered = (bool)$row->bRecordEntered;
            $mrRec->lOriginID      = $row->lOriginID;
            $mrRec->lLastUpdateID  = $row->lLastUpdateID;
            $mrRec->dteOrigin      = $row->dteOrigin;
            $mrRec->dteLastUpdate  = $row->dteLastUpdate;
            $mrRec->strUCFName     = $row->strUCFName;
            $mrRec->strUCLName     = $row->strUCLName;
            $mrRec->strULFName     = $row->strULFName;
            $mrRec->strULLName     = $row->strULLName;
            if ($bMultiEntry){
            }

            if ($utable->lNumFields > 0){
               foreach ($utable->ufields as $ufield){
                  $lFieldID = $ufield->pff_lKeyID;

                  $enumType = $ufield->enumFieldType;
                  if (!($enumType==CS_FT_LOG || $enumType==CS_FT_HEADING)){
                     $strFN = $ufield->strFieldNameInternal;
                     $mrRec->$strFN = $row->$strFN;
                  }

                     // load the ddl text entry
                  if ($enumType==CS_FT_DDL){
                     $strDDLTextFN = $strFN.'_ddlText';
                     $strDBFN = 'strDDL'.$strFN;
                     $mrRec->$strDDLTextFN = $row->$strDBFN.'';
                  }

                     // load the multi-ddl text entry
                  if ($enumType==CS_FT_DDLMULTI){
                     $strDDLMultiFN = $strFN.'_ddlMulti';
                     $this->loadMultiDDLSelects($lTableID, $lFieldID, $lDataRecID, $mrRec->$strDDLMultiFN);
                  }
               }
            }
            ++$idx;
         }
      }
   }


       /* -------------------------------------------------------------------------
                     D a t a   E n t r y
        ------------------------------------------------------------------------- */
   function bUFDataEntered($lTableID, $bMultiEntry, $lFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
   // UPDATE  `uf_000001` SET  `uf000001_bRecordEntered` =1 WHERE  `uf000001_000001` IS NOT NULL
      $strUTable   = $this->strGenUF_TableName($lTableID);
      $strFNPrefix = $this->strGenUF_KeyFieldPrefix($lTableID);
      $strFIDFN    = $this->strGenUF_ForeignIDFN($lTableID);

      if ($bMultiEntry){
         $sqlStr =
           "SELECT COUNT(*) AS lNumRecs
            FROM $strUTable
            WHERE $strFIDFN=$lFID
               AND NOT $strFNPrefix".'_bRetired;';
         $query = $this->db->query($sqlStr);
         if ($query->num_rows() == 0){
            return(false);
         }else {
            $row = $query->row();
            return($row->lNumRecs > 0);
         }
      }else {
         $sqlStr =
           "SELECT $strFNPrefix"."_bRecordEntered AS bDataEntered
            FROM $strUTable
            WHERE $strFIDFN=$lFID;";
         $query = $this->db->query($sqlStr);
         if ($query->num_rows() == 0){
            return(false);
         }else {
            $row = $query->row();
            return((boolean)$row->bDataEntered);
         }
      }
   }

   function lMostRecentRecID_ViaTID_FID($lTableID, $lFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strTabName = $this->strGenUF_TableName($lTableID);
      $strFNPrefix = $this->strGenUF_KeyFieldPrefix($lTableID);
      $strFN_FID   = $this->strGenUF_ForeignIDFN($lTableID);
      $strFN_KeyID = $strFNPrefix.'_lKeyID';

      $sqlStr =
         "SELECT MAX($strFN_KeyID) AS lMaxRecID
          FROM $strTabName
          WHERE $strFN_FID=$lFID
          AND NOT ".$strFNPrefix."_bRetired;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      $lKeyID = $row->lMaxRecID;

      if (is_null($lKeyID)){
         return(null);
      }else {
         return((int)$lKeyID);
      }


   }






}

?>