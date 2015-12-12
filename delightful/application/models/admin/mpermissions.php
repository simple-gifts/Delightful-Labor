<?php
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2014-2015 by Database Austin
 Austin, Texas

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.

-------------------------------------------------------------------
      $this->load->model('admin/mpermissions', 'perms');
---------------------------------------------------------------------

-------------------------------------------------------------------*/

class mpermissions extends CI_Model{


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
   }

   function tablePerms(&$utable, &$lNumPerms, &$perms){
   //-----------------------------------------------------------------------
   // the indexes of the table array are the table IDs
   // A single table ID can also be passed.
   //-----------------------------------------------------------------------
      $lNumPerms = 0;
      if (is_array($utable)){
         $locIDs = arrayCopy($utable);
      }else {
         $locIDs = array();
         $locIDs[0] = $utable;
      }
      if (count($locIDs) > 0){
         foreach ($locIDs as $lTableID){

            $query = $this->db->query($this->strSqlLoadGroups($lTableID));
            $lNumRows = $query->num_rows();
            if ($lNumRows > 0){
               foreach ($query->result() as $row) {
                  $lGroupChildID        = $row->gc_lKeyID;
                  if (!isset($perms[$lGroupChildID])){
                     ++$lNumPerms;
                     $perms[$lGroupChildID] = new stdClass;
                     $tab = &$perms[$lGroupChildID];
                     $tab->strGroupName = $row->gp_strGroupName;
                     $tab->strSafeGroupName = htmlspecialchars($row->gp_strGroupName);
                     $tab->lGroupChildID        = $row->gc_lKeyID;
                     $tab->lGroupID             = $row->gc_lGroupID;
                     $tab->lForeignID           = $row->gc_lForeignID;
                     $tab->enumSubGroup         = $row->gc_enumSubGroup;
                  }
               }
            }
         }
      }
   }
   
   function consolidateTablePerms($lNumPerms, &$perms, &$lNumConsolidated, &$cperms){
   //---------------------------------------------------------------------
   //
   //--------------------------------------------------------------------- 
      if ($lNumPerms == 0){
         $lNumConsolidated = 0;
         $cperms = null;
         return;
      }
      
      $cperms = array();
      $lNumConsolidated = 0;
      foreach ($perms as $perm){
         $lGroupID = $perm->lGroupID;
         
         if (!isset($cperms[$lGroupID])){
            $cperms[$lGroupID] = new stdClass;
            $cp = &$cperms[$lGroupID];
            $cp->strGroupName     = $perm->strGroupName;
            $cp->strSafeGroupName = $perm->strSafeGroupName;
            $cp->enumSubGroup     = $perm->enumSubGroup;
            ++$lNumConsolidated;
         }
      }      
   }

   private function strSqlLoadGroups($lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow;

      return(
           "SELECT gp_strGroupName,
               gc_lKeyID, gc_lGroupID, gc_lForeignID, gc_enumSubGroup
            FROM groups_child
               INNER JOIN groups_parent ON gc_lGroupID=gp_lKeyID
            WHERE
             gc_lForeignID=$lTableID
            AND gc_enumSubGroup=".strPrepStr(CENUM_CONTEXT_PTABLE).'
            AND gp_enumGroupType='.strPrepStr(CENUM_CONTEXT_USER).';');
   }

   function loadUserGroups($lUserID, &$userGroups, &$lGroupIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $userGroups = new stdClass;
      
      $sqlStr =
           "SELECT
               gc_lKeyID, gc_lGroupID, gp_strGroupName
            FROM groups_child
               INNER JOIN groups_parent ON gp_lKeyID=gc_lGroupID
            WHERE gc_lForeignID=$lUserID
               AND gc_enumSubGroup  = ".strPrepStr(CENUM_CONTEXT_USER).'
               AND gp_enumGroupType = '.strPrepStr(CENUM_CONTEXT_USER).'
            ORDER BY gp_strGroupName, gc_lGroupID, gc_lKeyID;';
      $query = $this->db->query($sqlStr);

      $userGroups->lNumUserGroups = $numRows = $query->num_rows();
      if ($numRows > 0) {
         $idx = 0;
         $userGroups->groups = array();
         $lGroupIDs = array();
         foreach ($query->result() as $row){
            $userGroups->groups[$idx] = new stdClass;
            $group = &$userGroups->groups[$idx];
            $group->lGroupChildID = (int)$row->gc_lKeyID;            
            $group->lGroupID      = $lGroupIDs[] = (int)$row->gc_lGroupID;
            $group->strGroupName  = $row->gp_strGroupName;
            ++$idx;
         }
      }
   }

   function loadUserAcctInfo($lUserID, &$acctAccess){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $acctAccess = new stdClass;
      $acctAccess->lUserID = $lUserID;

      $sqlStr =
        "SELECT
            us_bInactive, us_strUserName, us_bAdmin, us_bDebugger,
            us_bVolAccount
         FROM admin_users
         WHERE us_lKeyID=$lUserID;";
      $query = $this->db->query($sqlStr);

      $numRows = $query->num_rows();
      if ($numRows==0) {
         $acctAccess->strUserName =
         $acctAccess->bAdmin      =
         $acctAccess->bUser       =
         $acctAccess->bVolAccount =
         $acctAccess->bDebugger   =
         $acctAccess->bInactive   = null;
      }else {
         $row = $query->row();
         $acctAccess->bInactive   = (boolean)$row->us_bInactive;
         $acctAccess->strUserName = $row->us_strUserName;
         $acctAccess->bAdmin      = (boolean)$row->us_bAdmin;
         $acctAccess->bVolAccount = (boolean)$row->us_bVolAccount;
         $acctAccess->bUser       = (!($acctAccess->bAdmin || $acctAccess->bVolAccount));
         $acctAccess->bDebugger   = (boolean)$row->us_bDebugger;
         
         if ($acctAccess->bUser){
            $this->loadUserGroups($lUserID, $acctAccess->userGroups, $acctAccess->lGroupIDs);
         }
      }
   }

   function bDoesUserHaveAccess(&$acctAccess, $lNumConsolidated, &$cperms){
   //---------------------------------------------------------------------
   // return true if user can access the custom form or personalized table.
   // Note that this is based solely on user groups associated with
   // the user and the tables that make up the custom form.
   //
   // caller must first call 
   //    $this->loadUserAcctInfo($lUserID, &$acctAccess)
   //
   // the $lNumConsolidated and &$cperms parameters can be generated by
   // a call to $cForm->loadCustomForms() or one of its wrappers
   //
   //       $this->load->model('custom_forms/mcustom_forms', 'cForm');
   //---------------------------------------------------------------------  
   
         // simple cases - admins can access everything, volunteers
         // can access nothing.
      if ($acctAccess->bAdmin)      return(true);
      if ($acctAccess->bVolAccount) return(false);
      if ($lNumConsolidated   == 0) return(true);  // form has no restrictions
      if ($acctAccess->userGroups->lNumUserGroups == 0) return(false); // form has restrictions; user belongs to no groups
      
      $lUserGroupIDs = &$acctAccess->lGroupIDs;
     
      foreach ($cperms as $lCGroupID => $dummy){
         if (!in_array($lCGroupID, $lUserGroupIDs)) return(false);
      }
      return(true);
   }
   
   function lGroupPerms($lFID, $enumContextType, &$perms){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $perms = array();

      $sqlStr =
           "SELECT DISTINCT gc_lGroupID
            FROM groups_child
            WHERE gc_lForeignID=$lFID
               AND gc_enumSubGroup=".strPrepStr($enumContextType).'
            ORDER BY gc_lGroupID;';
      $query = $this->db->query($sqlStr);
      $lNumPerms = $query->num_rows();
      if ($lNumPerms > 0){
         foreach ($query->result() as $row){
            $perms[$row->gc_lGroupID] = true;
         }
      }
      return($lNumPerms);
   }   
   
   function bDoesUserHaveFunctionalAccess($acctAccess, $strUserGroupName){
   //---------------------------------------------------------------------
   // for example, can a user access a routine that requires membership
   // in "SHIFT JMI"
   //     $this->load->model ('admin/mpermissions',  'perms');
   //
   //     $this->perms->loadUserAcctInfo($glUserID, $acctAccess);
   //     if (!$this->perms->bDoesUserHaveFunctionalAccess($acctAccess, 'SHIFT JMI')){
   //        badBoyRedirect('Your account does not allow you access to this feature.');
   //        return;
   //     }
   //---------------------------------------------------------------------   
      global $gbAdmin, $glUserID;
      
      if ($gbAdmin) return(true);
  
      $sqlStr =  
        'SELECT gp_lKeyID 
         FROM groups_parent 
         WHERE 
            gp_enumGroupType='.strPrepStr(CENUM_CONTEXT_USER).'
            AND gp_strGroupName='.strPrepStr($strUserGroupName).'
         LIMIT 0, 1;';
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0) return(false);
      $row = $query->row();
      $lGroupEntryID = (int)$row->gp_lKeyID;
      return(in_array($lGroupEntryID, $acctAccess->lGroupIDs));
   }
   
   
   
   
}










