<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
/*---------------------------------------------------------------------
      $this->load->helper('img_docs/img_doc_tags');
---------------------------------------------------------------------*/

namespace imgDocTags;

   function loadImgContexts(&$econtext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $econtext = array(
              CENUM_CONTEXT_IMG_AUCTION,
              CENUM_CONTEXT_IMG_AUCTIONPACKAGE,
              CENUM_CONTEXT_IMG_AUCTIONITEM,
              CENUM_CONTEXT_IMG_BIZ,
              CENUM_CONTEXT_IMG_CLIENT,
              CENUM_CONTEXT_IMG_CLIENTLOCATION,
              CENUM_CONTEXT_IMG_GRANTS,
              CENUM_CONTEXT_IMG_GRANTPROVIDER,
              CENUM_CONTEXT_IMG_INVITEM,
              CENUM_CONTEXT_IMG_ORGANIZATION,
              CENUM_CONTEXT_IMG_PEOPLE,
              CENUM_CONTEXT_IMG_SPONSOR,
              CENUM_CONTEXT_IMG_STAFF,
              CENUM_CONTEXT_IMG_VOLUNTEER
              );
   }

   function loadDocContexts(&$econtext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $econtext = array(
              CENUM_CONTEXT_DOC_AUCTION,
              CENUM_CONTEXT_DOC_AUCTIONPACKAGE,
              CENUM_CONTEXT_DOC_AUCTIONITEM,
              CENUM_CONTEXT_DOC_BIZ,
              CENUM_CONTEXT_DOC_CLIENT,
              CENUM_CONTEXT_DOC_CLIENTLOCATION,
              CENUM_CONTEXT_DOC_GRANTS,
              CENUM_CONTEXT_DOC_GRANTPROVIDER,
              CENUM_CONTEXT_DOC_INVITEM,
              CENUM_CONTEXT_DOC_ORGANIZATION,
              CENUM_CONTEXT_DOC_PEOPLE,
              CENUM_CONTEXT_DOC_SPONSOR,
              CENUM_CONTEXT_DOC_STAFF,
              CENUM_CONTEXT_DOC_VOLUNTEER
              );
   }

   function strXlateImgDocType($enumContext, &$bImage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumContext){
         case CENUM_CONTEXT_IMG_AUCTION:
         case CENUM_CONTEXT_IMG_AUCTIONPACKAGE:
         case CENUM_CONTEXT_IMG_AUCTIONITEM:
         case CENUM_CONTEXT_IMG_BIZ:
         case CENUM_CONTEXT_IMG_CLIENT:
         case CENUM_CONTEXT_IMG_CLIENTLOCATION:
         case CENUM_CONTEXT_IMG_GRANTS:
         case CENUM_CONTEXT_IMG_GRANTPROVIDER:
         case CENUM_CONTEXT_IMG_INVITEM:
         case CENUM_CONTEXT_IMG_ORGANIZATION:
         case CENUM_CONTEXT_IMG_PEOPLE:
         case CENUM_CONTEXT_IMG_SPONSOR:
         case CENUM_CONTEXT_IMG_STAFF:
         case CENUM_CONTEXT_IMG_VOLUNTEER:
            $strOut = 'Image';
            $bImage = true;
            break;

         case CENUM_CONTEXT_DOC_AUCTION:
         case CENUM_CONTEXT_DOC_AUCTIONPACKAGE:
         case CENUM_CONTEXT_DOC_AUCTIONITEM:
         case CENUM_CONTEXT_DOC_BIZ:
         case CENUM_CONTEXT_DOC_CLIENT:
         case CENUM_CONTEXT_DOC_CLIENTLOCATION:
         case CENUM_CONTEXT_DOC_GRANTS:
         case CENUM_CONTEXT_DOC_GRANTPROVIDER:
         case CENUM_CONTEXT_DOC_INVITEM:
         case CENUM_CONTEXT_DOC_ORGANIZATION:
         case CENUM_CONTEXT_DOC_PEOPLE:
         case CENUM_CONTEXT_DOC_SPONSOR:
         case CENUM_CONTEXT_DOC_STAFF:
         case CENUM_CONTEXT_DOC_VOLUNTEER:
            $strOut = 'Document';
            $bImage = false;
            break;
         default:
            screamForHelp($enumContext.': invalid image/doc context<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strOut);
   }

   function strXlateImgDocContext($enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumContext){
         case CENUM_CONTEXT_IMG_AUCTION:          $strOut = 'Auction Images';                  break;
         case CENUM_CONTEXT_IMG_AUCTIONPACKAGE:   $strOut = 'Auction Package Images';          break;
         case CENUM_CONTEXT_IMG_AUCTIONITEM:      $strOut = 'Auction Item Images';             break;
         case CENUM_CONTEXT_IMG_BIZ:              $strOut = 'Business/Organization Images';    break;
         case CENUM_CONTEXT_IMG_CLIENT:           $strOut = 'Client Images';                   break;
         case CENUM_CONTEXT_IMG_CLIENTLOCATION:   $strOut = 'Client Location Images';          break;
         case CENUM_CONTEXT_IMG_GRANTS:           $strOut = 'Grant Images';                    break;
         case CENUM_CONTEXT_IMG_GRANTPROVIDER:    $strOut = 'Funder/Provider Images';          break;
         case CENUM_CONTEXT_IMG_INVITEM:          $strOut = 'Inventory Item Images';           break;
         case CENUM_CONTEXT_IMG_ORGANIZATION:     $strOut = 'Organization Images';             break;
         case CENUM_CONTEXT_IMG_PEOPLE:           $strOut = 'People Images';                   break;
         case CENUM_CONTEXT_IMG_SPONSOR:          $strOut = 'Sponsorship Images';              break;
         case CENUM_CONTEXT_IMG_STAFF:            $strOut = 'Staff Images';                    break;
         case CENUM_CONTEXT_IMG_VOLUNTEER:        $strOut = 'Volunteer Images';                break;

         case CENUM_CONTEXT_DOC_AUCTION:          $strOut = 'Auction Documents';               break;
         case CENUM_CONTEXT_DOC_AUCTIONPACKAGE:   $strOut = 'Auction Package Documents';       break;
         case CENUM_CONTEXT_DOC_AUCTIONITEM:      $strOut = 'Auction Item Documents';          break;
         case CENUM_CONTEXT_DOC_BIZ:              $strOut = 'Business/Organization Documents'; break;
         case CENUM_CONTEXT_DOC_CLIENT:           $strOut = 'Client Documents';                break;
         case CENUM_CONTEXT_DOC_CLIENTLOCATION:   $strOut = 'Client Location Documents';       break;
         case CENUM_CONTEXT_DOC_GRANTS:           $strOut = 'Grant Documents';                 break;
         case CENUM_CONTEXT_DOC_GRANTPROVIDER:    $strOut = 'Funder/Provider Documents';       break;
         case CENUM_CONTEXT_DOC_INVITEM:          $strOut = 'Inventory Item Documents';        break;
         case CENUM_CONTEXT_DOC_ORGANIZATION:     $strOut = 'Organization Documents';          break;
         case CENUM_CONTEXT_DOC_PEOPLE:           $strOut = 'People Documents';                break;
         case CENUM_CONTEXT_DOC_SPONSOR:          $strOut = 'Sponsorship Documents';           break;
         case CENUM_CONTEXT_DOC_STAFF:            $strOut = 'Staff Documents';                 break;
         case CENUM_CONTEXT_DOC_VOLUNTEER:        $strOut = 'Volunteer Documents';             break;
         default:
            screamForHelp($enumContext.': invalid image/doc context<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strOut);
   }

   function xlateTagTypeViaContextType($enumContextType, $enumEntryType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bImage = ($enumEntryType == CENUM_IMGDOC_ENTRY_IMAGE);

      switch ($enumContextType){
         case CENUM_CONTEXT_AUCTION:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_AUCTION : CENUM_CONTEXT_DOC_AUCTION;
            break;

         case CENUM_CONTEXT_AUCTIONITEM:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_AUCTIONITEM : CENUM_CONTEXT_DOC_AUCTIONITEM;
            break;

         case CENUM_CONTEXT_AUCTIONPACKAGE:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_AUCTIONPACKAGE : CENUM_CONTEXT_DOC_AUCTIONPACKAGE;
            break;

         case CENUM_CONTEXT_PEOPLE:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_PEOPLE : CENUM_CONTEXT_DOC_PEOPLE;
            break;

         case CENUM_CONTEXT_CLIENT:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_CLIENT : CENUM_CONTEXT_DOC_CLIENT;
            break;

         case CENUM_CONTEXT_BIZ:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_BIZ : CENUM_CONTEXT_DOC_BIZ;
            break;

         case CENUM_CONTEXT_GRANTS:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_GRANTS : CENUM_CONTEXT_DOC_GRANTS;
            break;

         case CENUM_CONTEXT_GRANTPROVIDER:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_GRANTPROVIDER : CENUM_CONTEXT_DOC_GRANTPROVIDER;
            break;

         case CENUM_CONTEXT_INVITEM:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_INVITEM : CENUM_CONTEXT_DOC_INVITEM;
            break;

         case CENUM_CONTEXT_LOCATION:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_CLIENTLOCATION : CENUM_CONTEXT_DOC_CLIENTLOCATION;
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_SPONSOR : CENUM_CONTEXT_DOC_SPONSOR;
            break;

         case CENUM_CONTEXT_ORGANIZATION:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_ORGANIZATION : CENUM_CONTEXT_DOC_ORGANIZATION;
            break;

         case CENUM_CONTEXT_STAFF:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_STAFF : CENUM_CONTEXT_DOC_STAFF;
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            $enumContext = $bImage ? CENUM_CONTEXT_IMG_VOLUNTEER : CENUM_CONTEXT_DOC_VOLUNTEER;
            break;

         default:
            screamForHelp($enumContextType.': invalid image/doc context<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($enumContext);
   }

   function xlateContextViaTagType($enumTagContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumTagContext){
         case CENUM_CONTEXT_IMG_AUCTION:
         case CENUM_CONTEXT_DOC_AUCTION:
            $enumContext = CENUM_CONTEXT_AUCTION;
            break;

         case CENUM_CONTEXT_IMG_AUCTIONITEM:
         case CENUM_CONTEXT_DOC_AUCTIONITEM:
            $enumContext = CENUM_CONTEXT_AUCTIONITEM;
            break;

         case CENUM_CONTEXT_IMG_AUCTIONPACKAGE:
         case CENUM_CONTEXT_DOC_AUCTIONPACKAGE:
            $enumContext = CENUM_CONTEXT_AUCTIONPACKAGE;
            break;

         case CENUM_CONTEXT_IMG_PEOPLE:
         case CENUM_CONTEXT_DOC_PEOPLE:
            $enumContext = CENUM_CONTEXT_PEOPLE;
            break;

         case CENUM_CONTEXT_IMG_CLIENT:
         case CENUM_CONTEXT_DOC_CLIENT:
            $enumContext = CENUM_CONTEXT_CLIENT;
            break;

         case CENUM_CONTEXT_IMG_BIZ:
         case CENUM_CONTEXT_DOC_BIZ:
            $enumContext = CENUM_CONTEXT_BIZ;
            break;

         case CENUM_CONTEXT_IMG_GRANTS:
         case CENUM_CONTEXT_DOC_GRANTS:
            $enumContext = CENUM_CONTEXT_GRANTS;
            break;

         case CENUM_CONTEXT_IMG_GRANTPROVIDER:
         case CENUM_CONTEXT_DOC_GRANTPROVIDER:
            $enumContext = CENUM_CONTEXT_GRANTPROVIDER;
            break;

         case CENUM_CONTEXT_IMG_INVITEM:
         case CENUM_CONTEXT_DOC_INVITEM:
            $enumContext = CENUM_CONTEXT_INVITEM;
            break;

         case CENUM_CONTEXT_IMG_CLIENTLOCATION:
         case CENUM_CONTEXT_DOC_CLIENTLOCATION:
            $enumContext = CENUM_CONTEXT_LOCATION;
            break;

         case CENUM_CONTEXT_IMG_SPONSOR:
         case CENUM_CONTEXT_DOC_SPONSOR:
            $enumContext = CENUM_CONTEXT_SPONSORSHIP;
            break;

         case CENUM_CONTEXT_IMG_ORGANIZATION:
         case CENUM_CONTEXT_DOC_ORGANIZATION:
            $enumContext = CENUM_CONTEXT_ORGANIZATION;
            break;

         case CENUM_CONTEXT_IMG_STAFF:
         case CENUM_CONTEXT_DOC_STAFF:
            $enumContext = CENUM_CONTEXT_STAFF;
            break;

         case CENUM_CONTEXT_IMG_VOLUNTEER:
         case CENUM_CONTEXT_DOC_VOLUNTEER:
            $enumContext = CENUM_CONTEXT_VOLUNTEER;
            break;

         default:
            screamForHelp($enumContextType.': invalid image/doc context<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($enumContext);
   }

   function strImgDocTagsDDL($strDDLName, $lSize, $bAddBlank, $lNumTags, $tags){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<select name="'.$strDDLName.'[]" multiple size="'.$lSize.'">'."\n";
      if ($bAddBlank){
         $strOut .= '<option value="-1">&nbsp;</option>'."\n";
      }

      if ($lNumTags > 0){
         foreach ($tags as $tag){
            if (!$tag->bRetired || $tag->bSelected){
               $strOut .= '<option value="'.$tag->lTagID.'" '.($tag->bSelected ? 'selected' : '').'>'
                  .htmlspecialchars($tag->strDDLEntry).'</option>'."\n";
            }
         }
      }
      $strOut .= '</select><br>'."\n"
             .'<span style="font-size: 8pt;"><i>Ctrl-click to select more than one tag</i></span>';
      return($strOut);
   }



