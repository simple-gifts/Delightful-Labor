<?php
//---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012-2015 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
/*---------------------------------------------------------------------
   $this->load->model('donations/mhon_mem', 'clsHonMem');
  ---------------------------------------------------------------------

   __construct            ()

   loadHonMem             ($enumLoadType)
   strXlateSearchType     ($enumHMType)
   lAddHonoree            ($lPID, $bHon)
   lAddHonMem             ($lPID, $bHon)
   setMemorialMailContact ($lMC_PID, $lHMID)
   removeHMMailContact    ($lHMID)
   updateHMMailContact    ($lMC_PID, $lHMID)
   hideUnhideHMRec        ($lHMID, $bHide)

   lNumGiftsViaHMID       ($lHMID)
   retireHMRec            ($lHMID)

   strDDLHonMem           ($lMatchID, $bShowBlank, $bExcludeHidden)
   lAddHMLink             ($lGID, $lHMID)
   removeHonMemLink       ($lHMLinkID)

   lNumHonViaGID          ($lGID)
   lNumMemViaGID          ($lGID)

   allHonMemPIDs          ($bHon, $bMem, $bMC)

---------------------------------------------------------------------*/

class mhon_mem extends CI_Model{

   public
      $lNumHonMem, $honMemTable, $lHMID, $lGID;

   function __construct(){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
		parent::__construct();

      $this->lNumHonMem = $this->honMemTable =
      $this->lHMID      = $this->lGID        = null;
   }

   public function loadHonMem($enumLoadType){
   //-----------------------------------------------------------------
   // $enumLoadType:
   //    via HMID
   //    via GiftID
   //    all
   //    all - Hon Only
   //    all - Mem Only
   //-----------------------------------------------------------------
      $sqlSelect = $strInner = '';
      $bViaGiftID = false;
      switch ($enumLoadType){
         case 'all':
            $strWhere = '';
            break;
         case 'all - Hon Only':
            $strWhere = ' AND ghm_bHon';
            break;
         case 'all - Mem Only':
            $strWhere = ' AND NOT ghm_bHon';
            break;
         case 'via GiftID':
            $bViaGiftID = true;
            $strWhere   = " AND ghml_lGiftID=$this->lGID ";
            $strInner   = ' INNER JOIN gifts_hon_mem_links ON ghml_lHonMemID=ghm_lKeyID ';
//            $sqlSelect  = ', ghml_lKeyID, ghml_bAck, UNIX_TIMESTAMP(ghml_dteAck) AS dteAck, ghml_lAckByID ';
            $sqlSelect  = ', ghml_lKeyID, ghml_bAck, ghml_dteAck, ghml_lAckByID ';
            break;
         case 'via HMID':
            $strWhere = " AND ghm_lKeyID=$this->lHMID ";
            break;
         default:
            screamForHelp($enumLoadType.': not implemented yet<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      $sqlStr =
        "SELECT
            ghm_lKeyID, ghm_lFID, ghm_lMailContactID, ghm_bHon,
            ghm_bHidden, ghm_bRetired,
            ghm_lOriginID, ghm_lLastUpdateID,
            UNIX_TIMESTAMP(ghm_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(ghm_dteLastUpdate) AS dteLastUpdate,

            tblHM.pe_strFName   AS strHM_FName,   tblHM.pe_strLName AS strHM_LName,
            tblHM.pe_strAddr1   AS strHM_Addr1,   tblHM.pe_strAddr2 AS strHM_Addr2,
            tblHM.pe_strCity    AS strHM_City,    tblHM.pe_strState AS strHM_State,
            tblHM.pe_strCountry AS strHM_Country, tblHM.pe_strZip   AS strHM_Zip,
            tblHM.pe_strPhone   AS strHM_Phone,   tblHM.pe_strCell  AS strHM_Cell,

            tblMC.pe_strFName   AS strMC_FName,   tblMC.pe_strLName AS strMC_LName,
            tblMC.pe_strAddr1   AS strMC_Addr1,   tblMC.pe_strAddr2 AS strMC_Addr2,
            tblMC.pe_strCity    AS strMC_City,    tblMC.pe_strState AS strMC_State,
            tblMC.pe_strCountry AS strMC_Country, tblMC.pe_strZip   AS strMC_Zip,
            tblMC.pe_strPhone   AS strMC_Phone,   tblMC.pe_strCell  AS strMC_Cell
            $sqlSelect

         FROM lists_hon_mem
            INNER JOIN people_names AS tblHM ON tblHM.pe_lKeyID=ghm_lFID
            $strInner
            LEFT  JOIN people_names AS tblMC ON tblMC.pe_lKeyID=ghm_lMailContactID

         WHERE
            1
            $strWhere
            AND NOT ghm_bRetired

         ORDER BY NOT ghm_bHon, tblHM.pe_strLName, tblHM.pe_strFName, ghm_lFID, ghm_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumHonMem = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->honMemTable[0] = new stdClass;
         $this->honMemTable[0]->ghm_lKeyID         =
         $this->honMemTable[0]->ghm_lFID           =
         $this->honMemTable[0]->ghm_lMailContactID =
         $this->honMemTable[0]->ghm_bHon           =
         $this->honMemTable[0]->ghm_bHidden        =
         $this->honMemTable[0]->ghm_strFName       =
         $this->honMemTable[0]->ghm_strLName       =
         $this->honMemTable[0]->ghm_strSafeName    =

         $this->honMemTable[0]->ghm_lOriginID      =
         $this->honMemTable[0]->ghm_lLastUpdateID  =
         $this->honMemTable[0]->dteOrigin          =
         $this->honMemTable[0]->dteLastUpdate      =

         $this->honMemTable[0]->honorMem           =
         $this->honMemTable[0]->mailContact        = null;

         if ($bViaGiftID){
            $this->honMemTable[0]->lHMLinkID       =
            $this->honMemTable[0]->bAck            =
            $this->honMemTable[0]->dteAck          =
            $this->honMemTable[0]->lAckByID        = null;
         }
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->honMemTable[$idx] = new stdClass;
            $this->honMemTable[$idx]->ghm_lKeyID         = $row->ghm_lKeyID;
            $this->honMemTable[$idx]->ghm_lFID           = $row->ghm_lFID;
            $this->honMemTable[$idx]->ghm_lMailContactID = $row->ghm_lMailContactID;
            $this->honMemTable[$idx]->ghm_bHon           = $row->ghm_bHon;
            $this->honMemTable[$idx]->ghm_bHidden        = $row->ghm_bHidden;
            $this->honMemTable[$idx]->ghm_strFName       = $row->strHM_FName;
            $this->honMemTable[$idx]->ghm_strLName       = $row->strHM_LName;
            $this->honMemTable[$idx]->ghm_strSafeName    = htmlspecialchars($row->strHM_FName.' '.$row->strHM_LName);
            $this->honMemTable[$idx]->ghm_lOriginID      = $row->ghm_lOriginID;
            $this->honMemTable[$idx]->ghm_lLastUpdateID  = $row->ghm_lLastUpdateID;
            $this->honMemTable[$idx]->dteOrigin          = $row->dteOrigin;
            $this->honMemTable[$idx]->dteLastUpdate      = $row->dteLastUpdate;

            $this->honMemTable[$idx]->honorMem = new stdClass;
            $this->loadHonoree($this->honMemTable[$idx]->honorMem, $row, 'strHM');

            $this->honMemTable[$idx]->mailContact = new stdClass;
            $this->loadHonoree($this->honMemTable[$idx]->mailContact, $row, 'strMC');

            if ($bViaGiftID){
               $this->honMemTable[$idx]->lHMLinkID       = $row->ghml_lKeyID;
               $this->honMemTable[$idx]->bAck            = $row->ghml_bAck;
               $this->honMemTable[$idx]->dteAck          = dteMySQLDate2Unix($row->ghml_dteAck);
               $this->honMemTable[$idx]->lAckByID        = $row->ghml_lAckByID;
            }
            ++$idx;
         }
      }
   }

   private function loadHonoree(&$clsHMCon, &$row, $strPrefix){
   //-----------------------------------------------------------------
   // honoree / mail contact info
   //-----------------------------------------------------------------
      $strMember = $strPrefix.'_FName';   $clsHMCon->strFName   = $row->$strMember;    //($strPrefix.'_FName');
      $strMember = $strPrefix.'_LName';   $clsHMCon->strLName   = $row->$strMember;    //[$strPrefix.'_LName'];
      $strMember = $strPrefix.'_Addr1';   $clsHMCon->strAddr1   = $row->$strMember;    //[$strPrefix.'_Addr1'];
      $strMember = $strPrefix.'_Addr2';   $clsHMCon->strAddr2   = $row->$strMember;    //[$strPrefix.'_Addr2'];
      $strMember = $strPrefix.'_City';    $clsHMCon->strCity    = $row->$strMember;    //[$strPrefix.'_City'];
      $strMember = $strPrefix.'_State';   $clsHMCon->strState   = $row->$strMember;    //[$strPrefix.'_State'];
      $strMember = $strPrefix.'_Country'; $clsHMCon->strCountry = $row->$strMember;    //[$strPrefix.'_Country'];
      $strMember = $strPrefix.'_Zip';     $clsHMCon->strZip     = $row->$strMember;    //[$strPrefix.'_Zip'];
      $strMember = $strPrefix.'_Phone';   $clsHMCon->strPhone   = $row->$strMember;    //[$strPrefix.'_Phone'];
      
      $clsHMCon->strSafeNameLF = htmlspecialchars($clsHMCon->strLName.', '.$clsHMCon->strFName);
      $clsHMCon->strSafeNameFL = htmlspecialchars($clsHMCon->strFName.' ' .$clsHMCon->strLName);
      $clsHMCon->strAddress    =
                     strBuildAddress(
                              $clsHMCon->strAddr1, $clsHMCon->strAddr2,   $clsHMCon->strCity,
                              $clsHMCon->strState, $clsHMCon->strCountry, $clsHMCon->strZip,
                              true);
   }

   public function strXlateSearchType($enumHMType){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      switch ($enumHMType){
         case 'hon': return('Honorarium');                break;
         case 'mem': return('Memorial');                  break;
         case 'mc' : return('Mail contact for Memorial'); break;
         default:
            return('#error#');
            break;
      }
   }

   public function lAddHonoree($lPID){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      return($this->lAddHonMem($lPID, true));
   }

   public function lAddMemorial($lPID){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      return($this->lAddHonMem($lPID, false));
   }

   private function lAddHonMem($lPID, $bHon){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        "INSERT INTO lists_hon_mem
         SET
            ghm_lFID           = $lPID,
            ghm_lOriginID      = $glUserID,
            ghm_lLastUpdateID  = $glUserID,
            ghm_lMailContactID = NULL,
            ghm_bHon           = ".($bHon ? '1' : '0').',
            ghm_bHidden        = 0,
            ghm_bRetired       = 0,
            ghm_dteOrigin      = NOW(),
            ghm_dteLastUpdate  = NOW();';
      $query = $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   public function setMemorialMailContact($lMC_PID, $lHMID){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      $this->updateHMMailContact($lMC_PID, $lHMID);
   }

   public function removeHMMailContact($lHMID){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      $this->updateHMMailContact(null, $lHMID);
   }

   private function updateHMMailContact($lMC_PID, $lHMID){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        "UPDATE lists_hon_mem
         SET
            ghm_lLastUpdateID  = $glUserID,
            ghm_lMailContactID = ".strDBValueConvert_INT($lMC_PID)."
          WHERE ghm_lKeyID=$lHMID;";
      $query = $this->db->query($sqlStr);
   }

   public function lNumGiftsViaHMID($lHMID){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM gifts_hon_mem_links
            INNER JOIN gifts ON ghml_lGiftID=gi_lKeyID
         WHERE ghml_lHonMemID=$lHMID
            AND NOT gi_bRetired;";

      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         return((integer)$row->lNumRecs);
      }
   }

   public function hideUnhideHMRec($lHMID, $bHide){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        'UPDATE lists_hon_mem
         SET
            ghm_bHidden='.($bHide ? '1' : '0').",
            ghm_lLastUpdateID=$glUserID
         WHERE ghm_lKeyID=$lHMID;";
      $query = $this->db->query($sqlStr);
   }

   public function retireHMRec($lHMID){
   //-----------------------------------------------------------------
   //
   //-----------------------------------------------------------------
      global $glUserID;

      $sqlStr =
        "UPDATE lists_hon_mem
         SET
            ghm_bRetired=1,
            ghm_lLastUpdateID=$glUserID
         WHERE ghm_lKeyID=$lHMID;";
      $query = $this->db->query($sqlStr);
   }

   public function strDDLHonMem($lMatchID, $bShowBlank, $bExcludeHidden){
   //-----------------------------------------------------------------------
   // caller must first call $this->loadHonMem($enumLoadType)
   //-----------------------------------------------------------------------
      $strDDL = '';
      if ($this->lNumHonMem > 0){
         if ($bShowBlank){
            $strDDL .= '<option value="-1">&nbsp;</option>'."\n";
         }

         foreach ($this->honMemTable as $clsHM){
            if (!$bExcludeHidden || !$clsHM->ghm_bHidden){
               $lKeyID = $clsHM->ghm_lKeyID;
               $strMatch = $lMatchID==$lKeyID ? ' SELECTED ' : '';
               $strDDL .= '<option value="'.$lKeyID.'" '.$strMatch.'>'
                            .$clsHM->honorMem->strSafeNameLF.'</option>'."\n";
            }
         }
      }
      return($strDDL);
   }

   function lAddHMLink($lGID, $lHMID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "INSERT INTO gifts_hon_mem_links
         SET
            ghml_lGiftID   = $lGID,
            ghml_lHonMemID = $lHMID,
            ghml_strNote   = '',
            ghml_bAck      = 0,
            ghml_dteAck    = NULL,
            ghml_lAckByID  = NULL
         ON DUPLICATE KEY UPDATE ghml_lGiftID=ghml_lGiftID;";
      $this->db->query($sqlStr);

         //----------------------------------------------
         // for a duplicate key pair, affected rows==2
         //----------------------------------------------
      if ($this->db->affected_rows() == 1){
         return($this->db->insert_id());
      }else {
         return(null);
      }
   }

   public function removeHonMemLink($lHMLinkID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
        "DELETE FROM gifts_hon_mem_links
         WHERE ghml_lKeyID=$lHMLinkID;";
      $this->db->query($sqlStr);
   }

   public function lNumHonViaGID($lGID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      return($this->lNumHonMemViaGID($lGID, true));
   }

   public function lNumMemViaGID($lGID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      return($this->lNumHonMemViaGID($lGID, false));
   }

   private function lNumHonMemViaGID($lGID, $bHon){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $sqlStr =
         "SELECT COUNT(*) AS lNumRecs
          FROM gifts_hon_mem_links
             INNER JOIN lists_hon_mem ON ghml_lHonMemID=ghm_lKeyID
          WHERE ghml_lGiftID=$lGID AND ".($bHon ? '' : ' NOT ').' ghm_bHon;';
          
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
          
      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         return((integer)$row->lNumRecs);
      }
   }

   public function allHonMemPIDs($bHon, $bMem, $bMC){
      if ($bHon){
         $strWhere = ' AND ghm_bHon ';
      }elseif ($bMem){
         $strWhere = ' AND NOT ghm_bHon ';
      }elseif ($bMC){
         $strWhere = ' AND ghm_lMailContactID IS NOT NULL ';
      }

      $lPIDs = array();
      $sqlStr =
              "SELECT ghm_lFID
               FROM lists_hon_mem
               WHERE
               NOT ghm_bRetired
               $strWhere
               ORDER BY ghm_lFID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() > 0){
         foreach ($query->result() as $row){
            $lPIDs[] = $row->ghm_lFID;
         }
      }
      return($lPIDs);
   }

}

?>