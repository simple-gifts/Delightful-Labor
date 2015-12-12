<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2011 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
/---------------------------------------------------------------------
   __construct           ()
   loadAccounts          ($bIncludeRetired, $bViaAcctID, $lAID)
   strDDLAccts           ($lMatchID, $bShowBlank, $bExcludeSpon)
   lAddNewAccount        ()
   updateAccount         ()
   bAnyGiftsViaAcctID    ($lAcctID)

   lAddNewCampaign       ()
   updateCampaign        ()
   insertDefaultCampaign ($lAcctID)
   lNumCampsViaAcctID    ($lAID, $bIncludeRetired)
   loadCampaigns         ($bIncludeRetired, $bViaAcctID, $lAID, $bViaCampID, $lCampID)
   xchangeAcctID         ($lCampID, $lNewAID)
   bAnyGiftsViaCampID    ($lCampID)
   lAcctIDViaAcctName    ($strAcctName)
   lCampIDViaCampName    ($lAcctID, $strCampName)

   strDDLCampaigns       ($lMatchID, $bShowBlank)
   strXMLCampaigns       ($lMatchID, $bShowBlank)

   changeCampAcct        ($lCampID, $lNewAcctID)
  ---------------------------------------------------------------------
      $this->load->model('donations/maccts_camps', 'clsAC');
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class maccts_camps extends CI_Model{

   public
      $lNumAccts, $accounts;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->lNumAccts = $this->accounts = null;
   }

   public function loadAccounts($bIncludeRetired, $bViaAcctID, $lAID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->accounts = array();
      if ($bViaAcctID){
         if (is_array($lAID)){
            $strIn = ' AND ga_lKeyID IN ('.implode(',', $lAID).') ';
         }else {
            $strIn = " AND ga_lKeyID=$lAID ";
         }
      }else {
         $strIn = '';
      }

      $sqlStr =
           'SELECT
               ga_lKeyID, ga_strAccount, ga_strNotes, ga_bRetired,
               ga_bProtected, ga_bSponsorship,
               ga_lOriginID, ga_lLastUpdate,
               UNIX_TIMESTAMP(ga_dteOrigin)     AS dteOrigin,
               UNIX_TIMESTAMP(ga_dteLastUpdate) AS dteLastUpdate
            FROM gifts_accounts
            WHERE 1 '.($bIncludeRetired ? '' : ' AND NOT ga_bRetired ')."
               $strIn
            ORDER BY ga_strAccount, ga_lKeyID;";
      $query = $this->db->query($sqlStr);
      $this->lNumAccts = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->accounts[0] = new stdClass;

         $this->accounts[0]->lKeyID        = $lAID;
         $this->accounts[0]->strAccount    =
         $this->accounts[0]->strSafeName   =
         $this->accounts[0]->strNotes      =
         $this->accounts[0]->bProtected    =
         $this->accounts[0]->bSponsorship  =
         $this->accounts[0]->bRetired      =
         $this->accounts[0]->lOriginID     =
         $this->accounts[0]->lLastUpdate   =
         $this->accounts[0]->dteOrigin     =
         $this->accounts[0]->dteLastUpdate = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->accounts[$idx] = new stdClass;

            $this->accounts[$idx]->lKeyID        = (int)$row->ga_lKeyID;
            $this->accounts[$idx]->strAccount    = $row->ga_strAccount;
            $this->accounts[$idx]->strSafeName   = htmlspecialchars($row->ga_strAccount);
            $this->accounts[$idx]->strNotes      = $row->ga_strNotes;
            $this->accounts[$idx]->bProtected    = (boolean)$row->ga_bProtected;
            $this->accounts[$idx]->bSponsorship  = (boolean)$row->ga_bSponsorship;
            $this->accounts[$idx]->bRetired      = (boolean)$row->ga_bRetired;
            $this->accounts[$idx]->lOriginID     = (int)$row->ga_lOriginID;
            $this->accounts[$idx]->lLastUpdate   = (int)$row->ga_lLastUpdate;
            $this->accounts[$idx]->dteOrigin     = (int)$row->dteOrigin;
            $this->accounts[$idx]->dteLastUpdate = (int)$row->dteLastUpdate;
            ++$idx;
         }
      }
   }

   public function strDDLAccts($lMatchID, $bShowBlank, $bExcludeSpon){
   //-----------------------------------------------------------------------
   // caller must first call $this->loadAccounts($bIncludeRetired, false, null)
   //-----------------------------------------------------------------------
      global $gstrSeed;

      $strDDL = '';
      if ($bShowBlank){
         $strDDL .= '<option value="-1">&nbsp;</option>'."\n";
      }

      foreach ($this->accounts as $clsAcct){
         if (!$bExcludeSpon || !$clsAcct->bSponsorship){
            $lKeyID = $clsAcct->lKeyID;
            $strMatch = $lMatchID==$lKeyID ? ' SELECTED ' : '';
            $strDDL .= '<option value="'.$lKeyID.'" '.$strMatch.'>'
                         .$clsAcct->strSafeName.'</option>'."\n";
         }
      }
      return($strDDL);
   }

   public function lAddNewAccount(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
         'INSERT INTO gifts_accounts
          SET '.$this->sqlCommonAcct().",
             ga_bRetired   = 0,
             ga_bProtected = 0,
             ga_lOriginID  = $glUserID,
             ga_dteOrigin  = NOW();";

      $this->db->query($sqlStr);
      $lAcctID = $this->db->insert_id();
      $this->insertDefaultCampaign($lAcctID);

      return($lAcctID);
   }

   public function updateAccount(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
         'UPDATE gifts_accounts
          SET '.$this->sqlCommonAcct().',
             ga_bRetired = '.($this->accounts[0]->bRetired ? '1' : '0').'
          WHERE ga_lKeyID='.$this->accounts[0]->lKeyID.';';

      $query = $this->db->query($sqlStr);
   }

   private function sqlCommonAcct(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      return('
           ga_strAccount  = '.strPrepStr($this->accounts[0]->strAccount).",
           ga_strNotes    = '',
           ga_lLastUpdate = $glUserID ");
   }

   public function lAddNewCampaign(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
         'INSERT INTO gifts_campaigns
          SET '.$this->sqlCommonCamp().',
             gc_lAcctID    = '.$this->campaigns[0]->lAcctID.",
             gc_bRetired   = 0,
             gc_bProtected = 0,
             gc_lOriginID  = $glUserID,
             gc_dteOrigin  = NOW();";

      $this->db->query($sqlStr);
      $this->campaigns[0]->lKeyID = $lCampID = $this->db->insert_id();
      return($lCampID);
   }

   public function updateCampaign(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
         'UPDATE gifts_campaigns
          SET '.$this->sqlCommonCamp().',
             gc_bRetired = '.($this->campaigns[0]->bRetired ? '1' : '0').'
          WHERE gc_lKeyID='.$this->campaigns[0]->lKeyID.';';
      $this->db->query($sqlStr);
   }

   private function sqlCommonCamp(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      return('
           gc_strCampaign  = '.strPrepStr($this->campaigns[0]->strCampaign).",
           gc_strNotes    = '',
           gc_lLastUpdate = $glUserID ");
   }

   private function insertDefaultCampaign($lAcctID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         "INSERT INTO gifts_campaigns
          SET
             gc_lAcctID       = $lAcctID,
             gc_strCampaign   = 'Default',
             gc_strNotes      = '',
             gc_bProtected    = 1,
             gc_bRetired      = 0,
             gc_lOriginID     = $glUserID,
             gc_lLastUpdate   = $glUserID,
             gc_dteOrigin     = NOW(),
             gc_dteLastUpdate = NOW();";

      $query = $this->db->query($sqlStr);
   }

   function lNumCampsViaAcctID($lAID, $bIncludeRetired){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
           "SELECT COUNT(*) AS lNumRecs
            FROM gifts_campaigns
            WHERE
               gc_lAcctID=$lAID "
               .($bIncludeRetired ? '' : ' AND NOT gc_bRetired ').';';

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         return((integer)$row->lNumRecs);
      }
   }

   public function loadCampaigns($bIncludeRetired, $bViaAcctID, $lAID, $bViaCampID, $lCampID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->campaigns = array();

      if ($bViaCampID){
         if (is_array($lCampID)){
            $strWhereCamp = " AND  gc_lKeyID IN (".implode(',', $lCampID).") \n";
         }else {
            $strWhereCamp = " AND  gc_lKeyID=$lCampID \n";
         }
      }else {
         $strWhereCamp = '';
      }

      $sqlStr =
        'SELECT
            gc_lKeyID, gc_lAcctID, gc_strCampaign, gc_strNotes, ga_strAccount,
            gc_lDefaultAckLetTemplate, gc_bProtected, gc_bRetired,
            gc_lOriginID, gc_lLastUpdate,
            UNIX_TIMESTAMP(gc_dteOrigin) AS dteOrigin,
            UNIX_TIMESTAMP(gc_dteLastUpdate) AS dteLastUpdate
         FROM gifts_campaigns
            LEFT JOIN gifts_accounts ON gc_lAcctID=ga_lKeyID
         WHERE 1 '
               .($bIncludeRetired ? '' : ' AND (NOT gc_bRetired) AND (NOT ga_bRetired) ').' '
               .$strWhereCamp.' '
               .($bViaAcctID ? " AND (gc_lAcctID=$lAID) "   : '').'
         ORDER BY  ga_strAccount, gc_strCampaign, gc_lKeyID;';

      $query = $this->db->query($sqlStr);
      $this->lNumCamps = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->campaigns[0] = new stdClass;

         $this->campaigns[0]->lKeyID          = $lCampID;
         $this->campaigns[0]->lAcctID         =
         $this->campaigns[0]->strCampaign     =
         $this->campaigns[0]->strSafeName     =
         $this->campaigns[0]->strAccount      =
         $this->campaigns[0]->strAcctSafeName =
         $this->campaigns[0]->bProtected      =
         $this->campaigns[0]->bRetired        =
         $this->campaigns[0]->lOriginID       =
         $this->campaigns[0]->lLastUpdate     =
         $this->campaigns[0]->dteOrigin       =
         $this->campaigns[0]->dteLastUpdate   = null;
      }else {
         $idx = 0;
         foreach ($query->result_array() as $row){
            $this->campaigns[$idx] = new stdClass;

            $this->campaigns[$idx]->lKeyID          = $row['gc_lKeyID'];
            $this->campaigns[$idx]->lAcctID         = $row['gc_lAcctID'];
            $this->campaigns[$idx]->strCampaign     = $row['gc_strCampaign'];
            $this->campaigns[$idx]->strSafeName     = htmlspecialchars($row['gc_strCampaign']);
            $this->campaigns[$idx]->strAccount      = $row['ga_strAccount' ];
            $this->campaigns[$idx]->strAcctSafeName = htmlspecialchars($row['ga_strAccount']);
            $this->campaigns[$idx]->bProtected      = $row['gc_bProtected' ];
            $this->campaigns[$idx]->bRetired        = $row['gc_bRetired'   ];
            $this->campaigns[$idx]->lOriginID       = $row['gc_lOriginID'  ];
            $this->campaigns[$idx]->lLastUpdate     = $row['gc_lLastUpdate'];
            $this->campaigns[$idx]->dteOrigin       = $row['dteOrigin'     ];
            $this->campaigns[$idx]->dteLastUpdate   = $row['dteLastUpdate' ];
            ++$idx;
         }
      }
   }

   public function xchangeAcctID($lCampID, $lNewAID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
           "UPDATE gifts_campaigns
               SET
               gc_lAcctID=$lNewAID,
               gc_lLastUpdate=$glUserID
            WHERE gc_lKeyID=$lCampID;";
      $query = $this->db->query($sqlStr);
   }

   public function bAnyGiftsViaAcctID($lAcctID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM gifts
            INNER JOIN gifts_campaigns ON gi_lCampID=gc_lKeyID
         WHERE NOT gi_bRetired AND gc_lAcctID=$lAcctID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(false);
      }else {
         $row = $query->row();
         return($row->lNumRecs > 0);
      }
   }

   public function bAnyGiftsViaCampID($lCampID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM gifts
         WHERE NOT gi_bRetired AND gi_lCampID=$lCampID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(false);
      }else {
         $row = $query->row();
         return($row->lNumRecs > 0);
      }
   }

   public function lAcctIDViaAcctName($strAcctName){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        'SELECT ga_lKeyID
         FROM gifts_accounts
         WHERE
            ga_strAccount='.strPrepStr($strAcctName).'
            AND NOT ga_bRetired
         ORDER BY ga_lKeyID
         LIMIT 0,1;';

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(null);
      }else {
         $row = $query->row();
         return((int)$row->ga_lKeyID);
      }
   }

   public function lCampIDViaCampName($lAcctID, $strCampName){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "SELECT gc_lKeyID
         FROM gifts_campaigns
         WHERE gc_lAcctID=$lAcctID
            AND gc_strCampaign=".strPrepStr($strCampName).'
            AND NOT gc_bRetired
         ORDER BY gc_lKeyID
         LIMIT 0,1;';

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(null);
      }else {
         $row = $query->row();
         return((int)$row->gc_lKeyID);
      }
   }

   public function strDDLCampaigns($lMatchID, $bShowBlank){
   //-----------------------------------------------------------------------
   // caller must first call
   //   $clsAC->loadCampaigns(false, true, $lAID, false, null);
   //-----------------------------------------------------------------------
      $strDDL = '';
      if ($bShowBlank){
         $strDDL .= '<option value="-1">&nbsp;</option>'."\n";
      }

      foreach ($this->campaigns as $clsCamp){
         $lKeyID = $clsCamp->lKeyID;
         $strMatch = $lMatchID==$lKeyID ? ' SELECTED ' : '';
         $strDDL .= '<option value="'.$lKeyID.'" '.$strMatch.'>'
                      .$clsCamp->strSafeName.'</option>'."\n";
      }
      return($strDDL);
   }

   public function strXMLCampaigns($lMatchID, $bShowBlank){
   //-----------------------------------------------------------------------
   // caller must first call
   //   $clsAC->loadCampaigns(false, true, $lAID, false, null);
   //-----------------------------------------------------------------------
      $strXML = '<?xml version="1.0" encoding="ISO-8859-1"?>
                 <campaigns>';
      if ($bShowBlank){
         $strXML .= '
               <camp>
                  <id>-1</id>
                  <encryptID>-1</encryptID>
                  <name>&nbsp;</name>
                  <selected>false</selected>
               </camp>'."\n";
      }

      foreach ($this->campaigns as $clsCamp){
         $lKeyID = $clsCamp->lKeyID;
         $strMatch = $lMatchID==$lKeyID ? 'true' : 'false';

         $strXML .= '
               <camp>
                  <id>'.$lKeyID.'</id>
                  <encryptID>'.$lKeyID.'</encryptID>
                  <name>'.$clsCamp->strSafeName.'</name>
                  <selected>'.$strMatch.'</selected>
               </camp>'."\n";
      }
      $strXML .= '</campaigns>';
      return($strXML);
   }

   public function changeCampAcct($lCampID, $lNewAcctID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
           "UPDATE gifts_campaigns
            SET gc_lAcctID=$lNewAcctID,
               gc_lLastUpdate=$glUserID
            WHERE gc_lKeyID=$lCampID;";

      $this->db->query($sqlStr);
   }

}

?>