<?php
   $clsForm = new Generic_form;
   $clsForm->strLabelClass = $clsForm->strLabelRowLabelClass = $clsForm->strLabelClassRequired = 'enpViewLabel';
   $clsForm->strTitleClass = 'enpViewTitle';
   $clsForm->strEntryClass = 'enpView';
   $clsForm->bValueEscapeHTML = false;

   $strButton =
       '<input type="submit" name="cmdSearch" value="Search"
           onclick="this.disabled=1; this.form.submit();"
           style="width: 47pt;"
           class="btn"
              onmouseover="this.className=\'btn btnhov\'"
              onmouseout="this.className=\'btn\'">&nbsp;&nbsp;';


      //-------------------------
      // via Vol ID
      //-------------------------
   showSearchOption($clsForm, $formErr, 'Search by Volunteer ID', 1, ' (volunteer ID)');

      //-------------------------
      // via People ID
      //-------------------------
   showSearchOption($clsForm, $formErr, 'Search by People ID', 2, ' (people ID)');

   showSearchOption($clsForm, $formErr, 'Search by First/Last Name',  3, ' (first few letters of the name)');
  
   echoT('<script type="text/javascript">frmPSearch3.txtSearch3.focus();</script>');
  
   
   function showSearchOption(&$clsForm, &$formErr, $strBlockLabel, $idx, $strTxtFieldLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock($strBlockLabel, '');
         echoT('<table cellpadding="0" cellspacing="0">');
         $attributes = array('name' => 'frmPSearch'.$idx, 'id' => 'pSearch'.$idx);
         echoT(form_open('volunteers/vol_search/searchOpts', $attributes));
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
   

