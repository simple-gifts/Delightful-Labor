<?php
/*---------------------------------------------------------------------
// Delightful Labor
// copyright (c) 2012-2016 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
  ---------------------------------------------------------------------
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('clients/mclients', 'clsClients');
  ---------------------------------------------------------------------
   initClientClass()
   clearClientFields            ()
   strCommonClientSQL           ()
   lAddNewClient                ()
   updateClientRec              ()
   removeClient                 ($lClientID)
   loadClientInfo               ()
   strSafeName                  ()

   lNumClientsViaLocID          ($lLocID, $bIncludeInactive)
   lNumClientsInAgeRange        ($bActive, $lAgeStart, $lAgeEnd)
   lClientCountGeneric          ()

   loadClientsViaLocID          ($lLocID)
   loadClientsViaClientID       ($lCID)
   loadClientsGeneric           ()
   loadSponsorshipInfo          ($lClientID, $bOrderStartDate, &$lNumSponsors, &$sponsors)
   clientHTMLSummary            ($idx)
   clientDirectory              ()

   xxx lNumKids                     ($lLocationID, $bActive)   DEPRICATED
   lNumSponsorsViaClientID      ($lClientID)
   lNumClientsViaLetter         ($strLetter, $bIncludeInactive){
   strWhereByLetter             ($strLetter)
   loadClientDirectoryPage      ($strWhereExtra, $lStartRec, $lRecsPerPage){
   bValidClientID               ($lClientID)

   clientAgeRanges              (&$ageRanges)

---------------------------------------------------------------------*/


class mclients extends CI_Model{

   public
      $lClientID, $strClientID,
      $strFName, $strMName, $strLName, $strNickname, $strSafeName,
      $mdteBirth,  $strDeath, $objBirth,                                         // mysql date format
      $strAgeBDay, $lBDayMonth, $lBDayDay, $lBDayYear,
      $lMaxAllowedSponsors, $lVocID,
      $dteEnrolled,
      $strBio,   $enumGender, $lCountryID, $strCountry,
      $lLocationID, $strLocation, $lSubFacEntryID, $strSubLocation,
      $lSponsorCatID,
      $lOriginID, $lLastUpdateID, $dteOrigin, $dteLastUpdate;

   public $sponsors, $lNumSponsors;

   public $bRetired;

   public $strDefaultImageFile_LR, $strDefaultImageFile_MR, $strDefaultImageFile_HR;

   public
      $strExtraClientWhere, $strClientLimit, $strClientOrder, $strInnerExtra,
      $lNumClients,         $clients;

   public
      $bDebug;

      //-------------------------
      // generic directory
      //-------------------------
   public
      $dir;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->initClientClass();
   }

   public function initClientClass() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------

      $this->clearClientFields();

      $this->strExtraClientWhere = $this->strClientLimit =
      $this->strClientOrder = $this->strInnerExtra = '';

      $this->lNumClients = $this->clients = null;

      $this->bDebug = false;

      $this->dir = new stdClass;
   }

   public function clearClientFields(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->lClientID           = $this->strFName      = $this->strMName       =
      $this->strLName            = $this->strNickname   = $this->strSafeName    =
      $this->mdteBirth           =
      $this->strDeath            = $this->objBirth      = $this->strAgeBDay     =
      $this->lBDayMonth          = $this->lBDayDay      = $this->lBDayYear      =
      $this->lMaxAllowedSponsors = $this->dteEnrolled   = $this->strBio         =
      $this->enumGender          = $this->strCountry    =
      $this->lLocationID         = $this->strLocation   = $this->lSubFacEntryID =
      $this->strSubLocation      = $this->lSponsorCatID = $this->lOriginID      =
      $this->lLastUpdateID       = $this->dteOrigin     = $this->dteLastUpdate  = null;

      $this->sponsors = $this->lNumSponsors = $this->bRetired = null;

      $this->v2_strPrivateBio =
      $this->v2_lCasteID      =
      $this->v2_lReligionID   = null;
   }

   private function strCommonClientSQL(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $client = &$this->clients[0];
      $sqlCommon =
          'cr_strFName      = '.strPrepStr ($client->strFName).',
           cr_strMName      = '.strPrepStr ($client->strMName).',
           cr_strLName      = '.strPrepStr ($client->strLName).',
           cr_dteBirth      = '.strPrepStr ($client->dteBirth).',
           cr_dteEnrollment = '.strPrepDate($client->dteEnrollment).',
           cr_strBio        = '.strPrepStr ($client->strBio).',
           cr_enumGender    = '.strPrepStr ($client->enumGender).',

           cr_strAddr1      = '.strPrepStr($client->strAddr1,   80).',
           cr_strAddr2      = '.strPrepStr($client->strAddr2,   80).',
           cr_strCity       = '.strPrepStr($client->strCity,    80).',
           cr_strState      = '.strPrepStr($client->strState,   80).',
           cr_strCountry    = '.strPrepStr($client->strCountry, 80).',
           cr_strZip        = '.strPrepStr($client->strZip,     40).',
           cr_strPhone      = '.strPrepStr($client->strPhone,   40).',
           cr_strCell       = '.strPrepStr($client->strCell,    40).',
           cr_strEmail      = '.strPrepStr($client->strEmail,  120).',

           cr_lMaxSponsors  = '.$client->lMaxSponsors.',
           cr_lLocationID   = '.$client->lLocationID.',
           cr_lAttributedTo = '.(is_null($client->lAttribID) ? 'null' : $client->lAttribID).',
           cr_lVocID        = '.$client->lVocID.",
           cr_lLastUpdateID = $glUserID ";

      return($sqlCommon);
   }

   public function lAddNewClient(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $gdteNow, $glUserID;
      $clsUFC = new muser_fields_create;

      $sqlCommon = $this->strCommonClientSQL();
      $sqlStr =
          "INSERT INTO client_records
           SET $sqlCommon,
             cr_lStatusCatID = ".$this->clients[0]->lStatusCatID.",
             cr_lOriginID    = $glUserID,
             cr_bRetired     = 0,
             cr_dteOrigin    = NOW();";
      $query = $this->db->query($sqlStr);
      $this->clients[0]->lKeyID = $lKeyID = $this->db->insert_id();   //mysql_insert_id();

         //--------------------------------------------------------
         // create blank/default records for all the personalized
         // client tables
         //--------------------------------------------------------
      $clsUFC->enumTType = CENUM_CONTEXT_CLIENT;
      $clsUFC->loadTablesViaTType();
      if ($clsUFC->lNumTables > 0){
         foreach ($clsUFC->userTables as $clsTable){
            $clsUFC->createSingleEmptyRec($clsTable, $lKeyID);
         }
      }

      return($lKeyID);
   }

   public function updateClientRec(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $gdteNow;

      $sqlCommon = $this->strCommonClientSQL();
      $sqlStr =
          "UPDATE client_records
           SET $sqlCommon,
               cr_bRetired=".($this->clients[0]->bRetired ? '1' : '0').'
           WHERE cr_lKeyID='.$this->clients[0]->lKeyID.';';
      $query = $this->db->query($sqlStr);
   }

   public function removeClient($lClientID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
          "UPDATE client_records
           SET
              cr_lLastUpdateID = $glUserID,
              cr_bRetired = 1
           WHERE cr_lKeyID=$lClientID;";
      $query = $this->db->query($sqlStr);

         // remove group membership
      $clsGroups = new mgroups;
      $clsGroups->removeMemFromAllGroups(CENUM_CONTEXT_CLIENT, $lClientID);

         // delete client entries in personalized tables
      $uf = new muser_fields;
      $uf->deleteForeignViaUFTableType(CENUM_CONTEXT_CLIENT,      $lClientID);
      $uf->deleteForeignViaUFTableType(CENUM_CONTEXT_CPROGENROLL, $lClientID);
      $uf->deleteForeignViaUFTableType(CENUM_CONTEXT_CPROGATTEND, $lClientID);
      $uf->deleteForeignViaUFTableType(CENUM_CONTEXT_CPREPOST,    $lClientID);

   }

   public function strSafeName(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      return(htmlspecialchars($this->strFName.' '.$this->strLName));
   }

   public function loadClientsViaLocID($lLocID, $bIncludeInactive){
   //-----------------------------------------------------------------------
   //  active means "show in directory"
   //-----------------------------------------------------------------------
      $this->strExtraClientWhere .= " AND (cr_lLocationID=$lLocID) ";
      if (!$bIncludeInactive){
         $this->strExtraClientWhere .= ' AND cst_bShowInDir ';
      }
      $this->loadClientsGeneric();
   }

   public function lNumClientsViaLocID($lLocID, $bIncludeInactive){
   //-----------------------------------------------------------------------
   //  active means "show in directory"
   //-----------------------------------------------------------------------
      $this->strExtraClientWhere = " AND (cr_lLocationID=$lLocID) ";
      if (!$bIncludeInactive){
         $this->strExtraClientWhere .= ' AND cst_bShowInDir ';
      }
      return($this->lClientCountGeneric());
   }

   function lNumClientsInAgeRange($bActive, $lAgeStart, $lAgeEnd){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;

      $mysqlDteBase = str_replace("'", '', strPrepDate($gdteNow));
      dateRangeYears($mysqlDteBase, $lAgeStart, $lAgeEnd, $mysqlStartDate, $mysqlEndDate);

      if ($bActive){
         $strInnerExtra = '
               INNER JOIN client_status                      ON csh_lClientID   = cr_lKeyID
               INNER JOIN lists_client_status_entries        ON csh_lStatusID   = cst_lKeyID
            ';
         $strSubQuery = '
                 -- ---------------------------------------
                 -- subquery to find most current status
                 -- ---------------------------------------
               AND csh_lKeyID=(SELECT csh_lKeyID
                               FROM client_status
                               WHERE csh_lClientID=cr_lKeyID
                                  AND NOT csh_bRetired
                               ORDER BY csh_dteStatusDate DESC, csh_lKeyID DESC
                               LIMIT 0,1)
               ';
         $strExtraClientWhere = ' AND cst_bShowInDir ';
      }else {
         $strInnerExtra =
         $strSubQuery   = '';
         $strExtraClientWhere = '';
      }

      $sqlStr =
           "SELECT COUNT(*) AS lNumClients
            FROM client_records
               $strInnerExtra
            WHERE 1
               AND NOT cr_bRetired
               $strSubQuery
               $strExtraClientWhere
               AND cr_dteBirth > ".strPrepStr($mysqlStartDate).'
               AND cr_dteBirth <='.strPrepStr($mysqlEndDate).';';

      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return($row->lNumClients);
   }

   public function lClientCountGeneric(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
           "SELECT COUNT(*) AS lNumClients
            FROM client_records
               INNER JOIN client_location                    ON cr_lLocationID = cl_lKeyID

               INNER JOIN client_status                      ON csh_lClientID   = cr_lKeyID
               INNER JOIN lists_client_status_entries        ON csh_lStatusID   = cst_lKeyID

            WHERE 1
               AND NOT cr_bRetired

                 -- ---------------------------------------
                 -- subquery to find most current status
                 -- ---------------------------------------
               AND csh_lKeyID=(SELECT csh_lKeyID
                               FROM client_status
                               WHERE csh_lClientID=cr_lKeyID
                                  AND NOT csh_bRetired
                               ORDER BY csh_dteStatusDate DESC, csh_lKeyID DESC
                               LIMIT 0,1)

               $this->strExtraClientWhere;";

      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return($row->lNumClients);
   }

   public function loadClientsViaAttribID($strAttrib, $bIncludeInactive, $strLimit){
   //-----------------------------------------------------------------------
   // examples of $strAttrib: 'IS NULL'   '=123'
   //-----------------------------------------------------------------------
      $this->strExtraClientWhere .= " AND (cr_lAttributedTo $strAttrib) ";
      if (!$bIncludeInactive){
         $this->strExtraClientWhere .= ' AND cst_bShowInDir ';
      }
      $this->strClientLimit = $strLimit;
      $this->loadClientsGeneric();
   }

   public function loadClientsViaClientID($lCID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_array($lCID)){
         $this->strExtraClientWhere .= ' AND (cr_lKeyID IN ('.implode(',', $lCID).')) ';
      }else {
         $this->strExtraClientWhere .= " AND (cr_lKeyID=$lCID) ";
      }
      $this->loadClientsGeneric();
   }

   public function loadClientsGeneric(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glclsDTDateFormat;

      customClient_AAYHF_01($strInnerAAYHF, $strFieldsAAYHF, $bAAYHF_Beacon);

      $strOrder = $this->strClientOrder;
      if ($strOrder == ''){
         $strOrder = ' cl_strLocation, cr_strLName, cr_strFName, cr_strMName, cr_lKeyID ';
      }

      $clsDateTime = new dl_date_time;

      $sqlStr =
           "SELECT
               cr_lKeyID, cr_strFName, cr_strMName, cr_strLName,
               cr_dteEnrollment,
               cr_dteBirth, cr_dteDeath, cr_enumGender,
               cr_lLocationID, cl_strCountry, cr_lStatusCatID, cr_lVocID,
               cr_lMaxSponsors, cr_strBio, cr_bNoLongerAtLocation,
               cr_bRetired, cr_lOriginID, cr_lLastUpdateID,
               UNIX_TIMESTAMP(cr_dteOrigin)     AS dteOrigin,
               UNIX_TIMESTAMP(cr_dteLastUpdate) AS dteLastUpdate,
               cl_strLocation, cl_bEnableEMR,
               cr_lAttributedTo, lgen_strListItem,

               cr_strAddr1,      cr_strAddr2,
               cr_strCity,       cr_strState,      cr_strCountry,
               cr_strZip,        cr_strPhone,      cr_strCell, cr_strEmail,

               -- ------------------------
               -- vocabulary
               -- ------------------------
               cv_strVocTitle,
               cv_strVocClientS, cv_strVocClientP,
               cv_strVocSponsorS, cv_strVocSponsorP,
               cv_strVocLocS, cv_strVocLocP,
               cv_strVocSubLocS, cv_strVocSubLocP,

               -- ------------------------
               -- current status
               -- ------------------------
               csh_lKeyID, csh_lStatusID,
               csh_dteStatusDate,
               csh_bIncludeNotesInPacket, csh_strStatusTxt,

               cst_lClientStatusCatID, cst_strStatus, cst_bAllowSponsorship,
               cst_bShowInDir, cst_bDefault,
               curStatSCat.csc_strCatName     AS curStat_strCatName,
               defSCat.csc_strCatName         AS defStat_strCatName,

               uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
               ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

               $strFieldsAAYHF

            FROM client_records
               $this->strInnerExtra
               $strInnerAAYHF
               LEFT JOIN client_location                    ON cr_lLocationID = cl_lKeyID
               LEFT JOIN lists_client_vocab                 ON cr_lVocID      = cv_lKeyID

               LEFT JOIN client_status                      ON csh_lClientID   = cr_lKeyID
               LEFT JOIN lists_client_status_entries        ON csh_lStatusID   = cst_lKeyID
               LEFT JOIN client_status_cats AS curStatSCat  ON cst_lClientStatusCatID = curStatSCat.csc_lKeyID
               LEFT JOIN client_status_cats AS defSCat      ON cr_lStatusCatID        = defSCat.csc_lKeyID

               INNER JOIN admin_users  AS uc ON uc.us_lKeyID=cr_lOriginID
               INNER JOIN admin_users  AS ul ON ul.us_lKeyID=cr_lLastUpdateID

               LEFT  JOIN lists_generic      ON cr_lAttributedTo=lgen_lKeyID
            WHERE 1
               AND NOT cr_bRetired

                 -- ---------------------------------------
                 -- subquery to find most current status
                 -- ---------------------------------------
               AND csh_lKeyID=(SELECT csh_lKeyID
                               FROM client_status
                               WHERE csh_lClientID=cr_lKeyID
                                  AND NOT csh_bRetired
                               ORDER BY csh_dteStatusDate DESC, csh_lKeyID DESC
                               LIMIT 0,1)

               $this->strExtraClientWhere
            ORDER BY $strOrder
            $this->strClientLimit;";

      $query = $this->db->query($sqlStr);
      $this->lNumClients = $numRows = $query->num_rows();
      $this->clients = array();
      if ($numRows==0) {
         $this->clients[0] = new stdClass;
         $client = &$this->clients[0];
         $client->lKeyID                        =
         $client->strFName                      =
         $client->strMName                      =
         $client->strLName                      =
         $client->dteEnrollment                 =
         $client->dteBirth                      =
         $client->dteDeath                      =
         $client->enumGender                    =
         $client->lLocationID                   =
         $client->lStatusCatID                  =
         $client->lVocID                        =
         $client->lMaxSponsors                  =
         $client->strBio                        =
         $client->lAttribID                     =
         $client->strAttrib                     =
         $client->bRetired                      =
         $client->lOriginID                     =
         $client->lLastUpdateID                 =
         $client->dteOrigin                     =
         $client->dteLastUpdate                 =
         $client->strLocation                   =
         $client->strLocCountry                 =

         $client->strAddr1                      =
         $client->strAddr2                      =
         $client->strCity                       =
         $client->strState                      =
         $client->strCountry                    =
         $client->strZip                        =
         $client->strPhone                      =
         $client->strCell                       =
         $client->strEmail                      =
         $client->strAddress                    =

         $client->cv_strVocTitle                =
         $client->cv_strVocClientS              =
         $client->cv_strVocClientP              =
         $client->cv_strVocSponsorS             =
         $client->cv_strVocSponsorP             =
         $client->cv_strVocLocS                 =
         $client->cv_strVocLocP                 =
         $client->cv_strVocSubLocS              =
         $client->cv_strVocSubLocP              =

         $client->curStat_lStatRecKeyID         =
         $client->curStat_lStatusID             =
         $client->curStat_dteStatus             =
         $client->curStat_bIncludeNotesInPacket =
         $client->curStat_strStatusNotes        =
         $client->curStat_lClientStatusCatID    =
         $client->curStat_strStatus             =
         $client->curStat_bAllowSponsorship     =
         $client->curStat_bShowInDir            =
         $client->curStat_bDefaultStatus        =
         $client->curStat_strStatusCatName      = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->clients[$idx] = new stdClass;
            $client = &$this->clients[$idx];

            $client->lKeyID            = (int)$row->cr_lKeyID;
            $client->strFName          = $row->cr_strFName;
            $client->strMName          = $row->cr_strMName;
            $client->strLName          = $row->cr_strLName;
            $client->strSafeName       =
                          htmlspecialchars($row->cr_strFName.' '.$row->cr_strLName);
            $client->strSafeNameLF     =
                          htmlspecialchars($row->cr_strLName.', '.$row->cr_strFName);
            $client->dteEnrollment     = dteMySQLDate2Unix($row->cr_dteEnrollment);
            $client->dteBirth          = $mySQLdteBirth = $row->cr_dteBirth;
            $client->dteDeath          = $row->cr_dteDeath;
            $client->enumGender        = $row->cr_enumGender;
            $client->lLocationID       = $row->cr_lLocationID;
            $client->lStatusCatID      = $row->cr_lStatusCatID;
            $client->strStatusCatName  = $row->defStat_strCatName;

               //------------------------------
               // client address/contact
               //------------------------------
            $client->strAddr1           = $row->cr_strAddr1;
            $client->strAddr2           = $row->cr_strAddr2;
            $client->strCity            = $row->cr_strCity;
            $client->strState           = $row->cr_strState;
            $client->strCountry         = $row->cr_strCountry;
            $client->strZip             = $row->cr_strZip;
            $client->strPhone           = $row->cr_strPhone;
            $client->strCell            = $row->cr_strCell;
            $client->strEmail           = $row->cr_strEmail;
            $client->strEmailFormatted  = strBuildEmailLink($client->strEmail, '', false, '');
            $client->strAddress         =
                        strBuildAddress(
                                 $client->strAddr1, $client->strAddr2,   $client->strCity,
                                 $client->strState, $client->strCountry, $client->strZip,
                                 true);

               //------------------------------
               // client age/birth day info
               //------------------------------
            if (is_null($mySQLdteBirth)){
               $client->objClientBirth = null;
               $client->lAgeYears      = null;
               $client->strClientAgeBDay = '(age n/a)';
            }else {
               $client->objClientBirth = new dl_date_time;
               $client->objClientBirth->setDateViaMySQL(0, $mySQLdteBirth);
               $client->strClientAgeBDay =
                          $client->objClientBirth->strPeopleAge(0, $mySQLdteBirth,
                               $client->lAgeYears, $glclsDTDateFormat);
            }

            $client->lVocID                        = $row->cr_lVocID;
            $client->lMaxSponsors                  = $row->cr_lMaxSponsors;
            $client->strBio                        = $row->cr_strBio;
            $client->bRetired                      = $row->cr_bRetired;
            $client->lOriginID                     = $row->cr_lOriginID;
            $client->lLastUpdateID                 = $row->cr_lLastUpdateID;
            $client->strLocation                   = $row->cl_strLocation;
            $client->strLocCountry                 = $row->cl_strCountry;
            $client->bEnableEMR                    = $row->cl_bEnableEMR;

            $client->cv_strVocTitle                = $row->cv_strVocTitle;
            $client->cv_strVocClientS              = $row->cv_strVocClientS;
            $client->cv_strVocClientP              = $row->cv_strVocClientP;
            $client->cv_strVocSponsorS             = $row->cv_strVocSponsorS;
            $client->cv_strVocSponsorP             = $row->cv_strVocSponsorP;
            $client->cv_strVocLocS                 = $row->cv_strVocLocS;
            $client->cv_strVocLocP                 = $row->cv_strVocLocP;
            $client->cv_strVocSubLocS              = $row->cv_strVocSubLocS;
            $client->cv_strVocSubLocP              = $row->cv_strVocSubLocP;

                //---------------------------------
                // status fields - current status
                //---------------------------------
            $client->curStat_lStatRecKeyID         = $row->csh_lKeyID;
            $client->curStat_lStatusID             = $row->csh_lStatusID;
            $client->curStat_dteStatus             = dteMySQLDate2Unix($row->csh_dteStatusDate);
            $client->curStat_bIncludeNotesInPacket = $row->csh_bIncludeNotesInPacket;
            $client->curStat_strStatusNotes        = $row->csh_strStatusTxt;
            $client->curStat_lClientStatusCatID    = $row->cst_lClientStatusCatID;
            $client->curStat_strStatus             = $row->cst_strStatus;
            $client->curStat_bAllowSponsorship     = $row->cst_bAllowSponsorship;
            $client->curStat_bShowInDir            = $row->cst_bShowInDir;
            $client->curStat_bDefaultStatus        = $row->cst_bDefault;
            $client->curStat_strStatusCatName      = $row->curStat_strCatName;

            $client->lAttribID                     = $row->cr_lAttributedTo;
            $client->strAttrib                     = $row->lgen_strListItem;

            $client->dteOrigin                     = $row->dteOrigin;
            $client->dteLastUpdate                 = $row->dteLastUpdate;
            $client->ucstrFName                    = $row->strUCFName;
            $client->ucstrLName                    = $row->strUCLName;
            $client->ulstrFName                    = $row->strULFName;
            $client->ulstrLName                    = $row->strULLName;

            $client->strFlagsTable                 = '';
                //------------------
                // sponsors
                //------------------
            $this->loadSponsorshipInfo(
                         $client->lKeyID,
                         true,
                         $client->lNumSponsors,
                         $client->sponsors);

                //------------------
                // custom fields
                //------------------
            addClientFields_AAYHF_Beacon($bAAYHF_Beacon, $client, $row);

            ++$idx;
         }
      }
      if ($this->bDebug) $this->dumpClientRecs();
   }

   function dumpClientRecs(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      echoT('<font class="debug">'.__FILE__.': '.__LINE__.'<br><pre>');
      print_r($this->clients);
      echoT('</pre><br></font>');
   }

   public function loadSponsorshipInfo($lClientID, $bOrderStartDate, &$lNumSponsors, &$sponsors){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $strOrder = $bOrderStartDate ? ' sp_dteStartMoYr, ' : '';
      $sqlStr =
        "SELECT
            sp_lKeyID, sp_lForeignID, sp_curCommitment,
            sp_bInactive, sp_lInactiveCatID,
            sp_lOriginID, sp_lLastUpdateID,
            sp_dteStartMoYr,
            MONTH(sp_dteStartMoYr)           AS lMonthStart,
            YEAR(sp_dteStartMoYr)            AS lYearStart,
            sp_dteInactive,
            UNIX_TIMESTAMP(sp_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(sp_dteLastUpdate) AS dteLastUpdate,

            pe_strFName, pe_strLName, pe_enumGender, pe_bBiz

         FROM sponsor
            INNER JOIN people_names ON pe_lKeyID=sp_lForeignID
         WHERE
            NOT sp_bRetired
            AND sp_lClientID=$lClientID
         ORDER BY $strOrder pe_strLName, pe_strFName, sp_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumSponsors = $query->num_rows();

      $sponsors = array();
      if ($lNumSponsors > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $sponsors[$idx] = new stdClass;
            $sp = &$sponsors[$idx];

            $sp->lKeyID         = (int)$row->sp_lKeyID;
            $sp->lPersonID      = (int)$row->sp_lForeignID;
            $sp->curCommitment  = (float)$row->sp_curCommitment;
            $sp->bInactive      = (bool)$row->sp_bInactive;
            $sp->lInactiveCatID = $row->sp_lInactiveCatID;
            $sp->lOriginID      = (int)$row->sp_lOriginID;
            $sp->lLastUpdateID  = (int)$row->sp_lLastUpdateID;
            $sp->dteStartMoYr   = dteMySQLDate2Unix($row->sp_dteStartMoYr);
            $sp->lMonthStart    = (int)$row->lMonthStart;
            $sp->lYearStart     = (int)$row->lYearStart;

            $sp->dteInactive    = dteMySQLDate2Unix($row->sp_dteInactive);
            $sp->dteOrigin      = (int)$row->dteOrigin;
            $sp->dteLastUpdate  = (int)$row->dteLastUpdate;
            $sp->bBiz           = (bool)$row->pe_bBiz;
            $sp->strFName       = $row->pe_strFName;
            $sp->strLName       = $row->pe_strLName;
            if ($sp->bBiz){
               $sp->strSafeNameFL = htmlspecialchars($row->pe_strLName);
            }else {
               $sp->strSafeNameFL = htmlspecialchars($row->pe_strFName.' '.$row->pe_strLName);
            }
            $sp->enumGender     = $row->pe_enumGender;
            ++$idx;
         }
      }
   }

   public function strClientHTMLSummary($idx){
   /*-----------------------------------------------------------------------
      assumes user has called $clsClient->loadClientsVia...()

      caller must include
            $this->load->model  ('img_docs/mimage_doc',   'clsImgDoc');
            $this->load->helper ('img_docs/image_doc');
   -----------------------------------------------------------------------*/
      $strOut = '';
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $clsC = $this->clients[$idx];
      $lCID = $clsC->lKeyID;

      $cImg = new mimage_doc;
      $cImg->bLoadContext = false;
      $cImg->loadProfileImage(CENUM_CONTEXT_CLIENT, $lCID);
      $bProfileImgAvail = $cImg->lNumImageDocs > 0;
      if ($bProfileImgAvail){
         $imgDoc = &$cImg->imageDocs[0];
         $strTNImg =
                     strLinkHTMLTag(CENUM_CONTEXT_CLIENT, CENUM_IMGDOC_ENTRY_IMAGE, $lCID, $imgDoc->strSystemFN,
                                   'View in new window', true, '')
                    .strImageHTMLTag(CENUM_CONTEXT_CLIENT, CENUM_IMGDOC_ENTRY_IMAGE, $lCID, $imgDoc->strSystemThumbFN,
                               '', false, ' style="border: 1px solid black; height: 80px;" ')
                    .'</a>';
         $strOut .= '<table border="0"><tr><td style="vertical-align: top;">';

      }else {
      }

      $strOut .= $clsRpt->openReport('', '');
      $strOut .=
           $clsRpt->openRow   (false)
          .$clsRpt->writeLabel($clsC->cv_strVocClientS.' Name:')
          .$clsRpt->writeCell (
                          strLinkView_ClientRecord($lCID, 'View Client Record', true).'&nbsp;'
                         .$clsC->strSafeName.'&nbsp;&nbsp;(client ID: '
                         .str_pad($lCID, 5, '0', STR_PAD_LEFT).')'
                         )
          .$clsRpt->closeRow  ();

      $strOut .=
           $clsRpt->openRow   (false)
          .$clsRpt->writeLabel('Birthday/Age:')
          .$clsRpt->writeCell ($clsC->strClientAgeBDay)
          .$clsRpt->closeRow  ();

      $strOut .=
           $clsRpt->openRow   (false)
          .$clsRpt->writeLabel('Gender:')
          .$clsRpt->writeCell ($clsC->enumGender)
          .$clsRpt->closeRow  ();

      $strOut .=
          $clsRpt->openRow   (false)
         .$clsRpt->writeLabel($clsC->cv_strVocLocS.':')
         .$clsRpt->writeCell (htmlspecialchars($clsC->strLocation))
         .$clsRpt->closeRow  ();

      $strStatus = htmlspecialchars($clsC->strStatusCatName);
      if (isset($clsC->strFlagsTable)) $strStatus .= '&nbsp;'.$clsC->strFlagsTable;
      $strOut .=
          $clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Status Category:')
         .$clsRpt->writeCell ($strStatus)
         .$clsRpt->closeRow  ();

      $strOut .=
         $clsRpt->closeReport('<br>');

      if ($bProfileImgAvail){
         $strOut .= '</td><td style="vertical-align: top;">'.$strTNImg.'</td></tr></table>';
      }

      return($strOut);
   }

   function loadDefaultClientImage(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
          "SELECT ic_strWebFN, ic_strCaption, ic_strWebFNExt, ic_strImageSubDir
           FROM tbl_image_catalog
           WHERE (NOT ic_bHidden) AND (NOT ic_bRetired)
               AND (ic_lForeignKey=$this->lClientID)
               AND (ic_lImageGroup=".CL_IMG_GROUP_ClientS.')
               AND (ic_lImageSubGroup='.CL_IMG_SUBG_Client_BIO.')
           ORDER BY ic_bDefault DESC, ic_lKeyID DESC
           LIMIT 0,1;';
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }else{
//         $numRows = mysql_num_rows($result);
      if ($numRows==0) {
         $this->strDefaultImageFile = '';
      }else {
//         $row = mysql_fetch_array($result);
         $row = $query->row();

         $strImageSubDir = $row->ic_strImageSubDir;
         $strWebFN       = $row->ic_strWebFN;
         $strWebFNExt    = $row->ic_strWebFNExt;
         $this->strDefaultImageFile_LR = CSTR_CLIENT_IMG_PATH.'/'.$strImageSubDir.'/'.$strWebFN.'_LR.'.$strWebFNExt;
         $this->strDefaultImageFile_MR = CSTR_CLIENT_IMG_PATH.'/'.$strImageSubDir.'/'.$strWebFN.'_MR.'.$strWebFNExt;
         $this->strDefaultImageFile_HR = CSTR_CLIENT_IMG_PATH.'/'.$strImageSubDir.'/'.$strWebFN.'_HR.'.$strWebFNExt;
      }
 //     }
   }

   function strXlateGender(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      switch ($this->enumGender){
         case 'M': return('boy');  break;
         case 'F': return('girl'); break;
         default:  return('(unknown'); break;
      }
   }

   function loadEligibleClients($bOrderName, $bOrderFac, $bOrderBDay, $bOrderCID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gstrImplodeLocationIDs;

      if (is_null($this->lLocationID)) {
         $strWhere = " AND (cr_lLocationID IN ($gstrImplodeLocationIDs))";
      }else {
         $strWhere = " AND (cr_lLocationID=$this->lLocationID) ";
      }

      if ($bOrderName){
         $strOrder = ' cr_strLName, cr_strFName, cr_lKeyID ';
      }elseif ($bOrderFac){
         $strOrder = ' spf_strTitle, cr_strLName, cr_strFName, cr_lKeyID ';
      }elseif ($bOrderBDay){
         $strOrder = ' cr_dteBirth DESC, spf_strTitle, cr_strLName, cr_strFName, cr_lKeyID ';
      }elseif ($bOrderCID){
         $strOrder = ' cr_lKeyID ';
      }

      $this->eligibleKids  = array();
      $this->lNumKids      = 0;
      $this->strInKidsList = '';

      $sqlStr =
          "SELECT
              cr_lKeyID, cr_dteBirth, cr_lLocationID, cr_enumGender,
              cr_strFName, cr_strLName,
              cl_strLocation
           FROM client_records
              INNER JOIN client_location ON cr_lLocationID=cl_lKeyID
           WHERE (NOT cr_bRetired)
              $strWhere
           ORDER BY $strOrder;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }else{
//         $numRows = mysql_num_rows($result);
      $idx = 0;
      foreach ($query->result() as $row){

//         for ($idx=0; $idx<$numRows; ++$idx) {
//            $row      = mysql_fetch_array($result);
         $lClientID = $row['cr_lKeyID'];
         $this->strInKidsList .= ", $lClientID";
         if ($this->bInDirectoryViaCurrentStatus($lClientID)){

            $this->eligibleKids[$idx] = new stdClass;
            $this->eligibleKids[$idx]->lClientID     = $lClientID;
            $this->eligibleKids[$idx]->bMale        = $row->cr_enumGender=='M';
            $this->eligibleKids[$idx]->strFName     = $row->cr_strFName;
            $this->eligibleKids[$idx]->strLName     = $row->cr_strLName;
            $this->eligibleKids[$idx]->mysqlDteBDay = $row->cr_dteBirth;
            $this->eligibleKids[$idx]->lLocID       = $row->cr_lLocationID;
            $this->eligibleKids[$idx]->strLocation  = $row->cl_strLocation;

            ++$this->lNumKids;
            ++$idx;
         }
      }
      $this->strInKidsList = substr($this->strInKidsList, 2);
//      }
   }

   public function bInDirectoryViaCurrentStatus($lClientID){
   //---------------------------------------------------------------------
   // dummy until status structure set up
   //---------------------------------------------------------------------
whereAmI(); die;
      return(true);
   }

   public function clientDirectory(){
   //---------------------------------------------------------------------
   // assumes a call to $clsClient->loadClientInfo();
   //---------------------------------------------------------------------
      $strOut = '';
      $lNumCols = count($this->dir->cols);

      $strOut .= $this->clientDirectoryHeader($lNumCols);
      $strOut .= $this->clientDirectoryRows  ($lNumCols);
      $strOut .= $this->clientDirectoryClose ();
      return($strOut);
   }

   private function clientDirectoryHeader($lNumCols){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '
          <table '.$this->dir->strTableClassStyle.'>
            <tr>
               <td colspan="'.$lNumCols.'" '.$this->dir->strTitleClassStyle.'>'
                  .$this->dir->strTitle.'
            </tr>
            <tr>'."\n";

      foreach($this->dir->cols as $col){
         $strStyle = '';
         if ($col->width != ''){
            $strStyle .= 'width: '.$col->width.';';
         }
         if ($col->label=='Select Link'){
            $strLabel = '&nbsp;';
         }else {
            $strLabel = $col->label;
         }

         $strOut .= '
            <td '.$this->dir->strHeaderClass.' style="'.$strStyle.'">'
               .$strLabel.'
            </td>'."\n";
      }
      $strOut .= '</tr>'."\n";
      return($strOut);
   }

   private function clientDirectoryRows(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      $clsSponProg = new msponsorship_programs;
      foreach ($this->clients as $client){
         $strOut .= '<tr '.$this->dir->strRowTRClass.' >'."\n";
         $lClientID = $client->lKeyID;

         foreach($this->dir->cols as $col){
            $strOut .= $this->writeDirectoryRowCol($clsSponProg, $lClientID, $client, $col);
         }
         $strOut .='</tr>'."\n";
      }
      return($strOut);
   }

   private function writeDirectoryRowCol($clsSponProg, $lClientID, $client, $col){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strStyle = '';
      if ($col->width != ''){
         $strStyle .= 'width: '.$col->width.';';
      }
      $strOut = '<td '.$col->tdClass.' style="'.$strStyle.'">'."\n";
      switch ($col->label){
         case 'Client ID':
            $strCell =
                  strLinkView_ClientRecord($client->lKeyID, 'View client record', true).' '
                 .str_pad($client->lKeyID, 5, '0', STR_PAD_LEFT);
            break;

         case 'Name':
            $strCell = $client->strSafeNameLF;
            break;

         case 'Location':
            $strCell = htmlspecialchars($client->strLocation);
            break;

         case 'Birthday/Age':
            $strCell = $client->strClientAgeBDay;
            break;

         case 'Gender':
            $strCell = $client->enumGender;
            break;

         case 'Sponsorship Program':
            $clsSponProg->loadSponProgsViaClientID($lClientID);
            if ($clsSponProg->lNumSponPrograms <= 0){
               $strCell = '<i>None</i>';
            }else {
               $strCell = '';
               foreach ($clsSponProg->sponProgs as $clsProg){
                  $strCell .= htmlspecialchars($clsProg->strProg).'<br>';
               }
            }
            break;

         case 'Select Link':
            $strCell = strImageLink($this->dir->clsSelLink->strLinkPath.'/'.$client->lKeyID,
                                    $this->dir->clsSelLink->strAnchorExtra.' id="selCID_'.$client->lKeyID.'" ',
                                    $this->dir->clsSelLink->bShowImage,
                                    $this->dir->clsSelLink->bShowText,
                                    $this->dir->clsSelLink->enumImage,
                                    $this->dir->clsSelLink->strLinkText);
            break;

         default:
            screamForHelp($col->label.': column not recognized<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
            break;
      }
      return($strOut.$strCell.'</td>'."\n");
   }

   private function clientDirectoryClose(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return('</table>'."\n");
   }

   public function lNumSponsorsViaClientID($lClientID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM sponsor
         WHERE
            sp_lClientID=$lClientID
            AND NOT sp_bInactive
            AND NOT sp_bRetired;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }else{
//         $numRows = mysql_num_rows($result);
      if ($numRows==0) {
         return(0);
      }else {
//            $row = mysql_fetch_array($result);
         $row = $query->row();
         return((integer)$row->lNumRecs);
      }
//      }
   }

   public function lNumClientsViaLetter($strLetter, $bIncludeInactive, $strWhereExtra=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strLetter = strtoupper($strLetter);
      $sqlWhere = $this->strWhereByLetter($strLetter, $bIncludeInactive);

      $strInner = '';
      if (!$bIncludeInactive){
         sqlQualClientViaStatus(
                             $strWhereEx, $strInner,        true,
                             true,        false, null);
         $sqlWhere .= $strWhereEx;
      }

      $sqlStr =
        "SELECT COUNT(*) AS lNumClients
         FROM client_records
            $strInner
            $this->strInnerExtra
         WHERE NOT cr_bRetired
            $sqlWhere $strWhereExtra ;";

      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((integer)$row->lNumClients);
   }

   function strWhereByLetter($strLetter, $bIncludeInactive){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($strLetter=='*'){
         $sqlWhere = ' ';
      }
      elseif ($strLetter=='#'){
         $sqlWhere = "AND ( (LEFT(cr_strLName, 1) > 'Z') OR ((LEFT(cr_strLName, 1) < 'A'))) ";
      }elseif ($strLetter==''){
         $sqlWhere = '';
      }else {
         $sqlWhere = 'AND (LEFT(cr_strLName, 1)='.strPrepStr($strLetter).' ) ';
      }
      if (!$bIncludeInactive) $sqlWhere .= ' AND cst_bShowInDir ';
      return($sqlWhere);
   }

   function loadClientDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage){
      $this->strClientLimit      = " LIMIT $lStartRec, $lRecsPerPage ";
      $this->strClientOrder      = ' cr_strLName, cr_strFName, cr_strMName, cr_lKeyID ';
      $this->strExtraClientWhere = $strWhereExtra;
      $this->loadClientsGeneric();
   }

   public function bValidClientID($lClientID){
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM client_records
         WHERE cr_lKeyID=$lClientID
            AND NOT cr_bRetired;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0) return(false);
      $row = $query->row();
      return($row->lNumRecs > 0);
   }

   public function clientAgeRanges(&$ageRanges){
   //---------------------------------------------------------------------
   // jpz - may be replaced by user-defined ranges
   //---------------------------------------------------------------------
      $ageRanges = array(0 => array('start'=> 0, 'end'=>   0, 'label'=>'Infants under 1'),
                         1 => array('start'=> 1, 'end'=>   2, 'label'=>'Toddlers (1-2)'),
                         2 => array('start'=> 3, 'end'=>   5, 'label'=>'Pre-schoolers (3-5)'),
                         3 => array('start'=> 6, 'end'=>  10, 'label'=>'Elementary (6-10)'),
                         4 => array('start'=>11, 'end'=>  13, 'label'=>'Middle school (11-13)'),
                         5 => array('start'=>14, 'end'=>  18, 'label'=>'High school (14-18)'),
                         6 => array('start'=>19, 'end'=>  21, 'label'=>'Young Adult (19-21)'),
                         7 => array('start'=>22, 'end'=>  64, 'label'=>'Adult (22-64)'),
                         8 => array('start'=>65, 'end'=> 200, 'label'=>'Senior (65+)'));
   }

   public function lClientLocIDViaName($strName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT cl_lKeyID
         FROM client_location
         WHERE NOT cl_bRetired
            AND cl_strLocation='.strPrepStr($strName).';';
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0){
         return(null);
      }else {
         $row = $query->row();
         return((int)$row->cl_lKeyID);
      }
   }

}

?>