<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class id_overview extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function overview(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('showReports')) return;
      
      $displayData        = array();
      $displayData['js']  = '';
      
         /*-------------------------------------------------
            models and helpers
           -------------------------------------------------*/
      $this->load->helper('img_docs/img_doc_tags');
      $this->load->model ('img_docs/mimg_doc_tags',    'cidTags');
      $this->load->model ('img_docs/mimage_doc',       'clsImgDoc');
      $this->load->model ('img_docs/mimage_doc_stats', 'cIDStats');
      $this->load->helper('img_docs/link_img_docs');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper ('js/div_hide_show');
      
      $displayData['js'] .= showHideDiv();
      
      $displayData['lTotImages'] = $this->cIDStats->lTotImages();
      $displayData['lTotDocs']   = $this->cIDStats->lTotDocs();
      
      $this->cIDStats->lImagesGrouped($displayData['lNumImgContextGroups'], $displayData['contextImgGroups']);
      $this->cIDStats->lDocsGrouped  ($displayData['lNumDocContextGroups'], $displayData['contextDocGroups']);
   
      $this->cIDStats->loadImageTags($displayData['tagsImage']);
      $this->cIDStats->loadDocTags  ($displayData['tagsDoc']);
      
      foreach ($displayData['tagsImage'] as $ti){
         if ($ti->lNumTags > 0){
            foreach ($ti->tags as $tag){
               $tag->lCnt = $this->cidTags->lNumImgDocTagsViaTagID($tag->lTagID);
            }
         }
      }
   
      foreach ($displayData['tagsDoc'] as $td){
         if ($td->lNumTags > 0){
            foreach ($td->tags as $tag){
               $tag->lCnt = $this->cidTags->lNumImgDocTagsViaTagID($tag->lTagID);
            }
         }
      }      
   
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle'] = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                             .' | Image/Document Overview';

      $displayData['title']          = CS_PROGNAME.' | Images/Documents';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'img_docs/image_doc_overview_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   
}
