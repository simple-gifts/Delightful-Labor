<?php
class events_add_edit extends CI_Controller {

	function __construct(){
		parent::__construct();
		session_start();
		setGlobals($this);
	}

	function addEditEvent($lEventID){
	//---------------------------------------------------------------------
	//
	//---------------------------------------------------------------------
		global $gdteNow, $gbDateFormatUS;

		if (!bTestForURLHack('editPeopleBizVol')) return;

		$this->load->helper('dl_util/verify_id');
		if ($lEventID.'' != '0') verifyID($this, $lEventID, 'event ID');

		$displayData = array();
		$displayData['formData'] = new stdClass;
		$displayData['lEventID'] = $lEventID = (integer)$lEventID;
		$displayData['bNew'] = $bNew = $lEventID <= 0;
		$displayData['js']	= '';


/*
			//-------------------------------
			// javascript verification
			//-------------------------------
		$this->load->library('js_build/js_verify');
		$this->load->helper('js/verify_vol_event');
		$this->load->helper('js/verify_recurring');
		$this->load->helper('dl_util/page_title');
		$this->js_verify = new js_verify;
		$this->js_verify->clearEmbedOpts();
		$this->js_verify->bShow_isValidDate		  =
		$this->js_verify->bShow_bVerifyString	  =
		$this->js_verify->bShow_bVerifyRadio	  =
		$this->js_verify->bShow_strRadioSelValue =
		$this->js_verify->bShow_trim				  = true;
		$displayData['js'] .= $this->js_verify->loadJavaVerify();
		$displayData['js'] .= volEventVerify();
		$displayData['js'] .= verifyRecurring();
*/
			//-------------------------
			// models & helpers
			//-------------------------
		$this->load->helper('dl_util/time_date');	 // for date verification
		$this->load->library('generic_form');
		$clsForm = new generic_form;

			// note - this form uses javascript verification due to the recurring options
			// (may be replaced by CI verification in the future)
		$this->load->model('vols/mvol_events', 'clsVolEvents');
		$this->load->model('vols/mvol_event_dates', 'clsVolEventDates');
		$this->load->model('util/mrecurring', 'clsRecurring');
		$this->load->helper('dl_util/recurring');
		$this->load->helper('dl_util/web_layout');
		$this->load->library('js_build/java_joe_radio');
		$clsJJR = new java_joe_radio;

			//-------------------------
			// validation rules
			//-------------------------
		$this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtEvent',	  'Event Name',		'trim|required');
		$this->form_validation->set_rules('txtNote',		  'Decscription',		'trim');
		$this->form_validation->set_rules('txtLocation',  'Location',			'trim');
		$this->form_validation->set_rules('txtContact',	  'Contact',			'trim');
		$this->form_validation->set_rules('txtPhone',	  'Phone',				'trim');
		$this->form_validation->set_rules('txtEmail',	  'EMail',				'trim|valid_email');
		$this->form_validation->set_rules('txtWebLink',	  'Web Site',			'trim');
		if ($bNew){
			$this->form_validation->set_rules('txtStart',	  'Starting Date',	'trim|required|callback_verifyRecurring');
		}

		$this->clsVolEvents->loadEventsViaEID($lEventID);
		$event = $this->clsVolEvents->events[0];

		if ($this->form_validation->run() == FALSE){
			$displayData['formData'] = new stdClass;
			$this->load->library('generic_form');
			$displayData['clsForm'] = &$clsForm;
			$displayData['strForm'] = $strForm = 'frmEditVolEvent';

				// first time displayed, no user data entry errors
			if (validation_errors()==''){


				$displayData['formData']->strEventName		 = htmlspecialchars($event->strEventName);
				$displayData['formData']->strDescription	 = htmlspecialchars($event->strDescription);
				$displayData['formData']->strLocation		 = htmlspecialchars($event->strLocation);
				$displayData['formData']->strContact		 = htmlspecialchars($event->strContact);
				$displayData['formData']->strPhone			 = htmlspecialchars($event->strPhone);
				$displayData['formData']->strEmail			 = htmlspecialchars($event->strEmail);
				$displayData['formData']->strWebSite		 = htmlspecialchars($event->strWebSite);


				if ($bNew){
					$strDate = strNumericDateViaUTS($gdteNow, $gbDateFormatUS);
					$selects = null;
					$displayData['recurringOpts'] =
							  strRecurringOpts($displayData,			$clsForm,	 $selects,
													$strForm,				$clsJJR,		 $strDate,
													$this->clsRecurring, 'txtStart', 'Event on',
													'Event Schedule',	  'Event on');
				}
			}else {
				setOnFormError($displayData);
				$strDate = set_value('txtStart');;
				$displayData['formData']->strEventName		 = set_value('txtEvent');
				$displayData['formData']->strDescription	 = set_value('txtNote');
				$displayData['formData']->strLocation		 = set_value('txtLocation');
				$displayData['formData']->strContact		 = set_value('txtContact');
				$displayData['formData']->strPhone			 = set_value('txtPhone');
				$displayData['formData']->strEmail			 = set_value('txtEmail');
				$displayData['formData']->strWebSite		 = set_value('txtWebLink');

				if ($bNew){
					$selects = new stdClass;
					$selects->lNumRecur = (int)$_POST['ddlRecur'];
					$selects->rdoRecur	= @$_POST['rdoRecur'];
					$selects->rdoFixedLoose = @$_POST['rdoFixedLoose'];

					$selects->chkWkSun	= @$_POST['chkWkSun']=='TRUE';
					$selects->chkWkMon	= @$_POST['chkWkMon']=='TRUE';
					$selects->chkWkTue	= @$_POST['chkWkTue']=='TRUE';
					$selects->chkWkWed	= @$_POST['chkWkWed']=='TRUE';
					$selects->chkWkThu	= @$_POST['chkWkThu']=='TRUE';
					$selects->chkWkFri	= @$_POST['chkWkFri']=='TRUE';
					$selects->chkWkSat	= @$_POST['chkWkSat']=='TRUE';
					$selects->chkWeekDaysOnly = @$_POST['chkWeekDaysOnly']=='TRUE';
					$selects->chkFixed = array();
					for ($idx=1; $idx<=31; ++$idx) $selects->chkFixed[$idx] = @$_POST['chkFixed'.$idx]=='on';

					$selects->chkRel_1st	 = @$_POST['chkRel_1st'] =='TRUE';
					$selects->chkRel_2nd	 = @$_POST['chkRel_2nd'] =='TRUE';
					$selects->chkRel_3rd	 = @$_POST['chkRel_3rd'] =='TRUE';
					$selects->chkRel_4th	 = @$_POST['chkRel_4th'] =='TRUE';
					$selects->chkRel_5th	 = @$_POST['chkRel_5th'] =='TRUE';
					$selects->chkRel_last = @$_POST['chkRel_last']=='TRUE';

					$selects->chkRelSun	 = @$_POST['chkRelSun'] == 'on';
					$selects->chkRelMon	 = @$_POST['chkRelMon'] == 'on';
					$selects->chkRelTue	 = @$_POST['chkRelTue'] == 'on';
					$selects->chkRelWed	 = @$_POST['chkRelWed'] == 'on';
					$selects->chkRelThu	 = @$_POST['chkRelThu'] == 'on';
					$selects->chkRelFri	 = @$_POST['chkRelFri'] == 'on';
					$selects->chkRelSat	 = @$_POST['chkRelSat'] == 'on';

					$displayData['recurringOpts'] =
							  strRecurringOpts($displayData,			 $clsForm,	  $selects,
													 $strForm,				 $clsJJR,	  $strDate,
													 $this->clsRecurring, 'txtStart', 'Event on',
													 'Event Schedule',	'Event on');
				}
			}

				//--------------------------
				// breadcrumbs
				//--------------------------
			$displayData['pageTitle']		 = anchor('main/menu/vols', 'Volunteers', 'class="breadcrumb"')
											  .' | '.anchor('volunteers/events_schedule/viewEventsList', 'Event List', 'class="breadcrumb"');
			if (!$bNew) $displayData['pageTitle'] .=
												' | '.anchor('volunteers/events_record/viewEvent/'.$lEventID, 'Event', 'class="breadcrumb"');
			$displayData['pageTitle'] .=
												' | '.($bNew ? 'Add New' : 'Edit').'  Event';

			$displayData['title']			 = CS_PROGNAME.' | Vol. Events';
			$displayData['nav']				 = $this->mnav_brain_jar->navData();
			$displayData['mainTemplate']	 = 'vols/event_add_edit_view';
			$this->load->vars($displayData);
			$this->load->view('template');
		}else {
         $this->eventUpdate($lEventID);
		}
	}

   function verifyRecurring($strStart){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if(!bValidVerifyDate($strStart)){
         $this->form_validation->set_message('verifyRecurring', 'Please enter a valid event start date.');
         return(false);
      }

      $lNumRecur = (int)$_POST['ddlRecur'];
      if ($lNumRecur == 1) return(true);

      $strRecur = trim($_POST['rdoRecur']);
      $bFailed = false; $strErr = '';
      switch ($strRecur){
         case 'daily':
            break;
         case 'weekly':
            if (!
                 ((@$_POST['chkWkSun'] == 'TRUE' ) ||
                  (@$_POST['chkWkMon'] == 'TRUE' ) ||
                  (@$_POST['chkWkTue'] == 'TRUE' ) ||
                  (@$_POST['chkWkWed'] == 'TRUE' ) ||
                  (@$_POST['chkWkThu'] == 'TRUE' ) ||
                  (@$_POST['chkWkFri'] == 'TRUE' ) ||
                  (@$_POST['chkWkSat'] == 'TRUE' ))){
               $bFailed = true;
               $strErr = 'For weekly recurrence, please select the recurring day(s) of the week.';
            }
            break;
         case 'monthly':
            $strFL = trim(@$_POST['rdoFixedLoose']);
            switch ($strFL){
               case 'FL':
                  $bFound = false; $idx = 1;
                  while (!$bFound && $idx<=31){
                     $bFound = @$_POST['chkFixed'.$idx]=='on';
                     ++$idx;
                  }
                  if (!$bFound){
                     $bFailed = true;
                     $strErr = 'For fixed monthly recurrence, please select one or more dates.';
                  }
                  break;
               case 'RM':
                  $bFound1 =
                     @$_POST['chkRel_1st'] =='TRUE' ||
                     @$_POST['chkRel_2nd'] =='TRUE' ||
                     @$_POST['chkRel_3rd'] =='TRUE' ||
                     @$_POST['chkRel_4th'] =='TRUE' ||
                     @$_POST['chkRel_5th'] =='TRUE' ||
                     @$_POST['chkRel_last']=='TRUE';

                  $bFound2 =
                     @$_POST['chkRelSun'] == 'on' ||
                     @$_POST['chkRelMon'] == 'on' ||
                     @$_POST['chkRelTue'] == 'on' ||
                     @$_POST['chkRelWed'] == 'on' ||
                     @$_POST['chkRelThu'] == 'on' ||
                     @$_POST['chkRelFri'] == 'on' ||
                     @$_POST['chkRelSat'] == 'on';
                  if (!($bFound1 && $bFound2)){
                     $bFailed = true;
                     $strErr = 'For relative monthly recurrence, please select one or more entries from each row.';
                  }
                  break;
               default:
                  $bFailed = true;
                  $strErr = 'For monthly recurrence, please select either "Fixed monthly dates" or "Relative monthly dates".';
                  break;
            }
            break;
      }
      if ($bFailed){
         $this->form_validation->set_message('verifyRecurring', $strErr);
         return(false);
      }else {
         return(true);
      }
   }

	function eventUpdate($lEventID){
	//---------------------------------------------------------------------
	//
	//---------------------------------------------------------------------
		$this->load->helper('dl_util/verify_id');
		if ($lEventID.'' != '0') verifyID($this, $lEventID, 'event ID');

		$lEventID = (integer)$lEventID;

		$this->load->model('vols/mvol_events',		 'clsVolEvents');
		$this->load->model('vols/mvol_event_dates', 'clsVolEventDates');
		$this->load->model('util/mrecurring',		'clsRecurring');
		$this->load->helper('dl_util/time_date');

		$bNew = $lEventID <= 0;

		$this->clsVolEvents->loadEventsViaEID($lEventID);
		$clsE = &$this->clsVolEvents->events[0];

		$clsE->strEventName	 = trim($_POST['txtEvent']);
		$clsE->strDescription = trim($_POST['txtNote']);
		$clsE->strLocation	 = trim($_POST['txtLocation']);
		$clsE->strContact		 = trim($_POST['txtContact']);
		$clsE->strPhone		 = trim($_POST['txtPhone']);
		$clsE->strEmail		 = trim($_POST['txtEmail']);
		$clsE->strWebSite		 = trim($_POST['txtWebLink']);

		if ($bNew){
			$strMsg	 = 'Event added!';
			$lEventID = $this->clsVolEvents->lAddNewEvent();

			$this->clsRecurring->lDaysExtend = 7;
			$this->clsRecurring->loadFormRecur();
			$this->clsRecurring->generateDateList();

			foreach ($this->clsRecurring->dateList as $dteRem){
				$lEventDateID = $this->clsVolEventDates->lInsertEventDate($lEventID, $dteRem->uts);
			}
		}else {
			$strMsg	 = 'Event updated!';
			$this->clsVolEvents->updateEvent();
		}
		$this->session->set_flashdata('msg', $strMsg);
		redirect_VolEvent($lEventID);
	}

	function remove($lEventID){
		$this->load->helper('dl_util/verify_id');
		verifyID($this, $lEventID, 'event ID');

		$lEventID = (integer)$lEventID;

		$this->load->model('vols/mvol_events', 'clsVolEvents');
		$this->clsVolEvents->deleteEvent($lEventID);

		$this->session->set_flashdata('msg', 'The specified event was removed.');
		redirect_VolEventList();
	}





}
