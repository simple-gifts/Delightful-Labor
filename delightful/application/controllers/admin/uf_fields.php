<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class uf_fields extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

	public function view($lTableID)	{
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterACO;
      if (!bTestForURLHack('adminOnly')) return;

      $displayData = array();
      $displayData['js'] = '';

      $this->load->model('admin/madmin_aco', 'clsACO');

         /*------------------------------------------------
             models/libraries/helpers
         ------------------------------------------------*/
      $params = array('enumStyle' => 'enpRptC');
      $this->load->library('generic_rpt', $params);
      $this->load->library('util/up_down_top_bottom');
      $this->load->helper('personalization/link_personalization');
      $this->load->model('admin/mpermissions',           'perms');
      $this->load->model('personalization/muser_fields', 'clsUF');

      $displayData['lTableID']   = $this->clsUF->lTableID = $lTableID;
      $this->clsUF->loadTableViaTableID();
      $this->clsUF->loadTableFields(false);
      $enumTType = $this->clsUF->userTables[0]->enumTType;
      $this->clsUF->setTType($enumTType);

      $displayData['userTable']     = $userTable = $this->clsUF->userTables[0];
      $displayData['strTTypeLabel'] = $this->clsUF->strTTypeLabel;
      $displayData['lNumFields']    = $this->clsUF->lNumFields;
      $displayData['fields']        = $this->clsUF->fields;
      $displayData['clsUF']         = $this->clsUF;

      $displayData['bClientProg'] = $bClientProg = $enumTType==CENUM_CONTEXT_CPROGENROLL || $enumTType==CENUM_CONTEXT_CPROGATTEND;
      if ($bClientProg){
         $this->load->model('client_features/mcprograms',         'cprograms');
         $this->load->helper('clients/link_client_features');

         $displayData['bEnrollment'] = $bEnrollment = $enumTType==CENUM_CONTEXT_CPROGENROLL;
         if ($bEnrollment){
            $this->cprograms->loadClientProgramsViaETableID($lTableID);
         }else {
            $this->cprograms->loadClientProgramsViaATableID($lTableID);
         }
         $cprog = &$this->cprograms->cprogs[0];
         $lCProgID = $cprog->lKeyID;
         $displayData['lActivityFieldID'] = $cprog->lActivityFieldID;
         $displayData['strTableLabel'] = ($bEnrollment ? 'Enrollment ' : 'Attendance ').' table: '
                                               .htmlspecialchars($cprog->strProgramName);
         $displayData['entrySummary'] = $this->cprograms->strHTMLProgramSummaryDisplay($enumTType);
      }else {
         $cprog = null;
         $displayData['strTableLabel'] = htmlspecialchars($userTable->strUserTableName);
         $displayData['entrySummary']  = $this->clsUF->strUFTableSummaryDisplay(true);
         $displayData['lActivityFieldID'] = null;
      }

         /*-------------------------------------
            stripes
         -------------------------------------*/
      $this->load->model('util/mbuild_on_ready',    'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] .= $this->clsOnReady->strOnReady;

         //-----------------------------
         // breadcrumbs and headers
         //-----------------------------
      if ($bClientProg){
         $displayData['title']        = CS_PROGNAME.' | Client Programs';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('cprograms/cprograms/overview', 'Client Programs', 'class="breadcrumb"')
                                 .' | '.anchor('cprograms/cprog_record/view/'.$lCProgID, htmlspecialchars($cprog->strProgramName), 'class="breadcrumb"')
                                 .' | Fields';
      }else {
         $displayData['title']        = CS_PROGNAME.' | Personalization';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('admin/personalization/overview/'.$enumTType, 'Personalization', 'class="breadcrumb"')
                                 .' | Fields';
      }
      $displayData['mainTemplate'] = 'personalization/review_fields_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }

   public function moveFields($lTableID, $lFieldID, $enumMove){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;

      $lTableID = (integer)$lTableID;
      $lFieldID = (integer)$lFieldID;

      $this->load->library('util/up_down_top_bottom', '', 'upDown');

      $this->upDown->enumMove    = $enumMove;
      $this->upDown->enumRecType = 'admin fields';
      $this->upDown->lKeyID      = $lFieldID;
      $this->upDown->lTableID    = $lTableID;
      $this->upDown->moveRecs();

      $this->session->set_flashdata('msg', 'The fields were re-ordered');

      redirect('admin/uf_fields/view/'.$lTableID);
   }

   public function xfer1($lTableID, $lFieldID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;

      $displayData = array();
      $displayData['js'] = '';

      $displayData['lTableID'] = $lTableID = (int)$lTableID;
      $displayData['lFieldID'] = $lFieldID = (int)$lFieldID;

         //--------------------------------
         // Models and helpers
         //--------------------------------
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('admin/mpermissions',           'perms');
      $displayData['lTableID']   = $this->clsUF->lTableID = $lTableID;
      $this->load->helper('dl_util/web_layout');

         //-------------------------
         // validation rules
         //-------------------------
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
      $this->form_validation->set_rules('ddlTables', 'Destination Table',  'trim|required|callback_verifyDDLDestination');

         // load the current table and field
      $this->clsUF->loadTableViaTableID();
      $displayData['sourceTable'] = $utable = &$this->clsUF->userTables[0];
      $this->clsUF->enumTType = $enumTType = $utable->enumTType;
      $displayData['strUserTableName'] = $strSourceTableName = $utable->strUserTableName;
      $displayData['bMultiEntry'] = $bMultiEntry = $utable->bMultiEntry;

      $this->clsUF->loadSingleField($lFieldID);
      $displayData['ufield'] = $ufield = &$this->clsUF->fields[0];

		if ($this->form_validation->run() == FALSE){

             // load the list of available tables
             // valid transfers:
             //    single->single
             //    single->multiple
         if (!$bMultiEntry){
            $this->clsUF->sqlWhereTableExtra = " AND pft_lKeyID != $lTableID ";
            $this->clsUF->loadTableInfoGeneric(true, false, true);
            $displayData['lNumTables'] = $lNumTables = $this->clsUF->lNumTables;
            if ($lNumTables > 0){
               $strDDL = '
                  <select name="ddlTables">
                     <option value="-1">&nbsp;</option>'."\n";
               foreach ($this->clsUF->userTables as $destTable){
                  $strDDL .= '<option value="'.$destTable->lKeyID.'">'.htmlspecialchars($destTable->strUserTableName).'</option>'."\n";
               }
               $strDDL .= '</select>'."\n";
            }
            $displayData['strDDL'] =  $strDDL;
         }

         $this->load->library('generic_form');

            // first time displayed, no user data entry errors
         if (validation_errors()==''){
         }else {
            setOnFormError($displayData);
         }

            //-----------------------------
            // breadcrumbs and headers
            //-----------------------------
         $displayData['title']        = CS_PROGNAME.' | Personalization';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('admin/personalization/overview/'.$enumTType, 'Personalization', 'class="breadcrumb"')
                                 .' | '.anchor('admin/uf_fields/view/'.$lTableID, 'Fields', 'class="breadcrumb"')
                                 .' | Transfer Field';
         $displayData['mainTemplate'] = 'personalization/field_xfer1_view';
         $displayData['nav'] = $this->mnav_brain_jar->navData();
         $this->load->vars($displayData);
         $this->load->view('template');
      }else {
         $lDestTableID = (int)$_POST['ddlTables'];
         $this->xferField($lDestTableID, $utable->strDataTableName, $lTableID, $lFieldID, $ufield);
      }
   }

   function verifyDDLDestination($strVal){
      $lTabIDX = (int)$strVal;
      if ($lTabIDX <= 0){
         $this->form_validation->set_message('verifyDDLDestination', 'Please select a destination table.');
         return(false);
      }else {
         return(true);
      }
   }

   function xferField($lDestTableID, $strSourceTableName, $lTableID, $lFieldID, &$ufieldFrom){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------

      $this->load->model('personalization/muser_fields_create', 'clsUFC');
      $this->load->helper('dl_util/util_db');

      $this->clsUFC->lTableID = $lTableID;
      $this->clsUFC->loadTableViaTableID();
      $strFromTableUserName = $this->clsUFC->userTables[0]->strUserTableName;

//      $lSortIdx = $this->clsUFC->lMaxFieldSortIDX($lDestTableID) + 1;

      $this->clsUFC->lTableID = $lDestTableID;
      $this->clsUFC->loadTableViaTableID();
      $strToTableUserName = $this->clsUFC->userTables[0]->strUserTableName;

      $this->clsUFC->loadSingleField(-1);
      $ufieldNew = &$this->clsUFC->fields[0];
      $ufieldNew->enumFieldType = $ufieldFrom->enumFieldType;

      $this->clsUFC->strENPTableName = $strENPTableName = $this->clsUFC->strGenUF_TableName($lDestTableID);

      $ufieldNew->pff_strFieldNameUser = $ufieldFrom->pff_strFieldNameUser;
      $ufieldNew->pff_bCheckDef        = $ufieldFrom->pff_bCheckDef;
      $ufieldNew->pff_curDef           = $ufieldFrom->pff_curDef;
      $ufieldNew->pff_strTxtDef        = $ufieldFrom->pff_strTxtDef;
      $ufieldNew->pff_lDef             = $ufieldFrom->pff_lDef;
      $ufieldNew->pff_lCurrencyACO     = $ufieldFrom->pff_lCurrencyACO;
      $ufieldNew->pff_bHidden          = $ufieldFrom->pff_bHidden;
      $ufieldNew->pff_bRequired        = $ufieldFrom->pff_bRequired;

      $this->clsUFC->addNewField();

      $this->clsUFC->transferField($lTableID, $lDestTableID, $ufieldFrom, $ufieldNew);

      $this->clsUFC->removeFieldCore($ufieldFrom->pff_lKeyID, $ufieldFrom->enumFieldType,
                                     $strSourceTableName, $ufieldFrom->strFieldNameInternal);
      $this->session->set_flashdata('msg', 'The field "'.htmlspecialchars($ufieldNew->pff_strFieldNameUser).'" was '
                            .'moved from table <b>'.htmlspecialchars($strFromTableUserName).'</b> '
                            .'to table <b>'.htmlspecialchars($strToTableUserName).'</b> ');
      redirect('admin/uf_fields/view/'.$lTableID);
   }

	public function reorder($lTableID)	{
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
/*----------------------------
echo(__FILE__.' '.__LINE__.'<br>'."\n"); $this->output->enable_profiler(TRUE);
//----------------------------- */

      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('personalization/muser_fields_create', 'clsUFC');

      $this->clsUF->lTableID = (int)$lTableID;
      $this->clsUF->loadTableFields(false);

      $fieldOrder = array();
      $newWorldOrder = array(); $lNumNWO = 0;
      $owo = array(); $lNumOWO = 0;

      $idx = 1;
      foreach ($this->clsUF->fields as $uf){
         $fieldOrder[$idx] = new stdClass;
         $fo = &$fieldOrder[$idx];
         $fo->lFieldID = $lFieldID = $uf->pff_lKeyID;
         $fo->lOrigSIdx = $idx;

         if (isset($_POST['txtOrder_'.$lFieldID])){
            $strHold = $_POST['txtOrder_'.$lFieldID];
            if (is_numeric($strHold)){
               $fo->lNewSIdx = (int)$strHold;
            }else {
               $fo->lNewSIdx = $idx;
            }
         }else {
            $fo->lNewSIdx = $idx;
         }
         $fo->bMismo = $fo->lOrigSIdx==$fo->lNewSIdx;
         if ($fo->bMismo){
            $owo[$lNumOWO] = new stdClass;
            $owo[$lNumOWO]->fid = $lFieldID;
            $owo[$lNumOWO]->lSortIDX = $idx;
            ++$lNumOWO;
         }else {
            $newWorldOrder[$lFieldID] = $fo->lNewSIdx;
            ++$lNumNWO;
         }
         ++$idx;
      }

      asort($newWorldOrder);
      $idx = 0;
      $nwo = array();
      foreach ($newWorldOrder as $fid=>$lSortIDX){
         $nwo[$idx] = new stdClass;
         $nwo[$idx]->fid = $fid;
         $nwo[$idx]->lSortIDX = $lSortIDX;
         ++$idx;
      }

      $bEndOfOWO = $bEndOfNWO = false;
      $newSort = array(); $lNewSortIDX = 0;
      $lRunAway = 0;
      $idxOWO = $idxNWO = 0;

      while (!($bEndOfOWO && $bEndOfNWO)){
         $this->addToSortFromNWO($bEndOfOWO, $bEndOfNWO,
                      $lNumOWO, $idxOWO, $owo,
                      $lNumNWO, $idxNWO, $nwo,
                      $lNewSortIDX, $newSort);
         $this->addToSortFromOWO($bEndOfOWO, $bEndOfNWO,
                      $lNumOWO, $idxOWO, $owo,
                      $lNumNWO, $idxNWO, $nwo,
                      $lNewSortIDX, $newSort);

         ++$lRunAway;
         if ($lRunAway > 300){
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$bEndOfOWO = $bEndOfOWO <br></font>\n");
$zzzlPos = strrpos(__FILE__, '\\'); $zzzlLen=strlen(__FILE__); echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\',-(($zzzlLen-$zzzlPos)+1))) .': '.__LINE__
.":\$bEndOfNWO = $bEndOfNWO <br><br></font>\n");
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$newSort   <pre>');
echo(htmlspecialchars( print_r($newSort, true))); echo('</pre></font><br>');
/* -------------------------------------
// ------------------------------------- */

            screamForHelp($lRunAway.': info<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         }
      }

      for ($idx=0; $idx<$lNewSortIDX; ++$idx){
         $this->clsUFC->setUFieldSortIDX($newSort[$idx]->fid, $idx);
      }
      $this->session->set_flashdata('msg', 'The fields were re-ordered');
      redirect('admin/uf_fields/view/'.$lTableID);
   }

   function addToSortFromNWO(&$bEndOfOWO, &$bEndOfNWO,
                      $lNumOWO, &$idxOWO, &$owo,
                      $lNumNWO, &$idxNWO, &$nwo,
                      &$lNewSortIDX, &$newSort){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bEndOfNWO) return;
      if ($idxNWO >= $lNumNWO){
         $bEndOfNWO = true;
         return;
      }

      if ($bEndOfOWO){
         for ($idx=$idxNWO; $idx<$lNumNWO; ++$idx){
            $this->addToNewSort(true, $lNewSortIDX, $newSort, $nwo[$idx]->fid, $nwo[$idx]->lSortIDX);
         }
         $bEndOfNWO = true;
         return;
      }

         // nominal case - add nwo (and consecutive entries)
      $lCurrentOWOSIDX = $owo[$idxOWO]->lSortIDX;
      $lCurrentNWOSIDX = $nwo[$idxNWO]->lSortIDX;

      if ($lCurrentNWOSIDX <= $lCurrentOWOSIDX){
         $this->addToNewSort(true, $lNewSortIDX, $newSort, $nwo[$idxNWO]->fid, $nwo[$idxNWO]->lSortIDX);
         ++$idxNWO;

         while (($idxNWO < $lNumNWO) &&
                  ((($nwo[$idxNWO]->lSortIDX)-1)==$lCurrentNWOSIDX)){
            $this->addToNewSort(true, $lNewSortIDX, $newSort, $nwo[$idxNWO]->fid, $nwo[$idxNWO]->lSortIDX);
            ++$idxNWO;
         }
         $bEndOfNWO = $idxNWO >= $lNumNWO;
      }
   }

   function addToSortFromOWO(&$bEndOfOWO, &$bEndOfNWO,
                      $lNumOWO, &$idxOWO, &$owo,
                      $lNumNWO, &$idxNWO, &$nwo,
                      &$lNewSortIDX, &$newSort){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bEndOfOWO) return;
      if ($idxOWO >= $lNumOWO){
         $bEndOfOWO = true;
         return;
      }

      if ($bEndOfNWO){
         for ($idx=$idxOWO; $idx<$lNumOWO; ++$idx){
            $this->addToNewSort(false, $lNewSortIDX, $newSort, $owo[$idx]->fid, $owo[$idx]->lSortIDX);
         }
         $bEndOfOWO = true;
         return;
      }

         // nominal case - add owo
      $lCurrentOWOSIDX = $owo[$idxOWO]->lSortIDX;
      $lCurrentNWOSIDX = $nwo[$idxNWO]->lSortIDX;
      while (($idxOWO < $lNumOWO) && ($lCurrentOWOSIDX < $lCurrentNWOSIDX)){
         $this->addToNewSort(false, $lNewSortIDX, $newSort, $owo[$idxOWO]->fid, $owo[$idxOWO]->lSortIDX);

         ++$idxOWO;
         if ($idxOWO < $lNumOWO) $lCurrentOWOSIDX = $owo[$idxOWO]->lSortIDX;
      }
   }

   private function addToNewSort($bNWO, &$lNewSortIDX, &$newSort, $fid, $lSortIDX){
      $newSort[$lNewSortIDX] = new stdClass;
      $newSort[$lNewSortIDX]->fid = $fid;
      $newSort[$lNewSortIDX]->lSortIDX = $lSortIDX;
      ++$lNewSortIDX;
   }


}














/* End of file ufFields.php */
/* Location: ./application/controllers/admin/uf_fields.php */