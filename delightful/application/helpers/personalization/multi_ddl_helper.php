<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('personalization/multi_ddl');
---------------------------------------------------------------------*/


   function loadMultiDDLViaParentID(
               $strParentTable, $strPTFNKeyID, $strTmpTable, $strTTForeignID,
               $ufTableSchema, $ufMultiDDLFieldIDX,
               &$lNumRecs, &$multiRecs){
   //---------------------------------------------------------------------
   // here's the situation:
   //   We have a single-entry personalized table with multiDDL fields.
   //   Let's say this table is attached to the client table.
   //   For a given list of client IDs (in a temporary table passed to
   //   this routine), we want to connect to the personalized table
   //   and capture all the multi-ddl selections for a given multiDDL
   //   field in the single-entry table.
   //
   //      $strParentTable     - parent table name (client_records, etc)
   //      $strPTFNKeyID       - parent table keyID field name (cr_lKeyID, etc)
   //      $strTmpTable        - name of temp table
   //      $strTTForeignID     - name of temp table foreign ID that connects to parent table
   //      $ufTableSchema      - schema structure for the personalized table
   //      $ufMultiDDLFieldIDX - index in the fields array for the multi-field of interest
   //---------------------------------------------------------------------
      $CI =& get_instance();

      $field = &$ufTableSchema->fields[$ufMultiDDLFieldIDX];
      $lUFFieldOfInterestID = $field->lFieldID;

      $strUFTableName    = $ufTableSchema->strDataTableName;
      $strUFFNTableFID   = $ufTableSchema->strDataTableFID;
      $strUFFNTableKeyID = $ufTableSchema->strDataTableKeyID;
      $lUFTableID        = $ufTableSchema->lTableID;

         // a thing of beauty is a joy forever....
      $sqlStr =
        "SELECT
            pdm_lKeyID, $strPTFNKeyID AS lParentKeyID,
            $strUFFNTableKeyID AS lUFTableKeyID,
            pdm_lDDLID, ufddl_strDDLEntry

         FROM $strParentTable
               -- connect to temp table
            INNER JOIN $strTmpTable ON $strPTFNKeyID=$strTTForeignID

               -- connect to single-record personalized table
            INNER JOIN  $strUFTableName ON $strPTFNKeyID=$strUFFNTableFID

               -- connect personalized table to multi-ddl link table
            INNER JOIN uf_ddl_multi ON pdm_lUTableRecID=$strUFFNTableKeyID

               -- connect multi-ddl link table to the DDL values table
            INNER JOIN uf_ddl ON pdm_lDDLID=ufddl_lKeyID

         WHERE
                pdm_lFieldID     = $lUFFieldOfInterestID
            AND pdm_lUTableID    = $lUFTableID
         ORDER BY $strPTFNKeyID, ufddl_lSortIDX, pdm_lKeyID;";
      $query = $CI->db->query($sqlStr);


      $lNumRecs = $numRows = $query->num_rows();
      $multiRecs = array();
      if ($lNumRecs > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $multiRecs[$idx] = new stdClass;
            $mr = &$multiRecs[$idx];

            $mr->lParentKeyID  = (int)$row->lParentKeyID;
            $mr->pdm_lKeyID    = (int)$row->pdm_lKeyID;
            $mr->lUFTableKeyID = (int)$row->lUFTableKeyID;
            $mr->pdm_lDDLID    = (int)$row->pdm_lDDLID;
            $mr->strDDLEntry   = $row->ufddl_strDDLEntry;

            ++$idx;
         }
      }
   }



