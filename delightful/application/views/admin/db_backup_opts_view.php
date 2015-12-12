<?php
   global $gbAdmin;


   if (!$gbAdmin){
      echoT('You must be an Administrator to use this feature.');
   }else {   
      openBlock('Database backup', '');
      
      $attributes =
          array(
               'name'     => 'frmBackup',
               'id'       => 'backupDB'
               );
      echoT(form_open('admin/db_zutil/backupRun',  $attributes));
      
      $clsForm = new generic_form;
      $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
      $clsForm->strTitleClass = 'enpViewTitle';
      $clsForm->strEntryClass = 'enpView';
      $clsForm->strStyleExtraLabel = 'width: 120pt;';
      $clsForm->bValueEscapeHTML = false;
      echoT('<table width="800" border="0">');
      
      echoT($clsForm->strLabelRow('Backup as', '
                                  <input type="radio" name="rdoType" value="zip" checked>zip&nbsp;&nbsp;
                                  <input type="radio" name="rdoType" value="gzip" >gzip&nbsp;&nbsp;
                                  <input type="radio" name="rdoType" value="txt"  >text&nbsp;&nbsp;
                                   ',  1));
      echoT($clsForm->strGenericCheckEntry('Include "DROP TABLE"', 'chkDrop', 'true', false, true));
      
      $clsForm->strStyleExtraLabel = 'text-align: left;';
      echoT($clsForm->strSubmitEntry('Run Backup', 2, 'cmdSubmit', 'text-align: left;'));
      
      echoT('</table>'.form_close('<br>'));
      
      
      closeblock();
   }   
