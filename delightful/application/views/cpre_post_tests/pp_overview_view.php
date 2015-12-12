<?php

//   echoT(strLinkAdd_CPPTest('Add new pre/post test', true).'&nbsp;'
//        .strLinkAdd_CPPTest('Add new pre/post test', false).'<br><br>');

   echoT('<h1>Client Pre/Post Tests</h1>');

   if ($lNumCats == 0){
      echoT('<i>There are no Pre/Post Categories defined in your database.<br><br></i>');
      return;
   }
  
  
   $attributes = new stdClass;
   $attributes->lTableWidth      = 900;
   $attributes->lUnderscoreWidth = 550;
   $attributes->bStartOpen       = false;
   $attributes->bAddTopBreak     = true;
   foreach ($ppcats as $ppcat){
      $lCatID = $ppcat->lKeyID;
      $attributes->divID      = 'groupDiv'.$lCatID;
      $attributes->divImageID = 'groupDivImg'.$lCatID;
      $attributes->bCloseDiv  = false;
   
      openBlock(htmlspecialchars($ppcat->strListItem), 
               strLinkAdd_CPPTest($lCatID, 'Add new pre/post test', true).'&nbsp;'
              .strLinkAdd_CPPTest($lCatID, 'Add new pre/post test', false),
              $attributes);
      
      if ($ppcat->lNumPPTests == 0){
         echoT('<i>No tests defined for this category</i>');
      }else {
         openPrePostTable();
         foreach ($ppcat->pptests as $pptest){
            writePrePostRow($pptest);
         }
         closePrePostTable();
      }
      
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$ppcats   <pre>');
echo(htmlspecialchars( print_r($ppcats, true))); echo('</pre></font><br>');
// -------------------------------------*/
/*
   openPrePostTable();
   foreach ($pptests as $pptest){
      writePrePostRow($pptest);
   }
   closePrePostTable();
*/
   
   function writePrePostRow($pptest){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lPPTestID = $pptest->lKeyID;
      $bHidden = $pptest->bHidden;
      $bPublished = $pptest->bPublished;
      
      if ($bHidden){
         $strHideStyle = 'color: #999; font-style: italic;';
      }else {
         $strHideStyle = '';
      }
      echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;'.$strHideStyle.'" nowrap>'
                  .str_pad($lPPTestID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_CPPTestView($lPPTestID, 'View pre/post test record', true).'
               </td>
               <td class="enpRpt" style="width: 130pt;'.$strHideStyle.'">'
                  .htmlspecialchars($pptest->strTestName).'
               </td>
               <td class="enpRpt" style="width: 190pt;'.$strHideStyle.'">'
                  .nl2br(htmlspecialchars($pptest->strDescription)).'
               </td>
               <td class="enpRpt" style="text-align: center;'.$strHideStyle.'">'
                  .$pptest->lNumQuest.'&nbsp;'
                  .strLinkView_CPPQuestions($lPPTestID, 'View Questions', true).'
               </td>
               <td class="enpRpt" nowrap style="'.$strHideStyle.'">
                  Visibility: '.($bHidden ? 'Hidden' : 'Visible').'<br>
                  Published: '.($bPublished ? 'Yes' : 'No').'<br>
               </td>
            </tr>');

   }

   function openPrePostTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         <table class="enpRptC" style="width: 570pt;">
            <tr>
               <td class="enpRptTitle" colspan="6">
                  Pre/Post Tests
               </td>
            </tr>');
      echoT('
            <tr>
               <td class="enpRptLabel" style="width: 50pt;">
                  &nbsp;
               </td>
               <td class="enpRptLabel" style="width: 130pt;">
                  Name
               </td>
               <td class="enpRptLabel" style="width: 190pt;">
                  Description
               </td>
               <td class="enpRptLabel">
                  Questions
               </td>
               <td class="enpRptLabel">
                  Status
               </td>
            </tr>');
   }

   function closePrePostTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('
         </table><br>');
   }
