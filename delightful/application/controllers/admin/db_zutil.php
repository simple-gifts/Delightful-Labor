<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class db_zutil extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function backup(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('dl_util/web_layout');
      $this->load->library('generic_form');

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                .' | Database Backup';

      $displayData['title']          = CS_PROGNAME.' | Database Backup';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'admin/db_backup_opts_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function backupRun(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;

      $this->load->dbutil();

      $strBackupType = trim($_POST['rdoType']);
      $bIncludeDrop  = @$_POST['chkDrop']=='true';
      $strDB         = $this->db->database;

      $this->genericDBBackup($strBackupType, $strDB, $bIncludeDrop);
   }

   function backupRunAuto(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $this->load->dbutil();

      $strBackupType = 'zip';
      $bIncludeDrop  = true;
      $strDB         = $this->db->database;

      $this->genericDBBackup($strBackupType, $strDB, $bIncludeDrop);
   }

   private function genericDBBackup($strBackupType, $strDB, $bIncludeDrop){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      set_time_limit (120);
      $this->load->helper('download');
      $this->load->model('admin/mdb_backup', 'cDBBackup');

      $prefs = array(
                'format'      => $strBackupType,    // gzip, zip, txt
                'filename'    => $strDB.'.sql',     // File name - NEEDED ONLY WITH ZIP FILES
                'add_drop'    => $bIncludeDrop,     // Whether to add DROP TABLE statements to backup file
                'add_insert'  => TRUE,              // Whether to add INSERT data to backup file
                'blockSize'   => 100,               // number of VALUES entries per statement
                'newline'     => "\n"               // Newline character used in backup file
              );
      $strBackup = $this->cDBBackup->strBackup($prefs);

      force_download($strDB.'.'.$strBackupType, $strBackup);
   }

   function opt(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->helper('dl_util/web_layout');
      $this->load->library('generic_form');

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                .' | Database Optimization';

      $displayData['title']          = CS_PROGNAME.' | Database Optimization';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'admin/db_opt_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

    function optRun(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();
      set_time_limit (90);

      $this->load->dbutil();
      $displayData['result'] = $this->dbutil->optimize_database();

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/admin',  'Admin',                 'class="breadcrumb"')
                                .' | '.anchor('admin/db_zutil/opt', 'Database Optimization', 'class="breadcrumb"');

      $displayData['title']          = CS_PROGNAME.' | Database Optimization';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'admin/db_opt_result_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function utables(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('devOnly')) return;

      $displayData = array();
      $displayData['js'] = '';

         //------------------------------------------------
         // libraries and utilities
         //------------------------------------------------
      $this->load->model('admin/dev/mdev_utables', 'cUT');
      $this->load->helper('clients/link_client_features');

         //-------------------------------------
         // stripes
         //-------------------------------------
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $this->cUT->loadUTableInfo($displayData['lNumUTables'], $displayData['utables']);

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                .' | Database Optimization';

      $displayData['title']          = CS_PROGNAME.' | DEV: Personalized Tables';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'admin/dev/utables_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }



}