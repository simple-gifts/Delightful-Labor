<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class image_doc_view extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($enumContextType, $enumEntryType, $lFID){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, $enumContextType, $lFID, false);

      $displayData = array();

      $displayData = array();
      $displayData['lFID']            = $lFID = (integer)$lFID;
      $displayData['enumContextType'] = $enumContextType;
      $displayData['enumEntryType']   = $enumEntryType;
      $displayData['bImage']          = $bImage = ($enumEntryType == CENUM_IMGDOC_ENTRY_IMAGE);
      $displayData['bUpdate']         = false;

      $strLabel                       = $bImage ? 'Image' : 'Document';

         //-------------------------
         // models & helpers
         //-------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '',   'clsDateTime');
      $this->load->model  ('img_docs/mimage_doc',     'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags',  'cidTags');
      
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('auctions/auction');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('img_docs/img_doc_tags');

      loadSupportModels($enumContextType, $lFID);

      $this->clsImgDoc->loadDocImageInfoViaEntryContextFID($enumEntryType, $enumContextType, $lFID);
      $displayData['lNumImageDocs'] = $lNumImageDocs = $this->clsImgDoc->lNumImageDocs;
      if ($lNumImageDocs > 0){
         $displayData['imageDocs'] = &$this->clsImgDoc->imageDocs;
         foreach ($displayData['imageDocs'] as $img){
            $img->strTagsUL = $this->cidTags->strImgDocTagsUL($img->lKeyID);
         }
      }

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['contextSummary'] = strContextHTML($enumContextType, $lFID, $displayData['strContextName']);
      $displayData['pageTitle']      = breadCrumbsToRecViewViaContextType($enumContextType, $lFID, 'View '.$strLabel.'s');
      $displayData['title']          = CS_PROGNAME.' | '.$strLabel.'s';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'img_docs/view_image_docs_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }












}
