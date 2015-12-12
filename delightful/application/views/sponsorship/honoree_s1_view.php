<?php

   $attributes = array('name'     => 'frmNewSponHonoree',
                       'onSubmit' => ' return verifySimpleSearch(frmNewSponHonoree); '
                       );

   echoT(form_open('sponsors/honorees/addNewS2/'.$lSponID, $attributes));
   echoT('<br><br>');
   
   $clsSearch = new msearch_single_generic;
   $clsSearch->strLegendLabel = 'Add an Honoree to this Sponsorship';
   $clsSearch->strButtonLabel = 'Search';

   $clsSearch->lSearchTableWidth = 240;
   $clsSearch->searchPeopleTableForm();
   
   
