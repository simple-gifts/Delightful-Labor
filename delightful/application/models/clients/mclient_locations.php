<?php
/*---------------------------------------------------------------------
// copyright (c) 2011-2015
// Austin, Texas 78759
//
// Serving the Children of India
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
   __construct            ()
   initClassVars          ()
   lLocationCount         ()
   loadLocationRec        ($id)
   addNewLocationRec      ()
   updateLocationRec      ()
   loadAllLocations       ()
   strDDLAllLocations     ($lMatchID)
   bTestNoLocations       ($strExtraMessage, &$strOutMsg)

   bLocSupportsSponCat    ($lLocID, $lSponCatID)
   clearSupportedSponCats ($lLocID)
   clearSupportedSponProgs($lLocID, $lSponProgID)
---------------------------------------------------------------------
      $this->load->model('clients/mclient_locations', 'clsLoc');
---------------------------------------------------------------------*/


class mclient_locations extends CI_Model{

      //------------------------------------------------
      // variables associated with children's locations
      //------------------------------------------------
   public
      $lKeyID,        $strLocation,   $strDescription,
      $strCountry,    $strWebLink,    $strAddress1,
      $strAddress2,   $strCity,       $strState,
      $strPostalCode, $strNotes,      $bRetired,
      $lOriginID,     $lLastUpdateID, $dteOrigin,
      $dteLastUpdate;
   public
      $objAllLocations, $lNumLocations, $lNumClientXfers, $clientXfers;


   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();
      $this->initClassVars();
   }

   private function initClassVars(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->cl_lKeyID         =
      $this->cl_strLocation    =
      $this->cl_strDescription =
      $this->cl_strCountry     =
      $this->cl_strWebLink     =
      $this->cl_strAddress1    =
      $this->cl_strAddress2    =
      $this->cl_strCity        =
      $this->cl_strState       =
      $this->cl_strPostalCode  =
      $this->cl_strNotes       =
      $this->cl_bEnableEMR     =
      $this->cl_bRetired       =
      $this->cl_lOriginID      =
      $this->cl_lLastUpdateID  =
      $this->cl_dteOrigin      =
      $this->cl_dteLastUpdate  = null;

      $this->clientLocations = $this->lNumLocations = null;
      $this->lNumClientXfers = 0;
      $this->clientXfers     = null;
   }

   public function lLocationCount(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT COUNT(*) AS lNumRecs
         FROM client_location
         WHERE NOT cl_bRetired;';
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

   public function loadLocationRec($id){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            cl_lKeyID,        cl_strLocation, cl_strDescription,
            cl_strCountry,    cl_strWebLink,  cl_strAddress1,
            cl_strAddress2,   cl_strCity,     cl_strState,
            cl_strPostalCode, cl_strNotes,    cl_bEnableEMR, cl_bRetired,
            cl_lOriginID,     cl_lLastUpdateID,
            UNIX_TIMESTAMP(cl_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(cl_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM client_location
               INNER JOIN admin_users  AS uc ON uc.us_lKeyID=cl_lOriginID
               INNER JOIN admin_users  AS ul ON ul.us_lKeyID=cl_lLastUpdateID

         WHERE cl_lKeyID=$id;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }else{
//         $numRows = mysql_num_rows($result);
      if ($numRows==0) {
         $this->lKeyID         =
         $this->strLocation    =
         $this->strDescription =
         $this->strCountry     =
         $this->strWebLink     =
         $this->strAddress1    =
         $this->strAddress2    =
         $this->strCity        =
         $this->strState       =
         $this->strPostalCode  =
         $this->strNotes       =
         $this->bEnableEMR     =
         $this->bRetired       =
         $this->lOriginID      =
         $this->lLastUpdateID  =
         $this->dteOrigin      =
         $this->dteLastUpdate  = null;

      }else {
         $row = $query->row();
//            $row = mysql_fetch_array($result);

         $this->lKeyID         = (int)$row->cl_lKeyID;
         $this->strLocation    = $row->cl_strLocation;
         $this->strDescription = $row->cl_strDescription;
         $this->strCountry     = $row->cl_strCountry;
         $this->strWebLink     = $row->cl_strWebLink;
         $this->strAddress1    = $row->cl_strAddress1;
         $this->strAddress2    = $row->cl_strAddress2;
         $this->strCity        = $row->cl_strCity;
         $this->strState       = $row->cl_strState;
         $this->strPostalCode  = $row->cl_strPostalCode;
         $this->strAddress     =
                     strBuildAddress(
                              $this->strAddress1, $this->strAddress2,   $this->strCity,
                              $this->strState,    $this->strCountry,    $this->strPostalCode,
                              true);

         $this->strNotes       = $row->cl_strNotes;
         $this->bEnableEMR     = $row->cl_bEnableEMR;
         $this->bRetired       = (boolean)$row->cl_bRetired;
         $this->lOriginID      = (int)$row->cl_lOriginID;
         $this->lLastUpdateID  = (int)$row->cl_lLastUpdateID;
         $this->dteOrigin      = (int)$row->dteOrigin;
         $this->dteLastUpdate  = (int)$row->dteLastUpdate;
         $this->strUCFName     = $row->strUCFName;
         $this->strUCLName     = $row->strUCLName;
         $this->strULFName     = $row->strULFName;
         $this->strULLName     = $row->strULLName;
      }
//      }
   }

   function lAddNewLocationRec(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'INSERT INTO client_location
          SET '.$this->strSqlCommon().",
             cl_bRetired  = 0,
             cl_lOriginID = $glUserID,
             cl_dteOrigin = NOW();";

      $this->db->query($sqlStr);
      $this->cl_lKeyID = $this->db->insert_id();
      return($this->cl_lKeyID);
   }

   function strSqlCommon(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      return('
           cl_strLocation    = '.strPrepStr($this->strLocation).',
           cl_strDescription = '.strPrepStr($this->strDescription).',
           cl_strCountry     = '.strPrepStr($this->strCountry).',
           cl_strWebLink     = '.strPrepStr($this->strWebLink).',
           cl_strAddress1    = '.strPrepStr($this->strAddress1).',
           cl_strAddress2    = '.strPrepStr($this->strAddress2).',
           cl_strCity        = '.strPrepStr($this->strCity).',
           cl_strState       = '.strPrepStr($this->strState).',
           cl_strPostalCode  = '.strPrepStr($this->strPostalCode).',
           cl_strNotes       = '.strPrepStr($this->strNotes).',
           cl_bEnableEMR     = '.($this->bEnableEMR ? '1' : '0').",
           cl_lLastUpdateID  = $glUserID,
           cl_dteLastUpdate  = NOW() ");
   }

   function updateLocationRec($id){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'UPDATE client_location
          SET '.$this->strSqlCommon().',
             cl_bRetired = '.($this->cl_bRetired ? '1' : '0')."
          WHERE cl_lKeyID=$id";
      $this->db->query($sqlStr);
   }

   public function retireLocationRec($id){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         "UPDATE client_location
          SET
             cl_lLastUpdateID = $glUserID,
             cl_dteLastUpdate = NOW(),
             cl_bRetired      = 1
          WHERE cl_lKeyID=$id;";
      $this->db->query($sqlStr);
   }

   function loadAllLocations(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->clientLocations = array();
      $this->lNumLocations   = 0;
      $sqlStr =
        'SELECT
            cl_lKeyID, cl_strLocation, cl_strCountry,
            cl_strDescription, cl_strWebLink, cl_strAddress1, cl_strAddress2,
            cl_strCity, cl_strState, cl_strPostalCode, cl_strNotes, cl_bEnableEMR,
            cl_lOriginID, cl_lLastUpdateID,
            UNIX_TIMESTAMP(cl_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(cl_dteLastUpdate) AS dteLastUpdate
         FROM client_location
         WHERE NOT cl_bRetired
         ORDER BY cl_strLocation, cl_lKeyID;';

      $query = $this->db->query($sqlStr);
      $this->lNumLocations = $numRows = $query->num_rows();

      $idx = 0;
      foreach ($query->result() as $row){
         $this->clientLocations[$idx] = new stdClass;
         $location = &$this->clientLocations[$idx];

         $location->strLocation     = $row->cl_strLocation;
         $location->lKeyID          = $row->cl_lKeyID;
         $location->strCountry      = $row->cl_strCountry;

         $location->strDescription  = $row->cl_strDescription;
         $location->strWebLink      = $row->cl_strWebLink;
         $location->strAddress1     = $row->cl_strAddress1;
         $location->strAddress2     = $row->cl_strAddress2;
         $location->strCity         = $row->cl_strCity;
         $location->strState        = $row->cl_strState;
         $location->strPostalCode   = $row->cl_strPostalCode;
         $location->strNotes        = $row->cl_strNotes;
         $location->bEnableEMR      = $row->cl_bEnableEMR;
         $location->lOriginID       = $row->cl_lOriginID;
         $location->lLastUpdateID   = $row->cl_lLastUpdateID;
         $location->dteOrigin       = $row->dteOrigin;
         $location->dteLastUpdate   = $row->dteLastUpdate;

         ++$idx;
      }
   }

   function strDDLAllLocations($lMatchID){
   //---------------------------------------------------------------------
   // user must call $this->loadAllLocations() first
   //---------------------------------------------------------------------
      $strList = '';

      foreach ($this->clientLocations as $location){
         $strMatch = $lMatchID==$location->lKeyID ? ' selected ' : '';
         $strList .=
            '<option value="'.$location->lKeyID.'" '.$strMatch.'>'
               .htmlspecialchars($location->strLocation).'</option>';
      }
      return($strList);
   }

   public function bTestNoLocations($strExtraMessage, &$strOutMsg){
   //---------------------------------------------------------------------
   // caller should first call $clsLocation->loadAllLocations();
   //---------------------------------------------------------------------
      global $gbAdmin;

      $strOutMsg = '';
      $bNoLocs = $this->lNumLocations == 0;
      if ($bNoLocs){
         $strOutMsg .= '<i>There are no client locations defined in your database.<br>'.$strExtraMessage.'</i><br><br>';
         if ($gbAdmin){
            $strOutMsg .= 'You can add client locations '.strLinkAdd_ClientLocation('here', false).'.<br><br>';
         }else {
            $strOutMsg .= 'Please contact your '.CS_PROGNAME.' administrator to add one or more client locations.<br><br>';
         }
      }
      return($bNoLocs);
   }

   public function bLocSupportsSponCat($lLocID, $lSponCatID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT clsp_lKeyID
          FROM client_loc_supported_sponprogs
          WHERE
             clsp_lLocID=$lLocID
             AND clsp_lSponProgID=$lSponCatID;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      return ($numRows > 0);

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }else{
//         $numRows = mysql_num_rows($result);
//         return ($numRows > 0);
//      }
   }

   public function clearSupportedSponProgs($lLocID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "DELETE FROM client_loc_supported_sponprogs
          WHERE clsp_lLocID=$lLocID;";
      $query = $this->db->query($sqlStr);

//      $result = mysql_query($sqlStr);
//      if (bSQLError('SQL error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__, $sqlStr) ) {
//         screamForHelp('Unexpected SQL error');
//      }
   }

   public function setSupportedSponProgs($lLocID, $lSponProgID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "INSERT INTO client_loc_supported_sponprogs
          SET
             clsp_lLocID=$lLocID,
             clsp_lSponProgID=$lSponProgID;";
      $this->db->query($sqlStr);
   }

   public function supportedSponProgs($lLocID){
   //---------------------------------------------------------------------
   //   return integer array of supported sponsor programs
   //---------------------------------------------------------------------
      $sponProg = array();
      $sqlStr =
        "SELECT
            sc_lKeyID
         FROM lists_sponsorship_programs
            INNER JOIN client_loc_supported_sponprogs ON sc_lKeyID=clsp_lSponProgID
         WHERE clsp_lLocID=$lLocID
         ORDER BY sc_strProgram, sc_lKeyID;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      foreach ($query->result() as $row){
         $sponProg[] = (int)$row->sc_lKeyID;
      }
      return($sponProg);
   }

   public function loadSupportedSponCats($lLocID, $clsSC){
   //---------------------------------------------------------------------
   //      $this->load->model('sponsorship/msponsorship_programs', 'clsSC');
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            sc_lKeyID, sc_bDefault, sc_strProgram, sc_strNotes,
            sc_curDefMonthlyCommit, sc_lACO,
            sc_bRetired, sc_lOriginID, sc_lLastUpdateID,
            UNIX_TIMESTAMP(sc_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(sc_dteLastUpdate) AS dteLastUpdate,
            aco_strCurrencySymbol
         FROM lists_sponsorship_programs
            INNER JOIN client_loc_supported_sponprogs ON sc_lKeyID=clsp_lSponProgID
            INNER JOIN admin_aco                      ON sc_lACO=aco_lKeyID
         WHERE clsp_lLocID=$lLocID
         ORDER BY sc_strProgram, sc_lKeyID;";

      $clsSC->loadSponProgsViaSQL($sqlStr);
   }

   public function lNumClientsViaLocation($lLocID){
      sqlQualClientViaStatus(
                       $strWhere, $strInner, true,
                       true,      false,     null);

      $sqlStr =
           "SELECT COUNT(*) AS lNumClients
            FROM client_records
               $strInner

            WHERE cr_lLocationID=$lLocID
               AND NOT cr_bRetired
               $strWhere;";

      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((integer)$row->lNumClients);
   }

   public function lNumActiveSponsViaLocID($lLocID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM sponsor
            INNER JOIN client_records ON sp_lClientID=cr_lKeyID
         WHERE
                NOT sp_bInactive
            AND NOT sp_bRetired
            AND cr_lLocationID=$lLocID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return($row->lNumRecs);
   }

   public function strClientLocationHTMLSummary(){
   //-----------------------------------------------------------------------
   // assumes user has called $this->loadLocationRec($id)
   //-----------------------------------------------------------------------
      $strOut = '';
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $lLocID = $this->lKeyID;
      $strOut .= $clsRpt->openReport('', '');

      $strOut .=
           $clsRpt->openRow   (false)
          .$clsRpt->writeLabel('Location ID:')
          .$clsRpt->writeCell (
                   strLinkView_ClientLocation($lLocID, 'View Client Location Record', true)
                  .' '.str_pad($lLocID, 5, '0', STR_PAD_LEFT))
          .$clsRpt->closeRow  ()

          .$clsRpt->openRow   (false)
          .$clsRpt->writeLabel('Location:')
          .$clsRpt->writeCell (htmlspecialchars($this->strLocation))
          .$clsRpt->closeRow  ();

      $strOut .=
          $clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Country:')
         .$clsRpt->writeCell (htmlspecialchars($this->strCountry))
         .$clsRpt->closeRow  ();

      $strOut .=
          $clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Allow Medical Recs:')
         .$clsRpt->writeCell (($this->bEnableEMR ? 'Yes' : 'No'))
         .$clsRpt->closeRow  ();

      $strOut .=
         $clsRpt->closeReport('<br>');

      return($strOut);
   }

   public function loadClientXfersViaClientID($lClientID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            cx_lKeyID, cx_lClientID,
            cx_lOldLocID, cx_lOldStatCatID, cx_lOldVocID,
            cx_lNewLocID, cx_lNewStatCatID, cx_lNewVocID,
            cx_lLastUpdateID,
            cl_dteEffectiveDate,
            UNIX_TIMESTAMP(cx_dteLastUpdate) AS dteLastUpdate,

            oldStatCat.csc_strCatName AS statCatOld,
            newStatCat.csc_strCatName AS statCatNew,

            oldLoc.cl_strLocation AS locOld,
            newLoc.cl_strLocation AS locNew,

            oldVoc.cv_strVocTitle AS vocOld,
            newVoc.cv_strVocTitle AS vocNew
         FROM client_xfers
            INNER JOIN client_status_cats AS oldStatCat ON oldStatCat.csc_lKeyID=cx_lOldStatCatID
            INNER JOIN client_status_cats AS newStatCat ON newStatCat.csc_lKeyID=cx_lNewStatCatID

            INNER JOIN client_location AS oldLoc ON oldLoc.cl_lKeyID=cx_lOldLocID
            INNER JOIN client_location AS newLoc ON newLoc.cl_lKeyID=cx_lNewLocID

            INNER JOIN lists_client_vocab AS oldVoc ON oldVoc.cv_lKeyID=cx_lOldVocID
            INNER JOIN lists_client_vocab AS newVoc ON newVoc.cv_lKeyID=cx_lNewVocID
         WHERE cx_lClientID=$lClientID
         ORDER BY cl_dteEffectiveDate, cx_lKeyID;";
      $query = $this->db->query($sqlStr);
      $this->lNumClientXfers = $lNumRows = $query->num_rows();

      $this->clientXfers = array();
      $idx = 0;
      foreach ($query->result() as $row){
         $this->clientXfers[$idx] = new stdClass;
         $cx = &$this->clientXfers[$idx];

         $cx->lKeyID        = (int)$row->cx_lKeyID;
         $cx->lClientID     = (int)$row->cx_lClientID;
         $cx->lOldLocID     = $row->cx_lOldLocID;
         $cx->lOldStatCatID = $row->cx_lOldStatCatID;
         $cx->lOldVocID     = $row->cx_lOldVocID;
         $cx->lNewLocID     = $row->cx_lNewLocID;
         $cx->lNewStatCatID = $row->cx_lNewStatCatID;
         $cx->lNewVocID     = $row->cx_lNewVocID;
         $cx->lLastUpdateID = $row->cx_lLastUpdateID;
         $cx->dteEffective  = dteMySQLDate2Unix($row->cl_dteEffectiveDate);
         $cx->dteLastUpdate = $row->dteLastUpdate;
         $cx->statCatOld    = $row->statCatOld;
         $cx->statCatNew    = $row->statCatNew;
         $cx->locOld        = $row->locOld;
         $cx->locNew        = $row->locNew;
         $cx->vocOld        = $row->vocOld;
         $cx->vocNew        = $row->vocNew;

         ++$idx;
      }
   }

   public function clientXfer($lClientID,
                              $lOldLocID, $lOldStatCatID, $lOldVocID,
                              $lNewLocID, $lNewStatCatID, $lNewVocID,
                              $dteEDate){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
           "INSERT INTO client_xfers
            SET
               cx_lClientID        = $lClientID,
               cx_lOldLocID        = $lOldLocID,
               cx_lOldStatCatID    = $lOldStatCatID,
               cx_lOldVocID        = $lOldVocID,
               cx_lNewLocID        = $lNewLocID,
               cx_lNewStatCatID    = $lNewStatCatID,
               cx_lNewVocID        = $lNewVocID,
               cx_lLastUpdateID    = $glUserID,
               cl_dteEffectiveDate = ".strPrepDate($dteEDate).',
               cx_dteLastUpdate    = NOW();';
      $this->db->query($sqlStr);

      $sqlStr =
        "UPDATE client_records
         SET
            cr_lLocationID   = $lNewLocID,
            cr_lStatusCatID  = $lNewStatCatID,
            cr_lVocID        = $lNewVocID,
            cr_lLastUpdateID = $glUserID,
            cr_dteLastUpdate = NOW()
         WHERE cr_lKeyID=$lClientID;";
      $this->db->query($sqlStr);
   }


}

?>