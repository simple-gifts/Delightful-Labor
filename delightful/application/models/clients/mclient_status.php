<?php
/*---------------------------------------------------------------------
 copyright (c) 2012-2015 Database Austin
 Austin, Texas 78759

 author: John Zimmerman

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
   Status Categories - top level grouping (such as "Children's Status", "Infant Status", "Elder Status")
   Status Entries    - entries within a category (such as "okay", "no longer in program", etc)
   Status Records    - status applied to individual clients
---------------------------------------------------------------------

   __construct()

Categories
============
   loadClientStatCats    ($bExcludeRetired, $bLoadSingleCat, $lCatID)
   addNewClientStatusCat ()
   updateClientStatusCat ()
   sqlCommonStatCat      ()
   bTestNoStatCats       ($strExtraMessage, &$strOutMsg)
   strClientStatCatDDL   ($bAddBlank, $lMatchID)
   strStatCatViaCatID    ($lStatCatID)
   htmlViewClientStatCat ($bShowLinks)
   removeCategory        ($lCatID)

Entries
============
   lNumClientStatEntriesViaCat     ($lClientStatusCatID)
   loadClientStatCatsEntries       ($bViaStatCatID,         $lSCID,
   clearClientStatEntryDefForSCID  ($lSCID)
   addNewClientStatusEntry         ()
   updateClientStatusEntry         ()
   removeEntry                     ($lCatEntryID)
   sqlCommonStatEntry              ()
   bTestNoStatEntries              ($lSCID, $bShowNotice, $strExtraMessage)
   strClientStatEntriesDDL         ($lSCID, $bAddBlank, $lMatchID)

Clients
============
   lInsertClientStatus         ()
   updateClientStatus          ()
   lNumClientStatEntries       ($lCID)
   fullStatusHistory           ($bViaStatRecID, $lStatRecID)
   updateClientStatAssociation ($lCID, $lCStatID)
   currentClientStatusViaCID   ($lCID, &$clsStatInfo)
   lPacketStatusID             ($lClientID)

---------------------------------------------------------------------
      $this->load->model('clients/mclient_status', 'clsClientStat');
---------------------------------------------------------------------*/


class mclient_status extends CI_Model{

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->lClientID  = null;
      $this->lNumStatus = 0;
      $this->lNumStatCats = $this->statCats = null;
   }

   public function loadClientStatCats($bExcludeRetired, $bLoadSingleCat, $lCatID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           'SELECT
               csc_lKeyID, csc_strCatName, csc_strDescription, csc_bProtected,
               csc_bRetired, csc_lOriginID, csc_lLastUpdateID,
               UNIX_TIMESTAMP(csc_dteOrigin)     AS dteOrigin,
               UNIX_TIMESTAMP(csc_dteLastUpdate) AS dteLastUpdate
            FROM client_status_cats
            WHERE 1 '.($bExcludeRetired ? ' AND NOT csc_bRetired ' : '').' '
               .($bLoadSingleCat ? " AND csc_lKeyID=$lCatID " : '').'
            ORDER BY csc_strCatName, csc_lKeyID;';
      $query = $this->db->query($sqlStr);
      $this->lNumStatCats = $numRows = $query->num_rows();

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }else{
      $this->statCats = array();
//         $this->lNumStatCats = $numRows = mysql_num_rows($result);
      if ($numRows==0) {
         $this->statCats[0] = new stdClass;
         $this->statCats[0]->lKeyID         =
         $this->statCats[0]->strCatName     =
         $this->statCats[0]->strDescription =
         $this->statCats[0]->bProtected     =
         $this->statCats[0]->bRetired       =
         $this->statCats[0]->lOriginID      =
         $this->statCats[0]->lLastUpdateID  =
         $this->statCats[0]->dteOrigin      =
         $this->statCats[0]->dteLastUpdate  = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
//         for ($idx=0; $idx<$numRows; ++$idx) {
//            $row = mysql_fetch_array($result);
            $this->statCats[$idx] = new stdClass;

            $this->statCats[$idx]->lKeyID         = $row->csc_lKeyID;
            $this->statCats[$idx]->strCatName     = $row->csc_strCatName;
            $this->statCats[$idx]->strDescription = $row->csc_strDescription;
            $this->statCats[$idx]->bProtected     = $row->csc_bProtected;
            $this->statCats[$idx]->bRetired       = $row->csc_bRetired;
            $this->statCats[$idx]->lOriginID      = $row->csc_lOriginID;
            $this->statCats[$idx]->lLastUpdateID  = $row->csc_lLastUpdateID;
            $this->statCats[$idx]->dteOrigin      = $row->dteOrigin;
            $this->statCats[$idx]->dteLastUpdate  = $row->dteLastUpdate;
            ++$idx;
         }
      }
   }

   public function lNumClientStatEntriesViaCat($lClientStatusCatID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM lists_client_status_entries
         WHERE
            cst_lClientStatusCatID=$lClientStatusCatID
            AND NOT cst_bRetired;";
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
         $row = $query->row();
//            $row = mysql_fetch_array($result);
         return((integer)$row->lNumRecs);
      }
//      }
   }

   public function addNewClientStatusCat(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
          'INSERT INTO client_status_cats
           SET '.$this->sqlCommonStatCat().",
              csc_bRetired  = 0,
              csc_lOriginID = $glUserID,
              csc_dteOrigin = NOW();";
      $query = $this->db->query($sqlStr);
//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }
      $this->statCats[0]->lKeyID = $this->db->insert_id();
   }

   public function updateClientStatusCat(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
          'UPDATE client_status_cats
           SET '.$this->sqlCommonStatCat().',
              csc_bRetired = '.($this->statCats[0]->bRetired ? '1' : '0').'
           WHERE csc_lKeyID = '.$this->statCats[0]->lKeyID.';';
      $query = $this->db->query($sqlStr);
//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }
   }

   private function sqlCommonStatCat(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      return('
            csc_strCatName     = '.strPrepStr($this->statCats[0]->strCatName).',
            csc_strDescription = '.strPrepStr($this->statCats[0]->strDescription).",
            csc_dteLastUpdate  = NOW(),
            csc_lLastUpdateID  = $glUserID ");
   }

   public function bTestNoStatCats($strExtraMessage, &$strOutMsg){
   //---------------------------------------------------------------------
   // caller should first call $clsClientStat->loadClientStatCats(true, false, null);
   //---------------------------------------------------------------------
      global $gbAdmin;

      $strOutMsg = '';
      $bNoStatCat = $this->lNumStatCats <= 0;
      if ($bNoStatCat){
         $strOutMsg .= '<i>There are no client status categories defined in your database. <br>'.$strExtraMessage.'</i><br><br>';
         if ($gbAdmin){
            $strOutMsg .= 'You can add client status categories '
               .strLinkAdd_ClientStatCat('here', false).'.<br><br>';
         }else {
            $strOutMsg .= 'Please contact your '.CS_PROGNAME.' administrator to add one or more client status categories.<br><br>';
         }
      }
      return($bNoStatCat);
   }

   public function strClientStatCatDDL($bAddBlank, $lMatchID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

      $this->loadClientStatCats(true, false, null);
      $strOut = '';

      if ($bAddBlank){
         $strOut .= '<option value="-1">&nbsp;</option>'."\n";
      }

      foreach ($this->statCats as $clsCats){
         $lKeyID = $clsCats->lKeyID;
         $bMatch = $lMatchID==$lKeyID;
         if (!$clsCats->bRetired || $bMatch){
            $strOut .= '<option value="'.$lKeyID.'" '.($bMatch ? 'SELECTED' : '').'>'
               .htmlspecialchars($clsCats->strCatName).'</option>'."\n";
         }
      }
      return($strOut);
   }

   public function strStatCatViaCatID($lStatCatID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT csc_strCatName
         FROM client_status_cats
         WHERE csc_lKeyID=$lStatCatID;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }else{
//         $numRows = mysql_num_rows($result);
      if ($numRows==0) {
         return('#error#');
      }else {
//         $row = mysql_fetch_array($result);
         $row = $query->row();
         return($row->csc_strCatName);
      }
//      }
   }

   public function removeCategory($lCatID){
      global $glUserID;

      $this->db->query("UPDATE client_status_cats
                        SET csc_bRetired=1,
                           csc_lLastUpdateID=$glUserID
                        WHERE csc_lKeyID=$lCatID;");
   }

   public function loadClientStatCatsEntries(
                            $bViaStatCatID,         $lSCID,
                            $bViaClientStatEntryID, $lCSEID,
                            $bExcludeRetired,       $bDefaultFirst){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bViaClientStatEntryID){
         $strWhere = " AND cst_lKeyID=$lCSEID ";
      }elseif ($bViaStatCatID) {
         $strWhere = " AND cst_lClientStatusCatID=$lSCID ";
      }else {
         $strWhere = '';
      }

      $sqlStr =
           "SELECT
               cst_lKeyID, cst_lClientStatusCatID, cst_strStatus,
               cst_bAllowSponsorship, cst_bShowInDir, cst_bDefault,
               cst_bRetired,
               cst_lOriginID, cst_lLastUpdateID,
               UNIX_TIMESTAMP(cst_dteOrigin)     AS dteOrigin,
               UNIX_TIMESTAMP(cst_dteLastUpdate) AS dteLastUpdate,
               csc_strCatName
            FROM lists_client_status_entries
               INNER JOIN client_status_cats ON cst_lClientStatusCatID=csc_lKeyID
            WHERE 1 $strWhere "
               .($bExcludeRetired ? ' AND NOT cst_bRetired ' : '').'
            ORDER BY '.($bDefaultFirst ? 'cst_bDefault DESC, ' : '').' cst_strStatus, cst_lKeyID;';

      $query = $this->db->query($sqlStr);
      $this->lNumCatEntries = $numRows = $query->num_rows();
         $this->catEntries = array();
      if ($numRows==0) {
         $this->catEntries[0] = new stdClass;
         $catEntry = &$this->catEntries[0];
         $catEntry->lKeyID             =
         $catEntry->lClientStatusCatID =
         $catEntry->strClientStatusCat =
         $catEntry->strStatusEntry     =
         $catEntry->bAllowSponsorship  =
         $catEntry->bShowInDir         =
         $catEntry->bDefault           =
         $catEntry->bRetired           =
         $catEntry->lOriginID          =
         $catEntry->lLastUpdateID      =
         $catEntry->strCatName         =
         $catEntry->dteOrigin          =
         $catEntry->dteLastUpdate      = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->catEntries[$idx] = new stdClass;
            $catEntry = &$this->catEntries[$idx];

            $catEntry->lKeyID             = (int)$row->cst_lKeyID;
            $catEntry->lClientStatusCatID = (int)$row->cst_lClientStatusCatID;
            $catEntry->strClientStatusCat = $row->csc_strCatName;
            $catEntry->strStatusEntry     = $row->cst_strStatus;
            $catEntry->bAllowSponsorship  = $row->cst_bAllowSponsorship;
            $catEntry->bShowInDir         = $row->cst_bShowInDir;
            $catEntry->bDefault           = $row->cst_bDefault;
            $catEntry->bRetired           = $row->cst_bRetired;
            $catEntry->lOriginID          = (int)$row->cst_lOriginID;
            $catEntry->lLastUpdateID      = (int)$row->cst_lLastUpdateID;
            $catEntry->strCatName         = $row->csc_strCatName;
            $catEntry->dteOrigin          = (int)$row->dteOrigin;
            $catEntry->dteLastUpdate      = (int)$row->dteLastUpdate;

            ++$idx;
         }
      }
   }

   public function clearClientStatEntryDefForSCID($lSCID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "UPDATE lists_client_status_entries
         SET cst_bDefault=0, cst_dteLastUpdate=cst_dteLastUpdate
         WHERE cst_lClientStatusCatID=$lSCID;";
      $query = $this->db->query($sqlStr);

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }
   }

   public function addNewClientStatusEntry(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
          'INSERT INTO lists_client_status_entries
           SET '.$this->sqlCommonStatEntry().",
              cst_bRetired  = 0,
              cst_lOriginID = $glUserID,
              cst_dteOrigin = NOW();";
      $query = $this->db->query($sqlStr);

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }
      $this->statCats[0]->lKeyID = $this->db->insert_id();
   }

   public function updateClientStatusEntry(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
          'UPDATE lists_client_status_entries
           SET '.$this->sqlCommonStatEntry().',
              cst_bRetired = '.($this->catEntries[0]->bRetired ? '1' : '0').'
           WHERE cst_lKeyID = '.$this->catEntries[0]->lKeyID.';';
      $query = $this->db->query($sqlStr);

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }
   }

   private function sqlCommonStatEntry(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      return(
           'cst_lClientStatusCatID ='.$this->catEntries[0]->lClientStatusCatID.',
            cst_strStatus          ='.strPrepStr($this->catEntries[0]->strStatusEntry).',
            cst_bAllowSponsorship  = '.($this->catEntries[0]->bAllowSponsorship ? '1' : '0').',
            cst_bShowInDir         = '.($this->catEntries[0]->bShowInDir        ? '1' : '0').',
            cst_bDefault           = '.($this->catEntries[0]->bDefault          ? '1' : '0').",
            cst_dteLastUpdate      = NOW(),
            cst_lLastUpdateID      = $glUserID ");
   }

   public function removeEntry($lCatEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $this->db->query("UPDATE lists_client_status_entries
                        SET cst_bRetired=1,
                           cst_lLastUpdateID=$glUserID
                        WHERE cst_lKeyID=$lCatEntryID;");
   }

   public function bTestNoStatEntries($lSCID, $bShowNotice, $strExtraMessage, &$strErrorMsg){
   //---------------------------------------------------------------------
   // must first call $clsClientStat->loadClientStatCatsEntries(
   //                         true,         $lSCID,
   //                         false, null,
   //                         true,  false);
   //---------------------------------------------------------------------
      global $gbAdmin;
      $strErrorMsg = '';

      $bNoStatEntry = $this->lNumCatEntries <= 0;

      if ($bNoStatEntry && $bShowNotice){
         $this->loadClientStatCats(false, true, $lSCID);
         $strErrorMsg .= '<i>There are no client status entries defined
                for status category <b>'.htmlspecialchars($this->statCats[0]->strCatName)
                .'</b>. <br><br>'.$strExtraMessage.'</i><br><br>';
         if ($gbAdmin){
            $strErrorMsg .= 'You can add client status entries '
               .strLinkView_ClientStatEntries($lSCID, 'here', false).'.<br><br>';
         }else {
            $strErrorMsg .= 'Please contact your '.CS_PROGNAME.' administrator to add one or more client status entries.<br><br>';
         }
      }
      return($bNoStatEntry);
   }

   public function strClientStatEntriesDDL($lSCID, $bAddBlank, $lMatchID, $bShowDirSpon=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gstrSeed;

      $this->loadClientStatCatsEntries(
                            true,  $lSCID,
                            false, null,
                            false, false);
      $strOut = '';

      if ($bAddBlank){
         $strOut .= '<option value="-1">&nbsp;</option>'."\n";
      }

      foreach ($this->catEntries as $clsEntry){
         $lKeyID = $clsEntry->lKeyID;
         $bMatch = $lMatchID==$lKeyID;
         if ($bShowDirSpon){
            if ($clsEntry->bAllowSponsorship){
               $strDirSpon = '&nbsp;&nbsp;(allows sponsorship / ';
            }else {
               $strDirSpon = '&nbsp;&nbsp;(sponsorship not allowed / ';
            }
            if ($clsEntry->bShowInDir){
               $strDirSpon .= 'appears in directory)';
            }else {
               $strDirSpon .= 'does not appear in directory)';
            }
         }else {
            $strDirSpon = '';
         }
         if (!$clsEntry->bRetired || $bMatch){
            $strOut .= '<option value="'.$lKeyID.'" '.($bMatch ? 'SELECTED' : '').'>'
               .htmlspecialchars($clsEntry->strStatusEntry).$strDirSpon.'</option>'."\n";
         }
      }
      return($strOut);
   }

   public function lInsertClientStatus(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      if ($this->clientStatus[0]->bIncludeNotesInPacket) $this->resetPacket($this->clientStatus[0]->lClientID);

      $sqlStr =
          'INSERT INTO client_status
           SET '.$this->strSQLCommonClientStatusEntry().',
              csh_lClientID    = '.$this->clientStatus[0]->lClientID.",
              csh_bRetired     = 0,
              csh_dteOrigin    = NOW(),
              csh_lOriginID    = $glUserID,
              csh_lLastUpdateID= $glUserID;";
      $query = $this->db->query($sqlStr);
      $this->clientStatus[0]->lKeyID = $lKeyID = $this->db->insert_id();
      return($lKeyID);
   }

   private function resetPacket($lClientID){
      $sqlStr =
         "UPDATE client_status
          SET csh_bIncludeNotesInPacket=0,
              csh_dteLastUpdate=csh_dteLastUpdate
          WHERE csh_lClientID=$lClientID
              AND NOT csh_bRetired;";
      $this->db->query($sqlStr);
   }

   public function updateClientStatus(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      if ($this->clientStatus[0]->bIncludeNotesInPacket) $this->resetPacket($this->clientStatus[0]->lClientID);
      $sqlStr =
          'UPDATE client_status
           SET '.$this->strSQLCommonClientStatusEntry().',
              csh_bRetired     = '.($this->clientStatus[0]->bRetired ? '1' : '0').",
              csh_lLastUpdateID= $glUserID
           WHERE csh_lKeyID= ".$this->clientStatus[0]->lKeyID.';';
      $query = $this->db->query($sqlStr);
   }

   public function removeClientStatusEntry($lClientStatusEntryID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
          "UPDATE client_status
           SET
              csh_bRetired     = 1,
              csh_lLastUpdateID= $glUserID
           WHERE csh_lKeyID= $lClientStatusEntryID;";
      $query = $this->db->query($sqlStr);
   }

   private function strSQLCommonClientStatusEntry(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return('
              csh_lStatusID             = '.$this->clientStatus[0]->lStatusID.',
              csh_bIncludeNotesInPacket = '.($this->clientStatus[0]->bIncludeNotesInPacket ? '1' : '0').',
              csh_dteStatusDate         = '.strPrepDate($this->clientStatus[0]->dteStatus).',
              csh_strStatusTxt          = '.strPrepStr($this->clientStatus[0]->strStatusTxt).' ');
   }

   public function lNumClientStatEntries($lCID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM client_status
         WHERE
            csh_lClientID=$lCID
            AND NOT csh_bRetired;";
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
//         $row = mysql_fetch_array($result);
         $row = $query->row();
         return((integer)$row->lNumRecs);
      }
//      }
   }

   public function fullStatusHistory($bViaStatRecID, $lStatRecID, $bNewestFirst=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_null($this->lClientID)) screamForHelp('UNINITIALIZED CLASS<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $sqlStr =
          "SELECT
             csh_lKeyID, csh_lClientID, csh_lStatusID,
             csh_bIncludeNotesInPacket, csh_strStatusTxt,
             csh_dteStatusDate,
             csh_bRetired, csh_lOriginID, csh_lLastUpdateID,
             UNIX_TIMESTAMP(csh_dteOrigin)     AS dteOrigin,
             UNIX_TIMESTAMP(csh_dteLastUpdate) AS dteLastUpdate,

             cst_lKeyID, cst_bAllowSponsorship, cst_bShowInDir, cst_strStatus, cst_lClientStatusCatID,
             csc_strCatName
           FROM client_status
               INNER JOIN lists_client_status_entries ON csh_lStatusID         =cst_lKeyID
               INNER JOIN client_status_cats          ON cst_lClientStatusCatID=csc_lKeyID
           WHERE (csh_lClientID=$this->lClientID) AND (NOT csh_bRetired) "
              .($bViaStatRecID ? " AND csh_lKeyID=$lStatRecID " : '').'
           ORDER BY csh_dteStatusDate '.($bNewestFirst ? 'ASC': 'DESC').', csh_lKeyID DESC;';
      $query = $this->db->query($sqlStr);
      $this->lNumClientStatus = $numRows = $query->num_rows();

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }else{
//         $this->lNumClientStatus = $numRows = mysql_num_rows($result);
      $this->clientStatus = array();
      if ($numRows==0) {
         $this->clientStatus[0] = new stdClass;
         $this->clientStatus[0]->lKeyID                 =
         $this->clientStatus[0]->lClientID              =
         $this->clientStatus[0]->lStatusID              =
         $this->clientStatus[0]->bIncludeNotesInPacket  =
         $this->clientStatus[0]->strStatusTxt           =
         $this->clientStatus[0]->dteStatus              =
         $this->clientStatus[0]->bRetired               =
         $this->clientStatus[0]->lOriginID              =
         $this->clientStatus[0]->lLastUpdateID          =
         $this->clientStatus[0]->dteOrigin              =
         $this->clientStatus[0]->dteLastUpdate          =
         $this->clientStatus[0]->bAllowSponsorship      =
         $this->clientStatus[0]->bShowInDir             =
         $this->clientStatus[0]->strStatus              =
         $this->clientStatus[0]->lClientStatusCatID     =
         $this->clientStatus[0]->strCatName             = null;
      }else {
         $idx = 0;

         foreach ($query->result() as $row){
//            $row = mysql_fetch_array($result);
            $this->clientStatus[$idx] = new stdClass;

            $this->clientStatus[$idx]->lKeyID                 = $row->csh_lKeyID;
            $this->clientStatus[$idx]->strStatus              = $row->cst_strStatus;
            $this->clientStatus[$idx]->strStatusTxt           = $row->csh_strStatusTxt;
            $this->clientStatus[$idx]->lClientID              = $row->csh_lClientID;
            $this->clientStatus[$idx]->lStatusID              = $row->csh_lStatusID;
            $this->clientStatus[$idx]->bIncludeNotesInPacket  = $row->csh_bIncludeNotesInPacket;
            $this->clientStatus[$idx]->dteStatus              = dteMySQLDate2Unix($row->csh_dteStatusDate);
            $this->clientStatus[$idx]->bRetired               = $row->csh_bRetired;
            $this->clientStatus[$idx]->lOriginID              = $row->csh_lOriginID;
            $this->clientStatus[$idx]->lLastUpdateID          = $row->csh_lLastUpdateID;
            $this->clientStatus[$idx]->dteOrigin              = $row->dteOrigin;
            $this->clientStatus[$idx]->dteLastUpdate          = $row->dteLastUpdate;
            $this->clientStatus[$idx]->bAllowSponsorship      = $row->cst_bAllowSponsorship;
            $this->clientStatus[$idx]->bShowInDir             = $row->cst_bShowInDir;
            $this->clientStatus[$idx]->lClientStatusCatID     = $row->cst_lClientStatusCatID;
            $this->clientStatus[$idx]->strCatName             = $row->csc_strCatName;

            ++$idx;
         }
      }
//      }
   }

   public function updateClientStatAssociation($lCID, $lCStatID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         "UPDATE client_records
          SET
             cr_lStatusCatID=$lCStatID,
             cr_lLastUpdateID=$glUserID
          WHERE cr_lKeyID=$lCID;";
      $query = $this->db->query($sqlStr);
   }

   public function currentClientStatusViaCID($lCID, &$clsStatInfo){
  //---------------------------------------------------------------------
  //
  //---------------------------------------------------------------------
      $clsStatInfo = new stdClass;
      $clsStatInfo->lClientID = $lCID;

      $sqlStr =
         "SELECT cst_lKeyID, cst_bAllowSponsorship, cst_bShowInDir, cst_strStatus,
            csh_dteStatusDate,  csh_strStatusTxt
          FROM tbl_sponsor_cstat_history
              INNER JOIN tbl_sponsor_cstatus ON csh_lStatusID=cst_lKeyID
          WHERE (csh_lClientID=$lCID) AND (NOT csh_bRetired)
          ORDER BY csh_dteStatusDate DESC, csh_lKeyID DESC
          LIMIT 0, 1;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

//      $result = mysql_query($sqlStr);
//     if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//        screamForHelp('Unexpected SQL error');
//     }else{
//        $numRows = mysql_num_rows($result);
      if ($numRows==0) {
         $clsStatInfo->bAppearInDir   =
         $clsStatInfo->bSponsorable   = false;
         $clsStatInfo->strStatus      = '(not available)';
         $clsStatInfo->strStatusNotes = '';
         $clsStatInfo->lStatusID      = null;
      }else {
//            $row = mysql_fetch_array($result);
         $row = $query->row();
         $clsStatInfo->bAppearInDir   = $row->cst_bShowInDir;
         $clsStatInfo->bSponsorable   = $row->cst_bAllowSponsorship;
         $clsStatInfo->strStatus      = $row->cst_strStatus;
         $clsStatInfo->strStatusNotes = $row->csh_strStatusTxt;
         $clsStatInfo->lStatusID      = $row->cst_lKeyID;
         $clsStatInfo->dteStatus      = dteMySQLDate2Unix($row->csh_dteStatusDate);
      }
//     }
  }

   public function packetStatusInfo($lClientID){
   //---------------------------------------------------------------------
   // if packet status not selected, return most recent status
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            csh_lKeyID, csh_strStatusTxt, csh_dteStatusDate
         FROM client_status
         WHERE
            NOT csh_bRetired
            AND csh_lClientID=$lClientID
         ORDER BY csh_bIncludeNotesInPacket DESC, csh_dteStatusDate DESC
         LIMIT 0,1;";
      $query = $this->db->query($sqlStr);

      if ($query->num_rows() == 0) return(null);

      $row = $query->row();
      $packetStatus = new stdClass;
      $packetStatus->clientID     = $lClientID;
      $packetStatus->statusID     = $row->csh_lKeyID;
      $packetStatus->strStatusTxt = $row->csh_strStatusTxt;
      $packetStatus->dteStatus    = dteMySQLDate2Unix($row->csh_dteStatusDate);
      return($packetStatus);
   }

   public function statusInfoViaStatID($lStatusID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $statInfo = new stdClass;
      $sqlStr =
        "SELECT
            cst_lKeyID, cst_lClientStatusCatID, cst_strStatus, cst_bAllowSponsorship,
            cst_bShowInDir, cst_bDefault, cst_bRetired, cst_lOriginID,
            cst_lLastUpdateID,
            UNIX_TIMESTAMP(cst_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(cst_dteLastUpdate) AS dteUpdate,
            csc_strCatName

         FROM lists_client_status_entries
            INNER JOIN client_status_cats ON cst_lClientStatusCatID = csc_lKeyID
         WHERE cst_lKeyID=$lStatusID;";

      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0) return(null);
      $row = $query->row();

      $statInfo->lKeyID              = $row->cst_lKeyID;
      $statInfo->lClientStatusCatID  = $row->cst_lClientStatusCatID;
      $statInfo->strStatus           = $row->cst_strStatus;
      $statInfo->bAllowSponsorship   = $row->cst_bAllowSponsorship;
      $statInfo->bShowInDir          = $row->cst_bShowInDir;
      $statInfo->bDefault            = $row->cst_bDefault;
      $statInfo->bRetired            = $row->cst_bRetired;
      $statInfo->lOriginID           = $row->cst_lOriginID;
      $statInfo->lLastUpdateID       = $row->cst_lLastUpdateID;
      $statInfo->dteOrigin           = $row->dteOrigin;
      $statInfo->dteUpdate           = $row->dteUpdate;
      $statInfo->strCatName          = $row->csc_strCatName;

      return($statInfo);
   }

   public function lNumClientsViaClientStatCat($lStatCatID, $bTestActive, $bActive){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      sqlQualClientViaStatus(
                       $sqlWhereSubquery, $strInner, $bTestActive,
                       $bActive,          false,     null);

      $sqlStr =
        "SELECT COUNT(cr_lKeyID) AS lNumRecs
            FROM client_records
               $strInner
            WHERE 1
               AND NOT cr_bRetired
               $sqlWhereSubquery
               AND cst_lClientStatusCatID=$lStatCatID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return($row->lNumRecs);
   }

   public function lNumClientsViaClientStatID($lStatID, $bTestActive, $bActive){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      sqlQualClientViaStatus(
                       $sqlWhereSubquery, $strInner, $bTestActive,
                       $bActive,          false,     null);

      $sqlStr =
        "SELECT COUNT(cr_lKeyID) AS lNumRecs
            FROM client_records
               $strInner
            WHERE 1
               AND NOT cr_bRetired
               $sqlWhereSubquery
               AND csh_lStatusID=$lStatID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return($row->lNumRecs);
   }

   function verifyClientStatus(&$lNumGood, &$lNumBad, &$badStatus){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumGood = $lNumBad = 0;
      $badStatus = array();

      $strTmpName = 'tmp_status';
      $this->createTempStatNoRetired($strTmpName);
      $sqlStr =
        "SELECT COUNT(tmp_lKeyID) AS lNumStat, cr_lKeyID, cr_strFName,
            cr_strLName, cr_strMName, cr_lStatusCatID, csc_strCatName
         FROM client_records
            LEFT JOIN $strTmpName ON tmp_lClientID=cr_lKeyID
            LEFT JOIN client_status_cats ON cr_lStatusCatID=csc_lKeyID
         WHERE NOT cr_bRetired
         GROUP BY cr_lKeyID
         ORDER BY COUNT(tmp_lKeyID),
            cr_strLName, cr_strFName, cr_strMName, cr_lKeyID;";
      $query = $this->db->query($sqlStr);

      $lNumRecs = $query->num_rows();
      if ($lNumRecs > 0) {
         foreach ($query->result() as $row){
            if ($row->lNumStat > 0){
               ++$lNumGood;
            }else {
               $badStatus[$lNumBad] = new stdClass;
               $bad = &$badStatus[$lNumBad];

               $bad->lKeyID       = (int)$row->cr_lKeyID;
               $bad->strFName     = $row->cr_strFName;
               $bad->strMName     = $row->cr_strMName;
               $bad->strLName     = $row->cr_strLName;
               $bad->lStatusCatID = (int)$row->cr_lStatusCatID;
               $bad->strCatName   = $row->csc_strCatName;

               ++$lNumBad;
            }
         }
      }
/* -------------------------------------
$zzzlPos = @strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, @strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$lNumGood = $lNumGood <br></font>\n");
$zzzlPos = @strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, @strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$lNumBad = $lNumBad <br></font>\n");
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$badStatus   <pre>');
echo(htmlspecialchars( print_r($badStatus, true))); echo('</pre></font><br>');
// ------------------------------------- */



   }

   function createTempStatNoRetired($strTmpTable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $query = $this->db->query("DROP TABLE IF EXISTS $strTmpTable;");
      $sqlStr =
        '-- CREATE TABLE IF NOT EXISTS '.$strTmpTable.' (
         CREATE TEMPORARY TABLE IF NOT EXISTS '.$strTmpTable.' (
           tmp_lKeyID             int(11) NOT NULL AUTO_INCREMENT,
           tmp_lClientID          int(11) DEFAULT NULL,
           tmp_lStatusID          int(11) NOT NULL,
           tmp_dteStatusDate      date    DEFAULT \'0000-00-00\',
         PRIMARY KEY (tmp_lKeyID),
            KEY tmp_lClientID (tmp_lClientID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;';
      $query = $this->db->query($sqlStr);

      $sqlStr =
         'INSERT INTO '.$strTmpTable.'
           (tmp_lKeyID,
            tmp_lClientID,
            tmp_lStatusID,
            tmp_dteStatusDate)
          SELECT csh_lKeyID, csh_lClientID, csh_lStatusID, csh_dteStatusDate
          FROM client_status
          WHERE NOT csh_bRetired;';
      $query = $this->db->query($sqlStr);
   }

   function correctClientStatus(&$badStatus){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $this->loadDefaultStatusCats($cats);

      $sqlBase =
        "INSERT INTO client_status
         SET
            csh_dteStatusDate         = NOW(),
            csh_bIncludeNotesInPacket = 0,
            csh_strStatusTxt          = 'Auto-corrected status',
            csh_bRetired              = 0,
            csh_lOriginID             = $glUserID,
            csh_lLastUpdateID         = $glUserID,
            csh_dteOrigin             = NOW(),
            csh_dteLastUpdate         = NOW(), ";
      foreach ($badStatus as $bad){
         $sqlStr = $sqlBase
            .'csh_lClientID = '.$bad->lKeyID.',
             csh_lStatusID  = '.$cats[$bad->lStatusCatID].';';
      $query = $this->db->query($sqlStr);
      }
   }

   function loadDefaultStatusCats(&$cats){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cats = array();
      $sqlStr =
        'SELECT cst_lKeyID, cst_lClientStatusCatID, cst_bDefault
         FROM lists_client_status_entries
         WHERE cst_bDefault AND NOT cst_bRetired;';
      $query = $this->db->query($sqlStr);
      foreach ($query->result() as $row){
         $cats[(int)$row->cst_lClientStatusCatID] = (int)$row->cst_lKeyID;
      }
   }

}

?>