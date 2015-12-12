<?php
/*-----------------------------------------------------------------------------
// copyright (c) 2011-2014 by Database Austin.
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
-----------------------------------------------------------------------------
      $this->load->helper('dl_util/web_layout');
-----------------------------------------------------------------------------*/

function openBlock($strTitle, $strLinks, $attributes=null){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT(strOpenBlock($strTitle, $strLinks, $attributes));
}

function strOpenBlock($strTitle, $strLinks, $attributes=null){
//---------------------------------------------------------------------
/* Typical usage:
      $this->load->helper ('js/div_hide_show');
      $displayData['js'] .= showHideDiv();

      $attributes = new stdClass;
      $attributes->lTableWidth      = 900;
      $attributes->lUnderscoreWidth = 300;
      $attributes->divID            = 'groupDiv';
      $attributes->divImageID       = 'groupDivImg';
      $attributes->bStartOpen       = true;
      $attributes->bAddTopBreak     = true;

      openBlock('My little section', $strMyLittleLinks, $attributes);
      echoT('<table width="100%">');
      ...
      echoT('</table>');
      $attributes->bCloseDiv = true;
      closeBlock($attributes);
---------------------------------------------------------------------*/
   global $gbBrowserFirefox;
   
   $strOut = '';
   if (is_null($attributes)){
      $attributes = new stdClass;
      $attributes->lTableWidth      = 800;
      $attributes->lUnderscoreWidth = 400;
      $attributes->divImageID       = null;
      $attributes->divID            = null;
      $attributes->bStartOpen       = false;
      $attributes->lMarginLeft      = 0;
      $attributes->lTitleFontSize   = 12;
      $attributes->bAddTopBreak     = true;
   }else {
      if (!isset($attributes->lTableWidth))      $attributes->lTableWidth      = 800;
      if (!isset($attributes->lUnderscoreWidth)) $attributes->lUnderscoreWidth = 400;
      if (!isset($attributes->divID))            $attributes->divID            = null;
      if (!isset($attributes->divImageID))       $attributes->divImageID       = null;
      if (!isset($attributes->bStartOpen))       $attributes->bStartOpen       = false;
      if (!isset($attributes->lMarginLeft))      $attributes->lMarginLeft      = 0;
      if (!isset($attributes->lTitleFontSize))   $attributes->lTitleFontSize   = 12;
      if (!isset($attributes->bAddTopBreak))     $attributes->bAddTopBreak     = true;
   }
   
      // note - if interior table width is greater than container table, stuff starts
      // dancing as the block is opened/closed; set table width null to not user the 
      // width specification.
   if (is_null($attributes->lTableWidth)){
      $strTabWidth = '';
   }else {
      $strTabWidth =  ' width="'.$attributes->lTableWidth.'" ';
   }

   if (is_null($attributes->divID)){
      $strDivImageLink = '';
      $strDivStart     = '';
   }else {
      $strDivImageLink = '
          <a id="'.$attributes->divImageID.'"
             href="javascript:toggleDivViaImage(\''.$attributes->divID
                         .'\', \''.$attributes->divImageID.'\', \'Show\', \'Hide\');"
             title="'.($attributes->bStartOpen ? 'Hide' : 'Show').'"
          > <img src="'.base_url().'images/misc/'.($attributes->bStartOpen ? 'minus' : 'plus').'.gif" border="0"></a>';
      $strDivStart = "\n".'<div id="'.$attributes->divID.'" style="display: '.($attributes->bStartOpen ? 'block' : 'none').';">'."\n";
   }

   $strMarginSpan = '<span style="margin-left: '.$attributes->lMarginLeft.'pt;">';
//                          font-size: 0px;">';   // font size needed for firefox
   $strOut .=
       ($attributes->bAddTopBreak ? '<br/>' : '')."\n"
          .$strMarginSpan.$strDivImageLink.'&nbsp;'
          .'<span style="font-size: '.$attributes->lTitleFontSize.'pt; font-variant: small-caps;">'.$strTitle   
          .'</span>&nbsp;&nbsp;&nbsp;'."\n"
          .$strLinks.'
         <br/>
         </span>
         <span style="margin-left: '.$attributes->lMarginLeft.'pt; '
                .($gbBrowserFirefox ? 'font-size: 0px;' : '').'">'
         .strImgTagBlackLine($attributes->lUnderscoreWidth, 2).'
         </span>'
     .$strDivStart.'
     <table '.$strTabWidth.'>
        <tr>
           <td width="20pt;">&nbsp;</td>
           <td style="text-align: left; vertical-align: top;">'."\n";
   return($strOut);
//         <img src="'.base_url().'images/layout/separatorBlack.jpg"
//            width="'.$attributes->lUnderscoreWidth.'px" height="2px" alt="---"
//            style="vertical-align:text-top;" />
   
}

function closeBlock($attributes=null){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   echoT(strCloseBlock($attributes));
}

function strCloseBlock($attributes=null){
/*---------------------------------------------------------------------
      $attributes = new stdClass;
      $attributes->strExtraText   = '<br>';
      $attributes->bCloseDiv      = true;
      closeBlock($attributes);
---------------------------------------------------------------------*/
   global $gbBrowserFirefox;

   if (is_null($attributes)){
      $attributes = new stdClass;
      $attributes->strExtraText = '';
      $attributes->bCloseDiv    = false;
   }else {
      if (!isset($attributes->strExtraText)) $attributes->strExtraText = '';
      if (!isset($attributes->bCloseDiv))    $attributes->bCloseDiv = false;
   }

   return('</td></tr></table>'.($attributes->bCloseDiv ? '</div>'.($gbBrowserFirefox ? '<br>' : '') : '').$attributes->strExtraText);
}

function initCalendarOpts(&$calOpts){
   global $gbDateFormatUS;

   $calOpts = new stdClass;
   $calOpts->strDate         = 
   $calOpts->strForm         = 
   $calOpts->strTextObjName  = 
   
      // additional javascript events
   $calOpts->strTextOnChange = 
   $calOpts->strTextOnFocus  = 
   $calOpts->strImgOnClick   = '';
   
   $calOpts->strTextBoxID    = 'datepicker';
   $calOpts->strDateFormat   = $gbDateFormatUS ? 'mm/dd/yyyy' : 'dd/mm/yyyy';
}

function strDatePicker($strTextBoxID='datepicker', $bAllowFuture=false, $lMinYear=1920){
//---------------------------------------------------------------------
//  http://en.wikipedia.org/wiki/Unix_epoch#Notable.C2.A0events.C2.A0in.C2.A0Unix.C2.A0time
//---------------------------------------------------------------------
   global $gbDateFormatUS, $gdteNow;
   $lFYear = date('Y', $gdteNow)+20;
   if ($lFYear >= 2038) $lFYear = 2038;
   
   $strDateFormat = $gbDateFormatUS ? 'mm/dd/yy' : 'dd/mm/yy';

   return('
         <script type="text/javascript">
         $(function() {
            $(\'#'.$strTextBoxID.'\').datepicker({

               dateFormat: \''.$strDateFormat.'\',
               minDate: new Date('.$lMinYear.', 1 - 1, 1),
               maxDate: new Date('.($bAllowFuture ? '2037, 1 - 1, 1' : '').'),
               yearRange: \'c-100:'.($bAllowFuture ? $lFYear : 'c').'\',
               changeMonth: true,
               changeYear: true
            });

         });
         </script>');
}

function strCalendarCell(&$opts){     
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   global $gbDateFormatUS;

   $strFormText = $opts->strForm.'.'.$opts->strTextObjName;
   if (!isset($opts->strAfterCalImg))$opts->strAfterCalImg = '';
   return(
        '<input name="'.$opts->strTextObjName.'" 
                 style="width: 95px;"
                 type="text"
                 value="'.$opts->strDate.'"
                 onChange="'.$strFormText.'.style.background=\'#fff\'; '.$opts->strTextOnChange.'  "
                 onFocus=" '.$opts->strTextOnFocus.' "
                 id="'.$opts->strTextBoxID.'" class="date-pick">

         <img src="'.DL_IMAGEPATH.'/layout/calendarDatePick01.png"
               onclick="'.$strFormText.'.focus();"
               onmouseover="document.body.style.cursor = \'pointer\';"
               onmouseout="document.body.style.cursor = \'default\';"
               >'.$opts->strAfterCalImg.'<br>
         <font style="font-size: 8pt; color: #888;">date format: '.$opts->strDateFormat.'</font>');
}


?>