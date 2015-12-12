<?php

   $attributes = array('name'     => 'frmNewBizContact',
                       'onSubmit' => 'return verifySimpleSearch(frmNewBizContact);'
                       );

   echoT(form_open('biz/biz_contact_add_edit/addNewS2/'.$lBizID, $attributes));
   echoT('<br><br>');
   
   $clsSearch = new msearch_single_generic;
   $clsSearch->strLegendLabel = 'Add a Contact to this Business/Organization';
   $clsSearch->strButtonLabel = 'Search';

   $clsSearch->lSearchTableWidth = 240;
   $clsSearch->searchPeopleTableForm();
   
