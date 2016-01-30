<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('creports/mcrpt_terms_display', 'crptTD');
---------------------------------------------------------------------

---------------------------------------------------------------------*/


class mcrpt_terms_display extends mcrpt_search_terms{


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
   }

   function strFormattedSearchExpression($lReportID, $att, &$bBalanced){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->setDefaultAttributes($att);
      $strOut = '';
      $this->loadSearchTermViaReportID($att->lReportID);
      $bBalanced = $this->bAreParensBalanced();
      if ($this->lNumTerms == 0){
         return('<i>There are no search expressions associated with this report.</i>');
      }

         // It's the little things that make a house a home.
      $bSingular = $this->lNumTerms==1;
      if ($att->showSearchCnt){
         $strOut .=
             'There '.($bSingular ? 'is' : 'are').' '
             .$this->lNumTerms.' search expression'.($bSingular ? '' : 's')
             .' associated with this report.<br><br>';
      }

      if ($att->bShowUpDownLink){
         $clsUpDown = new up_down_top_bottom;
         $clsUpDown->lMax = $this->lNumTerms;
      }

      if ($att->warnUnbalancedParen){
         if (!$bBalanced){
            $strOut .= '
               <div class="formError">Warning: the parentheses in this search
                  expresssion are not balanced. You will not be able to run
                  this report until the parenthese are balanced.</div>';
         }
      }

      if ($att->bShowSortLink){
         if ($this->lNumTerms > 1){
            $strOut .= strLinkEdit_CRptSearchFieldOrder($lReportID, 'Search order', false).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
         }
      }

      if ($att->bShowParenEditLink){
         $strOut .= strLinkEdit_CRptSearchFieldParens($lReportID, 'Parentheses', false).'<br>';
      }

      $lMaxP = $this->lMaxParentheses();

      $strOut .=
         '<table border="0" cellspacing="5">'."\n";
      $iFldCnt = 1; $idx = 0; $lMaxNumParen = 6;

      foreach ($this->terms as $term){
         $lFieldID = $term->lFieldID;
         $lTermID  = $term->lKeyID;

         $strOut .= '<tr>';

         if ($att->showEditDelete){
            $strOut .=
                '<td>'
                   .strLinkEdit_CReportSearchTerm($lReportID, $term->strFieldID, $lTermID, 'Edit search term', true).'&nbsp;&nbsp;&nbsp;&nbsp;'
                   .strLinkRem_CReportSearchTerm($lReportID, $lTermID, 'Remove search term', true, true).'
                 </td>';
         }

            // field count
         if ($att->showFieldIDX){
            $strOut .= '
               <td style="text-align: center;">('
                  .$iFldCnt.')&nbsp;
               </td>';
         }

            // up/down links
         if ($att->bShowUpDownLink){
            $clsUpDown->strLinkBase =
               '<a href="'.base_url()."index.php/creports/search_order/move/$lReportID/$lTermID/";
            $clsUpDown->upDownLinks($idx);
            $strOut .=
                   '<td style="width: 20pt;">&nbsp;</td>
                    <td class="enpRpt" style="font-size: 7pt;" nowrap>'
                     .$clsUpDown->strUp.$clsUpDown->strDown
                     .'&nbsp;&nbsp;&nbsp;&nbsp;'
                     .$clsUpDown->strTop.$clsUpDown->strBottom.'
                    </td>';
         }

            // opening parentheses
         $strOut .= '
               <td style="font-family: courier;">'."\n";
         if ($att->bParenAsTextInput){
            $strOut .= $this->inputTextParens($lTermID, true, $term->lNumLParen, $lMaxNumParen, $idx);
         }else {
            for ($jidx = 0; $jidx < $term->lNumLParen; ++$jidx){
               $strOut .= '( ';
            }
         }
         $strOut .= '&nbsp;</td>';

            // table and field name
         if (isset($term->strAttachLabel)){
            $strAttach = '<b>['.$term->strAttachLabel.']</b>&nbsp;';
         }else {
            $strAttach = '';
         }
         $strOut .=
             '<td style="font-weight: bold; font-size: 8pt;">'.$strAttach.'['
                    .htmlspecialchars($term->strUserTableName).']
              </td>
              <td>'
                .htmlspecialchars($term->strFieldNameUser).'
              </td>';

            // comparison operator
         $strOut .=
             '<td style="font-weight: bold;">'
                    .crptLabelSearchType($term->lCompareOpt) .'
              </td>';

            // comparitor
         $strOut .=
             '<td>'.$this->strComparitor($term) .'</td>';

            // closing parentheses
         $strOut .= '<td style="font-family: courier;">&nbsp;';
         if ($att->bParenAsTextInput){
            $strOut .= $this->inputTextParens($lTermID, false, $term->lNumRParen, $lMaxNumParen, $idx);
         }else {
            for ($jidx = 0; $jidx < $term->lNumRParen; ++$jidx){
               $strOut .= ' )';
            }
         }
         $strOut .= '</td>';


            // spacer
         $strOut .=
             '<td style="width: 20pt;">&nbsp;</td>';

            // boolean with next term
         if ($iFldCnt == $this->lNumTerms){
            $strOut .= '<td>&nbsp;</td>';
         }else {
            if ($att->bParenAsTextInput){
               $strOut .= '<td><i>'.$this->strAndOrDDL($lTermID, $term->bNextTermBoolAND, $idx).'</i></td>';
            }else {
               $strOut .=
                   '<td>'.($term->bNextTermBoolAND ? 'AND' : 'OR').'</td>';
            }
         }

         $strOut .= '</tr>';
         ++$iFldCnt; ++$idx;
      }
      $strOut .= '</table>';
      return($strOut);
   }

   function setDefaultAttributes(&$att){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!isset($att->showSearchCnt))       $att->showSearchCnt       = true;
      if (!isset($att->showFieldIDX))        $att->showFieldIDX        = true;
      if (!isset($att->warnUnbalancedParen)) $att->warnUnbalancedParen = true;
      if (!isset($att->bShowSortLink))       $att->bShowSortLink       = true;
      if (!isset($att->bShowUpDownLink))     $att->bShowUpDownLink     = false;
      if (!isset($att->bShowParenEditLink))  $att->bShowParenEditLink  = false;
      if (!isset($att->bParenAsTextInput))   $att->bParenAsTextInput   = false;
      if (!isset($att->showEditDelete))      $att->showEditDelete      = false;
   }

   function lMaxParentheses(){
   //---------------------------------------------------------------------
   //  for the loaded search terms, find the max parenthesis on either
   //  the right or left side. Used in formatting.
   //---------------------------------------------------------------------
      $lMax = 0;
      foreach ($this->terms as $term){
         if ($term->lNumLParen > $lMax) $lMax = $term->lNumLParen;
         if ($term->lNumRParen > $lMax) $lMax = $term->lNumRParen;
      }
   }

   function strComparitor(&$term){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS;

      switch ($term->enumFieldType){
         case CS_FT_CHECKBOX:
            $strOut = ($term->bCompareBool ? CS_SRCH_CHK_YES : CS_SRCH_CHK_NO);
            break;

         case CS_FT_ID:
         case CS_FT_INTEGER:
            $strOut = number_format($term->lCompVal);
            break;

         case CS_FT_CURRENCY:
            $strOut = $term->ACO->strFlagImg.'&nbsp;'.number_format($term->curCompVal, 2);
            break;

         case CS_FT_DDL:
         case CS_FT_DDLMULTI:
            $cuf = new muser_fields;
            $strOut = '"'.htmlspecialchars($cuf->strDDLValue($term->lCompVal)).'"';
            break;

         case CS_FT_DDL_SPECIAL:
            $strOut = '"'.htmlspecialchars(sddl\loadSpecialDDLValue($term->strFieldID, $term->strCompVal)).'"';
            break;

         case CS_FT_DATE:
            $strOut = strNumericDateViaMysqlDate($term->mdteCompVal, $gbDateFormatUS);
            break;

         case CS_FT_TEXTLONG:
         case CS_FT_TEXT255:
         case CS_FT_TEXT80:
         case CS_FT_TEXT20:
         case CS_FT_TEXT:
            $strOut = '"'.htmlspecialchars($term->strCompVal).'"';
            break;

         default:
            screamForHelp($term->enumFieldType.': unsupported field type<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strOut);
   }

   function strAndOrDDL($lTermID, $bTermBoolAND, $lTermIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '
         <select name="ddlAndOr_'.$lTermID.'" id="idAndOrDDL_'.$lTermIDX.'">
            <option value="AND" '.($bTermBoolAND ? 'selected' : '').'>AND</option>
            <option value="OR"  '.($bTermBoolAND ? '' : 'selected').'>OR</option>
         </select>';
      return($strOut);
   }

   function inputTextParens($lSearchTermID, $bLeft, $lNumParen, $lMaxNumParen, $lTermIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      $strTextNameBase = 'txtParen_'.($bLeft?'L':'R').'_'.$lSearchTermID.'_';
      if ($bLeft){
         $strParen = '(';
         $lCntStart = $lMaxNumParen;
         $lCntStop  = 1;
         $lIncrement = -1;
      }else {
         $strParen = ')';
         $lCntStart = 1;
         $lCntStop  = $lMaxNumParen;
         $lIncrement = 1;
      }
      for ($idx=$lCntStart;  ($bLeft ? $idx>=$lCntStop : $idx<=$lCntStop); $idx += $lIncrement) {
         $strID = ' id="idParen'.($bLeft?'L':'R').'_'.$lTermIDX.'_'.$idx.'" ';
         $bSetParen = $lNumParen>=$idx;
         $strTextName = $strTextNameBase.$idx;
         $strOut .=
           '<input type="text" name="'.$strTextName.'" READONLY '.$strID
              .' style= "width: 10px; border: solid 1px #abf; text-align: center; '
                   .'font-size: 12pt; font-weight: bold;"
                 value="'.($bSetParen ? $strParen : '').'" '
               .'onClick="toggleParen(frmSearch.'.$strTextName.',\''.$strParen.'\')" '
            .'><span style="font-size: 3pt;">&nbsp;</span>';
      }
      return($strOut);
   }






}






