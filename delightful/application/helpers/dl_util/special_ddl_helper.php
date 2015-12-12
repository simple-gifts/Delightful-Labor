<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012-2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
/*---------------------------------------------------------------------
      $this->load->helper('dl_util/special_ddl');
---------------------------------------------------------------------*/
namespace sddl;

   function loadSpecialDDL($strFN, &$lNumEntries, &$entries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($strFN){
            // client DDLs
         case 'cr_enumGender':   sddl_cr_enumGender  ($lNumEntries, $entries);  break;
         case 'cr_lLocationID':  sddl_cr_lLocationID ($lNumEntries, $entries);  break;
         case 'cr_lStatusCatID': sddl_cr_lStatusCatID($lNumEntries, $entries);  break;
         case 'cr_lVocID':       sddl_cr_lVocID      ($lNumEntries, $entries);  break;
         default:
            screamForHelp($strFN.': invalid special ddl type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function sddl_cr_enumGender(&$lNumEntries, &$entries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumEntries = 0;  $entries = array();
      addEntry($lNumEntries, $entries, 'Male',    'Male');
      addEntry($lNumEntries, $entries, 'Female',  'Female');
      addEntry($lNumEntries, $entries, 'Unknown', 'Unknown');
   }


   function sddl_cr_lLocationID(&$lNumEntries, &$entries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT cl_lKeyID AS sddlKey, cl_strLocation AS sddlValue
         FROM client_location
         WHERE NOT cl_bRetired
         ORDER BY cl_strLocation, cl_lKeyID;';
      db_sddl_lookup($sqlStr, $lNumEntries, $entries);
   }

   function sddl_cr_lStatusCatID(&$lNumEntries, &$entries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT csc_lKeyID AS sddlKey, csc_strCatName AS sddlValue
         FROM client_status_cats
         WHERE NOT csc_bRetired
         ORDER BY csc_strCatName, csc_lKeyID;';
      db_sddl_lookup($sqlStr, $lNumEntries, $entries);
   }

   function sddl_cr_lVocID(&$lNumEntries, &$entries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT cv_lKeyID AS sddlKey, cv_strVocTitle AS sddlValue
         FROM lists_client_vocab 
         WHERE NOT cv_bRetired
         ORDER BY cv_strVocTitle, cv_lKeyID;';
      db_sddl_lookup($sqlStr, $lNumEntries, $entries);
   }


   function db_sddl_lookup($sqlStr, &$lNumEntries, &$entries){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumEntries = 0;  $entries = array();
      $CI =& get_instance();
      $query = $CI->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows > 0) {
         foreach ($query->result() as $row){
            addEntry($lNumEntries, $entries, $row->sddlKey, $row->sddlValue);
         }
      }
   }

   function addEntry(&$lNumEntries, &$entries, $vKey, $strValue){
      $entries[$lNumEntries] = new \stdClass;
      $entries[$lNumEntries]->key   = $vKey;
      $entries[$lNumEntries]->value = $strValue;
      ++$lNumEntries;
   }






   //---------------------------------------------------------------------
   //      L O A D   V A L U E
   //---------------------------------------------------------------------

   function loadSpecialDDLValue($strFN, $vKey){
   //---------------------------------------------------------------------
   // from the key value, return the ddl value
   //---------------------------------------------------------------------
      switch ($strFN){
            // client DDLs
         case 'cr_enumGender':   return(value_sddl_cr_enumGender  ($vKey));  break;
         case 'cr_lLocationID':  return(value_sddl_cr_lLocationID ($vKey));  break;
         case 'cr_lStatusCatID': return(value_sddl_cr_lStatusCatID($vKey));  break;
         case 'cr_lVocID':       return(value_sddl_cr_lVocID      ($vKey));  break;
         default:
            screamForHelp($strFN.': invalid special ddl type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function value_sddl_cr_enumGender($vKey){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return($vKey);  // Q.E.D.
   }

   function value_sddl_cr_lLocationID($vKey){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'SELECT cl_strLocation as sddlValue FROM client_location  WHERE cl_lKeyID='.(int)$vKey.';';
      return(db_value_via_key($sqlStr));
   }

   function value_sddl_cr_lStatusCatID($vKey){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'SELECT csc_strCatName as sddlValue FROM client_status_cats  WHERE csc_lKeyID='.(int)$vKey.';';
      return(db_value_via_key($sqlStr));
   }
   
   function value_sddl_cr_lVocID($vKey){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'SELECT cv_strVocTitle as sddlValue FROM lists_client_vocab  WHERE cv_lKeyID='.(int)$vKey.';';
      return(db_value_via_key($sqlStr));
   }

   function db_value_via_key($sqlStr){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $CI =& get_instance();
      $query = $CI->db->query($sqlStr);
      $row = $query->row();
      return($row->sddlValue);
   }




