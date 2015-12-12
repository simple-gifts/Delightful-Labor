<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title>Delightful Labor | Installation</title>
  <link href="<?php echo base_url();?>css/default.css" rel="stylesheet" type="text/css" />
  <link href="<?php echo base_url();?>css/dbInstall.css" rel="stylesheet" type="text/css" />
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
echoT('
   <body>
   <!-- <div id="wrapper" >  -->
      <div id="header" >');
    
switch ($viewType){
   case 'dbInstall':    
      $this->load->view('install/db_install_form'); 
      break;
   case 'dbInstallComplete':    
      $this->load->view('install/db_install_complete'); 
      break;
   default:
      break;
}      
      
echoT('      
      </div>   ');
echoT('
   </body>
   </html>');

