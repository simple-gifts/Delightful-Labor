<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('vols/mvol_events', 'clsVolEvents');
--------------------------------------------------------------------
   __construct         ()
   loadEvents          ()
   loadEventsViaEID    ($lEventID)
   loadEventsGeneric   ($strWhere)
   lAddNewEvent        ()
   updateEvent         ()
   deleteEvent         ($lEventID)

   volEventHTMLSummary ()

---------------------------------------------------------------------*/


class mvol_events extends CI_Model{

   public
       $lVolID, $lEventID,
       $bCurrentFuture, $bAllEvents, $dteStart, $dteEnd, $bPastEvents,
       $lNumEvents, $events;

   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->lVolID   = $this->lEventID =
      $this->bCurrentFuture = $this->bAllEvents = $this->bPastEvents =
      $this->dteStart = $this->dteEnd   = $this->lNumEvents     = $this->events     = null;
   }

   function entireEventInfo($lEventID, &$lNumEvents, &$events){
   /*-----------------------------------------------------------------------
      $lEventID can be a single event ID or an array of event IDs

      returns an array that has event info, dates, shifts, and vols

      $this->load->model('vols/mvol_events',                  'clsVolEvents');
      $this->load->model('vols/mvol_event_dates',             'clsVolEventDates');
      $this->load->model('vols/mvol_event_dates_shifts',      'clsShifts');
      $this->load->model('vols/mvol_event_dates_shifts_vols', 'clsSV');

   -----------------------------------------------------------------------*/
      $clsVolEventDates = new mvol_event_dates;
      $clsShifts        = new mvol_event_dates_shifts;
      $clsSV            = new mvol_event_dates_shifts_vols;

      $this->loadEventsViaEID($lEventID);
      $lNumEvents = $this->lNumEvents;
      $events = arrayCopy($this->events);

      foreach ($events as $event){
         $lEID = $event->lKeyID;
         $clsVolEventDates->loadEventDates($lEID);

         $event->lNumDates = $lNumDates = $clsVolEventDates->lNumDates;
         if ($lNumDates > 0){
            $event->dates = arrayCopy($clsVolEventDates->dates);
            foreach ($event->dates as $edate){
               $lEventDateID = $edate->lKeyID;
               if ($edate->lNumShifts > 0){
                  $clsShifts->loadShiftsViaEventDateID($lEventDateID);
                  $edate->shifts = arrayCopy($clsShifts->shifts);
                  foreach ($edate->shifts as $shift){
                     $lShiftID = $shift->lKeyID;
                     $clsSV->loadVolsViaShiftID($lShiftID);
                     $shift->lNumVols = $lNumVols = $clsSV->lNumVols;
                     if ($lNumVols > 0){
                        $shift->vols = arrayCopy($clsSV->vols);
                     }
                  }
               }
            }
         }
      }
   }

   public function loadEvents() {
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $gdteNow;
      if ($this->bCurrentFuture){
         $strWhereDate = ' AND vem_dteEventEndDate >= '.strPrepDate($gdteNow).' ';
      }elseif ($this->bPastEvents){
         $strWhereDate = ' AND vem_dteEventEndDate < '.strPrepDate($gdteNow).' ';
      }elseif ($this->bAllEvents){
         $strWhereDate = '';
      }else {
         whereAmI(); die;
      }
      $this->loadEventsGeneric($strWhereDate);
   }

   public function loadEventsViaEID($lEventID) {
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_array($lEventID)){
         $strSQL = ' AND vem_lKeyID IN ('.implode(',', $lEventID).') ';
      }else {
         $strSQL = " AND vem_lKeyID=$lEventID ";
      }
      $this->loadEventsGeneric($strSQL);
   }

   private function loadEventsGeneric($strWhere) {
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $clsEventDates = new mvol_event_dates;
      $clsEventDates->updateStartStopDates();
      $sqlStr =
        "SELECT
            vem_lKeyID, vem_strEventName, vem_strDescription,
            vem_strLocation, vem_strContact, vem_strPhone, vem_strEmail,
            vem_strWebSite, vem_bRetired,
            vem_lOriginID, vem_lLastUpdateID,
            vem_dteEventStartDate, vem_dteEventEndDate,

            UNIX_TIMESTAMP(vem_dteOrigin) AS dteOrigin,
            UNIX_TIMESTAMP(vem_dteLastUpdate) AS dteLastUpdate,

            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM vol_events
            INNER JOIN admin_users   AS uc ON uc.us_lKeyID=vem_lOriginID
            INNER JOIN admin_users   AS ul ON ul.us_lKeyID=vem_lLastUpdateID

         WHERE
            NOT vem_bRetired
            $strWhere
         ORDER BY vem_dteEventStartDate, vem_strEventName, vem_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumEvents = $numRows = $query->num_rows();
      $this->events = array();
      if ($numRows==0) {
            $this->events[0] = new stdClass;

            $this->events[0]->lKeyID         =
            $this->events[0]->strEventName   =
            $this->events[0]->strDescription =
            $this->events[0]->strLocation    =
            $this->events[0]->strContact     =
            $this->events[0]->strPhone       =
            $this->events[0]->strEmail       =
            $this->events[0]->strWebSite     =
            $this->events[0]->bRetired       =
            $this->events[0]->lOriginID      =
            $this->events[0]->lLastUpdateID  =
            $this->events[0]->dteEventStart  =
            $this->events[0]->dteEventEnd    =
            $this->events[0]->dteOrigin      =
            $this->events[0]->dteLastUpdate  =
            $this->events[0]->strUCFName     =
            $this->events[0]->strUCLName     =
            $this->events[0]->strULFName     =
            $this->events[0]->strULLName     = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->events[$idx] = new stdClass;

            $this->events[$idx]->lKeyID         = (int)$row->vem_lKeyID;
            $this->events[$idx]->strEventName   = $row->vem_strEventName;
            $this->events[$idx]->strDescription = $row->vem_strDescription;
            $this->events[$idx]->strLocation    = $row->vem_strLocation;
            $this->events[$idx]->strContact     = $row->vem_strContact;
            $this->events[$idx]->strPhone       = $row->vem_strPhone;
            $this->events[$idx]->strEmail       = $row->vem_strEmail;
            $this->events[$idx]->strWebSite     = $row->vem_strWebSite;
            $this->events[$idx]->bRetired       = (bool)$row->vem_bRetired;
            $this->events[$idx]->lOriginID      = $row->vem_lOriginID;
            $this->events[$idx]->lLastUpdateID  = $row->vem_lLastUpdateID;
            $this->events[$idx]->dteEventStart  = dteMySQLDate2Unix($row->vem_dteEventStartDate);
            $this->events[$idx]->dteEventEnd    = dteMySQLDate2Unix($row->vem_dteEventEndDate);
            $this->events[$idx]->dteOrigin      = $row->dteOrigin;
            $this->events[$idx]->dteLastUpdate  = $row->dteLastUpdate;
            $this->events[$idx]->strUCFName     = $row->strUCFName;
            $this->events[$idx]->strUCLName     = $row->strUCLName;
            $this->events[$idx]->strULFName     = $row->strULFName;
            $this->events[$idx]->strULLName     = $row->strULLName;

            ++$idx;
         }
      }
   }

   public function lAddNewEvent(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        'INSERT INTO vol_events
         SET '.$this->strSQLCommonVolEvent().",
         vem_bRetired  = 0,
         vem_lOriginID = $glUserID,
         vem_dteOrigin = NOW();";

      $query = $this->db->query($sqlStr);
      return($this->events[0]->lKeyID = $this->db->insert_id());
   }

   public function updateEvent(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $clsE = $this->events[0];
      $sqlStr =
        'UPDATE vol_events
         SET '.$this->strSQLCommonVolEvent()."
         WHERE vem_lKeyID=$clsE->lKeyID;";

      $this->db->query($sqlStr);
   }

   private function strSQLCommonVolEvent(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $clsE = $this->events[0];
      return('
         vem_strEventName   = '.strPrepStr($clsE->strEventName)  .',
         vem_strDescription = '.strPrepStr($clsE->strDescription).',
         vem_strLocation    = '.strPrepStr($clsE->strLocation)   .',
         vem_strContact     = '.strPrepStr($clsE->strContact)    .',
         vem_strPhone       = '.strPrepStr($clsE->strPhone)      .',
         vem_strEmail       = '.strPrepStr($clsE->strEmail)      .',
         vem_strWebSite     = '.strPrepStr($clsE->strWebSite)    .",

         vem_lLastUpdateID  = $glUserID,
         vem_dteLastUpdate  = NOW() ");
   }

   public function deleteEvent($lEventID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID;

      $clsE = $this->events[0];
      $sqlStr =
        "DELETE
             vol_events,
             vol_events_dates,
             vol_events_dates_shifts,
             vol_events_dates_shifts_assign
         FROM vol_events
            LEFT JOIN vol_events_dates               ON vem_lKeyID = ved_lVolEventID
            LEFT JOIN vol_events_dates_shifts        ON ved_lKeyID = vs_lEventDateID
            LEFT JOIN vol_events_dates_shifts_assign ON vs_lKeyID  = vsa_lEventDateShiftID
         WHERE vem_lKeyID=$lEventID;";

      $this->db->query($sqlStr);
   }

   public function volEventHTMLSummary($idx){
   //-----------------------------------------------------------------------
   // assumes user has called $clsEvent->loadEventsViaEID($lEventID);
   //-----------------------------------------------------------------------
      global $gdteNow, $genumDateFormat;

      $clsVE    = $this->events[$idx];
      $lEventID = $clsVE->lKeyID;

      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      return(
          $clsRpt->openReport('', '')

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Event ID:')
         .$clsRpt->writeCell (strLinkView_VolEvent($lEventID, 'View event', true).'&nbsp;'
                             .str_pad($lEventID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Volunteer Event:')
         .$clsRpt->writeCell ($clsVE->strEventName)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('Start Date:')
         .$clsRpt->writeCell (date($genumDateFormat.' (D)', $clsVE->dteEventStart))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   ()
         .$clsRpt->writeLabel('End Date:')
         .$clsRpt->writeCell (date($genumDateFormat.' (D)', $clsVE->dteEventEnd))
         .$clsRpt->closeRow  ()

         .$clsRpt->closeReport('<br>'));
   }



   /*--------------------------------------------------------------
                        R E P O R T S
   ---------------------------------------------------------------*/

   function scheduleReportExport(&$sRpt, &$displayData, $bReport){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         $this->scheduleReport($sRpt, $displayData);
      }else {
         return($this->scheduleExport($sRpt));
      }
   }

   function scheduleReport(&$sRpt, &$displayData){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $displayData['strReportID'] = $sRpt->reportID;
      $this->entireEventInfo($sRpt->lEventIDs, $displayData['lNumEvents'], $displayData['events']);
   }

   function scheduleExport(&$sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterVoc;

      $strDateFormat     = strMysqlDateFormat(false);
      $this->entireEventInfo($sRpt->lEventIDs, $lNumEvents, $events);

      $strTempTable = 'tmpExp';
      $this->buildEventScheduleExportTable($strTempTable);

      foreach ($events as $event){
         $rowFields = array(); $fieldIDX = 0;
         $this->setExportField($rowFields, $fieldIDX, 'texp_lEventID',       (string)$event->lKeyID);
         $this->setExportField($rowFields, $fieldIDX, 'texp_strEventName',   strPrepStr($event->strEventName));
         $this->setExportField($rowFields, $fieldIDX, 'texp_strDescription', strPrepStr($event->strDescription));

         $this->setExportField($rowFields, $fieldIDX, 'texp_dteEventStartDate', strPrepDate($event->dteEventStart));
         $this->setExportField($rowFields, $fieldIDX, 'texp_dteEventEndDate',   strPrepDate($event->dteEventEnd));
         $this->setExportField($rowFields, $fieldIDX, 'texp_strLocation',       strPrepStr($event->strLocation));
         $this->setExportField($rowFields, $fieldIDX, 'texp_strContact',        strPrepStr($event->strContact));
         $this->setExportField($rowFields, $fieldIDX, 'texp_strPhone',          strPrepStr($event->strPhone));
         $this->setExportField($rowFields, $fieldIDX, 'texp_strEmail',          strPrepStr($event->strEmail));
         $this->setExportField($rowFields, $fieldIDX, 'texp_strWebSite',        strPrepStr($event->strWebSite));

         if ($event->lNumDates > 0){
            foreach ($event->dates as $edate){
               $this->setExportField($rowFields, $fieldIDX, 'texp_lDateID',       (string)$edate->lKeyID);
               $this->setExportField($rowFields, $fieldIDX, 'texp_dteEventDate', strPrepDate($edate->dteEvent));
               if ($edate->lNumShifts > 0){
                  foreach ($edate->shifts as $shift){
                     $this->setExportField($rowFields, $fieldIDX, 'texp_lShiftID',           (string)$shift->lKeyID);
                     $this->setExportField($rowFields, $fieldIDX, 'texp_strShiftName',        strPrepStr($shift->strShiftName));
                     $this->setExportField($rowFields, $fieldIDX, 'texp_strShiftDescription', strPrepStr($shift->strDescription));
                     $this->setExportField($rowFields, $fieldIDX, 'texp_dteShiftStartTime',   strPrepDateTime($shift->dteEventStartTime));
                     $this->setExportField($rowFields, $fieldIDX, 'texp_enumDuration',        strPrepStr($shift->enumDuration));
                     $this->setExportField($rowFields, $fieldIDX, 'texp_lNumVolsNeeded',      (string)$shift->lNumVolsNeeded);

                     if ($shift->lNumVols > 0){
                        foreach ($shift->vols as $vol){
                           $this->setExportField($rowFields, $fieldIDX, 'texp_lVolID',    (string)$vol->lVolID);
                           $this->setExportField($rowFields, $fieldIDX, 'texp_lPeopleID', (string)$vol->lPeopleID);
                           $this->setExportField($rowFields, $fieldIDX, 'texp_strFName',    strPrepStr($vol->strFName));
                           $this->setExportField($rowFields, $fieldIDX, 'texp_strLName',    strPrepStr($vol->strLName));
                           $this->setExportField($rowFields, $fieldIDX, 'texp_strAddr1',    strPrepStr($vol->strAddr1));
                           $this->setExportField($rowFields, $fieldIDX, 'texp_strAddr2',    strPrepStr($vol->strAddr2));
                           $this->setExportField($rowFields, $fieldIDX, 'texp_strCity',     strPrepStr($vol->strCity));
                           $this->setExportField($rowFields, $fieldIDX, 'texp_strState',    strPrepStr($vol->strState));
                           $this->setExportField($rowFields, $fieldIDX, 'texp_strCountry',  strPrepStr($vol->strCountry));
                           $this->setExportField($rowFields, $fieldIDX, 'texp_strZip',      strPrepStr($vol->strZip));
                           $this->setExportField($rowFields, $fieldIDX, 'texp_strVolPhone', strPrepStr($vol->strPhone));
                           $this->setExportField($rowFields, $fieldIDX, 'texp_strCell',     strPrepStr($vol->strCell));
                           $this->setExportField($rowFields, $fieldIDX, 'texp_strVolEmail', strPrepStr($vol->strEmail));

                           $this->writeTempScheduleExportRow($strTempTable, $rowFields);
                        }
                     }else {
                        $this->writeTempScheduleExportRow($strTempTable, $rowFields);
                     }
                     $this->expClearVolFields($rowFields);
                  }
                  $this->expClearShiftFields($rowFields);
               }else {
                  $this->writeTempScheduleExportRow($strTempTable, $rowFields);
               }
            }
            $this->expClearDateFields($rowFields);
         }else {
            $this->writeTempScheduleExportRow($strTempTable, $rowFields);
         }
      }

      $sqlStr =
        "SELECT
            texp_lEventID              AS `event ID`,
            texp_strEventName          AS `Event Name`,
            texp_strDescription        AS `Event Description`,
            DATE_FORMAT(texp_dteEventStartDate, $strDateFormat) AS `Event Start Date`,
            DATE_FORMAT(texp_dteEventEndDate,   $strDateFormat) AS `Event End Date`,
            texp_strLocation           AS `Event Location`,
            texp_strContact            AS `Event Contact`,
            texp_strPhone              AS `Contact Phone`,
            texp_strEmail              AS `Contact Email`,
            texp_strWebSite            AS `Event Web Site`,
            texp_lDateID               AS `event date ID`,
            DATE_FORMAT(texp_dteEventDate, $strDateFormat) AS `Event Date`,
            DATE_FORMAT(texp_dteEventDate, '%W')           AS `Event Day of Week`,
            texp_lShiftID              AS `shift ID`,
            texp_strShiftName          AS `Shift Name`,
            texp_strShiftDescription   AS `Shift Description`,
            DATE_FORMAT(texp_dteShiftStartTime, '%l:%i %p') AS `Shift Start Time`,
            texp_enumDuration          AS `Duration`,
            texp_lNumVolsNeeded        AS `Num. Vols Needed`,
            texp_lVolID                AS `volunteer ID`,
            texp_lPeopleID             AS `people ID`,
            texp_strFName              AS `Vol. First Name`,
            texp_strLName              AS `Vol. Last Name`,
            texp_strAddr1              AS `Vol. Address 1`,
            texp_strAddr2              AS `Vol. Address 2`,
            texp_strCity               AS `Vol. City`,
            texp_strState              AS `Vol. $gclsChapterVoc->vocState`,
            texp_strCountry            AS `Vol. Country`,
            texp_strZip                AS `Vol. $gclsChapterVoc->vocZip`,
            texp_strVolPhone           AS `Vol. Phone`,
            texp_strCell               AS `Vol. Cell`,
            texp_strVolEmail           AS `Vol. Email`

         FROM $strTempTable
         WHERE 1
         ORDER BY texp_lKeyID;";
      $query = $this->db->query($sqlStr);
      return($this->dbutil->csv_from_result($query));
   }

   private function expClearDateFields(&$rowFields){
      unset($rowFields['texp_lDateID'     ]);
      unset($rowFields['texp_dteEventDate']);
   }

   private function expClearShiftFields(&$rowFields){
      unset($rowFields['texp_lShiftID'           ]);
      unset($rowFields['texp_strShiftName'       ]);
      unset($rowFields['texp_strShiftDescription']);
      unset($rowFields['texp_dteShiftStartTime'  ]);
      unset($rowFields['texp_enumDuration'       ]);
      unset($rowFields['texp_lNumVolsNeeded'     ]);
   }

   private function expClearVolFields(&$rowFields){
      unset($rowFields['texp_lVolID'     ]);
      unset($rowFields['texp_lPeopleID'  ]);
      unset($rowFields['texp_strFName'   ]);
      unset($rowFields['texp_strLName'   ]);
      unset($rowFields['texp_strAddr1'   ]);
      unset($rowFields['texp_strAddr2'   ]);
      unset($rowFields['texp_strCity'    ]);
      unset($rowFields['texp_strState'   ]);
      unset($rowFields['texp_strCountry' ]);
      unset($rowFields['texp_strZip'     ]);
      unset($rowFields['texp_strVolPhone']);
      unset($rowFields['texp_strCell'    ]);
      unset($rowFields['texp_strVolEmail']);
   }

   private function writeTempScheduleExportRow($strTempTable, &$rowFields){
      $sqlStr = '';
      foreach ($rowFields as $strFName=>$fieldValue){
         $sqlStr .= ', '.$strFName.' = '.$fieldValue."\n";
      }
      $sqlStr = "INSERT INTO $strTempTable SET ".substr($sqlStr, 1).';';
      $query = $this->db->query($sqlStr);
   }

   private function setExportField(&$rowFields, &$fieldIDX, $strFN, $strFValue){
       $rowFields[$strFN] = $strFValue;
   }

   private function buildEventScheduleExportTable($strTableName){
   //---------------------------------------------------------------------
   // 
   //---------------------------------------------------------------------      
      $sqlStr = "DROP TABLE IF EXISTS $strTableName;";
      $query = $this->db->query($sqlStr);

//          "CREATE TABLE IF NOT EXISTS $strTableName (
      $sqlStr =
          "CREATE TEMPORARY TABLE IF NOT EXISTS $strTableName (
              texp_lKeyID                    int(11) NOT NULL AUTO_INCREMENT,
              texp_lEventID                  int(11) NOT NULL,
              texp_strEventName              varchar(255) NOT NULL DEFAULT '',
              texp_strDescription            text NOT NULL,
              texp_dteEventStartDate         date NOT NULL DEFAULT '0000-00-00',
              texp_dteEventEndDate           date NOT NULL DEFAULT '0000-00-00',
              texp_strLocation               text NOT NULL,
              texp_strContact                varchar(255) NOT NULL DEFAULT '',
              texp_strPhone                  varchar(80)  NOT NULL DEFAULT '',
              texp_strEmail                  varchar(200) NOT NULL DEFAULT '',
              texp_strWebSite                varchar(200) NOT NULL DEFAULT '',

              texp_lDateID                   int(11) DEFAULT NULL,
              texp_dteEventDate              date DEFAULT NULL,

              texp_lShiftID                  int(11) DEFAULT NULL,
              texp_strShiftName              varchar(255) NOT NULL DEFAULT '',
              texp_strShiftDescription       text,
              texp_dteShiftStartTime         time DEFAULT NULL,
              texp_enumDuration              enum('(all day)','15 minutes','30 minutes','45 minutes','1 hour','1 hour 15 minutes','1 hour 30 minutes','1 hour 45 minutes','2 hours','2 hours 15 minutes','2 hours 30 minutes','2 hours 45 minutes','3 hours','3 hours 15 minutes','3 hours 30 minutes','3 hours 45 minutes','4 hours','4 hours 15 minutes','4 hours 30 minutes','4 hours 45 minutes','5 hours','5 hours 15 minutes','5 hours 30 minutes','5 hours 45 minutes','6 hours','6 hours 15 minutes','6 hours 30 minutes','6 hours 45 minutes','7 hours','7 hours 15 minutes','7 hours 30 minutes','7 hours 45 minutes','8 hours','8 hours 15 minutes','8 hours 30 minutes','8 hours 45 minutes','9 hours','9 hours 15 minutes','9 hours 30 minutes','9 hours 45 minutes','10 hours','10 hours 15 minutes','10 hours 30 minutes','10 hours 45 minutes','11 hours','11 hours 15 minutes','11 hours 30 minutes','11 hours 45 minutes','12 hours','12 hours 15 minutes','12 hours 30 minutes','12 hours 45 minutes','13 hours','13 hours 15 minutes','13 hours 30 minutes','13 hours 45 minutes','14 hours','14 hours 15 minutes','14 hours 30 minutes','14 hours 45 minutes','15 hours','15 hours 15 minutes','15 hours 30 minutes','15 hours 45 minutes','16 hours','16 hours 15 minutes','16 hours 30 minutes','16 hours 45 minutes','17 hours','17 hours 15 minutes','17 hours 30 minutes','17 hours 45 minutes','18 hours','18 hours 15 minutes','18 hours 30 minutes','18 hours 45 minutes','19 hours','19 hours 15 minutes','19 hours 30 minutes','19 hours 45 minutes','20 hours','20 hours 15 minutes','20 hours 30 minutes','20 hours 45 minutes','21 hours','21 hours 15 minutes','21 hours 30 minutes','21 hours 45 minutes','22 hours','22 hours 15 minutes','22 hours 30 minutes','22 hours 45 minutes','23 hours','23 hours 15 minutes','23 hours 30 minutes','23 hours 45 minutes') NOT NULL DEFAULT '(all day)' COMMENT 'Duration',
              texp_lNumVolsNeeded            smallint(6) NOT NULL DEFAULT '0',

              texp_lVolID                    int(11) DEFAULT NULL,
              texp_lPeopleID                 int(11) DEFAULT NULL,

              texp_strFName                  varchar(80)  NOT NULL DEFAULT '',
              texp_strLName                  varchar(80)  NOT NULL DEFAULT '',
              texp_strAddr1                  varchar(80)  NOT NULL DEFAULT '',
              texp_strAddr2                  varchar(80)  NOT NULL DEFAULT '',
              texp_strCity                   varchar(80)  NOT NULL DEFAULT '',
              texp_strState                  varchar(80)  NOT NULL DEFAULT '',
              texp_strCountry                varchar(80)  NOT NULL DEFAULT '',
              texp_strZip                    varchar(40)  NOT NULL DEFAULT '',
              texp_strVolPhone               varchar(40)  NOT NULL DEFAULT '',
              texp_strCell                   varchar(40)  NOT NULL DEFAULT '',
              texp_strVolEmail               varchar(120) NOT NULL DEFAULT '',


           PRIMARY KEY (texp_lKeyID),
           KEY texp_lEventID        (texp_lEventID),
           KEY texp_dteEventStartDate    (texp_dteEventStartDate),
           KEY texp_dteEventEndDate (texp_dteEventEndDate),
           KEY texp_dteEventDate (texp_dteEventDate)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
      $query = $this->db->query($sqlStr);
   }

}

?>