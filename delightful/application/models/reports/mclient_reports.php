<?php
/*---------------------------------------------------------------------
// Delightful Labor
// copyright (c) 2012 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
  ---------------------------------------------------------------------
      $this->load->model('reports/mclient_reports', 'cCRpt');
---------------------------------------------------------------------

---------------------------------------------------------------------*/


class mclient_reports extends CI_Model{

   public $lNumLocs, $lNumClients, $locAggStats;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->lNumLocs = $this->lNumClients = 0;
   }

   function aggStatsViaLoc($bActiveOnly){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cLoc    = new mclient_locations;
      $cClient = new mclients;
      $this->lNumClients = 0;
      $cLoc->loadAllLocations();
      $lNumLocs = $this->lNumLocs = $cLoc->lNumLocations;
      if ($lNumLocs == 0) return;

      $this->locAggStats = array();
      $idx = 0;
      foreach ($cLoc->clientLocations as $loc){
         $this->locAggStats[$idx] = new stdClass;
         $lagg = &$this->locAggStats[$idx];
         $lagg->lLocID = $lLocID = $loc->lKeyID;
         $lagg->strLocation = $loc->strLocation;
         $lagg->strCountry  = $loc->strCountry;
         $lagg->lNumClients = $lNumClients = $cClient->lNumClientsViaLocID($lLocID, !$bActiveOnly);
         $this->lNumClients += $lNumClients;

         ++$idx;
      }
   }

   function aggStatsViaAge(&$ageRanges){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cClient = new mclients;
      $cClient->clientAgeRanges($ageRanges);
      $idx = 0;
      foreach ($ageRanges as $aRange){
         $ageRanges[$idx]['numClients'] = $cClient->lNumClientsInAgeRange(true, $aRange['start'], $aRange['end']);
         ++$idx;
      }
   }

   function lNumRecsAgeReport(
                           &$sRpt,
                           $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;

      $mysqlDteBase = str_replace("'", '', strPrepDate($gdteNow));
      dateRangeYears($mysqlDteBase, $sRpt->lStartAge, $sRpt->lEndAge, $mysqlStartDate, $mysqlEndDate);

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      sqlQualClientViaStatus(
                       $sqlWhereSubquery, $strInner, true,
                       true,              false,     false);

      $sqlStr =
        "SELECT cr_lKeyID
            FROM client_records $strInner
            WHERE 1
            AND NOT cr_bRetired

            $sqlWhereSubquery

            AND cr_dteBirth > ".strPrepStr($mysqlStartDate).'
            AND cr_dteBirth <='.strPrepStr($mysqlEndDate)."
         $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strClientAgeReportExport(&$sRpt,
                                     $bReport, $lStartRec,   $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strClientAgeReport($sRpt,
                                   $lStartRec, $lRecsPerPage));
      }else {
         return($this->strClientAgeExport($sRpt));
      }
   }

   function strClientAgeExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;

      $mysqlDteBase = str_replace("'", '', strPrepDate($gdteNow));
      dateRangeYears($mysqlDteBase, $sRpt->lStartAge, $sRpt->lEndAge, $mysqlStartDate, $mysqlEndDate);
      
      sqlQualClientViaStatus(
                       $sqlWhereSubquery, $strInner, true,
                       true,              false,     false);

      $sqlStr =
        'SELECT '.strExportFields_Client()."
            FROM client_records
               INNER JOIN client_location             ON cr_lLocationID         = cl_lKeyID
               INNER JOIN client_status_cats          ON cr_lStatusCatID        = csc_lKeyID
               INNER JOIN lists_client_vocab          ON cr_lVocID              = cv_lKeyID
               
               $strInner
               
               LEFT  JOIN lists_generic               ON cr_lAttributedTo       = lgen_lKeyID
            WHERE 1
            AND NOT cr_bRetired

            $sqlWhereSubquery
            
            AND cr_dteBirth > ".strPrepStr($mysqlStartDate).'
            AND cr_dteBirth <='.strPrepStr($mysqlEndDate).';';

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   function strClientAgeReport(&$sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;

      $cClient = new mclients;

      $strOut = '';

      $mysqlDteBase = str_replace("'", '', strPrepDate($gdteNow));
      dateRangeYears($mysqlDteBase, $sRpt->lStartAge, $sRpt->lEndAge, $mysqlStartDate, $mysqlEndDate);

      $cClient->strClientLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      $cClient->strExtraClientWhere = "
            AND cst_bShowInDir
            AND cr_dteBirth > ".strPrepStr($mysqlStartDate).'
            AND cr_dteBirth <='.strPrepStr($mysqlEndDate).' ';

      $cClient->loadClientsGeneric();
      if ($cClient->lNumClients == 0){
         return('<i><br>There are no clients who meet your search criteria.</i>');
      }else {
         return($this->strBasicClientRpt($cClient, 'Client Age: '.htmlspecialchars($sRpt->label)));
      }
   }
      
   private function strBasicClientRpt(&$cClient, $strTitle){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut =
         '<table class="enpRptC">
            <tr>
               <td class="enpRptTitle" colspan="5">'
                  .$strTitle.'
               </td>
            </tr>';

      $strOut .= '
            <tr>
               <td class="enpRptLabel">
                  Client ID
               </td>
               <td class="enpRptLabel">
                  Name
               </td>
               <td class="enpRptLabel">
                  Birthday/Age
               </td>
               <td class="enpRptLabel">
                  Location
               </td>
               <td class="enpRptLabel">
                  Status
               </td>
            </tr>';

      foreach ($cClient->clients as $client){
         $strOut .= '
               <tr class="makeStripe">
                  <td class="enpRpt" style="text-align: center;">'
                     .str_pad($client->lKeyID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                     .strLinkView_ClientRecord($client->lKeyID, 'View client record', true).'
                  </td>
                  <td class="enpRpt" >'
                     .htmlspecialchars($client->strLName.', '.$client->strFName).'
                  </td>
                  <td class="enpRpt" >'
                     .$client->strClientAgeBDay.'
                  </td>
                  <td class="enpRpt" >'
                     .htmlspecialchars($client->strLocation.' / '.$client->strLocCountry).'
                  </td>
                  <td class="enpRpt">'
                     .htmlspecialchars($client->curStat_strStatus).'
                  </td>
               </tr>';
      }

      $strOut .= '</table><br>';
      return($strOut);
   }

   function lNumRecsBDayReport(
                           &$sRpt,
                           $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;

      $lMonth = $sRpt->lMonth;   
      $lLocID = $sRpt->lLocID;
      
      if ($lLocID > 0){
         $strLocWhere = " AND (cr_lLocationID = $lLocID) ";
      }else {
         $strLocWhere = '';
      }

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      sqlQualClientViaStatus(
                       $sqlWhereSubquery, $strInner, true,
                       true,              false,     false);

      $sqlStr =
        "SELECT cr_lKeyID
            FROM client_records $strInner
            WHERE 1
            AND NOT cr_bRetired

            $sqlWhereSubquery
            AND MONTH(cr_dteBirth) = $lMonth
            $strLocWhere
         $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }
   
   function strClientBDayReportExport(&$sRpt,
                                      $bReport, $lStartRec,   $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strClientBDayReport($sRpt, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strClientBDayExport($sRpt));
      }
   }
   
   function strClientBDayReport(&$sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;
      
      $lMonth = $sRpt->lMonth;   
      $lLocID = $sRpt->lLocID;
      
      if ($lLocID > 0){
         $strLocWhere = " AND (cr_lLocationID = $lLocID) ";
         $loc = new mclient_locations;
         $loc->loadLocationRec($lLocID);
         $strLocLabel = '('.htmlspecialchars($loc->strLocation).')';
      }else {
         $strLocWhere = '';
         $strLocLabel = '(all locations)';
      }
      
      $cClient = new mclients;

      $strOut = '';

      $cClient->strClientLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      $cClient->strExtraClientWhere = "
            $strLocWhere
            AND cst_bShowInDir
            AND MONTH(cr_dteBirth) = $lMonth ";
      $cClient->strClientOrder = ' DAY(cr_dteBirth), cr_dteBirth, cl_strLocation, cr_strLName, cr_strFName, cr_strMName, cr_lKeyID ';

      $cClient->loadClientsGeneric();
      if ($cClient->lNumClients == 0){
         return('<i><br>There are no clients who meet your search criteria.</i>');
      }else {
         return($this->strBasicClientRpt(
                         $cClient, 
                         'Clients with a Birthday in '.strXlateMonth($lMonth).' '.$strLocLabel));
      }
   }   
   
   function strClientBDayExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;

      $lMonth = $sRpt->lMonth;   
      $lLocID = $sRpt->lLocID;
      
      if ($lLocID > 0){
         $strLocWhere = " AND (cr_lLocationID = $lLocID) ";
      }else {
         $strLocWhere = '';
      }
      
      sqlQualClientViaStatus(
                       $sqlWhereSubquery, $strInner, true,
                       true,              false,     false);

      $sqlStr =
        'SELECT '.strExportFields_Client()."
            FROM client_records
               INNER JOIN client_location             ON cr_lLocationID         = cl_lKeyID
               INNER JOIN client_status_cats          ON cr_lStatusCatID        = csc_lKeyID
               INNER JOIN lists_client_vocab          ON cr_lVocID              = cv_lKeyID
               
               $strInner
               
               LEFT  JOIN lists_generic               ON cr_lAttributedTo       = lgen_lKeyID
            WHERE 1
            AND NOT cr_bRetired

            $sqlWhereSubquery
            
            AND MONTH(cr_dteBirth) = $lMonth ";

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }
   

   
   
   
   function lNumRecsCViaStatReport(
                           &$sRpt,
                           $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lStatusID = $sRpt->lStatusID;   
      
      $strStatIDWhere = " AND (cst_lKeyID = $lStatusID) ";

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      sqlQualClientViaStatus(
                       $sqlWhereSubquery, $strInner, false,
                       true,              false,     false);

      $sqlStr =
        "SELECT cr_lKeyID
            FROM client_records $strInner
            WHERE 1
            AND NOT cr_bRetired
            $sqlWhereSubquery
            $strStatIDWhere
         $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }
   
   function strCViaStatIDReportExport(&$sRpt,
                                      $bReport, $lStartRec,   $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lStatusID = $sRpt->lStatusID;   
      if ($bReport){
         return($this->strCViaStatIDReport($sRpt, $lStatusID, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strCViaStatIDExport($sRpt, $lStatusID));
      }
   }
   
   function strCViaStatIDReport(&$sRpt, $lStatusID, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cClient = new mclients;
      $cStatus = new mclient_status;
      $statInfo = $cStatus->statusInfoViaStatID($lStatusID);
      $strOut = '';

      $cClient->strClientLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      $cClient->strExtraClientWhere = " AND (cst_lKeyID = $lStatusID) ";

      $cClient->loadClientsGeneric();
      if ($cClient->lNumClients == 0){
         return('<i><br>There are no clients who meet your search criteria.</i>');
      }else {
         return($this->strBasicClientRpt($cClient, 'Client with status: ['
                       .htmlspecialchars($statInfo->strCatName).'] '
                       .htmlspecialchars($statInfo->strStatus).'<br>
                       <span style="font-weight: normal; font-size: 9pt;">Status properties: '
                       .($statInfo->bShowInDir ? 'Show' : 'Do Not Show').' in Directory / '
                       .($statInfo->bAllowSponsorship ? '' : 'NOT ').' Sponsorable
                       </span>'
                       ));
      }   
   }
   
   function strCViaStatIDExport(&$sRpt, $lStatusID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strLocWhere = " AND (cst_lKeyID = $lStatusID) ";
      
      sqlQualClientViaStatus(
                       $sqlWhereSubquery, $strInner, true,
                       true,              false,     false);

      $sqlStr =
        'SELECT '.strExportFields_Client()."
            FROM client_records
               INNER JOIN client_location             ON cr_lLocationID         = cl_lKeyID
               INNER JOIN client_status_cats          ON cr_lStatusCatID        = csc_lKeyID
               INNER JOIN lists_client_vocab          ON cr_lVocID              = cv_lKeyID
               
               $strInner
               
               LEFT  JOIN lists_generic               ON cr_lAttributedTo       = lgen_lKeyID
            WHERE 1
            AND NOT cr_bRetired

            $sqlWhereSubquery
            $strLocWhere ;";

      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }
   
   
   
   
   
   function lNumRecsCViaStatCatReport(
                           &$sRpt,
                           $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lStatCatID = $sRpt->lStatCatID;   
      
      $strStatCatIDWhere = " AND (csc_lKeyID = $lStatCatID) ";

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      sqlQualClientViaStatus(
                       $sqlWhereSubquery, $strInner, false,
                       true,              false,     false);

      $sqlStr =
        "SELECT cr_lKeyID
            FROM client_records 
               $strInner
               INNER JOIN client_status_cats ON csc_lKeyID=cst_lClientStatusCatID
            WHERE 1
            AND NOT cr_bRetired
            $sqlWhereSubquery
            $strStatCatIDWhere
         $strLimit;";

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }
   
   function strCViaStatCatIDReportExport(&$sRpt,
                                      $bReport, $lStartRec,   $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lStatCatID = $sRpt->lStatCatID;   
      if ($bReport){
         return($this->strCViaStatCatIDReport($sRpt, $lStatCatID, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strCViaStatCatIDExport($sRpt, $lStatCatID));
      }
   }
   
   function strCViaStatCatIDReport(&$sRpt, $lStatCatID, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bActive = $sRpt->bActive;
      $cClient = new mclients;
      $cStatus = new mclient_status;
      $statCat = $cStatus->strStatCatViaCatID($lStatCatID);
      $strOut = '';

      $cClient->strClientLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      $cClient->strExtraClientWhere = 
                 "  AND (cst_lClientStatusCatID = $lStatCatID) "
                .'  AND '.($bActive ? '' : 'NOT ').' cst_bShowInDir ';

      $cClient->loadClientsGeneric();
      if ($cClient->lNumClients == 0){
         return('<i><br>There are no clients who meet your search criteria.</i>');
      }else {
         return($this->strBasicClientRpt($cClient, ($bActive ? 'Active ' : 'Inactive ').'Clients in status category "'
                       .htmlspecialchars($statCat).'"'));
      }   
   }
   
   function strCViaStatCatIDExport(&$sRpt, $lStatCatID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strLocWhere = " AND (cst_lClientStatusCatID = $lStatCatID) ";
      
      sqlQualClientViaStatus(
                       $sqlWhereSubquery, $strInner, true,
                       true,              false,     false);

      $sqlStr =
        'SELECT '.strExportFields_Client()."
            FROM client_records
               INNER JOIN client_location             ON cr_lLocationID         = cl_lKeyID
               INNER JOIN client_status_cats          ON cr_lStatusCatID        = csc_lKeyID
               INNER JOIN lists_client_vocab          ON cr_lVocID              = cv_lKeyID
               
               $strInner
               
               LEFT  JOIN lists_generic               ON cr_lAttributedTo       = lgen_lKeyID
            WHERE 1
            AND NOT cr_bRetired

            $sqlWhereSubquery
            $strLocWhere ;";
/*            
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__);
$zzzstrFile=substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1)));
echoT('<font class="debug">'.$zzzstrFile.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br(htmlspecialchars($sqlStr))."<br><br></font>\n");
//die;
*/
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }
   
   
   
   
   
   
}