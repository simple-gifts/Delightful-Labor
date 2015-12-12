<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class grant_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function viewGrant($lGrantID){
   //-------------------------------------------------------------------------
   //
   //-------------------------------------------------------------------------
      global $gclsChapterACO; // $gstrFormatDatePicker, $gbDateFormatUS;

      if (!bTestForURLHack('showGrants')) return;

      $this->load->helper('dl_util/verify_id');
      verifyID($this, $lGrantID, 'grant ID');

      $displayData = array();
      $displayData['js'] = '';
      $displayData['lGrantID'] = $lGrantID = (integer)$lGrantID;

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
//      $this->load->helper ('img_docs/link_img_docs');

      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->model  ('util/mlist_generic',     'clsList');
      $this->clsList->enumListType = CENUM_LISTTYPE_ATTRIB;

         // load the associated grants
      $this->cgrants->loadGrantsViaGrantID($lGrantID, '', $lNumGrants, $grants);
      $grant = $displayData['grant'] = &$grants[0];

/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$grant   <pre>');
echo(htmlspecialchars( print_r($grant, true))); echo('</pre></font><br>');
// ------------------------------------- */


         // load the grant provider
      $lProviderID = $grant->lProviderKeyID;
      $this->cgrants->loadGrantProviderViaGPID($lProviderID, $lNumProviders, $providers);
      $provider = $displayData['provider'] = &$providers[0];


         //-------------------------------
         // images and documents
         //-------------------------------
      loadImgDocRecView($displayData, CENUM_CONTEXT_GRANTPROVIDER, $lProviderID);


         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['contextSummary'] = $this->cgrants->providerHTMLSummary($provider);
      $displayData['pageTitle']      = anchor('main/menu/financials',                              'Financials/Grants', 'class="breadcrumb"')
                                .' | '.anchor('grants/provider_directory/viewProDirectory',        'Provider Directory', 'class="breadcrumb"')
                                .' | '.anchor('grants/provider_record/viewProvider/'.$lProviderID, 'Provider Record', 'class="breadcrumb"')
                                .' | Grant Record';

      $displayData['title']          = CS_PROGNAME.' | Grants';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'grants/grant_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');

   }

}

