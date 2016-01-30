<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('personalization/field_display');
---------------------------------------------------------------------*/
namespace fdh;

   function strDisplayValueViaType($bBiz, $vVal, $field, $bAsTableRow,
                  $strClass='enpRpt', $strStyleExtra=''){
              // $vVal, $enumType, $lTableID, $bAsTableRow, $strFieldName, $lWidth=0, $strClass='enpRpt', $strStyleExtra=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      $enumType     = $field->enumType;
      $lTableID     = $field->lTableID;
      $strFieldName = $field->strFieldName;
      $lWidth       = $field->displayWidth;

      $strAlign = 'left';
      switch ($enumType){
         case CS_FT_HEADING:  $strOut = $vVal; break;
         case CS_FT_LOG:      $strOut = $vVal; break;
         case CS_FT_CHECKBOX: $strOut = ($vVal ? 'Yes' : 'No');
            break;
         case CS_FT_DATE:
            $strAlign = 'center';
            if ($vVal.''==''){
               $strOut = '&nbsp;';
            }else {
               $strOut = strNumericDateViaMysqlDate($vVal, $gbDateFormatUS);
            }
            break;

         case CS_FT_DDL:
         case CS_FT_DDL_SPECIAL:
         case CS_FT_DDLMULTI:
         case CS_FT_TEXT80:
         case CS_FT_TEXT255:
         case CS_FT_TEXT20:
            $strOut = htmlspecialchars($vVal);
            break;

         case CS_FT_TEXT:
         case CS_FT_TEXTLONG:
            $strOut = nl2br(htmlspecialchars($vVal));
            break;

         case CS_FT_CLIENTID:
         case CS_FT_ID:
            $strAlign = 'center';
            $strOut = str_pad($vVal, 5, '0', STR_PAD_LEFT);

            switch ($strFieldName){
               case 'cr_lKeyID':       $strOut .= '&nbsp;'.strLinkView_ClientRecord($vVal, 'View client record', true); break;
               case 'gi_lKeyID':       $strOut .= '&nbsp;'.strLinkView_GiftsRecord ($vVal, 'View gift record',   true); break;
               case 'pe_lKeyID':
                  if ($bBiz){
                     $strOut .= '&nbsp;'.strLinkView_BizRecord   ($vVal, 'View business/organization record', true);
                  }else {
                     $strOut .= '&nbsp;'.strLinkView_PeopleRecord($vVal, 'View people record', true); 
                  }
                  break;
               case 'vol_lKeyID':      $strOut .= '&nbsp;'.strLinkView_Volunteer($vVal, 'View volunteer record', true);  break;
               case 'pe_lHouseholdID': $strOut .= '&nbsp;'.strLinkView_Household($vVal, -1, 'View Household', true);     break;
            }
            break;

         case CS_FT_INTEGER:
            $strAlign = 'right';
            if ($vVal.''==''){
               $strOut = '&nbsp;';
            }else {
               $strOut = number_format($vVal, 0);
            }
            break;

         case CS_FT_CURRENCY:
            $strAlign = 'right';
            if ($vVal.''==''){
               $strOut = '&nbsp;';
            }else {
               $strOut = number_format($vVal, 2);
            }
            break;
         default:
            screamForHelp($enumType.': unexpected field type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      if ($bAsTableRow){
         if ($lWidth > 0){
            $strStyle = ' style="width: '.$lWidth.'pt; '.$strStyleExtra.' text-align: '.$strAlign.';" ';
         }else {
            $strStyle = ' style="'.$strStyleExtra.' text-align: '.$strAlign.';" ';
         }
         $strOut = '<td class="'.$strClass.'" '.$strStyle.'>'.$strOut.'</td>';
      }
      return($strOut);
   }

   function strExportValueViaType($vVal, $enumType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      switch ($enumType){
         case CS_FT_CHECKBOX: $strOut = ($vVal ? '"Yes"' : '"No"');  break;
         case CS_FT_DATE:
            if ($vVal.''==''){
               $strOut = '';
            }else {
               $strOut = strNumericDateViaMysqlDate($vVal, $gbDateFormatUS);
            }
            break;

         case CS_FT_LOG:
         case CS_FT_HEADING:
         case CS_FT_DDL:
         case CS_FT_DDL_SPECIAL:
         case CS_FT_DDLMULTI:
         case CS_FT_TEXT80:
         case CS_FT_TEXT255:
         case CS_FT_TEXT20:
         case CS_FT_TEXT:
         case CS_FT_TEXTLONG:
            $strOut = '"'.str_replace('"', '""', $vVal).'"';
            break;


         case CS_FT_ID:
            $strOut = str_pad($vVal, 5, '0', STR_PAD_LEFT);
            break;

         case CS_FT_INTEGER:
            if ($vVal.''==''){
               $strOut = '';
            }else {
               $strOut = number_format($vVal, 0);
            }
            break;

         case CS_FT_CURRENCY:
            if ($vVal.''==''){
               $strOut = '';
            }else {
               $strOut = number_format($vVal, 2);
            }
            break;
         default:
            screamForHelp($enumType.': unexpected field type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      return($strOut);
   }

   function strSimpleValueViaType($vVal, $enumType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      switch ($enumType){
         case CS_FT_CHECKBOX: $strOut = ($vVal ? 'Yes' : 'No');  break;
         case CS_FT_DATE:
            if ($vVal.''==''){
               $strOut = '';
            }else {
               $strOut = strNumericDateViaMysqlDate($vVal, $gbDateFormatUS);
            }
            break;

         case CS_FT_LOG:
         case CS_FT_HEADING:
         case CS_FT_DDL:
         case CS_FT_DDL_SPECIAL:
         case CS_FT_DDLMULTI:
         case CS_FT_TEXT80:
         case CS_FT_TEXT255:
         case CS_FT_TEXT20:
         case CS_FT_TEXT:
         case CS_FT_TEXTLONG:
            $strOut = $vVal;
            break;


         case CS_FT_ID:
            $strOut = str_pad($vVal, 5, '0', STR_PAD_LEFT);
            break;

         case CS_FT_INTEGER:
            if ($vVal.''==''){
               $strOut = '';
            }else {
               $strOut = number_format($vVal, 0);
            }
            break;

         case CS_FT_CURRENCY:
            if ($vVal.''==''){
               $strOut = '';
            }else {
               $strOut = number_format($vVal, 2);
            }
            break;
         default:
            screamForHelp($enumType.': unexpected field type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      return($strOut);
   }


