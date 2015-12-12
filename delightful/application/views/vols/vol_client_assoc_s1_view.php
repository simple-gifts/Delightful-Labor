<?php

   $attributes = array('name'     => 'frmNewVolClient',
                       'onSubmit' => 'return verifySimpleSearch(frmNewVolClient);'
                       );

   echoT(form_open('volunteers/vol_client_association/addS2/'.$lVolID, $attributes));
   echoT('<br><br>');
   
   $clsSearch = new msearch_single_generic;
   $clsSearch->strLegendLabel = 'Associate Volunteer with Client';
   $clsSearch->strButtonLabel = 'Search';

   $clsSearch->lSearchTableWidth = 240;
   $clsSearch->searchClientTableForm();
   
