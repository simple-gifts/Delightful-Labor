<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2005-2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('people/people_display');
---------------------------------------------------------------------
// getPeopleRecPerPageLimit   (&$lRecsPerPage, &$lStartRec,
// lNumPeopleRecsViaLetter    ($strDirLetter, $enumRecType, $bIncludeInactive=false){
//---------------------------------------------------------------------*/

function lNumPeopleRecsViaLetter($strDirLetter, $enumRecType,
                 $bIncludeInactive=false, $strWhereExtra=''){
//---------------------------------------------------------------------
// $bIncludeInactive applies to volunteers
//
// $enumRecType: 'biz' 'bizContact' 'people' 'volunteer'
//---------------------------------------------------------------------
   $strWhereName = strNameWhereClauseViaLetter('pe_strLName', $strDirLetter);
   $CI =& get_instance();
   switch ($enumRecType){
      case CENUM_CONTEXT_BIZ:
         $sqlStr =
             "SELECT
                 COUNT(*) AS lNumRecs
              FROM people_names
              WHERE 1
                   $strWhereName $strWhereExtra
                   AND (NOT pe_bRetired)
                   AND pe_bBiz;";
         break;

      case CENUM_CONTEXT_BIZCONTACT:
         $sqlStr =
              "SELECT COUNT(*) AS lNumRecs
               FROM people_names
                  INNER JOIN biz_contacts ON pe_lKeyID=bc_lContactID
               WHERE NOT pe_bBiz
                  AND NOT pe_bRetired
                  AND NOT bc_bRetired
                  $strWhereName $strWhereExtra
               GROUP BY pe_lKeyID;";
         break;

      case CENUM_CONTEXT_HOUSEHOLD:
         $sqlStr =
             "SELECT
                 COUNT(*) AS lNumRecs
              FROM people_names
              WHERE 1
                   $strWhereName $strWhereExtra
                   AND pe_lHouseholdID=pe_lKeyID
                   AND (NOT pe_bRetired)
                   AND NOT pe_bBiz;";
         break;

      case CENUM_CONTEXT_PEOPLE:
         $sqlStr =
             "SELECT
                 COUNT(*) AS lNumRecs
              FROM people_names
              WHERE 1
                   $strWhereName $strWhereExtra
                   AND (NOT pe_bRetired)
                   AND NOT pe_bBiz;";
         break;

      case CENUM_CONTEXT_VOLUNTEER:
         if (!$bIncludeInactive){
            $strWhereName .= ' AND NOT vol_bInactive ';
         }
         $sqlStr =
             "SELECT
                 COUNT(*) AS lNumRecs
              FROM people_names
                 INNER JOIN volunteers on pe_lKeyID=vol_lPeopleID
              WHERE 1
                   $strWhereName $strWhereExtra
                   AND (NOT pe_bRetired)
                   AND NOT pe_bBiz;";
         break;

      default:
         screamForHelp($enumRecType.': invalid rec type<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         break;
   }

   $query = $CI->db->query($sqlStr);
   $numRows = $query->num_rows();

   if ($enumRecType == CENUM_CONTEXT_BIZCONTACT){
      return($numRows);
   }else {
      if ($numRows == 0){
         return(0);
      }else {
         $row = $query->row();
         return($row->lNumRecs);
      }
   }
}

?>