<?php
/*
   Delightful Labor
  
   copyright (c) 2012-2013 by Database Austin
   Austin, Texas
  
   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('js/check_boxes_in_div');
---------------------------------------------------------------------
   Thanks to 
   http://www.webhostingtalk.com/showthread.php?t=426817   
      
   CODE
   ====
   <div id="test">
   <input type="checkbox">
   <input type="checkbox">
   <input type="checkbox">
   <input type="checkbox">
   <input type="checkbox">
   </div>
   <script type="text/javascript">

   function checkByParent(aId, aChecked) {
       var collection = document.getElementById(aId).getElementsByTagName('INPUT');
       for (var x=0; x<collection.length; x++) {
           if (collection[x].type.toUpperCase()=='CHECKBOX')
               collection[x].checked = aChecked;
       }
   }
   checkByParent('test', true);
   </script>


   USAGE
   =====
   button...
   <input type="button" value="Check All" onclick="checkByParent('test', true);">
   <input type="button" value="Uncheck All" onclick="checkByParent('test', false);">

   link...
   <a href="index.html" onclick="checkByParent('test', true); return false;">check all</a>
      
*/

function checkUncheckInDiv(){
//---------------------------------------------------------------------
//   aID - the divID
//   aChecked - (boolean) set to this state
//---------------------------------------------------------------------
   return('
   <script language="JavaScript"><!--

   function checkByParent(aId, aChecked) {
       var collection = document.getElementById(aId).getElementsByTagName(\'INPUT\');
       for (var x=0; x<collection.length; x++) {
           if (collection[x].type.toUpperCase()==\'CHECKBOX\')
               collection[x].checked = aChecked;
       }
   }
   //--></script>');
}


