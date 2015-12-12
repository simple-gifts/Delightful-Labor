<?php

   $params = array('enumStyle' => 'terse', 'crpt');
   $crpt = new generic_rpt($params);
   $crpt->strWidthLabel = '130pt';

   showPrePostTest  ($crpt, $lPPTestID, $pptest, $lNumQuests);
   showPrePostPermissions($pptest, $pdgroup, $lPPTestID);
   showPrePostENPStats($crpt, $pptest);

   function showPrePostTest(&$crpt, $lPPTestID, &$pptest, $lNumQuests){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bPublished = $pptest->bPublished;

      if ($bPublished){
         $strLinkEdit = '';
         $strLinkAddQues = '';
      }else {
         $strLinkEdit =
                    strLinkEdit_CPPTest($lPPTestID, 'Edit pre/post test', true).'&nbsp;'
                   .strLinkEdit_CPPTest($lPPTestID, 'Edit pre/post test', false).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                   .strLinkRem_CPPTest ($lPPTestID, 'Remove pre/post test', true, true);
         $strLinkAddQues = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                    .strLinkAdd_CPPQuestion($lPPTestID, 'Add question', true).'&nbsp;'
                    .strLinkAdd_CPPQuestion($lPPTestID, 'Add question', false);
      }

      if ($lNumQuests > 0){
         $strLinkViewQuest = strLinkView_CPPQuestions($lPPTestID, 'View questions associated with this test', true);
      }else {
         $strLinkViewQuest = '';
      }

      openBlock('Client Pre/Post Test', $strLinkEdit);

      echoT(
          $crpt->openReport()

         .$crpt->openRow   ()
         .$crpt->writeLabel('Pre/Post Test ID:')
         .$crpt->writeCell (str_pad($lPPTestID, 5, '0', STR_PAD_LEFT))
         .$crpt->closeRow  ()

         .$crpt->openRow   ()
         .$crpt->writeLabel('Category:')
         .$crpt->writeCell (htmlspecialchars($pptest->strPPTestCat))
         .$crpt->closeRow  ()

         .$crpt->openRow   ()
         .$crpt->writeLabel('Test Name:')
         .$crpt->writeCell (htmlspecialchars($pptest->strTestName))
         .$crpt->closeRow  ());

         //------------------
         // description
         //------------------
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Description:')
         .$crpt->writeCell (nl2br(htmlspecialchars($pptest->strDescription)))
         .$crpt->closeRow  ());

         //------------------
         // # of Questions
         //------------------
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('# of Questions:')
         .$crpt->writeCell ($lNumQuests.$strLinkViewQuest.$strLinkAddQues)
         .$crpt->closeRow  ());

         //------------------
         // Visibility
         //------------------
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Visibility:')
         .$crpt->writeCell (($pptest->bHidden ? 'Hidden' : 'Visible').'&nbsp;'
                          .strSpecial_CPPHideShow($lPPTestID, !$pptest->bHidden, true))
         .$crpt->closeRow  ());

         //------------------
         // Published?
         //------------------
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Published?:')
         .$crpt->writeCell (($pptest->bPublished ? 'Yes' : 'No'))
         .$crpt->closeRow  ());

      echoT($crpt->closeReport());

      closeBlock();
   }


   function showPrePostPermissions(&$pptest, &$pdgroup, $lPPTestID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // show group membership
      $attributes = new stdClass;
      $attributes->divID        = 'groupDiv';
      $attributes->divImageID   = 'groupDivImg';
      $attributes->bStartOpen   = true;
      openBlock('Permissions Groups', '', $attributes);

      $opts = new stdClass;
      $opts->strSafeName = htmlspecialchars($pptest->strTestName);
      $opts->lCntGroupMembership = $pdgroup->lCntGroupMembership;
      $opts->inGroups     = &$pdgroup->inGroups;
      $opts->enumGType    = CENUM_CONTEXT_USER;
      $opts->enumSubGroup = CENUM_CONTEXT_CPREPOST;
      $opts->lFID         = $lPPTestID;
      $opts->strFrom      = 'clientPrePostRecView';
      $opts->lNumGroups   = $pdgroup->lNumGroups;
      $opts->groupList    = &$pdgroup->groupList;
      $opts->postText     = '<b>Note:</b> pre/post test records that belong to no user groups are<br>
                                  accessible to admins and all users.<br>';
      if ($pdgroup->lCntGroupMembership==0){
         echoT('<i>Admins and all users can access this pre/post test.</i><br>'."\n");
      }else {
         echoT('<i>Admins and users who are members of <b>ALL</b> the following groups can access this pre/post test:</i><br>'."\n");
         echoT(strGroupMembership($opts));
      }
      echoT(strDDLAvailableGroups($opts));

      $attributes->bCloseDiv      = true;
      closeBlock($attributes);
   }

   function showPrePostENPStats(&$crpt, &$pptest){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'clientENP';
      $attributes->divImageID   = 'clientENPDivImg';
      openBlock('Record Information', '', $attributes);
      echoT(
         $crpt->showRecordStats($pptest->dteOrigin,
                                $pptest->ucstrFName.' '.$pptest->ucstrLName,
                                $pptest->dteLastUpdate,
                                $pptest->ulstrFName.' '.$pptest->ulstrLName,
                                $crpt->strWidthLabel));
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }


