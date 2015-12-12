<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2011 by Database Austin
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
/---------------------------------------------------------------------
      $this->load->model('clients/mclient_vocabulary', 'clsClientV');
---------------------------------------------------------------------*/

class mclient_vocabulary extends CI_Model{

   public
      $lVocID,

      $lNumVocs, $vocs;

   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
 		parent::__construct();
      $this->lVocID = null;
      $this->initVoc();
   }

   public function initVoc(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->lNumVocs = 0;

      $this->vocs[0] = new stdClass;

      $this->vocs[0]->lKeyID        =
      $this->vocs[0]->strVocTitle   =

      $this->vocs[0]->strLocS       =
      $this->vocs[0]->strLocP       =

      $this->vocs[0]->strClientS    =
      $this->vocs[0]->strClientP    =

      $this->vocs[0]->strSponsorS   =
      $this->vocs[0]->strSponsorP   =

      $this->vocs[0]->strSubLocS    =
      $this->vocs[0]->strSubLocP    =

      $this->vocs[0]->bProtected    =
      $this->vocs[0]->bRetired      = null;
   }

   public function loadClientVocabulary($bLoadViaVocID, $bSortByProtected){
   //---------------------------------------------------------------------
   // return all personalized vocabulary
   //---------------------------------------------------------------------
      if ($bLoadViaVocID){
         if (is_null($this->lVocID)) screamForHelp('UNINITIALIZED CLASS<br></b>error on <b>line: </b>'.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
      }

      $sqlStr =
         'SELECT
             cv_lKeyID, cv_strVocTitle,
             cv_strVocClientS,  cv_strVocClientP, cv_strVocSponsorS,
             cv_strVocSponsorP, cv_strVocLocS,    cv_strVocLocP,
             cv_strVocSubLocS,  cv_strVocSubLocP, cv_bProtected, cv_bRetired
          FROM lists_client_vocab
          WHERE 1 '.($bLoadViaVocID ? " AND cv_lKeyID=$this->lVocID " : '').'
          ORDER BY '.($bSortByProtected ? ' cv_bProtected DESC, ' : '').' cv_strVocTitle, cv_lKeyID;';
          
      $query = $this->db->query($sqlStr);
      $this->lNumVocs = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->initVoc();
      }else {
         $idx = 0;
         $this->vocs = array();
         foreach ($query->result() as $row){
            $this->vocs[$idx] = new stdClass;

            $this->vocs[$idx]->lKeyID        = (int)$row->cv_lKeyID;
            $this->vocs[$idx]->strVocTitle   = $row->cv_strVocTitle;

            $this->vocs[$idx]->strLocS       = $row->cv_strVocLocS;
            $this->vocs[$idx]->strLocP       = $row->cv_strVocLocP;

            $this->vocs[$idx]->strClientS    = $row->cv_strVocClientS;
            $this->vocs[$idx]->strClientP    = $row->cv_strVocClientP;

            $this->vocs[$idx]->strSponsorS   = $row->cv_strVocSponsorS;
            $this->vocs[$idx]->strSponsorP   = $row->cv_strVocSponsorP;

            $this->vocs[$idx]->strSubLocS    = $row->cv_strVocSubLocS;
            $this->vocs[$idx]->strSubLocP    = $row->cv_strVocSubLocP;

            $this->vocs[$idx]->bProtected    = (boolean)$row->cv_bProtected;
            $this->vocs[$idx]->bRetired      = (boolean)$row->cv_bRetired;
            
            ++$idx;
         }
      }
   }

   public function addNewClientVoc(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'INSERT INTO lists_client_vocab
          SET '.$this->strSQLCommon().",
             cv_bRetired = 0,
             cv_lOriginID = $glUserID,
             cv_dteOrigin = NOW();";
      $this->db->query($sqlStr);
      $this->vocs[0]->lKeyID = $this->db->insert_id();
   }

   public function updateClientVoc(){
   //---------------------------------------------------------------------
   // the admin list entry, not the client association
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         'UPDATE lists_client_vocab
          SET '.$this->strSQLCommon().',
             cv_bRetired = '.($this->vocs[0]->bRetired ? '1' : '0').",
             cv_lOriginID = $glUserID,
             cv_dteOrigin = NOW()
          WHERE cv_lKeyID = ".$this->vocs[0]->lKeyID.';';

      $this->db->query($sqlStr);
   }
   
   function retireVoc($lVocID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      
      $this->db->query(
              "UPDATE lists_client_vocab 
               SET 
                  cv_bRetired=1,
                  cv_lLastUpdateID=$glUserID
               WHERE cv_lKeyID=$lVocID;");
   }

   private function strSQLCommon(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      return('
            cv_strVocTitle         = '.strPrepStr($this->vocs[0]->strVocTitle)   .',
            cv_strVocClientS       = '.strPrepStr($this->vocs[0]->strClientS) .',
            cv_strVocClientP       = '.strPrepStr($this->vocs[0]->strClientP) .',
            cv_strVocSponsorS      = '.strPrepStr($this->vocs[0]->strSponsorS).',
            cv_strVocSponsorP      = '.strPrepStr($this->vocs[0]->strSponsorP).',
            cv_strVocLocS          = '.strPrepStr($this->vocs[0]->strLocS)    .',
            cv_strVocLocP          = '.strPrepStr($this->vocs[0]->strLocP)    .',
            cv_strVocSubLocS       = '.strPrepStr($this->vocs[0]->strSubLocS) .',
            cv_strVocSubLocP       = '.strPrepStr($this->vocs[0]->strSubLocP) .',
            cv_bProtected          = '.($this->vocs[0]->bProtected ? '1' : '0').",
            cv_lLastUpdateID       = $glUserID,
            cv_dteLastUpdate       = NOW() ");
   }

   public function strClientVocDDL($bAddBlank, $lMatchID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadClientVocabulary(false, false);
      $strOut = '';

      if ($bAddBlank){
         $strOut .= '<option value="-1">&nbsp;</option>'."\n";
      }

      foreach ($this->vocs as $clsVoc){
         $lKeyID = $clsVoc->lKeyID;
         $bMatch = $lMatchID==$lKeyID;
         if (!$clsVoc->bRetired || $bMatch){
            $strOut .= '<option value="'.$lKeyID.'" '.($bMatch ? 'SELECTED' : '').'>'
               .htmlspecialchars($clsVoc->strVocTitle).'</option>'."\n";
         }
      }
      return($strOut);
   }

   public function updateClientVocAssociation($lCID, $lVocID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $sqlStr =
         "UPDATE client_records
          SET
             cr_lVocID=$lVocID,
             cr_lLastUpdateID=$glUserID
          WHERE cr_lKeyID=$lCID;";
      $this->db->query($sqlStr);
   }

}


?>