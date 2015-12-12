<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2015 Database Austin

   author: John Zimmerman

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.

---------------------------------------------------------------------
      $this->load->helper('creports/creport_tables');
---------------------------------------------------------------------*/

namespace crptTables;

   function tablesUsed($report, $terms, $sortTerms, &$tableIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $tableIDs = array();
      foreach ($report->fields as $field){
         if ($field->lTableID > 0){
            $tableIDs[$field->lTableID] = true;
         }
      }
      
      tableIDsSearchTerms($terms, $tableIDs);
/*      
      if (count($terms) > 0){
         foreach ($terms as $term){
            if ($term->lTableID > 0){
               $tableIDs[$term->lTableID] = true;
            }
         }
      }
*/

      if (count($sortTerms) > 0){
         foreach ($sortTerms as $sterm){
            if ($sterm->lTableID > 0){
               $tableIDs[$sterm->lTableID] = true;
            }
         }
      }

      $tableIDs = array_keys($tableIDs);
      sort($tableIDs);
   }
   
   function tableIDsSearchTerms($terms, &$tableIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
//      $tableIDs = array();
      if (count($terms) > 0){
         foreach ($terms as $term){
            if ($term->lTableID > 0){
               $tableIDs[$term->lTableID] = true;
            }
         }
      }
   }

