<?php

   if (!bAllowAccess('adminOnly')) return('');
   
   echoT('<br>'
     .strLinkAdd_TimeSheetProject('Add time sheet project', true).'&nbsp;'
     .strLinkAdd_TimeSheetProject('Add time sheet project', false).'<br>');

   if ($lNumProjects <= 0){
      echoT('<br><i>There are no time sheet projects defined in your database.</i><br><br>');
      return;
   }
   
   openTSProjectsTable();
   foreach ($projects as $proj){
      writeProjectRow($proj);
   }
   closeTSProjectsTable();
   
   function writeProjectRow($proj){
   //---------------------------------------------------------------------
   //
   //--------------------------------------------------------------------- 
      $lProjectID = $proj->lKeyID;   
      echoT('
            <tr class="makeStripe">
               <td class="enpRpt" style="width: 50pt; text-align: center;">'
                  .strLinkEdit_TimeSheetProject($lProjectID, 'Edit project', true).'&nbsp;'
                  .str_pad($lProjectID, 5, '0', STR_PAD_LEFT).'
               </td>
               <td class="enpRpt" style="width: 20pt; text-align: center;">'
                  .strLinkRem_TimeSheetProject($lProjectID, 'Remove project', true, true).' 
               </td>
               <td class="enpRpt" style="width: 200pt;">'
                  .htmlspecialchars($proj->strProjectName).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .($proj->bInternalProject ? 'Yes' : 'No').'
               </td>
            </tr>');
   }
   
   function openTSProjectsTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------   
      echoT('
         <table class="enpRptC" style="width: 300pt;">
            <tr>
               <td class="enpRptLabel" style="width: 50pt;">
                  Project ID
               </td>
               <td class="enpRptLabel" style="width: 20pt;">
                  &nbsp;
               </td>
               <td class="enpRptLabel" style="width: 200pt;">
                  Project Name
               </td>
               <td class="enpRptLabel" style="">
                  Internal?
               </td>
            </tr>');
   }
   
   function closeTSProjectsTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT('</table><br><br>');   
   }
   
   
   
   
   
   
   