<?php
global $gstrUserName, $gstrSafeName, $gbDev;

echoT('<br><font style="font-size: 13pt;font-variant: small-caps;"><b><i>Delightful Labor</b></i></font><br>
       <img src="'.base_url().'images/layout/separatorBlack.jpg"
                 width="400px" height="2px" alt="---"
                 style="vertical-align:text-top;" /><br>
       <table class="enpView">
       
          <tr>
             <td class="enpViewLabel">Current User:</td>
             <td class="enpView">'
                .htmlspecialchars($gstrUserName).' ('.$gstrSafeName.')
             </td>
          </tr>
          <tr>
             <td class="enpViewLabel">PHP version:</td>
             <td class="enpView">'
                .phpversion().'
             </td>
          </tr>
          <tr>
             <td class="enpViewLabel">Software version:</td>
             <td class="enpView">'
                .$versionInfo->softwareLevel.'
             </td>
          </tr>
          <tr>
             <td class="enpViewLabel">Software date:</td>
             <td class="enpView">'
                .$versionInfo->softwareDate.'
             </td>
          </tr>
          <tr>
             <td class="enpViewLabel">Database version:</td>
             <td class="enpView">'
                .$versionInfo->dbVersion.'
             </td>
          </tr>
          <tr>
             <td class="enpViewLabel">Date of DB install/upgrade:</td>
             <td class="enpView">'
                .date('Y-m-d', $versionInfo->dteDBInstall).'
             </td>
          </tr>');
          
   $CI =& get_instance();
   $strDB = $CI->db->database;          
   echoT('          
          <tr>
             <td class="enpViewLabel">Database:</td>
             <td class="enpView">'
                .$strDB.'
             </td>
          </tr>
          <tr>
             <td class="enpViewLabel">Database Notes:</td>
             <td class="enpView">'
                .nl2br($versionInfo->strVersionNotes).'
             </td>
          </tr>
          <tr>
             <td class="enpViewLabel">User\'s Guide:</td>
             <td class="enpView">'
                .anchor('http://www.delightfullabor.com/userGuide/', 'http://www.delightfullabor.com/userGuide/').'
             </td>
          </tr>
          <tr>
             <td class="enpViewLabel">Community Forum:</td>
             <td class="enpView">'
                .anchor('http://www.delightfullabor.com/forum/', 'http://www.delightfullabor.com/forum/').'
             </td>
          </tr>');
          
   if ($gbDev){
      $atts = array(
              'width'      => '800',
              'height'     => '600',
              'scrollbars' => 'yes',
              'status'     => 'yes',
              'resizable'  => 'yes',
              'screenx'    => '0',
              'screeny'    => '0'
            );

      echoT('
          <tr>
             <td class="enpViewLabel">PHP Info:</td>
             <td class="enpView">'
                .anchor_popup('more/about/php_info', 'phpInfo (new window)', $atts).'
             </td>
          </tr>');
      
   }   
          
          
   echoT('          
       </table><br><br>

       ');