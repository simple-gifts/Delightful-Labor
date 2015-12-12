<?php
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2013-2015 by Database Austin
 Austin, Texas

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.

-------------------------------------------------------------------
      $this->load->model('groups/mgroups', 'groups');
---------------------------------------------------------------------
 __construct                 ()

 groupMembershipViaFID       ($enumGroupType, $lFID)
 loadActiveGroupsViaType     ($enumGroupType, $enumSort, $strExcludeIDs, ...
 bGroupMember                ($lGID, $lForeignID)
 lCntActiveGroupsViaType     ($enumGroupType)
 clearGroupMembership        ()
 addGroupMembership          ()

 loadGroupMembership         ($enumGroupType, $lGID)

 removeMemberFromGroup       ()
 removeBlockMembersFromGroup ()
 removeMemFromAllGroups      ($enumGroupType, $lForeignID)

 loadGroupInfo               ()
 lAddNewGroupParent          ()
 updateGroupParentRec        ()
 sqlCommonGroupParent        ()
 remGroup                    ()
 lCountMembersInGroup        ()

 lNumRecsInReport            ($enumContext, &$groupIDs, $bShowAny, $bUseLimits, $lStartRec, $lRecsPerPage){
 strGroupReportPage          ($enumContext, &$groupIDs, $bShowAny, $bUseLimits, $lStartRec, $lRecsPerPage){
 strDDLActiveGroupEntries    ($strSelectName, $enumGroupType, $lMatchID, $bAddBlank, $bMulti=false){


---------------------------------------------------------------------
 Group types are defined in helpers/dl_config_helper.php
    CENUM_CONTEXT_BIZ
    CENUM_CONTEXT_BIZCONTACT
    CENUM_CONTEXT_CLIENT
    CENUM_CONTEXT_GIFT
    CENUM_CONTEXT_HOUSEHOLD
    CENUM_CONTEXT_PEOPLE
    CENUM_CONTEXT_SPONSORSHIP
    CENUM_CONTEXT_STAFF
    CENUM_CONTEXT_VOLUNTEER

-------------------------------------------------------------------*/

class mgroups extends CI_Model{
   var $lGroupID, $clsGroupMembers, $lNumGroupMems;

      // a person/biz/vol etc is a member of these groups...
   var $arrMemberInGroups, $lNumMemInGroups;

      // groups of a specified type
   var $arrGroupList, $lNumGroupList, $strMemListIDs;

   public
      $lForeignID, $strForeignIDs, $enumSubGroup;

      //--------------------
      // Group info
      //--------------------
   public
      $gp_strGroupName, $gp_enumGroupType,  $gp_bTempGroup,
      $gp_strNotes, $gp_lOriginID, $gp_lLastUpdateID,
      $gp_dteExpire, $gp_dteOrigin, $gp_dteLastUpdate,
      $numGroups, $groupTable;

   public
      $strGroupName, $strGroupCategory;

   public
      $groupMembers, $lCntMembersInGroup, $groupMemLabels;


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->lGroupID          = $this->clsGroupMembers =
      $this->arrMemberInGroups = $this->lNumMemInGroups =
      $this->arrGroupList      = $this->lNumGroupList   =
      $this->enumSubGroup      =
      $this->lNumGroupMems     = $this->strMemListIDs   = null;

      $this->lForeignID =  $this->strForeignIDs = null;

      $this->gp_strGroupName = $this->gp_enumGroupType  = $this->gp_bTempGroup    =
      $this->gp_strNotes     = $this->gp_lOriginID      = $this->gp_lLastUpdateID =
      $this->gp_dteExpire    = $this->gp_dteOrigin      = $this->gp_dteLastUpdate = null;

      $this->strGroupName    = $this->strGroupCategory = null;

      $this->groupMembers = $this->lCntMembersInGroup = $this->groupMemLabels = null;

      $this->groupTable = null;
   }

   function strSQLQualUserPerms(&$enumGroupType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $enumHold = $enumGroupType;
      if (bUserPermGroupType($enumGroupType)){
         $enumGroupType = CENUM_CONTEXT_USER;
         $sqlQual = ' AND gc_enumSubGroup='.strPrepStr($enumHold).' ';
      }else {
         $sqlQual = '';
      }
      return($sqlQual);
   }

   function groupMembershipViaFID($enumGroupType, $lFID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->arrMemberInGroups = array();
      $lHoldMembers = array();
      $this->lNumMemInGroups   = 0;

         // map the personalized tables and custom forms into
         // the user permissions group
      $sqlQual = $this->strSQLQualUserPerms($enumGroupType);

      $sqlStr =
        "SELECT gp_lKeyID, gp_strGroupName, gp_strNotes,
           gp_bGeneric1, gp_lGeneric1
         FROM groups_child
            INNER JOIN groups_parent ON gc_lGroupID=gp_lKeyID
         WHERE  gc_lForeignID=$lFID
            $sqlQual
            AND gp_enumGroupType=".strPrepStr($enumGroupType).'
            AND gp_dteExpire > NOW()
         ORDER BY gp_strGroupName;';
      $query = $this->db->query($sqlStr);

      $this->lNumMemInGroups = $numRows = $query->num_rows();
      if ($numRows > 0) {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->arrMemberInGroups[$idx] = new stdClass;
            $mInG = &$this->arrMemberInGroups[$idx];
            $mInG->lGroupID     = $row->gp_lKeyID;
            $mInG->strGroupName = $row->gp_strGroupName;
            $mInG->strGroupNote = $row->gp_strNotes;
            $mInG->bGeneric1    = (boolean)$row->gp_bGeneric1;
            $mInG->lGeneric1    = $row->gp_lGeneric1;
            array_push($lHoldMembers, $row->gp_lKeyID);

            ++$idx;
         }
         $this->strMemListIDs = implode(',', $lHoldMembers);
      }
   }

   function strMembersInGroupDDL($strDDLName, $bAddBlank, $lMatchID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut =
         '<select name="'.$strDDLName.'">'."\n";
      if ($bAddBlank){
         $strOut .= '<option value="-1">&nbsp;</option>'."\n";
      }

      if ($this->lNumMemInGroups > 0){
         foreach ($this->arrMemberInGroups as $member){
            $lMemberID = $member->lGroupID;
            $strSel = ($lMatchID==$lMemberID ? 'selected' : '');
            $strOut .= '<option value="'.$lMemberID.'" '.$strSel.'>'.htmlspecialchars($member->strGroupName).'</option>'."\n";
         }
      }
      $strOut .= '</select>'."\n";
      return($strOut);
   }

   function loadActiveGroupsViaType(
                  $enumGroupType, $enumSort, $strExcludeIDs,
                  $bViaGroupID,   $lGroupID){
   //-----------------------------------------------------------------------
   // enumSort - groupName, ID, expire, expireDesc, dteOrigin
   //
   // $strExcludeIDs - an imploded string of IDs to exclude from the list
   //    (or blank for all active groups in the type)
   //-----------------------------------------------------------------------
      global $glChapterID;

      if (bUserPermGroupType($enumGroupType)) $enumGroupType = CENUM_CONTEXT_USER;

      $this->arrGroupList  = array();
      $this->lNumGroupList = 0;

      switch ($enumSort){
         case 'ID':
            $strSort = ' gp_lKeyID ';
            break;
         case 'expire':
            $strSort = ' gp_dteExpire, gp_lKeyID ';
            break;
         case 'expireDesc':
            $strSort = ' gp_dteExpire DESC, gp_lKeyID ';
            break;
         case 'dteOrigin':
            $strSort = ' gp_dteOrigin, gp_lKeyID ';
            break;
         case 'groupName':
         default:
            $strSort = ' gp_strGroupName, gp_lKeyID ';
            break;
      }

      if ($strExcludeIDs=='') {
         $strExclude = '';
      }else {
         $strExclude = " AND NOT (gp_lKeyID IN ($strExcludeIDs)) ";
      }

      if ($bViaGroupID){
         $strSqlExtra = " AND gp_lKeyID=$lGroupID ";
      }else {
         $strSqlExtra = '';
      }

      $sqlStr =
        'SELECT
            gp_lKeyID, gp_strGroupName, gp_bTempGroup,
            gp_strNotes,
            gp_bGeneric1,
            gp_lGeneric1, gp_dteExpire,
            UNIX_TIMESTAMP(gp_dteOrigin) AS dteOrigin
         FROM groups_parent
         WHERE  gp_enumGroupType='.strPrepStr($enumGroupType)."
            AND gp_dteExpire > NOW()
            $strExclude $strSqlExtra
         ORDER BY $strSort;";
      $query = $this->db->query($sqlStr);

      $this->lNumGroupList = $numRows = $query->num_rows();
      if ($numRows==0) {
         $this->arrGroupList[0] = new stdClass;
         $gl = &$this->arrGroupList[0];
         $gl->lKeyID       =
         $gl->strGroupName =
         $gl->bTempGroup   =

         $gl->gp_bGeneric1 =
         $gl->gp_lGeneric1 =

         $gl->strNotes     =
         $gl->dteExpire    =
         $gl->dteOrigin    = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->arrGroupList[$idx] = new stdClass;
            $gl = &$this->arrGroupList[$idx];
            $gl->lKeyID       = $row->gp_lKeyID;
            $gl->strGroupName = $row->gp_strGroupName;
            $gl->bTempGroup   = $row->gp_bTempGroup;

            $gl->gp_bGeneric1 = $row->gp_bGeneric1;
            $gl->gp_lGeneric1 = $row->gp_lGeneric1;

            $gl->strNotes     = $row->gp_strNotes;
            $gl->dteExpire    = dteMySQLDate2Unix($row->gp_dteExpire);
            $gl->dteOrigin    = $row->dteOrigin;
            ++$idx;
         }
      }
   }

   function bGroupMember($lGID, $lForeignID, $enumGroupType){
   //-----------------------------------------------------------------------
   // return true if the foreign ID is a member of the specified group
   //-----------------------------------------------------------------------
      $sqlQual = $this->strSQLQualUserPerms($enumGroupType);

      $sqlStr =
        "SELECT gc_lKeyID
         FROM groups_child
         WHERE
                gc_lGroupID=$lGID
            $sqlQual
            AND gp_enumGroupType=".strPrepStr($enumGroupType)."
            AND gc_lForeignID=$lForeignID;";

      $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      return($numRows > 0);
   }

   function lCntActiveGroupsViaType($enumGroupType){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (bUserPermGroupType($enumGroupType)) $enumGroupType = CENUM_CONTEXT_USER;

      $sqlStr =
        'SELECT DISTINCT gp_lKeyID
         FROM groups_parent
            -- INNER JOIN groups_child ON gp_lKeyID=gc_lGroupID
         WHERE gp_enumGroupType='.strPrepStr($enumGroupType)."
            -- sqlQual
            AND gp_dteExpire>NOW();";
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function clearGroupMembership(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $enumType = $this->gp_enumGroupType;
      if (is_null($this->lForeignID)
       || is_null($this->gp_enumGroupType)) screamForHelp('UNITIALIZED CLASS<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $sqlQual = $this->strSQLQualUserPerms($enumGroupType);

      $sqlStr =
        "DELETE groups_child.*
            FROM groups_child
            INNER JOIN tbl_group_parent ON gp_lKeyID=gc_lGroupID
         WHERE gc_lForeignID=$this->lForeignID
            $sqlQual
            AND gp_enumGroupType=".strPrepStr($enumType).';';
      $this->db->query($sqlStr);
   }

   function addGroupMembership(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_null($this->lForeignID) ||
          is_null($this->lGroupID  )) screamForHelp('UNINITIALIZED CLASS<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      if (is_null($this->enumSubGroup)){
         $sqlAddExtra = '';
      }else {
         $sqlAddExtra = 'gc_enumSubGroup = '.strPrepStr($this->enumSubGroup).', '."\n";
      }
      $sqlStr =
         "INSERT groups_child
          SET
             gc_lGroupID = $this->lGroupID,
             gc_lForeignID = $this->lForeignID,
             $sqlAddExtra
             gc_dteAdded = NOW()
          ON DUPLICATE KEY UPDATE gc_dteAdded=gc_dteAdded;";
      $this->db->query($sqlStr);
   }

   function removeMemberFromGroup(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_null($this->lForeignID) ||
          is_null($this->lGroupID)) screamForHelp('UNITIALIZED CLASS<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      if (is_null($this->enumSubGroup)){
         $sqlWhereExtra = '';
      }else {
         $sqlWhereExtra = ' AND gc_enumSubGroup = '.strPrepStr($this->enumSubGroup).' '."\n";
      }

      $sqlStr =
         "DELETE FROM groups_child
          WHERE gc_lGroupID=$this->lGroupID
             $sqlWhereExtra
             AND gc_lForeignID=$this->lForeignID;";
      $this->db->query($sqlStr);
   }

   function removeBlockMembersFromGroup(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_null($this->strForeignIDs) ||
          is_null($this->lGroupID)) screamForHelp('UNITIALIZED CLASS<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $sqlStr =
         "DELETE FROM groups_child
          WHERE gc_lGroupID=$this->lGroupID
             AND gc_lForeignID IN ( $this->strForeignIDs );";

      $this->db->query($sqlStr);
   }

   function removeMemFromAllGroups($enumGroupType, $lForeignID){
   //---------------------------------------------------------------------
   // useful when removing a person, business, etc
   //---------------------------------------------------------------------
      $sqlStr =
           'DELETE groups_child
            FROM groups_child
               INNER JOIN groups_parent
            WHERE gp_enumGroupType='.strPrepStr($enumGroupType)."
               AND gc_lForeignID=$lForeignID;";
      $this->db->query($sqlStr);
   }

   function loadGroupInfo($lGroupID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->groupTable = array();
      if (is_array($lGroupID)){
         if (count($lGroupID)==0){
            $strWhere = ' AND gp_lKeyID IN ( -1 ) ';
         }else {
            $strWhere = ' AND gp_lKeyID IN ('.implode(',', $lGroupID).') ';
         }
      }else {
         $strWhere = " AND gp_lKeyID=$lGroupID ";
      }
      $sqlStr =
        "SELECT
            gp_strGroupName, gp_enumGroupType,  gp_bTempGroup,
            gp_strNotes, gp_lOriginID, gp_lLastUpdateID,
            gp_dteExpire,
            UNIX_TIMESTAMP(gp_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(gp_dteLastUpdate) AS dteLastUpdate
         FROM groups_parent
         WHERE 1 $strWhere;";

      $query = $this->db->query($sqlStr);
      $this->numGroups = $numRows = $query->num_rows();
      if ($numRows==0) {
         $this->groupTable[0] = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->groupTable[$idx] = new stdClass;

            $this->groupTable[$idx]->gp_strGroupName        = $row->gp_strGroupName;
            $this->groupTable[$idx]->gp_enumGroupType       = $gp_enumGroupType = $row->gp_enumGroupType;
            $this->groupTable[$idx]->gp_bTempGroup          = $row->gp_bTempGroup;
            $this->groupTable[$idx]->gp_strNotes            = $row->gp_strNotes;
            $this->groupTable[$idx]->gp_lOriginID           = $row->gp_lOriginID;
            $this->groupTable[$idx]->gp_lLastUpdateID       = $row->gp_lLastUpdateID;
            $this->groupTable[$idx]->gp_dteExpire           = dteMySQLDate2Unix($row->gp_dteExpire);
            $this->groupTable[$idx]->gp_dteOrigin           = $row->dteOrigin;
            $this->groupTable[$idx]->gp_dteLastUpdate       = $row->dteLastUpdate;
            $this->groupTable[$idx]->strGroupCategory       = strXlateContext($gp_enumGroupType);

            ++$idx;
         }
      }
   }

   public function lAddNewGroupParent(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'INSERT INTO groups_parent
          SET '.$this->sqlCommonGroupParent().',
             gp_enumGroupType = '.strPrepStr($this->gp_enumGroupType).",
             gp_lOriginID     = $glUserID,
             gp_dteOrigin     = NOW();";

      $query = $this->db->query($sqlStr);
      $this->arrGroupList[0]->lKeyID = $this->lGroupID = $this->db->insert_id();
      return($this->lGroupID);
   }

   public function updateGroupParentRec(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'UPDATE groups_parent
          SET '.$this->sqlCommonGroupParent().'
          WHERE gp_lKeyID='.$this->arrGroupList[0]->lKeyID.';';

      $query = $this->db->query($sqlStr);
      $this->arrGroupList[0]->lKeyID = $this->lGroupID = $this->db->insert_id();
      return($this->lGroupID);
   }

   private function sqlCommonGroupParent(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $clsList = $this->arrGroupList[0];

      return(
        'gp_strGroupName  = '.strPrepStr($clsList->strGroupName).',
         gp_bTempGroup    = '.($clsList->bTempGroup ? '1' : '0').',

         gp_bGeneric1     = '.($clsList->gp_bGeneric1 ? '1' : '0').',
         gp_lGeneric1     = '.(is_null($clsList->gp_lGeneric1) ? 'null' : (int)$clsList->lGeneric1).',

         gp_dteExpire     = '.strPrepDate($clsList->dteExpire).',
         gp_strNotes      = '.strPrepStr($clsList->strNotes).",
         gp_lLastUpdateID = $glUserID ");
   }

   public function remGroup(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // children first
      $sqlStr = "DELETE FROM groups_child WHERE gc_lGroupID=$this->lGroupID;";
      $query = $this->db->query($sqlStr);

      $sqlStr = "DELETE FROM groups_parent WHERE gp_lKeyID=$this->lGroupID;";
      $query = $this->db->query($sqlStr);
   }

   public function lCountMembersInGroup($lGroupID, $enumGroupType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumGroupType){
         case CENUM_CONTEXT_BIZ:
            $sqlStr =
              "SELECT COUNT(*) AS lNumRecs
               FROM groups_child
                  INNER JOIN people_names ON pe_lKeyID=gc_lForeignID
               WHERE gc_lGroupID=$lGroupID
                  AND NOT pe_bRetired;";
            break;

         case CENUM_CONTEXT_PEOPLE:
            $sqlStr =
              "SELECT COUNT(*) AS lNumRecs
               FROM groups_child
                  INNER JOIN people_names ON pe_lKeyID=gc_lForeignID
               WHERE gc_lGroupID=$lGroupID
                  AND NOT pe_bRetired;";
            break;

         case CENUM_CONTEXT_CLIENT:
            $sqlStr =
              "SELECT COUNT(*) AS lNumRecs
               FROM groups_child
                  INNER JOIN client_records  ON cr_lKeyID      = gc_lForeignID
                  INNER JOIN client_location ON cr_lLocationID = cl_lKeyID
               WHERE gc_lGroupID=$lGroupID
                  AND NOT cr_bRetired;";
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $sqlStr =
              "SELECT COUNT(*) AS lNumRecs
               FROM groups_child
                  INNER JOIN sponsor      ON sp_lKeyID     = gc_lForeignID
                  INNER JOIN people_names ON sp_lForeignID = pe_lKeyID
               WHERE gc_lGroupID=$lGroupID
                  AND NOT pe_bRetired
                  AND NOT sp_bRetired;";
            break;

         case CENUM_CONTEXT_STAFF_TS_LOCATIONS:
         case CENUM_CONTEXT_STAFF_TS_PROJECTS:
            $sqlStr =
              "SELECT COUNT(*) AS lNumRecs
               FROM groups_child
                  INNER JOIN staff_timesheets  ON ts_lKeyID = gc_lForeignID
               WHERE gc_lGroupID=$lGroupID
                  AND NOT ts_bRetired;";
            break;

         case CENUM_CONTEXT_STAFF:
         case CENUM_CONTEXT_USER:
            $sqlStr =
              "SELECT COUNT(*) AS lNumRecs
               FROM groups_child
                  INNER JOIN admin_users      ON us_lKeyID     = gc_lForeignID
               WHERE gc_lGroupID=$lGroupID
                  AND NOT us_bInactive;";
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            $sqlStr =
              "SELECT COUNT(*) AS lNumRecs
               FROM groups_child
                  INNER JOIN volunteers   ON vol_lKeyID=gc_lForeignID
                  INNER JOIN people_names ON pe_lKeyID=vol_lPeopleID
               WHERE gc_lGroupID=$lGroupID
                  AND NOT pe_bRetired;";
            break;

         default:
            screamForHelp($enumGroupType.': group type not yet available<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
            break;
      }

      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return($row->lNumRecs);
   }

   public function loadGroupMembership($enumGroupType, $lGID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->groupMembers = array();
      $this->lCntMembersInGroup = 0;
      $this->groupMemLabels = new stdClass;

      if (is_array($lGID)){
         $strGroupWhere = ' gc_lGroupID IN ('.implode(',', $lGID).') ';
      }else {
         $strGroupWhere = " gc_lGroupID=$lGID ";
      }

      switch ($enumGroupType){
         case CENUM_CONTEXT_BIZ:
            $this->groupMemLabels->strName    = 'Business/Organization';
            $this->groupMemLabels->strAddress = 'Address';
            $this->groupMemLabels->strKey     = 'Business ID';
            $sqlStr =
              "SELECT
                  gc_lForeignID AS lKeyID, gc_dteAdded,
                  pe_strLName AS strName,
                  CONCAT(pe_strAddr1, '\\n',
                         pe_strAddr2, '\\n',
                         pe_strCity,  '\\n',
                         pe_strState,' ', pe_strZip, ' ', pe_strCountry) AS strAddress
               FROM groups_child
                  INNER JOIN people_names ON pe_lKeyID=gc_lForeignID
               WHERE $strGroupWhere
                  AND NOT pe_bRetired
               ORDER BY pe_strLName, gc_lForeignID;";
            break;

         case CENUM_CONTEXT_PEOPLE:
            $this->groupMemLabels->strName    = 'Name';
            $this->groupMemLabels->strAddress = 'Address';
            $this->groupMemLabels->strKey     = 'People ID';
            $sqlStr =
              "SELECT
                  gc_lForeignID AS lKeyID, gc_dteAdded,
                  CONCAT(pe_strLName, ', ', pe_strFName) AS strName,
                  CONCAT(pe_strAddr1, '\\n',
                         pe_strAddr2, '\\n',
                         pe_strCity,  '\\n',
                         pe_strState,' ', pe_strZip, ' ', pe_strCountry) AS strAddress
               FROM groups_child
                  INNER JOIN people_names ON pe_lKeyID=gc_lForeignID
               WHERE $strGroupWhere
                  AND NOT pe_bRetired
               ORDER BY pe_strLName, pe_strFName, gc_lForeignID;";
            break;

         case CENUM_CONTEXT_CLIENT:
            $this->groupMemLabels->strName    = 'Client';
            $this->groupMemLabels->strAddress = 'Location';
            $this->groupMemLabels->strKey     = 'client ID';
            $sqlStr =
              "SELECT
                  gc_lForeignID AS lKeyID, gc_dteAdded,
                  CONCAT(cr_strLName, ', ', cr_strFName) AS strName,
                  cl_strLocation AS strAddress
               FROM groups_child
                  INNER JOIN client_records  ON cr_lKeyID      = gc_lForeignID
                  INNER JOIN client_location ON cr_lLocationID = cl_lKeyID
               WHERE $strGroupWhere
                  AND NOT cr_bRetired
               ORDER BY cr_strLName, cr_strFName, gc_lForeignID;";
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $this->groupMemLabels->strName    = 'Sponsor';
            $this->groupMemLabels->strAddress = 'Address';
            $this->groupMemLabels->strKey     = 'sponsor ID';
            $sqlStr =
              "SELECT
                  gc_lForeignID AS lKeyID, gc_dteAdded,
                  CONCAT(pe_strLName, ', ', pe_strFName) AS strName,
                  CONCAT(pe_strAddr1, '\\n',
                         pe_strAddr2, '\\n',
                         pe_strCity,  '\\n',
                         pe_strState,' ', pe_strZip, ' ', pe_strCountry) AS strAddress
               FROM groups_child
                  INNER JOIN sponsor      ON sp_lKeyID     = gc_lForeignID
                  INNER JOIN people_names ON sp_lForeignID = pe_lKeyID
               WHERE $strGroupWhere
                  AND NOT pe_bRetired
                  AND NOT sp_bRetired
               ORDER BY pe_strLName, pe_strFName, gc_lForeignID;";
            break;

         case CENUM_CONTEXT_STAFF_TS_LOCATIONS:
         case CENUM_CONTEXT_STAFF_TS_PROJECTS:
            $this->groupMemLabels->strName    = 'Time Sheet Templates';
            $this->groupMemLabels->strAddress = 'N/A';
            $this->groupMemLabels->strKey     = 'template ID';
            $sqlStr =
              "SELECT
                  gc_lForeignID AS lKeyID, gc_dteAdded,
                  ts_strTSName AS strName,
                  'n/a' AS strAddress
               FROM groups_child
                  INNER JOIN staff_timesheets  ON ts_lKeyID = gc_lForeignID
               WHERE $strGroupWhere
                  AND NOT ts_bRetired
               ORDER BY ts_strTSName, gc_lForeignID;";
            break;

         case CENUM_CONTEXT_STAFF:
         case CENUM_CONTEXT_USER:
            $this->groupMemLabels->strName    = 'User';
            $this->groupMemLabels->strAddress = 'Address';
            $this->groupMemLabels->strKey     = 'user ID';
            $sqlStr =
              "SELECT
                  gc_lForeignID AS lKeyID, gc_dteAdded,
                  CONCAT(us_strLastName, ', ', us_strFirstName) AS strName,
                  CONCAT(us_strAddr1, '\\n',
                         us_strAddr2, '\\n',
                         us_strCity,  '\\n',
                         us_strState,' ', us_strZip, ' ', us_strCountry) AS strAddress
               FROM groups_child
                  INNER JOIN admin_users      ON us_lKeyID     = gc_lForeignID
                  -- INNER JOIN people_names ON sp_lForeignID = pe_lKeyID
               WHERE $strGroupWhere
                  AND NOT us_bInactive
               ORDER BY us_strLastName, us_strFirstName, gc_lForeignID;";
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            $this->groupMemLabels->strName    = 'Name';
            $this->groupMemLabels->strAddress = 'Address';
            $this->groupMemLabels->strKey     = 'vol ID';
            $sqlStr =
              "SELECT
                  gc_lForeignID AS lKeyID, gc_dteAdded,
                  CONCAT(pe_strLName, ', ', pe_strFName) AS strName,
                  CONCAT(pe_strAddr1, '\\n',
                         pe_strAddr2, '\\n',
                         pe_strCity,  '\\n',
                         pe_strState,' ', pe_strZip, ' ', pe_strCountry) AS strAddress
               FROM groups_child
                  INNER JOIN volunteers   ON vol_lKeyID=gc_lForeignID
                  INNER JOIN people_names ON pe_lKeyID=vol_lPeopleID
               WHERE $strGroupWhere
                  AND NOT pe_bRetired
               ORDER BY pe_strLName, pe_strFName, gc_lForeignID;";
            break;

         default:
            screamForHelp($enumGroupType.': group type not yet available<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
            break;
      }

      $query = $this->db->query($sqlStr);
      $this->lCntMembersInGroup = $numRows = $query->num_rows();
      if ($numRows > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->groupMembers[$idx] = new stdClass;
            $this->groupMembers[$idx]->lKeyID     = $lKeyID = (int)$row->lKeyID;
            $this->groupMembers[$idx]->dteAdded   = dteMySQLDate2Unix($row->gc_dteAdded);
            $this->groupMembers[$idx]->strAddress = str_replace("\n\n", "\n", $row->strAddress);
            $this->groupMembers[$idx]->strName    = $strName = trim($row->strName);
            $lNameLenM1 = strlen($strName)-1;
            if (substr($strName, $lNameLenM1, 1)==','){   // business sponsorships; remove comma from name
               $this->groupMembers[$idx]->strName = substr($strName, 0, $lNameLenM1);
            }
            switch ($enumGroupType){
               case CENUM_CONTEXT_BIZ:
                  $this->groupMembers[$idx]->strLinkView = strLinkView_BizRecord($lKeyID, 'View business record', true, '');
                  break;
               case CENUM_CONTEXT_CLIENT:
                  $this->groupMembers[$idx]->strLinkView = strLinkView_ClientRecord($lKeyID, 'View client record', true, '');
                  break;
               case CENUM_CONTEXT_PEOPLE:
                  $this->groupMembers[$idx]->strLinkView = strLinkView_PeopleRecord($lKeyID, 'View people record', true, '');
                  break;
               case CENUM_CONTEXT_SPONSORSHIP:
                  $this->groupMembers[$idx]->strLinkView = strLinkView_Sponsorship($lKeyID, 'View sponsorship record', true, '');
                  break;
               case CENUM_CONTEXT_STAFF_TS_LOCATIONS:
               case CENUM_CONTEXT_STAFF_TS_PROJECTS:
                  $this->groupMembers[$idx]->strLinkView = strLinkView_TimeSheetTemplateRecord($lKeyID, 'View timesheet template', true);
                  break;
               case CENUM_CONTEXT_VOLUNTEER:
                  $this->groupMembers[$idx]->strLinkView = strLinkView_Volunteer($lKeyID, 'View volunteer record', true, '');
                  break;
               case CENUM_CONTEXT_STAFF:
               case CENUM_CONTEXT_USER:
                  $this->groupMembers[$idx]->strLinkView = strLinkView_User($lKeyID, 'View user record', true, '');
                  break;

               default:
                  screamForHelp($enumGroupType.': group type not yet available<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
                  break;
            }
            ++$idx;
         }
      }
   }


      /* -----------------------------------------------------------------------
                                 R E P O R T S
      --------------------------------------------------------------------------*/
   function lNumRecsInReport($enumContext, &$groupIDs, $bShowAny, $bUseLimits, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strGroupList = implode(',', $groupIDs);
      $lNumGroups   = count($groupIDs);
      if ($lNumGroups==0) $strGroupList = ' -1 ';

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      if ($bShowAny){
         switch ($enumContext){
            case CENUM_CONTEXT_BIZ:
            case CENUM_CONTEXT_PEOPLE:
               $sqlStr =
                 'SELECT DISTINCT pe_lKeyID
                  FROM people_names
                     INNER JOIN groups_child  ON pe_lKeyID=gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID=gp_lKeyID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT pe_bRetired
                     AND '.($enumContext==CENUM_CONTEXT_PEOPLE ? ' NOT ' : '').'pe_bBiz '
                  .$strLimit.';';
               break;

            case CENUM_CONTEXT_SPONSORSHIP:
               $sqlStr =
                 'SELECT DISTINCT sp_lKeyID
                  FROM sponsor
                     INNER JOIN groups_child  ON sp_lKeyID   = gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID = gp_lKeyID
                     INNER JOIN people_names  ON pe_lKeyID   = sp_lForeignID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT pe_bRetired
                     AND NOT sp_bRetired '
                  .$strLimit.';';
               break;

            case CENUM_CONTEXT_VOLUNTEER:
               $sqlStr =
                 'SELECT DISTINCT vol_lKeyID
                  FROM volunteers
                     INNER JOIN groups_child  ON vol_lKeyID  = gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID = gp_lKeyID
                     INNER JOIN people_names  ON pe_lKeyID   = vol_lPeopleID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT vol_bInactive
                     AND NOT vol_bRetired
                     AND NOT pe_bRetired '
                  .$strLimit.';';
               break;

            case CENUM_CONTEXT_CLIENT:
               $sqlStr =
                 'SELECT DISTINCT cr_lKeyID
                  FROM client_records
                     INNER JOIN groups_child  ON cr_lKeyID  = gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID = gp_lKeyID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT cr_bRetired '
                  .$strLimit.';';
               break;

            default:
               screamForHelp($enumContext.': group type not yet available<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
               break;
            }
      }else {
            // member of all selected groups
         switch ($enumContext){
            case CENUM_CONTEXT_BIZ:
            case CENUM_CONTEXT_PEOPLE:
               $sqlStr =
                 'SELECT pe_lKeyID, COUNT(pe_lKeyID) AS lNumRecs
                  FROM people_names
                     INNER JOIN groups_child  ON pe_lKeyID=gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID=gp_lKeyID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND '.($enumContext==CENUM_CONTEXT_PEOPLE ? ' NOT ' : '').'pe_bBiz
                     AND NOT pe_bRetired
                  GROUP BY pe_lKeyID
                  HAVING COUNT(pe_lKeyID)='.$lNumGroups.'
                  ORDER BY pe_lKeyID '
                  .$strLimit.';';
               break;

            case CENUM_CONTEXT_VOLUNTEER:
               $sqlStr =
                 'SELECT vol_lKeyID, COUNT(vol_lKeyID) AS lNumRecs
                  FROM volunteers
                     INNER JOIN groups_child  ON vol_lKeyID  = gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID = gp_lKeyID
                     INNER JOIN people_names  ON pe_lKeyID   = vol_lPeopleID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT pe_bRetired
                     AND NOT vol_bInactive
                     AND NOT vol_bRetired
                  GROUP BY vol_lKeyID
                  HAVING COUNT(vol_lKeyID)='.$lNumGroups.'
                  ORDER BY vol_lKeyID '
                  .$strLimit.';';
               break;

            case CENUM_CONTEXT_SPONSORSHIP:
               $sqlStr =
                 'SELECT sp_lKeyID, COUNT(sp_lKeyID) AS lNumRecs
                  FROM sponsor
                     INNER JOIN groups_child  ON sp_lKeyID   = gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID = gp_lKeyID
                     INNER JOIN people_names  ON pe_lKeyID   = sp_lForeignID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT pe_bRetired
                     AND NOT sp_bRetired
                  GROUP BY sp_lKeyID
                  HAVING COUNT(sp_lKeyID)='.$lNumGroups.'
                  ORDER BY sp_lKeyID '
                  .$strLimit.';';
               break;

            case CENUM_CONTEXT_CLIENT:
               $sqlStr =
                 'SELECT cr_lKeyID, COUNT(cr_lKeyID) AS lNumRecs
                  FROM client_records
                     INNER JOIN groups_child  ON cr_lKeyID=gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID=gp_lKeyID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT cr_bRetired
                  GROUP BY cr_lKeyID
                  HAVING COUNT(cr_lKeyID)='.$lNumGroups.'
                  ORDER BY cr_lKeyID '
                  .$strLimit.';';
               break;

            default:
               screamForHelp($enumContext.': group type not yet available<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
               break;
         }
      }
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strGroupReportPage($enumContext, &$groupIDs, $bShowAny,
                               $bReport,     $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->db->query('DROP TABLE IF EXISTS tmpGroupMatch;');

      $strOut = $strExport = '';
      $strLabel = strLabelViaContextType($enumContext, true, true);
      $strOut   = $strLabel.' who belong to <b>'.($bShowAny ? 'any' : 'all')
                  .'</b> of the following groups:<br>';
      $this->loadGroupInfo($groupIDs);
      $strGroupsReviewed = '';
      if ($this->numGroups > 0){
         if ($bReport) $strOut .= '<ul>';
         foreach($this->groupTable as $grp){
            if ($bReport){
               $strOut .= '<li>'.htmlspecialchars($grp->gp_strGroupName).'</li>';
            }else {
               $strGroupsReviewed .= "\n* ".$grp->gp_strGroupName;
            }
         }
         if ($bReport) $strOut .= '</ul>';
      }

         // create temporary table to hold foreign ID of report results
      $sqlStr =
           'CREATE TEMPORARY TABLE tmpGroupMatch (
              gm_lKeyID int(11) NOT NULL AUTO_INCREMENT,
              gm_lForeignID int(11) NOT NULL DEFAULT \'0\',
              PRIMARY KEY (gm_lKeyID),
              KEY gm_lForeignID (gm_lForeignID)
            ) ENGINE=MyISAM;';
      $this->db->query($sqlStr);
      $strGroupList = implode(',', $groupIDs);
      $lNumGroups   = count($groupIDs);
      if ($lNumGroups==0) $strGroupList = ' -1 ';
      if ($bReport){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      if ($bShowAny){
         switch ($enumContext){
            case CENUM_CONTEXT_BIZ:
            case CENUM_CONTEXT_PEOPLE:
               $sqlSelect =
                 'SELECT DISTINCT pe_lKeyID AS lForeignID
                  FROM people_names
                     INNER JOIN groups_child  ON pe_lKeyID=gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID=gp_lKeyID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT pe_bRetired
                     AND '.($enumContext==CENUM_CONTEXT_PEOPLE ? ' NOT ' : '').'pe_bBiz
                  ORDER BY pe_strLName, pe_strFName, pe_lKeyID '
                  .$strLimit.';';
               break;

            case CENUM_CONTEXT_VOLUNTEER:
               $sqlSelect =
                 'SELECT DISTINCT vol_lKeyID AS lForeignID
                  FROM volunteers
                     INNER JOIN groups_child  ON vol_lKeyID  = gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID = gp_lKeyID
                     INNER JOIN people_names  ON pe_lKeyID   = vol_lPeopleID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT pe_bRetired
                     AND NOT vol_bRetired
                     AND NOT vol_bInactive
                  ORDER BY pe_strLName, pe_strFName, pe_lKeyID '
                  .$strLimit.';';
               break;

            case CENUM_CONTEXT_CLIENT:
               $sqlSelect =
                 'SELECT DISTINCT cr_lKeyID AS lForeignID
                  FROM client_records
                     INNER JOIN groups_child  ON cr_lKeyID   = gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID = gp_lKeyID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT cr_bRetired
                  ORDER BY cr_strLName, cr_strFName, cr_lKeyID '
                  .$strLimit.';';
               break;

            case CENUM_CONTEXT_SPONSORSHIP:
               $sqlSelect =
                 'SELECT DISTINCT sp_lKeyID AS lForeignID
                  FROM sponsor
                     INNER JOIN groups_child  ON sp_lKeyID   = gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID = gp_lKeyID
                     INNER JOIN people_names  ON pe_lKeyID   = sp_lForeignID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT pe_bRetired
                     AND NOT sp_bRetired
                  ORDER BY pe_strLName, pe_strFName, pe_lKeyID '
                  .$strLimit.';';
               break;

            default:
               screamForHelp($enumContext.': group type not yet available<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
               break;
            }
      }else {
            // member of all selected groups
         switch ($enumContext){
            case CENUM_CONTEXT_BIZ:
            case CENUM_CONTEXT_PEOPLE:
               $sqlSelect =
                 'SELECT pe_lKeyID AS lForeignID
                  FROM people_names
                     INNER JOIN groups_child  ON pe_lKeyID=gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID=gp_lKeyID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND '.($enumContext==CENUM_CONTEXT_PEOPLE ? ' NOT ' : '').'pe_bBiz
                     AND NOT pe_bRetired
                  GROUP BY pe_lKeyID
                  HAVING COUNT(pe_lKeyID)='.$lNumGroups.'
                  ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID '
                  .$strLimit.';';
               break;

            case CENUM_CONTEXT_VOLUNTEER:
               $sqlSelect =
                 'SELECT vol_lKeyID AS lForeignID
                  FROM volunteers
                     INNER JOIN groups_child  ON vol_lKeyID  = gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID = gp_lKeyID
                     INNER JOIN people_names  ON pe_lKeyID   = vol_lPeopleID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT pe_bRetired
                     AND NOT vol_bRetired
                     AND NOT vol_bInactive
                  GROUP BY vol_lKeyID
                  HAVING COUNT(vol_lKeyID)='.$lNumGroups.'
                  ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID '
                  .$strLimit.';';
               break;

            case CENUM_CONTEXT_SPONSORSHIP:
               $sqlSelect =
                 'SELECT sp_lKeyID AS lForeignID
                  FROM sponsor
                     INNER JOIN groups_child  ON sp_lKeyID   = gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID = gp_lKeyID
                     INNER JOIN people_names  ON pe_lKeyID   = sp_lForeignID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT pe_bRetired
                     AND NOT sp_bRetired
                  GROUP BY sp_lKeyID
                  HAVING COUNT(sp_lKeyID)='.$lNumGroups.'
                  ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID '
                  .$strLimit.';';
               break;

            case CENUM_CONTEXT_CLIENT:
               $sqlSelect =
                 'SELECT cr_lKeyID AS lForeignID
                  FROM client_records
                     INNER JOIN groups_child  ON cr_lKeyID  = gc_lForeignID
                     INNER JOIN groups_parent ON gc_lGroupID=gp_lKeyID
                  WHERE gp_enumGroupType='.strPrepStr($enumContext).'
                     AND gp_lKeyID IN ('.$strGroupList.')
                     AND NOT cr_bRetired
                  GROUP BY cr_lKeyID
                  HAVING COUNT(cr_lKeyID)='.$lNumGroups.'
                  ORDER BY cr_strLName, cr_strFName, cr_lKeyID '
                  .$strLimit.';';
               break;

            default:
               screamForHelp($enumContext.': group type not yet available<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
               break;
         }
      }

      $sqlStr =
         "INSERT INTO tmpGroupMatch
            (gm_lForeignID)
            $sqlSelect;";
      $query = $this->db->query($sqlStr);
      $lNumRows = $this->db->affected_rows();

      if ($lNumRows==0){
         return($strOut.'<br><br><i>There are no records that match your search criteria.</i>');
      }

      if ($bReport){
         if ($enumContext==CENUM_CONTEXT_CLIENT){
            $strOut .= $this->strClientBasedGroupRptHTML();
         }else {
            $strOut .= $this->strPeopleBasedGroupRptHTML($enumContext);
         }
         return($strOut);
      }else {
         switch ($enumContext){
            case CENUM_CONTEXT_PEOPLE:
               $strExport = $this->strGroupRptExport_PeopleBiz(false, $strGroupsReviewed, $bShowAny);
               break;

            case CENUM_CONTEXT_CLIENT:
               $strExport = $this->strGroupRptExport_Client($strGroupsReviewed, $bShowAny);
               break;

            case CENUM_CONTEXT_VOLUNTEER:
               $strExport = $this->strGroupRptExport_Vol($strGroupsReviewed, $bShowAny);
               break;

            case CENUM_CONTEXT_SPONSORSHIP:
               $strExport = $this->strGroupRptExport_Sponsor($strGroupsReviewed, $bShowAny);
               break;

            case CENUM_CONTEXT_BIZ:
               $strExport = $this->strGroupRptExport_PeopleBiz(true, $strGroupsReviewed, $bShowAny);
               break;
            default:
               screamForHelp($enumContext.': group type not yet available<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
               break;
         }

         return($strExport);
      }
   }

   private function strGroupRptExport_Sponsor(&$strGroupsReviewed, $bShowAny){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $sqlStr =
         'SELECT '.strExportFields_Sponsor().'
          FROM tmpGroupMatch
             INNER JOIN sponsor                    ON sp_lKeyID            = gm_lForeignID
             INNER JOIN people_names AS sponpeep   ON sponpeep.pe_lKeyID   = sp_lForeignID
             INNER JOIN lists_sponsorship_programs ON sp_lSponsorProgramID = sc_lKeyID
             INNER JOIN admin_aco AS commitACO     ON commitACO.aco_lKeyID = sp_lCommitmentACO
             LEFT  JOIN client_records             ON cr_lKeyID            = sp_lClientID
             LEFT  JOIN client_location            ON cr_lLocationID       = cl_lKeyID
             LEFT  JOIN lists_client_vocab         ON cv_lKeyID            = cr_lVocID
             LEFT  JOIN people_names AS honpeep    ON honpeep.pe_lKeyID    = sp_lHonoreeID
             LEFT  JOIN lists_generic AS atab      ON sp_lAttributedTo     = lgen_lKeyID
          ORDER BY gm_lKeyID;';
      $query = $this->db->query($sqlStr);
      $rptExport = $this->dbutil->csv_from_result($query);
      if ($this->config->item('dl_addExportRptInfo')){
         $rptExport .=
             strPrepStr(
                  CS_PROGNAME." export\n"
                 .'Created '.date($genumDateFormat.' H:i:s e')."\n"
                 .'Sponsors who are in '.($bShowAny ? 'any' : 'all')
                 .' of these groups: '.$strGroupsReviewed, null, '"');
      }
      return($rptExport);
   }

   private function strGroupRptExport_Vol(&$strGroupsReviewed, $bShowAny){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $sqlStr =
         'SELECT '.strExportFields_Vol().'
          FROM tmpGroupMatch
             INNER JOIN volunteers              ON vol_lKeyID=gm_lForeignID
             INNER JOIN people_names AS peepTab ON peepTab.pe_lKeyID   = vol_lPeopleID
          ORDER BY gm_lKeyID;';
      $query = $this->db->query($sqlStr);
      $rptExport = $this->dbutil->csv_from_result($query);

      if ($this->config->item('dl_addExportRptInfo')){
         $rptExport .=
             strPrepStr(
                  CS_PROGNAME." export\n"
                 .'Created '.date($genumDateFormat.' H:i:s e')."\n"
                 .'Volunteers who are in '.($bShowAny ? 'any' : 'all')
                 .' of these groups: '.$strGroupsReviewed, null, '"');
      }
      return($rptExport);
   }

   private function strGroupRptExport_PeopleBiz($bBiz, &$strGroupsReviewed, $bShowAny){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      if ($bBiz){
         $strFields = strExportFields_Biz();
         $strTableAlias = 'bizTab';
      }else {
         $strFields = strExportFields_People();
         $strTableAlias = 'peepTab';
      }
      $sqlStr =
         "SELECT $strFields
          FROM tmpGroupMatch
             INNER JOIN people_names AS $strTableAlias ON $strTableAlias.pe_lKeyID=gm_lForeignID
          ORDER BY gm_lKeyID;";
      $query = $this->db->query($sqlStr);
      $rptExport = $this->dbutil->csv_from_result($query);
      if ($this->config->item('dl_addExportRptInfo')){
         $rptExport .=
             strPrepStr(
                  CS_PROGNAME." export\n"
                 .'Created '.date($genumDateFormat.' H:i:s e')."\n"
                 .($bBiz ? 'Businesses/Organizations' : 'People')
                 .' who are in '.($bShowAny ? 'any' : 'all').' of these groups: '.$strGroupsReviewed,
                 null, '"');
      }
      return($rptExport);
   }

   private function strGroupRptExport_Client(&$strGroupsReviewed, $bShowAny){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $sqlStr =
         'SELECT '.strExportFields_Client().'
          FROM tmpGroupMatch
             INNER JOIN client_records              ON cr_lKeyID              = gm_lForeignID
             INNER JOIN client_location             ON cr_lLocationID         = cl_lKeyID
             INNER JOIN client_status_cats          ON cr_lStatusCatID        = csc_lKeyID
             INNER JOIN lists_client_vocab          ON cr_lVocID              = cv_lKeyID
             INNER JOIN client_status               ON csh_lClientID          = cr_lKeyID
             INNER JOIN lists_client_status_entries ON csh_lStatusID          = cst_lKeyID
             LEFT  JOIN lists_generic               ON cr_lAttributedTo       = lgen_lKeyID
          ORDER BY gm_lKeyID;';
      $query = $this->db->query($sqlStr);
      $rptExport = $this->dbutil->csv_from_result($query);
      if ($this->config->item('dl_addExportRptInfo')){
         $rptExport .=
             strPrepStr(
                  CS_PROGNAME." export\n"
                 .'Created '.date($genumDateFormat.' H:i:s e')."\n"
                 .'Clients who are in '.($bShowAny ? 'any' : 'all')
                 .' of these groups: '.$strGroupsReviewed, null, '"');
      }
      return($rptExport);
   }

   private function strClientBasedGroupRptHTML(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glclsDTDateFormat;
      $clsDateTime = new dl_date_time;

      $strOut =
              '<table class="enpRptC">
                 <tr>
                    <td class="enpRptLabel">clientID</td>
                    <td class="enpRptLabel">Name</td>
                    <td class="enpRptLabel">Location</td>
                    <td class="enpRptLabel">Age/Gender</td>
                    <td class="enpRptLabel">Group Membership</td>
                 </tr>'."\n";

      $sqlStr =
         "SELECT
            cr_lKeyID, cr_strFName, cr_strLName,
            cr_dteBirth, cr_enumGender, cr_lLocationID,
            cl_strLocation
          FROM tmpGroupMatch
            INNER JOIN client_records ON cr_lKeyID=gm_lForeignID
            INNER JOIN client_location ON cr_lLocationID=cl_lKeyID
          ORDER BY gm_lKeyID;";
      $query = $this->db->query($sqlStr);
      foreach ($query->result() as $row){
         $lFID = $row->cr_lKeyID;
         $this->groupMembershipViaFID(CENUM_CONTEXT_CLIENT, $lFID);
         $strGroupList = '<ul style="list-style-type: square; display:inline; margin-left: 0; padding-left: 0;">';
         foreach ($this->arrMemberInGroups as $grpMember){
            $strGroupList .= '<li style="margin-left: 20px; padding-left: 3px;">'
                .htmlspecialchars($grpMember->strGroupName).'</li>';
         }
         $strGroupList .= '</ul>';

         $strAgeBDay = $clsDateTime->strPeopleAge(0, $row->cr_dteBirth, $lAgeYears, $glclsDTDateFormat);
         $strOut .=
              '<tr>
                  <td class="enpRpt" style="width: 65px;">'
                     .strLinkView_ClientRecord($lFID, 'View client record', true).'&nbsp;'
                     .str_pad($lFID, 5, '0', STR_PAD_LEFT).'
                  </td>
                  <td class="enpRpt" style="width: 160px;">'
                     .htmlspecialchars($row->cr_strLName.', '.$row->cr_strFName).'
                  </td>
                  <td class="enpRpt" style="width: 200px;">'
                       .htmlspecialchars($row->cl_strLocation).'
                  </td>
                  <td class="enpRpt" style="width: 150px;">'
                     .$strAgeBDay.'<br>'.$row->cr_enumGender.'
                  </td>
                  <td class="enpRpt" style="width: 200px;">'
                     .$strGroupList.'
                  </td>
               </tr>'."\n";
         $strOut .= '</tr>'."\n";
      }

      $strOut .= '</table>'."\n";
      return($strOut);

   }

   private function strPeopleBasedGroupRptHTML($enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumContext){
         case CENUM_CONTEXT_PEOPLE:
            $strIDLabel = 'PeopleID';
            $strInner   = ' INNER JOIN people_names ON pe_lKeyID=gm_lForeignID ';
            $strFID     = ' pe_lKeyID AS lFID ';
            break;

         case CENUM_CONTEXT_BIZ:
            $strIDLabel = 'businessID';
            $strInner   = ' INNER JOIN people_names ON pe_lKeyID=gm_lForeignID ';
            $strFID     = ' pe_lKeyID AS lFID ';
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            $strIDLabel = 'volunteerID';
            $strInner   = ' INNER JOIN volunteers   ON vol_lKeyID    = gm_lForeignID '
                         .' INNER JOIN people_names ON vol_lPeopleID = pe_lKeyID ';
            $strFID     = ' pe_lKeyID, vol_lKeyID AS lFID ';
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $strIDLabel = 'sponsorID';
            $strInner   = ' INNER JOIN sponsor   ON sp_lKeyID        = gm_lForeignID '
                         .' INNER JOIN people_names ON sp_lForeignID = pe_lKeyID ';
            $strFID     = ' pe_lKeyID, sp_lKeyID AS lFID ';
            break;
         default:
            screamForHelp($enumContext.': group type not yet available<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
            break;
      }

      $strOut =
              '<table class="enpRptC">
                 <tr>
                    <td class="enpRptLabel">'
                       .$strIDLabel.'
                    </td>
                    <td class="enpRptLabel">
                       Name
                    </td>
                    <td class="enpRptLabel">
                       Address
                    </td>
                    <td class="enpRptLabel">
                       Phone/Email
                    </td>
                    <td class="enpRptLabel">
                       Group Membership
                    </td>
                 </tr>'."\n";
      $sqlStr =
         "SELECT
             $strFID,
             pe_strLName, pe_strFName, pe_strAddr1, pe_strAddr2,
             pe_strCity, pe_strState, pe_strCountry, pe_strZip, pe_strPhone, pe_strCell, pe_strEmail
          FROM tmpGroupMatch
             $strInner
          ORDER BY gm_lKeyID;";
      $query = $this->db->query($sqlStr);
      foreach ($query->result() as $row){
         $lFID = $row->lFID;
         if ($row->pe_strEmail.'' == ''){
            $strEmail = '';
         }else {
            $strEmail = '<br>'.mailto($row->pe_strEmail, $row->pe_strEmail);
         }

         $this->groupMembershipViaFID($enumContext, $lFID);
            /*
               tip for preventing that pesky line break before a list
               http://stackoverflow.com/questions/1682873/how-do-i-prevent-a-line-break-occurring-before-an-unordered-list
               ul.errorlist {list-style-type: none; display:inline; margin-left: 0; padding-left: 0;}
               ul.errorlist li {display: inline; color:red; font-size: 0.8em; margin-left: 0px; padding-left: 10px;}
            */

         $strGroupList = '<ul style="list-style-type: square; display:inline; margin-left: 0; padding-left: 0;">';
         foreach ($this->arrMemberInGroups as $grpMember){
            $strGroupList .= '<li style="margin-left: 20px; padding-left: 3px;">'
                .htmlspecialchars($grpMember->strGroupName).'</li>';
         }
         $strGroupList .= '</ul>';

         switch ($enumContext){
            case CENUM_CONTEXT_PEOPLE:
               $strLink = strLinkView_PeopleRecord($lFID, 'View people record', true).'&nbsp;';
               break;
            case CENUM_CONTEXT_BIZ:
               $strLink = strLinkView_BizRecord($lFID, 'View people record', true).'&nbsp;';
               break;
            case CENUM_CONTEXT_VOLUNTEER:
               $strLink = strLinkView_Volunteer($lFID, 'View volunteer record', true).'&nbsp;';
               break;
            case CENUM_CONTEXT_SPONSORSHIP:
               $strLink = strLinkView_Sponsorship($lFID, 'View sponsor record', true).'&nbsp;';
               break;
            default:
               screamForHelp($enumContext.': group type not yet available<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
               break;
         }
         $bBiz = $enumContext==CENUM_CONTEXT_BIZ;
         $strOut .=
              '<tr class="makeStripe">
                  <td class="enpRpt" style="width: 65px;">'
                     .$strLink
                     .str_pad($lFID, 5, '0', STR_PAD_LEFT).'
                  </td>
                  <td class="enpRpt" style="width: 160px;">'
                     .htmlspecialchars($row->pe_strLName.($bBiz ? '' : ', '.$row->pe_strFName)).'
                  </td>
                  <td class="enpRpt" style="width: 200px;">'
                       .strBuildAddress(
                                 $row->pe_strAddr1, $row->pe_strAddr2,   $row->pe_strCity,
                                 $row->pe_strState, $row->pe_strCountry, $row->pe_strZip,
                                 true).'
                  </td>
                  <td class="enpRpt" style="width: 120px;">'
                     .htmlspecialchars(strPhoneCell($row->pe_strPhone, $row->pe_strCell)).$strEmail.'
                  </td>
                  <td class="enpRpt" style="width: 200px;">'
                     .$strGroupList.'
                  </td>
               </tr>'."\n";
         $strOut .= '</tr>'."\n";
      }

      $strOut .= '</table>'."\n";
      return($strOut);
   }

   function strDDLActiveGroupEntries($strSelectName, $enumGroupType, $lMatchID,
                            $bAddBlank, $bMulti=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut =
         '<select name="'.$strSelectName.($bMulti ? '[]' : '').'" '
                  .($bMulti ? 'multiple size="5" ' : '').'>'."\n";
      if ($bAddBlank){
         $strOut .= '<option value="-1">&nbsp;</option>'."\n";
      }

      $this->loadActiveGroupsViaType(
                  $enumGroupType, 'groupName', '',
                  false,   null);

      if ($this->lNumGroupList > 0){
         foreach ($this->arrGroupList as $listEntry){
            $lListID = $listEntry->lKeyID;
            if ($bMulti){
               if (in_array($lListID, $lMatchID)){
                  $strSel = ' selected ';
               }else {
                  $strSel = '';
               }
            }else {
               $strSel = ($lMatchID==$lListID ? 'selected' : '');
            }
            $strOut .= '<option value="'.$lListID.'" '.$strSel.'>'.htmlspecialchars($listEntry->strGroupName).'</option>'."\n";
         }
      }

      $strOut .= '</select>'."\n";
      return($strOut);
   }

}
?>
