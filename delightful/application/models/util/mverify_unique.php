<?php
//---------------------------------------------------------------------
// copyright (c) 2012 Database Austin
//
// Serving the Children of India
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
/*---------------------------------------------------------------------
   $this->load->model('util/mverify_unique');
---------------------------------------------------------------------*/
// screamForHelp('<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
//traceFilepath(__FILE__);

class mverify_unique extends CI_Model {

    function __construct(){
        parent::__construct();
    }

   public function bVerifyUniqueText(
                   $strTestText, $strTxtFN,
                   $lKeyExclude, $strKeyFN,
                   $bExcludeRetired, $strRetiredFN,
                   $bQualSubGroup1, $lKeySubGroup1, $strSubGroupFN_1,
                   $bQualSubGroup2, $lKeySubGroup2, $strSubGroupFN_2,
                   $strTableName) {
   //---------------------------------------------------------------------
   // return true if the text entry is not already in the database.
   // Inputs:
   //    $strTestText - text to test against
   //    $strTxtFN    - field name of the text field
   //    $lKeyExclude - exclusion key value (if testing for uniqueness but want to exclude
   //                   the current entry (for updates))
   //    $strKeyFN    - the field name of the key
   //    $bExcludeRetired - if true, exclude retired table entries from the test
   //    $strRetiredFN - field name of retired entry flag (assumed to be boolean)
   //    $strTableName - the database table name
   //
   //    $bQualSubGroup1/2, $lKeySubGroup1/2, $strSubGroupFN_1/2
   //                  - if true, qualify the search to records that have matching
   //                    key values
   //---------------------------------------------------------------------
      $strQual1 = $strQual2 = '';
      if ($bQualSubGroup1){
         if (is_numeric($lKeySubGroup1)){
            $strQual1 = "AND ($strSubGroupFN_1 = $lKeySubGroup1) ";
         }else {
            $strQual1 = "AND ($strSubGroupFN_1 = ".strPrepStr($lKeySubGroup1).') ';
         }
      }

      if ($bQualSubGroup2){
         if (is_numeric($lKeySubGroup2)){
            $strQual2 = "AND ($strSubGroupFN_2 = $lKeySubGroup2) ";
         }else {
            $strQual2 = "AND ($strSubGroupFN_2 = ".strPrepStr($lKeySubGroup2).') ';
         }
      }

      $sqlStr =
          "SELECT count($strKeyFN) as lNumMatch
           FROM $strTableName
           WHERE ucase($strTxtFN)=".strPrepStr(strtoupper($strTestText))."
              AND $strKeyFN<>$lKeyExclude
              $strQual1
              $strQual2 "
             .($bExcludeRetired ? "AND ($strRetiredFN=0) ":'').';';
             
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         screamForHelp('Unexpected SQL error - forms/util/util_VerifyUnique.php/bVerifyUniqueText');
      }else {
         $row = $query->row();
         return($row->lNumMatch==0);
      }
   }

}

?>