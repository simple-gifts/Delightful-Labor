<?php
/*---------------------------------------------------------------------
// copyright (c) 2014 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model ('admin/mpermissions',           'perms');
      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->model ('personalization/muser_schema', 'cUFSchema');
      $this->load->model ('client_features/mcprograms',   'cprograms');
      $this->load->helper('dl_util/time_date');

      $this->load->model ('client_features/mcprog_data_recs', 'cprogdata');
---------------------------------------------------------------------*/

class mcprog_data_recs extends CI_Model{

      // client program vars
   public
      $ccprog, $cprog,
      $lCProgID,           $strProgramName,
      $lEnrollmentTableID, $lAttendanceTableID,
      $strATableFNPrefix,  $strETableFNPrefix,
      $strEnrollmentTable, $strAttendanceTable,
      $lActivityFieldID,   $strActivityFN;

   public
      $cschema,
      $etable,  $efields, $atable,  $afields, $lNumERecs, $erecs,
      $sqlWhereETable, $sqlOrderETable,
      $sqlWhereATable, $sqlOrderATable;

	function __construct(){
		parent::__construct();

      $this->cprog = null;

      $this->sqlWhereETable = $this->sqlOrderETable = '';
      $this->sqlWhereATable = $this->sqlOrderATable = '';
	}

   function load_cprog($lCProgID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load_cprogGeneric($lCProgID, null, true);
   }

   function load_cprogViaName($strCProgName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load_cprogGeneric(null, $strCProgName, false);
   }

   function load_cprogGeneric($lCProgID, $strCProgName, $bViaID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->ccprog = new mcprograms;
      if ($bViaID){
         $this->ccprog->loadClientProgramsViaCPID($lCProgID);
      }else {
         $this->ccprog->loadClientProgramsViaProgramName($strCProgName);
      }
      if ($this->ccprog->lNumCProgs == 0){
         screamForHelp($lCProgID.'/'.$strCProgName.': unable to load client program<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }

      $this->cprog     = &$this->ccprog->cprogs[0];

      $this->lCProgID           = $lCProgID     = $this->cprog->lKeyID;
      $this->strProgramName     = $strCProgName = $this->cprog->strProgramName;
      $this->lEnrollmentTableID = $this->cprog->lEnrollmentTableID;
      $this->lAttendanceTableID = $this->cprog->lAttendanceTableID;
      $this->strATableFNPrefix  = $this->cprog->strATableFNPrefix;
      $this->strETableFNPrefix  = $this->cprog->strETableFNPrefix;
      $this->strEnrollmentTable = $this->cprog->strEnrollmentTable;
      $this->strAttendanceTable = $this->cprog->strAttendanceTable;
      $this->lActivityFieldID   = $this->cprog->lActivityFieldID;
      $this->strActivityFN      = $this->cprog->strActivityFN;

      $this->cschema = new muser_schema;

         // Enrollment table schema
      $this->cschema->loadUFSchemaSingleTable($this->lEnrollmentTableID);
      $this->etable = &$this->cschema->schema[$this->lEnrollmentTableID];

         // Attendance table schema
      $this->cschema->loadUFSchemaSingleTable($this->lAttendanceTableID);
      $this->atable = &$this->cschema->schema[$this->lAttendanceTableID];
   }

   function loadEnrollment(){
   //---------------------------------------------------------------------
   // build the $this->erecs array for the specified client program
   //---------------------------------------------------------------------
      global $setEFieldFun, $loadEFieldFun;

      $ccprog = new mcprograms;

      $strETable = $this->strEnrollmentTable;
      $strEFNPre = $this->strETableFNPrefix;
      $lETableID = $this->lEnrollmentTableID;

      $strATable = $this->strAttendanceTable;
      $strAFNPre = $this->strATableFNPrefix;
      $lATableID = $this->lAttendanceTableID;

      $strBRetired = $strEFNPre.'_bRetired';


         // load fields unique to the client program
      $setEFieldFun($this, $lETableID);

            // load eTable default fields
      $this->cschema->eTableFieldInfo($this->efields, $this->etable->strFieldPrefix);

      $strSelect = $this->strSelectsViaFields($this->efields);

      $strForeignIDFN = $this->efields['lForeignKey']->strFieldNameInternal;

      if ($this->sqlOrderETable == ''){
         $strOrder = ' cr_strLName, cr_strFName, cr_lKeyID ';
      }else {
         $strOrder = $this->sqlOrderETable;
      }

      $ccprog->buildCProgDDLSql($this->efields, $strDDLInner, $strDDLSelect, $lCntDDLFields, $ddlConsolidated);
      if ($strDDLSelect != '') $strDDLSelect = $strDDLSelect.', ';
      $sqlStr =
        "SELECT
            $strSelect,
            $strDDLSelect
            cr_strFName, cr_strLName, cr_enumGender,
            cr_strAddr1, cr_strAddr2, cr_strCity, cr_strState,
            cr_strCountry, cr_strZip, cr_strPhone, cr_strCell, cr_strEmail
         FROM $strETable
            INNER JOIN client_records ON cr_lKeyID=$strForeignIDFN
            $strDDLInner
         WHERE NOT $strBRetired $this->sqlWhereETable
         ORDER BY $strOrder";

      $query = $this->db->query($sqlStr);
      $this->lNumERecs = $lNumERecs = $query->num_rows();
      $this->erecs = array();
      if ($lNumERecs > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->erecs[$idx] = new stdClass;
            $erec = &$this->erecs[$idx];

            $erec->lKeyID                   = (int)$row->lKeyID;
            $erec->lClientID                = (int)$row->lForeignKey;
            $erec->bRetired                 = (boolean)$row->bRetired;
            $erec->lOriginID                = (int)$row->lOriginID;
            $erec->dteOrigin                = (int)$row->dteOrigin;
            $erec->lLastUpdateID            = (int)$row->lLastUpdateID;
            $erec->dteLastUpdate            = (int)$row->dteLastUpdate;
            $erec->mdteStart                = $row->dteStart;
            $erec->mdteEnd                  = $row->dteEnd;
            $erec->dteStart                 = dteMySQLDate2Unix($row->dteStart);
            $erec->dteEnd                   = dteMySQLDate2Unix($row->dteEnd);

            $erec->bCurrentlyEnrolled       = (boolean)$row->bCurrentlyEnrolled;

            $erec->client_strFName          = $row->cr_strFName;
            $erec->client_strLName          = $row->cr_strLName;
            $erec->client_enumGender        = $row->cr_enumGender;
            $erec->client_strAddr1          = $row->cr_strAddr1;
            $erec->client_strAddr2          = $row->cr_strAddr2;
            $erec->client_strCity           = $row->cr_strCity;
            $erec->client_strState          = $row->cr_strState;
            $erec->client_strCountry        = $row->cr_strCountry;
            $erec->client_strZip            = $row->cr_strZip;
            $erec->client_strPhone          = $row->cr_strPhone;
            $erec->client_strCell           = $row->cr_strCell;
            $erec->client_strEmail          = $row->cr_strEmail;

               // add cprogram custom fields to the erecs array
            $loadEFieldFun($erec, $row);

               // load the ddl values
            if ($lCntDDLFields > 0){
               foreach ($ddlConsolidated as $d){
                  $strFN = $d->strFieldNameUser;
                  $erec->$strFN = $row->$strFN;
               }
            }

            ++$idx;
         }
      }
   }

   function loadAttendance(&$lNumARecs, &$aTableRecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $setAFieldFun, $loadAFieldFun;

      $ccprog = new mcprograms;

      $lNumARecs = 0;
      $aTableRecs = array();

         // load aTable default fields
      $setAFieldFun($this, $this->lAttendanceTableID);
      $this->setSingleField($this->lAttendanceTableID, 'lActivityDDL', 'Activity', $this->afields);
      $this->cschema->aTableFieldInfo($this->afields, $this->atable->strFieldPrefix);

      $strSelect = $this->strSelectsViaFields($this->afields);

      if ($this->sqlOrderATable == ''){
         $strOrder = ' '.$this->strATableFNPrefix.'_dteAttendance, '.$this->strATableFNPrefix.'_lKeyID ';
      }else {
         $strOrder = $this->sqlOrderATable;
      }

      $ccprog->buildCProgDDLSql($this->afields, $strDDLInner, $strDDLSelect, $lCntDDLFields, $ddlConsolidated);
      if ($strDDLSelect != '') $strDDLSelect = ', '.$strDDLSelect;
      $sqlStr =
        "SELECT
            $strSelect
            $strDDLSelect,

            ".$this->strATableFNPrefix."_lOriginID, ".$this->strATableFNPrefix."_lLastUpdateID,

            UNIX_TIMESTAMP(".$this->strATableFNPrefix."_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(".$this->strATableFNPrefix."_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM $this->strAttendanceTable
            $strDDLInner
            INNER JOIN admin_users   AS uc ON uc.us_lKeyID=".$this->strATableFNPrefix."_lOriginID
            INNER JOIN admin_users   AS ul ON ul.us_lKeyID=".$this->strATableFNPrefix."_lLastUpdateID

         WHERE NOT ".$this->strATableFNPrefix."_bRetired $this->sqlWhereATable
         ORDER BY $strOrder";

      $query = $this->db->query($sqlStr);
      $lNumARecs = $query->num_rows();
      if ($lNumARecs > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $aTableRecs[$idx] = new stdClass;
            $arec = &$aTableRecs[$idx];

            $arec->lActivityDDL         = (int)$row->lActivityDDL;
            $arec->lKeyID               = (int)$row->lKeyID;
            $arec->lClient              = (int)$row->lForeignKey;
            $arec->bRetired             = (boolean)$row->bRetired;
            $arec->lEnrollID            = (int)$row->lEnrollID;
            $arec->dteMysqlAttendance   = $row->dteAttendance;
            $arec->dteAttendance        = dteMySQLDate2Unix($arec->dteMysqlAttendance);

            $arec->dDuration            = (float)$row->dDuration;
            $arec->strCaseNotes         = $row->strCaseNotes;

            $arec->lOriginID            = (int)$row->lOriginID;
            $arec->lLastUpdateID        = (int)$row->lLastUpdateID;
            $arec->dteOrigin            = (int)$row->dteOrigin;
            $arec->dteLastUpdate        = (int)$row->dteLastUpdate;
            $arec->ucstrFName           = $row->strUCFName;
            $arec->ucstrLName           = $row->strUCLName;
            $arec->ulstrFName           = $row->strULFName;
            $arec->ulstrLName           = $row->strULLName;

               // load the ddl values
            if ($lCntDDLFields > 0){
               foreach ($ddlConsolidated as $d){
                  $strFN = $d->strFieldNameUser;
                  $arec->$strFN = $row->$strFN;
               }
            }

               // custom attendance fields
            $loadAFieldFun($arec, $row);

            ++$idx;
         }
      }
   }

   public function clientsWithAttendanceRecs(&$lNumClients, &$clients){
   //---------------------------------------------------------------------
   // return a list of clients who have attendance records for the
   // already loaded client program. Caller can narrow the
   // search by setting $this->sqlWhereATable
   //---------------------------------------------------------------------
      $lNumClients = 0;
      $clients = array();

      $this->cschema->aTableFieldInfo($this->afields, $this->atable->strFieldPrefix);

      $cprog     = &$this->ccprog->cprogs[0];
      $strPre    = $cprog->strATableFNPrefix;
      $strATable = $cprog->strAttendanceTable;
      $strFKeyFN = $this->afields['lForeignKey']->strFieldNameInternal;

      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs, $strFKeyFN AS lClientID,
            cr_strFName, cr_strLName
         FROM $strATable
            INNER JOIN client_records ON $strFKeyFN=cr_lKeyID
         WHERE NOT $strPre"."_bRetired $this->sqlWhereATable
         GROUP BY  $strFKeyFN
         ORDER BY  cr_strLName, cr_strFName, cr_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumClients = $query->num_rows();
      if ($lNumClients > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $clients[$idx] = new stdClass;
            $crec = &$clients[$idx];

            $crec->lClientID        = (int)$row->lClientID;
            $crec->lNumAttendance   = (int)$row->lNumRecs;
            $crec->strClientFName   = $row->cr_strFName;
            $crec->strClientLName   = $row->cr_strLName;

            ++$idx;
         }
      }
   }

   public function setSingleField($lTableID, $strFN, $strUFieldName, &$fArray){
   //---------------------------------------------------------------------
   // load fArray with info about the user field
   //---------------------------------------------------------------------
      $fArray[$strFN] = new stdClass;
      $ff = &$fArray[$strFN];
      $this->cschema->fieldInfoViaUserFieldName($lTableID, $strUFieldName, $ff, true);
   }

   public function strSelectsViaFields(&$efields){
      $strOut = '';
      foreach ($efields as $strFieldAs => $finfo){
         $strOut .= ', '.$finfo->strFieldNameInternal.' AS '.$strFieldAs."\n";
      }
      if ($strOut != '') $strOut = substr($strOut, 1);
      return($strOut);
   }

   public function monthlyEnrollStatsForYear($lYear, &$monthlyEnrollCnts, $strWhereExtra=''){
   //---------------------------------------------------------------------
   // for the currently loaded cprog
   //---------------------------------------------------------------------
      $monthlyEnrollCnts = array(1=>0, 2=>0, 3=>0,  4=>0,  5=>0,  6=>0,
                                 7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0);

      $strETable = $this->strEnrollmentTable;
      $strEFNPre = $this->strETableFNPrefix;

      $strFNStart = $strEFNPre.'_dteStart';
      $sqlStr =
           "SELECT COUNT(*) AS lNumRecs, MONTH($strFNStart) AS lMonth
            FROM $strETable
            WHERE NOT $strEFNPre"."_bRetired
               AND YEAR($strFNStart)=$lYear
               $strWhereExtra
            GROUP BY MONTH($strFNStart)
            ORDER BY MONTH($strFNStart);";

      $query = $this->db->query($sqlStr);
      $this->lNumCProgs = $numRows = $query->num_rows();
      $this->cprogs = array();
      if ($numRows > 0) {
         foreach ($query->result() as $row){
            $monthlyEnrollCnts[(int)$row->lMonth] = (int)$row->lNumRecs;
         }
      }
   }








}
