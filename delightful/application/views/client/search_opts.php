<?php
   $clsForm = new Generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   $strButton =
       '<input type="submit" name="cmdSearch" value="Search"
           style="width: 47pt;"
           onclick="this.disabled=1; this.form.submit();"
           class="btn"
              onmouseover="this.className=\'btn btnhov\'"
              onmouseout="this.className=\'btn\'">&nbsp;&nbsp;';


   if (!$bViaAvailRpt){
         //-------------------------
         // via Client ID
         //-------------------------
      showSearchOption($clsForm, $formErr, 'Search by Client ID', 1, ' (client ID)');

         //-------------------------
         // via first/last name
         //-------------------------
      showSearchOption($clsForm, $formErr, 'Search by First/Last Name',  2, ' (first few letters of the name)');
   }
      //--------------------------------------
      // clients available for sponsorship
      //--------------------------------------
   showClientsAvailForSpon($bViaAvailRpt, $strSponProgDDL, $strButton);
   
   echoT('<script type="text/javascript">frmPSearch2.srch2.focus();</script>');
   
   
   function showSearchOption(&$clsForm, &$formErr, $strBlockLabel, $idx, $strTxtFieldLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock($strBlockLabel, '');
         echoT('<table cellpadding="0" cellspacing="0">');
         $attributes = array('name' => 'frmPSearch'.$idx, 'id' => 'pSearch'.$idx);
         echoT(form_open('clients/client_search/searchOpts/false', $attributes));
         echoT(form_hidden('searchIdx', $idx));
         echoT('<tr><td style="vertical-align: top;">'
            .$clsForm->strSubmitButton('Search', 'submit', '')
            .'</td>
              <td style="width: 225pt;">
                 &nbsp;&nbsp;<input type="text" id="srch'.$idx.'" name="txtSearch'.$idx.'" style="width: 50pt;">'
                 .$strTxtFieldLabel
                 .$formErr[$idx].'
              </td></tr>');

         echoT('</table>');
         echoT(form_close('<br>'));
      closeBlock();
   }   
   
function showClientsAvailForSpon($bViaAvailRpt, $strSponProgDDL, $strButton){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $clsNav = new stdClass;
   $clsNav->type  = 'sponsorships';
   $clsNav->st    = 'client';
   $clsNav->sst   = 'search';
   $clsNav->ssst  = 'run';
   $clsNav->sssst = 'availSpon';

   openBlock('Clients Available for Sponsorship', '');
   if ($bViaAvailRpt) echoT('<br>');
   echoT(form_open('clients/client_search/searchViaAvail/'.($bViaAvailRpt ? 'true' : 'false')));

   echoT($strButton
     .'<select name="ddlSponProg">
          <option value="-1">(any program)</option>'
          .$strSponProgDDL
      .'</select></form>');
   echoT(form_close(''));
   closeBlock();
}


