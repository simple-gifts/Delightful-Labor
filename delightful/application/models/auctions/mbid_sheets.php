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
      $this->load->model('auctions/mbid_sheets', 'cBidSheets');
--------------------------------------------------------------------

---------------------------------------------------------------------*/


class mbid_sheets extends CI_Model{

   public
       $lNumBidSheets, $bidSheets,
       $strWhereExtra, $strOrderBidSheet;

   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->lNumBidSheets = $this->bidSheets = null;
      $this->strWhereExtra = $this->strOrderBidSheet = '';
   }


      /* ---------------------------------------------------
                  A U C T I O N   B I D   S H E E T S
         --------------------------------------------------- */

   function loadSheetByBSID($lBSID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = " AND abs_lKeyID=$lBSID ";
      $this->loadBidSheets();
   }

   function loadBidSheets(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glChapterID;
      
      if ($this->strOrderBidSheet == ''){
         $strOrder = ' abs_strSheetName, abs_lKeyID ';
      }else {
         $strOrder = $this->strOrderBidSheet;
      }

      $this->bidSheets = array();
      $sqlStr =
        "SELECT
            abs_lKeyID, abs_lTemplateID, abs_strSheetName, abs_strDescription,
            abs_enumPaperType, abs_lNumSignupPages,
            abs_strSignUpCol1, abs_strSignUpCol2, abs_strSignUpCol3, abs_strSignUpCol4,
            abs_lSigunUpColWidth1, abs_lSigunUpColWidth2, abs_lSigunUpColWidth3, abs_lSigunUpColWidth4,
            abs_bIncludeOrgName, abs_bIncludeOrgLogo, abs_lLogoImgID,
            abs_bIncludeMinBid, abs_bIncludeMinBidInc, abs_bIncludeBuyItNow, abs_bIncludeReserve,
            abs_bIncludeDate, abs_bIncludeFooter,
            abs_bIncludePackageName, abs_bIncludePackageID, abs_bIncludePackageDesc,
            abs_bIncludePackageImage, abs_bIncludePackageEstValue,
            abs_bIncludeItemName, abs_bIncludeItemID, abs_bIncludeItemDesc, abs_bIncludeItemImage,
            abs_bIncludeItemDonor, abs_bIncludeItemEstValue, abs_bIncludeSignup,
            abs_bReadOnly, abs_bRetired,

            abs_lAuctionID, auc_strAuctionName, auc_dteAuctionDate,

            di_lKeyID, di_strSystemFN, di_strSystemThumbFN, di_strPath, di_strCaptionTitle,

            abs_lOriginID, abs_lLastUpdateID,
            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            UNIX_TIMESTAMP(abs_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(abs_dteLastUpdate) AS dteLastUpdate

         FROM gifts_auctions_bidsheets
            INNER JOIN gifts_auctions ON auc_lKeyID=abs_lAuctionID
            LEFT  JOIN docs_images ON abs_lLogoImgID=di_lKeyID
            INNER JOIN admin_users AS usersC   ON abs_lOriginID     = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL   ON abs_lLastUpdateID = usersL.us_lKeyID

          WHERE NOT abs_bRetired
            $this->strWhereExtra
          ORDER BY $strOrder;";

      $query = $this->db->query($sqlStr);
      $this->lNumBidSheets = $lNumBidSheets = $query->num_rows();
      if ($lNumBidSheets == 0){
         $this->bidSheets[0] = new stdClass;
         $bs = &$this->bidSheets[0];

         $bs->lKeyID                  =
         $bs->lTemplateID             =
         $bs->strSheetName            =
         $bs->strDescription          =

         $bs->enumPaperType           =
         $bs->lNumSignupPages         =

         $bs->bIncludeOrgLogo         =
         $bs->lLogoImgID              =
         $bs->lImageID                =
         $bs->strSystemFN             =
         $bs->strSystemThumbFN        =
         $bs->strPath                 =
         $bs->strCaptionTitle         =

         $bs->bIncludeOrgName         =
         $bs->bIncludeMinBid          =
         $bs->bIncludeMinBidInc       =
         $bs->bIncludeBuyItNow        =
         $bs->bIncludeReserve         =
         $bs->bIncludeDate            =
         $bs->bIncludeFooter          =

         $bs->bIncludePackageName     =
         $bs->bIncludePackageID       =
         $bs->bIncludePackageDesc     =
         $bs->bIncludePackageImage    =
         $bs->bIncludePackageEstValue =
         $bs->bIncludeSignup          = 

         $bs->bIncludeItemName        =
         $bs->bIncludeItemID          =
         $bs->bIncludeItemDesc        =
         $bs->bIncludeItemImage       =
         $bs->bIncludeItemDonor       =
         $bs->bIncludeItemEstValue    =

         $bs->lAuctionID              =
         $bs->strAuctionName          =
         $bs->dteAuction              =

         $bs->bRetired                =
         $bs->bReadOnly               =

         $bs->lOriginID               =
         $bs->lLastUpdateID           =
         $bs->strCFName               =
         $bs->strCLName               =
         $bs->strLFName               =
         $bs->strLLName               =
         $bs->dteOrigin               =
         $bs->dteLastUpdate           = null;

         $bs->signUpCols = array();
         $bs->lNumSignupCols = 0;
         for ($jidx = 1; $jidx <= 4; ++$jidx){
            $bs->signUpCols[$jidx] = new stdClass;
            $suCol = &$bs->signUpCols[$jidx];
            $suCol->heading = null;
            $suCol->width   = null;
            $suCol->bShow   = false;
         }

      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->bidSheets[$idx] = new stdClass;
            $bs = &$this->bidSheets[$idx];

            $bs->lKeyID                  = $row->abs_lKeyID;
            $bs->lTemplateID             = $row->abs_lTemplateID;
            $bs->strSheetName            = $row->abs_strSheetName;
            $bs->strDescription          = $row->abs_strDescription;

            $bs->enumPaperType           = $row->abs_enumPaperType;
            $bs->lNumSignupPages         = $row->abs_lNumSignupPages;

               // bid sheets
            $bs->lNumSignupCols = 0;
            $bs->signUpCols = array();
            for ($jidx = 1; $jidx <= 4; ++$jidx){
               $bs->signUpCols[$jidx] = new stdClass;
               $suCol = &$bs->signUpCols[$jidx];
               $suCol->heading = $row->{'abs_strSignUpCol'.$jidx};
               $suCol->width   = $row->{'abs_lSigunUpColWidth'.$jidx};
               $suCol->bShow   = !is_null($suCol->heading);
               if ($suCol->bShow) ++$bs->lNumSignupCols;
            }
            
            $bs->lAuctionID              = $lAuctionID = $row->abs_lAuctionID;
            $bs->strAuctionName          = $row->auc_strAuctionName;
            $bs->dteAuction              = dteMySQLDate2Unix($row->auc_dteAuctionDate);

            $bs->bIncludeOrgLogo         = $row->abs_bIncludeOrgLogo;
            $bs->lLogoImgID              = $row->abs_lLogoImgID;
            $bs->lImageID                = $row->di_lKeyID;
            $bs->strSystemFN             = $row->di_strSystemFN;
            $bs->strSystemThumbFN        = $row->di_strSystemThumbFN;
            $bs->strPath                 = $row->di_strPath;
            $bs->strCaptionTitle         = $row->di_strCaptionTitle;
            if (is_null($bs->lLogoImgID)){
               $bs->strLogoImgTN         = null;
               $bs->strLogoImgLink       = null;
            }else {
               $bs->strLogoImgTN         = strImageHTMLTag(CENUM_CONTEXT_AUCTION, CENUM_IMGDOC_ENTRY_IMAGE,
                                             $lAuctionID, $bs->strSystemThumbFN);
               $bs->strLogoImgLink       = strLinkHTMLTag(CENUM_CONTEXT_AUCTION, CENUM_IMGDOC_ENTRY_IMAGE,
                                             $lAuctionID, $bs->strSystemFN);
            }
            $bs->bIncludeOrgName         = $row->abs_bIncludeOrgName;
            $bs->bIncludeMinBid          = $row->abs_bIncludeMinBid;
            $bs->bIncludeMinBidInc       = $row->abs_bIncludeMinBidInc;
            $bs->bIncludeBuyItNow        = $row->abs_bIncludeBuyItNow;
            $bs->bIncludeReserve         = $row->abs_bIncludeReserve;
            $bs->bIncludeDate            = $row->abs_bIncludeDate;
            $bs->bIncludeFooter          = $row->abs_bIncludeFooter;

            $bs->bIncludePackageName     = $row->abs_bIncludePackageName;
            $bs->bIncludePackageID       = $row->abs_bIncludePackageID;
            $bs->bIncludePackageDesc     = $row->abs_bIncludePackageDesc;
            $bs->bIncludePackageImage    = $row->abs_bIncludePackageImage;
            $bs->bIncludePackageEstValue = $row->abs_bIncludePackageEstValue;
            $bs->bIncludeSignup          = $row->abs_bIncludeSignup;

            $bs->bIncludeItemName        = $row->abs_bIncludeItemName;
            $bs->bIncludeItemID          = $row->abs_bIncludeItemID;
            $bs->bIncludeItemDesc        = $row->abs_bIncludeItemDesc;
            $bs->bIncludeItemImage       = $row->abs_bIncludeItemImage;
            $bs->bIncludeItemDonor       = $row->abs_bIncludeItemDonor;
            $bs->bIncludeItemEstValue    = $row->abs_bIncludeItemEstValue;
            $bs->bIncludeSignup          = $row->abs_bIncludeSignup;

            $bs->bRetired                = $row->abs_bRetired;
            $bs->bReadOnly               = $row->abs_bReadOnly;

            $bs->lOriginID               = $row->abs_lOriginID;
            $bs->lLastUpdateID           = $row->abs_lLastUpdateID;
            $bs->strCFName               = $row->strCFName;
            $bs->strCLName               = $row->strCLName;
            $bs->strLFName               = $row->strLFName;
            $bs->strLLName               = $row->strLLName;
            $bs->dteOrigin               = $row->dteOrigin;
            $bs->dteLastUpdate           = $row->dteLastUpdate;

            ++$idx;
         }
      }
   }

   function addNewBidSheet(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $bs = &$this->bidSheets[0];
      $sqlStr =
          'INSERT INTO gifts_auctions_bidsheets
           SET '.$this->sqlCommonBidSheets().",
              abs_bReadOnly   = 0,
              abs_bRetired    = 0,
              abs_lTemplateID = $bs->lTemplateID,
              abs_lAuctionID  = $bs->lAuctionID,
              abs_lOriginID   = $glUserID,
              abs_dteOrigin   = NOW();";
      $this->db->query($sqlStr);
      return($bs->lKeyID = $this->db->insert_id());
   }

   function updateBidSheet($lBSID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $bs = &$this->bidSheets[0];
      $sqlStr =
          'UPDATE gifts_auctions_bidsheets
           SET '.$this->sqlCommonBidSheets()."
           WHERE abs_lKeyID=$lBSID;";
      $this->db->query($sqlStr);
   }

   function sqlCommonBidSheets(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $bs = &$this->bidSheets[0];

      if (is_null($bs->lLogoImgID)){
         $strImgID = 'NULL';
      }else {
         $strImgID = $bs->lLogoImgID.'';
      }

      $strOut = '';
      for ($idx=1; $idx<=4; ++$idx){
         if (!is_null($bs->signUpCols[$idx]->heading)){
            $strOut .= 'abs_strSignUpCol'    .$idx.' = '.strPrepStr($bs->signUpCols[$idx]->heading).", \n";
            $strOut .= 'abs_lSigunUpColWidth'.$idx.' = '.(int)$bs->signUpCols[$idx]->width.", \n";
         }else {
            $strOut .= 'abs_strSignUpCol'    .$idx.' = null, '."\n";
            $strOut .= 'abs_lSigunUpColWidth'.$idx.' = null, '."\n";
         }
      }
      return($strOut. '
            abs_strSheetName            = '.strPrepStr($bs->strSheetName).',
            abs_strDescription          = '.strPrepStr($bs->strDescription).',
            abs_enumPaperType           = '.strPrepStr($bs->enumPaperType).',
            abs_lNumSignupPages         = '.$bs->lNumSignupPages.',

            abs_lLogoImgID              = '.$strImgID.',
            abs_bIncludeOrgLogo         = '.($bs->bIncludeOrgLogo         ? '1' : '0').',
            abs_bIncludeOrgName         = '.($bs->bIncludeOrgName         ? '1' : '0').',
            abs_bIncludeMinBid          = '.($bs->bIncludeMinBid          ? '1' : '0').',

            abs_bIncludeMinBidInc       = '.($bs->bIncludeMinBidInc       ? '1' : '0').',
            abs_bIncludeBuyItNow        = '.($bs->bIncludeBuyItNow        ? '1' : '0').',
            abs_bIncludeReserve         = '.($bs->bIncludeReserve         ? '1' : '0').',
            abs_bIncludeDate            = '.($bs->bIncludeDate            ? '1' : '0').',
            abs_bIncludeFooter          = '.($bs->bIncludeFooter          ? '1' : '0').',

            abs_bIncludePackageName     = '.($bs->bIncludePackageName     ? '1' : '0').',
            abs_bIncludePackageID       = '.($bs->bIncludePackageID       ? '1' : '0').',
            abs_bIncludePackageDesc     = '.($bs->bIncludePackageDesc     ? '1' : '0').',
            abs_bIncludePackageImage    = '.($bs->bIncludePackageImage    ? '1' : '0').',
            abs_bIncludePackageEstValue = '.($bs->bIncludePackageEstValue ? '1' : '0').',

            abs_bIncludeItemName        = '.($bs->bIncludeItemName        ? '1' : '0').',
            abs_bIncludeItemID          = '.($bs->bIncludeItemID          ? '1' : '0').',
            abs_bIncludeItemDesc        = '.($bs->bIncludeItemDesc        ? '1' : '0').',
            abs_bIncludeItemImage       = '.($bs->bIncludeItemImage       ? '1' : '0').',
            abs_bIncludeItemDonor       = '.($bs->bIncludeItemDonor       ? '1' : '0').',
            abs_bIncludeItemEstValue    = '.($bs->bIncludeItemEstValue    ? '1' : '0').',
            abs_bIncludeSignup          = '.($bs->bIncludeSignup          ? '1' : '0').",

            abs_lLastUpdateID           = $glUserID,
            abs_dteLastUpdate           = NOW() ");

   }

   function strBidSheetTemplateRadio($strRdo, $lMatchID, $lImgHeight=100){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      $strOut .= '<table border="0">
                     <tr>';
      $strOut .= $this->strTemplateRadioCell($strRdo, CENUM_BSTEMPLATE_SIMPLEPACK, $lImgHeight, $lMatchID);
      $strOut .= $this->strTemplateRadioCell($strRdo, CENUM_BSTEMPLATE_PACKAGEPIC, $lImgHeight, $lMatchID);
      $strOut .= $this->strTemplateRadioCell($strRdo, CENUM_BSTEMPLATE_MIN,        $lImgHeight, $lMatchID);
      $strOut .= $this->strTemplateRadioCell($strRdo, CENUM_BSTEMPLATE_ITEMS,      $lImgHeight, $lMatchID);

      $strOut .='
                     </tr>
                  </table>';
      return($strOut);
   }

   private function strTemplateRadioCell($strRdo, $enumTempType, $lImgHeight, $lMatchID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      strXlateTemplate($enumTempType, $tInfo);
      return('
               <td style="vertical-align: top;">
                  <input type="radio" name="'.$strRdo.'" '
                      .($lMatchID==$enumTempType ? 'checked' : '').' value="'.$enumTempType.'">
               </td>
               <td style="vertical-align: top;">'
                  .$tInfo->strLinkImage.$tInfo->strThumbImgLink.'</a><br>'
                  .$tInfo->htmlInfo.'
               </td>');
   }

   function removeBidSheet($lBSID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
          "UPDATE gifts_auctions_bidsheets
           SET
              abs_bRetired      = 1,
              abs_lLastUpdateID = $glUserID
           WHERE abs_lKeyID=$lBSID;";
      $this->db->query($sqlStr);

         // set bidsheet ID to null any auction or package that references this bid sheet
      $sqlStr =
           "UPDATE gifts_auctions_packages
            SET ap_lBidSheetID=null
            WHERE ap_lBidSheetID=$lBSID;";
      $this->db->query($sqlStr);

         // set to null any auction or package that references this bid sheet
      $sqlStr =
           "UPDATE gifts_auctions
            SET auc_lDefaultBidSheet=null
            WHERE auc_lDefaultBidSheet=$lBSID;";
      $this->db->query($sqlStr);

   }

   function strBidSheetListDDL($lAuctionID, $lMatchID, $strDDLName, $bAddBlank, &$lNumBidSheets){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<select name="'.$strDDLName.'">'."\n";
      if ($bAddBlank){
         $strOut .= '<option value="-1">&nbsp;</option>'."\n";
      }
      $sqlStr =
        "SELECT
            abs_lKeyID, abs_lTemplateID, abs_strSheetName
         FROM gifts_auctions_bidsheets
         WHERE NOT abs_bRetired AND abs_lAuctionID=$lAuctionID
         ORDER BY abs_strSheetName, abs_lKeyID;";
      $query = $this->db->query($sqlStr);
      $lNumBidSheets = $query->num_rows();
      if ($lNumBidSheets > 0){
         foreach ($query->result() as $row){
            $lKeyID = $row->abs_lKeyID;
            $lTemplateID = $row->abs_lTemplateID;
            strXlateTemplate($lTemplateID, $tInfo);
            $strOut .= '<option value="'.$lKeyID.'" '.($lMatchID==$lKeyID ? 'selected' : '').'>'
                         .htmlspecialchars($row->abs_strSheetName.' (template "'.$tInfo->title.'")')
                         .'</option>'."\n";
         }
      }

      $strOut .= '</select>'."\n";
      return($strOut);
   }

   function strExtraSheetsDDL($strDDLName, $lMatchID, $bAddBlank){
      $strOut = '<select name="'.$strDDLName.'">'."\n";
      if ($bAddBlank){
         $strOut .= '<option value="-1">&nbsp;</option>'."\n";
      }
      for ($idx = 0; $idx <= 5; ++$idx){
         $strOut .= '<option value="'.$idx.'" '.($idx==$lMatchID ? 'selected' : '').'>'.$idx.'</option>'."\n";
      }
      $strOut .= '</select>'."\n";
      return($strOut);
   }
}