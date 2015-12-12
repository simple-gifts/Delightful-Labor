<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class upload_image_doc extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function add($enumContextType, $enumEntryType, $lFID){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      global $gstrFormatDatePicker, $gdteNow, $gbDateFormatUS;

      if (!bTestForURLHack('editImagesDocs', $enumContextType)) return;

      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, $enumContextType, $lFID, false);

      $displayData = array();
      $displayData['formData']        = new stdClass;
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
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('auctions/auction');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->model  ('admin/mpermissions',   'perms');

      loadSupportModels($enumContextType, $lFID);

      $enumIDContext = imgDocTags\xlateTagTypeViaContextType($enumContextType, $enumEntryType);
      $displayData['strTagLabel'] = imgDocTags\strXlateImgDocContext($enumIDContext);
      $this->cidTags->loadImgDocTagsForDDL($enumIDContext, -1, $displayData['lNumTags'], $displayData['tags']);

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtDescription', 'Description', 'trim');
		$this->form_validation->set_rules('txtCaption',     'Caption',     'trim');
      $this->form_validation->set_rules('txtDate',   'Date of '.$strLabel,  'trim|required|'
                                                                           .'callback_clientImageUploadVerifyBDateValid');
      if ($bImage){
		   $this->form_validation->set_rules('chkProfile', 'Profile', 'trim');
		   $this->form_validation->set_rules('userfile', 'File Name', 'callback_clientUpImageFN');
      }else {
		   $this->form_validation->set_rules('userfile', 'File Name', 'callback_clientUpDocFN');
      }

		if ($this->form_validation->run() == FALSE){
         $this->load->helper('dl_util/web_layout');
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->txtDate        = date($gstrFormatDatePicker, $gdteNow);
            $displayData['formData']->txtDescription = '';
            $displayData['formData']->txtCaption     = '';
            if ($bImage){
               $displayData['formData']->bProfile = false;
            }
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtDescription = set_value('txtDescription');
            $displayData['formData']->txtCaption     = set_value('txtCaption');
            $displayData['formData']->txtDate        = set_value('txtDate');
            $this->updateTagSelect($displayData['lNumTags'], $displayData['tags']);
            if ($bImage){
               $displayData['formData']->bProfile = set_value('chkProfile')=='TRUE';
            }
               // if errors other than those related to file upload, delete
               // temporary upload file
            $strTempFN = @$_SESSION[CS_NAMESPACE.'clientUploadEncryptFN'];
            if ($strTempFN != ''){
               unlink('./images/upload/'.$strTempFN);
            }
         }
         $displayData['contextSummary'] = strContextHTML($enumContextType, $lFID, $displayData['strContextName']);

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = breadCrumbsToRecViewViaContextType($enumContextType, $lFID, 'Add '.$strLabel);
         $displayData['title']          = CS_PROGNAME.' | '.$strLabel.'s';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'img_docs/upload_image_doc_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->load->model('img_docs/mimage_doc', 'clsImgDoc');
         if ($bImage) $this->load->library('image_lib');
         $this->clsImgDoc->loadDocImageInfoViaID(-1);
         $imageDoc = &$this->clsImgDoc->imageDocs[0];

         $bProfile       = @$_POST['chkProfile']=='TRUE';
         $strDate        = trim($_POST['txtDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $dteMySQLDate = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

         $uploadResults = $_SESSION[CS_NAMESPACE.'uploadResults'];

         $enumEntryType = ($bImage ? CENUM_IMGDOC_ENTRY_IMAGE : CENUM_IMGDOC_ENTRY_PDF);
         $imageDoc->enumEntryType    = $enumEntryType;
         $imageDoc->enumContextType  = $enumContextType;

         $imageDoc->lForeignID       = $lFID;
         $imageDoc->strCaptionTitle  = trim($_POST['txtCaption']);
         $imageDoc->strDescription   = trim($_POST['txtDescription']);
         $imageDoc->dteMysqlDocImage = $dteMySQLDate;
         $imageDoc->bProfile         = $bProfile;
         $imageDoc->strUserFN        = $uploadResults['orig_name'];
         $imageDoc->strSystemFN      = $strFN = $uploadResults['file_name'];
         if ($bImage){
            $imageDoc->strSystemThumbFN = $strThumbFN = $uploadResults['raw_name'].'_tn'.$uploadResults['file_ext'];
         }else {
            $imageDoc->strSystemThumbFN = $strThumbFN = null;
         }
         $imageDoc->strPath = $strPath = $this->clsImgDoc->strCatalogPath($enumContextType, $enumEntryType, $lFID);

         $this->clsImgDoc->transferUploadFile($strPath, $strFN, $strThumbFN, $bImage, $bImage);
         $lImgDocID = $this->clsImgDoc->insertDocImageRec();

         if ($bImage && $bProfile){
            $this->clsImgDoc->setProfileFlag($lImgDocID, $enumContextType, $lFID);
         }
         
            // tags
         $this->setUserSelectedTagsFromPost($lImgDocID);
         
/*
         $tagIDs = array();
         if (isset($_POST['ddlTags'])){
            $bTagIDs = array();
            foreach ($_POST['ddlTags'] as $uselTag){
               if ((int)$uselTag > 0) $tagIDs[] = (int)$uselTag;
            }
         }
         $this->cidTags->setTagIDsViaImgDocID($lImgDocID, $tagIDs);
*/
         $this->session->set_flashdata('msg', 'The '.$strLabel.' was uploaded');
         redirectViaContextType($enumContextType, $lFID);
      }
   }

   function clientUpImageFN(){
      return($this->clientUpFN(true));
   }

   function clientUpDocFN(){
      return($this->clientUpFN(false));
   }

   function clientUpFN($bImage){
   //---------------------------------------------------------------------
   // notes on file upload callback at
   // http://codeigniter.com/forums/viewthread/74624/#780154
   //
   // Form validation is a little different when uploading a file
   // as part of the form. This validation routine actually uploads
   // the file. If any errors, return the upload errors as the
   // validation error. If no upload errors, set session variables with the
   // file upload info. Upon returning, if other validation errors
   // are detected, delete the temporary upload file.
   //---------------------------------------------------------------------
//      $_SESSION[CS_NAMESPACE.'imgdocUploadEncryptFN'] = '';
      $config['upload_path'] = './upload/';
      if ($bImage){
         $config['allowed_types'] = 'gif|jpg|jpeg|png';
      }else {
         $config['allowed_types'] = 'pdf';
      }
      $config['max_size']    = CI_IMGDOC_MAXUPLOADKB;
      $config['encrypt_name'] = true;
      $this->load->library('upload', $config);

      if (!$this->upload->do_upload('userfile'))        {
          $this->form_validation->set_message(($bImage ? 'clientUpImageFN' : 'clientUpDocFN'), $this->upload->display_errors());
          return(false);
      } else {
         $results = $this->upload->data();
          $_SESSION[CS_NAMESPACE.'uploadResults']    = $results;
          return(true);
      }
    }

   function clientImageUploadVerifyBDateValid($strDate){
      return(bValidVerifyDate($strDate));
   }

   function remove($lImageDocID){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      $lImageDocID = (integer)$lImageDocID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->library('util/dl_date_time', '',  'clsDateTime');
      $this->load->model  ('img_docs/mimage_doc',    'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags', 'cidTags');
      $this->load->model  ('admin/mpermissions',     'perms');
      
      $this->load->helper ('dl_util/time_date');
      $this->load->helper ('dl_util/context');
      $this->load->helper ('auctions/auction');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('img_docs/img_doc_tags');

      $enumContextType = $this->clsImgDoc->enumContextViaID  ($lImageDocID);
      $lFID            = $this->clsImgDoc->lForeignIDViaID   ($lImageDocID);
      $enumEntryType   = $this->clsImgDoc->enumEntryTypeViaID($lImageDocID);
      $bImage   = ($enumEntryType == CENUM_IMGDOC_ENTRY_IMAGE);
      $strLabel = $bImage ? 'Image' : 'Document';

      if (!bTestForURLHack('editImagesDocs', $enumContextType)) return;

      loadSupportModels($enumContextType, $lFID);

      $this->clsImgDoc->removeImageDoc($lImageDocID);
      $this->cidTags->removeImageTags($lImageDocID);
      $this->session->set_flashdata('msg', 'The '.$strLabel.' was removed.');
      redirectViaContextType($enumContextType, $lFID);
   }


   function updateTagSelect($lNumTags, &$tags){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($lNumTags > 0){
         foreach ($tags as $tag) $tag->bSelected = false;
      }
      if (isset($_POST['ddlTags'])){
         $bTagIDs = array();
         foreach ($_POST['ddlTags'] as $uselTag){
            $bTagIDs[(int)$uselTag] = true;
         }
         foreach ($tags as $tag){
            $tag->bSelected = isset($bTagIDs[$tag->lTagID]);
         }
      }
   }

   function edit($lImageDocID){
   //------------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------------
      global $gstrFormatDatePicker, $gbDateFormatUS;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lImageDocID, 'image/document ID');

      $displayData = array();
      $displayData['formData'] = new stdClass;
      $displayData['lImageDocID'] = $lImageDocID = (integer)$lImageDocID;
      $displayData['bUpdate'] = true;

         //-------------------------
         // models & helpers
         //-------------------------
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model ('img_docs/mimage_doc',    'clsImgDoc');
      $this->load->model ('admin/mpermissions',     'perms');
      $this->load->helper('auctions/auction');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/context');
      $this->load->helper('img_docs/image_doc');
      $this->load->helper('img_docs/link_img_docs');

      $displayData['enumContextType'] = $enumContextType = $this->clsImgDoc->enumContextViaID  ($lImageDocID);
      $displayData['lFID']            = $lFID            = $this->clsImgDoc->lForeignIDViaID   ($lImageDocID);
      $displayData['enumEntryType']   = $enumEntryType   = $this->clsImgDoc->enumEntryTypeViaID($lImageDocID);
      $displayData['bImage']          = $bImage   = ($enumEntryType == CENUM_IMGDOC_ENTRY_IMAGE);
      $strLabel = $bImage ? 'Image' : 'Document';

      
      loadSupportModels($enumContextType, $lFID);
      $this->clsImgDoc->loadDocImageInfoViaID($lImageDocID);
      $imgDoc = &$this->clsImgDoc->imageDocs[0];

      $enumIDContext = imgDocTags\xlateTagTypeViaContextType($enumContextType, $enumEntryType);
      $displayData['strTagLabel'] = imgDocTags\strXlateImgDocContext($enumIDContext);
      $this->cidTags->loadImgDocTagsForDDL($enumIDContext, $lImageDocID, $displayData['lNumTags'], $displayData['tags']);
      
         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtDescription', 'Description', 'trim');
		$this->form_validation->set_rules('txtCaption',     'Caption',     'trim');
      $this->form_validation->set_rules('txtDate',        'Date of '.$strLabel,  'trim|required|'
                                                                           .'callback_clientImageUploadVerifyBDateValid');
      if ($bImage){
		   $this->form_validation->set_rules('chkProfile', 'Profile', 'trim');
      }

		if ($this->form_validation->run() == FALSE){
         $this->load->helper('dl_util/web_layout');
         $this->load->library('generic_form');
         if ($bImage){
            $displayData['strImageTag'] =
                        strLinkHTMLTag($enumContextType, $enumEntryType, $lFID, $imgDoc->strSystemFN,
                                      'View in new window', true, '')
                       .strImageHTMLTag($enumContextType, $enumEntryType, $lFID, $imgDoc->strSystemThumbFN,
                                  '', false, ' style="border: 1px solid black;" ')
                       .'</a>';
         }else {
            $displayData['strImageTag'] = '';
         }

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->txtDate        = date($gstrFormatDatePicker, $imgDoc->dteDocImage);
            $displayData['formData']->txtDescription = htmlspecialchars($imgDoc->strDescription);
            $displayData['formData']->txtCaption     = htmlspecialchars($imgDoc->strCaptionTitle);
            if ($bImage){
               $displayData['formData']->bProfile = $imgDoc->bProfile;
            }
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtDate        = set_value('txtDate');
            $displayData['formData']->txtDescription = set_value('txtDescription');
            $displayData['formData']->txtCaption     = set_value('txtCaption');
            $this->updateTagSelect($displayData['lNumTags'], $displayData['tags']);
            if ($bImage){
               $displayData['formData']->bProfile = set_value('chkProfile')=='TRUE';
            }
         }
         $displayData['contextSummary'] = strContextHTML($enumContextType, $lFID, $displayData['strContextName']);

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']  = breadCrumbsToRecViewViaContextType($enumContextType, $lFID, 'Update '.$strLabel);

         $displayData['title']          = CS_PROGNAME.' | Clients';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'img_docs/upload_image_doc_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->load->model('img_docs/mimage_doc', 'clsImgDoc');
         if ($bImage) $this->load->library('image_lib');
         $this->clsImgDoc->loadDocImageInfoViaID(-1);
         $imageDoc = &$this->clsImgDoc->imageDocs[0];

         $bProfile       = @$_POST['chkProfile']=='TRUE';
         $strDate        = trim($_POST['txtDate']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $dteMySQLDate = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

//         $uploadResults = $_SESSION[CS_NAMESPACE.'uploadResults'];

         $enumEntryType = ($bImage ? CENUM_IMGDOC_ENTRY_IMAGE : CENUM_IMGDOC_ENTRY_PDF);
         $imageDoc->enumEntryType    = $enumEntryType;
         $imageDoc->enumContextType  = $enumContextType;

         $imageDoc->lForeignID       = $lFID;
         $imageDoc->strCaptionTitle  = trim($_POST['txtCaption']);
         $imageDoc->strDescription   = trim($_POST['txtDescription']);
         $imageDoc->dteMysqlDocImage = $dteMySQLDate;
         $imageDoc->bProfile         = $bProfile;
         
            // tags
         $this->setUserSelectedTagsFromPost($lImageDocID);

         $this->clsImgDoc->updateDocImageRec($lImageDocID);

         if ($bImage && $bProfile){
            $this->clsImgDoc->setProfileFlag($lImageDocID, $enumContextType, $lFID);
         }

         $this->session->set_flashdata('msg', 'The '.$strLabel.' information was updated');
         redirectViaContextType($enumContextType, $lFID);
      }
   }
   
   function setUserSelectedTagsFromPost($lImgDocID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $tagIDs = array();
      if (isset($_POST['ddlTags'])){
         $bTagIDs = array();
         foreach ($_POST['ddlTags'] as $uselTag){
            if ((int)$uselTag > 0) $tagIDs[] = (int)$uselTag;
         }
      }
      $this->cidTags->setTagIDsViaImgDocID($lImgDocID, $tagIDs);   
   }



}