<?php
class biz_contacts extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewViaBizID($lBizID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lBizID, 'business ID');

      $displayData = array();
      $displayData['lBizID'] = $lBizID = (integer)$lBizID;

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',        'clsDateTime');
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->model('biz/mbiz', 'clsBiz');
//      $this->load->helper ('dl_util/email_web');

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

      $this->clsBiz->loadBizRecsViaBID($lBizID);
      $displayData['contextSummary'] = $this->clsBiz->strBizHTMLSummary();
      $displayData['strBizName'] = $this->clsBiz->bizRecs[0]->strBizName;

      $this->clsBiz->contactList(true, false, false);
      $displayData['contacts'] = &$this->clsBiz->contacts;
      $displayData['lNumContacts'] = $this->clsBiz->lNumContacts;

         //-----------------------------
         // breadcrumbs & page setup
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Business';
      $displayData['pageTitle']    = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                              .' | '.anchor('biz/biz_record/view/'.$lBizID, 'Business Record', 'class="breadcrumb"')
                              .' | Business Contacts';

      $displayData['mainTemplate'] = 'biz/contact_list_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');


   }
/*
*/



}