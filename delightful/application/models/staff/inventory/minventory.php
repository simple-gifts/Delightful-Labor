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
      $this->load->model('staff/inventory/minventory',   'cinv');
  ---------------------------------------------------------------------

---------------------------------------------------------------------*/


class minventory extends CI_Model{
   public $lRunAway, $strCatDDL;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
      $this->lRunAway = 0;
      $this->strCatDDL = '';
		parent::__construct();
   }

      //----------------------------
      //    C A T E G O R I E S
      //----------------------------

   function loadInventoryCategories(&$icats, $props){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumICats = 0;
      $icats = array();
      $this->loadCatViaParentID(' AND ivc_lParentID IS NULL ', $lNumICats, $icats, $props);
   }

   function loadCatViaParentID($sqlWhere, &$lNumICats, &$icats, &$props){
   //---------------------------------------------------------------------
   /*
       $props->bCountItems
       $props->bLostOnly
       $props->bRemInvOnly
   */
   //---------------------------------------------------------------------
      ++$this->lRunAway;
      if ($this->lRunAway > 100){
         screamForHelp('Runaway Train!<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }
      $sqlStr =
        "SELECT
            ivc_lKeyID, ivc_strCatName, ivc_strNotes, ivc_lParentID,
            ivc_lOriginID, ivc_lLastUpdateID, ivc_dteOrigin, ivc_dteLastUpdate
         FROM inv_cats
         WHERE NOT ivc_bRetired
            $sqlWhere
         GROUP BY ivc_lKeyID
         ORDER BY ivc_strCatName, ivc_lKeyID;";
      $query = $this->db->query($sqlStr);
      $lNumICats = $query->num_rows();
      if ($lNumICats > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $icats[$idx] = new stdClass;
            $icat = &$icats[$idx];
            $icat->lKeyID     = $lKeyID = (int)$row->ivc_lKeyID;
            $icat->strCatName = $row->ivc_strCatName;
            $icat->strNotes   = $row->ivc_strNotes;
            $icat->lParentID  = $lParentID = (int)$row->ivc_lParentID;
            if ($props->bCountItems) $icat->lNumItems  = $this->lNumItemsViaCatID($lKeyID, $props->bLostOnly, $props->bRemInvOnly);
            $icat->children   = array();
            $this->loadCatViaParentID(" AND ivc_lParentID=$lKeyID ", $icat->lNumChildren, $icat->children, $props);

            ++$idx;
         }
      }
      --$this->lRunAway;
   }

   function loadSingleInventoryCategories($lICatID, &$cat){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            ivc_lKeyID, ivc_strCatName, ivc_strNotes, ivc_lParentID,
            ivc_lOriginID, ivc_lLastUpdateID, ivc_dteOrigin, ivc_dteLastUpdate
         FROM inv_cats
            LEFT JOIN inv_items ON ivi_lCategoryID=ivc_lKeyID
         WHERE NOT ivc_bRetired
            AND ivc_lKeyID=$lICatID
         ORDER BY ivc_strCatName, ivc_lKeyID;";
      $query = $this->db->query($sqlStr);
      $lNumICats = $query->num_rows();
      if ($lNumICats == 0){
         $cat = null;
      }else {
         $row = $query->row();
         $cat = new stdClass;
         $cat->lKeyID     = $lKeyID = (int)$row->ivc_lKeyID;
         $cat->strCatName = $row->ivc_strCatName;
         $cat->strNotes   = $row->ivc_strNotes;
         $cat->lParentID  = $lParentID = (int)$row->ivc_lParentID;
      }
   }

   function findICatViaICatID($lICatID, $icats){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (count($icats)==0) return(null);
      if ($lICatID <= 0) return(null);
      return($this->findICatRecurse($lICatID, $icats));
   }

   function findICatRecurse($lICatID, &$icats){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      ++$this->lRunAway;
      if ($this->lRunAway > 2000){
         screamForHelp('Runaway Train!<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }
      foreach ($icats as $icat){
         if ($icat->lKeyID == $lICatID){
            return($icat);
         }else {
            if ($icat->lNumChildren > 0){
               $hold = $this->findICatRecurse($lICatID, $icat->children);
               if (!is_null($hold)) return($hold);
            }
         }
      }
      return(null);
   }

   function strDDLICats($lMatchID, $lExcludeID, &$icats, $strIndent){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (count($icats)==0) return;
      $bTopLevel = $strIndent == '';
      foreach ($icats as $icat){
         if ($icat->lKeyID==$lExcludeID){
            $this->strCatDDL .= '<option value="-999" style="color:gray;">'
                                 .$strIndent.htmlspecialchars($icat->strCatName).'</option>'."\n";
         }else {
            $this->strCatDDL .=
                    '<option
                        value="'.$icat->lKeyID.'" '.($icat->lKeyID==$lMatchID ? 'selected' : '')
                        .($bTopLevel ? ' style="color: #220000; background-color: #ccc;" ' : '')
                        .'>'
                        .$strIndent.htmlspecialchars($icat->strCatName).'</option>'."\n";
         }
         $this->strDDLICats($lMatchID, $lExcludeID, $icat->children, ($bTopLevel ? '&nbsp;&nbsp;' : '')
                                .$strIndent.'|&mdash; ');
      }
   }

   function buildTempCatTable($strTmpTable, &$icats){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

      $sqlStr = "DROP TABLE IF EXISTS $strTmpTable;";
      $this->db->query($sqlStr);

      $sqlStr =
        "-- CREATE TEMPORARY TABLE IF NOT EXISTS $strTmpTable (
         CREATE TABLE IF NOT EXISTS $strTmpTable (
           tmp_lKeyID         int(11) NOT NULL AUTO_INCREMENT,
           tmp_lICatID        int(11) NOT NULL DEFAULT '0' COMMENT 'foreign key to table inv_cats',
           tmp_strFullCatName varchar(1024) NOT NULL DEFAULT '',
           PRIMARY KEY            (tmp_lKeyID),
           KEY tmp_lICatID        (tmp_lICatID),
           KEY tmp_strFullCatName (tmp_strFullCatName)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Expanded Inventory Category List' AUTO_INCREMENT=1;";
      $this->db->query($sqlStr);

      $strFullPath = '(root)';
      $this->addTempCatRow($strTmpTable, $icats, $strFullPath);

/*
      if (count($icats) > 0){
         $strFullPath = '(root)';
         foreach ($icats as $icat){
            $this->addTempCatRow($strTmpTable, $icat, $strFullPath);
         }
      }

// -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$icats   <pre>');
echo(htmlspecialchars( print_r($icats, true))); echo('</pre></font><br>');
// -------------------------------------
*/


   }

   function addTempCatRow(&$strTmpTable, &$icats, $strFullPath){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (count($icats)==0) return;

      foreach ($icats as $icat){
         $strTempPath = $strFullPath.' | '.$icat->strCatName;
         $sqlStr =
            "INSERT INTO $strTmpTable
             SET
                tmp_lICatID=$icat->lKeyID,
                tmp_strFullCatName = ".strPrepStr($strTempPath).';';
         $this->db->query($sqlStr);
/*
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$strFullPath = $strTempPath <br></font>\n");
*/
         $this->addTempCatRow($strTmpTable, $icat->children, $strFullPath.' | '.$icat->strCatName);
      }

   }


   function addNewICat($cat){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
        'INSERT INTO inv_cats
         SET '.$this->sqlCommonICat($cat).",
            ivc_bRetired  = 0,
            ivc_lOriginID = $glUserID,
            ivc_dteOrigin = NOW();";
      $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function updateICat($cat){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
        'UPDATE inv_cats
         SET '.$this->sqlCommonICat($cat)."
         WHERE ivc_lKeyID=$cat->lKeyID;";
      $this->db->query($sqlStr);
   }

   function sqlCommonICat($cat){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      if ($cat->lParentID <= 0){
         $strParentID = 'null';
      }else {
         $strParentID = $cat->lParentID.'';
      }
      return(
         'ivc_strCatName    = '.strPrepStr($cat->strCatName).',
          ivc_strNotes      = '.strPrepStr($cat->strNotes).",
          ivc_lParentID     = $strParentID,
          ivc_lLastUpdateID = NOW(),
          ivc_dteLastUpdate = $glUserID ");
   }

   function removeCategory($lCatID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
          "UPDATE inv_cats
           SET
              ivc_bRetired  = 1,
              ivc_lOriginID = $glUserID,
              ivc_dteOrigin = NOW()
           WHERE ivc_lKeyID=$lCatID;";
      $this->db->query($sqlStr);
   }

   function icatBreadCrumbs(&$strBreadCrumb, $lICatID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      ++$this->lRunAway;
      if ($this->lRunAway > 100){
         screamForHelp('Runaway Train!<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }
      $sqlStr =
         "SELECT ivc_strCatName, ivc_lParentID
          FROM inv_cats
          WHERE ivc_lKeyID=$lICatID;";
      $query = $this->db->query($sqlStr);
      $lNumICats = $query->num_rows();
      if ($lNumICats == 0){
         return;
      }else {
         $row = $query->row();
         if ($strBreadCrumb == ''){
            $strBetween = '';
         }else {
            $strBetween = ' | ';
         }
         $strBreadCrumb = $row->ivc_strCatName . $strBetween . $strBreadCrumb;
         if (!is_null($row->ivc_lParentID)){
            $this->icatBreadCrumbs($strBreadCrumb, (int)$row->ivc_lParentID);
         }else {
            $strBreadCrumb = '(root)' . $strBetween . $strBreadCrumb;
         }
      }
   }


      //----------------------------
      //    I T E M S
      //----------------------------
   function lNumItemsViaCatID($lCatID, $bLostOnly, $bRemInvOnly){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strWhere = '';
      if ($bLostOnly){
         $strWhere .= ' AND ivi_dteReportedLost IS NOT NULL ';
      }
      if ($bRemInvOnly){
         $strWhere .= ' AND ivi_dteRemInventory IS NOT NULL ';
      }else {
         $strWhere .= ' AND ivi_dteRemInventory IS NULL ';
      }
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM inv_items
         WHERE
            ivi_lCategoryID=$lCatID
            AND NOT ivi_bRetired $strWhere;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }

   function loadSingleInventoryItem($lIItemID, &$lNumItems, &$items, $bLoadStatus=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlWhere = " AND ivi_lKeyID=$lIItemID ";
      $this->loadIItems($sqlWhere, $lNumItems, $items, $bLoadStatus);
   }

   function loadInventoryItemsViaCatID($lICatID, &$lNumItems, &$items,
                 $bLostOnly=false, $bRemInvOnly=false, $bLoadStatus=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlWhere = " AND ivi_lCategoryID=$lICatID ";
      if ($bLostOnly) $sqlWhere   .= ' AND ivi_dteReportedLost IS NOT NULL ';
      if ($bRemInvOnly) $sqlWhere .= ' AND ivi_dteRemInventory IS NOT NULL ';
      if (!$bLostOnly && !$bRemInvOnly) $sqlWhere .= ' AND ivi_dteRemInventory IS NULL ';
      $this->loadIItems($sqlWhere, $lNumItems, $items, $bLoadStatus);
   }

   function lCountItems($sqlWhereExtra){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT COUNT(*) AS lNumRecs
          FROM inv_items
          WHERE NOT ivi_bRetired $sqlWhereExtra;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }

   function loadIItems($sqlWhere, &$lNumItems, &$items, $bLoadStatus=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsACO = new madmin_aco();
      $items = array();

      $sqlStr =
        "SELECT
            ivi_lKeyID, ivi_strItemName, ivi_strItemSNa, ivi_strItemSNb,
            ivi_strLocation, ivi_strDescription, ivi_lCategoryID,
            ivi_strRParty,
            ivi_dteObtained, ivi_dteRemInventory, ivi_dteReportedLost,
            ivi_bAvailForLoan,
            ivi_strLostNotes,
            ivi_lFlaggedLostByID, ivi_lRemInventoryByID,

            ivc_strCatName,
            ivi_lOriginID, ivi_lLastUpdateID,
            UNIX_TIMESTAMP(ivi_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(ivi_dteLastUpdate) AS dteLastUpdate,

            ivi_curEstValue, ivi_lACOID, aco_strFlag, aco_strCurrencySymbol, aco_strName,

            uc.us_strFirstName    AS strCFName,      uc.us_strLastName    AS strCLName,
            ul.us_strFirstName    AS strLFName,      ul.us_strLastName    AS strLLName,
            ur.us_strFirstName    AS strRemInvFName, ur.us_strLastName    AS strRemInvLName,
            ulost.us_strFirstName AS strLostFName,   ulost.us_strLastName AS strLostLName

         FROM inv_items
               INNER JOIN admin_users  AS uc    ON uc.us_lKeyID    = ivi_lOriginID
               INNER JOIN admin_users  AS ul    ON ul.us_lKeyID    = ivi_lLastUpdateID
               LEFT  JOIN admin_users  AS ur    ON ur.us_lKeyID    = ivi_lRemInventoryByID
               LEFT  JOIN admin_users  AS ulost ON ulost.us_lKeyID = ivi_lFlaggedLostByID
               INNER JOIN inv_cats              ON ivc_lKeyID      = ivi_lCategoryID
               INNER JOIN admin_aco             ON ivi_lACOID      = aco_lKeyID

         WHERE NOT ivi_bRetired $sqlWhere
         ORDER BY ivi_strItemName, ivi_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumItems = $query->num_rows();
      if ($lNumItems == 0) {
         $items[0] = new stdClass;
         $item = &$items[0];

         $item->lKeyID            = null;
         $item->strItemName       =
         $item->strItemSNa        =
         $item->strItemSNb        =
         $item->strLocation       =
         $item->strDescription    =
         $item->lCategoryID       =
         $item->strCatName        =
         $item->dteObtained       =
         $item->strRParty         = null;

         $item->bAvailForLoan     =
         $item->dteRemInventory   =
         $item->lRemInventoryByID =
         $item->strRemInvFName    =
         $item->strRemInvLName    = null;

         $item->dteReportedLost   =
         $item->bLost             =
         $item->strLostNotes      =
         $item->lFlaggedLostByID  =
         $item->strLostFName      =
         $item->strLostLName      = null;

         $item->curEstValue       =
         $item->lACOID            =
         $item->strFlagImg        =
         $item->strACOCurSymbol   =
         $item->strACOCountry     =
         $item->strFormattedAmnt  = null;

         $item->lOriginID         =
         $item->lLastUpdateID     =
         $item->ucstrFName        =
         $item->ucstrLName        =
         $item->ulstrFName        =
         $item->ulstrLName        =
         $item->dteOrigin         =
         $item->dteLastUpdate     = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $items[$idx] = new stdClass;
            $item = &$items[$idx];

            $item->lKeyID            = $lIItemID = (int)$row->ivi_lKeyID;
            $item->strItemName       = $row->ivi_strItemName;
            $item->strItemSNa        = $row->ivi_strItemSNa;
            $item->strItemSNb        = $row->ivi_strItemSNb;
            $item->strLocation       = $row->ivi_strLocation;
            $item->strDescription    = $row->ivi_strDescription;
            $item->lCategoryID       = (int)$row->ivi_lCategoryID;
            $item->strCatName        = $row->ivc_strCatName;
            $item->dteObtained       = dteMySQLDate2Unix($row->ivi_dteObtained);
            $item->strRParty         = $row->ivi_strRParty;

            $item->bAvailForLoan     = (bool)$row->ivi_bAvailForLoan;
            $item->dteRemInventory   = dteMySQLDate2Unix($row->ivi_dteRemInventory);
            $item->bRemoved          = !is_null($item->dteRemInventory);
            $item->lRemInventoryByID = $row->ivi_lRemInventoryByID;
            $item->strRemInvFName    = $row->strRemInvFName;
            $item->strRemInvLName    = $row->strRemInvLName;

            $item->dteReportedLost   = dteMySQLDate2Unix($row->ivi_dteReportedLost);
            $item->bLost             = !is_null($item->dteReportedLost);
            $item->strLostNotes      = $row->ivi_strLostNotes;
            $item->lFlaggedLostByID  = $row->ivi_lFlaggedLostByID;
            $item->strLostFName      = $row->strLostFName;
            $item->strLostLName      = $row->strLostLName;

            $item->curEstValue       = (float)$row->ivi_curEstValue;
            $item->lACOID            = (int)$row->ivi_lACOID;
            $item->strFlagImg        = $clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);
            $item->strACOCurSymbol   = $row->aco_strCurrencySymbol;
            $item->strACOCountry     = $row->aco_strName;
            $item->strFormattedAmnt  = $item->strACOCurSymbol.' '
                                      .number_format($item->curEstValue, 2).' '
                                      .$item->strFlagImg;

            $item->lOriginID         = (int)$row->ivi_lOriginID;
            $item->lLastUpdateID     = (int)$row->ivi_lLastUpdateID;
            $item->ucstrFName        = $row->strCFName;
            $item->ucstrLName        = $row->strCLName;
            $item->ulstrFName        = $row->strLFName;
            $item->ulstrLName        = $row->strLLName;
            $item->dteOrigin         = (int)$row->dteOrigin;
            $item->dteLastUpdate     = (int)$row->dteLastUpdate;

            if ($bLoadStatus){
               $item->statProps = new stdClass;
               $item->statProps->bAvail          = $item->bAvailForLoan;
               $item->statProps->bLost           = $item->bLost;
               $item->statProps->bCheckedOut     = $this->bItemCheckedOutInfo($lIItemID, $item->statProps->strCO_To,
                                                        $item->statProps->cicoID, $item->statProps->dteCO);
               $item->statProps->bRemovedFromInv = $item->bRemoved;

               $item->strStatus = cico\strItemStatus($item->statProps);
            }
            ++$idx;
         }
      }
   }

   function bItemCheckedOutInfo($lIItemID, &$strCO_To, &$cicoID, &$dteCO){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strCO_To = $cicoID = $dteCO = null;
      $sqlStr =
         "SELECT
             icc_lKeyID, icc_strCheckedOutTo,
             icc_dteCheckedOut,
             icc_dteCheckedIn
         FROM inv_cico
         WHERE NOT icc_bRetired
            AND icc_lItemID=$lIItemID
         ORDER BY icc_lKeyID DESC
         LIMIT 0,1;";
      $query = $this->db->query($sqlStr);
      $lNumItems = $query->num_rows();
      if ($lNumItems == 0) {
         return(false);
      }else {
         $row = $query->row();
         if (is_null($row->icc_dteCheckedOut) || !is_null($row->icc_dteCheckedIn)){
            return(false);
         }else {
            $strCO_To = $row->icc_strCheckedOutTo;
            $cicoID   = (int)$row->icc_lKeyID;
            $dteCO    = dteMySQLDate2Unix($row->icc_dteCheckedOut);
            return(true);
         }
      }
   }

   function lAddNewIItem($item){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        'INSERT INTO inv_items
         SET '.$this->sqlCommonItems($item).",
            ivi_lCategoryID = $item->lCategoryID,
            ivi_bRetired    = 0,
            ivi_lOriginID   = $glUserID,
            ivi_dteOrigin   = NOW();";
      $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function updateIItem($lIItemID, $item){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'UPDATE inv_items
         SET '.$this->sqlCommonItems($item)."
         WHERE ivi_lKeyID=$lIItemID";
      $this->db->query($sqlStr);
   }

   function sqlCommonItems($item){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      return(
           'ivi_strItemName    = '.strPrepStr($item->strItemName).',
            ivi_strItemSNa     = '.strPrepStr($item->strItemSNa).',
            ivi_strItemSNb     = '.strPrepStr($item->strItemSNb).',
            ivi_strRParty      = '.strPrepStr($item->strRParty).',
            ivi_curEstValue    = '.number_format($item->curEstValue, 2, '.', '').',
            ivi_lACOID         = '.(int)($item->lACOID).',
            ivi_bAvailForLoan  = '.($item->bAvailForLoan ? '1' : '0').',
            ivi_strLocation    = '.strPrepStr($item->strLocation).',
            ivi_strDescription = '.strPrepStr($item->strDescription).',
            ivi_dteObtained    = '.strPrepStr($item->mdteObtained).",
            ivi_lLastUpdateID  = $glUserID,
            ivi_dteLastUpdate  = NOW() ");
   }

   function changeItemCategory($lIItemID, $lCatID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      if ($lCatID <= 0) return;
      $sqlStr =
         "UPDATE inv_items
          SET
            ivi_lCategoryID    = $lCatID,
            ivi_lLastUpdateID  = $glUserID,
            ivi_dteLastUpdate  = NOW()
          WHERE ivi_lKeyID=$lIItemID;";
      $this->db->query($sqlStr);
   }

   function removeIItem($lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // remove history
      $sqlStr = "DELETE FROM inv_history WHERE ih_lItemID=$lIItemID;";
      $this->db->query($sqlStr);

         // remove cico
      $sqlStr = "DELETE FROM inv_cico WHERE icc_lItemID=$lIItemID;";
      $this->db->query($sqlStr);

         // remove item
      $sqlStr = "DELETE FROM inv_items WHERE ivi_lKeyID=$lIItemID;";
      $this->db->query($sqlStr);
   }

   function markLostFound($lIItemID, $bLost, $strNotes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
         'UPDATE inv_items
          SET
             ivi_dteReportedLost  = '.($bLost ? 'NOW()' : 'NULL').',
             ivi_strLostNotes     = '.strPrepStr($strNotes).",
             ivi_lFlaggedLostByID = $glUserID,
             ivi_lLastUpdateID    = $glUserID
          WHERE ivi_lKeyID=$lIItemID;";
      $this->db->query($sqlStr);
   }

   function markRemoveRestore($lIItemID, $bRemove){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
         'UPDATE inv_items
          SET
             ivi_dteRemInventory   = '.($bRemove ? 'NOW()' : 'NULL').",
             ivi_lRemInventoryByID = $glUserID,
             ivi_lLastUpdateID     = $glUserID
          WHERE ivi_lKeyID=$lIItemID;";
      $this->db->query($sqlStr);
   }

   public function strIItemHTMLSummary($item){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $lIItemID = $item->lKeyID;

      $strOut .= $clsRpt->openReport('', '');
      $strOut .=
           $clsRpt->openRow   (false)
          .$clsRpt->writeLabel('Item Name:')
          .$clsRpt->writeCell (
                          strLinkView_InventoryItem($lIItemID, 'View item', true)
                         .htmlspecialchars($item->strItemName).'&nbsp;&nbsp;(item ID: '
                         .str_pad($lIItemID, 5, '0', STR_PAD_LEFT).')'
                         )
          .$clsRpt->closeRow  ();

      $strOut .=
           $clsRpt->openRow   (false)
          .$clsRpt->writeLabel('Category:')
          .$clsRpt->writeCell (htmlspecialchars($item->strCatBreadCrumb))
          .$clsRpt->closeRow  ();

      $strOut .=
           $clsRpt->openRow   (false)
          .$clsRpt->writeLabel('Responsible Party:')
          .$clsRpt->writeCell (htmlspecialchars($item->strRParty))
          .$clsRpt->closeRow  ();

      $strOut .=
         $clsRpt->closeReport('<br>');

      return($strOut);
   }


      //----------------------------
      //    H I S T O R Y
      //----------------------------
   function lAddItemHistoryRec($lIItemID, $lCICO_ID, $enumOperation){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      if (is_null($lCICO_ID)) $lCICO_ID = 'null';
      $sqlStr =
        'INSERT INTO inv_history
         SET
           lh_enumOperation = '.strPrepStr($enumOperation).",
           ih_lItemID       = $lIItemID,
           ih_lCICOID       = $lCICO_ID,
           ih_lOriginID     = $glUserID;";
      $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function loadItemHistory($lIItemID, &$lNumHRecs, &$histRecs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $histRecs = array();
      $sqlStr =
        "SELECT
            ih_lKeyID, ih_lItemID, ih_lCICOID, lh_enumOperation, ih_lOriginID,
            ih_dteLastUpdate,
            uc.us_strFirstName AS strCFName,
            uc.us_strLastName  AS strCLName
         FROM inv_history
               INNER JOIN admin_users  AS uc    ON uc.us_lKeyID = ih_lOriginID
         WHERE ih_lItemID=$lIItemID
         ORDER BY ih_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumHRecs = $query->num_rows();
      if ($lNumHRecs > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $histRecs[$idx] = new stdClass;
            $histRec = &$histRecs[$idx];

            $histRec->lKeyID        = (int)$row->ih_lKeyID;
            $histRec->lItemID       = (int)$row->ih_lItemID;
            $histRec->lCICOID       = $row->ih_lCICOID;
            $histRec->enumOperation = $row->lh_enumOperation;
            $histRec->lOriginID     = (int)$row->ih_lOriginID;
            $histRec->dteAction     = dteMySQLDate2Unix($row->ih_dteLastUpdate);
            $histRec->strCFName     = $row->strCFName;
            $histRec->strCLName     = $row->strCLName;

            ++$idx;
         }
      }
   }

}
