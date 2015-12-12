<?php

//   $clsDateTime = new dl_date_time;
   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '90pt';
   $clsRpt->bValueEscapeHTML = false;

   $lBID = $biz->lKeyID;

   $lIndentWidth = 20;

   showBizInfo                ($clsRpt, $lBID, $biz);
   showSponsorshipInfo        ($clsRpt, $sponInfo, $lNumSponsors, $lBID, $biz);
   showBizContactInfo         ($clsRpt, $lBID, $biz, $lNumContacts, $contacts);
   
   showGroupInfo              ($lBID, $biz->strSafeName, $lNumGroups, $groupList,
                               $inGroups, $lCntGroupMembership,
                               CENUM_CONTEXT_BIZ, 'bRecView');
   
   
   showCustomBizTableInfo     ($strPT, $lNumPTablesAvail);

   if (bAllowAccess('showFinancials')){
      showGifts               ($lBID, true, 
                               $strCumGiftsNonSoftMon, $strCumGiftsNonSoftInKind,
                               $strCumGiftsSoft,       $strCumSpon, $lNumPledges,
                               $lTotHard, $lTotSoft, $lTotInKind, $lNumSponPay);
   }                               

   showImageInfo              (CENUM_CONTEXT_BIZ, $lBID, ' Images',
                               $images, $lNumImages, $lNumImagesTot);
//   showReminderBlock          ($clsRem, $lBID, CENUM_CONTEXT_BIZ);
   showDocumentInfo           (CENUM_CONTEXT_BIZ, $lBID, ' Documents',
                               $docs, $lNumDocs, $lNumDocsTot);                               
                               
   showBizENPStats            ($clsRpt, $biz);

function showBizENPStats($clsRpt, $biz){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'bizENPStats';
   $attributes->divImageID   = 'bizENPStatsDivImg';
   openBlock('Record Information', '', $attributes);
   echoT(
      $clsRpt->showRecordStats($biz->dteOrigin, 
                            $biz->strStaffCFName.' '.$biz->strStaffCLName, 
                            $biz->dteLastUpdate, 
                            $biz->strStaffLFName.' '.$biz->strStaffLLName, 
                            $clsRpt->strWidthLabel));
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}   
   
function showCustomBizTableInfo($strPT, $lNumPTablesAvail){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'bizPer';
   $attributes->divImageID   = 'bizPerDivImg';
   $attributes->bStartOpen   = false;
   openBlock('Personalized Tables <span style="font-size: 9pt;">('.$lNumPTablesAvail.')</span>', '', $attributes);
   
   echoT($strPT);
      
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}
      
function showBizContactInfo($clsRpt, $lBID, $clsBiz, $lNumContacts, &$contacts){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'bizCon';
   $attributes->divImageID   = 'bizConDivImg';
   openBlock('Associated Contacts <span style="font-size: 9pt;"> ('.$lNumContacts.')</span>',
                strLinkView_BizContacts($lBID, 'View contacts', true,  '').' '
               .strLinkView_BizContacts($lBID, 'View', false, '').'&nbsp;&nbsp;&nbsp;&nbsp;'
               .strLinkAdd_BizContact  ($lBID, 'Add new business contact', true, '').'&nbsp;'
               .strLinkAdd_BizContact  ($lBID, 'Add new contact', false, ''), $attributes);
   
   if ($lNumContacts == 0){
      echoT('<i>There are no contacts associated with this business/organization</i><br>');
   }else {
      echoT("\n\n".$clsRpt->openReport());
      
      foreach ($contacts as $clsCon){
         $lContactID = $clsCon->lBizConRecID;
         $lPID       = $clsCon->lPeopleID;
         echoT(
             $clsRpt->openRow()
            .$clsRpt->writeCell(strLinkView_PeopleRecord($clsCon->lPeopleID, 'View people record', true))
            .$clsRpt->writeCell(strLinkEdit_BizContact($lBID, $lContactID, $lPID, 'Edit business contact', true))
            .$clsRpt->writeCell(strLinkRem_BizContact($lBID, $lContactID, 'Remove business contact', true, true))
            .$clsRpt->writeCell($clsCon->strSafeNameLF));
         if ($clsCon->strRelationship.'' == ''){
            echoT($clsRpt->writeCell('&nbsp;'));
         }else {
            echoT($clsRpt->writeCell('('.htmlspecialchars($clsCon->strRelationship).')'));
         }
         if ($clsCon->bSoftCash){
            echoT($clsRpt->writeCell('<img src="'.DL_IMAGEPATH.'/misc/dollar.gif" border="0" title="soft cash relationship">'));
         }else {
            echoT($clsRpt->writeCell('&nbsp;'));
         }
         echoT($clsRpt->closeRow());
      }
      echoT($clsRpt->closeReport());

   }
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showBizInfo($clsRpt, $lBID, &$clsBiz){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gbDateFormatUS, $glclsDTDateFormat;

   $clsDateTime = new dl_date_time;

   echoT(strLinkAdd_Biz('Add new business/organization', true).'&nbsp;'
        .strLinkAdd_Biz('Add new business/organization', false).'<br>');

   openBlock('Business',
                strLinkEdit_Biz($lBID, 'Edit business/organization information', true)
               .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
               .strLinkRem_Biz($lBID, 'Remove this business record', true, true));

   echoT(
       $clsRpt->openReport()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Business ID:')
      .$clsRpt->writeCell (str_pad($lBID, 5, '0', STR_PAD_LEFT), '', '', 1, 1, ' id="bID" ')
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Name:')
      .$clsRpt->writeCell ($clsBiz->strSafeName, '', '', 1, 1, ' id="safeBizName" ')
      .$clsRpt->closeRow  ()
      
      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Category:')
      .$clsRpt->writeCell ($clsBiz->strIndustry)
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('Address:')
      .$clsRpt->writeCell (strBuildAddress(
                              $clsBiz->strAddr1, $clsBiz->strAddr2,   $clsBiz->strCity,
                              $clsBiz->strState, $clsBiz->strCountry, $clsBiz->strZip,
                              true,              true))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Phone:')
      .$clsRpt->writeCell (htmlspecialchars(strPhoneCell($clsBiz->strPhone, $clsBiz->strCell)), '', '', 1, 1, ' id="phone" ')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Fax:')
      .$clsRpt->writeCell (htmlspecialchars($clsBiz->strFax), '', '', 1, 1, ' id="fax" ')
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Web Site:')
      .$clsRpt->writeCell (htmlspecialchars($clsBiz->strWebSite))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Email:')
      .$clsRpt->writeCell ($clsBiz->strEmailFormatted)
      .$clsRpt->closeRow  ()

      .$clsRpt->openRow   ()
      .$clsRpt->writeLabel('ACO:')
      .$clsRpt->writeCell ($clsBiz->strFlagImage.' '.$clsBiz->strCurSymbol)
      .$clsRpt->closeRow  ());
      
         //------------------
         // Notes
         //------------------
      echoT(
             $clsRpt->openRow   ()
            .$clsRpt->writeLabel('Notes:')
            .$clsRpt->writeCell (nl2br(htmlspecialchars($clsBiz->strNotes)))
            .$clsRpt->closeRow  ());    
      
         //------------------
         // Attributed To
         //------------------
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Attributed To:')
         .$clsRpt->writeCell (htmlspecialchars($clsBiz->strAttrib))
         .$clsRpt->closeRow  ());

      echoT(
         $clsRpt->closeReport());

   closeBlock();
}















