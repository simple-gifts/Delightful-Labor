<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014 by Database Austin
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//---------------------------------------------------------------------
// Functions to support the User-Defined Personalized Fields
---------------------------------------------------------------------
      $this->load->model('personalization/muser_fields',        'clsUF');
      $this->load->model('personalization/muser_fields_create', 'clsUFC');
      $this->load->model('personalization/muser_clone',         'cUFClone');
---------------------------------------------------------------------*/

class muser_clone extends CI_Model{

   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();
   }

   function cloneUTable($attr){
   //---------------------------------------------------------------------
   // destination table must exist prior to call
   //---------------------------------------------------------------------
      $csrcTable  = new muser_fields_create;
      $cdestTable = new muser_fields_create;

         // load source table record
      $csrcTable->lTableID = $attr->lSrcTableID;
      $csrcTable->loadTableViaTableID(false);
      $srcTable = &$csrcTable->userTables[0];

         // load source table fields
      $csrcTable->loadTableFields(true);
      $srcFields = &$csrcTable->fields;

      if ($csrcTable->lNumFields == 0) return;  // nothing to clone

         // the destination table
      $cdestTable->lTableID = $attr->lDestTableID;
      $cdestTable->strENPTableName = $cdestTable->strGenUF_TableName($attr->lDestTableID);
      $cdestTable->fields = array();
      $cdestTable->fields[0] = new stdClass;
      $destField = &$cdestTable->fields[0];
      foreach ($csrcTable->fields as $sfield){
         $lSrcFieldID = $sfield->pff_lKeyID;

         $destField->enumFieldType        = $enumFieldType = $sfield->enumFieldType;
         $destField->pff_strFieldNameUser = $sfield->pff_strFieldNameUser;
         $destField->pff_bCheckDef        = $sfield->pff_bCheckDef;
         $destField->pff_curDef           = $sfield->pff_curDef;
         $destField->pff_strTxtDef        = $sfield->pff_strTxtDef;
         $destField->pff_lDef             = $sfield->pff_lDef;
         $destField->pff_lCurrencyACO     = $sfield->pff_lCurrencyACO;
         $destField->pff_bHidden          = $sfield->pff_bHidden;
         $destField->pff_bRequired        = $sfield->pff_bRequired;
         $destField->bPrefilled           = $sfield->bPrefilled;

         $destField->strFieldNotes        = $sfield->strFieldNotes;

         $lDestFieldID = $cdestTable->addNewField();

            // map the sort order
         $cdestTable->setUFieldSortIDX($lDestFieldID, $sfield->lSortIDX);

            // clone the drop-down list entries
         if ($enumFieldType == CS_FT_DDL || $enumFieldType == CS_FT_DDLMULTI){
            $this->cloneUFDDL($lSrcFieldID, $lDestFieldID);
            $cdestTable->setDDL_asConfigured($lDestFieldID);
         }
      }
   }

   function cloneUFDDL($lSrcFieldID, $lDestFieldID){
   //---------------------------------------------------------------------
   // my hands never leave my wrists....
   //---------------------------------------------------------------------
      $sqlStr =
        "INSERT INTO uf_ddl
           (ufddl_lFieldID,
            ufddl_lSortIDX,
            ufddl_bRetired,
            ufddl_strDDLEntry)
         SELECT
            $lDestFieldID,
            ufddl_lSortIDX,
            ufddl_bRetired,
            ufddl_strDDLEntry
         FROM uf_ddl
         WHERE ufddl_lFieldID=$lSrcFieldID;";
      $query = $this->db->query($sqlStr);
   }

   function cloneCProgram($lCProgSourceID, $lCProgDestID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cdest = new muser_fields_create;

      $ccpSource = new mcprograms;
      $ccpDest   = new mcprograms;

      $ccpSource->loadClientProgramsViaCPID($lCProgSourceID);
      $cpSource = &$ccpSource->cprogs[0];

      $ccpDest->loadClientProgramsViaCPID($lCProgDestID);
      $cpDest = &$ccpDest->cprogs[0];

      $lETableSrcID = $cpSource->lEnrollmentTableID;
      $lATableSrcID = $cpSource->lAttendanceTableID;

      $lETableDestID = $cpDest->lEnrollmentTableID;
      $lATableDestID = $cpDest->lAttendanceTableID;

         // clone the enrollment table
      $attr = new stdClass;

      $attr->lSrcTableID  = $lETableSrcID;
      $attr->lDestTableID = $lETableDestID;
      $this->cloneUTable($attr);

         // clone the attendance table; first remove the new
         // activity DDL field (it will be cloned appropriately)
      $cdest->loadSingleField($cpDest->lActivityFieldID);
      $cdest->strENPTableName = $cpDest->strAttendanceTable;
      $cdest->removeField();

      $attr->lSrcTableID  = $lATableSrcID;
      $attr->lDestTableID = $lATableDestID;
      $this->cloneUTable($attr);

         //--------------------------------------------------
         // now identify the new activity ddl, and set
         // that field name in the parent Client Program
         // record
         //--------------------------------------------------
         // this is a bit kludgy - the activity field will be
         // the first ddl field in the attendance table
         //--------------------------------------------------
      $sqlStr =
        "SELECT pff_lKeyID
         FROM uf_fields
         WHERE pff_lTableID=$lATableDestID
            AND pff_enumFieldType='DDL'
         ORDER BY pff_lKeyID
         LIMIT 0,1;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() != 1){
         screamForHelp($lATableDestID.': Attendance table clone failure: unable to detect the activity DDL in cloned table<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }
      $row = $query->row();
      $lActivityFieldID = (int)$row->pff_lKeyID;

      $sqlStr =
        "UPDATE cprograms
         SET cp_lActivityFieldID=$lActivityFieldID
         WHERE cp_lKeyID=$lCProgDestID;";
      $query = $this->db->query($sqlStr);
   }

   function cloneAttendance($cloneOpts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $cschema = new muser_schema;
      $cschema->loadUFSchemaSingleTable($cloneOpts->lATableID);
      $atable = &$cschema->schema[$cloneOpts->lATableID];

         // build the personalized field list
      $strUFields = '';
      $lNumMulti = 0;
      $multi = array();
      if ($atable->lNumFields > 0){
         foreach ($atable->fields as $afield){
            if ($afield->enumFieldType != CS_FT_HEADING){
               $strUFields .= ', '.$afield->strFieldNameInternal."\n";
            }
            if ($afield->enumFieldType == CS_FT_DDLMULTI){
               $multi[$lNumMulti] = new stdClass;
               $mddl = &$multi[$lNumMulti];
               $mddl->lFieldID = $afield->lFieldID;
               ++$lNumMulti;
            }
         }
      }

      $strFP = $atable->strFieldPrefix;
      $sqlBase =
         "INSERT INTO $atable->strDataTableName
         (
            $strFP"."_bRetired,
            $strFP"."_lOriginID,
            $strFP"."_dteOrigin,
            $strFP"."_lLastUpdateID,
            $strFP"."_dteLastUpdate,
            $strFP"."_dteAttendance,
            $strFP"."_dDuration,
            $strFP"."_strCaseNotes
            $strUFields,
            $strFP"."_lEnrollID,
            $strFP"."_lForeignKey
         )

         SELECT
            '0'       AS bRetired,
            $glUserID AS lOriginID,
            NOW()     AS dteOrigin,
            $glUserID AS lLastUpdateID,
            NOW()     AS dteLastUpdate,
            '$cloneOpts->mdteAttendance' AS dteAttendance,
            $strFP"."_dDuration,
            $strFP"."_strCaseNotes
            $strUFields, ";

      $arecIDs = array();
      foreach ($cloneOpts->clients as $IDs){
         if (!$cloneOpts->bSkipDups ||
             !$this->bClientAsAttendanceOnDate(
                          $IDs->lClientID, $cloneOpts->mdteAttendance, $atable->strFieldPrefix,
                          $atable->strDataTableName, $atable->strDataTableFID)){
            $sqlStr = $sqlBase .
               "$IDs->lEnrollID AS lEnrollID,
                $IDs->lClientID AS lForeignKey
                FROM $atable->strDataTableName
                WHERE $atable->strDataTableKeyID=$cloneOpts->lARecID;";
            $query = $this->db->query($sqlStr);
            $arecIDs[] = $this->db->insert_id();
         }
      }

         // clone multi-entry ddls
      if ($lNumMulti > 0 && count($arecIDs) > 0){
            // load the multi values
         foreach ($multi as $mddl){
            $mddl->selectValues = $this->loadMultiViaARec($mddl->lFieldID, $cloneOpts->lATableID, $cloneOpts->lARecID);
            if (count($mddl->selectValues) > 0){
               foreach ($arecIDs as $aID){
                  $this->cloneARecMDDL($mddl->selectValues, $aID, $mddl->lFieldID, $cloneOpts->lATableID);
               }
            }
         }
      }
   }

   function cloneARecMDDL($selectValues, $aID, $lFieldID, $lATableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($selectValues as $ddlID){
         $sqlStr =
           "INSERT INTO uf_ddl_multi
            SET
               pdm_lDDLID = $ddlID,
               pdm_lFieldID = $lFieldID,
               pdm_lUTableID = $lATableID,
               pdm_lUTableRecID=$aID";
         $query = $this->db->query($sqlStr);
      }
   }

   function loadMultiViaARec($lFieldID, $lATableID, $lARecID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $selects = array();
      $sqlStr =
        "SELECT pdm_lDDLID
         FROM uf_ddl_multi
         WHERE pdm_lFieldID = $lFieldID
            AND pdm_lUTableID = $lATableID
            AND pdm_lUTableRecID = $lARecID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() > 0){
         foreach ($query->result() as $row){
            $selects[] = $row->pdm_lDDLID;
         }
      }
      return($selects);
   }

   private function bClientAsAttendanceOnDate(
                              $lClientID, $mdteAttendance, $strFieldPrefix,
                              $strATableName, $strATableFID){
   //---------------------------------------------------------------------
   // return true if client has an attendance record on the specified
   // mySql date
   //---------------------------------------------------------------------
      $sqlStr =
           "SELECT COUNT(*) AS lNumRecs
            FROM $strATableName
            WHERE NOT $strFieldPrefix"."_bRetired
               AND $strATableFID  = $lClientID
               AND $strFieldPrefix"."_dteAttendance = ".strPrepStr($mdteAttendance).';';
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return(((int)$row->lNumRecs) > 0);
   }


 }
