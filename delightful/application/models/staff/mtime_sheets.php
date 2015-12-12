<?php
/*---------------------------------------------------------------------
// copyright (c) 2014-2015 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('staff/mtime_sheets', 'cts');
---------------------------------------------------------------------*/

class mtime_sheets extends CI_Model{

   public $lNumTST, $timeSheetTemplates, $sqlWhere, $sqlOrder;

	function __construct(){
		parent::__construct();

      $this->lNumTST = 0;
      $this->timeSheetTemplates = null;
      $this->sqlWhere = $this->sqlOrder = '';
	}

   public function loadTimeSheetTemplateViaTSTID($lTSTID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND ts_lKeyID=$lTSTID ";
      $this->loadTimeSheetTemplates();
   }

   public function loadTimeSheetTemplates(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->timeSheetTemplates = array();
      $sqlStr =
        "SELECT
            ts_lKeyID, ts_strTSName, ts_lFirstDayOfWeek,
            ts_strNotes, ts_strAckText,
            ts_b24HrTime, ts_enumRptPeriod, ts_enumGranularity, ts_bHidden,
            UNIX_TIMESTAMP(ts_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(ts_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM staff_timesheets
            INNER JOIN admin_users   AS uc ON uc.us_lKeyID=ts_lOriginID
            INNER JOIN admin_users   AS ul ON ul.us_lKeyID=ts_lLastUpdateID

         WHERE NOT ts_bRetired $this->sqlWhere
         ORDER BY ts_strTSName, ts_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumTST = $numRows = $query->num_rows();
      if ($numRows > 0) {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->timeSheetTemplates[$idx] = new stdClass;
            $tst = &$this->timeSheetTemplates[$idx];

            $tst->lKeyID          = (int)$row->ts_lKeyID;
            $tst->strTSName       = $row->ts_strTSName;
            $tst->lFirstDayOfWeek = (int)$row->ts_lFirstDayOfWeek;
            $tst->strNotes        = $row->ts_strNotes;
            $tst->strAckText      = $row->ts_strAckText;
            $tst->b24HrTime       = $row->ts_b24HrTime;
            $tst->enumRptPeriod   = $row->ts_enumRptPeriod;
            $tst->enumGranularity = $row->ts_enumGranularity;
            $tst->bHidden         = $row->ts_bHidden;

            $tst->dteOrigin       = (int)$row->dteOrigin;
            $tst->dteLastUpdate   = (int)$row->dteLastUpdate;
            $tst->strUCFName      = $row->strUCFName;
            $tst->strUCLName      = $row->strUCLName;
            $tst->strULFName      = $row->strULFName;
            $tst->strULLName      = $row->strULLName;

            ++$idx;
         }
      }
   }

   function addTimeSheetTemplate(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
          'INSERT INTO staff_timesheets
           SET '.$this->sqlCommonAddUpdate().",
              ts_bRetired  = 0,
              ts_lOriginID = $glUserID,
              ts_dteOrigin = NOW();";
      $query = $this->db->query($sqlStr);
      return((int)$this->db->insert_id());
   }

   function updateTimeSheetTemplate($lTSTID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
          'UPDATE staff_timesheets
           SET '.$this->sqlCommonAddUpdate()."
           WHERE ts_lKeyID=$lTSTID;";
      $query = $this->db->query($sqlStr);
   }

   private function sqlCommonAddUpdate(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $tst = &$this->timeSheetTemplates[0];
      return('
         ts_strTSName       = '.strPrepStr($tst->strTSName).',
         ts_strNotes        = '.strPrepStr($tst->strNotes).',
         ts_strAckText      = '.strPrepStr($tst->strAckText).',
         ts_enumRptPeriod   = '.strPrepStr($tst->enumRptPeriod).',
         ts_enumGranularity = '.strPrepStr($tst->enumGranularity).',
         ts_b24HrTime       = '.($tst->b24HrTime ? '1' : '0').',
         ts_bHidden         = '.($tst->bHidden   ? '1' : '0').",
         ts_lFirstDayOfWeek = $tst->lFirstDayOfWeek,
         ts_lLastUpdateID   = $glUserID,
         ts_dteLastUpdate   = NOW() ");
   }

   /*----------------------------------------------
             S T A F F   M E M B E R S
   ----------------------------------------------*/

   function lStaffTSAssignment($lUserID, &$strTemplateName){
   //---------------------------------------------------------------------
   // return the time sheet template ID that a user is assigned to
   //---------------------------------------------------------------------
      $strTemplateName = '';
      $sqlStr =
          "SELECT tss_lTimeSheetID, ts_strTSName
           FROM staff_ts_staff
              INNER JOIN staff_timesheets ON ts_lKeyID=tss_lTimeSheetID
           WHERE tss_lStaffID=$lUserID;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows > 0) {
         $row = $query->row();
         $strTemplateName = $row->ts_strTSName;
         return((int)$row->tss_lTimeSheetID);
      }else {
         return(null);
      }
   }

/*
   function lStaffTSAdmin($lUserID, $lTSTempateID){
   //---------------------------------------------------------------------
   // return true if a user is an admin for the specified time sheet
   // template
   //---------------------------------------------------------------------
      $sqlStr =
          "SELECT tsa_lKeyID
           FROM staff_ts_admin
           WHERE tsa_lStaffID=$lUserID AND tsa_lTimeSheetID=$lTSTempateID;";
      $query = $this->db->query($sqlStr);
      return($query->num_rows() > 0);
   }
*/
   function removeTSUsersViaTSTID($lTSTID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           "DELETE FROM staff_ts_staff
            WHERE tss_lTimeSheetID=$lTSTID;";
      $query = $this->db->query($sqlStr);
   }

   function addTSUsersViaTSTID($lTSTID, $lUserIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'INSERT INTO staff_ts_staff (
                tss_lTimeSheetID, tss_lOriginID, tss_lLastUpdateID,
                tss_dteOrigin, tss_dteLastUpdate, tss_lStaffID)
                VALUES ';
      $strValuesBase =
           "\n($lTSTID, $glUserID, $glUserID, NOW(), NOW(), ";
      foreach ($lUserIDs as $lUserID){
         $sqlStr .= $strValuesBase.$lUserID.'), ';
      }
      $sqlStr = substr($sqlStr, 0, strlen($sqlStr)-2).';';
      $query = $this->db->query($sqlStr);
   }

/*
   function removeTSAdminsViaTSTID($lTSTID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           "DELETE FROM staff_ts_admin
            WHERE tsa_lTimeSheetID=$lTSTID;";
      $query = $this->db->query($sqlStr);
   }
*/
   function removeTSAdmins(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           "DELETE FROM staff_ts_admin WHERE 1;";
      $query = $this->db->query($sqlStr);
   }

   function addTSAdmins($lUserIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'INSERT INTO staff_ts_admin (
                tsa_lOriginID, tsa_lLastUpdateID,
                tsa_dteOrigin, tsa_dteLastUpdate, tsa_lStaffID)
                VALUES ';
      $strValuesBase =
           "\n($glUserID, $glUserID, NOW(), NOW(), ";
      foreach ($lUserIDs as $lUserID){
         $sqlStr .= $strValuesBase.$lUserID.'), ';
      }
      $sqlStr = substr($sqlStr, 0, strlen($sqlStr)-2).';';
      $query = $this->db->query($sqlStr);
   }



   /*----------------------------------------------
            T I M E   S H E E T   L O G S
   ----------------------------------------------*/

   function lAddNewTSLog($lUserID, $lTSTID, $dteStartingDate){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         "INSERT INTO staff_ts_log
          SET
             tsl_lTimeSheetID   = $lTSTID,
             tsl_lStaffID       = $lUserID,
             tsl_lOriginID      = $glUserID,
             tsl_lLastUpdateID  = $glUserID,
             tsl_dteTSEntry     = ".strPrepDate($dteStartingDate).',
             tsl_dteSubmitted   = NULL,
             tsl_dteOrigin      = NOW(),
             tsl_dteLastUpdate  = NOW();';
      $query = $this->db->query($sqlStr);
      return((int)$this->db->insert_id());
   }

   function loadUserTSLogByYear($lUserID, $lYear, &$lNumLogRecs, &$logRecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND tsl_lStaffID=$lUserID AND YEAR(tsl_dteTSEntry)=$lYear ";
      $this->sqlOrder = ' tsl_dteTSEntry ';
      $this->loadUserTSLog($lNumLogRecs, $logRecs);
   }

   function loadUserTSLogByLogID($lTSLogID, &$lNumLogRecs, &$logRecs, $bLoadDailyEntries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND tsl_lKeyID=$lTSLogID ";
      $this->sqlOrder = ' tsl_dteTSEntry ';
      $this->loadUserTSLog($lNumLogRecs, $logRecs, $bLoadDailyEntries);
   }

   function loadUserTSLogUserDateTST($lTSTID, $lUserID, $dteLookup, &$lNumLogRecs, &$logRecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND tsl_lStaffID=$lUserID
                    AND tsl_lTimeSheetID=$lTSTID
                    AND tsl_dteTSEntry = ".strPrepDate($dteLookup).' ';
      $this->loadUserTSLog($lNumLogRecs, $logRecs);
   }

   function loadUserTSLog(&$lNumLogRecs, &$logRecs, $bLoadDailyEntries = false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumLogRecs = 0;
      $logRecs = array();

      if ($this->sqlOrder == ''){
         $strOrder = ' uts.us_strLastName, uts.us_strFirstName, tsl_lStaffID, tsl_dteSubmitted DESC ';
      }else {
         $strOrder = $this->sqlOrder;
      }

      $sqlStr =
        "SELECT
            tsl_lKeyID, tsl_lTimeSheetID, tsl_lStaffID,
            tsl_dteTSEntry, tsl_dteSubmitted,

            uts.us_strFirstName AS strUserFName, uts.us_strLastName AS strUserLName,

            ts_strTSName, ts_enumRptPeriod, ts_lFirstDayOfWeek, ts_b24HrTime, ts_enumGranularity,
            ts_bHidden,

            tsl_lOriginID, tsl_lLastUpdateID,
            UNIX_TIMESTAMP(tsl_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(tsl_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM staff_ts_log
            INNER JOIN staff_timesheets ON ts_lKeyID=tsl_lTimeSheetID
            INNER JOIN admin_users   AS uc  ON uc.us_lKeyID=ts_lOriginID
            INNER JOIN admin_users   AS ul  ON ul.us_lKeyID=ts_lLastUpdateID
            INNER JOIN admin_users   AS uts ON uts.us_lKeyID=tsl_lStaffID

         WHERE 1 $this->sqlWhere
         ORDER BY $strOrder;";

      $query = $this->db->query($sqlStr);
      $lNumLogRecs = $query->num_rows();
      if ($lNumLogRecs > 0) {
         $idx = 0;
         foreach ($query->result() as $row) {
            $logRecs[$idx] = new stdClass;
            $tsl = &$logRecs[$idx];

            $tsl->lKeyID          = $lTSLogID = (int)$row->tsl_lKeyID;
            $tsl->lTimeSheetID    = (int)$row->tsl_lTimeSheetID;
            $tsl->lStaffID        = (int)$row->tsl_lStaffID;
            $tsl->dteTSEntry      = dteMySQLDate2Unix($row->tsl_dteTSEntry);
            $tsl->strDteTSEntry   = date('m/d/Y H:i:s', $tsl->dteTSEntry);
            $tsl->dteSubmitted    = dteMySQLDate2Unix($row->tsl_dteSubmitted);
            $tsl->strUserFName    = $row->strUserFName;
            $tsl->strUserLName    = $row->strUserLName;
            $tsl->strTSName       = $row->ts_strTSName;
            $tsl->lFirstDayOfWeek = $row->ts_lFirstDayOfWeek;
            $tsl->b24HrTime       = $row->ts_b24HrTime;
            $tsl->enumRptPeriod   = $row->ts_enumRptPeriod;
            $tsl->enumGranularity = $row->ts_enumGranularity;
            $tsl->bHidden         = (boolean)$row->ts_bHidden;

            $tsl->dteOrigin       = (int)$row->dteOrigin;
            $tsl->dteLastUpdate   = (int)$row->dteLastUpdate;
            $tsl->strUCFName      = $row->strUCFName;
            $tsl->strUCLName      = $row->strUCLName;
            $tsl->strULFName      = $row->strULFName;
            $tsl->strULLName      = $row->strULLName;

            $tsl->lMinutesTot     = $this->lCumulativeMinutesViaLogSheet($lTSLogID);
            $tsl->lMinutesTotProj = $this->lProjectCumulativeMinsViaLogSheet($lTSLogID);

            if ($bLoadDailyEntries){
               $this->loadTSLogEntriesViaLogID($lTSLogID, $tsl->lNumEntries, $tsl->logEntries);
            }

            ++$idx;
         }
      }
   }

   function submitTSLog($lTSLogID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
        "UPDATE staff_ts_log
         SET tsl_dteSubmitted=NOW(),
            tsl_lLastUpdateID=$glUserID
         WHERE tsl_lKeyID=$lTSLogID;";
      $query = $this->db->query($sqlStr);
   }

   function unsubmitTimeSheet($lTSLogID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
        "UPDATE staff_ts_log
         SET tsl_dteSubmitted=NULL,
            tsl_lLastUpdateID=$glUserID
         WHERE tsl_lKeyID=$lTSLogID;";
      $query = $this->db->query($sqlStr);
   }

   /*----------------------------------------------
     T I M E   S H E E T   L O G    E N T R I E S
   ----------------------------------------------*/
   function loadTSLogEntriesViaLogID($lTSLogID, &$lNumEntries, &$logEntries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND tsle_lTSLogID = $lTSLogID ";
      $this->loadTSLogEntries($lNumEntries, $logEntries);
   }

   function loadTSLogEntriesViaEntryID($lEntryID, &$lNumEntries, &$logEntries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND tsle_lKeyID = $lEntryID ";
      $this->loadTSLogEntries($lNumEntries, $logEntries);
   }

   function loadTSLogEntries(&$lNumEntries, &$logEntries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumEntries = 0;
      $logEntries  = array();

      $sqlStr =
        "SELECT
            tsle_lKeyID, tsle_lTSLogID,
            tsle_dteLogEntry,
            tsle_tmTimeIn, tsle_tmTimeOut,

            tsle_lLocationID, gp_strGroupName AS strLocation,
            tsle_strNotes,

            tsl_lTimeSheetID, tsl_lStaffID,
            UNIX_TIMESTAMP(tsl_dteSubmitted) AS dteSubmitted,

            tsle_lOriginID, tsle_lLastUpdateID,
            UNIX_TIMESTAMP(tsle_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(tsle_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName
         FROM staff_ts_log_entry
            INNER JOIN staff_ts_log  ON tsle_lTSLogID=tsl_lKeyID
            INNER JOIN groups_parent ON gp_lKeyID = tsle_lLocationID
            INNER JOIN admin_users   AS uc  ON uc.us_lKeyID=tsle_lOriginID
            INNER JOIN admin_users   AS ul  ON ul.us_lKeyID=tsle_lLastUpdateID
         WHERE 1 $this->sqlWhere
         ORDER BY tsle_dteLogEntry, tsle_tmTimeIn, tsle_tmTimeOut, tsle_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumEntries = $query->num_rows();
      if ($lNumEntries > 0) {
         $idx = 0;
         foreach ($query->result() as $row) {
            $logEntries[$idx] = new stdClass;
            $le = &$logEntries[$idx];

            $le->lKeyID         = $row->tsle_lKeyID;
            $le->lTSLogID       = $row->tsle_lTSLogID;
//            $le->dteLogEntry    = $row->dteLogEntry;
               // weird bug - the UNIX_TIMESTAMP function returns a time of 1:00am for a Date field (expected 0:00)
            $le->dteLogEntry    = dteMySQLDate2Unix($row->tsle_dteLogEntry);
$le->strDteLogEntry = date('m/d/Y H:i:s', $le->dteLogEntry);

            $le->tmTimeIn       = $row->tsle_tmTimeIn;
            $le->tmTimeOut      = $row->tsle_tmTimeOut;
            $le->lTimeInMin     = lMySQLTimeToMinutes($le->tmTimeIn);
            $le->lTimeOutMin    = lMySQLTimeToMinutes($le->tmTimeOut);

            $le->lLocationID    = $row->tsle_lLocationID;
            $le->strLocation    = $row->strLocation;
            $le->strNotes       = $row->tsle_strNotes;

            $le->lTimeSheetID   = $row->tsl_lTimeSheetID;
            $le->lStaffID       = $row->tsl_lStaffID;
            $le->dteSubmitted   = $row->dteSubmitted;
            $le->bSubmitted     = !is_null($row->dteSubmitted);

            $le->lOriginID      = $row->tsle_lOriginID;
            $le->lLastUpdateID  = $row->tsle_lLastUpdateID;
            $le->dteOrigin      = $row->dteOrigin;
            $le->dteLastUpdate  = $row->dteLastUpdate;
            $le->strUCFName     = $row->strUCFName;
            $le->strUCLName     = $row->strUCLName;
            $le->strULFName     = $row->strULFName;
            $le->strULLName     = $row->strULLName;

            ++$idx;
         }
      }
   }

   function loadTSEntriesForOverlapTest($lExcludeID, $dteLogEntry, $lLogID, &$lNumEntries, &$logEntries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            tsle_lKeyID,
            tsle_tmTimeIn, tsle_tmTimeOut
         FROM staff_ts_log_entry
         WHERE (tsle_lTSLogID = $lLogID)
            AND (tsle_dteLogEntry = ".strPrepDate($dteLogEntry).")
            AND (tsle_lKeyID != $lExcludeID)
         ORDER BY tsle_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumEntries = $query->num_rows();
      if ($lNumEntries > 0) {
         $idx = 0;
         foreach ($query->result() as $row) {
            $logEntries[$idx] = new stdClass;
            $le = &$logEntries[$idx];

            $le->lKeyID       = $row->tsle_lKeyID;
            $le->bValidEntry  = true;

            $le->timeIn       = lMySQLTimeToMinutes($row->tsle_tmTimeIn);
            $le->timeOut      = lMySQLTimeToMinutes($row->tsle_tmTimeOut);

            ++$idx;
         }
      }
   }

   function lCumulativeMinutesViaLogSheet($lTSLogID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lSumMinutes = 0.0;
      $sqlStr =
        "SELECT TIMEDIFF(tsle_tmTimeOut, tsle_tmTimeIn) AS tDiff
         FROM staff_ts_log_entry
         WHERE tsle_lTSLogID=$lTSLogID;";

      $query = $this->db->query($sqlStr);
      $lNumRecs = $query->num_rows();
      if ($lNumRecs > 0) {
         foreach ($query->result() as $row) {
            $tFields = explode(':', $row->tDiff);
            $lSumMinutes += ((int)$tFields[0])*60 + (int)$tFields[1];
         }
      }
      return($lSumMinutes);
   }

   function addTSEntry($lTSLogID, $dteEntry, &$entry){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        'INSERT INTO staff_ts_log_entry
         SET
            tsle_dteLogEntry = '.strPrepDate($dteEntry).',
            tsle_tmTimeIn    = '.strPrepStr(strMinutesToMySQLTime($entry->timeIn)).',
            tsle_tmTimeOut   = '.strPrepStr(strMinutesToMySQLTime($entry->timeOut)).',
            tsle_strNotes    = '.strPrepStr($entry->notes).',
            tsle_lLocationID = '.$entry->location.",
            tsle_lTSLogID      = $lTSLogID,
            tsle_lOriginID     = $glUserID,
            tsle_lLastUpdateID = $glUserID,
            tsle_dteOrigin     = NOW(),
            tsle_dteLastUpdate = NOW();";

      $query = $this->db->query($sqlStr);
      return((int)$this->db->insert_id());
   }

   function updateTSEntry($lEntryID, $entry){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        'UPDATE staff_ts_log_entry
         SET
            tsle_tmTimeIn    = '.strPrepStr(strMinutesToMySQLTime($entry->timeIn)).',
            tsle_tmTimeOut   = '.strPrepStr(strMinutesToMySQLTime($entry->timeOut)).',
            tsle_strNotes    = '.strPrepStr($entry->notes).',
            tsle_lLocationID = '.$entry->location.",
            tsle_lLastUpdateID = $glUserID,
            tsle_dteLastUpdate = NOW()
         WHERE tsle_lKeyID=$lEntryID;";

      $query = $this->db->query($sqlStr);
   }

   function removeTSEntry($lEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "DELETE FROM staff_ts_log_entry
         WHERE tsle_lKeyID = $lEntryID;";
      $query = $this->db->query($sqlStr);
   }

   /*----------------------------------------------
            T I M E S H E E T   A D M I N
   ----------------------------------------------*/
/*
   function bIsUserAuthorizedToViewEdit($lTSTID, $lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM staff_ts_admin
         WHERE tsa_lTimeSheetID = $lTSTID
            AND tsa_lStaffID = $lUserID;";

      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs > 0);
   }
*/
   function bIsUserTSAdmin($lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM staff_ts_admin
         WHERE tsa_lStaffID = $lUserID;";

      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs > 0);
   }

   function loadMappedUsers(&$lNumUsers, &$users){
   //---------------------------------------------------------------------
   // load all users who are mapped to any time sheet template
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT
            tss_lKeyID, tss_lTimeSheetID, tss_lStaffID,
            tss_lOriginID, tss_lLastUpdateID,
            UNIX_TIMESTAMP(tss_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(tss_dteLastUpdate) AS dteLastUpdate,
            ts_strTSName,
            us_strLastName, us_strFirstName
         FROM staff_ts_staff
            INNER JOIN staff_timesheets ON tss_lTimeSheetID=ts_lKeyID
            INNER JOIN admin_users ON tss_lStaffID=us_lKeyID
         WHERE NOT ts_bRetired
         ORDER BY us_strLastName, us_strFirstName, tss_lKeyID;';

      $query = $this->db->query($sqlStr);
      $lNumUsers = $query->num_rows();
      if ($lNumUsers > 0) {
         $idx = 0;
         foreach ($query->result() as $row) {
            $users[$idx] = new stdClass;
            $user = &$users[$idx];

            $user->lStaffTSID           = $row->tss_lKeyID;
            $user->lTimeSheetID         = $row->tss_lTimeSheetID;
            $user->lStaffID             = $row->tss_lStaffID;
            $user->lOriginID            = $row->tss_lOriginID;
            $user->lLastUpdateID        = $row->tss_lLastUpdateID;
            $user->dteOrigin            = $row->dteOrigin;
            $user->dteLastUpdate        = $row->dteLastUpdate;
            $user->strTSName            = $row->ts_strTSName;
            $user->strLastName          = $row->us_strLastName;
            $user->strFirstName         = $row->us_strFirstName;

            ++$idx;
         }
      }
   }

   function lNumSubUnSubTSViaUserID($bSubmitted, $lUserID){
   //---------------------------------------------------------------------
   // return the number of submitted or unsubmitted time sheet logs
   // by userID
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM staff_ts_log
         WHERE tsl_lStaffID=$lUserID
            AND tsl_dteSubmitted IS ".($bSubmitted ? 'NOT' : '')." NULL;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }


   /*----------------------------------------------
                  P R O J E C T S
   ----------------------------------------------*/
   function lProjectCumulativeMinsViaLogSheet($lTSLogID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT SUM(tspr_lMinutesToProject) AS lNumMinutes
         FROM staff_ts_log_projects
         WHERE tspr_lTSLogID = $lTSLogID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumMinutes);
   }

   function lProjectMinutesViaProjLog($projectID, $lTSLogID, &$strNotes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT tspr_lMinutesToProject AS lNumMinutes, tspr_strNotes
         FROM staff_ts_log_projects
         WHERE tspr_lTSLogID = $lTSLogID
            AND tspr_lProjectID=$projectID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         $strNotes = '';
         return(0);
      }else {
         $row = $query->row();
         $strNotes = $row->tspr_strNotes;
         return((int)$row->lNumMinutes);
      }
   }

   function projectsViaLogID($lTSLogID, &$lNumProjects, &$projects){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumProjects = 0;
      $projects = array();

      $sqlStr =
        "SELECT tspr_lKeyID, tspr_lTSLogID, tspr_lProjectID,
           tspr_lMinutesToProject, tspr_strNotes, tspr_lOriginID,
           tspr_lLastUpdateID,
           gp_strGroupName AS strProjectName,
           UNIX_TIMESTAMP(tspr_dteOrigin)     AS dteOrigin,
           UNIX_TIMESTAMP(tspr_dteLastUpdate) AS dteLastUpdate

         FROM staff_ts_log_projects
            INNER JOIN groups_parent ON tspr_lProjectID=gp_lKeyID
         WHERE tspr_lTSLogID = $lTSLogID
         ORDER BY gp_strGroupName, tspr_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumProjects = $query->num_rows();
      if ($lNumProjects > 0) {
         $idx = 0;
         foreach ($query->result() as $row) {
            $projects[$idx] = new stdClass;
            $proj = &$projects[$idx];

            $proj->lKeyID            = $row->tspr_lKeyID;
            $proj->lTSLogID          = $row->tspr_lTSLogID;
            $proj->lProjectID        = $row->tspr_lProjectID;
            $proj->lMinutesToProject = $row->tspr_lMinutesToProject;
            $proj->strNotes          = $row->tspr_strNotes;
            $proj->lOriginID         = $row->tspr_lOriginID;
            $proj->lLastUpdateID     = $row->tspr_lLastUpdateID;
            $proj->strProjectName    = $row->strProjectName;
            $proj->dteOrigin         = $row->dteOrigin;
            $proj->dteLastUpdate     = $row->dteLastUpdate;

            ++$idx;
         }
      }
   }

   function updateProjectAssignments($lTSLogID, $projects){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // out with the old
      $sqlStr =
         "DELETE FROM staff_ts_log_projects
          WHERE tspr_lTSLogID=$lTSLogID;";
      $query = $this->db->query($sqlStr);

         // in with the new
      foreach ($projects as $project){
         if ($project->lMinutes > 0){
            $this->setMinutesToProject($lTSLogID, $project->lGroupID, $project->lMinutes, $project->notes);
         }
      }
   }

   function setMinutesToProject($lTSLogID, $projectID, $lMinutes, $strNotes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        'INSERT INTO staff_ts_log_projects
         SET
            tspr_strNotes          = '.strPrepStr($strNotes).",
            tspr_lTSLogID          = $lTSLogID,
            tspr_lProjectID        = $projectID,
            tspr_lMinutesToProject = $lMinutes,
            tspr_lOriginID         = $glUserID,
            tspr_lLastUpdateID     = $glUserID,
            tspr_dteOrigin         = NOW(),
            tspr_dteLastUpdate     = NOW();";
      $query = $this->db->query($sqlStr);
   }

}









