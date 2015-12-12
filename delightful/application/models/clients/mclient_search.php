<?php
/*---------------------------------------------------------------------
// copyright (c) 2012
// Austin, Texas 78759
//
// Serving the Children of India
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('clients/mclients',       'clsClients');
      $this->load->model('clients/mclient_search', 'cClientSearch');
----------------------------------------------------------------------

-----------------------------------------------------------------------*/
class mclient_search extends mclients{

   public
      $bShowSponProg, $lQualSponProgs,
      $bShowAge,      $qualAge;


      //---------------------------------------
      // for clients available for sponsorship
      //---------------------------------------
   public
      $lSponProgID;

   public $bDebug;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
      $this->bShowSponProg  = false; $this->lQualSponProgs = null;
      $this->bShowAge       = false; $this->qualAge        = null;
      $this->bShowLoc       = false; $this->qualLocation   = null;
      $this->bShowStatusCat = false; $this->qualStatusCat  = null;
      $this->bShowStatus    = false; $this->qualStatus     = null;

      $this->bShowSponsorableOnly = false; $this->bSponsorableOnly = null;
      $this->bShowNeedsSponsors   = false; $this->bNeedsSponsors   = null;

      $this->lSponProgID = null;

      $this->bDebug = false;

      $this->initClientClass();
   }


   public function showClientSearch(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
   }


   function createClientPopulationTempTable($strTmpTableName){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "CREATE TEMPORARY TABLE IF NOT EXISTS $strTmpTableName (
         -- CREATE TABLE IF NOT EXISTS $strTmpTableName (
           tmpc_lKeyID      int(11) NOT NULL AUTO_INCREMENT,
           tmpc_lClientID   int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to client table',
           tmpc_enumGender  enum('Male','Female','Unknown') NOT NULL DEFAULT 'Unknown',
           tmpc_dteBirth    date DEFAULT NULL,

           PRIMARY KEY (tmpc_lKeyID),
           UNIQUE KEY   tmpc_lClientID  (tmpc_lClientID),
           KEY          tmpc_enumGender (tmpc_enumGender)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $query = $this->db->query($sqlStr);
   }

   function populateClientPopulationTable($strTmpTableName, $strWhere, $strInner){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "INSERT INTO $strTmpTableName
           (tmpc_lClientID, tmpc_enumGender, tmpc_dteBirth)
            SELECT  cr_lKeyID, cr_enumGender, cr_dteBirth
            FROM client_records
               $strInner
            WHERE NOT cr_bRetired
               $strWhere
         ON DUPLICATE KEY UPDATE tmpc_enumGender=tmpc_enumGender

               ;";
      $query = $this->db->query($sqlStr);
   }

   function clearClientPopulationTable($strTmpTableName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "TRUNCATE TABLE $strTmpTableName;";
      $query = $this->db->query($sqlStr);
   }

}


?>