<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class biz_directory extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($strLookupLetter, $lStartRec=0, $lRecsPerPage=50){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->viewBizCon($strLookupLetter, $lStartRec, $lRecsPerPage, false);
   }

   function viewCBizName($strLookupLetter, $lStartRec=0, $lRecsPerPage=50){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->viewBizCon($strLookupLetter, $lStartRec, $lRecsPerPage, true);
   }

   function viewBizCon($strLookupLetter, $lStartRec, $lRecsPerPage, $bShowContactNames){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showPeople')) return;

      $strLookupLetter = urldecode($strLookupLetter);
      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('dl_util/rs_navigate');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('biz/biz');
      $this->load->helper ('people/people_display');
      $this->load->helper ('dl_util/directory');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model  ('sponsorship/msponsorship', 'clsSpon');
      $this->load->model  ('admin/madmin_aco', 'clsACO');
      $this->load->model  ('biz/mbiz', 'clsBiz');
      $this->load->model  ('donations/mdonations', 'clsGifts');
      $this->load->model  ('people/mpeople', 'clsPeople');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

         //------------------------------------------------
         // sanitize the lookup letter and inputs
         //------------------------------------------------
      $displayData['strDirLetter'] = $strLookupLetter = strSanitizeLetter($strLookupLetter);

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $this->clsSpon->bUseDateRange = false;

      $displayData['strDirTitle'] = 'Business Directory';
      $displayData['lNumRecsTot'] =  lNumPeopleRecsViaLetter($strLookupLetter, CENUM_CONTEXT_BIZ); //$this->clsBiz->lNumBizRecords();

      if (bAllowAccess('showGiftHistory')){
         $this->clsGifts->bUseDateRange = false;
         $this->clsGifts->cumulativeOpts = new stdClass;
         $this->clsGifts->cumulativeOpts->enumCumulativeSource = CENUM_CONTEXT_BIZ;
      }
      $lNumRecs = lNumPeopleRecsViaLetter($strLookupLetter, CENUM_CONTEXT_BIZ);

         //------------------------------------------------
         // set up directory display
         //------------------------------------------------
      if ($bShowContactNames){
         $displayData['strRptTitle']    = 'Business Contact Directory';
         $displayData['strRecNavTitle'] = 'Business Contact Directory: ';
      }else {
         $displayData['strRptTitle']    = 'Business/Organization Directory';
         $displayData['strRecNavTitle'] = 'Business Directory: ';
      }

      $displayData['strDirLetter'] = $strLookupLetter;
      $displayData['strLinkBase']  = $strLinkBase = 'biz/biz_directory/'
                                           .($bShowContactNames ? 'viewCBizName' : 'view').'/';
      $displayData['strDirTitle']  = strDisplayDirectory(
                                         $strLinkBase, ' class="directoryLetters" ', $strLookupLetter,
                                         true, $lStartRec, $lRecsPerPage);

         //------------------------------------------------
         // load biz directory page
         //------------------------------------------------
      $strWhereExtra = $this->clsPeople->strWhereByLetter($strLookupLetter, CENUM_CONTEXT_BIZ, false);
      $this->clsBiz->loadBizDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage,
                                          !$bShowContactNames, !$bShowContactNames);
      $displayData['lNumDisplayRows']      = $lNumBizRecs = $this->clsBiz->lNumBizRecs;
      $displayData['directoryRecsPerPage'] = $lRecsPerPage;
      $displayData['directoryStartRec']    = $lStartRec;
      $displayData['bizRecs']              = $this->clsBiz->bizRecs;

      if ($lNumBizRecs > 0){
         foreach ($this->clsBiz->bizRecs as $biz){
            $this->clsBiz->lBID = $lBID = $biz->lKeyID;
            if ($bShowContactNames){
               $this->clsBiz->contactList(true, false, false, '', '');
               $biz->lNumContacts = $lNumCon = $this->clsBiz->lNumContacts;
               if ($lNumCon > 0) $biz->contacts = arrayCopy($this->clsBiz->contacts);
            }else {
               $biz->lNumContacts  = $this->clsBiz->lNumContacts(true, false);
            }

         }
      }

      initBizReportDisplay($displayData);
      if ($bShowContactNames){
         $displayData['showFields']->bContactNames = true;
         $displayData['showFields']->bGiftSummary  =
         $displayData['showFields']->bSponsor      =
         $displayData['showFields']->bRemBiz       =
         $displayData['showFields']->bContacts     = false;
      }


         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = array('biz/biz_directory_view', 'biz/rpt_generic_biz_list');
      $displayData['pageTitle']    = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                              .' | Directory';

      $displayData['title']        = CS_PROGNAME.' | Businesses';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function viewCName($strLookupLetter='A', $lStartRec=0, $lRecsPerPage=50){
   //---------------------------------------------------------------------
   // contact directory by name
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showPeople')) return;

      $strLookupLetter = urldecode($strLookupLetter);
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper ('dl_util/rs_navigate');
//      $this->load->helper ('dl_util/email_web');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('biz/biz');
      $this->load->helper ('people/people_display');
      $this->load->helper ('dl_util/directory');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model  ('biz/mbiz', 'clsBiz');
      $this->load->model  ('people/mpeople', 'clsPeople');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

         //------------------------------------------------
         // sanitize the lookup letter and inputs
         //------------------------------------------------
      $displayData['strDirLetter'] = $strLookupLetter = strSanitizeLetter($strLookupLetter);

      $strWhereExtra = $this->clsPeople->strWhereByLetter($strLookupLetter, CENUM_CONTEXT_PEOPLE, '');
      $this->clsBiz->loadContactNameDirectoryPage($strWhereExtra, $lStartRec, $lRecsPerPage);

         //------------------------------------------------
         // stripes
         //------------------------------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;

         //------------------------------------------------
         // set up directory display
         //------------------------------------------------
      $displayData['strRptTitle']  = 'Contact Directory';

      $displayData['lNumRecsTot'] =  lNumPeopleRecsViaLetter($strLookupLetter, CENUM_CONTEXT_BIZCONTACT);
      $displayData['strDirLetter'] = $strLookupLetter;
      $displayData['strLinkBase']  = $strLinkBase = 'biz/biz_directory/viewCName/';
      $displayData['strDirTitle']  = strDisplayDirectory(
                                         $strLinkBase, ' class="directoryLetters" ', $strLookupLetter,
                                         true, $lStartRec, $lRecsPerPage);

      $displayData['strRecNavTitle'] = 'Contact Directory (by Contact Name): ';

      $displayData['lNumDisplayRows']      = $lNumContactsNames = $this->clsBiz->lNumContactsNames;
      $displayData['directoryRecsPerPage'] = $lRecsPerPage;
      $displayData['directoryStartRec']    = $lStartRec;
      $displayData['bizRecs']              = &$this->clsBiz->contactsNames;

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['mainTemplate'] = array('biz/biz_directory_view', 'biz/contact_dir');
      $displayData['pageTitle']    = anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                              .' | Contact Directory by Name';

      $displayData['title']        = CS_PROGNAME.' | Businesses';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }

}



