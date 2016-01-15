<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2010-2016 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('util/mlist_generic', 'clsList');
---------------------------------------------------------------------

   __construct           ()
   initializeListManager ($strTopListType, $strListType)
   loadList              ()
   strRetrieveListItem   ($lListID)
   strSqlNewListItem     ($strListItemName, $lUserID)
   strSqlUpdateListItem  ($bRetired, $lListItemID, $strListItemName, $lUserID)
   setAddEditTableText   ()
   lListCnt              ()
   genericLoadList       ()
   strLoadListDDL        ($strDDLName, $bAddBlank, $lMatchID)

*/

class mlist_generic extends CI_Model{

   public
      $strTopListType, $strListType, $lChapterID;

      //------------------------------------------
      // text associated with the add/edit table
      //------------------------------------------
   public
       $strAddEditTableTitle,        $strAddEditTableItemLabel,
       $strAddEditTableButtonAddNew, $strAddEditTableButtonUpdate,
       $strDBLookupEmpty,            $strListTableTitle,
       $strPageTitle
       ;

      //------------------------------------------
      // database table and field variables
      //------------------------------------------
   public
       $strListTable, $strKeyIDFN,   $strListItemFN,  $strFieldPrefix,
       $enumListQual, $strQualFN,
       $lForeignID,   $strForeignID, $strForeignIDFN, $strForeignIDKeyFN,
       $strForeignTable,
       $strSQL_ExtraInner, $strSQL_ExtraWhere, $strExtraInsert;

   public
      $strBlankDDLName;

   function __construct(){
   //-----------------------------------------------------------------
   // Constructor
   //-----------------------------------------------------------------
		parent::__construct();

      $this->strTopListType    = $this->strListType       =
      $this->enumListQual      = $this->strQualFN         =
      $this->strForeignTable   = $this->strForeignIDFN    = $this->strForeignIDKeyFN = null;
      $this->strSQL_ExtraInner = $this->strSQL_ExtraWhere = $this->strExtraInsert    = '';

      $this->strBlankDDLName   = '&nbsp;';
   }

   public function initializeListManager($strTopListType, $strListType){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      $this->strTopListType = $strTopListType;
      $this->strListType    = $strListType;

      $this->strListTable      = 'lists_generic';
      $this->strKeyIDFN        = 'lgen_lKeyID';
      $this->strListItemFN     = 'lgen_strListItem';
      $this->strFieldPrefix    = 'lgen_';
      $this->strQualFN         = 'lgen_enumListType';

      switch ($this->strListType){

         case CENUM_LISTTYPE_ATTRIB:
            $this->strDBLookupEmpty =
                 'There are currently no <b>names</b> in your list of people who can be '
                .'attributed to donors, sponsors, and gifts.<br><br>'."\n";
            $this->strListTableTitle = 'People Attribute List';
            $this->strPageTitle      = 'List Manager: People attributed to gifts, sponsorships, etc';
            $this->enumListQual      = 'attrib';
            break;

         case CENUM_LISTTYPE_BIZCAT:
            $this->strDBLookupEmpty =
                 'There are currently no <b>business/organization categories</b> in your database.<br><br>'."\n";
            $this->strListTableTitle = 'Business/Organization Categories';
            $this->strPageTitle      = 'List Manager: Business/Organization Categories';
            $this->enumListQual      = 'bizCat';
            break;

         case CENUM_LISTTYPE_BIZCONTACTREL:
            $this->strDBLookupEmpty =
                 'There are currently no <b>business contact relationships</b> in your database.<br><br>'."\n";
            $this->strListTableTitle = 'Business/Organization Contact Relationships';
            $this->strPageTitle      = 'List Manager: Business/Organization Contact Relationships';
            $this->enumListQual      = 'bizContactRel';
            break;

         case CENUM_LISTTYPE_CAMPEXPENSE:
            $this->strDBLookupEmpty =
                 'There are currently no <b>Campaign Expense Categories</b> defined in your database.<br><br>'."\n";
            $this->strListTableTitle = 'Campaign Expense Categories';
            $this->strPageTitle      = 'List Manager: Campaign Expense Categories';
            $this->enumListQual      = 'campaignExpense';
            break;

         case CENUM_LISTTYPE_GIFTPAYTYPE:
            $this->strDBLookupEmpty =
                 'There are currently no <b>Gift Payment Types</b> defined in your database.<br><br>'."\n";
            $this->strListTableTitle = 'Gift Payment Types';
            $this->strPageTitle      = 'List Manager: Campaign Expense Categories';
            $this->enumListQual      = 'giftPayType';
            break;

         case CENUM_LISTTYPE_INKIND:
            $this->strDBLookupEmpty =
                 'There are currently no <b>In-Kind Donation Categories</b> defined in your database.<br><br>'."\n";
            $this->strListTableTitle = 'In-Kind Categories';
            $this->strPageTitle      = 'List Manager: In-Kind Donation Categories';
            $this->enumListQual      = 'inKind';
            break;

         case CENUM_LISTTYPE_MAJORGIFTCAT:
            $this->strDBLookupEmpty =
                 'There are currently no <b>Major Gift Categories</b> defined in your database.<br><br>'."\n";
            $this->strListTableTitle = 'Major Gift Categories';
            $this->strPageTitle      = 'List Manager: Major Gift Categories';
            $this->enumListQual      = 'majorGiftCats';
            break;

         case CENUM_LISTTYPE_SPONTERMCAT:
            $this->strDBLookupEmpty =
                 'There are currently no <b>Sponsorship Termination Categories</b> defined in your database.<br><br>'."\n";
            $this->strListTableTitle = 'Sponsorship Termination Categories';
            $this->strPageTitle      = 'List Manager: Sponsorship Termination Categories';
            $this->enumListQual      = 'sponTermCat';
            break;

         case CENUM_LISTTYPE_VOLJOBCAT:
            $this->strDBLookupEmpty =
                 'There are currently no <b>Volunteer Job Categories</b> defined in your database.<br><br>'."\n";
            $this->strListTableTitle = 'Volunteer Job Categories';
            $this->strPageTitle      = 'List Manager: Volunteer Job Categories';
            $this->enumListQual      = 'volJobCat';
            break;

         case CENUM_LISTTYPE_VOLJOBCODES:
            $this->strDBLookupEmpty =
                 'There are currently no <b>Volunteer Shift Job Codes</b> defined in your database.<br><br>'."\n";
            $this->strListTableTitle = 'Volunteer Shift Job Codes';
            $this->strPageTitle      = 'List Manager: Volunteer Shift Job Codes';
            $this->enumListQual      = 'volShiftJobCodes';
            break;

         case CENUM_LISTTYPE_VOLACT:
            $this->strDBLookupEmpty =
                 'There are currently no <b>Volunteer Activities</b> defined in your database.<br><br>'."\n";
            $this->strListTableTitle = 'Volunteer Activities';
            $this->strPageTitle      = 'List Manager: Volunteer Activities';
            $this->enumListQual      = 'volActivities';
            break;

         case CENUM_LISTTYPE_VOLSKILLS:
            $this->strDBLookupEmpty =
                 'There are currently no <b>Volunteer Skills</b> defined in your database.<br><br>'."\n";
            $this->strListTableTitle = 'Skills the Volunteer Possesses';
            $this->strPageTitle      = 'List Manager: Volunteer Skills';
            $this->enumListQual      = 'volSkills';
            break;

         case CENUM_LISTTYPE_CPREPOSTCAT:
            $this->strDBLookupEmpty =
                 'There are currently no <b>Client Pre/Post Test Categories</b> defined in your database.<br><br>'."\n";
            $this->strListTableTitle = 'Pre/Post Test Categories';
            $this->strPageTitle      = 'List Manager: Pre/Post Test Categories';
            $this->enumListQual      = 'prePostTestCat';
            break;

//         case CENUM_LISTTYPE_TS_LOCATIONS:
//            $this->strDBLookupEmpty =
//                 'There are currently no <b>Time Sheet Locations</b> defined in your database.<br><br>'."\n";
//            $this->strListTableTitle = 'Time Sheet Locations';
//            $this->strPageTitle      = 'List Manager: Time Sheet Locations';
//            $this->enumListQual      = 'timeSheetLocations';
//            break;

         default:
            screamForHelp($this->strListType.': list type not defined<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            echoT('*** ERROR *** '.$this->strListType.': not defined<br>'); die;
            $this->strDBLookupEmpty =
                 'There are currently no records that match your search criteria.<br><br>'."\n";
            $this->strListTableTitle =
            $this->strPageTitle      =
            $this->strListTable      =
            $this->strKeyIDFN        =
            $this->strListItemFN     =
            $this->strFieldPrefix    =
                                        '### error ###';
            break;
      }
   }

   public function loadList(){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      $this->listItems = array();
      $sqlStr =
              "SELECT
                  $this->strKeyIDFN AS lListKeyID, $this->strListItemFN AS strListItem
               FROM $this->strListTable
                  $this->strSQL_ExtraInner
               WHERE
                  $this->strQualFN = ".strPrepStr($this->enumListQual).'
                  AND (NOT '.$this->strFieldPrefix."bRetired)
                  $this->strSQL_ExtraWhere
               ORDER BY $this->strListItemFN;";

      $query = $this->db->query($sqlStr);
      $this->lNumInList = $numRows = $query->num_rows();
      if ($numRows==0) {
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->listItems[$idx] = new stdClass;
            $this->listItems[$idx]->lKeyID      = $row->lListKeyID;
            $this->listItems[$idx]->strListItem = $row->strListItem;

            ++$idx;
         }
      }
   }

   public function loadListUTable($lFieldID, $bIncludeRetired, $bOrderViaSortIDX){
   //-----------------------------------------------------------------
   // load the personalized list associated with personalized ddl 
   // and mddl fields
   //-----------------------------------------------------------------
      $this->listItems = array();
      $sqlStr =
        "SELECT ufddl_lKeyID AS lListKeyID, ufddl_strDDLEntry AS strListItem
         FROM uf_ddl
         WHERE `ufddl_lFieldID`=$lFieldID "
            .($bIncludeRetired ? '' : ' AND NOT ufddl_bRetired ').'
         ORDER BY '.($bOrderViaSortIDX ? ' ufddl_lSortIDX ' : ' ufddl_strDDLEntry ' ).', ufddl_lKeyID;';

      $query = $this->db->query($sqlStr);
      $this->lNumInList = $numRows = $query->num_rows();
      if ($numRows==0) {
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->listItems[$idx] = new stdClass;
            $this->listItems[$idx]->lKeyID      = $row->lListKeyID;
            $this->listItems[$idx]->strListItem = $row->strListItem;

            ++$idx;
         }
      }
   }

   public function strRetrieveListItem($lListID){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      $this->listItems = array();
      $sqlStr =
              "SELECT
                   $this->strListItemFN AS strListItem
               FROM $this->strListTable
               WHERE
                  $this->strKeyIDFN=$lListID
               ORDER BY $this->strListItemFN;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         return('### error ###');
      }else {
         $row = $query->row();
         return($row->strListItem);
      }
   }
   
   public function genericLoadListItem($lListID){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      $sqlStr =
          "SELECT lgen_strListItem
           FROM lists_generic
           WHERE lgen_lKeyID=$lListID;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         return('### error ###');
      }else {
         $row = $query->row();
         return($row->lgen_strListItem);
      }
   }
   
   public function lInsertNewListItem($strItem){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      global $glUserID;
      $sqlStr = $this->strSqlNewListItem($strItem, $glUserID);
      $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function strSqlNewListItem($strListItemName, $lUserID){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      $sqlStr =
              "INSERT INTO $this->strListTable
               SET
                   $this->strListItemFN = ".strPrepStr(xss_clean($strListItemName)).",
                   $this->strQualFN     = ".strPrepStr(xss_clean($this->enumListQual)).', '
                  .$this->strFieldPrefix.'bRetired      = 0, '
                  .$this->strFieldPrefix."lOriginID     = $lUserID, "
                  .$this->strFieldPrefix."lLastUpdateID = $lUserID, "
                  .$this->strFieldPrefix."dteOrigin     = NOW()
                  $this->strExtraInsert;";
      return($sqlStr);
   }

   public function updateListItem($strItem, $id){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      global $glUserID;

      $sqlStr = $this->strSqlUpdateListItem(false, $id, $strItem, $glUserID);
      $query = $this->db->query($sqlStr);
   }

   function strSqlUpdateListItem($bRetired, $lListItemID, $strListItemName, $lUserID){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      $sqlStr =
           "UPDATE $this->strListTable
            SET
                 $this->strListItemFN=".strPrepStr(xss_clean($strListItemName)).", "
                 .$this->strFieldPrefix.'bRetired='.($bRetired ? '1':'0').", "
                 .$this->strFieldPrefix."lLastUpdateID=$lUserID
            WHERE ".$this->strFieldPrefix."lKeyID=$lListItemID;";

      return($sqlStr);
   }

   function removeListItem($lListID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr = "
           UPDATE lists_generic
           SET lgen_bRetired=1, lgen_lLastUpdateID=$glUserID
           WHERE lgen_lKeyID=$lListID;";
      $query = $this->db->query($sqlStr);
   }

   function setAddEditTableText(){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      switch ($this->strListType){

         case CENUM_LISTTYPE_ATTRIB:
            $this->strAddEditTableTitle =
                  'Names of people in your organization<br> '
                 .'who bring in donors, sponsors, and gifts:';
            $this->strAddEditTableItemLabel    = 'Name: ';
            $this->strAddEditTableButtonAddNew = 'Add New Name';
            $this->strAddEditTableButtonUpdate = 'Update Name';
            break;

         case CENUM_LISTTYPE_BIZCAT:
            $this->strAddEditTableTitle        = 'Business Categories';
            $this->strAddEditTableItemLabel    = 'Category: ';
            $this->strAddEditTableButtonAddNew = 'Add New Category';
            $this->strAddEditTableButtonUpdate = 'Update Category';
            break;

         case CENUM_LISTTYPE_BIZCONTACTREL:
            $this->strAddEditTableTitle        = 'Business Contact Relationships';
            $this->strAddEditTableItemLabel    = 'Relationship: ';
            $this->strAddEditTableButtonAddNew = 'Add New Relationship';
            $this->strAddEditTableButtonUpdate = 'Update Relationship';
            break;

         case CENUM_LISTTYPE_CAMPEXPENSE:
            $this->strAddEditTableTitle        = 'Campaign Expense Categories';
            $this->strAddEditTableItemLabel    = 'Category: ';
            $this->strAddEditTableButtonAddNew = 'Add New Category';
            $this->strAddEditTableButtonUpdate = 'Update Category';
            break;

         case CENUM_LISTTYPE_GIFTPAYTYPE:
            $this->strAddEditTableTitle        = 'Donation Payment Type';
            $this->strAddEditTableItemLabel    = 'Payment Type: ';
            $this->strAddEditTableButtonAddNew = 'Add New Payment Type';
            $this->strAddEditTableButtonUpdate = 'Update Payment Type';
            break;

         case CENUM_LISTTYPE_INKIND:
            $this->strAddEditTableTitle        = 'In-Kind Donation Categories';
            $this->strAddEditTableItemLabel    = 'Category: ';
            $this->strAddEditTableButtonAddNew = 'Add New Category';
            $this->strAddEditTableButtonUpdate = 'Update Category';
            break;

         case CENUM_LISTTYPE_MAJORGIFTCAT:
            $this->strAddEditTableTitle        = 'Major Gift Categories';
            $this->strAddEditTableItemLabel    = 'Category: ';
            $this->strAddEditTableButtonAddNew = 'Add New Category';
            $this->strAddEditTableButtonUpdate = 'Update Category';
            break;

         case CENUM_LISTTYPE_SPONTERMCAT:
            $this->strAddEditTableTitle        = 'Sponsorship Termination Categories';
            $this->strAddEditTableItemLabel    = 'Category: ';
            $this->strAddEditTableButtonAddNew = 'Add New Category';
            $this->strAddEditTableButtonUpdate = 'Update Category';
            break;

         case CENUM_LISTTYPE_VOLJOBCAT:
            $this->strAddEditTableTitle        = 'Volunteer Job Categories';
            $this->strAddEditTableItemLabel    = 'Category: ';
            $this->strAddEditTableButtonAddNew = 'Add New Category';
            $this->strAddEditTableButtonUpdate = 'Update Category';
            break;

         case CENUM_LISTTYPE_VOLJOBCODES:
            $this->strAddEditTableTitle        = 'Volunteer Shift Job Codes';
            $this->strAddEditTableItemLabel    = 'Job Code: ';
            $this->strAddEditTableButtonAddNew = 'Add New Job Code';
            $this->strAddEditTableButtonUpdate = 'Update Job Code';
            break;

         case CENUM_LISTTYPE_VOLACT:
            $this->strAddEditTableTitle        = 'Volunteer Activities';
            $this->strAddEditTableItemLabel    = 'Activity: ';
            $this->strAddEditTableButtonAddNew = 'Add New Activity';
            $this->strAddEditTableButtonUpdate = 'Update Activity';
            break;

         case CENUM_LISTTYPE_VOLSKILLS:
            $this->strAddEditTableTitle        = 'Volunteer Skills';
            $this->strAddEditTableItemLabel    = 'Skill: ';
            $this->strAddEditTableButtonAddNew = 'Add New Skill';
            $this->strAddEditTableButtonUpdate = 'Update Skill List';
            break;

         case CENUM_LISTTYPE_CPREPOSTCAT:
            $this->strAddEditTableTitle        = 'Pre/Post Test Categories';
            $this->strAddEditTableItemLabel    = 'Category: ';
            $this->strAddEditTableButtonAddNew = 'Add New Category';
            $this->strAddEditTableButtonUpdate = 'Update Category List';
            break;

//         case CENUM_LISTTYPE_TS_LOCATIONS:
//            $this->strAddEditTableTitle        = 'Time Sheet Locations';
//            $this->strAddEditTableItemLabel    = 'Location: ';
//            $this->strAddEditTableButtonAddNew = 'Add New Location';
//            $this->strAddEditTableButtonUpdate = 'Update Skill Location';
//            break;

         default:
            screamForHelp($this->strListType.': Unrecognized list type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   public function lListCnt(){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      if (is_null($this->enumListType)) screamForHelp('Class not initialized<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);

      $sqlStr =
        'SELECT COUNT(*) AS lNumRecs
         FROM lists_generic
         WHERE
            lgen_enumListType='.strPrepStr($this->enumListType).'
            AND (NOT lgen_bRetired);';
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         return((int)$row->lNumRecs);
      }
   }

   public function genericLoadList($bIncludeRetired=false){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      if (is_null($this->enumListType)) screamForHelp('Class not initialized<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);

      $this->listItems = array();
      $sqlStr =
          'SELECT
              lgen_lKeyID, lgen_enumListType, lgen_strListItem, lgen_lSortIDX,
              lgen_bRetired
           FROM lists_generic
           WHERE
              lgen_enumListType='.strPrepStr($this->enumListType).' '
              .($bIncludeRetired ? '' : ' AND (NOT lgen_bRetired) ').'
           ORDER BY lgen_enumListType, lgen_lSortIDX, lgen_strListItem, lgen_lKeyID;';
      $query = $this->db->query($sqlStr);
      $this->lNumInList = $numRows = $query->num_rows();

      $idx = 0;
      if ($numRows > 0){
         foreach ($query->result() as $row){
            $this->listItems[$idx] = new stdClass;
            $li = &$this->listItems[$idx];
            $li->lKeyID       = $row->lgen_lKeyID;
            $li->strListItem  = $row->lgen_strListItem;
            $li->lSortIDX     = $row->lgen_lSortIDX;
            $li->enumListType = $row->lgen_enumListType;
            $li->bRetired     = $row->lgen_bRetired;
            ++$idx;
         }
      }
   }

   public function strLoadListDDL($strDDLName, $bAddBlank, $lMatchID){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      $strDDL = '<select name="'.$strDDLName.'">'."\n";
      if ($bAddBlank){
         $strDDL .= '<option value="-1">'
                    .$this->strBlankDDLName.'</option>'."\n";
      }

      $this->genericLoadList();

      foreach ($this->listItems as $clsItem){
         $lKeyID = $clsItem->lKeyID;
         $strSelect = $lKeyID == $lMatchID ? ' SELECTED ' : '';
         $strDDL .= '<option value="'.$lKeyID.'" '.$strSelect.'>'
                   .htmlspecialchars($clsItem->strListItem).'</option>'."\n";
      }

      $strDDL .= '</select>'."\n";
      return($strDDL);
   }



}


?>