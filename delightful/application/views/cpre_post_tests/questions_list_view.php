<?php

   echoT($strTestSummary.'<br>');
   
   $cUpDown = new up_down_top_bottom;
   $cUpDown->lMax = $lNumQuestions;
   
   
   $bPublished = $pptest->bPublished;
   $lPPTestID  = $pptest->lKeyID;

   if (!$bPublished){
      echoT(strLinkAdd_CPPQuestion($lPPTestID, 'Add question', true).'&nbsp;'
           .strLinkAdd_CPPQuestion($lPPTestID, 'Add question', false).'<br><br>');
   }

   if ($lNumQuestions == 0){
      echoT('<br><i>There are no questions defined for this pre/post test.</i></br><br>');
      return;
   }

   $strLinkBase = '<a href="'.base_url()."index.php/cpre_post_tests/ppquest_add_edit/moveQuest/$lPPTestID/";
   
   openQuestionTable();
   $idx = 0;
   foreach ($quests as $quest){
      writeQuestionRow($quest, $bPublished, $lPPTestID, $cUpDown, $strLinkBase, $idx);
      ++$idx;
   }
   closeQuestionTable();

   function writeQuestionRow(&$quest, $bPublished, $lPPTestID, &$cUpDown, $strLinkBase, $idx){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      $lQuestID = $quest->lKeyID;
      
      if ($bPublished){
         $strLinkEdit   = '';
         $strLinkRemove = '';
         $strOrderLinks = 'n/a';         
      }else {
         $strLinkEdit   = '&nbsp;'.strLinkEdit_CPPQuestion($lQuestID, $lPPTestID, 'Edit question', true);
         $strLinkRemove = '&nbsp;'.strLinkRem_CPPQuestion($lQuestID, $lPPTestID, 'Remove question', true, true);
         $cUpDown->strLinkBase = $strLinkBase.$lQuestID.'/';
         $cUpDown->upDownLinks($idx);
         $strOrderLinks =             
             $cUpDown->strUp
            .$cUpDown->strDown.'&nbsp;&nbsp;&nbsp;&nbsp;'
            .$cUpDown->strTop
            .$cUpDown->strBottom;
      }
      echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;">'
                  .($idx+1).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .str_pad($lQuestID, 5, '0', STR_PAD_LEFT).$strLinkEdit.'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .$strLinkRemove.'
               </td>
               <td class="enpRpt" style="width: 330pt;">'
                  .'<b>Q:</b> '.nl2br(htmlspecialchars($quest->strQuestion)).'<br><br>'
                  .'<b>A:</b> <i>'.nl2br(htmlspecialchars($quest->strAnswer)).'</i>
               </td>
               <td class="enpRpt">'
                  .$strOrderLinks.'
               </td>
            </tr>');
   }

   function openQuestionTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------  
      echoT('
         <table class="enpRptC" style="width: 500pt;">
            <tr>
               <td class="enpRptTitle" colspan="5">
                  Pre/Post Tests Questions
               </td>
            </tr>');
      echoT('
            <tr>
               <td class="enpRptLabel" style="width: 10pt;">
                  &nbsp;
               </td>
               <td class="enpRptLabel" style="width: 50pt;">
                  &nbsp;
               </td>
               <td class="enpRptLabel" style="width: 10pt;">
                  &nbsp;
               </td>
               <td class="enpRptLabel" style="width: 330pt;">
                  Question / Answer
               </td>
               <td class="enpRptLabel">
                  Order
               </td>
            </tr>');
   }


   function closeQuestionTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      echoT('
         </table><br>');
   }

   
   
   