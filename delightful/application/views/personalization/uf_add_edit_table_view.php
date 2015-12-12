<?php

   $attributes = array('name' => 'frmLoc', 'id' => 'frmAddEdit');
   echoT('<br>'.form_open('admin/uf_tables_add_edit/addEditTable/'.$enumTType.'/'.$lTableID, $attributes));
   $clsGF = new generic_form();
   $clsGF->strLabelClass = $clsGF->strLabelRowLabelClass = $clsGF->strLabelClassRequired = 'enpViewLabel';
   $clsGF->strTitleClass = 'enpViewTitle';
   $clsGF->strEntryClass = 'enpView';
   $clsGF->bValueEscapeHTML = false;
   $clsGF->bValueEscapeHTML = false;

   $strBlockLabel = ($bNew ? 'New ' : 'Update a ').'Personalized Table: <b>'.$strTTypeLabel.'</b>';

   openBlock($strBlockLabel, '');
   echoT('<table class="enpView">');



      // table name
//   $clsGF->strLabelClass = 'enpRptLabel';
//   echoT($clsGF->strTitleRow( (($bNew ? 'New ' : 'Update a ').'Personalized Table: <b>'.$strTTypeLabel.'</b>'), 2, ''));
   $clsGF->strExtraFieldText = form_error('txtTableName');
   $clsGF->strID = 'addEditEntry';
   echoT($clsGF->strGenericTextEntry('Table name', 'txtTableName',   true,  $strTableName,   40, 40));

      // multi-entry
   if ($bNew){
      $strMulti = '<input type="checkbox" name="chkMultiEntry" value="true" '.($bMultiEntry ? 'checked' : '').'> '
         .'Allow multiple table entries per '.$strTTypeLabel.'.';
   }else {
      if ($bMultiEntry){
         $strMulti = 'This table allows multiple table entries per '.$strTTypeLabel.'.';
      }else {
         $strMulti = 'This table allows a single table entry per '.$strTTypeLabel.'.';
      }
   }
   echoT($clsGF->strLabelRow('Multi-entry', $strMulti, 1));   
   
      // read only
   if ($bNew || $bMultiEntry){
      $strReadOnly = '<input type="checkbox" name="chkReadOnly" value="true" '.($bReadOnly ? 'checked' : '').'> '
         .'For multi-entry tables, do not allow a record to be edited.';
      echoT($clsGF->strLabelRow('Read Only', $strReadOnly, 1));
   }   

      // description
   echoT($clsGF->strNotesEntry('Description',      'txtDescription', false, $strDescription,  3, 45));


      // Collapsible heading groups
   $clsGF->strExtraFieldText =
                      'Check for collapsable headings<br>
                         <table><tr><td style="width: 300pt; font-style:italic">
                         For tables with many fields, you can group the fields by using
                         headings. This option allows the fields under a heading to
                         optionally be collapsed.
                         </td></tr></table>';
   echoT($clsGF->strGenericCheckEntry('Collapsible heading groups?', 'chkCollapsible', 'true', false, $bCollapsible));

      // Alert on No Entry
   $clsGF->strExtraFieldText =
                      'Check to generate alert<br><table><tr><td style="width: 300pt; font-style:italic">
                         Create an alert to the user if this table doesn\'t have
                         an entry. The alert is displayed when viewing the
                         <b>'.$strTTypeLabel.'</b> record.
                         </td></tr></table>';
   echoT($clsGF->strGenericCheckEntry('Create alert for no data entry?', 'chkAlertNoDataEntry', 'true', false, $bAlertNoDataEntry));

      // Alert text
      // will use strip_tags ( string $str [, string $allowable_tags ] ) when displayed
      // Example: echo strip_tags($text, '<b><i><br>');
   $clsGF->strExtraFieldText =
                      '<br>You can use the following HTML tags:<br>
                      <ul style="margin-top: 0pt; margin-bottom: 0pt; font-family: courier" >
                         <li>&lt;b&gt; - bold</li>
                         <li>&lt;i&gt; - italic</li>
                         <li>&lt;br&gt; - line break</li>
                         <li>&lt;font&gt; - font</li>
                      </ul>';

   echoT($clsGF->strNotesEntry('Alert Text',      'txtAlert', false, $strAlert,  3, 45));



   if (!$bNew) {
      if ($bCurrentlyHidden){
         $clsGF->strExtraFieldText =
                      '<table><tr><td style="width: 300pt; font-style:italic">
                         This table is currently hidden. Unchecking this box will restore the table.
                         </td></tr></table>';
      }else {
         $clsGF->strExtraFieldText =
                         'Check to hide<br><table><tr><td style="width: 300pt; font-style:italic">
                          Checking this box will hide this table,
                          and the associated data. You can restore
                          the table at a later time.
                          </td></tr></table>';
      }
      echoT($clsGF->strGenericCheckEntry('Hide table', 'chkHide', 'true', false, $bHidden));

      $clsGF->strExtraFieldText =
             'Check to remove<br><table><tr><td style="width: 300pt; font-style:italic">
               Checking this box will remove this table
               and the associated data, from your database.
               <font color="red"><b>This action can not be undone!</b></font>
               </td></tr></table>';
      echoT($clsGF->strGenericCheckEntry('Remove table', 'chkRetire', 'YES', false, false));
   }

   echoT('</table>');
   closeBlock();


      /*----------------------
         Validation file
      ----------------------*/
   openBlock('Validation', '');
   echoT('<table class="enpView">');
   $strNote =
          '<i>You can optionally provide your own software to manage complex validation.<br>'
         .'The validation software is in the form of a codeIgniter helper file. Save your file<br>'
         .'in directory </i><font style="font-family: courier;">
         application/helpers/custom_verification</font><i>
         and your <br> file name must end with
         </i><font style="font-family: courier;">"_helper.php"</font><i>.
         Please see the user\'s guide for more details.<br>';
   echoT($clsGF->strLabelRowOneCol($strNote, 2));

   $clsGF->strExtraFieldText = form_error('txtVerificationModule');
   echoT($clsGF->strGenericTextEntry('Validation File', 'txtVerificationModule', false, $txtVerificationModule, 60, 255));

   $clsGF->strExtraFieldText = '<br>'
                  .'<i>If you specify a validation file, this is where you specify the name of your function <br>'
                  .'to call. Your routine returns false if the form data is not valid.<br>'
                  .form_error('txtVModEntryPoint');
   echoT($clsGF->strGenericTextEntry('Entry Point', 'txtVModEntryPoint', false, $txtVModEntryPoint, 60, 255));

   echoT('</table>');
   closeBlock();


   echoT($clsGF->strSubmitEntry('Submit', 1, 'cmdSubmit', ' width: 120pt; '));
   echoT(form_close('<br><br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

?>
