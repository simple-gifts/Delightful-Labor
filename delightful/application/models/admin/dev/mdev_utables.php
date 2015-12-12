<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('admin/dev/mdev_utables', 'cUT');
--------------------------------------------------------------------

---------------------------------------------------------------------*/


class mdev_utables extends CI_Model{
   public
       $customForms, $lNumCustomForms,
       $strWhereExtra, $strOrder;

   public $lNumTables, $utables;

   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

   }


   function loadUTableInfo(&$lNumUTables, &$utables){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumUTables = 0;
      $utables = array();
      $sqlStr =
        'SELECT pft_lKeyID, pft_strUserTableName, pft_strDataTableName,
           pft_enumAttachType, pft_bMultiEntry, pft_bHidden,
           pft_strVerificationModule, pft_strVModEntryPoint,
           UNIX_TIMESTAMP(pft_dteOrigin) AS dteOrigin,
           cpETable.cp_lKeyID AS lCProgEID, cpETable.cp_strProgramName AS strCProgEName,
           cpATable.cp_lKeyID AS lCProgAID, cpATable.cp_strProgramName AS strCProgAName
         FROM uf_tables
            LEFT JOIN cprograms AS cpETable ON cpETable.cp_lEnrollmentTableID=pft_lKeyID
            LEFT JOIN cprograms AS cpATable ON cpATable.cp_lAttendanceTableID=pft_lKeyID
         WHERE NOT pft_bRetired
         ORDER BY pft_lKeyID;';

      $query = $this->db->query($sqlStr);

      $lNumUTables = $query->num_rows();
      if ($lNumUTables > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $utables[$idx] = new stdClass;
            $ut = &$utables[$idx];

            $ut->lKeyID                = (int)$row->pft_lKeyID;
            $ut->strUserTableName      = $row->pft_strUserTableName;
            $ut->strDataTableName      = $row->pft_strDataTableName;
            $ut->enumAttachType        = $row->pft_enumAttachType;
            $ut->bMultiEntry           = $row->pft_bMultiEntry;
            $ut->bHidden               = $row->pft_bHidden;
            $ut->strVerificationModule = $row->pft_strVerificationModule;
            $ut->strVModEntryPoint     = $row->pft_strVModEntryPoint;
            $ut->dteOrigin             = (int)$row->dteOrigin;
            $ut->lCProgEID             = $row->lCProgEID;
            $ut->strCProgEName         = $row->strCProgEName;
            $ut->lCProgAID             = $row->lCProgAID;
            $ut->strCProgAName         = $row->strCProgAName;

            ++$idx;
         }
      }
   }

}