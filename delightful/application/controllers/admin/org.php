<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*---------------------------------------------------------------------
   Delightful Labor!

   copyright (c) 2014 by Database Austin
   Austin, Texas

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------*/

class org extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function orgView($lChapterID = null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glChapterID;

      if (!bTestForURLHack('adminOnly')) return;

      if (is_null($lChapterID)){
         $lChapterID = $glChapterID;
      }else {
         $lChapterID = (int)$lChapterID;
      }

      $displayData = array();
      $displayData['js'] = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper ('img_docs/img_doc_tags');
      $this->load->helper ('dl_util/directory');
      $this->load->helper ('dl_util/rs_navigate');
      $this->load->model  ('admin/morganization',    'clsChapter');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->model  ('img_docs/mimage_doc',    'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags', 'cidTags');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('js/div_hide_show');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');
      $displayData['js'] .= showHideDiv();

      $this->clsChapter->lChapterID = $lChapterID;
      $this->clsChapter->loadChapterInfo();
      $displayData['chapterRec'] = $this->clsChapter->chapterRec;

         //-------------------------------
         // images and documents
         //-------------------------------
      loadImgDocRecView($displayData, CENUM_CONTEXT_ORGANIZATION, $lChapterID);

      $this->load->library('generic_form');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

      $displayData['title']          = CS_PROGNAME.' | Your Organization';
      $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"').' | Your Organization';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'admin/organization_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addEdit($id){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;

      $displayData = array();
      $displayData['clsChapter'] = new stdClass;

      $displayData['id'] = $lChapterID = (integer)$id;
      $displayData['bNew']    = $bNew    = $id <= 0;

         // load models
      $this->load->model('admin/morganization', 'clsChapter');
      $this->load->model('admin/madmin_aco',    'clsACO');
      $this->load->model('admin/mtime_zones',   'cTZ');
      $this->load->helper('dl_util/web_layout');

      $this->clsChapter->lChapterID = $id;
      $this->clsChapter->loadChapterInfo();
      $cRec = $this->clsChapter->chapterRec;

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtChapter',  'Organization Name',   'trim|required');
      $this->form_validation->set_rules('txtBanner',   'Banner Tag Line',     'trim|required');
      $this->form_validation->set_rules('rdoACO',      'Accounting Country',  'trim|required');
      $this->form_validation->set_rules('rdoDefDate',  'Date/Time Format',    'trim|required');
      $this->form_validation->set_rules('ddlTZ',       'Time Zone',           'trim|required|callback_verifyTimeZone');
      $this->form_validation->set_rules('txtAddr1'    );
      $this->form_validation->set_rules('txtAddr2'    );
      $this->form_validation->set_rules('txtCity'     );
      $this->form_validation->set_rules('txtState'    );
      $this->form_validation->set_rules('txtCountry'  );
      $this->form_validation->set_rules('txtZip'      );
      $this->form_validation->set_rules('txtPhone'    );
      $this->form_validation->set_rules('txtFax'      );
      $this->form_validation->set_rules('txtEmail',    'EMail', 'trim|valid_email');
      $this->form_validation->set_rules('txtWebSite'  );
      $this->form_validation->set_rules('txtDefAC'    );
      $this->form_validation->set_rules('txtDefState' );
      $this->form_validation->set_rules('txtDefCountry');
      $this->form_validation->set_rules('txtVocZip',    '<b>Zip Code Vocabulary</b>',   'trim|required');
      $this->form_validation->set_rules('txtVocState',  '<b>State Vocabulary</b>',      'trim|required');
      $this->form_validation->set_rules('txtJobSkills', '<b>Job Skills Vocabulary</b>', 'trim|required');

      if ($this->form_validation->run() == FALSE){

         $displayData['title']        = CS_PROGNAME.' | Your Organization';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('admin/org/orgView', 'Your Organization', 'class="breadcrumb"')
                                 .' |  Edit Organization';
         $displayData['nav']          = $this->mnav_brain_jar->navData();

         $this->load->library('generic_form');

         $this->cTZ->loadTimeZones();

         if (validation_errors()==''){
            $displayData['clsChapter']->strName          = htmlspecialchars($cRec->strChapterName);
            $displayData['clsChapter']->strBanner        = htmlspecialchars($cRec->strBannerTagLine);
            $displayData['clsChapter']->strPhone         = htmlspecialchars($cRec->strPhone);
            $displayData['clsChapter']->strFax           = htmlspecialchars($cRec->strFax);
            $displayData['clsChapter']->strEmail         = htmlspecialchars($cRec->strEmail);
            $displayData['clsChapter']->strAddr1         = htmlspecialchars($cRec->strAddress1);
            $displayData['clsChapter']->strAddr2         = htmlspecialchars($cRec->strAddress2);
            $displayData['clsChapter']->strCity          = htmlspecialchars($cRec->strCity);
            $displayData['clsChapter']->strState         = htmlspecialchars($cRec->strState);
            $displayData['clsChapter']->strCountry       = htmlspecialchars($cRec->strCountry);
            $displayData['clsChapter']->strZip           = htmlspecialchars($cRec->strZip);

            $displayData['clsChapter']->strWebSite       = htmlspecialchars($cRec->strWebSite);
            $displayData['clsChapter']->strEmail         = htmlspecialchars($cRec->strEmail);
            $displayData['clsChapter']->strDefAreaCode   = htmlspecialchars($cRec->strDefAreaCode);
            $displayData['clsChapter']->strDefState      = htmlspecialchars($cRec->strDefState);
            $displayData['clsChapter']->strDefCountry    = htmlspecialchars($cRec->strDefCountry);
            $displayData['clsChapter']->strDateFormatRadio =
                             $this->clsChapter->strDefaultDateFormatRadio('rdoDefDate', $cRec->bUS_DateFormat);

            $displayData['clsChapter']->strACORadio      = $this->clsACO->strACO_Radios ($cRec->lDefaultACO, 'rdoACO');
            $displayData['clsChapter']->strTimeZoneDDL   = $this->cTZ->strTimeZoneDDL('ddlTZ', 'orgTZ', $cRec->lTimeZoneID, true);

            $displayData['clsChapter']->txtVocZip        = htmlspecialchars($cRec->vocabulary->vocZip);
            $displayData['clsChapter']->txtVocState      = htmlspecialchars($cRec->vocabulary->vocState);
            $displayData['clsChapter']->txtJobSkills     = htmlspecialchars($cRec->vocabulary->vocJobSkills);
         }else {
            setOnFormError($displayData);
            $displayData['clsChapter']->strName          = set_value('txtChapter');
            $displayData['clsChapter']->strBanner        = set_value('txtBanner');
            $displayData['clsChapter']->strPhone         = set_value('txtPhone');
            $displayData['clsChapter']->strFax           = set_value('txtFax');
            $displayData['clsChapter']->strEmail         = set_value('txtEmail');
            $displayData['clsChapter']->strAddr1         = set_value('txtAddr1');
            $displayData['clsChapter']->strAddr2         = set_value('txtAddr2');
            $displayData['clsChapter']->strCity          = set_value('txtCity');
            $displayData['clsChapter']->strState         = set_value('txtState');
            $displayData['clsChapter']->strCountry       = set_value('txtCountry');
            $displayData['clsChapter']->strZip           = set_value('txtZip');

            $displayData['clsChapter']->strWebSite       = set_value('txtWebSite');
            $displayData['clsChapter']->strEmail         = set_value('txtEmail');
            $displayData['clsChapter']->strDefAreaCode   = set_value('txtDefAC');
            $displayData['clsChapter']->strDefState      = set_value('txtDefState');
            $displayData['clsChapter']->strDefCountry    = set_value('txtDefCountry');
            $displayData['clsChapter']->strDateFormatRadio =
                             $this->clsChapter->strDefaultDateFormatRadio('rdoDefDate', set_value('rdoDefDate')=='US');

            $displayData['clsChapter']->strACORadio      = $this->clsACO->strACO_Radios (set_value('rdoACO'), 'rdoACO');
            $displayData['clsChapter']->strTimeZoneDDL   = $this->cTZ->strTimeZoneDDL('ddlTZ', 'orgTZ', set_value('ddlTZ'), true);

            $displayData['clsChapter']->txtVocZip        = set_value('txtVocZip');
            $displayData['clsChapter']->txtVocState      = set_value('txtVocState');
            $displayData['clsChapter']->txtJobSkills     = set_value('txtJobSkills');
         }

         $displayData['mainTemplate'] = 'admin/organization_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $cRec->strChapterName   = xss_clean(trim($_POST['txtChapter']));
         $cRec->strBannerTagLine = xss_clean(trim($_POST['txtBanner']));
         $cRec->strPhone         = xss_clean(trim($_POST['txtPhone']));
         $cRec->strFax           = xss_clean(trim($_POST['txtFax']));
         $cRec->strEmail         = xss_clean(trim($_POST['txtEmail']));
         $cRec->strAddress1      = xss_clean(trim($_POST['txtAddr1']));
         $cRec->strAddress2      = xss_clean(trim($_POST['txtAddr2']));
         $cRec->strCity          = xss_clean(trim($_POST['txtCity']));
         $cRec->strState         = xss_clean(trim($_POST['txtState']));
         $cRec->strCountry       = xss_clean(trim($_POST['txtCountry']));
         $cRec->strZip           = xss_clean(trim($_POST['txtZip']));
         $cRec->strWebSite       = xss_clean(trim($_POST['txtWebSite']));
         $cRec->strEmail         = xss_clean(trim($_POST['txtEmail']));
         $cRec->strDefAreaCode   = xss_clean(trim($_POST['txtDefAC']));
         $cRec->strDefState      = xss_clean(trim($_POST['txtDefState']));
         $cRec->strDefCountry    = xss_clean(trim($_POST['txtDefCountry']));
         $cRec->bUS_DateFormat   = trim($_POST['rdoDefDate'])=='US';
         $cRec->lDefaultACO      = (int) trim($_POST['rdoACO']);
         $cRec->lTimeZoneID      = (int) trim($_POST['ddlTZ']);

         $cRec->vocabulary->vocZip       = xss_clean(trim($_POST['txtVocZip']));
         $cRec->vocabulary->vocState     = xss_clean(trim($_POST['txtVocState']));
         $cRec->vocabulary->vocJobSkills = xss_clean(trim($_POST['txtJobSkills']));

         $_SESSION[CS_NAMESPACE.'_chapter']->vocZip       = $cRec->vocabulary->vocZip;
         $_SESSION[CS_NAMESPACE.'_chapter']->vocState     = $cRec->vocabulary->vocState;
         $_SESSION[CS_NAMESPACE.'_chapter']->vocJobSkills = $cRec->vocabulary->vocJobSkills;

         $_SESSION[CS_NAMESPACE.'_chapter']->strBanner      = $cRec->strBannerTagLine;
         $_SESSION[CS_NAMESPACE.'_chapter']->strChapterName = $cRec->strChapterName;

         $strTZ = $this->cTZ->strTimeZoneViaID($cRec->lTimeZoneID);
         $_SESSION[CS_NAMESPACE.'_chapter']->lTimeZoneID    = $cRec->lTimeZoneID;
         $_SESSION[CS_NAMESPACE.'_chapter']->strTimeZone    = $strTZ;

         if ($bNew){
            $lNewChapterID = $this->clsChapter->insertChapter();
            $this->session->set_flashdata('msg', 'The organization information was added');
         }else {
            $this->clsChapter->updateChapter($lChapterID);
            $this->session->set_flashdata('msg', 'Your organization\'s information was updated');
         }
         redirect('admin/org/orgView');
      }
   }

   function verifyTimeZone($strValue){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lSelectID = (int)$strValue;
      if ($lSelectID <= 0){
         $this->form_validation->set_message('verifyTimeZone', 'Please select a time zone.');
         return(false);
      }else {
         return(true);
      }
   }

}