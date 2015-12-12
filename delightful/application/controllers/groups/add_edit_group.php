<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class add_edit_group extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEdit($enumGroupType, $lGID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bAllowAccess('adminOnly')) return;
      $this->load->helper('dl_util/verify_id');
      if ($lGID!='0') verifyID($this, $lGID, 'group ID');

      $displayData = array();
      $lGID = (integer)$lGID;

         //-------------------------
         // models & helpers
         //-------------------------
      $this->load->model('groups/mgroups', 'clsGroups');
      $this->load->helper('groups/groups');
      $this->load->helper('dl_util/web_layout');
      $displayData['strGroupType']  = $strGroupType = strXlateContext($enumGroupType);

      groupExtensionProperties($enumGroupType, $gProps);
      $displayData['gProps'] = &$gProps;

        //----------------------------
        // validation rules
        //----------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('txtGroupName', $strGroupType.' Group Name',
                          'trim|callback_groupNameBlankTest|callback_groupNameDupTest['.$enumGroupType.','.$lGID.']');
      if ($gProps->extended){
         if ($gProps->lNumBool > 0){
            foreach ($gProps->bools as $bField){
               $this->form_validation->set_rules($bField->strFormFN, 'Check Box', 'trim');
            }
         }
         if ($gProps->lNumInt > 0){
            echo(__FILE__.' '.__LINE__.'<br>'."\n"); die;
         }
      }

      if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');

         $displayData['lGID'] = $lGID = (integer)$lGID;
         $displayData['bNew'] = $bNew = $lGID <= 0;
         $displayData['enumGroupType'] = $this->clsGroups->gp_enumGroupType = $enumGroupType = htmlspecialchars($enumGroupType);

         if (validation_errors()==''){
            $this->clsGroups->loadActiveGroupsViaType(
                     $enumGroupType, '', '',
                     true,           $lGID);

            $gl = &$this->clsGroups->arrGroupList[0];
            $displayData['strGroupName'] = htmlspecialchars($gl->strGroupName);

               // set the database values for extended fields
            if ($gProps->extended){
               if ($gProps->lNumBool > 0){
                  foreach ($gProps->bools as $bField){
                     $strFN = $bField->strDBFN;
                     $bField->bValue = $gl->$strFN;
                  }
               }
               if ($gProps->lNumInt > 0){
                  echo(__FILE__.' '.__LINE__.'<br>'."\n"); die;
               }
            }
         }else {
            setOnFormError($displayData);
            $displayData['strGroupName'] = set_value('txtGroupName');

            if ($gProps->extended){
               if ($gProps->lNumBool > 0){
                  foreach ($gProps->bools as $bField){
                     $bField->bValue = set_value($bField->strFormFN)=='true';
                  }
               }
               if ($gProps->lNumInt > 0){
                  echo(__FILE__.' '.__LINE__.'<br>'."\n"); die;
               }
            }
         }

         $displayData['nav']           = $this->mnav_brain_jar->navData();

            //----------------------
            // set breadcrumbs
            //----------------------
         $displayData['title']        = CS_PROGNAME.' | Groups';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('admin/alists/showLists', 'Lists', 'class="breadcrumb"')
                                 .' | '.anchor('groups/groups_view/view/'.$enumGroupType, 'Groups: '.$strGroupType, 'class="breadcrumb"')
                                 .' | '.($bNew ? 'Add New' : 'Edit');

         $displayData['mainTemplate'] = 'groups/group_add_edit_view';
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $bNew = $lGID <= 0;
         $strGroupCat = strXlateContext($enumGroupType);
         $this->clsGroups->loadActiveGroupsViaType(
                        $this->clsGroups->gp_enumGroupType, '', '',
                        true,                      -1);
         $clsList                = $this->clsGroups->arrGroupList[0];
         $clsList->lKeyID        = $lGID;
         $clsList->strGroupName  = xss_clean(trim($_POST['txtGroupName']));
         $clsList->dteExpire     = strtotime('1/1/2030');
         $clsList->bTempGroup    = false;
         $clsList->strNotes      = '';

         if ($gProps->extended){
            if ($gProps->lNumBool > 0){
               foreach ($gProps->bools as $bField){
                  $strFN = $bField->strDBFN;
                  $clsList->$strFN = @$_POST[$bField->strFormFN]=='true';
               }
            }
            if ($gProps->lNumInt > 0){
               echo(__FILE__.' '.__LINE__.'<br>'."\n"); die;
            }
         }

         if ($bNew){
            $this->clsGroups->gp_enumGroupType = $enumGroupType;
            $this->session->set_flashdata('msg', $strGroupCat.' Group added');
            $this->clsGroups->lAddNewGroupParent();
         }else {
            $this->session->set_flashdata('msg', $strGroupCat.' Group updated');
            $this->clsGroups->updateGroupParentRec();
         }
         redirect('groups/groups_view/view/'.$enumGroupType);
      }
   }

   function groupNameBlankTest($strGroup, $strParams){
   //-------------------------------------------------------------------------------
   // verification callback
   //-------------------------------------------------------------------------------
      return($strGroup != '');
   }

   function groupNameDupTest($strGroup, $strParams){
   //-------------------------------------------------------------------------------
   // verification callback
   //-------------------------------------------------------------------------------
      $params = explode(',', $strParams);
      $enumGroupType = $params[0];
      $lGID          = (integer)$params[1];

      $this->load->model('util/mverify_unique', 'clsUnique');
      if (!$this->clsUnique->bVerifyUniqueText(
                $strGroup,  'gp_strGroupName',
                $lGID,      'gp_lKeyID',
                false,      null,
                true,       $enumGroupType, 'gp_enumGroupType',
                false,      null,            null,
                'groups_parent')){
         return(false);
      }else {
         return(true);
      }
   }

   function remove($enumGroupType, $lGID){
   //-------------------------------------------------------------------------------
   // remove group
   //-------------------------------------------------------------------------------
      $this->load->model('groups/mgroups', 'clsGroups');
      $this->load->helper('groups/groups');

      $lGID = $this->clsGroups->lGroupID = (integer)$lGID;
      $strGroupCat = strXlateContext($enumGroupType);
      $this->clsGroups->remGroup();

      $this->session->set_flashdata('msg', $strGroupCat.' Group removed');
      redirect('groups/groups_view/view/'.$enumGroupType);

   }

}
