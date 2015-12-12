<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2013 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//---------------------------------------------------------------------
//if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class ajax_names extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }
   
   function lookupNamesBiz(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $strSearch = urldecode($_GET['search']);
//echo($strSearch);      
      $this->load->model('ajax/mpeople_biz', 'cAjaxPepBiz');
      $this->cAjaxPepBiz->people_biz_search($strSearch, 30);
      
      if ($this->cAjaxPepBiz->lNumRows == 0){
         echo('');         
      }else {
         echo($this->cAjaxPepBiz->strXML_peopleBiz());
      }
   }
   
}
