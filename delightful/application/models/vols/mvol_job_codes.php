<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('vols/mvol_job_codes', 'cVJobCodes');
---------------------------------------------------------------------*/


class mvol_job_codes extends CI_Model{

   function strVolJobCodes(
                 $sRpt,
                 $bReport,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bAllJobCodes = $sRpt->lJobCodeID <= 0;

      $jCodes = array();
      $this->prepJobCodeArray($jCodes, $sRpt, $bAllJobCodes, $lNumJobCodes);
      if ($lNumJobCodes == 0){
         return('There are no job codes defined in your database.');
      }

         // load job code hours by shift
      $this->loadShiftJobCodesViaMonth($jCodes, $sRpt, $bAllJobCodes);

         // load unscheduled job code hours
      $this->loadUnJobCodesViaMonth($jCodes, $sRpt, $bAllJobCodes);

         // calculate the anual totals
      $jcAnnualHrs = &$jCodes[13]->hours;
      for ($idx=1; $idx<=12; ++$idx){
         foreach ($jCodes[$idx]->hours as $lJCIDX=>$hour){
            $jcAnnualHrs[$lJCIDX]->sngNumShiftHours += $hour->sngNumShiftHours;
            $jcAnnualHrs[$lJCIDX]->sngNumUnHours    += $hour->sngNumUnHours;
         }
      }
      return($this->strFormatJobCodeRpt($jCodes, $sRpt));
   }

   function prepJobCodeArray(&$jCodes, $sRpt, $bAllJobCodes, &$lNumJobCodes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $masterCodes = array();
      $jcList = new mlist_generic;
      $jcList->enumListType = CENUM_LISTTYPE_VOLJOBCODES;
      if ($bAllJobCodes){
         $jcList->genericLoadList();
         $lNumJobCodes = count($jcList->listItems);
         if ($lNumJobCodes == 0) return;
         foreach ($jcList->listItems as $listItem){
            $masterCodes[$listItem->lKeyID] = new stdClass;
            $mc = &$masterCodes[$listItem->lKeyID];
            $mc->strJobCode = $listItem->strListItem;
            $mc->sngNumShiftHours = 0.0;
            $mc->sngNumUnHours = 0.0;
         }
         $masterCodes[-1] = new stdClass;
         $mc = &$masterCodes[-1];
         $mc->strJobCode = '(no job code assigned)';
         $mc->sngNumShiftHours = 0.0;
         $mc->sngNumUnHours = 0.0;
      }else {
         $lNumJobCodes = 1;
         $masterCodes[$sRpt->lJobCodeID] = new stdClass;
         $mc = &$masterCodes[$sRpt->lJobCodeID];
         $mc->strJobCode = $jcList->genericLoadListItem($sRpt->lJobCodeID);
         $mc->sngNumShiftHours = 0.0;
         $mc->sngNumUnHours = 0.0;
      }

      for ($idx=1; $idx<=13; ++$idx){      // month 13 is used for annual total
         $jCodes[$idx] = new stdClass;
         $jc = &$jCodes[$idx];
         $jc->lMonth = $idx;
         if ($idx <= 12) $jc->strMonth = strXlateMonth($idx);
         $jc->hours = arrayCopy($masterCodes);
      }
   }

   function loadShiftJobCodesViaMonth(&$jCodes, $sRpt, $bAllJobCodes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bAllJobCodes){
         $strWhereJC = '';
      }else {
         $strWhereJC = ' AND vs_lJobCode='.$sRpt->lJobCodeID.' ';
      }

      $sqlStr =
        'SELECT vs_lJobCode, MONTH(ved_dteEvent) AS lMonth, SUM(vsa_dHoursWorked) AS sngNumHrs
         FROM vol_events_dates
            INNER JOIN vol_events_dates_shifts        ON vs_lEventDateID       = ved_lKeyID
            INNER JOIN vol_events_dates_shifts_assign ON vsa_lEventDateShiftID = vs_lKeyID
         WHERE NOT vs_bRetired AND NOT vsa_bRetired
            AND YEAR(ved_dteEvent)='.$sRpt->lYear.' '.$strWhereJC.'
         GROUP BY MONTH(ved_dteEvent), vs_lJobCode
         ORDER BY MONTH(ved_dteEvent), vs_lJobCode;';

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      $idx = 0;
      if ($numRows > 0){
         foreach ($query->result() as $row){
            if (is_null($row->vs_lJobCode)){
               $lJobCode = -1;
            }else {
               $lJobCode = (int)$row->vs_lJobCode;
            }
            $jCodes[$row->lMonth]->hours[$lJobCode]->sngNumShiftHours = (float)$row->sngNumHrs;
         }
      }
   }

   function loadUnJobCodesViaMonth(&$jCodes, $sRpt, $bAllJobCodes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bAllJobCodes){
         $strWhereJC = '';
      }else {
         $strWhereJC = ' AND vsa_lJobCode='.$sRpt->lJobCodeID.' ';
      }

      $sqlStr =
        'SELECT vsa_lJobCode, MONTH(vsa_dteActivityDate) AS lMonth, SUM(vsa_dHoursWorked) AS sngNumHrs
         FROM vol_events_dates_shifts_assign
         WHERE NOT vsa_bRetired
            AND YEAR(vsa_dteActivityDate)='.$sRpt->lYear.' '.$strWhereJC.'
         GROUP BY MONTH(vsa_dteActivityDate), vsa_lJobCode
         ORDER BY MONTH(vsa_dteActivityDate), vsa_lJobCode;';

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      $idx = 0;
      if ($numRows > 0){
         foreach ($query->result() as $row){
            if (is_null($row->vsa_lJobCode)){
               $lJobCode = -1;
            }else {
               $lJobCode = (int)$row->vsa_lJobCode;
            }
            $jCodes[$row->lMonth]->hours[$lJobCode]->sngNumUnHours = (float)$row->sngNumHrs;
         }
      }
   }

   function strFormatJobCodeRpt($jCodes, $sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // annual summary
      $strOut =
         '<br>
          <table class="enpRptC">
             <tr>
                <td colspan="5" class="enpRptTitle">
                   Job Code Summary for the Year '.$sRpt->lYear.'
                </td>
             </tr>';
      $strOut .= '
            <tr>
               <td class="enpRptLabel">
                  Job Code
               </td>
               <td class="enpRptLabel">
                  Hours (Shift)
               </td>
               <td class="enpRptLabel">
                  Hours (Unscheduled)
               </td>
               <td class="enpRptLabel">
                  Total
               </td>
            </tr>';

      $jc = &$jCodes[13];
      $sngTotYearShift = $sngTotYearUn = 0.0;

      foreach ($jc->hours as $lJCID=>$hours){
         $strOut .= '
           <tr>
             <td class="enpRpt" style="width: 160pt;">'
                .htmlspecialchars($hours->strJobCode).'
             </td>
             <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 10px;">'
                .number_format($hours->sngNumShiftHours, 2).'
             </td>
             <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 10px;">'
                .number_format($hours->sngNumUnHours, 2).'
             </td>
             <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 10px;">'
                .number_format($hours->sngNumShiftHours+$hours->sngNumUnHours, 2).'
             </td>
          </tr>';
          $sngTotYearShift += $hours->sngNumShiftHours;
          $sngTotYearUn    += $hours->sngNumUnHours;
      }
      $strOut .= '
         <tr>
            <td class="enpRpt" style="">
               <b>Total:</b>
            </td>
            <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 10px; "><b>'
               .number_format($sngTotYearShift, 2).'</b>
            </td>
            <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 10px; "><b>'
               .number_format($sngTotYearUn, 2).'</b>
            </td>
            <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 10px; "><b>'
               .number_format($sngTotYearShift + $sngTotYearUn, 2).'</b>
            </td>
         </tr>';
      $strOut .= '</table>';


         // monthly summary
      $lNumCodes = count($jCodes[1]->hours);
      $strOut .=
         '<br>
          <table class="enpRptC">
             <tr>
                <td colspan="5" class="enpRptTitle">
                   Volunteer Job Codes for the Year '.$sRpt->lYear.'
                </td>
             </tr>';
      $strOut .= '
            <tr>
               <td class="enpRptLabel">
                  Month
               </td>
               <td class="enpRptLabel">
                  Job Code
               </td>
               <td class="enpRptLabel">
                  Hours (Shift)
               </td>
               <td class="enpRptLabel">
                  Hours (Unscheduled)
               </td>
               <td class="enpRptLabel">
                  Total
               </td>
            </tr>';

      $bEven = true;
      $sngTotTot = 0.0;
      for ($idx=1; $idx<=12; ++$idx){
         $sngTotMoShift = $sngTotMoUn = 0.0;
         if ($bEven){
            $strBG = 'background-color: #f6f6f6;';
         }else {
            $strBG = '';
         }
         $bEven = !$bEven;

         $jc = &$jCodes[$idx];
         $strOut .= '
             <tr>
               <td class="enpRpt" rowspan='.($lNumCodes+1).' style="width: 80pt; '.$strBG.'">
                  <b>'.strLinkView_VolsJobCodeViaMonth($sRpt->lYear, $idx, $sRpt->lJobCodeID, 'View monthly details', true).'&nbsp;'
                  .$jc->strMonth.'
               </td>';

         $bFirst = true;
         foreach ($jc->hours as $lJCID=>$hours){
            if (!$bFirst){
               $strOut .= '<tr class="makeStripe">'."\n";
               $bFirst = false;
            }
            $strOut .= '
                <td class="enpRpt" style="width: 160pt; '.$strBG.' ">'
                   .htmlspecialchars($hours->strJobCode).'
                </td>
                <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 10px; '.$strBG.' ">'
                   .number_format($hours->sngNumShiftHours, 2).'
                </td>
                <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 10px; '.$strBG.' ">'
                   .number_format($hours->sngNumUnHours, 2).'
                </td>
                <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 10px; '.$strBG.' ">'
                   .number_format($hours->sngNumShiftHours+$hours->sngNumUnHours, 2).'
                </td>
             </tr>';
             $sngTotMoShift += $hours->sngNumShiftHours;
             $sngTotMoUn    += $hours->sngNumUnHours;
         }

         $strOut .= '
            <tr>
               <td class="enpRpt" style=" '.$strBG.' ">
                  <b>Total:</b>
               </td>
               <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 10px; '.$strBG.' "><b>'
                  .number_format($sngTotMoShift, 2).'</b>
               </td>
               <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 10px; '.$strBG.' "><b>'
                  .number_format($sngTotMoUn, 2).'</b>
               </td>
               <td class="enpRpt" style="width: 50pt; text-align: right; padding-right: 10px; '.$strBG.' "><b>'
                  .number_format($sngTotMoShift + $sngTotMoUn, 2).'</b>
               </td>

            </tr>';
      }

      $strOut .= '</table>';
      return($strOut);
   }




   /* -----------------------------------------------------------
          M O N T H L Y   D E T A I L   R E P O R T
      ----------------------------------------------------------- */
   function strVolJobCodeMonthlyDetail(
                 $sRpt,
                 $bReport,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bAllJobCodes = $sRpt->lJobCodeID <= 0;
      $strOut = '<br>';
      $strMoYr = strXlateMonth($sRpt->lMonth).' '.$sRpt->lYear;

      $strOut .= $this->strShiftJobCodesSummaryViaMonth($strMoYr, $sRpt, $bAllJobCodes);
      $strOut .= $this->strShiftJobCodesDetailsViaMonth($strMoYr, $sRpt, $bAllJobCodes);
      $strOut .= $this->strUnJobCodesDetailsViaMonth($strMoYr, $sRpt, $bAllJobCodes);
      return($strOut);
   }

   function strShiftJobCodesSummaryViaMonth($strMoYr, $sRpt, $bAllJobCodes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
//      global $genumDateFormat;
      $strTmpTable = 'tmpJCSumMo';
      $this->buildTmpTabJobCodeSumMo($sRpt, $strTmpTable);

      $sqlStr =
          "SELECT pe_strLName, pe_strFName, tmp_lVolID,
              tmp_dHoursScheduled, tmp_dHoursUnscheduled,
              jc.lgen_strListItem AS strJobCode
           FROM $strTmpTable
              INNER JOIN volunteers           ON tmp_lVolID    = vol_lKeyID
              INNER JOIN people_names         ON vol_lPeopleID = pe_lKeyID
              LEFT  JOIN lists_generic AS jc  ON tmp_lJobCode  = jc.lgen_lKeyID
           ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID, strJobCode;";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows == 0) return('<br><i>There are no records that meet your search criteria.</i>');


      $strOut = '
            <table class="enpRptC" style="width: 600pt;">
                <tr>
                   <td colspan="6" class="enpRptTitle">
                      Job Code Summary: '.$strMoYr.'
                   </td>
                </tr>
               <tr>
                  <td class="enpRptLabel">
                     vol ID
                  </td>
                  <td class="enpRptLabel">
                     Volunteer
                  </td>
                  <td class="enpRptLabel">
                     Job Code
                  </td>
                  <td class="enpRptLabel">
                     Scheduled<br>Hours
                  </td>
                  <td class="enpRptLabel">
                     Unscheduled<br>Hours
                  </td>
                  <td class="enpRptLabel">
                     Total
                  </td>
               </tr>';
                
                ;
      $sngTotS = $sngTotU = 0.0;
      $lVIDHold = -999;
      foreach ($query->result() as $row){
         $lVolID = (int)$row->tmp_lVolID;
         if ($lVIDHold != $lVolID){
            $lVIDHold = $lVolID;
            $strName = htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName);
         }else {
            $strName = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"&nbsp;&nbsp;&nbsp;"';
         }
         if (is_null($row->strJobCode)){
            $strJobCode = '<i>No job code</i>';
         }else {
            $strJobCode = htmlspecialchars($row->strJobCode);
         }
         $strOut .=
            '
               <tr class="makeStripe">
                  <td class="enpRpt" style="text-align: center; width: 40pt;">'
                     .str_pad($lVolID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                     .strLinkView_Volunteer($lVolID, 'View volunteer record', true, ' id="vrec'.$lVolID.'" ').'
                  </td>
                  <td class="enpRpt">'
                     .$strName.'
                  </td>
                  <td class="enpRpt">'
                     .$strJobCode.'
                  </td>
                  <td class="enpRpt" style="text-align: right; width: 40pt;">'
                     .number_format($row->tmp_dHoursScheduled, 2).'&nbsp;'
                     .strLinkView_VolHrsViaVolID($lVolID, true, 'View details', true).'
                  </td>
                  <td class="enpRpt" style="text-align: right; width: 40pt;">'
                     .number_format($row->tmp_dHoursUnscheduled, 2).'&nbsp;'
                     .strLinkView_VolHrsViaVolID($lVolID, false, 'View details', true).'
                  </td>
                  <td class="enpRpt" style="text-align: right; width: 40pt;">'
                     .number_format($row->tmp_dHoursScheduled + $row->tmp_dHoursUnscheduled, 2).'
                  </td>
               </tr>';
         $sngTotS += $row->tmp_dHoursScheduled;
         $sngTotU += $row->tmp_dHoursUnscheduled;
      }
      $strOut .=
            '
               <tr >
                  <td class="enpRptLabel" colspan="3">
                     Total
                  </td>
                  <td class="enpRpt" style="text-align: right; padding-right: 15pt;"><b>'
                     .number_format($sngTotS, 2).'</b>
                  </td>
                  <td class="enpRpt" style="text-align: right; padding-right: 15pt;"><b>'
                     .number_format($sngTotU, 2).'</b>
                  </td>
                  <td class="enpRpt" style="text-align: right;"><b>'
                     .number_format($sngTotS + $sngTotU, 2).'</b>
                  </td>
               </tr>';

      $strOut .= '</table><br><br>'."\n";
      return($strOut);
   }

   function buildTmpTabJobCodeSumMo($sRpt, $strTmpTable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $vols = array();

         // unscheduled volunteers
      $sqlStr =
           "SELECT vsa_lVolID, vsa_lJobCode, SUM(vsa_dHoursWorked) AS dHoursWorked
            FROM vol_events_dates_shifts_assign
            WHERE NOT vsa_bRetired
               AND MONTH(vsa_dteActivityDate) =  $sRpt->lMonth
               AND YEAR (vsa_dteActivityDate) =  $sRpt->lYear
            GROUP BY vsa_lVolID, vsa_lJobCode;";
      $query  = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         foreach ($query->result() as $row){
            $lVolID   = (int)$row->vsa_lVolID;
            $lJobCode = (int)$row->vsa_lJobCode;
            $vols[$lVolID][$lJobCode] = new stdClass;
            $vols[$lVolID][$lJobCode]->unscheduled = (float)$row->dHoursWorked;
            $vols[$lVolID][$lJobCode]->scheduled   = 0.0;
         }
      }

         // scheduled volunteers
      $sqlStr =
           "SELECT vsa_lVolID, vs_lJobCode, SUM(vsa_dHoursWorked) AS dHoursWorked
            FROM vol_events_dates_shifts_assign
               INNER JOIN vol_events_dates_shifts ON vsa_lEventDateShiftID = vs_lKeyID
               INNER JOIN  vol_events_dates       ON vs_lEventDateID       = ved_lKeyID
            WHERE NOT vsa_bRetired
               AND MONTH(ved_dteEvent) =  $sRpt->lMonth
               AND YEAR (ved_dteEvent) =  $sRpt->lYear
            GROUP BY vsa_lVolID, vs_lJobCode;";
      $query  = $this->db->query($sqlStr);
      $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         foreach ($query->result() as $row){
            $lVolID = (int)$row->vsa_lVolID;
            $lJobCode = (int)$row->vs_lJobCode;
            if (isset($vols[$lVolID][$lJobCode])){
               $vols[$lVolID][$lJobCode]->scheduled = (float)$row->dHoursWorked;
            }else {
               $vols[$lVolID][$lJobCode] = new stdClass;
               $vols[$lVolID][$lJobCode]->unscheduled = 0.0;
               $vols[$lVolID][$lJobCode]->scheduled = (float)$row->dHoursWorked;
            }
         }
      }

      $sqlStr = "DROP TABLE IF EXISTS $strTmpTable;";
      $this->db->query($sqlStr);

      $sqlStr = "
         CREATE TEMPORARY TABLE IF NOT EXISTS $strTmpTable (
           tmp_lKeyID            int(11) NOT NULL AUTO_INCREMENT,
           tmp_lVolID            int(11) NOT NULL ,
           tmp_lJobCode          int(11) DEFAULT NULL ,
           tmp_dHoursScheduled   decimal(10,2) NOT NULL DEFAULT '0.00',
           tmp_dHoursUnscheduled decimal(10,2) NOT NULL DEFAULT '0.00',

           PRIMARY KEY      (tmp_lKeyID),
           KEY tmp_lVolID   (tmp_lVolID),
           KEY tmp_lJobCode (tmp_lJobCode)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
      $this->db->query($sqlStr);

      if (count($vols) > 0){
         foreach ($vols as $lVolID=>$jcTable){
            foreach ($jcTable as $lJC=>$dHours){
               if ($lJC <= 0){
                  $strJC = 'NULL';
               }else {
                  $strJC = $lJC.'';
               }
               $sqlStr =
                 "INSERT INTO $strTmpTable
                  SET
                     tmp_lVolID = $lVolID,
                     tmp_lJobCode = $strJC,
                     tmp_dHoursScheduled=$dHours->scheduled,
                     tmp_dHoursUnscheduled=$dHours->unscheduled;";
               $this->db->query($sqlStr);
            }
         }
      }
   }

   function strShiftJobCodesDetailsViaMonth($strMoYr, $sRpt, $bAllJobCodes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strOut = '
            <table class="enpRptC" style="width: 600pt;">
                <tr>
                   <td colspan="6" class="enpRptTitle">
                      Job Code Details for Scheduled Shifts: '.$strMoYr.'
                   </td>
                </tr>
                <tr>
                   <td class="enpRptLabel">
                      Event
                   </td>
                   <td class="enpRptLabel">
                      Shift/Date
                   </td>
                   <td class="enpRptLabel">
                      Volunteer
                   </td>
                   <td class="enpRptLabel">
                      Job Code
                   </td>
                   <td class="enpRptLabel">
                      Hours
                   </td>
                </tr>
                ';
      if ($bAllJobCodes){
         $strWhereJC = '';
      }else {
         $strWhereJC = ' AND vs_lJobCode='.$sRpt->lJobCodeID.' ';
      }

      $sqlStr =
        'SELECT vs_lJobCode, ved_dteEvent, vsa_dHoursWorked, vsa_lVolID, vem_lKeyID, ved_lKeyID,
            jc.lgen_strListItem AS strJobCode,
            vem_strEventName, vs_strShiftName,
            pe_strFName, pe_strLName
         FROM vol_events_dates
            INNER JOIN vol_events_dates_shifts        ON vs_lEventDateID       = ved_lKeyID
            INNER JOIN vol_events_dates_shifts_assign ON vsa_lEventDateShiftID = vs_lKeyID
            INNER JOIN vol_events                     ON ved_lVolEventID       = vem_lKeyID
            INNER JOIN volunteers                     ON vol_lKeyID            = vsa_lVolID
            INNER JOIN people_names                   ON vol_lPeopleID         = pe_lKeyID
            LEFT  JOIN lists_generic   AS jc          ON vs_lJobCode           = jc.lgen_lKeyID
         WHERE NOT vs_bRetired AND NOT vsa_bRetired
            AND YEAR (ved_dteEvent)='.$sRpt->lYear.'
            AND MONTH(ved_dteEvent)='.$sRpt->lMonth.'
            AND vsa_dHoursWorked > 0
            '.$strWhereJC.'
         ORDER BY vem_strEventName, strJobCode, ved_dteEvent, pe_strLName, pe_strFName, pe_lKeyID;';

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      $idx = 0;
      $sngTotHrs = 0.0;
      if ($numRows > 0){
         foreach ($query->result() as $row){
            $dteEvent = dteMySQLDate2Unix($row->ved_dteEvent);
            $strJobCode = ($row->strJobCode.'' == '') ? '(not set)' : htmlspecialchars($row->strJobCode);
            $strOut .= '
                <tr class="makeStripe">
                   <td class="enpRpt" style="width: 150pt;">'
                      .strLinkView_VolEvent($row->vem_lKeyID, 'View event', true).'&nbsp;'
                      .htmlspecialchars($row->vem_strEventName).'
                   </td>
                   <td class="enpRpt" style="width: 130pt;">'
                      .strLinkView_VolEventDate($row->ved_lKeyID, 'View event date and shifts', true).'&nbsp;'
                      .htmlspecialchars($row->vs_strShiftName).'<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                      .date($genumDateFormat, $dteEvent).'
                   </td>
                   <td class="enpRpt" style="width: 130pt;">'
                      .strLinkView_Volunteer($row->vsa_lVolID, 'Volunteer Record', true).'&nbsp;'
                      .htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName).'
                   </td>
                   <td class="enpRpt" style="width: 100pt;">'
                      .$strJobCode.'
                   </td>
                   <td class="enpRpt" style="width: 35pt; padding-right: 10px; text-align: right;">'
                      .number_format($row->vsa_dHoursWorked, 2).'
                   </td>
                </tr>';
            $sngTotHrs += $row->vsa_dHoursWorked;
         }
      }

      $strOut .= '
          <tr>
             <td class="enpRpt" colspan="4"><b>
                Total:</b>
             </td>
             <td class="enpRpt" style="width: 35pt; padding-right: 10px; text-align: right;"><b>'
                .number_format($sngTotHrs, 2).'</b>
             </td>
          </tr>';

      $strOut .= '</table>'."\n";
      return($strOut);
   }

   function strUnJobCodesDetailsViaMonth($strMoYr, $sRpt, $bAllJobCodes){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $strOut = '<br><br>
            <table class="enpRptC" style="width: 600pt;">
                <tr>
                   <td colspan="4" class="enpRptTitle">
                      Job Code Details for Unschedule Volunteers: '.$strMoYr.'
                   </td>
                </tr>
                <tr>
                   <td class="enpRptLabel">
                      Date
                   </td>
                   <td class="enpRptLabel">
                      Volunteer
                   </td>
                   <td class="enpRptLabel">
                      Job Code
                   </td>
                   <td class="enpRptLabel">
                      Hours
                   </td>
                </tr>
                ';
      if ($bAllJobCodes){
         $strWhereJC = '';
      }else {
         $strWhereJC = ' AND vsa_lJobCode='.$sRpt->lJobCodeID.' ';
      }
      $sqlStr =
        'SELECT vsa_lJobCode, vsa_dHoursWorked, vsa_lVolID,
            jc.lgen_strListItem AS strJobCode, vsa_dteActivityDate,
            pe_strFName, pe_strLName
         FROM vol_events_dates_shifts_assign
            INNER JOIN volunteers                     ON vol_lKeyID            = vsa_lVolID
            INNER JOIN people_names                   ON vol_lPeopleID         = pe_lKeyID
            LEFT  JOIN lists_generic   AS jc          ON vsa_lJobCode          = jc.lgen_lKeyID
         WHERE NOT vsa_bRetired
            AND YEAR (vsa_dteActivityDate)='.$sRpt->lYear.'
            AND MONTH(vsa_dteActivityDate)='.$sRpt->lMonth.'
            AND vsa_dHoursWorked > 0
            '.$strWhereJC.'
         ORDER BY strJobCode, vsa_dteActivityDate, pe_strLName, pe_strFName, pe_lKeyID;';

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      $idx = 0;
      $sngTotHrs = 0.0;
      if ($numRows > 0){
         foreach ($query->result() as $row){
            $dteActivity = dteMySQLDate2Unix($row->vsa_dteActivityDate);
            $strJobCode = ($row->strJobCode.'' == '') ? '(not set)' : htmlspecialchars($row->strJobCode);
            $strOut .= '
                <tr class="makeStripe">
                   <td class="enpRpt" style="width: 130pt;">'
                      .date($genumDateFormat, $dteActivity).'
                   </td>
                   <td class="enpRpt" style="width: 130pt;">'
                      .strLinkView_Volunteer($row->vsa_lVolID, 'Volunteer Record', true).'&nbsp;'
                      .htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName).'
                   </td>
                   <td class="enpRpt" style="width: 100pt;">'
                      .$strJobCode.'
                   </td>
                   <td class="enpRpt" style="width: 35pt; padding-right: 10px; text-align: right;">'
                      .number_format($row->vsa_dHoursWorked, 2).'
                   </td>
                </tr>';
            $sngTotHrs += $row->vsa_dHoursWorked;
         }
      }

      $strOut .= '
          <tr>
             <td class="enpRpt" colspan="3"><b>
                Total:</b>
             </td>
             <td class="enpRpt" style="width: 35pt; padding-right: 10px; text-align: right;"><b>'
                .number_format($sngTotHrs, 2).'</b>
             </td>
          </tr>';

      $strOut .= '</table>'."\n";
      return($strOut);
   }

}
