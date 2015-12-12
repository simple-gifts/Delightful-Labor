<?php
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2012-2013 by Database Austin
 Austin, Texas

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.

/*---------------------------------------------------------------------
      $this->load->model('util/msearch_log', 'cLogSearch');
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class msearch_log extends CI_Model{

   public $strHighlightPhrase, $strSearchText, $strSearchTypeLabel,
          $strWordListTable,
          $bTypeExact, $bTypeAll, $bTypeAny;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->strHighlightPhrase = $this->strSearchText    =
      $this->strSearchTypeLabel = $this->strWordListTable =
      $this->bTypeExact = $this->bTypeAll = $this->bTypeAny = null;
   }

   public function initSearch($strSearchText, $bTypeExact, $bTypeAll, $bTypeAny){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->strSearchText = $strSearchText;
      $this->bTypeExact    = $bTypeExact;
      $this->bTypeAll      = $bTypeAll;
      $this->bTypeAny      = $bTypeAny;

      if ($bTypeExact){
         $this->strSearchTypeLabel = 'Exact Phrase';
      }elseif ($bTypeAll){
         $this->strSearchTypeLabel = 'All Words';
      }elseif ($bTypeAny){
         $this->strSearchTypeLabel = 'Any Word';
      }

      $this->strHighlightPhrase = array();
      if ($bTypeExact) {
         $this->strHighlightPhrase[0] = $strSearchText;
      }else {
         $idx = 0;
         $this->strWordListTable = explode(' ', $strSearchText);
         foreach ($this->strWordListTable as $strTestWord) {
            $strTestWord = trim($strTestWord);
            if ($strTestWord!=''){
               $this->strHighlightPhrase[$idx] = $strTestWord;
               ++$idx;
            }
         }
      }
   }

   public function runSearch($searchTarget){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $results = new stdClass;
      switch ($searchTarget){
         case 'clientStatus':
            $this->clientStatusSearch($results);
            break;

         case 'clientBio':
            $this->clientBioSearch($results);
            break;

         case 'docsImages':
            $this->docImageSearch($results);
            break;

         case 'giftNotes':
            $this->giftNotesSearch($results);
            break;

            // personalized tables
         case CENUM_CONTEXT_BIZ:
         case CENUM_CONTEXT_CLIENT:
         case CENUM_CONTEXT_LOCATION:
         case CENUM_CONTEXT_GIFT:
         case CENUM_CONTEXT_PEOPLE:
         case CENUM_CONTEXT_SPONSORSHIP:
         case CENUM_CONTEXT_VOLUNTEER:
            $this->personalizedLogSearch($results, $searchTarget);
            break;

         default:
            screamForHelp($searchTarget.': Unknow search target<br>error on line <b> -- '.__LINE__.' -- </b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($results);
   }

   private function personalizedLogSearch(&$results, $enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $cPer = new muser_fields;
      $results->strLabel    = 'Personalized Table / '.strXlateContext($enumContext, true, false);
      $results->lNumResults = 0;

      $sqlStr = '
            SELECT
               uflog_lKeyID, uflog_lFieldID, uflog_lForeignID,
               uflog_strLogTitle, uflog_strLogEntry,
               uflog_dteOrigin,
               pff_lKeyID, pff_lTableID, pff_lSortIDX, pff_strFieldNameInternal, pff_strFieldNameUser,
               pft_lKeyID, pft_strUserTableName, pft_strDataTableName, pft_enumAttachType

            FROM uf_logs
               INNER JOIN uf_fields ON uflog_lFieldID=pff_lKeyID
               INNER JOIN uf_tables ON pff_lTableID=pft_lKeyID

            WHERE NOT pft_bRetired
               AND pft_enumAttachType='.strPrepStr($enumContext).' AND ('
               .$this->textSearchWhere('uflog_strLogTitle').' OR '
               .$this->textSearchWhere('uflog_strLogEntry').')
            ORDER BY pft_strUserTableName, uflog_dteOrigin, uflog_lKeyID;';

      $query = $this->db->query($sqlStr);
      $results->lNumResults = $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         $idx = 0;
         $results->matches = array();
         $bModelLoaded = array();
         foreach ($query->result() as $row){
            $enumContext   = $enumContext;
            $lForeignID    = $row->uflog_lForeignID;
            if (!isset($bModelLoaded[$enumContext])){
               $bModelLoaded[$enumContext] = true;
               loadSupportModels($enumContext, $lForeignID);
            }


            $results->matches[$idx] = new stdClass;
            $match = &$results->matches[$idx];
            contextNameLink($enumContext, $lForeignID,
                            $strContextName, $strContextLink);
            $match->links = $strContextLink.' '.htmlspecialchars($strContextName);
            $strLabelStyle =  ' text-align: left; padding: 0px; ';
            $strEntryStyle =  ' padding: 0px 0px 0px 4px; ';
            $match->searchInfo = '
                 <table class="enpRptView" style="padding: 0px; margin: 0px;">
                    <tr>
                       <td class="enpViewLabel" style="'.$strLabelStyle.'">
                          Table:
                       </td>
                       <td class="enpView" style="'.$strEntryStyle.'">'
                          .htmlspecialchars($row->pft_strUserTableName).'
                       </td>
                    </tr>
                    <tr>
                       <td class="enpViewLabel" style="'.$strLabelStyle.'">
                          Field:
                       </td>
                       <td class="enpView" style="'.$strEntryStyle.'">'
                          .htmlspecialchars($row->pff_strFieldNameUser).'
                       </td>
                    </tr>
                    <tr>
                       <td class="enpViewLabel" style="'.$strLabelStyle.'">
                          Entry date:
                       </td>
                       <td class="enpView" style="'.$strEntryStyle.'">'
                          .date($genumDateFormat, dteMySQLDate2Unix($row->uflog_dteOrigin)).'
                       </td>
                  </table>';
            $match->text = $row->uflog_strLogTitle."\n\n".$row->uflog_strLogEntry;
            $match->textHighlighted =
                            '<u>'.$this->highlightMatchedText($row->uflog_strLogTitle).'</u><br>'
                           .nl2br($this->highlightMatchedText($row->uflog_strLogEntry));

            ++$idx;
         }
      }
   }


   private function giftNotesSearch(&$results){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $results->strLabel    = 'Gift notes';
      $results->lNumResults = 0;
      $cACO = new madmin_aco;

      $sqlStr =
           'SELECT
               gi_lKeyID, gi_lForeignID, gi_curAmnt, gi_lACOID,
               gi_dteDonation,
               gi_strNotes,
               pe_strLName, pe_strFName, pe_bBiz, gi_lSponsorID
            FROM gifts
               INNER JOIN people_names ON pe_lKeyID=gi_lForeignID
            WHERE NOT gi_bRetired
               AND NOT pe_bRetired AND '
               .$this->textSearchWhere('gi_strNotes').'
            ORDER BY pe_strLName, pe_strFName, gi_lKeyID;';


      $query = $this->db->query($sqlStr);
      $results->lNumResults = $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         $idx = 0;
         $results->matches = array();
         foreach ($query->result() as $row){
            $results->matches[$idx] = new stdClass;
            $match    = &$results->matches[$idx];
            $lGiftID  = $row->gi_lKeyID;
            $lFID     = $row->gi_lForeignID;
            $strFID   = str_pad($lFID, 5, '0', STR_PAD_LEFT);
            $bBiz     = $row->pe_bBiz;
            $lACOID   = $row->gi_lACOID;
            $bSponPay = !is_null($row->gi_lSponsorID);

            $cACO->loadCountries(false, false, true, $lACOID);
            $giftACO = &$cACO->countries[0];

            if ($bSponPay){
               $match->links = 'Sponsor Payment ID '
                           .str_pad($lGiftID, 5, '0', STR_PAD_LEFT)
                           .strLinkView_SponsorPayment($lGiftID, 'View payment record', true).'&nbsp;&nbsp;&nbsp;';
            }else {
               $match->links = 'Gift ID '
                           .str_pad($lGiftID, 5, '0', STR_PAD_LEFT)
                           .strLinkView_GiftsRecord($lGiftID, 'View gift record', true).'&nbsp;&nbsp;&nbsp;';
            }
            $match->searchInfo = 'Donation record of '.$giftACO->strCurrencySymbol.' '
                                 .number_format($row->gi_curAmnt, 2).' '.$giftACO->strFlagImg
                                 .' of '.date($genumDateFormat, dteMySQLDate2Unix($row->gi_dteDonation)).' by ';
            if ($bBiz){
               $match->links .= 'Business ID: '.$strFID.strLinkView_BizRecord($lFID, 'View business record', true);
               $match->searchInfo .= htmlspecialchars($row->pe_strLName);
            }else {
               $match->links .= 'People ID: '.$strFID.strLinkView_PeopleRecord($lFID, 'View people record', true);
               $match->searchInfo .= htmlspecialchars($row->pe_strFName.' '.$row->pe_strLName);
            }
            $match->text = $row->gi_strNotes;
            $match->textHighlighted = $this->highlightMatchedText($row->gi_strNotes);
            ++$idx;
         }
      }
   }

   private function docImageSearch(&$results){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $results->strLabel    = 'Image and Document Descriptions';
      $results->lNumResults = 0;

      $sqlStr =
           'SELECT
               di_lKeyID, di_enumEntryType, di_enumContextType,
               di_lForeignID, di_strCaptionTitle, di_strDescription,
               di_dteDocImage, di_strUserFN
            FROM docs_images
            WHERE NOT di_bRetired AND '
              .$this->textSearchWhere('di_strDescription').'
            ORDER BY di_enumContextType, di_lForeignID, di_lKeyID;';

      $query = $this->db->query($sqlStr);
      $results->lNumResults = $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         $idx = 0;
         $results->matches = array();
         $bModelLoaded = array();
         foreach ($query->result() as $row){
            $enumContext   = $row->di_enumContextType;
            $enumEntryType = $row->di_enumEntryType;
            $lForeignID    = $row->di_lForeignID;
            if (!isset($bModelLoaded[$enumContext])){
               $bModelLoaded[$enumContext] = true;
               loadSupportModels($enumContext, $lForeignID);
            }

            $results->matches[$idx] = new stdClass;
            $match = &$results->matches[$idx];
            $match->searchInfo = strLabelViaContextType($enumContext, true, false).' / '.$enumEntryType;
            contextNameLink($enumContext, $lForeignID,
                            $strContextName, $strContextLink);
            $match->links = $strContextLink.' '.htmlspecialchars($strContextName);
            $match->text = $row->di_strDescription;
            $match->textHighlighted = $this->highlightMatchedText($row->di_strDescription);
            ++$idx;
         }
      }
   }

   private function clientBioSearch(&$results){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $results->strLabel    = 'Client Bio';
      $results->lNumResults = 0;

      $sqlStr =
           'SELECT cr_lKeyID,
               cr_strFName, cr_strLName, cr_lLocationID, cl_strLocation,
               cr_strBio
            FROM client_records
               INNER JOIN client_location ON cr_lLocationID = cl_lKeyID
            WHERE NOT cr_bRetired AND '
               .$this->textSearchWhere('cr_strBio').'
            ORDER BY cr_strLName, cr_strFName, cr_lKeyID;';

      $query = $this->db->query($sqlStr);
      $results->lNumResults = $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         $idx = 0;
         $results->matches = array();
         foreach ($query->result() as $row){
            $results->matches[$idx] = new stdClass;
            $match = &$results->matches[$idx];
            $lClientID = $row->cr_lKeyID;
            $match->links = 'Client ID '
                           .str_pad($lClientID, 5, '0', STR_PAD_LEFT)
                           .strLinkView_ClientRecord($lClientID, 'View client record', true);
            $match->searchInfo = 'Client record for '
                           .htmlspecialchars($row->cr_strFName.' '.$row->cr_strLName).'
                            ('.htmlspecialchars($row->cl_strLocation).')';
            $match->text = $row->cr_strBio;
            $match->textHighlighted = $this->highlightMatchedText($row->cr_strBio);
            ++$idx;
         }
      }
   }

   private function clientStatusSearch(&$results){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;
      $results->strLabel    = 'Client Status';
      $results->lNumResults = 0;

      $sqlStr =
           'SELECT csh_lKeyID, csh_lClientID, csh_lStatusID,
               csh_dteStatusDate,
               csh_strStatusTxt,
               cr_strFName, cr_strLName, cr_lLocationID, cl_strLocation
            FROM client_status
               INNER JOIN client_records  ON cr_lKeyID      = csh_lClientID
               INNER JOIN client_location ON cr_lLocationID = cl_lKeyID
            WHERE NOT csh_bRetired AND '
               .$this->textSearchWhere('csh_strStatusTxt').'
            ORDER BY csh_dteStatusDate, csh_lKeyID;';

      $query = $this->db->query($sqlStr);
      $results->lNumResults = $lNumRows = $query->num_rows();
      if ($lNumRows > 0){
         $idx = 0;
         $results->matches = array();
         foreach ($query->result() as $row){
            $results->matches[$idx] = new stdClass;
            $lClientID = $row->csh_lClientID;
            $match = &$results->matches[$idx];
            $match->links = 'Client ID '
                           .str_pad($lClientID, 5, '0', STR_PAD_LEFT)
                           .strLinkView_ClientRecord($lClientID, 'View client record', true).'&nbsp;&nbsp;'
                           .'Client Status ID: '
                           .str_pad($row->csh_lKeyID, 5, '0', STR_PAD_LEFT)
                           .strLinkView_ClientStatusHistory($lClientID, 'View client status', true);
            $match->searchInfo = 'Client status record for '
                           .htmlspecialchars($row->cr_strFName.' '.$row->cr_strLName).'
                            ('.htmlspecialchars($row->cl_strLocation).') of '
                           .date($genumDateFormat, dteMySQLDate2Unix($row->csh_dteStatusDate));
            $match->text = $row->csh_strStatusTxt;
            $match->textHighlighted = $this->highlightMatchedText($row->csh_strStatusTxt);
            ++$idx;
         }
      }
   }

   private function highlightMatchedText($strText){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
//      return(strHighlightPhrases(htmlspecialchars($strText), $this->strHighlightPhrase, '<b>', '</b>'));

      return(safeHighlight($this->strHighlightPhrase,
                    '<b><font color="#63007c">', '</font></b>', htmlspecialchars($strText)));
   }

   private function textSearchWhere($strSearchFN){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlWhere = '';
         //------------------------------
         // exact phrase
         //------------------------------
      if ($this->bTypeExact) {
         $sqlWhere = ' ( INSTR('.$strSearchFN.', '.strPrepStr($this->strSearchText).')>0) '."\n";

         //------------------------------
         // all words must match
         //------------------------------
      }elseif ($this->bTypeAll){
         $strWordList = '';
         foreach ($this->strWordListTable as $strTestWord) {
            $strTestWord = trim($strTestWord);
            if ($strTestWord!='') $strWordList .= '+'.$strTestWord.' ';
         }
         $sqlWhere = ' (MATCH ('.$strSearchFN.') AGAINST ('.strPrepStr($strWordList)
                               .' IN BOOLEAN MODE) )'."\n";

         //----------------------------------------------------------------------
         // any word can match (note - mysql discards short and common words)
         //----------------------------------------------------------------------
      }elseif ($this->bTypeAny){
         $sqlWhere = ' (MATCH ('.$strSearchFN.') AGAINST ('.strPrepStr($this->strSearchText)
                               .' IN BOOLEAN MODE) )'."\n";
      }
      return($sqlWhere);
   }







}
