<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012-2016 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('admin/mimport', 'clsImport');

Sample usage:
         $this->load->model('admin/mimport', 'clsImport');
         $this->load->model('admin/madmin_aco', 'clsACO');
         $this->load->helper('dl_util/time_date');

         $this->load->model('admin/mimport', 'clsImport');
         $this->clsImport->initImportClass($enumImportType);
         $this->clsImport->initACOTable($this->clsACO);    // only needed if importing an aco field

         $this->clsImport->addImportField($strFieldName, $enumType, $lMaxLen, $varDefaultValue, $bRequired, $strDBFN)
         $this->clsImport->initImportClass($enumImportType);

         $this->clsImport->addImportField('Title',              'text',        80, '', false, 'pe_strTitle');
         $this->clsImport->addImportField('Last Name',          'text',        80, '', true,  'pe_strLName');
         ...

            // must set import fields (above) before setting the header fields
         $this->clsImport->setHeaderFields($this->csvreader->fields);
         if ($this->clsImport->bError){
            $this->session->set_flashdata('error', $this->clsImport->strErrMsg);
            redirect('whereever');
         }

         $this->clsImport->verifyCSV($userCSV);
         if ($this->clsImport->bError){
            $this->session->set_flashdata('error', $this->clsImport->strErrMsg);
            redirect('whereever');
         }


---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mimport extends CI_Model{
   public $importFields, $lNumFields, $enumContext, $headerFields,
          $strErrMsg, $bError, $enumGenders, $enumYesNo, $aco,
          $lImportLogID,
          $forceSet, $lErrCnt, $lMaxErrCnt,
          $strImportTable,
          $lNumLogEntries, $logEntries, $lNumImportDetails, $importDetails,
          $lNumFIDs, $foreignIDs,
          $clients, $cstatus, $cvoc, $csponprog;


   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->enumGenders  = array('male', 'female', 'unknown');
      $this->enumYesNo    = array('yes', 'no');
      $this->aco = null; $this->genericList = array();
      $this->lNumImportDetails = $this->lNumLogEntries = 0;
      $this->importDetails     = $this->logEntries     = null;

      $this->lNumFIDs = 0; $this->foreignIDs = null;
   }

   public function initImportClass($enumContext){
      $this->importFields    = array();
      $this->enumContext     = $enumContext;
      $this->lNumFields      = 0;
      $this->headerFields    = array();
      $this->strErrMsg       = '';
      $this->bError          = false;
      $this->lImportLogID    = null;
      $this->forceSet        = array();
      $this->strImportTable  = null;

      $this->lErrCnt    = 0;
      $this->lMaxErrCnt = 20;
      $this->clients   = new mclients;
      if ($enumContext==CENUM_CONTEXT_CLIENT){
         $this->cstatus   = new mclient_status;
         $this->cvoc      = new mclient_vocabulary;
         $this->csponprog = new msponsorship_programs;
      }
   }

   public function initACOTable(&$clsACO){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsACO->loadCountries(true, false, false, null);
      $this->aco = array();
      foreach ($clsACO->countries as $country){
         $this->aco[$country->lKeyID] = strtolower($country->strName);
      }
   }

   public function initGenericListTable($genListIDX, $enumListType, &$clsList, $lFieldID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->genericList[$genListIDX] = array();
      if ($enumListType == CENUM_LISTTYPE_USERTABLE){
         $clsList->loadListUTable($lFieldID, false, false);
      }else {
         $clsList->initializeListManager('generic', $enumListType);
         $clsList->loadList();
      }
      if ($clsList->lNumInList > 0){
         foreach ($clsList->listItems as $listEntry){
            $this->genericList[$genListIDX][$listEntry->lKeyID] = strtolower($listEntry->strListItem);
         }
      }
   }

   public function addImportField($strFieldName,     $enumType,  $lMaxLen,
                                  $varDefaultValue,  $bRequired, $strDBFN,
                                  $lGenListIDX=null, $lFieldID=-1){
   /*
    * supported types:
    *    'integer'
    *    'text'
    *    'YesNo'
    *    'mysqlDate'  - i.e. 'yyyy-mm-dd'
    *    'enumGender' - 'Male', 'Female', 'Unknown'
    *    'aco'        - text, corresponding to ACO table entry
    *    'genList'    - generic list; entry in the list table
    *    'currency'   - currency/floating point
    *    'Account'    - gift account
    *    'Campaign'   - gift campaign
    *
   */
      $this->importFields[$this->lNumFields] = new stdClass;
      $field = &$this->importFields[$this->lNumFields];
      $field->lFieldID      = $lFieldID;
      $field->fieldName     = strtolower($strFieldName);
      $field->userFieldName = $strFieldName;
      $field->type          = $enumType;
      $field->maxLen        = $lMaxLen;
      $field->defaultValue  = $varDefaultValue;
      $field->DBFieldName   = $strDBFN;
      $field->required      = $bRequired;
      $field->genListIDX    = $lGenListIDX;

      $field->colIDX        = null;         // will be set when parsing the header record

      if ($enumType=='aco'){
         if (is_null($this->aco)){
            screamForHelp('aco table not initialized<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         }
      }
      ++$this->lNumFields;
   }


   public function setHeaderFields($csvFields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($csvFields as $field){
         $this->headerFields[] = trim(strtolower($field));
      }

      foreach ($this->importFields as $field){
         $strFN = $field->fieldName;

         $lColIDX = array_search($strFN, $this->headerFields);
         if ($lColIDX===false){
            if ($field->required){
               $this->strErrMsg .= 'Required field "'.$field->userFieldName.'" was not found in your file!<br>';
               $this->bError    = true;
               ++$this->lErrCnt;
               if ($this->lErrCnt >= $this->lMaxErrCnt) return;
            }
         }else {
            $field->colIDX = $lColIDX;
         }
      }
   }

   public function verifyCSV(&$userCSV, $enumAttachType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $idx = 2;
      foreach($userCSV as $rec){
         $this->lClientStatCatID = -1;
         $this->bAcct   = $this->bCamp   = false;
         $this->strAcct = $this->strCamp = '';
         foreach ($this->importFields as $field){
            if (!is_null($field->colIDX)){
               $varUserValue = $rec[$field->fieldName];
               $this->verifyViaDataType($idx,               $varUserValue,         $field->type,
                                        $field->required,   $field->userFieldName, $field->maxLen,
                                        $field->genListIDX, $field->DBFieldName,   $enumAttachType);
//                  // special case - client initial status, based on the status category
//               if ($enumAttachType==CENUM_CONTEXT_CLIENT && $field->DBFieldName==cr_lStatusCatID){
//                  $this->lClientStatCatID = (int)$varUserValue;
//               }

               if ($this->lErrCnt >= $this->lMaxErrCnt) return;
            }
         }

         if ($this->bAcct || $this->bCamp){
            $this->verifyAcctCamp($idx);
            if ($this->lErrCnt >= $this->lMaxErrCnt) return;
         }
         ++$idx;
      }
   }

   private function verifyAcctCamp($lLineNum){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!($this->bAcct && $this->bCamp)){
         $this->strErrMsg .= strQuoteFix('Both <b>Account</b> and <b>Campaign</b> fields are required. (record '.$lLineNum.')<br>');
         $this->bError    = true;
         ++$this->lErrCnt;
      }else {
         if (is_null($this->lCampIDViaAcctCamp($this->strAcct, $this->strCamp))){
            $this->strErrMsg .= strQuoteFix('The <b>Account</b> / <b>Campaign</b> pair you specified ('
                                             .htmlspecialchars($this->strAcct.' / '.$this->strCamp).') is not valid '
                                             .'(record '.$lLineNum.')<br>');
            $this->bError    = true;
            ++$this->lErrCnt;
         }
      }
   }

   private function lCampIDViaAcctCamp($strAcct, $strCamp){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT gc_lKeyID
         FROM gifts_campaigns
            INNER JOIN gifts_accounts  ON gc_lAcctID=ga_lKeyID
         WHERE NOT gc_bRetired
            AND gc_strCampaign='.strPrepStr($strCamp).'
            AND ga_strAccount='.strPrepStr($strAcct).';';
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0) return(null);
      $row = $query->row();
      return($row->gc_lKeyID);
   }

   private function bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum){
      if ($bRequired && $strVal==''){
         $this->strErrMsg .= 'Required field "'.htmlspecialchars($strUserFN).'" was blank (record '.$lLineNum.')<br>';
         $this->bError    = true;
         ++$this->lErrCnt;
      }
      return(($this->lErrCnt >= $this->lMaxErrCnt));
   }

   function verifyViaDataType($lLineNum,    $varUserValue, $enumType,
                              $bRequired,   $strUserFN,    $lMaxLen,
                              $lGenListIDX, $strDBFN,      $enumAttachType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumType){
         case 'text':
            $strVal = trim($varUserValue.'');
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;

               // set max length to -1 for mysql 'text' fields
            if ($lMaxLen > 0){
               if (strlen($strVal) > $lMaxLen){
                  $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN)
                                     .'" exceeds the maximum length of '.$lMaxLen.' characters (record '.$lLineNum.')<br>');
                  $this->bError    = true;
                  ++$this->lErrCnt;
                  if ($this->lErrCnt >= $this->lMaxErrCnt) return;
               }
            }
            break;

         case 'YesNo':
            $strVal = strtolower(trim($varUserValue.''));
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
            if ($strVal != ''){
               if (!in_array($strVal, $this->enumYesNo)){
                  $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                                    .htmlspecialchars($strVal).'</b> is not a valid yes/no entry (record '.$lLineNum.')<br>');
                  $this->bError    = true;
                  ++$this->lErrCnt;
                  if ($this->lErrCnt >= $this->lMaxErrCnt) return;
               }
            }
            break;

         case 'enumGender':
            $strVal = strtolower(trim($varUserValue.''));
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
            if ($strVal != ''){
               if (!in_array($strVal, $this->enumGenders)){
                  $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                                    .htmlspecialchars($strVal).'</b> is not a valid gender entry (record '.$lLineNum.')<br>');
                  $this->bError    = true;
                  ++$this->lErrCnt;
                  if ($this->lErrCnt >= $this->lMaxErrCnt) return;
               }
            }
            break;

         case 'aco':
            $strVal = strtolower(trim($varUserValue.''));
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
            if ($strVal != ''){
               $lACOID = array_search($strVal, $this->aco);
               if ($lACOID === false){
                  $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                             .htmlspecialchars($strVal).'</b> is not a valid Acounting Country entry (record '.$lLineNum.')<br>');
                  $this->bError    = true;
                  ++$this->lErrCnt;
                  if ($this->lErrCnt >= $this->lMaxErrCnt) return;
               }
            }
            break;

         case 'genList':
            $strVal = strtolower(trim($varUserValue.''));
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
            if ($strVal != ''){
               $lGenListID = array_search($strVal, $this->genericList[$lGenListIDX]);
               if ($lGenListID === false){
                  $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                             .htmlspecialchars($strVal).'</b> is not a valid list entry (record '.$lLineNum.')<br>');
                  $this->bError    = true;
                  ++$this->lErrCnt;
                  if ($this->lErrCnt >= $this->lMaxErrCnt) return;
               }
            }
            break;

         case 'mgenList':
            $strList = explode("\n", $varUserValue);
            foreach ($strList as $strVal){
               $strVal = strtolower(trim($strVal.''));
               if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
               if ($strVal != ''){
                  $lGenListID = array_search($strVal, $this->genericList[$lGenListIDX]);
                  if ($lGenListID === false){
                     $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                                .htmlspecialchars($strVal).'</b> is not a valid list entry (record '.$lLineNum.')<br>');
                     $this->bError    = true;
                     ++$this->lErrCnt;
                     if ($this->lErrCnt >= $this->lMaxErrCnt) return;
                  }
               }
            }
            break;

         case 'mysqlDate':
            $strVal = trim($varUserValue.'');
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
            if ($strVal != ''){
               if (!bValidMySQLDate($strVal)){
                  $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                             .htmlspecialchars($strVal).'</b> is not a valid date entry. '
                             .'Please use the format <b>"yyyy-mm-dd"</b>'
                             .' (record '.$lLineNum.')<br>');
                  $this->bError = true;
                  ++$this->lErrCnt;
                  if ($this->lErrCnt >= $this->lMaxErrCnt) return;
               }
            }
            break;

         case 'date':
            $strVal = trim($varUserValue.'');
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
            if ($strVal != ''){
               if (strtotime($strVal) === false){
                  $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                             .htmlspecialchars($strVal).'</b> is not a valid date entry. '
                             .'Please use the format <b>"mm/dd/yyyy"</b>'
                             .' (record '.$lLineNum.')<br>');
                  $this->bError = true;
                  ++$this->lErrCnt;
                  if ($this->lErrCnt >= $this->lMaxErrCnt) return;
               }
            }
            break;

         case 'int':
         case 'currency':
            $strVal = trim($varUserValue.'');
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
            if ($strVal != ''){
               if (!is_numeric($strVal)){
                  $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                             .htmlspecialchars($strVal).'</b> is not a valid number entry. '
                             .' (record '.$lLineNum.')<br>');
                  $this->bError = true;
                  ++$this->lErrCnt;
                  if ($this->lErrCnt >= $this->lMaxErrCnt) return;
               }
            }
            break;

         case 'clientID':
            $strVal = trim($varUserValue.'');
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
            if ($strVal != ''){
               if (!is_numeric($strVal)){
                  $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                             .htmlspecialchars($strVal).'</b> is not a valid clientID entry. '
                             .' (record '.$lLineNum.')<br>');
                  $this->bError = true;
                  ++$this->lErrCnt;
                  if ($this->lErrCnt >= $this->lMaxErrCnt) return;
               }else {
                  if (!$this->clients->bValidClientID((int)$strVal)){
                     $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                                .htmlspecialchars($strVal).'</b> clientID not found. '
                                .' (record '.$lLineNum.')<br>');
                     $this->bError = true;
                     ++$this->lErrCnt;
                     if ($this->lErrCnt >= $this->lMaxErrCnt) return;
                  }
               }
            }
            break;

         case 'Account':
            $strVal = trim($varUserValue.'');
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
            $this->bAcct   = true;
            $this->strAcct = $strVal;
            break;

         case 'Campaign':
            $strVal = trim($varUserValue.'');
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
            $this->bCamp   = true;
            $this->strCamp = $strVal;
            break;

         case 'clientSpecial':
            $strVal = trim($varUserValue.'');
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
            $this->bVerifyClientSpecialField($strVal, $strDBFN, $strUserFN, $lLineNum);
            break;

         case 'email':
            $strVal = trim($varUserValue.'');
            if ($this->bRequiredErr($bRequired, $strVal, $strUserFN, $lLineNum)) return;
            if ($strVal != ''){
               if (!valid_email($strVal)){
                  $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                             .htmlspecialchars($strVal).'</b> is not a valid email address. '
                             .' (record '.$lLineNum.')<br>');
                  $this->bError = true;
                  ++$this->lErrCnt;
                  if ($this->lErrCnt >= $this->lMaxErrCnt) return;
               }
            }
            break;

            // ID type used in personalized tables - the foreign Key
         case 'ID':
            $strVal = trim($varUserValue.'');
            if (!$this->bVerifyPTableKey((integer)$strVal, $enumAttachType)){
               $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                          .htmlspecialchars($strVal).'</b> is not a valid ID. '
                          .' (record '.$lLineNum.')<br>');
               $this->bError = true;
            }
            break;

         default:
            screamForHelp($enumType.': invalid data type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

         //-----------------------------------------------------
         // foreign key check
         // It's the little things that make a house a home...
         //-----------------------------------------------------
      $bFKeyFail = false;
      switch ($strDBFN){
         case 'gi_lForeignID':
            $bFKeyFail = !$this->bVerifyForeignKey((integer)$strVal, 'people_names', 'pe_lKeyID', 'pe_bRetired');
            break;
      }
      if ($bFKeyFail){
         $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                    .htmlspecialchars($strVal).'</b> is not a valid ID. '
                    .' (record '.$lLineNum.')<br>');
         $this->bError = true;
      }
   }

   function getClientSpecialField($varUserValue, &$field){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($field->DBFieldName){
         case 'cr_lLocationID':
            return($this->clients->lClientLocIDViaName($varUserValue));
            break;
         case 'cr_lStatusCatID':
            return($this->lClientStatCatID = $this->cstatus->lStatusCatIDViaName($varUserValue));
            break;
         case 'cr_lVocID':
            return($this->cvoc->lVocIDViaName($varUserValue));
            break;
         case 'sponProg':
            $field->mListIDs = array();

            $strList = explode("\n", $varUserValue);
            foreach ($strList as $strVal){
               $strVal = strtolower(trim($strVal.''));
               if ($strVal != ''){
                  $field->mListIDs[] = $this->csponprog->lProgIDViaName($strVal);
               }
            }
//
//            if ($strVal=='') return;
//            $progs = explode("\n", $strVal);
//            foreach ($progs as $prog){
//               $prog = trim($prog);
//               if (is_null($this->csponprog->lProgIDViaName($prog))){
//                  $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
//                             .htmlspecialchars($prog).'</b> is not a valid sponsorship program. '
//                             .' (record '.$lLineNum.')<br>');
//                  $this->bError = true;
//               }
//            }
            break;
         case 'initialStatus':
            return($this->cstatus->lStatusCatEntryIDViaName($this->lClientStatCatID, $varUserValue));
            break;

         default:
            screamForHelp($field->DBFieldName.': invalid client data type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

   }

   private function bVerifyClientSpecialField($strVal, $strDBFN, $strUserFN, $lLineNum){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($strVal=='') return;
      switch ($strDBFN){
         case 'cr_lLocationID':
            if (is_null($this->clients->lClientLocIDViaName($strVal))){
               $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                          .htmlspecialchars($strVal).'</b> is not a valid client location. '
                          .' (record '.$lLineNum.')<br>');
               $this->bError = true;
            }
            break;
         case 'cr_lStatusCatID':
            if (is_null($this->lClientStatCatID = $this->cstatus->lStatusCatIDViaName($strVal))){
               $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                          .htmlspecialchars($strVal).'</b> is not a valid client status category. '
                          .' (record '.$lLineNum.')<br>');
               $this->bError = true;
            }
            break;
         case 'cr_lVocID':
            if (is_null($this->cvoc->lVocIDViaName($strVal))){
               $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                          .htmlspecialchars($strVal).'</b> is not a valid client vocabulary. '
                          .' (record '.$lLineNum.')<br>');
               $this->bError = true;
            }
            break;
         case 'sponProg':
            if ($strVal=='') return;
            $progs = explode("\n", $strVal);
            foreach ($progs as $prog){
               $prog = trim($prog);
               if (is_null($this->csponprog->lProgIDViaName($prog))){
                  $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                             .htmlspecialchars($prog).'</b> is not a valid sponsorship program. '
                             .' (record '.$lLineNum.')<br>');
                  $this->bError = true;
               }
            }
            break;
         case 'initialStatus':
            if ($this->lClientStatCatID==-1){
               $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                          .htmlspecialchars($prog).'</b> column must appear after column <b>Status Category</b>. '
                          .' (record '.$lLineNum.')<br>');
               $this->bError = true;
               return;
            }elseif (is_null($this->lClientStatCatID)){
               $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                          .htmlspecialchars($prog).'</b> not valid if <b>Status Category</b> is not set. '
                          .' (record '.$lLineNum.')<br>');
               $this->bError = true;
               return;
            }
            if (is_null($this->cstatus->lStatusCatEntryIDViaName($this->lClientStatCatID, $strVal))){
               $this->strErrMsg .= strQuoteFix('Field "'.htmlspecialchars($strUserFN).'": <b>'
                          .htmlspecialchars($strVal).'</b> is not a valid client status. '
                          .' (record '.$lLineNum.')<br>');
               $this->bError = true;
            }
            break;

         default:
            screamForHelp($strDBFN.': invalid client data type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }




   public function lInsertImport(&$userCSV, $lGroupIDs, $utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gdteNow;

      $bClientImport = $this->strImportTable == 'client_records';

      if (is_null($utable)){
         $bMultiTable = true;
      }else {
         $bMultiTable = $utable->bMultiEntry;
         $lUTableID = $utable->lTableID;
      }

      $bAddToGroup = count($lGroupIDs) > 0;
      if ($bAddToGroup){
         $cgroup = new mgroups;
         $cgroup->enumSubGroup = null;
      }

      $this->lImportLogID = $lLogID = $this->lAddImportLogEntry($this->enumContext, $utable);

      $sqlBase = "INSERT INTO $this->strImportTable
                  SET
                  ";

         // perpare for potential volunteer import
      if ($this->enumContext==CENUM_CONTEXT_PEOPLE){
         $cvol = new mvol;
         $cvol->volRecs = array();
         $cvol->volRecs[0] = new stdClass;
         $cvol->volRecs[0]->Notes = 'Included in people import / logID: '.$lLogID;
      }

      $this->bGIK = false;
      foreach($userCSV as $rec){
         $sqlStr         = '';
         $this->bGIK     = false;
         $bAddVolRec     = false;
         $bAnyMultiLists = false;
         $lForeignID     = null;

         $this->lClientStatCatID = -1;
         $lInitialClientStatusID = -1;

         foreach ($this->importFields as $field){
            $bMultiListClientSP = ($field->type=='clientSpecial') && ($field->DBFieldName == 'sponProg');
            $bClientInitStatus  = ($field->type=='clientSpecial') && ($field->DBFieldName == 'initialStatus');
            $bMultiList = $field->type=='mgenList';
            $bAnyMultiLists = $bAnyMultiLists || $bMultiList;
            $strDBField = $field->DBFieldName;

            if (is_null($field->colIDX)){
               $varUserValue = null;
            }else {
               $varUserValue = $rec[$field->fieldName];
            }

            $bPeopleVolField = $strDBField == 'isVolunteer';
            if ($bPeopleVolField){
               $bAddVolRec = strtolower($varUserValue.'')=='yes';
            }else {
                  // a null field name means that this field is not to be inserted
               if (!(is_null($strDBField) || $bClientInitStatus)){
                  $strFieldValue = $this->strPrepInputVar(
                                         $field, $varUserValue, $strDBField, $rec, $lForeignID).", \n";
                  if ($field->type == 'ID') $lFKeyID = (int)$varUserValue;
                  if (!($bMultiList || $bMultiListClientSP)) $sqlStr .= $strDBField.' = '.$strFieldValue;
               }else {
                  if ($bClientInitStatus){
                     $lInitialClientStatusID =
                            $this->strPrepInputVar($field, $varUserValue, $strDBField, $rec, $lForeignID);
                  }
               }
            }
         }

            // special case - in-kind
         if ($this->bGIK){
            $sqlStr .= 'gi_bGIK = 1, '."\n";
         }

         $lNumExtra = count($this->forceSet);
         if ($lNumExtra > 0){
            foreach ($this->forceSet as $setF){
               $sqlStr .= $setF['fn'].' = '.$setF['value'].", \n";
            }
         }
         $sqlStr = substr($sqlStr, 0, -3).' ';  // remove trailing comma

         if ($bMultiTable){
            $this->db->query($sqlBase.$sqlStr.';');
            $lKeyID = $this->db->insert_id();
         }else {
            $lKeyID = $this->insertUpdateUTable($utable, $sqlStr, $lFKeyID);
         }

         if ($bClientImport){
               // client import - sponsorship programs for client
            foreach ($this->importFields as $field){
               if ($field->DBFieldName=='sponProg'){
                  if (count($field->mListIDs) > 0){
                     foreach($field->mListIDs as $lSCID){
                        $this->csponprog->setClientSponProgram($lKeyID, $lSCID);
                     }
                  }
               }

               if ($field->DBFieldName=='initialStatus'){
                  $this->cstatus->clientStatus = array();
                  $this->cstatus->clientStatus[0] = new stdClass;;
                  $this->cstatus->clientStatus[0]->lClientID = $lKeyID;
                  $this->cstatus->clientStatus[0]->lStatusID = $lInitialClientStatusID;
                  $this->cstatus->clientStatus[0]->bIncludeNotesInPacket = false;
                  $this->cstatus->clientStatus[0]->dteStatus = $gdteNow;
                  $this->cstatus->clientStatus[0]->strStatusTxt = 'Initial Status (via import)';
                  $this->cstatus->lInsertClientStatus();
               }
            }
         }

            // any multi-select lists?
         if ($bAnyMultiLists){
               // personalized table multi-select DDL
            foreach ($this->importFields as $field){
               if ($field->type=='mgenList'){
                  $this->updateMultiList($lUTableID, $field->mListIDs, $lKeyID, $field->lFieldID, $lForeignID);
               }
            }
         }

            // add record to groups?
         if ($bAddToGroup){
            $cgroup->lForeignID = $lKeyID;
            foreach ($lGroupIDs as $lGroupID){
               $cgroup->lGroupID = $lGroupID;
               $cgroup->addGroupMembership();
            }
         }

            // people record / add as volunteer?
         if ($bAddVolRec){
            $cvol->volRecs[0]->lPeopleID = $lKeyID;
            $cvol->lAddNewVolunteer();
         }

         $this->logImportRec($lLogID, $lKeyID);
      }
         // if people import, set household IDs
      if ($this->enumContext==CENUM_CONTEXT_PEOPLE || $this->enumContext==CENUM_CONTEXT_BIZ){
         $peeps = new mpeople;
         $peeps->forceNullToHeadOfHousehold();
      }
   }

   private function updateMultiList($lUTableID, $mListIDs, $lKeyID, $lFieldID, $lForeignID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // out with the old
      $sqlStr =
        "DELETE FROM uf_ddl_multi
         WHERE
                pdm_lUTableID    = $lUTableID
            AND pdm_lFieldID     = $lFieldID
            AND pdm_lUTableRecID = $lKeyID;";
      $this->db->query($sqlStr);

         // in with the new
      if (count($mListIDs) > 0){
         $sqlValues = '';
         foreach ($mListIDs as $lDDLID){
            $sqlValues .= ', '."\n".'('.$lFieldID.', '.$lUTableID.', '.$lKeyID.', '.$lDDLID.')';
         }
         $sqlStr =
           'INSERT INTO `uf_ddl_multi` (`pdm_lFieldID`, `pdm_lUTableID`, `pdm_lUTableRecID`, `pdm_lDDLID`)
            VALUES '.substr($sqlValues, 1).';';
         $this->db->query($sqlStr);
      }
   }

   private function strPrepInputVar(&$field,
                                    $varUserValue, $strDBField,      &$userRec,
                                    &$lForeignID){
   //---------------------------------------------------------------------
   // a default value that begins with '#' means use the value for
   // the field following the '#'
   //---------------------------------------------------------------------

      $enumType        = $field->type;
      $varDefaultValue = $field->defaultValue;
      $lGenListIDX     = $field->genListIDX;

      $field->mListIDs   = null;  // list of list IDs for multi-drop downs

      $varUserValue = trim($varUserValue);
      if ($varUserValue.'' == ''){
         if (is_null($varDefaultValue)){
            return('null');
         }elseif ($varDefaultValue==''){
            $varUserValue = $varDefaultValue;
         }elseif (substr($varDefaultValue, 0, 1)=='#'){
            $strLookupField = strtolower(substr($varDefaultValue, 1));
            $varUserValue = $userRec[$strLookupField];
         }else {
            $varUserValue = $varDefaultValue;
         }
      }

      switch ($enumType){
         case 'text':
         case 'email':
            return(strPrepStr($varUserValue.''));
            break;

         case 'ID':
            $lForeignID = (int)$varUserValue;
         case 'int':
         case 'clientID':
            return((int)$varUserValue);
            break;

         case 'currency':
            return(number_format($varUserValue, 2, '.', ''));
            break;

         case 'Campaign':
            return($this->lCampIDViaAcctCamp($userRec['account'], $userRec['campaign']));
            break;

         case 'aco':
            return(array_search(strtolower($varUserValue), $this->aco));
            break;

         case 'genList':
            $lIdx = array_search(strtolower($varUserValue), $this->genericList[$lGenListIDX]);
            if ($lIdx===false){
               $lIdx = $varDefaultValue;
               if ($lIdx.'' == '') $lIdx = 'null';
            }
            if ($strDBField=='gi_lGIK_ID' && $varUserValue!='null'){
               $this->bGIK = true;
            }
            return($lIdx);
            break;

         case 'mgenList':
            $field->mListIDs = array();

            $strList = explode("\n", $varUserValue);
            foreach ($strList as $strVal){
               $strVal = strtolower(trim($strVal.''));
               if ($strVal != ''){
                  $field->mListIDs[] = array_search($strVal, $this->genericList[$lGenListIDX]);
               }
            }
            break;

         case 'mysqlDate':
            return(strPrepStr($varUserValue));
            break;

         case 'YesNo':
            return((strtolower($varUserValue.'')=='yes') ? '1' : '0');
            break;

         case 'enumGender':
            return(strPrepStr($varUserValue));
            break;

         case 'clientSpecial':
            $clientValue = $this->getClientSpecialField($varUserValue, $field);
            if (!is_null($clientValue)) return($clientValue);
            break;

         default:
            screamForHelp($enumType.': invalid import type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   private function lAddImportLogEntry($enumType, $utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      if (is_null($utable)){
         $sqlStr =
            'INSERT INTO import_log
             SET
                il_enumImportType='.strPrepStr($enumType).",
                il_bRetired  = 0,
                il_lOriginID = $glUserID,
                il_dteOrigin = NOW();";
      }else {
         $sqlStr =
            'INSERT INTO import_log
             SET
                il_enumImportType='.strPrepStr(CENUM_CONTEXT_PTABLE).",
                il_lUTableID = $utable->lTableID,
                il_bRetired  = 0,
                il_lOriginID = $glUserID,
                il_dteOrigin = NOW();";

      }
      $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   private function logImportRec($lLogID, $lForeignID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "INSERT INTO import_ids SET iid_lImportID=$lLogID, iid_lForeignID=$lForeignID;";
      $this->db->query($sqlStr);
   }

   public function loadImportLog($bViaID, $lImportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bViaID){
         $strWhere = " AND il_lKeyID=$lImportID ";
      }else {
         $strWhere = '';
      }
      $this->logEntries = array();
      $sqlStr =
           "SELECT il_lKeyID, il_enumImportType, il_lUTableID, il_lOriginID,
               UNIX_TIMESTAMP(il_dteOrigin) AS dteOrigin,
               usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,

               pft_strUserTableName, pft_strDataTableName, pft_enumAttachType, pft_bMultiEntry
            FROM import_log
               INNER JOIN admin_users AS usersC ON il_lOriginID = usersC.us_lKeyID
               LEFT  JOIN uf_tables             ON pft_lKeyID   = il_lUTableID
            WHERE NOT il_bRetired $strWhere
            ORDER BY  il_lKeyID DESC;";
      $query = $this->db->query($sqlStr);

      $this->lNumLogEntries = $query->num_rows();
      if ($this->lNumLogEntries == 0){
         $this->logEntries[0] = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->logEntries[$idx] = new stdClass;
            $entry = &$this->logEntries[$idx];
            $entry->lKeyID            = $lLogID = $row->il_lKeyID;
            $entry->enumImportType    = $row->il_enumImportType;
            $entry->lUTableID         = $row->il_lUTableID;

            $entry->strUserTableName  = $row->pft_strUserTableName;
            $entry->strDataTableName  = $row->pft_strDataTableName;
            $entry->enumAttachType    = $row->pft_enumAttachType;
            $entry->bMultiEntry       = $row->pft_bMultiEntry;

            $entry->lOriginID         = $row->il_lOriginID;
            $entry->dteOrigin         = $row->dteOrigin;
            $entry->strUserCFName     = $row->strCFName;
            $entry->strUserCLName     = $row->strCLName;
            $entry->strUserCSafeName  = htmlspecialchars($row->strCFName.' '.$row->strCLName);
            $entry->lNumRecs          = $this->lNumRecsViaImportID($lLogID);
            ++$idx;
         }
      }
   }

   function pTableForeignIDsViaImportID($lImportID, $lUTableID, &$lNumFIDs){
   //---------------------------------------------------------------------
   // return an array of foreign IDs for the pTable records that
   // were included in a given import
   //---------------------------------------------------------------------
      $FIDs = array();
      $cUF = new muser_fields;
      $strUTable  = $cUF->strGenUF_TableName($lUTableID);
      $strKeyIDFN = $cUF->strGenUF_KeyIDFN($lUTableID);
      $strFID_FN  = $cUF->strGenUF_ForeignIDFN($lUTableID);

      $sqlStr =
         "SELECT DISTINCT $strFID_FN AS lKeyID
          FROM import_ids
             INNER JOIN $strUTable ON $strKeyIDFN=iid_lForeignID
          WHERE iid_lImportID=$lImportID
          ORDER BY $strFID_FN;";

      $query = $this->db->query($sqlStr);
      $lNumFIDs = $query->num_rows();
      if ($this->lNumFIDs > 0){
         foreach ($query->result() as $row)   {
            $FIDs[] = (int)$row->lKeyID;
         }
      }
      return($FIDs);
   }

   function lNumRecsViaImportID($lLogID){
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM import_ids
         WHERE iid_lImportID=$lLogID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return($row->lNumRecs);
   }

   public function loadForeignIDs($lImportID){
      $this->foreignIDs = array();
      $sqlStr = "SELECT iid_lForeignID FROM import_ids WHERE iid_lImportID=$lImportID;";
      $query = $this->db->query($sqlStr);
      $this->lNumFIDs = $query->num_rows();
      if ($this->lNumFIDs > 0){
         foreach ($query->result() as $row)   {
            $this->foreignIDs[] = (int)$row->iid_lForeignID;
         }
      }
   }

   public function bVerifyForeignKey($lID, $strTable, $strKeyFN, $strRetiredFN, $strWhereExtra=''){
      $sqlStr =
         "SELECT COUNT(*) AS lNumRecs
          FROM $strTable
          WHERE $strKeyFN=$lID AND NOT $strRetiredFN $strWhereExtra;";
      $query = $this->db->query($sqlStr);
      $row =  $query->row();
      return($row->lNumRecs > 0);
   }

   private function bVerifyPTableKey($lID, $enumAttachType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumAttachType){
         case CENUM_CONTEXT_PEOPLE:
            $bFKeyOK = $this->bVerifyForeignKey($lID, 'people_names', 'pe_lKeyID', 'pe_bRetired', ' AND NOT pe_bBiz ');
            break;
         case CENUM_CONTEXT_BIZ:
            $bFKeyOK = $this->bVerifyForeignKey($lID, 'people_names', 'pe_lKeyID', 'pe_bRetired', ' AND pe_bBiz ');
            break;
         case CENUM_CONTEXT_CLIENT:
            $bFKeyOK = $this->bVerifyForeignKey($lID, 'client_records', 'cr_lKeyID', 'cr_bRetired');
            break;
         case CENUM_CONTEXT_GIFT:
            $bFKeyOK = $this->bVerifyForeignKey($lID, 'gifts', 'gi_lKeyID', 'gi_bRetired', ' AND gi_lSponsorID IS NULL');
            break;
         case CENUM_CONTEXT_SPONSORSHIP:
            $bFKeyOK = $this->bVerifyForeignKey($lID, 'sponsor', 'sp_lKeyID', 'sp_bRetired');
            break;
         case CENUM_CONTEXT_USER:
            $bFKeyOK = $this->bVerifyForeignKey($lID, 'admin_users', 'us_lKeyID', 'us_bInactive');
            break;
         case CENUM_CONTEXT_VOLUNTEER:
            $bFKeyOK = $this->bVerifyForeignKey($lID, 'volunteers', 'vol_lKeyID', 'vol_bRetired');
            break;
         default:
            screamForHelp($enumAttachType.': invalid data type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($bFKeyOK);
   }

   function reviewPeopleBizCSV($userCSV){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strTTableName = 'tmpImportReview';
      $this->createTempReviewTable($strTTableName);
      foreach($userCSV as $rec){

         $strInsert = '';
         foreach ($this->importFields as $field){
            $strDBField = $field->DBFieldName;
            if (is_null($field->colIDX)){
               $varUserValue = null;
            }else {
                  // need to trim out the &nbsp; character (xA0)
               $varUserValue = trim($rec[$field->fieldName], " \t\n\r\0\x0B\xA0");
            }
               // a null field name means that this field is not to be inserted
            if (!is_null($strDBField)){
               $strInsert .= ",\n $strDBField = ".strPrepStr($varUserValue)." ";
            }
         }
         if ($strInsert != ''){
            $sqlStr = "INSERT INTO $strTTableName SET ".substr($strInsert, 1).';';
            $query = $this->db->query($sqlStr);
         }
      }

         // now review the import file with existing people
      $sqlStr = "SELECT * FROM $strTTableName WHERE 1";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $lRecID = $row->lKeyID;
            $strNotes = $this->strReviewSingleImportRow($row, $strCode);
            if ($strNotes != ''){
               $sqlUpdate =
                    "UPDATE $strTTableName
                     SET strNotes=".strPrepStr($strNotes).',
                        strCode = '.strPrepStr($strCode)."
                     WHERE lKeyID=$lRecID;";
               $query = $this->db->query($sqlUpdate);
            }
         }
      }

         // return the results
      $sqlStr =
         "SELECT
           lKeyID     AS `idx`,
           strCode    AS `code`,
           strNotes   AS `notes`,

           strTitle   AS `Title`,
           strLName   AS `Last Name`,
           strFName   AS `First Name`,
           strBizName AS `Business Name`,
           strAddr1   AS `Address 1`,
           strAddr2   AS `Address 2`,
           strCity    AS `City`,
           strState   AS `State`,
           strCountry AS `Country`,
           strZip     AS `Zip`,
           strPhone   AS `Phone`,
           strCell    AS `Cell`,
           strEmail   AS `Email`
         FROM $strTTableName
          WHERE 1
          ORDER BY lKeyID;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   private function strReviewSingleImportRow($row, &$strCode){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      if (($row->strFName != '') && ($row->strLName != '')){
         $strOut .= $this->strReviewNameInfo($row, $strCode);
      }elseif ($row->strBizName != ''){
         $strOut .= $this->strReviewBizInfo($row, $strCode);
      }elseif ($row->strEmail != ''){
         $strOut .= $this->strReviewEmailInfo($row, $strCode);
      }else {
         $strCode = '99 - not enough info to analyze';
         $strOut .= 'Not enough info';
      }
      return($strOut);
   }

   private function strReviewEmailInfo($rrow, &$strCode){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      $sqlStr =
        "SELECT
            pe_lKeyID, pe_strFName, pe_strLName, pe_strAddr1, pe_strAddr2,
            pe_strCity, pe_strState, pe_strCountry, pe_strZip, pe_strPhone,
            pe_strCell, pe_strEmail, pe_bBiz,
            pe_lOriginID, UNIX_TIMESTAMP(pe_dteOrigin) AS dteOrigin
         FROM people_names
         WHERE
            NOT pe_bRetired
            AND pe_strEmail=".strPrepStr($rrow->strEmail)."
         ORDER BY pe_lKeyID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows > 0) {
         $idx = 0;
         $strOut .= "Name matches the following records:\n";
         $strCode = '3 - email matches the following records:';
         foreach ($query->result() as $row){
            $lPID = $row->pe_lKeyID;
            $bBiz = (boolean)$row->pe_bBiz;
            if ($bBiz){
               $strOut .= "   Business ID: $lPID\n"
                         .'   Name: '.$row->pe_strLName."\n";
            }else {
               $strOut .= "   People ID: $lPID\n"
                         .'   Name: '.$row->pe_strLName.', '.$row->pe_strFName."\n";
            }
         }
      }else {
         $strCode = '98 - no email match';
         $strOut .= "Import Email does not match any records in the database.\n";
      }

      return($strOut);
   }

   private function strReviewBizInfo($rrow, &$strCode){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return($this->strReviewNameBizInfo(true, $rrow, $strCode));
   }

   private function strReviewNameInfo($rrow, &$strCode){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return($this->strReviewNameBizInfo(false, $rrow, $strCode));
   }

   private function strReviewNameBizInfo($bBiz, $rrow, &$strCode){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bBiz){
         $strLabel = 'business/organization';
         $strWhereExtra = ' AND pe_bBiz
                            AND pe_strLName='.strPrepStr($rrow->strBizName).' ';
      }else {
         $strLabel = 'people';
         $strWhereExtra = ' AND NOT pe_bBiz
             AND pe_strFName='.strPrepStr($rrow->strFName).'
             AND pe_strLName='.strPrepStr($rrow->strLName).' ';
      }
      $strOut = '';
      $sqlStr =
        "SELECT
            pe_lKeyID, pe_strFName, pe_strLName, pe_strAddr1, pe_strAddr2,
            pe_strCity, pe_strState, pe_strCountry, pe_strZip, pe_strPhone,
            pe_strCell, pe_strEmail,
            pe_lOriginID, UNIX_TIMESTAMP(pe_dteOrigin) AS dteOrigin
         FROM people_names
         WHERE
            NOT pe_bRetired
            $strWhereExtra
         ORDER BY pe_lKeyID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows > 0) {
         $idx = 0;
         $strOut .= "Name matches the following $strLabel records:\n";
         $strCode = '2 - possible duplicate entry';
         foreach ($query->result() as $row){
            $lPID = $row->pe_lKeyID;
            $strOut .= "   $strLabel ID: $lPID\n";
            $strOut .= $this->reviewTestFieldMismatch('      ', 'Address 1', $rrow->strAddr1,   $row->pe_strAddr1);
            $strOut .= $this->reviewTestFieldMismatch('      ', 'Address 2', $rrow->strAddr2,   $row->pe_strAddr2);
            $strOut .= $this->reviewTestFieldMismatch('      ', 'City',      $rrow->strCity,    $row->pe_strCity);
            $strOut .= $this->reviewTestFieldMismatch('      ', 'State',     $rrow->strState,   $row->pe_strState);
            $strOut .= $this->reviewTestFieldMismatch('      ', 'Country',   $rrow->strCountry, $row->pe_strCountry);
            $strOut .= $this->reviewTestFieldMismatch('      ', 'Zip',       $rrow->strZip,     $row->pe_strZip);
            $strOut .= $this->reviewTestFieldMismatch('      ', 'Phone',     $rrow->strPhone,   $row->pe_strPhone);
            $strOut .= $this->reviewTestFieldMismatch('      ', 'Cell',      $rrow->strCell,    $row->pe_strCell);
            $strOut .= $this->reviewTestFieldMismatch('      ', 'EMail',     $rrow->strEmail,   $row->pe_strEmail);
         }
      }else {
         $strCode = '1 - candidate for import';
         $strOut .= "Name does not match any $strLabel names in the database\n";
      }

      return($strOut);
   }

   private function reviewTestFieldMismatch($strIndent, $strLabel, $strVal1, $strVal2){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($strVal1 != $strVal2){
         $strOut = $strIndent.$strLabel.' mismatch:'."\n"
            .$strIndent.$strIndent.'import: '.$strVal1."\n"
            .$strIndent.$strIndent.'database: '.$strVal2."\n";
      }else {
         $strOut = '';
      }
      return($strOut);
   }

   private function createTempReviewTable($strTmpTable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "DROP TABLE IF EXISTS $strTmpTable;";
      $query = $this->db->query($sqlStr);

      $sqlStr =
        "
         -- CREATE TABLE IF NOT EXISTS $strTmpTable (
         CREATE TEMPORARY TABLE IF NOT EXISTS $strTmpTable (
           lKeyID int(11) NOT NULL AUTO_INCREMENT,
           lPID int(11) DEFAULT NULL,
           lBID int(11) DEFAULT NULL,
           strCode  varchar(80) NOT NULL DEFAULT '',
           strNotes text NOT NULL,

           strTitle   varchar(80) NOT NULL DEFAULT '',
           strLName   varchar(80) NOT NULL DEFAULT '',
           strFName   varchar(80) NOT NULL DEFAULT '',
           strBizName varchar(80) NOT NULL DEFAULT '',
           strAddr1   varchar(80) NOT NULL DEFAULT '',
           strAddr2   varchar(80) NOT NULL DEFAULT '',
           strCity    varchar(80) NOT NULL DEFAULT '',
           strState   varchar(80) NOT NULL DEFAULT '',
           strCountry varchar(80) NOT NULL DEFAULT '',
           strZip     varchar(80) NOT NULL DEFAULT '',
           strPhone   varchar(80) NOT NULL DEFAULT '',
           strCell    varchar(80) NOT NULL DEFAULT '',
           strEmail   varchar(80) NOT NULL DEFAULT '',

           PRIMARY KEY (lKeyID),
           KEY lPID (lPID),
           KEY lBID (lBID)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $query = $this->db->query($sqlStr);
   }

   function lFieldIDX_ViaFieldName($strFieldName, $bFreakIfNotFound=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strTest = strtolower($strFieldName);
      for ($idx=0; $idx<$this->lNumFields; ++$idx){
         if ($this->importFields[$idx]->fieldName == $strTest){
            return($idx);
         }
      }
      if ($bFreakIfNotFound){
         screamForHelp(htmlspecialchars($strFieldName).': field not found<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }else {
         return(null);
      }

   }


   /* ---------------------------------------------------------
         P e r s o n a l i z e d   T a b l e   I m p o r t
      --------------------------------------------------------- */

   function strParentTableDDL($enumMatch, &$lNumAvail){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumAvail = 0;
      $cschema = new muser_schema;
      $enumAttachType = array(
                  CENUM_CONTEXT_BIZ,
                  CENUM_CONTEXT_CLIENT,
                  CENUM_CONTEXT_GIFT,
                  CENUM_CONTEXT_PEOPLE,
                  CENUM_CONTEXT_SPONSORSHIP,
                  CENUM_CONTEXT_USER,
                  CENUM_CONTEXT_VOLUNTEER
                 );
      $cschema->loadUFSchemaViaAttachType($enumAttachType, false);
      if ($cschema->lNumTables == 0) return(null);

      $strOut = "\n".'<option value="-1">&nbsp;</option>'."\n";

      foreach ($cschema->schema as $pTable){
         if ($pTable->lNumFields > 0){
            $strOut .=
                 '<option value="'.$pTable->lTableID.'">['
                          .strXlateContext($pTable->enumAttachType, true, false).'] '
                          .htmlspecialchars($pTable->strUserTableName)
                .'</option>'."\n";
            ++$lNumAvail;
         }
      }

      if ($lNumAvail > 0){
         return($strOut);
      }else {
         return(null);
      }
   }

   function xlateUField2IField($enumFType, &$enumIType, &$lSize, &$strDefault){
   //---------------------------------------------------------------------
   // translate personalized field type to import type
   //---------------------------------------------------------------------
      switch ($enumFType){
         case CS_FT_CHECKBOX:  $enumIType = 'YesNo';     $lSize = 10;  $strDefault = 'No';         break;
         case CS_FT_DDL:       $enumIType = 'genList';   $lSize = 255; $strDefault = '';           break;
         case CS_FT_DDLMULTI:  $enumIType = 'mgenList';  $lSize = 255; $strDefault = '';           break;
         case CS_FT_DATE:      $enumIType = 'mysqlDate'; $lSize = 40;  $strDefault = '0000-00-00'; break;
         case CS_FT_CURRENCY:  $enumIType = 'currency';  $lSize = 40;  $strDefault = '0.0';        break;
         case CS_FT_TEXTLONG:  $enumIType = 'text';      $lSize = 0;   $strDefault = '';           break;
         case CS_FT_TEXT255:   $enumIType = 'text';      $lSize = 255; $strDefault = '';           break;
         case CS_FT_TEXT80:    $enumIType = 'text';      $lSize = 80;  $strDefault = '';           break;
         case CS_FT_TEXT20:    $enumIType = 'text';      $lSize = 20;  $strDefault = '';           break;
         case CS_FT_INTEGER:   $enumIType = 'int';       $lSize = 20;  $strDefault = '';           break;
         case CS_FT_CLIENTID:  $enumIType = 'clientID';  $lSize = 20;  $strDefault = '';           break;
         default:
            screamForHelp($enumFType.': invalid field type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   private function insertUpdateUTable($utable, $sqlSet, $lFKeyID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'SELECT '.$utable->strDataTableKeyID.' AS lKeyID
          FROM '.$utable->strDataTableName.'
          WHERE '.$utable->strDataTableFID."=$lFKeyID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() > 0){
         $row = $query->row();
         $lKeyID = (int)$row->lKeyID;
         $sqlStr = 'UPDATE '.$utable->strDataTableName.' SET '.$sqlSet.' WHERE '.$utable->strDataTableKeyID." = $lKeyID;";
         $this->db->query($sqlStr);
      }else {
         $sqlStr = 'INSERT INTO '.$utable->strDataTableName.' SET '.$sqlSet.';';
         $this->db->query($sqlStr);
         $lKeyID = $this->db->insert_id();
      }
      return($lKeyID);
   }




}
