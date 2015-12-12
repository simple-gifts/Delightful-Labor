<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2013-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
---------------------------------------------------------------------
      $this->load->model('admin/mupgrade', 'clsUpgrade');
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mupgrade extends CI_Model{


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
   }

   private function upgradeDBLevel($strNewVersionLevel, $strComment){
      $sqlStr =
         "INSERT INTO admin_version
             SET av_sngVersion  = $strNewVersionLevel,
             av_strVersionNotes = ".strPrepStr($strComment).',
             av_dteInstalled    = NOW();';
      $this->db->query($sqlStr);
   }

   public function upgrade_00_900_to_00_901(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "
         --
         -- Table structure for table 'deposit_log'
         --

         CREATE TABLE deposit_log (
           dl_lKeyID        int(11)     NOT NULL AUTO_INCREMENT,
           dl_lACOID        int(11)     NOT NULL DEFAULT '0' COMMENT 'Foreign key to table admin_aco',
           dl_dteStart      datetime    DEFAULT NULL,
           dl_dteEnd        datetime    DEFAULT NULL,
           dl_strBank       varchar(80) NOT NULL DEFAULT '',
           dl_strAccount    varchar(80) NOT NULL DEFAULT '',
           dl_strNotes      text        NOT NULL,

           dl_bRetired      tinyint(1)  NOT NULL DEFAULT '0',
           dl_lOriginID     int(11)     NOT NULL DEFAULT '0',
           dl_lLastUpdateID int(11)     NOT NULL DEFAULT '0',
           dl_dteOrigin     datetime    NOT NULL DEFAULT '0000-00-00 00:00:00',
           dl_dteLastUpdate timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY     (dl_lKeyID),
           KEY dl_dteStart (dl_dteStart),
           KEY dl_dteEnd   (dl_dteEnd),
           KEY dl_lACOID   (dl_lACOID)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Deposit Log';";
      $this->db->query($sqlStr);

      $sqlStr = "
         ALTER TABLE  gifts
            ADD  gi_lDepositLogID INT NULL DEFAULT NULL
            COMMENT 'Foreign key to the deposit log table'
            AFTER  gi_lGIK_ID,
            ADD INDEX (gi_lDepositLogID );";
      $this->db->query($sqlStr);
      $this->upgradeDBLevel('0.901', 'Added deposit_log table, added gi_lDepositLogID field to table gifts');

      return('Upgrade from 0.900 to 0.901 successful<br>');
   }

   public function upgrade_00_901_to_00_902(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // personalized logs
      $sqlStr = 'ALTER TABLE uf_logs ADD FULLTEXT uflog_strLogEntry (uflog_strLogEntry);';
      $this->db->query($sqlStr);

      $sqlStr = 'ALTER TABLE uf_logs ADD FULLTEXT  uflog_strLogTitle (uflog_strLogTitle);';
      $this->db->query($sqlStr);

         // client bio
      $sqlStr = 'ALTER TABLE client_records ADD FULLTEXT  cr_strBio (cr_strBio);';
      $this->db->query($sqlStr);

         // client status
      $sqlStr = 'ALTER TABLE client_status ADD FULLTEXT csh_strStatusTxt (csh_strStatusTxt);';
      $this->db->query($sqlStr);

         // document and image description
      $sqlStr = 'ALTER TABLE docs_images ADD FULLTEXT  di_strDescription (di_strDescription);';
      $this->db->query($sqlStr);

         // donation descriptions
      $sqlStr = 'ALTER TABLE gifts ADD FULLTEXT  gi_strNotes (gi_strNotes);';
      $this->db->query($sqlStr);

      $this->upgradeDBLevel('0.902', 'Added full text search to personalized logs');

      return('Upgrade from 0.901 to 0.902 successful<br>');
   }

   public function upgrade_00_902_to_01_000(){
   //---------------------------------------------------------------------
   // note: no schema changes from beta 0.902 to production 1.000
   //---------------------------------------------------------------------
      $this->upgradeDBLevel('1.000', 'Production release 1.000');

      return('Upgrade from 0.902 to 1.000 successful<br>');
   }

   public function upgrade_01_000_to_01_001(){
   //---------------------------------------------------------------------
   // support for simple volunteer hours recording
   //---------------------------------------------------------------------
      global $glUserID;

         // support for generic list "Vol Activities"
      $sqlStr = "
          ALTER TABLE  `lists_generic` CHANGE  `lgen_enumListType`  `lgen_enumListType`
            ENUM(  'bizCat',  'bizContactRel',  'inKind',  'attrib', 'campaignExpense',
                   'majorGiftCats',  'giftPayType',  'volJobCat',  'sponTermCat',
                   'volSkills',  'volActivities' )
            CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
      $this->db->query($sqlStr);

         // default value for "Vol Activities"
      $sqlStr = "
            INSERT INTO `lists_generic` (
                            `lgen_enumListType`, `lgen_strListItem`,
                            `lgen_lSortIDX`,     `lgen_bRetired`,     `lgen_lOriginID`,
                            `lgen_lLastUpdateID`, `lgen_dteOrigin`, `lgen_dteLastUpdate`)
            VALUES ('volActivities', '(other/unknown)',
                    '0',        '0', '$glUserID',
                    '$glUserID', NOW(), NOW());";
      $this->db->query($sqlStr);

         /* ------------------------------------------------------
            vol hours support in vol_events_dates_shifts_assign
            ------------------------------------------------------ */
      $sqlStr = "
            ALTER TABLE  `vol_events_dates_shifts_assign`
            CHANGE  `vsa_lEventDateShiftID`  `vsa_lEventDateShiftID` INT( 11 ) NULL
            COMMENT  'Foreign key to vol_events_dates_shifts / null for simple vol hrs';";
      $this->db->query($sqlStr);

      $sqlStr = "
         ALTER TABLE  `vol_events_dates_shifts_assign`
         ADD  `vsa_dteActivityDate` DATETIME NULL DEFAULT NULL               COMMENT  'fDate/time start for unscheduled hours'      AFTER `vsa_dHoursWorked` ,
         ADD  `vsa_lActivityID`     INT NULL DEFAULT NULL                    COMMENT  'fkey to lists_generic/for unscheduled hours' AFTER `vsa_dteActivityDate` ,
         ADD  `vsa_lOriginID`       INT NULL DEFAULT NULL                    COMMENT  'for unscheduled hours' AFTER `vsa_bRetired` ,
         ADD  `vsa_lLastUpdateID`   INT NULL DEFAULT NULL                    COMMENT  'for unscheduled hours' AFTER `vsa_lOriginID` ,
         ADD  `vsa_dteOrigin`       DATETIME NULL DEFAULT NULL               COMMENT  'for unscheduled hours' AFTER `vsa_lLastUpdateID` ,
         ADD  `vsa_dteLastUpdate`   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT  'for unscheduled hours' AFTER `vsa_dteOrigin`;";
      $this->db->query($sqlStr);

      $this->upgradeDBLevel('1.001', 'Upgrade to support simple volunteer hours logging');
      return('Upgrade from 1.000 to 1.001 successful<br>');
   }

   public function upgrade_01_001_to_01_002(){
   //---------------------------------------------------------------------
   // temporary table for serializing objects (bypasses using session
   // variables to hold state info
   //---------------------------------------------------------------------
      $sqlStr =
        'CREATE TABLE IF NOT EXISTS serial_objects (
           so_lKeyID     int(11)   NOT NULL AUTO_INCREMENT,
           so_object     text      NOT NULL COMMENT \'serialized object\',
           so_dteCreated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (so_lKeyID),
           KEY so_dteCreated (so_dteCreated)
         ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT=\'Hold temporary serialized objects\' AUTO_INCREMENT=1;';
      $this->db->query($sqlStr);

      $this->upgradeDBLevel('1.002', 'Upgrade to support serialized objects');
      return('Upgrade from 1.001 to 1.002 successful<br>');
   }

   public function upgrade_01_002_to_01_003(){
   //---------------------------------------------------------------------
   // logo field name in chapter table
   //---------------------------------------------------------------------
      $sqlStr =
        "ALTER TABLE  `docs_images`
           CHANGE  `di_enumContextType`            `di_enumContextType`
                ENUM('client',   'location',       'sponsorship', 'people',         'household',
                     'business', 'volunteer',      'Unknown',
                     'auction',  'auctionPackage', 'auctionItem',
                     'organization', 'staff' )
           CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT  'Unknown';";
      $this->db->query($sqlStr);

      $this->upgradeDBLevel('1.003', 'Upgrade to support additional image/pdf catagories');
      return('Upgrade from 1.002 to 1.003 successful<br>');
   }

   public function upgrade_01_003_to_01_004(){
   //---------------------------------------------------------------------
   // logo field name in chapter table
   //---------------------------------------------------------------------

         // future development - custom reports
      $sqlStr =
        "CREATE TABLE IF NOT EXISTS `creport_dir` (
           `crd_lKeyID` int(11) NOT NULL AUTO_INCREMENT,
           `crd_strName` varchar(255) NOT NULL DEFAULT '',
           `crd_strNotes` text NOT NULL,
           `crd_enumRptType` enum('gifts','gifts/hon','gifts/mem','gifts/per') NOT NULL,
           `crd_bPrivate` tinyint(1) NOT NULL DEFAULT '0',
           `crd_bRetired` tinyint(1) NOT NULL DEFAULT '0',
           `crd_lOriginID` int(11) NOT NULL DEFAULT '0',
           `crd_lLastUpdateID` int(11) NOT NULL DEFAULT '0',
           `crd_dteOrigin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
           `crd_dteLastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (`crd_lKeyID`),
           KEY `crd_lOriginID` (`crd_lOriginID`),
           KEY `crd_enumRptType` (`crd_enumRptType`)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Custom report directory';";
      $this->db->query($sqlStr);


         // silent auctions - auction events
      $sqlStr =
        "CREATE TABLE IF NOT EXISTS `gifts_auctions` (
           `auc_lKeyID`            int(11) NOT NULL AUTO_INCREMENT,
           `auc_strAuctionName`    varchar(255) NOT NULL DEFAULT '',
           `auc_strDescription`    text NOT NULL,
           `auc_dteAuctionDate`    date NOT NULL DEFAULT '0000-00-00',
           `auc_dteAuctionEndDate` date DEFAULT NULL COMMENT 'reserved',
           `auc_lACOID`            int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to table admin_aco',
           `auc_lCampaignID`       int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to campaigns for winning bid gifts',
           `auc_lDefaultBidSheet`  int(11) DEFAULT NULL COMMENT 'Default bid sheet for packages in this auction',
           `auc_strLocation`       text NOT NULL,
           `auc_strContact`        varchar(255) NOT NULL DEFAULT '',
           `auc_strPhone`          varchar(80) NOT NULL DEFAULT '',
           `auc_strEmail`          varchar(200) NOT NULL DEFAULT '',
           `auc_bRetired`          tinyint(1) NOT NULL DEFAULT '0',
           `auc_lOriginID`         int(11) NOT NULL DEFAULT '0',
           `auc_lLastUpdateID`     int(11) NOT NULL DEFAULT '0',
           `auc_dteOrigin`         datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
           `auc_dteLastUpdate`     timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (`auc_lKeyID`),
           KEY `auc_strAuctionName` (`auc_strAuctionName`),
           KEY `auc_dteAuctionDate` (`auc_dteAuctionDate`)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Master silent auction table';";
      $this->db->query($sqlStr);


         // silent auctions - bid sheet templates
      $sqlStr =
        "CREATE TABLE IF NOT EXISTS `gifts_auctions_bidsheets` (
           `abs_lKeyID`                  int(11) NOT NULL AUTO_INCREMENT,
           `abs_lTemplateID`             int(11) NOT NULL DEFAULT '0' COMMENT 'the base template',
           `abs_lAuctionID`              int(11) NOT NULL,
           `abs_strSheetName`            varchar(255) NOT NULL DEFAULT '',
           `abs_strDescription`          text NOT NULL,
           `abs_enumPaperType`           enum('Letter','Legal','A3','A4','A5') NOT NULL DEFAULT 'Letter',
           `abs_lNumSignupPages`         smallint(6) NOT NULL,
           `abs_strSignUpCol1`           varchar(30) DEFAULT 'Bid Amount',
           `abs_strSignUpCol2`           varchar(30) DEFAULT 'Name/Address',
           `abs_strSignUpCol3`           varchar(30) DEFAULT 'Phone',
           `abs_strSignUpCol4`           varchar(30) DEFAULT NULL,
           `abs_lSigunUpColWidth1`       smallint(6) DEFAULT '20',
           `abs_lSigunUpColWidth2`       smallint(6) DEFAULT '50',
           `abs_lSigunUpColWidth3`       smallint(6) DEFAULT '30',
           `abs_lSigunUpColWidth4`       smallint(6) DEFAULT NULL,
           `abs_bIncludeOrgName`         tinyint(1) NOT NULL DEFAULT '1',
           `abs_bIncludeOrgLogo`         tinyint(1) NOT NULL DEFAULT '0',
           `abs_lLogoImgID`              int(11) DEFAULT NULL,
           `abs_bIncludeMinBid`          tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludeMinBidInc`       tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludeBuyItNow`        tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludeReserve`         tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludeDate`            tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludeFooter`          tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Include page footer w/page cnt',
           `abs_bIncludePackageName`     tinyint(1) NOT NULL DEFAULT '1',
           `abs_bIncludePackageID`       tinyint(1) NOT NULL DEFAULT '1',
           `abs_bIncludePackageDesc`     tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludePackageImage`    tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludePackageEstValue` tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludeItemName`        tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludeItemID`          tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludeItemDesc`        tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludeItemImage`       tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludeItemDonor`       tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludeItemEstValue`    tinyint(1) NOT NULL DEFAULT '0',
           `abs_bIncludeSignup`          tinyint(1) NOT NULL DEFAULT '0',
           `abs_bReadOnly`               tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If set, template can''t be edited',
           `abs_bRetired`                tinyint(1) NOT NULL DEFAULT '0',
           `abs_lOriginID`               int(11) NOT NULL DEFAULT '0',
           `abs_lLastUpdateID`           int(11) NOT NULL DEFAULT '0',
           `abs_dteOrigin`               datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
           `abs_dteLastUpdate`           timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (`abs_lKeyID`),
           KEY `abs_strSheetName` (`abs_strSheetName`),
           KEY `abs_lAuctionID`   (`abs_lAuctionID`)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Bid Sheet Templates';";
      $this->db->query($sqlStr);


         // silent auctions - auction items
      $sqlStr =
        "CREATE TABLE IF NOT EXISTS `gifts_auctions_items` (
           `ait_lKeyID`           int(11) NOT NULL AUTO_INCREMENT,
           `ait_lPackageID`       int(11) DEFAULT NULL COMMENT 'Foreign key to table gifts_auctions_packages',
           `ait_strItemName`      varchar(255) NOT NULL DEFAULT '',
           `ait_strDescription`   text NOT NULL,
           `ait_strInternalNotes` text NOT NULL,
           `ait_dteItemObtained`  date NOT NULL DEFAULT '0000-00-00' COMMENT 'Date the auction item was obtained',
           `ait_lItemDonorID`     int(11) NOT NULL DEFAULT '0' COMMENT 'FID to People Table - donor or item (not bidder)',
           `ait_strDonorAck`      varchar(80) NOT NULL DEFAULT '' COMMENT '\"Donated By\" text for bid sheet',
           `ait_curEstAmnt`       decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Estimated value of item',
           `ait_curOutOfPocket`   decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Out-of-pocket expense',
           `ait_bRetired`         tinyint(1) NOT NULL DEFAULT '0',
           `ait_lOriginID`        int(11) NOT NULL DEFAULT '0',
           `ait_lLastUpdateID`    int(11) NOT NULL DEFAULT '0',
           `ait_dteOrigin`        datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
           `ait_dteLastUpdate`    timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (`ait_lKeyID`),
           KEY `ait_lPackageID`   (`ait_lPackageID`),
           KEY `ait_lItemDonorID` (`ait_lItemDonorID`),
           KEY `ait_strItemName`  (`ait_strItemName`)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Individual auction items';";
      $this->db->query($sqlStr);


         // silent auctions - packages
      $sqlStr =
        "CREATE TABLE IF NOT EXISTS `gifts_auctions_packages` (
           `ap_lKeyID`           int(11) NOT NULL AUTO_INCREMENT,
           `ap_lAuctionID`       int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table gifts_auctions',
           `ap_strPackageName`   varchar(255) NOT NULL DEFAULT '',
           `ap_curMinBidAmnt`    decimal(10,2) NOT NULL DEFAULT '0.00',
           `ap_curReserveAmnt`   decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Bid reserve',
           `ap_curMinBidInc`     decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Minimum bid increment',
           `ap_curBuyItNowAmnt`  decimal(10,2) DEFAULT NULL COMMENT 'For bidsheet - buy it now price',
           `ap_curWinBidAmnt`    decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Winning bid amount',
           `ap_strDescription`   text NOT NULL,
           `ap_strInternalNotes` text NOT NULL,
           `ap_lBidSheetID`      int(11) DEFAULT NULL,
           `ap_lBidWinnerID`     int(11) DEFAULT NULL COMMENT 'Winning bidder/foreign key to table people_names',
           `ap_dteWinnerContact` date DEFAULT NULL,
           `xxxap_dtePaid`       date DEFAULT NULL,
           `ap_lGiftID`          int(11) DEFAULT NULL COMMENT 'optional gift ID for winning bidder',
           `ap_bRetired`         tinyint(1) NOT NULL DEFAULT '0',
           `ap_lOriginID`        int(11) NOT NULL DEFAULT '0',
           `ap_lLastUpdateID`    int(11) NOT NULL DEFAULT '0',
           `ap_dteOrigin`        datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
           `ap_dteLastUpdate`    timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (`ap_lKeyID`),
           KEY `ap_lAuctionID`     (`ap_lAuctionID`),
           KEY `ap_lBidWinnerID`   (`ap_lBidWinnerID`),
           KEY `ap_lGiftID`        (`ap_lGiftID`),
           KEY `ap_strPackageName` (`ap_strPackageName`)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Packaged auction items';";
      $this->db->query($sqlStr);

         // add full-text search capabilities to log entries
      $sqlStr =
          "ALTER TABLE  `uf_logs` ADD FULLTEXT (`uflog_strLogEntry`);";
      $this->db->query($sqlStr);


      $this->upgradeDBLevel('1.004', 'Upgrade to support silent auctions');
      return('Upgrade from 1.003 to 1.004 successful<br>');
   }

   public function upgrade_01_004_to_01_005(){
   //---------------------------------------------------------------------
   // new country support; medical records
   //---------------------------------------------------------------------
      $this->load->model('admin/mgrowth_chart_init', 'mMedRecordUpgrade');

      $sqlStr =
        "INSERT INTO `admin_aco`
            (`aco_strFlag`, `aco_strName`, `aco_strCurrencySymbol`, `aco_bInUse`, `aco_bDefault`)
         VALUES
            ('spain.png',        'Spain',        '&euro;',   0, 0),
            ('taiwan.png',       'Taiwan',       'NT$',      0, 0),
            ('indonesia.png',    'Indonesia',    'Rp',       0, 0),
            ('brazil.png',       'Brazil',       'R$',       0, 0),
            ('peru.png',         'Peru',         'S/.',      0, 0),
            ('malaysia.png',     'Malaysia',     'RM',       0, 0),
            ('bangladesh.png',   'Bangladesh',   'BDT',      0, 0);";
      $this->db->query($sqlStr);

         // enable medical records on a location-by-location basis
      $sqlStr = "
            ALTER TABLE client_location
               ADD cl_bEnableEMR TINYINT(1) NOT NULL
               DEFAULT  '0'
               COMMENT 'If set, allow medical records features for this location'
               AFTER  `cl_strNotes`;";
      $this->db->query($sqlStr);

         // medical records height/weight/ofc measurements
      $sqlStr = "DROP TABLE IF EXISTS `emr_measurements`;";
      $this->db->query($sqlStr);

      $sqlStr =
          "CREATE TABLE IF NOT EXISTS emr_measurements (
              meas_lKeyID         int(11) NOT NULL AUTO_INCREMENT,
              meas_lClientID      int(11) NOT NULL DEFAULT '0',
              meas_dteMeasurement date NOT NULL DEFAULT '0000-00-00',
              meas_sngHeadCircCM  float DEFAULT NULL,
              meas_sngWeightKilos float DEFAULT NULL,
              meas_sngHeightCM    float DEFAULT NULL,
              meas_strNotes       text,
              meas_bRetired       tinyint(1) NOT NULL DEFAULT '0',
              meas_lOriginID      int(11) NOT NULL DEFAULT '0',
              meas_lLastUpdateID  int(11) NOT NULL DEFAULT '0',
              meas_dteOrigin      datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
              meas_dteLastUpdate  timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (meas_lKeyID),
              KEY meas_lClientID      (meas_lClientID),
              KEY meas_dteMeasurement (meas_dteMeasurement),
              KEY meas_sngHeadCircCM  (meas_sngHeadCircCM),
              KEY meas_sngWeightKilos (meas_sngWeightKilos),
              KEY meas_sngHeightCM    (meas_sngHeightCM)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='health record: height/weight/ofc - metric units' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

         // Table structure for table `emr_who_growth_charts`
      $sqlStr = "DROP TABLE IF EXISTS `emr_who_growth_charts`;";
      $this->db->query($sqlStr);
      $sqlStr =
        "CREATE TABLE IF NOT EXISTS `emr_who_growth_charts` (
           `whogc_lKeyID`       int(11) NOT NULL AUTO_INCREMENT,
           `whogc_lAgeDays`     int(11) NOT NULL DEFAULT '0',
           `whogc_enumGender`   enum('M','F') NOT NULL DEFAULT 'M',
           `whogc_enumChart`    enum('BMI','height','weight','OFC') NOT NULL DEFAULT 'BMI',
           `whogc_lPercentile`  smallint(11) NOT NULL DEFAULT '0',
           `whogc_lMeasurement` decimal(10,2) NOT NULL DEFAULT '0.00',
         PRIMARY KEY (`whogc_lKeyID`),
           KEY `whogc_lAgeDays`     (`whogc_lAgeDays`),
           KEY `whogc_enumGender`   (`whogc_enumGender`),
           KEY `whogc_enumChart`    (`whogc_enumChart`),
           KEY `whogc_lPercentile`  (`whogc_lPercentile`),
           KEY `whogc_lMeasurement` (`whogc_lMeasurement`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='WHO growth chart tabular data in percentiles' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

         // Table structure for table `emr_who_percentile`
      $sqlStr = "DROP TABLE IF EXISTS `emr_who_percentile`;";
      $this->db->query($sqlStr);

      $this->mMedRecordUpgrade->insertGrowthChartData();

      $sqlStr =
        "CREATE TABLE IF NOT EXISTS `emr_who_percentile` (
           `wper_lKeyID`    int(11) NOT NULL AUTO_INCREMENT,
           `wper_lChildID`  int(11) NOT NULL,
           `wper_dteHeight` date DEFAULT NULL,
           `wper_dteWeight` date DEFAULT NULL,
           `wper_dteBMI`    date DEFAULT NULL,
           `wper_dteOFC`    date DEFAULT NULL,
           `wper_sngHeight` float DEFAULT NULL,
           `wper_sngWeight` float DEFAULT NULL,
           `wper_sngBMI`    float DEFAULT NULL,
           `wper_sngOFC`    float DEFAULT NULL,
           `wper_sngHeightPercentile` float DEFAULT NULL,
           `wper_sngWeightPercentile` float DEFAULT NULL,
           `wper_sngBMIPercentile`    float DEFAULT NULL,
           `wper_sngOFCPercentile`    float DEFAULT NULL,
           `wper_dteLastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (`wper_lKeyID`),
           KEY `wper_lChildID` (`wper_lChildID`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='who percentile report' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $this->upgradeDBLevel('1.005', 'New country support/client med. stats');
      return('Upgrade from 1.004 to 1.005 successful<br>');
   }

   public function upgrade_01_005_to_01_006(){
   //---------------------------------------------------------------------
   // volunteer registration support
   //---------------------------------------------------------------------
         // allow multiple entries for a given foreign key in personalized tables
      $sqlStr =
          "ALTER TABLE  `uf_tables`
              ADD  `pft_bMultiEntry` TINYINT( 1 ) NOT NULL DEFAULT '0'
              COMMENT 'When set, allow multiple entries for a foreign key' AFTER  `pft_strDescription`;";
      $this->db->query($sqlStr);

         // increase user name field to support emails as user IDs;
         // add new fields to support volunteer login
      $sqlStr =
         "ALTER TABLE admin_users
            CHANGE us_strUserName  us_strUserName VARCHAR( 120 )
            CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
            ADD us_bVolAccount             TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bDebugger ,
            ADD us_bVolEditContact         TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bVolAccount,
            ADD us_bVolPassReset           TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bVolEditContact,
            ADD us_bVolViewGiftHistory     TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bVolPassReset,
            ADD us_bVolShiftSignup         TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bVolViewGiftHistory,
            ADD us_bVolEditJobSkills       TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bVolShiftSignup,
            ADD us_bVolViewHrsHistory      TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bVolEditJobSkills,
            ADD us_bVolAddVolHours         TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bVolViewHrsHistory,

            ADD us_bUserDataEntryPeople    TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bVolAddVolHours,
            ADD us_bUserDataEntryGifts     TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bUserDataEntryPeople,
            ADD us_bUserEditPeople         TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bUserDataEntryGifts,
            ADD us_bUserEditGifts          TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bUserEditPeople,
            ADD us_bUserViewPeople         TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bUserEditGifts,
            ADD us_bUserViewGiftHistory    TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bUserViewPeople,
            ADD us_bUserViewReports        TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bUserViewGiftHistory,
            ADD us_bUserAllowExports       TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bUserViewReports,
            ADD us_bUserAllowSponsorship   TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bUserAllowExports,
            ADD us_bUserAllowSponFinancial TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bUserAllowSponsorship,
            ADD us_bUserAllowClient        TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bUserAllowSponFinancial,
            ADD us_bUserAllowAuctions      TINYINT(1) NOT NULL DEFAULT  '0' AFTER  us_bUserAllowClient,

            ADD us_lPeopleID               INT(11)             DEFAULT NULL AFTER  us_bUserAllowAuctions,
            ADD INDEX (us_lPeopleID);";
      $this->db->query($sqlStr);

      $sqlStr =
            'UPDATE admin_users
             SET us_bUserDataEntryPeople     = 1,
                 us_bUserDataEntryGifts      = 1,
                 us_bUserEditPeople          = 1,
                 us_bUserEditGifts           = 1,
                 us_bUserViewPeople          = 1,
                 us_bUserViewGiftHistory     = 1,
                 us_bUserViewReports         = 1,
                 us_bUserAllowExports        = 1,
                 us_bUserAllowSponsorship    = 1,
                 us_bUserAllowSponFinancial  = 1,
                 us_bUserAllowClient         = 1,
                 us_bUserAllowAuctions       = 1
             WHERE NOT us_bVolAccount AND NOT us_bAdmin AND NOT us_bInactive;';
      $this->db->query($sqlStr);


      $sqlStr = 'DROP TABLE IF EXISTS vol_reg;';
      $this->db->query($sqlStr);

      $sqlStr =
         "CREATE TABLE IF NOT EXISTS vol_reg (
             vreg_lKeyID              int(11) NOT NULL AUTO_INCREMENT,
             vreg_strFormName         varchar(255) NOT NULL DEFAULT '',
             vreg_strURLHash          varchar(255) NOT NULL DEFAULT '' COMMENT 'Hash of key ID for vol. URL',
             vreg_strDescription      text NOT NULL COMMENT 'Internal description',
             vreg_strIntro            text NOT NULL COMMENT 'Visible to the volunteer',
             vreg_strSubmissionText   text NOT NULL COMMENT 'Text displayed to volunteer after successful submission',
             vreg_strBannerOrg        varchar(255) NOT NULL DEFAULT '',
             vreg_strBannerTitle      varchar(255) NOT NULL DEFAULT '',
             vreg_lLogoImageID        int(11)      DEFAULT NULL COMMENT 'Optional organization logo',
             vreg_strCSSFN            varchar(255) NOT NULL DEFAULT 'default.css' COMMENT 'User-selectable style sheet, located in ./css/vol_reg',
             vreg_strHexBGColor       varchar(25)  NOT NULL DEFAULT ''  COMMENT 'ref: http://jsfiddle.net/bgrins/ctkY3/',
             vreg_strContact          varchar(255) NOT NULL DEFAULT '',
             vreg_strContactPhone     varchar(80)  NOT NULL DEFAULT '',
             vreg_strContactEmail     varchar(200) NOT NULL DEFAULT ''  COMMENT 'Will receive a registration alert at this address',
             vreg_strWebSite          varchar(255) NOT NULL DEFAULT '',
             vreg_lVolGroupID         int(11)      DEFAULT NULL COMMENT 'Optional vol. group ID; new reg. placed in group',

                -- permissions when registered
             vreg_bPermEditContact       tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'Allow vol to edit contact info',
             vreg_bPermPassReset         tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'Allow vol to reset password',
             vreg_bPermViewGiftHistory   tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'Allow vol to view donation history',
             vreg_bPermEditJobSkills     tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'Allow vol to edit job skills',
             vreg_bPermViewHrsHistory    tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'Allow vol to view volunteer hours',
             vreg_bPermAddVolHours       tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'Allow vol to add/edit vol hours',
             vreg_bVolShiftSignup        tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'Allow vol view events and sign up for shifts',

                -- standard display fields
             vreg_bShowFName          tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bShowLName          tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bShowAddr           tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bShowEmail          tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bShowPhone          tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bShowCell           tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bShowBDay           tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'Show birthdate field?',

                -- Disclaimer
             vreg_bShowDisclaimer     tinyint(1)   NOT NULL DEFAULT '1',
             vreg_strDisclaimerAck    varchar(255) NOT NULL DEFAULT '' COMMENT 'Acknowledgement text',
             vreg_strDisclaimer       text         NOT NULL COMMENT 'Form disclaimer, visibile to volunteer',
             vreg_bFNameRequired      tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bLNameRequired      tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bAddrRequired       tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bEmailRequired      tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bPhoneRequired      tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bCellRequired       tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bBDateRequired      tinyint(1)   NOT NULL DEFAULT '1',
             vreg_bDisclaimerAckRqrd  tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'Must the vol. acknowledge the disclaimer to submit?',
             vreg_bCaptchaRequired    tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'http://www.google.com/recaptcha',

             vreg_bRetired            tinyint(1)   NOT NULL DEFAULT '0',
             vreg_lOriginID           int(11)      NOT NULL DEFAULT '0',
             vreg_lLastUpdateID       int(11)      NOT NULL DEFAULT '0',
             vreg_dteOrigin           datetime     NOT NULL DEFAULT '0000-00-00 00:00:00',
             vreg_dteLastUpdate       timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
         PRIMARY KEY (vreg_lKeyID),           KEY vreg_strFormName (vreg_strFormName),
            KEY vreg_lVolGroupID (vreg_lVolGroupID),
            KEY vreg_strURLHash  (vreg_strURLHash)
         )ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);

      $sqlStr = 'DROP TABLE IF EXISTS vol_reg_skills;';
      $this->db->query($sqlStr);

      $sqlStr =
         "CREATE TABLE IF NOT EXISTS vol_reg_skills (
             vrs_lKeyID     int(11) NOT NULL AUTO_INCREMENT,
             vrs_lRegFormID int(11) NOT NULL COMMENT 'Foreign Key to vol_reg',
             vrs_lSkillID   int(11) NOT NULL COMMENT 'Foreign Key to lists_generic/volSkills',
          PRIMARY KEY        (vrs_lKeyID),
             KEY vrs_lRegFormID (vrs_lRegFormID),
             KEY vrs_lSkillID   (vrs_lSkillID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);

      $sqlStr = 'DROP TABLE IF EXISTS vol_reg_uf;';
      $this->db->query($sqlStr);

      $sqlStr =
         "CREATE TABLE IF NOT EXISTS vol_reg_uf (
             vruf_lKeyID     int(11) NOT NULL AUTO_INCREMENT,
             vruf_lRegFormID int(11) NOT NULL COMMENT 'Foreign Key to vol_reg',
             vruf_lTableID   int(11) NOT NULL COMMENT 'Foreign Key to uf_tables',
             vruf_lFieldID   int(11) NOT NULL COMMENT 'Foreign Key to uf_fields',
             vruf_bRequired  tinyint(1)   NOT NULL DEFAULT '0',
             vruf_strLabel   varchar(255) NOT NULL DEFAULT '' COMMENT 'Public label for field',
          PRIMARY KEY         (vruf_lKeyID),
             KEY vruf_lRegFormID (vruf_lRegFormID),
             KEY vruf_lTableID   (vruf_lTableID),
             KEY vruf_lFieldID   (vruf_lFieldID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);

      $sqlStr = 'DROP TABLE IF EXISTS vol_reg_table_labels;';
      $this->db->query($sqlStr);

      $sqlStr =
         "CREATE TABLE IF NOT EXISTS vol_reg_table_labels (
             vrtl_lKeyID     int(11) NOT NULL AUTO_INCREMENT,
             vrtl_lRegFormID int(11) NOT NULL COMMENT 'Foreign Key to vol_reg',
             vrtl_lTableID   int(11) NOT NULL COMMENT 'Foreign Key to uf_tables',
             vrtl_strLabel   varchar(255) NOT NULL DEFAULT '' COMMENT 'Public label for table',
          PRIMARY KEY         (vrtl_lKeyID),
             KEY vrtl_lRegFormID (vrtl_lRegFormID),
             KEY vrtl_lTableID   (vrtl_lTableID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);

   //         -- http://ellislab.com/codeigniter%20/user-guide/helpers/captcha_helper.html
      $sqlStr = 'DROP TABLE IF EXISTS captcha;';
      $this->db->query($sqlStr);

      $sqlStr =
         "CREATE TABLE IF NOT EXISTS captcha (
             captcha_id   bigint(13)  unsigned NOT NULL auto_increment,
             captcha_time int(10)     unsigned NOT NULL,
             ip_address   varchar(16) default '0' NOT NULL,
             word         varchar(20) NOT NULL,
          PRIMARY KEY `captcha_id` (`captcha_id`),
             KEY `word` (`word`)
         );";
      $this->db->query($sqlStr);

      $this->upgradeDBLevel('1.006', 'Volunteer registration forms');
      return('Upgrade from 1.005 to 1.006 successful<br>');
   }

   public function upgrade_01_006_to_01_007(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // new tables
      $this->up_01_007_tablePerms();
      $this->up_01_007_pledge();
      $this->up_01_007_ufDDLMulti();
      $this->up_01_007_clientPrograms();
      $this->up_01_007_customForms();
      $this->up_01_007_timeSheets();
      $this->up_01_007_cppTests();
      $this->up_01_007_imgDocs();
      $this->up_01_007_creports();


         // add "user" to groups
      $sqlStr =
           "ALTER TABLE groups_parent
               CHANGE gp_enumGroupType gp_enumGroupType
                  ENUM('people', 'household', 'business', 'businessContact', 'volunteer',
                     'staff', 'sponsorship', 'gift', 'client', 'user')
               CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
               DEFAULT 'people';";
      $this->db->query($sqlStr);

         // add pledge ID to gift table
      $sqlStr =
         "ALTER TABLE  gifts
            ADD gi_lPledgeID INT NULL DEFAULT NULL
               COMMENT 'Foreign key to the pledge table'
               AFTER gi_lDepositLogID,
            ADD INDEX (gi_lPledgeID);";
      $this->db->query($sqlStr);

         // add functionality to personalized tables (hide/show, collapsable heading groups, etc)
      $sqlStr =
           "ALTER TABLE uf_tables
              ADD  pft_bHidden TINYINT( 1 ) NOT NULL DEFAULT  '0'
                 COMMENT 'If set, the table is hidden' AFTER  pft_bMultiEntry ,
              ADD  pft_bCollapsibleHeadings TINYINT( 1 ) NOT NULL DEFAULT  '0'
                 COMMENT 'If set, all fields under a header to be collapsed' AFTER  pft_bHidden ,
              ADD  pft_bCollapseDefaultHide TINYINT( 1 ) NOT NULL DEFAULT  '0'
                 COMMENT  'If set, the default state is collapsed' AFTER  pft_bCollapsibleHeadings,
              CHANGE  `pft_enumAttachType`  `pft_enumAttachType`
                  ENUM(  'people',  'business',  'sponsorship', 'client',  'location',  'gift',
                      'volunteer', 'clientProgramEnrollment', 'clientProgramAttendance')
                      CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
                      COMMENT  'Parent table this table attaches to';";
      $this->db->query($sqlStr);

         // multi-select ddl support
      $sqlStr =
           "ALTER TABLE  uf_fields CHANGE  pff_enumFieldType  pff_enumFieldType
                ENUM('Checkbox',  'Date',  'DateTime',  'TextLong', 'Text255',
                     'Text80',  'Text20',  'Integer',  'Currency',  'DDL',
                     'Log',  'Heading',  'Email',  'Hyperlink',  'Reminder',
                     'DDLMulti', 'clientID' )
            CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT  'custom field type';";
      $this->db->query($sqlStr);

      $sqlStr =
            "ALTER TABLE uf_fields
                ADD pff_bHidden TINYINT(1) NOT NULL
                   DEFAULT '0' COMMENT 'If set, hide this field' AFTER pff_lCurrencyACO,
                ADD pff_strFieldNotes TEXT NOT NULL AFTER  pff_strFieldNameUser,
                ADD  pff_bPrefilled   TINYINT(1) NOT NULL DEFAULT  '0' COMMENT 'For multi-record fields, prefill from most recent' AFTER  pff_enumFieldType;";
      $this->db->query($sqlStr);

         // add subgroup to child groups to qualify user permissions in
         // personalized tables and custom forms
      $sqlStr =
         "ALTER TABLE groups_child
             ADD gc_enumSubGroup
                 ENUM('customForm',    'personalizedTable', 'user',
                      'clientProgram', 'clientPrePostTest') NULL
             DEFAULT NULL
             COMMENT 'Sub-group qualifier for certain groups (i.e. user perms)'
             AFTER  gc_lForeignID,
          ADD INDEX (gc_enumSubGroup);";
      $this->db->query($sqlStr);

         // if volunteer registered via vol reg form, log the form ID
      $sqlStr =
           'ALTER TABLE volunteers
              ADD vol_lRegFormID INT NULL DEFAULT NULL COMMENT \'If user registered via vol. reg. form, this is the formID\' AFTER vol_lPeopleID';
      $this->db->query($sqlStr);

         // group membership must now be qualified by subgroup
      $sqlStr =
          "ALTER TABLE groups_child DROP INDEX gc_lGroupID_2,
           ADD UNIQUE gc_lGroupID_2(gc_lGroupID, gc_lForeignID, gc_enumSubGroup);";
      $this->db->query($sqlStr);

         // group membership must now be qualified by subgroup
      $sqlStr =
          "ALTER TABLE people_names
             ADD pe_strFax     VARCHAR( 40) NOT NULL DEFAULT '' AFTER pe_strCell,
             ADD pe_strWebSite VARCHAR(255) NOT NULL DEFAULT '' AFTER pe_strFax;";
      $this->db->query($sqlStr);

         // add new generic list type: Client pre/post test categories
      $sqlStr =
           "ALTER TABLE  `lists_generic`
            CHANGE  `lgen_enumListType`  `lgen_enumListType`
               ENUM('bizCat',  'bizContactRel',  'inKind',  'attrib',
                   'campaignExpense',  'majorGiftCats',  'giftPayType',
                   'volJobCat',  'sponTermCat',  'volSkills',  'volActivities',
                   'prePostTestCat' )
            CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
      $this->db->query($sqlStr);

         // add new group fields that allow additional info about groups
      $sqlStr =
         "ALTER TABLE  groups_parent
             ADD  gp_bGeneric1 TINYINT( 1 ) NOT NULL DEFAULT  '0' COMMENT  'Boolean: extends the basic group info' AFTER  gp_bTempGroup ,
             ADD  gp_lGeneric1 INT NULL              DEFAULT NULL COMMENT  'Int: extends the basic group info'     AFTER  gp_bGeneric1;";
      $this->db->query($sqlStr);

         // add new group types: 'staffTSProject',  'staffTSLocation'
      $sqlStr =
        "ALTER TABLE  groups_parent CHANGE  gp_enumGroupType  gp_enumGroupType
             ENUM('people',  'household',  'business', 'businessContact',  'volunteer',
                  'staff',  'sponsorship',  'gift',  'client',  'user',
                  'staffTSProject',  'staffTSLocation' )
                  CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT  'people';";
      $this->db->query($sqlStr);


         // alerts for single-entry personalized tables missing data entry
      $sqlStr =
        'ALTER TABLE  uf_tables
            ADD  pft_bAlertIfNoEntry TINYINT( 1 ) NOT NULL DEFAULT  \'0\' COMMENT \'For single-entry tables - generate alert if not completed\' AFTER  pft_bMultiEntry,
            ADD  pft_strAlertMsg     TEXT         NOT NULL                COMMENT \'Alert message if alert flag set and no data entry\'         AFTER  pft_bAlertIfNoEntry; ';
      $this->db->query($sqlStr);

         // user-created verification for client program enrollment and attendance records
      $sqlStr =
        'ALTER TABLE cprograms
            ADD  cp_strE_VerificationModule VARCHAR( 255 ) NULL DEFAULT NULL AFTER  cp_bMentorMentee,
            ADD  cp_strE_VModEntryPoint     VARCHAR( 255 ) NULL DEFAULT NULL AFTER  cp_strE_VerificationModule,
            ADD  cp_strA_VerificationModule VARCHAR( 255 ) NULL DEFAULT NULL AFTER  cp_strE_VModEntryPoint,
            ADD  cp_strA_VModEntryPoint     VARCHAR( 255 ) NULL DEFAULT NULL AFTER  cp_strA_VerificationModule;';
      $this->db->query($sqlStr);

      $this->upgradeDBLevel('1.007', 'Upgrade');
      return('Upgrade from 1.006 to 1.007 successful<br>');
   }

   public function upgrade_01_007_to_01_008(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->upgradeUserTablesForAlerts($strDummy);

         // add "users" (i.e. staff members) to personalized table list
      $sqlStr =
          "ALTER TABLE `uf_tables`
           CHANGE  `pft_enumAttachType`  `pft_enumAttachType`
              ENUM('people', 'business', 'sponsorship', 'client', 'location', 'gift',
                   'volunteer',  'clientProgramEnrollment',  'clientProgramAttendance',
                   'user' )
              CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
              COMMENT  'Parent table this table attaches to';";
      $this->db->query($sqlStr);

      $this->upgradeDBLevel('1.008', 'Upgrade');
      return('Upgrade from 1.007 to 1.008 successful<br>');
   }

   public function upgrade_01_008_to_01_009(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

         // add optional verification to personalized tables
         // allow multi-tables to be read-only
      $sqlStr =
          'ALTER TABLE uf_tables
              ADD pft_strVerificationModule  varchar(255) DEFAULT NULL AFTER pft_bCollapseDefaultHide,
              ADD pft_strVModEntryPoint      varchar(255) DEFAULT NULL AFTER pft_strVerificationModule,
              ADD pft_bReadOnly TINYINT(1) NOT NULL DEFAULT  \'0\' COMMENT \'if true, multi-record can only be written once\'
                 AFTER pft_bMultiEntry;';
      $this->db->query($sqlStr);

         // upgrade user permissions to include grant access
      $sqlStr =
         'ALTER TABLE  `admin_users` ADD  `us_bUserAllowGrants` TINYINT( 1 ) NOT NULL DEFAULT  \'0\'
              COMMENT  \'if true, allow access to grant features\' AFTER  `us_bUserAllowAuctions`;';
      $this->db->query($sqlStr);

         // add parent table type 'grants'
      $sqlStr =
           "ALTER TABLE `docs_images`
               CHANGE  `di_enumContextType`  `di_enumContextType`
               ENUM('client', 'location', 'sponsorship', 'people', 'household',
                  'business', 'volunteer',  'Unknown', 'auction', 'auctionPackage',
                  'auctionItem', 'organization', 'staff', 'grants', 'grantProvider' )
            CHARACTER SET latin1
            COLLATE latin1_swedish_ci NOT NULL DEFAULT  'Unknown';";
      $this->db->query($sqlStr);

      $sqlStr =
        "ALTER TABLE  `groups_parent`
            CHANGE  `gp_enumGroupType`  `gp_enumGroupType`
            ENUM('people', 'household', 'business', 'businessContact', 'volunteer',
               'staff', 'sponsorship', 'gift', 'client', 'user', 'staffTSProject',
               'staffTSLocation', 'grants', 'grantProvider' )
            CHARACTER SET latin1
            COLLATE latin1_swedish_ci NOT NULL DEFAULT  'people';";
      $this->db->query($sqlStr);

      $sqlStr =
        "ALTER TABLE `uf_tables`
            CHANGE  `pft_enumAttachType`  `pft_enumAttachType`
            ENUM('people', 'business', 'sponsorship', 'client', 'location', 'gift',
               'volunteer', 'clientProgramEnrollment', 'clientProgramAttendance',
               'user', 'grants', 'grantProvider' )
            CHARACTER SET latin1
            COLLATE latin1_swedish_ci NOT NULL
            COMMENT  'Parent table this table attaches to';";
      $this->db->query($sqlStr);

      $sqlStr =
        "ALTER TABLE  doc_img_tag_ddl
            CHANGE  dit_enumContext  dit_enumContext
            ENUM(  'imgAuction',  'imgAuctionPackage',  'imgAuctionItem', 'imgBiz',  'imgClient',
                   'imgOrganization',  'imgPeople',  'imgSponsor',  'imgStaff',  'imgVolunteer',
                   'docAuction',  'docAuctionPackage',  'docAuctionItem', 'docBiz',  'docClient',
                   'docOrganization',  'docPeople',  'docSponsor',  'docStaff',  'docVolunteer',
                   'Unknown',  'imgClientLocation',  'docClientLocation',
                   'imgGrants',  'imgGrantProvider',  'docGrants',  'docGrantProvider' )
            CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Unknown';";
      $this->db->query($sqlStr);


      $this->upgradeDBLevel('1.009', 'Upgrade');
      return('Upgrade from 1.008 to 1.009 successful<br>');
   }

   public function upgrade_01_009_to_01_010(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // upgrade user permissions to include inventory access
      $sqlStr =
         "ALTER TABLE `admin_users`
              ADD  `us_bUserAllowInventory` TINYINT(1) NOT NULL DEFAULT '0'
              COMMENT  'if true, allow access to inventory features'
              AFTER  `us_bUserAllowAuctions`;";
      $this->db->query($sqlStr);

         // add list type for inventory categories
      $sqlStr =
        "ALTER TABLE  `lists_generic` CHANGE  `lgen_enumListType`  `lgen_enumListType`
            ENUM('bizCat',  'bizContactRel',  'inKind',  'attrib', 'campaignExpense',
                 'majorGiftCats',  'giftPayType',  'volJobCat',  'sponTermCat',
                 'volSkills',  'volActivities',  'prePostTestCat',  'inventoryCat' )
         CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
      $this->db->query($sqlStr);

         // image/document types
      $sqlStr =
        "ALTER TABLE  `docs_images` CHANGE  `di_enumContextType`  `di_enumContextType`
         ENUM('client',  'location',  'sponsorship', 'people',  'household',
            'business',  'volunteer',  'Unknown',  'auction',  'auctionPackage',
            'auctionItem',  'organization',  'staff',  'grants', 'grantProvider',
            'inventoryItem' )
         CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT  'Unknown';";
      $this->db->query($sqlStr);

         // inventory image/doc tags
      $sqlStr =
        "ALTER TABLE  doc_img_tag_ddl
            CHANGE  dit_enumContext  dit_enumContext
            ENUM(  'imgAuction',  'imgAuctionPackage',  'imgAuctionItem', 'imgBiz',  'imgClient',
                   'imgOrganization',  'imgPeople',  'imgSponsor',  'imgStaff',  'imgVolunteer',
                   'docAuction',  'docAuctionPackage',  'docAuctionItem', 'docBiz',  'docClient',
                   'docOrganization',  'docPeople',  'docSponsor',  'docStaff',  'docVolunteer',
                   'Unknown',  'imgClientLocation',  'docClientLocation',
                   'imgGrants',  'imgGrantProvider',  'docGrants',  'docGrantProvider',
                   'imgInventory', 'docInventory' )
            CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Unknown';";
      $this->db->query($sqlStr);

         /* ----------------------------------
             Inventory categories
         ---------------------------------- */
      $sqlStr = 'DROP TABLE IF EXISTS inv_cats;';
      $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TABLE IF NOT EXISTS inv_cats (
           ivc_lKeyID        int(11) NOT NULL AUTO_INCREMENT,
           ivc_strCatName    varchar(255) NOT NULL DEFAULT '',
           ivc_strNotes      text NOT NULL,
           ivc_lParentID     int(11) DEFAULT NULL COMMENT 'Parent category ID in this table',

           ivc_bRetired      tinyint(1) NOT NULL DEFAULT '0',
           ivc_lOriginID     int(11) NOT NULL DEFAULT '0',
           ivc_lLastUpdateID int(11) NOT NULL DEFAULT '0',
           ivc_dteOrigin     datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
           ivc_dteLastUpdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

           PRIMARY KEY (ivc_lKeyID),
           KEY ivc_strCatName (ivc_strCatName),
           KEY ivc_lParentID (ivc_lParentID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Inventory Categories' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

         /*----------------------------------
             Inventory items
         ----------------------------------*/
      $sqlStr = 'DROP TABLE IF EXISTS inv_items;';
      $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TABLE IF NOT EXISTS inv_items (
           ivi_lKeyID            int(11)       NOT NULL AUTO_INCREMENT,
           ivi_strItemName       varchar(255)  NOT NULL DEFAULT '',
           ivi_strItemSNa        varchar(255)  NOT NULL DEFAULT '' COMMENT 'Serial Number (a)',
           ivi_strItemSNb        varchar(255)  NOT NULL DEFAULT '' COMMENT 'Serial Number (b)',
           ivi_strRParty         varchar(255)  NOT NULL DEFAULT '' COMMENT 'Responsible Party',
           ivi_curEstValue       decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Estimated value',
           ivi_lACOID            int(11)       NOT NULL DEFAULT '0'    COMMENT 'foreign key to table admin_aco',

           ivi_bAvailForLoan     tinyint(1)    NOT NULL DEFAULT '0'    COMMENT 'if true, item is available for loan',
           ivi_dteRemInventory   date    DEFAULT NULL COMMENT 'Date removed from inventory (null for still in inventory)',
           ivi_lRemInventoryByID int(11) DEFAULT NULL COMMENT 'User who removed item from inventory; foreign key to table admin_users',

           ivi_dteReportedLost   date DEFAULT NULL COMMENT 'Date reported lost (null for not lost)',
           ivi_lFlaggedLostByID  int(11)    DEFAULT NULL COMMENT 'id of user reporting item lost; foreign key to table admin_users',
           ivi_strLostNotes      text NOT NULL,

           ivi_strLocation       text NOT NULL,
           ivi_strDescription    text NOT NULL,
           ivi_dteObtained       datetime NOT NULL DEFAULT '0000-00-00 00:00:00',

           ivi_lCategoryID    int(11) DEFAULT NULL COMMENT 'Foreign key to inventory item category',

           ivi_bRetired       tinyint(1) NOT NULL DEFAULT '0',
           ivi_lOriginID      int(11)    NOT NULL DEFAULT '0',
           ivi_lLastUpdateID  int(11)    NOT NULL DEFAULT '0',
           ivi_dteOrigin      datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
           ivi_dteLastUpdate  timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

           PRIMARY KEY (ivi_lKeyID),
           KEY ivi_strItemName       (ivi_strItemName),
           KEY ivi_strItemSNa        (ivi_strItemSNa),
           KEY ivi_strItemSNb        (ivi_strItemSNb),
           KEY ivi_lCategoryID       (ivi_lCategoryID),
           KEY ivi_dteRemInventory   (ivi_dteRemInventory),
           KEY ivi_lRemInventoryByID (ivi_lRemInventoryByID),
           KEY ivi_dteReportedLost   (ivi_dteReportedLost),
           KEY ivi_lFlaggedLostByID  (ivi_lFlaggedLostByID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Inventory Items' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

         /*----------------------------------
            check out / check in table
         ----------------------------------*/
      $sqlStr = 'DROP TABLE IF EXISTS inv_cico;';
      $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TABLE IF NOT EXISTS inv_cico (
           icc_lKeyID            int(11)       NOT NULL AUTO_INCREMENT,
           icc_lItemID           int(11)       NOT NULL DEFAULT '0' COMMENT 'foreign key to table inv_items',
           icc_strCO_Notes       text NOT NULL COMMENT 'Notes about item at check-out',
           icc_strCI_Notes       text NOT NULL COMMENT 'Notes about item at check-in',
           icc_strCheckedOutTo   varchar(255)  NOT NULL DEFAULT '',
           icc_strSecurity       varchar(255)  NOT NULL DEFAULT '' COMMENT 'deposit, collateral, etc',
           icc_dteCheckedOut     date NOT NULL COMMENT 'Date checked out',
           icc_dteCheckedIn      date DEFAULT NULL COMMENT 'Date checked in (null for not checked in)',

           icc_lCheckedOutByID   int(11)    NOT NULL DEFAULT '0' COMMENT 'foreign key to table admin_users',
           icc_lCheckedInByID    int(11)    DEFAULT NULL COMMENT 'foreign key to table admin_users',

           icc_bRetired       tinyint(1) NOT NULL DEFAULT '0',
           icc_lOriginID      int(11)    NOT NULL DEFAULT '0',
           icc_lLastUpdateID  int(11)    NOT NULL DEFAULT '0',
           icc_dteOrigin      datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
           icc_dteLastUpdate  timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

           PRIMARY KEY (icc_lKeyID),
           KEY icc_lItemID          (icc_lItemID),
           KEY icc_strCheckedOutTo  (icc_strCheckedOutTo),
           KEY icc_dteCheckedOut    (icc_dteCheckedOut),
           KEY icc_lCheckedOutByID  (icc_lCheckedOutByID),
           KEY icc_lCheckedInByID   (icc_lCheckedInByID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Inventory Checkout/Checkin History' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

         /*----------------------------------
             Inventory history
         ----------------------------------*/
      $sqlStr = 'DROP TABLE IF EXISTS inv_history;';
      $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TABLE IF NOT EXISTS inv_history (
           ih_lKeyID            int(11)       NOT NULL AUTO_INCREMENT,
           ih_lItemID           int(11)       NOT NULL DEFAULT '0' COMMENT 'foreign key to table inv_items',
           ih_lCICOID           int(11)       DEFAULT NULL COMMENT 'foreign key to table inv_cico',
           lh_enumOperation     enum('created', 'updated', 'removed from inventory', 'returned to inventory',
                                     'lost', 'found', 'transfer category',
                                     'checked-out','checked-in',
                                     'made available','made unavailable') NOT NULL,
           ih_lOriginID         int(11)    NOT NULL DEFAULT '0',
           ih_dteLastUpdate     timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (ih_lKeyID),
           KEY ih_lItemID          (ih_lItemID),
           KEY ih_lCICOID          (ih_lCICOID),
           KEY lh_enumOperation    (lh_enumOperation),
           KEY ih_dteLastUpdate    (ih_dteLastUpdate)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Inventory Item History' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);


      $this->upgradeDBLevel('1.010', 'Inventory Management');
      return('Upgrade from 1.009 to 1.010 successful<br>');
   }

   public function upgrade_01_010_to_01_011(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // time zone support
      upgrade_addTimeZoneTables();

         // custom reports
      $sqlStr = 'DROP TABLE IF EXISTS creport_dir;';
      $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TABLE IF NOT EXISTS creport_dir (
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


      $sqlStr = 'DROP TABLE IF EXISTS creport_fields;';
      $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TABLE IF NOT EXISTS creport_fields (
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

      $sqlStr = 'DROP TABLE IF EXISTS creport_search;';
      $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TABLE IF NOT EXISTS creport_search (
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
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Search terms for custom report' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $sqlStr = 'DROP TABLE IF EXISTS creport_sort;';
      $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TABLE IF NOT EXISTS creport_sort (
            crst_lKeyID             int(11) NOT NULL AUTO_INCREMENT,
            crst_lReportID          int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to creport_dir',
            crst_lFieldID           int(11) NOT NULL DEFAULT  '0',
            crst_lTableID           int(11) NOT NULL DEFAULT  '0',
            crst_strFieldID         VARCHAR (255) NOT NULL DEFAULT  '',
            crst_lSortIDX           SMALLINT(  5) NOT NULL DEFAULT  '0',
            crst_bLarkAscending     TINYINT (  1) NOT NULL DEFAULT  1,
            crst_lOriginID          int(11) NOT NULL DEFAULT '0',
            crst_lLastUpdateID      int(11) NOT NULL DEFAULT '0',
            crst_dteOrigin          datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            crst_dteLastUpdate      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (crst_lKeyID),
              KEY crst_lReportID  (crst_lReportID),
              KEY crst_lTableID   (crst_lTableID),
              KEY crst_lFieldID   (crst_lFieldID),
              KEY crst_strFieldID (crst_strFieldID),
              KEY crst_lSortIDX   (crst_lSortIDX)
         ) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Sort terms for custom report' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $this->upgradeDBLevel('1.011', 'Custom Reports/Time Zone Support');
      return('Upgrade from 1.010 to 1.011 successful<br>
             <span style="color: red; font-size: 9pt;">This upgrade includes enhancements to time zone support.<br>
             Please verify your time zone under "Admin / Your Organization / Organization Record".</span><br>');
   }

   public function upgrade_01_011_to_01_012(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // add additional custom report types
      $sqlStr =
         "ALTER TABLE  `creport_dir`
             CHANGE  `crd_enumRptType`  `crd_enumRptType`
                ENUM('gifts',  'gifts/hon',  'gifts/mem',  'gifts/per',  'clients',
                     'clients/cprog', 'clients/spon',  'clients/spon/pay',
                     'people',  'biz',  'volunteer' )
                CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL;";
      $this->db->query($sqlStr);

         // add volunteer job codes to generic lists
      $sqlStr =
         "ALTER TABLE  `lists_generic`
             CHANGE  `lgen_enumListType`  `lgen_enumListType`
                 ENUM('bizCat',  'bizContactRel',  'inKind',  'attrib', 'campaignExpense',
                      'majorGiftCats',  'giftPayType',  'volJobCat',  'sponTermCat',  'volSkills',
                      'volActivities',  'prePostTestCat',  'inventoryCat', 'volShiftJobCodes' )
                 CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL;";
      $this->db->query($sqlStr);

         // add volunteer job code foreign key to volunteer shifts
      $sqlStr =
         "ALTER TABLE `vol_events_dates_shifts`
            ADD  `vs_lJobCode` INT NULL DEFAULT NULL
            COMMENT  'Foreign key to lists_generic'
            AFTER `vs_strShiftName`;";
      $this->db->query($sqlStr);

         // add volunteer job code foreign key to unassigned volunteers
      $sqlStr =
         "ALTER TABLE  `vol_events_dates_shifts_assign`
             ADD  `vsa_lJobCode` INT NULL DEFAULT NULL
             COMMENT  'only for unscheduled hours'
             AFTER `vsa_lActivityID`;";
      $this->db->query($sqlStr);


      $this->upgradeDBLevel('1.012', 'Additional Custom Reports/Vol. Job Codes');
      return('Upgrade from 1.011 to 1.012 successful<br>');
   }

   public function upgrade_01_012_to_01_013(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // upgrade 1.013a 12/1/15
         // volunteer-client associations
      $sqlStr = "DROP TABLE IF EXISTS `vol_client_association`;";
      $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TABLE IF NOT EXISTS `vol_client_association` (
           `vca_lKeyID`        int(11) NOT NULL AUTO_INCREMENT,
           `vca_lVolID`        int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to volunteers',
           `vca_lClientID`     int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to client_records',
           `vca_Notes`         text,
           `vca_bRetired`      tinyint(1) NOT NULL DEFAULT '0',
           `vca_lOriginID`     int(11)    NOT NULL DEFAULT '0',
           `vca_lLastUpdateID` int(11)    NOT NULL DEFAULT '0',
           `vca_dteOrigin`     datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
           `vca_dteLastUpdate` timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
           PRIMARY KEY (`vca_lKeyID`),
           KEY `ufddl_lFieldID` (`vca_lVolID`),
           KEY `ufddl_lSortIDX` (`vca_lClientID`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Associates volunteers with clients' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $sqlStr =
        "ALTER TABLE `admin_users`
         ADD  `us_bUserVolManager` TINYINT( 1 ) NOT NULL DEFAULT  '0'
         AFTER  `us_bUserAllowClient`;";
      $this->db->query($sqlStr);

         // vocabulary for organization
      $sqlStr =
        "ALTER TABLE `admin_chapters`
            ADD `ch_vocZip`       VARCHAR(80) NOT NULL DEFAULT 'Zip Code'   AFTER `ch_lDefaultACO`,
            ADD `ch_vocState`     VARCHAR(80) NOT NULL DEFAULT 'State'      AFTER `ch_vocZip`,
            ADD `ch_vocJobSkills` VARCHAR(80) NOT NULL DEFAULT 'Job Skills' AFTER `ch_vocState`;";
      $this->db->query($sqlStr);

      $this->upgradeDBLevel('1.013', 'volunteer-client associations');
      return('Upgrade from 1.012 to 1.013 successful<br>');
   }




}



