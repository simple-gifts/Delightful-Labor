<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ts_admin extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewUsers(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
//      $this->load->helper('dl_util/permissions');    // in autoload

      if (!bAllowAccess('timeSheetAdmin')){
         redirect('staff/timesheets/ts_log_edit/error_tst_access');
      }
      
      $displayData = array();
      $displayData['js'] = '';

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('staff/link_staff');
      $this->load->helper('dl_util/web_layout');
      $this->load->model ('staff/mtime_sheets', 'cts');
      $this->load->helper('staff/timesheet');
      $this->load->helper('staff/link_staff');
      $this->load->helper('dl_util/time_date');
      
         //--------------------------
         // Stripes
         //--------------------------
      $this->load->model ('util/mbuild_on_ready',  'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

      $this->cts->loadMappedUsers($lNumUsers, $users);
      if ($lNumUsers > 0){
         foreach ($users as $user){
            $lUserID = $user->lStaffID;
            $user->lNumSubmitted    = $this->cts->lNumSubUnSubTSViaUserID(true,  $lUserID);
            $user->lNumNotSubmitted = $this->cts->lNumSubUnSubTSViaUserID(false, $lUserID);
         }
      }
      $displayData['lNumUsers'] = $lNumUsers;
      $displayData['users']     = &$users;
      
/*/ -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$users   <pre>');
echo(htmlspecialchars( print_r($users, true))); echo('</pre></font><br>');
// ------------------------------------- */
      

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/more', 'More', 'class="breadcrumb"')
                                .' | Time Sheet Administration';

      $displayData['title']          = CS_PROGNAME.' | Staff';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'staff/timesheets/ts_admin_users_view';
      $this->load->vars($displayData);
      $this->load->view('template');

   }


}



