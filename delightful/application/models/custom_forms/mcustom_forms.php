<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2013-2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('custom_forms/mcustom_forms', 'cForm');
--------------------------------------------------------------------

---------------------------------------------------------------------*/


class mcustom_forms extends CI_Model{
   public
       $customForms, $lNumCustomForms,
       $strWhereExtra, $strOrder;

   public $lNumTables, $utables;

   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->customForms = null;
      $this->lNumCustomForms = 0;
      $this->strWhereExtra  = $this->strOrder = '';

      $this->lNumTables = 0;
      $this->utables    = null;
   }

   function loadCustomFormsViaCFID($lCFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = " AND cf_lKeyID=$lCFormID ";
      $this->loadCustomForms();
   }

   function loadCustomFormsViaType($enumType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = ' AND cf_enumContextType= '.strPrepStr($enumType).' ';
      $this->loadCustomForms();
   }

   function loadCustomForms(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->customForms = array();

      $clsUF = new muser_fields;
      $cperms = new mpermissions;

      if ($this->strOrder == ''){
         $strOrder = ' cf_strFormName, cf_lKeyID ';
      }else {
         $strOrder = $this->strOrder;
      }
      $sqlStr = "
         SELECT
            cf_lKeyID, cf_strFormName, cf_strDescription,
            cf_strIntro, cf_strSubmissionText, cf_strBannerTitle,
            cf_strContact, cf_enumContextType,
            cf_bCreateNewParent, cf_lParentGroupID,
            cf_strVerificationModule, cf_strVModEntryPoint,

            cf_bRetired, cf_lOriginID, cf_lLastUpdateID,

            UNIX_TIMESTAMP(cf_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(cf_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName
         FROM custom_forms
            INNER JOIN admin_users   AS uc ON uc.us_lKeyID=cf_lOriginID
            INNER JOIN admin_users   AS ul ON ul.us_lKeyID=cf_lLastUpdateID

         WHERE NOT cf_bRetired $this->strWhereExtra
         ORDER BY $strOrder;";

      $query = $this->db->query($sqlStr);
      $this->lNumCustomForms = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->customForms[0] = new stdClass;
         $cform = &$this->customForms[0];
         $cform->lKeyID                =
         $cform->strFormName           =
         $cform->strDescription        =
         $cform->strIntro              =
         $cform->strSubmissionText     =
         $cform->strBannerTitle        =
         $cform->strContact            =
         $cform->enumContextType       =
         $cform->lParentGroupID        = null;

         $cform->strVerificationModule =
         $cform->strVModEntryPoint     =

         $cform->bRetired              =

         $cform->lOriginID             =
         $cform->lLastUpdateID         =
         $cform->dteOrigin             =
         $cform->dteLastUpdate         =
         $cform->strUCFName            =
         $cform->strUCLName            =
         $cform->strULFName            =
         $cform->strULLName            = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->customForms[$idx] = new stdClass;
            $cform = &$this->customForms[$idx];

            $cform->lKeyID                = $lCFID = (int)$row->cf_lKeyID;
            $cform->strFormName           = $row->cf_strFormName;
            $cform->strDescription        = $row->cf_strDescription;
            $cform->strIntro              = $row->cf_strIntro;
            $cform->strSubmissionText     = $row->cf_strSubmissionText;
            $cform->strBannerTitle        = $row->cf_strBannerTitle;
            $cform->strContact            = $row->cf_strContact;
            $cform->enumContextType       = $row->cf_enumContextType;
            $cform->lParentGroupID        = (int)$row->cf_lParentGroupID;

            $cform->strVerificationModule = $row->cf_strVerificationModule;
            $cform->strVModEntryPoint     = $row->cf_strVModEntryPoint;

            $cform->bRetired              = (boolean)$row->cf_bRetired;

            $cform->lOriginID             = (int)$row->cf_lOriginID;
            $cform->lLastUpdateID         = (int)$row->cf_lLastUpdateID;
            $cform->dteOrigin             = (int)$row->dteOrigin;
            $cform->dteLastUpdate         = (int)$row->dteLastUpdate;
            $cform->strUCFName            = $row->strUCFName;
            $cform->strUCLName            = $row->strUCLName;
            $cform->strULFName            = $row->strULFName;
            $cform->strULLName            = $row->strULLName;

            $cform->bAnyTablesMulti       = false;

               // constituent tables and associated user-group permissions
            $cform->lNumPerms = 0;
            $this->loadPTablesForDisplay($lCFID, $clsUF, false);
            $cform->lNumTables = $lNumTables = $this->lNumTables;

            if ($lNumTables > 0){
               $cform->utables = arrayCopy($this->utables);
               $cform->tableIDs = array();
               foreach ($cform->utables as $utable){
                  $cform->tableIDs[] = $utable->lTableID;
                  if ($utable->bMultiEntry) $cform->bAnyTablesMulti = true;
               }
               $cperms->tablePerms($cform->tableIDs, $cform->lNumPerms, $cform->perms);
               $cperms->consolidateTablePerms($cform->lNumPerms, $cform->perms,
                                     $cform->lNumConsolidated, $cform->cperms);
            }
            ++$idx;
         }
      }
   }

/*
error - using the CFID instead of the table IDs
   function userGroupsViaCFormID($lCFID, &$userGroups){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $userGroups = new stdClass;
      $sqlStr =
           "SELECT
               gc_lKeyID, gc_lGroupID, gp_strGroupName
            FROM groups_child
               INNER JOIN groups_parent ON gp_lKeyID=gc_lGroupID
            WHERE gc_lForeignID=$lCFID
               AND gp_enumGroupType = ".strPrepStr(CENUM_CONTEXT_USER).'
               AND gc_enumSubGroup  = '.strPrepStr(CENUM_CONTEXT_PTABLE).'
            ORDER BY gp_strGroupName, gc_lGroupID, gc_lKeyID;';
      $query = $this->db->query($sqlStr);

      $userGroups->lNumUserGroups = $numRows = $query->num_rows();
      if ($numRows > 0) {
         $idx = 0;
         $userGroups->groups = array();
         foreach ($query->result() as $row){
            $userGroups->groups[$idx] = new stdClass;
            $group = &$userGroups->groups[$idx];
            $group->lGroupChildID = $row->gc_lKeyID;
            $group->lGroupID      = $row->gc_lGroupID;
            $group->strGroupName  = $row->gp_strGroupName;
            ++$idx;
         }
      }
   }
*/
   function bShowRequiredUFFields($lCIFormID, $lTableID, $lFieldID, &$bShow, &$bRequired){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bShow = $bRequired = false;

      $sqlStr =
        "SELECT cfuf_bRequired
         FROM custom_form_uf
         WHERE cfuf_lCFormID=$lCIFormID
            AND cfuf_lTableID=$lTableID
            AND cfuf_lFieldID=$lFieldID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() != 0){
         $row = $query->row();
         $bShow = true;
         $bRequired = $row->cfuf_bRequired;
      }
   }

   function strPublicUFTable($lTableID, $lFormID, $strDefault){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT cftl_strLabel
         FROM custom_form_table_labels
         WHERE cftl_lCFormID = $lFormID
            AND cftl_lTableID=$lTableID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return($strDefault);
      }else {
         $row = $query->row();
         return($row->cftl_strLabel);
      }
   }

   function loadPTablesForDisplay($lRegFormID, &$clsUF, $bLoadFields=true){
   //---------------------------------------------------------------------
   // requires       $this->load->model  ('personalization/muser_fields', 'clsUF');
   //---------------------------------------------------------------------
      $this->utables = array();

      $sqlStr =
           "SELECT DISTINCT
               cfuf_lCFormID, cfuf_lTableID,
               pft_strUserTableName, pft_strDataTableName, pft_bMultiEntry
            FROM custom_form_uf
               INNER JOIN uf_tables ON cfuf_lTableID=pft_lKeyID

            WHERE cfuf_lCFormID=$lRegFormID
               AND NOT pft_bRetired AND NOT pft_bHidden
            ORDER BY pft_strUserTableName, cfuf_lTableID;";

      $query = $this->db->query($sqlStr);
      $this->lNumTables = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->utables[0] = new stdClass;
         $utable = &$this->utables[0];

         $utable->lRegFormID        =
         $utable->lTableID          =
         $utable->strUserTableName  =
         $utable->strDataTableName  =
         $utable->bMultiEntry       =
         $utable->ufields           = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->utables[$idx] = new stdClass;
            $utable = &$this->utables[$idx];

            $utable->lCFID             = $row->cfuf_lCFormID;
            $utable->lTableID          = $lTableID = $row->cfuf_lTableID;
            $utable->strUserTableName  = $row->pft_strUserTableName;
            $utable->strDataTableName  = $row->pft_strDataTableName;
            $utable->bMultiEntry       = $row->pft_bMultiEntry;
            $utable->strFieldPrefix    = $clsUF->strGenUF_KeyFieldPrefix($lTableID);

            if ($bLoadFields){
               $utable->lNumFields     = 0;  // note: if not initialized, the class variable won't
                                             // be created in call to loadTableFieldsForDisplay
               $utable->ufields = array();
               $this->loadTableFieldsForDisplay(
                                       $utable->ufields, $utable->lNumFields, $utable->lCFID,
                                       $utable->lTableID);
            }
            ++$idx;
         }
      }
   }

   private function loadTableFieldsForDisplay(&$ufields, &$lNumFields, $lCFID, $lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumFields = 0;

      $sqlStr =
           "SELECT
               cfuf_lKeyID,
               cfuf_lFieldID, cfuf_bRequired, cfuf_strLabel,
               pff_lKeyID, pff_lTableID, pff_lSortIDX, pff_strFieldNameInternal,
               pff_strFieldNameUser, pff_strFieldNotes, pff_enumFieldType, pff_bConfigured,
               pff_lCurrencyACO,

               pff_bCheckDef, pff_curDef, pff_strTxtDef, pff_lDef, pff_lDDLDefault

            FROM custom_form_uf
               INNER JOIN uf_fields ON cfuf_lFieldID=pff_lKeyID

            WHERE cfuf_lCFormID=$lCFID
               AND cfuf_lTableID=$lTableID
               AND NOT pff_bHidden
            ORDER BY pff_lSortIDX, pff_strFieldNameUser, cfuf_lFieldID;";


      $query = $this->db->query($sqlStr);
      $lNumFields = $query->num_rows();

      if ($lNumFields > 0) {
         $idx = 0;
         foreach ($query->result() as $row) {
            $ufields[$idx] = new stdClass;
            $ufield = &$ufields[$idx];
            
               // compatibility issue...
            $ufield->lKeyID               = $ufield->pff_lKeyID = (int)$row->cfuf_lKeyID;
            $ufield->lFieldID             = (int)$row->cfuf_lFieldID;
            $ufield->bRequired            = (bool)$row->cfuf_bRequired;
            $ufield->strLabel             = $row->cfuf_strLabel;
            $ufield->lTableID             = (int)$row->pff_lTableID;
            $ufield->lSortIDX             = (int)$row->pff_lSortIDX;
            $ufield->strFieldNameInternal = $row->pff_strFieldNameInternal;
            $ufield->strFieldNameUser     = $row->pff_strFieldNameUser;
            $ufield->strFieldNotes        = $row->pff_strFieldNotes;
            $ufield->enumFieldType        = $row->pff_enumFieldType;
            $ufield->bConfigured          = (bool)$row->pff_bConfigured;
            $ufield->lCurrencyACO         = (int)$row->pff_lCurrencyACO;

               // defaults
            $ufield->bCheckDef            = $row->pff_bCheckDef;
            $ufield->curDef               = $row->pff_curDef;
            $ufield->strTxtDef            = $row->pff_strTxtDef;
            $ufield->lDef                 = $row->pff_lDef;
            $ufield->lDDLDefault          = $row->pff_lDDLDefault;

            ++$idx;
         }
      }
   }

   function strCustomFormsPageTitleAddEdit($enumType, $strLastLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch($enumType){
         case CENUM_CONTEXT_CLIENT:
            $strPageTitle =
                        anchor('main/menu/client', 'Clients',   'class="breadcrumb"')
                 .' | '.anchor('custom_forms/custom_form_add_edit/view/'.CENUM_CONTEXT_CLIENT, 'Custom Forms', 'class="breadcrumb"')
                 .' | '.htmlspecialchars($strLastLabel);
            break;
         default:
            screamForHelp($enumType.': Custom forms not available nyet.<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strPageTitle);
   }


   function strBlockLinksToCForms($enumType, $lParentID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadCustomFormsViaType($enumType);
      if ($this->lNumCustomForms == 0){
         return('');
      }

      $strOut = '';
      foreach ($this->customForms as $cForm){
         $lCFID = $cForm->lKeyID;
         $strOut .= strLinkAdd_CustomFormDataEntry($lCFID, $lParentID, 'Open form', true).'&nbsp;'
                   .strLinkAdd_CustomFormDataEntry($lCFID, $lParentID, 'Open form', false).'&nbsp;'
                   .'<b>'.htmlspecialchars($cForm->strFormName).'</b><br>';
      }
      return($strOut);
   }


   function tablesIDsInCForm($lCFID, &$lNumTables, &$tableIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $tableIDs = array();

      $sqlStr =
           "SELECT DISTINCT cfuf_lTableID
            FROM custom_form_uf
               INNER JOIN uf_tables ON cfuf_lTableID=pft_lKeyID
            WHERE cfuf_lCFormID=$lCFID
               AND NOT pft_bRetired AND NOT pft_bHidden
            ORDER BY pft_strUserTableName, cfuf_lTableID;";

      $query = $this->db->query($sqlStr);
      $lNumTables = $query->num_rows();

      if ($lNumTables > 0) {
         foreach ($query->result() as $row) {
            $tableIDs[] = $row->cfuf_lTableID;
         }
      }
   }




      /* ----------------------------------------------------------
              C U S T O M   F O R M   L O G
         ---------------------------------------------------------- */

   function lLogFormSave($lForeignID, $lCFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         "INSERT INTO custom_form_log
          SET
             cfl_lCFormID   = $lCFID,
             cfl_lForeignID = $lForeignID,
             cfl_lOriginID  = $glUserID,
             cfl_dteOrigin  = NOW();";
      $query = $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function formLogViaCFID_FID($lCFID, $lFID, &$lNumLogEntries, &$formLog){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlQualWhere =
         " AND cfl_lCFormID=$lCFID AND cfl_lForeignID=$lFID ";

      $this->formLog($sqlQualWhere, '', $lNumLogEntries, $formLog);
   }

   function formLog($sqlQualWhere, $sqlOrder, &$lNumLogEntries, &$formLog){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumLogEntries = 0;
      $formLog = array();

      if ($sqlOrder == ''){
         $strOrder = ' cfl_dteOrigin, cfl_lKeyID ';
      }else {
         $strOrder = $sqlOrder;
      }

      $sqlStr =
        "SELECT cfl_lKeyID, cfl_lCFormID, cfl_lForeignID, cfl_lOriginID,
            UNIX_TIMESTAMP(cfl_dteOrigin) AS dteOrigin,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName
         FROM custom_form_log
            INNER JOIN admin_users AS uc ON uc.us_lKeyID = cfl_lOriginID
         WHERE 1 $sqlQualWhere
         ORDER BY $strOrder;";
      $query = $this->db->query($sqlStr);
      $lNumLogEntries = $query->num_rows();
      if ($lNumLogEntries > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $formLog[$idx] = new stdClass;
            $fl = &$formLog[$idx];

            $fl->lLogID      = $row->cfl_lKeyID;
            $fl->lCFormID    = $row->cfl_lCFormID;
            $fl->lForeignID  = $row->cfl_lForeignID;
            $fl->lOriginID   = $row->cfl_lOriginID;
            $fl->dteOrigin   = $row->dteOrigin;
            $fl->strUCFName  = $row->strUCFName;
            $fl->strUCLName  = $row->strUCLName;

            ++$idx;
         }
      }
   }

}



