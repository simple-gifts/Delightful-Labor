<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class reminder_record extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function viewViaObject($enumRemType, $lFID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS, $gdteNow, $glUserID, $genumDateFormat;

      if (!bTestForURLHack('notVolunteer')) return;
      $this->load->helper('dl_util/verify_id');
      verifyIDsViaType($this, $enumRemType, $lFID, false);

      $displayData = array();
      $displayData['enumRemType'] = $enumRemType;
      $displayData['lFID'] = $lFID = (integer)$lFID;

         //--------------------------
         // models & helpers
         //--------------------------
      $this->load->model('admin/madmin_aco', 'clsACO');
      $this->load->model('admin/muser_accts');
      $this->load->model('clients/mclients');
      $this->load->model('people/mpeople');
      $this->load->model('donations/mdonations');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
      $this->load->model('sponsorship/msponsorship', 'clsSpon');
      $this->load->model('biz/mbiz', 'clsBiz');

//      $this->load->helper('dl_util/email_web');
      $this->load->helper('dl_util/web_layout');
      $this->load->helper('dl_util/time_date');
      $this->load->helper('dl_util/page_title');
      $this->load->helper('dl_util/reminder');
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);
      $clsRpt = new generic_rpt($params);
      $this->load->model('reminders/mreminders', 'clsRem');

      $this->clsRem->enumSort    = 'currentFirst';
      configRemRecViewViaType($enumRemType, $lFID, $displayData);

      $bUserReminder = $enumRemType==CENUM_CONTEXT_USER;
      if ($bUserReminder){
         $this->clsRem->enumLookupType = 'CurrentViaEnumRemTypeUserID';
      }else {
         $this->clsRem->enumLookupType = 'ViaEnumRemTypeFID';
      }

      $this->clsRem->loadEnumTypeList($enumRemType);

      $strRemRpt = '';

      $lTotRems = 0;
      $this->clsRem->lFID = $lFID;
      foreach ($this->clsRem->enumRemTypeList as $enumRT){
         $this->clsRem->enumRemType  = $enumRT;
         $strRT = strtoupper(substr($enumRT, 0, 1)).substr($enumRT, 1);
         $this->clsRem->loadReminders();
         $lTotRems += $this->clsRem->lNumReminders;
         if ($this->clsRem->lNumReminders > 0){

            $strRemRpt .= $clsRpt->openReport(550);

            if ($bUserReminder){
               $strRemRpt .= $clsRpt->openRow(false);
               $strRemRpt .= $clsRpt->writeTitle($strRT.' Reminders', '', '', 8);
               $strRemRpt .= $clsRpt->closeRow();
            }

            $strRemRpt .= $clsRpt->openRow(false);
            $strRemRpt .= $clsRpt->writeLabel('Reminder Dates', '160pt');
            $strRemRpt .= $clsRpt->writeLabel('Reminder', '');
//            $strRemRpt .= $clsRpt->writeLabel('&nbsp;', '');
            $strRemRpt .= $clsRpt->closeRow();

            foreach ($this->clsRem->reminders as $clsSingleRem){
               if ($this->clsRem->bOKToView($clsSingleRem, $glUserID)){
                  $lRemID = $clsSingleRem->lKeyID;

                  $strRemRpt .= $clsRpt->openRow(true);
                  $strRemLogLabel = '';
                  $lNumPast    = $this->clsRem->lNumPast  ($clsSingleRem, $gdteNow, $lIdxLastPast);
                  $lNumFuture  = $this->clsRem->lNumFuture($clsSingleRem, $gdteNow, $lIdxFirstFuture);
                  $lCurDateIdx = $this->clsRem->lCurrentRemIdx($clsSingleRem, $gdteNow);

                     //-------------------------------------------------------
                     // it's the little things that make a house a home....
                     //-------------------------------------------------------
                  if ($lNumPast > 0){
                     $strRemLogLabel .= ' / '.$lNumPast.' past reminder'.($lNumPast==1 ? '' : 's');
                  }
                  if ($lNumFuture > 0){
                     $strRemLogLabel .= ' / '.$lNumFuture.' future reminder'.($lNumFuture==1 ? '' : 's');
                  }
                  if ($strRemLogLabel != ''){
                     $strRemLogLabel = '<br><small>'.substr($strRemLogLabel, 3).'</small>';
                  }
                  if ($bUserReminder){
                     $strRemLink = '<br><i>'.$this->clsRem->strHTMLOneLineLink($clsSingleRem).'</i>';
                  }else {
                     $strRemLink = '';
                  }

                     //---------------------------------------------------------------------------
                     // if reminder is not current, indicate next reminder; if no next reminder,
                     // indicate most recent reminder
                     //---------------------------------------------------------------------------
                  $bCurrent = !is_null($lCurDateIdx);
                  if (!$bCurrent){
                     $strRemStyle = 'color: #999999;';
                     if ($lNumFuture > 0){
                        $strRemDate = '<i>next reminder: '
                            .date($genumDateFormat,
                                  $clsSingleRem->dates[$lIdxFirstFuture]->dteDisplayStart)
                            .'</i>';
                     }else {
                        $strRemDate = '<i>reminded on: '
                            .date($genumDateFormat,
                                  $clsSingleRem->dates[$lIdxLastPast]->dteDisplayStart)
                            .'</i>';
                     }
                  }else {
                     $strRemStyle = '';
                     $strRemDate =
                          date($genumDateFormat,
                               $clsSingleRem->dates[$lCurDateIdx]->dteDisplayStart);
                  }

                     //--------------------------
                     // reminder title/note
                     //--------------------------
                  $strReminder = '<b>'.htmlspecialchars($clsSingleRem->strTitle).'</b>'.$strRemLink;
                  if ($clsSingleRem->strTitle != ''){
                     $strReminder .= '<br>'.nl2br(htmlspecialchars($clsSingleRem->strReminderNote));
                  }

                     //--------------------------
                     // follow-up notes
                     //--------------------------
                  if ($clsSingleRem->lNumFollowUps > 0){
                     $strReminder .= '<small><br><br><b>Follow-up notes:</b><br>';
                     foreach ($clsSingleRem->followUpNotes as $clsFollow){
                        $strReminder .=
                           '<i>'.date($genumDateFormat, $clsFollow->dteOfNote)
                           .' by '.$clsFollow->strSafeName.'</i><br>'
                           .nl2br(htmlspecialchars($clsFollow->strFollowUpNote)).'<br><br>';
                     }
                     $strReminder .= '</small>';
                  }

                     //--------------------------
                     // follow-up note form
                     //--------------------------
                  $clsNav = new stdClass;
                  $clsNav->strKey02     = 'RID';
                  $clsNav->lKey02       = $lRemID;

                  $attributes = array('name' => 'frmFollowUp'.$lRemID);
                  $strReminder .=
                     form_open('reminders/rem_add_edit/addReminderFollup/'.$enumRemType.'/'.$lFID.'/'.$lRemID, $attributes)
                    .'<br>
                      <i>add follow-up note:</i>
                        <input type="submit" name="cmdSubmit" value="Save"
                             style="font-size: 7pt; height: 13pt; vertical-align: center;"
                             onclick="this.disabled=1; this.form.submit();"
                             class="btn"
                                onmouseover="this.className=\'btn btnhov\'"
                                onmouseout="this.className=\'btn\'"><br>
                        <textarea name="txtFollowUp" rows="1" cols="45"></textarea><br>
                        </form>';

                  $strRemRpt .= $clsRpt->writeCell($strRemDate.$strRemLogLabel
                                    .'<br><br>'.strLinkRem_Reminder($lRemID, $lFID, $enumRemType,
                                      'Remove reminder', true, true));

                  $strRemRpt .= $clsRpt->writeCell($strReminder, '', $strRemStyle);
/*
               $strRemRpt .= $clsRpt->writeCell(strLinkRem_Reminder($lRemID, $lFID, $enumRemType,
                                   'Remove reminder', true, true), '', $strRemStyle);
*/
                  $strRemRpt .= $clsRpt->closeRow();
               }
            }
            $strRemRpt .= $clsRpt->closeReport('<br>');
         }
      }
      if ($lTotRems == 0){
         $strRemRpt = '<br><i>There are no reminders for this user.</i><br><br>';
      }

      $displayData['strRemRpt'] = $strRemRpt;

         //--------------------------
         // breadcrumbs
         //--------------------------
      $displayData['title']          = CS_PROGNAME.' | People';
      $displayData['nav']            = $this->mnav_brain_jar->navData();

      $displayData['mainTemplate']   = 'reminders/view_reminder_record_view';
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   function viewViaUser($lUserID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      if (!bTestForURLHack('notVolunteer')) return;

      $displayData['lUserID'] = $lUserID = (integer)$lUserID;
      $this->viewViaObject(CENUM_CONTEXT_USER, $glUserID);
   }


}