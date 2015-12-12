<?php

   $params = array('enumStyle' => 'terse', 'crpt');
   $crpt = new generic_rpt($params);
   $crpt->strWidthLabel = '130pt';

   showClientProgram        ($crpt,  $lCProgID, $cprog);
   showProgramPermissions   ($cprog, $pdgroup,  $lCProgID);
   showCustomTableInfo      ($crpt,  $cprog,    true);
   showCustomTableInfo      ($crpt,  $cprog,    false);
   showClientProgramENPStats($crpt,  $cprog);

   function showFormValidation(&$crpt, $lCProgID, &$cprog){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

         // show group membership
      $attributes = new stdClass;
      $attributes->divID        = 'fvDiv';
      $attributes->divImageID   = 'fvDivImg';
      $attributes->bStartOpen   = true;
      openBlock('Form Validation', '', $attributes);
      echoT(
          $crpt->openReport());

      echoT($crpt->closeReport());

      $attributes->bCloseDiv      = true;
      closeBlock($attributes);
   }

   function showClientProgram(&$crpt, $lCProgID, &$cprog){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      openBlock('Client Program',
                    strLinkEdit_CProgram($lCProgID, 'Edit client program', true).'&nbsp;'
                   .strLinkEdit_CProgram($lCProgID, 'Edit client program', false).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                   .strLinkRem_ClientProgram($lCProgID, 'Remove client program', true, true)
                   );

      echoT(
          $crpt->openReport()

         .$crpt->openRow   ()
         .$crpt->writeLabel('Client Program ID:')
         .$crpt->writeCell (str_pad($lCProgID, 5, '0', STR_PAD_LEFT))
         .$crpt->closeRow  ()

         .$crpt->openRow   ()
         .$crpt->writeLabel('Program Name:')
         .$crpt->writeCell (htmlspecialchars($cprog->strProgramName))
         .$crpt->closeRow  ());

      if ($cprog->bMentorMentee){
         echoT(
             $crpt->openRow   ()
            .$crpt->writeLabel('Program Type:')
            .$crpt->writeCell ('Mentor/Mentee')
            .$crpt->closeRow  ());
      }

         //------------------
         // Enrollment Label
         //------------------
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Enrollment Label:')
         .$crpt->writeCell (htmlspecialchars($cprog->strEnrollmentLabel))
         .$crpt->closeRow  ());

         //------------------
         // Attendance Label
         //------------------
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Attendance Label:')
         .$crpt->writeCell (htmlspecialchars($cprog->strAttendanceLabel))
         .$crpt->closeRow  ());

         //------------------
         // description
         //------------------
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Description:')
         .$crpt->writeCell (nl2br(htmlspecialchars($cprog->strDescription)))
         .$crpt->closeRow  ());

         //------------------
         // enrollment date
         //------------------
      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Duration:')
         .$crpt->writeCell (date($genumDateFormat, $cprog->dteStart).' - '.date($genumDateFormat, $cprog->dteEnd))
         .$crpt->closeRow  ());

      echoT($crpt->closeReport());

      closeBlock();
   }

   function showProgramPermissions(&$cprog, &$pdgroup, $lCProgID){
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
      $opts->strSafeName = htmlspecialchars($cprog->strProgramName);
      $opts->lCntGroupMembership = $pdgroup->lCntGroupMembership;
      $opts->inGroups     = &$pdgroup->inGroups;
      $opts->enumGType    = CENUM_CONTEXT_USER;
      $opts->enumSubGroup = CENUM_CONTEXT_CPROGRAM;
      $opts->lFID         = $lCProgID;
      $opts->strFrom      = 'clientProgramRecView';
      $opts->lNumGroups   = $pdgroup->lNumGroups;
      $opts->groupList    = &$pdgroup->groupList;
      $opts->postText     = '<b>Note:</b> client program records that belong to no user groups are<br>
                                  accessible to admins and all users.<br>';
      if ($pdgroup->lCntGroupMembership==0){
         echoT('<i>Admins and all users can access this table.</i><br>'."\n");
      }else {
         echoT('<i>Admins and users who are members of <b>ALL</b> the following groups can access this table:</i><br>'."\n");
         echoT(strGroupMembership($opts));
      }
      echoT(strDDLAvailableGroups($opts));

      $attributes->bCloseDiv      = true;
      closeBlock($attributes);
   }

   function showCustomTableInfo(&$crpt, &$cprog, $bEnrollment){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth      = 900;
      $attributes->lUnderscoreWidth = 300;
      $attributes->divID            = 'group'.($bEnrollment ? 'E' : 'A').'Div';
      $attributes->divImageID       = 'group'.($bEnrollment ? 'E' : 'A').'DivImg';
      $attributes->bStartOpen       = false;

      openBlock(($bEnrollment ? 'Enrollment' : 'Attendance').' Table', '', $attributes);

      if ($bEnrollment){
         $strNumFields = $cprog->lNumEFields.'&nbsp;'
                      .strLinkView_UFFields($cprog->lEnrollmentTableID, 'View enrollment fields', true);
         $bReadOnly = $cprog->bETableReadOnly;
      }else {
         $strNumFields = $cprog->lNumAFields.'&nbsp;'
                      .strLinkView_UFFields($cprog->lAttendanceTableID, 'View attendance fields', true);
         $bReadOnly = $cprog->bATableReadOnly;
      }


      echoT(
          $crpt->openReport());

      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('# Fields:')
         .$crpt->writeCell ($strNumFields)
         .$crpt->closeRow  ());

      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Read Only?')
         .$crpt->writeCell ($bReadOnly ? 'Yes' : 'No')
         .$crpt->closeRow  ());

      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Verification File:')
         .$crpt->writeCell (htmlspecialchars(($bEnrollment ? $cprog->strE_VerificationModule : $cprog->strA_VerificationModule)))
         .$crpt->closeRow  ());

      echoT(
          $crpt->openRow   ()
         .$crpt->writeLabel('Entry Point:')
         .$crpt->writeCell (htmlspecialchars(($bEnrollment ? $cprog->strE_VModEntryPoint : $cprog->strA_VModEntryPoint)))
         .$crpt->closeRow  ());

      echoT($crpt->closeReport());

      $attributes->bCloseDiv = true;
      closeBlock($attributes);

   }

   function showClientProgramENPStats(&$crpt, &$cprog){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth  = 900;
      $attributes->divID        = 'clientENP';
      $attributes->divImageID   = 'clientENPDivImg';
      openBlock('Record Information', '', $attributes);
      echoT(
         $crpt->showRecordStats($cprog->dteOrigin,
                                $cprog->ucstrFName.' '.$cprog->ucstrLName,
                                $cprog->dteLastUpdate,
                                $cprog->ulstrFName.' '.$cprog->ulstrLName,
                                $crpt->strWidthLabel));
      $attributes = new stdClass;
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }


