<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('personalization/ptable');
---------------------------------------------------------------------
---------------------------------------------------------------------*/

function strPTableDisplay($enumType, $lFID, &$clsUFD, &$perms,
                      &$acctAccess, &$strAlert, &$lNumTablesAvail){
//---------------------------------------------------------------------
//  return a string that contains the standard record personalized
//  table display. The user must have previously called:
//
//      $this->perms->loadUserAcctInfo($glUserID, $acctAccess);
//---------------------------------------------------------------------
   $strAlert = '';

   $clsUFD->setTType($enumType);
   $clsUFD->loadTablesViaTType();
   $clsUFD->lForeignID = $lFID;
   $clsUFD->lFieldSetWidth = 386;
   $clsUFD->lFieldValWidth = 380;
   $clsUFD->strViewAllLogLinkBase = 'admin/uf_log/viewAll/';

   $lNumTablesAvail = 0;
   $strPT = '';
   if ($clsUFD->lNumTables > 0){
      foreach ($clsUFD->userTables as $utable){
         $clsUFD->lDispTableID = $lTableID = $utable->lKeyID;
         if ($perms->bDoesUserHaveAccess($acctAccess, $utable->lNumConsolidated, $utable->cperms)){
            $strPT .= $clsUFD->strDiplayUserTable($utable);
            ++$lNumTablesAvail;

               // alert if no data entry?
            if ($utable->bAlertIfNoEntry){
               if (!$clsUFD->bUFDataEntered($lTableID, $utable->bMultiEntry, $lFID)){
                  $strAlert .= $utable->strAlertMsg.'<br>';
               }
            }
         }
      }
   }

   if ($lNumTablesAvail == 0) {
      $strPT .= '<i>No personalized tables are available.</i><br>';
   }
   return($strPT);
}


