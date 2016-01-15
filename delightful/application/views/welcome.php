<h2>Welcome to Delightful Labor!</h2>
<?php

global $glUserID;

//echo('test test test test '.$lNumTotLogins.'<br>'); $lNumTotLogins=1;
if ($lNumTotLogins==1){
   $strAttribute = 'target="_blank"';
   echoT('It looks like this is your first log-in to <b><i>Delightful Labor</b></i>. These links (all open in a new page)
      will <br>help you get started:<br>');

   echoT(
     '<ul>
        <li>The Delightful Labor <b>user\'s guide</b> can be found '
                   .anchor('http://www.delightfullabor.com/userGuide/', 'here.', $strAttribute).'</li>
        <li>You can update your <b>user account</b>  '
                   .strLinkEdit_User($glUserID, 'here.', false, $strAttribute).'
        <li>You can update your <b>organization\'s information</b>  '
                   .anchor('admin/org/orgView', 'here.', $strAttribute).'</li>
                   
        <li>You can configure many of the <b>lists</b> used by Delightful Labor '
                   .anchor('admin/alists/showLists', 'here.', $strAttribute).'</li>                   
      </ul>'
   );


}