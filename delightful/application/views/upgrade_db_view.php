<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title>Delightful Labor | Login</title>
  <link href="<?php echo base_url();?>css/default.css" rel="stylesheet" type="text/css" />
  <noscript>
    Javascript is not enabled! Please turn on Javascript to use this site.
  </noscript>

<script type="text/javascript">
//<![CDATA[
base_url = '<?php echo base_url();?>';
//]]>
</script>


</head>
<?php
   global $glChapterID, $glUserID, $gstrFName;

   echoT('
      <body>
      <!-- <div id="wrapper" >  -->
         <div id="header" >
       
            <div class="login">
               <div class="loginWelcome">
                  <img src="'.base_url().'images/layout/dl_logo02.png"><br>
                  <img src="'.base_url().'images/layout/dl_logo03.png">
               </div> ');

   echoT('<br>Welcome!<br><br>
      It appears that you have recently upgraded your <b><i>'.CS_PROGNAME.'</b></i> software and<br>
      now require a database upgrade.<br><br>
      
      If you wish to backup your database before upgrading the database, '
      .anchor('admin/db_zutil/backupRunAuto', 'click here', 'style="font-size: 12pt;" id="upgrade_backup"').'.<br><br>
      To upgrade to the required database level, '
      .anchor('admin/upgrades/upgrade', 'click here', 'style="font-size: 12pt;" id="upgrade_run"').'.<br><br>
   
   ');
   
   echoT('
      <table>
          <tr>
             <td >
                Software version:</td>
             <td >'
                .$versionInfo->softwareLevel.'
             </td>
          </tr>
          <tr>
             <td >
                Database version:
             </td>
             <td >'
                .$versionInfo->dbVersion.'
             </td>
          </tr>
          <tr>
             <td >
                Required db version:
             </td>
             <td >'
                .$versionInfo->softwareDBCompabilityLevel.'
             </td>
          </tr>
          
       </table>');
            
   
   
   echoT('               
         </div>   ');
         
   echoT('
      </body>
      </html>');

