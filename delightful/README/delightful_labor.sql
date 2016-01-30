# -------------------------------------------------------------------
#  Delightful Labor
#
#  copyright (c) 2012-2016 by Database Austin
#  Austin, Texas
#
#  This software is provided under the GPL.
#  Please see http://www.gnu.org/copyleft/gpl.html for details.
#
#  Delightful Labor sql version 1.015 2016-01-30
# --------------------------------------------------------------------


#
# TABLE STRUCTURE FOR: admin_aco
#

DROP TABLE IF EXISTS admin_aco;

# [BREAK]

CREATE TABLE `admin_aco` (
  `aco_lKeyID`            int(11) NOT NULL AUTO_INCREMENT,
  `aco_strFlag`           varchar(20) NOT NULL,
  `aco_strName`           varchar(20) NOT NULL,
  `aco_strCurrencySymbol` varchar(20) NOT NULL,
  `aco_bInUse`            tinyint(1) NOT NULL,
  `aco_bDefault`          tinyint(1) NOT NULL,
  PRIMARY KEY (`aco_lKeyID`),
  KEY `aco_strName` (`aco_strName`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Accounting country of origin';

# [BREAK]

INSERT INTO `admin_aco` (`aco_lKeyID`, `aco_strFlag`, `aco_strName`, `aco_strCurrencySymbol`, `aco_bInUse`, `aco_bDefault`) VALUES
(1, 'us.png',            'US',           '$',        1, 1),
(2, 'uk.png',            'UK',           '&pound;',  0, 0),
(3, 'india.png',         'India',        '&#8360;',  0, 0),
(4, 'canada.png',        'Canada',       '$',        1, 0),
(5, 'europeanunion.png', 'Euro',         '&euro;',   1, 0),
(6, 'australia.png',     'Australia',    '$',        0, 0),
(7, 'iceland.png',       'Iceland',      'Kr',       0, 0),
(8, 'japan.png',         'Japan',        '&yen;',    0, 0),
(9, 'sweden.png',        'Sweden',       'SEK',      0, 0),
(10, 'finland.png',      'Finland',      '&euro;',   0, 0),
(11, 'norway.png',       'Norway',       'NOK',      0, 0),
(12, 'denmark.png',      'Denmark',      'DKK',      0, 0),
(13, 'netherlands.png',  'Netherlands',  '&euro;',   0, 0),
(14, 'germany.png',      'Germany',      '&euro;',   0, 0),
(15, 'mexico.png',       'Mexico',       '$',        1, 0),
(16, 'china.png',        'China',        '&yen;',    0, 0),
(17, 'france.png',       'France',       '&euro;',   0, 0),
(18, 'vatican.png',      'Vatican City', '&euro;',   0, 0),
(19, 'ireland.png',      'Ireland',      '&euro;',   0, 0),
(20, 'italy.png',        'Italy',        '&euro;',   0, 0),
(21, 'nepal.png',        'Nepal',        'Rp',       0, 0),
(22, 'nigeria.png',      'Nigeria',      '&#x20a6;', 0, 0),
(23, 'za.png',           'South Africa', 'R',        0, 0),
(24, 'spain.png',        'Spain',        '&euro;',   0, 0),
(25, 'taiwan.png',       'Taiwan',       'NT$',      0, 0),
(26, 'indonesia.png',    'Indonesia',    'Rp',       0, 0),
(27, 'brazil.png',       'Brazil',       'R$',       0, 0),
(28, 'peru.png',         'Peru',         'S/.',      0, 0),
(29, 'malaysia.png',     'Malaysia',     'RM',       0, 0),
(30, 'bangladesh.png',   'Bangladesh',   'BDT',      0, 0);


# [BREAK]

#
# TABLE STRUCTURE FOR: admin_chapters
#

DROP TABLE IF EXISTS admin_chapters;
# [BREAK]

CREATE TABLE `admin_chapters` (
  `ch_lKeyID`           int(11)      NOT NULL AUTO_INCREMENT,
  `ac_customerID`       int(11)      NOT NULL DEFAULT '0' COMMENT 'reserved',
  `ch_strChapterName`   varchar(80)  NOT NULL DEFAULT '',
  `ch_strBannerTagLine` varchar(80)  NOT NULL DEFAULT 'Your Organization' COMMENT 'Tag line for top banner',
  `ch_lPW_MinLen`       tinyint(4)   NOT NULL DEFAULT '5',
  `ch_bPW_UpperLower`   tinyint(1)   NOT NULL DEFAULT '0',
  `ch_bPW_Number`       tinyint(1)   NOT NULL DEFAULT '0',
  `ch_strAddress1`      varchar(80)  NOT NULL DEFAULT '',
  `ch_strAddress2`      varchar(80)  NOT NULL DEFAULT '',
  `ch_strCity`          varchar(40)  NOT NULL DEFAULT '',
  `ch_strState`         varchar(40)  NOT NULL DEFAULT '',
  `ch_strCountry`       varchar(40)  NOT NULL DEFAULT '',
  `ch_strZip`           varchar(25)  NOT NULL DEFAULT '',
  `ch_strFax`           varchar(50)  NOT NULL DEFAULT '',
  `ch_strPhone`         varchar(50)  NOT NULL DEFAULT '',
  `ch_strEmail`         varchar(80)  NOT NULL DEFAULT '',
  `ch_strEmailCalDistr` varchar(80)  NOT NULL COMMENT '"From" Email for cal distribution lists',
  `ch_strWebSite`       varchar(100) NOT NULL DEFAULT '',
  `ch_strDefAreaCode`   varchar(10)  NOT NULL DEFAULT '',
  `ch_strDefState`      varchar(40)  NOT NULL DEFAULT ''  COMMENT 'Default state for new contacts',
  `ch_strDefCountry`    varchar(40)  NOT NULL DEFAULT ''  COMMENT 'Default country for new contacts',
  `ch_bUS_DateFormat`   tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'if true, default is mm/dd/yyyy',
  `ch_lTimeZone`        int(11)      NOT NULL DEFAULT '2' COMMENT 'foreign key to table lists_tz',
  `ch_strTaxID`         varchar(80)  NOT NULL,
  `ch_lDefaultACO`      int(11)      NOT NULL DEFAULT '1' COMMENT 'foreign key to table admin_aco',
  `ch_vocZip`           varchar(80)  NOT NULL DEFAULT 'Zip Code',
  `ch_vocState`         varchar(80)  NOT NULL DEFAULT 'State',
  `ch_vocJobSkills`     varchar(80)  NOT NULL DEFAULT 'Job Skills',
  `ch_bRetired`         tinyint(1)   NOT NULL DEFAULT '0',
  `ch_lOrigID`          int(11)      NOT NULL DEFAULT '0',
  `ch_lLastUpdateID`    int(11)      NOT NULL DEFAULT '0',
  `ch_dteOrigin`        datetime     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ch_dteLastUpdate`    timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ch_lKeyID`),
  KEY `ch_strChapterName` (`ch_strChapterName`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='identifies the various chapters of the organization';

# [BREAK]

INSERT INTO admin_chapters (
   `ch_lKeyID`,     `ch_strChapterName`, `ch_strBannerTagLine`, `ch_lPW_MinLen`,  `ch_bPW_UpperLower`,
   `ch_bPW_Number`, `ch_strAddress1`,    `ch_strAddress2`,      `ch_strCity`,     `ch_strState`,
   `ch_strCountry`, `ch_strZip`,         `ch_strFax`,           `ch_strPhone`,    `ch_strEmail`,
   `ch_strEmailCalDistr`, `ch_strWebSite`, `ch_strDefAreaCode`, `ch_strDefState`, `ch_strDefCountry`,
   `ch_lTimeZone`,
   `ch_bUS_DateFormat`, `ch_strTaxID`, `ch_lDefaultACO`, `ch_bRetired`,
   `ch_lOrigID`, `ch_lLastUpdateID`, `ch_dteOrigin`, `ch_dteLastUpdate`)
   VALUES (1, 'Your Non-Profit', 'Your Organization', 5, 0,
      0, '123 Pine St.', '', 'Austin', 'TX',
      'USA', '78759', '555-faxx', '(512) 555-1212', '',
      '', '', '512', 'TX', 'US',
      2,
      1, '', 1, 0,
      1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00');

# [BREAK]

#
# TABLE STRUCTURE FOR: admin_usage_log
#

DROP TABLE IF EXISTS admin_usage_log;

# [BREAK]

CREATE TABLE `admin_usage_log` (
  `el_lKeyID`           int(11) NOT NULL AUTO_INCREMENT,
  `el_lUserID`          int(11) DEFAULT NULL,
  `el_enumApp`          enum('EMR','LNP') NOT NULL,
  `el_dteLogDate`       timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `el_strUserName`      varchar(100) NOT NULL DEFAULT '',
  `el_bLoginSuccessful` tinyint(1) NOT NULL DEFAULT '0',
  `el_str_Remote_Addr`  varchar(20) NOT NULL DEFAULT '',
  `el_str_Remote_Host`  varchar(20) NOT NULL DEFAULT '',
  `el_str_Remote_Port`  varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`el_lKeyID`),
  KEY `el_dteLogDate` (`el_dteLogDate`),
  KEY `el_lUserID`    (`el_lUserID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Record of log-ins';

# [BREAK]


#
# TABLE STRUCTURE FOR: admin_users
#

DROP TABLE IF EXISTS admin_users;

# [BREAK]

CREATE TABLE `admin_users` (
  `us_lKeyID`               int(11)      NOT NULL AUTO_INCREMENT,
  `us_strUserName`          varchar(120) NOT NULL DEFAULT '',
  `us_strUserPWord`         varchar(80)  NOT NULL DEFAULT '',
  `us_lChapterID`           int(11)      NOT NULL,
  `us_bAdmin`               tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Access to all systems',
  `us_bDebugger`            tinyint(1) NOT NULL DEFAULT '0',

  `us_bVolAccount`          tinyint(1) NOT NULL DEFAULT '0',
  `us_bVolEditContact`      tinyint(1) NOT NULL DEFAULT '0',
  `us_bVolPassReset`        tinyint(1) NOT NULL DEFAULT '0',
  `us_bVolViewGiftHistory`  tinyint(1) NOT NULL DEFAULT '0',
  `us_bVolEditJobSkills`    tinyint(1) NOT NULL DEFAULT '0',
  `us_bVolViewHrsHistory`   tinyint(1) NOT NULL DEFAULT '0',
  `us_bVolAddVolHours`      tinyint(1) NOT NULL DEFAULT '0',
  `us_bVolShiftSignup`      tinyint(1) NOT NULL DEFAULT '0',

  `us_bUserDataEntryPeople` tinyint(1) NOT NULL DEFAULT '0',
  `us_bUserDataEntryGifts`  tinyint(1) NOT NULL DEFAULT '0',
  `us_bUserEditPeople`      tinyint(1) NOT NULL DEFAULT '0',
  `us_bUserEditGifts`       tinyint(1) NOT NULL DEFAULT '0',
  `us_bUserViewPeople`      tinyint(1) NOT NULL DEFAULT '0',
  `us_bUserViewGiftHistory` tinyint(1) NOT NULL DEFAULT '0',
  `us_bUserViewReports`     tinyint(1) NOT NULL DEFAULT '0',
  `us_bUserAllowExports`    tinyint(1) NOT NULL DEFAULT '0',
  `us_bUserAllowAuctions`   tinyint(1) NOT NULL DEFAULT '0',
  `us_bUserAllowInventory`  tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if true, allow access to inventory features',
  `us_bUserAllowGrants`     tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if true, allow access to grant features',
  `us_bUserAllowSponsorship`   TINYINT(1) NOT NULL DEFAULT  '0',
  `us_bUserAllowSponFinancial` TINYINT(1) NOT NULL DEFAULT  '0',
  `us_bUserAllowClient`        TINYINT(1) NOT NULL DEFAULT  '0',
  `us_bUserVolManager`         TINYINT(1) NOT NULL DEFAULT  '0',

  `us_lPeopleID`            int(11)    DEFAULT NULL,

  `us_strFirstName`         varchar(40) NOT NULL DEFAULT '',
  `us_strLastName`          varchar(40) NOT NULL DEFAULT '',
  `us_strTitle`             varchar(80) NOT NULL DEFAULT '',
  `us_strPhone`             varchar(50) NOT NULL,
  `us_strCell`              varchar(50) NOT NULL,
  `us_strEmail`             varchar(80) NOT NULL DEFAULT '',
  `us_strAddr1`             varchar(80) NOT NULL DEFAULT '',
  `us_strAddr2`             varchar(80) NOT NULL DEFAULT '',
  `us_strCity`              varchar(40) NOT NULL DEFAULT '',
  `us_strState`             varchar(40) NOT NULL DEFAULT '',
  `us_strCountry`           varchar(40) NOT NULL DEFAULT '',
  `us_strZip`               varchar(25) NOT NULL DEFAULT '',
  `us_enumDateFormat`       enum('M j Y','m/d/Y','j M Y','d/m/Y','F j Y','j F Y') NOT NULL DEFAULT 'j M Y',
  `us_enumMeasurePref`      enum('metric','English') NOT NULL DEFAULT 'English',
  `us_bInactive`            tinyint(1) NOT NULL DEFAULT '0',
  `us_dteOrigin`            datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `us_dteLastUpdate`        timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `us_lOriginID`            int(11) NOT NULL DEFAULT '0',
  `us_lLastUpdateID`        int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`us_lKeyID`),
  KEY `us_strUserName`  (`us_strUserName`),
  KEY `us_strUserPWord` (`us_strUserPWord`),
  KEY `us_lChapterID`   (`us_lChapterID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='users of the Delightful Labor system';

# [BREAK]

INSERT INTO admin_users (
          `us_lKeyID`, `us_strUserName`, `us_strUserPWord`, `us_lChapterID`, `us_bAdmin`,
          `us_bDebugger`, `us_strFirstName`, `us_strLastName`, `us_strTitle`, `us_strPhone`,
          `us_strCell`, `us_strEmail`, `us_strAddr1`, `us_strAddr2`, `us_strCity`, `us_strState`,
          `us_strCountry`, `us_strZip`, `us_enumDateFormat`, `us_enumMeasurePref`, `us_bInactive`,
          `us_dteOrigin`, `us_dteLastUpdate`, `us_lOriginID`, `us_lLastUpdateID`)
    VALUES (1, 'admin', PASSWORD('admin'), 1, 1, 0, 'Jane', 'Doe', '',
               '(512) 555-1212', '', '', '123 Pine', 'Suite A101-a', 'Austin', 'TX', 'United States',
               '78000', 'm/d/Y', 'English', 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00', 1, 1);

# [BREAK]


--
-- Table structure for table `admin_version`
--

DROP TABLE IF EXISTS `admin_version`;

# [BREAK]

CREATE TABLE IF NOT EXISTS `admin_version` (
  `av_lKeyID`          int(11)       NOT NULL AUTO_INCREMENT,
  `av_sngVersion`      decimal(10,3) NOT NULL DEFAULT '0.000',
  `av_strVersionNotes` varchar(80)   NOT NULL DEFAULT '',
  `av_dteInstalled`    timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`av_lKeyID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Version and upgrade info' ;

# [BREAK]

--
-- Dumping data for table `admin_version`
--

-- note: av_dteInstalled is not specified; it will default to the actual date/time of installation
INSERT INTO `admin_version` (`av_lKeyID`, `av_sngVersion`, `av_strVersionNotes`) VALUES
(1,  '0.900', 'Initial schema for Delightful Labor'),
(2,  '0.901', 'Upgrade for deposit_log'),
(3,  '0.902', 'Added full text search to personalized logs'),
(4,  '1.000', 'Production release'),
(5,  '1.001', 'Upgrade to support unscheduled volunteer activities'),
(6,  '1.002', 'Upgrade to support serialized objects'),
(7,  '1.003', 'Intermediate schema change (not released)'),
(8,  '1.004', 'Silent Auctions'),
(9,  '1.005', 'Additional countries, client med. records'),
(10, '1.006', 'Volunteer registration forms; multi-record personalized tables'),
(11, '1.007', 'Client features'),
(12, '1.008', 'Update to personalized tables'),
(13, '1.009', 'Upgrade'),
(14, '1.010', 'Inventory Management'),
(15, '1.011', 'Custom Reports'),
(16, '1.012', 'Vol. Job Codes'),
(17, '1.013', 'Enhanced volunteer registration'),
(18, '1.014', 'Enhanced importing features'),
(19, '1.015', 'Enhanced custom reports')
;

# [BREAK]


#
# TABLE STRUCTURE FOR: biz_contacts
#

DROP TABLE IF EXISTS biz_contacts;
# [BREAK]

CREATE TABLE `biz_contacts` (
  `bc_lKeyID`           int(11) NOT NULL AUTO_INCREMENT,
  `bc_lBizID`           int(11) NOT NULL COMMENT 'foreign key to people_names bBiz=true',
  `bc_lContactID`       int(11) NOT NULL COMMENT 'foreign key to people_names bBiz=false',
  `bc_lBizContactRelID` int(11) DEFAULT NULL COMMENT 'Foreign key to generic list type "bizContactRel"',
  `bc_bSoftCash`        tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If true, a soft-cash relationship exists',
  `bc_bRetired`         tinyint(1) NOT NULL DEFAULT '0',
  `bc_lOriginID`        int(11) NOT NULL COMMENT 'ID of the creator of business contact record' ,
  `bc_dteOrigin`        datetime NOT NULL,
  `bc_lLastUpdateID`    int(11) NOT NULL COMMENT 'ID of the user who last updated business contact record',
  `bc_dteLastUpdate`    timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`bc_lKeyID`),
  KEY `bc_lBizID` (`bc_lBizID`,`bc_lContactID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Business contacts';

# [BREAK]


#
# TABLE STRUCTURE FOR: client_loc_supported_sponprogs
#

DROP TABLE IF EXISTS client_loc_supported_sponprogs;
# [BREAK]

CREATE TABLE `client_loc_supported_sponprogs` (
  `clsp_lKeyID` int(11)      NOT NULL AUTO_INCREMENT,
  `clsp_lLocID` int(11)      NOT NULL DEFAULT '0' COMMENT 'Foreign key to client_location',
  `clsp_lSponProgID` int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to lists_sponsorship_programs',
  PRIMARY KEY (`clsp_lKeyID`),
  KEY `clsp_lLocID` (`clsp_lLocID`),
  KEY `clsp_lSponCatID` (`clsp_lSponProgID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Supported sponsorship progs for this location';

# [BREAK]


#
# TABLE STRUCTURE FOR: client_location
#

DROP TABLE IF EXISTS client_location;

# [BREAK]

CREATE TABLE `client_location` (
  `cl_lKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `cl_strLocation` varchar(200) NOT NULL DEFAULT '',
  `cl_strDescription` text NOT NULL,
  `cl_strCountry`     varchar(200) NOT NULL DEFAULT '',
  `cl_strWebLink`     varchar(200) NOT NULL DEFAULT '',
  `cl_strAddress1`    varchar(100) NOT NULL DEFAULT '',
  `cl_strAddress2`    varchar(100) NOT NULL DEFAULT '',
  `cl_strCity`        varchar(50)  NOT NULL DEFAULT '',
  `cl_strState`       varchar(50)  NOT NULL DEFAULT '',
  `cl_strPostalCode`  varchar(25)  NOT NULL DEFAULT '',
  `cl_strNotes`       text         NOT NULL,
  `cl_bEnableEMR`     tinyint(1)   NOT NULL DEFAULT '0' COMMENT 'If set, allow medical records features for this location',
  `cl_bRetired`       tinyint(1)   NOT NULL DEFAULT '0',
  `cl_lOriginID`      int(11)      NOT NULL DEFAULT '0',
  `cl_lLastUpdateID`  int(11)      NOT NULL DEFAULT '0',
  `cl_dteOrigin`      datetime     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cl_dteLastUpdate`  timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cl_lKeyID`),
  KEY `cl_strLocation` (`cl_strLocation`),
  KEY `cl_strCountry`  (`cl_strCountry`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Client locations';

# [BREAK]


#
# TABLE STRUCTURE FOR: client_records
#

DROP TABLE IF EXISTS client_records;
# [BREAK]

CREATE TABLE `client_records` (
  `cr_lKeyID`              int(11) NOT NULL AUTO_INCREMENT,
  `cr_strFName`            varchar(30) NOT NULL DEFAULT '',
  `cr_strMName`            varchar(30) NOT NULL DEFAULT '',
  `cr_strLName`            varchar(30) NOT NULL DEFAULT '',
  `cr_dteEnrollment`       date DEFAULT NULL,
  `cr_dteBirth`            date DEFAULT NULL,
  `cr_dteDeath`            date DEFAULT NULL,
  `cr_enumGender`          enum('Male','Female','Unknown') NOT NULL DEFAULT 'Unknown',
  `cr_strAddr1`            varchar(80) NOT NULL DEFAULT '',
  `cr_strAddr2`            varchar(80) NOT NULL DEFAULT '',
  `cr_strCity`             varchar(80) NOT NULL DEFAULT '',
  `cr_strState`            varchar(80) NOT NULL DEFAULT '',
  `cr_strCountry`          varchar(80) NOT NULL DEFAULT '',
  `cr_strZip`              varchar(40) NOT NULL DEFAULT '',
  `cr_strPhone`            varchar(40) NOT NULL DEFAULT '',
  `cr_strCell`             varchar(40) NOT NULL DEFAULT '',
  `cr_strEmail`            varchar(120) NOT NULL DEFAULT '',
  `cr_lLocationID`         int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table child_location',
  `cr_lStatusCatID`        int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to client_status_cats',
  `cr_lVocID`              int(11) NOT NULL             COMMENT 'foreign key to lists_client_vocab ',
  `cr_lMaxSponsors`        int(11) NOT NULL DEFAULT '0',
  `cr_strBio`              text NOT NULL,
  `cr_bNoLongerAtLocation` tinyint(1) NOT NULL DEFAULT '0',
  `cr_lAttributedTo`       int(11) DEFAULT NULL,
  `cr_strImportID`         varchar(40) DEFAULT NULL,
  `cr_bRetired`            tinyint(1) NOT NULL DEFAULT '0',
  `cr_lOriginID`           int(11)    NOT NULL DEFAULT '0',
  `cr_lLastUpdateID`       int(11)    NOT NULL DEFAULT '0',
  `cr_dteOrigin`           datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cr_dteLastUpdate`       timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY            (`cr_lKeyID`),
  KEY `cr_strFName`      (`cr_strFName`),
  KEY `cr_strLName`      (`cr_strLName`),
  KEY `cr_dteBirth`      (`cr_dteBirth`),
  KEY `cr_lLocationID`   (`cr_lLocationID`),
  KEY `cr_enumGender`    (`cr_enumGender`),
  KEY `cr_lAttributedTo` (cr_lAttributedTo),
  FULLTEXT KEY cr_strBio (cr_strBio)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Client''s records';

# [BREAK]

#
# TABLE STRUCTURE FOR: client_status
#

DROP TABLE IF EXISTS client_status;

# [BREAK]

CREATE TABLE `client_status` (
  `csh_lKeyID`                int(11)    NOT NULL AUTO_INCREMENT,
  `csh_lClientID`             int(11)    NOT NULL,
  `csh_lStatusID`             int(11)    NOT NULL COMMENT 'foreign key to lists_client_status_entries',
  `csh_dteStatusDate`         date       NOT NULL,
  `csh_bIncludeNotesInPacket` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if checked, include notes field in sponsor packet',
  `csh_strStatusTxt`          text       NOT NULL,
  `csh_bRetired`              tinyint(1) NOT NULL DEFAULT '0',
  `csh_lOriginID`             int(11)    NOT NULL,
  `csh_lLastUpdateID`         int(11)    NOT NULL,
  `csh_dteOrigin`             datetime   NOT NULL,
  `csh_dteLastUpdate`         timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`csh_lKeyID`),
  KEY `csh_lClientID`             (csh_lClientID),
  KEY `csh_lStatusID`             (csh_lStatusID),
  KEY `csh_dteStatusDate`         (csh_dteStatusDate),
  KEY `csh_bIncludeNotesInPacket` (csh_bIncludeNotesInPacket),
  FULLTEXT KEY csh_strStatusTxt   (csh_strStatusTxt)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='client''s status history';

# [BREAK]


#
# TABLE STRUCTURE FOR: client_status_cats
#

DROP TABLE IF EXISTS client_status_cats;
# [BREAK]

CREATE TABLE `client_status_cats` (
  `csc_lKeyID`         int(11) NOT NULL AUTO_INCREMENT,
  `csc_strCatName`     varchar(200) NOT NULL DEFAULT '',
  `csc_strDescription` text         NOT NULL,
  `csc_bProtected`     tinyint(1)   NOT NULL DEFAULT '0',
  `csc_bRetired`       tinyint(1)   NOT NULL DEFAULT '0',
  `csc_lOriginID`      int(11)      NOT NULL DEFAULT '0',
  `csc_lLastUpdateID`  int(11)      NOT NULL DEFAULT '0',
  `csc_dteOrigin`      datetime     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `csc_dteLastUpdate`  timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`csc_lKeyID`),
  KEY `csc_strCatName` (`csc_strCatName`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Client status categories';
# [BREAK]

INSERT INTO client_status_cats (`csc_lKeyID`, `csc_strCatName`, `csc_strDescription`, `csc_bProtected`, `csc_bRetired`, `csc_lOriginID`, `csc_lLastUpdateID`, `csc_dteOrigin`, `csc_dteLastUpdate`)
   VALUES (1, 'Default', '', 1, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00');
# [BREAK]


#
# TABLE STRUCTURE FOR: client_supported_sponprogs
#

DROP TABLE IF EXISTS client_supported_sponprogs;
# [BREAK]

CREATE TABLE `client_supported_sponprogs` (
  `csp_lKeyID`      int(11) NOT NULL AUTO_INCREMENT,
  `csp_lClientID`   int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to client_records ',
  `csp_lSponProgID` int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to lists_sponsorship_categories',
  PRIMARY KEY (`csp_lKeyID`),
  KEY `csp_lClientID`  (`csp_lClientID`),
  KEY `csp_lSponCatID` (`csp_lSponProgID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Supported sponsorship progs for this client';

# [BREAK]


#
# TABLE STRUCTURE FOR: client_xfers
#

DROP TABLE IF EXISTS client_xfers;
# [BREAK]

CREATE TABLE `client_xfers` (
  `cx_lKeyID`           int(11)   NOT NULL AUTO_INCREMENT,
  `cx_lClientID`        int(11)   NOT NULL DEFAULT '0',
  `cx_lOldLocID`        int(11)   NOT NULL DEFAULT '0',
  `cx_lOldStatCatID`    int(11)   NOT NULL DEFAULT '0',
  `cx_lOldVocID`        int(11)   NOT NULL DEFAULT '0',
  `cx_lNewLocID`        int(11)   NOT NULL DEFAULT '0',
  `cx_lNewStatCatID`    int(11)   NOT NULL DEFAULT '0',
  `cx_lNewVocID`        int(11)   NOT NULL DEFAULT '0',
  `cl_dteEffectiveDate` date      NOT NULL COMMENT 'Effective date of transfer',
  `cx_lLastUpdateID`    int(11)   NOT NULL DEFAULT '0',
  `cx_dteLastUpdate`    timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cx_lKeyID`),
  KEY `cx_lClientID`        (`cx_lClientID`),
  KEY `cx_lOldLocID`        (`cx_lOldLocID`),
  KEY `cx_lOldStatCatID`    (`cx_lOldStatCatID`),
  KEY `cx_lOldVocID`        (`cx_lOldVocID`),
  KEY `cx_lNewLocID`        (`cx_lNewLocID`),
  KEY `cx_lNewStatCatID`    (`cx_lNewStatCatID`),
  KEY `cx_lNewVocID`        (`cx_lNewVocID`),
  KEY `cl_dteEffectiveDate` (`cl_dteEffectiveDate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Children''s transfer history';

# [BREAK]

#
# Table structure for table `cpp_questions`
#

DROP TABLE IF EXISTS `cpp_questions`;
# [BREAK]

CREATE TABLE IF NOT EXISTS `cpp_questions` (
  `cpq_lKeyID`        int(11)    NOT NULL AUTO_INCREMENT,
  `cpq_lPrePostID`    int(11)    NOT NULL DEFAULT '0' COMMENT 'Foreign key to table cpp_tests',
  `cpq_lSortIDX`      int(11)    NOT NULL DEFAULT '0',
  `cpq_strQuestion`   text       NOT NULL,
  `cpq_strAnswer`     text       NOT NULL,
  `cpq_bRetired`      tinyint(1) NOT NULL DEFAULT '0',
  `cpq_lOriginID`     int(11)    NOT NULL DEFAULT '0',
  `cpq_lLastUpdateID` int(11)    NOT NULL DEFAULT '0',
  `cpq_dteOrigin`     datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cpq_dteLastUpdate` timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cpq_lKeyID`),
  KEY `cpq_lPrePostID` (`cpq_lPrePostID`),
  KEY `cpq_lSortIDX`   (`cpq_lSortIDX`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

# [BREAK]



#
# Table structure for table `cpp_tests`
#

DROP TABLE IF EXISTS `cpp_tests`;
# [BREAK]

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

# [BREAK]

#
# Table structure for table `cpp_test_log`
#

DROP TABLE IF EXISTS `cpp_test_log`;
# [BREAK]

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

# [BREAK]

#
# Table structure for table `cpp_test_results`
#

DROP TABLE IF EXISTS `cpp_test_results`;
# [BREAK]

CREATE TABLE IF NOT EXISTS `cpp_test_results` (
  `cptr_lKeyID`         int(11)    NOT NULL AUTO_INCREMENT,
  `cptr_lQuestionID`    int(11)    NOT NULL DEFAULT '0' COMMENT 'Foreign key to table cpp_questions',
  `cptr_lTestLogID`     int(11)    NOT NULL DEFAULT '0' COMMENT 'Foreign key to table cpp_test_log',
  `cptr_bPreTest`       tinyint(1) NOT NULL DEFAULT '0',
  `cptr_bAnswerCorrect` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cptr_lKeyID`),
  KEY `cptr_lQuestionID` (`cptr_lQuestionID`),
  KEY `cptr_lTestLogID`  (`cptr_lTestLogID`),
  KEY `cptr_lQuestLog`   (`cptr_lQuestionID`,`cptr_lTestLogID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

# [BREAK]


#
# Table structure for table `cprograms`
#

DROP TABLE IF EXISTS `cprograms`;
# [BREAK]
CREATE TABLE IF NOT EXISTS `cprograms` (
  `cp_lKeyID`                  int(11) NOT NULL AUTO_INCREMENT,
  `cp_strProgramName`          varchar(255) NOT NULL DEFAULT '',
  `cp_strDescription`          text         NOT NULL COMMENT 'Internal description',
  `cp_dteStart`                date         NOT NULL DEFAULT '0000-00-00' COMMENT 'Program start date',
  `cp_dteEnd`                  date         NOT NULL DEFAULT '0000-00-00' COMMENT 'Program end date',
  `cp_strVocEnroll`            varchar(80)  NOT NULL DEFAULT 'Enrollment',
  `cp_strVocAttendance`        varchar(80)  NOT NULL DEFAULT 'Attendance',
  `cp_bHidden`                 tinyint(1)   NOT NULL DEFAULT '0' COMMENT 'Hidden but not deleted',
  `cp_lEnrollmentTableID`      int(11)      NOT NULL DEFAULT '0',
  `cp_lAttendanceTableID`      int(11)      NOT NULL DEFAULT '0',
  `cp_lActivityFieldID`        int(11)      NOT NULL DEFAULT '0' COMMENT 'Field ID for the attendance table activity DDL',
  `cp_bMentorMentee`           tinyint(1)   NOT NULL DEFAULT '0' COMMENT 'Is this a mentor/mentee client program?',
  `cp_strE_VerificationModule` VARCHAR( 255 )   NULL DEFAULT NULL,
  `cp_strE_VModEntryPoint`     VARCHAR( 255 )   NULL DEFAULT NULL,
  `cp_strA_VerificationModule` VARCHAR( 255 )   NULL DEFAULT NULL,
  `cp_strA_VModEntryPoint`     VARCHAR( 255 )   NULL DEFAULT NULL,
  `cp_bRetired`                tinyint(1)   NOT NULL DEFAULT '0',
  `cp_lOriginID`               int(11)      NOT NULL DEFAULT '0',
  `cp_lLastUpdateID`           int(11)      NOT NULL DEFAULT '0',
  `cp_dteOrigin`               datetime     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cp_dteLastUpdate`           timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cp_lKeyID`),
  KEY `cp_strProgramName` (`cp_strProgramName`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

# [BREAK]

#
# Table structure for table 'creport_dir'
#

DROP TABLE IF EXISTS creport_dir;
# [BREAK]

CREATE TABLE IF NOT EXISTS creport_dir (
  crd_lKeyID        int(11) NOT NULL AUTO_INCREMENT,
  crd_strName       varchar(255) NOT NULL DEFAULT '',
  crd_strNotes      text NOT NULL,
  crd_enumRptType   enum(
                        'gifts','gifts/hon','gifts/mem','gifts/per',
                        'clients',  'clients/cprog',  'clients/spon',  'clients/spon/pay',
                        'people',  'biz',  'volunteer'
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Custom report directory' AUTO_INCREMENT=1 ;

# [BREAK]

#
# Table structure for table 'creport_fields'
#

DROP TABLE IF EXISTS creport_fields;
# [BREAK]

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Fields for custom report' AUTO_INCREMENT=1 ;

# [BREAK]


#
# Table structure for table 'creport_search'
#

DROP TABLE IF EXISTS creport_search;
# [BREAK]

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Search terms for custom report' AUTO_INCREMENT=1 ;
# [BREAK]


#
# Table structure for table 'creport_sort'
#

DROP TABLE IF EXISTS creport_sort;
# [BREAK]

CREATE TABLE IF NOT EXISTS creport_sort (
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Sort terms for custom report' AUTO_INCREMENT=1 ;


# [BREAK]

#
# Table structure for table `custom_forms`
#

DROP TABLE IF EXISTS `custom_forms`;
# [BREAK]

CREATE TABLE IF NOT EXISTS `custom_forms` (
  `cf_lKeyID`                int(11) NOT NULL AUTO_INCREMENT,
  `cf_strFormName`           varchar(255) NOT NULL DEFAULT '',
  `cf_strDescription`        text NOT NULL COMMENT 'Internal description',
  `cf_enumContextType`       enum('client','location','sponsorship','people','household','business','volunteer','Unknown','auction','auctionPackage','auctionItem','organization') NOT NULL DEFAULT 'Unknown',
  `cf_strIntro`              text NOT NULL COMMENT 'Displayed at top of intake form',
  `cf_strSubmissionText`     text NOT NULL COMMENT 'Text displayed after successful submission',
  `cf_strBannerTitle`        varchar(255) NOT NULL DEFAULT '',
  `cf_strContact`            varchar(255) NOT NULL DEFAULT '',
  `cf_bCreateNewParent`      tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'If set, create a new parent record',
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

# [BREAK]

#
# Table structure for table `custom_form_log`
#

DROP TABLE IF EXISTS `custom_form_log`;

# [BREAK]

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

# [BREAK]

#
# Table structure for table `custom_form_table_labels`
#

DROP TABLE IF EXISTS `custom_form_table_labels`;
# [BREAK]

CREATE TABLE IF NOT EXISTS `custom_form_table_labels` (
  `cftl_lKeyID`   int(11) NOT NULL AUTO_INCREMENT,
  `cftl_lCFormID` int(11) NOT NULL COMMENT 'Foreign Key to custom_forms',
  `cftl_lTableID` int(11) NOT NULL COMMENT 'Foreign Key to uf_tables',
  `cftl_strLabel` varchar(255) NOT NULL DEFAULT '' COMMENT 'Public label for table',
  PRIMARY KEY (`cftl_lKeyID`),
  KEY `cftl_lCFormID` (`cftl_lCFormID`),
  KEY `cftl_lTableID` (`cftl_lTableID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

# [BREAK]

#
# Table structure for table `custom_form_uf`
#

DROP TABLE IF EXISTS `custom_form_uf`;
# [BREAK]

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

# [BREAK]

#
# Table structure for table 'deposit_log'
#

DROP TABLE IF EXISTS deposit_log;
# [BREAK]
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Deposit Log';
# [BREAK]

#
# TABLE STRUCTURE FOR: docs_images
#

DROP TABLE IF EXISTS docs_images;
# [BREAK]

CREATE TABLE `docs_images` (
  `di_lKeyID`           int(11) NOT NULL AUTO_INCREMENT,
  `di_enumEntryType`    enum('image','pdf','Unknown') NOT NULL DEFAULT 'Unknown',
  `di_enumContextType`  enum('client',    'location',       'sponsorship', 'people',         'household',
                             'business',  'volunteer',      'Unknown',
                             'auction',   'auctionPackage', 'auctionItem',
                             'organization', 'staff', 'grants', 'grantProvider', 'inventoryItem') NOT NULL DEFAULT 'Unknown',
  `di_lForeignID`       int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key based on enumContextType',
  `di_strCaptionTitle`  varchar(255) NOT NULL DEFAULT '',
  `di_strDescription`   text NOT NULL,
  `di_dteDocImage`      date DEFAULT NULL,
  `di_bProfile`         tinyint(1)   NOT NULL DEFAULT '0' COMMENT 'Profile image?',
  `di_strUserFN`        varchar(255) NOT NULL DEFAULT ''  COMMENT 'Original name of file user uploaded',
  `di_strSystemFN`      varchar(255) NOT NULL DEFAULT ''  COMMENT 'Renamed filename/extension',
  `di_strSystemThumbFN` varchar(255) DEFAULT NULL,
  `di_strPath`          varchar(255) NOT NULL DEFAULT '',
  `di_bRetired`         tinyint(1) NOT NULL DEFAULT '0',
  `di_lOriginID`        int(11) NOT NULL DEFAULT '0',
  `di_lLastUpdateID`    int(11) NOT NULL DEFAULT '0',
  `di_dteOrigin`        datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `di_dteLastUpdate`    timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`di_lKeyID`),
  KEY `di_enumEntryType`         (di_enumEntryType),
  KEY `di_enumContextType`       (di_enumContextType),
  KEY `di_lForeignID`            (di_lForeignID),
  KEY `di_dteDocImage`           (di_dteDocImage),
  KEY `di_strSystemFN`           (di_strSystemFN),
  FULLTEXT KEY di_strDescription (di_strDescription)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Document and Image Catelog';

# [BREAK]

#
# Table structure for table 'doc_img_tag_ddl'
#

DROP TABLE IF EXISTS doc_img_tag_ddl;

# [BREAK]

CREATE TABLE IF NOT EXISTS doc_img_tag_ddl (
  `dit_lKeyID`       int(11) NOT NULL AUTO_INCREMENT,

  `dit_enumContext` enum('imgAuction','imgAuctionPackage','imgAuctionItem','imgBiz',
                         'imgClient','imgOrganization','imgPeople','imgSponsor',
                         'imgStaff','imgVolunteer','docAuction','docAuctionPackage',
                         'docAuctionItem','docBiz','docClient','docOrganization',
                         'docPeople','docSponsor','docStaff','docVolunteer',
                         'Unknown','imgClientLocation','docClientLocation',
                         'imgGrants','imgGrantProvider','docGrants','docGrantProvider',
                         'imgInventory', 'docInventory')
                         NOT NULL DEFAULT 'Unknown',
  `dit_strDDLEntry`  varchar(80) NOT NULL,
  `dit_lSortIDX`     int(11) NOT NULL DEFAULT '0',
  `dit_bRetired`     tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY           (`dit_lKeyID`),
  KEY `dit_enumContext` (`dit_enumContext`),
  KEY `dit_lSortIDX`    (`dit_lSortIDX`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Image/document drop-down list entries (tags)' AUTO_INCREMENT=1 ;

# [BREAK]

#
# Table structure for table 'doc_img_tag_ddl_multi'
#

DROP TABLE IF EXISTS doc_img_tag_ddl_multi;

# [BREAK]

CREATE TABLE IF NOT EXISTS doc_img_tag_ddl_multi (
  `dim_lKeyID`    int(11) NOT NULL AUTO_INCREMENT,
  `dim_lImgDocID` int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to docs_images',
  `dim_lDDLID`    int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to doc_img_tag_ddl',
  PRIMARY KEY         (`dim_lKeyID`),
  KEY `dim_lDDLID`    (`dim_lImgDocID`),
  KEY `pdm_lUTableID` (`dim_lDDLID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

# [BREAK]


DROP TABLE IF EXISTS `emr_measurements`;
# [BREAK]
CREATE TABLE IF NOT EXISTS `emr_measurements` (
  `meas_lKeyID`         int(11) NOT NULL AUTO_INCREMENT,
  `meas_lClientID`      int(11) NOT NULL DEFAULT '0',

  `meas_dteMeasurement` date  NOT NULL DEFAULT '0000-00-00',
  `meas_sngHeadCircCM`  float DEFAULT NULL,
  `meas_sngWeightKilos` float DEFAULT NULL,
  `meas_sngHeightCM`    float DEFAULT NULL,

  `meas_strNotes`       text,
  `meas_bRetired`       tinyint(1) NOT NULL DEFAULT '0',
  `meas_lOriginID`      int(11)    NOT NULL DEFAULT '0',
  `meas_lLastUpdateID`  int(11)    NOT NULL DEFAULT '0',
  `meas_dteOrigin`      datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
  `meas_dteLastUpdate`  timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`meas_lKeyID`),
  KEY `meas_lClientID`      (`meas_lClientID`),
  KEY `meas_dteMeasurement` (`meas_dteMeasurement`),
  KEY `meas_sngHeadCircCM`  (`meas_sngHeadCircCM`),
  KEY `meas_sngWeightKilos` (`meas_sngWeightKilos`),
  KEY `meas_sngHeightCM`    (`meas_sngHeightCM`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='health record: height/weight/ofc - metric units' AUTO_INCREMENT=1;

# [BREAK]


#
# TABLE STRUCTURE FOR: gifts
#

DROP TABLE IF EXISTS gifts;
# [BREAK]

CREATE TABLE `gifts` (
  `gi_lKeyID`        int(11) NOT NULL AUTO_INCREMENT,
  `gi_lForeignID`    int(11) NOT NULL COMMENT 'Foreign key to people_names table; may be person or biz',
  `gi_lSponsorID`    int(11) DEFAULT NULL COMMENT 'if sponsor payment, the sponsor ID',
  `gi_lCampID`       int(11) NOT NULL DEFAULT '0',
  `gi_curAmnt`       decimal(10,2) NOT NULL DEFAULT '0.00',
  `gi_lACOID`        int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to table admin_aco',
  `gi_dteDonation`   date NOT NULL DEFAULT '0000-00-00',
  `gi_lAttributedTo` int(11) DEFAULT NULL COMMENT 'foreign key to lists_generic',
  `gi_bGIK`          tinyint(1) NOT NULL DEFAULT '0',
  `gi_bHon`          tinyint(1) NOT NULL DEFAULT '0',
  `gi_bMem`          tinyint(1) NOT NULL DEFAULT '0',
  `gi_lGIK_ID`       int(11) DEFAULT NULL COMMENT 'foreign key to lists_generic',
  `gi_lDepositLogID` INT NULL DEFAULT NULL COMMENT 'Foreign key to the deposit log table',
  `gi_lPledgeID`     int(11) DEFAULT NULL COMMENT 'Foreign key to the pledge table',
--  `gi_lPledgeID`     INT NULL DEFAULT NULL COMMENT 'Foreign key to the pledge table',
  `gi_strImportID`   varchar(40) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL,
  `gi_strNotes`      text NOT NULL,
  `gi_strCheckNum`   varchar(255) NOT NULL DEFAULT '',
  `gi_lPaymentType`  int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to lists_generic',
  `gi_lMajorGiftCat` int(11) DEFAULT NULL COMMENT 'foreign key to lists_generic',
  `gi_bAck`          tinyint(1) NOT NULL DEFAULT '0',
  `gi_dteAck`        datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `gi_lAckByID`      int(11) DEFAULT NULL,
  `gi_bRetired`      tinyint(1) NOT NULL DEFAULT '0',
  `gi_lOriginID`     int(11) NOT NULL DEFAULT '0',
  `gi_lLastUpdateID` int(11) NOT NULL DEFAULT '0',
  `gi_dteOrigin`     datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `gi_dteLastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`gi_lKeyID`),
  KEY `gi_lPersonID`       (gi_lForeignID),
  KEY `gi_lDepositLogID`   (gi_lDepositLogID),
--  KEY `gi_lPledgeID`       (gi_lPledgeID),
  KEY `gi_lCampID`         (gi_lCampID),
  KEY `gi_curAmnt`         (gi_curAmnt),
  KEY `gi_dteDonation`     (gi_dteDonation),
  KEY `gi_bGIK`            (gi_bGIK),
  KEY `gi_bHon`            (gi_bHon),
  KEY `gi_bMem`            (gi_bMem),
  KEY `gi_bRetired`        (gi_bRetired),
  KEY `gi_lAttributedTo`   (gi_lAttributedTo),
  KEY `gi_enumCCType`      (gi_lPaymentType),
  KEY `gi_lSponsorID`      (gi_lSponsorID),
  KEY `gi_enumCurrency`    (gi_lACOID),
  KEY `gi_lPledgeID`       (`gi_lPledgeID`),
  FULLTEXT KEY gi_strNotes (gi_strNotes)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

# [BREAK]


#
# TABLE STRUCTURE FOR: gifts_accounts
#

DROP TABLE IF EXISTS gifts_accounts;
# [BREAK]

CREATE TABLE `gifts_accounts` (
  `ga_lKeyID`        int(11)     NOT NULL AUTO_INCREMENT,
  `ga_strAccount`    varchar(80) NOT NULL DEFAULT '',
  `ga_strNotes`      text        NOT NULL,
  `ga_bProtected`    tinyint(1)  NOT NULL DEFAULT '0' COMMENT 'If protected, do not delete',
  `ga_bSponsorship`  tinyint(1)  NOT NULL DEFAULT '0',
  `ga_bRetired`      tinyint(1)  NOT NULL DEFAULT '0',
  `ga_lOriginID`     int(11)     NOT NULL DEFAULT '0',
  `ga_lLastUpdate`   int(11)     NOT NULL DEFAULT '0',
  `ga_dteOrigin`     datetime    NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ga_dteLastUpdate` timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ga_lKeyID`),
  KEY `ga_strAccount` (`ga_strAccount`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]

INSERT INTO gifts_accounts (`ga_lKeyID`, `ga_strAccount`, `ga_strNotes`, `ga_bProtected`, `ga_bSponsorship`, `ga_bRetired`, `ga_lOriginID`, `ga_lLastUpdate`, `ga_dteOrigin`, `ga_dteLastUpdate`)
   VALUES (1, 'Undirected', 'Default Account', 1, 0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00');
# [BREAK]
INSERT INTO gifts_accounts (`ga_lKeyID`, `ga_strAccount`, `ga_strNotes`, `ga_bProtected`, `ga_bSponsorship`, `ga_bRetired`, `ga_lOriginID`, `ga_lLastUpdate`, `ga_dteOrigin`, `ga_dteLastUpdate`)
   VALUES (2, 'Sponsorship', '', 1, 1, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00');

# [BREAK]


#
# Table structure for table `gifts_auctions`
#

DROP TABLE IF EXISTS `gifts_auctions`;

# [BREAK]

CREATE TABLE IF NOT EXISTS `gifts_auctions` (
  `auc_lKeyID`            int(11) NOT NULL AUTO_INCREMENT,
  `auc_strAuctionName`    varchar(255) NOT NULL DEFAULT '',
  `auc_strDescription`    text NOT NULL,
  `auc_dteAuctionDate`    date NOT NULL DEFAULT '0000-00-00',
  `auc_dteAuctionEndDate` date DEFAULT NULL COMMENT 'reserved',
  `auc_lACOID`            int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to table admin_aco',
  `auc_lCampaignID`       int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to campaigns for winning bid gifts',
  `auc_lDefaultBidSheet`  int(11) DEFAULT NULL COMMENT 'Default bid sheet for packages in this auction',
  `auc_strLocation`       text         NOT NULL,
  `auc_strContact`        varchar(255) NOT NULL DEFAULT '',
  `auc_strPhone`          varchar(80)  NOT NULL DEFAULT '',
  `auc_strEmail`          varchar(200) NOT NULL DEFAULT '',
  `auc_bRetired`          tinyint(1)   NOT NULL DEFAULT '0',
  `auc_lOriginID`         int(11)      NOT NULL DEFAULT '0',
  `auc_lLastUpdateID`     int(11)      NOT NULL DEFAULT '0',
  `auc_dteOrigin`         datetime     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `auc_dteLastUpdate`     timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`auc_lKeyID`),
  KEY `auc_strAuctionName` (`auc_strAuctionName`),
  KEY `auc_dteAuctionDate` (`auc_dteAuctionDate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Master silent auction table';

# [BREAK]


#
# Table structure for table `gifts_auctions_bidsheets`
#

DROP TABLE IF EXISTS `gifts_auctions_bidsheets`;

# [BREAK]

CREATE TABLE IF NOT EXISTS `gifts_auctions_bidsheets` (
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Bid Sheet Templates';

# [BREAK]



#
# Table structure for table `gifts_auctions_items`
#

DROP TABLE IF EXISTS `gifts_auctions_items`;
# [BREAK]
CREATE TABLE IF NOT EXISTS `gifts_auctions_items` (
  `ait_lKeyID`           int(11) NOT NULL AUTO_INCREMENT,
  `ait_lPackageID`       int(11) DEFAULT NULL COMMENT 'Foreign key to table gifts_auctions_packages',
  `ait_strItemName`      varchar(255)  NOT NULL DEFAULT '',
  `ait_strDescription`   text          NOT NULL,
  `ait_strInternalNotes` text          NOT NULL,
  `ait_dteItemObtained`  date          NOT NULL DEFAULT '0000-00-00' COMMENT 'Date the auction item was obtained',
  `ait_lItemDonorID`     int(11)       NOT NULL DEFAULT '0' COMMENT 'FID to People Table - donor or item (not bidder)',
  `ait_strDonorAck`      varchar(80)   NOT NULL DEFAULT '' COMMENT '"Donated By" text for bid sheet',
  `ait_curEstAmnt`       decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Estimated value of item',
  `ait_curOutOfPocket`   decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Out-of-pocket expense',
  `ait_bRetired`         tinyint(1)    NOT NULL DEFAULT '0',
  `ait_lOriginID`        int(11)       NOT NULL DEFAULT '0',
  `ait_lLastUpdateID`    int(11)       NOT NULL DEFAULT '0',
  `ait_dteOrigin`        datetime      NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ait_dteLastUpdate`    timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ait_lKeyID`),
  KEY `ait_lPackageID`   (`ait_lPackageID`),
  KEY `ait_lItemDonorID` (`ait_lItemDonorID`),
  KEY `ait_strItemName`  (`ait_strItemName`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Individual auction items';

# [BREAK]


#
# Table structure for table `gifts_auctions_packages`
#

DROP TABLE IF EXISTS `gifts_auctions_packages`;

# [BREAK]

CREATE TABLE IF NOT EXISTS `gifts_auctions_packages` (
  `ap_lKeyID`           int(11)       NOT NULL AUTO_INCREMENT,
  `ap_lAuctionID`       int(11)       NOT NULL DEFAULT '0' COMMENT 'Foreign key to table gifts_auctions',
  `ap_strPackageName`   varchar(255)  NOT NULL DEFAULT '',
  `ap_curMinBidAmnt`    decimal(10,2) NOT NULL DEFAULT '0.00',
  `ap_curReserveAmnt`   decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Bid reserve',
  `ap_curMinBidInc`     decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Minimum bid increment',
  `ap_curBuyItNowAmnt`  decimal(10,2) DEFAULT NULL COMMENT 'For bidsheet - buy it now price',
  `ap_curWinBidAmnt`    decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Winning bid amount',
  `ap_strDescription`   text          NOT NULL,
  `ap_strInternalNotes` text          NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Packaged auction items';

# [BREAK]


#
# TABLE STRUCTURE FOR: gifts_campaigns
#

DROP TABLE IF EXISTS gifts_campaigns;
# [BREAK]

CREATE TABLE `gifts_campaigns` (
  `gc_lKeyID`                 int(11)     NOT NULL AUTO_INCREMENT,
  `gc_lAcctID`                int(11)     NOT NULL DEFAULT '0',
  `gc_strCampaign`            varchar(80) NOT NULL DEFAULT '',
  `gc_strNotes`               text        NOT NULL,
  `gc_lDefaultAckLetTemplate` int(11)     DEFAULT NULL,
  `gc_bProtected`             tinyint(1)  NOT NULL DEFAULT '0' COMMENT 'If protected, do not delete',
  `gc_bRetired`               tinyint(1)  NOT NULL DEFAULT '0',
  `gc_lOriginID`              int(11)     NOT NULL DEFAULT '0',
  `gc_lLastUpdate`            int(11)     NOT NULL DEFAULT '0',
  `gc_dteOrigin`              datetime    NOT NULL DEFAULT '0000-00-00 00:00:00',
  `gc_dteLastUpdate`          timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`gc_lKeyID`),
  KEY `gc_lAcctID`                (`gc_lAcctID`),
  KEY `gc_strCampaign`            (`gc_strCampaign`),
  KEY `gc_lDefaultAckLetTemplate` (`gc_lDefaultAckLetTemplate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]

INSERT INTO gifts_campaigns (`gc_lKeyID`, `gc_lAcctID`, `gc_strCampaign`, `gc_strNotes`, `gc_lDefaultAckLetTemplate`, `gc_bProtected`, `gc_bRetired`, `gc_lOriginID`, `gc_lLastUpdate`, `gc_dteOrigin`, `gc_dteLastUpdate`)
   VALUES (1, 1, 'Other/Unknown', '', NULL, 1, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00');
# [BREAK]
INSERT INTO gifts_campaigns (`gc_lKeyID`, `gc_lAcctID`, `gc_strCampaign`, `gc_strNotes`, `gc_lDefaultAckLetTemplate`, `gc_bProtected`, `gc_bRetired`, `gc_lOriginID`, `gc_lLastUpdate`, `gc_dteOrigin`, `gc_dteLastUpdate`)
   VALUES (2, 2, 'Sponsorship payment', '', NULL, 1, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00');
# [BREAK]


#
# TABLE STRUCTURE FOR: gifts_hon_mem_links
#

DROP TABLE IF EXISTS gifts_hon_mem_links;
# [BREAK]

CREATE TABLE `gifts_hon_mem_links` (
  `ghml_lKeyID`    int(11) NOT NULL AUTO_INCREMENT,
  `ghml_lGiftID`   int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to gifts',
  `ghml_lHonMemID` int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to lists_hon_mem',
  `ghml_strNote`   varchar(80) NOT NULL DEFAULT '',
  `ghml_bAck`      tinyint(1) NOT NULL DEFAULT '0',
  `ghml_dteAck`    datetime DEFAULT NULL,
  `ghml_lAckByID`  int(11) DEFAULT NULL,
  PRIMARY KEY (`ghml_lKeyID`),
  UNIQUE KEY `ghml_lGiftID_2` (`ghml_lGiftID`,`ghml_lHonMemID`),
  KEY `ghml_lGiftID`   (`ghml_lGiftID`),
  KEY `ghml_lHonMemID` (`ghml_lHonMemID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Links the gifts with hon/mem records';

# [BREAK]

#
# TABLE STRUCTURE FOR: gifts_pledges
#

DROP TABLE IF EXISTS gifts_pledges;
# [BREAK]

CREATE TABLE gifts_pledges (
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

# [BREAK]



#
# TABLE STRUCTURE FOR: gifts_pledge_schedule
#

DROP TABLE IF EXISTS gifts_pledge_schedule;
# [BREAK]

CREATE TABLE gifts_pledge_schedule (
  gps_lKeyID        int(11)       NOT NULL AUTO_INCREMENT,
  gps_lPledgeID     int(11)       NOT NULL COMMENT 'Foreign key to gifts_pledges',
  gps_lGiftID       int(11)       DEFAULT NULL COMMENT 'Foreign key to gifts',
  gps_dtePledge     date          NOT NULL DEFAULT '0000-00-00' COMMENT 'pledge due date',

  gps_bRetired      tinyint(1) NOT NULL DEFAULT '0',
  gps_lOriginID     int(11)    NOT NULL DEFAULT '0',
  gps_lLastUpdateID int(11)    NOT NULL DEFAULT '0',
  gps_dteOrigin     datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
  gps_dteLastUpdate timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (gps_lKeyID),
  KEY gps_lPledgeID       (gps_lPledgeID),
  KEY gps_lGiftID         (gps_lGiftID),
  KEY gps_dtePledge       (gps_dtePledge)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

# [BREAK]


#
# TABLE STRUCTURE FOR: groups_child
#

DROP TABLE IF EXISTS groups_child;
# [BREAK]

CREATE TABLE IF NOT EXISTS `groups_child` (
  `gc_lKeyID`       int(11) NOT NULL AUTO_INCREMENT,
  `gc_lGroupID`     int(11) NOT NULL DEFAULT '0',
  `gc_lForeignID`   int(11) NOT NULL DEFAULT '0',
  `gc_enumSubGroup` enum('customForm','personalizedTable','user','clientProgram','clientPrePostTest') DEFAULT NULL COMMENT 'Sub-group qualifier for certain groups (i.e. user perms)',
  `gc_dteAdded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`gc_lKeyID`),
  UNIQUE KEY `gc_lGroupID_2` (`gc_lGroupID`,`gc_lForeignID`,`gc_enumSubGroup`),
  KEY `gc_lGroupID`     (`gc_lGroupID`),
  KEY `gc_dteAdded`     (`gc_dteAdded`),
  KEY `gc_lForeignID`   (`gc_lForeignID`),
  KEY `gc_enumSubGroup` (`gc_enumSubGroup`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

# [BREAK]



#
# TABLE STRUCTURE FOR: groups_parent
#

DROP TABLE IF EXISTS groups_parent;
# [BREAK]

CREATE TABLE `groups_parent` (
  `gp_lKeyID`        int(11) NOT NULL AUTO_INCREMENT,
  `gp_strGroupName`  varchar(80) NOT NULL DEFAULT '',
  `gp_enumGroupType` enum('people', 'household', 'business', 'businessContact', 'volunteer',
                       'staff', 'sponsorship', 'gift', 'client', 'user',
                       'staffTSProject', 'staffTSLocation', 'grants', 'grantProvider') NOT NULL DEFAULT 'people',
  `gp_bTempGroup`    tinyint(1) NOT NULL DEFAULT '0',
  `gp_bGeneric1`     tinyint(1) NOT NULL DEFAULT '0'    COMMENT 'Boolean: extends the basic group info',
  `gp_lGeneric1`     int(11)    DEFAULT NULL            COMMENT 'Int: extends the basic group info',
  `gp_dteExpire`     date NOT NULL DEFAULT '2038-01-01' COMMENT 'group may be deleted after this date',
  `gp_strNotes`      text,
  `gp_lOriginID`     int(11) NOT NULL DEFAULT '0',
  `gp_lLastUpdateID` int(11) NOT NULL DEFAULT '0',
  `gp_dteOrigin`     datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `gp_dteLastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`gp_lKeyID`),
  KEY `gp_strGroupName`  (`gp_strGroupName`),
  KEY `gp_dteLastUpdate` (`gp_dteLastUpdate`),
  KEY `gp_dteExpire`     (`gp_dteExpire`),
  KEY `gp_enumGroupType` (`gp_enumGroupType`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]



#
# TABLE STRUCTURE FOR: import_ids
#

DROP TABLE IF EXISTS import_ids;
# [BREAK]

CREATE TABLE `import_ids` (
  `iid_lKeyID`     int(11) NOT NULL AUTO_INCREMENT,
  `iid_lImportID`  int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table import_log table',
  `iid_lForeignID` int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table people/biz/gift table',
  PRIMARY KEY (`iid_lKeyID`),
  KEY `iid_lImportID`  (`iid_lImportID`),
  KEY `iid_lForeignID` (`iid_lForeignID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

# [BREAK]


#
# TABLE STRUCTURE FOR: import_log
#

DROP TABLE IF EXISTS import_log;
# [BREAK]

CREATE TABLE `import_log` (
  `il_lKeyID`         int(11) NOT NULL AUTO_INCREMENT,
  `il_enumImportType` enum('people','business','gift','sponsorPayment','personalizedTable','client') DEFAULT NULL,
  `il_lUTableID`      int(11) DEFAULT NULL COMMENT 'Foreign key to uf_tables (for ptable imports only)',
  `il_bRetired`       tinyint(1) NOT NULL DEFAULT '0',
  `il_lOriginID`      int(11) NOT NULL DEFAULT '0',
  `il_dteOrigin`      datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`il_lKeyID`),
  KEY `il_enumImportType` (`il_enumImportType`),
  KEY `il_dteOrigin`      (`il_dteOrigin`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

# [BREAK]

-- ----------------------------------
-- Inventory categories
-- ----------------------------------
DROP TABLE IF EXISTS inv_cats;

# [BREAK]

CREATE TABLE inv_cats (
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Inventory Categories' AUTO_INCREMENT=1;

# [BREAK]

-- ----------------------------------
-- Inventory items
-- ----------------------------------
DROP TABLE IF EXISTS inv_items;

# [BREAK]

CREATE TABLE inv_items (
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Inventory Items' AUTO_INCREMENT=1;

# [BREAK]

-- ----------------------------------
-- check out / check in table
-- ----------------------------------
DROP TABLE IF EXISTS inv_cico;

# [BREAK]

CREATE TABLE inv_cico (
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Inventory Checkout/Checkin History' AUTO_INCREMENT=1;

# [BREAK]

-- ----------------------------------
-- Inventory history
-- ----------------------------------
DROP TABLE IF EXISTS inv_history;

# [BREAK]

CREATE TABLE inv_history (
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Inventory Item History' AUTO_INCREMENT=1;

# [BREAK]

#
# TABLE STRUCTURE FOR: lists_client_status_entries
#

DROP TABLE IF EXISTS lists_client_status_entries;
# [BREAK]

CREATE TABLE `lists_client_status_entries` (
  `cst_lKeyID`             int(11)     NOT NULL AUTO_INCREMENT,
  `cst_lClientStatusCatID` int(11)     NOT NULL DEFAULT '0' COMMENT 'foreign key to client_status_cats',
  `cst_strStatus`          varchar(80) NOT NULL,
  `cst_bAllowSponsorship`  tinyint(1)  NOT NULL DEFAULT '0',
  `cst_bShowInDir`         tinyint(1)  NOT NULL DEFAULT '0',
  `cst_bDefault`           tinyint(1)  NOT NULL DEFAULT '0',
  `cst_bRetired`           tinyint(1)  NOT NULL DEFAULT '0',
  `cst_lOriginID`          int(11)     NOT NULL DEFAULT '0',
  `cst_lLastUpdateID`      int(11)     NOT NULL DEFAULT '0',
  `cst_dteOrigin`          datetime    NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cst_dteLastUpdate`      timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cst_lKeyID`),
  KEY `cst_lSponCatID` (`cst_lClientStatusCatID`),
  KEY `cst_strStatus`  (`cst_strStatus`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]
INSERT INTO lists_client_status_entries (cst_lKeyID, cst_lClientStatusCatID, cst_strStatus, cst_bAllowSponsorship, cst_bShowInDir, cst_bDefault, cst_bRetired, cst_lOriginID, cst_lLastUpdateID, cst_dteOrigin, cst_dteLastUpdate) VALUES
(1, 1, 'Eligible', 1, 1, 1, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(2, 1, 'Not eligible / Show in Directory', 0, 1, 0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(3, 1, 'No longer enrolled in program', 0, 0, 0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00');
# [BREAK]


#
# TABLE STRUCTURE FOR: lists_client_vocab
#

DROP TABLE IF EXISTS lists_client_vocab;
# [BREAK]

CREATE TABLE `lists_client_vocab` (
  `cv_lKeyID`         int(11) NOT NULL AUTO_INCREMENT,
  `cv_strVocTitle`    varchar(250) NOT NULL DEFAULT '',
  `cv_strVocClientS`  varchar(40)  NOT NULL DEFAULT '',
  `cv_strVocClientP`  varchar(40)  NOT NULL DEFAULT '',
  `cv_strVocSponsorS` varchar(40)  NOT NULL DEFAULT '',
  `cv_strVocSponsorP` varchar(40)  NOT NULL DEFAULT '',
  `cv_strVocLocS`     varchar(40)  NOT NULL DEFAULT '',
  `cv_strVocLocP`     varchar(40)  NOT NULL DEFAULT '',
  `cv_strVocSubLocS`  varchar(40)  NOT NULL,
  `cv_strVocSubLocP`  varchar(40)  NOT NULL,
  `cv_bProtected`     tinyint(1)   NOT NULL DEFAULT '0',
  `cv_bRetired`       tinyint(1)   NOT NULL DEFAULT '0',
  `cv_lOriginID`      int(11)      NOT NULL DEFAULT '0',
  `cv_lLastUpdateID`  int(11)      NOT NULL DEFAULT '0',
  `cv_dteOrigin`      datetime     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `cv_dteLastUpdate`  timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cv_lKeyID`),
  KEY `cv_strVocTitle` (`cv_strVocTitle`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Client Vocabularies';
# [BREAK]

INSERT INTO lists_client_vocab (`cv_lKeyID`, `cv_strVocTitle`, `cv_strVocClientS`, `cv_strVocClientP`, `cv_strVocSponsorS`, `cv_strVocSponsorP`, `cv_strVocLocS`, `cv_strVocLocP`, `cv_strVocSubLocS`, `cv_strVocSubLocP`, `cv_bProtected`, `cv_bRetired`, `cv_lOriginID`, `cv_lLastUpdateID`, `cv_dteOrigin`, `cv_dteLastUpdate`) VALUES (1, 'Default', 'Client', 'Clients', 'Sponsor', 'Sponsors', 'Home', 'Homes', '', '', 1, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00');
# [BREAK]


#
# TABLE STRUCTURE FOR: lists_generic
#

DROP TABLE IF EXISTS lists_generic;
# [BREAK]


CREATE TABLE IF NOT EXISTS lists_generic (
  lgen_lKeyID        int(11) NOT NULL AUTO_INCREMENT,
  lgen_enumListType  enum('bizCat','bizContactRel','inKind','attrib','campaignExpense',
                          'majorGiftCats','giftPayType','volJobCat','sponTermCat',
                          'volSkills','volActivities','prePostTestCat',
                          'inventoryCat', 'volShiftJobCodes') DEFAULT NULL,
  lgen_strListItem   varchar(255) NOT NULL DEFAULT '',
  lgen_lSortIDX      int(11)      NOT NULL DEFAULT '0' COMMENT 'reserved',
  lgen_bRetired      tinyint(1)   NOT NULL DEFAULT '0',
  lgen_lOriginID     int(11)      NOT NULL DEFAULT '0',
  lgen_lLastUpdateID int(11)      NOT NULL DEFAULT '0',
  lgen_dteOrigin     datetime     NOT NULL DEFAULT '0000-00-00 00:00:00',
  lgen_dteLastUpdate timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (lgen_lKeyID),
  KEY lgen_enumListType (lgen_enumListType),
  KEY lgen_lSortIDX     (lgen_lSortIDX),
  KEY lgen_strListItem  (lgen_strListItem)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

# [BREAK]

INSERT INTO lists_generic (`lgen_lKeyID`, `lgen_enumListType`, `lgen_strListItem`, `lgen_lSortIDX`, `lgen_bRetired`, `lgen_lOriginID`, `lgen_lLastUpdateID`, `lgen_dteOrigin`, `lgen_dteLastUpdate`)
VALUES
(1,   'attrib',          '(other)',                    0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(2,   'attrib',          '(unknown)',                  0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(3,   'bizCat',          '(other/unknown)',            0, 1, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(4,   'bizCat',          'Media',                      0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(5,   'bizCat',          'Office Supplies',            0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(6,   'bizCat',          'Non-profit',                 0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(7,   'bizCat',          'NGO',                        0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(8,   'bizCat',          'Landscaping',                0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(9,   'bizCat',          'Web Design',                 0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(10,  'bizCat',          'Computers',                  0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(21,  'bizContactRel',   '(other/unknown)',            0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(22,  'bizContactRel',   'Employee',                   0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(23,  'bizContactRel',   'Employer',                   0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(24,  'bizContactRel',   'Intern',                     0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(25,  'bizContactRel',   'Owner',                      0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(26,  'bizContactRel',   'Customer',                   0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(31,  'campaignExpense', '(other/unknown)',            0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(32,  'campaignExpense', 'Equipment rental',           0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(33,  'campaignExpense', 'Food/beverage',              0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(34,  'campaignExpense', 'Transportation',             0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(41,  'giftPayType',     '(other/unknown)',            0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(42,  'giftPayType',     'Cash',                       0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(43,  'giftPayType',     'Check',                      0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(44,  'giftPayType',     'Credit Card - Master Card',  0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(45,  'giftPayType',     'Credit Card - Visa',         0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(46,  'giftPayType',     'Credit Card - Discover',     0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(51,  'inKind',          '(other/unknown)',            0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(52,  'inKind',          'Computers/software',         0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(53,  'inKind',          'Landscaping',                0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(54,  'inKind',          'Office Supplies',            0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(61,  'majorGiftCats',   '(other/unknown)',            0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(62,  'majorGiftCats',   'Big Donors',                 0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(63,  'majorGiftCats',   'General Donations',          0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(64,  'majorGiftCats',   'Grants',                     0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(71,  'sponTermCat',     'Client no longer available', 0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(72,  'sponTermCat',     '(other/unknown)',            0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(81,  'volJobCat',       'Computer services',          0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(82,  'volJobCat',       'Intern',                     0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(83,  'volJobCat',       'Marketing',                  0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(84,  'volJobCat',       'Office support',             0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(100, 'volSkills',       'Event management',           0, 0, 1, 1, '2012-02-27 05:11:56', '2015-01-30 00:00:00'),
(101, 'volSkills',       'Office skills',              0, 0, 1, 1, '2012-02-27 05:12:08', '2015-01-30 00:00:00'),
(102, 'volSkills',       'HTML',                       0, 0, 1, 1, '2012-02-27 05:12:19', '2015-01-30 00:00:00'),
(103, 'volSkills',       'Landscaping',                0, 0, 1, 1, '2012-02-27 05:13:15', '2015-01-30 00:00:00'),
(201, 'attrib',          'Staff Member',               0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(202, 'attrib',          'Sally, The Intern',          0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(300, 'bizCat',          '(other/unknown)',            0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(301, 'volActivities',   '(other/unknown)',            0, 0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00')
;
# [BREAK]


#
# TABLE STRUCTURE FOR: lists_hon_mem
#

DROP TABLE IF EXISTS lists_hon_mem;
# [BREAK]

CREATE TABLE `lists_hon_mem` (
  `ghm_lKeyID`         int(11) NOT NULL AUTO_INCREMENT,
  `ghm_lFID`           int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to people_names',
  `ghm_lMailContactID` int(11) DEFAULT NULL COMMENT 'foreign key to people_names',
  `ghm_bHon`           tinyint(1) NOT NULL DEFAULT '0',
  `ghm_bHidden`        tinyint(1) NOT NULL DEFAULT '0',
  `ghm_bRetired`       tinyint(1) NOT NULL DEFAULT '0',
  `ghm_dteOrigin`      datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ghm_lOriginID`      int(11)    NOT NULL DEFAULT '0',
  `ghm_lLastUpdateID`  int(11)    NOT NULL DEFAULT '0',
  `ghm_dteLastUpdate`  timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ghm_lKeyID`),
  KEY `ghm_lFID`           (`ghm_lFID`),
  KEY `ghm_lMailContactID` (`ghm_lMailContactID`),
  KEY `ghm_dteOrigin`      (`ghm_dteOrigin`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]


#
# TABLE STRUCTURE FOR: lists_people_relationships
#

DROP TABLE IF EXISTS lists_people_relationships;
# [BREAK]

CREATE TABLE `lists_people_relationships` (
  `lpr_lKeyID`           int(11) NOT NULL AUTO_INCREMENT,
  `lpr_enumCategory`     enum('Family','Community','Business','Other') NOT NULL DEFAULT 'Other',
  `lpr_bSpouse`          tinyint(1)  NOT NULL DEFAULT '0',
  `lpr_strRelationship`  varchar(40) NOT NULL DEFAULT '',
  `lpr_bRetired`         tinyint(1)  NOT NULL DEFAULT '0',
  `lpr_lOriginID`        int(11)     NOT NULL DEFAULT '0',
  `lpr_lLastUpdateID`    int(11)     NOT NULL DEFAULT '0',
  `lpr_dteOrigin`        datetime    NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lpr_dteLastUpdate`    timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`lpr_lKeyID`),
  KEY `lpr_strCategory` (`lpr_strRelationship`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='list of people relationships';
# [BREAK]

INSERT INTO lists_people_relationships (`lpr_lKeyID`, `lpr_enumCategory`, `lpr_bSpouse`, `lpr_strRelationship`, `lpr_bRetired`, `lpr_lOriginID`, `lpr_lLastUpdateID`, `lpr_dteOrigin`, `lpr_dteLastUpdate`)
VALUES
(1,  'Family',    0, 'Aunty',                1, 1, 1, '2015-01-30 00:00:00', '2012-04-25 00:00:00'),
(2,  'Family',    0, 'Uncle',                0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(3,  'Family',    0, 'Niece',                0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(4,  'Family',    0, 'Nephew',               0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(5,  'Family',    0, 'Cousin',               0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(6,  'Family',    0, 'Brother',              0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(7,  'Family',    0, 'Sister',               0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(8,  'Family',    0, 'Son',                  0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(9,  'Family',    0, 'Son-In-Law',           0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(10, 'Family',    0, 'Daughter',             0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(11, 'Family',    0, 'Daughter-In-Law',      0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(12, 'Family',    0, 'Father',               0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(13, 'Family',    0, 'Mother',               0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(14, 'Family',    0, 'Grandmother',          0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(15, 'Family',    0, 'Grandfather',          0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(16, 'Family',    0, 'Step-Mother',          0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(17, 'Family',    0, 'Step-Father',          0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(18, 'Family',    0, 'Grandson',             0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(19, 'Family',    0, 'Granddaughter',        0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(20, 'Family',    0, '(other relative)',     0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(21, 'Family',    0, 'Sister-In-Law',        0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(22, 'Family',    0, 'Brother-In-Law',       0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(23, 'Family',    0, 'Mother-In-Law',        0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(24, 'Family',    0, 'Father-In-Law',        0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(25, 'Family',    0, 'Step-Son',             0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(26, 'Family',    0, 'Step-Daughter',        0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(27, 'Family',    0, 'Step-Brother',         0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(28, 'Family',    0, 'Step-Sister',          0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(29, 'Family',    0, 'Significant Other',    0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(30, 'Family',    1, 'Domestic Partner',     0, 1, 1, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(31, 'Family',    0, 'Boyfriend',            0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(32, 'Family',    0, 'Girlfriend',           0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(33, 'Family',    1, 'Husband',              0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(34, 'Family',    1, 'Wife',                 0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(35, 'Family',    0, 'Ex-Husband',           1, 0, 1, '2015-01-30 00:00:00', '2012-04-25 00:00:00'),
(36, 'Family',    0, 'Ex-Wife',              1, 0, 1, '2015-01-30 00:00:00', '2012-04-25 00:00:00'),
(37, 'Family',    0, 'Dependant',            0, 0, 0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(38, 'Family',    0, 'Friend of the Family', 1, 7, 7, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(39, 'Community', 0, '(other community relationship)', 0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(40, 'Community', 0, 'Classmate',                      0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(41, 'Community', 0, 'Volunteer',                      0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(42, 'Community', 0, 'Teacher',                        0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(43, 'Community', 0, 'Student',                        0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(44, 'Community', 0, 'Pastor',                         0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(45, 'Community', 0, 'Parishioner',                    0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(46, 'Community', 0, 'Friend',                         0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(47, 'Community', 0, 'Associate',                      0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(48, 'Community', 0, 'Colleague',                      0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(49, 'Business',  0, 'Employee',                       0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(50, 'Business',  0, 'Employee\'s Spouse',             0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(51, 'Business',  0, 'Employer',                       0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(52, 'Business',  0, 'Employer\'s Spouse',             0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(53, 'Business',  0, 'Partner',                        0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(54, 'Business',  0, 'Business Associate',             0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(55, 'Business',  0, 'Business Colleague',             0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(56, 'Business',  0, 'Client',                         0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(57, 'Business',  0, 'Customer',                       0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(58, 'Business',  0, 'Business Service Provider',      0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(59, 'Business',  0, 'Business Service Consumer',      0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(60, 'Business',  0, '(other business relationship)',  0,  0,  0, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(61, 'Business',  0, 'Assistant',                      0, 62, 62, '2015-01-30 00:00:00', '2015-01-30 00:00:00'),
(62, 'Family',    0, 'Aunt',                           0,  1,  1, '2015-01-30 00:00:00', '2015-01-30 00:00:00');

# [BREAK]

#
# TABLE STRUCTURE FOR: lists_sponsorship_programs
#

DROP TABLE IF EXISTS lists_sponsorship_programs;
# [BREAK]

CREATE TABLE `lists_sponsorship_programs` (
  `sc_lKeyID`              int(11)       NOT NULL AUTO_INCREMENT,
  `sc_bDefault`            tinyint(1)    NOT NULL DEFAULT '0',
  `sc_strProgram`          varchar(80)   NOT NULL DEFAULT '' COMMENT 'Sponsorship program name',
  `sc_strNotes`            text          NOT NULL,
  `sc_curDefMonthlyCommit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `sc_lACO`                int(11)       NOT NULL COMMENT 'foreign key to admin_aco',
  `sc_bRetired`            tinyint(1)    NOT NULL DEFAULT '0',
  `sc_lOriginID`           int(11)       NOT NULL DEFAULT '0',
  `sc_lLastUpdateID`       int(11)       NOT NULL DEFAULT '0',
  `sc_dteOrigin`           datetime      NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sc_dteLastUpdate`       timestamp     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sc_lKeyID`),
  KEY `sc_strCat` (`sc_strProgram`),
  KEY `sc_lACO`   (`sc_lACO`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Sponsorship Categories and Vocabularies';
# [BREAK]

INSERT INTO lists_sponsorship_programs (`sc_lKeyID`, `sc_bDefault`, `sc_strProgram`, `sc_strNotes`, `sc_curDefMonthlyCommit`, `sc_lACO`, `sc_bRetired`, `sc_lOriginID`, `sc_lLastUpdateID`, `sc_dteOrigin`, `sc_dteLastUpdate`) VALUES (1, 0, 'Basic sponsorship', '', '10.00', 1, 0, 1, 1, '2012-08-19 15:57:07', '2012-08-19 15:57:07');
# [BREAK]







--
-- Table structure for table `lists_tz`
--

DROP TABLE IF EXISTS `lists_tz`;
# [BREAK]


CREATE TABLE `lists_tz` (
  `tz_lKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `tz_strTimeZone` varchar(120) NOT NULL DEFAULT '',
  `tz_lTZ_Const` int(11) NOT NULL,
  `tz_bTopList` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tz_lKeyID`),
  KEY `tz_strTimeZone` (`tz_strTimeZone`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='php time zone constants via http://pecl.php.net/get/timezonedb';
# [BREAK]


INSERT INTO `lists_tz` (`tz_lKeyID`, `tz_strTimeZone`, `tz_lTZ_Const`, `tz_bTopList`) VALUES
(1,  'US/Eastern',                         255194, 1),  (2, 'US/Central',                          253903, 1),
(3,  'US/Mountain',                        258908, 1),  (4, 'US/Pacific',                          259797, 1),
(5,  'US/Alaska',                          252018, 1),  (6, 'US/Hawaii',                           257091, 1),
(7,  'Canada/Atlantic',                    156029, 1),  (8,  'Canada/Central',                     157285, 1),
(9,  'Canada/East-Saskatchewan',           159599, 1),  (10, 'Canada/Eastern',                     158335, 1),
(11, 'Canada/Mountain',                    159992, 1),  (12, 'Canada/Newfoundland',                160878, 1),
(13, 'Canada/Pacific',                     162201, 1),  (14, 'Canada/Saskatchewan',                163250, 1),
(15, 'Canada/Yukon',                       163643, 1),  (16, 'Africa/Abidjan',                          0, 0),
(17, 'Africa/Accra',                           85, 0),  (18, 'Africa/Addis_Ababa',                    413, 0),
(19, 'Africa/Algiers',                        540, 0),  (20, 'Africa/Asmara',                         839, 0),
(21, 'Africa/Asmera',                         966, 0),  (22, 'Africa/Bamako',                        1093, 0),
(23, 'Africa/Bangui',                        1178, 0),  (24, 'Africa/Banjul',                        1263, 0),
(25, 'Africa/Bissau',                        1348, 0),  (26, 'Africa/Blantyre',                      1450, 0),
(27, 'Africa/Brazzaville',                   1535, 0),  (28, 'Africa/Bujumbura',                     1620, 0),
(29, 'Africa/Cairo',                         1705, 0),  (30, 'Africa/Casablanca',                    2704, 0),
(31, 'Africa/Ceuta',                         3314, 0),  (32, 'Africa/Conakry',                       4089, 0),
(33, 'Africa/Dakar',                         4174, 0),  (34, 'Africa/Dar_es_Salaam',                 4259, 0),
(35, 'Africa/Djibouti',                      4386, 0),  (36, 'Africa/Douala',                        4513, 0),
(37, 'Africa/El_Aaiun',                      4598, 0),  (38, 'Africa/Freetown',                      5153, 0),
(39, 'Africa/Gaborone',                      5238, 0),  (40, 'Africa/Harare',                        5323, 0),
(41, 'Africa/Johannesburg',                  5408, 0),  (42, 'Africa/Juba',                          5518, 0),
(43, 'Africa/Kampala',                       5793, 0),  (44, 'Africa/Khartoum',                      5920, 0),
(45, 'Africa/Kigali',                        6195, 0),  (46, 'Africa/Kinshasa',                      6280, 0),
(47, 'Africa/Lagos',                         6388, 0),  (48, 'Africa/Libreville',                    6473, 0),
(49, 'Africa/Lome',                          6558, 0),  (50, 'Africa/Luanda',                        6643, 0),
(51, 'Africa/Lubumbashi',                    6728, 0),  (52, 'Africa/Lusaka',                        6836, 0),
(53, 'Africa/Malabo',                        6921, 0),  (54, 'Africa/Maputo',                        7006, 0),
(55, 'Africa/Maseru',                        7091, 0),  (56, 'Africa/Mbabane',                       7201, 0),
(57, 'Africa/Mogadishu',                     7311, 0),  (58, 'Africa/Monrovia',                      7438, 0),
(59, 'Africa/Nairobi',                       7540, 0),  (60, 'Africa/Ndjamena',                      7667, 0),
(61, 'Africa/Niamey',                        7775, 0),  (62, 'Africa/Nouakchott',                    7860, 0),
(63, 'Africa/Ouagadougou',                   7945, 0),  (64, 'Africa/Porto-Novo',                    8030, 0),
(65, 'Africa/Sao_Tome',                      8115, 0),  (66, 'Africa/Timbuktu',                      8200, 0),
(67, 'Africa/Tripoli',                       8285, 0),  (68, 'Africa/Tunis',                         8550, 0),
(69, 'Africa/Windhoek',                      8824, 0),  (70, 'America/Adak',                         9407, 0),
(71, 'America/Anchorage',                   10293, 0),  (72, 'America/Anguilla',                    11177, 0),
(73, 'America/Antigua',                     11262, 0),  (74, 'America/Araguaina',                   11347, 0),
(75, 'America/Argentina/Buenos_Aires',      11704, 0),  (76, 'America/Argentina/Catamarca',         12134, 0),
(77, 'America/Argentina/ComodRivadavia',    12583, 0),  (78, 'America/Argentina/Cordoba',           13005, 0),
(79, 'America/Argentina/Jujuy',             13474, 0),  (80, 'America/Argentina/La_Rioja',          13910, 0),
(81, 'America/Argentina/Mendoza',           14350, 0),  (82, 'America/Argentina/Rio_Gallegos',      14798, 0),
(83, 'America/Argentina/Salta',             15235, 0),  (84, 'America/Argentina/San_Juan',          15663, 0),
(85, 'America/Argentina/San_Luis',          16103, 0),  (86, 'America/Argentina/Tucuman',           16557, 0),
(87, 'America/Argentina/Ushuaia',           17001, 0),  (88, 'America/Aruba',                       17444, 0),
(89, 'America/Asuncion',                    17546, 0),  (90, 'America/Atikokan',                    18287, 0),
(91, 'America/Atka',                        18501, 0),  (92, 'America/Bahia',                       19371, 0),
(93, 'America/Bahia_Banderas',              19774, 0),  (94, 'America/Barbados',                    20407, 0),
(95, 'America/Belem',                       20561, 0),  (96, 'America/Belize',                      20812, 0),
(97, 'America/Blanc-Sablon',                21192, 0),  (98, 'America/Boa_Vista',                   21372, 0),
(99, 'America/Bogota',                      21637, 0),  (100, 'America/Boise',                      21745, 0),
(101, 'America/Buenos_Aires',               22664, 0),  (102, 'America/Cambridge_Bay',              23073, 0),
(103, 'America/Campo_Grande',               23881, 0),  (104, 'America/Cancun',                     24632, 0),
(105, 'America/Caracas',                    24994, 0),  (106, 'America/Catamarca',                  25097, 0),
(107, 'America/Cayenne',                    25519, 0),  (108, 'America/Cayman',                     25617, 0),
(109, 'America/Chicago',                    25702, 0),  (110, 'America/Chihuahua',                  27005, 0),
(111, 'America/Coral_Harbour',              27624, 0),  (112, 'America/Cordoba',                    27770, 0),
(113, 'America/Costa_Rica',                 28192, 0),  (114, 'America/Creston',                    28330, 0),
(115, 'America/Cuiaba',                     28470, 0),  (116, 'America/Curacao',                    29204, 0),
(117, 'America/Danmarkshavn',               29306, 0),  (118, 'America/Dawson',                     29630, 0),
(119, 'America/Dawson_Creek',               30427, 0),  (120, 'America/Denver',                     30901, 0),
(121, 'America/Detroit',                    31803, 0),  (122, 'America/Dominica',                   32666, 0),
(123, 'America/Edmonton',                   32751, 0),  (124, 'America/Eirunepe',                   33703, 0),
(125, 'America/El_Salvador',                33983, 0),  (126, 'America/Ensenada',                   34100, 0),
(127, 'America/Fort_Wayne',                 35291, 0),  (128, 'America/Fortaleza',                  34973, 0),
(129, 'America/Glace_Bay',                  35909, 0),  (130, 'America/Godthab',                    36796, 0),
(131, 'America/Goose_Bay',                  37504, 0),  (132, 'America/Grand_Turk',                 38717, 0),
(133, 'America/Grenada',                    39196, 0),  (134, 'America/Guadeloupe',                 39281, 0),
(135, 'America/Guatemala',                  39366, 0),  (136, 'America/Guayaquil',                  39503, 0),
(137, 'America/Guyana',                     39596, 0),  (138, 'America/Halifax',                    39725, 0),
(139, 'America/Havana',                     41027, 0),  (140, 'America/Hermosillo',                 41910, 0),
(141, 'America/Indiana/Indianapolis',       42132, 0),  (142, 'America/Indiana/Knox',               42789, 0),
(143, 'America/Indiana/Marengo',            43708, 0),  (144, 'America/Indiana/Petersburg',         44386, 0),
(145, 'America/Indiana/Tell_City',          45743, 0),  (146, 'America/Indiana/Vevay',              46408, 0),
(147, 'America/Indiana/Vincennes',          46979, 0),  (148, 'America/Indiana/Winamac',            47671, 0),
(149, 'America/Indianapolis',               45125, 0),  (150, 'America/Inuvik',                     48368, 0),
(151, 'America/Iqaluit',                    49127, 0),  (152, 'America/Jamaica',                    49929, 0),
(153, 'America/Jujuy',                      50126, 0),  (154, 'America/Juneau',                     50552, 0),
(155, 'America/Kentucky/Louisville',        51446, 0),  (156, 'America/Kentucky/Monticello',        52500, 0),
(157, 'America/Knox_IN',                    53401, 0),  (158, 'America/Kralendijk',                 54282, 0),
(159, 'America/La_Paz',                     54384, 0),  (160, 'America/Lima',                       54487, 0),
(161, 'America/Los_Angeles',                54655, 0),  (162, 'America/Louisville',                 55696, 0),
(163, 'America/Lower_Princes',              56709, 0),  (164, 'America/Maceio',                     56811, 0),
(165, 'America/Managua',                    57125, 0),  (166, 'America/Manaus',                     57304, 0),
(167, 'America/Marigot',                    57562, 0),  (168, 'America/Martinique',                 57647, 0),
(169, 'America/Matamoros',                  57755, 0),  (170, 'America/Mazatlan',                   58356, 0),
(171, 'America/Mendoza',                    58977, 0),  (172, 'America/Menominee',                  59413, 0),
(173, 'America/Merida',                     60310, 0),  (174, 'America/Metlakatla',                 60881, 0),
(175, 'America/Mexico_City',                61196, 0),  (176, 'America/Miquelon',                   61831, 0),
(177, 'America/Moncton',                    62457, 0),  (178, 'America/Monterrey',                  63632, 0),
(179, 'America/Montevideo',                 64243, 0),  (180, 'America/Montreal',                   65029, 0),
(181, 'America/Montserrat',                 66293, 0),  (182, 'America/Nassau',                     66378, 0),
(183, 'America/New_York',                   67215, 0),  (184, 'America/Nipigon',                    68506, 0),
(185, 'America/Nome',                       69355, 0),  (186, 'America/Noronha',                    70249, 0),
(187, 'America/North_Dakota/Beulah',        70553, 0),  (188, 'America/North_Dakota/Center',        71469, 0),
(189, 'America/North_Dakota/New_Salem',     72385, 0),  (190, 'America/Ojinaga',                    73322, 0),
(191, 'America/Panama',                     73931, 0),  (192, 'America/Pangnirtung',                74016, 0),
(193, 'America/Paramaribo',                 74838, 0),  (194, 'America/Phoenix',                    74984, 0),
(195, 'America/Port-au-Prince',             75174, 0),  (196, 'America/Port_of_Spain',              75978, 0),
(197, 'America/Porto_Acre',                 75718, 0),  (198, 'America/Porto_Velho',                76063, 0),
(199, 'America/Puerto_Rico',                76309, 0),  (200, 'America/Rainy_River',                76416, 0),
(201, 'America/Rankin_Inlet',               77240, 0),  (202, 'America/Recife',                     77982, 0),
(203, 'America/Regina',                     78280, 0),  (204, 'America/Resolute',                   78726, 0),
(205, 'America/Rio_Branco',                 79470, 0),  (206, 'America/Rosario',                    79734, 0),
(207, 'America/Santa_Isabel',               80156, 0),  (208, 'America/Santarem',                   81087, 0),
(209, 'America/Santiago',                   81348, 0),  (210, 'America/Santo_Domingo',              82068, 0),
(211, 'America/Sao_Paulo',                  82266, 0),  (212, 'America/Scoresbysund',               83049, 0),
(213, 'America/Shiprock',                   83799, 0),  (214, 'America/Sitka',                      84688, 0),
(215, 'America/St_Barthelemy',              85592, 0),  (216, 'America/St_Johns',                   85677, 0),
(217, 'America/St_Kitts',                   87040, 0),  (218, 'America/St_Lucia',                   87125, 0),
(219, 'America/St_Thomas',                  87210, 0),  (220, 'America/St_Vincent',                 87295, 0),
(221, 'America/Swift_Current',              87380, 0),  (222, 'America/Tegucigalpa',                87669, 0),
(223, 'America/Thule',                      87796, 0),  (224, 'America/Thunder_Bay',                88379, 0),
(225, 'America/Tijuana',                    89220, 0),  (226, 'America/Toronto',                    90141, 0),
(227, 'America/Tortola',                    91453, 0),  (228, 'America/Vancouver',                  91538, 0),
(229, 'America/Virgin',                     92623, 0),  (230, 'America/Whitehorse',                 92708, 0),
(231, 'America/Winnipeg',                   93505, 0),  (232, 'America/Yakutat',                    94593, 0),
(233, 'America/Yellowknife',                95468, 0),  (234, 'Antarctica/Casey',                   96252, 0),
(235, 'Antarctica/Davis',                   96410, 0),  (236, 'Antarctica/DumontDUrville',          96571, 0),
(237, 'Antarctica/Macquarie',               96716, 0),  (238, 'Antarctica/Mawson',                  97305, 0),
(239, 'Antarctica/McMurdo',                 97429, 0),  (240, 'Antarctica/Palmer',                  98368, 0),
(241, 'Antarctica/Rothera',                 98947, 0),  (242, 'Antarctica/South_Pole',              99065, 0),
(243, 'Antarctica/Syowa',                   99959, 0),  (244, 'Antarctica/Troll',                  100069, 0),
(245, 'Antarctica/Vostok',                 100535, 0),  (246, 'Arctic/Longyearbyen',               100648, 0),
(247, 'Asia/Aden',                         101466, 0),  (248, 'Asia/Almaty',                       101551, 0),
(249, 'Asia/Amman',                        101934, 0),  (250, 'Asia/Anadyr',                       102628, 0),
(251, 'Asia/Aqtau',                        103142, 0),  (252, 'Asia/Aqtobe',                       103653, 0),
(253, 'Asia/Ashgabat',                     104093, 0),  (254, 'Asia/Ashkhabad',                    104378, 0),
(255, 'Asia/Baghdad',                      104663, 0),  (256, 'Asia/Bahrain',                      105036, 0),
(257, 'Asia/Baku',                         105138, 0),  (258, 'Asia/Bangkok',                      105882, 0),
(259, 'Asia/Beirut',                       105967, 0),  (260, 'Asia/Bishkek',                      106748, 0),
(261, 'Asia/Brunei',                       107176, 0),  (262, 'Asia/Calcutta',                     107274, 0),
(263, 'Asia/Chita',                        107395, 0),  (264, 'Asia/Choibalsan',                   107928, 0),
(265, 'Asia/Chongqing',                    108543, 0),  (266, 'Asia/Chungking',                    108703, 0),
(267, 'Asia/Colombo',                      108863, 0),  (268, 'Asia/Dacca',                        109019, 0),
(269, 'Asia/Damascus',                     109185, 0),  (270, 'Asia/Dhaka',                        110033, 0),
(271, 'Asia/Dili',                         110199, 0),  (272, 'Asia/Dubai',                        110337, 0),
(273, 'Asia/Dushanbe',                     110422, 0),  (274, 'Asia/Gaza',                         110681, 0),
(275, 'Asia/Harbin',                       111532, 0),  (276, 'Asia/Hebron',                       111692, 0),

(277, 'Asia/Ho_Chi_Minh', 112552, 0),      (278, 'Asia/Hong_Kong', 112714, 0),      (279, 'Asia/Hovd', 113164, 0),
(280, 'Asia/Irkutsk', 113770, 0),          (281, 'Asia/Istanbul', 114261, 0),       (282, 'Asia/Jakarta', 115266, 0),
(283, 'Asia/Jayapura', 115436, 0),         (284, 'Asia/Jerusalem', 115593, 0),      (285, 'Asia/Kabul', 116408, 0),
(286, 'Asia/Kamchatka', 116489, 0),        (287, 'Asia/Karachi', 116994, 0),        (288, 'Asia/Kashgar', 117175, 0),
(289, 'Asia/Kathmandu', 117260, 0),        (290, 'Asia/Katmandu', 117362, 0),       (291, 'Asia/Khandyga', 117464, 0),
(292, 'Asia/Kolkata', 118018, 0),          (293, 'Asia/Krasnoyarsk', 118139, 0),    (294, 'Asia/Kuala_Lumpur', 118632, 0),
(295, 'Asia/Kuching', 118821, 0),          (296, 'Asia/Kuwait', 119059, 0),         (297, 'Asia/Macao', 119144, 0),
(298, 'Asia/Macau', 119459, 0),            (299, 'Asia/Magadan', 119774, 0),        (300, 'Asia/Makassar', 120290, 0),
(301, 'Asia/Manila', 120487, 0),           (302, 'Asia/Muscat', 120620, 0),         (303, 'Asia/Nicosia', 120705, 0),
(304, 'Asia/Novokuznetsk', 121449, 0),     (305, 'Asia/Novosibirsk', 121993, 0),    (306, 'Asia/Omsk', 122489, 0),
(307, 'Asia/Oral', 122981, 0),             (308, 'Asia/Phnom_Penh', 123445, 0),     (309, 'Asia/Pontianak', 123530, 0),
(310, 'Asia/Pyongyang', 123724, 0),        (311, 'Asia/Qatar', 123857, 0),          (312, 'Asia/Qyzylorda', 123959, 0),
(313, 'Asia/Rangoon', 124429, 0),          (314, 'Asia/Riyadh', 124549, 0),         (315, 'Asia/Saigon', 124634, 0),
(316, 'Asia/Sakhalin', 124796, 0),         (317, 'Asia/Samarkand', 125305, 0),      (318, 'Asia/Seoul', 125615, 0),
(319, 'Asia/Shanghai', 125858, 0),         (320, 'Asia/Singapore', 126030, 0),      (321, 'Asia/Srednekolymsk', 126213, 0),
(322, 'Asia/Taipei', 126725, 0),           (323, 'Asia/Tashkent', 127030, 0),       (324, 'Asia/Tbilisi', 127335, 0),
(325, 'Asia/Tehran', 127777, 0),           (326, 'Asia/Tel_Aviv', 128399, 0),       (327, 'Asia/Thimbu', 129214, 0),
(328, 'Asia/Thimphu', 129316, 0),          (329, 'Asia/Tokyo', 129418, 0),          (330, 'Asia/Ujung_Pandang', 129556, 0),
(331, 'Asia/Ulaanbaatar', 129681, 0),      (332, 'Asia/Ulan_Bator', 130258, 0),     (333, 'Asia/Urumqi', 130821, 0),
(334, 'Asia/Ust-Nera', 130919, 0),         (335, 'Asia/Vientiane', 131449, 0),      (336, 'Asia/Vladivostok', 131534, 0),
(337, 'Asia/Yakutsk', 132024, 0),          (338, 'Asia/Yekaterinburg', 132514, 0),  (339, 'Asia/Yerevan', 133059, 0),
(340, 'Atlantic/Azores', 133571, 0),       (341, 'Atlantic/Bermuda', 134854, 0),    (342, 'Atlantic/Canary', 135591, 0),
(343, 'Atlantic/Cape_Verde', 136317, 0),   (344, 'Atlantic/Faeroe', 136438, 0),     (345, 'Atlantic/Faroe', 137114, 0),
(346, 'Atlantic/Jan_Mayen', 137790, 0),    (347, 'Atlantic/Madeira', 138608, 0),    (348, 'Atlantic/Reykjavik', 139897, 0),
(349, 'Atlantic/South_Georgia', 140358, 0),(350, 'Atlantic/St_Helena', 140888, 0),  (351, 'Atlantic/Stanley', 140426, 0),
(352, 'Australia/ACT', 140973, 0),         (353, 'Australia/Adelaide', 141776, 0),  (354, 'Australia/Brisbane', 142594, 0),
(355, 'Australia/Broken_Hill', 142799, 0), (356, 'Australia/Canberra', 143635, 0),  (357, 'Australia/Currie', 144438, 0),
(358, 'Australia/Darwin', 145263, 0),      (359, 'Australia/Eucla', 145403, 0),     (360, 'Australia/Hobart', 145623, 0),
(361, 'Australia/LHI', 146491, 0),         (362, 'Australia/Lindeman', 147164, 0),  (363, 'Australia/Lord_Howe', 147395, 0),
(364, 'Australia/Melbourne', 148084, 0),   (365, 'Australia/North', 148895, 0),     (366, 'Australia/NSW', 149017, 0),
(367, 'Australia/Perth', 149820, 0),       (368, 'Australia/Queensland', 150042, 0),(369, 'Australia/South', 150220, 0),
(370, 'Australia/Sydney', 151023, 0),      (371, 'Australia/Tasmania', 151858, 0),  (372, 'Australia/Victoria', 152701, 0),
(373, 'Australia/West', 153504, 0),        (374, 'Australia/Yancowinna', 153692, 0),(375, 'Brazil/Acre', 154500, 0),
(376, 'Brazil/DeNoronha', 154760, 0),      (377, 'Brazil/East', 155048, 0),         (378, 'Brazil/West', 155781, 0),
(379, 'Canada/Atlantic', 156029, 0),       (380, 'Canada/Central', 157285, 0),      (381, 'Canada/East-Saskatchewan', 159599, 0),
(382, 'Canada/Eastern', 158335, 0),        (383, 'Canada/Mountain', 159992, 0),     (384, 'Canada/Newfoundland', 160878, 0),
(385, 'Canada/Pacific', 162201, 0),        (386, 'Canada/Saskatchewan', 163250, 0), (387, 'Canada/Yukon', 163643, 0),
(388, 'CET', 164414, 0),                   (389, 'Chile/Continental', 165191, 0),   (390, 'Chile/EasterIsland', 165897, 0),
(391, 'CST6CDT', 166514, 0),               (392, 'Cuba', 167363, 0),                (393, 'EET', 168246, 0),
(394, 'Egypt', 168937, 0),                 (395, 'Eire', 169936, 0),

(396, 'EST',           171233, 0),     (397, 'EST5EDT',       171301, 0),        (398, 'Etc/GMT',       172150, 0),
(399, 'Etc/GMT+0',     172354, 0),     (400, 'Etc/GMT+1',     172492, 0),        (401, 'Etc/GMT+10',    172633, 0),
(402, 'Etc/GMT+11',    172775, 0),     (403, 'Etc/GMT+12',    172917, 0),        (404, 'Etc/GMT+2',     173200, 0),
(405, 'Etc/GMT+3',     173340, 0),     (406, 'Etc/GMT+4',     173480, 0),        (407, 'Etc/GMT+5',     173620, 0),
(408, 'Etc/GMT+6',     173760, 0),     (409, 'Etc/GMT+7',     173900, 0),        (410, 'Etc/GMT+8',     174040, 0),
(411, 'Etc/GMT+9',     174180, 0),     (412, 'Etc/GMT-0',     172286, 0),        (413, 'Etc/GMT-1',     172422, 0),
(414, 'Etc/GMT-10',    172562, 0),     (415, 'Etc/GMT-11',    172704, 0),        (416, 'Etc/GMT-12',    172846, 0),
(417, 'Etc/GMT-13',    172988, 0),     (418, 'Etc/GMT-14',    173059, 0),        (419, 'Etc/GMT-2',     173130, 0),
(420, 'Etc/GMT-3',     173270, 0),     (421, 'Etc/GMT-4',     173410, 0),        (422, 'Etc/GMT-5',     173550, 0),
(423, 'Etc/GMT-6',     173690, 0),     (424, 'Etc/GMT-7',     173830, 0),        (425, 'Etc/GMT-8',     173970, 0),
(426, 'Etc/GMT-9',     174110, 0),     (427, 'Etc/GMT0',      172218, 0),        (428, 'Etc/Greenwich', 174250, 0),
(429, 'Etc/UCT',       174318, 0),     (430, 'Etc/Universal', 174386, 0),        (431, 'Etc/UTC',       174454, 0),
(432, 'Etc/Zulu',      174522, 0),

(433, 'Europe/Amsterdam', 174590, 0),  (434, 'Europe/Andorra', 175676, 0),       (435, 'Europe/Athens', 176312, 0),
(436, 'Europe/Belfast', 177147, 0),    (437, 'Europe/Belgrade', 178482, 0),      (438, 'Europe/Berlin', 179195, 0),
(439, 'Europe/Bratislava', 180063, 0), (440, 'Europe/Brussels', 180881, 0),      (441, 'Europe/Bucharest', 181960, 0),
(442, 'Europe/Budapest', 182770, 0),   (443, 'Europe/Busingen', 183643, 0),      (444, 'Europe/Chisinau', 184338, 0),
(445, 'Europe/Copenhagen', 185248, 0), (446, 'Europe/Dublin', 186026, 0),        (447, 'Europe/Gibraltar', 187323, 0),
(448, 'Europe/Guernsey', 188434, 0),   (449, 'Europe/Helsinki', 189769, 0),      (450, 'Europe/Isle_of_Man', 190463, 0),
(451, 'Europe/Istanbul', 191798, 0),   (452, 'Europe/Jersey', 192803, 0),        (453, 'Europe/Kaliningrad', 194138, 0),
(454, 'Europe/Kiev', 194757, 0),       (455, 'Europe/Lisbon', 195553, 0),        (456, 'Europe/Ljubljana', 196837, 0),
(457, 'Europe/London', 197550, 0),     (458, 'Europe/Luxembourg', 198885, 0),    (459, 'Europe/Madrid', 199995, 0),
(460, 'Europe/Malta', 200961, 0),      (461, 'Europe/Mariehamn', 201914, 0),     (462, 'Europe/Minsk', 202608, 0),
(463, 'Europe/Monaco', 203139, 0),     (464, 'Europe/Moscow', 204222, 0),        (465, 'Europe/Nicosia', 204824, 0),
(466, 'Europe/Oslo', 205568, 0),       (467, 'Europe/Paris', 206386, 0),         (468, 'Europe/Podgorica', 207480, 0),
(469, 'Europe/Prague', 208193, 0),     (470, 'Europe/Riga', 209011, 0),          (471, 'Europe/Rome', 209848, 0),
(472, 'Europe/Samara', 210811, 0),     (473, 'Europe/San_Marino', 211428, 0),    (474, 'Europe/Sarajevo', 212391, 0),
(475, 'Europe/Simferopol', 213104, 0), (476, 'Europe/Skopje', 213697, 0),        (477, 'Europe/Sofia', 214410, 0),
(478, 'Europe/Stockholm', 215186, 0),  (479, 'Europe/Tallinn', 215873, 0),       (480, 'Europe/Tirane', 216699, 0),
(481, 'Europe/Tiraspol', 217473, 0),   (482, 'Europe/Uzhgorod', 218383, 0),      (483, 'Europe/Vaduz', 219174, 0),
(484, 'Europe/Vatican', 219861, 0),    (485, 'Europe/Vienna', 220824, 0),        (486, 'Europe/Vilnius', 221637, 0),
(487, 'Europe/Volgograd', 222468, 0),  (488, 'Europe/Warsaw', 223017, 0),        (489, 'Europe/Zagreb', 224010, 0),
(490, 'Europe/Zaporozhye', 224723, 0), (491, 'Europe/Zurich', 225556, 0),        (492, 'Factory', 226243, 0),
(493, 'GB', 226356, 0),                (494, 'GB-Eire', 227691, 0),              (495, 'GMT', 229026, 0),
(496, 'GMT+0', 229230, 0),             (497, 'GMT-0', 229162, 0),                (498, 'GMT0', 229094, 0),
(499, 'Greenwich', 229298, 0),         (500, 'Hongkong', 229366, 0),             (501, 'HST', 229816, 0),
(502, 'Iceland', 229884, 0),           (503, 'Indian/Antananarivo', 230345, 0),  (504, 'Indian/Chagos', 230472, 0),
(505, 'Indian/Christmas', 230570, 0),  (506, 'Indian/Cocos', 230638, 0),         (507, 'Indian/Comoro', 230706, 0),
(508, 'Indian/Kerguelen', 230833, 0),  (509, 'Indian/Mahe', 230918, 0),          (510, 'Indian/Maldives', 231003, 0),
(511, 'Indian/Mauritius', 231088, 0),  (512, 'Indian/Mayotte', 231206, 0),       (513, 'Indian/Reunion', 231333, 0),
(514, 'Iran', 231418, 0),              (515, 'Israel', 232040, 0),               (516, 'Jamaica', 232855, 0),
(517, 'Japan', 233052, 0),             (518, 'Kwajalein', 233190, 0),            (519, 'Libya', 233289, 0),
(520, 'MET', 233554, 0),               (521, 'Mexico/BajaNorte', 234331, 0),     (522, 'Mexico/BajaSur', 235204, 0),
(523, 'Mexico/General', 235785, 0),    (524, 'MST', 236391, 0),                  (525, 'MST7MDT', 236459, 0),
(526, 'Navajo', 237308, 0),            (527, 'NZ', 238197, 0),                   (528, 'NZ-CHAT', 239091, 0),
(529, 'Pacific/Apia', 239831, 0),      (530, 'Pacific/Auckland', 240243, 0),     (531, 'Pacific/Bougainville', 241151, 0),
(532, 'Pacific/Chatham', 241270, 0),   (533, 'Pacific/Chuuk', 242025, 0),        (534, 'Pacific/Easter', 242114, 0),
(535, 'Pacific/Efate', 242744, 0),     (536, 'Pacific/Enderbury', 242942, 0),    (537, 'Pacific/Fakaofo', 243052, 0),
(538, 'Pacific/Fiji', 243133, 0),      (539, 'Pacific/Funafuti', 243536, 0),     (540, 'Pacific/Galapagos', 243604, 0),
(541, 'Pacific/Gambier', 243724, 0),   (542, 'Pacific/Guadalcanal', 243825, 0),  (543, 'Pacific/Guam', 243910, 0),
(544, 'Pacific/Honolulu', 243996, 0),  (545, 'Pacific/Johnston', 244115, 0),     (546, 'Pacific/Kiritimati', 244242, 0),
(547, 'Pacific/Kosrae', 244349, 0),    (548, 'Pacific/Kwajalein', 244442, 0),    (549, 'Pacific/Majuro', 244550, 0),
(550, 'Pacific/Marquesas', 244645, 0), (551, 'Pacific/Midway', 244748, 0),       (552, 'Pacific/Nauru', 244881, 0),
(553, 'Pacific/Niue', 245001, 0),      (554, 'Pacific/Norfolk', 245095, 0),      (555, 'Pacific/Noumea', 245180, 0),
(556, 'Pacific/Pago_Pago', 245324, 0), (557, 'Pacific/Palau', 245443, 0),        (558, 'Pacific/Pitcairn', 245511, 0),
(559, 'Pacific/Pohnpei', 245596, 0),   (560, 'Pacific/Ponape', 245681, 0),       (561, 'Pacific/Port_Moresby', 245750, 0),
(562, 'Pacific/Rarotonga', 245832, 0), (563, 'Pacific/Saipan', 246052, 0),       (564, 'Pacific/Samoa', 246138, 0),
(565, 'Pacific/Tahiti', 246257, 0),    (566, 'Pacific/Tarawa', 246358, 0),       (567, 'Pacific/Tongatapu', 246442, 0),
(568, 'Pacific/Truk', 246582, 0),      (569, 'Pacific/Wake', 246651, 0),         (570, 'Pacific/Wallis', 246731, 0),
(571, 'Pacific/Yap', 246799, 0),       (572, 'Poland', 246868, 0),               (573, 'Portugal', 247861, 0),
(574, 'PRC', 249137, 0),               (575, 'PST8PDT', 249297, 0),              (576, 'ROC', 250146, 0),
(577, 'ROK', 250451, 0),               (578, 'Singapore', 250694, 0),            (579, 'Turkey', 250877, 0),
(580, 'UCT',               251882, 0), (581, 'Universal',         251950, 0),    (582, 'US/Alaska',         252018, 0),
(583, 'US/Aleutian',       252891, 0), (584, 'US/Arizona',        253761, 0),    (585, 'US/Central',        253903, 0),
(586, 'US/East-Indiana',   256473, 0), (587, 'US/Eastern',        255194, 0),    (588, 'US/Hawaii',         257091, 0),
(589, 'US/Indiana-Starke', 257204, 0), (590, 'US/Michigan',       258085, 0),    (591, 'US/Mountain',       258908, 0),
(592, 'US/Pacific',        259797, 0), (593, 'US/Pacific-New',    260826, 0),    (594, 'US/Samoa',          261855, 0),
(595, 'UTC',               261974, 0), (596, 'W-SU',              262733, 0),    (597, 'WET',               262042, 0),
(598, 'Zulu',              263312, 0);

# [BREAK]


#
# TABLE STRUCTURE FOR: people_names
#

DROP TABLE IF EXISTS people_names;
# [BREAK]

CREATE TABLE `people_names` (
  `pe_lKeyID`             int(11) NOT NULL AUTO_INCREMENT COMMENT 'People ID / Business ID for contact info',
  `pe_lHouseholdID`       int(11) DEFAULT NULL COMMENT 'household ID; head of household = (householdID==peopleID), ',
  `pe_lChapterID`         int(11) NOT NULL DEFAULT '0',
  `pe_bBiz`               tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = is a business/organization, 0=people',
  `pe_lBizIndustryID`     int(11) DEFAULT NULL COMMENT 'Foreign key to industry type list / Business Category' ,
  `pe_strTitle`           varchar(80) NOT NULL DEFAULT '''''',
  `pe_strFName`           varchar(80) NOT NULL DEFAULT '',
  `pe_strMName`           varchar(80) NOT NULL DEFAULT '',
  `pe_strLName`           varchar(80) NOT NULL DEFAULT '',
  `pe_strPreferredName`   varchar(80) NOT NULL DEFAULT '',
  `pe_strSalutation`      varchar(80) NOT NULL DEFAULT '',
  `pe_dteBirthDate`       date DEFAULT NULL,
  `pe_dteDeathDate`       date DEFAULT NULL,
  `pe_enumGender`         enum('Male','Female','Unknown') NOT NULL DEFAULT 'Unknown',
  `pe_lACO`               int(11) DEFAULT NULL COMMENT 'foreign key to table admin_aco',
  `pe_strAddr1`           varchar(80) NOT NULL DEFAULT '',
  `pe_strAddr2`           varchar(80) NOT NULL DEFAULT '',
  `pe_strCity`            varchar(80) NOT NULL DEFAULT '',
  `pe_strState`           varchar(80) NOT NULL DEFAULT '',
  `pe_strCountry`         varchar(80) NOT NULL DEFAULT '',
  `pe_strZip`             varchar(40) NOT NULL DEFAULT '',
  `pe_strPhone`           varchar(40) NOT NULL DEFAULT '',
  `pe_strCell`            varchar(40) NOT NULL DEFAULT '',

  `pe_strFax`             VARCHAR( 40 ) NOT NULL DEFAULT  '',
  `pe_strWebSite`         VARCHAR( 255 ) NOT NULL DEFAULT '',

  `pe_strEmail`           varchar(120) NOT NULL DEFAULT '',
  `pe_strNotes`           text NOT NULL,
  `pe_bNoGiftAcknowledge` tinyint(1) NOT NULL DEFAULT '0',
  `pe_lAttributedTo`      int(11) DEFAULT NULL,
  `pe_strImportID`        varchar(40) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL,
  `pe_dteExpire`          date DEFAULT NULL COMMENT 'for temp mailing lists - date record can be removed',
  `pe_bRetired`           tinyint(1) NOT NULL DEFAULT '0',
  `pe_lOriginID`          int(11) NOT NULL DEFAULT '0' COMMENT 'ID of the creator of this people/biz record' ,
  `pe_lLastUpdateID`      int(11) NOT NULL DEFAULT '0' COMMENT 'ID of the user who last updated people/biz record' ,
  `pe_dteOrigin`          datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pe_dteLastUpdate`      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pe_lKeyID`),
  KEY `pe_strFName`      (`pe_strFName`),
  KEY `pe_strLName`      (`pe_strLName`),
  KEY `pe_lChapterID`    (`pe_lChapterID`),
  KEY `pe_dteBirthDate`  (`pe_dteBirthDate`),
  KEY `pe_dteDeathDate`  (`pe_dteDeathDate`),
  KEY `pe_lImportID`     (`pe_strImportID`),
  KEY `pe_lAttributedTo` (`pe_lAttributedTo`),
  KEY `pe_lHouseholdID`  (`pe_lHouseholdID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

# [BREAK]


#
# TABLE STRUCTURE FOR: people_relationships
#

DROP TABLE IF EXISTS people_relationships;
# [BREAK]

CREATE TABLE `people_relationships` (
  `pr_lKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `pr_lPerson_A_ID` int(11) NOT NULL DEFAULT '0',
  `pr_lPerson_B_ID` int(11) NOT NULL DEFAULT '0',
  `pr_lRelID_A2B` int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to table tbl_lists_people_relationships',
  `pr_bSoftDonations` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If true, a soft cash relationship exists',
  `pr_strNotes` text,
  `pr_bRetired` tinyint(1) NOT NULL DEFAULT '0',
  `pr_lOriginID` int(11) NOT NULL DEFAULT '0',
  `pr_lLastUpdateID` int(11) NOT NULL DEFAULT '0',
  `pr_dteOrigin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pr_dteLastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pr_lKeyID`),
  KEY `pr_lPerson_A_ID` (`pr_lPerson_A_ID`),
  KEY `pr_lPerson_B_ID` (`pr_lPerson_B_ID`),
  KEY `pr_lRelID_A_B` (`pr_lRelID_A2B`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Relationships betwee People';
# [BREAK]



#
# TABLE STRUCTURE FOR: recycle_bin
#

DROP TABLE IF EXISTS recycle_bin;
# [BREAK]

CREATE TABLE `recycle_bin` (
  `rb_lKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `rb_lGroupID` int(11) NOT NULL DEFAULT '0',
  `rb_lForeignID` int(11) NOT NULL,
  `rb_enumRecycleType` enum('people','household','business','businessContact','volunteer','staff','sponsorship','gift','client') NOT NULL,
  `rb_strDescription` varchar(255) NOT NULL DEFAULT '',
  `rb_strTable` varchar(200) NOT NULL DEFAULT '',
  `rb_strKeyIDFN` varchar(200) NOT NULL DEFAULT '',
  `rb_strRetireFN` varchar(200) NOT NULL DEFAULT '',
  `rb_lOriginID` int(11) NOT NULL DEFAULT '0',
  `rb_lLastUpdateID` int(11) NOT NULL DEFAULT '0',
  `rb_dteOrigin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `rb_dteLastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rb_lKeyID`),
  KEY `rb_lGroupID` (`rb_lGroupID`),
  KEY `rb_enumRecycleType` (`rb_enumRecycleType`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Recycle bin';
# [BREAK]



#
# TABLE STRUCTURE FOR: reminders
#

DROP TABLE IF EXISTS reminders;
# [BREAK]

CREATE TABLE `reminders` (
  `re_lKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `re_enumSource` enum('people','business','sponsorship','client','location','gift','volunteer','user','generic') DEFAULT NULL,
  `re_lForeignID` int(11) DEFAULT NULL COMMENT 'peopleID, giftID, etc.',
  `re_strTitle` varchar(255) NOT NULL DEFAULT '',
  `re_strReminderNote` text NOT NULL,
  `re_bRetired` tinyint(1) NOT NULL DEFAULT '0',
  `re_lOriginID` int(11) NOT NULL DEFAULT '0',
  `re_lLastUpdateID` int(11) NOT NULL DEFAULT '0',
  `re_dteOrigin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `re_dteLastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`re_lKeyID`),
  KEY `re_enumSource` (`re_enumSource`),
  KEY `re_lForeignID` (`re_lForeignID`),
  KEY `re_lLastUpdateID` (`re_lLastUpdateID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Reminders parent table';
# [BREAK]



#
# TABLE STRUCTURE FOR: reminders_dates
#

DROP TABLE IF EXISTS reminders_dates;
# [BREAK]

CREATE TABLE `reminders_dates` (
  `rd_lKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `rd_lRemID` int(11) DEFAULT NULL COMMENT 'foreign key to table reminders',
  `rd_dteDisplayDate` date NOT NULL DEFAULT '0000-00-00',
  `rd_dteEndDisplayDate` date NOT NULL,
  PRIMARY KEY (`rd_lKeyID`),
  KEY `rd_lRemID` (`rd_lRemID`),
  KEY `rd_dteDisplayDate` (`rd_dteDisplayDate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Reminders dates';
# [BREAK]



#
# TABLE STRUCTURE FOR: reminders_followup
#

DROP TABLE IF EXISTS reminders_followup;
# [BREAK]

CREATE TABLE `reminders_followup` (
  `rfu_lKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `rfu_lRemID` int(11) DEFAULT '0' COMMENT 'foreign key to table reminders',
  `rfu_lUserID` int(11) DEFAULT '0' COMMENT 'note made by this user',
  `rfu_strFollowUpNote` text NOT NULL,
  `rfu_dteOfNote` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rfu_lKeyID`),
  KEY `rfu_lRemID` (`rfu_lRemID`),
  KEY `rfu_dteOfNote` (`rfu_dteOfNote`),
  KEY `rfu_lUserID` (`rfu_lUserID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='follow-up notes; child of reminders';
# [BREAK]



#
# TABLE STRUCTURE FOR: reminders_inform
#

DROP TABLE IF EXISTS reminders_inform;
# [BREAK]

CREATE TABLE `reminders_inform` (
  `ri_lKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `ri_lRemDateID` int(11) DEFAULT '0' COMMENT 'foreign key to table reminders_dates',
  `ri_lUserID` int(11) DEFAULT '0' COMMENT 'inform this user',
  `ri_bViewed` tinyint(1) NOT NULL DEFAULT '0',
  `ri_bHidden` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ri_lKeyID`),
  KEY `ri_lRemID` (`ri_lRemDateID`),
  KEY `ri_lUserID` (`ri_lUserID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Inform these users; child of reminders_dates';

# [BREAK]



DROP TABLE IF EXISTS `serial_objects`;

# [BREAK]

CREATE TABLE IF NOT EXISTS `serial_objects` (
  `so_lKeyID`     int(11) NOT NULL AUTO_INCREMENT,
  `so_object`     text NOT NULL COMMENT 'serialized object',
  `so_dteCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`so_lKeyID`),
  KEY `so_dteCreated` (`so_dteCreated`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Hold temporary serialized objects' AUTO_INCREMENT=1 ;


# [BREAK]




#
# TABLE STRUCTURE FOR: sponsor
#

DROP TABLE IF EXISTS sponsor;
# [BREAK]

CREATE TABLE `sponsor` (
  `sp_lKeyID`             int(11) NOT NULL AUTO_INCREMENT,
  `sp_lForeignID`         int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to table people_names; person or business',
  `sp_lHonoreeID`         int(11) DEFAULT NULL COMMENT 'foreign key to people; optional mail contact for sponsorship',
  `sp_lClientID`          int(11) DEFAULT NULL,
  `sp_curCommitment`      decimal(10,2) NOT NULL DEFAULT '0.00',
  `sp_lCommitmentACO`     int(11) NOT NULL COMMENT 'Accounting Country of Origin for Sponsorship payments',
  `sp_dteStartMoYr`       date NOT NULL DEFAULT '0000-00-00',
  `sp_lDefPayType`        smallint(6) NOT NULL DEFAULT '0',
  `sp_lSponsorProgramID`  int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to lists_sponsorship_categories',
  `sp_lAttributedTo`      int(11) DEFAULT NULL,
  `sp_bInactive`          tinyint(1) NOT NULL DEFAULT '0',
  `sp_dteInactive`        date DEFAULT NULL,
  `sp_lInactiveCatID`     int(11) DEFAULT NULL COMMENT 'foreign key to lists_generic / sponTermCat',
  `sp_strTerminationNote` text,
  `sp_bInactiveDueToXfer` tinyint(1) NOT NULL DEFAULT '0',
  `sp_lxferSponsorID`     int(11) DEFAULT NULL,
  `sp_bRetired`           tinyint(1) NOT NULL DEFAULT '0',
  `sp_lOriginID`          int(11) NOT NULL DEFAULT '0',
  `sp_dteOrigin`          datetime NOT NULL,
  `sp_lLastUpdateID`      int(11) NOT NULL DEFAULT '0',
  `sp_dteLastUpdate`      timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sp_lKeyID`),
  KEY `sp_lPersonID`        (`sp_lForeignID`),
  KEY `sp_lClientID`        (`sp_lClientID`),
  KEY `sp_lDefPayType`      (`sp_lDefPayType`),
  KEY `sp_lDefSponsorCatID` (`sp_lSponsorProgramID`),
  KEY `sp_lAttributedTo`    (`sp_lAttributedTo`),
  KEY `sp_lxferSponsorID`   (`sp_lxferSponsorID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='master sponsorship table';
# [BREAK]



#
# TABLE STRUCTURE FOR: sponsor_autocharge_log
#

DROP TABLE IF EXISTS sponsor_autocharge_log;
# [BREAK]

CREATE TABLE `sponsor_autocharge_log` (
  `spcl_lKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `spcl_dteDateOfCharges` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Month/Year of Charge',
  `spcl_dteOrigin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `spcl_lOriginID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`spcl_lKeyID`),
  KEY `spcl_dteDateOfCharges` (`spcl_dteDateOfCharges`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]



#
# TABLE STRUCTURE FOR: sponsor_charges
#

DROP TABLE IF EXISTS sponsor_charges;
# [BREAK]

CREATE TABLE `sponsor_charges` (
  `spc_lKeyID` int(11) NOT NULL AUTO_INCREMENT,
  `spc_lAutoGenID` int(11) DEFAULT NULL COMMENT 'foreign key to sponsor_autocharge_log',
  `spc_lAutoGenACOID` int(11) DEFAULT NULL,
  `spc_curAutoGenCommitAmnt` decimal(10,2) DEFAULT NULL,
  `spc_curChargeAmnt` decimal(10,2) NOT NULL DEFAULT '0.00',
  `spc_lACOID` int(11) DEFAULT NULL,
  `spc_dteCharge` date NOT NULL DEFAULT '0000-00-00',
  `spc_lSponsorshipID` int(11) DEFAULT NULL,
  `spc_strNotes` text,
  `spc_bRetired` tinyint(1) NOT NULL DEFAULT '0',
  `spc_lOriginID` int(11) NOT NULL DEFAULT '0',
  `spc_lLastUpdateID` int(11) NOT NULL DEFAULT '0',
  `spc_dteOrigin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `spc_dteLastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`spc_lKeyID`),
  KEY `spc_dteCharge` (`spc_dteCharge`),
  KEY `spc_lSponsorshipID` (`spc_lSponsorshipID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Charges applied to the sponsors';

# [BREAK]


#
# Table structure for table 'staff_timesheets'
#


DROP TABLE IF EXISTS staff_timesheets;

# [BREAK]

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

# [BREAK]

#
# Table structure for table 'staff_ts_admin'
#

DROP TABLE IF EXISTS staff_ts_admin;

# [BREAK]

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Users who are authorized to view/edit time sheet entries' AUTO_INCREMENT=6 ;

# [BREAK]

#
# Table structure for table 'staff_ts_log'
#

DROP TABLE IF EXISTS staff_ts_log;

# [BREAK]

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Time sheet log for staff member' AUTO_INCREMENT=1 ;

# [BREAK]

#
# Table structure for table 'staff_ts_log_entry'
#

DROP TABLE IF EXISTS staff_ts_log_entry;

# [BREAK]

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Time sheet log entry for staff member' AUTO_INCREMENT=1 ;

# [BREAK]

#
# Table structure for table 'staff_ts_log_projects'
#

DROP TABLE IF EXISTS staff_ts_log_projects;

# [BREAK]

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Time assigned to projects' AUTO_INCREMENT=1 ;

# [BREAK]

#
# Table structure for table 'staff_ts_staff'
#

DROP TABLE IF EXISTS staff_ts_staff;

# [BREAK]

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Staff member time sheet template assignments' AUTO_INCREMENT=1 ;


# [BREAK]


#
# TABLE STRUCTURE FOR: uf_ddl
#

DROP TABLE IF EXISTS uf_ddl;
# [BREAK]

CREATE TABLE `uf_ddl` (
  `ufddl_lKeyID`      int(11)     NOT NULL AUTO_INCREMENT,
  `ufddl_lFieldID`    int(11)     NOT NULL DEFAULT '0',
  `ufddl_lSortIDX`    int(11)     NOT NULL DEFAULT '0',
  `ufddl_bRetired`    tinyint(1)  NOT NULL DEFAULT '0',
  `ufddl_strDDLEntry` varchar(80) NOT NULL,
  PRIMARY KEY (`ufddl_lKeyID`),
  KEY `ufddl_lFieldID` (`ufddl_lFieldID`),
  KEY `ufddl_lSortIDX` (`ufddl_lSortIDX`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='User-defined drop-down list entries';
# [BREAK]


#
# Table structure for table `uf_ddl_multi`
#

DROP TABLE IF EXISTS `uf_ddl_multi`;
# [BREAK]
CREATE TABLE IF NOT EXISTS `uf_ddl_multi` (
  `pdm_lKeyID`       int(11) NOT NULL AUTO_INCREMENT,
  `pdm_lFieldID`     int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to uf_fields',
  `pdm_lUTableID`    int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to uf_tables',
  `pdm_lUTableRecID` int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to ufield data table',
  `pdm_lDDLID`       int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to uf_ddl',
  PRIMARY KEY (`pdm_lKeyID`),
  KEY `pdm_lFieldID`     (`pdm_lFieldID`),
  KEY `pdm_lUTableID`    (`pdm_lUTableID`),
  KEY `pdm_lUTableRecID` (`pdm_lUTableRecID`),
  KEY `pdm_entry`        (`pdm_lFieldID`,`pdm_lUTableID`,`pdm_lUTableRecID`),
  KEY `pdm_lDDLID`       (`pdm_lDDLID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

# [BREAK]


#
# TABLE STRUCTURE FOR: uf_fields
#

DROP TABLE IF EXISTS uf_fields;
# [BREAK]

CREATE TABLE `uf_fields` (
  `pff_lKeyID`               int(11) NOT NULL AUTO_INCREMENT,
  `pff_lTableID`             int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table uf_tables',
  `pff_lSortIDX`             int(6) NOT NULL DEFAULT '0' COMMENT 'How fields are sorted for display',
  `pff_strFieldNameInternal` varchar(50) NOT NULL DEFAULT '' COMMENT 'Internal field name',
  `pff_strFieldNameUser`     varchar(80) NOT NULL DEFAULT '' COMMENT 'User-defined field name',
  `pff_strFieldNotes`        text NOT NULL,
  `pff_enumFieldType`        enum('Checkbox','Date','DateTime','TextLong','Text255','Text80','Text20','Integer','Currency','DDL','Log','Heading','Email','Hyperlink','Reminder','DDLMulti','clientID') NOT NULL COMMENT 'custom field type',
  `pff_bPrefilled`           tinyint(1) NOT NULL DEFAULT '0' COMMENT 'For multi-record fields, prefill from most recent',
  `pff_bRequired`            tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Is field required?',
  `pff_bConfigured`          tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Has DDL been configured?',
  `pff_bCheckDef`            tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Default checkbox value',
  `pff_curDef`               decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Default currency value',
  `pff_strTxtDef`     text    NOT NULL COMMENT 'Default text value',
  `pff_lDef`          int(11) NOT NULL DEFAULT '0' COMMENT 'Default integer value',
  `pff_lDDLDefault`   int(11) DEFAULT NULL COMMENT 'Default DDL Index',
  `pff_lCurrencyACO`  int(11) NOT NULL COMMENT 'foreign key to table admin_aco',
  `pff_bHidden`       tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If set, hide this field',
  `pff_lOriginID`     int(11)    NOT NULL DEFAULT '0',
  `pff_lLastUpdateID` int(11)    NOT NULL DEFAULT '0',
  `pff_dteOrigin`     datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
  `pff_dteLastUpdate` timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`pff_lKeyID`),
  KEY `pff_lTableID` (`pff_lTableID`),
  KEY `pff_lSortIDX` (`pff_lSortIDX`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]

#
# TABLE STRUCTURE FOR: uf_logs
#

DROP TABLE IF EXISTS uf_logs;
# [BREAK]

CREATE TABLE `uf_logs` (
  `uflog_lKeyID`        int(11) NOT NULL AUTO_INCREMENT,
  `uflog_lFieldID`      int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to table uf_fields',
  `uflog_lForeignID`    int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key; peopleID, sponID, etc',
  `uflog_lOriginID`     int(11) NOT NULL COMMENT 'user creating the entry',
  `uflog_lLastUpdateID` int(11) NOT NULL COMMENT 'user to last update the entry',
  `uflog_dteOrigin`     datetime NOT NULL,
  `uflog_dteLastUpdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `uflog_strLogTitle`   varchar(255) NOT NULL DEFAULT '' COMMENT 'optional log entry title',
  `uflog_strLogEntry`   text NOT NULL COMMENT 'log entry text',
  PRIMARY KEY (`uflog_lKeyID`),
  KEY `uflog_lFieldID`   (`uflog_lFieldID`),
  KEY `uflog_lForeignID` (`uflog_lForeignID`),
  KEY `uflog_lOriginID`  (`uflog_lOriginID`),
  KEY `uflog_dteOrigin`  (`uflog_dteOrigin`),
  FULLTEXT KEY uflog_strLogTitle   (uflog_strLogTitle),
  FULLTEXT KEY `uflog_strLogEntry` (uflog_strLogEntry)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]



#
# TABLE STRUCTURE FOR: uf_tables
#

DROP TABLE IF EXISTS uf_tables;
# [BREAK]

CREATE TABLE uf_tables (
  pft_lKeyID                 int(11) NOT NULL AUTO_INCREMENT,
  pft_strUserTableName       varchar(50) NOT NULL DEFAULT '',
  pft_strDataTableName       varchar(50) NOT NULL DEFAULT '',
  pft_enumAttachType         enum('people', 'business', 'sponsorship', 'client',
                                  'location', 'gift', 'volunteer',
                                  'clientProgramEnrollment', 'clientProgramAttendance',
                                  'user', 'grants', 'grantProvider') NOT NULL COMMENT 'Parent table this table attaches to',
  pft_strDescription         text NOT NULL,
  pft_bMultiEntry            tinyint(1) NOT NULL DEFAULT '0' COMMENT 'When set, allow multiple entries for a foreign key',
  pft_bReadOnly              tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if true, multi-record can only be written once',
  pft_bAlertIfNoEntry        tinyint(1) NOT NULL DEFAULT '0' COMMENT 'For single-entry tables - generate alert if not completed',
  pft_strAlertMsg            text       NOT NULL             COMMENT 'Alert message if alert flag set and no data entry',
  pft_bHidden                tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If set, the table is hidden',
  pft_bCollapsibleHeadings   tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If set, all fields under a header to be collapsed',
  pft_bCollapseDefaultHide   tinyint(1) NOT NULL DEFAULT '0' COMMENT 'If set, the default state is collapsed',

  pft_strVerificationModule  varchar(255) DEFAULT NULL,
  pft_strVModEntryPoint      varchar(255) DEFAULT NULL,

  pft_bRetired               tinyint(1) NOT NULL DEFAULT '0',
  pft_lPermissions           int(11)    NOT NULL DEFAULT '0',
  pft_lOriginID              int(11)    NOT NULL DEFAULT '0',
  pft_lLastUpdateID          int(11)    NOT NULL DEFAULT '0',
  pft_dteOrigin              datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
  pft_dteLastUpdate          timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (pft_lKeyID),
  KEY pft_strUserTableName (pft_strUserTableName),
  KEY pft_enumAttachType   (pft_enumAttachType)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]


--
-- Table structure for table 'uf_table_perms'
--

DROP TABLE IF EXISTS uf_table_perms;
# [BREAK]

CREATE TABLE IF NOT EXISTS uf_table_perms (
  ppr_lKeyID   int(11) NOT NULL AUTO_INCREMENT,
  ppr_lTableID int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to uf_tables',
  ppr_lGroupID int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to groups_parent',
  PRIMARY KEY      (ppr_lKeyID),
  KEY ppr_lTableID (ppr_lTableID),
  KEY ppr_lGroupID (ppr_lGroupID)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1
    COMMENT='Permission groups for standard users; no permission groups means all access';

# [BREAK]



#
# TABLE STRUCTURE FOR: volunteers
#

DROP TABLE IF EXISTS volunteers;
# [BREAK]

CREATE TABLE `volunteers` (
  `vol_lKeyID`        int(11) NOT NULL AUTO_INCREMENT,
  `vol_lPeopleID`     int(11) DEFAULT NULL,
  `vol_lRegFormID`    int(11) DEFAULT NULL COMMENT 'If user registered via vol. reg. form, this is the formID',
  `vol_bInactive`     tinyint(1) NOT NULL DEFAULT '0',
  `vol_dteInactive`   datetime   DEFAULT NULL,
  `vol_Notes`         text,
  `vol_bRetired`      tinyint(1) NOT NULL DEFAULT '0',
  `vol_lOriginID`     int(11)    NOT NULL DEFAULT '0',
  `vol_lLastUpdateID` int(11)    NOT NULL DEFAULT '0',
  `vol_dteOrigin`     datetime   NOT NULL DEFAULT '0000-00-00 00:00:00',
  `vol_dteLastUpdate` timestamp  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`vol_lKeyID`),
  UNIQUE KEY `vol_lPeopleID` (`vol_lPeopleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]




#
# TABLE STRUCTURE FOR: vol_client_association
#

DROP TABLE IF EXISTS `vol_client_association`;
# [BREAK]

CREATE TABLE IF NOT EXISTS `vol_client_association` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Associates volunteers with clients' AUTO_INCREMENT=1;
# [BREAK]



#
# TABLE STRUCTURE FOR: vol_events
#

DROP TABLE IF EXISTS vol_events;
# [BREAK]

CREATE TABLE `vol_events` (
  `vem_lKeyID`            int(11)      NOT NULL AUTO_INCREMENT,
  `vem_strEventName`      varchar(255) NOT NULL DEFAULT '',
  `vem_strDescription`    text NOT NULL,
  `vem_dteEventStartDate` date NOT NULL DEFAULT '0000-00-00',
  `vem_dteEventEndDate`   date NOT NULL DEFAULT '0000-00-00',
  `vem_strLocation`       text NOT NULL,
  `vem_strContact`        varchar(255) NOT NULL DEFAULT '',
  `vem_strPhone`          varchar(80)  NOT NULL DEFAULT '',
  `vem_strEmail`          varchar(200) NOT NULL DEFAULT '',
  `vem_strWebSite`        varchar(200) NOT NULL DEFAULT '',
  `vem_bRetired`          tinyint(1)   NOT NULL DEFAULT '0',
  `vem_lOriginID`         int(11)      NOT NULL DEFAULT '0',
  `vem_lLastUpdateID`     int(11)      NOT NULL DEFAULT '0',
  `vem_dteOrigin`         datetime     NOT NULL DEFAULT '0000-00-00 00:00:00',
  `vem_dteLastUpdate`     timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`vem_lKeyID`),
  KEY `vem_dteEventDate`    (`vem_dteEventStartDate`),
  KEY `vem_dteEventEndDate` (`vem_dteEventEndDate`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]



#
# TABLE STRUCTURE FOR: vol_events_dates
#

DROP TABLE IF EXISTS vol_events_dates;
# [BREAK]

CREATE TABLE `vol_events_dates` (
  `ved_lKeyID`      int(11) NOT NULL AUTO_INCREMENT,
  `ved_lVolEventID` int(11) DEFAULT NULL COMMENT 'foreign key to table vol_events',
  `ved_dteEvent`    date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`ved_lKeyID`),
  KEY `ved_lVolEventID` (`ved_lVolEventID`),
  KEY `ved_dteEvent`    (`ved_dteEvent`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Volunteer event dates';

# [BREAK]


#
# TABLE STRUCTURE FOR: vol_events_dates_shifts
#

DROP TABLE IF EXISTS vol_events_dates_shifts;
# [BREAK]

CREATE TABLE `vol_events_dates_shifts` (
  `vs_lKeyID`            int(11) NOT NULL AUTO_INCREMENT,
  `vs_lEventDateID`      int(11) NOT NULL COMMENT 'Foreign key to vol_events_dates',
  `vs_strShiftName`      varchar(255) NOT NULL DEFAULT '',
  `vs_lJobCode`          int(11)               DEFAULT NULL COMMENT 'Foreign key to lists_generic',
  `vs_strDescription`    text NOT NULL,
  `vs_dteShiftStartTime` time NOT NULL,
  `vs_enumDuration`      enum(
       '(all day)','15 minutes','30 minutes','45 minutes',
       '1 hour',   '1 hour 15 minutes',  '1 hour 30 minutes',  '1 hour 45 minutes',
       '2 hours',  '2 hours 15 minutes', '2 hours 30 minutes', '2 hours 45 minutes',
       '3 hours',  '3 hours 15 minutes', '3 hours 30 minutes', '3 hours 45 minutes',
       '4 hours',  '4 hours 15 minutes', '4 hours 30 minutes', '4 hours 45 minutes',
       '5 hours',  '5 hours 15 minutes', '5 hours 30 minutes', '5 hours 45 minutes',
       '6 hours',  '6 hours 15 minutes', '6 hours 30 minutes', '6 hours 45 minutes',
       '7 hours',  '7 hours 15 minutes', '7 hours 30 minutes', '7 hours 45 minutes',
       '8 hours',  '8 hours 15 minutes', '8 hours 30 minutes', '8 hours 45 minutes',
       '9 hours',  '9 hours 15 minutes', '9 hours 30 minutes', '9 hours 45 minutes',
       '10 hours','10 hours 15 minutes','10 hours 30 minutes','10 hours 45 minutes',
       '11 hours','11 hours 15 minutes','11 hours 30 minutes','11 hours 45 minutes',
       '12 hours','12 hours 15 minutes','12 hours 30 minutes','12 hours 45 minutes',
       '13 hours','13 hours 15 minutes','13 hours 30 minutes','13 hours 45 minutes',
       '14 hours','14 hours 15 minutes','14 hours 30 minutes','14 hours 45 minutes',
       '15 hours','15 hours 15 minutes','15 hours 30 minutes','15 hours 45 minutes',
       '16 hours','16 hours 15 minutes','16 hours 30 minutes','16 hours 45 minutes',
       '17 hours','17 hours 15 minutes','17 hours 30 minutes','17 hours 45 minutes',
       '18 hours','18 hours 15 minutes','18 hours 30 minutes','18 hours 45 minutes',
       '19 hours','19 hours 15 minutes','19 hours 30 minutes','19 hours 45 minutes',
       '20 hours','20 hours 15 minutes','20 hours 30 minutes','20 hours 45 minutes',
       '21 hours','21 hours 15 minutes','21 hours 30 minutes','21 hours 45 minutes',
       '22 hours','22 hours 15 minutes','22 hours 30 minutes','22 hours 45 minutes',
       '23 hours','23 hours 15 minutes','23 hours 30 minutes','23 hours 45 minutes')
    NOT NULL DEFAULT '(all day)' COMMENT 'Duration',
--  `vs_lDuration15` smallint(6) NOT NULL DEFAULT '0' COMMENT 'Duration in 15 min block, -1:all day',
  `vs_lNumVolsNeeded` smallint(6) NOT NULL DEFAULT '0',
  `vs_bRetired` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`vs_lKeyID`),
  KEY `vs_lEventDateID`      (`vs_lEventDateID`),
  KEY `vs_dteShiftStartDate` (`vs_dteShiftStartTime`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]



#
# TABLE STRUCTURE FOR: vol_events_dates_shifts_assign
#

DROP TABLE IF EXISTS vol_events_dates_shifts_assign;
# [BREAK]


CREATE TABLE IF NOT EXISTS vol_events_dates_shifts_assign (
  vsa_lKeyID            int(11) NOT NULL AUTO_INCREMENT,
  vsa_lEventDateShiftID int(11) DEFAULT NULL COMMENT 'Foreign key to vol_events_dates_shifts / null for simple vol hrs',
  vsa_lVolID            int(11) NOT NULL     COMMENT 'Foreign key to volunteers',
  vsa_strNotes          text NOT NULL,
  vsa_dHoursWorked      decimal(10,2) NOT NULL DEFAULT '0.00',
  vsa_dteActivityDate   datetime DEFAULT NULL COMMENT 'Date/time start for unscheduled hours',
  vsa_lActivityID       int(11)  DEFAULT NULL COMMENT 'fkey to lists_generic/for unscheduled hours',
  vsa_lJobCode          int(11)  DEFAULT NULL COMMENT 'only for unscheduled hours',
  vsa_bRetired          tinyint(1) NOT NULL DEFAULT '0',
  vsa_lOriginID         int(11)  DEFAULT NULL COMMENT 'for unscheduled hours',
  vsa_lLastUpdateID     int(11)  DEFAULT NULL COMMENT 'for unscheduled hours',
  vsa_dteOrigin         datetime DEFAULT NULL COMMENT 'for unscheduled hours',
  vsa_dteLastUpdate     timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'for unscheduled hours',
  PRIMARY KEY (vsa_lKeyID),
  KEY vsa_lEventDateShiftID (vsa_lEventDateShiftID),
  KEY vsa_lVolID            (vsa_lVolID)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]


DROP TABLE IF EXISTS vol_reg;
# [BREAK]

CREATE TABLE IF NOT EXISTS vol_reg (
  vreg_lKeyID                int(11)      NOT NULL AUTO_INCREMENT,
  vreg_strFormName           varchar(255) NOT NULL DEFAULT '',
  vreg_strURLHash            varchar(255) NOT NULL DEFAULT '' COMMENT 'Hash of key ID for vol. URL',
  vreg_strDescription        text         NOT NULL COMMENT 'Internal description',
  vreg_strIntro              text         NOT NULL COMMENT 'Visible to the volunteer',
  vreg_strSubmissionText     text         NOT NULL COMMENT 'Text displayed to volunteer after successful submission',
  vreg_strBannerOrg          varchar(255) NOT NULL DEFAULT '',
  vreg_strBannerTitle        varchar(255) NOT NULL DEFAULT '',
  vreg_lLogoImageID          int(11)               DEFAULT NULL COMMENT 'Optional organization logo',
  vreg_strCSSFN              varchar(255) NOT NULL DEFAULT 'default.css' COMMENT 'User-selectable style sheet, located in ./css/vol_reg',
  vreg_strHexBGColor         varchar(25)  NOT NULL DEFAULT '' COMMENT 'ref: http://jsfiddle.net/bgrins/ctkY3/',
  vreg_strContact            varchar(255) NOT NULL DEFAULT '',
  vreg_strContactPhone       varchar(80)  NOT NULL DEFAULT '',
  vreg_strContactEmail       varchar(200) NOT NULL DEFAULT '' COMMENT 'Will receive a registration alert at this address',
  vreg_strWebSite            varchar(255) NOT NULL DEFAULT '',
  vreg_lVolGroupID           int(11)               DEFAULT NULL COMMENT 'Optional vol. group ID; new reg. placed in group',

     -- permissions when registered
  vreg_bPermEditContact      tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Allow vol to edit contact info',
  vreg_bPermPassReset        tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Allow vol to reset password',
  vreg_bPermViewGiftHistory  tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Allow vol to view donation history',
  vreg_bPermEditJobSkills    tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Allow vol to edit job skills',
  vreg_bPermViewHrsHistory   tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Allow vol to view volunteer hours',
  vreg_bPermAddVolHours      tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Allow vol to add/edit vol hours',
  vreg_bVolShiftSignup       tinyint(1) NOT NULL DEFAULT '1' COMMENT 'If checked, volunteer can sign up for event shifts',

     -- standard display fields
  vreg_bShowFName            tinyint(1)   NOT NULL DEFAULT '1',
  vreg_bShowLName            tinyint(1)   NOT NULL DEFAULT '1',
  vreg_bShowAddr             tinyint(1)   NOT NULL DEFAULT '1',
  vreg_bShowEmail            tinyint(1)   NOT NULL DEFAULT '1',
  vreg_bShowPhone            tinyint(1)   NOT NULL DEFAULT '1',
  vreg_bShowCell             tinyint(1)   NOT NULL DEFAULT '1',
  vreg_bShowBDay             tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'Show birthdate field?',
  vreg_bShowDisclaimer       tinyint(1)   NOT NULL DEFAULT '1',
  vreg_strDisclaimerAck      varchar(255) NOT NULL DEFAULT '' COMMENT 'Acknowledgement text',
  vreg_strDisclaimer         text         NOT NULL COMMENT 'Form disclaimer, visibile to volunteer',
  vreg_bFNameRequired        tinyint(1)   NOT NULL DEFAULT '1',
  vreg_bLNameRequired        tinyint(1)   NOT NULL DEFAULT '1',
  vreg_bAddrRequired         tinyint(1)   NOT NULL DEFAULT '1',
  vreg_bEmailRequired        tinyint(1)   NOT NULL DEFAULT '1',
  vreg_bPhoneRequired        tinyint(1)   NOT NULL DEFAULT '1',
  vreg_bCellRequired         tinyint(1)   NOT NULL DEFAULT '1',
  vreg_bBDateRequired        tinyint(1)   NOT NULL DEFAULT '1',

     -- Disclaimer
  vreg_bDisclaimerAckRqrd    tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'Must the vol. acknowledge the disclaimer to submit?',
  vreg_bCaptchaRequired      tinyint(1)   NOT NULL DEFAULT '1' COMMENT 'http://www.google.com/recaptcha',
  vreg_bRetired              tinyint(1)   NOT NULL DEFAULT '0',
  vreg_lOriginID             int(11)      NOT NULL DEFAULT '0',
  vreg_lLastUpdateID         int(11)      NOT NULL DEFAULT '0',
  vreg_dteOrigin             datetime     NOT NULL DEFAULT '0000-00-00 00:00:00',
  vreg_dteLastUpdate         timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (vreg_lKeyID),
  KEY vreg_strFormName (vreg_strFormName),
  KEY vreg_lVolGroupID (vreg_lVolGroupID),
  KEY vreg_strURLHash  (vreg_strURLHash)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

# [BREAK]

DROP TABLE IF EXISTS vol_reg_skills;
# [BREAK]

CREATE TABLE IF NOT EXISTS vol_reg_skills (
  vrs_lKeyID     int(11) NOT NULL AUTO_INCREMENT,
  vrs_lRegFormID int(11) NOT NULL COMMENT 'Foreign Key to vol_reg',
  vrs_lSkillID   int(11) NOT NULL COMMENT 'Foreign Key to lists_generic/volSkills',
  PRIMARY KEY        (vrs_lKeyID),
  KEY vrs_lRegFormID (vrs_lRegFormID),
  KEY vrs_lSkillID   (vrs_lSkillID)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
# [BREAK]

DROP TABLE IF EXISTS vol_reg_uf;
# [BREAK]

CREATE TABLE IF NOT EXISTS vol_reg_uf (
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
# [BREAK]


DROP TABLE IF EXISTS vol_reg_table_labels;
# [BREAK]

CREATE TABLE IF NOT EXISTS vol_reg_table_labels (
  vrtl_lKeyID     int(11) NOT NULL AUTO_INCREMENT,
  vrtl_lRegFormID int(11) NOT NULL COMMENT 'Foreign Key to vol_reg',
  vrtl_lTableID   int(11) NOT NULL COMMENT 'Foreign Key to uf_tables',
  vrtl_strLabel   varchar(255) NOT NULL DEFAULT '' COMMENT 'Public label for table',

  PRIMARY KEY         (vrtl_lKeyID),
  KEY vrtl_lRegFormID (vrtl_lRegFormID),
  KEY vrtl_lTableID   (vrtl_lTableID)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
# [BREAK]

-- http://ellislab.com/codeigniter%20/user-guide/helpers/captcha_helper.html
DROP TABLE IF EXISTS captcha;
# [BREAK]

CREATE TABLE IF NOT EXISTS captcha (
 captcha_id   bigint(13)  unsigned NOT NULL auto_increment,
 captcha_time int(10)     unsigned NOT NULL,
 ip_address   varchar(16) default '0' NOT NULL,
 word         varchar(20) NOT NULL,
 PRIMARY KEY `captcha_id` (`captcha_id`),
 KEY `word` (`word`)
);
# [BREAK]


#
# TABLE STRUCTURE FOR: vol_skills
#

DROP TABLE IF EXISTS vol_skills;
# [BREAK]

CREATE TABLE `vol_skills` (
  `vs_lKeyID`   int(11) NOT NULL AUTO_INCREMENT,
  `vs_lVolID`   int(11) NOT NULL COMMENT 'Foreign Key to volunteers',
  `vs_lSkillID` int(11) NOT NULL COMMENT 'Foreign Key to lists_generic/volSkills',
  `vs_Notes` text,
  PRIMARY KEY (`vs_lKeyID`),
  KEY `vs_lVolID`   (`vs_lVolID`),
  KEY `vs_lSkillID` (`vs_lSkillID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
# [BREAK]

