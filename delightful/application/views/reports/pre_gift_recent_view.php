<?php


   $attributes =
       array(
            'name'     => 'frmGiftRecentRpt',
            'id'       => 'giftRecentRpt'
            );

   echoT(form_open('/reports/pre_gifts/recentRun',  $attributes));

   openBlock('Recent Donations', '');

   $clsForm = new generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->strStyleExtraLabel = 'width: 100pt;';
   $clsForm->bValueEscapeHTML = false;
   echoT('<table width="900" border="0">');
   
      //--------------------------------
      // Time Frame
      //--------------------------------
   $clsForm->strStyleExtraLabel = 'width: 100pt; padding-top: 6px;';
   echoT($clsForm->strLabelRow('Donations from',
                         '<select name="ddlPast">
                            <option value="0"  >All</option>
                            <option value="1"  >Today</option>
                            <option value="3"  >Past 3 days</option>
                            <option value="7"  selected>Past 7 days</option>
                            <option value="30" >Past 30 days</option>
                            <option value="60" >Past 60 days</option>
                            <option value="90" >Past 90 days</option>
                            <option value="120">Past 120 days</option>
                            <option value="365">Past 365 days</option>
                          </select>'
                        , 1));
   

      //--------------------------------
      // Sort by
      //--------------------------------
   echoT($clsForm->strLabelRow('Sort by',
                         '<input type="radio" name="rdoSort" value="date"  checked >Date&nbsp;
                          <input type="radio" name="rdoSort" value="amnt" >Amount&nbsp
                          <input type="radio" name="rdoSort" value="donor">Donor&nbsp;'
                        , 1));

   $clsForm->strStyleExtraLabel .= 'text-align: left;';
   echoT($clsForm->strSubmitEntry('Run Report', 2, 'cmdSubmit', 'text-align: left;'));
   echoT('</table>'.form_close('<br>'));
   echoT('<script type="text/javascript">skillsRpt.addEditEntry.focus();</script>');
   
   closeblock();
   
