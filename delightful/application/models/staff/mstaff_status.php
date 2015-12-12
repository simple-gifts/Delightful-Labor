<?php
/*---------------------------------------------------------------------
// copyright (c) 2014 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('staff/mstaff_status', 'cstat');
---------------------------------------------------------------------*/

class mstaff_status extends CI_Model{

   public
      $lNumSReports, $sreports,
      $sqlWhere, $sqlOrder,
      $sqlWhereSections, $sqlOrderSections,
      $sqlWhereReviews, $sqlOrderReviews;

	function __construct(){
		parent::__construct();

      $this->lNumSReports = $this->sreports = null;
      $this->sqlWhere = $this->sqlOrder = '';
      $this->sqlWhereSections = $this->sqlOrderSections = '';

      $this->sqlWhereReviews = $this->sqlOrderReviews = '';
	}

   function loadStatusReportViaRptID($lStatusRptID, $bLoadSections = true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND ss_lKeyID=$lStatusRptID ";
      $this->loadStatusReports($bLoadSections);
   }

   function loadStatusReportViaUserID($lUserID, $bLoadSections = true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND ss_lUserID=$lUserID ";
      $this->sqlOrder = ' ss_bPublished, ss_dteSubmitDate DESC, ss_dteLastUpdate DESC, ss_lKeyID ';
      $this->loadStatusReports($bLoadSections);
   }

   function loadStatusDrafts($lUserID, $bLoadSections = false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND ss_lUserID=$lUserID AND NOT ss_bPublished ";
      $this->sqlOrder = ' ss_dteLastUpdate DESC, ss_lKeyID ';
      $this->loadStatusReports($bLoadSections);
   }

   function loadStatusReports($bLoadSections = true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->sqlOrder == ''){
         $strOrder = ' ss_lKeyID ';
      }else {
         $strOrder = $this->sqlOrder;
      }
      $sqlStr =
        "SELECT
            ss_lKeyID, ss_lUserID, ss_bPublished,
            UNIX_TIMESTAMP(ss_dteSubmitDate) AS dteSubmitDate,
            -- ss_strWAComplete, ss_strGoals, ss_strIssues, ss_strAccomp,
            ss_lOriginID, ss_lLastUpdateID,

            UNIX_TIMESTAMP(ss_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(ss_dteLastUpdate) AS dteLastUpdate,
            rpt.us_strFirstName AS strRptFName, rpt.us_strLastName AS strRptLName,
            uc.us_strFirstName  AS strUCFName,  uc.us_strLastName  AS strUCLName,
            ul.us_strFirstName  AS strULFName,  ul.us_strLastName  AS strULLName

         FROM aayhf_staff_status
            INNER JOIN admin_users   AS rpt ON rpt.us_lKeyID = ss_lUserID
            INNER JOIN admin_users   AS uc  ON uc.us_lKeyID  = ss_lOriginID
            INNER JOIN admin_users   AS ul  ON ul.us_lKeyID  = ss_lLastUpdateID

         WHERE 1 $this->sqlWhere
         ORDER BY $strOrder;";

      $query = $this->db->query($sqlStr);
      $this->lNumSReports = $numRows = $query->num_rows();
      $this->sreports = array();
      if ($numRows==0) {
         $this->sreports[0] = new stdClass;
         $sreport = &$this->sreports[0];

         $sreport->lKeyID         =
         $sreport->lUserID        =

         $sreport->bPublished     =
         $sreport->dteSubmitDate  =

         $sreport->strRptFName    =
         $sreport->strRptLName    =
         $sreport->strRptSafeName =

         $sreport->lOriginID      =
         $sreport->lLastUpdateID  =
         $sreport->dteOrigin      =
         $sreport->dteLastUpdate  =
         $sreport->strUCFName     =
         $sreport->strUCLName     =
         $sreport->strULFName     =
         $sreport->strULLName     = null;

         $this->initSectionsTable($sreport->sections);
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->sreports[$idx] = new stdClass;
            $sreport = &$this->sreports[$idx];

            $sreport->lKeyID         = $lStatID = (int)$row->ss_lKeyID;
            $sreport->lUserID        = (int)$row->ss_lUserID;

            $sreport->bPublished     = (boolean)$row->ss_bPublished;
            $sreport->dteSubmitDate  = (int)$row->dteSubmitDate;

            $this->initSectionsTable($sreport->sections);
            if ($bLoadSections) $this->loadSectionsViaStatID($lStatID, $sreport->sections);

            $sreport->strRptFName    = $row->strRptFName;
            $sreport->strRptLName    = $row->strRptLName;
            $sreport->strRptSafeName = htmlspecialchars($row->strRptFName.' '.$row->strRptLName);

            $sreport->lOriginID      = (int)$row->ss_lOriginID;
            $sreport->lLastUpdateID  = (int)$row->ss_lLastUpdateID;
            $sreport->dteOrigin      = (int)$row->dteOrigin;
            $sreport->dteLastUpdate  = (int)$row->dteLastUpdate;
            $sreport->strUCFName     = $row->strUCFName;
            $sreport->strUCLName     = $row->strUCLName;
            $sreport->strULFName     = $row->strULFName;
            $sreport->strULLName     = $row->strULLName;

            ++$idx;
         }
      }
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$this->sreports   <pre>');
echo(htmlspecialchars( print_r($this->sreports, true))); echo('</pre></font><br>');
// ------------------------------------- */
   }

   function addNewStatusReport(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sreport = &$this->sreports[0];

      $sqlStr =
         'INSERT INTO aayhf_staff_status
          SET '.$this->sqlCommonStatRpt().",
             ss_lUserID   = $glUserID,
             ss_lOriginID = $glUserID,
             ss_dteOrigin = NOW();";

      $query = $this->db->query($sqlStr);
      $sreport->lKeyID = $lKeyID = $this->db->insert_id();
      return($lKeyID);
   }

   function updateStatusReport($lSReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sreport = &$this->sreports[0];

      $sqlStr =
         'UPDATE aayhf_staff_status
          SET '.$this->sqlCommonStatRpt()."
          WHERE ss_lKeyID = $lSReportID;";

      $query = $this->db->query($sqlStr);
   }

   function initNewStatRptTemplate(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sreports = array();
      $this->sreports[0] = new stdClass;
      $sr = &$this->sreports[0];
      $sr->bPublished = false;
   }

   private function sqlCommonStatRpt(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sreport = &$this->sreports[0];

      if ($sreport->bPublished){
         $strDteSubmit = ' NOW() ';
      }else {
         $strDteSubmit = ' NULL ';
      }

      return(
         'ss_dteSubmitDate='.$strDteSubmit.',
          ss_bPublished    = '.($sreport->bPublished ? '1' : '0').",
          ss_dteLastUpdate = NOW(),
          ss_lLastUpdateID = $glUserID ");
   }

   function deleteStatusReport($lSReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "DELETE FROM aayhf_staff_status
         WHERE ss_lKeyID=$lSReportID;";
      $query = $this->db->query($sqlStr);

         // delete entries
      $sqlStr =
        "DELETE FROM aayhf_staff_status_details
         WHERE ssd_lStatusID = $lSReportID;";
      $query = $this->db->query($sqlStr);
   }




      //----------------------------------------------------
      //   S T A T U S   E N T R I E S / S E C T I O N S
      //----------------------------------------------------

   private function initSectionsLabels(
                          $section,       $enumType,  $strLabel1,
                          $bShowSection2, $strLabel2, $bField2AsCurrency){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

      $section->enumType          = $enumType;
      $section->strLabel1         = $strLabel1;
      $section->bShowSection2     = $bShowSection2;
      $section->strLabel2         = $strLabel2;
      $section->bField2AsCurrency = $bField2AsCurrency;
   }

   function loadSectionsViaStatID($lStatID, &$sections){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhereSections = " AND ssd_lStatusID=$lStatID ";
      $this->loadSectionEntries($sections);
   }

   function loadSectionsViaSectionEntryID($lEntryID, &$sections){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhereSections = " AND ssd_lKeyID=$lEntryID ";
      $this->loadSectionEntries($sections);
   }

   function loadSectionEntries(&$sections){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            ssd_lKeyID, ssd_lStatusID, ssd_enumStatusType,
            ssd_strText01, ssd_strText02, ssd_curEstAmnt,
            ssd_strUrgency,

            ssd_lOriginID, ssd_lLastUpdateID,

            UNIX_TIMESTAMP(ssd_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(ssd_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName  AS strUCFName,  uc.us_strLastName  AS strUCLName,
            ul.us_strFirstName  AS strULFName,  ul.us_strLastName  AS strULLName

         FROM aayhf_staff_status_details
            INNER JOIN admin_users   AS uc  ON uc.us_lKeyID  = ssd_lOriginID
            INNER JOIN admin_users   AS ul  ON ul.us_lKeyID  = ssd_lLastUpdateID

         WHERE 1 $this->sqlWhereSections
         ORDER BY ssd_lKeyID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $enumSRType = $row->ssd_enumStatusType;
            $lNumE = &$sections[$enumSRType]->lNumEntries;
            if ($lNumE == 0){
               $sections[$enumSRType]->entries = array();
            }
            $sections[$enumSRType]->entries[$lNumE] = new stdClass;
            $entry = $sections[$enumSRType]->entries[$lNumE];

            $entry->lKeyID         = (int)$row->ssd_lKeyID;
            $entry->lStatusID      = (int)$row->ssd_lStatusID;
            $entry->enumStatusType = $row->ssd_enumStatusType;
            $entry->strText01      = $row->ssd_strText01;
            $entry->strText02      = $row->ssd_strText02;
            $entry->curEstAmnt     = $row->ssd_curEstAmnt;
            $entry->strUrgency     = $row->ssd_strUrgency;
            $entry->lOriginID      = (int)$row->ssd_lOriginID;
            $entry->lLastUpdateID  = (int)$row->ssd_lLastUpdateID;
            $entry->dteOrigin      = (int)$row->dteOrigin;
            $entry->dteLastUpdate  = (int)$row->dteLastUpdate;
            $entry->strUCFName     = $row->strUCFName;
            $entry->strUCLName     = $row->strUCLName;
            $entry->strULFName     = $row->strULFName;
            $entry->strULLName     = $row->strULLName;

            ++$sections[$enumSRType]->lNumEntries;
         }
      }
   }

   private function initSectionsTable(&$sections){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sections = array(CENUM_STATCAT_CURRENTPROJECTS   => new stdClass,
                        CENUM_STATCAT_CURRENTACTIVITIES => new stdClass,
                        CENUM_STATCAT_UPCOMINGEVENTS    => new stdClass,
                        CENUM_STATCAT_UPCOMINGFUNDRQST  => new stdClass,
                        CENUM_STATCAT_CONCERNSISSUES    => new stdClass);
      $sections[CENUM_STATCAT_CURRENTPROJECTS  ]->lNumEntries =
      $sections[CENUM_STATCAT_CURRENTACTIVITIES]->lNumEntries =
      $sections[CENUM_STATCAT_UPCOMINGEVENTS   ]->lNumEntries =
      $sections[CENUM_STATCAT_UPCOMINGFUNDRQST ]->lNumEntries =
      $sections[CENUM_STATCAT_CONCERNSISSUES   ]->lNumEntries = 0;

      $this->initSectionsLabels($sections[CENUM_STATCAT_CURRENTPROJECTS],   CENUM_STATCAT_CURRENTPROJECTS,   'Current Projects',      true,  'Status',      false);
      $this->initSectionsLabels($sections[CENUM_STATCAT_CURRENTACTIVITIES], CENUM_STATCAT_CURRENTACTIVITIES, 'Current Activities',    false, '',            false);
      $this->initSectionsLabels($sections[CENUM_STATCAT_UPCOMINGEVENTS],    CENUM_STATCAT_UPCOMINGEVENTS,    'Upcoming Events',       false, '',            false);
      $this->initSectionsLabels($sections[CENUM_STATCAT_UPCOMINGFUNDRQST],  CENUM_STATCAT_UPCOMINGFUNDRQST,  'Upcoming Fund Request', true,  'Est. Amount', true);
      $this->initSectionsLabels($sections[CENUM_STATCAT_CONCERNSISSUES],    CENUM_STATCAT_CONCERNSISSUES,    'Concerns/Issues',       true,  'Urgency',     false);
   }

   function lInsertStatusEntry($lSReportID, &$entry){
   //---------------------------------------------------------------------
   // entry may be some like....
   // $this->cstat->sreports[0]->sections[$enumSRType]->entries[0]
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
         'INSERT INTO aayhf_staff_status_details
          SET '.$this->statEntrySQLCommon($entry).',
             ssd_enumStatusType = '.strPrepStr($entry->enumStatusType).",
             ssd_lStatusID = $lSReportID,
             ssd_lOriginID = $glUserID,
             ssd_dteOrigin = NOW();";

      $query = $this->db->query($sqlStr);
      $entry->lKeyID = $lKeyID = $this->db->insert_id();
      return($lKeyID);
   }

   function updateStatusEntry($lEntryID, &$entry){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'UPDATE aayhf_staff_status_details
          SET '.$this->statEntrySQLCommon($entry)."
          WHERE ssd_lKeyID = $lEntryID;";
      $query = $this->db->query($sqlStr);
   }

   private function statEntrySQLCommon(&$entry){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $strOut = '';

      switch ($entry->enumStatusType){
         case CENUM_STATCAT_CURRENTPROJECTS:
            $strOut .= 'ssd_strText01 = '.strPrepStr($entry->strText01).',
                        ssd_strText02 = '.strPrepStr($entry->strText02).",\n";
            break;

         case CENUM_STATCAT_CURRENTACTIVITIES:
         case CENUM_STATCAT_UPCOMINGEVENTS:
            $strOut .= 'ssd_strText01 = '.strPrepStr($entry->strText01).",\n";
            break;

         case CENUM_STATCAT_UPCOMINGFUNDRQST:
            $strOut .= 'ssd_strText01  = '.strPrepStr($entry->strText01).',
                        ssd_curEstAmnt = '.number_format($entry->curEstAmnt, 2, '.', '').",\n";
            break;

         case CENUM_STATCAT_CONCERNSISSUES:
            $strOut .= 'ssd_strText01  = '.strPrepStr($entry->strText01).',
                        ssd_strUrgency = '.strPrepStr($entry->strUrgency).",\n";
            break;
         default:
            screamForHelp($enumSRType.': invalid status report entry type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }


      $strOut .= "
         ssd_dteLastUpdate = NOW(),
         ssd_lLastUpdateID = $glUserID ";
      return($strOut);
   }

   function removeStatusEntry($lEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "DELETE FROM aayhf_staff_status_details
          WHERE ssd_lKeyID = $lEntryID;";
      $query = $this->db->query($sqlStr);
   }



      //----------------------------------------------------
      //       M A N A G E M E N T   T O O L S
      //----------------------------------------------------

   function lStaffManagementGroupID(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT gp_lKeyID
         FROM groups_parent
         WHERE gp_strGroupName = "Management"
            AND gp_enumGroupType  = '.strPrepStr(CENUM_CONTEXT_STAFF).'
         LIMIT 0,1;';
      $query = $this->db->query($sqlStr);

      if ($query->num_rows() == 0){
         return(null);
      }else {
         $row = $query->row();
         return((int)$row->gp_lKeyID);
      }
   }

   function loadUsersAndStaffGroups($strTmpTable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
            "DROP TABLE IF EXISTS $strTmpTable;";
      $query = $this->db->query($sqlStr);

         // note - this can't be created as a temporary table - a weird
         // locking error occurs in the party pooper sql below
      $sqlStr =
        "CREATE TABLE IF NOT EXISTS $strTmpTable (
         -- CREATE TEMPORARY TABLE IF NOT EXISTS  $strTmpTable (
           tmp_lKeyID   int(11) NOT NULL AUTO_INCREMENT,
           tmp_lUserID  int(11) NOT NULL DEFAULT '0',
           tmp_lGroupID int(11) DEFAULT NULL,
           PRIMARY KEY (tmp_lKeyID),
           KEY tmp_lUserID   (tmp_lUserID),
           KEY tmp_lGroupID  (tmp_lGroupID)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $query = $this->db->query($sqlStr);
      
      $sqlStr =
         "INSERT INTO $strTmpTable (tmp_lUserID, tmp_lGroupID)
             SELECT
                us_lKeyID, gp_lKeyID
             FROM admin_users
                INNER JOIN groups_child ON us_lKeyID=gc_lForeignID
                INNER JOIN groups_parent ON gc_lGroupID=gp_lKeyID
             WHERE NOT us_bVolAccount
                AND NOT us_bInactive
                AND gp_enumGroupType=".strPrepStr(CENUM_CONTEXT_STAFF).';';

      $query = $this->db->query($sqlStr);

         // now the party poopers - users not in any group
         // thanks to http://www.codeproject.com/Articles/33052/Visual-Representation-of-SQL-Joins
      $sqlStr =
         "INSERT INTO $strTmpTable (tmp_lUserID)
            SELECT us_lKeyID
            FROM admin_users AS A
            LEFT JOIN $strTmpTable AS B ON A.us_lKeyID = B.tmp_lUserID
            WHERE B.tmp_lUserID IS NULL;";
            
      $query = $this->db->query($sqlStr);
   }

   function buildStaffGroups($strTmpTable, &$lNumStaffGroups, &$staffGroups){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumStaffGroups = 0;
      $staffGroups = array();

      $sqlStr =
         "SELECT us_lKeyID, us_strFirstName, us_strLastName,
            gp_lKeyID, gp_strGroupName
          FROM $strTmpTable
             INNER JOIN admin_users   ON us_lKeyID    = tmp_lUserID
             LEFT  JOIN groups_parent ON tmp_lGroupID = gp_lKeyID
          WHERE 1
          ORDER BY gp_strGroupName, gp_lKeyID, us_strLastName, us_strFirstName, us_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows > 0) {
         $idx = -1;
         $lGroupOld = -999;
         foreach ($query->result() as $row){
            $lGroupID = (int)$row->gp_lKeyID;
            if ($lGroupOld != $lGroupID){
               $lGroupOld = $lGroupID;
               ++$idx; ++$lNumStaffGroups;
               $staffGroups[$idx] = new stdClass;
               $sg = &$staffGroups[$idx];
               $sg->strGroupName = $row->gp_strGroupName.'';
               $sg->lGroupID     = $lGroupID;
               $sg->users = array();
               $sg->lNumUsers = 0;
            }
            $sg->users[$sg->lNumUsers] = new stdClass;
            $usr = &$sg->users[$sg->lNumUsers];
            $usr->lUserID  = $row->us_lKeyID;
            $usr->strFName = $row->us_strFirstName;
            $usr->strLName = $row->us_strLastName;
            ++$sg->lNumUsers;
         }
      }
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$staffGroups   <pre>');
echo(htmlspecialchars( print_r($staffGroups, true))); echo('</pre></font><br>');
// ------------------------------------- */

   }

   function bIsUserInManagement($lUserID, $lManagementGroupID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT COUNT(*) AS lNumRecs
          FROM groups_child
            -- INNER JOIN
             -- INNER JOIN groups_parent ON gp_lKeyID=
          WHERE gc_lGroupID=$lManagementGroupID
             AND gc_lForeignID=$lUserID;";

      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(null);
      }else {
         $row = $query->row();
         return($row->lNumRecs > 0);
      }
   }
   
   function lNumStatRptsInMonth($lMonth, $lYear){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM aayhf_staff_status
         WHERE ss_bPublished
            AND MONTH(ss_dteSubmitDate)=$lMonth
            AND YEAR(ss_dteSubmitDate) =$lYear;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(0);
      }else {
         $row = $query->row();
         return((int)$row->lNumRecs);
      }
   }
   
   function loadStatRptViaUsersMonthYear($userIDs, $lMonth, $lYear, &$lNumStat, &$statRecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT ss_lKeyID, ss_lUserID, 
            UNIX_TIMESTAMP(ss_dteSubmitDate) AS dteSubmit,
            DAY(ss_dteSubmitDate) AS lDayOfMonthSubmit
         FROM aayhf_staff_status 
         WHERE ss_bPublished   
            AND (ss_lUserID IN ('.implode(',', $userIDs)."))
            AND MONTH(ss_dteSubmitDate)=$lMonth
            AND YEAR(ss_dteSubmitDate) =$lYear;";
            
      $query = $this->db->query($sqlStr);
      $lNumStat = $query->num_rows();
      $statRecs = array();
      if ($lNumStat > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $statRecs[$idx] = new stdClass;
            $sr = &$statRecs[$idx];
            
            $sr->lKeyID            = (int)$row->ss_lKeyID;
            $sr->lUserID           = (int)$row->ss_lUserID;
            $sr->dteSubmit         = (int)$row->dteSubmit;
            $sr->lDayOfMonthSubmit = (int)$row->lDayOfMonthSubmit;
            
            ++$idx;
         }
      }            
   }



      //----------------------------------------------------
      //       R E V I E W   U T I L I T I E S
      //----------------------------------------------------
   function loadReviewsViaRptID($lRptID, &$lNumReviews, &$reviewLog){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhereReviews = " AND ssr_lStatusID=$lRptID ";
      $this->loadReviews($lNumReviews, $reviewLog);
   }

   function loadReviewsViaReviewID($lReviewID, &$lNumReviews, &$reviewLog){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhereReviews = " AND ssr_lKeyID=$lReviewID ";
      $this->loadReviews($lNumReviews, $reviewLog);
   }

   function loadReviews(&$lNumReviews, &$reviewLog){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumReviews = 0;

      $reviewLog = array();

      $sqlStr =
        "SELECT
            ssr_lKeyID, ssr_lStatusID,
            UNIX_TIMESTAMP(ssr_dteDateReviewed) AS dteReviewed,
            ssr_bReviewed, ssr_strMgrNotes, ssr_strPublicNotes,

            ssr_lOriginID, ssr_lLastUpdateID,
            UNIX_TIMESTAMP(ssr_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(ssr_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName  AS strUCFName,  uc.us_strLastName  AS strUCLName,
            ul.us_strFirstName  AS strULFName,  ul.us_strLastName  AS strULLName

         FROM aayhf_staff_status_review
            INNER JOIN admin_users   AS uc  ON uc.us_lKeyID  = ssr_lOriginID
            INNER JOIN admin_users   AS ul  ON ul.us_lKeyID  = ssr_lLastUpdateID
         WHERE 1 $this->sqlWhereReviews
         ORDER BY  uc.us_strLastName, uc.us_strFirstName, ssr_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumReviews = $query->num_rows();
      if ($lNumReviews==0) {
         $reviewLog[0] = new stdClass;
         $rLog = &$reviewLog[0];
         $rLog->lKeyID         =
         $rLog->lStatusID      =
         $rLog->dteReviewed    =
         $rLog->bReviewed      =
         $rLog->strMgrNotes    =
         $rLog->strPublicNotes =
         $rLog->lReviewerID    =
         $rLog->lOriginID      =
         $rLog->lLastUpdateID  =
         $rLog->dteOrigin      =
         $rLog->dteLastUpdate  =
         $rLog->strUCFName     =
         $rLog->strUCLName     =
         $rLog->strULFName     =
         $rLog->strULLName     = null;
         $rLog->strReviewerSafeName = '';
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $reviewLog[$idx] = new stdClass;
            $rLog = &$reviewLog[$idx];

            $rLog->lKeyID         = (int)$row->ssr_lKeyID;
            $rLog->lStatusID      = (int)$row->ssr_lStatusID;
            $rLog->dteReviewed    = (int)$row->dteReviewed;
            $rLog->bReviewed      = (boolean)$row->ssr_bReviewed;
            $rLog->strMgrNotes    = $row->ssr_strMgrNotes;
            $rLog->strPublicNotes = $row->ssr_strPublicNotes;

            $rLog->lReviewerID    =
            $rLog->lOriginID      = (int)$row->ssr_lOriginID;
            $rLog->lLastUpdateID  = (int)$row->ssr_lLastUpdateID;
            $rLog->dteOrigin      = (int)$row->dteOrigin;
            $rLog->dteLastUpdate  = (int)$row->dteLastUpdate;
            $rLog->strUCFName     = $row->strUCFName;
            $rLog->strUCLName     = $row->strUCLName;
            $rLog->strULFName     = $row->strULFName;
            $rLog->strULLName     = $row->strULLName;

            $rLog->strReviewerSafeName = htmlspecialchars($row->strULFName.' '.$row->strULLName);

            ++$idx;
         }
      }
   }

   function lAddNewStatusReview(&$mgrReview){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'INSERT INTO aayhf_staff_status_review
          SET '.$this->sqlCommonRptReview($mgrReview).",
             ssr_dteOrigin = NOW(),
             ssr_lOriginID = $glUserID;";
      $query = $this->db->query($sqlStr);
      $mgrReview->lKeyID = $lKeyID = $this->db->insert_id();
      return($lKeyID);
   }

   function updateStatusReview($lReviewID, &$mgrReview){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'UPDATE aayhf_staff_status_review
          SET '.$this->sqlCommonRptReview($mgrReview)."
          WHERE ssr_lKeyID=$lReviewID;";
      $query = $this->db->query($sqlStr);
   }

   private function sqlCommonRptReview($mgrReview){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      return(
        'ssr_lStatusID       = '.$mgrReview->lStatusID.',
         ssr_dteDateReviewed = NOW(),
         ssr_bReviewed       = '.($mgrReview->bReviewed ? '1' : '0').',
         ssr_strMgrNotes     = '.strPrepStr($mgrReview->strMgrNotes).',
         ssr_strPublicNotes  = '.strPrepStr($mgrReview->strPublicNotes).",
         ssr_dteLastUpdate   = NOW(),
         ssr_lLastUpdateID   = $glUserID ");

   }


   function reviewCountsForStaffMember(
                            $lStaffMemberUID, $lMgrUID,
                            &$lNumReviewed, &$lNumReviewedDraft, &$lNumNotReviewed,
                            &$lTotPublished){
   //---------------------------------------------------------------------
   /*
       # of published status reports reviewed by user
       # of published status reports reviewed (draft) by user
       # of published status reports not reviewed by user
   */
   //---------------------------------------------------------------------
      $lNumReviewed = $lNumReviewedDraft = $lNumNotReviewed = 0;
      $lNumReviewed      = $this->lNumStatReviewViaStaffMgr($lStaffMemberUID, $lMgrUID, false);
      $lNumReviewedDraft = $this->lNumStatReviewViaStaffMgr($lStaffMemberUID, $lMgrUID, true);
      $lTotPublished     = $this->lNumPublishedStatRptsViaUserID($lStaffMemberUID);
      $lNumNotReviewed   = $lTotPublished - ($lNumReviewed+$lNumReviewedDraft);
   }

   function lNumStatReviewViaStaffMgr($lStaffMemberUID, $lMgrUID, $bAsDraft){
   //---------------------------------------------------------------------
   // find the number of published status report for a given staff
   // member that have been reviewed by a given management user.
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM aayhf_staff_status
            INNER JOIN aayhf_staff_status_review ON ssr_lStatusID=ss_lKeyID
         WHERE ssr_lOriginID=$lMgrUID
            AND ss_bPublished
            AND ss_lUserID=$lStaffMemberUID
            AND ".($bAsDraft ? ' NOT ' : '').' ssr_bReviewed;';

      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(0);
      }else {
         $row = $query->row();
         return((int)$row->lNumRecs);
      }
   }

   function lNumPublishedStatRptsViaUserID($lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM aayhf_staff_status
         WHERE
            ss_bPublished
            AND ss_lUserID=$lUserID;";

      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(0);
      }else {
         $row = $query->row();
         return((int)$row->lNumRecs);
      }
   }



}



