<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2013 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('clients/mclient_intake', 'cIntake');
--------------------------------------------------------------------

---------------------------------------------------------------------*/


class mclient_intake extends CI_Model{
   public
       $intakeRecs, $lNumIntakeRecs,
       $strWhereExtra, $strOrder;

   public $lNumTables, $utables;

   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->intakeRecs = null;
      $this->lNumIntakeRecs = 0;
      $this->strWhereExtra  = $this->strOrder = '';

      $this->lNumTables = 0;
      $this->utables    = null;
   }
   
   function loadClientIntakeFormsViaCIFID($lCIFormID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = " AND cif_lKeyID=$lCIFormID ";
      $this->loadIntakeForms();
   }   
   
   function loadIntakeForms(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->regRecs = array();

      if ($this->strOrder == ''){
         $strOrder = ' cif_strFormName, cif_lKeyID ';
      }else {
         $strOrder = $this->strOrder;
      }
      $sqlStr = "
         SELECT
            cif_lKeyID, cif_strFormName, cif_strDescription,
            cif_strIntro, cif_strSubmissionText, cif_strBannerTitle,
            cif_strContact, cif_strContactPhone, cif_strContactEmail, 

                 -- standard display fields
            cif_bShowFName, cif_bShowLName, cif_bShowAddr, cif_bShowEmail,
            cif_bShowPhone,  cif_bShowCell, cif_bShowBDay, 
            cif_bShowDisclaimer, cif_strDisclaimerAck, cif_strDisclaimer, 
            cif_lClientGroupID,

            cif_bFNameRequired, cif_bLNameRequired, cif_bAddrRequired, cif_bEmailRequired,
            cif_bPhoneRequired, cif_bCellRequired,
            cif_bBDateRequired, cif_bDisclaimerAckRqrd,

            cif_bRetired, cif_lOriginID, cif_lLastUpdateID,

            UNIX_TIMESTAMP(cif_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(cif_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName
         FROM client_intake_forms
            INNER JOIN admin_users   AS uc ON uc.us_lKeyID=cif_lOriginID
            INNER JOIN admin_users   AS ul ON ul.us_lKeyID=cif_lLastUpdateID

         WHERE NOT cif_bRetired $this->strWhereExtra
         ORDER BY $strOrder;";
      $query = $this->db->query($sqlStr);
      $this->lNumIntakeRecs = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->intakeRecs[0] = new stdClass;
         $rRec = &$this->intakeRecs[0];
         $rRec->lKeyID               =
         $rRec->strFormName          =
         $rRec->strDescription       =
         $rRec->strIntro             =
         $rRec->strSubmissionText    = 
         $rRec->strBannerTitle       =
         $rRec->strContact           =
         $rRec->strContactPhone      =
         $rRec->strContactEmail      =
         $rRec->lClientGroupID       =

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
            $this->intakeRecs[$idx] = new stdClass;
            $rRec = &$this->intakeRecs[$idx];

            $rRec->lKeyID               = $row->cif_lKeyID;
            $rRec->strFormName          = $row->cif_strFormName;
            $rRec->strDescription       = $row->cif_strDescription;
            $rRec->strIntro             = $row->cif_strIntro;
            $rRec->strSubmissionText    = $row->cif_strSubmissionText;
            $rRec->strBannerTitle       = $row->cif_strBannerTitle;
            $rRec->strContact           = $row->cif_strContact;
            $rRec->strContactPhone      = $row->cif_strContactPhone;
            $rRec->strContactEmail      = $row->cif_strContactEmail;
            $rRec->lClientGroupID       = $row->cif_lClientGroupID;

            $rRec->bShowFName           = (boolean)$row->cif_bShowFName;
            $rRec->bShowLName           = (boolean)$row->cif_bShowLName;
            $rRec->bShowAddr            = (boolean)$row->cif_bShowAddr;
            $rRec->bShowEmail           = (boolean)$row->cif_bShowEmail;
            $rRec->bShowPhone           = (boolean)$row->cif_bShowPhone;
            $rRec->bShowCell            = (boolean)$row->cif_bShowCell;

            $rRec->bShowBDay            = (boolean)$row->cif_bShowBDay;

               // disclaimer
            $rRec->bShowDisclaimer      = (boolean)$row->cif_bShowDisclaimer;
            $rRec->strDisclaimerAck     = $row->cif_strDisclaimerAck;
            $rRec->strDisclaimer        = $row->cif_strDisclaimer;
            $rRec->bDisclaimerAckRqrd   = (boolean)$row->cif_bDisclaimerAckRqrd;

            $rRec->bFNameRequired       = (boolean)$row->cif_bFNameRequired;
            $rRec->bLNameRequired       = (boolean)$row->cif_bLNameRequired;
            $rRec->bAddrRequired        = (boolean)$row->cif_bAddrRequired;
            $rRec->bEmailRequired       = (boolean)$row->cif_bEmailRequired;
            $rRec->bPhoneRequired       = (boolean)$row->cif_bPhoneRequired;
            $rRec->bCellRequired        = (boolean)$row->cif_bCellRequired;

            $rRec->bBDateRequired       = (boolean)$row->cif_bBDateRequired;

            $rRec->bRetired             = (boolean)$row->cif_bRetired;

            $rRec->lOriginID            = $row->cif_lOriginID;
            $rRec->lLastUpdateID        = $row->cif_lLastUpdateID;
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
   
   
   
   function bShowRequiredUFFields($lCIFormID, $lTableID, $lFieldID, &$bShow, &$bRequired){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $bShow = $bRequired = false;

      $sqlStr =
        "SELECT ciuf_bRequired
         FROM client_intake_uf
         WHERE ciuf_lCIFormID=$lCIFormID
            AND ciuf_lTableID=$lTableID
            AND ciuf_lFieldID=$lFieldID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() != 0){
         $row = $query->row();
         $bShow = true;
         $bRequired = $row->ciuf_bRequired;
      }
   }
   
   
   function strPublicUFTable($lTableID, $lFormID, $strDefault){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT citl_strLabel
         FROM client_intake_table_labels
         WHERE citl_lCIFormID = $lFormID
            AND citl_lTableID=$lTableID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return($strDefault);
      }else {
         $row = $query->row();
         return($row->citl_strLabel);
      }
   }   
   
   
   
   
   
   
   
   
}



