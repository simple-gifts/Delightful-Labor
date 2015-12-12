<?php
/*---------------------------------------------------------------------
// copyright (c) 2014 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('admin/mpermissions',                  'perms');
      $this->load->model('personalization/muser_fields',        'clsUF');
      $this->load->model('personalization/muser_schema',        'cUFSchema');
      $this->load->model('client_features/mcprograms',          'cprograms');
      $this->load->model('client_features/mcprog_enrollee_rpt', 'cperpt');
---------------------------------------------------------------------*/

class mcprog_enrollee_rpt extends mcprograms{

   public $enrollees, $lNumEnrollees;

	function __construct(){
		parent::__construct();
	}

   function loadReportEnrolless(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND cp_lKeyID $sRpt->strCProgIn ";
      $this->loadClientPrograms();

      $erecs = array();
      foreach ($this->cprogs as $cprog){

            // note - interesting debate over where to use IN or temp table
            // http://stackoverflow.com/questions/1532366/mysql-number-of-items-within-in-clause

            // load the enrollees
         $this->extractCProgFields($cprog,
                     $lETableID, $lATableID, $strETable, $strEFNPrefix, $strATable,$strAFNPrefix);

         $sqlStr =
            "SELECT $strEFNPrefix"."_lForeignKey AS lClientID,
                $strEFNPrefix"."_lKeyID AS lERecID,
                $strEFNPrefix"."_dteStart AS mdteStart,
                $strEFNPrefix"."_dteEnd   AS mdteEnd
             FROM $strETable
             WHERE NOT $strEFNPrefix"."_bRetired "
                .$this->strActivelyEnrolledDuringTimeFrameWhere($cprog, $sRpt->dteStart, $sRpt->dteEnd)."
             ORDER BY $strEFNPrefix"."_lKeyID;";
         $query = $this->db->query($sqlStr);
         $lNumERecs = $query->num_rows();

         if ($lNumERecs > 0) {
            foreach ($query->result() as $row){
               $lClientID = $row->lClientID;
               if (!isset($erecs[$lClientID])){
                  $erecs[$lClientID] = new stdClass;
                  $erec = &$erecs[$lClientID];
                  $erec->programs = array();
                  $erec->lProgCnt = 0;
               }

               $lCnt = $erec->lProgCnt;
               $erec->programs[$lCnt] = new stdClass;
               $erec->programs[$lCnt]->strProgName = $cprog->strProgramName;
               $erec->programs[$lCnt]->lCProgID    = $cprog->lKeyID;
               $erec->programs[$lCnt]->lETableID   = $cprog->lEnrollmentTableID;
               $erec->programs[$lCnt]->lERecID     = $row->lERecID;
               $erec->programs[$lCnt]->dteStart    = dteMySQLDate2Unix($row->mdteStart);
               $erec->programs[$lCnt]->dteEnd      = dteMySQLDate2Unix($row->mdteEnd);
               ++$erec->lProgCnt;
            }
         }
      }

      $this->lNumEnrollees = count($erecs);

      $this->enrollees = array();
      if ($this->lNumEnrollees > 0){

         $strIn = implode(', ', array_keys($erecs));
         $sqlStr =
              "SELECT
                  cr_lKeyID, cr_strFName, cr_strLName
               FROM client_records
               WHERE
                  cr_lKeyID IN ($strIn)
                  AND NOT cr_bRetired
               ORDER BY cr_strLName, cr_strFName, cr_lKeyID;";

         $query = $this->db->query($sqlStr);
         $lNumCRecs = $query->num_rows();
         if ($lNumCRecs > 0) {
            $idx = 0;
            foreach ($query->result() as $row){
               $lClientID = (int)$row->cr_lKeyID;
               $this->enrollees[$idx] = new stdClass;
               $crec = &$this->enrollees[$idx];
               $crec->lClientID = $lClientID;
               $crec->strCFName = $row->cr_strFName;
               $crec->strCLName = $row->cr_strLName;
               $crec->programs  = arrayCopy($erecs[$lClientID]->programs);
               ++$idx;
            }
         }
      }
   }
}

