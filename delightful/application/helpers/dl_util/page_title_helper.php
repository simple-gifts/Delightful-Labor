<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
/*---------------------------------------------------------------------
      $this->load->helper('dl_util/page_title');
---------------------------------------------------------------------*/
// screamForHelp('</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);

function strPageTitle($enumPageType, $lFIDs){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strOut = '';
   switch ($enumPageType){
      case 'reminderBiz':
         $strOut =
                       anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                .' | '.anchor('biz/biz_record/view/'.$lFIDs[0], 'Record', 'class="breadcrumb"')
                .' | Reminders';
         break;

      case 'reminderPeople':
         $strOut =
                       anchor('main/menu/people', 'People', 'class="breadcrumb"')
                .' | '.anchor('people/people_record/view/'.$lFIDs[0], 'Record', 'class="breadcrumb"')
                .' | Reminders';
         break;

      case 'reminderPeopleGift':
         $strOut =
                        anchor('main/menu/people', 'People', 'class="breadcrumb"')
                 .' | '.anchor('people/people_record/view/' .$lFIDs[1], 'Record',      'class="breadcrumb"')
                 .' | '.anchor('donations/gift_record/view/'.$lFIDs[0], 'Gift Record', 'class="breadcrumb"')
                 .' | Reminders';
         break;

      case 'reminderRecordPeople':
         $strOut =
                        anchor('main/menu/people', 'People', 'class="breadcrumb"')
                 .' | '.anchor('people/people_record/view/'.$lFIDs[0], 'Record', 'class="breadcrumb"')
                 .' | Reminders';

         break;
         
      case 'reminderSponsor':
         $strOut =
                        anchor('main/menu/sponsorship', 'Sponsorship', 'class="breadcrumb"')
                 .' | '.anchor('sponsors/view_spon_rec/viewViaSponID/'.$lFIDs[0], 'Sponsorship Record', 'class="breadcrumb"')
                 .' | Reminders';

         break;

      case 'reminderRecordPeopleGift':
         $strOut =
                        anchor('main/menu/people', 'People', 'class="breadcrumb"')
                 .' | '.anchor('people/people_record/view/'.$lFIDs[1], 'Record', 'class="breadcrumb"')
                 .' | '.anchor('donations/gift_record/view/'.$lFIDs[0],        'Gift Record',   'class="breadcrumb"')
                 .' | Reminders';
         break;

      case 'reminderRecordBizGift':
         $strOut =
                        anchor('main/menu/biz', 'Businesses/Organizations', 'class="breadcrumb"')
                 .' | '.anchor('biz/biz_record/view/'.$lFIDs[1], 'Record', 'class="breadcrumb"')
                 .' | '.anchor('donations/gift_record/view/'.$lFIDs[0],        'Gift Record',   'class="breadcrumb"')
                 .' | Reminders';
         break;

      case 'reminderClient':
         $strOut =
                        anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                 .' | '.anchor('clients/client_record/view/'.$lFIDs[0], 'Client Record', 'class="breadcrumb"')
                 .' | Reminders';
         break;

      case 'reminderRecordUser':
         $strOut = 'Reminders';
         break;

      default:
         screamForHelp($enumPageType.': Invalide Page Type</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
         break;
   }
   return($strOut);
}