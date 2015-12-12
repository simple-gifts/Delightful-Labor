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
      $this->load->model('auctions/mauctions', 'cAuction');
--------------------------------------------------------------------

---------------------------------------------------------------------*/


class mauctions extends CI_Model{

   public
       $lAuctionID, $lNumAuctions, $auctions,
       $strWhereExtra;

   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->lAuctionID = $this->lNumAuctions = $this->auctions = null;
      $this->strWhereExtra = '';
   }


      /* ---------------------------------------------------
                    A U C T I O N   E V E N T S
         --------------------------------------------------- */

   function loadAuctionByAucID($lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = " AND auc_lKeyID=$lAuctionID ";
      $this->loadAuctions();
   }

   function lCountAuctions(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->auctions = array();
      $sqlStr =
        'SELECT COUNT(*) AS lNumRecs
         FROM gifts_auctions
         WHERE NOT auc_bRetired;';
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }

   function loadAuctions(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->auctions = array();
      $sqlStr =
        "SELECT
            auc_lKeyID, auc_strAuctionName, auc_strDescription,
            auc_dteAuctionDate,

            auc_strLocation, auc_strContact, auc_strPhone, auc_strEmail,
            auc_lDefaultBidSheet, abs_lKeyID, abs_lTemplateID, abs_strSheetName,
            auc_lCampaignID, gc_strCampaign,
            ga_lKeyID, ga_strAccount,

            auc_lACOID,
            aco_strFlag, aco_strCurrencySymbol, aco_strName,

            auc_lOriginID, auc_lLastUpdateID,
            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            UNIX_TIMESTAMP(auc_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(auc_dteLastUpdate) AS dteLastUpdate

         FROM gifts_auctions
            INNER JOIN admin_users AS usersC   ON auc_lOriginID     = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL   ON auc_lLastUpdateID = usersL.us_lKeyID
            INNER JOIN admin_aco               ON auc_lACOID        = aco_lKeyID
            INNER JOIN gifts_campaigns         ON auc_lCampaignID   = gc_lKeyID
            INNER JOIN gifts_accounts          ON gc_lAcctID        = ga_lKeyID
            LEFT  JOIN gifts_auctions_bidsheets ON abs_lKeyID        = auc_lDefaultBidSheet

         WHERE NOT auc_bRetired
            $this->strWhereExtra
         ORDER BY auc_dteAuctionDate DESC, auc_strAuctionName, auc_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumAuctions = $lNumAuctions = $query->num_rows();
      if ($lNumAuctions == 0){
         $this->auctions[0] = new stdClass;
         $auction = &$this->auctions[0];

         $auction->lKeyID            =
         $auction->strAuctionName    =
         $auction->strSafeName       =
         $auction->strDescription    =
         $auction->dteAuction        =
         $auction->mdteAuction       =

         $auction->lDefaultBidSheet  =
         $auction->lBidsheetID       =
         $auction->lTemplateID       =
         $auction->tInfo             =

         $auction->strLocation       =
         $auction->strContact        =
         $auction->strPhone          =
         $auction->strEmail          =
         $auction->lCampaignID       =
         $auction->strCampaign       =
         $auction->lAccountID        =
         $auction->strAccount        =
         $auction->lACOID            =
         $auction->strFlag           =
         $auction->strCurrencySymbol =
         $auction->strACOName        =
         $auction->auc_lOriginID     =
         $auction->auc_lLastUpdateID =
         $auction->strCFName         =
         $auction->strCLName         =
         $auction->strLFName         =
         $auction->strLLName         =
         $auction->dteOrigin         =
         $auction->dteLastUpdate     = null;
      }else {
         $idx = 0;
         $cACO = new madmin_aco;
         foreach ($query->result() as $row){
            $this->auctions[$idx] = new stdClass;
            $auction = &$this->auctions[$idx];

            $auction->lKeyID            = $row->auc_lKeyID;
            $auction->strAuctionName    = $row->auc_strAuctionName;
            $auction->strSafeName       = htmlspecialchars($row->auc_strAuctionName);
            $auction->strDescription    = $row->auc_strDescription;
            $auction->dteAuction        = dteMySQLDate2Unix($row->auc_dteAuctionDate);
            $auction->mdteAuction       = $row->auc_dteAuctionDate;

            $auction->lDefaultBidSheet  = $row->auc_lDefaultBidSheet;
            $auction->lBidsheetID       = $row->abs_lKeyID;
            $auction->lTemplateID       = $lTemplateID = $row->abs_lTemplateID;
            if (is_null($lTemplateID)){
               $auction->tInfo = null;
            }else {
               strXlateTemplate($lTemplateID, $auction->tInfo);
            }
            $auction->strSheetName      = $row->abs_strSheetName;

            $auction->lCampaignID       = $row->auc_lCampaignID;
            $auction->strCampaign       = $row->gc_strCampaign;
            $auction->lAccountID        = $row->ga_lKeyID;
            $auction->strAccount        = $row->ga_strAccount;

            $auction->strLocation       = $row->auc_strLocation;
            $auction->strContact        = $row->auc_strContact;
            $auction->strPhone          = $row->auc_strPhone;
            $auction->strEmail          = $row->auc_strEmail;

            $auction->lACOID            = $row->auc_lACOID;
            $auction->strCurrencySymbol = $row->aco_strCurrencySymbol;
            $auction->strACOName        = $row->aco_strName;
            $auction->strFlag           = $row->aco_strFlag;
            $auction->strFlagImg        = $cACO->strFlagImage($auction->strFlag, $auction->strACOName);

            $auction->auc_lOriginID     = $row->auc_lOriginID;
            $auction->auc_lLastUpdateID = $row->auc_lLastUpdateID;
            $auction->strCFName         = $row->strCFName;
            $auction->strCLName         = $row->strCLName;
            $auction->strLFName         = $row->strLFName;
            $auction->strLLName         = $row->strLLName;
            $auction->dteOrigin         = $row->dteOrigin;
            $auction->dteLastUpdate     = $row->dteLastUpdate;

            ++$idx;
         }
      }
   }

   function addNewAuction(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $auction = &$this->auctions[0];

      $sqlStr =
         'INSERT INTO gifts_auctions
          SET '.$this->sqlCommonAuctions().",
            auc_lOriginID = $glUserID,
            auc_dteOrigin = NOW(),
            auc_bRetired  = 0;";
      $this->db->query($sqlStr);
      return($auction->lKeyID = $this->db->insert_id());
   }

   function updateAuction($lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'UPDATE gifts_auctions
          SET '.$this->sqlCommonAuctions()."
          WHERE auc_lKeyID = $lAuctionID;";
      $this->db->query($sqlStr);
   }

   function sqlCommonAuctions(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $auction = &$this->auctions[0];
      if (is_null($auction->lDefaultBidSheet)){
         $strDefBS = 'null';
      }else {
         $strDefBS = ((int)$auction->lDefaultBidSheet).'';
      }

      return( '
         auc_strAuctionName   = '.strPrepStr($auction->strAuctionName).',
         auc_strDescription   = '.strPrepStr($auction->strDescription).',
         auc_dteAuctionDate   = '.strPrepStr($auction->mdteAuction).',
         auc_lACOID           = '.(int)$auction->lACOID.',
         auc_strEmail         = '.strPrepStr($auction->strEmail).',
         auc_lCampaignID      = '.(int)($auction->lCampaignID).',
         auc_strLocation      = '.strPrepStr($auction->strLocation).',
         auc_strContact       = '.strPrepStr($auction->strContact).',
         auc_strPhone         = '.strPrepStr($auction->strPhone).",
         auc_lDefaultBidSheet = $strDefBS,
         auc_lLastUpdateID    = $glUserID,
         auc_dteLastUpdate    = NOW() ");
   }

   function strDDLAuctions($strName, $lMatchID, $bAddBlank){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strOut = '<select name="'.$strName.'">'."\n";
      if ($bAddBlank){
         $strOut .= '<option value="-1">&nbsp;</option>'."\n";
      }

      $sqlStr =
        "SELECT
            auc_lKeyID, auc_strAuctionName, auc_dteAuctionDate
         FROM gifts_auctions
         WHERE NOT auc_bRetired
         ORDER BY auc_strAuctionName, auc_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumAuctions = $query->num_rows();
      if ($lNumAuctions > 0){
         foreach ($query->result() as $row){
            $lKeyID = $row->auc_lKeyID;
            $strOut .= '<option value="'.$lKeyID.'" '.($lKeyID==$lMatchID ? 'SELECTED' : '')
                        .'>'.htmlspecialchars($row->auc_strAuctionName).'&nbsp;('
                        .date($genumDateFormat, dteMySQLDate2Unix($row->auc_dteAuctionDate)).')'
                        .'</option>'."\n";
         }
      }
      $strOut .= '</select>'."\n";
      return($strOut);
   }

   public function strAuctionHTMLSummary(){
   //-----------------------------------------------------------------------
   // assumes user has called $cAuction->loadAuctionByAucID($lAuctionID);
   //-----------------------------------------------------------------------
      global $genumDateFormat;

      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $auction    = &$this->auctions[0];
      $lAuctionID = $auction->lKeyID;
      $strOut =
          $clsRpt->openReport('', '')
         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Auction Name:')
         .$clsRpt->writeCell ($auction->strSafeName)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Auction ID:')
         .$clsRpt->writeCell (
                               str_pad($lAuctionID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                              .strLinkView_AuctionRecord($lAuctionID, 'View auction record', true))
         .$clsRpt->closeRow  ()

         .$clsRpt->writeLabel('Overview:')
         .$clsRpt->writeCell (strLinkView_AuctionOverview($lAuctionID, 'Overview', true))
         .$clsRpt->closeRow  ()

         .$clsRpt->writeLabel('Packages:')
         .$clsRpt->writeCell (strLinkView_AuctionPackages($lAuctionID, 'View packages for this auction', true))
         .$clsRpt->closeRow  ()


         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Date:')
         .$clsRpt->writeCell (date($genumDateFormat, $auction->dteAuction))
         .$clsRpt->closeRow  ()

         .$clsRpt->closeReport('<br>');

      return($strOut);
   }

   function removeAuction($lAuctionID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

         // associated bid templates
      $sqlStr =
        "UPDATE gifts_auctions_bidsheets
         SET abs_bRetired=1, abs_lLastUpdateID=$glUserID
         WHERE abs_lAuctionID=$lAuctionID;";
      $this->db->query($sqlStr);

      $sqlStr =
          "UPDATE gifts_auctions
           SET
              auc_bRetired      = 1,
              auc_lLastUpdateID = $glUserID
           WHERE auc_lKeyID=$lAuctionID;";
      $this->db->query($sqlStr);
   }

}