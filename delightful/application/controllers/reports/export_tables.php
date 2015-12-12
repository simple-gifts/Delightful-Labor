<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class export_tables extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function run($enumContext){
      if (!bTestForURLHack('allowExports')) return;
      if (!bTestForURLHack('showUTable', $enumContext)) return;
      
      $this->load->model('reports/mexports', 'clsExports');
      $this->load->helper('download');
      $this->load->helper('reports/report_util');
      $this->load->dbutil();
      force_download($this->strTableExportFN($enumContext), $this->strExportViaContext($enumContext));
   }

   private function strExportViaContext($enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumContext){
         case CENUM_CONTEXT_PEOPLE:
            return($this->clsExports->strExportPeople());
            break;

         case CENUM_CONTEXT_BIZ:
            return($this->clsExports->strExportBiz());
            break;

         case CENUM_CONTEXT_BIZCONTACT:
            return($this->clsExports->strExportBizCon());
            break;

         case CENUM_CONTEXT_GIFT:
            return($this->clsExports->strExportGift());
            break;

         case CENUM_CONTEXT_GIFTMEM:
            return($this->clsExports->strExportHonMem(false));
            break;

         case CENUM_CONTEXT_GIFTHON:
            return($this->clsExports->strExportHonMem(true));
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            return($this->clsExports->strExportSpon());
            break;

         case CENUM_CONTEXT_SPONSORPACKET:
            $this->load->model('clients/mclient_status', 'clsClientStat');
            return($this->clsExports->strExportSponPacket());
            break;

         case CENUM_CONTEXT_CLIENT:
            return($this->clsExports->strExportClients());
            break;

         default:
            $this->session->set_flashdata('error', 'The '.$enumContext.' export is currently under development');
            redirect('reports/exports/showTableOpts');
      }
   }

   function strTableExportFN($strTable){
      return('exp_'.$strTable.'_'.date('Ymd_His').'.csv');
   }

   function utab($lTableID){
      $lTableID = (integer)$lTableID;

      $this->load->model ('personalization/muser_fields', 'clsUF');
      $this->load->model ('admin/mpermissions',           'perms');
      $this->load->model ('reports/mexports',             'clsExports');
      $this->load->helper('download');
      $this->load->dbutil();

      force_download($this->strTableExportFN('UserTable'), $this->clsExports->strExportUserTable($lTableID));
   }


   function img($enumContext){
      $this->imgDoc(true, $enumContext);
   }
   
   function doc($enumContext){
      $this->imgDoc(false, $enumContext);
   }

   function imgDoc($bImage, $enumContext){
      $this->load->dbutil();
      $this->load->helper('download');
      $this->load->helper('reports/report_util');
      $this->load->model('reports/mexports', 'clsExports');

      force_download($this->strTableExportFN(($bImage ? 'Images' : 'Docs')), 
                     $this->clsExports->strExportImgDocs($bImage, $enumContext));
   }



}
