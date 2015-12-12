<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2015 Database Austin

   author: John Zimmerman

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.

---------------------------------------------------------------------
      $this->load->helper('creports/creport_special_ddl');
---------------------------------------------------------------------*/

namespace crptDDL;

   function ddl2sqlSpecial($field, &$strSelect, &$strJoin){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------      
      $strSelect = $strJoin = '';
      if ($field->enumType != CS_FT_DDL_SPECIAL) return;
      switch ($field->strUserFN){
         case 'Location':
            $strJoin = 'INNER JOIN client_location on cl_lKeyID=cr_lLocationID';
            $strSelect = 'cl_strLocation AS `Client:Location`';
            break;
         case 'Status Category':
            $strJoin = 'INNER JOIN client_status_cats on csc_lKeyID=cr_lStatusCatID';
            $strSelect = 'csc_strCatName AS `Client:Status Category`';
            break;
         case 'Vocabulary':
            $strJoin = 'LEFT JOIN lists_client_vocab ON cr_lVocID = cv_lKeyID';
            $strSelect = 'cv_strVocTitle AS `Client:Vocabulary`';
            break;
         default:
            screamForHelp($field->strUserFN.': client special ddl type not available<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      
   }
