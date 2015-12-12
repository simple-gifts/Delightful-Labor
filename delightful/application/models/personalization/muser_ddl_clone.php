<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2011-2014 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
 Functions to support the User-Defined Personalized Fields
---------------------------------------------------------------------

      $this->load->model('personalization/muser_ddl_clone', 'cClone');

---------------------------------------------------------------------*/

class muser_ddl_clone extends CI_Model{

   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();
   }


   function loadAllDDLMultDDL(&$lNumDDLs, &$ddls){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            pft_strUserTableName, pft_lKeyID, pft_enumAttachType,
            pff_lKeyID, pff_lTableID, pff_strFieldNameInternal, pff_strFieldNameUser,
            pff_strFieldNotes, pff_enumFieldType, pff_bHidden,
            ufddl_lKeyID, ufddl_strDDLEntry
         FROM uf_fields
            INNER JOIN uf_tables ON pff_lTableID=pft_lKeyID
            INNER JOIN uf_ddl ON pff_lKeyID=ufddl_lFieldID

         WHERE (pff_enumFieldType='DDL' OR pff_enumFieldType='DDLMulti')
            AND NOT ufddl_bRetired
            AND NOT pft_bRetired
            AND NOT pff_bHidden
            AND NOT pft_bHidden
         ORDER BY  pft_strUserTableName, pff_strFieldNameUser, pff_lKeyID, ufddl_lSortIDX;";

      $query = $this->db->query($sqlStr);

      $lNumDDLs = $query->num_rows();
      $ddls = array();
      $lGroupFID = -999;
      if ($lNumDDLs > 0) {
         foreach ($query->result() as $row){
            $lFieldID = (int)$row->pff_lKeyID;
            if ($lGroupFID != $lFieldID){
               $ddls[$lNumDDLs] = new stdClass;
               $ddl = &$ddls[$lNumDDLs];

                  // table info
               $ddl->lTableID         = (int)$row->pft_lKeyID;
               $ddl->strUserTableName = $row->pft_strUserTableName;
               $ddl->enumAttachType   = $row->pft_enumAttachType;

                  // ddl / MultiDDL field info
               $ddl->lFieldID              = (int)$row->pff_lKeyID;
               $ddl->strFieldNameInternal  = $row->pff_strFieldNameInternal;
               $ddl->strFieldNameUser      = $row->pff_strFieldNameUser;
               $ddl->strFieldNotes         = $row->pff_strFieldNotes;
               $ddl->enumFieldType         = $row->pff_enumFieldType;
               $ddl->bHidden               = (bool)$row->pff_bHidden;

                  // ddl entry setup
               $ddl->lNumEntries = 0;
               $ddl->entries = array();

               ++$lNumDDLs;
               $lGroupFID = $lFieldID;
            }

               // add the ddl entries
            $ddl->entries[$ddl->lNumEntries] = new stdClass;
            $ddl->entries[$ddl->lNumEntries]->lEntryID = (int)$row->ufddl_lKeyID;
            $ddl->entries[$ddl->lNumEntries]->strEntry = $row->ufddl_strDDLEntry;
            ++$ddl->lNumEntries;
         }
      }
   }

   function cloneDDL($lTargetFID, $lSourceFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
          "INSERT INTO uf_ddl (
              ufddl_lFieldID,
              ufddl_lSortIDX,
              ufddl_bRetired,
              ufddl_strDDLEntry)
              SELECT
                 $lTargetFID AS lFieldID,
                 ufddl_lSortIDX AS lSortIDX,
                 0 AS bRetired,
                 ufddl_strDDLEntry AS strEntry
              FROM uf_ddl
              WHERE ufddl_lFieldID=$lSourceFID AND NOT ufddl_bRetired;";
      $query = $this->db->query($sqlStr);
      
         // set the "configured" flag
      $sqlStr = "UPDATE uf_fields SET pff_bConfigured=1 WHERE pff_lKeyID=$lTargetFID;";
      $query = $this->db->query($sqlStr);
   }



}



