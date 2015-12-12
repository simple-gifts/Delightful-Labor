<?php

echoT('
    You have successfully enrolled <b>'.$client->strSafeName
        .'</b> in <b>'.htmlspecialchars($cprog->strProgramName).'</b>.<br><br>');

echoT(
   strLinkAdd_CProgAttendance(false, $lClientID, $lCProgID, $lEnrollRecID,
           'Add '.$cprog->strSafeAttendLabel.' Record', true).'&nbsp;'
  .strLinkAdd_CProgAttendance(false, $lClientID, $lCProgID, $lEnrollRecID,
           'Add '.$cprog->strSafeAttendLabel.' Record', false).'<br><br>');
           
echoT(
   strLinkView_CProgEnrollRec($lTableID, $lClientID, $lEnrollRecID, 'View '.$cprog->strSafeEnrollLabel.' record', true).'&nbsp;'           
  .strLinkView_CProgEnrollRec($lTableID, $lClientID, $lEnrollRecID, 'View '.$cprog->strSafeEnrollLabel.' record', false)
      .'<br><br>');
           
           
echoT(
   strLinkView_ClientRecord($lClientID, 'View client record', true).'&nbsp;'
  .strLinkView_ClientRecord($lClientID, 'View client record', false).'<br><br>');
           
/*
*/
