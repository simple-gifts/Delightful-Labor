<?php

   $attributes = array('name'     => 'frmNewVolSearch',
                       'onSubmit' => ' return verifySimpleSearch(frmNewVolSearch); '
                       );

   echoT(form_open('volunteers/vol_add_edit/addEditS2', $attributes));
   echoT('<br><br>');
   
   $clsSearch = new msearch_single_generic;
   $clsSearch->strLegendLabel = 'Add a new Volunteer';
   $clsSearch->strButtonLabel = 'Search';

   $clsSearch->lSearchTableWidth = 240;
   $clsSearch->searchPeopleTableForm();
   
   
   