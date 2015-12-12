<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2015 Database Austin

   author: John Zimmerman

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
-----------------------------------------------------------------------------
      $this->load->helper('staff/cico');
-----------------------------------------------------------------------------*/
   namespace cico;

   function strItemStatus(&$properties){
   /* -----------------------------------------------------------
      Input
         $properties->bAvail
         $properties->bLost
         $properties->bCheckedOut
         $properties->strCO_To
         $properties->dteCO
         $properties->bRemovedFromInv

      $properties:
         avail     lost   checked   removed   |      links
         for loan           out     from inv. |  co   ci   lost  found  rem  un-rem
         =====================================================================
            0        0       0         0      |   0    0    1      0     1     0        not available for loan
            0        0       0         1      |   0    0    1      0     0     1        removed from inventory
            0        0       1         0      |   0    1    1      0     1     0        checked out
            0        0       1         1      |   0    1    1      0     0     1        checked out / removed from inventory

            0        1       0         0      |   0    0    0      1     1     0        lost
            0        1       0         1      |   0    0    0      1     0     1        lost / removed from inventory
            0        1       1         0      |   0    0    0      1     1     0        lost / checked out
            0        1       1         1      |   0    0    0      1     0     1        lost / checked out / removed from inventory

            1        0       0         0      |   1    0    1      0     1     0        available for checkout
            1        0       0         1      |   0    0    1      0     0     1        removed from inventory
            1        0       1         0      |   0    1    1      0     1     0        checked out
            1        0       1         1      |   0    1    1      0     0     1        checked out / removed from inventory

            1        1       0         0      |   0    0    0      1     1     0        lost
            1        1       0         1      |   0    0    0      1     0     1        lost / removed from inventory
            1        1       1         0      |   0    0    0      1     1     0        lost / checked out
            1        1       1         1      |   0    0    0      1     0     1        lost / checked out / removed from inventory
   ----------------------------------------------------------- */
      global $genumDateFormat;
   
      $bAvail = $properties->bAvail;
      $bLost  = $properties->bLost;
      $bCO    = $properties->bCheckedOut;
      $bRem   = $properties->bRemovedFromInv;

      $properties->bLinkCO    = $bAvail && !$bLost && !$bCO && !$bRem;
      $properties->bLinkCI    = $bCO && !$bLost;
      $properties->bLinkLost  = !$bLost;
      $properties->bLinkFound = $bLost;
      $properties->bLinkRem   = !$bRem;
      $properties->bLinkUnRem = $bRem;

      if ($bCO) $strCOInfo = 'Checked out to '.$properties->strCO_To.' on '.date($genumDateFormat, $properties->dteCO);
      
      if (!$bAvail && !$bLost && !$bCO && !$bRem) return('Not available for loan');
      if (!$bAvail && !$bLost && !$bCO &&  $bRem) return('Removed from inventory');
      if (!$bAvail && !$bLost &&  $bCO && !$bRem) return($strCOInfo);
      if (!$bAvail && !$bLost &&  $bCO &&  $bRem) return($strCOInfo.' / removed from inventory');

      if (!$bAvail &&  $bLost && !$bCO && !$bRem) return('Lost');
      if (!$bAvail &&  $bLost && !$bCO &&  $bRem) return('Lost / removed from inventory');
      if (!$bAvail &&  $bLost &&  $bCO && !$bRem) return('Lost / '.$strCOInfo);
      if (!$bAvail &&  $bLost &&  $bCO &&  $bRem) return('Lost / '.$strCOInfo.' / removed from inventory');

      if ( $bAvail && !$bLost && !$bCO && !$bRem) return('Available for checkout');
      if ( $bAvail && !$bLost && !$bCO &&  $bRem) return('Removed from inventory');
      if ( $bAvail && !$bLost &&  $bCO && !$bRem) return($strCOInfo);
      if ( $bAvail && !$bLost &&  $bCO &&  $bRem) return($strCOInfo.' / removed from inventory');

      if ( $bAvail &&  $bLost && !$bCO && !$bRem) return('Lost');
      if ( $bAvail &&  $bLost && !$bCO &&  $bRem) return('Lost / removed from inventory');
      if ( $bAvail &&  $bLost &&  $bCO && !$bRem) return('Lost / '.$strCOInfo);
      if ( $bAvail &&  $bLost &&  $bCO &&  $bRem) return('Lost / '.$strCOInfo.' / removed from inventory');
   }
