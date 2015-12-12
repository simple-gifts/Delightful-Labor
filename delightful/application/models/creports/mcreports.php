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
      $this->load->helper('reports/search');
      $this->load->helper('dl_util/context');
      $this->load->helper('creports/creport_field');
      $this->load->helper('reports/creport_util');
      $this->load->model ('admin/madmin_aco');
      $this->load->model ('creports/mcreports', 'clsCReports');
---------------------------------------------------------------------

---------------------------------------------------------------------*/


//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mcreports extends CI_Model{

   public $cUserRpts, $lNumUsers, $cRptDir, $lNumRptsInDir;

   public $lNumReports, $reports;
   public $strWhereExtra, $strOrder;
   public $cRptTypes, $crptSchema, $ufSchema, $crptUFields;

   public $perms, $acctAccess;


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->cUserRpts = $this->lNumUsers =
      $this->cRptDir   = $this->lNumRptsInDir  = null;

      $this->lNumReports = $this->reports = null;

      $this->strWhereExtra = $this->strOrder = '';

      $this->cRptTypes = loadCReportTypeArray();
   }

   function loadUserDirectoryCounts(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      global $glUserID, $gbAdmin;

      $this->cUserRpts = array();

      $this->load->model('admin/muser_accts', 'clsUser');

      $sqlStr =
        'SELECT
            us_lKeyID, us_strUserName, us_strFirstName, us_strLastName
         FROM admin_users
         WHERE NOT us_bInactive
         ORDER BY us_strLastName, us_strFirstName, us_lKeyID;';
      $query = $this->db->query($sqlStr);
      $this->lNumUsers = $query->num_rows();
      $idx = 0;
      foreach ($query->result() as $row){
         $this->cUserRpts[$idx] = new stdClass;
         $cU = &$this->cUserRpts[$idx];
         $cU->lUserID       = $row->us_lKeyID;
         $cU->strUserName   = $row->us_strUserName;
         $cU->strFirstName  = $row->us_strFirstName;
         $cU->strLastName   = $row->us_strLastName;
         $cU->strSafeNameFL = htmlspecialchars($row->us_strFirstName.' '.$row->us_strLastName);
         $cU->bCurrentUser  = $row->us_lKeyID==$glUserID;
         $cU->lRptCnt       = $this->lNumRptsViaUserID($row->us_lKeyID, $cU->bCurrentUser || $gbAdmin);
         ++$idx;
      }
   }

   function lNumRptsViaUserID($lUserID, $bIncludePrivate){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           'SELECT COUNT(*) AS lNumRecs
            FROM creport_dir
            WHERE NOT crd_bRetired '
            .($bIncludePrivate ? '' : ' AND NOT crd_bPrivate ')."
            AND crd_lOriginID=$lUserID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return($row->lNumRecs);
   }

   function loadReportDirViaUserID($lUserID, $enumSort='', $strSQLWhereExtra=''){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gbAdmin;

      $this->cRpts = array();

      switch ($enumSort){
         case 'rptName':
            $strSort = ' crd_strName, crd_enumRptType, crd_dteOrigin DESC ';
            break;
         case 'rptDate':
            $strSort = ' crd_dteOrigin DESC, crd_strName, crd_enumRptType ';
            break;
         case 'rptType':
         default:
            $strSort = ' crd_enumRptType, crd_strName, crd_dteOrigin  DESC ';
            break;
      }

      $sqlStr =
        "SELECT
            crd_lKeyID, crd_strName, crd_strNotes, crd_enumRptType, crd_bPrivate,
            crd_lOriginID, crd_lLastUpdateID,
            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            UNIX_TIMESTAMP(crd_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(crd_dteLastUpdate) AS dteLastUpdate
         FROM creport_dir
            INNER JOIN admin_users AS usersC ON crd_lOriginID     = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL ON crd_lLastUpdateID = usersL.us_lKeyID
         WHERE
            NOT crd_bRetired
            AND crd_lOriginID=$lUserID
            $strSQLWhereExtra
         ORDER BY $strSort , crd_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumRptsInDir = $query->num_rows();
      $idx = 0;
      foreach ($query->result() as $row){
         $this->cRptDir[$idx] = new stdClass;
         $cRpt = &$this->cRptDir[$idx];

         $cRpt->lKeyID         = $row->crd_lKeyID;
         $cRpt->strName        = $row->crd_strName;
         $cRpt->strNotes       = $row->crd_strNotes;
         $cRpt->enumRptType    = $row->crd_enumRptType;
         $cRpt->bPrivate       = $row->crd_bPrivate;
         $cRpt->lOriginID      = $row->crd_lOriginID;
         $cRpt->lLastUpdateID  = $row->crd_lLastUpdateID;

         $cRpt->bUserIsOwner         = $cRpt->lOriginID == $glUserID;
         $cRpt->bUserHasReadAccess   = $gbAdmin || !$cRpt->bPrivate || $cRpt->bUserIsOwner;
         $cRpt->bUserHasWriteAccess  = $gbAdmin || $cRpt->bUserIsOwner;

         $cRpt->strCFName      = $row->strCFName;
         $cRpt->strCLName      = $row->strCLName;
         $cRpt->strLFName      = $row->strLFName;
         $cRpt->strLLName      = $row->strLLName;
         $cRpt->dteOrigin      = $row->dteOrigin;
         $cRpt->dteLastUpdate  = $row->dteLastUpdate;

         ++$idx;
      }
   }

   function loadReportViaID($lCRptID, $bIncludeFields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strWhereExtra = " AND crd_lKeyID=$lCRptID ";
      $this->loadReports($bIncludeFields);
   }

   function loadReports($bIncludeFields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gbAdmin;

      $this->reports = array();

      if ($this->strOrder.'' != ''){
         $strOrderBy = $this->strOrder;
      }else {
         $strOrderBy = ' crd_lKeyID ';
      }

      $sqlStr =
        "SELECT
            crd_lKeyID, crd_strName, crd_strNotes, crd_enumRptType,
            crd_bPrivate, crd_lOriginID, crd_lLastUpdateID,
            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            UNIX_TIMESTAMP(crd_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(crd_dteLastUpdate) AS dteLastUpdate

         FROM creport_dir
            INNER JOIN admin_users AS usersC ON crd_lOriginID      = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL ON crd_lLastUpdateID  = usersL.us_lKeyID

         WHERE NOT crd_bRetired $this->strWhereExtra
         ORDER BY $strOrderBy;";

      $query = $this->db->query($sqlStr);
      $this->lNumReports = $lNumReports = $query->num_rows();
      $idx = 0;
      if ($lNumReports == 0){
         $this->reports[0] = new stdClass;
         $cRpt = &$this->reports[0];

         $cRpt->lKeyID               =
         $cRpt->strName              =
         $cRpt->strSafeName          =
         $cRpt->strNotes             =
         $cRpt->enumRptType          =
         $cRpt->strXlatedRptType     =
         $cRpt->bPrivate             =
         $cRpt->lOriginID            =
         $cRpt->lLastUpdateID        =

         $cRpt->bUserIsOwner         =
         $cRpt->bUserHasReadAccess   =
         $cRpt->bUserHasWriteAccess  =

         $cRpt->strCFName            =
         $cRpt->strCLName            =
         $cRpt->strSafeRptOwner      =
         $cRpt->strLFName            =
         $cRpt->strLLName            =
         $cRpt->dteOrigin            =
         $cRpt->dteLastUpdate        =
         $cRpt->fields               = null;
      }else {
         foreach ($query->result() as $row){
            $this->reports[$idx] = new stdClass;
            $cRpt = &$this->reports[$idx];

            $cRpt->lKeyID               = (int)$row->crd_lKeyID;
            $cRpt->strName              = $row->crd_strName;
            $cRpt->strSafeName          = htmlspecialchars($row->crd_strName);
            $cRpt->strNotes             = $row->crd_strNotes;
            $cRpt->enumRptType          = $row->crd_enumRptType;
            $cRpt->strXlatedRptType     = $this->cRptTypes[$row->crd_enumRptType]->strLabel;
            $cRpt->bPrivate             = (bool)$row->crd_bPrivate;
            $cRpt->lOriginID            = (int)$row->crd_lOriginID;
            $cRpt->lLastUpdateID        = $row->crd_lLastUpdateID;

            $cRpt->bUserIsOwner         = $cRpt->lOriginID == $glUserID;
            $cRpt->bUserHasReadAccess   = $gbAdmin || !$cRpt->bPrivate || $cRpt->bUserIsOwner;
            $cRpt->bUserHasWriteAccess  = $gbAdmin || $cRpt->bUserIsOwner;

            $cRpt->strCFName            = $row->strCFName;
            $cRpt->strCLName            = $row->strCLName;
            $cRpt->strSafeRptOwner      = htmlspecialchars($row->strCFName.' '.$row->strCLName);
            $cRpt->strLFName            = $row->strLFName;
            $cRpt->strLLName            = $row->strLLName;
            $cRpt->dteOrigin            = (int)$row->dteOrigin;
            $cRpt->dteLastUpdate        = (int)$row->dteLastUpdate;

            if ($bIncludeFields){
               $cRpt->fields = $this->cReportFields($cRpt->lKeyID, $cRpt->lNumFields, false);
            }
            ++$idx;
         }
         if ($bIncludeFields && $cRpt->lNumFields > 0){
            $caco = new madmin_aco;
            foreach ($cRpt->fields as $field){
               if ($field->enumType == CS_FT_CURRENCY){
                  $caco->setACOClassViaFieldID($field->lFieldID, $field->ACO);
               }
            }
         }
      }
   }

   function bVerifyUserAccessToReport($report, &$lNumFails, &$failTables){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID, $gbAdmin;

      $lNumFails = 0; $failTables = array();

      if ($gbAdmin) return(true);

      $lReportID = $report->lKeyID;

         // load search terms
      $this->loadSearchTermViaReportID($lReportID);

         // load sort terms
      $this->loadSortFieldsViaReportID($lReportID, $lNumSortTerms, $sortTerms);

      $this->uf = new muser_fields;

         // tables needed for the report
      crptTables\tablesUsed($report, $this->terms, $sortTerms, $tableIDs);
      if (count($tableIDs)==0) return(true);
      $cperm = new mpermissions;
      $cperm->loadUserAcctInfo($glUserID, $acctAccess);

      $cUF = new muser_fields;
      $cUF->lTableID = array();
      foreach ($tableIDs as $TID){
         $cUF->lTableID[] = $TID;
      }
      $cUF->loadTableViaTableID(false);

      foreach ($cUF->userTables as $utable){
         if (!$cperm->bDoesUserHaveAccess($acctAccess, $utable->lNumConsolidated, $utable->cperms)){
            $failTables[$lNumFails] = '['.$utable->enumTType.'] '.$utable->strUserTableName;
            ++$lNumFails;
         }
      }
      return($lNumFails == 0);
   }

   function addNewCReport(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
        'INSERT INTO creport_dir
         SET '.$this->crSQLCommon().",
             crd_bRetired  = 0,
             crd_lOriginID = $glUserID,
             crd_dteOrigin = NOW();";
      $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function updateCReport($lCRptID){
      $sqlStr =
        'UPDATE creport_dir
         SET '.$this->crSQLCommon()."
         WHERE crd_lKeyID=$lCRptID;";
      $this->db->query($sqlStr);
   }

   private function crSQLCommon(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $cRpt = &$this->reports[0];

      return(
          'crd_strName     = '.strPrepStr($cRpt->strName).',
           crd_strNotes    = '.strPrepStr($cRpt->strNotes).',
           crd_enumRptType = '.strPrepStr($cRpt->enumRptType).',
           crd_bPrivate    = '.($cRpt->bPrivate ? '1' : '0').",
           crd_lLastUpdateID = $glUserID,
           crd_dteLastUpdate = NOW() ");
   }

   function removeCReport($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // remove sort terms
      $sqlStr = "DELETE FROM creport_sort WHERE crst_lReportID=$lReportID";
      $this->db->query($sqlStr);

         // remove search terms
      $sqlStr = "DELETE FROM creport_search WHERE crs_lReportID=$lReportID";
      $this->db->query($sqlStr);

         // remove reporting fields
      $sqlStr = "DELETE FROM creport_fields WHERE crf_lReportID=$lReportID";
      $this->db->query($sqlStr);

         // remove the report
      $sqlStr = "DELETE FROM creport_dir WHERE crd_lKeyID=$lReportID";
      $this->db->query($sqlStr);
   }

   /*------------------------------------------------------
             F I E L D S
   ------------------------------------------------------*/
   function lFieldCount($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "SELECT COUNT(*) AS lNumRecs
                 FROM creport_fields
                 WHERE crf_lReportID=$lReportID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return($row->lNumRecs);
   }

   function cReportFields($lReportID, &$lNumFields, $bSimpleArray=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $fields = array();
      $sqlStr =
        "SELECT crf_lKeyID, crf_lReportID, crf_strFieldName, crf_lSortIDX,
           crf_lFieldID, crf_lTableID, crf_strTableName,
           pft_enumAttachType,
           pft_strUserTableName, pff_strFieldNameUser, pff_enumFieldType
         FROM creport_fields
            LEFT JOIN uf_tables ON pft_lKeyID   = crf_lTableID
            LEFT JOIN uf_fields ON crf_lFieldID = pff_lKeyID
         WHERE crf_lReportID=$lReportID
         ORDER BY crf_lSortIDX, crf_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumFields = $query->num_rows();
      if ($lNumFields > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            if ($bSimpleArray){
               $fields[$idx] = $row->crf_strFieldName;
            }else {
               $fields[$idx] = new stdClass;
               $field = &$fields[$idx];
               $field->lKeyID           = (int)$row->crf_lKeyID;
               $field->lReportID        = (int)$row->crf_lReportID;
               $field->strFieldName     = $row->crf_strFieldName;
               $field->strUserFN        = $row->pff_strFieldNameUser;
               $field->enumType         = $row->pff_enumFieldType;
               $field->lSortIDX         = (int)$row->crf_lSortIDX;
               $field->lFieldID         = (int)$row->crf_lFieldID;
               $field->lTableID         = (int)$row->crf_lTableID;
               $field->strUserTableName = $row->pft_strUserTableName;
               $field->enumParentTable  = $row->pft_enumAttachType;

               if ($field->lTableID > 0 ){
                  $field->strAttachLabel   = strLabelViaContextType($field->enumParentTable, true, false);
               }
/*
               crptFieldPropsParentTable($field->lTableID, $field->strFieldName,
                         $field->strUserTableName, $field->enumType,
                         $field->strFieldName, $field->enumAttachType);
*/

            }
            ++$idx;
         }

            // load parent table info
         if (!$bSimpleArray){
            crptFields\parentTableFieldInfo($fields);
         }
      }
      return($fields);
   }

    public function strCReportHTMLSummary(){
   //-----------------------------------------------------------------------
   // assumes user has called $this->clsCReports->loadReportViaID($lReportID, ...
   //-----------------------------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $clsRpt->setEntrySummary();

      $report = &$this->reports[0];
      $lReportID = $report->lKeyID;
      $strOut =
          $clsRpt->openReport('', '')

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Report Name:')
         .$clsRpt->writeCell (strLinkView_CReportRec($lReportID, 'View report record', true).'&nbsp;'
                              .$report->strSafeName)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Report ID:')
         .$clsRpt->writeCell (str_pad($lReportID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Type:')
         .$clsRpt->writeCell ($report->strXlatedRptType)
         .$clsRpt->closeRow  ()

         .$clsRpt->openRow   (false)
         .$clsRpt->writeLabel('Owner:')
         .$clsRpt->writeCell ($report->strSafeRptOwner)
         .$clsRpt->closeRow  ()

         .$clsRpt->closeReport('<br>');

      return($strOut);
   }

   /*------------------------------------------------------
            S O R T   O R D E R
   ------------------------------------------------------*/
   function addSortTerm($opts){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      if (is_null($opts->lFieldID)) $opts->lFieldID = 0;

      $sqlStr =
        'INSERT INTO creport_sort
         SET
            crst_bLarkAscending = '.($opts->bAscending ? '1' : '0').',
            crst_strFieldID     = '.strPrepStr($opts->strFieldID).',
            crst_lSortIDX       = '.($this->lMaxSortOrderIDX($opts->lReportID)+1).",
            crst_lReportID      = $opts->lReportID,
            crst_lFieldID       = $opts->lFieldID,
            crst_lTableID       = $opts->lTableID,
            crst_lOriginID      = $glUserID,
            crst_lLastUpdateID  = $glUserID,
            crst_dteOrigin      = NOW(),
            crst_dteLastUpdate  = NOW();";

      $this->db->query($sqlStr);
      return((int)$this->db->insert_id());
   }

   function lMaxSortOrderIDX($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT MAX(crst_lSortIDX) AS lMaxSortIDX
         FROM creport_sort
         WHERE crst_lReportID=$lReportID;";

      $query = $this->db->query($sqlStr);
      $row = $query->row();
      if (is_null($row->lMaxSortIDX)){
         return(0);
      }else {
         return((int)$row->lMaxSortIDX);
      }
   }

   function bSortTermGoesWithReport($lReportID, $lSortTermID){
   //---------------------------------------------------------------------
   // defense against the dark arts
   //---------------------------------------------------------------------
      $sqlStr = "SELECT crst_lReportID FROM creport_sort WHERE crst_lKeyID=$lSortTermID;";
      $query = $this->db->query($sqlStr);
      $lNumTerms = $query->num_rows();
      if ($lNumTerms == 0){
         return(false);
      }else {
         $row = $query->row();
         return(((int)$row->crst_lReportID) == $lReportID);
      }
   }

   function deleteSortOrderTerm($lSortTermID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "DELETE FROM creport_sort WHERE crst_lKeyID=$lSortTermID;";
      $this->db->query($sqlStr);
   }

   function deleteSortOrderTermViaRptIDFieldID($lReportID, $lFieldID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
            "DELETE FROM creport_sort
             WHERE crst_lReportID=$lReportID AND crst_lFieldID=$lFieldID;";
      $this->db->query($sqlStr);
   }

   function loadSortFieldsViaReportID($lReportID, &$lNumTerms, &$sortTerms){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumTerms = 0;
      $sortTerms = array();

      $sqlStr =
        "SELECT
            crst_lKeyID, crst_lReportID, crst_lFieldID, crst_lTableID,
            crst_strFieldID, crst_lSortIDX, crst_bLarkAscending,
            crst_lOriginID, crst_lLastUpdateID,

            pff_strFieldNameUser, pff_enumFieldType, pft_strUserTableName, pft_enumAttachType,

            usersC.us_strFirstName AS strCFName, usersC.us_strLastName AS strCLName,
            usersL.us_strFirstName AS strLFName, usersL.us_strLastName AS strLLName,
            UNIX_TIMESTAMP(crst_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(crst_dteLastUpdate) AS dteLastUpdate
         FROM creport_sort
            INNER JOIN admin_users AS usersC ON crst_lOriginID      = usersC.us_lKeyID
            INNER JOIN admin_users AS usersL ON crst_lLastUpdateID  = usersL.us_lKeyID

            LEFT JOIN uf_fields ON pff_lKeyID   = crst_lFieldID
            LEFT JOIN uf_tables ON pff_lTableID = pft_lKeyID

         WHERE crst_lReportID=$lReportID
         ORDER BY crst_lSortIDX, crst_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumTerms = $query->num_rows();
      $idx = 0;
      if ($lNumTerms == 0){
         $sortTerms[0] = new stdClass;
         $sortTerm = &$sortTerms[0];

         $sortTerm->lKeyID           =
         $sortTerm->lReportID        =
         $sortTerm->lFieldID         =
         $sortTerm->lTableID         =
         $sortTerm->strFieldID       =
         $sortTerm->lSortIDX         =
         $sortTerm->bLarkAscending   =

         $sortTerm->strFieldNameUser =
         $sortTerm->enumFieldType    =
         $sortTerm->strUserTableName =
         $sortTerm->enumAttachType   =

         $sortTerm->lOriginID        =
         $sortTerm->lLastUpdateID    =
         $sortTerm->strCFName        =
         $sortTerm->strCLName        =
         $sortTerm->strLFName        =
         $sortTerm->strLLName        =
         $sortTerm->dteOrigin        =
         $sortTerm->dteLastUpdate    = null;
      }else {
         foreach ($query->result() as $row){
            $sortTerms[$idx] = new stdClass;
            $sortTerm = &$sortTerms[$idx];

            $sortTerm->lKeyID           = (int)$row->crst_lKeyID;
            $sortTerm->lReportID        = (int)$row->crst_lReportID;
            $sortTerm->lFieldID         = (int)$row->crst_lFieldID;
            $sortTerm->lTableID         = (int)$row->crst_lTableID;
            $sortTerm->strFieldID       = $row->crst_strFieldID;
            $sortTerm->lSortIDX         = (int)$row->crst_lSortIDX;
            $sortTerm->bLarkAscending   = (bool)$row->crst_bLarkAscending;

            if ($sortTerm->lTableID <=0 ){
               crptFieldPropsParentTable($sortTerm->lTableID, $sortTerm->strFieldID,
                         $sortTerm->strUserTableName, $sortTerm->enumFieldType, $sortTerm->strFieldNameUser, $sortTerm->enumAttachType);
            }else {
               $sortTerm->enumFieldType     = $row->pff_enumFieldType;
               $sortTerm->strUserTableName  = $row->pft_strUserTableName;
               $sortTerm->strFieldNameUser  = $row->pff_strFieldNameUser;
               $sortTerm->enumAttachType    = $row->pft_enumAttachType;
               $sortTerm->strAttachLabel    = strLabelViaContextType($sortTerm->enumAttachType, true, false);
            }

            $sortTerm->lOriginID        = (int)$row->crst_lOriginID;
            $sortTerm->lLastUpdateID    = (int)$row->crst_lLastUpdateID;
            $sortTerm->strCFName        = $row->strCFName;
            $sortTerm->strCLName        = $row->strCLName;
            $sortTerm->strLFName        = $row->strLFName;
            $sortTerm->strLLName        = $row->strLLName;
            $sortTerm->dteOrigin        = (int)$row->dteOrigin;
            $sortTerm->dteLastUpdate    = (int)$row->dteLastUpdate;

            ++$idx;
         }
      }
/* -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$sortTerms   <pre>');
echo(htmlspecialchars( print_r($sortTerms, true))); echo('</pre></font><br>');
// ------------------------------------- */

   }


   /*------------------------------------------------------
            T A B L E   S T R U C T U R E S
   ------------------------------------------------------*/
   function loadTableStructures($enumRptType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $tables = array();
      $lTableIDX = -1;
      $lFieldIDX = 0;
      $crptType = &$this->cRptTypes[$enumRptType];

      $this->crptSchema  = new muser_schema;
      $this->crptUFields = new muser_fields;

      switch ($enumRptType){
         case CE_CRPT_GIFTS:

               //----------------------
               // Gift table
               //----------------------
            $this->addReportTable($tables, $lTableIDX, $lFieldIDX, 'Gifts', 'gifts', CL_STID_GIFTS);
            $this->addBaseGiftFields($tables, $lTableIDX);

               //----------------------
               // Donor table
               //----------------------
            $this->addReportTable($tables, $lTableIDX, $lFieldIDX, 'Donor', 'people_names', CL_STID_PEOPLEBIZ);
            $this->addBaseDonorFields($tables, $lTableIDX);
            break;

         case CE_CRPT_PEOPLE:
            $this->addReportTable($tables, $lTableIDX, $lFieldIDX, 'People', 'people_names', CL_STID_PEOPLE);

            $this->addBasePeopleFields($tables, $lTableIDX);

               // personalized tables associated with clients
            $this->crptSchema->sqlWhereExtra = '';
            $this->crptSchema->loadUFSchemaViaAttachType(CENUM_CONTEXT_PEOPLE, false, true);
            $this->ufSchema = &$this->crptSchema->schema;

         case CE_CRPT_CLIENTS:
            $this->addReportTable($tables, $lTableIDX, $lFieldIDX, 'Client', 'client_records', CL_STID_CLIENT);

            $this->addBaseClientFields($tables, $lTableIDX);

               // personalized tables associated with clients
            $this->crptSchema->sqlWhereExtra = '';
            $this->crptSchema->loadUFSchemaViaAttachType(CENUM_CONTEXT_CLIENT, false, true);
            $this->ufSchema = &$this->crptSchema->schema;
            break;

         default:
            screamForHelp($enumRptType.': unknow custom report type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }


         // transfer personalized tables to the master table structure
      $lNumPTables = count($this->ufSchema);
      if ($lNumPTables > 0){
         $lTIdx = count($tables);
         foreach ($this->ufSchema as $pTable){
            if ($pTable->bAllowAccess){
               $this->xferPTableToTable($tables, $lTIdx, $pTable);
               ++$lTIdx;
            }
         }
      }

      return($tables);
   }

   private function xferPTableToTable(&$tables, $lTIdx, &$pTable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $tables[$lTIdx] = new stdClass;
      $t = &$tables[$lTIdx];
      $t->lTableID       = $pTable->lTableID;
      $t->name           = $pTable->strUserTableName;
      $t->internalTName  = $pTable->strDataTableName;
      $t->enumAttachType = $pTable->enumAttachType;
      $t->strAttachLabel = strLabelViaContextType($t->enumAttachType, true, false);
      $t->tType = 'Personalized table: '.($pTable->bMultiEntry ? 'Multiple' : 'Single').'-entry';
      $t->fields = array();
      $lFIdx = 0;
      foreach ($pTable->fields as $pField){
         $pfType = $pField->enumFieldType;
         if (!$pField->bHidden){
            $this->addReportFields($t->fields, $lFIdx, $pfType,
                 $pField->strFieldNameUser, $pField->strFieldNameInternal,
                 $pField->lCurrencyACO, $pField->lFieldID);
         }
      }
   }

   function addReportFields(
                       &$fields, &$lFieldIDX, $enumType,
                       $publicName, $internalName, $lCurrencyACO=null, $lFieldID=null){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $fields[$lFieldIDX] = new stdClass;
      $fields[$lFieldIDX]->lFieldID     = $lFieldID;
      $fields[$lFieldIDX]->publicName   = $publicName;
      $fields[$lFieldIDX]->internalName = $internalName;
      $fields[$lFieldIDX]->enumType     = $enumType;
      $fields[$lFieldIDX]->lCurrencyACO = $lCurrencyACO;
      if ($enumType == CS_FT_CURRENCY){
         $cACO = new madmin_aco;
         $cACO->loadCountries(false, true, true, $lCurrencyACO);
         $fields[$lFieldIDX]->ACO = clone($cACO->countries[0]);
      }
      $fields[$lFieldIDX]->fTypeLabel   = $this->crptUFields->strFieldTypeLabel($enumType);
      ++$lFieldIDX;
   }

   private function addBasePeopleFields(&$tables, $lTableIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterVoc;

      $fields = &$tables[$lTableIDX]->fields;
      $lFieldIDX = 0;
      $this->addReportFields($fields, $lFieldIDX, CS_FT_ID,            'People ID',               'pe_lKeyID');

      $this->addReportFields($fields, $lFieldIDX, CS_FT_ID,            'Household ID',            'pe_lHouseholdID');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Title',                   'pe_strTitle');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'First Name',              'pe_strFName');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Middle Name',             'pe_strMName');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Last Name',               'pe_strLName');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Preferred Name',          'pe_strPreferredName');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Salutation',              'pe_strSalutation');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_DATE,          'Birth Date',              'pe_dteBirthDate');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Gender',                  'pe_enumGender');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_ID,            'Accounting Country',      'pe_lACO');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Address 1',               'pe_strAddr1');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Address 2',               'pe_strAddr2');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'City',                    'pe_strCity');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          $gclsChapterVoc->vocState, 'pe_strState');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Country',                 'pe_strCountry');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          $gclsChapterVoc->vocZip,   'pe_strZip');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Phone',                   'pe_strPhone');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Cell',                    'pe_strCell');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Fax',                     'pe_strFax');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Web Site',                'pe_strWebSite');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Email',                   'pe_strEmail');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,          'Notes',                   'pe_strNotes');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_CHECKBOX,      'No Gift Ack.',            'pe_bNoGiftAcknowledge');
   }

   private function addBaseClientFields(&$tables, $lTableIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterVoc;

      $fields = &$tables[$lTableIDX]->fields;
      $lFieldIDX = 0;
      $this->addReportFields($fields, $lFieldIDX, CS_FT_ID,          'Client ID',               'cr_lKeyID');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_DATE,        'Date Enrolled',           'cr_dteEnrollment');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        'Last Name',               'cr_strLName');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        'First Name',              'cr_strFName');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        'Middle Name',             'cr_strMName');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_DATE,        'Birth Date',              'cr_dteBirth');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        'Gender',                  'cr_enumGender');

      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        'Address 1',               'cr_strAddr1');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        'Address 2',               'cr_strAddr2');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        'City',                    'cr_strCity');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        $gclsChapterVoc->vocState, 'cr_strState');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        'Country',                 'cr_strCountry');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        $gclsChapterVoc->vocZip,   'cr_strZip');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        'Phone',                   'cr_strPhone');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        'Cell',                    'cr_strCell');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        'Email',                   'cr_strEmail');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_DDL_SPECIAL, 'Location',                'cr_lLocationID');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_DDL_SPECIAL, 'Status Category',         'cr_lStatusCatID');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_DDL_SPECIAL, 'Vocabulary',              'cr_lVocID');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_INTEGER,     'Max # of Sponsors',       'cr_lMaxSponsors');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT,        'Bio',                     'cr_strBio');
   }

   private function addBaseGiftFields(&$tables, $lTableIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $fields = &$tables[$lTableIDX]->fields;
      $lFieldIDX = 0;
      $this->addReportFields($fields, $lFieldIDX, CS_FT_ID,       'Gift ID',           'gi_lKeyID');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_CURRENCY, 'Amount',            'gi_curAmnt');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_DATE,     'Date of Donation',  'dteDonation');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_DATE,     'Date Recorded',     'dteRecorded');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_CHECKBOX, 'In-Kind?',          'gi_bGIK');
   }

   private function addBaseDonorFields(&$tables, $lTableIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $fields = &$tables[$lTableIDX]->fields;
      $lFieldIDX = 0;
      $this->addReportFields($fields, $lFieldIDX, CS_FT_ID,   'Business ID',   'businessID');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_ID,   'People ID',     'peopleID');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT, 'Business Name', 'businessName');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT, 'Last Name',     'lastName');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT, 'First Name',    'firstName');
      $this->addReportFields($fields, $lFieldIDX, CS_FT_TEXT, 'Middle Name',   'middleName');
   }

   function addReportTable(&$tables, &$lTableIDX, &$lFieldIDX,
                           $strPublicTableName, $strInternalName, $lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      ++$lTableIDX;
      $tables[$lTableIDX] = new stdClass;
      $tt = &$tables[$lTableIDX];
      $tt->name          = $strPublicTableName;
      $tt->internalTName = $strInternalName;
      $tt->lTableID      = $lTableID;
      $tt->tType         = '';
      $tt->fields = array();

      $lFieldIDX = 0;
   }

   function strCRptSelect($enumRptType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strSelect = '';
      switch ($enumRptType){
         case CE_CRPT_GIFTS:
            $strSelect =
              "SELECT
                  gi_lKeyID,
                  gi_curAmnt, gi_dteDonation,
                  UNIX_TIMESTAMP(gi_dteOrigin) AS dteRecorded,
                  IF((donor.pe_bBiz*gi_lForeignID)=0, null, gi_lForeignID) AS businessID,
                  IF((donor.pe_bBiz*gi_lForeignID)=0, gi_lForeignID, null) AS peopleID,
                  IF (donor.pe_bBiz, donor.pe_strLName, null) AS businessName,
                  IF (donor.pe_bBiz, null, donor.pe_strLName) AS lastName,
                  IF (donor.pe_bBiz, null, donor.pe_strFName) AS firstName,
                  IF (donor.pe_bBiz, null, donor.pe_strMName) AS middleName ";
            break;

         default:
            screamForHelp($enumRptType.': unknow custom report type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strSelect);
   }

   function saveFields($lReportID, $fields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->deleteReportFields($lReportID);
      $idx = 0;
      foreach ($fields as $field){
         $fieldInfo = explode('|', $field);
         $lTableID = (int)$fieldInfo[0];
         if ($fieldInfo[1].'' == ''){
            $lFieldID = 'null';
         }else {
            $lFieldID = (int)$fieldInfo[1];
         }
         $strFN = $fieldInfo[2];

         $sqlStr =
             "INSERT INTO creport_fields
              SET
                 crf_lReportID = $lReportID,
                 crf_lTableID  = $lTableID,
                 crf_lFieldID  = $lFieldID,
                 crf_lSortIDX  = $idx,
                 crf_strFieldName=".strPrepStr($strFN).';';
         $this->db->query($sqlStr);
         ++$idx;
      }
   }

   function deleteReportFields($lReportID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "DELETE FROM creport_fields WHERE crf_lReportID=$lReportID;";
      $this->db->query($sqlStr);
   }

   function deleteSingleReportDisplayField($lReportID, $lFieldID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "DELETE FROM creport_fields
          WHERE  crf_lReportID=$lReportID AND crf_lFieldID=$lFieldID;";
      $this->db->query($sqlStr);
   }

   function findFieldInTables($strInternalFN, $tables, &$lTableIDX, &$lFieldIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lTableIDX = 0;
      foreach ($tables as $table){
/* -------------------------------------
$zzzlPos = @strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, @strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$strInternalFN = $strInternalFN <br></font>\n");

echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$table   <pre>');
echo(htmlspecialchars( print_r($table, true))); echo('</pre></font><br>');
// -------------------------------------*/

         $lFieldIDX = 0;
         foreach ($table->fields as $field){
            if ($field->internalName == $strInternalFN) return;
            ++$lFieldIDX;
         }
         ++$lTableIDX;
      }
      $lTableIDX = $lFieldIDX = null;
   }

}