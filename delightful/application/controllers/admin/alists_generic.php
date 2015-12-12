<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class alists_generic extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function index(){

   }

   public function view(){
      if (!bTestForURLHack('adminOnly')) return;
      $this->load->model('util/mlist_generic', 'listGeneric');
      $strListType = $this->uri->segment(4);
      $this->listGeneric->initializeListManager('generic', $strListType);
      $this->listGeneric->loadList();
      
      $displayData = array();
      
         // some default items can't be removed or edited
      $displayData['strBlockEdit'] = '';
      switch ($strListType){
         case CENUM_LISTTYPE_BIZCAT:
         case CENUM_LISTTYPE_BIZCONTACTREL:
         case CENUM_LISTTYPE_CAMPEXPENSE:
         case CENUM_LISTTYPE_INKIND:
         case CENUM_LISTTYPE_MAJORGIFTCAT:
         case CENUM_LISTTYPE_GIFTPAYTYPE:
         case CENUM_LISTTYPE_SPONTERMCAT:
            $displayData['strBlockEdit'] = '(other/unknown)';
            break;
         case CENUM_LISTTYPE_ATTRIB:
            $displayData['strBlockEdit'] = '(other)';
            break;
            
      }
      
      $displayData['title']          = CS_PROGNAME.' | Lists';
      $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"') 
                                .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"').' | '.$this->listGeneric->strListTableTitle;
      $displayData['mainTemplate']   = 'admin/alist_generic_view';
      $displayData['listItems']      = $this->listGeneric->listItems;
      $displayData['listEmptyLabel'] = $this->listGeneric->strDBLookupEmpty;
      $displayData['lNumInList']     = $this->listGeneric->lNumInList;
      $displayData['xlateListType']  = $this->listGeneric->strListTableTitle;
      $displayData['strListType']    = $strListType;

      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $this->load->vars($displayData);
      $this->load->view('template');

   }

   public function addEdit(){
      if (!bTestForURLHack('adminOnly')) return;
      $strListType = $this->uri->segment(4);
      $id          = (integer)($this->uri->segment(5));

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtListItem', 'List Item', 
                           'trim|required|callback_verifyUniqueGenericListItem['.$strListType.','.$id.']');
      $this->load->model('util/mlist_generic', 'listGeneric');
      $this->listGeneric->initializeListManager('generic', $strListType);

		if ($this->form_validation->run() == FALSE){
         $this->listGeneric->setAddEditTableText();

         $displayData = array();
         $displayData['listItem'] = new stdClass;

         $strListItem = set_value('txtListItem').'';
         if ($strListItem==''){
            if ($id > 0){
               $strListItem = $this->listGeneric->strRetrieveListItem($id);
            }
         }

         $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"') 
                                   .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb" ')
                                   .' | '.anchor('admin/alists_generic/view/'.$strListType,
                                                       $this->listGeneric->strListTableTitle,
                                                      'class="breadcrumb" ');

         $displayData['title']          = CS_PROGNAME.' | Lists';
         $displayData['xlateListType']  = $this->listGeneric->strListTableTitle;
         $displayData['strListType']    = $strListType;
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['listItem']->name = $strListItem;
         $displayData['listItem']->id   = $id;

         $displayData['strAddEditTableTitle']        = $this->listGeneric->strAddEditTableTitle;
         $displayData['strAddEditTableItemLabel']    = $this->listGeneric->strAddEditTableItemLabel;
         $displayData['strAddEditTableButton']       = ($id > 0 ?
                                                            $this->listGeneric->strAddEditTableButtonUpdate :
                                                            $this->listGeneric->strAddEditTableButtonAddNew);
         $displayData['strAddEditTableButtonUpdate'] = $this->listGeneric->strAddEditTableButtonUpdate;

         $displayData['mainTemplate']   = 'admin/alist_generic_add_edit';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         if ($id==0){
            $this->listGeneric->lInsertNewListItem(trim($_POST['txtListItem']));
            $this->session->set_flashdata('msg', 'Your list item was added');
         }else {
            $this->listGeneric->updateListItem(trim($_POST['txtListItem']), $id);
            $this->session->set_flashdata('msg', 'Your list item was updated');
         }
         redirect('admin/alists_generic/view/'.$strListType);
      }
   }
   
   function verifyUniqueGenericListItem($strField, $params){
      $arrayParams = explode(',', $params);
      $strListType = $arrayParams[0];
      $lListID     = (integer)$arrayParams[1];

      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                $strField,  'lgen_strListItem',
                $lListID,   'lgen_lKeyID',
                true,       'lgen_bRetired',
                true,       $strListType, 'lgen_enumListType' ,
                false,      null,   null,
                'lists_generic')){
         return(false);
      }else {
         return(true);
      }
   }

   public function remove(){
      if (!bTestForURLHack('adminOnly')) return;
      $strListType = $this->uri->segment(4);
      $id          = (integer)($this->uri->segment(5));
      $this->load->model('util/mlist_generic', 'listGeneric');
      $this->listGeneric->removeListItem($id);
      $this->session->set_flashdata('msg', 'The list item was removed');

      redirect('admin/alists_generic/view/'.$strListType);
   }

}