<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class rem_add_edit extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function addEdit($enumRemType, $lFID, $lRemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS, $gdteNow;

      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, CENUM_CONTEXT_REMINDER, $lRemID, true);
      verifyIDsViaType($this, $enumRemType, $lFID, false);

      $displayData = array();
      $displayData['enumRemType'] = $enumRemType;
      $displayData['lFID']   = $lFID = (integer)$lFID;
      $displayData['lRemID'] = $lRemID = (integer)$lRemID;
      $displayData['bNew']   = $bNew = $lRemID <= 0;

         //-------------------------------
         // javascript verification
         //-------------------------------
      $this->load->library('js_build/js_verify');
      $this->load->helper('js/verify_reminder');
      $this->load->helper('js/verify_recurring');
      $this->load->helper('dl_util/page_title');
      $this->js_verify = new js_verify;
      $this->js_verify->clearEmbedOpts();
      $this->js_verify->bShow_isValidDate      =
      $this->js_verify->bShow_bVerifyString    =
      $this->js_verify->bShow_bVerifyRadio     =
      $this->js_verify->bShow_strRadioSelValue =
      $this->js_verify->bShow_trim             = true;
      $displayData['js'] = $this->js_verify->loadJavaVerify();
      $displayData['js'] .= reminderVerify();
      $displayData['js'] .= verifyRecurring();

         //--------------------------------
         // load models and helpers
         //--------------------------------
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->model('reminders/mreminders', 'clsRem');
      $this->load->model('util/mrecurring', 'clsRecurring');
      $this->load->model('people/mpeople', 'clsPeople');
      $this->load->model('donations/mdonations', 'clsGifts');
      $this->load->model('clients/mclients', 'clsClients');
      $this->load->model('biz/mbiz', 'clsBiz');
      $this->load->model('sponsorship/msponsorship', 'clsSpon');
      $this->load->model('admin/muser_accts', 'clsUser');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->library('generic_form');
      $clsForm = new generic_form;
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/reminder');
//      $this->load->helper('dl_util/email_web');
      $this->load->helper('dl_util/recurring');

         //---------------------------
         // load the reminder
         //---------------------------
      $this->clsRem->enumLookupType = 'ViaRID';
      $this->clsRem->lRID           = $lRemID;
      $this->clsRem->enumRemType    = $enumRemType;
      $this->clsRem->enumSort       = 'keyID';
      $this->clsRem->loadReminders();

      $params = array('enumStyle' => 'terse');
      $this->load->library('generic_rpt', $params);

      $displayData['clsForm'] = &$clsForm;
      $displayData['strForm'] = $strForm = 'frmRemAddEdit';
      configRemRecViewViaType($enumRemType, $lFID, $displayData);
      $reminder = &$this->clsRem->reminders[0];
      $this->load->library('js_build/java_joe_radio');
      $clsJJR = new java_joe_radio;

      if ($bNew){
         $strDate = strNumericDateViaUTS($gdteNow, $gbDateFormatUS);
         $displayData['recurringOpts'] =
                 strRecurringOpts($displayData,       $clsForm,
                                 $strForm,            $clsJJR,     $strDate,
                                 $this->clsRecurring, 'txtStart', 'Remind on',
                                 'Remind Schedule',   'Display on');
         $displayData['strInformDDL'] = $this->strInformList($bNew, null);
         $displayData['strTitle'] = '';
         $displayData['strReminderNote'] = '';
      }else {
         $this->clsRem->informListViaRemID($lRemID, $lUserIDs);
         $displayData['strInformDDL']    = $this->strInformList($bNew, $lUserIDs);
         $displayData['strTitle']        = $reminder->strTitle;
         $displayData['strReminderNote'] = $reminder->strReminderNote;
      }

         //------------------------------------------------
         // breadcrumbs / page setup
         //------------------------------------------------
      $displayData['title']        = CS_PROGNAME.' | Reminders';
      $displayData['nav']          = $this->mnav_brain_jar->navData();
      $displayData['mainTemplate'] = 'reminders/add_edit_reminder_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function strInformList($bNew, $lUserIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('admin/muser_accts', 'clsUsers');
      $this->clsUsers->loadUserRecords();
      $bSearchList = !is_null($lUserIDs);

      $strDDL =
         '<select multiple name="ddlUsers[]" size="5">
            <option value="-1" '.((($bNew && !$bSearchList) || ($bSearchList && in_array(-1, $lUserIDs))) ? 'SELECTED' : '').'>(Visible to all users)</option>';
      $strSelect = '';
      foreach ($this->clsUsers->userRec as $clsUser){
         $lUserID = $clsUser->us_lKeyID;
         if ($bSearchList){
            $strSelect = in_array($lUserID, $lUserIDs) ? 'SELECTED' : '';
         }
         $strDDL .= '<option value="'.$lUserID.'" '.$strSelect.'>'
                    .$clsUser->strSafeName.'</option>'."\n";
      }
      $strDDL .= '</select>';
      return($strDDL);
   }

   function addEditUpdate($enumRemType, $lFID, $lRemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('reminders/mreminders', 'clsRem');
      $this->load->model('util/mrecurring', 'clsRecurring');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/context');

      $this->clsRem->lRID = $lRemID = (integer)$lRemID;

      $this->clsRem->reminders = array();
      $this->clsRem->reminders[0] = new stdClass;
      $this->clsRem->reminders[0]->lKeyID = $lRemID;
      $this->clsRem->reminders[0]->enumSource = $enumRemType;
      $this->clsRem->reminders[0]->lForeignID = $lFID = (integer)$lFID;
      $this->clsRem->reminders[0]->strTitle = trim($_POST['txtSubject']);
      $this->clsRem->reminders[0]->strReminderNote = trim($_POST['txtNote']);

      $bNew = $lRemID <= 0;

      $lNumUsers = 0; $lUsers = array();
      foreach ($_REQUEST['ddlUsers'] as $strUser){
         $lUsers[$lNumUsers] = (integer)$strUser;
         ++$lNumUsers;
      }

      $this->clsRecurring->lDaysExtend = 7;
      $this->clsRecurring->loadFormRecur();
      $this->clsRecurring->generateDateList();
      if ($bNew){
         $lRemID = $this->clsRem->lInsertReminderBase();
         foreach ($this->clsRecurring->dateList as $dteRem){
            $lRemDateID = $this->clsRem->lInsertReminderDate($lRemID, $dteRem->uts, $dteRem->utsEnd);
            foreach ($lUsers as $lUser){
               $this->clsRem->lInsertReminderUser($lRemDateID, $lUser);
            }
         }
      }else {
         $this->clsRem->updateReminderBase();
      }
      $this->session->set_flashdata('msg', 'Reminder '.($bNew ? 'added!' : 'updated!'));
      redirectViaContextType($enumRemType, $lFID);
   }

   function remove($enumRemType, $lFID, $lRemID){
      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, $enumRemType, $lFID, false);
      verifyID($this, $lRemID, 'reminder ID');

      $this->load->model('reminders/mreminders', 'clsRem');
      $this->clsRem->removeReminder($lRemID);
      $this->session->set_flashdata('msg', 'Reminder was removed');
      redirect_ReminderViaObject($lFID, $enumRemType);
   }

   function addReminderFollup($enumRemType, $lFID, $lRemID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lFID   = (integer)$lFID;
      $lRemID = (integer)$lRemID;
      $this->load->model('reminders/mreminders', 'clsRem');

      $this->clsRem->addFollowup($lRemID, trim($_POST['txtFollowUp']));
      redirect_ReminderViaObject($lFID, $enumRemType, 'Follow-up note was added');
   }

}