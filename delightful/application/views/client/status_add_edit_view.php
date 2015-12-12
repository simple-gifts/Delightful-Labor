<?php
   global $gbDateFormatUS, $genumDateFormat, $gstrFormatDatePicker;

   $clsForm = new generic_form;
   $clsForm->strLabelClass = 'enpRptLabel';
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';

   $lEntryID = $clsEntry->lKeyID;

   $attributes = array('name' => 'frmClientStat');
   echoT(form_open('clients/client_rec_stat/addEditStatusEntry/'.$lClientID.'/'.$lStatusID, $attributes));


   echoT('<table class="enpView">');
   $lMatchID = $bNew ? -1 : $clsEntry->lStatusID;
   echoT($clsForm->strTitleRow(($bNew ? 'Add new ' : 'Update ').'status for '
                 .$client->strSafeName, 2, ''));

      //---------------------------
      // status category and entry
      //---------------------------
   echoT($clsForm->strLabelRow('Status Category', $client->strStatusCatName, 1));
   $clsForm->strExtraFieldText = form_error('ddlStatus');
   echoT($clsForm->strGenericDDLEntry('Status', 'ddlStatus', true, $statDDL));

      //---------------------------
      // Packet status?
      //---------------------------
   $clsForm->strExtraFieldText = ' (if checked, this status will be included in packet export)';
   echoT($clsForm->strGenericCheckEntry('Packet Status?', 'chkPacket', 'TRUE', false, $bPacketStatus));
   
   
      //--------------------
      // status date
      //--------------------
   echoT(strDatePicker('datepicker1', false));
   $clsForm->strExtraFieldText = form_error('txtDate');
   echoT($clsForm->strGenericDatePicker(
                      'Date',   'txtDate',       true,
                      $txtDate, 'frmClientStat', 'datepicker1'));

   echoT($clsForm->strNotesEntry('Notes', 'txtNotes', false,
               $txtNotes, 5, 46));
   echoT($clsForm->strSubmitEntry('Submit', 1, 'cmdSubmit', 'text-align: left;'));
   echoT('</table></form>');
