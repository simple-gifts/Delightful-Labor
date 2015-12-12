<?php

   if (bAllowAccess('showSponsors')){
      echoT(
          '<br>
           <b>Accounting Features</b>
           <ul style="margin-top: 4pt;">
               <li>'.anchor('sponsors/auto_charge/applyChargesOpts', 'Apply Charges').'
               <li>'.anchor('sponsors/batch_payments/batchSelectOpts', 'Batch Payments').'
            </ul>
         
           <br>
           <b>Directories</b>
           
           <ul style="margin-top: 4pt;">
             <li>'.anchor('sponsors/spon_directory/view/false/-1/A/0/50',  'Directory').'</li>
           </ul>
             
           <b>Utilities</b>
           <ul style="margin-top: 4pt;">
             <li>'.anchor('sponsors/spon_search/opts',  'Search')       .'</li>
           </ul>             
           <br><br>'
         );
         
   }
     
     
     
     
     