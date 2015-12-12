<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2011-2015 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
 Functions to support the User-Defined Personalized Fields
---------------------------------------------------------------------
      $this->load->model('personalization/muser_schema', 'cUFSchema');
---------------------------------------------------------------------*/

class muser_schema extends CI_Model{

   public $lNumTables, $schema;
   public $sqlWhereExtra, $bLoadFields;

   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();

      $this->sqlWhereExtra = '';

         // load fields when loading tables?
      $this->bLoadFields = true;
   }

   function loadUFSchemaSingleTable($lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhereExtra = " AND pft_lKeyID=$lTableID ";
      $this->loadUFSchema();
   }

   function loadUFSchemaViaAttachType($enumAttachType, $bShowHidden=true, $bIncludePerms=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_array($enumAttachType)){
         for ($idx=0; $idx < count($enumAttachType); ++$idx){
            $enumAttachType[$idx] = strPrepStr($enumAttachType[$idx]);
         }
         $this->sqlWhereExtra .= ' AND pft_enumAttachType IN ('.implode(',', $enumAttachType).') ';
      }else {
         $this->sqlWhereExtra .= ' AND pft_enumAttachType = '.strPrepStr($enumAttachType).' ';
      }

      if (!$bShowHidden){
         $this->sqlWhereExtra .= ' AND NOT pft_bHidden ';
      }
      $this->loadUFSchema($bIncludePerms);
   }

   function loadUFSchemaViaAttachTypeUserTabName($enumAttachType, $strUserTabName, &$lTableID, $bFreakIfNotFound = false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhereExtra =
           ' AND pft_enumAttachType   = '.strPrepStr($enumAttachType).'
             AND pft_strUserTableName = '.strPrepStr($strUserTabName).' ';
      $this->loadUFSchema();

      if ($bFreakIfNotFound && $this->lNumTables==0){
         screamForHelp($strUserTabName.': user table not found<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }

         // technique to get first key value
         // http://stackoverflow.com/questions/4095796/php-how-to-get-associative-array-key-from-numeric-index
      reset($this->schema);
      $lTableID = key($this->schema);
   }

   function loadUFSchema($bIncludePerms=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      if (!isset($this->schema)) $this->schema = array();

      if ($bIncludePerms){
         $perms = new mpermissions;
         $perms->loadUserAcctInfo($glUserID, $acctAccess);
      }

      $sqlStr =
           'SELECT
               pft_lKeyID, pft_strUserTableName, pft_strDataTableName, pft_bHidden,
               pft_strDescription,
               pft_bCollapsibleHeadings, pft_bCollapseDefaultHide,
               pft_enumAttachType, pft_bMultiEntry, pft_lPermissions,
               pft_bAlertIfNoEntry, pft_strAlertMsg, pft_bReadOnly,
               pft_strVerificationModule,
               pft_strVModEntryPoint
            FROM uf_tables
            WHERE NOT pft_bRetired '.$this->sqlWhereExtra.'
            ORDER BY pft_strUserTableName, pft_lKeyID;';

      $query = $this->db->query($sqlStr);

      $this->lNumTables = $lNumTables = $query->num_rows();
      if ($lNumTables > 0) {
         foreach ($query->result() as $row){
            $lTableID = (int)$row->pft_lKeyID;

            $this->schema[$lTableID] = new stdClass;
            $utable = &$this->schema[$lTableID];

            $utable->lTableID               = $lTableID;
            $utable->strUserTableName       = $row->pft_strUserTableName;
            $utable->strDataTableName       = $row->pft_strDataTableName;
            $utable->enumAttachType         = $row->pft_enumAttachType;
            $utable->strDescription         = $row->pft_strDescription;
            $utable->bHidden                = (boolean)$row->pft_bHidden;
            $utable->bMultiEntry            = (boolean)$row->pft_bMultiEntry;
            $utable->bReadOnly              = (boolean)$row->pft_bReadOnly;
            $utable->bCollapsibleHeadings   = (boolean)$row->pft_bCollapsibleHeadings;
            $utable->bCollapseDefaultHide   = (boolean)$row->pft_bCollapseDefaultHide;

            $utable->strVerificationModule  = $row->pft_strVerificationModule;
            $utable->strVModEntryPoint      = $row->pft_strVModEntryPoint;

            $utable->bAlertIfNoEntry        = (boolean)$row->pft_bAlertIfNoEntry;
            $utable->strAlertMsg            = $row->pft_strAlertMsg;

            $utable->lPermissions           = $row->pft_lPermissions;

            $utable->strFieldPrefix         = 'uf'.str_pad($lTableID, 6, '0', STR_PAD_LEFT);
            $utable->strDataTableKeyID      = $utable->strFieldPrefix.'_lKeyID';
            $utable->strDataTableFID        = $utable->strFieldPrefix.'_lForeignKey';

            if ($this->bLoadFields){
               $this->loadUTableFields($lTableID, $utable->lNumFields, $utable->fields);
            }

            if ($bIncludePerms){
               $perms->tablePerms($lTableID, $utable->lNumPerms, $utable->perms);
               $perms->consolidateTablePerms($utable->lNumPerms, $utable->perms,
                                   $utable->lNumConsolidated, $utable->cperms);

               $utable->bAllowAccess = $perms->bDoesUserHaveAccess($acctAccess, $utable->lNumConsolidated, $utable->cperms);
            }
         }
      }
   }

   function loadUTableFields($lTableID, &$lNumFields, &$fields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $fields = array();
      $sqlStr =
        "SELECT
            pff_lKeyID, pff_lSortIDX, pff_strFieldNameInternal, pff_strFieldNameUser,
            pff_strFieldNotes,
            pff_enumFieldType, pff_bRequired, pff_bConfigured, pff_bCheckDef,
            pff_curDef, pff_strTxtDef, pff_lDef,
            pff_lDDLDefault, pff_lCurrencyACO, pff_bHidden
         FROM uf_fields
         WHERE pff_lTableID=$lTableID
         ORDER BY pff_lSortIDX, pff_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumFields = $query->num_rows();
      if ($lNumFields > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $fields[$idx] = new stdClass;
            $field = &$fields[$idx];

            $field->lFieldID             = $row->pff_lKeyID;
            $field->lSortIDX             = $row->pff_lSortIDX;
            $field->strFieldNameInternal = $row->pff_strFieldNameInternal;
            $field->strFieldNameUser     = $row->pff_strFieldNameUser;
            $field->strFieldNotes        = $row->pff_strFieldNotes;
            $field->enumFieldType        = $row->pff_enumFieldType;
            $field->bRequired            = $row->pff_bRequired;
            $field->bConfigured          = $row->pff_bConfigured;
            $field->bCheckDef            = $row->pff_bCheckDef;
            $field->curDef               = $row->pff_curDef;
            $field->strTxtDef            = $row->pff_strTxtDef;
            $field->lDef                 = $row->pff_lDef;
            $field->lDDLDefault          = $row->pff_lDDLDefault;
            $field->lCurrencyACO         = $row->pff_lCurrencyACO;
            $field->bHidden              = $row->pff_bHidden;

            ++$idx;
         }
      }
   }

   function tableInfoViaUserTableName($strUTableName, &$tableInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $tableInfo = null;
      foreach ($this->schema as $utable){
         if ($utable->strUserTableName == $strUTableName){
            $this->loadTable($tableInfo, $utable);
            break;
         }
      }
   }

   function tableInfoViaUserTableID($lTableID, &$tableInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $tableInfo = null;
      foreach ($this->schema as $utable){
         if ($utable->lTableID == $lTableID){
            $this->loadTable($tableInfo, $utable);
            break;
         }
      }
   }

   private function loadTable(&$tableInfo, &$utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $tableInfo = new stdClass;

      $tableInfo->lTableID             = $utable->lTableID;
      $tableInfo->strUserTableName     = $utable->strUserTableName;
      $tableInfo->strDataTableName     = $utable->strDataTableName;
      $tableInfo->enumAttachType       = $utable->enumAttachType;
      $tableInfo->bMultiEntry          = $utable->bMultiEntry;
      $tableInfo->bHidden              = $utable->bHidden;
      $tableInfo->bCollapsibleHeadings = $utable->bCollapsibleHeadings;
      $tableInfo->bCollapseDefaultHide = $utable->bCollapseDefaultHide;
      $tableInfo->lPermissions         = $utable->lPermissions;
      $tableInfo->strFieldPrefix       = $utable->strFieldPrefix;
      $tableInfo->strDataTableKeyID    = $utable->strDataTableKeyID;
      $tableInfo->strDataTableFID      = $utable->strDataTableFID;
   }

   function fieldInfoViaUserFieldName($lTableID, $strUFieldName, &$fieldInfo, $bFreakOutIfNotFound = false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $fieldInfo = null;
      foreach ($this->schema[$lTableID]->fields as $ufield){
         if ($ufield->strFieldNameUser == $strUFieldName){
            $this->loadField($fieldInfo, $ufield);
            break;
         }
      }
      if ($bFreakOutIfNotFound && is_null($fieldInfo)){
         screamForHelp($strUFieldName.': field not found<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }
   }

   function lFieldIdxViaUserFieldName($lTableID, $strUFieldName, $bFreakOutIfNotFound = false){
   //---------------------------------------------------------------------
   // return index in the fields array corresponding to user's field name
   //---------------------------------------------------------------------
      $ut = &$this->schema[$lTableID];  // hook 'em
      $lCnt = count($ut->fields);

      for ($idx=0; $idx < $lCnt; ++$idx){
         if ($ut->fields[$idx]->strFieldNameUser == $strUFieldName){
            return($idx);
            break;
         }
      }
      if ($bFreakOutIfNotFound){
         screamForHelp('"'.$strUFieldName.'": field not found<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }else {
         return(null);
      }
   }

   function lFieldIdxViaFieldID($lTableID, $lFieldID, $bFreakOutIfNotFound = false){
   //---------------------------------------------------------------------
   // return index in the fields array corresponding to field ID
   //---------------------------------------------------------------------
      $ut = &$this->schema[$lTableID];  // hook 'em
      $lCnt = count($ut->fields);

      for ($idx=0; $idx < $lCnt; ++$idx){
         if ($ut->fields[$idx]->lFieldID == $lFieldID){
            return($idx);
            break;
         }
      }
      if ($bFreakOutIfNotFound){
         screamForHelp('"'.$lFieldID.'": field ID not found<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }else {
         return(null);
      }
   }

   function fieldsEssentialsViaFieldIDX($lTableID, $lFieldIDX,
                  &$lFieldID, &$lSortIDX, &$strFNInternal, &$strFNUser,
                  &$enumFType, &$strFieldNotes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $ut = &$this->schema[$lTableID]->fields[$lFieldIDX];  // hook 'em

      $lFieldID      = $ut->lFieldID;
      $lSortIDX      = $ut->lSortIDX;
      $strFNInternal = $ut->strFieldNameInternal;
      $strFNUser     = $ut->strFieldNameUser;
      $enumFType     = $ut->enumFieldType;
      $strFieldNotes = $ut->strFieldNotes;
   }

   function tableEssentialsViaTableID($lTableID,
                  &$strUserTableName, &$strDataTableName,  &$enumAttachType,
                  &$strFieldPrefix,   &$strDataTableKeyID, &$strDataTableFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $ut = &$this->schema[$lTableID];  // hook 'em

      $strUserTableName  = $ut->strUserTableName;
      $strDataTableName  = $ut->strDataTableName;
      $enumAttachType    = $ut->enumAttachType;
      $strFieldPrefix    = $ut->strFieldPrefix;
      $strDataTableKeyID = $ut->strDataTableKeyID;
      $strDataTableFID   = $ut->strDataTableFID;
   }

   function lFieldIDViaUserFieldName($lTableID, $strUFieldName, $bFreakOutIfNotFound = true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lFieldID = null;
      foreach ($this->schema[$lTableID]->fields as $ufield){
         if ($ufield->strFieldNameUser == $strUFieldName){
            $lFieldID = $ufield->lFieldID;
            break;
         }
      }
      if ($bFreakOutIfNotFound && is_null($lFieldID)){
         screamForHelp($strUFieldName.': field not found<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }
      return($lFieldID);
   }

   function lFieldIDX_ViaUserFieldName($lTableID, $strUFieldName, $bFreakOutIfNotFound = true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lFieldIDX = null;
      foreach ($this->schema[$lTableID]->fields as $idx=>$ufield){
         if ($ufield->strFieldNameUser == $strUFieldName){
            $lFieldIDX = $idx;
            break;
         }
      }
      if ($bFreakOutIfNotFound && is_null($lFieldIDX)){
         screamForHelp($strUFieldName.': field not found<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }
      return($lFieldIDX);
   }


   function fieldInfoViaUserFieldID($lTableID, $lFieldID, &$fieldInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $fieldInfo = null;

      foreach ($this->schema[$lTableID]->fields as $ufield){
         if ($ufield->lFieldID == $lFieldID){
            $this->loadField($fieldInfo, $ufield);
            break;
         }
      }
   }

   function loadDDLValues($lTableID){
   //---------------------------------------------------------------------
   // if $lTableID is null, load ddl values for all tables
   //
   // ddl values returned in the table fields
   //---------------------------------------------------------------------
      if (is_null($lTableID)){
         foreach ($this->schema as $lTabID=>$utable){
            $this->loadSchemaDDLFields($this->schema[$lTabID]);
         }
      }else {
         $this->loadSchemaDDLFields($this->schema[$lTableID]);
      }
   }

   private function loadSchemaDDLFields(&$utable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lTableID = $utable->lTableID;
      foreach ($utable->fields as $field){
         if (($field->enumFieldType == CS_FT_DDL ) ||
             ($field->enumFieldType == CS_FT_DDLMULTI)){
            $this->loadDDLField($field->lFieldID, $field->lNumDDL, $field->ddlInfo);
         }
      }
   }
   
   public function lDDLItemIDViaUserName($lTableID, $lFieldIDX, $strUserName, $bFreakIfNotFound=true){
   //---------------------------------------------------------------------
   // caller must previously load tables and ddl entires.
   // sample calling sequence:
   //   $cSchema->loadUFSchemaViaAttachTypeUserTabName(CENUM_CONTEXT_VOLUNTEER, 'Patient Visit', $lTableID, true);
   //   $cSchema->loadDDLValues($lTableID);
   //   $lLocationIDX = $cSchema->lFieldIDX_ViaUserFieldName($lTableID, 'Location', true);
   //   $lDDLEntryID = $cSchema->lDDLItemIDViaUserName($lTableID, $lLocationIDX, 'Other', true);
   //---------------------------------------------------------------------    
      foreach ($this->schema[$lTableID]->fields[$lFieldIDX]->ddlInfo as $ddlEntry){
         if ($ddlEntry->strDDLEntry == $strUserName){
            return($ddlEntry->lKeyID);
         }
      }
      if ($bFreakIfNotFound){
         screamForHelp('tableID: '.$lTableID.' fieldIDX: '.$lFieldIDX.' '.$strUserName.': ddl entry not found<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }else {
         return(null);
      }   
   }

   public function loadDDLField($lFieldID, &$lNumDDL, &$ddlInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumDDL = 0;
      $ddlInfo = array();
      $sqlStr =
        "SELECT
            ufddl_lKeyID, ufddl_lSortIDX, ufddl_strDDLEntry
         FROM uf_ddl
         WHERE NOT ufddl_bRetired
            AND ufddl_lFieldID=$lFieldID
         ORDER BY ufddl_lSortIDX, ufddl_lKeyID;";

      $query = $this->db->query($sqlStr);

      $lNumDDL = $query->num_rows();
      if ($lNumDDL > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $ddlInfo[$idx] = new stdClass;
            $ddl = &$ddlInfo[$idx];
            $ddl->lKeyID      = (int)$row->ufddl_lKeyID;
            $ddl->lSortIDX    = (int)$row->ufddl_lSortIDX;
            $ddl->strDDLEntry = $row->ufddl_strDDLEntry;
            ++$idx;
         }
      }
   }

   function syncDDLLists(&$field1, $strSyncFN1,
                         &$field2, $strSyncFN2, &$bInSync,
                         &$strOutSync1, &$strOutSync2){
   //---------------------------------------------------------------------
   // sync up the values in two ddl lists. Assign a pointer between
   // the two lists; return bInSync true if both lists contain identical
   // values
   //
   // caller should first call loadSchemaDDLFields(&$utable) to
   // preload the ddl field values
   //---------------------------------------------------------------------
      $bInSync = false;
      $strOutSync1 = array(); $strOutSync2 = array();

      if (($field1->lNumDDL == 0) || ($field2->lNumDDL == 0)) return;

      $this->syncDDL_InitField($field1, $strSyncFN1);
      $this->syncDDL_InitField($field2, $strSyncFN2);
      $this->syncDDL_A_2_B($field1, $strSyncFN1, $field2, $strSyncFN2);
      $this->syncDDL_A_2_B($field2, $strSyncFN2, $field1, $strSyncFN1);
      $bInSync = $this->syncDDL_NoNulls($field1, $strSyncFN1) &&
                 $this->syncDDL_NoNulls($field2, $strSyncFN2);
      if (!$bInSync){
         $this->syncDDL_errorFields($field1, $strSyncFN1, $strOutSync1);
         $this->syncDDL_errorFields($field2, $strSyncFN2, $strOutSync2);
      }
   }

   private function syncDDL_errorFields($field, $strSyncFN, &$strOutSync){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($field->ddlInfo as $ddlEntry){
         if (is_null($ddlEntry->$strSyncFN)){
            $strOutSync[] = $ddlEntry->strDDLEntry;
         }
      }
   }

   private function syncDDL_InitField(&$field, $strSyncFN){
      foreach ($field->ddlInfo as $ddlEntry){
         $ddlEntry->$strSyncFN = null;
      }
   }

   private function syncDDL_NoNulls($field, $strSyncFN){
      foreach ($field->ddlInfo as $ddlEntry){
         if (is_null($ddlEntry->$strSyncFN)) return(false);
      }
      return(true);
   }

   private function syncDDL_A_2_B($field1, $strSyncFN1, $field2, $strSyncFN2){
      foreach ($field1->ddlInfo as $idx=>$ddlEntry1){
         if (is_null($ddlEntry1->$strSyncFN1)){
            foreach ($field2->ddlInfo as $jidx=>$ddlEntry2){
               if (is_null($ddlEntry2->$strSyncFN2)){
                  if ($ddlEntry1->strDDLEntry == $ddlEntry2->strDDLEntry){
                     $ddlEntry1->$strSyncFN1 = $jidx;
                     $ddlEntry2->$strSyncFN2 = $idx;
                     break;
                  }
               }
            }
         }
      }
   }

   private function loadField(&$fieldInfo, &$ufield){
      $fieldInfo = new stdClass;

      $fieldInfo->lFieldID              = $ufield->lFieldID;
      $fieldInfo->lSortIDX              = $ufield->lSortIDX;
      $fieldInfo->strFieldNameInternal  = $ufield->strFieldNameInternal;
      $fieldInfo->strFieldNameUser      = $ufield->strFieldNameUser;
      $fieldInfo->strFieldNotes         = $ufield->strFieldNotes;
      $fieldInfo->enumFieldType         = $ufield->enumFieldType;
      $fieldInfo->bRequired             = $ufield->bRequired;
      $fieldInfo->bConfigured           = $ufield->bConfigured;
      $fieldInfo->bCheckDef             = $ufield->bCheckDef;
      $fieldInfo->curDef                = $ufield->curDef;
      $fieldInfo->strTxtDef             = $ufield->strTxtDef;
      $fieldInfo->lDef                  = $ufield->lDef;
      $fieldInfo->lDDLDefault           = $ufield->lDDLDefault;
      $fieldInfo->lCurrencyACO          = $ufield->lCurrencyACO;
   }

   function eTableFieldInfo(&$efields, $strFieldPrefix, $bViaIDX=false){
   //---------------------------------------------------------------------
   // this routine enhances the $fieldInfo array with the predefined
   // enrollment record fields. It can be called before or after
   // fieldInfoViaUserFieldName($lTableID,...
   //---------------------------------------------------------------------
      $this->setSingleEAField($bViaIDX, $efields, $strFieldPrefix.'_lKeyID',             'lKeyID',             CS_FT_INTEGER);
      $this->setSingleEAField($bViaIDX, $efields, $strFieldPrefix.'_lForeignKey',        'lForeignKey',        CS_FT_INTEGER);
      $this->setSingleEAField($bViaIDX, $efields, $strFieldPrefix.'_bRetired',           'bRetired',           CS_FT_CHECKBOX);
      $this->setSingleEAField($bViaIDX, $efields, $strFieldPrefix.'_lOriginID',          'lOriginID',          CS_FT_INTEGER);
      $this->setSingleEAField($bViaIDX, $efields, $strFieldPrefix.'_dteOrigin',          'dteOrigin',          CS_FT_DATETIME);
      $this->setSingleEAField($bViaIDX, $efields, $strFieldPrefix.'_lLastUpdateID',      'lLastUpdateID',      CS_FT_INTEGER);
      $this->setSingleEAField($bViaIDX, $efields, $strFieldPrefix.'_dteLastUpdate',      'dteLastUpdate',      CS_FT_DATETIME);
      $this->setSingleEAField($bViaIDX, $efields, $strFieldPrefix.'_dteStart',           'dteStart',           CS_FT_DATE);
      $this->setSingleEAField($bViaIDX, $efields, $strFieldPrefix.'_dteEnd',             'dteEnd',             CS_FT_DATE);
      $this->setSingleEAField($bViaIDX, $efields, $strFieldPrefix.'_bCurrentlyEnrolled', 'bCurrentlyEnrolled', CS_FT_CHECKBOX);
   }

   function aTableFieldInfo(&$afields, $strFieldPrefix, $bViaIDX=false){
   //---------------------------------------------------------------------
   // this routine enhances the $fieldInfo array with the predefined
   // attendance record fields. It can be called before or after
   // fieldInfoViaUserFieldName($lTableID,...
   //---------------------------------------------------------------------
      $this->setSingleEAField($bViaIDX, $afields, $strFieldPrefix.'_lKeyID',         'lKeyID',               CS_FT_INTEGER);
      $this->setSingleEAField($bViaIDX, $afields, $strFieldPrefix.'_lForeignKey',    'lForeignKey',          CS_FT_INTEGER);
      $this->setSingleEAField($bViaIDX, $afields, $strFieldPrefix.'_bRetired',       'bRetired',             CS_FT_CHECKBOX);
      $this->setSingleEAField($bViaIDX, $afields, $strFieldPrefix.'_lOriginID',      'lOriginID',            CS_FT_INTEGER);
      $this->setSingleEAField($bViaIDX, $afields, $strFieldPrefix.'_dteOrigin',      'dteOrigin',            CS_FT_DATETIME);
      $this->setSingleEAField($bViaIDX, $afields, $strFieldPrefix.'_lLastUpdateID',  'lLastUpdateID',        CS_FT_INTEGER);
      $this->setSingleEAField($bViaIDX, $afields, $strFieldPrefix.'_dteLastUpdate',  'dteLastUpdate',        CS_FT_DATETIME);
      $this->setSingleEAField($bViaIDX, $afields, $strFieldPrefix.'_lEnrollID',      'lEnrollID',            CS_FT_INTEGER);
      $this->setSingleEAField($bViaIDX, $afields, $strFieldPrefix.'_dteAttendance',  'dteAttendance',        CS_FT_DATETIME);
      $this->setSingleEAField($bViaIDX, $afields, $strFieldPrefix.'_dDuration',      'dDuration',            CS_FT_DECIMAL);
      $this->setSingleEAField($bViaIDX, $afields, $strFieldPrefix.'_strCaseNotes',   'strCaseNotes',         CS_FT_TEXTLONG);
   }

   private function setSingleEAField($bViaIDX, &$fArray, $strDBFN, $strUserFN, $enumType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bViaIDX){
         $lCnt = count($fArray);
         $fArray[$lCnt] = new stdClass;
         $fieldInfo = &$fArray[$lCnt];
      }else {
         $fArray[$strUserFN] = new stdClass;
         $fieldInfo = &$fArray[$strUserFN];
      }

      $fieldInfo->lFieldID              = null;
      $fieldInfo->lSortIDX              = null;
      $fieldInfo->strFieldNameInternal  = $strDBFN;
      $fieldInfo->strFieldNameUser      = $strUserFN;
      $fieldInfo->strFieldNotes         = '';   // pending
      $fieldInfo->enumFieldType         = $enumType;
      $fieldInfo->bRequired             = true;
      $fieldInfo->bConfigured           = true;
      $fieldInfo->bCheckDef             = false;
      $fieldInfo->curDef                = 0.0;
      $fieldInfo->strTxtDef             = '';
      $fieldInfo->lDef                  = 0;
      $fieldInfo->lDDLDefault           = -1;
      $fieldInfo->lCurrencyACO          = 1;
   }

   function sqlViaTableFields($lTableID, $parentTable, &$sqlParts,
                $strFNPrefix='', $bExcludeDDL=false, $bExcludeMultiDDL=false,
                $bIncludeHidden=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlParts = new stdClass;
      $strFieldPrefix = $this->schema[$lTableID]->strFieldPrefix;
      $strFNForeignID = $this->schema[$lTableID]->strDataTableFID;
      $sqlParts->strSELECT = $this->schema[$lTableID]->strDataTableKeyID.', '
                  .$strFNForeignID.' ';
      $sqlParts->lNumRSFields = 0;
      $sqlParts->rsFields = array();


      if ($this->schema[$lTableID]->lNumFields > 0){
         $fields = &$this->schema[$lTableID]->fields;
         foreach ($fields as $field){

            $enumFT = $field->enumFieldType;
            $bAddField = true;
            if ($field->bHidden && !$bIncludeHidden){
               $bAddField = false;
            }elseif ($enumFT == CS_FT_HEADING){
               $bAddField = false;
            }elseif ($enumFT == CS_FT_DDL){
               if ($bExcludeDDL) $bAddField = false;
            }elseif ($enumFT == CS_FT_DDLMULTI){
               if ($bExcludeMultiDDL) $bAddField = false;
            }
            if ($bAddField){
               $sqlParts->strSELECT .= ", \n".$field->strFieldNameInternal.' AS `'.$strFNPrefix.strEscMysqlQuote($field->strFieldNameUser).'`';
               $sqlParts->rsFields[$sqlParts->lNumRSFields] = new stdClass;
               $spfield = &$sqlParts->rsFields[$sqlParts->lNumRSFields];
               $spfield->strFieldNameInternal = $field->strFieldNameInternal;
               $spfield->lFieldID             = $field->lFieldID;
               $spfield->strFieldNameUser     = $field->strFieldNameUser;
               ++$sqlParts->lNumRSFields;
            }
         }
      }

      $sqlParts->strINNER =
             ' INNER JOIN '.$this->schema[$lTableID]->strDataTableName." ON $parentTable->strFNKeyID = $strFNForeignID ";
   }

}
