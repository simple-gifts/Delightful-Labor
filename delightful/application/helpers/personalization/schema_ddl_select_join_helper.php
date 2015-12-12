<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('personalization/schema_ddl_select_join');
---------------------------------------------------------------------*/

   function createJoinsSelectsForDDLS($fields, $bAsLeftJoin,
                 &$lNumDDLs, &$strJoins, &$strDDLFields, &$strDDLRecSetFNs){
   //---------------------------------------------------------------------
   // This routine creates the Joins and Selects to load the DDL values
   // from a personalized table.
   //
   // $fields is a pointer to the fields table of a schema:
   //   $this->saiSchema = new muser_schema;
   //   $this->saiSchema->loadUFSchemaViaAttachTypeUserTabName(CENUM_CONTEXT_CLIENT, 'Shift Ascension Intake', $this->lSAITableID);
   //   $this->saiTableSchema = &$this->saiSchema->schema[$this->lSAITableID];
   //   $saiFields = $this->saiTableSchema->fields;
   //
   //   createJoinsSelectsForDDLS($saiFields, true, $lNumDDLs, $strJoins, $strDDLFields, $strDDLRecSetFNs);
   //---------------------------------------------------------------------  
      $lNumDDLs = 0;
      $strJoins = '';
      $strDDLFields = '';
      $strDDLRecSetFNs = array();
      foreach ($fields as $field){
         if ($field->enumFieldType == CS_FT_DDL){
            $strFNInternal = $field->strFieldNameInternal;
            $strJoinTableAs = 'ddl_'.$strFNInternal;
            $strJoins .=
               ($bAsLeftJoin ? 'LEFT ' : 'INNER')." JOIN uf_ddl AS $strJoinTableAs ON $strJoinTableAs.ufddl_lKeyID = $strFNInternal \n";
            $strDDLFields .= 
                        ",\n $strJoinTableAs.ufddl_strDDLEntry AS `"
                                .strEscMysqlQuote($field->strFieldNameUser)."` ";
            $strDDLRecSetFNs[$lNumDDLs] = $field->strFieldNameUser;
            
            ++$lNumDDLs;
         }
      }
   }
   
   
   
   