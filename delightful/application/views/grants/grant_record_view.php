<?php

   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '120pt';

   $attributes = new stdClass;
   $attributes->lTableWidth      = 1200;
   $attributes->lUnderscoreWidth = 400;
   $attributes->divID            = 'grantDiv';
   $attributes->divImageID       = 'grantDivImg';
   $attributes->bStartOpen       = false;
   $attributes->bAddTopBreak     = true;


   showGrant     ($clsRpt, $grant, $lGrantID);
   showProjects  ($clsRpt, $grant, $lGrantID, $attributes);
   showSchedule  ($clsRpt, $grant, $lGrantID, $attributes);
   showAwards    ($clsRpt, $grant, $lGrantID, $attributes);
   showResources ($clsRpt, $grant, $lGrantID, $attributes);
   showReporting ($clsRpt, $grant, $lGrantID, $attributes);

   showImageInfo            (CENUM_CONTEXT_GRANTS, $lGrantID, 'Grant Images',
                             $images, $lNumImages, $lNumImagesTot);
   showDocumentInfo         (CENUM_CONTEXT_GRANTS, $lGrantID, 'Grant Documents',
                             $docs, $lNumDocs, $lNumDocsTot);


   function showGrant($clsRpt, $grant, $lGrantID){
   //--------------------------------------------------
   //
   //--------------------------------------------------
      openBlock('Grant',
                 strLinkEdit_Grant($lGrantID, 'Edit Grant Record', true).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                .strLinkRem_Grant($lGrantID, 'Remove Grant Record', true, true)
                 );

      echoT(
          $clsRpt->openReport()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Grant ID:')
         .$clsRpt->writeCell(str_pad($lGrantID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Grant Name:')
         .$clsRpt->writeCell (htmlspecialchars($grant->strGrantName))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Accounting Country:')
         .$clsRpt->writeCell ($grant->strFlagImg)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Notes:')
         .$clsRpt->writeCell (nl2br(htmlspecialchars($grant->strNotes)))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Attributed to:')
         .$clsRpt->writeCell (htmlspecialchars($grant->strAttributedTo))
         .$clsRpt->closeRow  ());

      echoT($clsRpt->closeReport());
      closeBlock();
   }

   function showProjects($clsRpt, $grant, $lGrantID, $attributes){
   //--------------------------------------------------
   //
   //--------------------------------------------------
      $attributes->divID            = 'projectsDiv';
      $attributes->divImageID       = 'projectsDivImg';

      openBlock('Projects', '', $attributes);

      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showSchedule($clsRpt, $grant, $lGrantID, $attributes){
   //--------------------------------------------------
   //
   //--------------------------------------------------
      $attributes->divID            = 'scheduleDiv';
      $attributes->divImageID       = 'scheduleDivImg';

      openBlock('Schedule/Work Flow', '', $attributes);

      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showAwards($clsRpt, $grant, $lGrantID, $attributes){
   //--------------------------------------------------
   //
   //--------------------------------------------------
      $attributes->divID            = 'awardDiv';
      $attributes->divImageID       = 'awardDivImg';

      openBlock('Awards/Distributions', '', $attributes);

      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showResources($clsRpt, $grant, $lGrantID, $attributes){
   //--------------------------------------------------
   //
   //--------------------------------------------------
      $attributes->divID            = 'resourcesDiv';
      $attributes->divImageID       = 'resourcesDivImg';

      openBlock('Resources', '', $attributes);

      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

   function showReporting($clsRpt, $grant, $lGrantID, $attributes){
   //--------------------------------------------------
   //
   //--------------------------------------------------
      $attributes->divID            = 'reportingDiv';
      $attributes->divImageID       = 'reportingDivImg';

      openBlock('Reporting Requirements', '', $attributes);

      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

