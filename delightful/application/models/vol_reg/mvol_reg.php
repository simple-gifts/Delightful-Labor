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
      $this->load->model('vol_reg/mvol_reg', 'volReg');
--------------------------------------------------------------------

---------------------------------------------------------------------*/


class mvol_reg extends CI_Model{
   public
       $regRecs, $lNumRegRecs,
       $strWhereExtra, $strOrder;

   public $lNumSkillTot, $lNumSkills, $skills, $bSkillsMissing,
          $lMissingSkills, $lOnFormSkills;

   public $lNumStyleSheets, $styleSheets;

   public $lNumTables, $utables;

   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->regRecs = null;
      $this->lNumRegRecs = 0;
      $this->strWhereExtra = $this->strOrder = '';

      $this->lNumSkillTot = $this->lNumSkills = 0;
      $this->lMissingSkills = $this->lOnFormSkills = 0;
      $this->skills = null;
      $this->bSkillsMissing = false;

      $this->lNumStyleSheets = 0;
      $this->styleSheets     = null;

      $this->lNumTables = 0;
      $this->utables    = null;
   }

   function loadVolRegFormsViaRFID($lRegFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = " AND vreg_lKeyID=$lRegFormID ";
      $this->loadVolRegForms();
   }

   function loadVolRegFormsViaHash($strHash){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = ' AND vreg_strURLHash='.strPrepStr($strHash).' ';
      $this->loadVolRegForms();
   }

   function loadVolRegForms(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->regRecs = array();

      if ($this->strOrder == ''){
         $strOrder = ' vreg_strFormName, vreg_lKeyID ';
      }else {
         $strOrder = $this->strOrder;
      }
      $sqlStr = "
         SELECT
            vreg_lKeyID, vreg_strFormName, vreg_strDescription,
            vreg_strIntro, vreg_strSubmissionText, vreg_strBannerOrg, vreg_strBannerTitle,
            vreg_strCSSFN, vreg_strHexBGColor, vreg_strContact,
            vreg_strContactPhone, vreg_strContactEmail, vreg_strWebSite,
            vreg_strURLHash,

                 -- standard display fields
            vreg_bShowFName, vreg_bShowLName, vreg_bShowAddr, vreg_bShowEmail,
            vreg_bShowPhone,  vreg_bShowCell, vreg_bShowBDay,
            vreg_bShowDisclaimer, vreg_strDisclaimerAck, vreg_strDisclaimer,
            vreg_lLogoImageID, vreg_lVolGroupID,

            vreg_bFNameRequired, vreg_bLNameRequired, vreg_bAddrRequired, vreg_bEmailRequired,
            vreg_bPhoneRequired, vreg_bCellRequired,
            vreg_bBDateRequired, vreg_bDisclaimerAckRqrd,

            vreg_bPermEditContact,   vreg_bPermPassReset,      vreg_bPermViewGiftHistory,
            vreg_bPermEditJobSkills, vreg_bPermViewHrsHistory, vreg_bPermAddVolHours,
            vreg_bVolShiftSignup,

            vreg_bCaptchaRequired,

            vreg_bRetired, vreg_lOriginID, vreg_lLastUpdateID,

            UNIX_TIMESTAMP(vreg_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(vreg_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName
         FROM vol_reg
            INNER JOIN admin_users   AS uc ON uc.us_lKeyID=vreg_lOriginID
            INNER JOIN admin_users   AS ul ON ul.us_lKeyID=vreg_lLastUpdateID

         WHERE NOT vreg_bRetired $this->strWhereExtra
         ORDER BY $strOrder;";
      $query = $this->db->query($sqlStr);
      $this->lNumRegRecs = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->regRecs[0] = new stdClass;
         $rRec = &$this->regRecs[0];
         $rRec->lKeyID               =
         $rRec->strFormName          =
         $rRec->strDescription       =
         $rRec->strIntro             =
         $rRec->strSubmissionText    =
         $rRec->strBannerOrg         =
         $rRec->strBannerTitle       =
         $rRec->strCSSFN             =
         $rRec->strHexBGColor        =
         $rRec->strContact           =
         $rRec->strContactPhone      =
         $rRec->strContactEmail      =
         $rRec->strWebSite           =
         $rRec->strURLHash           =
         $rRec->lLogoImageID         =
         $rRec->lVolGroupID          =

         $rRec->bShowFName           =
         $rRec->bShowLName           =
         $rRec->bShowAddr            =
         $rRec->bShowEmail           =
         $rRec->bShowPhone           =
         $rRec->bShowCell            =

         $rRec->bShowBDay            = null;

         $rRec->bShowDisclaimer      =
         $rRec->strDisclaimerAck     =
         $rRec->strDisclaimer        =

         $rRec->bFNameRequired       =
         $rRec->bLNameRequired       =
         $rRec->bAddrRequired        =
         $rRec->bEmailRequired       =
         $rRec->bBDateRequired       =
         $rRec->bDisclaimerAckRqrd   =

         $rRec->bPermEditContact     =
         $rRec->bPermPassReset       =
         $rRec->bPermViewGiftHistory =
         $rRec->bPermEditJobSkills   =
         $rRec->bPermViewHrsHistory  =
         $rRec->bPermAddVolHours     =
         $rRec->bVolShiftSignup      =

         $rRec->bCaptchaRequired     =

         $rRec->bRetired             =

         $rRec->lOriginID            =
         $rRec->lLastUpdateID        =
         $rRec->dteOrigin            =
         $rRec->dteLastUpdate        =
         $rRec->strUCFName           =
         $rRec->strUCLName           =
         $rRec->strULFName           =
         $rRec->strULLName           = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->regRecs[$idx] = new stdClass;
            $rRec = &$this->regRecs[$idx];

            $rRec->lKeyID               = $row->vreg_lKeyID;
            $rRec->strFormName          = $row->vreg_strFormName;
            $rRec->strDescription       = $row->vreg_strDescription;
            $rRec->strIntro             = $row->vreg_strIntro;
            $rRec->strSubmissionText    = $row->vreg_strSubmissionText;
            $rRec->strBannerOrg         = $row->vreg_strBannerOrg;
            $rRec->strBannerTitle       = $row->vreg_strBannerTitle;
            $rRec->strCSSFN             = $row->vreg_strCSSFN;
            $rRec->strHexBGColor        = $row->vreg_strHexBGColor;
            $rRec->strContact           = $row->vreg_strContact;
            $rRec->strContactPhone      = $row->vreg_strContactPhone;
            $rRec->strContactEmail      = $row->vreg_strContactEmail;
            $rRec->strWebSite           = $row->vreg_strWebSite;
            $rRec->strURLHash           = $row->vreg_strURLHash;
            $rRec->lLogoImageID         = $row->vreg_lLogoImageID;
            $rRec->lVolGroupID          = $row->vreg_lVolGroupID;
            $rRec->strVolRegFormURL     = site_url('vol_reg/vol/register/'.$rRec->strURLHash);

            $rRec->lLogoImageID         = $row->vreg_lLogoImageID;

            $rRec->bShowFName           = (boolean)$row->vreg_bShowFName;
            $rRec->bShowLName           = (boolean)$row->vreg_bShowLName;
            $rRec->bShowAddr            = (boolean)$row->vreg_bShowAddr;
            $rRec->bShowEmail           = (boolean)$row->vreg_bShowEmail;
            $rRec->bShowPhone           = (boolean)$row->vreg_bShowPhone;
            $rRec->bShowCell            = (boolean)$row->vreg_bShowCell;

            $rRec->bShowBDay            = (boolean)$row->vreg_bShowBDay;

               // disclaimer
            $rRec->bShowDisclaimer      = (boolean)$row->vreg_bShowDisclaimer;
            $rRec->strDisclaimerAck     = $row->vreg_strDisclaimerAck;
            $rRec->strDisclaimer        = $row->vreg_strDisclaimer;
            $rRec->bDisclaimerAckRqrd   = (boolean)$row->vreg_bDisclaimerAckRqrd;

            $rRec->bFNameRequired       = (boolean)$row->vreg_bFNameRequired;
            $rRec->bLNameRequired       = (boolean)$row->vreg_bLNameRequired;
            $rRec->bAddrRequired        = (boolean)$row->vreg_bAddrRequired;
            $rRec->bEmailRequired       = (boolean)$row->vreg_bEmailRequired;
            $rRec->bPhoneRequired       = (boolean)$row->vreg_bPhoneRequired;
            $rRec->bCellRequired        = (boolean)$row->vreg_bCellRequired;

            $rRec->bBDateRequired       = (boolean)$row->vreg_bBDateRequired;

            $rRec->bPermEditContact     = (boolean)$row->vreg_bPermEditContact;
            $rRec->bPermPassReset       = (boolean)$row->vreg_bPermPassReset;
            $rRec->bPermViewGiftHistory = (boolean)$row->vreg_bPermViewGiftHistory;
            $rRec->bPermEditJobSkills   = (boolean)$row->vreg_bPermEditJobSkills;
            $rRec->bPermViewHrsHistory  = (boolean)$row->vreg_bPermViewHrsHistory;
            $rRec->bPermAddVolHours     = (boolean)$row->vreg_bPermAddVolHours;
            $rRec->bVolShiftSignup      = (boolean)$row->vreg_bVolShiftSignup;

            $rRec->bCaptchaRequired     = (boolean)$row->vreg_bCaptchaRequired;

            $rRec->bRetired             = (boolean)$row->vreg_bRetired;

            $rRec->lOriginID            = $row->vreg_lOriginID;
            $rRec->lLastUpdateID        = $row->vreg_lLastUpdateID;
            $rRec->dteOrigin            = $row->dteOrigin;
            $rRec->dteLastUpdate        = $row->dteLastUpdate;
            $rRec->strUCFName           = $row->strUCFName;
            $rRec->strUCLName           = $row->strUCLName;
            $rRec->strULFName           = $row->strULFName;
            $rRec->strULLName           = $row->strULLName;

            ++$idx;
         }
      }
   }

   function setLogoImageTag($lRegFormIDX=0){
   /*-----------------------------------------------------------------------
      assumes user has called $this->loadVolRegForms()

      caller must include
            $this->load->model  ('img_docs/mimage_doc',   'clsImgDoc');
            $this->load->helper ('img_docs/image_doc');
   -----------------------------------------------------------------------*/
      $rRec = &$this->regRecs[$lRegFormIDX];
      $lImageID = $rRec->lLogoImageID;

      $rRec->strLogoImageTag = null;

      if (!is_null($rRec->lLogoImageID)){
         $cImg = new mimage_doc;
         $cImg->bLoadContext = false;
         $cImg->loadDocImageInfoViaID($lImageID);

         $bImgAvail = $cImg->lNumImageDocs > 0;

         if ($bImgAvail){
            $imgDoc = &$cImg->imageDocs[0];

            $rRec->strLogoImageTag = '<div style="text-align: center;"><img src="'.base_url().substr($imgDoc->strPath, 2)
                           .'/'.$imgDoc->strSystemFN.'" '.$imgDoc->imageSize[3].'></div>';
         }
      }
   }

   function loadVolRegFormSkills($lFormID, $bLoadAllSkills){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bLoadAllSkills){
         $this->loadAllJobSkills($lFormID);
      }else {
         $this->loadJobSkillsOnFormOnly($lFormID);
      }
   }

   private function loadAllJobSkills($lFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->skills = array();
      $this->lNumSkills = 0;

      $sqlStr = 'DROP TABLE IF EXISTS tmp_reg_skills;';
      $query = $this->db->query($sqlStr);

      $sqlStr = '
            CREATE TEMPORARY TABLE IF NOT EXISTS tmp_reg_skills (
              tjs_lKeyID      int(11) NOT NULL AUTO_INCREMENT,
              tjs_lRegSkillID int(11) NOT NULL COMMENT \'Foreign key to vol_reg_skills\',
              tjs_lSkillID    int(11) NOT NULL COMMENT \'Foreign key to generic list table\',
              PRIMARY KEY         (tjs_lKeyID),
              KEY tjs_lRegSkillID (tjs_lRegSkillID),
              KEY vrs_lSkillID    (tjs_lSkillID)
            ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;';
      $query = $this->db->query($sqlStr);

      $sqlStr = "
          INSERT INTO tmp_reg_skills (tjs_lRegSkillID, tjs_lSkillID)
            SELECT vrs_lKeyID, vrs_lSkillID
            FROM vol_reg_skills
            WHERE vrs_lRegFormID=$lFormID;";
      $query = $this->db->query($sqlStr);

      $sqlStr = "
         SELECT
            lgen_lKeyID, lgen_strListItem, tjs_lRegSkillID
         FROM lists_generic
            LEFT JOIN tmp_reg_skills ON lgen_lKeyID=tjs_lSkillID
         WHERE NOT lgen_bRetired
            AND lgen_enumListType='".CENUM_LISTTYPE_VOLSKILLS."'
         ORDER BY lgen_strListItem, tjs_lRegSkillID;";

      $query = $this->db->query($sqlStr);
      $this->lNumSkillTot = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->lNumSkills = 0;
         $this->skills[0] = new stdClass;
         $skill = &$this->skills[0];

         $skill->lRegFormID =
         $skill->bOnForm    =
         $skill->lSkillID   =
         $skill->strSkill   = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->skills[$idx] = new stdClass;
            $skill = &$this->skills[$idx];

            $skill->lSkillID   = $row->lgen_lKeyID;
            $skill->strSkill   = $row->lgen_strListItem;

            $skill->lRegFormID = $row->tjs_lRegSkillID;
            $skill->bOnForm    = !is_null($row->tjs_lRegSkillID);
            if ($skill->bOnForm) ++$this->lNumSkills;
            ++$idx;
         }
      }
   }

   private function loadJobSkillsOnFormOnly($lFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->bSkillsMissing = false;
      $this->lMissingSkills = $this->lOnFormSkills = 0;
      $this->skills = array();
      $sqlStr = "
            SELECT
               vrs_lKeyID, vrs_lRegFormID, vrs_lSkillID,
               lgen_lKeyID, lgen_strListItem, lgen_bRetired
            FROM vol_reg_skills
               LEFT JOIN lists_generic ON vrs_lSkillID=lgen_lKeyID
            WHERE vrs_lRegFormID=$lFormID
            ORDER BY lgen_strListItem, vrs_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumSkillTot = $this->lNumSkills = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->skills[0] = new stdClass;
         $skill = &$this->skills[0];

         $skill->lKeyID     =
         $skill->lRegFormID =
         $skill->lSkillID   =
         $skill->bMissing   =
         $skill->strSkill   = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->skills[$idx] = new stdClass;
            $skill = &$this->skills[$idx];

            $skill->lKeyID     = $row->vrs_lKeyID;
            $skill->lRegFormID = $row->vrs_lRegFormID;
            $skill->lSkillID   = $row->vrs_lSkillID;

               // has the skill list item been removed?
            if (is_null($row->lgen_lKeyID) || $row->lgen_bRetired){
               $skill->bMissing      = true;
               $this->bSkillsMissing = true;
               $skill->strSkill      = '#error#';
               ++$this->lMissingSkills;
            }else {
               $skill->bMissing = false;
               $skill->strSkill = $row->lgen_strListItem;
               ++$this->lOnFormSkills;
            }
            ++$idx;
         }
      }
   }

   function lNumVolRecsViaRegFormID($lRegFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM volunteers
            INNER JOIN people_names ON pe_lKeyID=vol_lPeopleID
         WHERE NOT vol_bRetired
            AND NOT pe_bRetired
            AND vol_lRegFormID = $lRegFormID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }



   function updateJobSkillFields($lRegFormID, $lNumSkills, &$jobSkills){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // out with the old
      $this->deleteJobSkillsFields($lRegFormID);

         // in with the new
      if ($lNumSkills > 0){
         foreach ($jobSkills as $skill){
            if ($skill->bShow){
               $this->insertJobSkill($lRegFormID, $skill->lSkillID);
            }
         }
      }
   }

   function deleteJobSkillsFields($lRegFormID){
      $sqlStr =
          "DELETE FROM vol_reg_skills
           WHERE vrs_lRegFormID=$lRegFormID;";
      $query = $this->db->query($sqlStr);
   }

   private function insertJobSkill($lRegFormID, $lSkillID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "INSERT INTO vol_reg_skills
         SET
            vrs_lRegFormID=$lRegFormID,
            vrs_lSkillID=$lSkillID;";
      $query = $this->db->query($sqlStr);
   }

/*
   function removeVolRegForm($lVolRegFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
        "UPDATE vol_reg
         SET
            vreg_bRetired = 1,
            vreg_lLastUpdateID = $glUserID,
            vreg_dteLastUpdate = NOW()
         WHERE vreg_lKeyID=$lVolRegFormID;";
      $query = $this->db->query($sqlStr);

      $this->deleteJobSkillsFields($lVolRegFormID);
      $this->deletePTableFields($lVolRegFormID);
   }
*/
   function availableStyleSheets(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->lNumStyleSheets = 0;
      $this->styleSheets     = array();

      $strDirPath = './css/vol_reg';
      $dirFiles = get_filenames($strDirPath);
      if ($dirFiles !== false){
         sort($dirFiles, SORT_STRING);
         foreach ($dirFiles as $strFN){
            if (strtoupper(substr($strFN, -4))=='.CSS'){
               $this->styleSheets[$this->lNumStyleSheets] = $strFN;
               ++$this->lNumStyleSheets;
            }
         }
      }
   }

   function strCSSDropDown($strDDLName, $strMatch, $bAddBlank){
   //---------------------------------------------------------------------
   // caller must previously call $this->availableStyleSheets()
   //---------------------------------------------------------------------
      $strOut = '<select name="'.$strDDLName.'">'."\n";
      if ($bAddBlank){
         $strOut .= '<option value="">&nbsp;</option>'."\n";
      }

      if ($this->lNumStyleSheets > 0){
         $bMatch = false;
         foreach ($this->styleSheets as $strFN){
            if ($strMatch==$strFN){
               $bMatch = true;
               break;
            }
         }
         if (!$bMatch){
            $strMatch = 'default.css';
         }

         foreach ($this->styleSheets as $strFN){
            $strOut .= '<option value="'.$strFN.'" '.($strMatch==$strFN ? 'selected' : '').'>'
                        .htmlspecialchars($strFN).'</option>'."\n";
         }
      }
      $strOut .= '</select>'."\n";
      return($strOut);
   }

   function bShowRequiredUFFields($lRegFormID, $lTableID, $lFieldID, &$bShow, &$bRequired){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bShow = $bRequired = false;

      $sqlStr =
        "SELECT vruf_bRequired
         FROM vol_reg_uf
         WHERE vruf_lRegFormID=$lRegFormID
            AND vruf_lTableID=$lTableID
            AND vruf_lFieldID=$lFieldID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() != 0){
         $row = $query->row();
         $bShow = true;
         $bRequired = $row->vruf_bRequired;
      }
   }

   function strPublicUFTable($lTableID, $lFormID, $strDefault){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT vrtl_strLabel
         FROM vol_reg_table_labels
         WHERE vrtl_lRegFormID = $lFormID
            AND vrtl_lTableID=$lTableID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return($strDefault);
      }else {
         $row = $query->row();
         return($row->vrtl_strLabel);
      }
   }

   function loadPTablesForDisplay($lRegFormID, &$clsUF, $bLoadFields=true){
   //---------------------------------------------------------------------
   //  data set:
   //    $this->lNumTables
   //    $this->utables
   //---------------------------------------------------------------------
      $this->utables = array();

      $sqlStr =
           "SELECT DISTINCT
               vruf_lRegFormID, vruf_lTableID,
               pft_strUserTableName, pft_strDataTableName, pft_bMultiEntry
            FROM vol_reg_uf
               INNER JOIN uf_tables ON vruf_lTableID=pft_lKeyID

            WHERE vruf_lRegFormID=$lRegFormID
               AND NOT pft_bRetired AND NOT pft_bHidden
            ORDER BY pft_strUserTableName, vruf_lTableID;";

      $query = $this->db->query($sqlStr);
      $this->lNumTables = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->utables[0] = new stdClass;
         $utable = &$this->utables[0];

         $utable->lRegFormID        =
         $utable->lTableID          =
         $utable->strUserTableName  =
         $utable->strDataTableName  =
         $utable->bMultiEntry       =
         $utable->ufields           = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row) {
            $this->utables[$idx] = new stdClass;
            $utable = &$this->utables[$idx];

            $utable->lRegFormID        = $row->vruf_lRegFormID;
            $utable->lTableID          = $lTableID = $row->vruf_lTableID;
            $utable->strUserTableName  = $row->pft_strUserTableName;
            $utable->strDataTableName  = $row->pft_strDataTableName;
            $utable->bMultiEntry       = $row->pft_bMultiEntry;
            $utable->strFieldPrefix    = $clsUF->strGenUF_KeyFieldPrefix($lTableID);

            if ($bLoadFields){
               $utable->ufields = array();
               $this->loadTableFieldsForDisplay($utable->ufields, $utable->lNumFields, $utable->lRegFormID, $utable->lTableID);
            }
            ++$idx;
         }
      }
   }

   private function loadTableFieldsForDisplay(&$ufields, &$lNumFields, $lRegFormID, $lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumFields = 0;

      $sqlStr =
           "SELECT
               vruf_lKeyID,
               vruf_lFieldID, vruf_bRequired, vruf_strLabel,
               pff_lKeyID, pff_lTableID, pff_lSortIDX, pff_strFieldNameInternal,
               pff_strFieldNameUser, pff_strFieldNotes, pff_enumFieldType, pff_bConfigured,
               pff_lCurrencyACO

            FROM vol_reg_uf
               INNER JOIN uf_fields ON vruf_lFieldID=pff_lKeyID

            WHERE vruf_lRegFormID=$lRegFormID
               AND vruf_lTableID=$lTableID
            ORDER BY pff_lSortIDX, pff_strFieldNameUser, vruf_lFieldID;";


      $query = $this->db->query($sqlStr);
      $lNumFields = $query->num_rows();

      if ($lNumFields > 0) {
         $idx = 0;
         foreach ($query->result() as $row) {
            $ufields[$idx] = new stdClass;
            $ufield = &$ufields[$idx];

            $ufield->lKeyID               = $row->vruf_lKeyID;
            $ufield->lFieldID             = $row->vruf_lFieldID;
            $ufield->bRequired            = $row->vruf_bRequired;
            $ufield->strLabel             = $row->vruf_strLabel;
            $ufield->lTableID             = $row->pff_lTableID;
            $ufield->lSortIDX             = $row->pff_lSortIDX;
            $ufield->strFieldNameInternal = $row->pff_strFieldNameInternal;
            $ufield->strFieldNameUser     = $row->pff_strFieldNameUser;
            $ufield->strFieldNotes        = $row->pff_strFieldNotes;
            $ufield->enumFieldType        = $row->pff_enumFieldType;
            $ufield->bConfigured          = $row->pff_bConfigured;
            $ufield->lCurrencyACO         = $row->pff_lCurrencyACO;

            ++$idx;
         }
      }
   }



       /*------------------------------------------------------
           R E G I S T R A T I O N   F O R M   E X P O R T
       ------------------------------------------------------ */
   function loadRegistrationGeneric($sqlWhere, $lRegFormID, &$lNumRegs, &$regTable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glclsDTDateFormat;

      $lNumRegs = 0;
      $regTable = array();

         // load vol registration form
      $this->loadVolRegFormsViaRFID($lRegFormID);
      $rRec = &$this->regRecs[0];

         // load user field meta-data
      $cUF = new muser_fields;
      $this->loadPTablesForDisplay($lRegFormID, $cUF, true);

         // load job skill meta-data
      $this->loadAllJobSkills($lRegFormID);

         // build a bit-map template array of job codes
      $bmJobCodes = array();
      if ($this->lNumSkills > 0){
         foreach ($this->skills as $skill){
            if ($skill->bOnForm){
               $bmJobCodes[(int)$skill->lSkillID] = false;
            }
         }
      }

         // load the people/volunteer info
      $sqlStr =
        "SELECT
            vol_lKeyID, vol_lPeopleID, vol_Notes,

            pe_strTitle, pe_strFName, pe_strMName, pe_strLName,
            pe_dteBirthDate, pe_strAddr1, pe_strAddr2, pe_strCity,
            pe_strState, pe_strCountry, pe_strZip, pe_strPhone, pe_strCell, pe_strFax,
            pe_strEmail,

            aco_lKeyID, aco_strName
         FROM volunteers
            INNER JOIN people_names ON vol_lPeopleID = pe_lKeyID
            INNER JOIN admin_aco    ON aco_lKeyID    = pe_lACO
         WHERE 1
            $sqlWhere
            AND NOT vol_bRetired
            AND NOT pe_bRetired
            AND NOT vol_bInactive
         ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumRegs = $this->lNumRegRecs = $query->num_rows();

      if ($lNumRegs > 0) {
         $volIDs = array();
         $idx = 0;
         foreach ($query->result() as $row) {
            $lVolID = $volIDs[] = (int)$row->vol_lKeyID;
            $regTable[$lVolID] = new stdClass;
            $rRec = &$regTable[$lVolID];

            $rRec->lVolID                  = (int)$row->vol_lKeyID;
            $rRec->lPeopleID               = (int)$row->vol_lPeopleID;
            $rRec->strVolNotes             = $row->vol_Notes;
            $rRec->strTitle                = $row->pe_strTitle;
            $rRec->strFName                = $row->pe_strFName;
            $rRec->strMName                = $row->pe_strMName;
            $rRec->strLName                = $row->pe_strLName;
            $rRec->dteBirthDate            = $mySQLdteBirth = $row->pe_dteBirthDate;
            $rRec->strAddr1                = $row->pe_strAddr1;
            $rRec->strAddr2                = $row->pe_strAddr2;
            $rRec->strCity                 = $row->pe_strCity;
            $rRec->strState                = $row->pe_strState;
            $rRec->strCountry              = $row->pe_strCountry;
            $rRec->strZip                  = $row->pe_strZip;
            $rRec->strPhone                = $row->pe_strPhone;
            $rRec->strCell                 = $row->pe_strCell;
            $rRec->strFax                  = $row->pe_strFax;
            $rRec->strEmail                = $row->pe_strEmail;
            $rRec->lACOID                  = $row->aco_lKeyID;
            $rRec->strACOName              = $row->aco_strName;

            $rRec->jobSkills               = array();
            $rRec->bmJobCodes              = arrayCopy($bmJobCodes);

            $rRec->pTableData              = array();

               //------------------------------
               // vol age/birth day info
               //------------------------------
            if (is_null($mySQLdteBirth)){
               $rRec->objVolBirth = null;
               $rRec->lAgeYears      = null;
               $rRec->strVolAgeBDay = '(age n/a)';
            }else {
               $rRec->objVolBirth = new dl_date_time;
               $rRec->objVolBirth->setDateViaMySQL(0, $mySQLdteBirth);
               $rRec->strVolAgeBDay =
                          $rRec->objVolBirth->strPeopleAge(0, $mySQLdteBirth,
                               $rRec->lAgeYears, $glclsDTDateFormat);
            }
            ++$idx;
         }

         $strVolIDs = implode(',', $volIDs);
      }

         // load the job skills for the registrants
      $sqlStr =
        "SELECT
            vs_lVolID, vs_lSkillID, lgen_strListItem
         FROM vol_skills
            INNER JOIN lists_generic ON vs_lSkillID = lgen_lKeyID
         WHERE
            vs_lVolID IN ($strVolIDs)
            AND NOT lgen_bRetired
         ORDER BY lgen_strListItem;";

      $query = $this->db->query($sqlStr);
      $lNumJobSkills = $query->num_rows();

      if ($lNumJobSkills > 0) {
         foreach ($query->result() as $row) {
            $lVolID   = (int)$row->vs_lVolID;
            $lSkillID = (int)$row->vs_lSkillID;
            $regRec = &$regTable[$lVolID];
            $regRec->jobSkills[] = $row->lgen_strListItem;
            $regRec->bmJobCodes[$lSkillID] = true;
         }
      }

         // load the personalized table records
         // for multi-records, export just the first entry
      if ($this->lNumTables > 0){
         foreach ($this->utables as $ut){
            $this->loadRegUTableData($ut, $strVolIDs, $regTable);
         }
      }
   }

   function loadRegistrationViaRegFormIDVolID($lVolID, $lRegFormID, &$lNumRegs, &$regTable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlWhere = " AND vol_lKeyID=$lVolID ";
      $this->loadRegistrationGeneric($sqlWhere, $lRegFormID, $lNumRegs, $regTable);
   }

   function strExportVolReg($lRegFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glclsDTDateFormat;

      $sqlWhere = " AND vol_lRegFormID=$lRegFormID ";
      $this->loadRegistrationGeneric($sqlWhere, $lRegFormID, $lNumRegs, $regExport);

         // set the export headings
      $strOut = $this->setVolRegExportHeadings($this->skills, $this->lNumTables, $this->utables);

         // set the data rows
      if ($lNumRegs > 0){
         foreach ($regExport as $rExp){
            $strOut .= $this->strVolRegExportDataRow($rExp, $this->skills, $this->lNumTables, $this->utables);
         }
      }
      return($strOut);
   }

   function loadRegUTableData(&$ut, $strVolIDs, &$regExport){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $ufd = new muser_fields_display;

      $ufd->lTableID = $lTableID = $ut->lTableID;

      $ufd->loadTableViaTableID();
      $ufd->loadFieldsGeneric(true, $lTableID, null);
      $ufd->userTables[0]->lNumFields = $ufd->lNumFields;
      $ufd->userTables[0]->ufields = &$ufd->fields;

         // limit the lookup to the qualified volunteer list
      $strFNPrefix = $ut->strFieldPrefix;
      $strFNFID    = $strFNPrefix.'_lForeignKey';
      $ufd->mrSQLWhereExtra = " AND $strFNFID IN ($strVolIDs) ";

         // order by vol, then earliest record; for multiple entries
         // we take only the first (we assume this was the entry from the registration form)
      $ufd->mrSQLOrder = ' '.$strFNFID.', '.$strFNPrefix.'_dteOrigin ';

         //    $ufd->lNumMRRecs  - number of loaded records
         //    $ufd->mrRecs      - array of data records
      $ufd->loadMRRecs(false, false);
      if ($ufd->lNumMRRecs > 0){
         $lVIDGroup = -999;
         foreach ($ufd->mrRecs as $mrRec){
            $lVolID = $mrRec->$strFNFID;
            if ($lVIDGroup != $lVolID){
               $lVIDGroup = $lVolID;
               $rRec = &$regExport[$lVolID];
               $rRec->pTableData[$lTableID] = clone($mrRec);
            }
         }
      }
   }

   function setVolRegExportHeadings($skills, $lNumTables, $utables){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterVoc;

         // general
      $strOut =
          'VolID'                   .CS_CSV_DELIMITER
         .'PeopleID'                .CS_CSV_DELIMITER
         .'Last Name'               .CS_CSV_DELIMITER
         .'First Name'              .CS_CSV_DELIMITER
         .'Middle Name'             .CS_CSV_DELIMITER
         .'Date of Birth'           .CS_CSV_DELIMITER
         .'Age'                     .CS_CSV_DELIMITER
         .'Address 1'               .CS_CSV_DELIMITER
         .'Address 2'               .CS_CSV_DELIMITER
         .'City'                    .CS_CSV_DELIMITER
         .$gclsChapterVoc->vocState .CS_CSV_DELIMITER
         .'Country'                 .CS_CSV_DELIMITER
         .$gclsChapterVoc->vocZip   .CS_CSV_DELIMITER
         .'Phone'                   .CS_CSV_DELIMITER
         .'Cell'                    .CS_CSV_DELIMITER
         .'Fax'                     .CS_CSV_DELIMITER
         .'Email'                   .CS_CSV_DELIMITER
         .'Job Skills';

         // job skills
      if (count($skills) > 0){
         foreach ($skills as $skill){
            if ($skill->bOnForm){
               $strOut .= CS_CSV_DELIMITER.'"[Job Skill] '.str_replace('"', '""', $skill->strSkill).'"';
            }
         }
      }

         // personalized fields
      if ($lNumTables > 0){
         foreach ($utables as $ut){
            if ($ut->lNumFields > 0){
               $strTabName = str_replace('"', '""', $ut->strUserTableName);
               foreach ($ut->ufields as $uf){
                  if ($this->bExportableField($uf->enumFieldType)){
                     $strOut .= CS_CSV_DELIMITER.'"['.$strTabName.'] '.str_replace('"', '""', $uf->strFieldNameUser).'"';
                  }
               }
            }
         }
      }
      return($strOut.CS_CSV_NEWLINE);
   }

   function strVolRegExportDataRow($rExp, $skills, $lNumTables, $utables){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // general
      $strOut =
          '"'.$rExp->lVolID      .'"'.CS_CSV_DELIMITER
         .'"'.$rExp->lPeopleID   .'"'.CS_CSV_DELIMITER
         .'"'.str_replace('"', '""', $rExp->strLName    ).'"'.CS_CSV_DELIMITER   // 'Last Name'
         .'"'.str_replace('"', '""', $rExp->strFName    ).'"'.CS_CSV_DELIMITER   // 'First Name'
         .'"'.str_replace('"', '""', $rExp->strMName    ).'"'.CS_CSV_DELIMITER   // 'Middle Name'
         .'"'.str_replace('"', '""', $rExp->dteBirthDate).'"'.CS_CSV_DELIMITER   // 'Date of Birth'
         .'"'.str_replace('"', '""', $rExp->lAgeYears   ).'"'.CS_CSV_DELIMITER   // 'Age'
         .'"'.str_replace('"', '""', $rExp->strAddr1    ).'"'.CS_CSV_DELIMITER   // 'Address 1'
         .'"'.str_replace('"', '""', $rExp->strAddr2    ).'"'.CS_CSV_DELIMITER   // 'Address 2'
         .'"'.str_replace('"', '""', $rExp->strCity     ).'"'.CS_CSV_DELIMITER   // 'City'
         .'"'.str_replace('"', '""', $rExp->strState    ).'"'.CS_CSV_DELIMITER   // 'State'
         .'"'.str_replace('"', '""', $rExp->strCountry  ).'"'.CS_CSV_DELIMITER   // 'Country'
         .'"'.str_replace('"', '""', $rExp->strZip      ).'"'.CS_CSV_DELIMITER   // 'Zip'
         .'"'.str_replace('"', '""', $rExp->strPhone    ).'"'.CS_CSV_DELIMITER   // 'Phone'
         .'"'.str_replace('"', '""', $rExp->strCell     ).'"'.CS_CSV_DELIMITER   // 'Cell'
         .'"'.str_replace('"', '""', $rExp->strFax      ).'"'.CS_CSV_DELIMITER   // 'Fax'
         .'"'.str_replace('"', '""', $rExp->strEmail    ).'"'.CS_CSV_DELIMITER   // 'Email'
         .'"'.str_replace('"', '""', implode("\n", $rExp->jobSkills)).'"';       // 'Job Skills';

         // job skills
      if (count($skills) > 0){
         foreach ($rExp->bmJobCodes as $bmJobCode){
            $strOut .= CS_CSV_DELIMITER.($bmJobCode ? 'Yes' : '');
         }
      }

         // personalized fields
      if ($lNumTables > 0){
         foreach ($utables as $ut){
            $lTableID = $ut->lTableID;

            if ($ut->lNumFields > 0){
               foreach ($ut->ufields as $uf){
                  $enumFieldType = $uf->enumFieldType;
                  if ($this->bExportableField($enumFieldType)){
                     $strIntFN = $uf->strFieldNameInternal;
                     if (isset($rExp->pTableData[$lTableID])){
                        $vVal = $this->valueViaType($enumFieldType, $strIntFN, $rExp->pTableData[$lTableID]);
                        $strOut .= CS_CSV_DELIMITER.fdh\strExportValueViaType($vVal, $enumFieldType);
                     }else {
                        $strOut .= CS_CSV_DELIMITER;
                     }
                  }
               }
            }
         }
      }
      return($strOut.CS_CSV_NEWLINE);
   }

   function strVolReg2HTML($regRec){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterVoc;
      
      $strTable = '<table style="font-family: arial; font-size: 10pt; width: 480pt;">'."\n";
      $lLWidth = 150;
      $strLabelStyle = 'style="width: '.$lLWidth.'pt; background-color: #ddd; text-align:right;"';
      $strDataStyle  = 'style="background-color: #e0e8f6;" ';

         // general
      $strOut =
         $strTable.'<tr><td colspan="2" style="background-color: #ccc; text-align:center;"><b>General Information</b></td></tr>'."\n"
         .'<tr><td '.$strLabelStyle.'>First Name:</td> <td '.$strDataStyle.'>'.htmlspecialchars($regRec->strFName    )          .'</td></tr>'."\n"   // 'First Name'
         .'<tr><td '.$strLabelStyle.'>Middle Name:</td><td '.$strDataStyle.'>'.htmlspecialchars($regRec->strMName    )          .'</td></tr>'."\n"   // 'Middle Name'
         .'<tr><td '.$strLabelStyle.'>Last Name:</td>  <td '.$strDataStyle.'>'.htmlspecialchars($regRec->strLName    )          .'</td></tr>'."\n"   // 'Last Name'
         .'<tr><td '.$strLabelStyle.'>Birthday:</td>   <td '.$strDataStyle.'>'.htmlspecialchars($regRec->dteBirthDate)          .'</td></tr>'."\n"   // 'Date of Birth'
         .'<tr><td '.$strLabelStyle.'>Age:</td>        <td '.$strDataStyle.'>'.htmlspecialchars($regRec->lAgeYears   )          .'</td></tr>'."\n"   // 'Age'
         .'<tr><td '.$strLabelStyle.'>Address 1:</td>  <td '.$strDataStyle.'>'.htmlspecialchars($regRec->strAddr1    )          .'</td></tr>'."\n"   // 'Address 1'
         .'<tr><td '.$strLabelStyle.'>Address 2:</td>  <td '.$strDataStyle.'>'.htmlspecialchars($regRec->strAddr2    )          .'</td></tr>'."\n"   // 'Address 2'
         .'<tr><td '.$strLabelStyle.'>City:</td>       <td '.$strDataStyle.'>'.htmlspecialchars($regRec->strCity     )          .'</td></tr>'."\n"   // 'City'
         .'<tr><td '.$strLabelStyle.'>'.$gclsChapterVoc->vocState.'</td><td '.$strDataStyle.'>'.htmlspecialchars($regRec->strState    )          .'</td></tr>'."\n"   // 'State'
         .'<tr><td '.$strLabelStyle.'>Country:</td>    <td '.$strDataStyle.'>'.htmlspecialchars($regRec->strCountry  )          .'</td></tr>'."\n"   // 'Country'
         .'<tr><td '.$strLabelStyle.'>'.$gclsChapterVoc->vocZip.'</td><td '.$strDataStyle.'>'.htmlspecialchars($regRec->strZip      )          .'</td></tr>'."\n"   // 'Zip'
         .'<tr><td '.$strLabelStyle.'>Phone:</td>    <td '.$strDataStyle.'>'.htmlspecialchars($regRec->strPhone    )          .'</td></tr>'."\n"   // 'Phone'
         .'<tr><td '.$strLabelStyle.'>Cell</td>      <td '.$strDataStyle.'.>'.htmlspecialchars($regRec->strCell     )          .'</td></tr>'."\n"   // 'Cell'
         .'<tr><td '.$strLabelStyle.'>Fax</td>       <td '.$strDataStyle.'.>'.htmlspecialchars($regRec->strFax      )          .'</td></tr>'."\n"   // 'Fax'
         .'<tr><td '.$strLabelStyle.'>EMail</td>     <td '.$strDataStyle.'.>'.htmlspecialchars($regRec->strEmail    )          .'</td></tr>'."\n"   // 'Email'
         .'<tr><td '.$strLabelStyle.'>Job Skills</td><td '.$strDataStyle.'.>'.nl2br(htmlspecialchars(implode("\n", $regRec->jobSkills))).'</td></tr>'."\n";       // 'Job Skills';
      $strOut .= '</table><br><br>';


         // personalized fields
      if ($this->utables > 0){
         foreach ($this->utables as $ut){
            $lTableID = $ut->lTableID;
            $strTabName = htmlspecialchars($ut->strUserTableName);
            $strOut .= $strTable.'<tr><td colspan="2" style="background-color: #ccc; text-align:center;"><b>'.$strTabName.'</b></td></tr>'."\n";
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$ut   <pre>');
echo(htmlspecialchars( print_r($ut, true))); echo('</pre></font><br>');
die;
// ------------------------------------- */

            if ($ut->lNumFields > 0){
               foreach ($ut->ufields as $uf){
                  $enumFieldType = $uf->enumFieldType;
                  if ($this->bExportableField($enumFieldType)){
                     $strIntFN = $uf->strFieldNameInternal;
                     if (isset($regRec->pTableData[$lTableID])){
                        $vVal = $this->valueViaType($enumFieldType, $strIntFN, $regRec->pTableData[$lTableID]);
                        $strOut .= '<tr><td '.$strLabelStyle.'>'
                                            .htmlspecialchars($uf->strFieldNameUser).':</td>'
                                      .'<td '.$strDataStyle.'>'.htmlspecialchars(fdh\strSimpleValueViaType($vVal, $enumFieldType)).'</td></tr>'."\n";
                     }else {
//                        $strOut .= CS_CSV_DELIMITER;
                     }
                  }
               }
            }
            $strOut .= '</table><br><br>';
         }
      }


      return($strOut);
   }

   function bExportableField($enumType){
      return(!($enumType==CS_FT_HEADING || $enumType==CS_FT_LOG));
   }

   function valueViaType($enumFieldType, $strIntFN, $pTableData){
   //---------------------------------------------------------------------
   // format the drop-down lists
   //---------------------------------------------------------------------
      if ($enumFieldType == CS_FT_DDL){
         $strIntFN = $strIntFN.'_ddlText';
         $strOut   = $pTableData->$strIntFN;
      }elseif ($enumFieldType == CS_FT_DDLMULTI){
         $strIntFN = $strIntFN.'_ddlMulti';
         if ($pTableData->$strIntFN->lNumEntries == 0){
            $strOut = '';
         }else {
            $strEntries = array();
            foreach ($pTableData->$strIntFN->entries as $entry){
               $strEntries[] = $entry->strDDLEntry;
            }
            $strOut = implode("\n", $strEntries);
         }
      }else {
         $strOut = $pTableData->$strIntFN;
      }
      return($strOut);
   }

 }
