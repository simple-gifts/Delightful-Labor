<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title><?php echo $title; ?></title>
<link href="<?php echo base_url();?>css/vol_reg/default.css" rel="stylesheet" type="text/css" />
<link type="text/css"          href="<?php echo(base_url()); ?>css/shady/jquery-ui-1.8.2.custom.css" rel="stylesheet" />
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery.ui.all.css">

<!-- thanks to Tomas Bagdanavicius, http://www.lwis.net/free-css-drop-down-menu/ for navigation css -->
<link href="<?php echo base_url();?>css/dropdown/dropdown.css"               media="screen" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url();?>css/dropdown/dropdown.vertical.rtl.css"  media="screen" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url();?>css/dropdown/themes/default/default.css" media="screen" rel="stylesheet" type="text/css" />

<noscript>
Javascript is not enabled! Please turn on Javascript to use this site.
</noscript>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
<script type="text/javascript">
//<![CDATA[
base_url = '<?php echo base_url();?>';
//]]>
</script>
<?php
   if (isset($js)){
      echoT($js);
   }
?>
</head>

<body>

<div id="wrapper">

   <div class="pageBanner404">
      <?php       
          echo(htmlspecialchars($strOrg)); 
      ?>
   </div>


<?php
   echoT('<font style="font-size: 16pt;"><br><br>
         Ooops! We couldn\'t find your volunteer registration form.<br><br>
         Please contact the volunteer coordinator at <b><i>'.htmlspecialchars($strOrg).'</i></b> for assistance.
         <br><br><br><br>');
?>


   <div id="footer">
      <?php $this->load->view('vol_reg/footer'); ?>
   </div>
</div>

</body>
</html>


