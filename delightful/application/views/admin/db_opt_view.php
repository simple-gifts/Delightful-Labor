<?php
   global $gbAdmin;


   if (!$gbAdmin){
      echoT('You must be an Administrator to use this feature.');
   }else {   
      openBlock('Database Optimization', '');
      
      $attributes =
          array(
               'name'     => 'frmBackup',
               'id'       => 'backupDB'
               );
      echoT(form_open('admin/db_zutil/optRun',  $attributes));
      
      echoT('<br><b><i>It is recommended that you back up your database before running this utility</b></i><br><br>');
      
      $clsForm = new generic_form;
      $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
      $clsForm->strTitleClass = 'enpViewTitle';
      $clsForm->strEntryClass = 'enpView';
      $clsForm->strStyleExtraLabel = 'width: 120pt;';
      $clsForm->bValueEscapeHTML = false;
      echoT('<table width="800" border="0">');
      
      
      $clsForm->strStyleExtraLabel = 'text-align: left;';
      echoT($clsForm->strSubmitEntry('Run Optimization ', 2, 'cmdSubmit', 'text-align: left;'));
      
      echoT('</table>'.form_close('<br>'));
      
      
   }