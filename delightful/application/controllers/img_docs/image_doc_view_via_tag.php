<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class image_doc_view_via_tag extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function view($lTagID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
//      if (!bTestForURLHack('showReports')) return;
      
      $displayData        = array();
      $displayData['js']  = '';
      
         /*-------------------------------------------------
            models and helpers
           -------------------------------------------------*/
      $this->load->helper ('img_docs/img_doc_tags');
      $this->load->model  ('img_docs/mimage_doc',       'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags',    'cidTags');
      $this->load->model  ('img_docs/mimage_doc_stats', 'cIDStats');
      $this->load->library('util/dl_date_time', '',   'clsDateTime');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('dl_util/web_layout');
      
      $this->cidTags->loadSingleTag($lTagID, $lNumTags, $tagInfo);
      $displayData['tag'] = $tag = &$tagInfo[0];
      $displayData['enumParentContext'] = $enumParentContext = $tag->enumParentContext;
      $displayData['enumEntryType'] = $tag->enumEntryType;
      
      if (!bPermitViaContext($enumParentContext)){
         badBoyRedirect('Your permissions do not give you access to these features.');
         return;
      }
      
      loadSupportModels($enumParentContext, null);
      
      $lNumParentRecs = $this->cidTags->lNumImgDocTagsViaTagID($lTagID);
      $displayData['strTagTableHeading'] = $this->cIDStats->strTagTableHeaderViaTag($lNumParentRecs, $tag);
      
      $this->clsImgDoc->loadDocImageInfoViaTagID($lTagID, $enumParentContext);
      $displayData['lNumImageDocs'] = $lNumImageDocs = $this->clsImgDoc->lNumImageDocs;
      if ($lNumImageDocs > 0){
         $displayData['imageDocs'] = &$this->clsImgDoc->imageDocs;
         foreach ($displayData['imageDocs'] as $img){
            $img->strTagsUL = $this->cidTags->strImgDocTagsUL($img->lKeyID);
         }
      }
  
/*      
      $lNumParentRecs = $this->cidTags->lNumImgDocTagsViaTagID($lTagID);
      $displayData['tagTable'] = $this->cIDStats->strTagTableViaTag($lNumParentRecs, $tag);
*/   
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle'] = anchor('main/menu/reports', 'Reports', 'class="breadcrumb"')
                           .' | '.anchor('reports/image_doc/id_overview/overview', 'Image/Document Overview', 'class="breadcrumb"')
                           .' | Via Tag';

      $displayData['title']          = CS_PROGNAME.' | Images/Documents';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'img_docs/image_doc_via_tag_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   
}
   
