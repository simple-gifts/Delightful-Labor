<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title><?php echo $title; ?></title>
<link href="<?php echo base_url();?>css/default.css" rel="stylesheet" type="text/css" />

<!-- thanks to Tomas Bagdanavicius, http://www.lwis.net/free-css-drop-down-menu/ for navigation css -->
<link href="<?php echo base_url();?>css/dropdown/dropdown.css"                rel="stylesheet" type="text/css" />
<link href="<?php echo base_url();?>css/dropdown/dropdown.vertical.rtl.css"   rel="stylesheet" type="text/css" />
<link href="<?php echo base_url();?>css/dropdown/themes/default/default.css"  rel="stylesheet" type="text/css" />
<?php
if (CB_AAYHF){
echo('<link href="'.base_url().'css/aayhf/aayhf.css"   rel="stylesheet" type="text/css" />'."\n");
}
?>
<link type="text/css"          href="<?php echo(base_url()); ?>css/shady/jquery-ui-1.8.2.custom.css" rel="stylesheet" />

<noscript>
Javascript is not enabled! Please turn on Javascript to use this site.
</noscript>

<!--
If you are unable to access the Internet, use the following three lines
instead of the three lines that follow. You must also uncomment the "echo" statements
<link rel="stylesheet" href="<?php echo(base_url()); ?>local/css/jquery.ui.all.css">
<script src="<?php //echo(base_url()); ?>local/js/jquery.min.js"></script>
<script src="<?php //echo(base_url()); ?>local/js/jquery-ui.min.js"></script>
-->


<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery.ui.all.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
<!--
-->

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

   <div id="pageBanner">
      <?php echo(htmlspecialchars(@$_SESSION[CS_NAMESPACE.'_chapter']->strBanner)); ?>
   </div>
   <?php $this->load->view('navigation'); ?>

   <?php
      global $gbAdmin, $gbVolLogin;
      
      if (!$gbVolLogin){      
         echo('<div id="bannerSearch">'."\n");
         $strDirs   = 'Directories: ';
         $strSearch = 'Search: ';
         $strHome   = 'Home: ';
         $strSpacer = '<span style="padding-left: 30px;">&nbsp;</span>';
         $strAStyle = 'style="font-size: 7pt;" ';

         if (bAllowAccess('showReports')){
            $strHome .= anchor('main/menu/reports',   'reports', $strAStyle.'id="nb_rpt_home" ').'&nbsp;&nbsp;'."\n";
         }

         if (bAllowAccess('showPeople')){
            $strDirs   .= anchor('people/people_dir/view/A',        'people',  $strAStyle.'id="nb_d_p" ').'&nbsp;&nbsp;'."\n"
                         .anchor('biz/biz_directory/view/A',        'bus/org', $strAStyle.'id="nb_d_b" ').'&nbsp;&nbsp;'."\n"
                         .anchor('volunteers/vol_directory/view/A', 'vol',     $strAStyle.'id="nb_d_v" ').'&nbsp;&nbsp;'."\n";

            $strSearch .= anchor('people/people_search/searchOpts',   'people',  $strAStyle.'id="nb_search_p" '  ) .'&nbsp;&nbsp;'."\n"
                         .anchor('biz/biz_search/searchOpts',         'bus/org', $strAStyle.'id="nb_search_bz" ' ).'&nbsp;&nbsp;'."\n"
                         .anchor('volunteers/vol_search/searchOpts',  'vol',     $strAStyle.'id="nb_search_vol" ').'&nbsp;&nbsp;'."\n";

            $strHome   .= anchor('main/menu/people', 'people',  $strAStyle.'id="nb_pe_home" ').'&nbsp;&nbsp;'."\n"
                         .anchor('main/menu/biz',    'bus/org', $strAStyle.'id="nb_bz_home" ').'&nbsp;&nbsp;'."\n"
                         .anchor('main/menu/vols',   'vol',     $strAStyle.'id="nb_vl_home" ').'&nbsp;&nbsp;'."\n";
         }

         if (bAllowAccess('showFinancials') || bAllowAccess('showGrants')){
            $strHome   .= anchor('main/menu/financials', 'financials', $strAStyle.'id="nb_fg_home" ').'&nbsp;&nbsp;'."\n";
         }

         if (bAllowAccess('showClients')){
            $strDirs   .= anchor('clients/client_dir/name/N/A',      'client', $strAStyle.'id="nb_d_c" ').'&nbsp;&nbsp;'."\n";
            $strSearch .= anchor('clients/client_search/searchOpts', 'client', $strAStyle.'id="nb_search_cl" ').'&nbsp;&nbsp;'."\n";
            $strHome   .= anchor('main/menu/client',                 'clients', $strAStyle.'id="nb_cl_home" ').'&nbsp;&nbsp;'."\n";
         }

         if (bAllowAccess('showSponsors')){
            $strDirs   .= anchor('sponsors/spon_directory/view/false/-1/A/0/50',  'sponsor',  $strAStyle.'id="nb_d_s" ').'&nbsp;&nbsp;'."\n";
            $strSearch .= anchor('sponsors/spon_search/opts',                     'sponsor',  $strAStyle.'id="nb_spon_search" ').'&nbsp;&nbsp;'."\n";
            $strHome   .= anchor('main/menu/sponsorship',                         'sponsors', $strAStyle.'id="nb_spon_home" ').'&nbsp;&nbsp;'."\n";
         }
         
         if ($gbAdmin){
            $strHome   .= anchor('main/menu/admin',   'admin', $strAStyle.'id="nb_admin_home" ').'&nbsp;&nbsp;'."\n";
         }
         
         $strHome .= anchor('main/menu/more',   'more', $strAStyle.'id="nb_more_home" ').'&nbsp;&nbsp;'."\n";

         echoT($strDirs.$strSpacer.$strSearch.$strSpacer.$strHome."\n");
         echoT('</div>'."\n");
      }
   ?>
   <div id="pageTitle">
      <?php echo($pageTitle); ?>
   </div>

<?php
       if ($this->session->flashdata('error')){
         echo('<div class="error">'.$this->session->flashdata('error').'</div>');
      }
       if ($this->session->flashdata('msg')){
         echo('<div class="message">'.$this->session->flashdata('msg').'</div>');
      }
      if (isset($strFormDataEntryAlert)){
         if ($strFormDataEntryAlert != ''){
            echo('<div class="error">'.strip_tags($strFormDataEntryAlert,'<b><i><br><font>').'</div>');
         }
      }
      if (isset($strErrOnForm)){
         echo('<div class="error">'.$strErrOnForm.'</div>');
      }
      if (isset($info)){
         echo('<div class="info">'.$info.'</div><br>');
      }
      echoT('   <div id="main">');
      if (isset($contextSummary)){
         echoT($contextSummary);
      }

      if (!is_null($mainTemplate)){
         if (is_array($mainTemplate)){
            foreach ($mainTemplate as $singleTemplate){
               $this->load->view($singleTemplate);
            }
         }else {
            $this->load->view($mainTemplate);
         }
      }
?>

   </div>

   <div id="footer">
      <?php $this->load->view('footer'); ?>
   </div>
</div>

</body>
</html>


