<?php
/*-----------------------------------------------------------------------------
// copyright (c) 2014 by Database Austin.
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
-----------------------------------------------------------------------------
      $this->load->helper('img_docs/image_doc');
-----------------------------------------------------------------------------*/

function loadImgDocRecView(&$displayData, $enumContextType, $lFID){
//---------------------------------------------------------------------
// load the tables necessary to display record-view images and docs
//---------------------------------------------------------------------
   $CI =& get_instance();
   $CI->clsImgDoc->sqlLimit = ' LIMIT 0, 3 ';
   $CI->clsImgDoc->loadDocImageInfoViaEntryContextFID(CENUM_IMGDOC_ENTRY_IMAGE, $enumContextType, $lFID);
   $displayData['images']        = $CI->clsImgDoc->imageDocs;
   $displayData['lNumImages']    = $CI->clsImgDoc->lNumImageDocs;
   $displayData['lNumImagesTot'] = $CI->clsImgDoc->lNumImageDocsViaEntryContextFID(CENUM_IMGDOC_ENTRY_IMAGE, $enumContextType, $lFID);
   if ($CI->clsImgDoc->lNumImageDocs > 0){
      foreach ($displayData['images'] as $img){
         $img->strTagsUL = $CI->cidTags->strImgDocTagsUL($img->lKeyID);
      }
   }


   $CI->clsImgDoc->loadDocImageInfoViaEntryContextFID(CENUM_IMGDOC_ENTRY_PDF, $enumContextType, $lFID);
   $displayData['docs']          = $CI->clsImgDoc->imageDocs;
   $displayData['lNumDocs']      = $CI->clsImgDoc->lNumImageDocs;
   $displayData['lNumDocsTot']   = $CI->clsImgDoc->lNumImageDocsViaEntryContextFID(CENUM_IMGDOC_ENTRY_PDF, $enumContextType, $lFID);
   if ($CI->clsImgDoc->lNumImageDocs > 0){
      foreach ($displayData['docs'] as $doc){
         $doc->strTagsUL = $CI->cidTags->strImgDocTagsUL($doc->lKeyID);
      }
   }
}

function strCatalogFID_Directory($lFID){
   $strFID = str_pad($lFID, 2, '0', STR_PAD_LEFT);
   return(substr($strFID, (strlen($strFID)-2)));
}

function strImageDocURLPath($enumContextType, $enumEntryType, $lFID){
//---------------------------------------------------------------------
//  no path testing - use $clsImgDoc->strCatalogPath($enumContextType, $enumEntryType, $lFID)
//  to create the path
//---------------------------------------------------------------------
   return(DL_CATALOGPATH.'/'.$enumContextType.'/'.$enumEntryType.'/'.strCatalogFID_Directory($lFID));
}

function strImageHTMLTag($enumContextType, $enumEntryType, $lFID, $strFN,
                         $strTitle='', $bBorder=false, $strImgTagExtra=''){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   return('<img src="'.strImageDocURLPath($enumContextType, $enumEntryType, $lFID).'/'.$strFN.'" '
           .'title="'.$strTitle.'" border="'.($bBorder ? '1' : '0').'" '.$strImgTagExtra.'>'
          );
}

function strLinkHTMLTag($enumContextType, $enumEntryType, $lFID, $strFN,
                         $strTitle='', $bNewWindow=true, $strLinkTagExtra=''){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   return('<a href="'.strImageDocURLPath($enumContextType, $enumEntryType, $lFID).'/'.$strFN.'" '
           .'title="'.$strTitle.'" '.($bNewWindow ? ' target="_blank" ' : '').$strLinkTagExtra.'>'
          );
}


function strImageDocTable($enumEntryType, $enumContext,   $lFID,
                          &$imageDocs,    $lNumImageDocs, $lTableWidth){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $genumDateFormat;

   $strOut = '';
   $bImage = $enumEntryType == CENUM_IMGDOC_ENTRY_IMAGE;
   $bPDF   = $enumEntryType == CENUM_IMGDOC_ENTRY_PDF;

   $lImageCellWidth = $bImage ? (CI_IMGDOC_THUMB_MAXDIMENSION+4) : 40;

   if ($bPDF){
      $strImageLink = '<img src="'.DL_IMAGEPATH.'/misc/pdfIcon.png" border="0" title="Open PDF document in new window">';
   }else {
      $strImageLink = '';
   }

   if ($lNumImageDocs > 0){
      foreach ($imageDocs as $imageDoc){
         if (is_null($lFID)){
            $lLocFID = $imageDoc->lForeignID;
         }else {
            $lLocFID = $lFID;
         }
         $strOut .= '<table style="border: 1px solid black; width: '.$lTableWidth.'px;">';
         $strProfile = $imageDoc->bProfile ? '<b>Profile</b>&nbsp;&nbsp;&nbsp; ' : '';
         $lImageDocID = $imageDoc->lKeyID;
         if ($bImage){
            $strImageLink =  strImageHTMLTag($enumContext, $enumEntryType, $lLocFID, $imageDoc->strSystemThumbFN,
                         'View in new window', false, '')."\n";
         }
         $strFullElement =
                strLinkHTMLTag($enumContext, $enumEntryType, $lLocFID, $imageDoc->strSystemFN,
                         'View in new window', true, '')."\n".$strImageLink.'</a>'."\n";

         $strOut .= '<tr><td valign="top" align="center"
                        style="border: 1px solid #aaa; width: '.($lImageCellWidth).'px;" >'
                        .$strFullElement.'</td>';

         $strOut .= '<td align="left" valign="top">
                   <table width="100%" class="enpRpt">';

         if (isset($imageDoc->strNameLabel)){
            $strOut .= '
                      <tr>
                         <td class="enpRptLabel" style="width: 120px;">'
                            .$imageDoc->strNameLabel.'
                         </td>
                         <td class="enpRpt">'
                            .$imageDoc->strName.'
                         </td>
                      </tr>';
         }

         if (isset($imageDoc->strAddressLabel)){
            $strOut .= '
                      <tr>
                         <td class="enpRptLabel" style="width: 120px;">'
                            .$imageDoc->strAddressLabel.'
                         </td>
                         <td class="enpRpt">'
                            .$imageDoc->strAddr.'
                         </td>
                      </tr>';
        }

         $strOut .= '
                      <tr>
                         <td class="enpRptLabel" style="width: 120px;">'
                            .($bImage ? 'Caption:' : 'Document Title:').'
                         </td>
                         <td class="enpRpt">'
                            .htmlspecialchars($imageDoc->strCaptionTitle).'
                         </td>
                      </tr>

                      <tr>
                         <td class="enpRptLabel" style="width: 120px;">
                            Description:
                         </td>
                         <td class="enpRpt">'
                            .nl2br(htmlspecialchars($imageDoc->strDescription)).'
                         </td>
                      </tr>

                      <tr>
                         <td class="enpRptLabel" style="width: 120px;">
                            Tags:
                         </td>
                         <td class="enpRpt">'
                            .$imageDoc->strTagsUL.'
                         </td>
                      </tr>

                      <tr>
                         <td class="enpRptLabel" style="width: 120px;">
                            '.($bImage ? 'Image ' : 'Document ').' Date:
                         </td>
                         <td class="enpRpt">'
                            .date($genumDateFormat, $imageDoc->dteDocImage).'
                         </td>
                      </tr>

                      <tr>
                         <td class="enpRptLabel" style="width: 120px;">
                            Upload Date:
                         </td>
                         <td class="enpRpt">'
                            .date($genumDateFormat.' H:i:s', $imageDoc->dteOrigin).'
                         </td>
                      </tr>
                      <tr>
                         <td class="enpRptLabel" style="width: 120px;">
                            Upload File Name:
                         </td>
                         <td class="enpRpt">'
                            .htmlspecialchars($imageDoc->strUserFN).'
                         </td>
                      </tr>

                      <tr>
                         <td class="enpRptLabel" style="width: 120px;">
                            Other:
                         </td>
                         <td class="enpRpt">'
                            .$strProfile
                            .strLinkEdit_ImageDoc($enumContext, $lImageDocID, 'Edit '.($bImage ? 'image' : 'document').' information', true)
                            .'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                            .strLinkRem_ImageDoc($enumContext, $lImageDocID, $bImage, 'Remove '.($bImage ? 'image' : 'document'), true, true).'
                         </td>
                      </tr>


                  </table>
                </td></tr>';
         $strOut .= '</table><br>';
      }
   }
   return($strOut);
}











