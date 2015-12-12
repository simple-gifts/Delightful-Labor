<?php

   echoT('<br>
      <table>
         <tr>
            <td><b>
               Total Images:</b>
            </td>
            <td style="width: 30pt; text-align: right;">'
               .number_format($lTotImages).'
            </td>
         </tr>
         <tr>
            <td><b>
               Total Documents:</b>
            </td>
            <td style="width: 30pt; text-align: right;">'
               .number_format($lTotDocs).'
            </td>
         </tr>
      </table>');

   imgDocsViaContext('Images, by Context',    'images',    'img', $lNumImgContextGroups, $contextImgGroups);
   imgDocsViaContext('Documents, by Context', 'documents', 'doc', $lNumDocContextGroups, $contextDocGroups);

   imgDocsViaTags('Images, by Tags',    'image',    'imgtag', $tagsImage);
   imgDocsViaTags('Documents, by Tags', 'document', 'doctag', $tagsDoc);


   function imgDocsViaTags($strTitle, $strLabel, $strDivGroup, $tags){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth      = 900;
      $attributes->lUnderscoreWidth = 400;
      $attributes->divID            = $strDivGroup;
      $attributes->divImageID       = $strDivGroup.'Img';
      $attributes->bStartOpen       = true;
      $attributes->bAddTopBreak     = true;

      openBlock($strTitle, '', $attributes);

      echoT('<ul style="list-style-type: none; margin-left: -40px;">'."\n");
      foreach ($tags as $tblock){
         $enumContext = imgDocTags\xlateContextViaTagType($tblock->econtext);

         echoT('<li><b>'.imgDocTags\strXlateImgDocContext($tblock->econtext).'</b>'."\n");
         if ($tblock->lNumTags == 0){
            echoT('<ul style="margin-bottom: 10px;"><li><i>No tags defined</i></ul>');
         }else {
            echoT('<ul style="margin-bottom: 10px;">'."\n");
            foreach ($tblock->tags as $tag){
               if ($tag->lCnt > 0){
                  $strLink = strLinkView_ImageDocsViaTag($tag->lTagID, $enumContext, 'View', true);
               }else {
                  $strLink = '&nbsp;&nbsp;';
               }
               echoT('<li>'.$strLink.htmlspecialchars($tag->strDDLEntry)
                  .' <i>(used by '.number_format($tag->lCnt).' '.$strLabel.($tag->lCnt == 1 ? '' : 's')
                  .') </i>'
                  .'</li>'."\n");
            }
            echoT('</ul>'."\n");
         }
         echoT('</li>'."\n");
      }
      echoT('</ul>'."\n");

      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }


   function imgDocsViaContext($strTitle, $strLabel, $strDivGroup, $lNumContextGroups, $contextGroups){
   //---------------------------------------------------------------------
   // images/docs by context
   //---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->lTableWidth      = 900;
      $attributes->lUnderscoreWidth = 400;
      $attributes->divID            = $strDivGroup;
      $attributes->divImageID       = $strDivGroup.'Img';
      $attributes->bStartOpen       = true;
      $attributes->bAddTopBreak     = true;

      openBlock($strTitle, '', $attributes);

      if ($lNumContextGroups == 0){
         echoT('<i>There are no '.$strLabel.' that meet your search criteria.</i>');
      }else {
         echoT('<table>');
         foreach ($contextGroups as $cg){
            echoT('
                  <tr>
                     <td style="width: 100pt;"><b>'
                        .$cg->strGroup.'</b>
                     </td>
                     <td style="width: 30pt; text-align: right;">'
                        .number_format($cg->lNumRecs).'
                     </td>
                  </tr>');
         }
         echoT('</table>');
      }


      $attributes->bCloseDiv = true;
      closeBlock($attributes);
   }

