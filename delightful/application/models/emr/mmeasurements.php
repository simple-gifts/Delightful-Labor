<?php
/*---------------------------------------------------------------------
// Delightful Labor
// copyright (c) 2013 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
  ---------------------------------------------------------------------
      $this->load->model('emr/mmeasurements', 'emrMeas');
      $this->load->library('util/dl_date_time', '', 'clsDateTime');
  ---------------------------------------------------------------------

---------------------------------------------------------------------*/


class mmeasurements extends CI_Model{
   public
      $lNumMeasure, $measurements,
      $strOrder, $strWhereExtra;


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->strOrder = $this->strWhereExtra = '';
      $this->lNumMeasure = 0;
      $this->measurements = null;
   }

   function lNumMeasureViaCID($lClientID, $bHeight, $bWeight, $bOFC){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strWhere = " AND meas_lClientID=$lClientID ".$this->strMeasureOrs($bHeight, $bWeight, $bOFC);
      $sqlStr =
         "SELECT COUNT(*) AS lNumRecs
          FROM emr_measurements
          WHERE NOT meas_bRetired $strWhere;";
      $query = $this->db->query($sqlStr);
      $lNumRecs = $query->num_rows();
      if ($lNumRecs == 0){
         return(0);
      }else {
         $row = $query->row();
         return($row->lNumRecs);
      }
   }

   function loadMeasurementViaMeasID($lMeasureID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = " AND meas_lKeyID=$lMeasureID ";
      $this->loadMeasurements();
   }

   function loadMeasurementViaClientID($lClientID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = " AND meas_lClientID=$lClientID ";
      $this->strOrder = ' dteMeasurement DESC, meas_lKeyID DESC';
      $this->loadMeasurements();
   }

   function loadMeasurements(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glclsDTDateFormat;

      $this->measurements = array();

      if ($this->strOrder==''){
         $strOrder = ' meas_lClientID, dteMeasurement DESC ';
      }else {
         $strOrder = $this->strOrder;
      }

      $sqlStr =
        "SELECT
            meas_lKeyID, meas_lClientID,
            meas_dteMeasurement,
            meas_sngHeadCircCM, meas_sngWeightKilos, meas_sngHeightCM,
            meas_strNotes,

            cr_strFName, cr_strMName, cr_strLName, cr_dteEnrollment,
            cr_dteBirth, cr_enumGender,

            cr_lLocationID, cl_strLocation, cl_strCountry,

            meas_lOriginID, meas_lLastUpdateID,
            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            UNIX_TIMESTAMP(meas_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(meas_dteLastUpdate) AS dteLastUpdate

         FROM emr_measurements
            INNER JOIN admin_users AS usersC   ON meas_lOriginID     = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL   ON meas_lLastUpdateID = usersL.us_lKeyID

            INNER JOIN client_records   ON meas_lClientID = cr_lKeyID
            INNER JOIN client_location  ON cr_lLocationID = cl_lKeyID

         WHERE NOT meas_bRetired $this->strWhereExtra
         ORDER BY $strOrder;";

      $query = $this->db->query($sqlStr);
      $this->lNumMeasure = $lNumMeasure = $query->num_rows();
      if ($lNumMeasure == 0){
         $this->measurements[0] = new stdClass;
         $measure = &$this->measurements[0];

         $measure->lClientID         =
         $measure->dteMeasurement    =
         $measure->mySQLDateMeasure  =

               // measurements
         $measure->sngHeadCircCM     =
         $measure->sngHeightCM       =
         $measure->sngWeightKilos    =

         $measure->lHeadCircIn       =
         $measure->lHeightIn         =
         $measure->lWeightLBS        =

         $measure->lHeightFt         =
         $measure->lHeightFtIn       =

            // client info
         $measure->strFName          =
         $measure->strMName          =
         $measure->strLName          =
         $measure->strSafeName       =
         $measure->strSafeNameLF     =
         $measure->dteEnrollment     =
         $measure->dteBirth          =
         $measure->enumGender        =
         $measure->lLocationID       =
         $measure->strLocation       =
         $measure->strLocCountry     = null;

            //------------------------------
            // client age/birth day info
            //------------------------------
         $measure->objClientBirth     =
         $measure->strClientAgeBDay   =

         $measure->strNotes           =

         $measure->meas_lOriginID     =
         $measure->meas_lLastUpdateID =
         $measure->strCFName          =
         $measure->strCLName          =
         $measure->strLFName          =
         $measure->strLLName          =
         $measure->dteOrigin          =
         $measure->dteLastUpdate      = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->measurements[$idx] = new stdClass;
            $measure = &$this->measurements[$idx];

            $measure->lKeyID            = $row->meas_lKeyID;
            $measure->lClientID         = $row->meas_lClientID;
            $measure->dteMeasurement    = dteMySQLDate2Unix($row->meas_dteMeasurement);
            $measure->mySQLDateMeasure  = $row->meas_dteMeasurement;

                  // measurements
            $measure->sngHeadCircCM     = $row->meas_sngHeadCircCM;
            $measure->sngHeightCM       = $row->meas_sngHeightCM;
            $measure->sngHeightM        = $row->meas_sngHeightCM/100.0;
            $measure->sngWeightKilos    = $row->meas_sngWeightKilos;
            $measure->sngBMI            = $row->meas_sngWeightKilos/pow($measure->sngHeightM, 2);

            if (is_null($measure->sngHeadCircCM)){
               $measure->sngHeadCircIn  = null;
            }else {
               $measure->sngHeadCircIn  = (round(($measure->sngHeadCircCM  * CSNG_CM_TO_INCHES), 1));
            }
            if (is_null($measure->sngHeightCM)){
               $measure->sngHeightIn    =
               $measure->lHeightFt      =
               $measure->lHeightFtIn    = null;
            }else {
               $measure->sngHeightIn    = (round(($measure->sngHeightCM    * CSNG_CM_TO_INCHES), 1));
               $measure->lHeightFt      = (int)($measure->sngHeightIn/12);
               $measure->lHeightFtIn    = (int)($measure->sngHeightIn - ($measure->lHeightFt*12));
            }
            if (is_null($measure->sngWeightKilos)){
               $measure->sngWeightLBS      = null;
            }else {
               $measure->sngWeightLBS      = (round(($measure->sngWeightKilos * CSNG_KILOS_TO_LBS), 1));
            }

               // client info
            $measure->strFName          = $row->cr_strFName;
            $measure->strMName          = $row->cr_strMName;
            $measure->strLName          = $row->cr_strLName;
            $measure->strSafeName       = htmlspecialchars($row->cr_strFName.' '.$row->cr_strLName);
            $measure->strSafeNameLF     = htmlspecialchars($row->cr_strLName.', '.$row->cr_strFName);
            $measure->dteEnrollment     = dteMySQLDate2Unix($row->cr_dteEnrollment);
            $measure->dteBirth          = $mySQLdteBirth = $row->cr_dteBirth;
            $measure->enumGender        = $row->cr_enumGender;
            $measure->lLocationID       = $row->cr_lLocationID;
            $measure->strLocation       = $row->cl_strLocation;
            $measure->strLocCountry     = $row->cl_strCountry;

               //------------------------------
               // client age/birth day info
               //------------------------------
            if (is_null($mySQLdteBirth)){
               $measure->objClientBirth = null;
               $measure->lAgeYears      = null;
               $measure->strClientAgeBDay = 'n/a';
            }else {
               $measure->objClientBirth = new dl_date_time;
               $measure->objClientBirth->setDateViaMySQL(0, $mySQLdteBirth);
               $measure->strClientAgeBDay = $measure->objClientBirth->strPeopleAge(0, $mySQLdteBirth,
                      $measure->lAgeYears, $glclsDTDateFormat);
            }

            $measure->strNotes           = $row->meas_strNotes;

            $measure->meas_lOriginID     = $row->meas_lOriginID;
            $measure->meas_lLastUpdateID = $row->meas_lLastUpdateID;
            $measure->strCFName          = $row->strCFName;
            $measure->strCLName          = $row->strCLName;
            $measure->strLFName          = $row->strLFName;
            $measure->strLLName          = $row->strLLName;
            $measure->dteOrigin          = $row->dteOrigin;
            $measure->dteLastUpdate      = $row->dteLastUpdate;

            ++$idx;
         }
      }
   }

   function strMeasureOrs($bHeight, $bWeight, $bOFC){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strWhere = '';
      if (!($bHeight && $bWeight && $bOFC)){
         $strOrs = array();
         if ($bHeight) $strOrs[] = ' (meas_sngHeightCM    IS NOT NULL) ';
         if ($bWeight) $strOrs[] = ' (meas_sngWeightKilos IS NOT NULL) ';
         if ($bOFC)    $strOrs[] = ' (meas_sngHeadCircCM  IS NOT NULL) ';

         $strWhere = ' AND ( '.implode(' OR ', $strOrs).' ) ';
      }
      return($strWhere);
   }


   function addNewMeasure(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gdteNow, $glUserID;

      $measure = &$this->measurements[0];

      $sqlStr = 'INSERT INTO emr_measurements '
               .'SET '.$this->sqlCommonAddUpdate().",
                     meas_lClientID     = $measure->lClientID,
                     meas_bRetired      = 0,
                     meas_lOriginID     = $glUserID,
                     meas_dteOrigin     = NOW() ;";
      $query = $this->db->query($sqlStr);
   }

   function sqlCommonAddUpdate(){
      global $glUserID;

      $measure = &$this->measurements[0];
      return("
         meas_sngHeadCircCM  = $measure->sngHeadCircCM,
         meas_sngWeightKilos = $measure->sngWeightKilos,
         meas_sngHeightCM    = $measure->sngHeightCM,
         meas_lLastUpdateID  = $glUserID,
         meas_strNotes       = ".strPrepStr($measure->strNotes).',
         meas_dteMeasurement = '.strPrepStr($measure->mySQLDateMeasure).',
         meas_dteLastUpdate  = NOW()
         ');

   }

/*
INSERT INTO `emr_measurements`
SET
meas_lKeyID = $measure->
meas_lClientID = $measure->
meas_dteMeasurement = $measure->
meas_sngHeadCircCM = $measure->
meas_sngWeightKilos = $measure->
meas_sngHeightCM = $measure->
meas_strNotes = $measure->
meas_bRetired = $measure->
meas_lOriginID = $measure->
meas_lLastUpdateID = $measure->
meas_dteOrigin = $measure->
meas_dteLastUpdate

*/




}
