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


      //-------------------------
      // via Sponsor ID
      //-------------------------
   showSearchOption($clsForm, $formErr, 'Search by Sponsor ID', 1, ' (sponsor ID)');

      //-------------------------
      // via People ID
      //-------------------------
   showSearchOption($clsForm, $formErr, 'Search by People/Business ID', 2, ' (people/business ID)');

   showSearchOption($clsForm, $formErr, 'Search by First/Last/Business Name',  3, ' (first few letters of the name)');

   showSponViaProgram($strSponProgDDL, $strButton);
   
   echoT('<script type="text/javascript">frmPSearch3.txtSearch3.focus();</script>');
   
   
   function showSearchOption(&$clsForm, &$formErr, $strBlockLabel, $idx, $strTxtFieldLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock($strBlockLabel, '');
         echoT('<table cellpadding="0" cellspacing="0">');
         $attributes = array('name' => 'frmPSearch'.$idx, 'id' => 'pSearch'.$idx);
         echoT(form_open('sponsors/spon_search/opts', $attributes));
         echoT(form_hidden('searchIdx', $idx));
         echoT('<tr><td style="vertical-align: top;">'
            .$clsForm->strSubmitButton('Search', 'submit', '')
            .'</td>
              <td style="width: 225pt;">
                 &nbsp;&nbsp;<input type="text" name="txtSearch'.$idx.'" style="width: 50pt;">'
                 .$strTxtFieldLabel
                 .$formErr[$idx].'
              </td></tr>');

         echoT('</table>');
         echoT(form_close('<br>'));
      closeBlock();
   }
   
function showSponViaProgram($strSponProgDDL, $strButton){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------

   openBlock('Sponsors Via Program', '');
   echoT(form_open('sponsors/spon_search/viaProgram'));

   echoT($strButton
     .'<select name="ddlSponProg">'
          .$strSponProgDDL
      .'</select></form>');
   echoT(form_close(''));
   closeBlock();
}


