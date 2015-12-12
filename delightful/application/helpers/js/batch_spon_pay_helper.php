<?php
/*
   Delightful Labor
  
   copyright (c) 2012-2013 by Database Austin
   Austin, Texas
  
   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('js/batch_spon_pay');
---------------------------------------------------------------------
   
   
*/

function batchSponPay(){
   return('
<script language="JavaScript"><!--
    var bUserDataEntered = false;
//--></script>');
}

function batchSponPayForceDirty(){
   return('
<script language="JavaScript"><!--
    bUserDataEntered = true;
//--></script>');
}


