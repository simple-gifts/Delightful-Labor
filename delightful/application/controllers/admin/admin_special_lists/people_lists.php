<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class people_lists extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }
   function relTypesView(){
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

      $this->load->model('people/mrelationships', 'clsRel');
      $this->clsRel->loadRelationships(true, false, false, null);
      $displayData['lNumRelListItems'] = $lNumRelListItems = $this->clsRel->lNumRelListItems;
      if ($lNumRelListItems > 0){
         $displayData['strHTMLRelItemList'] = $this->clsRel->strHTMLRelItemList(true);
      }

         //----------------------
         // set breadcrumbs
         //----------------------
      $displayData['title']        = CS_PROGNAME.' | Groups';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | People Relationships';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate'] = 'people/relationships_view';

      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addEditRel($lKeyID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      if (!bTestForURLHack('adminOnly')) return;
      $this->load->helper('dl_util/verify_id');
      if ($lKeyID!='0') verifyID($this, $lKeyID, 'relationship entry ID');   
   
      $lKeyID = (integer)$lKeyID;
      $bNew   = $lKeyID <= 0;

      $this->load->model('people/mrelationships', 'clsRel');
      $this->clsRel->loadRelationships(false, false, true, $lKeyID);
      $clsItem = $this->clsRel->relListItems[0];

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtRelName', 'Relationship Name',
                          'trim|required|callback_verifyUniqueRelType['.$lKeyID.']');
		$this->form_validation->set_rules('ddlRC',      'Relationship Category',
                          'trim|callback_verifyRelCatSel');

		$this->form_validation->set_rules('chkSpouse');

		if ($this->form_validation->run() == FALSE){
         $displayData = array();
         $displayData['formD'] = new stdClass;
         $this->load->library('generic_form');

         if (validation_errors()==''){
            $displayData['formD']->txtRelName   = htmlspecialchars($clsItem->strRelationship);
            $displayData['formD']->strRelCatDDL = $this->clsRel->strRelCatDDL($clsItem->enumCategory, true);
            $displayData['formD']->bSpouse      = $clsItem->bSpouse;
         }else {
            setOnFormError($displayData);
            $displayData['formD']->txtRelName   = set_value('txtRelName');
            $displayData['formD']->strRelCatDDL = $this->clsRel->strRelCatDDL(set_value('ddlRC'), true);
            $displayData['formD']->bSpouse      = set_value('chkSpouse')=='TRUE';
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                   .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb" ')
                                   .' | '.anchor('admin/admin_special_lists/people_lists/relTypesView', 'People Relationships', 'class="breadcrumb" ')
                                   .' | '.($bNew ? 'Add new status category' : 'Edit status category');

         $displayData['title']          = CS_PROGNAME.' | Lists';
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['lKeyID']         = $lKeyID;
         $displayData['bNew']           = $bNew;

         $displayData['mainTemplate']   = 'people/rel_types_add_edit';
         $this->load->vars($displayData);
         $this->load->view('template');

      }else {

         $this->clsRel->relListItems[0]->strRelationship = xss_clean(trim($_POST['txtRelName']));
         $this->clsRel->relListItems[0]->enumCategory    = xss_clean(trim($_POST['ddlRC']));
         $this->clsRel->relListItems[0]->bSpouse                 = @$_POST['chkSpouse']=='TRUE';

         if ($bNew){
            $id = $this->clsRel->addNewPeopleRelListItem();
            $this->session->set_flashdata('msg', 'Your relationship entry was added');
         }else {
            $this->clsRel->relListItems[0]->lKeyID = $lKeyID;
            $this->clsRel->updatePeopleRelListItem();
            $this->session->set_flashdata('msg', 'Your relationship entry was updated');
         }
         redirect('admin/admin_special_lists/people_lists/relTypesView');
      }
   }

   function verifyRelCatSel($strField){
   //--------------------------------------------------------------------------------------
   // note - this function uses the custom error technique described in
   // application/language/english/dl_errors_lang.php
   // Could have also used
   //    $this->form_validation->set_message('verifyRelCatSel', 'my little message.');
   // but it would be language specific
   //--------------------------------------------------------------------------------------
      return($strField != '');
   }

   function verifyUniqueRelType($strRelName, $lKeyID){
   //--------------------------------------------------------------------------------------
   //
   //--------------------------------------------------------------------------------------
      $lKeyID = (integer)$lKeyID;

      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                $strRelName, 'lpr_strRelationship',
                $lKeyID,     'lpr_lKeyID',
                true,        'lpr_bRetired',
                false,       null, null,
                false,       null, null,
                'lists_people_relationships')){
         return(false);
      }else {
         return(true);
      }
   }

   function remove($lKeyID){
      if (!bTestForURLHack('adminOnly')) return;
      $lKeyID = (integer)$lKeyID;
      $this->load->model('people/mrelationships', 'clsRel');
      $this->clsRel->retireRelListItem($lKeyID);
      $this->session->set_flashdata('msg', 'The specificed relationship entry was retired.');
      redirect('admin/admin_special_lists/people_lists/relTypesView');
   }

}