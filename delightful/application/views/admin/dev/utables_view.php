<?php
   global $gdteNow;

   if ($lNumUTables == 0){
      echoT('<br><i>There are no personalized tables in your database!</i><br><br>');
      return;
   }

   echoT('<br>
      <table class="enpRpt">
         <tr>
            <td colspan="11" class="enpRptTitle">
               Personalized Tables: '.date('Y-m-d H:i:s', $gdteNow).'
            </td>
         </tr>');

   echoT('
      <tr>
         <td class="enpRptLabel" rowspan="2">
            &nbsp;
         </td>
         <td class="enpRptLabel" rowspan="2">
            pft_lKeyID
         </td>
         <td class="enpRptLabel" rowspan="2">
            Fields
         </td>
         <td class="enpRptLabel" rowspan="2">
            Created
         </td>
         <td class="enpRptLabel" rowspan="2">
            Internal Name
         </td>
         <td class="enpRptLabel" rowspan="2">
            External Name
         </td>
         <td class="enpRptLabel" rowspan="2">
            Validation
         </td>
         <td class="enpRptLabel" colspan="5" style="text-align: center;">
            Client Program
         </td>
      </tr>
      <tr>
         <td class="enpRptLabel">
            CProgID
         </td>
         <td class="enpRptLabel">
            Name
         </td>
         <td class="enpRptLabel">
            Enroll
         </td>
         <td class="enpRptLabel">
            Attend
         </td>
      </tr>');

   foreach ($utables as $idx=>$ut){
      if ($ut->bHidden){
         $strStyleHidden = ' color: #aaa; ';
      }else {
         $strStyleHidden = '';
      }
      
      $strLinkFields = strLinkView_UFFields($ut->lKeyID, 'View fields', true);
      
      if (!is_null($ut->lCProgEID)){
         $strPName      = '<b>'.htmlspecialchars($ut->strCProgEName).'</b>';
         $strCPID       = str_pad($ut->lCProgEID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                               .strLinkView_CProgram($ut->lCProgEID, 'View client program', true);
        $strCPE        = '<b>X</b>';
         $strCPA        = '-';
         $bCP           = true;
         $strLinkUTable = '';
      }elseif (!is_null($ut->lCProgAID)){
         $strPName      = '<b>'.htmlspecialchars($ut->strCProgAName).'</b>';
         $strCPID       = str_pad($ut->lCProgAID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                               .strLinkView_CProgram($ut->lCProgAID, 'View client program', true);
         $strCPE        = '-';
         $strCPA        = '<b>X</b>';
         $bCP           = true;
         $strLinkUTable = '';
      }else {
         $strPName      = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-';
         $strCPID       = '-';
         $strCPE        = '-';
         $strCPA        = '-';
         $bCP           = false;
         $strLinkUTable = '&nbsp;'.strLinkView_UFTable($ut->lKeyID, 'View table', true);
      }
      echoT('
         <tr class="makeStripe">
            <td class="enpRpt" style="text-align: center; width: 20pt; '.$strStyleHidden.'">'
               .($idx+1).'
            </td>
            <td class="enpRpt" style="text-align: left; width: 40pt; '.$strStyleHidden.'">'
               .str_pad($ut->lKeyID, 5, '0', STR_PAD_LEFT).$strLinkUTable.'
            </td>
            <td class="enpRpt" style="text-align: center; width: 20pt; '.$strStyleHidden.'">'
               .$strLinkFields.'
            </td>
            <td class="enpRpt" style="text-align: left; width: 100pt; '.$strStyleHidden.'">'
               .date('Y-m-d H:i:s', $ut->dteOrigin).'
            </td>
            <td class="enpRpt" style="text-align: center; width: 50pt; '.$strStyleHidden.'">'
               .$ut->strDataTableName.'
            </td>
            <td class="enpRpt" style="text-align: left; width: 160pt; '.$strStyleHidden.'">'
               .(!$bCP ? '<b>' : '').htmlspecialchars($ut->strUserTableName).(!$bCP ? '</b>' : '').'
            </td>
            <td class="enpRpt" style="text-align: left; width: 120pt; '.$strStyleHidden.'">'
               .htmlspecialchars($ut->strVerificationModule).'
            </td>
            <td class="enpRpt" style="text-align: center; width: 40pt; '.$strStyleHidden.'">'
               .$strCPID.'
            </td>
            <td class="enpRpt" style="text-align: left; width: 160pt; '.$strStyleHidden.'">'
               .$strPName.'
            </td>
            <td class="enpRpt" style="text-align: center; width: 40pt; '.$strStyleHidden.'">'
               .$strCPE.'
            </td>
            <td class="enpRpt" style="text-align: center; width: 40pt; '.$strStyleHidden.'">'
               .$strCPA.'
            </td>
         </tr>');
   }

   echoT('</table><br>');









