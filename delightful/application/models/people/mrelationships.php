<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2011-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
//
//---------------------------------------------------------------------
//
//
---------------------------------------------------------------------
   $this->load->model('people/mrelationships', 'clsRel');
---------------------------------------------------------------------

//-----------------------------------------------------------------------

 methods:
   clearRelVars               ()
   loadFromRelViaPID          ()
   loadToRelViaPID            ()
   removeRelViaRelID          ()

   addRelType                 ()
   removeRelTypeViaRelTypeID  ()
   relTypeInfoViaRelTypeID    ()
   strPeopleRelationshipsDDL  ($bShowBlank, $lMatchID)

   loadRelationships          ($bSortCat, $bLoadRetired, $bViaItemID, $lRelItemID)
-----------------------------------------------------------------------*/
class mrelationships extends CI_Model{

      // relationship IDs
   var $lPID, $lRelID;

      // relationship types
   var $lRelTypeID,           $strRelType,        $enumRelTypeCategory, $bSpouse,
       $bRelTypeRetired,      $lRelTypeChapterID, $lRelTypeOriginID,
       $lRelTypeLastUpdateID, $dteRelTypeOrigin,  $dteRelTypeLastUpdate;

      // relationships for a person
   public $lNumRelAB, $arrRelAB;

      // relationship details for a single relationship
   public
          $lPersonID_A, $strFName_A, $strLName_A,
          $lPersonID_B, $strFName_B, $strLName_B,
          $lRelNameID,  $strRelationship,
          $bSoftCash,   $strNotes;

      // relationship list
   public
         $lNumRelListItems, $relListItems, $enumRelCategories;


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->clearRelVars();
   }

   function clearRelVars(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->lPID        = $this->lRelID          =
      $this->lPersonID_A = $this->strFName_A      = $this->strLName_A =
      $this->lPersonID_B = $this->strFName_B      = $this->strLName_B =
      $this->lRelNameID  = $this->strRelationship =
      $this->bSoftCash   = $this->strNotes        = $this->bSoftCash   = null;

      $this->lRelTypeID           = $this->strRelType           = $this->enumRelTypeCategory =
      $this->bSpouse              = $this->bRelTypeRetired      = $this->lRelTypeChapterID   =
      $this->lRelTypeOriginID     = $this->lRelTypeLastUpdateID = $this->dteRelTypeOrigin    =
      $this->dteRelTypeLastUpdate = null;

      $this->lNumRelAB = $this->arrRelAB = null;

      $this->lNumRelListItems = $this->relListItems = null;

      $this->relCategoryList();
   }

   function loadFromRelViaPID(){
   //-----------------------------------------------------------------------
   // for a given person ($lPID), retrieve all the FROM relationships
   // PID => other people
   //-----------------------------------------------------------------------
      if (is_null($this->lPID)){
         screamForHelp('People ID not set!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }
      $this->genericLoadFromToRelationships(true);
   }

   function loadToRelViaPID(){
   //-----------------------------------------------------------------------
   // for a given person ($lPID), retrieve all the TO relationships
   // other people => PID
   //-----------------------------------------------------------------------
      if (is_null($this->lPID)){
         screamForHelp('People ID not set!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }
      $this->genericLoadFromToRelationships(false);
   }

   function genericLoadFromToRelationships($bFrom){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->lNumRelAB = 0;
      $this->arrRelAB  = array();

      $sqlStr =
        'SELECT
            pr_lKeyID, pr_lPerson_A_ID, pr_lPerson_B_ID, pr_lRelID_A2B,
            pr_bSoftDonations, lpr_strRelationship, pr_strNotes,
            tblA.pe_strFName AS strAFName, tblA.pe_strLName AS strALName,
            tblB.pe_strFName AS strBFName, tblB.pe_strLName AS strBLName

         FROM people_relationships
            INNER JOIN lists_people_relationships ON lpr_lKeyID=pr_lRelID_A2B
            INNER JOIN people_names AS tblA       ON tblA.pe_lKeyID=pr_lPerson_A_ID
            INNER JOIN people_names AS tblB       ON tblB.pe_lKeyID=pr_lPerson_B_ID

         WHERE pr_lPerson_'.($bFrom ? 'A' : 'B')."_ID=$this->lPID
            AND NOT pr_bRetired
            AND NOT tblA.pe_bRetired
            AND NOT tblB.pe_bRetired
         ORDER BY
            tblA.pe_strLName, tblA.pe_strFName,
            tblB.pe_strLName, tblB.pe_strFName,
            pr_lPerson_A_ID, pr_lPerson_B_ID,
            lpr_strRelationship, pr_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumRelAB = $numRows = $query->num_rows();
      if ($numRows > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $this->arrRelAB[$idx] = new stdClass;

            $this->arrRelAB[$idx]->lRelID          = $row->pr_lKeyID;
            $this->arrRelAB[$idx]->lPerson_A_ID    = $row->pr_lPerson_A_ID;
            $this->arrRelAB[$idx]->lPerson_B_ID    = $row->pr_lPerson_B_ID;
            $this->arrRelAB[$idx]->lRelTypeID      = $row->pr_lRelID_A2B;
            $this->arrRelAB[$idx]->bSoftDonations  = (boolean)$row->pr_bSoftDonations;
            $this->arrRelAB[$idx]->strRelationship = $row->lpr_strRelationship;
            $this->arrRelAB[$idx]->strNotes        = $row->pr_strNotes;
            $this->arrRelAB[$idx]->strAFName       = $row->strAFName;
            $this->arrRelAB[$idx]->strALName       = $row->strALName;
            $this->arrRelAB[$idx]->strBFName       = $row->strBFName;
            $this->arrRelAB[$idx]->strBLName       = $row->strBLName;

            ++$idx;
         }
      }
   }

   function removeRelViaRelID(){
   //-----------------------------------------------------------------------
   // remove a relationship based on the relationship ID
   //-----------------------------------------------------------------------
      if (is_null($this->lRelID)) screamForHelp('Relationship ID not set!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $sqlStr =
         "DELETE FROM people_relationships WHERE pr_lKeyID=$this->lRelID;";
      $query = $this->db->query($sqlStr);
   }

   function addRelType(){
   //-----------------------------------------------------------------------
   // add a relationship type
   //-----------------------------------------------------------------------
      global $glUserID, $glChapterID;
      if (is_null($this->strRelType)||is_null(enumRelTypeCategory)){
         screamForHelp('Relationship Type/Category not set!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }
      $sqlStr =
        'INSERT INTO lists_people_relationships
         SET
            lpr_enumCategory    ='.strPrepStr($this->enumRelTypeCategory).',
            lpr_bSpouse         ='.($this->bSpouse ? '1' :'0').',
            lpr_strRelationship ='.strPrepStr($this->$strRelType).",
            lpr_lChapterID      = $glChapterID,
            lpr_bRetired        = 0,
            lpr_lOriginID       = $glUserID,
            lpr_lLastUpdateID   = $glUserID;";

      $query = $this->db->query($sqlStr);
      $this->lRelTypeID = $this->db->insert_id();
   }

   function removeRelTypeViaRelTypeID(){
   //-----------------------------------------------------------------------
   // remove a relationship type as well as all the relationships using
   // this relationship type
   //-----------------------------------------------------------------------
      global $glChapterID;

      if (is_null($this->lRelTypeID)){
         screamForHelp('Relationship Type ID not set!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }

         //----------------------------------------------------------
         // only delete relationship if it belongs to this chapter
         //----------------------------------------------------------
      $this->relTypeInfoViaRelTypeID();
      if ($this->lRelTypeChapterID != $glChapterID){
         echoT('<font color="red">WARNING: attempt to delete relationship that does not belong to this chapter!</font><br>');
         return;
      }

         //---------------------------------
         // delete existing relationships
         //---------------------------------
      $sqlStr =
         "DELETE FROM tbl_people_relationships
          WHERE pr_lRelID_A2B=$this->lRelTypeID;";
      $query = $this->db->query($sqlStr);

         //-------------------
         // delete type
         //-------------------
      $sqlStr =
         "DELETE FROM tbl_lists_people_relationships
          WHERE lpr_lKeyID=$this->lRelTypeID;";
      $query = $this->db->query($sqlStr);
   }

   function relTypeInfoViaRelTypeID(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_null($this->lRelTypeID)){
         screamForHelp('Relationship Type ID not set!<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }

      $sqlStr =
        "SELECT
           lpr_enumCategory, lpr_bSpouse, lpr_strRelationship,
           lpr_lChapterID, lpr_bRetired, lpr_lOriginID,
           lpr_lLastUpdateID,
           UNIX_TIMESTAMP(lpr_dteOrigin)     AS dteOrigin,
           UNIX_TIMESTAMP(lpr_dteLastUpdate) AS dteLastUpdate
         FROM tbl_lists_people_relationships
         WHERE lpr_lKeyID=$this->lRelTypeID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         echo('<font face="monospace" style="font-size: 8pt;">'.__FILE__.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br($sqlStr)."<br><br></font>\n");
         screamForHelp('UNEXPECTED EOF<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }else {
         $row = $query->row();
         $this->enumRelTypeCategory  = $row->lpr_enumCategory;
         $this->bSpouse              = (boolean)$row->lpr_bSpouse;
         $this->strRelType           = $row->lpr_strRelationship;
         $this->lRelTypeChapterID    = (int)$row->lpr_lChapterID;
         $this->bRelTypeRetired      = (boolean)$row->lpr_bRetired;
         $this->lRelTypeOriginID     = $row->lpr_lOriginID;
         $this->lRelTypeLastUpdateID = $row->lpr_lLastUpdateID;
         $this->dteRelTypeOrigin     = $row->dteOrigin;
         $this->dteRelTypeLastUpdate = $row->dteLastUpdate;
      }
   }

   function reciprocalRelInfoViaPeopleIDs(
                             $lPersonA_ID,  $lPersonB_ID,
                             &$lNumRel_A2B, &$relA2B,
                             &$lNumRel_B2A, &$relB2A){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumRel_A2B = $lNumRel_B2A = 0;
      $relA2B = array();
      $relB2A = array();
      $lRelID_A2B = $this->lPeopleRelInfoViaPeopleIDs(
                           $lPersonA_ID,         $lPersonB_ID,
                           $lNumRel_A2B,         $relA2B);

      $lRelID_B2A2 = $this->lPeopleRelInfoViaPeopleIDs(
                           $lPersonB_ID,         $lPersonA_ID,
                           $lNumRel_B2A,         $relB2A);
   }

   function lPeopleRelInfoViaPeopleIDs(
                        $lPersonA_ID,          $lPersonB_ID,
                        &$lNumRel_A2B,         &$relA2B){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumRel_A2B = 0;
      $relA2B      = array();
      $sqlStr =
         "SELECT
              pr_lKeyID,
              pr_lRelID_A2B, pr_bSoftDonations, pr_strNotes,
              lpr_strRelationship
           FROM people_relationships
              INNER JOIN lists_people_relationships ON pr_lRelID_A2B=lpr_lKeyID
           WHERE
              NOT pr_bRetired
              AND ( ( pr_lPerson_A_ID=$lPersonA_ID ) AND ( pr_lPerson_B_ID=$lPersonB_ID ) )
           ORDER BY pr_lKeyID;";
      $query = $this->db->query($sqlStr);
      $lNumRel_A2B = $query->num_rows();
      if ($lNumRel_A2B > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $relA2B[$idx] = new stdClass;
            $relA2B[$idx]->lKeyID          = $row->pr_lKeyID;
            $relA2B[$idx]->lRelID_A2B      = $row->pr_lRelID_A2B;
            $relA2B[$idx]->bSoftDonations  = $row->pr_bSoftDonations;
            $relA2B[$idx]->strNotes        = $row->pr_strNotes;
            $relA2B[$idx]->strRelationship = $row->lpr_strRelationship;

            ++$idx;
         }
      }
   }

   function strPeopleRelationshipsDDL($bShowBlank, $lMatchID){
   //---------------------------------------------------------------------
   // caller must include:
   //      require_once('../config/def_relationships.php');
   //---------------------------------------------------------------------
      $strDDL = '';

      if ($bShowBlank) {
         $strDDL .= '<option value="">&nbsp;</option>';
      }

      $sqlStr =
          "SELECT lpr_lKeyID, lpr_strRelationship
           FROM lists_people_relationships
           WHERE NOT lpr_bRetired
           ORDER BY lpr_strRelationship;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         screamForHelp('UNEXPECTED EOF<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }else {
         $lGroupID = -999;
         $idx = 0;
         foreach ($query->result() as $row) {
            $lKeyID      = (int)$row->lpr_lKeyID;
            $strSelected = ($lKeyID == $lMatchID ? 'selected' : '');
            $strDDL .=
                  '<option value="'.$lKeyID.'" '.$strSelected.'>'
                     .htmlspecialchars($row->lpr_strRelationship)
                 .'</option>'."\n";
            ++$idx;
         }
      }
      return($strDDL);
   }

   function lSavePeopleRelationship(
               $lRelID,
               $lPersonID_A, $lPersonID_B, $lRelTypeID,
               $bSoftCache,  $strNotes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $bNew = $lRelID <= 0;

      $sqlCommon =
         " pr_lPerson_A_ID=$lPersonID_A,
           pr_lPerson_B_ID=$lPersonID_B,
           pr_lRelID_A2B=$lRelTypeID,
           pr_bSoftDonations=".($bSoftCache ? '1' : '0').',
           pr_strNotes='.strPrepStr($strNotes).",
           pr_lLastUpdateID=$glUserID ";

      if ($bNew) {
         $sqlStr =
            "INSERT INTO people_relationships
             SET $sqlCommon,
                pr_bRetired=0,
                pr_lOriginID=$glUserID,
                pr_dteOrigin=NOW();";
      }else {
         $sqlStr =
            "UPDATE people_relationships
             SET $sqlCommon
             WHERE pr_lKeyID=$lRelID;";
      }
      $this->db->query($sqlStr);
      if ($bNew){
         return($this->db->insert_id());
      }else {
         return($lRelID);
      }
   }

   function relationshipInfoViaRelID($lRelID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT
              pr_lPerson_A_ID, tblPeople_A.pe_strFName AS strFName_A, tblPeople_A.pe_strLName AS strLName_A,
              pr_lPerson_B_ID, tblPeople_B.pe_strFName AS strFName_B, tblPeople_B.pe_strLName AS strLName_B,
              pr_lRelID_A2B, lpr_strRelationship,
              pr_bSoftDonations, pr_strNotes
          FROM people_relationships
             INNER JOIN lists_people_relationships  ON pr_lRelID_A2B         = lpr_lKeyID
             INNER JOIN people_names AS tblPeople_A ON tblPeople_A.pe_lKeyID = pr_lPerson_A_ID
             INNER JOIN people_names AS tblPeople_B ON tblPeople_B.pe_lKeyID = pr_lPerson_B_ID
          WHERE pr_lKeyID=$lRelID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         screamForHelp('UNEXPECTED EOF</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
      }else {
         $row = $query->row() ;
         $this->lRelID          = $lRelID;
         $this->lPersonID_A     = $row->pr_lPerson_A_ID;
         $this->strFName_A      = $row->strFName_A;
         $this->strLName_A      = $row->strLName_A;

         $this->lPersonID_B     = $row->pr_lPerson_B_ID;
         $this->strFName_B      = $row->strFName_B;
         $this->strLName_B      = $row->strLName_B;

         $this->lRelNameID      = $row->pr_lRelID_A2B;
         $this->strRelationship = $row->lpr_strRelationship;
         $this->bSoftCash       = $row->pr_bSoftDonations;
         $this->strNotes        = $row->pr_strNotes;
      }
   }

      //--------------------------------------------------------------
      // R E L A T I O N S H I P   L I S T S
      //--------------------------------------------------------------

   public function loadRelationships($bSortCat, $bLoadRetired, $bViaItemID, $lRelItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->relListItems = array();

      $sqlStr =
        'SELECT
            lpr_lKeyID, lpr_enumCategory, lpr_bSpouse, lpr_strRelationship,
            lpr_bRetired, lpr_lOriginID, lpr_lLastUpdateID,
            UNIX_TIMESTAMP(lpr_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(lpr_dteLastUpdate) AS dteLastUpdate
         FROM lists_people_relationships
         WHERE 1 '
            .($bLoadRetired ? '' : ' AND NOT lpr_bRetired ')
            .($bViaItemID   ? " AND lpr_lKeyID=$lRelItemID " : '').'
         ORDER BY '.($bSortCat ? ' lpr_enumCategory, ' : '').'lpr_strRelationship, lpr_lKeyID;';

      $query = $this->db->query($sqlStr);
      $this->lNumRelListItems = $numRows = $query->num_rows();

      if ($numRows==0) {
            $this->relListItems[0] = new stdClass;

            $this->relListItems[0]->lKeyID          =
            $this->relListItems[0]->enumCategory    =
            $this->relListItems[0]->bSpouse         =
            $this->relListItems[0]->strRelationship =
            $this->relListItems[0]->bRetired        =
            $this->relListItems[0]->lOriginID       =
            $this->relListItems[0]->lLastUpdateID   =
            $this->relListItems[0]->dteOrigin       =
            $this->relListItems[0]->dteLastUpdate   = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->relListItems[$idx] = new stdClass;

            $this->relListItems[$idx]->lKeyID          = (int)$row->lpr_lKeyID;
            $this->relListItems[$idx]->enumCategory    = $row->lpr_enumCategory;
            $this->relListItems[$idx]->bSpouse         = $row->lpr_bSpouse;
            $this->relListItems[$idx]->strRelationship = $row->lpr_strRelationship;
            $this->relListItems[$idx]->bRetired        = (bool)$row->lpr_bRetired;
            $this->relListItems[$idx]->lOriginID       = (int)$row->lpr_lOriginID;
            $this->relListItems[$idx]->lLastUpdateID   = (int)$row->lpr_lLastUpdateID;
            $this->relListItems[$idx]->dteOrigin       = (int)$row->dteOrigin;
            $this->relListItems[$idx]->dteLastUpdate   = (int)$row->dteLastUpdate;

            ++$idx;
         }
      }
   }

   public function relCategoryList(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->enumRelCategories = array();
      $this->enumRelCategories[0] = 'Family';
      $this->enumRelCategories[1] = 'Community';
      $this->enumRelCategories[2] = 'Business';
      $this->enumRelCategories[3] = 'Other';
   }

   public function strRelCatDDL($enumMatch, $bAddBlank){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bAddBlank){
         $strDDL = '<option value="">&nbsp;</option>'."\n";
      }else {
         $strDDL = '';
      }
      foreach ($this->enumRelCategories as $enumCat){
         if ($enumCat==$enumMatch){
            $strSel = ' SELECTED ';
         }else {
            $strSel = '';
         }
         $strDDL .= '<option value="'.$enumCat.'" '.$strSel.'>'.$enumCat."</option>\n";
      }
      return($strDDL);
   }

   public function strHTMLRelItemList($bShowEditLink){
   //---------------------------------------------------------------------
   // user must call $this->loadRelationships prior to this call
   //---------------------------------------------------------------------
      $strOut = '
         <table class="enpRptC">
            <tr>
               <td class="enpRptLabel" >
                  ID
               </td>';
      if ($bShowEditLink){
         $strOut .= '
               <td class="enpRptLabel" >
                  &nbsp;
               </td>';
      }

      $strOut .= '
               <td class="enpRptLabel" >
                  Type
               </td>
               <td class="enpRptLabel">
                  Name
               </td>
               <td class="enpRptLabel">
                  Spousal Rel?
               </td>
            </tr>';

      foreach ($this->relListItems as $clsRelItem){
         if (!$clsRelItem->bRetired){
            $lKeyID = $clsRelItem->lKeyID;
            if (!$bShowEditLink){
               $strLinkEdit = '';
            }else {
               $strLinkEdit = strLinkEdit_PeopleRelItem($lKeyID, 'Edit relationship list item', true);
            }
            $strOut .= '
               <tr class="makeStripe">
                  <td class="enpRpt" style="text-align: center;">'
                     .$strLinkEdit.' '
                     .str_pad($lKeyID, 5, '0', STR_PAD_LEFT).'
                  </td>';

            if ($bShowEditLink){
               $strOut .= '
                     <td class="enpRpt" style="text-align: center;">'
                        .strLinkRem_PeopleRelItem($lKeyID, 'Remove this relationship entry', true, true).'
                     </td>';
            }

            $strOut .= '
                  <td class="enpRpt" style="text-align: left;">'
                     .htmlspecialchars($clsRelItem->enumCategory).'
                  </td>

                  <td class="enpRpt">'
                     .htmlspecialchars($clsRelItem->strRelationship).'
                  </td>
                  <td class="enpRpt" style="text-align: center;">'
                     .($clsRelItem->bSpouse ?
                              '<img src="'.base_url().'images/misc/checkBox.gif"
                                    title="Spousal relationship" border="0">' : '&nbsp;').'
                  </td>
               </tr>';
         }
      }
      $strOut .= '</table>';
      return($strOut);
   }

   public function addNewPeopleRelListItem(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'INSERT INTO lists_people_relationships
          SET '.$this->strSQLCommonList().",
             lpr_bRetired = 0,
             lpr_lOriginID = $glUserID,
             lpr_dteOrigin = NOW();";
      $query = $this->db->query($sqlStr);
      $this->relListItems[0]->lKeyID = $this->db->insert_id();
   }

   public function updatePeopleRelListItem(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'UPDATE lists_people_relationships
          SET '.$this->strSQLCommonList()."
          WHERE lpr_lKeyID = ".$this->relListItems[0]->lKeyID.';';
      $query = $this->db->query($sqlStr);
   }

   public function retireRelListItem($lKeyID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        "UPDATE lists_people_relationships
         SET
            lpr_bRetired = 1,
            lpr_lLastUpdateID    = $glUserID,
            lpr_dteLastUpdate    = NOW()
         WHERE lpr_lKeyID=$lKeyID;";

      $query = $this->db->query($sqlStr);
   }

   private function strSQLCommonList(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      return('
            lpr_enumCategory     = '.strPrepStr($this->relListItems[0]->enumCategory)   .',
            lpr_bSpouse          = '.($this->relListItems[0]->bSpouse ? '1' : '0')      .',
            lpr_strRelationship  = '.strPrepStr($this->relListItems[0]->strRelationship).",
            lpr_lLastUpdateID    = $glUserID,
            lpr_dteLastUpdate    = NOW() ");
   }




}
?>