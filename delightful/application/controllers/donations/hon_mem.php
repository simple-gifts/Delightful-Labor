<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class hon_mem extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function addNewSelect($enumHMType, $lHMID=0){
      if (!bTestForURLHack('editGifts')) return;
      $lHMID = (integer)$lHMID;

      $bHon = $enumHMType == 'hon';
      $bMem = $enumHMType == 'mem';
      $bMC  = $enumHMType == 'mc';   // mail contact for memorial

      $this->load->model('donations/mhon_mem', 'clsHonMem');
      $strTitle = $this->clsHonMem->strXlateSearchType($enumHMType);

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtSearch', 'SEARCH', 'trim|required');
      $bFormValidated = $this->form_validation->run();

         //----------------------
         // set breadcrumbs
         //----------------------
      $displayData = array();

      if ($bFormValidated){
         $strMCExtra = ($bMC ? '/'.$lHMID : '');
         $strBCExtra = ' | '.anchor('donations/hon_mem/addNewSelect/'.$enumHMType.$strMCExtra, 'Add New: Search', 'class="breadcrumb"')
                      .' | Add New: Select';
      }else {
         $strBCExtra = ' | Add New: Search';
      }

      $strMemSafeName = '';
      $lMC_PID = 0;
      if ($bHon){
         $strBC = anchor('admin/admin_special_lists/hon_mem/honView', 'Honorariums', 'class="breadcrumb"');
         $strLabel = 'Honorarium';
      }elseif ($bMem){
         $strBC = anchor('admin/admin_special_lists/hon_mem/memView', 'Memorials', 'class="breadcrumb"');
         $strLabel = 'Memorial';
      }elseif ($bMC){
         $strBC = anchor('admin/admin_special_lists/hon_mem/memView', 'Memorials', 'class="breadcrumb"');
         $strLabel = 'Mail Contact for Memorial';
         $this->clsHonMem->lHMID = $lHMID;
         $this->clsHonMem->loadHonMem('via HMID');
         $lMC_PID = $this->clsHonMem->honMemTable[0]->ghm_lFID;
         $strMemSafeName = $this->clsHonMem->honMemTable[0]->honorMem->strSafeNameFL;
      }

      $displayData['title']        = CS_PROGNAME.' | Client Status Categories';
      $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                              .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                              .' | '.$strBC
                              .$strBCExtra;
      $displayData['nav']          = $this->mnav_brain_jar->navData();


		if (!$bFormValidated){
         $displayData['search'] = new stdClass;
         $displayData['search']->strButtonLabel = 'Search';

         if ($bMC){
            $displayData['search']->lHMID = $this->clsHonMem->lHMID = $lHMID;
            $displayData['search']->strLegendLabel =
              'Add a new <i>mail contact</i> for '.$strMemSafeName;
         }else {
            $displayData['search']->strLegendLabel = 'Add a new <i>'.$strTitle.'</i>';
         }

         $displayData['search']->formLink = 'donations/hon_mem/addNewSelect/'.$enumHMType;

            // if mail contact, include memorial ID
         if ($bMC){
            $displayData['search']->formLink .= '/'.$lHMID;
         }

         $displayData['search']->lSearchTableWidth = 240;
         $displayData['search']->bBiz = false;


         $displayData['mainTemplate'] = 'util/search_people_biz_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $this->searchSelected(
                       $displayData,   $enumHMType, $bHon,
                       $bMem,          $bMC,        $lHMID,
                       $lMC_PID,       $strMemSafeName, $strTitle,
                       xss_clean(trim($_POST['txtSearch'])));
      }
   }

   private function searchSelected(
                           &$displayData, $enumHMType,     $bHon,
                           $bMem,         $bMC,            $lHMID,
                           $lMC_PID,      $strMemSafeName, $strTitle,
                           $strSearch){

      if (!bTestForURLHack('editGifts')) return;

      $this->load->model('util/msearch_single_generic', 'clsSearch');
      $this->load->model('people/mpeople',  'clsPeople');

      $displayData['search'] = new stdClass;
      $displayData['search']->strButtonLabel = 'Select';
      $displayData['search']->strSearchTerm  = $strSearch;
      $lLeftCnt = strlen($strSearch);
      $this->clsSearch->strWhereExtra = '';
      if ($bHon || $bMem){
         $lPIDs = $this->clsHonMem->allHonMemPIDs($bHon, $bMem, $bMC);
         if (count($lPIDs) > 0){
            $this->clsSearch->strWhereExtra .= ' AND NOT ((pe_lKeyID IN ('.implode(',', $lPIDs).'))) ';
         }
      }else {
         $this->clsSearch->strWhereExtra .= " AND (pe_lKeyID != $lMC_PID) ";
      }
      $this->clsSearch->strWhereExtra .= " AND LEFT(pe_strLName, $lLeftCnt)=".strPrepStr($strSearch).' ';

      $displayData['search']->enumSearchType      = CENUM_CONTEXT_PEOPLE;
      $displayData['search']->strSearchLabel      = 'People';
      $displayData['search']->strSelectLabel      = 'Select this contact';
      $displayData['search']->bShowKeyID          = true;
      $displayData['search']->bShowSelect         = true;
      $displayData['search']->bShowEnumSearchType = false;

      if ($bMC){
         $displayData['search']->strDisplayTitle =
           'Please select the new <i>mail contact</i> for '.$strMemSafeName;
      }else {
         $displayData['search']->strDisplayTitle =
                '<br>Please select the person for the new '.$strTitle.':<br>';
      }

      $displayData['search']->formLink = 'donations/hon_mem/addNewSelected/'.$enumHMType;
         // if mail contact, include memorial ID
      if ($bMC){
         $displayData['search']->formLink .= '/'.$lHMID;
      }
      
      $displayData['search']->strIDLabel = 'peopleID: ';
      $displayData['search']->bShowLink  = false;

      $this->clsSearch->searchPeople();

      $displayData['search']->lNumSearchResults = $this->clsSearch->lNumSearchResults;
      $displayData['search']->searchResults     = $this->clsSearch->searchResults;

      $displayData['mainTemplate'] = 'util/search_sel_people_biz_view';
      $this->load->vars($displayData);
      $this->load->view('template');

   }


   function addNewSelected($enumHMType, $id1, $id2=0){ //$lPID, $lMCID=0){
      if (!bTestForURLHack('editGifts')) return;
      if ($enumHMType=='mc'){
         $lPID  = $id2;
         $lMCID = $id1;
      }else {
         $lPID = $id1;
      }

      $this->load->model('donations/mhon_mem', 'clsHonMem');
      $this->load->model('people/mpeople', 'clsPeople');
      $this->load->helper('dl_util/util_db');
      $this->clsPeople->lPeopleID = $lPID;
      $this->clsPeople->peopleInfoLight();
      $this->session->set_flashdata('msg', $this->clsPeople->strSafeName
                         .' was added as '.$this->clsHonMem->strXlateSearchType($enumHMType));

      $bHon = false;
      switch ($enumHMType){
         case 'hon':
            $this->clsHonMem->lAddHonoree($lPID);
            $bHon = true;
            break;
         case 'mem':
            $this->clsHonMem->lAddMemorial($lPID);
            break;
         case 'mc':
            $this->clsHonMem->setMemorialMailContact($lPID, $lMCID);
            break;
         default:
            screamForHelp($enumHMType.': Invalid switch value</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
            break;
      }
      redirect('admin/admin_special_lists/hon_mem/'.($bHon ? 'honView' : 'memView'));
   }

   public function hide($lHMID){
      $this->hideUnHide($lHMID, true);
   }
   public function unhide($lHMID){
      $this->hideUnHide($lHMID, false);
   }

   private function hideUnHide($lHMID, $bHide){
      if (!bTestForURLHack('editGifts')) return;
      $lHMID = (integer)$lHMID;
      $this->load->model('donations/mhon_mem', 'clsHonMem');
      $this->clsHonMem->lHMID = $lHMID;
      $this->clsHonMem->loadHonMem('via HMID');
      $bHon = $this->clsHonMem->honMemTable[0]->ghm_bHon;
      $this->clsHonMem->hideUnhideHMRec($lHMID, $bHide);

      $this->session->set_flashdata('msg', 'The '.($bHon ? 'honorarium' : 'memorial').' for '
                             .$this->clsHonMem->honMemTable[0]->ghm_strSafeName
                             .' was '.($bHide ? 'hidden' : 'unhidden'));

      redirect('admin/admin_special_lists/hon_mem/'.($bHon ? 'honView' : 'memView'));
   }

   public function remove($lHMID){
      if (!bTestForURLHack('editGifts')) return;
      $lHMID = (integer)$lHMID;
      $this->load->model('donations/mhon_mem', 'clsHonMem');
      $this->clsHonMem->lHMID = $lHMID;
      $this->clsHonMem->loadHonMem('via HMID');
      $bHon = $this->clsHonMem->honMemTable[0]->ghm_bHon;

      $this->clsHonMem->retireHMRec($lHMID);

      $this->session->set_flashdata('msg', 'The '.($bHon ? 'honorarium' : 'memorial').' for '
                             .$this->clsHonMem->honMemTable[0]->ghm_strSafeName
                             .' was retired.');

      redirect('admin/admin_special_lists/hon_mem/'.($bHon ? 'honView' : 'memView'));
   }

   function removeMC($lHMID){
      if (!bTestForURLHack('editGifts')) return;
      $lHMID = (integer)$lHMID;
      $this->load->model('donations/mhon_mem', 'clsHonMem');
      $this->load->helper('dl_util/util_db');

      $this->clsHonMem->lHMID = $lHMID;
      $this->clsHonMem->loadHonMem('via HMID');

      $this->clsHonMem->removeHMMailContact($lHMID);

      $this->session->set_flashdata('msg', 'The mail contact for '
                             .$this->clsHonMem->honMemTable[0]->ghm_strSafeName
                             .' was removed.');

      redirect('admin/admin_special_lists/hon_mem/memView');
   }

   function addNew($lGiftID, $strHonMem){
   //---------------------------------------------------------------------
   // attach an honorarium or memorial to a gift
   //---------------------------------------------------------------------
      if (!bTestForURLHack('editGifts')) return;
      $displayData = array();
      $displayData['lGiftID']   = $lGiftID = (integer)$lGiftID;
      $displayData['bHon']      = $bHon = ($strHonMem=='hon');
      $displayData['strHonMem'] = $strHonMem;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('donations/mdonations', 'clsGifts');
      $this->load->model('donations/mhon_mem',    'clsHonMem');
      $this->load->model('admin/madmin_aco',      'clsACO');
      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

      $this->clsGifts->loadGiftViaGID($lGiftID);

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('ddlHM[]', 'Honoararium/Memorial List', 'trim|required');

		if ($this->form_validation->run() == FALSE){

         $displayData['contextSummary'] = $this->clsGifts->giftHTMLSummary();

         if ($bHon){
            $displayData['strLabel']  = 'Add honorarium for donation from '.$this->clsGifts->gifts[0]->strSafeName;
            $displayData['strButton'] = 'honorarium';
         }else {
            $displayData['strLabel']  = 'Add memorial for donation from '.$this->clsGifts->gifts[0]->strSafeName;
            $displayData['strButton'] = 'memorial';
         }

         $this->clsHonMem->loadHonMem(($bHon ? 'all - Hon Only' : 'all - Mem Only'));
         $displayData['lNumHonMem'] = $lNumHonMem = $this->clsHonMem->lNumHonMem;
         $displayData['honMemTable'] = &$this->clsHonMem->honMemTable;
         $displayData['ddlHonMem']   = $this->clsHonMem->strDDLHonMem(-1, false, true);

            //--------------------------
            // breadcrumbs
            //--------------------------
         $lPeopleBizID = $this->clsGifts->gifts[0]->gi_lForeignID;
         if ($this->clsGifts->gifts[0]->pe_bBiz){
            $displayData['pageTitle'] =
                     anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
              .' | '.anchor('biz/biz_record/view/'.$lPeopleBizID, 'Record', 'class="breadcrumb"')
              .' | '.anchor('donations/gift_record/view/'.$lGiftID,     'Gift Record',   'class="breadcrumb"')
              .' | '.($bHon ? 'Honorariums' : 'Memorials');
         }else {
            $displayData['pageTitle'] =
                     anchor('main/menu/people', 'People', 'class="breadcrumb"')
              .' | '.anchor('people/people_record/view/'.$lPeopleBizID, 'Record', 'class="breadcrumb"')
              .' | '.anchor('donations/gift_record/view/'.$lGiftID,     'Gift Record',   'class="breadcrumb"')
              .' | '.($bHon ? 'Honorariums' : 'Memorials');
         }

         $displayData['title']          = CS_PROGNAME.' | Gifts';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'donations/hon_mem_add_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $lHMCnt = 0;
         foreach($_REQUEST['ddlHM'] as $lHMID){
            $lHMID = (integer)$lHMID;
            $lHMLinkID = $this->clsHonMem->lAddHMLink($lGiftID, $lHMID);
            ++$lHMCnt;
         }
        $this->session->set_flashdata('msg',
                 ($lHMCnt == 1 ? ($bHon ? 'An honorarium was ' : 'A memorial was ') :
                                 ($bHon ? 'Honorariums were '  : 'Memorials were '))
                .'added to gift '.str_pad($lGiftID, 5, '0', STR_PAD_LEFT).' '
                .$this->clsGifts->gifts[0]->strFormattedAmnt.' / '
                .$this->clsGifts->gifts[0]->strSafeName);
         redirect('donations/gift_record/view/'.$lGiftID);
      }
   }

   function removeHMLink($lHMLinkID, $lGiftID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      if (!bTestForURLHack('editGifts')) return;
      $lHMLinkID = (integer)$lHMLinkID;
      $lGiftID   = (integer)$lGiftID;

      $this->load->model('donations/mdonations', 'clsGifts');
      $this->load->model('donations/mhon_mem',    'clsHonMem');
      $this->load->model('admin/madmin_aco',      'clsACO');

      $this->clsGifts->loadGiftViaGID($lGiftID);
      $this->clsHonMem->lHMID = $lHMLinkID;
      $this->clsHonMem->loadHonMem('via HMID');
      $bHon = $this->clsHonMem->honMemTable[0]->ghm_bHon;

      $this->clsHonMem->removeHonMemLink($lHMLinkID);

      $this->session->set_flashdata('msg',
                'The specified '.($bHon ? 'honorarium' : 'memorial').' was removed '
                .'from gift '.str_pad($lGiftID, 5, '0', STR_PAD_LEFT).' '
                .$this->clsGifts->gifts[0]->strFormattedAmnt.' / '
                .$this->clsGifts->gifts[0]->strSafeName);
      redirect('donations/gift_record/view/'.$lGiftID);
   }



}