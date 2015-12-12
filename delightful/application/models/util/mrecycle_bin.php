<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
/*---------------------------------------------------------------------


---------------------------------------------------------------------
      $this->load->model('util/mrecycle_bin', 'clsRecycle');
---------------------------------------------------------------------*/


//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mrecycle_bin extends CI_Model{

   public
      $lForeignID, $strTable, $strRetireFN, $strKeyIDFN,
      $strNotes, $lGroupID, $enumRecycleType;


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->lForeignID = $this->strTable = $this->strRetireFN =
      $this->strKeyIDFN = $this->strNotes =
      $this->lGroupID   = $this->enumRecycleType = null;
   }

   function addRecycleEntry(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $varGroupID = (is_null($this->lGroupID) ? 0 : $this->lGroupID);

      $sqlStr =
        "INSERT INTO recycle_bin
         SET
            rb_lGroupID        = $varGroupID,
            rb_enumRecycleType = ".strPrepStr($this->enumRecycleType).',
            rb_strDescription  = '.strPrepStr(substr($this->strNotes, 0, 255)).',
            rb_strTable        = '.strPrepStr($this->strTable).',
            rb_strKeyIDFN      = '.strPrepStr($this->strKeyIDFN).',
            rb_strRetireFN     = '.strPrepStr($this->strRetireFN).",
            rb_lForeignID      = $this->lForeignID,
            rb_lOriginID       = $glUserID,
            rb_lLastUpdateID   = $glUserID,
            rb_dteOrigin       = NOW(),
            rb_dteLastUpdate   = NOW();";

      $query = $this->db->query($sqlStr);

      if (is_null($this->lGroupID)){
         $this->lGroupID = $lKeyID = $this->db->insert_id();   // mysql_insert_id();
         $sqlStr = "UPDATE recycle_bin SET rb_lGroupID=$lKeyID WHERE rb_lKeyID=$lKeyID;";
         $query = $this->db->query($sqlStr);
      }
   }


}



?>