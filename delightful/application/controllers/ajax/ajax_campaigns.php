<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2011 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//---------------------------------------------------------------------
//if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class ajax_campaigns extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function parseAjaxCampaign($strType, $lAcctID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------  
      switch ($strType){
         case 'loadViaAcctID':
            $this->buildCampaignXMLViaAcctID((integer)$lAcctID);
            break;

         default:
            screamForHelp($strType.': INVALID PROCESSING OPTIONS</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
            break;
      }
   }

   function buildCampaignXMLViaAcctID($lAcctID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------  
      $this->load->model('donations/maccts_camps', 'clsAC');
      $this->clsAC->loadCampaigns(false, true, $lAcctID, false, null);
      echoT($this->clsAC->strXMLCampaigns(-1, false));
   }

}
?>