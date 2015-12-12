<?php
   global $gclsChapterVoc;

   $attributes = array('name' => 'frmLoc', 'id' => 'frmAddEdit');
   echoT(form_open('admin/org/addEdit/'.$id, $attributes).'<br>');
   
   $clsGF = new generic_form();
   $clsGF->bValueEscapeHTML = false;
   $clsGF->strLabelClass = $clsGF->strLabelRowLabelClass = $clsGF->strLabelClassRequired = 'enpViewLabel';
   $clsGF->strTitleClass = 'enpViewTitle';
   $clsGF->strStyleExtraLabel = 'width: 120pt; padding-top: 8px;';   
   $clsGF->strEntryClass = 'enpView';
   
   
   openBlock(($bNew ? 'Add new ' : 'Edit ').'Your Organization Info', '');  echoT('<table class="enpView" >');
//   echoT($clsGF->strTitleRow( (($bNew ? 'Add new ' : 'Edit ').'Your Organization Info'), 2, ''));
    
    
   $clsGF->strExtraFieldText = form_error('txtChapter');
   $clsGF->strID = 'addEditEntry';
   echoT($clsGF->strGenericTextEntry  ('Organization Name', 'txtChapter',    true,   $clsChapter->strName,   50, 80));
   $clsGF->strExtraFieldText = form_error('txtBanner');
   echoT($clsGF->strGenericTextEntry  ('Banner Tag Line',   'txtBanner',     true,   $clsChapter->strBanner, 50, 80));
    
   echoT($clsGF->strGenericTextEntry  ('Address',                 'txtAddr1',      false,  $clsChapter->strAddr1,   30, 80));
   echoT($clsGF->strGenericTextEntry  ('Address 2',               'txtAddr2',      false,  $clsChapter->strAddr2,   30, 80));
   echoT($clsGF->strGenericTextEntry  ('City',                    'txtCity',       false,  $clsChapter->strCity,    30, 40));
   echoT($clsGF->strGenericTextEntry  ($gclsChapterVoc->vocState, 'txtState',      false,  $clsChapter->strState,   30, 40));
   echoT($clsGF->strGenericTextEntry  ('Country',                 'txtCountry',    false,  $clsChapter->strCountry, 30, 40));
   echoT($clsGF->strGenericTextEntry  ($gclsChapterVoc->vocZip,   'txtZip',        false,  $clsChapter->strZip,     12, 25));
  
   echoT($clsGF->strGenericTextEntry  ('Phone',             'txtPhone',      false,  $clsChapter->strPhone,   30,  50));
   echoT($clsGF->strGenericTextEntry  ('Fax',               'txtFax',        false,  $clsChapter->strFax,     30,  50));
   $clsGF->strExtraFieldText = form_error('txtEmail');
   echoT($clsGF->strGenericTextEntry  ('Email',             'txtEmail',      false,  $clsChapter->strEmail,   30,  80));
   echoT($clsGF->strGenericTextEntry  ('Web Site',          'txtWebSite',    false,  $clsChapter->strWebSite, 30, 100));

   echoT($clsGF->strGenericTextEntry  ('Default Area Code', 'txtDefAC',      false,  $clsChapter->strDefAreaCode, 16, 10));
   echoT($clsGF->strGenericTextEntry  ('Default '.$gclsChapterVoc->vocState,     'txtDefState',   false,  $clsChapter->strDefState,    16, 40));
   echoT($clsGF->strGenericTextEntry  ('Default Country',   'txtDefCountry', false,  $clsChapter->strDefCountry,  16, 40));
   
   $clsGF->strStyleExtraLabel = 'padding-top: 2px;';   
   echoT($clsGF->strLabelRow          ('Default Date Format', $clsChapter->strDateFormatRadio, 1));
   $clsGF->strStyleExtraLabel = 'padding-top: 8px;';   
   echoT($clsGF->strLabelRow          ('Default Accounting Country', $clsChapter->strACORadio, 1));

   $clsGF->strExtraFieldText = form_error('ddlTZ');
   echoT($clsGF->strLabelRow          ('Time Zone', $clsChapter->strTimeZoneDDL, 1));

      // vocabulary
   $clsGF->strExtraFieldText = '<br><i>Display for zip code, postal code, etc.</i>'.form_error('txtVocZip');
   echoT($clsGF->strGenericTextEntry  ('Voc. for Zip/Postal Code', 'txtVocZip',    true,   $clsChapter->txtVocZip,   50, 80));
   
   $clsGF->strExtraFieldText = '<br><i>Display for State, Province, etc.</i>'.form_error('txtVocState');
   echoT($clsGF->strGenericTextEntry  ('Voc. for State/Province', 'txtVocState',    true,   $clsChapter->txtVocState,   50, 80));
   
   $clsGF->strExtraFieldText = '<br><i>Display for Job Skills, etc.</i>'.form_error('txtJobSkills');
   echoT($clsGF->strGenericTextEntry  ('Voc. for Job Skills', 'txtJobSkills',    true,   $clsChapter->txtJobSkills,   50, 80));
   
   
   echoT($clsGF->strSubmitEntry('Save', 2, 'cmdSubmit', 'width: 90pt;'));
   echoT('</table>'); closeBlock();

   echoT(form_close('<br><br>'));
   echoT('<script type="text/javascript">frmAddEdit.addEditEntry.focus();</script>');

