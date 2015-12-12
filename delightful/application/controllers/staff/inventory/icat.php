<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class icat extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewICats(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->viewCatsGeneric('allItems');
   }

   function viewICatsRemOnly(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->viewCatsGeneric('remItems');
   }

   function viewICatsLostOnly(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->viewCatsGeneric('lostItems');
   }

   function viewCatsGeneric($enumReportType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;

      $displayData = array();
      $displayData['js'] = '';
      $displayData['enumReportType'] = $enumReportType;

      $bAllItems = $bLostOnly = $bRemInvOnly = false;
      switch ($enumReportType){
         case 'allItems':
            $displayData['strTitle'] = 'Inventory Categories';
            $displayData['bShowAddCatLink'] = true;
            $displayData['bShowAddItemsLink'] = true;
            $displayData['bHideZeroItems'] = false;
            $displayData['strNoCatMsg'] = '<br><i>There are no inventory categories defined in your database.<br></i>';

            $strBreadPath = 'Inventory Items';
            $bAllItems = true;
            break;
         case 'remItems':
            $displayData['strTitle'] = 'Items Removed from the Inventory';
            $displayData['bShowAddCatLink'] = false;
            $displayData['bShowAddItemsLink'] = false;
            $displayData['bHideZeroItems'] = false;
            $displayData['strNoCatMsg'] = '<br><i>There are no items removed from your inventory.<br></i>';

            $strBreadPath = 'Removed Inventory Items';
            $bRemInvOnly = true;
            break;
         case 'lostItems':
            $displayData['strTitle'] = 'Items Reported Lost';
            $displayData['bShowAddCatLink'] = false;
            $displayData['bShowAddItemsLink'] = false;
            $displayData['bHideZeroItems'] = false;
            $displayData['strNoCatMsg'] = '<br><i>There are no items reported lost in your inventory.<br></i>';

            $strBreadPath = 'Lost Inventory Items';
            $bLostOnly = true;
            break;
         default:
            screamForHelp($enumReportType.': invalid report type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory',   'cinv');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper('staff/link_inventory');

         //-------------------------------------
         // stripes
         //-------------------------------------
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         // load the inventory categories
      $props = new stdClass;
      $props->bCountItems = true;
      $props->bLostOnly = $bLostOnly;
      $props->bRemInvOnly = $bRemInvOnly;
      $this->cinv->loadInventoryCategories($displayData['icats'], $props);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']   = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                             .' | '.anchor('staff/inventory/icat/viewICats', 'Inventory Categories', 'class="breadcrumb"')
                             .' | '.$strBreadPath;

      $displayData['title']          = CS_PROGNAME.' | Inventory Management';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'staff/inventory/icats_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function addEditICat($lICatID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;
      $this->load->helper('dl_util/verify_id');
      if ($lICatID!='0') verifyID($this, $lICatID, 'inventory cat ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lICatID'] = $lICatID = (int)$lICatID;
      $displayData['bNew'] = $bNew = $lICatID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory',   'cinv');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('staff/link_inventory');

         // load the inventory categories
      $props = new stdClass;
      $props->bCountItems = false;
      $props->bLostOnly   = $props->bRemInvOnly = false;
      $this->cinv->loadInventoryCategories($icats, $props);

         // find the current category
      $this->cinv->lRunAway = 0;
      $cat = $this->cinv->findICatViaICatID($lICatID, $icats);
      if (is_null($cat)){
         $lDDLID = null;
      }else {
         $lDDLID = $cat->lParentID;
      }

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtCat',    'Category',        'trim|required');
		$this->form_validation->set_rules('ddlParent', 'Parent Category', 'trim|required|callback_testDDLParent');
		$this->form_validation->set_rules('txtNotes',  'Notes',           'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $this->cinv->strCatDDL = '';
            $this->cinv->strDDLICats($lDDLID, $lICatID, $icats, '');
            $displayData['ddlParent'] = $this->cinv->strCatDDL;
            if ($bNew){
               $displayData['formData']->txtCat   = '';
               $displayData['formData']->txtNotes = '';
            }else {
               $displayData['formData']->txtCat = htmlspecialchars($cat->strCatName);
               $displayData['formData']->txtNotes = htmlspecialchars($cat->strNotes);
            }
         }else {
            setOnFormError($displayData);
            $this->cinv->strCatDDL = '';
            $this->cinv->strDDLICats((int)@$_POST['ddlParent'], $lICatID, $icats, '');
            $displayData['ddlParent'] = $this->cinv->strCatDDL;
            $displayData['formData']->txtCat   = set_value('txtCat');
            $displayData['formData']->txtNotes = set_value('txtNotes');
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']   = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                .' | '.anchor('staff/inventory/icat/viewICats', 'Inventory Categories', 'class="breadcrumb"')
                                .' | '.($bNew ? 'Add New ' : 'Edit ').'Category';

         $displayData['title']          = CS_PROGNAME.' | Inventory Management';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'staff/inventory/icats_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $cat->lKeyID     = $lICatID;
         $cat->strCatName = trim($_POST['txtCat']);
         $cat->strNotes   = trim($_POST['txtNotes']);
         $cat->lParentID  = (int)@$_POST['ddlParent'];

         if ($bNew){
            $this->cinv->addNewICat($cat);
            $this->session->set_flashdata('msg', 'Inventory category added.');
         }else {
            $this->cinv->updateICat($cat);
            $this->session->set_flashdata('msg', 'Inventory category updated.');
         }
         redirect('staff/inventory/icat/viewICats');
      }
   }

   function testDDLParent($lParentID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lParentID = (int)$lParentID;
      if ($lParentID==0 || $lParentID < -1){
         $this->form_validation->set_message('testDDLParent', 'Please select a parent category.');
         return(false);
      }else {
         return(true);
      }
   }

   function removeCat($lCatID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('staff/inventory/minventory',   'cinv');
      $this->cinv->removeCategory($lCatID);

      $this->session->set_flashdata('msg', 'Inventory category removed.');
      redirect('staff/inventory/icat/viewICats');
   }

   function itemsCheckedOutList($enumSort='cat'){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;

      $displayData = array();
      $displayData['js'] = '';
      $displayData['enumSort'] = $enumSort;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory');
      $this->load->model  ('staff/inventory/minv_cico',   'ccico');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('staff/link_inventory');
      $this->load->helper ('staff/cico');

         //-------------------------------------
         // stripes
         //-------------------------------------
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         // load the inventory categories
      $props = new stdClass;
      $props->bCountItems = false;
      $props->bLostOnly = $props->bRemInvOnly = false;
      $this->ccico->loadInventoryCategories($icats, $props);

      $strTmpTable = 'tmpInvCats';
      $this->ccico->buildTempCatTable($strTmpTable, $icats);

      $this->ccico->loadCheckedOutItems($enumSort, $strTmpTable, $displayData['lNumItems'], $displayData['items']);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']   = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                             .' | '.anchor('staff/inventory/icat/viewICats', 'Inventory Categories', 'class="breadcrumb"')
                             .' | Checked-Out Items';

      $displayData['title']          = CS_PROGNAME.' | Inventory Management';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'staff/inventory/co_items_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function itemsAllList($enumSort='cat', $lStartRec=0, $lRecsPerPage=50){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;

      $displayData = array();
      $displayData['js'] = '';
      $displayData['enumSort'] = $enumSort;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory');
      $this->load->model  ('staff/inventory/minv_cico',   'ccico');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('staff/link_inventory');
      $this->load->helper ('staff/cico');
      $this->load->helper ('dl_util/rs_navigate');

         //-------------------------------------
         // stripes
         //-------------------------------------
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         // load the inventory categories
      $props = new stdClass;
      $props->bCountItems = false;
      $props->bLostOnly = $props->bRemInvOnly = false;
      $this->ccico->loadInventoryCategories($icats, $props);

      $strTmpTable = 'tmpInvCats';
      $this->ccico->buildTempCatTable($strTmpTable, $icats);

      $displayData['lNumRecsTot'] = $lNumRecsTot = $this->ccico->lCountItems('');

      $this->ccico->loadAllItems($lStartRec, $lRecsPerPage, $enumSort, $strTmpTable, $lNumItems, $displayData['items']);
      $displayData['lNumDisplayRows']      = $lNumItems;
      $displayData['directoryRecsPerPage'] = $lRecsPerPage;
      $displayData['directoryStartRec']    = $lStartRec;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']   = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                             .' | '.anchor('staff/inventory/icat/viewICats', 'Inventory Categories', 'class="breadcrumb"')
                             .' | All Items';

      $displayData['title']          = CS_PROGNAME.' | Inventory Management';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'staff/inventory/items_all_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }



}
