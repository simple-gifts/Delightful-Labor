<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*---------------------------------------------------------------------
   Delightful Labor!

   copyright (c) 2011 by Database Austin
   Austin, Texas

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('admin/morganization', 'clsChapter');
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class morganization extends CI_Model{
   var $lChapterID, $chapterRec;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->lChapterID  = null;

      $this->clearChapterVars();
   }

   function clearChapterVars(){
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
       $this->lChapterID =
       $this->chapterRec = null;
   }

   function loadChapterInfo(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsACO = new madmin_aco();
      if (is_null($this->lChapterID)){
         screamForHelp('CHAPTER ID NOT SET<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      }

      $sqlStr =
         "SELECT
             ch_lKeyID,
             ch_strChapterName, ch_strAddress1, ch_strAddress2,
             ch_strCity, ch_strState, ch_strCountry, ch_strZip,
             ch_strFax, ch_strPhone, ch_strBannerTagLine,
             ch_strEmail, ch_strEmailCalDistr, ch_strWebSite,
             ch_strDefAreaCode, ch_strDefState, ch_strDefCountry,
             ch_lPW_MinLen, ch_bPW_UpperLower, ch_bPW_Number,
             ch_bUS_DateFormat, ch_lDefaultACO, ch_lTimeZone,
             ch_vocZip, ch_vocState, ch_vocJobSkills,
             aco_strFlag, aco_strCurrencySymbol, aco_strName,
             ch_bRetired,

             ch_lOrigID, ch_lLastUpdateID,
             UNIX_TIMESTAMP(ch_dteOrigin) AS dteOrigin,
             UNIX_TIMESTAMP(ch_dteLastUpdate) AS dteLastUpdate,

             tz_strTimeZone,

             usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
             usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
             UNIX_TIMESTAMP(ch_dteOrigin) AS dteOrigin,
             UNIX_TIMESTAMP(ch_dteLastUpdate) AS dteLastUpdate

          FROM admin_chapters
            INNER JOIN admin_users AS usersC ON ch_lOrigID       = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL ON ch_lLastUpdateID = usersL.us_lKeyID
            INNER JOIN admin_aco             ON ch_lDefaultACO   = aco_lKeyID
            INNER JOIN lists_tz              ON ch_lTimeZone     = tz_lKeyID

          WHERE ch_lKeyID=$this->lChapterID;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         echo("<br><br>$sqlStr<br><br>");
         screamForHelp('EOF error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__);
      }else {
         $row = $query->row();

         $this->chapterRec = new stdClass;

         $this->chapterRec->lKeyID               = (int)$row->ch_lKeyID;
         $this->chapterRec->strChapterName       = $row->ch_strChapterName;
         $this->chapterRec->strSafeChapterName   = htmlspecialchars($row->ch_strChapterName);
         $this->chapterRec->strBannerTagLine     = $row->ch_strBannerTagLine;

         $this->chapterRec->strAddress1          = $row->ch_strAddress1;
         $this->chapterRec->strAddress2          = $row->ch_strAddress2;
         $this->chapterRec->strCity              = $row->ch_strCity;
         $this->chapterRec->strState             = $row->ch_strState;
         $this->chapterRec->strCountry           = $row->ch_strCountry;
         $this->chapterRec->strZip               = $row->ch_strZip;
         $this->chapterRec->strAddress           =
                     strBuildAddress(
                        $this->chapterRec->strAddress1, $this->chapterRec->strAddress2, $this->chapterRec->strCity,
                        $this->chapterRec->strState,    $this->chapterRec->strCountry,  $this->chapterRec->strZip,
                        true);

         $this->chapterRec->strFax                = $row->ch_strFax;
         $this->chapterRec->strPhone              = $row->ch_strPhone;
         $this->chapterRec->strEmail              = $row->ch_strEmail;
         $this->chapterRec->strEmailCalDistLists  = $row->ch_strEmailCalDistr;
         $this->chapterRec->strWebSite            = $row->ch_strWebSite;
         $this->chapterRec->lTimeZoneID           = (int)$row->ch_lTimeZone;
         $this->chapterRec->strTimeZone           = $row->tz_strTimeZone;

         $this->chapterRec->strDefAreaCode        = $row->ch_strDefAreaCode;
         $this->chapterRec->strDefState           = $row->ch_strDefState;
         $this->chapterRec->strDefCountry         = $row->ch_strDefCountry;
         $this->chapterRec->bUS_DateFormat        = (bool)$row->ch_bUS_DateFormat;
         $this->chapterRec->lDefaultACO           = (int)$row->ch_lDefaultACO;
         $this->chapterRec->strFlag               = $row->aco_strFlag;
         $this->chapterRec->strFlagImg            = $clsACO->strFlagImage($row->aco_strFlag, $row->aco_strName);

         $this->chapterRec->strCurrencySymbol     = $row->aco_strCurrencySymbol;
         $this->chapterRec->strCountryName        = $row->aco_strName;

         $this->chapterRec->lPW_MinLen            = $row->ch_lPW_MinLen;
         $this->chapterRec->bPW_UpperLower        = (boolean)$row->ch_bPW_UpperLower;
         $this->chapterRec->bPW_Number            = (boolean)$row->ch_bPW_Number;

         $this->chapterRec->bRetired              = (boolean)$row->ch_bRetired;

         $this->chapterRec->strStaffCFName        = $row->strCFName;
         $this->chapterRec->strStaffCLName        = $row->strCLName;
         $this->chapterRec->strStaffLFName        = $row->strLFName;
         $this->chapterRec->strStaffLLName        = $row->strLLName;

         $this->chapterRec->vocabulary            = new stdClass;
         $this->chapterRec->vocabulary->vocZip    = $row->ch_vocZip;
         $this->chapterRec->vocabulary->vocState  = $row->ch_vocState;
         $this->chapterRec->vocabulary->vocJobSkills = $row->ch_vocJobSkills;

         $this->chapterRec->dteOrigin             = $row->dteOrigin;
         $this->chapterRec->dteLastUpdate         = $row->dteLastUpdate;
      }
   }

   function insertChapter(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbAdmin, $glUserID;
      $sqlStr =
          'INSERT INTO admin_chapters
           SET '.$this->strSQLCommon().",
               ch_bOkayInAggregate = 1,
               ch_lAgencyID        = -1,
               ch_strLogoImg       = '',
               ch_strImgBackground = '',
               ch_bRetired         = 0,
               ch_lOrigID          = $glUserID,
               ch_dteOrigin        = NOW();";
      $query = $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function updateChapter($lChapterID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbAdmin, $glUserID;

      $sqlStr =
         'UPDATE admin_chapters
          SET '.$this->strSQLCommon()."
          WHERE ch_lKeyID=$lChapterID;";
      $query = $this->db->query($sqlStr);
   }

   private function strSQLCommon(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $cRec = $this->chapterRec;
      return('
            ch_strChapterName   = '.strPrepStr($cRec->strChapterName)   .',
            ch_strBannerTagLine = '.strPrepStr($cRec->strBannerTagLine) .',
            ch_strAddress1      = '.strPrepStr($cRec->strAddress1)      .',
            ch_strAddress2      = '.strPrepStr($cRec->strAddress2)      .',
            ch_strCity          = '.strPrepStr($cRec->strCity)          .',
            ch_strState         = '.strPrepStr($cRec->strState)         .',
            ch_strCountry       = '.strPrepStr($cRec->strCountry)       .',
            ch_strZip           = '.strPrepStr($cRec->strZip)           .',
            ch_strFax           = '.strPrepStr($cRec->strFax)           .',
            ch_strPhone         = '.strPrepStr($cRec->strPhone)         .',
            ch_strEmail         = '.strPrepStr($cRec->strEmail)         .',
            ch_strEmailCalDistr = '.strPrepStr('')                      .',
            ch_strWebSite       = '.strPrepStr($cRec->strWebSite)       .',
            ch_bUS_DateFormat   = '.($cRec->bUS_DateFormat ? '1' : '0') .',

            ch_vocZip           = '.strPrepStr($cRec->vocabulary->vocZip)      .',
            ch_vocState         = '.strPrepStr($cRec->vocabulary->vocState)    .',
            ch_vocJobSkills     = '.strPrepStr($cRec->vocabulary->vocJobSkills).',

            ch_strDefAreaCode   = '.strPrepStr($cRec->strDefAreaCode)   .',
            ch_strDefState      = '.strPrepStr($cRec->strDefState)      .',
            ch_lDefaultACO      = '.(int)$cRec->lDefaultACO             .',
            ch_lTimeZone        = '.(int)$cRec->lTimeZoneID             .',
            ch_strDefCountry    = '.strPrepStr($cRec->strDefCountry)    .",

            ch_lLastUpdateID    = $glUserID ");
   }

   public function strChapterHTMLSummary(){
   //-----------------------------------------------------------------------
   // assumes user has called $this->loadChapterInfo();
   //-----------------------------------------------------------------------
      global $genumDateFormat;

      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $chapter    = &$this->chapterRec;
      $lChapterID = $chapter->lKeyID;
      $strOut =
          $clsRpt->openReport('', '')
         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Organization:')
         .$clsRpt->writeCell ($chapter->strSafeChapterName)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Organization ID:')
         .$clsRpt->writeCell (
                               str_pad($lChapterID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                              .strLinkView_OrganizationRecord($lChapterID, 'View organization record', true))
         .$clsRpt->closeRow  ()


         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Address:')
         .$clsRpt->writeCell ($chapter->strAddress)
         .$clsRpt->closeRow  ()

         .$clsRpt->closeReport('<br>');

      return($strOut);
   }

   public function strDefaultDateFormatRadio($strDDLName, $bMatchUS){
      $strOut = "\n"
         .'<input type="radio" name="'.$strDDLName.'" value="US" '.($bMatchUS ? 'checked' : '').'>US (m / d / Y)&nbsp;'."\n"
         .'<input type="radio" name="'.$strDDLName.'" value="EU" '.($bMatchUS ? '' : 'checked').'>Europe/India (d / m / Y)&nbsp;'."\n";
      return($strOut);
   }
}

?>