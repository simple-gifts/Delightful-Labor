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
      // via People ID
      //-------------------------
   showSearchOption($clsForm, $formErr, 'Search by Business ID',   1, ' (business ID)');
   showSearchOption($clsForm, $formErr, 'Search by Business Name', 2, ' (first few letters of the name)');
   showSearchBizCat($clsForm, $formErr, $formData,                 3);
   echoT('<script type="text/javascript">frmPSearch2.txtSearch2.focus();</script>');
   
   function showSearchOption(&$clsForm, &$formErr, $strBlockLabel, $idx, $strTxtFieldLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock($strBlockLabel, '');
         echoT('<table cellpadding="0" cellspacing="0">');
         $attributes = array('name' => 'frmPSearch'.$idx, 'id' => 'pSearch'.$idx);
         echoT(form_open('biz/biz_search/searchOpts', $attributes));
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
   
   function showSearchBizCat(&$clsForm, &$formErr, $formData, $lSearchIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      openBlock('Search by Business/Organization Category', '');
         echoT('<table cellpadding="0" cellspacing="0">');
         $attributes = array('name' => 'bizCat', 'id' => 'bizCat');
         echoT(form_open('biz/biz_search/searchOpts', $attributes));
         echoT(form_hidden('searchIdx', $lSearchIDX));
         echoT('<tr><td style="vertical-align: top;">'
            .$clsForm->strSubmitButton('Search', 'submit', '')
            .'</td>
              <td >
                 &nbsp;&nbsp;'
                 .$formData->strBizList
                 .$formErr[$lSearchIDX].'<br>
                   &nbsp;&nbsp;<i>(work in progress)</i>
              </td></tr>');

         echoT('</table>');
         echoT(form_close('<br>'));
      closeBlock();
   }   
   

