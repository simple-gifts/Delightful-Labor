<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2013-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('auctions/mpackages', 'cPackages');
--------------------------------------------------------------------

---------------------------------------------------------------------*/


class mpackages extends CI_Model{

   public
       $lAuctionID, $lPackageID, $lNumPackages, $packages,
       $strWhereExtra;

   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->lAuctionID = $this->lPackageID = $this->lNumPackages = $this->packages = null;
      $this->strWhereExtra = '';
   }



      /* ---------------------------------------------------
                         P A C K A G E S
         --------------------------------------------------- */
   function lCountPackagesViaAID($lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "
         SELECT COUNT(*) AS lNumRecs
         FROM gifts_auctions_packages
         WHERE ap_lAuctionID=$lAuctionID AND NOT ap_bRetired;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return($row->lNumRecs);
   }

   function loadPackageByPacID($lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = " AND ap_lKeyID=$lPackageID ";
      $this->loadPackages();
   }

   function loadPackageByAID($lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = " AND ap_lAuctionID=$lAuctionID ";
      $this->loadPackages();
   }

   function loadPackages(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $packages = array();
      $sqlStr =
        "SELECT
            ap_lKeyID, ap_lAuctionID, ap_strPackageName,
            ap_curMinBidAmnt, ap_curReserveAmnt, ap_curMinBidInc,
            ap_curBuyItNowAmnt, ap_curWinBidAmnt,
            ap_strDescription, ap_strInternalNotes, ap_lBidWinnerID, ap_dteWinnerContact,
            ap_lBidSheetID, abs_lKeyID, abs_lTemplateID, abs_strSheetName,
            ap_lGiftID, gi_curAmnt,

            bwPeople.pe_bBiz AS bw_bBiz, bwPeople.pe_strFName AS bw_strFName, bwPeople.pe_strLName AS bw_strLName,

            auc_strAuctionName, auc_lDefaultBidSheet, auc_dteAuctionDate,
            auc_lACOID, aco_strFlag, aco_strCurrencySymbol,

            ap_lOriginID, ap_lLastUpdateID,
            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            UNIX_TIMESTAMP(ap_dteOrigin) AS dteOrigin,
            UNIX_TIMESTAMP(ap_dteLastUpdate) AS dteLastUpdate

         FROM gifts_auctions_packages
            INNER JOIN gifts_auctions           ON ap_lAuctionID    = auc_lKeyID
            INNER JOIN admin_aco                ON auc_lACOID       = aco_lKeyID
            INNER JOIN admin_users AS usersC    ON ap_lOriginID     = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL    ON ap_lLastUpdateID = usersL.us_lKeyID
            LEFT  JOIN gifts                    ON ap_lGiftID       = gi_lKeyID
            LEFT  JOIN gifts_auctions_bidsheets ON abs_lKeyID       = ap_lBidSheetID
            LEFT  JOIN people_names AS bwPeople ON pe_lKeyID        = ap_lBidWinnerID

         WHERE NOT auc_bRetired AND NOT ap_bRetired
            $this->strWhereExtra
         ORDER BY ap_strPackageName, ap_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumPackages = $query->num_rows();

      if ($this->lNumPackages==0){
         $this->packages[0] = new stdClass;
         $package = &$this->packages[0];

         $package->lKeyID             =

         $package->lAuctionID         =
         $package->lACOID             =
         $package->strFlag            =
         $package->strCurrencySymbol  =

         $package->strAuctionName     =
         $package->dteAuction         =
         $package->strPackageName     =
         $package->strPackageSafeName =
         $package->curMinBidAmnt      =
         $package->curReserveAmnt     =
         $package->curMinBidInc       =
         $package->curBuyItNowAmnt    =
         $package->curWinBidAmnt      =
         $package->curActualGiftAmnt  =
         $package->strDescription     =
         $package->strInternalNotes   =
         $package->lBidWinnerID       =
         $package->dteContacted       =
         $package->mdteContacted      =
         $package->lGiftID            =
         $package->lOriginID          =
         $package->lLastUpdateID      =
         $package->strCFName          =
         $package->strCLName          =
         $package->strLFName          =
         $package->strLLName          =
         $package->dteOrigin          =
         $package->dteLastUpdate      = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->packages[$idx] = new stdClass;
            $package = &$this->packages[$idx];

            $package->lKeyID             = $row->ap_lKeyID;

            $package->lAuctionID         = $row->ap_lAuctionID;
            $package->strAuctionName     = $row->auc_strAuctionName;
            $package->dteAuction         = dteMySQLDate2Unix($row->auc_dteAuctionDate);
            $package->lACOID             = $row->auc_lACOID;
            $package->strFlag            = $row->aco_strFlag;
            $package->strCurrencySymbol  = $row->aco_strCurrencySymbol;

            $package->strPackageName     = $row->ap_strPackageName;
            $package->strPackageSafeName = htmlspecialchars($row->ap_strPackageName);
            $package->curMinBidAmnt      = $row->ap_curMinBidAmnt;
            $package->curBuyItNowAmnt    = $row->ap_curBuyItNowAmnt;
            $package->curReserveAmnt     = $row->ap_curReserveAmnt;
            $package->curMinBidInc       = $row->ap_curMinBidInc;
            $package->curWinBidAmnt      = $row->ap_curWinBidAmnt;
            $package->curActualGiftAmnt  = $row->gi_curAmnt;
            $package->strDescription     = $row->ap_strDescription;
            $package->strInternalNotes   = $row->ap_strInternalNotes;

            $package->lBidSheetID        = $row->ap_lBidSheetID;
            if (is_null($package->lBidSheetID)) $package->lBidSheetID = $row->auc_lDefaultBidSheet;
            $package->lTemplateID        = $lTemplateID = $row->abs_lTemplateID;
            if (is_null($lTemplateID)){
               $package->tInfo = null;
            }else {
               strXlateTemplate($lTemplateID, $package->tInfo);
            }
            $package->strSheetName       = $row->abs_strSheetName;

            $package->lBidWinnerID       = $row->ap_lBidWinnerID;
            $package->bw_bBiz            = $row->bw_bBiz;
            $package->bw_strFName        = $row->bw_strFName;
            $package->bw_strLName        = $row->bw_strLName;
            if (is_null($package->lBidWinnerID)){
               $package->bw_strSafeName = 'not set';
            }else {
               if ($package->bw_bBiz){
                  $package->bw_strSafeName = htmlspecialchars($package->bw_strLName).' (business)';
               }else {
                  $package->bw_strSafeName = htmlspecialchars($package->bw_strFName.' '.$package->bw_strLName);
               }
            }

            $package->dteContacted       = dteMySQLDate2Unix($row->ap_dteWinnerContact);
            $package->mdteContacted      = $row->ap_dteWinnerContact;
            $package->lGiftID            = $row->ap_lGiftID;
            $package->lOriginID          = $row->ap_lOriginID;
            $package->lLastUpdateID      = $row->ap_lLastUpdateID;
            $package->strCFName          = $row->strCFName;
            $package->strCLName          = $row->strCLName;
            $package->strLFName          = $row->strLFName;
            $package->strLLName          = $row->strLLName;
            $package->dteOrigin          = $row->dteOrigin;
            $package->dteLastUpdate      = $row->dteLastUpdate;

            ++$idx;
         }
      }
   }

   function loadPackageProfileImage(){
   //---------------------------------------------------------------------
   // note: can't do this function inside of $this->loadPackages because
   // it creates a circular reference in the image_doc class.
   //---------------------------------------------------------------------
      $cImages = new mimage_doc;
      if ($this->lNumPackages > 0){
         $idx = 0;

         foreach ($this->packages as $package){
            $lPackageID = $package->lKeyID;

               // profile image
            $cImages->loadProfileImage(CENUM_CONTEXT_AUCTIONPACKAGE, $lPackageID);

            if ($cImages->lNumImageDocs==0){
               $package->profileImage = null;
            }else {
               $package->profileImage = new stdClass;
               $pImg = &$cImages->imageDocs[0];

               $package->profileImage->imageID          = $pImg->lKeyID;
               $package->profileImage->caption          = $pImg->strCaptionTitle;
               $package->profileImage->strUserFN        = $pImg->strUserFN;
               $package->profileImage->strSystemFN      = $pImg->strSystemFN;
               $package->profileImage->strSystemThumbFN = $pImg->strSystemThumbFN;
               $package->profileImage->strPath          = $pImg->strPath;
            }
            ++$idx;
         }
      }
   }

   function addNewPackage(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $package = &$this->packages[0];

      $sqlStr = '
          INSERT INTO gifts_auctions_packages
          SET '.$this->strSqlCommonPackageAddEdit().',
            ap_lAuctionID       = '.$package->lAuctionID.",
            ap_curWinBidAmnt    = 0.0,
            ap_lBidWinnerID     = NULL,
            ap_dteWinnerContact = NULL,
            ap_lGiftID   = NULL,
            ap_bRetired  = 0,
            ap_lOriginID = $glUserID,
            ap_dteOrigin = NOW();";
      $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function updatePackage($lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $package = &$this->packages[0];

      $sqlStr = '
          UPDATE gifts_auctions_packages
          SET '.$this->strSqlCommonPackageAddEdit()."
          WHERE ap_lKeyID=$lPackageID;";
      $this->db->query($sqlStr);
   }

   private function strSqlCommonPackageAddEdit(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $package = &$this->packages[0];
      if (is_null($package->lBidSheetID)){
         $strBidSheet = 'null';
      }else {
         $strBidSheet = ((int)$package->lBidSheetID).'';
      }

      if (is_null($package->curBuyItNowAmnt) || ($package->curBuyItNowAmnt < 0.001)){
         $strBuyItNow = 'null';
      }else {
         $strBuyItNow = number_format($package->curBuyItNowAmnt, 2, '.', '');
      }

      return('
         ap_strPackageName   ='.strPrepStr($package->strPackageName).',
         ap_curMinBidAmnt    ='.number_format($package->curMinBidAmnt, 2, '.', '').',
         ap_curReserveAmnt   ='.number_format($package->curReserveAmnt, 2, '.', '').',
         ap_curMinBidInc     ='.number_format($package->curMinBidInc, 2, '.', '').',
         ap_curBuyItNowAmnt  = '.$strBuyItNow.',
         ap_strDescription   ='.strPrepStr($package->strDescription).',
         ap_strInternalNotes ='.strPrepStr($package->strInternalNotes).",
         ap_lBidSheetID      = $strBidSheet,
         ap_lLastUpdateID    = $glUserID,
         ap_dteLastUpdate    = NOW() ");
   }


   public function packageHTMLSummary(){
   //-----------------------------------------------------------------------
   // assumes user has called $cPackages->loadPackageByPacID($lPackageID);
   //-----------------------------------------------------------------------
      global $genumDateFormat;

      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $package    = &$this->packages[0];
      $lAuctionID = $package->lAuctionID;
      $lPackageID = $package->lKeyID;
      $strOut =
          $clsRpt->openReport('', '')
         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Auction Name:')
         .$clsRpt->writeCell (htmlspecialchars($package->strAuctionName))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Overview:')
         .$clsRpt->writeCell (strLinkView_AuctionOverview($lAuctionID, 'Auction overview', true))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Auction ID:')
         .$clsRpt->writeCell (
                               str_pad($lAuctionID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                              .strLinkView_AuctionRecord($lAuctionID, 'View auction record', true))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Package Name:')
         .$clsRpt->writeCell ($package->strPackageSafeName)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Package ID:')
         .$clsRpt->writeCell (
                               str_pad($lPackageID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                              .strLinkView_AuctionPackageRecord($lPackageID, 'View auction package record', true))
         .$clsRpt->closeRow  ();

      if (is_null($package->lBidWinnerID)){
         $strOut .=
             $clsRpt->openRow   (false)
            .$clsRpt->writeLabel('Bid Winner:')
            .$clsRpt->writeCell ('<i>Not set</i>')
            .$clsRpt->closeRow  ();
      }else {
         if ($package->bw_bBiz){
            $strLink = strLinkView_BizRecord($package->lBidWinnerID, 'View business record', true);
         }else {
            $strLink = strLinkView_PeopleRecord($package->lBidWinnerID, 'View people record', true);
         }
         $strOut .=
             $clsRpt->openRow   (false)
            .$clsRpt->writeLabel('Bid Winner:')
            .$clsRpt->writeCell ($package->bw_strSafeName.'&nbsp;'.$strLink)
            .$clsRpt->closeRow  ();
      }

      $strOut .=
          $clsRpt->closeReport('<br>');
      return($strOut);
   }

   function updateWinBidAmount($lPackageID, $lDonorID, $curWinBid){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
          'UPDATE gifts_auctions_packages
           SET
              ap_curWinBidAmnt = '.number_format($curWinBid, 2, '.', '').",
              ap_lBidWinnerID  = $lDonorID,
              ap_dteWinnerContact = NOW()
           WHERE ap_lKeyID=$lPackageID;";
      $this->db->query($sqlStr);
   }

   function setBidGiftID($lPackageID, $lGiftID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
          "UPDATE gifts_auctions_packages
           SET
              ap_lGiftID = $lGiftID
           WHERE ap_lKeyID=$lPackageID;";
      $this->db->query($sqlStr);
   }

   function removeBid($lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
          "UPDATE gifts_auctions_packages
           SET
              ap_curWinBidAmnt    = 0.0,
              ap_lBidWinnerID     = NULL,
              ap_dteWinnerContact = NULL,
              ap_lGiftID          = NULL
           WHERE ap_lKeyID=$lPackageID;";
      $this->db->query($sqlStr);
   }

   function removePackage($lPackageID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
          "UPDATE gifts_auctions_packages
           SET
              ap_bRetired      = 1,
              ap_lLastUpdateID = $glUserID
           WHERE ap_lKeyID=$lPackageID;";
      $this->db->query($sqlStr);
   }
}
