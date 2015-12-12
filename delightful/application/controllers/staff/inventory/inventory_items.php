<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class inventory_items extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEditItem($lICatID, $lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gstrFormatDatePicker, $gclsChapterACO, $gbDateFormatUS;

      if (!bTestForURLHack('inventoryMgr')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lICatID, 'inventory cat ID');
      if ($lIItemID != '0') verifyID($this, $lIItemID, 'inventory item ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lICatID']  = $lICatID  = (int)$lICatID;
      $displayData['lIItemID'] = $lIItemID = (int)$lIItemID;
      $displayData['bNew']     = $bNew = $lIItemID <= 0;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory',   'cinv');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('staff/link_inventory');
      $this->load->helper ('dl_util/time_date');

         // load the inventory category
      $this->cinv->loadSingleInventoryCategories($lICatID, $displayData['cat']);

         // load the inventory category breadcrumbs
      $displayData['strBreadCrumb'] = '';
      $this->cinv->icatBreadCrumbs($displayData['strBreadCrumb'], $lICatID);

         // load the inventory item
      $this->cinv->loadSingleInventoryItem($lIItemID, $lNumItems, $items);
      $item = &$items[0];
      if (is_null($item->lACOID)) $item->lACOID = $gclsChapterACO->lKeyID;

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtItem',     'Item',        'trim|required');
		$this->form_validation->set_rules('txtRParty',   'Responsible Party', 'trim|required');
		$this->form_validation->set_rules('txtNotes',    'Description', 'trim');
		$this->form_validation->set_rules('txtSNa',      'Serial # (a)', 'trim');
		$this->form_validation->set_rules('txtSNb',      'Serial # (b)', 'trim');
		$this->form_validation->set_rules('txtLocation', 'Location', 'trim|required');
      $this->form_validation->set_rules('txtDateAcquired', 'Date Acquired', 'trim|required|callback_itemVerifyDateAcquiredValid');
		$this->form_validation->set_rules('txtEstValue',  'Estimated Value',     'trim|required|callback_stripCommas|numeric');
      $this->form_validation->set_rules('rdoACO',       'Accounting Country',  'trim|required');
      $this->form_validation->set_rules('chkAvailLoan', 'Available for Loan');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            $displayData['formData']->txtItem       = htmlspecialchars($item->strItemName);
            $displayData['formData']->txtNotes      = htmlspecialchars($item->strDescription);
            $displayData['formData']->txtSNa        = htmlspecialchars($item->strItemSNa);
            $displayData['formData']->txtSNb        = htmlspecialchars($item->strItemSNb);
            $displayData['formData']->txtLocation   = htmlspecialchars($item->strLocation);
            $displayData['formData']->txtRParty     = htmlspecialchars($item->strRParty);
            $displayData['formData']->bAvailForLoan = $item->bAvailForLoan;
            if (is_null($item->dteObtained)){
               $displayData['formData']->txtDateAcquired = '';
            }else {
               $displayData['formData']->txtDateAcquired = date($gstrFormatDatePicker, $item->dteObtained);
            }

            $displayData['formData']->txtEstValue   = number_format($item->curEstValue, 2);
            $displayData['formData']->strACORadio   = $this->clsACO->strACO_Radios ($item->lACOID, 'rdoACO');
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtItem         = set_value('txtItem');
            $displayData['formData']->txtNotes        = set_value('txtNotes');
            $displayData['formData']->txtSNa          = set_value('txtSNa');
            $displayData['formData']->txtSNb          = set_value('txtSNb');
            $displayData['formData']->txtLocation     = set_value('txtLocation');
            $displayData['formData']->txtDateAcquired = set_value('txtDateAcquired');
            $displayData['formData']->txtRParty       = set_value('txtRParty');
            $displayData['formData']->txtEstValue     = set_value('txtEstValue');
            $displayData['formData']->strACORadio     = $this->clsACO->strACO_Radios ($_POST['rdoACO'], 'rdoACO');
            $displayData['formData']->bAvailForLoan   = set_value('chkAvailLoan')=='TRUE';
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']   = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                .' | '.anchor('staff/inventory/icat/viewICats', 'Inventory Categories', 'class="breadcrumb"')
                                .' | '.($bNew ? 'Add New ' : 'Edit ').'Item';

         $displayData['title']          = CS_PROGNAME.' | Inventory Management';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'staff/inventory/iitems_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $item->strItemName    = trim($_POST['txtItem']);
         $item->strRParty      = trim($_POST['txtRParty']);
         $item->lCategoryID    = $lICatID;
         $item->strDescription = trim($_POST['txtNotes']);
         $item->strLocation    = trim($_POST['txtLocation']);
         $item->strItemSNa     = trim($_POST['txtSNa']);
         $item->strItemSNb     = trim($_POST['txtSNb']);
         $item->lACOID         = (integer)trim($_POST['rdoACO']);
         $item->curEstValue    = (float)trim($_POST['txtEstValue']);
         $item->bAvailForLoan  = @$_POST['chkAvailLoan']=='TRUE';

         $strDate   = trim($_POST['txtDateAcquired']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         $item->mdteObtained = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);

         if ($bNew){
            $lIItemID = $this->cinv->lAddNewIItem($item);
            $this->cinv->lAddItemHistoryRec($lIItemID, null, 'created');
            $this->session->set_flashdata('msg', 'Inventory item added');
         }else {
            $this->cinv->updateIItem($lIItemID, $item);
            $this->cinv->lAddItemHistoryRec($lIItemID, null, 'updated');
            $this->session->set_flashdata('msg', 'Inventory item updated');
         }
         redirect('staff/inventory/inventory_items/iitemRec/'.$lIItemID);
      }
   }

   function stripCommas(&$strAmount){
      $strAmount = str_replace (',', '', $strAmount);
      return(true);
   }

   function itemVerifyDateAcquiredValid($strAcquiredDate){
      if(bValidVerifyDate($strAcquiredDate)){
         return(true);
      }else {
         $this->form_validation->set_message('itemVerifyDateAcquiredValid', 'Please enter a valid acquisistion date.');
         return(false);
      }
   }

   function iitemRec($lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lIItemID, 'inventory item ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lIItemID'] = $lIItemID = (int)$lIItemID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory');
      $this->load->model  ('staff/inventory/minv_cico',   'ccico');

      $this->load->model  ('img_docs/mimage_doc',                  'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags',               'cidTags');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->helper ('dl_util/web_layout');

      $this->load->helper ('staff/link_inventory');
      $this->load->helper ('staff/cico');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->helper ('dl_util/record_view');

      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         //-------------------------------------
         // stripes
         //-------------------------------------
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         // load the inventory item
      $this->ccico->loadSingleInventoryItem($lIItemID, $lNumItems, $items, true);
      $displayData['item'] = &$items[0];
      $displayData['lICatID'] = $lICatID = $displayData['item']->lCategoryID;

         // load the inventory category
      $this->ccico->loadSingleInventoryCategories($lICatID, $displayData['cat']);

         // load the inventory category breadcrumbs
      $displayData['strBreadCrumb'] = '';
      $this->ccico->icatBreadCrumbs($displayData['strBreadCrumb'], $lICatID);

         // check-out / check-in history
      $this->ccico->itemCICOHistory($lIItemID, '', '', $displayData['item']->lNumCICO, $displayData['item']->CICOrecs);
      $currentCICO = &$displayData['item']->CICOrecs[0];

         // item history
      $this->ccico->loadItemHistory($lIItemID, $displayData['item']->lNumHRecs, $displayData['item']->histRecs);
      
         // images and documents
      loadImgDocRecView($displayData, CENUM_CONTEXT_INVITEM, $lIItemID);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']   = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                             .' | '.anchor('staff/inventory/icat/viewICats', 'Inventory Categories', 'class="breadcrumb"')
                             .' | Item Record';

      $displayData['title']          = CS_PROGNAME.' | Inventory Management';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'staff/inventory/iitem_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function xferItemCatOpts($lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lIItemID, 'inventory item ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lIItemID'] = $lIItemID = (int)$lIItemID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory',   'cinv');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('staff/link_inventory');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $displayData['formData'] = new stdClass;
      $this->load->library('generic_form');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

         // load the inventory item
      $this->cinv->loadSingleInventoryItem($lIItemID, $lNumItems, $items);
      $displayData['item'] = $item = &$items[0];
      $displayData['lICatID'] = $lICatID = $displayData['item']->lCategoryID;

         // load the inventory category breadcrumbs
      $item->strCatBreadCrumb = '';
      $this->cinv->icatBreadCrumbs($item->strCatBreadCrumb, $lICatID);

         // load the inventory categories
      $props = new stdClass;
      $props->bCountItems = false;
      $props->bLostOnly   = $props->bRemInvOnly = false;      
      $this->cinv->loadInventoryCategories($icats, $props);

         // inventory category drop-down list
      $this->cinv->strCatDDL = '';
      $this->cinv->strDDLICats($lICatID, -999, $icats, '');
      $displayData['ddlXfer'] = $this->cinv->strCatDDL;

         // load the inventory category breadcrumbs
      $displayData['strBreadCrumb'] = '';
      $this->cinv->icatBreadCrumbs($displayData['strBreadCrumb'], $lICatID);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['contextSummary'] = $this->cinv->strIItemHTMLSummary($item);

      $displayData['pageTitle']   = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                             .' | '.anchor('staff/inventory/icat/viewICats', 'Inventory Categories', 'class="breadcrumb"')
                             .' | '.anchor('staff/inventory/inventory_items/iitemRec/'.$lIItemID, 'Inventory Item', 'class="breadcrumb"')
                             .' | Transfer Item';

      $displayData['title']          = CS_PROGNAME.' | Inventory Management';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'staff/inventory/iitem_xfer_opts_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function xferItemCat($lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lIItemID, 'inventory item ID');
      $lIItemID = (int)$lIItemID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('staff/inventory/minventory', 'cinv');

      $this->cinv->changeItemCategory($lIItemID, (int)$_POST['ddlXfer']);
      $this->cinv->lAddItemHistoryRec($lIItemID, null, 'transfer category');

      $this->session->set_flashdata('msg', 'Inventory category updated');
      redirect('staff/inventory/inventory_items/iitemRec/'.$lIItemID);
   }

   function viewViaCatID($enumRptType, $lICatID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lICatID, 'inventory cat ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lICatID'] = $lICatID = (int)$lICatID;

      $bLostOnly = $bRemInvOnly = false;
      switch ($enumRptType){
         case 'allItems':
            $displayData['strTitle'] = '';
            $displayData['bShowAddLink'] = true;
            $strBreadCrumb = 'Items by Category';
            break;
         case 'remItems':
            $displayData['strTitle'] = 'Items Removed from the Inventory<br>';
            $displayData['bShowAddLink'] = false;
            $bRemInvOnly = true;
            $strBreadCrumb = 'Items Removed from Inventory';
            break;
         case 'lostItems':
            $displayData['strTitle'] = 'Items Reported Lost<br>';
            $displayData['bShowAddLink'] = false;
            $bLostOnly = true;
            $strBreadCrumb = 'Lost Items';
            break;
         default:
            screamForHelp($enumRptType.': invalid report type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory',   'cinv');
      $this->load->model  ('admin/madmin_aco');
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

         // load the inventory category
      $this->cinv->loadSingleInventoryCategories($lICatID, $displayData['cat']);

         // load the inventory category breadcrumbs
      $displayData['strBreadCrumb'] = '';
      $this->cinv->icatBreadCrumbs($displayData['strBreadCrumb'], $lICatID);

         // load the inventory items for this category
      $this->cinv->loadInventoryItemsViaCatID($lICatID, $displayData['lNumItems'], $displayData['items'], 
                         $bLostOnly, $bRemInvOnly, true);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']   = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                             .' | '.anchor('staff/inventory/icat/viewICats', 'Inventory Categories', 'class="breadcrumb"')
                             .' | '.$strBreadCrumb;

      $displayData['title']          = CS_PROGNAME.' | Inventory Management';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'staff/inventory/iitems_via_cat_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function removeIItem($lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lIItemID, 'inventory item ID');

      $lIItemID = (int)$lIItemID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory', 'cinv');

      $this->cinv->removeIItem($lIItemID);

      $this->session->set_flashdata('msg', 'Inventory item record deleted');
      redirect('staff/inventory/icat/viewICats');
   }

   function itemLost($lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->itemLostFoundOpts($lIItemID, true);
   }

   function itemFound($lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->itemLostFoundOpts($lIItemID, false);
   }

   function itemLostFoundOpts($lIItemID, $bLost){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lIItemID, 'inventory item ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lIItemID'] = $lIItemID = (int)$lIItemID;
      $displayData['bLost']    = $bLost;
      $displayData['strLostLabel'] = ($bLost ? 'LOST' : 'FOUND');

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory',   'cinv');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('staff/link_inventory');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $displayData['formData'] = new stdClass;
      $this->load->library('generic_form');

         // load the inventory item
      $this->cinv->loadSingleInventoryItem($lIItemID, $lNumItems, $items);
      $displayData['item'] = $item = &$items[0];
      $displayData['lICatID'] = $lICatID = $displayData['item']->lCategoryID;

         // load the inventory category breadcrumbs
      $item->strCatBreadCrumb = '';
      $this->cinv->icatBreadCrumbs($item->strCatBreadCrumb, $lICatID);

         // load the inventory categories
      $props = new stdClass;
      $props->bCountItems = false;
      $props->bLostOnly   = $props->bRemInvOnly = false;      
      $this->cinv->loadInventoryCategories($icats, $props);

         // load the inventory category breadcrumbs
      $displayData['strBreadCrumb'] = '';
      $this->cinv->icatBreadCrumbs($displayData['strBreadCrumb'], $lICatID);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['contextSummary'] = $this->cinv->strIItemHTMLSummary($item);

      $displayData['pageTitle']   = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                             .' | '.anchor('staff/inventory/icat/viewICats', 'Inventory Categories', 'class="breadcrumb"')
                             .' | '.anchor('staff/inventory/inventory_items/iitemRec/'.$lIItemID, 'Inventory Item', 'class="breadcrumb"')
                             .' | Report Item Lost';

      $displayData['title']          = CS_PROGNAME.' | Inventory Management';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'staff/inventory/iitem_lost_found_opts_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function lostFound($enumType, $lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lIItemID, 'inventory item ID');

      $bLost = strtolower($enumType)=='lost';
      $strNotes = @$_POST['txtNotes'];

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory',   'cinv');

      $this->cinv->markLostFound($lIItemID, $bLost, $strNotes);
      $this->cinv->lAddItemHistoryRec($lIItemID, null, ($bLost ? 'lost' : 'found'));

      $this->session->set_flashdata('msg', 'Inventory item marked as '.($bLost ? '"LOST"' : '"FOUND"'));
      redirect('staff/inventory/inventory_items/iitemRec/'.$lIItemID);
   }

   function itemRemoveFromInv($lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->itemInvRemoveRestore($lIItemID, true);
   }

   function itemRestoreToInv($lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->itemInvRemoveRestore($lIItemID, false);
   }

   function itemInvRemoveRestore($lIItemID, $bRemove){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('inventoryMgr')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lIItemID, 'inventory item ID');

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory',   'cinv');

      $this->cinv->markRemoveRestore($lIItemID, $bRemove);
      $this->cinv->lAddItemHistoryRec($lIItemID, null, ($bRemove ? 'removed from inventory' : 'returned to inventory'));

      $this->session->set_flashdata('msg', 'Item '.($bRemove ? 'removed from' : 'restored to').' the inventory.');
      if ($bRemove){
         redirect('staff/inventory/icat/viewICats');
      }else {
         redirect('staff/inventory/inventory_items/iitemRec/'.$lIItemID);
      }
   }





}
