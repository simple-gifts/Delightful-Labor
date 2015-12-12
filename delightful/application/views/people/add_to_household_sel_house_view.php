<?php
// form setup

   $attributes = array('name'     => 'frmNewRelSearch',
                       'onSubmit' => ' return verifySimpleSearch(frmNewRelSearch); '
                       );

   echoT(form_open('people/household/addNewS2/'.$lHouseholdID, $attributes));
   echoT('<br><br>');
   
   $clsSearch = new msearch_single_generic;
   $clsSearch->strLegendLabel =
        'Add a new member to <b>'.$strHouseholdName
                    .'</b> <i>(householdID '.$lHouseholdID.')</i>';
   $clsSearch->strButtonLabel = 'Search';

   $clsSearch->lSearchTableWidth = 240;
   $clsSearch->searchPeopleTableForm();
   echoT(form_close());
   
//echoT('<h3>Household for new person:</h3>');

