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
      $this->load->model('staff/inventory/minventory',  'cinv');
      $this->load->model('staff/inventory/minv_cico',   'ccico');
  ---------------------------------------------------------------------

---------------------------------------------------------------------*/


class minv_cico extends minventory{

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
   }


      //-------------------------------------------
      //    C H E C K - O U T  /  C H E C K - I N
      //-------------------------------------------
   function bItemCheckedOut($lIItemID, &$lCICO_ID){
   //---------------------------------------------------------------------
   // returns true if item is checked out and not reported lost;
   // also returns CICO ID of check-out record
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT icc_lKeyID
         FROM inv_cico
         WHERE
            icc_lItemID=$lIItemID
            AND icc_dteCheckedIn IS NULL
            AND NOT icc_bRetired
         LIMIT 0,1;";
      $query = $this->db->query($sqlStr);
      $lNumCICO = $query->num_rows();
      if ($lNumCICO == 0) {
         $lCICO_ID = null;
         return(false);
      }else {
         $row = $query->row();
         $lCICO_ID = (int)$row->icc_lKeyID;
         return(true);
      }
   }
/*
   function bItemLost($lIItemID, &$lCICO_ID){
   //---------------------------------------------------------------------
   // returns true if item is checked out and reported lost;
   // also returns CICO ID of check-out record
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT icc_lKeyID
         FROM inv_cico
         WHERE
            icc_lItemID=$lIItemID
            AND icc_dteCheckedIn IS NOT NULL
            AND icc_dteReportedLost IS NOT NULL
            AND NOT icc_bRetired
         LIMIT 0,1;";
      $query = $this->db->query($sqlStr);
      $lNumCICO = $query->num_rows();
      if ($lNumCICO == 0) {
         $lCICO_ID = null;
         return(false);
      }else {
         $row = $query->row();
         $lCICO_ID = (int)$row->icc_lKeyID;
         return(true);
      }
   }
*/
   function itemCICOHistory($lIItemID, $strOrder, $strLimit, &$lNumCICO, &$CICOrecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strWhere = " AND icc_lItemID = $lIItemID ";
      $this->itemCICO_Recs($strWhere, $strOrder, $strLimit, $lNumCICO, $CICOrecs);
   }

   function itemCICOMostRecent($lIItemID, &$lNumCICO, &$CICOrec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strWhere = " AND icc_lItemID = $lIItemID ";
      $this->itemCICO_Recs($strWhere, ' icc_lKeyID DESC ', ' LIMIT 0,1 ', $lNumCICO, $CICOrecs);
      $CICOrec = $CICOrecs[0];
   }

   function itemCICO_Recs($strWhere, $strOrder, $strLimit, &$lNumCICO, &$CICOrecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $CICOrecs = array();
      if ($strOrder == ''){
         $strOrder = ' icc_dteCheckedOut DESC, icc_lKeyID DESC ';
      }

      $sqlStr =
        "SELECT
            icc_lKeyID, icc_lItemID, icc_strCO_Notes, icc_strCI_Notes,
            icc_strCheckedOutTo, icc_strSecurity,
            UNIX_TIMESTAMP(ivi_dteOrigin       ) AS dteOrigin,
            UNIX_TIMESTAMP(ivi_dteLastUpdate   ) AS dteLastUpdate,

            icc_dteCheckedOut, icc_dteCheckedIn,

            ivi_strItemName, ivi_lCategoryID,

            icc_lOriginID, icc_lLastUpdateID,
            uc.us_strFirstName AS strCFName, uc.us_strLastName AS strCLName,
            ul.us_strFirstName AS strLFName, ul.us_strLastName AS strLLName,

            icc_lCheckedOutByID,
            icc_lCheckedInByID,
            uco.us_strFirstName   AS strCheckOutFName,  uco.us_strLastName   AS strCheckOutLName,
            uci.us_strFirstName   AS strCheckInFName,   uci.us_strLastName   AS strCheckInLName

            FROM inv_cico
               INNER JOIN admin_users  AS uc    ON uc.us_lKeyID    = icc_lOriginID
               INNER JOIN admin_users  AS ul    ON ul.us_lKeyID    = icc_lLastUpdateID
               INNER JOIN admin_users  AS uco   ON uco.us_lKeyID   = icc_lCheckedOutByID
               LEFT  JOIN admin_users  AS uci   ON uci.us_lKeyID   = icc_lCheckedInByID

               INNER JOIN inv_items             ON icc_lItemID     = ivi_lKeyID

            WHERE NOT icc_bRetired $strWhere
            ORDER BY $strOrder
            $strLimit;";

      $query = $this->db->query($sqlStr);
      $lNumCICO = $query->num_rows();
      if ($lNumCICO == 0) {
         $CICOrecs[0] = new stdClass;
         $CICO = &$CICOrecs[0];

         $CICO->lKeyID            = null;
         $CICO->lItemID           =
         $CICO->strCO_Notes       =
         $CICO->strCI_Notes       =
         $CICO->strCheckedOutTo   =
         $CICO->strSecurity       =
         $CICO->dteCheckedOut     =
         $CICO->dteCheckedIn      =
         $CICO->dteReportedLost   =
         $CICO->dteOrigin         =
         $CICO->dteLastUpdate     =
         $CICO->strItemName       =
         $CICO->lCategoryID       =
         $CICO->lOriginID         =
         $CICO->lLastUpdateID     =
         $CICO->strCFName         =
         $CICO->strCLName         =
         $CICO->strLFName         =
         $CICO->strLLName         =
         $CICO->lCheckedOutByID   =
         $CICO->lCheckedInByID    =
         $CICO->lFlaggedLostByID  =
         $CICO->strCheckOutFName  =
         $CICO->strCheckOutLName  =
         $CICO->strCheckInFName   =
         $CICO->strCheckInLName   =
         $CICO->strSetAsLostFName =
         $CICO->strSetAsLostLName = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $CICOrecs[$idx] = new stdClass;
            $CICO = &$CICOrecs[$idx];

            $CICO->lKeyID            = (int)$row->icc_lKeyID;
            $CICO->lItemID           = (int)$row->icc_lItemID;
            $CICO->strCO_Notes       = $row->icc_strCO_Notes;
            $CICO->strCI_Notes       = $row->icc_strCI_Notes;
            $CICO->strCheckedOutTo   = $row->icc_strCheckedOutTo;
            $CICO->strSecurity       = $row->icc_strSecurity;
            $CICO->dteCheckedOut     = dteMySQLDate2Unix($row->icc_dteCheckedOut);
            $CICO->dteCheckedIn      = dteMySQLDate2Unix($row->icc_dteCheckedIn);
            $CICO->dteOrigin         = $row->dteOrigin;
            $CICO->dteLastUpdate     = $row->dteLastUpdate;
            $CICO->strItemName       = $row->ivi_strItemName;
            $CICO->lCategoryID       = (int)$row->ivi_lCategoryID;
            $CICO->lOriginID         = (int)$row->icc_lOriginID;
            $CICO->lLastUpdateID     = (int)$row->icc_lLastUpdateID;
            $CICO->strCFName         = $row->strCFName;
            $CICO->strCLName         = $row->strCLName;
            $CICO->strLFName         = $row->strLFName;
            $CICO->strLLName         = $row->strLLName;
            $CICO->lCheckedOutByID   = (int)$row->icc_lCheckedOutByID;
            $CICO->lCheckedInByID    = $row->icc_lCheckedInByID;
            $CICO->strCheckOutFName  = $row->strCheckOutFName;
            $CICO->strCheckOutLName  = $row->strCheckOutLName;
            $CICO->strCheckInFName   = $row->strCheckInFName;
            $CICO->strCheckInLName   = $row->strCheckInLName;

            ++$idx;
         }
      }
   }

   function lAddCheckOutRec($CICOrec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        'INSERT INTO inv_cico
         SET
            icc_lItemID          = '.$CICOrec->lItemID.',
            icc_strCO_Notes      ='.strPrepStr($CICOrec->strCO_Notes).',
            icc_strCI_Notes      = \'\',
            icc_strCheckedOutTo  = '.strPrepStr($CICOrec->strCheckedOutTo).',
            icc_strSecurity      = '.strPrepStr($CICOrec->strSecurity).',
            icc_dteCheckedOut    = '.strPrepStr($CICOrec->mdteCheckedOut).",
            icc_dteCheckedIn     = NULL,
            icc_lCheckedOutByID  = $glUserID,
            icc_lCheckedInByID   = NULL,
            icc_bRetired         = 0,
            icc_lOriginID        = $glUserID,
            icc_lLastUpdateID    = $glUserID,
            icc_dteOrigin        = NOW(),
            icc_dteLastUpdate    = NOW();";
      $query = $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function addCheckInRec($CICOrec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
        'UPDATE inv_cico
         SET
            icc_strCI_Notes      ='.strPrepStr($CICOrec->strCI_Notes).',
            icc_dteCheckedIn     ='.strPrepStr($CICOrec->mdteCheckedIn).",
            icc_lCheckedInByID   = $glUserID,
            icc_lLastUpdateID    = $glUserID,
            icc_dteLastUpdate    = NOW()
         WHERE icc_lKeyID=$CICOrec->lKeyID;";
      $query = $this->db->query($sqlStr);
   }

   function strItemOrder($enumSort){
   //---------------------------------------------------------------------
   // enumSort:
   //     ''     - default sort (cat)
   //     itemid - item ID
   //     item   - item name
   //     date   - checkout date
   //     cat    - by category
   //     name   - "checked-out to" name
   //---------------------------------------------------------------------
      switch ($enumSort){
         case 'itemid':
            $strOrder = ' ivi_lKeyID ';
            break;

         case 'item':
            $strOrder = ' ivi_strItemName, tmp_strFullCatName, ivi_lKeyID ';
            break;

         case 'date':
            $strOrder = ' icc_dteCheckedOut, tmp_strFullCatName, ivi_strItemName, ivi_lKeyID ';
            break;

         case 'name':
            $strOrder = ' icc_strCheckedOutTo, tmp_strFullCatName, ivi_strItemName, ivi_lKeyID ';
            break;

         case 'cat':
         default:
            $strOrder = ' tmp_strFullCatName, ivi_strItemName, ivi_lKeyID ';
            break;
      }
      return($strOrder);
   }

   function loadCheckedOutItems($enumSort, $strTmpTable, &$lNumItems, &$items){
   //---------------------------------------------------------------------
   // caller must previously called
   //    $this->buildTempCatTable($strTmpTable, &$icats)   //
   //---------------------------------------------------------------------
      $items = array();
      $strOrder = $this->strItemOrder($enumSort);
      $sqlStr =
        "SELECT
            MAX(icc_lKeyID) AS lKeyID,
            icc_strCO_Notes,
            icc_lItemID, icc_strCheckedOutTo, icc_strSecurity,
            icc_dteCheckedOut, ivi_dteRemInventory, ivi_dteReportedLost,

            ivi_strItemName, ivi_strItemSNa, ivi_strItemSNb, ivi_strRParty,
            ivi_bAvailForLoan,
            ivi_lCategoryID,
            tmp_strFullCatName

         FROM inv_cico
            INNER JOIN inv_items    ON icc_lItemID=ivi_lKeyID
            INNER JOIN inv_cats     ON ivi_lCategoryID=ivc_lKeyID
            INNER JOIN $strTmpTable ON tmp_lICatID=ivc_lKeyID

         WHERE NOT icc_bRetired
            AND icc_dteCheckedOut IS NOT NULL
            AND icc_dteCheckedIn IS NULL
         GROUP BY icc_lItemID
         ORDER BY $strOrder;";

      $query = $this->db->query($sqlStr);
      $lNumItems = $query->num_rows();
      if ($lNumItems > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $items[$idx] = new stdClass;
            $item = &$items[$idx];

            $item->lCICO_ID        = (int)$row->lKeyID;

            $item->strCO_Notes     = $row->icc_strCO_Notes;
            $item->lItemID         = (int)$row->icc_lItemID;
            $item->strCheckedOutTo = $row->icc_strCheckedOutTo;
            $item->strSecurity     = $row->icc_strSecurity;
            $item->dteCheckedOut   = dteMySQLDate2Unix($row->icc_dteCheckedOut);
            $item->strItemName     = $row->ivi_strItemName;
            $item->strItemSNa      = $row->ivi_strItemSNa;
            $item->strItemSNb      = $row->ivi_strItemSNb;
            $item->strRParty       = $row->ivi_strRParty;

            $item->dteRemInventory = dteMySQLDate2Unix($row->ivi_dteRemInventory);
            $item->dteReportedLost = dteMySQLDate2Unix($row->ivi_dteReportedLost);
            $item->lCategoryID     = (int)$row->ivi_lCategoryID;
            $item->strFullCatName  = $row->tmp_strFullCatName;

            $item->properties = new stdClass;
            $item->properties->bAvail          = (bool)$row->ivi_bAvailForLoan;
            $item->properties->bLost           = !is_null($item->dteReportedLost);
            $item->properties->bCheckedOut     = true;
            $item->properties->strCO_To        = $item->strCheckedOutTo;
            $item->properties->dteCO           = $item->dteCheckedOut;
            $item->properties->bRemovedFromInv = !is_null($item->dteRemInventory);
            $item->strStatus = cico\strItemStatus($item->properties);

            ++$idx;
         }
      }
   }

   function loadAllItems($lStartRec, $lRecsPerPage, $enumSort, $strTmpTable, &$lNumItems, &$items){
   //---------------------------------------------------------------------
   // caller must previously called
   //    $this->buildTempCatTable($strTmpTable, &$icats)   //
   //---------------------------------------------------------------------
      $items = array();
      $strOrder = $this->strItemOrder($enumSort);
      $strLimit = "LIMIT $lStartRec, $lRecsPerPage ";
      $sqlStr =
        "SELECT
            ivi_lKeyID,
            ivi_strItemName, ivi_strItemSNa, ivi_strItemSNb, ivi_strRParty,
            ivi_bAvailForLoan,
            ivi_dteRemInventory, ivi_dteReportedLost,
            ivi_lCategoryID,
            tmp_strFullCatName

         FROM inv_items
            INNER JOIN inv_cats     ON ivi_lCategoryID=ivc_lKeyID
            INNER JOIN $strTmpTable ON tmp_lICatID=ivc_lKeyID
         WHERE NOT ivi_bRetired
         ORDER BY $strOrder
         $strLimit;";

      $query = $this->db->query($sqlStr);
      $lNumItems = $query->num_rows();
      if ($lNumItems > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $items[$idx] = new stdClass;
            $item = &$items[$idx];

            $item->lItemID         = $lIItemID = (int)$row->ivi_lKeyID;
            $item->strItemName     = $row->ivi_strItemName;
            $item->strItemSNa      = $row->ivi_strItemSNa;
            $item->strItemSNb      = $row->ivi_strItemSNb;
            $item->strRParty       = $row->ivi_strRParty;

            $item->dteRemInventory = dteMySQLDate2Unix($row->ivi_dteRemInventory);
            $item->dteReportedLost = dteMySQLDate2Unix($row->ivi_dteReportedLost);
            $item->lCategoryID     = (int)$row->ivi_lCategoryID;
            $item->strFullCatName  = $row->tmp_strFullCatName;

            $item->properties = new stdClass;
            $item->properties->bAvail          = (bool)$row->ivi_bAvailForLoan;
            $item->properties->bLost           = !is_null($item->dteReportedLost);
            $item->properties->bCheckedOut     = $this->bItemCheckedOutInfo(
                                                    $lIItemID, $item->properties->strCO_To,
                                                    $cicoID, $item->properties->dteCO);
            $item->properties->bRemovedFromInv = !is_null($item->dteRemInventory);
            $item->strStatus = cico\strItemStatus($item->properties);

            ++$idx;
         }
      }
   }


}
