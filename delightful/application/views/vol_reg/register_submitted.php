<?php

   htmlHeader($title, $rRec->strCSSFN, $js);
   
   startBody();
   submitted($rRec);
   endBody($this);


   function htmlHeader($title, $strCSSFN, &$js){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
              "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
         <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
         <head>
           <meta http-equiv="content-type" content="text/html; charset=utf-8" />
           <title>'.$title.'</title>
         <link href="'.base_url().'css/vol_reg/'.$strCSSFN.'" rel="stylesheet" type="text/css" />
         <link type="text/css"          href="'.base_url().'css/shady/jquery-ui-1.8.2.custom.css" rel="stylesheet" />
         <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery.ui.all.css">

         <noscript>
         Javascript is not enabled! Please turn on Javascript to use this site.
         </noscript>

         <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
         <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.23/jquery-ui.min.js"></script>
         <script type="text/javascript">
         //<![CDATA[
         base_url = \''.base_url().'\';
         //]]>
         </script>');
      if (isset($js)){
         echoT($js);
      }
      echoT('</head>');
   }

   function startBody(){
      echoT('
         <body>
         <div id="wrapper">');
   }
   
   function submitted(&$rRec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // logo image
      if (!is_null($rRec->strLogoImageTag)){
         echoT($rRec->strLogoImageTag.'<br>');
      }

      echoT('<div class="orgName">'.$rRec->strBannerOrg.'</div>');
      echoT('<div class="pageBanner">'.$rRec->strBannerTitle.'</div><br>');

      echoT('<hr style="width: 90%;">');

      echoT('<div class="intoText">'
                 .nl2br(htmlspecialchars($rRec->strSubmissionText)).'</div><br>');
   }
   
   
   function endBody(&$cThis){
      echoT('
         <div id="footer">');
       $cThis->load->view('vol_reg/footer');
       echoT('
               </div>
            </div>

            </body>
            </html>');
   }
   
