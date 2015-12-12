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
   __construct                   ()
  ---------------------------------------------------------------------
      $this->load->model('donations/mdonation_agg', 'clsAgg');
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mdonation_agg extends CI_Model{

   public $bDebug;


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->bDebug = false;
   }

   function strGiftAggReport(&$sRpt, &$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cAC = new maccts_camps;
      $cGoogleRpts = new mgoogle_charts;

      $cGoogleRpts->initPieChart();
      $cGoogleRpts->openPieChart();

      $displayData['strRptTitle'] = $this->strReportTitle($sRpt);
      $strBetween = ' gi_dteDonation BETWEEN '.
                          strPrepDate($sRpt->dteStart).' AND '.strPrepDateTime($sRpt->dteEnd)."\n";
      $lACO = $sRpt->lACO;

      $displayData['lNumGiftMonetary'] = $this->lGiftCnt($strBetween, $lACO, false, false, null, null, $displayData['curTotGiftMonetary']);
      $displayData['lNumSpon']         = $this->lGiftCnt($strBetween, $lACO, false, true,  null, null, $displayData['curTotSpon']);
      $displayData['lNumGIK']          = $this->lGiftCnt($strBetween, $lACO, true,  false, null, null, $displayData['curTotGIK']);

      $pieChart = array();
      $pieChart[0]['label']  = 'Monetary';
      $pieChart[0]['lValue'] = $displayData['curTotGiftMonetary'];
      $pieChart[1]['label']  = 'Sponsorship';
      $pieChart[1]['lValue'] = $displayData['curTotSpon'];
      $pieChart[2]['label']  = 'In-Kind';
      $pieChart[2]['lValue'] = $displayData['curTotGIK'];
      $optionVars = array(
                 'chartArea' => '{left:0,top:0,width:500,height:250}',
                 'legend'    => '{position: \'right\',fontSize: 11, alignment:\'center\'}',
                 'is3D'      => 'true');
      $cGoogleRpts->addPieChart('_01', 'Type', 'Amount',
                             $pieChart, $optionVars, 'chart_01_div');

         //-------------------
         // Accounts
         //-------------------
      $cAC->loadAccounts(false, false, null);
      $displayData['accts']   = $pieChart = array();
      $displayData['acctCnt'] = $cAC->lNumAccts;
      $idx = 0;
      foreach ($cAC->accounts as $acct){
         $displayData['accts'][$idx] = new stdClass;
         $displayData['accts'][$idx]->strSafeAcct = $acct->strSafeName;
         $displayData['accts'][$idx]->ID = $ID = $acct->lKeyID;
         $displayData['accts'][$idx]->lNumGiftsM =
                  $this->lGiftCnt($strBetween, $lACO, false,  false, $ID, null,
                                  $displayData['accts'][$idx]->curTotalM);
         $displayData['accts'][$idx]->lNumGiftsG =
                  $this->lGiftCnt($strBetween, $lACO, true,  false, $ID, null,
                                  $displayData['accts'][$idx]->curTotalG);
         $displayData['accts'][$idx]->lNumGiftsS =
                  $this->lGiftCnt($strBetween, $lACO, false,  true, $ID, null,
                                  $displayData['accts'][$idx]->curTotalS);

         $pieChart[$idx]['label']  = $acct->strAccount;
         $pieChart[$idx]['lValue'] = $displayData['accts'][$idx]->curTotalM +
                                     $displayData['accts'][$idx]->curTotalG +
                                     $displayData['accts'][$idx]->curTotalS;

         ++$idx;
      }

      $cGoogleRpts->addPieChart('_02', 'Account', 'Amount',
                             $pieChart, $optionVars, 'chart_02_div');


         //-------------------
         // Campaigns
         //-------------------
      $cAC->loadCampaigns(false, false, null, false, null);
      $displayData['camps']   = array();
      $displayData['campCnt'] = $cAC->lNumCamps;
      $idx = 0;
      $lDivIDX = 2;
      $lAcctGrp = -999;
      foreach ($cAC->campaigns as $camp){
         $lAcctID = $camp->lAcctID;

         if ($lAcctGrp != $lAcctID){
            if ($lAcctGrp > 0){
               $cGoogleRpts->addPieChart('_'.str_pad($lDivIDX, 2, '0', STR_PAD_LEFT), 'Campaign', 'Amount',
                             $pieChart, $optionVars, 'chart_'.str_pad($lDivIDX, 2, '0', STR_PAD_LEFT).'_div');
            }
            $lAcctGrp = $lAcctID;
            $pidx = 0;
            $pieChart = array();
            ++$lDivIDX;
         }
         $displayData['camps'][$idx] = new stdClass;
         $displayData['camps'][$idx]->strSafeCamp = $camp->strSafeName;
         $displayData['camps'][$idx]->strSafeAcct = $camp->strAcctSafeName;
         $displayData['camps'][$idx]->ID = $ID = $camp->lKeyID;
         $displayData['camps'][$idx]->lAcctID  = $lAcctID;
         $displayData['camps'][$idx]->lNumGiftsM =
                  $this->lGiftCnt($strBetween, $lACO, false,  false, null, $ID,
                                  $displayData['camps'][$idx]->curTotalM);
         $displayData['camps'][$idx]->lNumGiftsG =
                  $this->lGiftCnt($strBetween, $lACO, true,  false, null, $ID,
                                  $displayData['camps'][$idx]->curTotalG);
         $displayData['camps'][$idx]->lNumGiftsS =
                  $this->lGiftCnt($strBetween, $lACO, false,  true, null, $ID,
                                  $displayData['camps'][$idx]->curTotalS);

         $pieChart[$pidx]['label']  = $camp->strCampaign;
         $pieChart[$pidx]['lValue'] = $displayData['camps'][$idx]->curTotalM +
                                      $displayData['camps'][$idx]->curTotalG +
                                      $displayData['camps'][$idx]->curTotalS;

         ++$idx;
         ++$pidx;
      }
      $cGoogleRpts->addPieChart('_'.str_pad($lDivIDX, 2, '0', STR_PAD_LEFT), 'Account', 'Amount',
                    $pieChart, $optionVars, 'chart_'.str_pad($lDivIDX, 2, '0', STR_PAD_LEFT).'_div');
      $cGoogleRpts->closePieChart();
      $displayData['js'] .= $cGoogleRpts->strHeader;

      return('');
   }


   private function strReportTitle(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      switch ($sRpt->rptName){
         case CENUM_REPORTNAME_GIFTAGG:
            $strTitle = 'Donation Aggregate Report';
            break;
         case CENUM_REPORTNAME_GIFTSPONAGG:
            $strTitle = 'Sponsorship Payment Aggregate Report';
            break;
         default:
            screamForHelp($sRpt->rptName.': invalid report type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;

      }
      $strOut = '';
      $strLIStyle = 'margin-left: 20pt; line-height:12pt;';
      $strOut .= '<b>'.$strTitle.'</b>
                  <ul style="list-style-type: square; display:inline; margin-left: 0; padding: 0pt;">';
      if (isset($sRpt->dteStart)){
         $strOut .= '<li style="'.$strLIStyle.'"><b>Date range:</b> '.$sRpt->strDateRange.'</li>';
      }

      if (isset($sRpt->lACO)){
         $strOut .= '<li style="'.$strLIStyle.' "><b>Accounting Country:</b> ';
         if ($sRpt->lACO <= 0){
            $strOut .= 'All countries</li>';
         }else {
            $cACO = new madmin_aco;
            $cACO->loadCountries(false, false, true, $sRpt->lACO);
            $strOut .= $cACO->countries[0]->strName.' '.$cACO->countries[0]->strFlagImg.'</li>';
         }
      }
      $strOut .= '</ul><br>'."\n";
      return($strOut);
   }

/*
   function giftsTotals($strBetween, $lACO,
                        &$lNumGiftMonetary,   &$lNumSpon,   &$lNumGIK,
                        &$curTotGiftMonetray, &$curTotSpon, &$curTotGIK){
*/
   function lGiftCnt($strBetween, $lACO, $bGIK, $bSpon, $lAcctID, $lCampID, &$curSum){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strInner = $strAcct = $strCamp = '';

      if (!is_null($lAcctID)){
         $strInner = ' INNER JOIN gifts_campaigns ON gi_lCampID=gc_lKeyID ';
         $strAcct  = " AND gc_lAcctID=$lAcctID ";
      }
      if (!is_null($lCampID)){
         $strAcct  = " AND gi_lCampID=$lCampID ";
      }

      $sqlStr =
          "SELECT COUNT(*) AS lNumRecs,
             SUM(gi_curAmnt) AS curTotal
           FROM gifts
              $strInner
           WHERE NOT gi_bRetired
              AND ".($bGIK  ? '' : ' NOT ').' gi_bGIK
              AND gi_lSponsorID IS '.($bSpon ? ' NOT ' : '')." NULL
              AND gi_lACOID=$lACO
              $strAcct $strCamp;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      $curSum = $row->curTotal;
      return((integer)$row->lNumRecs);

   }

   function strSponPayAggReport($sRpt, &$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData['strRptTitle'] = $this->strReportTitle($sRpt);
      $strBetween = ' gi_dteDonation BETWEEN '.
                          strPrepDate($sRpt->dteStart).' AND '.strPrepDateTime($sRpt->dteEnd)."\n";
      $lACO = $sRpt->lACO;

      $displayData['lNumSponPay'] = $this->lSponPayCnt($strBetween, $lACO, false, null, false, null, $displayData['curTotSpon']);

         //-----------------------------
         // via sponsorship program
         //-----------------------------
      $cSP = new msponsorship_programs;
      $cSP->loadSponProgs();
      $displayData['lNumSponProg'] = $lNumSponProg = $cSP->lNumSponPrograms;
      $displayData['sp']   = array();
      $idx = 0;
      if ($lNumSponProg > 0){
         foreach ($cSP->sponProgs AS $sProg){
            $displayData['sp'][$idx] = new stdClass;
            $displayData['sp'][$idx]->strSafeName = htmlspecialchars($sProg->strProg);
            $displayData['sp'][$idx]->ID          = $ID = $sProg->lKeyID;

            $displayData['sp'][$idx]->lNumSponPay = $this->lSponPayCnt($strBetween, $lACO,
                                                        true, $ID,  false, null,
                                                        $displayData['sp'][$idx]->curTotSpon);
            ++$idx;
         }
      }

         //-----------------------------
         // via location
         //-----------------------------
      $cLoc = new mclient_locations;
      $cLoc->loadAllLocations();
      $displayData['lNumSponProg'] = $lNumLocs = $cLoc->lNumLocations + 1;
      $displayData['locs']    = array();
      $displayData['locs'][0] = new stdClass;
      $displayData['locs'][0]->strLocation = '(Sponsorships w/o clients)';
      $displayData['locs'][0]->ID = null;
      $displayData['locs'][0]->lNumSponPay = $this->lSponPayCnt($strBetween, $lACO,
                                                        false, null, true, null,
                                                        $displayData['locs'][0]->curTotSpon);
      if ($lNumLocs > 1){
         $idx = 1;
         foreach ($cLoc->clientLocations as $loc){
            $displayData['locs'][$idx] = new stdClass;
            $displayData['locs'][$idx]->strLocation = htmlspecialchars($loc->strLocation.' ('.$loc->strCountry.')');
            $displayData['locs'][$idx]->ID = $ID = $loc->lKeyID;
            $displayData['locs'][$idx]->lNumSponPay = $this->lSponPayCnt($strBetween, $lACO,
                                                        false, null, true, $ID,
                                                        $displayData['locs'][$idx]->curTotSpon);
            ++$idx;
         }
      }

/*
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<pre>'); print_r($displayData); echo('</pre></font><br>');
*/
      return('');

   }


   function lSponPayCnt($strBetween, $lACO, $bViaProg, $lProgID, $bViaLoc, $lLocID, &$curSum){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strInner = $strWhereProg = $strWhereLoc = '';

      if ($bViaProg){
         $strWhereProg = " AND sp_lSponsorProgramID=$lProgID ";
      }
      if ($bViaLoc){
         if (is_null($lLocID)){
            $strWhereLoc = " AND sp_lClientID IS NULL ";
         }else {
            $strWhereLoc = " AND cr_lLocationID=$lLocID ";
            $strInner     = ' INNER JOIN client_records ON sp_lClientID=cr_lKeyID ';
         }
      }

      $sqlStr =
          "SELECT COUNT(*) AS lNumRecs,
             SUM(gi_curAmnt) AS curTotal
           FROM gifts
              INNER JOIN sponsor ON gi_lSponsorID=sp_lKeyID
              $strInner
           WHERE NOT gi_bRetired
              AND gi_lSponsorID IS NOT NULL
              AND gi_lACOID=$lACO
              $strWhereProg $strWhereLoc;";

      $query = $this->db->query($sqlStr);
      $row = $query->row();
      $curSum = $row->curTotal;
      return((integer)$row->lNumRecs);

   }





}