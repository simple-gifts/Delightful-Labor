<?php
/*---------------------------------------------------------------------
// copyright (c) 2014 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('admin/mpermissions',           'perms');
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('personalization/muser_schema', 'cUFSchema');
      $this->load->model('client_features/mcprograms',   'cprograms');
---------------------------------------------------------------------*/

class mcprograms extends CI_Model{

   public
      $lNumCProgs, $cprogs,
      $sqlWhere, $sqlWhereLoadClients, $sqlOrder;

   public $cvschema, $lNumUFVolTables, $UFVschema;

	function __construct(){
		parent::__construct();

      $this->lNumCProgs = $this->cprogs = null;
      $this->sqlWhere = $this->sqlOrder = $this->sqlWhereLoadClients = '';

      $this->lNumUFVolTables = 0;
      $this->cvschema  = null;
      $this->UFVschema = null;
	}

   function loadClientProgramsViaCPID($lCProgID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_array($lCProgID)){
         $this->sqlWhere = ' AND cp_lKeyID IN ('.implode(',', $lCProgID).') ';
      }else {
         $this->sqlWhere = " AND cp_lKeyID=$lCProgID ";
      }
      $this->loadClientPrograms();
   }

   function loadClientProgramsViaETableID($lETableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND cp_lEnrollmentTableID=$lETableID ";
      $this->loadClientPrograms();
   }

   function loadClientProgramsViaATableID($lATableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND cp_lAttendanceTableID=$lATableID ";
      $this->loadClientPrograms();
   }

   function loadClientProgramsViaProgramName($strCProgName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = ' AND cp_strProgramName = '.strPrepStr($strCProgName);
      $this->loadClientPrograms();
   }

   function lClientProgramIDViaProgramName($strCProgName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT cp_lKeyID
         FROM cprograms
         WHERE NOT cp_bRetired AND cp_strProgramName = '.strPrepStr($strCProgName).'
         LIMIT 0,1;';
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0){
         return(null);
      }else {
         $row = $query->row();
         return((int)$row->cp_lKeyID);
      }
   }

   function loadClientPrograms($bShowHidden=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cUF = new muser_fields;
      $cperms = new mpermissions;

      if ($bShowHidden){
         $strHidden = '';
      }else {
         $strHidden = ' AND NOT cp_bHidden ';
      }

      if ($this->sqlOrder == ''){
         $strOrder = ' cp_strProgramName, cp_lKeyID ';
      }else {
         $strOrder = $this->sqlOrder;
      }

      $sqlStr =
        "SELECT
            cp_lKeyID, cp_strProgramName, cp_strDescription,
            cp_strVocEnroll, cp_strVocAttendance,
            cp_lEnrollmentTableID, cp_lAttendanceTableID, cp_lActivityFieldID,
            cp_dteStart, cp_dteEnd,
            cp_bMentorMentee,

            cp_strE_VerificationModule, cp_strE_VModEntryPoint,
            cp_strA_VerificationModule, cp_strA_VModEntryPoint,

            ute.pft_bReadOnly AS bETableReadOnly,
            uta.pft_bReadOnly AS bATableReadOnly,

            cp_bHidden, cp_bRetired,
            cp_lOriginID, cp_lLastUpdateID,

            UNIX_TIMESTAMP(cp_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(cp_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM cprograms
            INNER JOIN uf_tables     AS ute ON ute.pft_lKeyID = cp_lEnrollmentTableID
            INNER JOIN uf_tables     AS uta ON uta.pft_lKeyID = cp_lAttendanceTableID
            INNER JOIN admin_users   AS uc  ON uc.us_lKeyID=cp_lOriginID
            INNER JOIN admin_users   AS ul  ON ul.us_lKeyID=cp_lLastUpdateID

         WHERE NOT cp_bRetired $this->sqlWhere $strHidden
         ORDER BY $strOrder;";

      $query = $this->db->query($sqlStr);
      $this->lNumCProgs = $numRows = $query->num_rows();
      $this->cprogs = array();
      if ($numRows==0) {
         $this->cprogs[0] = new stdClass;
         $cprog = &$this->cprogs[0];

         $cprog->lKeyID             =
         $cprog->strProgramName     =

         $cprog->strEnrollmentLabel =
         $cprog->strAttendanceLabel =
         $cprog->strSafeEnrollLabel =
         $cprog->strSafeAttendLabel =

         $cprog->bETableReadOnly    =
         $cprog->bATableReadOnly    =

         $cprog->strE_VerificationModule =
         $cprog->strE_VModEntryPoint     =
         $cprog->strA_VerificationModule =
         $cprog->strA_VModEntryPoint     =

         $cprog->strDescription     =
         $cprog->dteMysqlStart      =
         $cprog->dteMysqlEnd        =
         $cprog->dteStart           =
         $cprog->dteEnd             =
         $cprog->bHidden            =

         $cprog->lEnrollmentTableID =
         $cprog->lAttendanceTableID =
         $cprog->lActivityFieldID   =
         $cprog->strActivityFN      =

         $cprog->bMentorMentee      =

         $cprog->lOriginID          =
         $cprog->lLastUpdateID      =

         $cprog->dteOrigin          =
         $cprog->dteLastUpdate      =
         $cprog->ucstrFName         =
         $cprog->ucstrLName         =
         $cprog->ulstrFName         =
         $cprog->ulstrLName         = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->cprogs[$idx] = new stdClass;
            $cprog = &$this->cprogs[$idx];

            $cprog->lKeyID             = $lCProgID = (int)$row->cp_lKeyID;
            $cprog->strProgramName     = $row->cp_strProgramName;

            $cprog->strEnrollmentLabel = $row->cp_strVocEnroll;
            $cprog->strAttendanceLabel = $row->cp_strVocAttendance;
            $cprog->strSafeEnrollLabel = htmlspecialchars($row->cp_strVocEnroll);
            $cprog->strSafeAttendLabel = htmlspecialchars($row->cp_strVocAttendance);

            $cprog->bETableReadOnly    = (bool)$row->bETableReadOnly;
            $cprog->bATableReadOnly    = (bool)$row->bATableReadOnly;

            $cprog->strE_VerificationModule = $row->cp_strE_VerificationModule;
            $cprog->strE_VModEntryPoint     = $row->cp_strE_VModEntryPoint;
            $cprog->strA_VerificationModule = $row->cp_strA_VerificationModule;
            $cprog->strA_VModEntryPoint     = $row->cp_strA_VModEntryPoint;

            $cprog->strDescription     = $row->cp_strDescription;
            $cprog->dteMysqlStart      = $row->cp_dteStart;
            $cprog->dteMysqlEnd        = $row->cp_dteEnd;
            $cprog->dteStart           = dteMySQLDate2Unix($row->cp_dteStart);
            $cprog->dteEnd             = dteMySQLDate2Unix($row->cp_dteEnd);
            $cprog->bHidden            = (boolean)$row->cp_bHidden;

            $cprog->lEnrollmentTableID = (int)$row->cp_lEnrollmentTableID;
            $cprog->lAttendanceTableID = $lATableID = (int)$row->cp_lAttendanceTableID;
            $cprog->strEnrollmentTable = $cUF->strGenUF_TableName($cprog->lEnrollmentTableID);
            $cprog->strETableFNPrefix  = $cUF->strGenUF_KeyFieldPrefix($cprog->lEnrollmentTableID);
            $cprog->strAttendanceTable = $cUF->strGenUF_TableName($cprog->lAttendanceTableID);
            $cprog->strATableFNPrefix  = $cUF->strGenUF_KeyFieldPrefix($cprog->lAttendanceTableID);
            $cprog->lActivityFieldID   = $lActivityFieldID = (int)$row->cp_lActivityFieldID;
            $cprog->strActivityFN      = $this->strActivityDDLFN($lATableID, $lActivityFieldID);

            $cprog->bMentorMentee      = (boolean)$row->cp_bMentorMentee;

            $cprog->lOriginID          = (int)$row->cp_lOriginID;
            $cprog->lLastUpdateID      = (int)$row->cp_lLastUpdateID;

            $cprog->dteOrigin          = (int)$row->dteOrigin;
            $cprog->dteLastUpdate      = (int)$row->dteLastUpdate;
            $cprog->ucstrFName         = $row->strUCFName;
            $cprog->ucstrLName         = $row->strUCLName;
            $cprog->ulstrFName         = $row->strULFName;
            $cprog->ulstrLName         = $row->strULLName;

               // user-group permissions for this program
            $cprog->lNumPerms = $cperms->lGroupPerms($lCProgID, CENUM_CONTEXT_CPROGRAM, $cprog->perms);

            ++$idx;
         }
      }
   }

   function addNewCProgram(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $cufc = new muser_fields_create;

         // first make the new entry in the client program table
      $sqlStr =
         'INSERT INTO cprograms
          SET '.$this->strSQLCommon().",
             cp_bRetired = 0,
             cp_lOriginID = $glUserID,
             cp_dteOrigin = NOW();";

      $query = $this->db->query($sqlStr);
      $this->cprogs[0]->lKeyID = $lKeyID = $this->db->insert_id();
      $strKeyID = str_pad($lKeyID, 5, '0', STR_PAD_LEFT);

         //---------------------------------
         // add the enrollment table
         //---------------------------------
      $cufc->bMultiEntry          = true;
      $cufc->bReadOnly            = $this->cprogs[0]->bETableReadOnly;
      $cufc->bCollapsibleHeadings = true;
      $cufc->bCollapseDefaultHide = true;
      $cufc->bAlertNoDataEntry    = false;
      $cufc->strAlert             = '';
      $cufc->strTableDescription  = 'Auto-generated for Client Program '.$strKeyID;

      $cufc->strUserTableName     = 'Client Programs Enrollment '.$strKeyID;
      $cufc->enumTType            = CENUM_CONTEXT_CPROGENROLL;

      $lEnrollTableID     = $cufc->lTableID = $cufc->lAddNewUFTable();
      $strEnrollTableName = $cufc->strENPTableName;
      $strFNPrefix        = $cufc->strGenUF_KeyFieldPrefix($lEnrollTableID);
      $strClientIDFN      = $strFNPrefix.'_lClientID';
      $strDateStartFN     = $strFNPrefix.'_dteStart';
      $strDateEndFN       = $strFNPrefix.'_dteEnd';
      $strBEnrolledFN     = $strFNPrefix.'_bCurrentlyEnrolled';

         // note that the foreign ID in the personalized table is the client ID
      $sqlStr =
           "ALTER TABLE $strEnrollTableName
               ADD $strDateStartFN  date NOT NULL,
               ADD $strDateEndFN    date DEFAULT NULL,
               ADD $strBEnrolledFN  tinyint(1) NOT NULL DEFAULT '0';";
      $query = $this->db->query($sqlStr);

         //---------------------------------
         // add the attendance table
         //---------------------------------
      $cufc->strUserTableName = 'Client Programs Attendance '.$strKeyID;
      $cufc->enumTType        = CENUM_CONTEXT_CPROGATTEND;
      $cufc->bReadOnly        = $this->cprogs[0]->bATableReadOnly;
      $lAttendTableID         = $cufc->lTableID = $cufc->lAddNewUFTable();

      $strAttendTableName = $cufc->strENPTableName;
      $strFNPrefix        = $cufc->strGenUF_KeyFieldPrefix($lAttendTableID);
      $strEnrollIDFN      = $strFNPrefix.'_lEnrollID';
      $strDateAttendFN    = $strFNPrefix.'_dteAttendance';
      $strDurationFN      = $strFNPrefix.'_dDuration';
      $strCaseNotesFN     = $strFNPrefix.'_strCaseNotes';

      $sqlStr =
           "ALTER TABLE $strAttendTableName
               ADD $strEnrollIDFN    INT NOT NULL COMMENT 'Foreign key to enrollment table',
               ADD $strDateAttendFN  date NOT NULL,
               ADD $strDurationFN    DECIMAL(10, 2) NOT NULL DEFAULT 0,
               ADD $strCaseNotesFN   TEXT NOT NULL,
            ADD INDEX($strEnrollIDFN),
            ADD INDEX($strDateAttendFN);";
      $query = $this->db->query($sqlStr);

         //------------------------------------------------------------------
         // add the activity DDL
         //------------------------------------------------------------------
      $cufc->lTableID = $lAttendTableID;
      $cufc->fields = array();
      $cufc->fields[0] = new stdClass;
      $ufield                       = &$cufc->fields[0];
      $ufield->enumFieldType        = CS_FT_DDL;
      $ufield->pff_strFieldNameUser = 'Activity';
      $ufield->pff_bCheckDef        = false;
      $ufield->pff_curDef           = 0.0;
      $ufield->pff_strTxtDef        = '';
      $ufield->pff_lDef             = 0;
      $ufield->pff_lCurrencyACO     = 1;
      $ufield->pff_bHidden          = false;
      $ufield->pff_bRequired        = true;

      $cufc->addNewField();
      $lDDLActivityFID = $ufield->pff_lKeyID;

         // add default activity
      $cufc->setDDL_asConfigured($lDDLActivityFID);
      $lNewSortIDX = 1;
      $lDDLEntryID = $cufc->addUF_DDLEntry('(other)', $lDDLActivityFID, $lNewSortIDX);

         //------------------------------------------------------------------
         // update cprogram table with the enrollment/attendance table IDs
         //------------------------------------------------------------------
      $sqlStr =
           "UPDATE cprograms
            SET
               cp_lEnrollmentTableID = $lEnrollTableID,
               cp_lAttendanceTableID = $lAttendTableID,
               cp_lActivityFieldID   = $lDDLActivityFID
            WHERE cp_lKeyID=$lKeyID;";
      $query = $this->db->query($sqlStr);
      return($lKeyID);
   }

   function updateCProgram($lCProgID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
           'UPDATE cprograms
            SET '.$this->strSQLCommon()."
            WHERE cp_lKeyID=$lCProgID;";
      $query = $this->db->query($sqlStr);

      $cprog = &$this->cprogs[0];

      $this->updateCProgReadOnly($cprog->lEnrollmentTableID, $cprog->bETableReadOnly);
      $this->updateCProgReadOnly($cprog->lAttendanceTableID, $cprog->bATableReadOnly);
   }

   function updateCProgReadOnly($lTableID, $bReadOnly){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'UPDATE uf_tables
          SET pft_bReadOnly='.($bReadOnly ? '1' : '0')."
          WHERE pft_lKeyID=$lTableID;";
      $query = $this->db->query($sqlStr);
   }

   private function strSQLCommon(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $cprog = &$this->cprogs[0];
      return('
            cp_strProgramName   = '.strPrepStr($cprog->strProgramName).',
            cp_strVocEnroll     = '.strPrepStr($cprog->strEnrollmentLabel).',
            cp_strVocAttendance = '.strPrepStr($cprog->strAttendanceLabel).',
            cp_strDescription   = '.strPrepStr($cprog->strDescription).',
            cp_dteStart         = '.strPrepStr($cprog->dteMysqlStart).',
            cp_dteEnd           = '.strPrepStr($cprog->dteMysqlEnd).',

            cp_strE_VerificationModule = '.strPrepStr($cprog->strE_VerificationModule).',
            cp_strE_VModEntryPoint     = '.strPrepStr($cprog->strE_VModEntryPoint    ).',
            cp_strA_VerificationModule = '.strPrepStr($cprog->strA_VerificationModule).',
            cp_strA_VModEntryPoint     = '.strPrepStr($cprog->strA_VModEntryPoint    ).',

            cp_bHidden          = '.($cprog->bHidden ? '1' : '0').',

            cp_bMentorMentee    = '.($cprog->bMentorMentee ? '1' : '0').",

            cp_dteLastUpdate    = NOW(),
            cp_lLastUpdateID    = $glUserID ");
   }

   function removeCProgram(){
   //---------------------------------------------------------------------
   // caller must first call
   //    $this->loadClientProgramsViaCPID($lCPID)
   // and load
   //   $this->load->model('client_features/mcprograms',                'cprograms');
   //   $this->load->model('personalization/muser_fields',        'clsUF');
   //   $this->load->model('personalization/muser_fields_create', 'clsUFC');
   //---------------------------------------------------------------------
      $clsUFC = new muser_fields_create;

      $cprog = &$this->cprogs[0];
      $lCProgID  = $cprog->lKeyID;

         // remove the personalized tables
      $clsUFC->removeUFTable($cprog->lEnrollmentTableID);
      $clsUFC->removeUFTable($cprog->lAttendanceTableID);

         // remove the cprogram table entry
      $sqlStr =
         "DELETE FROM cprograms
          WHERE cp_lKeyID=$lCProgID;";
      $query = $this->db->query($sqlStr);
   }

   function strHTMLProgramSummaryDisplay($enumTType){
   //---------------------------------------------------------------------
   //  caller must first load the client program record
   //---------------------------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $strOut = '';

      $cprog = &$this->cprogs[0];
      $lCProgID = $cprog->lKeyID;
      $bEnrollment = $enumTType == CENUM_CONTEXT_CPROGENROLL;

      $clsRpt->setEntrySummary();
      $strOut .= $clsRpt->openReport();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Client Program:', '115pt')
         .$clsRpt->writeCell(
                    htmlspecialchars($cprog->strProgramName).'&nbsp;'
                   .strLinkView_CProgram($lCProgID, 'View program record', true))
         .$clsRpt->closeRow();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Table Type:', '115pt')
         .$clsRpt->writeCell(($bEnrollment ? $cprog->strSafeEnrollLabel : $cprog->strSafeAttendLabel))
         .$clsRpt->closeRow();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Client Program ID:', '115pt')
         .$clsRpt->writeCell(str_pad($lCProgID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Description:')
         .$clsRpt->writeCell(nl2br(htmlspecialchars($cprog->strDescription)), '350pt;')
         .$clsRpt->closeRow();

      $strOut .=
         $clsRpt->closeReport();
      return($strOut);
   }

   function bIsClientCurrentlyEnrolled($lClientID, &$cprog, &$lNumEnrollments, &$erecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strETable    = $cprog->strEnrollmentTable;
      $strEFNPrefix = $cprog->strETableFNPrefix;
      $sqlWhere =
           ' AND ETable.'.$strEFNPrefix.'_lForeignKey='.$lClientID.'
             AND ETable.'.$strEFNPrefix.'_bCurrentlyEnrolled ';
      $this->loadBaseEFieldRecs($cprog, $lNumEnrollments, $erecs, $sqlWhere);
      return($lNumEnrollments > 0);
   }

   function bIsClientInProgram($lClientID, &$cprog, &$lNumEnrollments, &$erecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strETable    = $cprog->strEnrollmentTable;
      $strEFNPrefix = $cprog->strETableFNPrefix;
      $sqlWhere =
           ' AND ETable.'.$strEFNPrefix.'_lForeignKey='.$lClientID.' ';
      $this->loadBaseEFieldRecs($cprog, $lNumEnrollments, $erecs, $sqlWhere);
      return($lNumEnrollments > 0);
   }

   function bIsClientActivelyEnrolledInProgram($lClientID, &$cprog, &$lNumEnrollments, &$erecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strETable    = $cprog->strEnrollmentTable;
      $strEFNPrefix = $cprog->strETableFNPrefix;
      $sqlWhere =
           ' AND ETable.'.$strEFNPrefix.'_lForeignKey='.$lClientID." AND ETable.$strEFNPrefix".'_bCurrentlyEnrolled ';
      $this->loadBaseEFieldRecs($cprog, $lNumEnrollments, $erecs, $sqlWhere);
      return($lNumEnrollments > 0);
   }

   function loadBaseERecViaProgClientID($lClientID, &$cprog, &$lNumEnrollments, &$erecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strETable    = $cprog->strEnrollmentTable;
      $strEFNPrefix = $cprog->strETableFNPrefix;
      $sqlWhere =
           ' AND ETable.'.$strEFNPrefix.'_lForeignKey='.$lClientID.' ';
      $this->loadBaseEFieldRecs($cprog, $lNumEnrollments, $erecs, $sqlWhere);
   }

   function loadBaseERecViaERecID(&$cprog, $eRecID, &$lNumERecs, &$erecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strETable    = $cprog->strEnrollmentTable;
      $strEFNPrefix = $cprog->strETableFNPrefix;
      $sqlWhere =
           ' AND ETable.'.$strEFNPrefix.'_lKeyID='.$eRecID.' ';
      $this->loadBaseEFieldRecs($cprog, $lNumERecs, $erecs, $sqlWhere);
   }

   function loadBaseARecViaARecID(&$cprog, $aRecID, &$lNumARecs, &$arecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strATable    = $cprog->strAttendanceTable;
      $strAFNPrefix = $cprog->strATableFNPrefix;
      $sqlWhere =
           ' AND '.$strAFNPrefix.'_lKeyID='.$aRecID.' ';
      $this->loadBaseAFieldRecs($cprog, $lNumARecs, $arecs, $sqlWhere);
   }

   function loadBaseARecViaERecID(&$cprog, $eRecID, &$lNumARecs, &$arecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strATable    = $cprog->strAttendanceTable;
      $strAFNPrefix = $cprog->strATableFNPrefix;
      $sqlWhere =
           ' AND '.$strAFNPrefix.'_lEnrollID='.$eRecID.' ';
      $this->loadBaseAFieldRecs($cprog, $lNumARecs, $arecs, $sqlWhere);
   }

   function loadBaseAFieldRecs(&$cprog, &$lNumARecs, &$arecs, $sqlWhere, $strOrder=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strATable     = $cprog->strAttendanceTable;
      $strAFNPrefix  = $cprog->strATableFNPrefix;
      $strActivityFN = $cprog->strActivityFN;

      if ($strOrder==''){
         $strOrder = $strAFNPrefix.'_dteAttendance, '.$strAFNPrefix.'_lKeyID ';
      }

      $sqlStr =
        'SELECT
             '.$strAFNPrefix.'_lKeyID                        AS lKeyID,
             '.$strAFNPrefix.'_lForeignKey                   AS lForeignKey,
             '.$strAFNPrefix.'_lEnrollID                     AS lEnrollID,
             '.$strAFNPrefix.'_lOriginID                     AS lOriginID,
             UNIX_TIMESTAMP('.$strAFNPrefix.'_dteOrigin)     AS dteOrigin,
             '.$strAFNPrefix.'_lLastUpdateID                 AS lLastUpdateID,
             UNIX_TIMESTAMP('.$strAFNPrefix.'_dteLastUpdate) AS dteLastUpdate,
             '.$strAFNPrefix.'_dteAttendance                 AS dteMysqlAttendance,
             '.$strAFNPrefix.'_dDuration                     AS dDuration,
             '.$strAFNPrefix."_strCaseNotes                  AS strCaseNotes,
             $strActivityFN                                  AS lActivityID,
             actDDL.ufddl_strDDLEntry                        AS strActivity,
             cr_strFName, cr_strLName

         FROM $strATable ".'
            INNER JOIN client_records   ON cr_lKeyID='.$strAFNPrefix."_lForeignKey
            LEFT  JOIN uf_ddl AS actDDL ON actDDL.ufddl_lKeyID = $strActivityFN
         WHERE NOT $strAFNPrefix"."_bRetired $sqlWhere
         ORDER BY $strOrder;";
      $query = $this->db->query($sqlStr);
      $lNumARecs = $query->num_rows();
      $arecs = array();
      if ($lNumARecs==0) {
         $arecs[0] = new stdClass;
         $arec = &$arecs[0];
         $arec->lKeyID             =
         $arec->lEnrollID          =
         $arec->lClientID          =
         $arec->dteMysqlAttendance =
         $arec->dteAttendance      =
         $arec->dDuration          =
         $arec->strCaseNotes       =
         $arec->lOriginID          =
         $arec->dteOrigin          =
         $arec->lLastUpdateID      =
         $arec->dteLastUpdate      = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $arecs[$idx] = new stdClass;
            $arec = &$arecs[$idx];
            $arec->lKeyID             = (int)$row->lKeyID;
            $arec->lEnrollID          = (int)$row->lEnrollID;
            $arec->lClientID          = (int)$row->lForeignKey;

            $arec->dteMysqlAttendance = $row->dteMysqlAttendance;
            $arec->dteAttendance      = dteMySQLDate2Unix($row->dteMysqlAttendance);
            $arec->dDuration          = (float)$row->dDuration;
            $arec->lActivityID        = $row->lActivityID;
            $arec->strActivity        = $row->strActivity;
            $arec->strCaseNotes       = $row->strCaseNotes;

            $arec->lOriginID          = (int)$row->lOriginID;
            $arec->dteOrigin          = (int)$row->dteOrigin;
            $arec->lLastUpdateID      = (int)$row->lLastUpdateID;
            $arec->dteLastUpdate      = (int)$row->dteLastUpdate;
            $arec->strClientFName     = $row->cr_strFName;
            $arec->strClientLName     = $row->cr_strLName;

            ++$idx;
         }
      }
   }

   function loadBaseEFieldRecs(&$cprog, &$lNumERecs, &$erecs, $sqlWhere, $strOrder=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strETable    = $cprog->strEnrollmentTable;
      $strEFNPrefix = $cprog->strETableFNPrefix;

      if ($strOrder==''){
         $strOrder = 'ETable.'.$strEFNPrefix.'_dteStart, ETable.'.$strEFNPrefix.'_dteEnd, ETable.'.$strEFNPrefix.'_lKeyID ';
      }

      customClient_AAYHF_01($strInnerAAYHF, $strFieldsAAYHF, $bAAYHF_Beacon);

      $sqlStr =
        'SELECT
             ETable.'.$strEFNPrefix.'_lKeyID                   AS lKeyID,
             ETable.'.$strEFNPrefix.'_lForeignKey              AS lForeignKey,
             ETable.'.$strEFNPrefix.'_lOriginID                AS lOriginID,
             ETable.'.$strEFNPrefix.'_dteOrigin                AS dteOrigin,
             ETable.'.$strEFNPrefix.'_lLastUpdateID            AS lLastUpdateID,
             ETable.'.$strEFNPrefix.'_dteLastUpdate            AS dteLastUpdate,
             ETable.'.$strEFNPrefix.'_dteStart                 AS dteMysqlStart,
             ETable.'.$strEFNPrefix.'_dteEnd                   AS dteMysqlEnd,
             ETable.'.$strEFNPrefix."_bCurrentlyEnrolled       AS bCurrentlyEnrolled,
             cr_strLName, cr_strFName, cr_dteEnrollment,
             cr_dteBirth
             $strFieldsAAYHF

         FROM $strETable AS ETable
            INNER JOIN client_records ON ETable.$strEFNPrefix"."_lForeignKey = cr_lKeyID
            $strInnerAAYHF
         WHERE NOT ETable.$strEFNPrefix"."_bRetired $sqlWhere
         ORDER BY $strOrder;";

      $query = $this->db->query($sqlStr);
      $lNumERecs = $query->num_rows();
      $erecs = array();
      if ($lNumERecs==0) {
         $erecs[0] = new stdClass;
         $erec = &$erecs[0];
         $erec->lKeyID             =
         $erec->lForeignKey        =
         $erec->lClientID          =
         $erec->dteMysqlStart      =
         $erec->dteMysqlEnd        =
         $erec->dteStart           =
         $erec->dteEnd             =
         $erec->bCurrentlyEnrolled =

         $erec->strClientFName     =
         $erec->strClientLName     =
         $erec->dteEnrolled        =
         $erec->mysqlDteBirth      =

         $erec->lOriginID          =
         $erec->dteOrigin          =
         $erec->lLastUpdateID      =
         $erec->dteLastUpdate      = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $erecs[$idx] = new stdClass;
            $erec = &$erecs[$idx];

            $erec->lKeyID             = (int)$row->lKeyID;
            $erec->lClientID          = (int)$row->lForeignKey;
            $erec->dteMysqlStart      = $row->dteMysqlStart;
            $erec->dteMysqlEnd        = $row->dteMysqlEnd;
            $erec->dteStart           = dteMySQLDate2Unix($row->dteMysqlStart);
            $erec->dteEnd             = dteMySQLDate2Unix($row->dteMysqlEnd);
            $erec->bCurrentlyEnrolled = (boolean)$row->bCurrentlyEnrolled;

            $erec->strClientFName     = $row->cr_strFName;
            $erec->strClientLName     = $row->cr_strLName;
            $erec->dteEnrolled        = dteMySQLDate2Unix($row->cr_dteEnrollment);
            $erec->mysqlDteBirth      = $row->cr_dteBirth;

            $erec->lOriginID          = (int)$row->lOriginID;
            $erec->dteOrigin          = (int)$row->dteOrigin;
            $erec->lLastUpdateID      = (int)$row->lLastUpdateID;
            $erec->dteLastUpdate      = (int)$row->dteLastUpdate;

            addClientFields_AAYHF_Beacon($bAAYHF_Beacon, $erec, $row);

            ++$idx;
         }
      }
   }

   function lNumEnrollmentsViaCIDCProgID($lClientID, $lCProgID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
echo(__FILE__.' '.__LINE__.'<br>'."\n"); die;


   }

   function lNumAttendanceViaEnrollID($lERecID, &$cprog){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strATable    = $cprog->strAttendanceTable;
      $strAFNPrefix = $cprog->strATableFNPrefix;
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM $strATable
         WHERE NOT ".$strAFNPrefix."_bRetired
            AND ".$strAFNPrefix."_lEnrollID=$lERecID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }

   function deleteATableRecsViaEnrollRecID($strATable, $strATableFNPre, $lEnrollRecID){
   //---------------------------------------------------------------------
   // this routine is called to delete child attendance records when
   // an enrollment record is removed
   //---------------------------------------------------------------------
      $sqlStr =
         "DELETE FROM $strATable
          WHERE $strATableFNPre"."_lEnrollID = $lEnrollRecID;";
      $query = $this->db->query($sqlStr);
   }

   function lNumEnrollmentsViaCProg(&$cprog, $bCurrentlyEnrolledOnly){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strETable    = $cprog->strEnrollmentTable;
      $strEFNPrefix = $cprog->strETableFNPrefix;

      if ($bCurrentlyEnrolledOnly){
         $strCurOnly = " AND $strEFNPrefix"."_bCurrentlyEnrolled ";
      }else {
         $strCurOnly = '';
      }

      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM $strETable
         WHERE NOT $strEFNPrefix"."_bRetired
            $strCurOnly;";

      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }

   function lNumClientsViaCProg(&$cprog, $bCurrentlyEnrolledOnly){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strETable    = $cprog->strEnrollmentTable;
      $strEFNPrefix = $cprog->strETableFNPrefix;

      if ($bCurrentlyEnrolledOnly){
         $strCurOnly = " AND $strEFNPrefix"."_bCurrentlyEnrolled ";
      }else {
         $strCurOnly = '';
      }

      $sqlStr =
        "-- # clients via cProg
         SELECT COUNT(*) AS lNumRecs
         FROM $strETable
         WHERE NOT $strEFNPrefix"."_bRetired
            $strCurOnly
         GROUP BY $strEFNPrefix"."_lForeignKey
         ORDER BY $strEFNPrefix"."_lForeignKey
         ;";

      $query = $this->db->query($sqlStr);
      if (($lNumRows = $query->num_rows()) == 0){
         return(0);
      }else {
         $row = $query->row();
         return($lNumRows);
      }
   }

   function strActivityDDLFN($lATableID, $lDDLFieldID){
      return('uf'.str_pad($lATableID.'', 6, '0', STR_PAD_LEFT).'_'. str_pad($lDDLFieldID.'', 6, '0', STR_PAD_LEFT));
   }

   function clientsActiveInMonth($lMonth, $lYear, $lProgID, $cprog, &$lNumClients, &$clients){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strETable    = $cprog->strEnrollmentTable;
      $strEFNPrefix = $cprog->strETableFNPrefix;

      $strERecStartFN = $strEFNPrefix.'_dteStart';
      $strERecEndFN   = $strEFNPrefix.'_dteEnd';
      $strActiveFN    = $strEFNPrefix.'_bCurrentlyEnrolled';

      $dteStart = strtotime($lMonth.'/1/'.$lYear);
      $dteEnd   = strtotime($lMonth.'/'.lLastDayMon($lMonth, $lYear).'/'.$lYear.' 23:59:59');
      $this->sqlWhereLoadClients = $this->strActivelyEnrolledDuringMonthWhere($cprog, $lMonth, $lYear);
      $this->clientsEnrolledViaProgID($lProgID, $cprog, false, $lNumClients, $clients);

      if ($lNumClients > 0){
         foreach ($clients as $client){
                        // active enrollments for the specified month
            $strWhereExtra = " AND $strEFNPrefix"."_lForeignKey = $client->lClientID ";
            $this->enrollmentsActiveInMonth($cprog, $lMonth, $lYear,
                    $client->lNumEnroll, $client->enrollIDs, $strWhereExtra);
            if ($client->lNumEnroll > 0){
               foreach($client->enrollIDs as $eRecID){
                  $this->loadBaseERecViaERecID($cprog, $eRecID, $lDummy, $client->erecs);
               }
            }
         }
      }
   }

   function clientsEnrolledViaProgID($lProgID, $cprog, $bCurrentlyEnrolledOnly,
                                        &$lNumClients, &$clients, $bEncludeEnrollment=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strETable    = $cprog->strEnrollmentTable;
      $strEFNPrefix = $cprog->strETableFNPrefix;

      $strERecStartFN = $strEFNPrefix.'_dteStart';
      $strERecEndFN   = $strEFNPrefix.'_dteEnd';
      $strActiveFN    = $strEFNPrefix.'_bCurrentlyEnrolled';
      if ($bCurrentlyEnrolledOnly){
         $sqlWhere = " AND $strActiveFN ";
      }else {
         $sqlWhere = '';
      }

      $strOrder = 'cr_strLName, cr_strFName, '.$strEFNPrefix.'_lForeignKey,'.$strEFNPrefix.'_lKeyID ';

      $sqlStr =
        'SELECT DISTINCT
             cr_lKeyID,
             cr_strFName, cr_strLName, cr_dteEnrollment,
             cr_dteBirth
         FROM client_records
            INNER JOIN '.$strETable.' ON '.$strEFNPrefix.'_lForeignKey = cr_lKeyID
         WHERE NOT cr_bRetired AND NOT '.$strEFNPrefix."_bRetired \n"
            .$sqlWhere.' '
            .$this->sqlWhereLoadClients.'
         ORDER BY '.$strOrder.';';

      $query = $this->db->query($sqlStr);
      $lNumClients = $query->num_rows();
      $clients = array();
      if ($lNumClients > 0) {

         if ($bCurrentlyEnrolledOnly){
            $strWhereEBase = " AND $strActiveFN ";
         }else {
            $strWhereEBase = '';
         }

         $idx = 0;
         foreach ($query->result() as $row){
            $clients[$idx] = new stdClass;
            $client = &$clients[$idx];

            $client->lClientID         = $lClientID = (int)$row->cr_lKeyID;

            $client->strClientFName    = $row->cr_strFName;
            $client->strClientLName    = $row->cr_strLName;
            $client->dteClientEnrolled = dteMySQLDate2Unix($row->cr_dteEnrollment);  // not cprogram enrollment date!
            $client->mysqlDteBirth     = $row->cr_dteBirth;


            if ($bEncludeEnrollment){
               $sqlEWhere = $strWhereEBase." AND ETable.$strEFNPrefix"."_lForeignKey = $lClientID ";
               $this->loadBaseEFieldRecs($cprog, $client->lNumERecs, $client->erecs, $sqlEWhere);
            }

            ++$idx;
         }
      }
   }





      /*-------------------------------------------------------
            D A Y S   A N D   H O U R S
      -------------------------------------------------------*/

   function lDaysViaEnrollID(&$cprog, $lEnrollID){
   //---------------------------------------------------------------------
   // unique days represented by the attendance table
   //---------------------------------------------------------------------
      $strATable        = $cprog->strAttendanceTable;
      $lATableID        = $cprog->lAttendanceTableID;
      $strAFNPrefix     = $cprog->strATableFNPrefix;

      $sqlStr =
           'SELECT DISTINCT('.$strAFNPrefix.'_dteAttendance)
            FROM '.$strATable.'
            WHERE NOT '.$strAFNPrefix.'_bRetired
               AND '.$strAFNPrefix.'_lEnrollID='.(int)$lEnrollID.'
            ORDER BY '.$strAFNPrefix.'_dteAttendance;';
      $query = $this->db->query($sqlStr);
      $lNumDays = (int)$query->num_rows();
      return($lNumDays);
   }

   function sngHoursViaEnrollID(&$cprog, $lEnrollID){
   //---------------------------------------------------------------------
   // unique days represented by the attendance table
   //---------------------------------------------------------------------
      $strATable        = $cprog->strAttendanceTable;
      $lATableID        = $cprog->lAttendanceTableID;
      $strAFNPrefix     = $cprog->strATableFNPrefix;

      $sqlStr =
           'SELECT SUM('.$strAFNPrefix.'_dDuration) AS dCurTot
            FROM '.$strATable.'
            WHERE NOT '.$strAFNPrefix.'_bRetired
               AND '.$strAFNPrefix.'_lEnrollID='.(int)$lEnrollID.';';
      $query = $this->db->query($sqlStr);
      $lNumRecs = (int)$query->num_rows();
      if ($lNumRecs == 0){
         return(0.0);
      }else {
         $row = $query->row();
         return((float)$row->dCurTot);
      }
   }

   function dHoursLogged(&$cprog, $bCurrentlyEnrolledOnly, &$hourInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $hourInfo = new stdClass;
      $dTot = 0.0;

      $strETable        = $cprog->strEnrollmentTable;
      $strEFNPrefix     = $cprog->strETableFNPrefix;
      $strATable        = $cprog->strAttendanceTable;
      $lATableID        = $cprog->lAttendanceTableID;
      $strAFNPrefix     = $cprog->strATableFNPrefix;
      $lActivityFieldID = $cprog->lActivityFieldID;
      $strActivityFN    = $cprog->strActivityFN;

      if ($bCurrentlyEnrolledOnly){
         $strCurOnly = " AND $strEFNPrefix"."_bCurrentlyEnrolled ";
      }else {
         $strCurOnly = '';
      }

      $sqlStr =
        "SELECT SUM($strAFNPrefix"."_dDuration) AS dTot, ufddl_lKeyID, ufddl_strDDLEntry
         FROM $strATable
            INNER JOIN $strETable ON $strAFNPrefix"."_lEnrollID = $strEFNPrefix"."_lKeyID
            LEFT  JOIN uf_ddl     ON ufddl_lKeyID               = $strActivityFN

         WHERE NOT $strEFNPrefix"."_bRetired
            AND NOT $strAFNPrefix"."_bRetired
         GROUP BY ufddl_lKeyID
         ORDER BY ufddl_strDDLEntry, ufddl_lKeyID;";


      $query = $this->db->query($sqlStr);
      $hourInfo->lNumActivities = $numRows = $query->num_rows();
      $hourInfo->activities = array();
      if ($numRows > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $hourInfo->activities[$idx] = new stdClass;
            $hrInfo = &$hourInfo->activities[$idx];
            $hrInfo->strActivity = $row->ufddl_strDDLEntry;
            $hrInfo->lActivityID = (int)$row->ufddl_lKeyID;
            $hrInfo->dDuration   = (float)$row->dTot;
            $dTot += $hrInfo->dDuration;
            ++$idx;
         }
      }
      $hourInfo->dTot = $dTot;
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$hourInfo   <pre>');
echo(htmlspecialchars( print_r($hourInfo, true))); echo('</pre></font><br>');
// ------------------------------------- */

      return((float)$dTot);
   }

   function clientsWithAttendanceInMonth(&$cprog, $lMonth, $lYear,
                    &$lNumClients, &$clientIDs){
   //---------------------------------------------------------------------
   // return an array of client IDs who have at least one attendance
   // record during the specified month
   //---------------------------------------------------------------------
      $lNumClients = 0;
      $clientIDs = array();

      $strETable = $cprog->strEnrollmentTable;
      $strEFNPre = $cprog->strETableFNPrefix;
      $lETableID = $cprog->lEnrollmentTableID;

      $strATable = $cprog->strAttendanceTable;
      $strAFNPre = $cprog->strATableFNPrefix;
      $lATableID = $cprog->lAttendanceTableID;

      $strBRetired = $strEFNPre.'_bRetired';

      $sqlStr =
           "SELECT DISTINCT $strEFNPre"."_lForeignKey AS lClientID
            FROM $strETable
               INNER JOIN $strATable ON $strAFNPre"."_lEnrollID=$strEFNPre"."_lKeyID
            WHERE 1
               AND NOT $strEFNPre"."_bRetired
               AND NOT $strAFNPre"."_bRetired \n"
               .$this->strAttendanceViaMonthWhere($cprog, $lMonth, $lYear)."
            ORDER BY $strEFNPre"."_lForeignKey;";

      $query = $this->db->query($sqlStr);
      $lNumClients = $query->num_rows();
      if ($lNumClients > 0) {
         foreach ($query->result() as $row){
            $clientIDs[] = (int)$row->lClientID;
         }
      }
   }

   function clientsEnrolledInMonth(&$cprog, $lMonth, $lYear,
                    &$lNumClients, &$clientIDs){
   //---------------------------------------------------------------------
   // return an array of client IDs for clients who were enrolled in
   // the specified Client Program during the specified month
   //---------------------------------------------------------------------
      $lNumClients = 0;
      $clientIDs = array();

      $strETable = $cprog->strEnrollmentTable;
      $strEFNPre = $cprog->strETableFNPrefix;
      $lETableID = $cprog->lEnrollmentTableID;

      $strATable = $cprog->strAttendanceTable;
      $strAFNPre = $cprog->strATableFNPrefix;
      $lATableID = $cprog->lAttendanceTableID;

      $strBRetired = $strEFNPre.'_bRetired';

      $sqlStr =
           "SELECT DISTINCT $strEFNPre"."_lForeignKey AS lClientID
            FROM $strETable
            WHERE 1
               AND NOT $strEFNPre"."_bRetired "
               .$this->strEnrollmentViaMonthWhere($cprog, $lMonth, $lYear)."
            ORDER BY $strEFNPre"."_lForeignKey;";

      $query = $this->db->query($sqlStr);
      $lNumClients = $query->num_rows();
      if ($lNumClients > 0) {
         foreach ($query->result() as $row){
            $clientIDs[] = (int)$row->lClientID;
         }
      }
   }


   function enrollmentsWithAttendanceInMonth(&$cprog, $lMonth, $lYear,
                    &$lNumEnroll, &$enrollIDs){
   //---------------------------------------------------------------------
   // return an array of enrollment IDs who have at least one attendance
   // record during the specified month
   //---------------------------------------------------------------------
      $lNumEnroll = 0;
      $enrollIDs = array();

      $strETable = $cprog->strEnrollmentTable;
      $strEFNPre = $cprog->strETableFNPrefix;
      $lETableID = $cprog->lEnrollmentTableID;

      $strATable = $cprog->strAttendanceTable;
      $strAFNPre = $cprog->strATableFNPrefix;
      $lATableID = $cprog->lAttendanceTableID;

      $strBRetired = $strEFNPre.'_bRetired';

      $sqlStr =
           "SELECT DISTINCT $strEFNPre"."_lKeyID AS lEnrollID
            FROM $strETable
               INNER JOIN $strATable ON $strAFNPre"."_lEnrollID=$strEFNPre"."_lKeyID
            WHERE 1
               AND NOT $strEFNPre"."_bRetired
               AND NOT $strAFNPre"."_bRetired \n"
               .$this->strAttendanceViaMonthWhere($cprog, $lMonth, $lYear)."
            ORDER BY $strEFNPre"."_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumEnroll = $query->num_rows();
      if ($lNumEnroll > 0) {
         foreach ($query->result() as $row){
            $enrollIDs[] = (int)$row->lEnrollID;
         }
      }
   }

   function enrollmentsActiveInMonth(&$cprog, $lMonth, $lYear,
                    &$lNumEnroll, &$enrollIDs, $strWhereExtra=''){
   //---------------------------------------------------------------------
   // return an array of enrollment IDs for enrollees actively enrolled
   // during the month
   //---------------------------------------------------------------------
      $lNumEnroll = 0;
      $enrollIDs = array();

      $strETable = $cprog->strEnrollmentTable;
      $strEFNPre = $cprog->strETableFNPrefix;
      $lETableID = $cprog->lEnrollmentTableID;

      $strBRetired = $strEFNPre.'_bRetired';

      $sqlStr =
           "SELECT DISTINCT $strEFNPre"."_lKeyID AS lEnrollID
            FROM $strETable
            WHERE NOT $strBRetired "
               .$strWhereExtra
               .$this->strActivelyEnrolledDuringMonthWhere($cprog, $lMonth, $lYear)."
            ORDER BY $strEFNPre"."_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumEnroll = $query->num_rows();
      if ($lNumEnroll > 0) {
         foreach ($query->result() as $row){
            $enrollIDs[] = (int)$row->lEnrollID;
         }
      }
   }

   function strAttendanceViaMonthWhere(&$cprog, $lMonth, $lYear){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strAFNPre = $cprog->strATableFNPrefix;
      return(" AND MONTH($strAFNPre"."_dteAttendance) = $lMonth
               AND YEAR($strAFNPre"."_dteAttendance) = $lYear \n");
   }

   function strEnrollmentViaMonthWhere(&$cprog, $lMonth, $lYear){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strEFNPre = $cprog->strETableFNPrefix;
      return(" AND MONTH($strEFNPre"."_dteStart) = $lMonth
               AND YEAR ($strEFNPre"."_dteStart) = $lYear \n");
   }

   function strActivelyEnrolledDuringMonthWhere(&$cprog, $lMonth, $lYear){
   //---------------------------------------------------------------------
   // find the enrollees whose start date is before the end of the month
   // and whose inactive date is either null or after the first of the
   // month.
   //---------------------------------------------------------------------
      $lMaxDays = lDaysInMonth($lMonth, $lYear);
      $dteLastSecOfMonth = mktime(23, 59, 59, $lMonth, $lMaxDays, $lYear);
      $mdteLastSec  = strPrepDateTime($dteLastSecOfMonth);
      $mdteFirstSec = strPrepDate(mktime(0, 0, 0, $lMonth, 1, $lYear));

      $strEFNPre = $cprog->strETableFNPrefix;
      $strDateStart = $strEFNPre.'_dteStart';
      $strDateEnd   = $strEFNPre.'_dteEnd';
      return(" AND ($strDateStart <= $mdteLastSec)
               AND (($strDateEnd IS NULL) OR ($strDateEnd >= $mdteFirstSec)) \n");
   }

   function strActivelyEnrolledDuringTimeFrameWhere(&$cprog, $dteStart, $dteEnd){
   //---------------------------------------------------------------------
   // find the enrollees whose start date is before the end of the month
   // and whose inactive date is either null or after the first of the
   // month.
   //---------------------------------------------------------------------
//      $lMaxDays = lDaysInMonth($lMonth, $lYear);
//      $dteLastSecOfMonth = mktime(23, 59, 59, $lMonth, $lMaxDays, $lYear);
      $mdteLastSec  = strPrepDateTime($dteEnd);
      $mdteFirstSec = strPrepDateTime($dteStart);

      $strEFNPre = $cprog->strETableFNPrefix;
      $strDateStart = $strEFNPre.'_dteStart';
      $strDateEnd   = $strEFNPre.'_dteEnd';
      return(" AND ($strDateStart <= $mdteLastSec)
               AND (($strDateEnd IS NULL) OR ($strDateEnd >= $mdteFirstSec)) \n");
   }



      /*-------------------------------------------------------
            M E N T O R  /  M E N T E E   S U P P O R T
      -------------------------------------------------------*/


      /*-------------------------------------------------------
            U T I L I T I E S
      -------------------------------------------------------*/
   function buildCProgDDLSql(&$aeField, &$strInner, &$strSelect, &$lCntDDLFields, &$ddlConsolidated){
   //---------------------------------------------------------------------
   //  $aeField is an array of stdClass with the following fields:
   //    lFieldID
   //    strFieldNameInternal
   //    strFieldNameUser
   //    enumFieldType
   //---------------------------------------------------------------------
      $lCntDDLFields = 0;
      $ddlConsolidated = array();
      $strInner = $strSelect = '';

      if (count($aeField) > 0){
         foreach ($aeField as $ae){
            if ($ae->enumFieldType == CS_FT_DDL){
               $lFieldID = $ae->lFieldID;
               $strMultiTable = 'uf_ddl_'.$lFieldID;
               $strInner  .= "LEFT JOIN uf_ddl AS $strMultiTable ON $strMultiTable.ufddl_lKeyID = $ae->strFieldNameInternal \n";
               $strSelect .= ", $strMultiTable.ufddl_strDDLEntry AS '$ae->strFieldNameUser' \n";

               $ddlConsolidated[$lCntDDLFields] = new stdClass;
               $d = &$ddlConsolidated[$lCntDDLFields];
               $d->lFieldID             = $lFieldID;
               $d->strFieldNameInternal = $ae->strFieldNameInternal;
               $d->strFieldNameUser     = $ae->strFieldNameUser;
               ++$lCntDDLFields;
            }
         }
      }
      if ($strSelect != '') {
         $strSelect = substr($strSelect, 1);
      }
   }

   function extractCProgFields($cprog,
                                       &$lEnrollmentTableID,
                                       &$lAttendanceTableID,
                                       &$strEnrollmentTable,
                                       &$strETableFNPrefix,
                                       &$strAttendanceTable,
                                       &$strATableFNPrefix){
   //---------------------------------------------------------------------
   // sample calling sequence...
   //   $this->extractCProgFields($cprog,
   //            $lETableID, $lATableID, $strETable, $strEFNPrefix, $strATable,$strAFNPrefix);
   //---------------------------------------------------------------------
      $lEnrollmentTableID = $cprog->lEnrollmentTableID;
      $lAttendanceTableID = $cprog->lAttendanceTableID;
      $strEnrollmentTable = $cprog->strEnrollmentTable;
      $strETableFNPrefix  = $cprog->strETableFNPrefix;
      $strAttendanceTable = $cprog->strAttendanceTable;
      $strATableFNPrefix  = $cprog->strATableFNPrefix;
   }

   function cprogramEnrollmentsViaClientID($lClientID, &$lNumEnrolls, &$enrollments){
   //---------------------------------------------------------------------
   // find all the client programs that the specified client has been
   // enrolled in.
   //
   // Caller must have previously called $this->loadClientPrograms($bShowHidden=true)
   //---------------------------------------------------------------------
      $lNumEnrolls = 0;
      $enrollments = array();
      if ($this->lNumCProgs == 0) return;
      foreach ($this->cprogs as $cprog){
         $strETable    = $cprog->strEnrollmentTable;
         $strEFNPrefix = $cprog->strETableFNPrefix;
         $strFIDFN     = $strEFNPrefix.'_lForeignKey';
         $strKeyFN     = $strEFNPrefix.'_lKeyID';
         $strRetiredFN = $strEFNPrefix.'_bRetired';

         $sqlStr =
            "SELECT $strKeyFN AS lEnrollID
             FROM $strETable
             WHERE $strFIDFN=$lClientID AND NOT $strRetiredFN
             ORDER BY $strKeyFN;";
         $query = $this->db->query($sqlStr);
         $numRows = $query->num_rows();
         if ($numRows > 0) {
            $enrollments[$lNumEnrolls] = new stdClass;
            $enroll = &$enrollments[$lNumEnrolls];

            $enroll->lCProgID           = $cprog->lKeyID;
            $enroll->lEnrollmentTableID = $cprog->lEnrollmentTableID;
            $enroll->strCProgName       = $cprog->strProgramName;
            $enroll->enrollIDs          = array();
            $idx = 0;
            foreach ($query->result() as $row){
               $enroll->enrollIDs[$idx] = $row->lEnrollID;
               ++$idx;
            }
            ++$lNumEnrolls;
         }
      }
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$enrollments   <pre>');
echo(htmlspecialchars( print_r($enrollments, true))); echo('</pre></font><br>');

echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$this->cprogs   <pre>');
echo(htmlspecialchars( print_r($this->cprogs, true))); echo('</pre></font><br>');
die;
// ------------------------------------- */

   }

   function moveAttendance($strATable, $strATableFNPrefix, $lARecID, $lDestinationERecID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
           "UPDATE $strATable
            SET
               ".$strATableFNPrefix."_lEnrollID = $lDestinationERecID,
               ".$strATableFNPrefix."_lLastUpdateID = $glUserID
            WHERE ".$strATableFNPrefix."_lKeyID=$lARecID;";
      $query = $this->db->query($sqlStr);
   }




}




