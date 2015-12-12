<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2013 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('ajax/mpeople_biz', 'cAjaxPepBiz');
---------------------------------------------------------------------

---------------------------------------------------------------------*/


//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mpeople_biz extends CI_Model{

   public $lNumRows, $results;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->lNumRows = $this->results = null;
   }

   function people_biz_search($strSearch, $lLimit=30){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lLen = strlen($strSearch);
      $sqlStr =
         'SELECT pe_strLName, pe_strFName, pe_bBiz, pe_lKeyID, pe_strAddr1
          FROM people_names
          WHERE NOT pe_bRetired
             AND LEFT(pe_strLName, '.$lLen.')='.strPrepStr($strSearch)."
          ORDER BY pe_strLName, pe_strFName, pe_lKeyID
          LIMIT 0, $lLimit;";

      $query = $this->db->query($sqlStr);
      $this->lNumRows = $query->num_rows();

      if ($this->lNumRows > 0){
         $idx = 0;
         $this->results = array();
         foreach ($query->result() as $row){
            $this->results[$idx] = new stdClass;
            $r = &$this->results[$idx];
            $r->lKeyID   = $row->pe_lKeyID;
            $r->strFName = $row->pe_strFName;
            $r->strLName = $row->pe_strLName;
            $r->bBiz     = $row->pe_bBiz;
            $r->strAddr1 = $row->pe_strAddr1;
            if ($r->bBiz){
               $r->strSafeName = htmlspecialchars($r->strLName).' (business)';
            }else {
               $r->strSafeName = htmlspecialchars($r->strLName.', '.$r->strFName);
            }
            ++$idx;
         }
      }
   }

   function strXML_peopleBiz($bShowBlank=true){
   //---------------------------------------------------------------------
   // caller must first call $this->people_biz_search($strSearch)
   //---------------------------------------------------------------------
      $strXML = '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n"
            .'<peoplebiz>'."\n";

      if ($bShowBlank){
         $strXML .= 
              '<name>
                  <id>-1</id>
                  <nameaddr> </nameaddr>
               </name>'."\n";
      }
/*
*/

      foreach ($this->results as $result){
         $lKeyID = $result->lKeyID;

         if ($result->strAddr1.'' == ''){
            $strAddr = ' (no address)';
         }else {
            $strAddr = ' / '.htmlspecialchars($result->strAddr1);
         }

         $strXML .= '
               <name>
                  <id>'.$lKeyID.'</id>
                  <nameaddr>'.$result->strSafeName.$strAddr.'</nameaddr>
               </name>'."\n";
      }
      $strXML .= '</peoplebiz>';
      return($strXML);
   }




}