<?php
/*
   Delightful Labor
  
   copyright (c) 2012-2013 by Database Austin
   Austin, Texas
  
   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('js/div_hide_show');
---------------------------------------------------------------------
Thanks to 
   http://www.randomsnippets.com/2008/02/12/how-to-hide-and-show-your-div/   
   
   
<div id="headerDivImg">
    <div id="titleTextImg">Let's use images!</div>
    <a id="imageDivLink" href="javascript:toggle5('contentDivImg', 'imageDivLink');"><img src="/wp-includes/images/minus.png"></a>
</div>
<div id="contentDivImg" style="display: block;">This demo uses plus and minus images for hiding and showing your div dynamically via JavaScript.</div>
   
   
*/

function showHideDiv(){
   return('

<script language="JavaScript"><!--

   function toggleDivViaImage(showHideDiv, switchImgTag, strLabelShow, strLabelHide) {
      var ele = document.getElementById(showHideDiv);
      var imageEle = document.getElementById(switchImgTag);
      if(ele.style.display == "block") {
         ele.style.display = "none";
         imageEle.innerHTML = \'<img src="'.base_url().'images/misc/plus.gif" border="0">\';
         imageEle.title = strLabelShow;
      }else {
         ele.style.display = "block";
         imageEle.innerHTML = \'<img src="'.base_url().'images/misc/minus.gif" border="0">\';
         imageEle.title = strLabelHide;
      }
   }      
//--></script>');
}


