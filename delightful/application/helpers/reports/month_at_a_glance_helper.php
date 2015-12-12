<?php
/*
      $this->load->helper('reports/month_at_a_glance');
*/

function maagNextPrevLinks($strBase1, $strBase2, $lMonth, $lYear, &$maagInfo){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $maagInfo = new stdClass;
   
   $maagInfo->dteMonth = strtotime($lMonth.'/1/'.$lYear);
   $maagInfo->lDaysInMonth = date('t', $maagInfo->dteMonth);
   $maagInfo->strMonth     = date('F', $maagInfo->dteMonth);
   
   $maagInfo->strPrevMonth = '&nbsp;'
         .anchor($strBase1.($lMonth-1).'/'.$lYear.$strBase2,
               '<img src="'.base_url().'images/dbNavigate/prev_15y.png"
               title="Previous Month" border="0" alt="link" />').'&nbsp;&nbsp;';
   $maagInfo->strNextMonth = '&nbsp;&nbsp;'
         .anchor($strBase1.($lMonth+1).'/'.$lYear.$strBase2,
               '<img src="'.base_url().'images/dbNavigate/next_15y.png"
               title="Next Month" border="0" alt="link" />').'&nbsp;';
}


function semiMonthlyNextPrevLinks(&$semiMoInfo){
//---------------------------------------------------------------------
// day must be 1 or 15
//---------------------------------------------------------------------
$semiMoInfo->strLinkPrev = $semiMoInfo->strLinkNext = '';
   $bFirstHalf = $semiMoInfo->lDay == 1;
   if ($bFirstHalf){
      $semiMoInfo->strDateLabel = date('F', $semiMoInfo->dteStart).' 1-14, '.$semiMoInfo->lYear;
      $semiMoInfo->strLinkPrev = '&nbsp;'
            .anchor($semiMoInfo->strAnchorBase.($semiMoInfo->lMonth-1).'/15/'.$semiMoInfo->lYear,
               '<img src="'.base_url().'images/dbNavigate/prev_15y.png"
               title="Previous" border="0" alt="link" />').'&nbsp;&nbsp;';
      
      $semiMoInfo->strLinkNext = '&nbsp;&nbsp;'
         .anchor($semiMoInfo->strAnchorBase.$semiMoInfo->lMonth.'/15/'.$semiMoInfo->lYear,
               '<img src="'.base_url().'images/dbNavigate/next_15y.png"
               title="Next" border="0" alt="link" />').'&nbsp;';
   }else {
      $semiMoInfo->strDateLabel = date('F', $semiMoInfo->dteStart).' 15-'
                    .date('j', $semiMoInfo->dteEnd).', '.$semiMoInfo->lYear;
      $semiMoInfo->strLinkPrev = '&nbsp;'
            .anchor($semiMoInfo->strAnchorBase.($semiMoInfo->lMonth).'/1/'.$semiMoInfo->lYear,
               '<img src="'.base_url().'images/dbNavigate/prev_15y.png"
               title="Previous" border="0" alt="link" />').'&nbsp;&nbsp;';
      $semiMoInfo->strLinkNext = '&nbsp;&nbsp;'
         .anchor($semiMoInfo->strAnchorBase.($semiMoInfo->lMonth+1).'/1/'.$semiMoInfo->lYear,
               '<img src="'.base_url().'images/dbNavigate/next_15y.png"
               title="Next" border="0" alt="link" />').'&nbsp;';
   }
   
   
   
}


