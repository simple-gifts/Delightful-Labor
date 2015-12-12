<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class inventory_cico extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function checkOutItem($lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->cicoGeneric($lIItemID, true, false, false);
   }

   function checkInItem($lIItemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->cicoGeneric($lIItemID, false, true, false);
   }

   function cicoGeneric($lIItemID, $bCheckOut, $bCheckIn, $bLost){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gstrFormatDatePicker, $gbDateFormatUS, $gdteNow;

      if (!bTestForURLHack('inventoryMgr')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lIItemID, 'inventory item ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lIItemID'] = $lIItemID = (int)$lIItemID;

      $displayData['bCheckOut']  = $bCheckOut  = (bool)$bCheckOut;
      $displayData['bCheckIn']   = $bCheckIn   = (bool)$bCheckIn;
      $displayData['bLost']      = $bLost      = (bool)$bLost;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('staff/inventory/minventory');
      $this->load->model  ('staff/inventory/minv_cico',   'ccico');
      $this->load->model  ('admin/madmin_aco',       'clsACO');
      $this->load->helper ('dl_util/web_layout');
      $this->load->helper ('staff/link_inventory');
      $this->load->helper ('dl_util/time_date');

      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $displayData['formData'] = new stdClass;
      $this->load->library('generic_form');

         // load the inventory item
      $this->ccico->loadSingleInventoryItem($lIItemID, $lNumItems, $items);
      $displayData['item'] = $item = &$items[0];
      $displayData['lICatID'] = $lICatID = $displayData['item']->lCategoryID;

         // most recent check-out / check-in entry
      $this->ccico->itemCICOMostRecent($lIItemID, $lNumCICO, $CICOrec);
      $displayData['bNew'] = $bNew = $lNumCICO==0;
      $displayData['lCICO_ID'] = $lCICO_ID = $CICOrec->lKeyID;
      $displayData['CICOrec']  = $CICOrec;

      $bCheckedOut = $this->ccico->bItemCheckedOut($lIItemID, $lCICO_CO_ID);
      $bLost       = $item->bLost;

         // make sure the request is compatible with current ci/co state of item
      if ($bCheckOut){
         if ($bCheckedOut){
            $this->session->set_flashdata('error', 'The item is currently checked out.');
            redirect('staff/inventory/inventory_items/iitemRec/'.$lIItemID);
         }
         if ($bLost){
            $this->session->set_flashdata('error', 'This item is flagged as "lost" and can not be checked out.');
            redirect('staff/inventory/inventory_items/iitemRec/'.$lIItemID);
         }
         $strTitleLabel = 'Item Check-out / Loan';
         $strVerifyDate = 'Date Checked Out';
      }elseif ($bCheckIn){
         if (!$bCheckedOut){
            $this->session->set_flashdata('error', 'The item is not currently checked out.');
            redirect('staff/inventory/inventory_items/iitemRec/'.$lIItemID);
         }
         if ($bLost){
            $this->session->set_flashdata('error', 'This item is flagged as "lost" and can not be checked out.');
            redirect('staff/inventory/inventory_items/iitemRec/'.$lIItemID);
         }
         $strTitleLabel = 'Item Check-in / Return';
         $strVerifyDate = 'Date Checked In';
      }elseif ($bLost){
         $strTitleLabel = 'Item Lost';
      }else {
         screamForHelp('Invalid ci/co operation<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      if ($bCheckOut){
         $this->form_validation->set_rules('txtCheckedOutTo', '<b>Checked-Out To</b>', 'trim|required');
         $this->form_validation->set_rules('txtSecurity',     'Security Deposit');
         $this->form_validation->set_rules('txtCO_Notes',     'Notes');
         $this->form_validation->set_rules('txtDateCICO', '<b>'.$strVerifyDate.'</b>', 'trim|required|callback_itemVerifyDateCICOValid[false,0]');
      }elseif ($bCheckIn){
         $this->form_validation->set_rules('txtCO_Notes',     'Notes');
         $this->form_validation->set_rules('txtDateCICO', '<b>'.$strVerifyDate.'</b>',
                         'trim|required|callback_itemVerifyDateCICOValid[true,'.$CICOrec->dteCheckedOut.']');
      }elseif ($bLost){
      }

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');

            // load the inventory category breadcrumbs
         $item->strCatBreadCrumb = '';
         $this->ccico->icatBreadCrumbs($item->strCatBreadCrumb, $lICatID);

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bCheckOut){
               $displayData['formData']->txtSecurity     = '';
               $displayData['formData']->txtCO_Notes     = '';
               $displayData['formData']->txtCheckedOutTo = '';
               $displayData['formData']->txtDateCICO     = date($gstrFormatDatePicker, $gdteNow);

/*
               $displayData['formData']->txtSecurity     = htmlspecialchars($CICOrec->strSecurity);
               $displayData['formData']->txtCO_Notes     = htmlspecialchars($CICOrec->strCO_Notes);
               $displayData['formData']->txtCheckedOutTo = htmlspecialchars($CICOrec->strCheckedOutTo);
               if (is_null($CICOrec->dteCheckedOut)){
                  $displayData['formData']->txtDateCICO = '';
               }else {
                  $displayData['formData']->txtDateCICO = date($gstrFormatDatePicker, $CICOrec->dteCheckedOut);
               }
*/
            }elseif ($bCheckIn){
               $displayData['formData']->txtCI_Notes     = htmlspecialchars($CICOrec->strCI_Notes);
               if (is_null($CICOrec->dteCheckedIn)){
                  $displayData['formData']->txtDateCICO = '';
               }else {
                  $displayData['formData']->txtDateCICO = date($gstrFormatDatePicker, $item->dteCheckedIn);
               }
            }elseif ($bLost){
            }
         }else {
            setOnFormError($displayData);
            if ($bCheckOut){
               $displayData['formData']->txtSecurity     = set_value('txtSecurity');
               $displayData['formData']->txtCO_Notes     = set_value('txtCO_Notes');
               $displayData['formData']->txtCheckedOutTo = set_value('txtCheckedOutTo');
               $displayData['formData']->txtDateCICO     = set_value('txtDateCICO');
            }elseif ($bCheckIn){
               $displayData['formData']->txtDateCICO     = set_value('txtDateCICO');
               $displayData['formData']->txtCI_Notes     = set_value('txtCI_Notes');
            }elseif ($bLost){
            }
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['contextSummary'] = $this->ccico->strIItemHTMLSummary($item);

         $displayData['pageTitle']   = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                .' | '.anchor('staff/inventory/icat/viewICats', 'Inventory Categories', 'class="breadcrumb"')
                                .' | '.anchor('staff/inventory/inventory_items/iitemRec/'.$lIItemID, 'Inventory Item', 'class="breadcrumb"')
                                .' | '.$strTitleLabel;

         $displayData['title']          = CS_PROGNAME.' | Inventory Management';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'staff/inventory/cico_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $CICOrec->lItemID = $lIItemID;
         $strDate   = trim($_POST['txtDateCICO']);
         MDY_ViaUserForm($strDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
         if ($bCheckOut){
            $CICOrec->strCO_Notes     = trim($_POST['txtCO_Notes']);
            $CICOrec->strCheckedOutTo = trim($_POST['txtCheckedOutTo']);
            $CICOrec->strSecurity     = trim($_POST['txtSecurity']);
            $CICOrec->mdteCheckedOut  = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);
            $lCICO_ID = $this->ccico->lAddCheckOutRec($CICOrec);
            $this->ccico->lAddItemHistoryRec($lIItemID, $lCICO_ID, 'checked-out');
            $this->session->set_flashdata('msg', 'Inventory item checked-out');
         }elseif ($bCheckIn){
            $CICOrec->strCI_Notes     = trim($_POST['txtCI_Notes']);
            $CICOrec->mdteCheckedIn   = strMoDaYr2MySQLDate($lMon, $lDay, $lYear);
            $this->ccico->addCheckInRec($CICOrec);
            $this->ccico->lAddItemHistoryRec($lIItemID, $lCICO_ID, 'checked-in');
            $this->session->set_flashdata('msg', 'Inventory item checked-in');
         }elseif ($bLost){
         }
         redirect('staff/inventory/inventory_items/iitemRec/'.$lIItemID);
      }
   }

   function itemVerifyDateCICOValid($strCICODate, $strParms){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $parms = explode(',', $strParms);

      $bVerifyAfterCO = $parms[0]=='true';
      $dteCO          = (int)$parms[1];

      if(!bValidVerifyDate($strCICODate)){
         $this->form_validation->set_message('itemVerifyDateCICOValid', 'Please enter a valid date.');
         return(false);
      }else {
         if ($bVerifyAfterCO){
           // CBH: cart before horse
            if (!bVerifyCartBeforeHorse(date($genumDateFormat, $dteCO), $strCICODate)){
               $this->form_validation->set_message('itemVerifyDateCICOValid', 'Your check-in date is <b>before</b> the check-out date!.');
               return(false);
            }
         }
      }
      return(true);
   }

}