<?php

   if ($lNumDisplayRows == 0){
      echoT('<i>There are no business contacts that meet your search criteria.<br><br></i>');
      return;
   }

   $params = array('enumStyle' => 'enpRptC');
   $clsRpt = new generic_rpt($params);

   echoT('<br>'.$clsRpt->openReport());
   echoT($clsRpt->writeTitle($strRptTitle, '', '', 6));
   echoT($clsRpt->openRow(true));
   echoT($clsRpt->writeLabel('PeopleID',   60));
//   echoT($clsRpt->writeLabel('&nbsp;',     20));
   echoT($clsRpt->writeLabel('Name',      150));
   echoT($clsRpt->writeLabel('Address'));
   echoT($clsRpt->writeLabel('Phone/Email'));
   echoT($clsRpt->writeLabel('Business Relationships'));
   echoT($clsRpt->closeRow());

   foreach ($bizRecs as $bz){
      $lPID    = $bz->lPeopleID;
      echoT($clsRpt->openRow(true));
      
      echoT($clsRpt->writeCell(
                         strLinkView_PeopleRecord($lPID, 'View people record', true)
                        .'&nbsp;'.str_pad($lPID, 5, '0', STR_PAD_LEFT), 60));
      
      echoT($clsRpt->writeCell($bz->strSafeNameLF, 210));
      echoT($clsRpt->writeCell($bz->strAddress, 160));
      echoT($clsRpt->writeCell(strPhoneCell($bz->strPhone, $bz->strCell, true, true)
            .'<br>'.$bz->strEmailFormatted));
      if ($bz->lNumBiz==0){
         echoT($clsRpt->writeCell('<i>none</i>', 260));         
      }else {
         $strOut = '<table width="100%">'."\n";
         foreach ($bz->biz as $biz){
            if ($biz->bSoftCash){
               $strSoft = '<img src="'.IMGLINK_DOLLAR.'" border="0" title="Soft cash relationship">';
            }else {
               $strSoft = '';
            }
            if ($biz->strRelationship.'' == ''){
               $strRel = '&nbsp;';
            }else {
               $strRel = '&nbsp;<i>('.htmlspecialchars($biz->strRelationship).')</i>';
            }
            
            
            $strOut .= '
               <tr>
                  <td style="width: 130pt;">'
                     .strLinkView_BizRecord($biz->bizID, 'View business record', true).'&nbsp;'
                     .$biz->strBizSafeName.'
                  </td>
                  <td>'
                     .$strRel.' '.$strSoft.'
                  </td>
               </tr>'."\n";
         }
         $strOut .= '</table>'."\n";
         echoT($clsRpt->writeCell($strOut, 300));         
            
      }
      
      echoT($clsRpt->closeRow());            
   }

   echoT($clsRpt->closeReport());


