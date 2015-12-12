<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class uf_ddl extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function configDDL($lTableID, $lFieldID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

      $displayData['lTableID'] = $lTableID = (integer)$lTableID;
      $displayData['lFieldID'] = $lFieldID = (integer)$lFieldID;

         //---------------------------------
         // models and helpers
         //---------------------------------
      $params = array('enumStyle' => 'enpRpt');
      $this->load->library('generic_rpt', $params);
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('admin/mpermissions',           'perms');
      $this->load->helper('clients/client_program');
      $this->load->helper('personalization/link_personalization');

      $displayData['lTableID'] = $this->clsUF->lTableID = $lTableID;
      $this->clsUF->loadTableViaTableID();
      $enumTType = $this->clsUF->userTables[0]->enumTType;
      $this->clsUF->uf_ddl_info($lFieldID);
      $this->clsUF->loadSingleField($lFieldID);

      $displayData['strUserTableName'] = $this->clsUF->userTables[0]->strUserTableName;
      $displayData['strUserFieldName'] = $this->clsUF->fields[0]->pff_strFieldNameUser;

      $this->load->library('util/up_down_top_bottom');
      
         // http://stackoverflow.com/questions/21498038/php-pointer-returns-null
      setClientProgFields($displayData, $bClientProg, $lCProgID, $cprog, $enumTType, $lTableID);
/*
      if ($bClientProg) $cprog = &$this->cprograms->cprogs[0];
      $this->myLittleTest($myLittlePointer);
      echo(gettype($myLittlePointer));
*/      
         //-----------------------------
         // ddl entry info
         //-----------------------------
      $displayData['lNumDDLEntries'] = $lNumEntries = $this->clsUF->clsDDL_Info->lNumEntries;
      if ($lNumEntries > 0){
         $displayData['clsEntries']  = $this->clsUF->clsDDL_Info->clsEntries;
         $displayData['lMinSortIDX'] = $this->clsUF->clsDDL_Info->lMinSortIDX;
         $displayData['lMaxSortIDX'] = $this->clsUF->clsDDL_Info->lMaxSortIDX;
      }

         //-----------------------------
         // breadcrumbs and headers
         //-----------------------------
      if ($bClientProg){
         $displayData['title']        = CS_PROGNAME.' | Client Programs';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('cprograms/cprograms/overview', 'Client Programs', 'class="breadcrumb"')
                                 .' | '.anchor('cprograms/cprog_record/view/'.$lCProgID, htmlspecialchars($cprog->strProgramName), 'class="breadcrumb"')
                                 .' | '.anchor('admin/uf_fields/view/'.$lTableID, 'Fields', 'class="breadcrumb"')
                                 .' | Drop-down List Entries';
      }else {
         $displayData['title']        = CS_PROGNAME.' | Personalization';
         $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('admin/personalization/overview', 'Personalization', 'class="breadcrumb"')
                                 .' | '.anchor('admin/uf_fields/view/'.$lTableID, 'Fields', 'class="breadcrumb"')
                                 .' | Drop-down List Entries';
      }
      $displayData['mainTemplate'] = 'personalization/ddl_entries_view';
      $displayData['nav'] = $this->mnav_brain_jar->navData();
      $this->load->vars($displayData);
      $this->load->view('template');
   }
   
   function addEditDDLEntry($lTableID, $lFieldID, $lDDLEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $displayData = array();

      $displayData['lTableID']    = $lTableID    = (integer)$lTableID;
      $displayData['lFieldID']    = $lFieldID    = (integer)$lFieldID;
      $displayData['lDDLEntryID'] = $lDDLEntryID = (integer)$lDDLEntryID;
      $bNew = $lDDLEntryID <= 0;

         //---------------------------------
         // models and helpers
         //---------------------------------
      $this->load->helper('clients/client_program');
      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('admin/mpermissions',           'perms');
      $params = array('enumStyle' => 'enpRpt');
      $this->load->library('generic_rpt', $params);
      
      $displayData['lTableID'] = $this->clsUF->lTableID = $lTableID;
      $this->clsUF->loadTableViaTableID();
      $enumTType = $this->clsUF->userTables[0]->enumTType;
      $this->clsUF->uf_ddl_info($lFieldID);
      $this->clsUF->loadSingleField($lFieldID);

         // validation rules
      $this->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$this->form_validation->set_rules('txtDDLEntry', 'Drop-down List Entry',
                                        'trim|callback_ufDDLAddEditUnique['.$lFieldID.','.$lDDLEntryID.']');
      setClientProgFields($displayData, $bClientProg, $lCProgID, $cprog, $enumTType, $lTableID);

		if ($this->form_validation->run() == FALSE){
         $this->load->library('generic_form');
         $displayData['strUserTableName'] = $this->clsUF->userTables[0]->strUserTableName;
         $displayData['strUserFieldName'] = $this->clsUF->fields[0]->pff_strFieldNameUser;

         if (validation_errors()==''){
            $displayData['strDDLEntry'] = $this->clsUF->strDDLValue($lDDLEntryID);
         }else {
            setOnFormError($displayData);
            $displayData['strDDLEntry'] = set_value('txtDDLEntry');
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         if ($bClientProg){
            $displayData['title']        = CS_PROGNAME.' | Client Programs';
            $displayData['pageTitle']    = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                    .' | '.anchor('cprograms/cprograms/overview', 'Client Programs', 'class="breadcrumb"')
                                    .' | '.anchor('cprograms/cprog_record/view/'.$lCProgID, htmlspecialchars($cprog->strProgramName), 'class="breadcrumb"')
                                    .' | '.anchor('admin/uf_fields/view/'.$lTableID, 'Fields', 'class="breadcrumb"')
                                    .' | '.anchor('admin/uf_ddl/configDDL/'.$lTableID.'/'.$lFieldID, 'Drop-down List Entries', 'class="breadcrumb"')
                                    .' | '.($bNew ? 'Add new ' : 'Update ').'list entry';
         }else {               
            $displayData['pageTitle'] = anchor('main/menu/admin', 'Admin', 'class="breadcrumb"')
                                 .' | '.anchor('admin/personalization/overview', 'Personalization', 'class="breadcrumb"')
                                 .' | '.anchor('admin/uf_fields/view/'.$lTableID, 'Fields', 'class="breadcrumb"')
                                 .' | '.anchor('admin/uf_ddl/configDDL/'.$lTableID.'/'.$lFieldID, 'Drop-down List Entries', 'class="breadcrumb"')
                                 .' | '.($bNew ? 'Add new ' : 'Update ').'list entry';

            $displayData['title']          = CS_PROGNAME.' | Personalization';
         }
         $displayData['nav']            = $this->mnav_brain_jar->navData();

         $displayData['bNew']           = $bNew;

         $displayData['mainTemplate']   = 'personalization/ddl_add_edit_entry_view';
         $this->load->vars($displayData);
         $this->load->view('template');

      }else {
         $strDDLEntry = trim($_POST['txtDDLEntry']);
         if ($bNew){
            $this->clsUF->setDDL_asConfigured($lFieldID);
            $lNewSortIDX = $this->clsUF->clsDDL_Info->lMaxSortIDX + 1;
            $lDDLEntryID = $this->clsUF->addUF_DDLEntry($strDDLEntry, $lFieldID, $lNewSortIDX);
            $this->session->set_flashdata('msg', 'The drop-down list entry was added');
         }else {
            $this->clsUF->updateUF_DDLEntry($strDDLEntry, $lDDLEntryID);
            $this->session->set_flashdata('msg', 'The drop-down list entry was updated');
         }
         redirect('admin/uf_ddl/configDDL/'.$lTableID.'/'.$lFieldID);
      }
   }

   function ufDDLAddEditUnique($strValue, $params){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $exParams    = explode(',', $params);
      $lFieldID    = (integer)$exParams[0];
      $lDDLEntryID = (integer)$exParams[1];

      $this->load->model('util/mverify_unique', 'clsUnique');
      return($this->clsUnique->bVerifyUniqueText(
                $strValue,    'ufddl_strDDLEntry',
                $lDDLEntryID, 'ufddl_lKeyID',
                true,         'ufddl_bRetired',
                true, $lFieldID, 'ufddl_lFieldID',
                false, null, null,
                'uf_ddl'));
   }

   function moveEntries($lTableID, $lFieldID, $lDDLEntryID, $enumMove){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $lTableID    = (integer)$lTableID;
      $lFieldID    = (integer)$lFieldID;
      $lDDLEntryID = (integer)$lDDLEntryID;

      $this->load->library('util/up_down_top_bottom', '', 'upDown');

      $this->upDown->enumMove            = $enumMove;
      $this->upDown->enumRecType         = 'ufield ddl';
      $this->upDown->strUfieldDDL        = 'uf_ddl';
      $this->upDown->strUfieldDDLKey     = 'ufddl_lKeyID';
      $this->upDown->strUfieldDDLSort    = 'ufddl_lSortIDX';
      $this->upDown->strUfieldDDLQual1   = 'ufddl_lFieldID';
      $this->upDown->strUfieldDDLRetired = 'ufddl_bRetired';
      $this->upDown->lUfieldDDLQual1Val  = $lFieldID;
      $this->upDown->lKeyID              = $lDDLEntryID;
      $this->upDown->moveRecs();

      $this->session->set_flashdata('msg', 'The entries were re-ordered');
      redirect('admin/uf_ddl/configDDL/'.$lTableID.'/'.$lFieldID);
   }

   function removeUF_DDLEntry($lTableID, $lFieldID, $lDDLEntryID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $lTableID    = (integer)$lTableID;
      $lFieldID    = (integer)$lFieldID;
      $lDDLEntryID = (integer)$lDDLEntryID;

      $this->load->model('personalization/muser_fields', 'clsUF');
      $this->load->model('admin/mpermissions',           'perms');
      $this->clsUF->remove_DDLEntry($lDDLEntryID);

      $this->session->set_flashdata('msg', 'The selected entry was removed.');
      redirect('admin/uf_ddl/configDDL/'.$lTableID.'/'.$lFieldID);
   }
   
   function sortEntries($lTableID, $lFieldID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;
      $lTableID    = (integer)$lTableID;
      $lFieldID    = (integer)$lFieldID;

      $this->load->model('personalization/muser_fields', 'clsUF');
//      $this->load->model('admin/mpermissions',           'perms');
      $this->clsUF->sort_DDLEntries($lFieldID);

      $this->session->set_flashdata('msg', 'The list was sorted.');
      redirect('admin/uf_ddl/configDDL/'.$lTableID.'/'.$lFieldID);
   }

}