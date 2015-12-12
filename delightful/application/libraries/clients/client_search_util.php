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
      $this->load->library('clients/client_search_util', '', 'clsCSearch');
      $clsCSearch = new clsCSearch;
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class client_search_util extends mclients{

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

   public function clientsAvailableForSponsorship(){
   //-----------------------------------------------------------------------
   // two client groups to consider:
   //   clients with no sponsors
   //   clients with sponsors (discarding inactive sponsorships)
   //-----------------------------------------------------------------------
      if (is_null($this->lSponProgID)) screamForHelp('CLASS NOT INITIALIZED $this->lSponProgID<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $this->lNumAvail  = 0;
      $this->lAvailList = array();

         //----------------------------------------------------------
         // find all clients with status that allows sponsorship
         // (optionally qualified by program); must have
         // max sponsorship > 0
         //----------------------------------------------------------
      $this->clientsEligibleViaStatus($this->lSponProgID, $lClientList, $lMaxSpon, $lNumEligibleClients);

      for ($idx = 0; $idx < $lNumEligibleClients; ++$idx){
         $lClientID = $lClientList[$idx];

         if ($this->lNumSponsorsViaClientID($lClientID) < $lMaxSpon[$idx]){
            $this->lAvailList[$this->lNumAvail] = $lClientID;
            ++$this->lNumAvail;
         }
      }
   }

   public function clientsEligibleViaStatus($lSponProgID, &$lClientList, &$lMaxSpon, &$lNumEligibleClients){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $lClientList = $lMaxSpon = array();

         //-----------------------------------------------
         // optionally qualify by sponsorship program
         //-----------------------------------------------
      if ($lSponProgID > 0){
         $strSponProgInner =
           ' INNER JOIN client_supported_sponprogs   ON csp_lClientID = cr_lKeyID ';
         $strSponProgWhere = " AND csp_lSponProgID=$lSponProgID ";
      }else {
         $strSponProgInner = '';
         $strSponProgWhere = '';
      }

      $sqlStr =
           "SELECT
               cr_lKeyID, cr_lMaxSponsors
            FROM client_records
               INNER JOIN client_status               ON csh_lClientID   = cr_lKeyID
               INNER JOIN lists_client_status_entries ON csh_lStatusID   = cst_lKeyID
               $strSponProgInner
            WHERE 1
               $strSponProgWhere
               AND NOT cr_bRetired
               AND cst_bAllowSponsorship
               AND cr_lMaxSponsors > 0

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
      $lNumEligibleClients = $numRows = $query->num_rows();
      if ($numRows > 0){
         $idx = 0;
         foreach ($query->result() as $row) {
            $lClientList[$idx] = $row->cr_lKeyID;
            $lMaxSpon   [$idx] = $row->cr_lMaxSponsors;
            ++$idx;
         }
      }
   }


}


?>