<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2011-2015 by Database Austin
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------

   __construct                 ()
   loadSponProgs               ()
   loadSponProgsViaSPID        ($lSPID)
   loadSponProgsGeneric        ($bViaSPID)
   loadSponProgsViaLocID       ($lLocID)
   loadSponProgsViaClientID    ($lClientID){

   loadSponProgsViaSQL         ($sqlStr)
   strSponProgsViaID           ($lSponProgID)
   addNewSponProgram           ()
   updateSponProgram           ()
   retireSponProgram           ($lSponProgID)
   lNumSponPrograms            ()
   strSponProgramDDL           ($lMatchID)
   bTestNoSponPrograms         ($bShowNotice, $strExtraMessage)

   bClientInSponProgram        ($lClientID)
   clearClientSponPrograms     ($lClientID)
   setClientSponProgram        ($lClientID, $lSCID)

   lNumClientsViaSponProg      ($lSponProg)
   lNumSponsorsViaSponProg     ($lSponProg)

---------------------------------------------------------------------
      $this->load->model('sponsorship/msponsorship_programs', 'clsSponProg');
---------------------------------------------------------------------*/


class msponsorship_programs extends CI_Model{

   public $lSCID, $sponCats, $lNumSponPrograms;

   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();
      $this->lSPID = $this->sponCats = $this->lNumSponPrograms = null;
   }

   public function loadSponProgs(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadSponProgsGeneric(false);
   }

   public function loadSponProgsViaSPID($lSPID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->lSPID = $lSPID;
      $this->loadSponProgsGeneric(true);
   }

   public function loadSponProgsGeneric($bViaSPID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bViaSPID){
         $strWhere = " AND sc_lKeyID=$this->lSPID ";
      }else {
         $strWhere = '';
      }

      $sqlStr =
        "SELECT
            sc_lKeyID, sc_bDefault, sc_strProgram, sc_strNotes,
            sc_curDefMonthlyCommit, sc_lACO,
            sc_bRetired, sc_lOriginID, sc_lLastUpdateID,
            UNIX_TIMESTAMP(sc_dteOrigin) AS dteOrigin,
            UNIX_TIMESTAMP(sc_dteLastUpdate) AS dteLastUpdate,
            aco_strCurrencySymbol
         FROM lists_sponsorship_programs
            INNER JOIN admin_aco ON sc_lACO=aco_lKeyID
         WHERE NOT sc_bRetired $strWhere
         ORDER BY sc_strProgram, sc_lKeyID;";

      $this->loadSponProgsViaSQL($sqlStr);
   }

   public function loadSponProgsViaLocID($lLocID){
   //---------------------------------------------------------------------
   // load the sponsorship categories that are supported by a given
   // client location
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            sc_lKeyID, sc_bDefault, sc_strProgram, sc_strNotes,
            sc_curDefMonthlyCommit, sc_lACO,
            sc_bRetired, sc_lOriginID, sc_lLastUpdateID,
            UNIX_TIMESTAMP(sc_dteOrigin) AS dteOrigin,
            UNIX_TIMESTAMP(sc_dteLastUpdate) AS dteLastUpdate,
            aco_strCurrencySymbol
         FROM lists_sponsorship_programs
            INNER JOIN client_loc_supported_sponprogs ON sc_lKeyID=clsp_lSponProgID
            INNER JOIN admin_aco                      ON sc_lACO=aco_lKeyID
         WHERE NOT sc_bRetired
            AND clsp_lLocID=$lLocID
         ORDER BY sc_strProgram, sc_lKeyID;";

      $this->loadSponProgsViaSQL($sqlStr);
   }

   public function loadSponProgsViaClientID($lClientID){
   //---------------------------------------------------------------------
   // load the sponsorship programs that a client is participating in
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            sc_lKeyID, sc_bDefault, sc_strProgram, sc_strNotes,
            sc_curDefMonthlyCommit, sc_lACO,
            sc_bRetired, sc_lOriginID, sc_lLastUpdateID,
            UNIX_TIMESTAMP(sc_dteOrigin) AS dteOrigin,
            UNIX_TIMESTAMP(sc_dteLastUpdate) AS dteLastUpdate,
            aco_strCurrencySymbol
         FROM lists_sponsorship_programs
            INNER JOIN client_supported_sponprogs ON sc_lKeyID=csp_lSponProgID
            INNER JOIN admin_aco                  ON sc_lACO=aco_lKeyID
         WHERE NOT sc_bRetired
            AND csp_lClientID=$lClientID
         ORDER BY sc_strProgram, sc_lKeyID;";

      $this->loadSponProgsViaSQL($sqlStr);
   }

   public function loadSponProgsViaSQL($sqlStr){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $query = $this->db->query($sqlStr);
      $this->lNumSponPrograms = $numRows = $query->num_rows();

      $this->sponProgs = array();
      if ($numRows == 0) {
         $this->sponProgs[0] = new stdClass;
         $sprog = &$this->sponProgs[0];
         $sprog->lKeyID              =
         $sprog->bDefault            =
         $sprog->strProg             =
         $sprog->strNotes            =
         $sprog->curDefMonthlyCommit =
         $sprog->lACO                =
         $sprog->strCurrencySymbol   =
         $sprog->bRetired            =
         $sprog->lOriginID           =
         $sprog->lLastUpdateID       =
         $sprog->dteOrigin           =
         $sprog->dteLastUpdate       = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->sponProgs[$idx] = new stdClass;
            $sprog = &$this->sponProgs[$idx];

            $sprog->lKeyID              = (int)$row->sc_lKeyID;
            $sprog->bDefault            = (bool)$row->sc_bDefault;
            $sprog->strProg             = $row->sc_strProgram;
            $sprog->strNotes            = $row->sc_strNotes;
            $sprog->curDefMonthlyCommit = $row->sc_curDefMonthlyCommit;
            $sprog->lACO                = (int)$row->sc_lACO;
            $sprog->strCurrencySymbol   = $row->aco_strCurrencySymbol;
            $sprog->bRetired            = $row->sc_bRetired;
            $sprog->lOriginID           = (int)$row->sc_lOriginID;
            $sprog->lLastUpdateID       = (int)$row->sc_lLastUpdateID;
            $sprog->dteOrigin           = (int)$row->dteOrigin;
            $sprog->dteLastUpdate       = (int)$row->dteLastUpdate;
            ++$idx;
         }
      }
   }

   public function strSponProgsViaID($lSponProgID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT sc_strProgram
         FROM lists_sponsorship_programs
         WHERE sc_lKeyID=$lSponProgID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows == 0) {
         return('#error#');
      }else {
         $row = $query->row();
         return($row->sc_strProgram);
      }
   }

   public function addNewSponProgram(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'INSERT INTO lists_sponsorship_programs
          SET '.$this->strSQLCommon().",
             sc_bRetired = 0,
             sc_lOriginID = $glUserID,
             sc_dteOrigin = NOW();";

      $query = $this->db->query($sqlStr);
      $this->sponProgs[0]->lKeyID = $this->db->insert_id();
   }

   public function updateSponProgram(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'UPDATE lists_sponsorship_programs
          SET '.$this->strSQLCommon().',
             sc_bRetired = '.($this->sponProgs[0]->bRetired ? '1' : '0').",
             sc_lOriginID = $glUserID,
             sc_dteOrigin = NOW()
          WHERE sc_lKeyID = ".$this->sponProgs[0]->lKeyID.';';
      $this->db->query($sqlStr);
   }

   function retireSponProgram($lSponProgID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

         //-------------------------------------------------------
         // remove records that associate location with program
         //-------------------------------------------------------
      $sqlStr =
         "DELETE FROM client_loc_supported_sponprogs
          WHERE clsp_lSponProgID = $lSponProgID;";
      $this->db->query($sqlStr);

      $sqlStr =
         "UPDATE lists_sponsorship_programs
          SET
             sc_bRetired = 1,
             sc_lOriginID = $glUserID,
             sc_dteOrigin = NOW()
          WHERE sc_lKeyID = $lSponProgID;";
      $this->db->query($sqlStr);
   }

   private function strSQLCommon(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      return('
            sc_bDefault            = '.($this->sponProgs[0]->bDefault ? '1' : '0')    .',
            sc_strProgram          = '.strPrepStr($this->sponProgs[0]->strProg)        .',
            sc_strNotes            = '.strPrepStr($this->sponProgs[0]->strNotes)      .',
            sc_curDefMonthlyCommit = '.number_format($this->sponProgs[0]->curDefMonthlyCommit, 2, '.', '').',
            sc_lACO                = '.$this->sponProgs[0]->lACO.",
            sc_lLastUpdateID       = $glUserID,
            sc_dteLastUpdate       = NOW() ");
   }

   public function lNumSponPrograms(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT COUNT(*) AS lNumCats
         FROM lists_sponsorship_programs
         WHERE NOT sc_bRetired;';

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         return((integer)$row->lNumCats);
      }
   }

   function strSponProgramDDL($lMatchID, $bShowCommit=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';

      $this->loadSponProgsGeneric(false);
      if ($this->lNumSponPrograms > 0){
         foreach ($this->sponProgs as $clsSP){
            $lKeyID = $clsSP->lKeyID;

            if ($bShowCommit){
               $strCommit = ' ('.$clsSP->strCurrencySymbol.' '
                   .number_format($clsSP->curDefMonthlyCommit, 2)
                   .')';
            }else {
               $strCommit = '';
            }
            $strOut .= '<option value="'.$lKeyID.'" '
                .($lKeyID==$lMatchID ? 'selected' : '').' >'.htmlspecialchars($clsSP->strProg)
                .$strCommit
                ."</option>\n";
         }
      }
      return($strOut);
   }

   public function bTestNoSponPrograms($bShowNotice, $strExtraMessage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbAdmin;

      $bNoSponCats = $this->lNumSponPrograms() == 0;
      if ($bNoSponCats && $bShowNotice){
         echoT('<i>There are no sponsorship categories defined in your database. '.$strExtraMessage.'</i><br><br>');
         if ($gbAdmin){
            echoT('You can add sponsorship categories '.strLinkAdd_SponCat('here', false).'.<br><br>');
         }else {
            echoT('Please contact your '.CS_PROGNAME.' administrator to add one or more sponsorship categories.<br><br>');
         }
      }
      return($bNoSponCats);
   }

   public function bClientInSponProgram($lClientID, $lSCID, &$lcsp_lKeyID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lcsp_lKeyID = null;
      $sqlStr =
        "SELECT csp_lKeyID
         FROM client_supported_sponprogs
         WHERE csp_lClientID=$lClientID AND csp_lSponProgID=$lSCID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(false);
      }else {
         $row = $query->row();
         $lcsp_lKeyID = $row->csp_lKeyID;
         return(true);
      }
   }

   public function clearClientSponPrograms($lClientID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "DELETE FROM client_supported_sponprogs
         WHERE csp_lClientID=$lClientID;";

      $query = $this->db->query($sqlStr);
   }

   public function setClientSponProgram($lClientID, $lSCID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "INSERT INTO client_supported_sponprogs
         SET csp_lClientID=$lClientID, csp_lSponProgID=$lSCID;";

      $query = $this->db->query($sqlStr);
   }

   public function lNumClientsViaSponProg($lSponProg){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT DISTINCT sp_lClientID
         FROM sponsor
         WHERE sp_lSponsorProgramID=$lSponProg
            AND sp_lClientID IS NOT NULL
            AND NOT sp_bRetired
            AND NOT sp_bInactive;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   public function clientListViaSponProg($lProgID, $bActiveOnly, &$clientList){
   //---------------------------------------------------------------------
   // return an array of client IDs that are sponsored under a specified
   // sponsorship program.
   //---------------------------------------------------------------------
      $clientList = array();
      $sqlStr =
        "SELECT DISTINCT sp_lClientID
         FROM sponsor
         WHERE sp_lSponsorProgramID=$lProgID
            AND sp_lClientID IS NOT NULL
            AND NOT sp_bRetired "
            .($bActiveOnly ? ' AND NOT sp_bInactive ' : '').'
         ORDER BY sp_lClientID;';

      $query = $this->db->query($sqlStr);
      foreach ($query->result() as $row){
         $clientList[] = $row->sp_lClientID;
      }
   }

   public function clientsViaSponProg($lProgID, $bActiveOnly, $clsClients){
   //---------------------------------------------------------------------
   // results returned in $clsClients->clients[]
   //---------------------------------------------------------------------
      $this->clientListViaSponProg($lProgID, $bActiveOnly, $clientList);
      if (count($clientList) > 0){
         $clsClients->strExtraClientWhere = ' AND cr_lKeyID IN ('.implode(',', $clientList).') ';
      }else {
         $clsClients->strExtraClientWhere = ' AND 0 ';
      }
      $clsClients->loadClientsGeneric();
   }

   public function lNumSponsorsViaSponProg($lSponProg){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumSponsors
         FROM sponsor
         WHERE sp_lSponsorProgramID=$lSponProg
            AND NOT sp_bRetired
            AND NOT sp_bInactive;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((integer)$row->lNumSponsors);
   }

   public function sponsorsViaSponProg($lSponProg, $bActiveOnly, $clsSpon){
   //---------------------------------------------------------------------
   // results returned in $clsSpon->sponInfo[]
   //---------------------------------------------------------------------
      $strWhere = " AND (sp_lSponsorProgramID=$lSponProg) "
            .($bActiveOnly ? ' AND NOT sp_bInactive ' : '');
      $clsSpon->sponsorInfoGenericViaWhere($strWhere, '');
   }

   public function lProgIDViaName($strName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT sc_lKeyID
         FROM lists_sponsorship_programs
         WHERE NOT sc_bRetired
            AND sc_strProgram='.strPrepStr($strName).';';
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0){
         return(null);
      }else {
         $row = $query->row();
         return((int)$row->sc_lKeyID);
      }
   }





   /*------------------------------------------------------------
                       R E P O R T S
   ------------------------------------------------------------*/

   function lNumRecsInSponViaProgReport(
                           &$sRpt,
                           $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lSponProg        = $sRpt->lSponProg;
      $bIncludeInactive = $sRpt->bIncludeInactive;

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $sqlStr =
        "SELECT sp_lKeyID
         FROM sponsor
         WHERE sp_lSponsorProgramID=$lSponProg
            AND NOT sp_bRetired
            ".($bIncludeInactive ? '' : ' AND NOT sp_bInactive ')."
            $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strSponViaProgReportExport(&$sRpt,   &$displayData,
                                       $bReport, $lStartRec,   $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         $this->strSponViaProgReport($sRpt,   $displayData,
                                     $lStartRec,   $lRecsPerPage);
      }else {
         return($this->strSponViaProgExport($sRpt));
      }
   }

   function strSponViaProgReport(&$sRpt,     &$displayData,
                                 $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsSpon = new msponsorship;
      $lSponProgID = $sRpt->lSponProg;
      $bIncludeInactive = $sRpt->bIncludeInactive;

      $displayData['strRptTitle'] = 'Sponsors of the <b>"'
                       .htmlspecialchars($this->strSponProgsViaID($lSponProgID)).'"</b> program';

      $displayData['showFields'] = new stdClass;
      $displayData['showFields']->bSponsorID    = true;
      $displayData['showFields']->bName         = true;
      $displayData['showFields']->bSponsorInfo  = true;
      $displayData['showFields']->bClient       = true;
      $displayData['showFields']->bLocation     = true;
      $displayData['showFields']->bSponAddr     = true;

      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";

      $strWhere = " AND (sp_lSponsorProgramID=$lSponProgID) "
            .($bIncludeInactive ? '' : ' AND NOT sp_bInactive ');
      $clsSpon->sponsorInfoGenericViaWhere($strWhere, $strLimit);
      $displayData['sponInfo'] = &$clsSpon->sponInfo;
      $displayData['lNumSpon'] = $clsSpon->lNumSponsors;
   }

   function strSponViaProgExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lSponProgID = $sRpt->lSponProg;
      $bIncludeInactive = $sRpt->bIncludeInactive;
      $sqlStr =
           'SELECT '.strExportFields_Sponsor()."
            FROM sponsor
               INNER JOIN people_names AS sponpeep   ON sponpeep.pe_lKeyID   = sp_lForeignID
               INNER JOIN admin_aco AS commitACO     ON commitACO.aco_lKeyID = sp_lCommitmentACO
               INNER JOIN lists_sponsorship_programs ON sc_lKeyID            = sp_lSponsorProgramID
               LEFT  JOIN client_records             ON cr_lKeyID            = sp_lClientID
               LEFT  JOIN client_location            ON cr_lLocationID       = cl_lKeyID
               LEFT  JOIN lists_client_vocab         ON cv_lKeyID            = cr_lVocID
               LEFT  JOIN people_names AS honpeep    ON honpeep.pe_lKeyID    = sp_lHonoreeID
               LEFT  JOIN lists_generic AS atab      ON sp_lAttributedTo     = lgen_lKeyID
            WHERE NOT sp_bRetired
               AND ( sp_lSponsorProgramID=$lSponProgID )"
               .($bIncludeInactive ? '' : ' AND NOT sp_bInactive ').'
            ORDER BY sp_lKeyID;';
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }






}


?>