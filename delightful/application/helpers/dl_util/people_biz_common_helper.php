<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('dl_util/people_biz_common');
---------------------------------------------------------------------*/

function bizOrPeopleViaFID($lFID, &$bBiz, &$clsPeople, &$clsBiz, &$strSafeName){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $CI =& get_instance();
   $sqlStr =
        "SELECT pe_bBiz
         FROM people_names
         WHERE pe_lKeyID=$lFID
            AND NOT pe_bRetired;";
   $query = $CI->db->query($sqlStr);
   $numRows = $query->num_rows();

   if ($numRows==0) {
      screamForHelpSQL($sqlStr);
      screamForHelp('Unexpected EOF<br></b>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
   }else {
      $row = $query->row();
      $bBiz = (boolean)$row->pe_bBiz;
      if ($bBiz){
         $clsBiz->loadBizRecsViaBID($lFID);
         $strSafeName = $clsBiz->bizRecs[0]->strSafeName;
      }else {
         $clsPeople->loadPeopleViaPIDs($lFID, false, false);
         $strSafeName = $clsPeople->people[0]->strSafeName;
      }
   }
}


?>