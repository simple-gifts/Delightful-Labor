<?php
   global $gclsChapterVoc;

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
      // via People ID
      //-------------------------
   showSearchOption($clsForm, $formErr, 'Search by People ID', 1, ' (people ID)');

   showSearchOption($clsForm, $formErr, 'Search by First/Last Name',  2, ' (first few letters of the name)');
   showSearchOption($clsForm, $formErr, 'Search by City',     3, ' (first few letters of the city)');
   showSearchOption($clsForm, $formErr, 'Search by '.$gclsChapterVoc->vocState,    4, ' (first few letters of the '.$gclsChapterVoc->vocState.')');
   showSearchOption($clsForm, $formErr, 'Search by Country',  5, ' (first few letters of the country)');
   showSearchOption($clsForm, $formErr, 'Search by '.$gclsChapterVoc->vocZip,      6, ' (first few letters of the '.$gclsChapterVoc->vocZip.')');
   showSearchOption($clsForm, $formErr, 'Search Everything',  7, ' (a few letters in the name/address)');
  
   echoT('<script type="text/javascript">frmPSearch2.txtSearch2.focus();</script>');
   
   function showSearchOption(&$clsForm, &$formErr, $strBlockLabel, $idx, $strTxtFieldLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock($strBlockLabel, '');
         echoT('<table cellpadding="0" cellspacing="0">');
         $attributes = array('name' => 'frmPSearch'.$idx, 'id' => 'pSearch'.$idx);
         echoT(form_open('people/people_search/searchOpts', $attributes));
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
   

