<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ts_projects extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewTSProjects(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bAllowAccess('adminOnly')) return;

      $displayData = array();
      $displayData['js'] = '';
      
         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('staff/link_staff');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('staff/link_staff');
      $this->load->model ('staff/mtime_sheets', 'cts');    

         //---------------------------
         // stripes
         //---------------------------
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;      
      
      $this->cts->sqlWhere = " AND NOT tsp_bRetired ";
      $this->cts->sqlOrder = 'tsp_bInternalProject, tsp_strProjectName, tsp_lKeyID';
      $this->cts->loadTSProjects($displayData['lNumProjects'], $displayData['projects'], null, null);
      
         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['pageTitle']      = anchor('main/menu/admin',        'Admin', 'class="breadcrumb"')
                                .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                                .' |   Time Sheets: Billable Projects';

      $displayData['title']          = CS_PROGNAME.' | Admin';
      $displayData['nav']            = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate']   = 'admin/lists_ts_projects_view';
      $this->load->vars($displayData);
      $this->load->view('template');
      
   }
   
   function addEditTSProject($lTSProjID){
   //---------------------------------------------------------------------
   //
   //--------------------------------------------------------------------- 
      if (!bAllowAccess('adminOnly')) return;

      $displayData = array();
      $displayData['js'] = '';
      
      $displayData['lTSProjID'] = $lTSProjID = (int)$lTSProjID;
      $displayData['bNew']      = $bNew = $lTSProjID <= 0;
      
         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->helper('staff/link_staff');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('staff/link_staff');
      $this->load->model ('staff/mtime_sheets', 'cts');    
      
      if (!$bNew){
         $this->cts->loadTSProjectViaTSID($lTSProjID, $lNumProjects, $projects);
         $project = &$projects[0];
      }
      
         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtProject',  'Project Name', 'trim|required|callback_verifyUniquePN['.$lTSProjID.']');
		$this->form_validation->set_rules('chkInternal', 'Internal Project', 'trim');

		if ($this->form_validation->run() == FALSE){
         $displayData['formData'] = new stdClass;
         $this->load->library('generic_form');
      
            // first time displayed, no user data entry errors
         if (validation_errors()==''){
            if ($bNew){
               $displayData['formData']->txtProject        = '';
               $displayData['formData']->bInternalProject  = false;
            }else {
               $displayData['formData']->txtProject = htmlspecialchars($project->strProjectName);
               $displayData['formData']->bInternalProject = $project->bInternalProject;
            }
            
         }else {
            setOnFormError($displayData);
            $displayData['formData']->txtProject       = set_value('txtProject');
            $displayData['formData']->bInternalProject = set_value('chkInternal')=='true';
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         $displayData['pageTitle']      = anchor('main/menu/admin',        'Admin', 'class="breadcrumb"')
                                   .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                                   .' | '.anchor('admin/timesheets/ts_projects/viewTSProjects', 'Time Sheets: Billable Projects', 'class="breadcrumb"')
                                   .' | '.($bNew ? 'Add New' : 'Edit').' Time Sheet Project';

         $displayData['title']          = CS_PROGNAME.' | Admin';
         $displayData['nav']            = $this->mnav_brain_jar->navData();
         $displayData['mainTemplate']   = 'admin/ts_projects_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $strProject = trim($_POST['txtProject']);
         $bInternalProject = @$_POST['chkInternal']=='true';
      
            //------------------------------------
            // update db tables and return
            //------------------------------------
         if ($bNew){
            $lTSProjID = $this->cts->addTSProject($strProject, $bInternalProject);
            $this->session->set_flashdata('msg', 'Time sheet project added');
         }else {
            $this->cts->updateTSProject($lTSProjID, $strProject, $bInternalProject);
            $this->session->set_flashdata('msg', 'Time sheet project updated');
         }         
         redirect('admin/timesheets/ts_projects/viewTSProjects');         
      }   
   }
   
   function verifyUniquePN($strVal, $params){
      $arrayParams = explode(',', $params);
      $lTSProjID   = (integer)$arrayParams[0];

      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                $strVal,    'tsp_strProjectName',
                $lTSProjID, 'tsp_lKeyID',
                true,       'tsp_bRetired',
                false,      null, null,
                false,      null, null,
                'staff_ts_projects')){
         $this->form_validation->set_message('verifyUniquePN',
                  'The Project Name you specified is already in your database.');
         return(false);
      }else {
         return(true);
      }
   }
   
   function removeProject($lTSProjID){
   //---------------------------------------------------------------------
   //
   //--------------------------------------------------------------------- 
      if (!bAllowAccess('adminOnly')) return;
      
      $lTSProjID = (int)$lTSProjID;
      
         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model ('staff/mtime_sheets', 'cts');  
      $this->cts->loadTSProjectViaTSID($lTSProjID, $lNumProjects, $projects);
      $this->cts->removeTSProject($lTSProjID);

      $this->session->set_flashdata('msg', 'Time sheet project <b>"'
                    .htmlspecialchars($projects[0]->strProjectName).'"</b> was removed');
      redirect('admin/timesheets/ts_projects/viewTSProjects');         
   }         
   
   
}
   
   