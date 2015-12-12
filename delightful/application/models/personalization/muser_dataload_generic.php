<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2011-2014 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
 Functions to support the User-Defined Personalized Fields
---------------------------------------------------------------------

      $this->load->model('personalization/muser_schema',           'cUFSchema');
      $this->load->model('personalization/muser_dataload_generic', 'cUFrec');

---------------------------------------------------------------------*/

class muser_dataload_generic extends muser_schema{


   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();

      $this->sqlWhereExtra = '';
   }
   
   
   function loadSingleRec($strTableName, $strRecIDFN, $lRecID, &$ufields){
   //---------------------------------------------------------------------
   // load a single utable record; the ufields are loaded prior to the
   /* call. Sample calling sequence (from enrollment table)
   
            // load schema info about enrollment table
         $cschema->loadUFSchemaSingleTable($lETableID);
         $eTabSchemaA = arrayCopy($cschema->schema);
         $eTabSchema = &$eTabSchemaA[$lETableID];
         
            // load eTable default fields
         $cschema->eTableFieldInfo($eTabSchema->fields, $strEFNPre, true);   
         ...
         $cschema->loadSingleRec($strETable, $eTabSchema->strDataTableKeyID, $client->lEnrollID, $eTabSchema->fields);

   
   ---------------------------------------------------------------------*/
      $lNumFields = count($ufields);
      if ($lNumFields <= 0) return;
      
      foreach ($ufields as $uf) $uf->value = null;
      
      $strFNs = '';
      foreach ($ufields as $uf){
         $enumType = $uf->enumFieldType;
         if (!($enumType==CS_FT_LOG || $enumType==CS_FT_HEADING)){
            $strFNs .= ', '.$uf->strFieldNameInternal;
         }
      }
      $strFNs = substr($strFNs, 1);

      $sqlStr =
         "SELECT $strFNs
          FROM $strTableName
          WHERE $strRecIDFN = $lRecID
          LIMIT 0,1;";
          
      $query = $this->db->query($sqlStr);

      if ($query->num_rows()> 0){
         $row = $query->row();
         foreach ($ufields as $uf){
            $enumType = $uf->enumFieldType;
            if (!($enumType==CS_FT_LOG || $enumType==CS_FT_HEADING)){
               $strFN = $uf->strFieldNameInternal;
               $uf->value = $row->$strFN;
            }
         }
      }
   }
   
}
