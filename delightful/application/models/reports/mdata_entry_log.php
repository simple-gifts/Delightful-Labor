<?php
/*---------------------------------------------------------------------
// copyright (c) 2014 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('reports/mdata_entry_log', 'cde');
---------------------------------------------------------------------*/


class mdata_entry_log extends CI_Model{
   public
      $lNumEntries, $entries, $lNumStaffGroups, $staffGroups,
      $lTotClientCnt,
      $cgroups;

   public $ccprogs, $lTotEnrollAttendCnt;

	function __construct(){
		parent::__construct();
      $this->lNumEntries     =
      $this->lNumStaffGroups = 0;

      $this->entries     =
      $this->staffGroups = null;
      $this->cgroups     = new mgroups;
	}

   function loadDataEntryStats(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
       $enumRptSource = $sRpt->enumSource;
       $enumGroup     = $sRpt->enumGroup;

       $strDateRange = $sRpt->strDateRange;
       $strBetween   = $sRpt->strBetween;

      if ($enumGroup == 'staffGroup'){
         $this->loadStaffGroups();
      }

      switch ($enumRptSource){
         case 'client':
            $this->lTotClientCnt = $this->lTotalClientCnt($strBetween);
            $this->clientDataEntry($strBetween, $enumGroup);
            break;
         case 'attend':
         case 'enroll':
            $bEnroll = $enumRptSource=='enroll';
            $this->loadClientPrograms();
            $this->lTotEnrollAttendCnt = $this->lTotalAECnt($bEnroll, $strBetween);
            $this->enrollAttendDataEntry($bEnroll, $strBetween, $enumGroup);
            break;
         default:
            screamForHelp($enumRptSource.': invalid report source<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function enrollAttendDataEntry($bEnroll, $strBetween, $enumGroup){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->lTotEnrollAttendCnt == 0) return;

      switch ($enumGroup){
         case 'staffGroup':
            break;
         case 'individual':
            $this->loadEnrollAttendViaUser($bEnroll, $strBetween);
            break;
         default:
            screamForHelp($enumGroup.': invalid report group<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   private function loadEnrollAttendViaUser($bEnroll, $strBetween){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      foreach ($this->ccprogs->cprogs as $cp){
         if ($bEnroll){
            $strAETab = $cp->strEnrollmentTable;
            $strFNP  = $cp->strETableFNPrefix;
         }else {         
            $strAETab = $cp->strAttendanceTable;
            $strFNP  = $cp->strATableFNPrefix;
         }
         
         $sqlStr =
              "SELECT COUNT(*) AS lNumRecs, us_lKeyID, us_strLastName, us_strFirstName
               FROM $strAETab
                  INNER JOIN admin_users ON us_lKeyID=$strFNP"."_lOriginID
               WHERE NOT $strFNP"."_bRetired
                  AND $strFNP"."_dteOrigin $strBetween
               GROUP BY us_lKeyID
               ORDER BY us_strLastName, us_strFirstName, us_lKeyID;";

         $query = $this->db->query($sqlStr);
         $cp->lNumUsers = $numRows = $query->num_rows();
         if ($numRows > 0){
            $idx = 0;
            $cp->users = array();
            foreach ($query->result() as $row){
               $cp->users[$idx] = new stdClass;
               $user = &$cp->users[$idx];
               $user->lNumAERecs   = $row->lNumRecs;
               $user->lUserID      = $row->us_lKeyID;
               $user->strLastName  = $row->us_strLastName;
               $user->strFirstName = $row->us_strFirstName;
               ++$idx;
            }
         }
      }
   }

   function clientDataEntry($strBetween, $enumGroup){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->lTotClientCnt == 0) return;

      switch ($enumGroup){
         case 'staffGroup':
            if ($this->lNumStaffGroups == 0) return;
            foreach ($this->staffGroups as $sge){
               $sge->lNumEntries = $this->lClientCntViaStaffGroup($sge->lKeyID, $strBetween);
            }
            break;
         case 'individual':
            $this->lNumEntries = 0;
            $this->entries = array();
            $sqlStr =
              "SELECT COUNT(*) AS lNumRecs, us_lKeyID, us_strLastName, us_strFirstName
               FROM client_records
                  INNER JOIN admin_users ON us_lKeyID = cr_lOriginID
               WHERE NOT cr_bRetired
                  AND cr_dteOrigin $strBetween
               GROUP BY us_lKeyID
               ORDER BY us_strLastName, us_strFirstName, us_lKeyID;";
            $query = $this->db->query($sqlStr);
            $this->lNumEntries = $numRows = $query->num_rows();
            if ($numRows > 0){
               $idx = 0;
               foreach ($query->result() as $row){
                  $this->entries[$idx] = new stdClass;
                  $en = &$this->entries[$idx];

                  $en->lNumRecs     = $row->lNumRecs;
                  $en->lUserID      = $row->us_lKeyID;
                  $en->strLastName  = $row->us_strLastName;
                  $en->strFirstName = $row->us_strFirstName;
                  $en->staffGroups  = $this->staffGroupsViaUserID($en->lNumGroups, $en->lUserID);

                  ++$idx;
               }
            }
            break;
         default:
            screamForHelp($enumGroup.': invalid report group<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   private function lClientCntViaStaffGroup($lGroupID, $strBetween){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(cr_lKeyID) AS lNumRecs
         FROM client_records
            INNER JOIN admin_users ON us_lKeyID = cr_lOriginID
            INNER JOIN groups_child ON us_lKeyID =  gc_lForeignID
         WHERE gc_lGroupID = $lGroupID
            AND NOT cr_bRetired
            AND cr_dteOrigin $strBetween;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }

   private function staffGroupsViaUserID(&$lNumGroups, $lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $ga = array();
      $this->cgroups->groupMembershipViaFID(CENUM_CONTEXT_STAFF, $lUserID);
      $lNumGroups = $this->cgroups->lNumMemInGroups;
      if ($lNumGroups > 0){
         $ga = arrayCopy($this->cgroups->arrMemberInGroups);
      }
      return($ga);
   }

   private function loadStaffGroups(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->staffGroups = array();
      $this->cgroups->loadActiveGroupsViaType(
                  CENUM_CONTEXT_STAFF, 'groupName', '',
                  false, null);
      $this->lNumStaffGroups = $this->cgroups->lNumGroupList;
      if ($this->lNumStaffGroups > 0){
         $this->staffGroups = arrayCopy($this->cgroups->arrGroupList);
         foreach ($this->staffGroups as $sg){
            $this->cgroups->loadGroupMembership(CENUM_CONTEXT_STAFF, $sg->lKeyID);
            $sg->lNumMembers = $this->cgroups->lCntMembersInGroup;
            if ($sg->lNumMembers > 0){
               $sg->staffMembers = arrayCopy($this->cgroups->groupMembers);
            }
         }
      }

/*
         // and one to grow on
      $this->staffGroups[$this->lNumStaffGroups] = new stdClass;
      $sg = &$this->staffGroups[$this->lNumStaffGroups];
      $sg->lKeyID       = null;
      $sg->strGroupName = '(not a member of any staff group)';
      $sg->bTempGroup   = false;
      $sg->strNotes     = '';
      $sg->dteExpire    = null;
      $sg->dteOrigin    = null;
      ++$this->lNumStaffGroups;
*/
      foreach ($this->staffGroups as $sge){
         $sge->lNumEntries = 0;
      }
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$this->staffGroups   <pre>');
echo(htmlspecialchars( print_r($this->staffGroups, true))); echo('</pre></font><br>');
die;
// -------------------------------------*/
   }





   private function lTotalClientCnt($strBetween){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           "SELECT COUNT(*) AS lNumRecs
            FROM client_records
            WHERE NOT cr_bRetired
               AND cr_dteOrigin $strBetween;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }

   private function lTotalAECnt($bEnroll, $strBetween){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->ccprogs->lNumCProgs == 0) return(0);

      $lTot = 0;
      foreach ($this->ccprogs->cprogs as $cp){
         if ($bEnroll){
            $strAETab = $cp->strEnrollmentTable;
            $strFNP  = $cp->strETableFNPrefix;
         }else {         
            $strAETab = $cp->strAttendanceTable;
            $strFNP  = $cp->strATableFNPrefix;
         }
         $sqlStr =
              "SELECT COUNT(*) AS lNumRecs
               FROM $strAETab
               WHERE NOT $strFNP"."_bRetired
                  AND $strFNP"."_dteOrigin $strBetween;";
         $query = $this->db->query($sqlStr);
         $row = $query->row();
         $cp->lNumAERecs = $lNumRecs = (int)$row->lNumRecs;
         $lTot += $lNumRecs;
      }
      return($lTot);
   }

   function loadClientPrograms(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->ccprogs = new mcprograms;
      $this->ccprogs->loadClientPrograms();
   }


}
















function testPtrBug(){
// http://stackoverflow.com/questions/23171130/php-stdclass-array-element-changed-after-referencing-element-via-pointer
   $myLittleArray = array();
   $myLittleArray[0] = new stdClass;
   $myLittleArray[0]->fruit = 'apple';

   $myLittleArray[1] = new stdClass;
   $myLittleArray[1]->fruit = 'banana';

   $myLittleArray[2] = new stdClass;
   $mla = &$myLittleArray[2];
   $mla->fruit = 'kiwi';

   print_r($myLittleArray);   // $myLittleArray[2]->fruit  displays as "kiwi"

   foreach ($myLittleArray as $mla1){
      $mla1->pricePerPound = 0.0;
   }

   print_r($myLittleArray);   // $myLittleArray[2]->fruit  displays as "banana" ???


/* first printr statement displays
Array
(
    [0] => stdClass Object
        (
            [fruit] => apple
        )

    [1] => stdClass Object
        (
            [fruit] => banana
        )

    [2] => stdClass Object
        (
            [fruit] => kiwi
        )
}

second printr statement (note that $myLittleArray[2]->fruit has changed to "banana"
Array
(
    [0] => stdClass Object
        (
            [fruit] => apple
            [pricePerPound] => 0
        )

    [1] => stdClass Object
        (
            [fruit] => banana
            [pricePerPound] => 0
        )

    [2] => stdClass Object
        (
            [fruit] => banana
            [pricePerPound] => 0
        )
)

*/

}


