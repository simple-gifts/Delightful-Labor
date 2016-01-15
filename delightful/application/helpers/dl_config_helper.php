<?php
//---------------------------------------------------------------------
// Copyright (c) 2005-2015 Database Austin
//
// Delightful Labor!
//
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of the GNU General Public License
//   as published by the Free Software Foundation; either version 2
//   of the License, or (at your option) any later version.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//---------------------------------------------------------------------

global $gstrFName, $gstrLName, $gstrUserName, $gstrSafeName, $gbAdmin, $gbDev,
       $genumDateFormat, $genumMeasurePref;

define('CS_PROGNAME', 'Delightful Labor');
define('PHP_MIN_DL_VERSION', '5.3.0');
define('CB_AAYHF',    false);


   // software and database levels defined in delightful/application/config/config.php
define('CSNG_CODE_VERSION', '0.1');
define('CSNG_DB_VERSION',   '0.1');

define('DL_IMAGEPATH',   base_url().'images');
define('DL_CATALOGPATH', base_url().'catalog');
define('CB_SHOWSQL_ON_ERROR', true);

define('CSNG_CM_TO_INCHES', 0.393701);
define('CSNG_KILOS_TO_LBS', 2.20462);
define('CSTR_UNITS_LB',  'lb');
define('CSTR_UNITS_IN',  'in');
define('CSTR_UNITS_KG',  'kg');
define('CSTR_UNITS_CM',  'cm');

define('CSTR_IMG_CHKYES', '<img src="'.DL_IMAGEPATH.'/misc/check06.gif">');

define('CL_SERVEROFFSET_SEC', 0);   // not implemented

define('CL_DEFAULT_CHAPTERID', 1);   

   //-----------------------------
   // context types
   //-----------------------------
define('CENUM_CONTEXT_ACCOUNT',            'account');
define('CENUM_CONTEXT_AUCTION',            'auction');
define('CENUM_CONTEXT_AUCTIONPACKAGE',     'auctionPackage');
define('CENUM_CONTEXT_AUCTIONITEM',        'auctionItem');
define('CENUM_CONTEXT_BIZ',                'business');
define('CENUM_CONTEXT_BIZCONTACT',         'businessContact');
define('CENUM_CONTEXT_CAMPAIGN',           'campaign');                                           
define('CENUM_CONTEXT_CLIENT',             'client');
define('CENUM_CONTEXT_CPROGRAM',           'clientProgram');
define('CENUM_CONTEXT_CPROGENROLL',        'clientProgramEnrollment');
define('CENUM_CONTEXT_CPROGATTEND',        'clientProgramAttendance');
define('CENUM_CONTEXT_CPREPOST',           'clientPrePostTest');                                           
define('CENUM_CONTEXT_CUSTOMFORM',         'customForm');
define('CENUM_CONTEXT_CUSTOMREPORT',       'customReport');
define('CENUM_CONTEXT_CUSTOMREPORTTERM',   'customReportSearchTerm');
define('CENUM_CONTEXT_GRANTS',             'grants');
define('CENUM_CONTEXT_GRANTPROVIDER',      'grantProvider');
define('CENUM_CONTEXT_GENERIC',            'generic');
define('CENUM_CONTEXT_GIFT',               'gift');
define('CENUM_CONTEXT_GIFTHON',            'honorarium');
define('CENUM_CONTEXT_GIFTMEM',            'memorial');
define('CENUM_CONTEXT_HOUSEHOLD',          'household');
define('CENUM_CONTEXT_INVITEM',            'inventoryItem');
define('CENUM_CONTEXT_LOCATION',           'location');
define('CENUM_CONTEXT_ORGANIZATION',       'organization');
define('CENUM_CONTEXT_PEOPLE',             'people');
define('CENUM_CONTEXT_PTABLE',             'personalizedTable');
define('CENUM_CONTEXT_REMINDER',           'reminder');
define('CENUM_CONTEXT_SPONSORPAY',         'sponsorPayment');
define('CENUM_CONTEXT_SPONSORPACKET',      'sponsorPacket');
define('CENUM_CONTEXT_SPONSORSHIP',        'sponsorship');
define('CENUM_CONTEXT_STATUSCAT',          'statusCategory');
define('CENUM_CONTEXT_STAFF',              'staff');
define('CENUM_CONTEXT_STAFF_TS_LOCATIONS', 'staffTSLocation');
define('CENUM_CONTEXT_STAFF_TS_PROJECTS',  'staffTSProject');
define('CENUM_CONTEXT_USER',               'user');
define('CENUM_CONTEXT_VOLUNTEER',          'volunteer');

   // image context
define('CENUM_CONTEXT_IMG_AUCTION',        'imgAuction');
define('CENUM_CONTEXT_IMG_AUCTIONPACKAGE', 'imgAuctionPackage');
define('CENUM_CONTEXT_IMG_AUCTIONITEM',    'imgAuctionItem');
define('CENUM_CONTEXT_IMG_BIZ',            'imgBiz');
define('CENUM_CONTEXT_IMG_CLIENT',         'imgClient');
define('CENUM_CONTEXT_IMG_CLIENTLOCATION', 'imgClientLocation');
define('CENUM_CONTEXT_IMG_GRANTS',         'imgGrants');
define('CENUM_CONTEXT_IMG_GRANTPROVIDER',  'imgGrantProvider');
define('CENUM_CONTEXT_IMG_INVITEM',        'imgInventory');
define('CENUM_CONTEXT_IMG_ORGANIZATION',   'imgOrganization');
define('CENUM_CONTEXT_IMG_PEOPLE',         'imgPeople');
define('CENUM_CONTEXT_IMG_SPONSOR',        'imgSponsor');
define('CENUM_CONTEXT_IMG_STAFF',          'imgStaff');
define('CENUM_CONTEXT_IMG_VOLUNTEER',      'imgVolunteer');

   // document context
define('CENUM_CONTEXT_DOC_AUCTION',        'docAuction');
define('CENUM_CONTEXT_DOC_AUCTIONPACKAGE', 'docAuctionPackage');
define('CENUM_CONTEXT_DOC_AUCTIONITEM',    'docAuctionItem');
define('CENUM_CONTEXT_DOC_BIZ',            'docBiz');
define('CENUM_CONTEXT_DOC_CLIENT',         'docClient');
define('CENUM_CONTEXT_DOC_CLIENTLOCATION', 'docClientLocation');
define('CENUM_CONTEXT_DOC_GRANTS',         'docGrants');
define('CENUM_CONTEXT_DOC_GRANTPROVIDER',  'docGrantProvider');
define('CENUM_CONTEXT_DOC_INVITEM',        'docInventory');
define('CENUM_CONTEXT_DOC_ORGANIZATION',   'docOrganization');
define('CENUM_CONTEXT_DOC_PEOPLE',         'docPeople');
define('CENUM_CONTEXT_DOC_SPONSOR',        'docSponsor');
define('CENUM_CONTEXT_DOC_STAFF',          'docStaff');
define('CENUM_CONTEXT_DOC_VOLUNTEER',      'docVolunteer');

   //-----------------------------
   // user field types
   //-----------------------------
define('CS_FT_CHECKBOX'   , 'Checkbox');
define('CS_FT_DATE'       , 'Date');
define('CS_FT_DATETIME'   , 'DateTime');  // depricated
define('CS_FT_DECIMAL'    , 'Decimal');  
define('CS_FT_TEXT255'    , 'Text255');
define('CS_FT_TEXT80'     , 'Text80');
define('CS_FT_TEXT20'     , 'Text20');
define('CS_FT_TEXTLONG'   , 'TextLong');
define('CS_FT_TEXT'       , 'TextGeneric');
define('CS_FT_INTEGER'    , 'Integer');
define('CS_FT_ID'         , 'ID');
define('CS_FT_CLIENTID'   , 'clientID');
define('CS_FT_CURRENCY'   , 'Currency');
define('CS_FT_DDL'        , 'DDL');
define('CS_FT_DDLMULTI'   , 'DDLMulti');
define('CS_FT_LOG'        , 'Log');
define('CS_FT_HEADING'    , 'Heading');
define('CS_FT_ENUM'       , 'Enumerated');
define('CS_FT_DDL_SPECIAL', 'Special DDL');     // the software will handle fields marked as special cases



   //-----------------------------
   // generic list types
   //-----------------------------
define('CENUM_LISTTYPE_ATTRIB',        'attrib');
define('CENUM_LISTTYPE_BIZCAT',        'bizCat');
define('CENUM_LISTTYPE_BIZCONTACTREL', 'bizContactRel');
define('CENUM_LISTTYPE_CAMPEXPENSE',   'campaignExpense');
define('CENUM_LISTTYPE_GIFTPAYTYPE',   'giftPayType');
define('CENUM_LISTTYPE_INKIND',        'inKind');
define('CENUM_LISTTYPE_MAJORGIFTCAT',  'majorGiftCats');
define('CENUM_LISTTYPE_VOLJOBCAT',     'volJobCat');
define('CENUM_LISTTYPE_VOLACT',        'volActivities');
define('CENUM_LISTTYPE_VOLSKILLS',     'volSkills');
define('CENUM_LISTTYPE_VOLJOBCODES',   'volShiftJobCodes');
define('CENUM_LISTTYPE_SPONTERMCAT',   'sponTermCat');
define('CENUM_LISTTYPE_CPREPOSTCAT',   'prePostTestCat');
define('CENUM_LISTTYPE_USERTABLE',     'personalizedTable');

//define('CENUM_LISTTYPE_TS_LOCATIONS',  'timeSheetLocations');

   //-----------------------------
   // reports
   //-----------------------------
define('CENUM_REPORTTYPE_PREDEFINED',     'predefined');
define('CENUM_REPORTTYPE_CUSTOM',         'custom');
define('CENUM_REPORTTYPE_DATAHIDDEN',     'dataHidden');
                                          
define('CENUM_REPORTDEST_SCREEN',         'screen');
define('CENUM_REPORTTYPE_EXPORT',         'export');
                                          
define('CENUM_REPORTNAME_GROUP',              1);
define('CENUM_REPORTNAME_VOLJOBSKILL',        2);
define('CENUM_REPORTNAME_VOLHOURS',           3);
define('CENUM_REPORTNAME_VOLHOURSDETAIL',     4);
define('CENUM_REPORTNAME_VOLHOURSVIAVID',     5);
define('CENUM_REPORTNAME_VOLHOURSCHEDULE',    6);
define('CENUM_REPORTNAME_SPONPASTDUE',        7);
define('CENUM_REPORTNAME_GIFTACK',            8);
define('CENUM_REPORTNAME_GIFTRECENT',         9);
define('CENUM_REPORTNAME_GIFTTIMEFRAME',     10);
define('CENUM_REPORTNAME_GIFTACCOUNT',       11);
define('CENUM_REPORTNAME_GIFTCAMP',          12);
define('CENUM_REPORTNAME_GIFTYEAREND',       13);
define('CENUM_REPORTNAME_GIFTAGG',           14);
define('CENUM_REPORTNAME_GIFTSPONAGG',       15);
define('CENUM_REPORTNAME_SPONVIAPROG',       16);
define('CENUM_REPORTNAME_SPONVIALOCID',      17);
define('CENUM_REPORTNAME_ADMINUSAGE',        18);
define('CENUM_REPORTNAME_ADMINUSERLOGIN',    19);
define('CENUM_REPORTNAME_ATTRIB',            20);
define('CENUM_REPORTNAME_SPONINCOMEMONTH',   21);
define('CENUM_REPORTNAME_SPONWOCLIENT',      22);
define('CENUM_REPORTNAME_CLIENTAGE',         23);
define('CENUM_REPORTNAME_SPONCHARGEMONTH',   24);
define('CENUM_REPORTNAME_HONMEMGIFTLIST',    25);
define('CENUM_REPORTNAME_VOLEVENTSCHEDULE',  26);
define('CENUM_REPORTNAME_CLIENTBDAY',        27);
define('CENUM_REPORTNAME_VOLHRS_PVA',        28);
define('CENUM_REPORTNAME_VOLHRSDETAIL_PVA',  29);
define('CENUM_REPORTNAME_DEPOSITLOG',        30);
define('CENUM_REPORTNAME_DEPOSITENTRY',      31);
define('CENUM_REPORTNAME_CLIENTVIASTATUS',   32);
define('CENUM_REPORTNAME_CLIENTVIASTATCAT',  33);
define('CENUM_REPORTNAME_VOL_HRS_YEAR',      34);
define('CENUM_REPORTNAME_VOL_HRS_MON',       35);
define('CENUM_REPORTNAME_VOL_HRS_SUM',       36);
define('CENUM_REPORTNAME_VOL_HRS_DETAIL',    37);
define('CENUM_REPORTNAME_CLIENT_PREPOST',    38);
define('CENUM_REPORTNAME_CLIENT_PREPOSTDIR', 39);
define('CENUM_REPORTNAME_DATAENTRYLOG',      40);
define('CENUM_REPORTNAME_CPROG_ENROLLEES',   41);
define('CENUM_REPORTNAME_VOL_JOBCODE_YEAR',  42);
define('CENUM_REPORTNAME_VOL_JOBCODE_MONTH', 43);
define('CENUM_REPORTNAME_VOLHOURSTFSUM',     44);

   // custom reports
define('CE_CRPT_GIFTS',           'gifts');
define('CE_CRPT_GIFTS_HON',       'gifts/hon');
define('CE_CRPT_GIFTS_MEM',       'gifts/mem');
define('CE_CRPT_GIFTS_PER',       'gifts/per');
define('CE_CRPT_PEOPLE',          'people');
define('CE_CRPT_BIZ',             'biz');
define('CE_CRPT_VOLUNTEER',       'volunteer');
define('CE_CRPT_CLIENTS',         'clients');
define('CE_CRPT_CLIENTS_CPROG',   'clients/cprog');
define('CE_CRPT_CLIENTS_SPON',    'clients/spon');
define('CE_CRPT_CLIENTS_SPONPAY', 'clients/spon/pay');

   // silent auction bid templates
define('CENUM_BSTEMPLATE_SIMPLEPACK',    1);  // simple template - package
define('CENUM_BSTEMPLATE_PACKAGEPIC',    2);  // simple template - package picture
define('CENUM_BSTEMPLATE_MIN',           3);  // minimalist
define('CENUM_BSTEMPLATE_ITEMS',         4);  // items

   // PDF paper sizes
define('CENUM_PDFPSIZE_LETTER',   'Letter');  
define('CENUM_PDFPSIZE_LEGAL',    'Legal');  
define('CENUM_PDFPSIZE_A3',       'A3');  
define('CENUM_PDFPSIZE_A4',       'A4');  
define('CENUM_PDFPSIZE_A5',       'A5');  

   //-----------------------------
   // image/doc entry types
   //-----------------------------
define('CENUM_IMGDOC_ENTRY_IMAGE',    'image');
define('CENUM_IMGDOC_ENTRY_PDF',      'pdf');
define('CENUM_IMGDOC_ENTRY_UNKNOWN',  'Unknown');

define('CI_IMGDOC_RESIZE_MAXDIMENSION',  1200);
define('CI_IMGDOC_THUMB_MAXDIMENSION',    120);
define('CSTR_IMAGE_LIBRARY',            'gd2');
define('CI_IMGDOC_MAXUPLOADKB',         10000);   // max image/doc upload size in kb (i.e. 10000 = 10mb)
define('CI_IMPORT_MAXUPLOADKB',         10000);   // max import upload size in kb (i.e. 1000 = 1mb)

define('CS_CSV_DELIMITER', ',');
define('CS_CSV_NEWLINE',   "\r\n");


?>
