<?php
   $bInactive = $sponRec->bInactive;
   $clsDateTime = new dl_date_time;

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = $strWidthLabel;   //'120pt';

   showSponInfo              ($clsRpt, $sponRec, $lSponID, $bInactive);
   showSponHonInfo           ($clsRpt, $lSponID, $sponRec, $bInactive);
   
   if (bAllowAccess('showClients')){
      showClientInfo            ($clsRpt, $lClientID, $clsClient, $clsDateTime, is_null($lClientID), $lSponID, true, 'Sponsored ');
   }
   if (bAllowAccess('showSponsorFinancials')){
      showSponFinancialInfo     ($clsRpt, $lSponID, $sponRec, $financialSummary);
   }
   showGroupInfo             ($lSponID, $sponRec->strSponSafeNameFL, $lNumGroups, $groupList,
                             $inGroups, $lCntGroupMembership,
                             CENUM_CONTEXT_SPONSORSHIP, 'spRecView');

   showCustomSponsorTableInfo($strPT, $lNumPTablesAvail);
   showImageInfo              (CENUM_CONTEXT_SPONSORSHIP, $lSponID, 'Sponsorship Images',
                               $images, $lNumImages, $lNumImagesTot);
   showDocumentInfo           (CENUM_CONTEXT_SPONSORSHIP, $lSponID, 'Sponsorship Documents',
                               $docs, $lNumDocs, $lNumDocsTot);
//   showReminderBlock         ($clsRem, $lSponID, CENUM_CONTEXT_SPONSORSHIP);
   showSponsorENPStats       ($clsRpt, $sponRec);

function showSponsorENPStats($clsRpt, $sponRec){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'sponENP';
   $attributes->divImageID   = 'sponENPDivImg';
   openBlock('Record Information', '', $attributes);
   echoT(
      $clsRpt->showRecordStats($sponRec->dteOrigin, 
                            $sponRec->ucstrFName.' '.$sponRec->ucstrLName, 
                            $sponRec->dteLastUpdate, 
                            $sponRec->ulstrFName.' '.$sponRec->ulstrLName, 
                            $clsRpt->strWidthLabel));
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showCustomSponsorTableInfo($strPT, $lNumPTablesAvail){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'sponCTable';
   $attributes->divImageID   = 'sponCTableDivImg';
   $attributes->bStartOpen   = false;
   openBlock('Personalized Tables <span style="font-size: 9pt;">('.$lNumPTablesAvail.')</span>', '', $attributes);

   echoT($strPT);
   
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showSponFinancialInfo(&$clsRpt, $lSponID, &$sponRec, &$financialSummary){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'sponFin';
   $attributes->divImageID   = 'sponFinDivImg';
   openBlock('Financials',
             strLinkView_SponsorFinancials($lSponID, 'View sponsorship financials', true).' '
            .strLinkView_SponsorFinancials($lSponID, 'View sponsorship financials', false),
            $attributes);
   echoT($financialSummary);
   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showSponHonInfo(&$clsRpt, $lSponID, &$sponRec, $bInactive){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if ($bInactive){
      $strLink = '';
   }else {
      if ($sponRec->bHonoree){
         $strLink = strLinkRem_SponsorHonoree($lSponID, 'Remove honoree', true, true).'&nbsp;'
                   .strLinkRem_SponsorHonoree($lSponID, 'Remove honoree', false, true);
      }else {
         $strLink = strLinkAdd_SponHonoree($lSponID, 'Add sponsorship honoree', true).'&nbsp;'
                   .strLinkAdd_SponHonoree($lSponID, 'Add sponsorship honoree', false);
      }
   }
   
   $attributes = new stdClass;
   $attributes->lTableWidth  = 900;
   $attributes->divID        = 'sponHon';
   $attributes->divImageID   = 'sponHonDivImg';
   
   openBlock('Honoree Info', $strLink, $attributes);

   if ($sponRec->bHonoree){
      $lHonFID = $sponRec->lHonoreeID;
      if ($sponRec->bHonBiz){
         $strLinkHon = strLinkView_Biz($lHonFID, 'View business record', true);
      }else {
         $strLinkHon = strLinkView_PeopleRecord($lHonFID, 'View people record', true);
      }
      echoT($clsRpt->openReport());
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Honoree:')
         .$clsRpt->writeCell ($strLinkHon.$sponRec->strHonSafeNameFL)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Address:')
         .$clsRpt->writeCell (
                   strBuildAddress(
                      $sponRec->strHonAddr1, $sponRec->strHonAddr2,   $sponRec->strHonCity,
                      $sponRec->strHonState, $sponRec->strHonCountry, $sponRec->strHonZip,
                      true))
         .$clsRpt->closeRow  ());
      echoT($clsRpt->closeReport(''));
   }else {
      echoT('<i>There is no honoree associated with this sponsorship</i>');
   }

   $attributes = new stdClass;
   $attributes->bCloseDiv = true;
   closeBlock($attributes);
}

function showSponInfo(&$clsRpt, &$sponRec, $lSponID, $bInactive){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   $strEditLink = $bInactive ? '' : strLinkEdit_SponRec($lSponID, 'Edit sponsorship', true);
   openBlock('Sponsorship Info',
              $strEditLink
             .'&nbsp;&nbsp;&nbsp;'
             .($bInactive ? '' : strLinkSpecial_SponDeactivate($lSponID, 'Terminate sponsorship', true))
             .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
             .strLinkRem_Sponsorship($lSponID, 'Delete sponsorship', true, true)

              );
   echoT(
       $clsRpt->openReport());

   echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Sponsor:')
         .$clsRpt->writeCell ('<b>'.$sponRec->strSponSafeNameFL.'</b>'.($sponRec->bSponBiz ? ' (business sponsorship)':''))
         .$clsRpt->closeRow  ()
         
         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Sponsor ID:')
         .$clsRpt->writeCell (str_pad($lSponID, 5, '0', STR_PAD_LEFT), '', '', 1, 1, 'id="sponID"')
         .$clsRpt->closeRow  ());

      //----------------------
      // sponsorship status
      //----------------------
   if ($sponRec->bInactive) {
      $strStatus = '<b>INACTIVE</b> as of '.date($genumDateFormat, $sponRec->dteInactive).'<br>'
                  .htmlspecialchars($sponRec->strSponTermType);
   }else {
      $strStatus = 'Active';
   }
   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Status:')
      .$clsRpt->writeCell ($strStatus)
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Sponsorship Program:')
      .$clsRpt->writeCell ($sponRec->strSponProgram)
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Monthly Commitment:')
      .$clsRpt->writeCell ($sponRec->strCommitACOCurSym.' '.number_format($sponRec->curCommitment, 2)
                          .' '.$sponRec->strCommitACOFlagImg)
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Start Date:')
      .$clsRpt->writeCell (date($genumDateFormat, $sponRec->dteStart))
      .$clsRpt->closeRow  ());

   echoT(
       $clsRpt->openRow   ()
      .$clsRpt->writeLabel('Attributed To:')
      .$clsRpt->writeCell (htmlspecialchars($sponRec->strAttributedTo))
      .$clsRpt->closeRow  ());

   echoT(
      $clsRpt->closeReport(''));

   closeBlock();
}

