<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class provider_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function viewProvider($lProviderID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $gclsChapterACO; // $gstrFormatDatePicker, $gbDateFormatUS;

      if (!bTestForURLHack('showGrants')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lProviderID, 'provider ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lProviderID'] = $lProviderID = (integer)$lProviderID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model  ('grants/mgrants',          'cgrants');
      $this->load->model  ('img_docs/mimage_doc',     'clsImgDoc');
      $this->load->model  ('img_docs/mimg_doc_tags',  'cidTags');
      
      $this->load->helper ('dl_util/web_layout');
      $params = array('enumStyle' => 'terse', 'clsRpt');
      $this->load->library('generic_rpt', $params);
      $this->load->helper ('grants/link_grants');
      $this->load->helper ('dl_util/record_view');
      $this->load->helper ('img_docs/image_doc');
      $this->load->helper ('img_docs/link_img_docs');
      $this->load->model('admin/madmin_aco', 'clsACO');
//      $this->load->helper ('img_docs/link_img_docs');
      
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();
      
         //-------------------------------------
         // stripes
         //-------------------------------------
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;      
      
      $this->load->model  ('util/mlist_generic',     'clsList');
      $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;

         // load the grant provider
      $this->cgrants->loadGrantProviderViaGPID($lProviderID, $lNumProviders, $providers);
      $provider = $displayData['provider'] = &$providers[0];
      
         // load the associated grants
      $this->cgrants->loadGrantsViaProviderID($lProviderID, '', '', $displayData['provider']->lNumGrants, $displayData['provider']->grants);
// -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$displayData[provider]   <pre>');
echo(htmlspecialchars( print_r($displayData['provider'], true))); echo('</pre></font><br>');
// -------------------------------------
      
         //-------------------------------
         // images and documents
         //-------------------------------
      loadImgDocRecView($displayData, CENUM_CONTEXT_GRANTPROVIDER, $lProviderID);
      
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/financials',        'Financials/Grants', 'class="breadcrumb"')
                                .' | Grant Provider Record';

      $displayData['title']          = CS_PROGNAME.' | Grants';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'grants/provider_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
      
   }
   
}




