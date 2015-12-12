<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admin_imgdoc_tags extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
//      setGlobals($this);
      setGlobals($this);
   }

	public function viewTags($enumContext) {
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();
      $displayData['enumContext'] = $enumContext;

         //---------------------------------
         // models and helpers
         //---------------------------------
      $this->load->helper ('img_docs/img_doc_tags');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->model  ('img_docs/mimg_doc_tags',  'cidTags');
      $this->load->library('util/up_down_top_bottom');
      $params = array('enumStyle' => 'enpRpt');
      $this->load->library('generic_rpt', $params);

      $displayData['strIDType']  = $strIDType  = imgDocTags\strXlateImgDocType($enumContext, $bImage);
      $displayData['strContext'] = $strContext = imgDocTags\strXlateImgDocContext($enumContext);

      $this->cidTags->loadImgDocsViaContext($enumContext, $displayData['lNumTags'], $displayData['tags']);

         //-----------------------------
         // breadcrumbs and headers
         //-----------------------------
      $displayData['title']        = CS_PROGNAME.' | Admin';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | Tags for '.$strContext;
      $displayData['mainTemplate'] = 'admin/img_doc_tag_dir_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addEdit($lDIT_ID, $enumContext=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

      $displayData['lDIT_ID'] = $lDIT_ID = (integer)$lDIT_ID;
      $bNew = $lDIT_ID <= 0;

         //---------------------------------
         // models and helpers
         //---------------------------------
      $this->load->helper ('img_docs/img_doc_tags');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->model  ('img_docs/mimg_doc_tags',  'cidTags');

      $params = array('enumStyle' => 'enpRpt');
      $this->load->library('generic_rpt', $params);

      $this->cidTags->loadSingleTag($lDIT_ID, $lNumTags, $tagInfo);
      $displayData['tag'] = $tag = &$tagInfo[0];
      if (!$bNew){
         $enumContext = $tag->enumContext;
      }
      $displayData['enumContext'] = $enumContext;
      $displayData['strIDType']   = $strIDType  = imgDocTags\strXlateImgDocType($enumContext, $bImage);
      $displayData['strContext']  = $strContext = imgDocTags\strXlateImgDocContext($enumContext);

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtDDLEntry', 'Tag Entry',
                                        'trim|required|callback_ufTagAddEditUnique['
                                        .$lDIT_ID.','.$enumContext.','.$strContext.']');

		if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');

         if (validation_errors()==''){
            $displayData['strDDLEntry'] = htmlspecialchars($tag->strDDLEntry);
         }else {
            setOnFormError($displayData);
            $displayData['strDDLEntry'] = set_value('txtDDLEntry');
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle'] = anchor('main/menu/admin',        'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | '.anchor('admin/admin_imgdoc_tags/viewTags/'.$enumContext, 'Tags for '.$strContext, 'class="breadcrumb"')
                              .' | '.($bNew ? 'Add new ' : 'Update ').'list entry';

         $displayData['title']          = CS_PROGNAME.' | Personalization';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['bNew']           = $bNew;

         $displayData['mainTemplate']   = 'personalization/img_doc_tag_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');

      }else {
         $strDDLEntry = trim($_POST['txtDDLEntry']);
         if ($bNew){
            $lNewSortIDX = $this->cidTags->lMaxSortIDX($enumContext) + 1;
            $lDIT_ID     = $this->cidTags->addTagEntry($strDDLEntry, $enumContext, $lNewSortIDX);
            $this->session->set_flashdata('msg', 'The tag entry for <b>"'.$strContext.'"</b> was added');
         }else {
            $this->cidTags->updateTagEntry($strDDLEntry, $lDIT_ID);
            $this->session->set_flashdata('msg', 'The tag for <b>"'.$strContext.'"</b> was updated');
         }
         redirect('admin/admin_imgdoc_tags/viewTags/'.$enumContext);
      }
   }

   function ufTagAddEditUnique($strValue, $params){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $exParams    = explode(',', $params);
      $lTagID      = (integer)$exParams[0];
      $enumContext = $exParams[1];
      $strContext  = $exParams[2];

      $this->load->model('util/mverify_unique', 'clsUnique');
      if ($this->clsUnique->bVerifyUniqueText(
                $strValue,    'dit_strDDLEntry',
                $lTagID,      'dit_lKeyID',
                true,         'dit_bRetired',
                true, $enumContext, 'dit_enumContext',
                false, null, null,
                'doc_img_tag_ddl')){
         return(true);
      }else {
         $this->form_validation->set_message('ufTagAddEditUnique', 'The tag "'
                     .htmlspecialchars($strValue).'" is already defined for this account.');
         return(false);
      }
   }

   function remove($enumContext, $lDIT_ID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $lDIT_ID = (int)$lDIT_ID;

         //---------------------------------
         // models and helpers
         //---------------------------------
      $this->load->helper ('img_docs/img_doc_tags');
      $this->load->model('img_docs/mimg_doc_tags', 'cidTags');

      $strContext = imgDocTags\strXlateImgDocContext($enumContext);
      $this->cidTags->removeImgDocTag($lDIT_ID);

      $this->session->set_flashdata('msg', 'The tag for <b>"'.$strContext.'"</b> was removed');
      redirect('admin/admin_imgdoc_tags/viewTags/'.$enumContext);
   }

   function moveEntries($enumContext, $lDIT_ID, $enumMove){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $lDIT_ID = (int)$lDIT_ID;

      $this->load->library('util/up_down_top_bottom', '', 'upDown');

      $this->upDown->enumMove            = $enumMove;
      $this->upDown->enumRecType         = 'img doc tag';
      $this->upDown->strUfieldDDL        = 'doc_img_tag_ddl';
      $this->upDown->strUfieldDDLKey     = 'dit_lKeyID';
      $this->upDown->strUfieldDDLSort    = 'dit_lSortIDX';
      $this->upDown->strUfieldDDLQual1   = 'dit_enumContext';
      $this->upDown->strUfieldDDLRetired = 'dit_bRetired';
      $this->upDown->lUfieldDDLQual1Val  = $enumContext;
      $this->upDown->lKeyID              = $lDIT_ID;
      $this->upDown->moveRecs();
      
      $this->session->set_flashdata('msg', 'The entries were re-ordered');
      redirect('admin/admin_imgdoc_tags/viewTags/'.$enumContext);
   }



}
