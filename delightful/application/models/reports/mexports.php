<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('reports/mexports', 'clsExports');
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mexports extends CI_Model{
   public $sRpt, $strPeopleWhere, $strBizWhere, $strClientWhere,
          $strSponsorshipWhere, $strGiftWhere;


   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->strPeopleWhere      = $this->strBizWhere  = $this->strClientWhere =
      $this->strSponsorshipWhere = $this->strGiftWhere = '';
   }

   public function exportCReport($sqlStr){
   //---------------------------------------------------------------------
   // 
   //---------------------------------------------------------------------      
      $query = $this->db->query($sqlStr);
//      $delimiter = ',';
//      $newline = "\r\n";
      return($this->dbutil->csv_from_result($query, CS_CSV_DELIMITER, CS_CSV_NEWLINE));
   }
   
   public function strExportPeople(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);
      $strDateTimeFormat = strMysqlDateFormat(true);

      $sqlStr =
         'SELECT '.strExportFields_People().",
             peepTab.pe_strPreferredName   AS `Preferred Name`,
             peepTab.pe_strSalutation      AS Salutation,
             peepTab.pe_strImportID        AS `Import ID`,
             peepTab.pe_lACO               AS `Accounting Country ID`,
             aco_strName                   AS `Accounting Country`,
             aco_strCurrencySymbol         AS `Currency Symbol`,
             lgen_strListItem              AS `Attributed To`,
             peepTab.pe_lHouseholdID       AS `Household ID`,
             CONCAT('The ', houseTab.pe_strFName,' ', houseTab.pe_strLName, ' Household') AS Household,

             DATE_FORMAT(peepTab.pe_dteBirthDate,  $strDateFormat) AS `Birth Date`,
             DATE_FORMAT(peepTab.pe_dteOrigin,     $strDateTimeFormat) AS `Record Creation Date`,
             DATE_FORMAT(peepTab.pe_dteLastUpdate, $strDateTimeFormat) AS `Record Last Update`

          FROM people_names AS peepTab
             INNER JOIN people_names AS houseTab ON peepTab.pe_lHouseholdID=houseTab.pe_lKeyID
             INNER JOIN admin_aco     ON peepTab.pe_lACO=aco_lKeyID
             LEFT  JOIN lists_generic ON peepTab.pe_lAttributedTo=lgen_lKeyID
          WHERE NOT peepTab.pe_bRetired
             AND NOT peepTab.pe_bBiz
             $this->strPeopleWhere
          ORDER BY peepTab.pe_lKeyID;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query, CS_CSV_DELIMITER, CS_CSV_NEWLINE));
   }

   public function strExportBiz(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);
      $strDateTimeFormat = strMysqlDateFormat(true);

      $sqlStr =
         'SELECT '.strExportFields_Biz().",

             bizTab.pe_strImportID        AS `Import ID`,
             bizTab.pe_lACO               AS `Accounting Country ID`,
             aco_strName                  AS `Accounting Country`,
             aco_strCurrencySymbol        AS `Currency Symbol`,
             listAttrib.lgen_strListItem  AS `Attributed To`,
             listBizCat.lgen_strListItem  AS `Business Category`,
             bizTab.pe_lBizIndustryID     AS `Business Industry ID`,

             DATE_FORMAT(bizTab.pe_dteOrigin,     $strDateTimeFormat) AS `Record Creation Date`,
             DATE_FORMAT(bizTab.pe_dteLastUpdate, $strDateTimeFormat) AS `Record Last Update`

          FROM people_names AS bizTab
             INNER JOIN admin_aco     ON bizTab.pe_lACO=aco_lKeyID
             LEFT  JOIN lists_generic AS listAttrib ON bizTab.pe_lAttributedTo  = listAttrib.lgen_lKeyID
             LEFT  JOIN lists_generic AS listBizCat ON bizTab.pe_lBizIndustryID = listBizCat.lgen_lKeyID
          WHERE
             NOT bizTab.pe_bRetired
             $this->strBizWhere
             AND bizTab.pe_bBiz
          ORDER BY bizTab.pe_lKeyID;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   public function strExportBizCon(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);
      $strDateTimeFormat = strMysqlDateFormat(true);

      $sqlStr =
         "SELECT
            bc_lKeyID           AS `contactID`,
            bc_lBizID           AS `businessID`,
            bizTab.pe_strLName  AS `Business Name`,
            bc_lContactID       AS `peopleID`,
            peepTab.pe_strLName AS `Last Name`,
            peepTab.pe_strFName AS `First Name`,
            peepTab.pe_strMName AS `Middle Name`,
            bc_lBizContactRelID AS `relationshipID`,
            listBizRelCat.lgen_strListItem  AS `Business Relationship`,

            IF (bc_bSoftCash, 'Yes', 'No') AS `Soft Cash Relationship`,
            DATE_FORMAT(bc_dteOrigin,     $strDateTimeFormat) AS `Record Creation Date`,
            DATE_FORMAT(bc_dteLastUpdate, $strDateTimeFormat) AS `Record Last Update`

         FROM biz_contacts
            INNER JOIN people_names AS peepTab ON bc_lContactID = peepTab.pe_lKeyID
            INNER JOIN people_names AS bizTab  ON bc_lBizID     = bizTab.pe_lKeyID
            LEFT  JOIN lists_generic AS listBizRelCat ON bc_lBizContactRelID=listBizRelCat.lgen_lKeyID

          WHERE NOT bc_bRetired
             AND NOT bizTab.pe_bRetired
          ORDER BY bc_lKeyID;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   public function strExportHonMem($bHon){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);
      $strDateTimeFormat = strMysqlDateFormat(true);
      $strLabel          = $bHon ? 'Honorarium' : 'Memorial';
      $sqlStr =
        "SELECT
            gi_lKeyID                    AS giftID,
            ghml_lKeyID                  AS honMemLinkID,

            ghml_lHonMemID               AS honMemListID,
            IF(ghm_bHon, 'Honorarium',
                         'Memorial')     AS `Honorarim/Memorial`,
            ghm_lFID                     AS `$strLabel peopleID`,
            peepHM.pe_strFName           AS `$strLabel First Name`,
            peepHM.pe_strLName           AS `$strLabel Last Name`,

            ghm_lMailContactID           AS `Mail Contact peopleID`,
            peepMC.pe_strFName           AS `Mail Contact First Name`,
            peepMC.pe_strLName           AS `Mail Contact Last Name`,

            IF((peepDonor.pe_bBiz*gi_lForeignID)=0, null, gi_lForeignID) AS `Donor businessID`,
            IF((peepDonor.pe_bBiz*gi_lForeignID)=0, gi_lForeignID, null) AS `Donor peopleID`,
            IF (peepDonor.pe_bBiz, peepDonor.pe_strLName, null) AS `Donor Business Name`,
            IF (peepDonor.pe_bBiz, null, peepDonor.pe_strLName) AS `Donor Last Name`,
            IF (peepDonor.pe_bBiz, null, peepDonor.pe_strFName) AS `Donor First Name`,
            FORMAT(gi_curAmnt, 2) AS Amount,
            DATE_FORMAT(gi_dteDonation,    $strDateTimeFormat) AS `Date of Donation`,
            gi_lACOID             AS `Accounting Country ID`,
            aco_strName           AS `Accounting Country`,

            ghml_strNote                 AS `$strLabel Note`,
            IF(ghml_bAck, 'Yes', 'No')   AS Acknowledged,

            DATE_FORMAT(ghml_dteAck, $strDateTimeFormat) AS `Date $strLabel Acknowledged`,

            IF(ghm_bHidden, 'Yes', 'No') AS `Hidden?`,
            ghml_lAckByID                AS `Acknowledged By userID`,
            CONCAT(ackUser.us_strFirstName, ' ', ackUser.us_strLastName) AS `Acknowledged By`

         FROM `gifts_hon_mem_links`
            INNER JOIN `lists_hon_mem`             ON ghm_lKeyID = ghml_lHonMemID
            INNER JOIN gifts                       ON gi_lKeyID  = ghml_lGiftID
            INNER JOIN admin_aco                   ON gi_lACOID  = aco_lKeyID
            INNER JOIN people_names AS peepDonor   ON gi_lForeignID      = peepDonor.pe_lKeyID
            INNER JOIN people_names AS peepHM      ON ghm_lFID           = peepHM.pe_lKeyID
            LEFT  JOIN people_names AS peepMC      ON ghm_lMailContactID = peepMC.pe_lKeyID

            LEFT JOIN admin_users AS ackUser       ON ackUser.us_lKeyID=ghml_lAckByID

         WHERE NOT gi_bRetired
            AND ".($bHon ? '' : 'NOT')." ghm_bHon
         ORDER BY gi_lKeyID;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   public function strExportGift(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);
      $strDateTimeFormat = strMysqlDateFormat(true);
      $sqlStr =
        "SELECT
            gi_lKeyID AS giftID,
            IF((pe_bBiz*gi_lForeignID)=0, null, gi_lForeignID) AS businessID,
            IF((pe_bBiz*gi_lForeignID)=0, gi_lForeignID, null) AS peopleID,
            IF (pe_bBiz, pe_strLName, null) AS `Business Name`,
            IF (pe_bBiz, null, pe_strLName) AS `Last Name`,
            IF (pe_bBiz, null, pe_strFName) AS `First Name`,
            IF (gi_lSponsorID IS NULL , 'No', 'Yes') AS `Sponsorship Payment`,
            gi_lSponsorID         AS sponsorID,

            gi_lDepositLogID      AS `Deposit ID`,

            ga_lKeyID             AS accountID,
            ga_strAccount         AS Account,

            gi_lCampID            AS campaignID,
            gc_strCampaign        AS Campaign,

            FORMAT(gi_curAmnt, 2) AS Amount,
            gi_lACOID             AS `Accounting Country ID`,
            aco_strName           AS `Accounting Country`,
            DATE_FORMAT(gi_dteDonation, $strDateFormat) AS `Date of Donation`,
            listAttrib.lgen_strListItem   AS `Attributed To`,
            IF (gi_bGIK, 'Yes', 'No')      AS `In Kind Donation`,
            IF (gi_bGIK, gi_lGIK_ID, NULL) AS inKindID,
            IF (gi_bGIK, listInKind.lgen_strListItem, NULL) AS `In-Kind Type`,
            gi_strImportID                AS importID,
            gi_strNotes                   AS Notes,
            gi_strCheckNum                AS `Check Number`,
            gi_lPaymentType               AS paymentTypeID,
            listPayType.lgen_strListItem  AS `Payment Type`,
            gi_lMajorGiftCat              AS giftCategoryID,
            listGiftCat.lgen_strListItem  AS `Major Gift Category`,

            IF (gi_bAck, 'Yes', 'No') AS `Acknowledged?`,
            IF (gi_bAck, DATE_FORMAT(gi_dteAck, $strDateTimeFormat), '') AS `Date Acknowledged`,

            DATE_FORMAT(gi_dteOrigin,     $strDateTimeFormat) AS `Record Creation Date`,
            DATE_FORMAT(gi_dteLastUpdate, $strDateTimeFormat) AS `Record Last Update`

         FROM gifts
            INNER JOIN people_names    ON gi_lForeignID = pe_lKeyID
            INNER JOIN admin_aco       ON gi_lACOID     = aco_lKeyID
            INNER JOIN gifts_campaigns ON gi_lCampID=gc_lKeyID
            INNER JOIN gifts_accounts  ON ga_lKeyID=gc_lAcctID
            LEFT  JOIN lists_generic AS listAttrib  ON gi_lAttributedTo = listAttrib.lgen_lKeyID
            LEFT  JOIN lists_generic AS listInKind  ON gi_lGIK_ID       = listInKind.lgen_lKeyID
            LEFT  JOIN lists_generic AS listPayType ON gi_lPaymentType  = listPayType.lgen_lKeyID
            LEFT  JOIN lists_generic AS listGiftCat ON gi_lMajorGiftCat = listGiftCat.lgen_lKeyID

         WHERE NOT gi_bRetired
             $this->strGiftWhere
         ORDER BY gi_lKeyID;";

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   public function strExportSpon(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);
      $strDateTimeFormat = strMysqlDateFormat(true);
      $sqlStr =
         'SELECT '.strExportFields_Sponsor().",
            DATE_FORMAT(sp_dteOrigin,     $strDateTimeFormat) AS `Record Creation Date`,
            DATE_FORMAT(sp_dteLastUpdate, $strDateTimeFormat) AS `Record Last Update`
          FROM sponsor
             INNER JOIN people_names AS sponpeep   ON sponpeep.pe_lKeyID   = sp_lForeignID
             INNER JOIN lists_sponsorship_programs ON sp_lSponsorProgramID = sc_lKeyID
             INNER JOIN admin_aco AS commitACO     ON commitACO.aco_lKeyID = sp_lCommitmentACO
             LEFT  JOIN client_records             ON cr_lKeyID            = sp_lClientID
             LEFT  JOIN client_location            ON cr_lLocationID       = cl_lKeyID
             LEFT  JOIN lists_client_vocab         ON cr_lVocID            = cv_lKeyID
             LEFT  JOIN people_names AS honpeep    ON honpeep.pe_lKeyID    = sp_lHonoreeID
             LEFT  JOIN lists_generic AS atab      ON sp_lAttributedTo     = lgen_lKeyID
          WHERE NOT sponpeep.pe_bRetired
             AND NOT sp_bRetired
             $this->strSponsorshipWhere
          ORDER BY sp_lKeyID;";

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   public function strExportSponPacket(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterVoc;

      $strDateFormat     = strMysqlDateFormat(false);
      $strTmpTable = 'tmpPacket';
      $this->tmpTableSponPacket($strTmpTable);

      $sqlStr =
         "SELECT
             tmp_lSponID    AS `Sponsorship ID`,
             tmp_lContactID AS `Contact People ID`,
             sp_lForeignID  AS `Sponsor People ID`,
             tmp_lClientID  AS `Client ID`,
             IF(tmp_bHonoree, 'Yes', 'No')     AS `Contact Is Honoree`,
             IF(sponpeep.pe_bBiz, 'Business', 'Person') AS `Sponsor Type`,

             sponcontact.pe_strLName   AS `Contact Last Name`,  sponcontact.pe_strFName AS `Contact First Name`,
             sponcontact.pe_strAddr1   AS `Contact Address 1`,  sponcontact.pe_strAddr2 AS `Contact Address 2`,
             sponcontact.pe_strCity    AS `Contact City`,       sponcontact.pe_strState AS `Contact $gclsChapterVoc->vocState`,
             sponcontact.pe_strCountry AS `Contact Country`,    sponcontact.pe_strZip   AS `Contact $gclsChapterVoc->vocZip`,
             sponcontact.pe_strPhone   AS `Contact Phone`,
             sponcontact.pe_strCell    AS `Contact Cell`,
             sponcontact.pe_strEmail   AS `Contact Email`,

             sponpeep.pe_strLName   AS `Sponsor Last Name`,  sponpeep.pe_strFName AS `Sponsor First Name`,
             sponpeep.pe_strAddr1   AS `Sponsor Address 1`,  sponpeep.pe_strAddr2 AS `Sponsor Address 2`,
             sponpeep.pe_strCity    AS `Sponsor City`,       sponpeep.pe_strState AS `Sponsor $gclsChapterVoc->vocState`,
             sponpeep.pe_strCountry AS `Sponsor Country`,    sponpeep.pe_strZip   AS `Sponsor $gclsChapterVoc->vocZip`,
             sponpeep.pe_strPhone   AS `Sponsor Phone`,
             sponpeep.pe_strCell    AS `Sponsor Cell`,
             sponpeep.pe_strEmail   AS `Sponsor Email`,

             cr_strFName           AS `Client First Name`,
             cr_strLName           AS `Client Last Name`,
             cr_enumGender         AS `Client Gender`,
             cr_lLocationID        AS `Client Location ID`,
             cl_strLocation        AS `Client Location`,
             cl_strCountry         AS `Location Country`,
             cl_strAddress1        AS `Location Address 1`,
             cl_strAddress2        AS `Location Address 2`,
             cl_strCity            AS `Location City`, 
             cl_strState           AS `Location $gclsChapterVoc->vocState`,
             cl_strPostalCode      AS `Location $gclsChapterVoc->vocZip`,

             DATE_FORMAT(cr_dteEnrollment, $strDateFormat) AS `Client Enrollment Date`,
             DATE_FORMAT(cr_dteBirth,      $strDateFormat) AS `Client Birth Date`,

             csh_lKeyID    AS `Status Entry ID`,
             csh_lStatusID AS `Status ID`,
             DATE_FORMAT(csh_dteStatusDate,     $strDateFormat) AS `Date of Status Entry`,

             cst_strStatus     AS `Status`,
             csh_strStatusTxt  AS `Status Text`,
             csc_strCatName    AS `Status Category`,

             cr_lVocID         AS `Vocabulary ID`,
             cv_strVocTitle    AS `Vocabulary Title`,
             cv_strVocClientS  AS `Voc: Client (singular)`,
             cv_strVocClientP  AS `Voc: Client (plural)`,
             cv_strVocSponsorS AS `Voc: Sponsor (singular)`,
             cv_strVocSponsorP AS `Voc: Sponsor (plural)`,
             cv_strVocLocS     AS `Voc: Location (singular)`,
             cv_strVocLocP     AS `Voc: Location (plural)`,
             cv_strVocSubLocS  AS `Voc: Sub-location (singular)`,
             cv_strVocSubLocP  AS `Voc: Sub-location (plural)`

          FROM $strTmpTable
             INNER JOIN sponsor ON tmp_lSponID=sp_lKeyID
             INNER JOIN people_names AS sponcontact   ON sponcontact.pe_lKeyID   = tmp_lContactID
             INNER JOIN people_names AS sponpeep      ON sponpeep.pe_lKeyID      = sp_lForeignID
             INNER JOIN lists_sponsorship_programs    ON sp_lSponsorProgramID    = sc_lKeyID
             INNER JOIN admin_aco AS commitACO        ON commitACO.aco_lKeyID    = sp_lCommitmentACO
             INNER JOIN client_records                ON cr_lKeyID               = sp_lClientID
             INNER JOIN client_location               ON cr_lLocationID          = cl_lKeyID
             INNER JOIN lists_client_vocab            ON cr_lVocID               = cv_lKeyID
             LEFT  JOIN people_names AS honpeep       ON honpeep.pe_lKeyID       = sp_lHonoreeID
             LEFT  JOIN client_status                 ON tmp_lStatusID           = csh_lKeyID
             LEFT  JOIN lists_client_status_entries   ON csh_lStatusID           = cst_lKeyID
             LEFT  JOIN client_status_cats            ON cst_lClientStatusCatID  = csc_lKeyID
          WHERE NOT sponpeep.pe_bRetired
             AND NOT sp_bRetired
          ORDER BY sp_lKeyID;";

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   private function tmpTableSponPacket($strTableName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlDelete = "DROP TABLE IF EXISTS $strTableName ;";
      $this->db->query($sqlDelete);
      $sqlStr = "
              CREATE TEMPORARY TABLE $strTableName(
                 tmp_lKeyID     int(11) NOT NULL AUTO_INCREMENT,
                 tmp_lSponID    int(11) NOT NULL DEFAULT '0',
                 tmp_lContactID int(11) NOT NULL DEFAULT '0',
                 tmp_lClientID  int(11) NOT NULL DEFAULT '0',
                 tmp_bHonoree   tinyint(1) NOT NULL DEFAULT '0',
                 tmp_lStatusID  int(11) DEFAULT NULL,
              PRIMARY KEY        (tmp_lKeyID),
              KEY tmp_lSponID    (tmp_lSponID),
              KEY tmp_lContactID (tmp_lContactID),
              KEY tmp_lStatusID  (tmp_lStatusID)
            ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

      $this->db->query($sqlStr);

         //------------------------------------------------------------
         // load all active sponsorships associated with clients
         //------------------------------------------------------------
      $sqlStr =
         "INSERT INTO $strTableName
             (
                tmp_lSponID,
                tmp_lContactID,
                tmp_lClientID,
                tmp_bHonoree
             )
             SELECT
                sp_lKeyID,
                IFNULL(sp_lHonoreeID, sp_lForeignID) AS lContactID,
                sp_lClientID,
                 IF(sp_lHonoreeID IS NULL, 0, 1)
             FROM sponsor
             WHERE NOT sp_bInactive
                AND NOT sp_bRetired
                AND sp_lClientID IS NOT NULL
                ORDER BY sp_lKeyID;";

      $this->db->query($sqlStr);

         //------------------------------------------------------------
         // link the packet status
         //------------------------------------------------------------
      $status = new mclient_status;
      $sqlStr = "SELECT tmp_lKeyID, tmp_lClientID FROM $strTableName;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() > 0){
         foreach ($query->result() as $row){
            $lTempID   = $row->tmp_lKeyID;
            $lClientID = $row->tmp_lClientID;
            $packet = $status->packetStatusInfo($lClientID);
            if (is_null($packet)){
               $lStatID = 'null';
            }else {
               $lStatID = $packet->statusID;
            }
            $this->addTmpStatusID($strTableName, $lTempID, $lStatID);
         }
      }

   }

   private function addTmpStatusID($strTableName, $lTempID, $lStatID){
      $sqlStr =
         "UPDATE $strTableName
          SET tmp_lStatusID=$lStatID
          WHERE tmp_lKeyID=$lTempID;";
      $this->db->query($sqlStr);
   }

   public function strExportClients(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);
      $strDateTimeFormat = strMysqlDateFormat(true);
      $sqlStr =
         'SELECT '.strExportFields_Client()."
          FROM client_records
             INNER JOIN client_location             ON cr_lLocationID         = cl_lKeyID
             INNER JOIN client_status_cats          ON cr_lStatusCatID        = csc_lKeyID
             INNER JOIN lists_client_vocab          ON cr_lVocID              = cv_lKeyID
             INNER JOIN client_status               ON csh_lClientID          = cr_lKeyID
             INNER JOIN lists_client_status_entries ON csh_lStatusID          = cst_lKeyID
             LEFT  JOIN lists_generic               ON cr_lAttributedTo       = lgen_lKeyID

          --   INNER JOIN client_status_cats          ON cst_lClientStatusCatID = curStatSCat.csc_lKeyID
          --   INNER JOIN client_status_cats AS defSCat      ON cr_lStatusCatID        = defSCat.csc_lKeyID

         WHERE NOT cr_bRetired
             $this->strClientWhere
                 -- ---------------------------------------
                 -- subquery to find most current status
                 -- ---------------------------------------
               AND csh_lKeyID=(SELECT csh_lKeyID
                               FROM client_status
                               WHERE csh_lClientID=cr_lKeyID
                                  AND NOT csh_bRetired
                               ORDER BY csh_dteStatusDate DESC, csh_lKeyID DESC
                               LIMIT 0,1)


          ORDER BY cr_lKeyID;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   public function strExportUserTable($lTableID){
      $cUF = new muser_fields;
      $cUF->lTableID = $lTableID;
      $cUF->loadTableViaTableID();

      $lNumFields = $cUF->lNumUF_TableFields($lTableID);

      if ($lNumFields == 0) return('');

      $cUF->loadTableFields();
      $enumTType = $cUF->userTables[0]->enumTType;
      $strTmpTable = 'tmpUFExport';
      $sqlStr = $this->strCreateTempTableUFExport($cUF->fields, $strTmpTable, $sqlDelete, $strFieldNamesSel);

      $this->db->query($sqlDelete);
      $this->db->query($sqlStr);

         //------------------------------------------------
         // populate the temp table with personalized data
         //------------------------------------------------
      $this->strPopulateUFTempData($cUF, $lTableID, $strTmpTable, $enumTType, $cUF->fields);

         //---------------------------
         // tie it all together
         //---------------------------
      $sqlStr = $this->strTmpTableJoinedToContext($enumTType,      $strTmpTable, $strFieldNamesSel,
                                                 'tmp_lForeignID', true,         '',
                                                 '');
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   private function strSelectExtraViaContext($enumTType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      switch ($enumTType){
         case CENUM_CONTEXT_PEOPLE:
            $strOut = '
                          pe_lKeyID   AS `peopleID`,
                          pe_strLName AS `Last Name`,
                          pe_strFName AS `First Name` ';
            break;

         case CENUM_CONTEXT_BIZ:
            $strOut = '
                          pe_lKeyID   AS `businessID`,
                          pe_strLName AS `Business Name` ';
            break;

         case CENUM_CONTEXT_CLIENT:
            $strOut = '
                          cr_lKeyID AS `clientID`,
                          cr_strLName AS `Client Last Name`,
                          cr_strFName AS `Client First Name` ';
            break;

         case CENUM_CONTEXT_LOCATION:
            $strOut = '
                          cl_lKeyID      AS locationID,
                          cl_strLocation AS `Location`,
                          cl_strCountry  AS `Country` ';
            break;

         case CENUM_CONTEXT_GIFT:
            $strOut = "
                           gi_lKeyID AS giftID,
                           IF((pe_bBiz*gi_lForeignID)=0, null, gi_lForeignID) AS businessID,
                           IF((pe_bBiz*gi_lForeignID)=0, gi_lForeignID, null) AS peopleID,
                           IF (pe_bBiz, pe_strLName, null) AS `Business Name`,
                           IF (pe_bBiz, null, pe_strLName) AS `Last Name`,
                           IF (pe_bBiz, null, pe_strFName) AS `First Name`,
                           IF (gi_lSponsorID IS NULL , 'No', 'Yes') AS `Sponsorship Payment`,
                           gi_lSponsorID         AS sponsorID,

                           FORMAT(gi_curAmnt, 2) AS Amount,
                           gi_lACOID             AS `Accounting Country ID`,
                           aco_strName           AS `Accounting Country` ";
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $strOut = "
                          sp_lKeyID AS `sponsorID`,
                          IF(pe_bBiz, 'Business', 'Person') AS `Sponsor Type`,
                          IF(sp_bInactive, 'No', 'Yes')     AS `Sponsorship Active`,
                          IF((pe_bBiz*pe_lKeyID)=0, null, pe_lKeyID) AS businessID,
                          IF((pe_bBiz*pe_lKeyID)=0, pe_lKeyID, null) AS peopleID,
                          IF (pe_bBiz, pe_strLName, null) AS `Business Name`,
                          IF (pe_bBiz, null, pe_strLName) AS `Last Name`,
                          IF (pe_bBiz, null, pe_strFName) AS `First Name`,
                          cr_lKeyID   AS clientID,
                          cr_strFName AS `Client First Name`,
                          cr_strLName AS `Client Last Name` ";
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            $strOut = "
                          vol_lKeyID AS volunteerID,
                          vol_lPeopleID AS peopleID,
                          IF(vol_bInactive, 'No', 'Yes') AS `Active?`,
                          pe_strLName AS `Last Name`,
                          pe_strFName AS `First Name` ";
            break;

         case CENUM_CONTEXT_BIZCONTACT:
         case CENUM_CONTEXT_GENERIC:
         case CENUM_CONTEXT_HOUSEHOLD:
         case CENUM_CONTEXT_STAFF:
         case CENUM_CONTEXT_USER:
            default:
               screamForHelp($enumTType.': invalid table type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
      }
      return($strOut);
   }

   private function strTmpTableJoinedToContext(
                              $enumTType,      $strJoinTable, $strFieldNamesSel,
                              $strForeignIDFN, $bInner,       $strWhereExtra,
                              $strOrderExtra){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      switch ($enumTType){
         case CENUM_CONTEXT_PEOPLE:
            if ($bInner){
               $strFrom = "FROM people_names
                              LEFT JOIN $strJoinTable ON pe_lKeyID = $strForeignIDFN \n";
            }else {
               $strFrom = "FROM $strJoinTable
                              INNER JOIN people_names ON pe_lKeyID = $strForeignIDFN \n";
            }
            $strOut = 'SELECT '.$this->strSelectExtraViaContext($enumTType).' '
                          .$strFieldNamesSel."
                       $strFrom
                       WHERE NOT pe_bRetired AND NOT pe_bBiz
                          $strWhereExtra
                       ORDER BY $strOrderExtra pe_lKeyID;";
            break;

         case CENUM_CONTEXT_BIZ:
            if ($bInner){
               $strFrom = "FROM people_names
                              LEFT JOIN $strJoinTable ON pe_lKeyID = $strForeignIDFN \n";
            }else {
               $strFrom = "FROM $strJoinTable
                              INNER JOIN people_names ON pe_lKeyID = $strForeignIDFN \n";
            }
            $strOut = 'SELECT '.$this->strSelectExtraViaContext($enumTType).' '
                          .$strFieldNamesSel."
                       $strFrom
                       WHERE NOT pe_bRetired AND pe_bBiz
                          $strWhereExtra
                       ORDER BY $strOrderExtra pe_lKeyID;";
            break;

         case CENUM_CONTEXT_CLIENT:
            if ($bInner){
               $strFrom = "FROM client_records
                              LEFT JOIN $strJoinTable ON cr_lKeyID = $strForeignIDFN \n";
            }else {
               $strFrom = "FROM $strJoinTable
                              INNER JOIN client_records ON cr_lKeyID = $strForeignIDFN \n";
            }
            $strOut = 'SELECT '.$this->strSelectExtraViaContext($enumTType).' '
                          .$strFieldNamesSel."
                       $strFrom
                       WHERE NOT cr_bRetired
                          $strWhereExtra
                       ORDER BY $strOrderExtra cr_lKeyID;";
            break;

         case CENUM_CONTEXT_LOCATION:
            if ($bInner){
               $strFrom = "FROM client_location
                              LEFT JOIN $strJoinTable ON cl_lKeyID = $strForeignIDFN \n";
            }else {
               $strFrom = "FROM $strJoinTable
                              INNER JOIN client_location ON cl_lKeyID = $strForeignIDFN \n";
            }
            $strOut = 'SELECT '.$this->strSelectExtraViaContext($enumTType).' '
                          .$strFieldNamesSel."
                       $strFrom
                       WHERE NOT cl_bRetired
                          $strWhereExtra
                       ORDER BY $strOrderExtra cl_lKeyID;";
            break;

         case CENUM_CONTEXT_GIFT:
            if ($bInner){
               $strFrom = "FROM gifts
                              INNER JOIN people_names  ON gi_lForeignID = pe_lKeyID
                              INNER JOIN admin_aco     ON gi_lACOID     = aco_lKeyID
                              LEFT  JOIN $strJoinTable ON gi_lKeyID = $strForeignIDFN \n";
            }else {
               $strFrom = "FROM $strJoinTable
                              INNER JOIN people_names  ON gi_lForeignID = pe_lKeyID
                              INNER JOIN admin_aco     ON gi_lACOID     = aco_lKeyID
                              INNER JOIN gifts         ON gi_lKeyID = $strForeignIDFN \n";
            }
            $strOut = 'SELECT '.$this->strSelectExtraViaContext($enumTType).' '
                          .$strFieldNamesSel."
                       $strFrom
                       WHERE NOT gi_bRetired
                          $strWhereExtra
                       ORDER BY $strOrderExtra gi_lKeyID;";
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            if ($bInner){
               $strFrom = "FROM sponsor
                              INNER JOIN people_names      ON pe_lKeyID = sp_lForeignID
                              LEFT  JOIN client_records    ON cr_lKeyID = sp_lClientID
                              LEFT  JOIN $strJoinTable     ON sp_lKeyID = $strForeignIDFN \n";
            }else {
               $strFrom = "FROM $strJoinTable
                              INNER JOIN sponsor           ON sp_lKeyID = $strForeignIDFN
                              INNER JOIN people_names      ON pe_lKeyID = sp_lForeignID
                              LEFT  JOIN client_records    ON cr_lKeyID = sp_lClientID \n";
            }
            $strOut = 'SELECT '.$this->strSelectExtraViaContext($enumTType).' '
                          .$strFieldNamesSel."
                       $strFrom
                       WHERE NOT pe_bRetired
                          AND NOT sp_bRetired
                          $strWhereExtra
                       ORDER BY $strOrderExtra sp_lKeyID;";
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            if ($bInner){
               $strFrom = "FROM volunteers
                              INNER JOIN people_names  ON pe_lKeyID = vol_lPeopleID
                              LEFT  JOIN $strJoinTable ON vol_lKeyID = $strForeignIDFN \n";
            }else {
               $strFrom = "FROM $strJoinTable
                             INNER JOIN volunteers    ON vol_lKeyID = $strForeignIDFN
                             INNER JOIN people_names  ON pe_lKeyID = vol_lPeopleID \n";
            }
            $strOut = 'SELECT '.$this->strSelectExtraViaContext($enumTType).' '
                          .$strFieldNamesSel."
                       $strFrom
                       WHERE NOT vol_bRetired
                          $strWhereExtra
                       ORDER BY $strOrderExtra vol_lKeyID;";
            break;

         case CENUM_CONTEXT_BIZCONTACT:
         case CENUM_CONTEXT_GENERIC:
         case CENUM_CONTEXT_HOUSEHOLD:
         case CENUM_CONTEXT_STAFF:
         case CENUM_CONTEXT_USER:
            default:
               screamForHelp($enumTType.': invalid table type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
      }

      return($strOut);
   }

   public function strPopulateUFTempData(&$cUF, $lTableID, $strTmpTable, $enumTType, &$fields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $ufTable            = $cUF->userTables[0]->strDataTableName;
      $ufTableForeignIDFN = $cUF->strGenUF_ForeignIDFN($lTableID);
      $sqlStr =
         "SELECT * FROM $ufTable;";
      $query = $this->db->query($sqlStr);

      $strFNs  = array();
      $idx     = 0;
      foreach ($fields as $field){
         if ($field->enumFieldType != CS_FT_HEADING){
            $strFNs[] = 'field_'.str_pad($idx, 4, '0', STR_PAD_LEFT);
            ++$idx;
         }
      }
      $sqlBase = "INSERT INTO $strTmpTable (\n tmp_lForeignID, \n".implode(",   ", $strFNs).') '."\n"
                      .'VALUES (';

      if ($query->num_rows() > 0){
         foreach ($query->result() as $row){
            $lForeignID = $row->$ufTableForeignIDFN;
            $sqlStr = $sqlBase."\n $lForeignID ";
            foreach ($fields as $field){
               $strFN    = $field->strFieldNameInternal;
               $enumFT   = $field->enumFieldType;
               $lFieldID = $field->pff_lKeyID;

               if ($enumFT==CS_FT_LOG){
                  $sqlStr .= ", ".strPrepStr($cUF->strConsolidateLogEntries($lFieldID, $lForeignID, false))." ";
               }elseif ($enumFT==CS_FT_HEADING){
               }else {
                  $value  = $row->$strFN;

                  if (is_null($value)){
                      $sqlStr .= ", '' ";
                  }else {
                     switch ($enumFT){
                        case CS_FT_CHECKBOX:
                           $sqlStr .= ', '.($value ? "'Yes'" : "'No'")." ";
                           break;

                        case CS_FT_DATE:
                           $sqlStr .= ", '".$value."' ";
                           break;

                        case CS_FT_DDL:
                           $sqlStr .= ', '.strPrepStr($cUF->strDDLValue($value)).' ';
                           break;

                        case CS_FT_TEXT20:
                        case CS_FT_TEXT80:
                        case CS_FT_TEXT255:
                        case CS_FT_TEXTLONG:
                        case CS_FT_INTEGER:
                        case CS_FT_CURRENCY:
                           $sqlStr .= ', '.strPrepStr($value).' ';
                           break;

                        default:
                           screamForHelp($enumFT.': invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                           break;
                     }
                  }
               }
            }
            $sqlStr .= ');';
            $this->db->query($sqlStr);
         }
      }
   }

   public function strCreateTempTableUFExport(&$fields, $strTableName, &$sqlDelete, &$strFieldNamesSel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlDelete = "DROP TABLE IF EXISTS $strTableName ;";
      $sqlStr = "
                 CREATE TEMPORARY TABLE $strTableName(
                    tmp_lKeyID     int(11) NOT NULL AUTO_INCREMENT,
                    tmp_lForeignID int(11) NOT NULL DEFAULT '0' ";

      $idx = 0;
      $strFieldNamesSel = '';
      foreach ($fields as $field){
         $enumFT = $field->enumFieldType;
         if ($enumFT != CS_FT_HEADING){
            $strFN = 'field_'.str_pad($idx, 4, '0', STR_PAD_LEFT);
            $strFieldNamesSel .= ', '.$strFN.' AS `'.strEscMysqlQuote($field->pff_strFieldNameUser).'` ';
         }

         switch ($enumFT){
            case CS_FT_CHECKBOX:
               $sqlStr .= ",\n $strFN  varchar(3)  DEFAULT '' ";
               break;

            case CS_FT_DATE:
               $sqlStr .= ",\n $strFN  date NOT NULL DEFAULT '0000-00-00' ";
               break;

            case CS_FT_TEXT255:
            case CS_FT_DDL:
               $sqlStr .= ",\n $strFN  varchar(255)  DEFAULT '' ";
               break;

            case CS_FT_TEXT80:
               $sqlStr .= ",\n $strFN  varchar(80)  DEFAULT '' ";
               break;

            case CS_FT_TEXT20:
               $sqlStr .= ",\n $strFN  varchar(20)  DEFAULT '' ";
               break;

            case CS_FT_INTEGER:
               $sqlStr .= ",\n $strFN  int(11) DEFAULT '0' ";
               break;

            case CS_FT_CURRENCY:
               $sqlStr .= ",\n $strFN        decimal(10,2)  DEFAULT '0.00' "
                         .",\n $strFN"."ACO  varchar(80)    DEFAULT '' ";
               break;

            case CS_FT_LOG:
            case CS_FT_TEXTLONG:
               $sqlStr .= ",\n $strFN  text NOT NULL ";
               break;

            case CS_FT_HEADING:
               break;

            default:
               screamForHelp($enumFT.': invalid field type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
               break;
         }
         if ($enumFT != CS_FT_HEADING) ++$idx;
      }

      $sqlStr .= " ,
                    PRIMARY KEY       (tmp_lKeyID),
                    KEY tmp_lForeignID (tmp_lForeignID)
                  ) ENGINE=MyISAM;";
      return($sqlStr);
   }

   public function strExportImgDocs($bImage, $enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);
      $strDateTimeFormat = strMysqlDateFormat(true);
      $strSiteURL = str_replace ('/index.php', '', site_url(''));

      if ($bImage) {
         $strThumb = ' di_strSystemThumbFN          AS `Thumbnail File Name`, '
                    ."CONCAT(".strPrepStr($strSiteURL).",
                             SUBSTR(di_strPath, 2), '/', di_strSystemThumbFN)  AS `Thumbnail URL`, ";
      }else {
         $strThumb = '';
      }

      $strFieldNamesSel = "
            ,
            di_lKeyID AS `".($bImage ? 'imageID' : 'docID')."`,
            di_strCaptionTitle AS Caption,
            di_strDescription  AS Description,
            DATE_FORMAT(di_dteDocImage, $strDateFormat) AS `".($bImage ? 'Image' : 'Document')." Date`,
            IF(di_bProfile, 'Yes', 'No') AS `Profile?`,
            di_strUserFN                 AS `User File Name`,
            di_strSystemFN               AS `System File Name`,
            CONCAT(".strPrepStr($strSiteURL).", SUBSTR(di_strPath, 2), '/', di_strSystemFN) AS `File URL`,
            $strThumb
            di_strPath                   AS Path,
            DATE_FORMAT(di_dteOrigin,     $strDateTimeFormat) AS `Record Creation Date`,
            DATE_FORMAT(di_dteLastUpdate, $strDateTimeFormat) AS `Record Last Update` ";

      $strWhereExtra = ' AND di_enumEntryType='.($bImage ? "'image'" : "'pdf'")
                      .' AND NOT di_bRetired '
                      .' AND di_enumContextType='.strPrepStr($enumContext).' ';

      $sqlStr = $this->strTmpTableJoinedToContext(
                              $enumContext,    'docs_images', $strFieldNamesSel,
                              'di_lForeignID', false,         $strWhereExtra,
                              'di_lKeyID, ');
/*
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__);
$zzzstrFile=substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1)));
echoT('<font class="debug">'.$zzzstrFile.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br(htmlspecialchars($sqlStr))."<br><br></font>\n");
die;
*/
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));


   }











}
