<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2015 Database Austin

   author: John Zimmerman

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.

---------------------------------------------------------------------
      $this->load->helper('creports/creport_field');
---------------------------------------------------------------------*/

namespace crptFields;

   function parentTableFieldInfo($fields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (count($fields)==0) return;
      foreach ($fields as $field){
         if ($field->lTableID < 0){
            switch ($field->lTableID){
               case CL_STID_CLIENT:
                  populateClientField($field);
                  break;
               case CL_STID_PEOPLE:
                  populatePeopleField($field);
                  break;
               case CL_STID_BIZ:
                  populateBizField($field);
                  break;
               case CL_STID_VOL:
                  populateVolField($field);
                  break;
               default:
                  screamForHelp($field->lTableID.': parent table type not available yet<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
                  break;
            }
         }
      }
   }

   function populateClientField(&$field){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $field->strUserTableName = 'Client';
      $field->enumParentTable = CENUM_CONTEXT_CLIENT;
      crptFieldPropsClient($field->strFieldName, $field->enumType, $field->strUserFN);
   }

   function populatePeopleField(&$field){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $field->strUserTableName = 'People';
      $field->enumParentTable = CENUM_CONTEXT_PEOPLE;
      crptFieldPropsPeople($field->strFieldName, $field->enumType, $field->strUserFN);
   }
   
   function populateVolField(&$field){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $field->strUserTableName = 'Volunteer';
      $field->enumParentTable = CENUM_CONTEXT_VOLUNTEER;
      crptFieldPropsVol($field->strFieldName, $field->enumType, $field->strUserFN);
   }
   
   function populateBizField(&$field){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $field->strUserTableName = 'Business/Organization';
      $field->enumParentTable = CENUM_CONTEXT_BIZ;
      crptFieldPropsBiz($field->strFieldName, $field->enumType, $field->strUserFN);
   }

   function strSelectTermViaFieldInfo($field){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strField = '';
      if ($field->lTableID > 0){
         $strField .= strtoupper(substr($field->enumParentTable, 0, 1))
                    .substr($field->enumParentTable, 1).':';
      }
      $strField .= $field->strUserTableName.':'.$field->strUserFN;
      return(strEscMysqlQuote($strField));
   }


