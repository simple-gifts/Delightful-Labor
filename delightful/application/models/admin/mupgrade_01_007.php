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
---------------------------------------------------------------------
      $this->load->model('admin/mupgrade_01_007', 'mup01_007');
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mupgrade_01_007 extends mupgrade{

   function up_01_007_tablePerms(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // add permissions table for personalized tables
      $sqlStr =
        "CREATE TABLE IF NOT EXISTS uf_table_perms (
           ppr_lKeyID   int(11) NOT NULL AUTO_INCREMENT,
           ppr_lTableID int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to uf_tables',
           ppr_lGroupID int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to groups_parent',
           PRIMARY KEY      (ppr_lKeyID),
           KEY ppr_lTableID (ppr_lTableID),
           KEY ppr_lGroupID (ppr_lGroupID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1
             COMMENT='Permission groups for standard users; no permission groups means all access';";
      $this->db->query($sqlStr);
   }

   function up_01_007_pledge(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // pledge table
      $sqlStr =
        "CREATE TABLE gifts_pledges (
           gp_lKeyID        int(11)       NOT NULL AUTO_INCREMENT,
           gp_lForeignID    int(11)       NOT NULL COMMENT 'Foreign key to people_names table; may be person or biz',
           gp_lCampID       int(11)       NOT NULL DEFAULT '0',
           gp_curCommitment decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Commitment for each donaton',
           gp_lNumCommit    int(11)       NOT NULL DEFAULT '0'    COMMENT 'Total number of pledge payments committed by donor',
           gp_enumFreq      enum('one-time', 'weekly', 'monthly', 'quarterly', 'annually', 'other') NOT NULL
                                      DEFAULT 'other',

           gp_lACOID        int(11)       NOT NULL DEFAULT '0' COMMENT 'foreign key to table admin_aco',
           gp_dteStart      date          NOT NULL DEFAULT '0000-00-00' COMMENT 'start date of pledge payments',
           gp_lAttributedTo int(11) DEFAULT NULL COMMENT 'foreign key to lists_generic',
           gp_strNotes      text NOT NULL,

           gp_bRetired      tinyint(1) NOT NULL DEFAULT '0',
           gp_lOriginID     int(11)    NOT NULL DEFAULT '0',
           gp_lLastUpdateID int(11)    NOT NULL DEFAULT '0',
           gp_dteOrigin     datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
           gp_dteLastUpdate timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (gp_lKeyID),
           KEY gp_lForeignID      (gp_lForeignID),
           KEY gp_lCampID         (gp_lCampID),
           KEY gp_enumFreq        (gp_enumFreq),
           KEY gp_curCommitment   (gp_curCommitment),
           KEY gp_dteStart        (gp_dteStart),
           KEY gp_bRetired        (gp_bRetired),
           KEY gp_lAttributedTo   (gp_lAttributedTo),
           KEY gp_lACOID    (gp_lACOID),
           FULLTEXT KEY gp_strNotes (gp_strNotes)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);

         // pledge schedule table
      $sqlStr =
        "CREATE TABLE gifts_pledge_schedule (
           gps_lKeyID        int(11)    NOT NULL AUTO_INCREMENT,
           gps_lPledgeID     int(11)    NOT NULL COMMENT 'Foreign key to gifts_pledges',
           gps_lGiftID       int(11)    DEFAULT NULL COMMENT 'Foreign key to gifts',
           gps_dtePledge     date       NOT NULL DEFAULT '0000-00-00' COMMENT 'pledge due date',

           gps_bRetired      tinyint(1) NOT NULL DEFAULT '0',
           gps_lOriginID     int(11)    NOT NULL DEFAULT '0',
           gps_lLastUpdateID int(11)    NOT NULL DEFAULT '0',
           gps_dteOrigin     datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
           gps_dteLastUpdate timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (gps_lKeyID),
           KEY gps_lPledgeID       (gps_lPledgeID),
           KEY gps_lGiftID         (gps_lGiftID),
           KEY gps_dtePledge       (gps_dtePledge)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);
   }

   function up_01_007_ufDDLMulti(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "CREATE TABLE IF NOT EXISTS uf_ddl_multi (
           pdm_lKeyID       int(11) NOT NULL AUTO_INCREMENT,
           pdm_lFieldID     int(11)  NOT NULL DEFAULT '0' COMMENT 'Foreign key to uf_fields',
           pdm_lUTableID    int(11)  NOT NULL DEFAULT '0' COMMENT 'Foreign key to uf_tables',
           pdm_lUTableRecID int(11)  NOT NULL DEFAULT '0' COMMENT 'Foreign key to ufield data table',
           pdm_lDDLID       int(11)  NOT NULL DEFAULT '0' COMMENT 'Foreign key to uf_ddl',
           PRIMARY KEY (pdm_lKeyID),
           KEY pdm_lFieldID     (pdm_lFieldID),
           KEY pdm_lUTableID    (pdm_lUTableID),
           KEY pdm_lUTableRecID (pdm_lUTableRecID),
           KEY pdm_entry        (pdm_lFieldID, pdm_lUTableID, pdm_lUTableRecID),
           KEY pdm_lDDLID       (pdm_lDDLID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);
   }

   function up_01_007_clientPrograms(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "
         CREATE TABLE IF NOT EXISTS `cprograms` (
           `cp_lKeyID`             int(11) NOT NULL AUTO_INCREMENT,
           `cp_strProgramName`     varchar(255) NOT NULL DEFAULT '',
           `cp_strDescription`     text NOT NULL COMMENT 'Internal description',
           `cp_dteStart`           date NOT NULL DEFAULT '0000-00-00' COMMENT 'Program start date',
           `cp_dteEnd`             date NOT NULL DEFAULT '0000-00-00' COMMENT 'Program end date',
           `cp_strVocEnroll`       varchar(80) NOT NULL DEFAULT 'Enrollment',
           `cp_strVocAttendance`   varchar(80) NOT NULL DEFAULT 'Attendance',
           `cp_bHidden`            tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hidden but not deleted',
           `cp_lEnrollmentTableID` int(11) NOT NULL DEFAULT '0',
           `cp_lAttendanceTableID` int(11) NOT NULL DEFAULT '0',
           `cp_lActivityFieldID`   int(11) NOT NULL DEFAULT '0' COMMENT 'Field ID for the attendance table activity DDL',
           `cp_bMentorMentee`      tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is this a mentor/mentee client program?',
           `cp_bRetired`           tinyint(1) NOT NULL DEFAULT '0',
           `cp_lOriginID`          int(11)    NOT NULL DEFAULT '0',
           `cp_lLastUpdateID`      int(11)    NOT NULL DEFAULT '0',
           `cp_dteOrigin`          datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
           `cp_dteLastUpdate`      timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (`cp_lKeyID`),
           KEY `cp_strProgramName` (`cp_strProgramName`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);
   }

   function up_01_007_customForms(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "
         CREATE TABLE IF NOT EXISTS `custom_forms` (
           `cf_lKeyID`                int(11) NOT NULL AUTO_INCREMENT,
           `cf_strFormName`           varchar(255) NOT NULL DEFAULT '',
           `cf_strDescription`        text NOT NULL COMMENT 'Internal description',
           `cf_enumContextType`       enum('client','location','sponsorship','people','household','business','volunteer','Unknown','auction','auctionPackage','auctionItem','organization') NOT NULL DEFAULT 'Unknown',
           `cf_strIntro`              text NOT NULL COMMENT 'Displayed at top of intake form',
           `cf_strSubmissionText`     text NOT NULL COMMENT 'Text displayed after successful submission',
           `cf_strBannerTitle`        varchar(255) NOT NULL DEFAULT '',
           `cf_strContact`            varchar(255) NOT NULL DEFAULT '',
           `cf_bCreateNewParent`      tinyint(1) NOT NULL DEFAULT '1' COMMENT 'If set, create a new parent record',
           `cf_lParentGroupID`        int(11) DEFAULT NULL COMMENT 'Optional group ID; new reg. placed in group',
           `cf_strVerificationModule` varchar(255) DEFAULT NULL COMMENT 'The user may provide enhanced form verification',
           `cf_strVModEntryPoint`     varchar(255) DEFAULT NULL COMMENT 'Entry point (function name) of the verification module',
           `cf_bRetired`              tinyint(1) NOT NULL DEFAULT '0',
           `cf_lOriginID`             int(11) NOT NULL DEFAULT '0',
           `cf_lLastUpdateID`         int(11) NOT NULL DEFAULT '0',
           `cf_dteOrigin`             datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
           `cf_dteLastUpdate`         timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (`cf_lKeyID`),
           KEY `cf_strFormName`     (`cf_strFormName`),
           KEY `cf_lParentGroupID`  (`cf_lParentGroupID`),
           KEY `cf_enumContextType` (`cf_enumContextType`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TABLE IF NOT EXISTS `custom_form_log` (
           `cfl_lKeyID`        int(11)   NOT NULL AUTO_INCREMENT,
           `cfl_lCFormID`      int(11)   NOT NULL COMMENT 'Foreign Key to custom_forms',
           `cfl_lForeignID`    int(11)   NOT NULL COMMENT 'Foreign Key to parent record',
           `cfl_lOriginID`     int(11)   NOT NULL COMMENT 'User who submitted the form',
           `cfl_dteOrigin`     datetime  NOT NULL DEFAULT '0000-00-00 00:00:00',
           PRIMARY KEY (`cfl_lKeyID`),
           KEY `cfl_lForeignID`    (cfl_lForeignID),
           KEY `cfl_lCFormID`      (cfl_lCFormID),
           KEY `cfl_lClientForm`   (cfl_lForeignID, cfl_lCFormID),
           KEY `cfl_lFormUserForm` (cfl_lOriginID,  cfl_lCFormID),
           KEY `cfl_dteOrigin`     (cfl_dteOrigin),
           KEY `cfl_lOriginID`     (cfl_lOriginID)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TABLE IF NOT EXISTS `custom_form_table_labels` (
           `cftl_lKeyID`   int(11) NOT NULL AUTO_INCREMENT,
           `cftl_lCFormID` int(11) NOT NULL COMMENT 'Foreign Key to custom_forms',
           `cftl_lTableID` int(11) NOT NULL COMMENT 'Foreign Key to uf_tables',
           `cftl_strLabel` varchar(255) NOT NULL DEFAULT '' COMMENT 'Public label for table',
           PRIMARY KEY (`cftl_lKeyID`),
           KEY `cftl_lCFormID` (`cftl_lCFormID`),
           KEY `cftl_lTableID` (`cftl_lTableID`)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TABLE IF NOT EXISTS `custom_form_uf` (
           `cfuf_lKeyID`    int(11)      NOT NULL AUTO_INCREMENT,
           `cfuf_lCFormID`  int(11)      NOT NULL COMMENT 'Foreign Key to custom_forms',
           `cfuf_lTableID`  int(11)      NOT NULL COMMENT 'Foreign Key to uf_tables',
           `cfuf_lFieldID`  int(11)      NOT NULL COMMENT 'Foreign Key to uf_fields',
           `cfuf_bRequired` tinyint(1)   NOT NULL DEFAULT '0',
           `cfuf_strLabel`  varchar(255) NOT NULL DEFAULT '' COMMENT 'Public label for field',
           PRIMARY KEY (`cfuf_lKeyID`),
           KEY `cfuf_lCFormID` (`cfuf_lCFormID`),
           KEY `cfuf_lTableID` (`cfuf_lTableID`),
           KEY `cfuf_lFieldID` (`cfuf_lFieldID`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);
   }

   function up_01_007_timeSheets(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "
      CREATE TABLE IF NOT EXISTS staff_timesheets (
        ts_lKeyID          int(11) NOT NULL AUTO_INCREMENT,
        ts_strTSName       varchar(80) NOT NULL DEFAULT '',
        ts_lFirstDayOfWeek tinyint(4) NOT NULL DEFAULT '0' COMMENT 'as defined by php 0=Sunday, 6=Saturday',
        ts_strAckText      text NOT NULL COMMENT 'Statement to be acknowledged by staff member before submitting time sheet',
        ts_strNotes        text NOT NULL,
        ts_b24HrTime       tinyint(1) NOT NULL DEFAULT '0',
        ts_enumGranularity enum('5','10','15','30','60') NOT NULL DEFAULT '5' COMMENT 'Granularity of the time increments, in minutes',
        ts_enumRptPeriod   enum('Weekly','Semi-monthly','Monthly') DEFAULT NULL COMMENT 'Reporting Period',
        ts_bHidden         tinyint(1) NOT NULL DEFAULT '0',
        ts_bRetired        tinyint(1) NOT NULL DEFAULT '0',
        ts_lOriginID       int(11) NOT NULL DEFAULT '0',
        ts_lLastUpdateID   int(11) NOT NULL DEFAULT '0',
        ts_dteOrigin       datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        ts_dteLastUpdate   timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (ts_lKeyID),
        KEY ts_strTSName (ts_strTSName)
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
      $this->db->query($sqlStr);

      $sqlStr = "
      CREATE TABLE IF NOT EXISTS staff_ts_admin (
        tsa_lKeyID        int(11) NOT NULL AUTO_INCREMENT,
        tsa_lTimeSheetID  int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table staff_timesheets',
        tsa_lStaffID      int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table admin_users',
        tsa_lOriginID     int(11) NOT NULL DEFAULT '0',
        tsa_lLastUpdateID int(11) NOT NULL DEFAULT '0',
        tsa_dteOrigin     datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        tsa_dteLastUpdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (tsa_lKeyID),
        KEY tsa_lTimeSheetID (tsa_lTimeSheetID),
        KEY tsa_lStaffID     (tsa_lStaffID)
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Users who are authorized to view/edit time sheet entries' AUTO_INCREMENT=1 ;";
      $this->db->query($sqlStr);

      $sqlStr = "
      CREATE TABLE IF NOT EXISTS staff_ts_log (
        tsl_lKeyID        int(11)  NOT NULL AUTO_INCREMENT,
        tsl_lTimeSheetID  int(11)  NOT NULL DEFAULT '0' COMMENT 'Foreign key to table staff_timesheets',
        tsl_lStaffID      int(11)  NOT NULL DEFAULT '0' COMMENT 'Foreign key to table admin_users',
        tsl_dteTSEntry    date     NOT NULL DEFAULT '0000-00-00' COMMENT 'First day of ts week, based on template start date',
        tsl_dteSubmitted  datetime          DEFAULT NULL,
        tsl_lOriginID     int(11)  NOT NULL DEFAULT '0',
        tsl_lLastUpdateID int(11)  NOT NULL DEFAULT '0',
        tsl_dteOrigin     datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        tsl_dteLastUpdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (tsl_lKeyID),
        KEY tsl_lTimeSheetID (tsl_lTimeSheetID),
        KEY tsl_dteTSEntry   (tsl_dteTSEntry),
        KEY tsl_lStaffID     (tsl_lStaffID)
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Time sheet log for staff member' AUTO_INCREMENT=1 ;";
      $this->db->query($sqlStr);

      $sqlStr = "
      CREATE TABLE IF NOT EXISTS staff_ts_log_entry (
        tsle_lKeyID        int(11) NOT NULL AUTO_INCREMENT,
        tsle_lTSLogID      int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table staff_ts_log',
        tsle_dteLogEntry   date NOT NULL DEFAULT '0000-00-00' COMMENT 'Date of time recording',
        tsle_tmTimeIn      time NOT NULL DEFAULT '00:00:00',
        tsle_tmTimeOut     time NOT NULL DEFAULT '00:00:00',
        tsle_lLocationID   int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table groups_parent',
        tsle_strNotes      text NOT NULL,
        tsle_lOriginID     int(11) NOT NULL DEFAULT '0',
        tsle_lLastUpdateID int(11) NOT NULL DEFAULT '0',
        tsle_dteOrigin     datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        tsle_dteLastUpdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (tsle_lKeyID),
        KEY tsle_lTSLogID    (tsle_lTSLogID),
        KEY tsle_dteLogEntry (tsle_dteLogEntry),
        KEY tsle_lLocationID (tsle_lLocationID)
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Time sheet log entry for staff member' AUTO_INCREMENT=1 ;";
      $this->db->query($sqlStr);

      $sqlStr = "
      CREATE TABLE IF NOT EXISTS staff_ts_log_projects (
        tspr_lKeyID            int(11) NOT NULL AUTO_INCREMENT,
        tspr_lTSLogID          int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table staff_ts_log',
        tspr_lProjectID        int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table groups_parent',
        tspr_lMinutesToProject int(11) NOT NULL DEFAULT '0',
        tspr_strNotes          text NOT NULL,
        tspr_lOriginID         int(11) NOT NULL DEFAULT '0',
        tspr_lLastUpdateID     int(11) NOT NULL DEFAULT '0',
        tspr_dteOrigin         datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        tspr_dteLastUpdate     timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (tspr_lKeyID),
        UNIQUE KEY uniqueLogProject (tspr_lTSLogID,tspr_lProjectID),
        KEY tspr_lTSLogID   (tspr_lTSLogID),
        KEY tspr_lProjectID (tspr_lProjectID)
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Time assigned to projects' AUTO_INCREMENT=1 ;";
      $this->db->query($sqlStr);

      $sqlStr = "
      CREATE TABLE IF NOT EXISTS staff_ts_staff (
        tss_lKeyID        int(11) NOT NULL AUTO_INCREMENT,
        tss_lTimeSheetID  int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table staff_timesheets',
        tss_lStaffID      int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table admin_users',
        tss_lOriginID     int(11) NOT NULL DEFAULT '0',
        tss_lLastUpdateID int(11) NOT NULL DEFAULT '0',
        tss_dteOrigin     datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        tss_dteLastUpdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (tss_lKeyID),
        UNIQUE KEY staff_timesheet (tss_lTimeSheetID,tss_lStaffID),
        KEY tss_lTimeSheetID       (tss_lTimeSheetID),
        KEY tss_lStaffID           (tss_lStaffID)
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Staff member time sheet template assignments' AUTO_INCREMENT=1 ;";
      $this->db->query($sqlStr);
   }

   function up_01_007_imgDocs(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "
         CREATE TABLE IF NOT EXISTS doc_img_tag_ddl (
           `dit_lKeyID`       int(11) NOT NULL AUTO_INCREMENT,
           `dit_enumContext`  enum('imgAuction','imgAuctionPackage','imgAuctionItem','imgBiz',
                                   'imgClient', 'imgClientLocation',
                                   'imgOrganization','imgPeople','imgSponsor','imgStaff','imgVolunteer',
                                   'docAuction','docAuctionPackage','docAuctionItem','docBiz',
                                   'docClient', 'docClientLocation',
                                   'docOrganization','docPeople','docSponsor','docStaff',
                                   'docVolunteer','Unknown') NOT NULL DEFAULT 'Unknown',
           `dit_strDDLEntry`  varchar(80) NOT NULL,
           `dit_lSortIDX`     int(11) NOT NULL DEFAULT '0',
           `dit_bRetired`     tinyint(1) NOT NULL DEFAULT '0',
           PRIMARY KEY           (`dit_lKeyID`),
           KEY `dit_enumContext` (`dit_enumContext`),
           KEY `dit_lSortIDX`    (`dit_lSortIDX`)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Image/document drop-down list entries (tags)' AUTO_INCREMENT=1 ;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TABLE IF NOT EXISTS doc_img_tag_ddl_multi (
           `dim_lKeyID`    int(11) NOT NULL AUTO_INCREMENT,
           `dim_lImgDocID` int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to docs_images',
           `dim_lDDLID`    int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to doc_img_tag_ddl',
           PRIMARY KEY         (`dim_lKeyID`),
           KEY `dim_lDDLID`    (`dim_lImgDocID`),
           KEY `pdm_lUTableID` (`dim_lDDLID`)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
      $this->db->query($sqlStr);

      $sqlStr = "
            ALTER TABLE `docs_images`
            CHANGE `di_enumContextType`
                   `di_enumContextType`
                       ENUM('client', 'location', 'sponsorship', 'people', 'household',
                            'business', 'volunteer', 'Unknown',
                            'auction', 'auctionPackage', 'auctionItem', 'organization', 'staff')
            CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT  'Unknown';";
      $this->db->query($sqlStr);
   }

   function up_01_007_cppTests(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "
         CREATE TABLE IF NOT EXISTS `cpp_questions` (
           `cpq_lKeyID`        int(11) NOT NULL AUTO_INCREMENT,
           `cpq_lPrePostID`    int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table cpp_tests',
           `cpq_lSortIDX`      int(11) NOT NULL DEFAULT '0',
           `cpq_strQuestion`   text NOT NULL,
           `cpq_strAnswer`     text NOT NULL,
           `cpq_bRetired`      tinyint(1) NOT NULL DEFAULT '0',
           `cpq_lOriginID`     int(11) NOT NULL DEFAULT '0',
           `cpq_lLastUpdateID` int(11) NOT NULL DEFAULT '0',
           `cpq_dteOrigin`     datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
           `cpq_dteLastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (`cpq_lKeyID`),
           KEY `cpq_lPrePostID` (`cpq_lPrePostID`),
           KEY `cpq_lSortIDX`   (`cpq_lSortIDX`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TABLE IF NOT EXISTS `cpp_tests` (
           `cpp_lKeyID`         int(11) NOT NULL AUTO_INCREMENT,
           `cpp_lTestCat`       int(11) DEFAULT NULL COMMENT 'foreign key to lists_generic',
           `cpp_strTestName`    varchar(255) NOT NULL DEFAULT '',
           `cpp_strDescription` text NOT NULL,
           `cpp_bPublished`     tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Once published, can not be altered',
           `cpp_bHidden`        tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hidden but not deleted',
           `cpp_bRetired`       tinyint(1) NOT NULL DEFAULT '0',
           `cpp_lOriginID`      int(11) NOT NULL DEFAULT '0',
           `cpp_lLastUpdateID`  int(11) NOT NULL DEFAULT '0',
           `cpp_dteOrigin`      datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
           `cpp_dteLastUpdate`  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (`cpp_lKeyID`),
           KEY `cpp_strProgramName` (`cpp_strTestName`),
           KEY `cpp_lTestCat`       (`cpp_lTestCat`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TABLE IF NOT EXISTS `cpp_test_log` (
           `cptl_lKeyID`        int(11) NOT NULL AUTO_INCREMENT,
           `cptl_lPrePostID`    int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table cpp_tests',
           `cptl_lClientID`     int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to client table',
           `cptl_dtePreTest`    date NOT NULL DEFAULT '0000-00-00',
           `cptl_dtePostTest`   date NOT NULL DEFAULT '0000-00-00',
           `cptl_bRetired`      tinyint(1) NOT NULL DEFAULT '0',
           `cptl_lOriginID`     int(11) NOT NULL DEFAULT '0',
           `cptl_lLastUpdateID` int(11) NOT NULL DEFAULT '0',
           `cptl_dteOrigin`     datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
           `cptl_dteLastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (`cptl_lKeyID`),
           KEY `cptl_lPrePostID`  (`cptl_lPrePostID`),
           KEY `cptl_dtePreTest`  (`cptl_dtePreTest`),
           KEY `cptl_dtePostTest` (`cptl_dtePostTest`),
           KEY `cptl_lClientID`   (`cptl_lClientID`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TABLE IF NOT EXISTS `cpp_test_results` (
           `cptr_lKeyID`         int(11) NOT NULL AUTO_INCREMENT,
           `cptr_lQuestionID`    int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table cpp_questions',
           `cptr_lTestLogID`     int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table cpp_test_log',
           `cptr_bPreTest`       tinyint(1) NOT NULL DEFAULT '0',
           `cptr_bAnswerCorrect` tinyint(1) NOT NULL DEFAULT '0',
           PRIMARY KEY (`cptr_lKeyID`),
           KEY `cptr_lQuestionID` (`cptr_lQuestionID`),
           KEY `cptr_lTestLogID`  (`cptr_lTestLogID`),
           KEY `cptr_lQuestLog`   (`cptr_lQuestionID`,`cptr_lTestLogID`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);
   }

   function up_01_007_creports(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "DROP TABLE IF EXISTS creport_dir;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TABLE IF NOT EXISTS creport_dir (
           crd_lKeyID        int(11) NOT NULL AUTO_INCREMENT,
           crd_strName       varchar(255) NOT NULL DEFAULT '',
           crd_strNotes      text NOT NULL,
           crd_enumRptType   enum(
                                 'gifts','gifts/hon','gifts/mem','gifts/per',
                                 'clients',  'clients/cprog',  'clients/spon',  'clients/spon/pay'
                             ) NOT NULL,
           crd_bPrivate      tinyint(1) NOT NULL DEFAULT '0',

           crd_bRetired      tinyint(1) NOT NULL DEFAULT '0',
           crd_lOriginID     int(11) NOT NULL DEFAULT '0',
           crd_lLastUpdateID int(11) NOT NULL DEFAULT '0',
           crd_dteOrigin     datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
           crd_dteLastUpdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (crd_lKeyID),
           KEY crd_lOriginID   (crd_lOriginID),
           KEY crd_enumRptType (crd_enumRptType)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Custom report directory' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $sqlStr = "DROP TABLE IF EXISTS creport_fields;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TABLE IF NOT EXISTS creport_fields (
           crf_lKeyID        int(11) NOT NULL AUTO_INCREMENT,
           crf_lReportID     int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to creport_dir',

           crf_lFieldID      int(11)  DEFAULT NULL COMMENT 'Foreign key to uf_fields; if null - parent table',
           crf_lTableID      int(11)  DEFAULT NULL COMMENT 'Foreign key to uf_tables',
           crf_strTableName  varchar(255) NOT NULL DEFAULT '',
           crf_strFieldName  varchar(255) DEFAULT NULL COMMENT 'For parent table fields',
           crf_lSortIDX       int(11)  NOT NULL DEFAULT '0',

           crf_lOriginID     int(11) NOT NULL DEFAULT '0',
           crf_lLastUpdateID int(11) NOT NULL DEFAULT '0',
           crf_dteOrigin     datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
           crf_dteLastUpdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (crf_lKeyID),
           KEY crf_lReportID   (crf_lReportID),
           KEY crf_lFieldID (crf_lFieldID),
           KEY crf_lTableID (crf_lTableID),
           KEY crf_lSortIDX (crf_lSortIDX)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Fields for custom report' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $sqlStr = "DROP TABLE IF EXISTS creport_search;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TABLE IF NOT EXISTS creport_search (
            crs_lKeyID             int(11) NOT NULL AUTO_INCREMENT,
            crs_lReportID          int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to creport_dir',
            crs_lFieldID           int(11) NOT NULL DEFAULT  '0',
            crs_lTableID           int(11) NOT NULL DEFAULT  '0',
            crs_strFieldID         VARCHAR (255) NOT NULL DEFAULT  '',
            crs_lNumLParen         SMALLINT(  5) NOT NULL DEFAULT  '0',
            crs_lNumRParen         SMALLINT(  5) NOT NULL DEFAULT  '0',
            crs_lSortIDX           SMALLINT(  5) NOT NULL DEFAULT  '0',
            crs_lCompareOpt        SMALLINT(  5) NOT NULL DEFAULT  '0',
            crs_bCompareBool       TINYINT (  1)   DEFAULT  NULL,
            crs_lCompVal           INT     ( 11)   DEFAULT NULL,
            crs_curCompVal         DECIMAL (10, 2) DEFAULT NULL,
            crs_strCompVal         VARCHAR (255)   DEFAULT NULL,
            crs_dteCompVal         DATE            DEFAULT NULL,
            crs_bNextTermBoolAND   TINYINT (  1) NOT NULL DEFAULT  '0',
            crs_lOriginID          int(11) NOT NULL DEFAULT '0',
            crs_lLastUpdateID      int(11) NOT NULL DEFAULT '0',
            crs_dteOrigin          datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            crs_dteLastUpdate      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (crs_lKeyID),
              KEY crs_lReportID  (crs_lReportID),
              KEY crs_lTableID   (crs_lTableID),
              KEY crs_lFieldID   (crs_lFieldID),
              KEY crs_strFieldID (crs_strFieldID),
              KEY crs_lSortIDX   (crs_lSortIDX)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Sort terms for custom report' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);
   }

   function upgradeUserTablesForAlerts(&$strOut){
   //---------------------------------------------------------------------
   // for upgrade to 1.007 - user tables now have alert notification
   // fields
   //
   // caller must include:
   //   $this->load->model('personalization/muser_fields',        'clsUF');
   //   $this->load->model('personalization/muser_fields_create', 'clsUFC');
   //---------------------------------------------------------------------
      $strOut = '<font style="font-family: courier;">';
      $cUFC = new muser_fields_create;

         // load all user tables
      $sqlStr =
        'SELECT pft_lKeyID, pft_strDataTableName, pft_strUserTableName, pft_bMultiEntry
         FROM uf_tables
         WHERE NOT pft_bRetired
         ORDER BY pft_strDataTableName;';
      $query = $this->db->query($sqlStr);

      $this->lNumTables = $lNumTables = $query->num_rows();
      if ($lNumTables > 0) {
         foreach ($query->result() as $row){
            $lTableID = (int)$row->pft_lKeyID;
            $bMulti = (bool)$row->pft_bMultiEntry;
            $strOut .= '<b>Processing table '.$row->pft_strDataTableName
                 .'</b> ('.htmlspecialchars($row->pft_strUserTableName).')<br>';
            $this->testCreateUFUpgradeFields($cUFC, $strOut, $lTableID,
                       $row->pft_strDataTableName, $bMulti);
         }
      }
      $strOut .= '</font>';
   }


   function testCreateUFUpgradeFields(&$cUFC, &$strOut, $lTableID,
                       $strDataTableName, $bMulti){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strIndent = '&nbsp;&nbsp;&nbsp;&nbsp;';
      $strKeyFNPrefix = $cUFC->strGenUF_KeyFieldPrefix($lTableID);
      $strOut .= $strIndent.'field prefix: '.$strKeyFNPrefix.'<br>'."\n";
      $strOut .= $strIndent.'data table: '.$strDataTableName.'<br>'."\n";

      $strFN  = $strKeyFNPrefix.'_bRecordEntered';
      $this->testAdd1007Field($strDataTableName, $strFN, 'recordEntered', $strOut);

      $strFN  = $strKeyFNPrefix.'_lLastUpdateID';
      $this->testAdd1007Field($strDataTableName, $strFN, 'lastUpdate', $strOut);

      $strFN  = $strKeyFNPrefix.'_dteLastUpdate';
      $this->testAdd1007Field($strDataTableName, $strFN, 'dteLastUpdate', $strOut);

      if (!$bMulti){
         $strFN  = $strKeyFNPrefix.'_lOriginID';
         $this->testAdd1007Field($strDataTableName, $strFN, 'originID', $strOut);

         $strFN  = $strKeyFNPrefix.'_dteOrigin';
         $this->testAdd1007Field($strDataTableName, $strFN, 'dteOrigin', $strOut);
      }
   }

   function testAdd1007Field($strDataTableName, $strFieldName, $enumFieldType, &$strOut){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strIndent = '&nbsp;&nbsp;&nbsp;&nbsp;';

      $sqlStr = "SHOW COLUMNS FROM $strDataTableName LIKE '$strFieldName';";
      $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      
//      $result = mysql_query($sqlStr);
      $bFieldExists = ($numRows > 0);
      if ($bFieldExists){
         $strOut .= $strIndent.'field exists: '.$strFieldName.'<br>'."\n";
      }else {
         $strOut .= $strIndent.'<b>adding field:</b> '.$strFieldName.'<br>'."\n";
         switch ($enumFieldType){
            case 'recordEntered':
               $sqlStr =
                 "ALTER TABLE $strDataTableName
                     ADD $strFieldName  TINYINT(1) NOT NULL default '0' COMMENT 'True if data entry has occurred';";
                break;

            case 'originID':
            case 'lastUpdate':
               $sqlStr =
                 "ALTER TABLE $strDataTableName
                     ADD $strFieldName  int(11) NOT NULL DEFAULT '0';";
                break;

            case 'dteLastUpdate':
               $sqlStr =
                 "ALTER TABLE $strDataTableName
                     ADD $strFieldName  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;";
                break;

            case 'dteOrigin':
               $sqlStr =
                 "ALTER TABLE $strDataTableName
                     ADD $strFieldName  datetime NOT NULL DEFAULT '2000-01-01 00:00:00';";
                break;

            default:
               screamForHelp($enumFieldType.': invalid field<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
         $this->db->query($sqlStr);
      }
   }


/*
// thanks to
// http://stackoverflow.com/questions/3395798/mysql-check-if-a-column-exists-in-a-table-with-sql
//       
//       SHOW COLUMNS FROM `table` LIKE 'fieldname';
//       With PHP it would be something like...
//       
//       $result = mysql_query("SHOW COLUMNS FROM `table` LIKE 'fieldname'");
//       $exists = (mysql_num_rows($result))?TRUE:FALSE;
//       
//       SELECT *
//       FROM information_schema.COLUMNS
//       WHERE
//           TABLE_SCHEMA = 'db_name'
//       AND TABLE_NAME = 'table_name'
//       AND COLUMN_NAME = 'column_name'
//          }
*/






}
