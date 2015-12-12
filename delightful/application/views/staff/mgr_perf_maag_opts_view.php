<?php

      $attributes =
          array(
               'name'     => 'frmMAAG',
               'id'       => 'maag'
               );

      echoT(form_open('staff/mgr_perf_rpt/maagOpts',  $attributes));

      openBlock('Status Reports By Month', '');

      $clsForm = new generic_form;
      $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
      $clsForm->strTitleClass = 'enpViewTitle';
      $clsForm->strEntryClass = 'enpView';
      $clsForm->bValueEscapeHTML = false;
      echoT('<table width="600" border="0">');

      $clsForm->strStyleExtraLabel = 'text-align: right; width: 90pt; padding-top: 8px;';

         //------------------------
         // Reporting month
         //------------------------
      $clsForm->strExtraFieldText = form_error('txtMonth');
      echoT($clsForm->strLabelRow('Month',
                         '<input type="text" value="'.$txtMonth.'" name="txtMonth" size="8" id="month1">', 1, ''));

         
         
      $clsForm->strStyleExtraLabel = 'text-align: left;';
      echoT($clsForm->strSubmitEntry('View Status Reports for Month', 2, 'cmdSubmit', ''));
      echoT('</table>'.form_close('<br>'));
      echoT('<script type="text/javascript">frmCPAttend.addEditEntry.focus();</script>');

      closeblock();

