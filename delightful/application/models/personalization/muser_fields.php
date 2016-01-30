<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2011-2014 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
 Functions to support the User-Defined Personalized Fields
---------------------------------------------------------------------
      $this->load->model('personalization/muser_fields', 'clsUF');
---------------------------------------------------------------------
__construct             ()

UTILITIES
=========
   initBaseClass           ()
   setTType                ($enumTType)
   strGenUF_TableName      ($lTableID)
   strGenUF_DDL_TableName  ($lTableID)
   strGenUF_Log_TableName  ($lTableID)
   strGenUF_KeyFieldPrefix ($lTableID)
   strGenUF_FieldName      ($lTableID, $lFieldID)
   loadTablesViaTType      ()
   loadTableViaTableID     ()
   loadTableInfoGeneric    ($bViaTType, $bViaTID)
   lNumUF_TableFields      ($lTableID)

FIELDS
======
   loadSingleField      ($lFieldID)
   loadTableFields      ()
   loadFieldsGeneric    ($bViaTableID, $lTID, $lFID)
   loadFieldTypes       ()
   strFieldTypeLabel    ($enumType)
   strXlateDefFieldValue($clsField)

LOGS
======
   lCntLogEntries       ($lFieldID, $lForeignID)
   loadLogEntries       ($lFieldID,  $lForeignID,
   loadSingleLogEntry   ($lLogEntryID)
   loadLogEntriesGeneric($lFieldID,  $lForeignID,
   addNewLogEntry()
   updateLogEntry()
   removeLogEntry()

DDLs
======
   strDDLValue         ($lDDLID)
   strDDLFields        ($enumMatch)
   uf_ddl_info         ($lFieldID)
   setDDL_asConfigured ($lFID)
   loadDDLEntries      ($lFieldID)
   displayUF_DDL       ($lFieldID, $lMatchID)


DATA RECORDS
============
   loadSingleDataRecord ($lTableID, $lForeignID, &$recInfo)
   loadUFRow            ($strTable, $strFieldPrefix, $lForeignID)
   createSingleUFRow    ($strTable, $strFieldPrefix, $lForeignID)
   markSingleEntryRecRecorded($lTableID, $lForeignID)
   markSingleEntryRecRecordedViaRecID($lTableID, $lRecID)

TABLE CONTEXT
============
   tableContext      ()
   loadPeopleContext ()
   loadClientContext ()

MISCELLANEOUS
=============
   ufTableSummaryDisplay($bLite)


---------------------------------------------------------------------*/


//define('CS_FT_REMINDER' , 'Reminder');


define('CS_UF_MAXLOGDISPLAYLEN', 255);   // max log display length

class muser_fields extends CI_Model{

   public $enumTableTypes, $enumTType, $strTTypeLabel, $strHTMLSummary;

      //------------------------
      // user tables
      //------------------------
   public $lNumTables, $userTables, $sqlWhereTableExtra;

   public $lTableID, $strTableDescription, $strUserTableName, $strENPTableName, $strFieldPrefix;


      //------------------------
      // fields
      //------------------------
   public $lNumFields, $lNumEditableFields, $fields;  // editable field count excludes logs and headings

   public $lNumFieldTypes, $fieldsTypes;

   public $lFieldID, $clsDDL_Info;

   public $lNumLogEntriesDisplayed, $lNumTotLogEntries, $lNumLogEntriesLoaded, $logEntries;

   public $lForeignID, $bGiftPerson;

      //------------------------
      // context
      //------------------------
   public $strContextLabel, $strContextName, $strContextViewLink,
          $clsPeople, $clsBiz, $clsClient, $clsVol;

   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();

      $this->initBaseClass();
   }

   public function initBaseClass(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         //-------------------------------------------------
         // table types match the enum settings in
         // field uf_tables.pft_enumAttachType
         //-------------------------------------------------
      $this->enumTableTypes = array(
                  CENUM_CONTEXT_BIZ,
                  CENUM_CONTEXT_CLIENT,    CENUM_CONTEXT_LOCATION,
                  CENUM_CONTEXT_GIFT,
                  CENUM_CONTEXT_PEOPLE,    CENUM_CONTEXT_SPONSORSHIP,
                  CENUM_CONTEXT_VOLUNTEER, CENUM_CONTEXT_USER);

      $this->enumTType = $this->strTTypeLabel = null;

      $this->lNumTables = $this->userTables = null;

      $this->lTableID        = $this->strTableDescription = $this->strUserTableName =
      $this->strENPTableName = $this->strFieldPrefix      = null;

      $this->lNumFields         = $this->fields      =
      $this->lNumFieldTypes     = $this->fieldsTypes =
      $this->lNumEditableFields =
      $this->lFieldID           = $this->clsDDL_Info = null;

      $this->lNumLogEntriesDisplayed = $this->lNumTotLogEntries =
      $this->lNumLogEntriesLoaded = $this->logEntries = $this->lForeignID =  null;

      $this->strContextLabel = $this->strContextName = $this->strContextViewLink = null;

      $this->clsPeople = $this->clsBiz = $this->clsClient = $clsVol = null;

      $this->sqlWhereTableExtra = '';
   }

   function setTType($enumTType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumTType){
         case CENUM_CONTEXT_PEOPLE:
            $this->strTTypeLabel = 'People';
            break;
         case CENUM_CONTEXT_BIZ:
            $this->strTTypeLabel = 'Business';
            break;
         case CENUM_CONTEXT_SPONSORSHIP:
            $this->strTTypeLabel = 'Sponsorship';
            break;
         case CENUM_CONTEXT_CLIENT:
            $this->strTTypeLabel = 'Client';
            break;
         case CENUM_CONTEXT_LOCATION:
            $this->strTTypeLabel = 'Client Location';
            break;
         case CENUM_CONTEXT_GIFT:
            $this->strTTypeLabel = 'Donation';
            break;
         case CENUM_CONTEXT_VOLUNTEER:
            $this->strTTypeLabel = 'Volunteer';
            break;
         case CENUM_CONTEXT_CPROGENROLL:
            $this->strTTypeLabel = 'Client Program/Enrollment';
            break;
         case CENUM_CONTEXT_CPROGATTEND:
            $this->strTTypeLabel = 'Client Program/Attendance';
            break;
         case CENUM_CONTEXT_USER:
            $this->strTTypeLabel = 'Users / Staff Members';
            break;
         default:
            screamForHelp($enumTType.': Invalid UF table type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      $this->enumTType = $enumTType;
   }

   public function strGenUF_TableName($lTableID){
   //---------------------------------------------------------------------
   // return the internal table name, based on the tableID
   //
   // sample output:
   //    'uf_000123'
   //---------------------------------------------------------------------
      return('uf_'.str_pad($lTableID.'', 6, '0', STR_PAD_LEFT));
   }

   public function strGenUF_DDL_TableName($lTableID){
   //---------------------------------------------------------------------
   // return the internal table name, based on the tableID
   //
   // sample output:
   //    'uf_000123_ddl'
   //---------------------------------------------------------------------
      return($this->strGenUF_TableName($lTableID).'_ddl');
   }

   public function strGenUF_Log_TableName($lTableID){
   //---------------------------------------------------------------------
   // return the internal table name, based on the tableID
   //
   // sample output:
   //    'uf_000123_log'
   //---------------------------------------------------------------------
      return($this->strGenUF_TableName($lTableID).'_log');
   }

   public function strGenUF_KeyFieldPrefix($lTableID){
   //---------------------------------------------------------------------
   // return a prefix for the user-defined field, based on the tableID
   //
   // sample output:
   //    'uf000123'
   //---------------------------------------------------------------------
      return('uf'.str_pad($lTableID.'', 6, '0', STR_PAD_LEFT));
   }

   public function strGenUF_FieldName($lTableID, $lFieldID){
   //---------------------------------------------------------------------
   // return a prefix for the user-defined field, based on the tableID and
   // field ID (for fields other than ddl, log)
   //
   // sample output:
   //    'uf000123_023412'
   //---------------------------------------------------------------------
      return(
               'uf'.str_pad($lTableID.'', 6, '0', STR_PAD_LEFT)
              .'_'. str_pad($lFieldID.'', 6, '0', STR_PAD_LEFT)
            );
   }

   public function strGenUF_KeyIDFN($lTableID){
   //---------------------------------------------------------------------
   // return a prefix for the user-defined field, based on the tableID and
   // field ID (for fields other than ddl, log)
   //
   // sample output:
   //    'uf000123_lKeyID'
   //---------------------------------------------------------------------
      return('uf'.str_pad($lTableID.'', 6, '0', STR_PAD_LEFT).'_lKeyID');
   }
   
   public function strGenUF_ForeignIDFN($lTableID){
   //---------------------------------------------------------------------
   // return a prefix for the user-defined field, based on the tableID and
   // field ID (for fields other than ddl, log)
   //
   // sample output:
   //    'uf000123_lForeignKey'
   //---------------------------------------------------------------------
      return('uf'.str_pad($lTableID.'', 6, '0', STR_PAD_LEFT).'_lForeignKey');
   }

   public function loadTablesViaTType($bExcludeHidden=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_null($this->enumTType)) screamForHelp('Class not initialized!<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      $this->loadTableInfoGeneric(true, false, $bExcludeHidden);
   }

   public function loadTableViaTableID($bExcludeHidden=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_null($this->lTableID)) screamForHelp('Class not initialized!<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      $this->loadTableInfoGeneric(false, true, $bExcludeHidden);
   }

   public function loadTableInfoGeneric($bViaTType, $bViaTID, $bExcludeHidden=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cperms = new mpermissions;
      if ($bViaTType){
         $strWhere = ' AND (NOT pft_bRetired)
                       AND (pft_enumAttachType='.strPrepStr($this->enumTType).') ';
      }elseif ($bViaTID){
         if (is_array($this->lTableID)){
            $strWhere = ' AND pft_lKeyID IN ('.implode(',', $this->lTableID).') ';
         }else {
            $strWhere = " AND pft_lKeyID=$this->lTableID ";
         }
      }else {
         screamForHelp('Invalid processing type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }
      if ($bExcludeHidden) $strWhere .= ' AND (NOT pft_bHidden) ';
      $sqlStr =
           "SELECT
              pft_lKeyID, pft_strUserTableName, pft_strDescription,
              pft_bMultiEntry, pft_bReadOnly, pft_bHidden,
              pft_bCollapsibleHeadings, pft_bCollapseDefaultHide,
              pft_strDataTableName, pft_enumAttachType,
              pft_bAlertIfNoEntry, pft_strAlertMsg,
              pft_strVerificationModule,
              pft_strVModEntryPoint
            FROM uf_tables
            WHERE 1 $strWhere $this->sqlWhereTableExtra
            ORDER BY pft_strUserTableName, pft_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumTables = $numRows = $query->num_rows();

      $this->userTables = array();
      if ($numRows==0){
         $this->userTables[0] = new stdClass;
         $uTable = &$this->userTables[0];
         $uTable->lKeyID                 =
         $uTable->strUserTableName       =
         $uTable->strDescription         =
         $uTable->strDataTableName       =
         $uTable->enumTType              =
         $uTable->bMultiEntry            =
         $uTable->bReadOnly              =
         $uTable->bHidden                =
         $uTable->bCollapsibleHeadings   =
         $uTable->bCollapseDefaultHide   =
         $uTable->bMultiEntry            =
         $uTable->bAlertIfNoEntry        =
         $uTable->strAlertMsg            =
         $uTable->strVerificationModule  =
         $uTable->strVModEntryPoint      =
         $uTable->strFieldPrefix         = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $lTableID = (int)$row->pft_lKeyID;
            $this->userTables[$idx] = new stdClass;
            $uTable = &$this->userTables[$idx];

            $uTable->lKeyID                 = $lTableID;
            $uTable->strUserTableName       = $row->pft_strUserTableName;
            $uTable->strDescription         = $row->pft_strDescription;
            $uTable->strDataTableName       = $row->pft_strDataTableName;
            $uTable->enumTType              = $row->pft_enumAttachType;
            $uTable->bMultiEntry            = (boolean)$row->pft_bMultiEntry;
            $uTable->bReadOnly              = (boolean)$row->pft_bReadOnly;
            $uTable->bHidden                = (boolean)$row->pft_bHidden;
            $uTable->bCollapsibleHeadings   = $row->pft_bCollapsibleHeadings;
            $uTable->bCollapseDefaultHide   = $row->pft_bCollapseDefaultHide;
            $uTable->strFieldPrefix         = $this->strGenUF_KeyFieldPrefix($lTableID);

            $uTable->strVerificationModule  = $row->pft_strVerificationModule;
            $uTable->strVModEntryPoint      = $row->pft_strVModEntryPoint;

               // client program tables - a special class of client personalized tables;
               // these fields are set in admin/uf_multirecord: addEditMultiRecord()
            $uTable->bCProg               = false;
            $uTable->bEnrollment          = false;

               // for single-entry tables: raise alert if not entered?
            $uTable->bAlertIfNoEntry      = (boolean)$row->pft_bAlertIfNoEntry;
            $uTable->strAlertMsg          = $row->pft_strAlertMsg;

               // table permissions
            $cperms->tablePerms($lTableID, $uTable->lNumPerms, $uTable->perms);
            $cperms->consolidateTablePerms($uTable->lNumPerms, $uTable->perms,
                                  $uTable->lNumConsolidated, $uTable->cperms);

            ++$idx;
         }
      }
   }

   function lNumUF_TableFields($lTableID, $bExcludeHidden=true){
   //---------------------------------------------------------------------
   // return the number of fields associated with a given user-defined
   // table
   //---------------------------------------------------------------------
      $sqlStr =
          "SELECT count(pff_lKeyID) AS lNumFields
           FROM uf_fields
           WHERE pff_lTableID=$lTableID ".($bExcludeHidden ? 'AND NOT pff_bHidden' : '').";";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return($row->lNumFields);
   }




      /*------------------------------------------------------------------
                         F I E L D S  /  M E T A D A T A
      ------------------------------------------------------------------*/
   public function bFieldExists($lFieldID, $bExcludeHidden=true){
   //---------------------------------------------------------------------
   // 
   //---------------------------------------------------------------------
         // special case - record written?
      if ($lFieldID < 0) return(true);
      
      $sqlStr = 
         "SELECT COUNT(*) AS lNumRecs 
          FROM uf_fields 
          WHERE pff_lKeyID=$lFieldID ".($bExcludeHidden ? ' AND NOT pff_bHidden ' : '').';';
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return(((int)$row->lNumRecs) > 0);
   }
   
   public function loadSingleField($lFieldID, $bExcludeHidden=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadFieldsGeneric(false, null, $lFieldID, $bExcludeHidden);
   }

   public function loadTableFields($bExcludeHidden=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_null($this->lTableID)) screamForHelp('Class not initialized!<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      $this->loadFieldsGeneric(true, $this->lTableID, null, $bExcludeHidden);
   }

   public function loadFieldsGeneric($bViaTableID, $lTID, $lFID, $bExcludeHidden=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterACO;

      $this->lNumFields = 0;
      $this->lNumEditableFields = 0;
      $this->fields = array();

      if ($bViaTableID){
         $strWhere = " (pff_lTableID=$lTID) ";
      }else {
         $strWhere = " (pff_lKeyID=$lFID) ";
      }

      $sqlStr =
           "SELECT
               pff_lKeyID, pff_strFieldNameUser, pff_strFieldNameInternal,
               pff_strFieldNotes, pff_bPrefilled,
               pff_enumFieldType, pff_bConfigured, pff_lDDLDefault,
               pff_bCheckDef, pff_curDef, pff_lCurrencyACO,
               pff_strTxtDef, pff_lDef, pff_bRequired, pff_bHidden,
               pff_lSortIDX
            FROM uf_fields
            WHERE $strWhere ".($bExcludeHidden ? 'AND NOT pff_bHidden' : '')."
            ORDER BY pff_lSortIDX;";

      $query = $this->db->query($sqlStr);
      $this->lNumFields = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->lNumEditableFields = 0;
         $this->fields[0] = new stdClass;
         $uField = &$this->fields[0];
         $uField->pff_lKeyID               =
         $uField->pff_strFieldNameUser     =
         $uField->strFieldNameInternal     =
         $uField->strFieldNotes            =
         $uField->enumFieldType            =
         $uField->bPrefilled               =
         $uField->pff_bConfigured          =
         $uField->pff_lDDLDefault          =
         $uField->pff_bCheckDef            =
         $uField->pff_curDef               =
         $uField->pff_strTxtDef            =
         $uField->pff_lDef                 =
         $uField->pff_bHidden              =
         $uField->lSortIDX                 =
         $uField->pff_bRequired            = null;

         $uField->pff_lCurrencyACO         = $gclsChapterACO->lKeyID;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->fields[$idx] = new stdClass;
            $uField = &$this->fields[$idx];
            $uField->pff_lKeyID               = (int)$row->pff_lKeyID;
            $uField->pff_strFieldNameUser     = $row->pff_strFieldNameUser;
            $uField->strFieldNameInternal     = $row->pff_strFieldNameInternal;
            $uField->strFieldNotes            = $row->pff_strFieldNotes;
            $uField->enumFieldType            = $enumType = $row->pff_enumFieldType;
            $uField->bPrefilled               = (boolean)$row->pff_bPrefilled;
            $uField->pff_bConfigured          = (boolean)$row->pff_bConfigured;
            $uField->strFieldTypeLabel        = $this->strFieldTypeLabel($row->pff_enumFieldType);
            $uField->pff_lDDLDefault          = $row->pff_lDDLDefault;
            $uField->pff_bCheckDef            = (boolean)$row->pff_bCheckDef;
            $uField->pff_curDef               = $row->pff_curDef;
            $uField->pff_lCurrencyACO         = (int)$row->pff_lCurrencyACO;
            $uField->pff_strTxtDef            = $row->pff_strTxtDef;
            $uField->pff_lDef                 = (int)$row->pff_lDef;
            $uField->pff_bHidden              = $row->pff_bHidden;
            $uField->lSortIDX                 = (int)$row->pff_lSortIDX;
            $uField->pff_bRequired            = (boolean)$row->pff_bRequired;
            if (!($enumType==CS_FT_LOG || $enumType==CS_FT_HEADING)) ++$this->lNumEditableFields;

            ++$idx;
         }
      }
   }

   public function bAnyPrefillFields(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->lNumFields == 0){
         return(false);
      }
      foreach ($this->fields as $field){
         if ($field->bPrefilled) return(true);
      }
      return(false);
   }

   public function loadFieldTypes($bMultiTable = false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->lNumFieldTypes = 13;
      if ($bMultiTable) --$this->lNumFieldTypes;  // exclude log fields from multi-tables
      $this->fieldsTypes = array();

      for ($idx=0; $idx < $this->lNumFieldTypes; ++$idx) $this->fieldsTypes[$idx] = new stdClass;

      $this->fieldsTypes[ 0]->strType = CS_FT_CHECKBOX;
      $this->fieldsTypes[ 1]->strType = CS_FT_DATE;
      $this->fieldsTypes[ 2]->strType = CS_FT_TEXT255;
      $this->fieldsTypes[ 3]->strType = CS_FT_TEXT80;
      $this->fieldsTypes[ 4]->strType = CS_FT_TEXT20;
      $this->fieldsTypes[ 5]->strType = CS_FT_TEXTLONG;
      $this->fieldsTypes[ 6]->strType = CS_FT_INTEGER;
      $this->fieldsTypes[ 7]->strType = CS_FT_CURRENCY;
      $this->fieldsTypes[ 8]->strType = CS_FT_DDL;
      $this->fieldsTypes[ 9]->strType = CS_FT_DDLMULTI;
      $this->fieldsTypes[10]->strType = CS_FT_HEADING;
      $this->fieldsTypes[11]->strType = CS_FT_CLIENTID;
      if (!$bMultiTable) $this->fieldsTypes[12]->strType = CS_FT_LOG;

      $this->fieldsTypes[ 0]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[ 0]->strType);
      $this->fieldsTypes[ 1]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[ 1]->strType);
      $this->fieldsTypes[ 2]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[ 2]->strType);
      $this->fieldsTypes[ 3]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[ 3]->strType);
      $this->fieldsTypes[ 4]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[ 4]->strType);
      $this->fieldsTypes[ 5]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[ 5]->strType);
      $this->fieldsTypes[ 6]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[ 6]->strType);
      $this->fieldsTypes[ 7]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[ 7]->strType);
      $this->fieldsTypes[ 8]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[ 8]->strType);
      $this->fieldsTypes[ 9]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[ 9]->strType);
      $this->fieldsTypes[10]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[10]->strType);
      $this->fieldsTypes[11]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[11]->strType);
      if (!$bMultiTable) $this->fieldsTypes[12]->strLabel = $this->strFieldTypeLabel($this->fieldsTypes[12]->strType);

      $this->fieldsTypes[ 0]->bText = false;
      $this->fieldsTypes[ 1]->bText = false;
      $this->fieldsTypes[ 2]->bText = true;
      $this->fieldsTypes[ 3]->bText = true;
      $this->fieldsTypes[ 4]->bText = true;
      $this->fieldsTypes[ 5]->bText = false;
      $this->fieldsTypes[ 6]->bText = false;
      $this->fieldsTypes[ 7]->bText = false;
      $this->fieldsTypes[ 8]->bText = true;
      $this->fieldsTypes[ 9]->bText = true;
      $this->fieldsTypes[10]->bText = false;
      $this->fieldsTypes[11]->bText = false;
      if (!$bMultiTable) $this->fieldsTypes[12]->bText = true;
   }

   public function strFieldTypeLabel($enumType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strLabel = '#error#';

      switch ($enumType){
         case CS_FT_CHECKBOX:    $strLabel = 'Checkbox';                  break;
         case CS_FT_DATE    :    $strLabel = 'Date';                      break;
         case CS_FT_TEXT255 :    $strLabel = 'Text (up to 255 char.)';    break;
         case CS_FT_TEXT80  :    $strLabel = 'Text (up to 80 char.)';     break;
         case CS_FT_TEXT20  :    $strLabel = 'Text (up to 20 char.)';     break;
         case CS_FT_TEXTLONG:    $strLabel = 'Text (long)';               break;
         case CS_FT_INTEGER :    $strLabel = 'Number';                    break;
         case CS_FT_CURRENCY:    $strLabel = 'Currency';                  break;
         case CS_FT_DDL     :    $strLabel = 'Drop-down list';            break;
         case CS_FT_DDLMULTI:    $strLabel = 'Drop-down (multi-select)';  break;
         case CS_FT_LOG     :    $strLabel = 'Log (long text)';           break;
         case CS_FT_HEADING :    $strLabel = 'Heading';                   break;
         case CS_FT_CLIENTID:    $strLabel = 'clientID';                  break;

         case CS_FT_ID:          $strLabel = 'Record ID';                 break;
         case CS_FT_TEXT:        $strLabel = 'Text';                      break;

         case CS_FT_ENUM:        $strLabel = 'Enumerated';                break;
         case CS_FT_DDL_SPECIAL: $strLabel = 'Drop-down list';            break;

      }
      return($strLabel);
   }

   public function strXlateDefFieldValue($clsField){
   //---------------------------------------------------------------------
   // return a string representing the user-defined field type
   //---------------------------------------------------------------------
      switch ($clsField->enumFieldType) {
         case CS_FT_CHECKBOX:
            $strDefValue = ($clsField->pff_bCheckDef ? 'Checked' : 'Unchecked');
            break;
         case CS_FT_DATE:
            $strDefValue = 'n/a';
            break;
//         case CS_FT_DATETIME:
//            $strDefValue = 'n/a';
//            break;
         case CS_FT_TEXT255:
         case CS_FT_TEXT80:
         case CS_FT_TEXT20:
            $strDefValue = $clsField->pff_strTxtDef;
            break;
         case CS_FT_TEXTLONG:
            $strDefValue = 'n/a';
            break;

         case CS_FT_CLIENTID:
            $strDefValue = 'n/a';
            break;

         case CS_FT_INTEGER:
            $strDefValue = (string)$clsField->pff_lDef;
            break;
         case CS_FT_CURRENCY:
            $strDefValue = number_format($clsField->pff_curDef, 2);
            break;
         case CS_FT_DDLMULTI:
         case CS_FT_DDL:
         case CS_FT_LOG:
         case CS_FT_HEADING:
            $strDefValue = 'n/a';
            break;
/*-----------
         case CI_UF_EMAIL:
            $strDefValue = 'n/a';
            break;
         case CI_UF_HLINK:
            $strDefValue = 'n/a';
            break;
-------*/
         default:
            $strDefValue = '#ERROR#';
            break;
      }
      return($strDefValue);
   }

   public function fieldEntriesViaTableID($lTableID, $enumFieldType, &$lNumFields,
                                          &$fieldArray, $bExcludeHidden=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $fieldArray = array();
      $lNumFields = 0;
      $sqlStr =
        'SELECT
            pff_lKeyID, pff_strFieldNameInternal, pff_strFieldNameUser, pff_strFieldNameInternal,
            pft_lKeyID, pft_strUserTableName, pft_strDataTableName,
            pft_enumAttachType, pft_strDescription, pff_bHidden
         FROM uf_fields
            INNER JOIN uf_tables ON pff_lTableID= pft_lKeyID
         WHERE pff_enumFieldType='.strPrepStr($enumFieldType)."
            AND pff_lTableID=$lTableID
            AND NOT pft_bRetired ".($bExcludeHidden ? 'AND NOT pff_bHidden' : '')."
         ORDER BY pff_lSortIDX;";

      $query = $this->db->query($sqlStr);
      $lNumFields = $query->num_rows();
      if ($lNumFields > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $fieldArray[$idx] = new stdClass;
            $fa = &$fieldArray[$idx];
            $fa->lFieldKeyID             = $row->pff_lKeyID;
            $fa->strFieldNameInternal    = $row->strFieldNameInternal;
            $fa->strFieldNameUser        = $row->pff_strFieldNameUser;
            $fa->strFieldNotes           = $row->pff_strFieldNotes;
            $fa->strSafeFieldName        = htmlspecialchars($row->pff_strFieldNameUser);
            $fa->bHiddenField            = $row->pff_bHidden;
            $fa->lTableID                = $row->pft_lKeyID;
            $fa->strTableName            = $row->pft_strUserTableName;
            $fa->strSafeTableName        = htmlspecialchars($row->pft_strUserTableName);
            $fa->strDataTableName        = $row->pft_strDataTableName;
            $fa->enumAttachType          = $row->pft_enumAttachType;
            $fa->pft_strTableDescription = $row->pft_strDescription;
            ++$idx;
         }
      }
   }

      /*------------------------------------------------------------------
                      L O G   F I E L D S
      ------------------------------------------------------------------*/

   public function lCntLogEntries($lFieldID, $lForeignID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT COUNT(*) AS lNumRecs
          FROM uf_logs
          WHERE uflog_lFieldID=$lFieldID AND uflog_lForeignID=$lForeignID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         $lCnt = (int)$row->lNumRecs;
         if (is_null($lCnt)){
            return(0);
         }else {
            return($lCnt);
         }
      }
   }

   public function loadLogEntries(
                        $lFieldID,   $lForeignID,
                        $lMaxToLoad, $lMaxLenEntry){
   //---------------------------------------------------------------------
   // set $lMaxToLoad to 0 to load all
   // set $lMaxLenEntry to 0 for no truncation
   //---------------------------------------------------------------------
      $this->loadLogEntriesGeneric(
                        $lFieldID,   $lForeignID,
                        $lMaxToLoad, $lMaxLenEntry, true,
                        null);
   }

   public function loadSingleLogEntry($lLogEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadLogEntriesGeneric(
                        null,  null,
                        1,     0,     false,
                        $lLogEntryID);
   }

   public function loadLogEntriesGeneric(
                        $lFieldID,   $lForeignID,
                        $lMaxToLoad, $lMaxLenEntry, $bLoadAll,
                        $lLogEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->logEntries = array();

      if ($lMaxToLoad > 0){
         $strLimit = " LIMIT 0, $lMaxToLoad ";
      }else {
         $strLimit = ' ';
      }

      if ($lMaxLenEntry > 0){
         $strLogEntry = " LEFT(uflog_strLogEntry, $lMaxLenEntry) ";
      }else {
         $strLogEntry = " uflog_strLogEntry ";
      }

      if ($bLoadAll){
         $strWhere =
            " uflog_lFieldID=$lFieldID
              AND uflog_lForeignID=$lForeignID ";

      }else {
         $strWhere = " uflog_lKeyID = $lLogEntryID ";
      }

      $sqlStr =
        "SELECT
            uflog_lKeyID                        AS lKeyID,
            uflog_lFieldID                      AS lFieldID,
            uflog_lForeignID                    AS lForeignID,
            uflog_lOriginID                     AS lOriginID,
            UNIX_TIMESTAMP(uflog_dteOrigin)     AS dteOrigin,
            uflog_lLastUpdateID                 AS lLastUpdateID,
            UNIX_TIMESTAMP(uflog_dteLastUpdate) AS dteLastUpdate,
            uflog_strLogTitle                   AS strTitle,
            $strLogEntry                        AS strLogEntry,
            LENGTH(uflog_strLogEntry)           AS lEntryLen
         FROM uf_logs
         WHERE $strWhere
         ORDER BY uflog_dteOrigin DESC, uflog_lKeyID
         $strLimit;";

      $query = $this->db->query($sqlStr);
      $this->lNumLogEntriesLoaded = $numRows = $query->num_rows();

      if ($numRows==0){
         $this->logEntries[0] = new stdClass;

         $this->logEntries[0]->lKeyID        =
         $this->logEntries[0]->lFieldID      =
         $this->logEntries[0]->lForeignID    =
         $this->logEntries[0]->lOriginID     =
         $this->logEntries[0]->dteOrigin     =
         $this->logEntries[0]->lLastUpdateID =
         $this->logEntries[0]->dteLastUpdate =
         $this->logEntries[0]->strTitle      =
         $this->logEntries[0]->strLogEntry   =
         $this->logEntries[0]->lEntryLen     = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->logEntries[$idx] = new stdClass;

            $this->logEntries[$idx]->lKeyID        = (int)$row->lKeyID;
            $this->logEntries[$idx]->lFieldID      = (int)$row->lFieldID;
            $this->logEntries[$idx]->lForeignID    = (int)$row->lForeignID;
            $this->logEntries[$idx]->lOriginID     = (int)$row->lOriginID;
            $this->logEntries[$idx]->dteOrigin     = (int)$row->dteOrigin;
            $this->logEntries[$idx]->lLastUpdateID = (int)$row->lLastUpdateID;
            $this->logEntries[$idx]->dteLastUpdate = (int)$row->dteLastUpdate;
            $this->logEntries[$idx]->strTitle      = $row->strTitle;
            $this->logEntries[$idx]->strLogEntry   = $row->strLogEntry;
            $this->logEntries[$idx]->lEntryLen     = $row->lEntryLen;

            ++$idx;
         }
      }
   }

   public function addNewLogEntry(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      if (is_null($this->logEntries[0]->lFieldID) || is_null($this->logEntries[0]->lForeignID))
         screamForHelp('CLASS NOT INITIALIZED<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      $sqlStr =
         "INSERT INTO uf_logs
         SET
            uflog_lFieldID      = ".$this->logEntries[0]->lFieldID.",
            uflog_lForeignID    = ".$this->logEntries[0]->lForeignID.",
            uflog_lOriginID     = $glUserID,
            uflog_lLastUpdateID = $glUserID,
            uflog_dteOrigin     = NOW(),
            uflog_dteLastUpdate = NOW(),
            uflog_strLogTitle   = ".strPrepStr($this->logEntries[0]->strTitle).',
            uflog_strLogEntry   = '.strPrepStr($this->logEntries[0]->strLogEntry).';';
      $query = $this->db->query($sqlStr);
   }

   public function updateLogEntry(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      if (is_null($this->logEntries[0]->lKeyID)) screamForHelp('CLASS NOT INITIALIZED<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      $sqlStr =
         "UPDATE uf_logs
         SET
            uflog_lLastUpdateID = $glUserID,
            uflog_dteLastUpdate = NOW(),
            uflog_strLogTitle   = ".strPrepStr($this->logEntries[0]->strTitle).',
            uflog_strLogEntry   = '.strPrepStr($this->logEntries[0]->strLogEntry).'
         WHERE uflog_lKeyID='.$this->logEntries[0]->lKeyID.';';
      $query = $this->db->query($sqlStr);
   }

   public function removeLogEntry(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_null($this->logEntries[0]->lKeyID))
         screamForHelp('CLASS NOT INITIALIZED<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);

      $sqlStr =
         "DELETE FROM uf_logs
          WHERE uflog_lKeyID=".$this->logEntries[0]->lKeyID.';';
      $this->db->query($sqlStr);
   }

   public function strConsolidateLogEntries($lFieldID, $lForeignID, $bHTML=false){
      global $genumDateFormat;
      $strLBreak = $bHTML ? "<br>\n" : "\n";
      $strOut = '';

      $this->loadLogEntriesGeneric($lFieldID,   $lForeignID,    0, 0, true, null);
      if ($this->lNumLogEntriesLoaded != 0){
         foreach ($this->logEntries as $entry){
            $strOut .= 'Log entry for '.date($genumDateFormat, $entry->dteOrigin).$strLBreak;
            if ($bHTML){
               $strOut .= htmlspecialchars($entry->strTitle).$strLBreak.$strLBreak
                         .nl2br(htmlspecialchars($entry->strLogEntry)).$strLBreak;
            }else {
               $strOut .= $entry->strTitle.$strLBreak.$strLBreak
                         .$entry->strLogEntry.$strLBreak;
            }
            $strOut .= '--------------------------------'.$strLBreak.$strLBreak;
         }
      }
      return($strOut);
   }

      /*------------------------------------------------------------------
              M U L T I   -   D R O P  -  D O W N  L I S T S
      ------------------------------------------------------------------*/
   public function addDDLMultiEntries($lDataRecID, $multiDDL){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

         // out with the old
      $sqlStr =
          "DELETE FROM uf_ddl_multi
           WHERE
                  pdm_lFieldID     = $multiDDL->lFieldID
              AND pdm_lUTableID    = $multiDDL->lTableID
              AND pdm_lUTableRecID = $lDataRecID;";
      $this->db->query($sqlStr);

         // in with the new
      if ($multiDDL->lNumEntries > 0){
         $sqlBase =
            "INSERT INTO uf_ddl_multi
             SET
                pdm_lFieldID     = $multiDDL->lFieldID,
                pdm_lUTableID    = $multiDDL->lTableID,
                pdm_lUTableRecID = $lDataRecID,
                pdm_lDDLID       = ";
         foreach ($multiDDL->entries as $entry){
            $sqlStr = $sqlBase.$entry.';';
            $this->db->query($sqlStr);
         }
      }
   }

   function loadMultiDDLSelects($lTableID, $lFieldID, $lDataRecID, &$multiDDL){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $multiDDL = new stdClass;
      $sqlStr =
        "SELECT
             pdm_lKeyID, pdm_lFieldID, pdm_lUTableID,
             pdm_lUTableRecID, pdm_lDDLID,
             ufddl_strDDLEntry
         FROM uf_ddl_multi
            INNER JOIN uf_ddl ON pdm_lDDLID=ufddl_lKeyID
         WHERE
                pdm_lFieldID     = $lFieldID
            AND pdm_lUTableID    = $lTableID
            AND pdm_lUTableRecID = $lDataRecID
         ORDER BY ufddl_lSortIDX, pdm_lKeyID;";

      $query = $this->db->query($sqlStr);
      $multiDDL->lNumEntries = $lNumEntries = $query->num_rows();
      if ($lNumEntries > 0) {
         $idx = 0;
         $multiDDL->entries = array();
         foreach ($query->result() as $row){
            $multiDDL->entries[$idx] = new stdClass;
            $entry = &$multiDDL->entries[$idx];

            $entry->lDDLMKeyID  = (int)$row->pdm_lKeyID;
            $entry->lFieldID    = (int)$row->pdm_lFieldID;
            $entry->lUTableID   = (int)$row->pdm_lUTableID;
            $entry->lUDataRecID = (int)$row->pdm_lUTableRecID;
            $entry->lDDLID      = (int)$row->pdm_lDDLID;
            $entry->strDDLEntry = $row->ufddl_strDDLEntry;

            ++$idx;
         }
      }
   }

   function strMultiDDLUL($multiDDL, $lMarginTop=0, $lMarginLeft = -16, $strEmpty='&nbsp;'){
   //---------------------------------------------------------------------
   // create an unordered list from a $multiDDL structure
   //---------------------------------------------------------------------
      if ($multiDDL->lNumEntries == 0){
         $strOut = $strEmpty;
      }else {
         $strOut = '<ul style="margin-top: '.$lMarginTop.'pt; margin-left: '.$lMarginLeft.'pt;">'."\n";
         foreach ($multiDDL->entries as $entry){
            $strOut .= '<li>'.htmlspecialchars($entry->strDDLEntry).'</li>'."\n";
         }
         $strOut .= '</ul>'."\n";
      }
      return($strOut);
   }

      /*------------------------------------------------------------------
                      D R O P  -  D O W N  L I S T S
      ------------------------------------------------------------------*/
   public function strDDLValue($lDDLID){
   //---------------------------------------------------------------------
   // load a single DDL entry
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT ufddl_strDDLEntry AS strEntry
         FROM uf_ddl
         WHERE ufddl_lKeyID=$lDDLID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return('');
      }else {
         $row = $query->row();
         return($row->strEntry);
      }
   }

   public function strDDLFields($enumMatch){
   //---------------------------------------------------------------------
   // return list of fields as DDL entries
   //---------------------------------------------------------------------
      if (is_null($this->lNumFieldTypes)) screamForHelp('Class not initialized!<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      $strDDL = '';
      foreach ($this->fieldsTypes as $clsField){
         $strSelect = $clsField->strType == $enumMatch ? 'SELECTED' : '';
         $strDDL .=
            '<option value="'.$clsField->strType.'" '.$strSelect.'>'
               .$clsField->strLabel
           .'</option>'."\n";
      }
      return($strDDL);
   }

   public function uf_ddl_info($lFieldID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
          "SELECT
              pff_lTableID, pff_strFieldNameUser, pff_strFieldNotes, pff_bConfigured,
              pff_strFieldNameInternal, pff_enumFieldType
           FROM uf_fields
           WHERE pff_lKeyID=$lFieldID;";
      $query = $this->db->query($sqlStr);
      $lNumFields = $query->num_rows();

      if ($lNumFields > 0) {
         $idx = 0;
         $row = $query->row();
         $enumFT = $row->pff_enumFieldType;
         if (!(($enumFT==CS_FT_DDL)||($enumFT==CS_FT_DDLMULTI))){
            screamForHelp('FIELD TYPE MISMATCH <br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         }
         $this->clsDDL_Info = new stdClass;
         $this->clsDDL_Info->pff_lKeyID               = $lFieldID;
         $this->clsDDL_Info->pff_lTableID             = $lTableID = $row->pff_lTableID;
         $this->clsDDL_Info->pff_strFieldNameUser     = $row->pff_strFieldNameUser;
         $this->clsDDL_Info->strFieldNotes            = $row->pff_strFieldNotes;
         $this->clsDDL_Info->pff_bConfigured          = $bConfigured = $row->pff_bConfigured;
         $this->clsDDL_Info->strFieldNameInternal     = $row->pff_strFieldNameInternal;
         if ($bConfigured){
            $this->loadDDLEntries($lFieldID, false);
         }else {
            $this->clsDDL_Info->lNumEntries = 0;
            $this->clsDDL_Info->lMinSortIDX = $this->clsDDL_Info->lMaxSortIDX = null;
         }
      }
   }

   public function setDDL_asConfigured($lFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         "UPDATE uf_fields
          SET pff_bConfigured=1, pff_lLastUpdateID=$glUserID
          WHERE pff_lKeyID=$lFID;";
      $query = $this->db->query($sqlStr);
   }

   function addUF_DDLEntry($strDDLEntry, $lFieldID, $lNewSortIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           "INSERT INTO uf_ddl
            SET
               ufddl_lFieldID    = $lFieldID,
               ufddl_lSortIDX    = $lNewSortIDX,
               ufddl_bRetired    = 0,
               ufddl_strDDLEntry = ".strPrepStr($strDDLEntry).';';
      $query = $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function updateUF_DDLEntry($strDDLEntry, $lDDLEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           "UPDATE uf_ddl
            SET
               ufddl_strDDLEntry = ".strPrepStr($strDDLEntry)."
            WHERE ufddl_lKeyID=$lDDLEntryID;";
      $query = $this->db->query($sqlStr);
   }

   function remove_DDLEntry($lDDLEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           "UPDATE uf_ddl
            SET
               ufddl_bRetired = 1
            WHERE ufddl_lKeyID=$lDDLEntryID;";
      $query = $this->db->query($sqlStr);
   }

   public function loadDDLEntries($lFieldID, $bCreateStdClass=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           "SELECT
               ufddl_lKeyID,
               ufddl_lSortIDX,
               ufddl_strDDLEntry,
               ufddl_bRetired
            FROM uf_ddl
            WHERE ufddl_lFieldID=$lFieldID
               AND NOT ufddl_bRetired
            ORDER BY ufddl_lSortIDX, ufddl_strDDLEntry;";
      $query = $this->db->query($sqlStr);

      if ($bCreateStdClass) $this->clsDDL_Info = new stdClass;
      $this->clsDDL_Info->lNumEntries = $numRows = $query->num_rows();
      $this->clsDDL_Info->clsEntries = array();
      $this->clsDDL_Info->lMinSortIDX = 99999;
      $this->clsDDL_Info->lMaxSortIDX = -99999;

      if ($numRows > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $this->clsDDL_Info->clsEntries[$idx] = new stdClass;
            $this->clsDDL_Info->clsEntries[$idx]->lKeyID   = (int)$row->ufddl_lKeyID;
            $this->clsDDL_Info->clsEntries[$idx]->lSortIDX = $lSortIDX = (int)$row->ufddl_lSortIDX;
            $this->clsDDL_Info->clsEntries[$idx]->strEntry = $row->ufddl_strDDLEntry;
            $this->clsDDL_Info->clsEntries[$idx]->bRetired = (bool)$row->ufddl_bRetired;
            if ($lSortIDX < $this->clsDDL_Info->lMinSortIDX) $this->clsDDL_Info->lMinSortIDX = $lSortIDX;
            if ($lSortIDX > $this->clsDDL_Info->lMaxSortIDX) $this->clsDDL_Info->lMaxSortIDX = $lSortIDX;

            ++$idx;
         }
      }
   }

   function loadMultiIDs($lFieldID, $lTableID, $lRecID, &$lNumEntries, &$lDDLIds){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lDDLIds = array();
      $sqlStr =
        "SELECT pdm_lDDLID
         FROM uf_ddl_multi
            INNER JOIN uf_ddl  ON pdm_lDDLID=ufddl_lKeyID
         WHERE
                pdm_lFieldID     = $lFieldID
            AND pdm_lUTableID    = $lTableID
            AND pdm_lUTableRecID = $lRecID
         ORDER BY ufddl_lSortIDX;";
      $query = $this->db->query($sqlStr);

      $lNumEntries = $query->num_rows();
      if ($lNumEntries > 0) {
         foreach ($query->result() as $row){
            $lDDLIds[] = $row->pdm_lDDLID;
         }
      }
   }

   function strDisplayUF_DDL($lFieldID, $lMatchID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      $this->loadDDLEntries($lFieldID);
      $bMultiSel = is_array($lMatchID);
      foreach ($this->clsDDL_Info->clsEntries as $clsDDL){
         if (!$clsDDL->bRetired){
            $lKeyID = $clsDDL->lKeyID;
            if ($bMultiSel){
               if (in_array($lKeyID, $lMatchID)){
                  $strSelect = ' SELECTED ';
               }else {
                  $strSelect = '';
               }
            }else {
               $strSelect = $lMatchID==$lKeyID ? ' SELECTED ' : '';
            }
            $strOut .= '
               <option value="'.$lKeyID.'" '.$strSelect.'>'
                  .htmlspecialchars($clsDDL->strEntry).'</option>';
         }
      }
      return($strOut);
   }

   function sort_DDLEntries($lFieldID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT ufddl_lKeyID
         FROM uf_ddl
         WHERE ufddl_lFieldID=$lFieldID
            AND NOT ufddl_bRetired
         ORDER BY ufddl_strDDLEntry, ufddl_lKeyID;";
      $query = $this->db->query($sqlStr);

      $lNumEntries = $query->num_rows();
      if ($lNumEntries > 0) {
         $idx = 1;
         foreach ($query->result() as $row){
            $lKeyID = $row->ufddl_lKeyID;
            $sqlUpdate = "UPDATE uf_ddl SET ufddl_lSortIDX=$idx WHERE ufddl_lKeyID=$lKeyID;";
            $this->db->query($sqlUpdate);
            ++$idx;
         }
      }
   }

      /*------------------------------------------------------------------
                      D A T A   R E C O R D S
      ------------------------------------------------------------------*/
   public function deleteForeignViaUFTableType($enumTableType, $lForeignID){
   //---------------------------------------------------------------------
   //  Delete all foreignID records from all the UF tables of the
   //  specified type. This is usually the result of a parent record
   //  being removed (such as a people record, client record, etc)
   //
   //  Note: $lForeignID can be a scalar or an array
   //---------------------------------------------------------------------
         // load all UF tables of specified type
      $sqlStr =
        'SELECT  pft_lKeyID, pft_strDataTableName
         FROM uf_tables
         WHERE NOT pft_bRetired
            AND  pft_enumAttachType = '.strPrepStr($enumTableType).';';
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) return;   // nothing to do
      foreach ($query->result() as $row){
         $lTableID = (int)$row->pft_lKeyID;
         $strTable = $row->pft_strDataTableName;
         $this->deleteForeignViaUFTable($strTable, $lTableID, $lForeignID);
      }
   }

   public function deleteForeignViaUFTable($strTable, $lTableID, $lForeignID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strFIDFN = $this->strGenUF_ForeignIDFN($lTableID);
      if (is_array($lForeignID)){
         $strWhere = " $strFIDFN IN (".implode(',', $lForeignID).") ";
      }else {
         $strWhere = " $strFIDFN=$lForeignID ";
      }
      $sqlStr =
         "DELETE FROM $strTable WHERE $strWhere;";
      $query = $this->db->query($sqlStr);
   }

   public function loadSingleDataRecord($lTableID, $lForeignID, &$recInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strTable       = $this->strGenUF_TableName($lTableID);
      $strFieldPrefix = $this->strGenUF_KeyFieldPrefix($lTableID);
      $strKeyFN       = $strFieldPrefix.'_lKeyID';
      $strFIDFN       = $strFieldPrefix.'_lForeignKey';

      $recInfo = new stdClass;

      $row = $this->loadUFRow($strTable, $strFieldPrefix, $lForeignID);
      if (is_null($row)){
         $this->createSingleUFRow($lTableID, $strTable, $strFieldPrefix, $lForeignID);
         $row = $this->loadUFRow ($strTable, $strFieldPrefix, $lForeignID);
      }

      $recInfo->strFieldPrefix = $strFieldPrefix;
      $recInfo->strTable       = $strTable;
      $recInfo->lRecID         = $row[$strKeyFN];
      $recInfo->lFID           = $row[$strFIDFN];
      $recInfo->bRecordEntered = $row[$strFieldPrefix.'_bRecordEntered'];

      $this->loadFieldsGeneric(true, $lTableID, null);
      for ($idx=0; $idx < $this->lNumFields; ++$idx){
         $enumFieldType = $this->fields[$idx]->enumFieldType;
         if ($enumFieldType == CS_FT_LOG || $enumFieldType == CS_FT_HEADING){
            $this->fields[$idx]->userValue = null;
         }else {
            $this->fields[$idx]->userValue = $row[$this->fields[$idx]->strFieldNameInternal];
         }
      }
   }

   private function loadUFRow($strTable, $strFieldPrefix, $lForeignID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT $strTable.*
         FROM $strTable
         WHERE ".$strFieldPrefix."_lForeignKey=$lForeignID;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(null);
      }else {
         $rarray = $query->result_array();
         return($rarray[0]);
      }
   }

   public function createSingleUFRow($lTableID, $strTable, $strFieldPrefix, $lForeignID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

      $strForeignFN = $strFieldPrefix.'_lForeignKey';
      $sqlStr =
         "INSERT INTO $strTable
          SET $strForeignFN=$lForeignID "
            .$this->strSetDefaultForLongText($lTableID)."
          ON DUPLICATE KEY UPDATE $strForeignFN=$strForeignFN;";

      $this->db->query($sqlStr);
   }

   public function markSingleEntryRecRecorded($lTableID, $lForeignID){
   //---------------------------------------------------------------------
   // For a single entry personalized table, flag a record as recorded
   // by foreign ID
   //---------------------------------------------------------------------
      global $glUserID;
      $strTable    = $this->strGenUF_TableName($lTableID);
      $strFNPrefix = $this->strGenUF_KeyFieldPrefix($lTableID);
      $strFNRecorded  = $strFNPrefix.'_bRecordEntered';
      $strFNOriginID  = $strFNPrefix.'_lOriginID';
      $strFNDteOrigin = $strFNPrefix.'_dteOrigin';
      $strFNForeignID = $strFNPrefix.'_lForeignKey';

      $sqlStr =
            "UPDATE $strTable
             SET
                $strFNRecorded=1,
                $strFNOriginID=$glUserID,
                $strFNDteOrigin=NOW()
             WHERE $strFNForeignID=$lForeignID AND NOT $strFNRecorded;";
      $query = $this->db->query($sqlStr);
   }

   function markSingleEntryRecRecordedViaRecID($lTableID, $lRecID){
   //---------------------------------------------------------------------
   // For a single entry personalized table, flag a record as recorded
   // by record ID
   //---------------------------------------------------------------------
      global $glUserID;
      $strTable    = $this->strGenUF_TableName($lTableID);
      $strFNPrefix = $this->strGenUF_KeyFieldPrefix($lTableID);
      $strFNRecorded  = $strFNPrefix.'_bRecordEntered';
      $strFNOriginID  = $strFNPrefix.'_lOriginID';
      $strFNDteOrigin = $strFNPrefix.'_dteOrigin';
      $strFNRecID     = $strFNPrefix.'_lKeyID';

      $sqlStr =
            "UPDATE $strTable
             SET
                $strFNRecorded=1,
                $strFNOriginID=$glUserID,
                $strFNDteOrigin=NOW()
             WHERE $strFNRecID=$lRecID AND NOT $strFNRecorded;";
      $query = $this->db->query($sqlStr);
   }

   private function strSetDefaultForLongText($lTableID){
   //---------------------------------------------------------------------
   // when creating default records, any TEXT field types must have
   // an explicit value
   //---------------------------------------------------------------------
      $strOut = '';
      $sqlStr =
        "SELECT
            pff_strFieldNameInternal
         FROM uf_fields
         WHERE pff_lTableID=$lTableID
         AND pff_enumFieldType=".strPrepStr(CS_FT_TEXTLONG).';';

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      foreach ($query->result() as $row) {
         $strOut .= ', '.$row->pff_strFieldNameInternal.'="" ';
      }
      return($strOut);
   }


      /*------------------------------------------------------------------
                      T A B L E   C O N T E X T
      ------------------------------------------------------------------*/
   public function strBreadcrumbsTableDisplay($lUserTableIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      $currentTable = $this->userTables[$lUserTableIDX];
      switch ($currentTable->enumTType){
         case CENUM_CONTEXT_PEOPLE:
            $strOut =
                        anchor('main/menu/People', 'People', 'class="breadcrumb"')
                 .' | '.anchor('people/people_record/view/'.$this->lForeignID, 'Record', 'class="breadcrumb"')
                 .' | Personalized Table';
            break;

         case CENUM_CONTEXT_CLIENT:
         case CENUM_CONTEXT_CPROGRAM:
         case CENUM_CONTEXT_CPROGENROLL:
         case CENUM_CONTEXT_CPROGATTEND:
            $strOut =
                        anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                 .' | '.anchor('clients/client_record/view/'.$this->lForeignID, 'Client Record', 'class="breadcrumb"')
                 .' | Personalized Table';
            break;

         case CENUM_CONTEXT_LOCATION:
            $strOut =
                        anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                 .' | '.anchor('admin/alists/showLists',                    'Lists',            'class="breadcrumb"')
                 .' | '.anchor('admin/admin_special_lists/clients/locationView',    'Client Locations', 'class="breadcrumb"')
                 .' | '.anchor('clients/locations/view/'.$this->lForeignID, 'Location Record',  'class="breadcrumb"')
                 .' | Personalized Table';
            break;

         case CENUM_CONTEXT_BIZ:
            $strOut =
                        anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                 .' | '.anchor('biz/biz_record/view/'.$this->lForeignID, 'Record', 'class="breadcrumb"')
                 .' | Personalized Table';
            break;

         case CENUM_CONTEXT_GIFT:
            if ($this->bGiftPerson){
               $strOut =
                        anchor('main/menu/people', 'People', 'class="breadcrumb"')
                 .' | '.anchor('people/people_record/view/'.$this->lForeignID, 'Record', 'class="breadcrumb"')
                 .' | Personalized Table';
            }else {
               screamForHelp($this->enumTType.': Biz Breadcrumbs....feature not available yet<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            }
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
               $strOut =
                        anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                 .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$this->lForeignID, 'Sponsorship Record', 'class="breadcrumb"')
                 .' | Personalized Table';
            break;

         case CENUM_CONTEXT_USER:
               $strOut =
                        anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                 .' | '.anchor('admin/accts/userAcctDir/A/', 'User Accounts', 'class="breadcrumb"')
                 .' | '.anchor('admin/accts/view/'.$this->lForeignID, 'User Record', 'class="breadcrumb"')
                 .' | Personalized Table';
            break;


         case CENUM_CONTEXT_VOLUNTEER:
            $strOut =
                        anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
                 .' | '.anchor('volunteers/vol_record/volRecordView/'.$this->lForeignID, 'Record', 'class="breadcrumb"')
                 .' | Personalized Table';
             break;

         case CENUM_CONTEXT_LOCATION:
         default:
            screamForHelp($currentTable->enumTType.': feature not available yet<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strOut);
   }

   public function tableContext($lUserTableIDX){
   //---------------------------------------------------------------------
   // info about the table association
   //---------------------------------------------------------------------
      $currentTable = $this->userTables[$lUserTableIDX];
      if (is_null($this->lForeignID) || (is_null($currentTable->lKeyID))) screamForHelp('Class not initialized!<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);

      switch ($currentTable->enumTType){
         case CENUM_CONTEXT_PEOPLE:
            $this->loadPeopleContext();
            break;

         case CENUM_CONTEXT_CLIENT:
         case CENUM_CONTEXT_CPROGRAM:
         case CENUM_CONTEXT_CPROGENROLL:
         case CENUM_CONTEXT_CPROGATTEND:
            $this->loadClientContext();
            break;

         case CENUM_CONTEXT_LOCATION:
            $this->loadClientLocationContext();
            break;

         case CENUM_CONTEXT_BIZ:
            $this->loadBizContext();
            break;

         case CENUM_CONTEXT_GIFT:
            $this->loadGiftContext();
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $this->loadSponsorshipContext();
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            $this->loadVolunteerContext();
            break;

         case CENUM_CONTEXT_USER:
            $this->loadUserContext();
            break;

         case CENUM_CONTEXT_LOCATION:
         default:
            screamForHelp($this->enumTType.': feature not available yet<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   public function tableContextRecView($lUserTableIDX){
   //---------------------------------------------------------------------
   // assumes previous call to $this->tableContext()
   //---------------------------------------------------------------------
      $currentTable = $this->userTables[$lUserTableIDX];
      if (is_null($this->lForeignID) || (is_null($currentTable->lKeyID))) screamForHelp('Class not initialized!<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);

      $this->strHTMLSummary = strContextHTML($currentTable->enumTType, $this->lForeignID, $strContextName);
   }

   private function loadSponsorshipContext(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->clsSpon = new msponsorship;

      $this->clsSpon->sponsorInfoViaID($this->lForeignID);
      $this->strContextLabel    = 'sponsorship record';
      $sponRec = $this->clsSpon->sponInfo[0];
      $this->strContextName     = $sponRec->strSponProgram;
      $this->strContextViewLink = strLinkView_Sponsorship($this->lForeignID, 'View sponsorship record', true);
   }

   private function loadGiftContext(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->clsGifts = new mdonations;
      $lGiftID = $this->lForeignID;
      $this->clsGifts->loadGiftViaGID($lGiftID);
      $this->strContextLabel    = 'donation record';
      $this->strContextName     = $this->clsGifts->gifts[0]->strFormattedAmnt.' / '.$this->clsGifts->gifts[0]->strSafeName;
      $this->strContextViewLink = strLinkView_GiftsRecord($lGiftID, 'View gift record', true);
   }

   private function loadVolunteerContext(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->clsVol = new mvol;
      $this->clsVol->loadVolRecsViaVolID($this->lForeignID, true);
      $this->strContextLabel    = 'volunteer record';
      $this->strContextName     = $this->clsVol->volRecs[0]->strSafeNameFL;
      $this->strContextViewLink = strLinkView_Volunteer($this->lForeignID, 'view volunteer record', true);
   }

   private function loadUserContext(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->clsUser = new muser_accts;
      $this->clsUser->loadSingleUserRecord($this->lForeignID);
      $this->strContextLabel    = 'user record';
      $this->strContextName     = $this->clsUser->userRec[0]->strSafeName;
      $this->strContextViewLink = ''; //strLinkView_PeopleRecord($this->lForeignID, 'view people record', true);
   }

   private function loadPeopleContext(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->clsPeople = new mpeople;
      $this->clsPeople->loadPeopleViaPIDs($this->lForeignID, false, false);
      $this->strContextLabel    = 'people record';
      $this->strContextName     = $this->clsPeople->people[0]->strSafeName;
      $this->strContextViewLink = strLinkView_PeopleRecord($this->lForeignID, 'view people record', true);
   }

   private function loadBizContext(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->clsBiz = new mbiz;
      $this->clsBiz->lBID = $lBID = $this->lForeignID;
      $this->clsBiz->loadBizRecsViaBID($lBID);
      $this->strContextLabel    = 'business/organization record';
      $this->strContextName     = $this->clsBiz->bizRecs[0]->strSafeName;
      $this->strContextViewLink = strLinkView_BizRecord($this->lForeignID, 'View business/organization record', true);
   }

   private function loadClientContext(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->clsClient = new mclients;
      $this->clsClient->lClientID = $this->lForeignID;
      $this->clsClient->loadClientsViaClientID($this->lForeignID);
      $clsC = $this->clsClient->clients[0];
      $this->strContextLabel    = 'client record';
      $this->strContextName     = $clsC->strSafeName;
      $this->strContextViewLink = strLinkView_ClientRecord($this->lForeignID, 'View client record', true);
   }

   private function loadClientLocationContext(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->clsLoc = new mclient_locations;
      $this->clsLoc->loadLocationRec($this->lForeignID);

      $this->strContextLabel    = 'client location record';
      $this->strContextName     = htmlspecialchars($this->clsLoc->strLocation);
      $this->strContextViewLink = strLinkEdit_ClientLocation($this->lForeignID, 'View client location record', true);
   }

      /*------------------------------------------------------------------
                      M I S C E L L A N E O U S
      ------------------------------------------------------------------*/

   function strUFTableSummaryDisplay($bLite){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $strOut = '';

      $utable = &$this->userTables[0];

      $clsRpt->setEntrySummary();
      $strOut .= $clsRpt->openReport();

      if ($bLite){
         $strLink = '&nbsp;'.strLinkView_UFFields($this->lTableID, 'View table fields', true, '');
      }else {
         $strLink = '';
      }

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Table:', '75pt')
         .$clsRpt->writeCell($strLink.' '.htmlspecialchars($utable->strUserTableName))
         .$clsRpt->closeRow();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Table ID:', '75pt')
         .$clsRpt->writeCell(str_pad($utable->lKeyID, 5, '0', STR_PAD_LEFT), '', '', 1, 1, ' id="pTabSummaryID" ')
         .$clsRpt->closeRow();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Parent:')
         .$clsRpt->writeCell($utable->enumTType)
         .$clsRpt->closeRow();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Type:')
         .$clsRpt->writeCell(($utable->bMultiEntry ? 'Multi-record' : 'Single-entry'))
         .$clsRpt->closeRow();

      if (!$bLite){
         $strOut .=
             $clsRpt->openRow()
            .$clsRpt->writeLabel('Description:')
            .$clsRpt->writeCell(nl2br(htmlspecialchars($this->strTableDescription)))
            .$clsRpt->closeRow();
      }
      $strOut .=
         $clsRpt->closeReport();
      return($strOut);
   }

}
