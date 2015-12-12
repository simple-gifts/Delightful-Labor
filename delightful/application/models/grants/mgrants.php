<?php
/*---------------------------------------------------------------------
// Delightful Labor
// copyright (c) 2015 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
  ---------------------------------------------------------------------
      $this->load->model('admin/madmin_aco', 'cACO');
      $this->load->model('grants/mgrants',   'cgrants');
  ---------------------------------------------------------------------

---------------------------------------------------------------------*/


class mgrants extends CI_Model{
   
   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
   }
   

   // -------------------------------------------------------------
   //         F U N D E R S   /   P R O V I D E R S
   // -------------------------------------------------------------

   function loadGrantProviderViaGPID($lGrantProviderID, &$lNumProviders, &$providers){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadGrantProviders(" AND gpr_lKeyID = $lGrantProviderID ", '', $lNumProviders, $providers);
   }

   function loadGrantProvidersNotHidden($sqlWhere, $sqlOrder, &$lNumProviders, &$providers){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadGrantProviders($sqlWhere.' AND NOT gpr_bHidden ', $sqlOrder, $lNumProviders, $providers);
   }

   function loadGrantProviders($sqlWhere, $sqlOrder, &$lNumProviders, &$providers){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $providers = array();

      $strOrder = $sqlOrder;
      if ($strOrder == ''){
         $strOrder = ' gpr_strGrantOrg, gpr_lKeyID ';
      }

      $sqlStr =
           "SELECT
               gpr_lKeyID, gpr_strGrantOrg, gpr_strAddr1, gpr_strAddr2, gpr_strCity,
               gpr_strState, gpr_strCountry, gpr_strZip, gpr_strPhone, gpr_strCell,
               gpr_strFax, gpr_strWebSite, gpr_strEmail, gpr_strNotes,

               gpr_lAttributedTo, lgen_strListItem AS strAttributedTo,

               -- gpr_lACO, aco_strFlag, aco_strName, aco_strCurrencySymbol,

               gpr_bHidden, gpr_lOriginID, gpr_lLastUpdateID,
               UNIX_TIMESTAMP(gpr_dteOrigin) AS dteOrigin,
               UNIX_TIMESTAMP(gpr_dteLastUpdate) AS dteLastUpdate,

               uc.us_strFirstName AS strCFName, uc.us_strLastName AS strCLName,
               ul.us_strFirstName AS strLFName, ul.us_strLastName AS strLLName

            FROM grant_providers
               INNER JOIN admin_users  AS uc ON uc.us_lKeyID=gpr_lOriginID
               INNER JOIN admin_users  AS ul ON ul.us_lKeyID=gpr_lLastUpdateID
               -- INNER JOIN admin_aco          ON gpr_lACO         = aco_lKeyID

               LEFT  JOIN lists_generic      ON gpr_lAttributedTo=lgen_lKeyID
            WHERE NOT gpr_bRetired $sqlWhere
            ORDER BY $strOrder ";

      $query = $this->db->query($sqlStr);
      $lNumProviders = $query->num_rows();
      if ($lNumProviders == 0) {
         $providers[0] = new stdClass;
         $pr = &$providers[0];

         $pr->lKeyID            =
         $pr->strGrantOrg       =
         $pr->strAddr1          =
         $pr->strAddr2          =
         $pr->strCity           =
         $pr->strState          =
         $pr->strCountry        =
         $pr->strZip            =
         $pr->strPhone          =
         $pr->strCell           =
         $pr->strFax            =
         $pr->strWebSite        =
         $pr->strEmail          =
         $pr->strNotes          =
         $pr->lAttributedTo     =
         $pr->strAttributedTo   =
         $pr->strACOFlag        =
         $pr->strACOName        =
         $pr->strCurrencySymbol =
         $pr->gpr_bHidden        =

         $pr->lOriginID         =
         $pr->lLastUpdateID     =
         $pr->ucstrFName        =
         $pr->ucstrLName        =
         $pr->ulstrFName        =
         $pr->ulstrLName        =

         $pr->dteOrigin         =
         $pr->dteLastUpdate     =

         $pr->strAddress        = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $providers[$idx] = new stdClass;
            $pr = &$providers[$idx];

            $pr->lKeyID            = (int)$row->gpr_lKeyID;
            $pr->strGrantOrg       = $row->gpr_strGrantOrg;
            $pr->strAddr1          = $row->gpr_strAddr1;
            $pr->strAddr2          = $row->gpr_strAddr2;
            $pr->strCity           = $row->gpr_strCity;
            $pr->strState          = $row->gpr_strState;
            $pr->strCountry        = $row->gpr_strCountry;
            $pr->strZip            = $row->gpr_strZip;
            $pr->strPhone          = $row->gpr_strPhone;
            $pr->strCell           = $row->gpr_strCell;
            $pr->strFax            = $row->gpr_strFax;
            $pr->strWebSite        = $row->gpr_strWebSite;
            $pr->strEmail          = $row->gpr_strEmail;
            $pr->strNotes          = $row->gpr_strNotes;
            $pr->lAttributedTo     = $row->gpr_lAttributedTo;
            $pr->strAttributedTo   = $row->strAttributedTo;
            $pr->bHidden           = (bool)$row->gpr_bHidden;

            $pr->lOriginID         = (int)$row->gpr_lOriginID;
            $pr->lLastUpdateID     = (int)$row->gpr_lLastUpdateID;
            $pr->ucstrFName        = $row->strCFName;
            $pr->ucstrLName        = $row->strCLName;
            $pr->ulstrFName        = $row->strLFName;
            $pr->ulstrLName        = $row->strLLName;

            $pr->dteOrigin         = (int)$row->dteOrigin;
            $pr->dteLastUpdate     = (int)$row->dteLastUpdate;

            $pr->strAddress =
                        strBuildAddress(
                                 $pr->strAddr1, $pr->strAddr2,   $pr->strCity,
                                 $pr->strState, $pr->strCountry, $pr->strZip,
                                 true);
            ++$idx;
         }
      }
   }

   function lAddGrantProvider($provider){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'INSERT INTO grant_providers
          SET '.$this->strGrantProvidersCommonSQL($provider).",
             gpr_lOriginID = $glUserID,
             gpr_dteOrigin = NOW();";

      $query = $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function updateGrantProvider($lProviderID, $provider){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'UPDATE grant_providers
          SET '.$this->strGrantProvidersCommonSQL($provider)."
          WHERE gpr_lKeyID=$lProviderID";

      $query = $this->db->query($sqlStr);
   }

   private function strGrantProvidersCommonSQL($provider){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      return(
        'gpr_strGrantOrg   = '.strPrepStr($provider->strGrantOrg).',
         gpr_strAddr1      = '.strPrepStr($provider->strAddr1).',
         gpr_strAddr2      = '.strPrepStr($provider->strAddr2).',
         gpr_strCity       = '.strPrepStr($provider->strCity).',
         gpr_strState      = '.strPrepStr($provider->strState).',
         gpr_strCountry    = '.strPrepStr($provider->strCountry).',
         gpr_strZip        = '.strPrepStr($provider->strZip).',
         gpr_strPhone      = '.strPrepStr($provider->strPhone).',
         gpr_strCell       = '.strPrepStr($provider->strCell).',
         gpr_strFax        = '.strPrepStr($provider->strFax).',
         gpr_strWebSite    = '.strPrepStr($provider->strWebSite).',
         gpr_strEmail      = '.strPrepStr($provider->strEmail).',
         gpr_strNotes      = '.strPrepStr($provider->strNotes).',
         gpr_lAttributedTo = '.(is_null($provider->lAttributedTo) ? 'NULL' : $provider->lAttributedTo).",
         gpr_lLastUpdateID = $glUserID  ");

   }
/*

            $pr = &$providers[$idx];

            $pr->lKeyID            = (int)$row->gpr_lKeyID;
            $pr->strGrantOrg       = $row->gpr_strGrantOrg;
            $pr->strGrantName      = $row->gpr_strGrantName;
            $pr->strAddr1          = $row->gpr_strAddr1;
            $pr->strAddr2          = $row->gpr_strAddr2;
            $pr->strCity           = $row->gpr_strCity;
            $pr->strState          = $row->gpr_strState;
            $pr->strCountry        = $row->gpr_strCountry;
            $pr->strZip            = $row->gpr_strZip;
            $pr->strPhone          = $row->gpr_strPhone;
            $pr->strCell           = $row->gpr_strCell;
            $pr->strFax            = $row->gpr_strFax;
            $pr->strWebSite        = $row->gpr_strWebSite;
            $pr->strEmail          = $row->gpr_strEmail;
            $pr->strNotes          = $row->gpr_strNotes;
            $pr->lAttributedTo     = $row->gpr_lAttributedTo;
            $pr->strAttributedTo   = $row->strAttributedTo;
            $pr->lACO              = (int)$row->gpr_lACO;
            $pr->strACOFlag        = $row->aco_strFlag;
            $pr->strACOName        = $row->aco_strName;
            $pr->strCurrencySymbol = $row->aco_strCurrencySymbol;
            $pr->gpr_bHidden        = (bool)$row->gpr_bHidden;

            $pr->lOriginID         = (int)$row->gpr_lOriginID;
            $pr->lLastUpdateID     = (int)$row->gpr_lLastUpdateID;
            $pr->ucstrFName        = $row->strUCFName;
            $pr->ucstrLName        = $row->strUCLName;
            $pr->ulstrFName        = $row->strULFName;
            $pr->ulstrLName        = $row->strULLName;

   }


            $pr->lACO              = (int)$row->gpr_lACO;
            $pr->strACOFlag        = $row->aco_strFlag;
            $pr->strACOName        = $row->aco_strName;
            $pr->strCurrencySymbol = $row->aco_strCurrencySymbol;







*/




   public function providerHTMLSummary($provider){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lProviderID = $provider->lKeyID;

      $strOut = '';
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $strOut .= $clsRpt->openReport('', '');
      $strOut .=
           $clsRpt->openRow   (false)
          .$clsRpt->writeLabel('Funder/Provider Name:')
          .$clsRpt->writeCell (htmlspecialchars($provider->strGrantOrg))
          .$clsRpt->closeRow  ();

      $strOut .=
           $clsRpt->openRow   (false)
          .$clsRpt->writeLabel('Provider ID:')
          .$clsRpt->writeCell (
                          strLinkView_GrantProvider($lProviderID, 'View Funder/Provider Record', true).'&nbsp;'
                         .str_pad($lProviderID, 5, '0', STR_PAD_LEFT))
          .$clsRpt->closeRow  ();


      $strOut .=
         $clsRpt->closeReport('<br>');

      return($strOut);
   }




      // -------------------------------------------------------------
      //         G R A N T S
      // -------------------------------------------------------------
   function loadGrantsViaGrantID($lGrantID, $sqlOrder, &$lNumGrants, &$grants){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadGrants(" AND gr_lKeyID = $lGrantID ", $sqlOrder, $lNumGrants, $grants);
   }

   function loadGrantsViaProviderID($lProviderID, $sqlWhere, $sqlOrder, &$lNumGrants, &$grants){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadGrants($sqlWhere." AND gr_lProviderID = $lProviderID ", $sqlOrder, $lNumGrants, $grants);
   }

   function loadGrants($sqlWhere, $sqlOrder, &$lNumGrants, &$grants){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $grants = array();

      $strOrder = $sqlOrder;
      if ($strOrder == ''){
         $strOrder = ' gr_strGrantName, gr_lKeyID ';
      }

      $sqlStr =
           "SELECT
               gpr_lKeyID, gpr_strGrantOrg,
               
               gr_lKeyID, gr_lProviderID, gr_strGrantName,
               gr_strNotes, 
               gr_lAttributedTo, lgen_strListItem AS strAttributedTo,
               
               gr_lACO, aco_strFlag, aco_strName, aco_strCurrencySymbol,

               gr_bHidden, gr_lOriginID, gr_lLastUpdateID,
               UNIX_TIMESTAMP(gpr_dteOrigin) AS dteOrigin,
               UNIX_TIMESTAMP(gpr_dteLastUpdate) AS dteLastUpdate,

               uc.us_strFirstName AS strCFName, uc.us_strLastName AS strCLName,
               ul.us_strFirstName AS strLFName, ul.us_strLastName AS strLLName

            FROM grants
               INNER JOIN grant_providers    ON gr_lProviderID=gpr_lKeyID
               INNER JOIN admin_users  AS uc ON uc.us_lKeyID  =gr_lOriginID
               INNER JOIN admin_users  AS ul ON ul.us_lKeyID  =gr_lLastUpdateID
               INNER JOIN admin_aco          ON gr_lACO       = aco_lKeyID

               LEFT  JOIN lists_generic      ON gr_lAttributedTo=lgen_lKeyID
            WHERE NOT gpr_bRetired AND NOT gr_bRetired $sqlWhere
            ORDER BY $strOrder;";

      $query = $this->db->query($sqlStr);
      $lNumGrants = $query->num_rows();
      if ($lNumGrants == 0) {
         $grants[0] = new stdClass;
         $gr = &$grants[0];
         
         $gr->lProviderKeyID    = 
         $gr->strGrantOrg       = 
         $gr->lGrantID          = 
         $gr->strGrantName      = 
         
         $gr->strNotes          = 
         $gr->lAttributedTo     = 
         $gr->strAttributedTo   = 
         $gr->bHidden           = 

         $gr->lOriginID         = 
         $gr->lLastUpdateID     = 
         $gr->ucstrFName        = 
         $gr->ucstrLName        = 
         $gr->ulstrFName        = 
         $gr->ulstrLName        = 

         $gr->dteOrigin         = 
         $gr->dteLastUpdate     = 

         $gr->lACO              = 
         $gr->strACOFlag        = 
         $gr->strACOName        = 
         $gr->strCurrencySymbol = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $grants[$idx] = new stdClass;
            $gr = &$grants[$idx];

            $gr->lProviderKeyID    = (int)$row->gpr_lKeyID;
            $gr->strGrantOrg       = $row->gpr_strGrantOrg;
            $gr->lGrantID          = (int)$row->gr_lKeyID;
            $gr->strGrantName      = $row->gr_strGrantName;
            
            $gr->strNotes          = $row->gr_strNotes;
            $gr->lAttributedTo     = $row->gr_lAttributedTo;
            $gr->strAttributedTo   = $row->strAttributedTo;
            $gr->bHidden           = (bool)$row->gr_bHidden;

            $gr->lOriginID         = (int)$row->gr_lOriginID;
            $gr->lLastUpdateID     = (int)$row->gr_lLastUpdateID;
            $gr->ucstrFName        = $row->strCFName;
            $gr->ucstrLName        = $row->strCLName;
            $gr->ulstrFName        = $row->strLFName;
            $gr->ulstrLName        = $row->strLLName;

            $gr->dteOrigin         = (int)$row->dteOrigin;
            $gr->dteLastUpdate     = (int)$row->dteLastUpdate;

            $gr->lACO              = (int)$row->gr_lACO;
            $gr->strACOFlag        = $row->aco_strFlag;
            $gr->strACOName        = $row->aco_strName;
            $gr->strCurrencySymbol = $row->aco_strCurrencySymbol;
            $gr->strFlagImg        = $this->clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);

            ++$idx;
         }
      }
   }

   function lAddGrant($lProviderID, $grant){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'INSERT INTO grants
          SET '.$this->strGrantsCommonSQL($grant).",
             gr_lProviderID = $lProviderID,
             gr_lOriginID   = $glUserID,
             gr_dteOrigin   = NOW();";

      $query = $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   private function strGrantsCommonSQL($grant){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      return(
        'gr_strGrantName   = '.strPrepStr($grant->strGrantName).',
         gr_strNotes       = '.strPrepStr($grant->strNotes).',
         gr_lAttributedTo  = '.(is_null($grant->lAttributedTo) ? 'NULL' : $grant->lAttributedTo).",
         gr_lACO           = $grant->lACO,
         gr_lLastUpdateID  = $glUserID  ");

   }
   

}
