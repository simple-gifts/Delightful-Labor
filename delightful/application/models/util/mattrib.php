<?php
/*---------------------------------------------------------------------
// Delightful Labor
// copyright (c) 2012 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
   __construct           ()
   strRecurringTable     ($strForm)
   generateDateList      ()
   looseMonthlyRecurring ()
   fixedMonthlyRecurring ()
   weeklyRecurring       ()
   loadFormRecur         ()
---------------------------------------------------------------------
      $this->load->model('util/mattrib', 'clsAttrib');
---------------------------------------------------------------------*/


class mattrib extends CI_Model{

   public
      $attribCount;

   public function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();
   }

   public function countAttribs(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->attribCount = array();

      $idx = 0;
      if (bAllowAccess('showPeople')){
         $this->attribCount[$idx] = new stdClass;
         $this->attribCount[$idx]->enumType    = CENUM_CONTEXT_PEOPLE;
         $this->attribCount[$idx]->strEnumType = strXlateContext(CENUM_CONTEXT_PEOPLE);
         $this->attribCntViaType($this->attribCount[$idx]->attribList, $this->attribCount[$idx]->lNumAttrib, CENUM_CONTEXT_PEOPLE);
         ++$idx;
         $this->attribCount[$idx] = new stdClass;
         $this->attribCount[$idx]->enumType  = CENUM_CONTEXT_BIZ;
         $this->attribCount[$idx]->strEnumType = strXlateContext(CENUM_CONTEXT_BIZ);
         $this->attribCntViaType($this->attribCount[$idx]->attribList, $this->attribCount[$idx]->lNumAttrib, CENUM_CONTEXT_BIZ);
         ++$idx;
      }

      if (bAllowAccess('showClients')){
         $this->attribCount[$idx] = new stdClass;
         $this->attribCount[$idx]->enumType  = CENUM_CONTEXT_CLIENT;
         $this->attribCount[$idx]->strEnumType = strXlateContext(CENUM_CONTEXT_CLIENT);
         $this->attribCntViaType($this->attribCount[$idx]->attribList, $this->attribCount[$idx]->lNumAttrib, CENUM_CONTEXT_CLIENT);
         ++$idx;
      }
      if (bAllowAccess('showSponsors')){
         $this->attribCount[$idx] = new stdClass;
         $this->attribCount[$idx]->enumType  = CENUM_CONTEXT_SPONSORSHIP;
         $this->attribCount[$idx]->strEnumType = strXlateContext(CENUM_CONTEXT_SPONSORSHIP);
         $this->attribCntViaType($this->attribCount[$idx]->attribList, $this->attribCount[$idx]->lNumAttrib, CENUM_CONTEXT_SPONSORSHIP);
         ++$idx;
      }

      if (bAllowAccess('showFinancials')){
         $this->attribCount[$idx] = new stdClass;
         $this->attribCount[$idx]->enumType  = CENUM_CONTEXT_GIFT;
         $this->attribCount[$idx]->strEnumType = strXlateContext(CENUM_CONTEXT_GIFT);
         $this->attribCntViaType($this->attribCount[$idx]->attribList, $this->attribCount[$idx]->lNumAttrib, CENUM_CONTEXT_GIFT);
         ++$idx;
      }
   }

   public function attribCntViaType(&$clsAList, &$lNumAttrib, $enumType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $clsAList = array();
      switch ($enumType){
         case CENUM_CONTEXT_BIZ:
         case CENUM_CONTEXT_PEOPLE:
            $sqlStr =
               'SELECT
                  COUNT(*) AS lNumRecs,
                  lgen_lKeyID AS lAttribID,
                  lgen_strListItem AS strAttribName
                FROM people_names
                   LEFT JOIN lists_generic ON pe_lAttributedTo=lgen_lKeyID

                WHERE NOT pe_bRetired AND '.($enumType==CENUM_CONTEXT_PEOPLE ? 'NOT' : '').' pe_bBiz
                   AND NOT lgen_bRetired
                GROUP BY lgen_lKeyID
                ORDER BY lgen_strListItem;';
            break;

         case CENUM_CONTEXT_CLIENT:
            $sqlStr =
               'SELECT
                  COUNT(*) AS lNumRecs,
                  lgen_lKeyID AS lAttribID,
                  lgen_strListItem AS strAttribName
                FROM client_records
                   LEFT JOIN lists_generic ON cr_lAttributedTo=lgen_lKeyID

                WHERE NOT cr_bRetired AND NOT lgen_bRetired
                GROUP BY lgen_lKeyID
                ORDER BY lgen_strListItem;';
            break;

         case CENUM_CONTEXT_GIFT:
            $sqlStr =
               'SELECT
                  COUNT(*) AS lNumRecs,
                  lgen_lKeyID AS lAttribID,
                  lgen_strListItem AS strAttribName
                FROM gifts
                   LEFT JOIN lists_generic ON gi_lAttributedTo=lgen_lKeyID

                WHERE NOT gi_bRetired AND NOT lgen_bRetired
                GROUP BY lgen_lKeyID
                ORDER BY lgen_strListItem;';
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $sqlStr =
               'SELECT
                  COUNT(*) AS lNumRecs,
                  lgen_lKeyID AS lAttribID,
                  lgen_strListItem AS strAttribName
                FROM sponsor
                   LEFT JOIN lists_generic ON sp_lAttributedTo=lgen_lKeyID

                WHERE NOT sp_bRetired AND NOT lgen_bRetired
                GROUP BY lgen_lKeyID
                ORDER BY lgen_strListItem;';
            break;

         default:
            screamForHelp($enumType.': invalid attrib type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      $query = $this->db->query($sqlStr);
      $lNumAttrib = $query->num_rows();
      if ($lNumAttrib > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $clsAList[$idx] = new stdClass;
            $clsAList[$idx]->lCount    = $row->lNumRecs;
            $clsAList[$idx]->lAttribID = $row->lAttribID;
            $clsAList[$idx]->strAttrib = $row->strAttribName;

            ++$idx;
         }
      }
   }



      /*----------------------------------------------------
                R E P O R T S
      ----------------------------------------------------*/
   function lNumRecsAttribReport(
                        &$sRpt,
                        $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $lAttribID = $sRpt->lAttribID;
      if ($lAttribID <= 0){
         $strAttrib = " IS NULL ";
      }else {
         $strAttrib = " = $lAttribID ";
      }

      $enumType = $sRpt->enumContext;
      switch ($enumType){
         case CENUM_CONTEXT_BIZ:
         case CENUM_CONTEXT_PEOPLE:
            $sqlStr =
               "SELECT pe_lKeyID
                FROM people_names
                WHERE NOT pe_bRetired
                   AND pe_lAttributedTo $strAttrib
                   AND ".($enumType==CENUM_CONTEXT_PEOPLE ? 'NOT' : '')." pe_bBiz
                ORDER BY pe_lKeyID
                $strLimit ;";
            break;

         case CENUM_CONTEXT_CLIENT:
            $sqlStr =
               "SELECT cr_lKeyID
                FROM client_records
                WHERE NOT cr_bRetired
                   AND cr_lAttributedTo $strAttrib
                ORDER BY cr_lKeyID
                $strLimit ;";
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $sqlStr =
               "SELECT sp_lKeyID
                FROM sponsor
                   INNER JOIN people_names ON pe_lKeyID=sp_lForeignID
                WHERE NOT sp_bRetired
                   AND sp_lAttributedTo $strAttrib
                   AND NOT pe_bRetired
                ORDER BY sp_lKeyID
                $strLimit ;";
            break;

         case CENUM_CONTEXT_GIFT:
            $sqlStr =
               "SELECT gi_lKeyID
                FROM gifts
                   INNER JOIN people_names ON pe_lKeyID=gi_lForeignID
                WHERE NOT gi_bRetired
                   AND gi_lAttributedTo $strAttrib
                   AND NOT pe_bRetired
                ORDER BY gi_lKeyID
                $strLimit ;";
            break;

         default:
            screamForHelp($enumType.': invalid attrib type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }

      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   public function strAttribReportExport(&$sRpt,   &$displayData,
                                          $bReport, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lAttribID = $sRpt->lAttribID;
      if ($lAttribID <= 0){
         $strAttrib = " IS NULL ";
         $strLabel  = ' not attributed to any source';
      }else {
         $strAttrib = " = $lAttribID ";
         $strLabel  = ' attributed to '.$this->strSafeAttribName($lAttribID);
      }

      if ($bReport){
         return($this->strAttribReport($sRpt, $lStartRec, $lRecsPerPage, $strAttrib, $strLabel));
      }else {
         return($this->strAttribExport($sRpt, $strAttrib));
      }
   }

   public function strSafeAttribName($lAttribID){
      $sqlStr = "SELECT lgen_strListItem FROM lists_generic WHERE lgen_lKeyID=$lAttribID;";
      $query  = $this->db->query($sqlStr);
      $row = $query->row();
      return(htmlspecialchars($row->lgen_strListItem));
   }

   private function strAttribReport(&$sRpt, $lStartRec, $lRecsPerPage, $strAttrib, $strLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      $enumType = $sRpt->enumContext;
      switch ($enumType){
         case CENUM_CONTEXT_BIZ:
         case CENUM_CONTEXT_PEOPLE:
            if (!bAllowAccess('showPeople')) return('');
            return($this->strAttribRptPeopleBiz($sRpt, $strLimit, $strAttrib, $enumType==CENUM_CONTEXT_PEOPLE, $strLabel));
            break;

         case CENUM_CONTEXT_CLIENT:
            if (!bAllowAccess('showClients')) return('');
            return($this->strAttribRptClient($sRpt, $strLimit, $strAttrib, $strLabel));
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            if (!bAllowAccess('showSponsors')) return('');
            return($this->strAttribRptSponsor($sRpt, $strLimit, $strAttrib, $strLabel));
            break;

         case CENUM_CONTEXT_GIFT:
            if (!bAllowAccess('showFinancials')) return('');
            return($this->strAttribRptGift($sRpt, $strLimit, $strAttrib, $strLabel));
            break;

         default:
            screamForHelp($enumType.': invalid attrib type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   private function strAttribExport(&$sRpt, $strAttrib){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $enumType = $sRpt->enumContext;
      switch ($enumType){
         case CENUM_CONTEXT_BIZ:
            return($this->strAttribExportBiz($sRpt, $strAttrib));
            break;

         case CENUM_CONTEXT_PEOPLE:
            return($this->strAttribExportPeople($sRpt, $strAttrib));
            break;

         case CENUM_CONTEXT_CLIENT:
            return($this->strAttribExportClient($sRpt, $strAttrib));
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            return($this->strAttribExportSponsorship($sRpt, $strAttrib));
            break;

         case CENUM_CONTEXT_GIFT:
            return($this->strAttribExportGifts($sRpt, $strAttrib));
            break;

         default:
            screamForHelp($enumType.': invalid attrib type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   private function strAttribExportGifts(&$sRpt, $strAttrib){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cEx = new mexports;
      $cEx->strGiftWhere = " AND gi_lAttributedTo $strAttrib ";
      return($cEx->strExportGift());
   }

   private function strAttribExportSponsorship(&$sRpt, $strAttrib){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cEx = new mexports;
      $cEx->strSponsorshipWhere = " AND sp_lAttributedTo $strAttrib ";
      return($cEx->strExportSpon());
   }

   private function strAttribExportClient($sRpt, $strAttrib){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cEx = new mexports;
      $cEx->strClientWhere = " AND cr_lAttributedTo $strAttrib ";
      return($cEx->strExportClients());
   }

   private function strAttribExportPeople($sRpt, $strAttrib){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cEx = new mexports;
      $cEx->strPeopleWhere = " AND peepTab.pe_lAttributedTo $strAttrib ";
      return($cEx->strExportPeople());
   }

   private function strAttribExportBiz($sRpt, $strAttrib){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cEx = new mexports;
      $cEx->strBizWhere = " AND bizTab.pe_lAttributedTo $strAttrib ";
      return($cEx->strExportBiz());
   }

   private function strAttribRptPeopleBiz(&$sRpt, $strLimit, $strAttrib, $bPeople, $strLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat     = strMysqlDateFormat(false);
      $strDateTimeFormat = strMysqlDateFormat(true);
      $sqlStr =
         "SELECT
             peepTab.pe_lKeyID AS lFID,
             peepTab.pe_strLName AS strLName, peepTab.pe_strFName AS strFName,
             peepTab.pe_strAddr1 AS strAddr1, peepTab.pe_strAddr2 AS strAddr2,
             peepTab.pe_strCity AS strCity, peepTab.pe_strState AS strState,
             peepTab.pe_strCountry AS strCountry,
             peepTab.pe_strZip AS strZip, peepTab.pe_strPhone AS strPhone,
             peepTab.pe_strCell AS strCell, peepTab.pe_strEmail AS strEmail,

             peepTab.pe_lACO,
             aco_strName,
             aco_strCurrencySymbol,
             aco_strFlag,
             peepTab.pe_lHouseholdID,
             CONCAT('The ', houseTab.pe_strFName,' ', houseTab.pe_strLName, ' Household') AS household,

             DATE_FORMAT(peepTab.pe_dteOrigin, $strDateTimeFormat) AS strDateOrigin

          FROM people_names AS peepTab
             INNER JOIN people_names AS houseTab ON peepTab.pe_lHouseholdID=houseTab.pe_lKeyID
             INNER JOIN admin_aco     ON peepTab.pe_lACO=aco_lKeyID
          WHERE NOT peepTab.pe_bRetired
             AND peepTab.pe_lAttributedTo $strAttrib
             AND ".($bPeople ? 'NOT' : '')." peepTab.pe_bBiz
          ORDER BY peepTab.pe_strLName, peepTab.pe_strFName, peepTab.pe_strMName, peepTab.pe_lKeyID
          $strLimit;";
      $query  = $this->db->query($sqlStr);
      $strOut =
         '<table class="enpRptC">
            <tr>
               <td class="enpRptTitle" colspan="6">'
                  .($bPeople ? 'People' : 'Business/Organization').' records '.$strLabel.'
               </td>
            </tr>
            <tr>
               <td class="enpRptLabel">'
                  .($bPeople ? 'peopleID' : 'businessID').'
               </td>
               <td class="enpRptLabel">
                  Name
               </td>
               <td class="enpRptLabel">
                  Address
               </td>
               <td class="enpRptLabel">
                  Household
               </td>
               <td class="enpRptLabel">
                  Phone/Email
               </td>
               <td class="enpRptLabel">
                  Creation Date
               </td>
            </tr>';

      foreach ($query->result() as $row){
         $lFID = $row->lFID;
         if ($row->strEmail.'' == ''){
            $strEmail = '';
         }else {
            $strEmail = '<br>'.mailto($row->strEmail, $row->strEmail);
         }

         if ($bPeople){
            $strLink = strLinkView_PeopleRecord($lFID, 'View people record', true).'&nbsp;';
         }else {
            $strLink = strLinkView_BizRecord($lFID, 'View people record', true).'&nbsp;';
         }

         $strOut .=
              '<tr class="makeStripe">
                  <td class="enpRpt" style="width: 65px;">'
                     .$strLink
                     .str_pad($lFID, 5, '0', STR_PAD_LEFT).'
                  </td>
                  <td class="enpRpt" style="width: 140px;">'
                     .htmlspecialchars($row->strLName.($bPeople ? ', '.$row->strFName : '')).'
                  </td>
                  <td class="enpRpt" style="width: 190px;">'
                       .strBuildAddress(
                                 $row->strAddr1, $row->strAddr2,   $row->strCity,
                                 $row->strState, $row->strCountry, $row->strZip,
                                 true).'
                  </td>
                  <td class="enpRpt" style="width: 190px;">'
                     .htmlspecialchars($row->household).'
                  </td>
                  <td class="enpRpt" style="width: 140px;">'
                     .strPhoneCell($row->strPhone, $row->strCell, true, true).$strEmail.'
                  </td>
                  <td class="enpRpt" style="width: 130px; text-align: center;">'
                     .$row->strDateOrigin.'
                  </td>
               </tr>'."\n";
         $strOut .= '</tr>'."\n";
      }

      $strOut .= '</table><br>';
      return($strOut);
   }

   private function strAttribRptClient(&$sRpt, $strLimit, $strAttrib, $strLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->helper('clients/client_sponsor');
      $this->load->model('clients/mclients', 'clsClients');
      $params = array('enumStyle' => 'enpRptC');
      $clsRpt = new generic_rpt($params);

      $this->clsClients->loadClientsViaAttribID($strAttrib, true, $strLimit);

      $strOut  = '<br>'.$clsRpt->openReport();
      $strOut .= $clsRpt->writeTitle('Clients '.$strLabel, '', '', 7);
      $strOut .= $clsRpt->openRow(true);
      $strOut .=
          $clsRpt->writeLabel('ClientID',  60)
         .$clsRpt->writeLabel('&nbsp;',    20)
         .$clsRpt->writeLabel('Name',     150)
         .$clsRpt->writeLabel('Birthday/Gender')
         .$clsRpt->writeLabel('Location')
         .$clsRpt->writeLabel('Status')
         .$clsRpt->writeLabel('Sponsors')
         .$clsRpt->closeRow();

      foreach ($this->clsClients->clients as $client){
         $lClientID    = $client->lKeyID;
         $strOut .= $clsRpt->openRow(true);
         $strOut .= $clsRpt->writeCell(
                               strLinkView_ClientRecord($lClientID, 'View client record', true)
                              .'&nbsp;'.str_pad($lClientID, 5, '0', STR_PAD_LEFT), 60);
         $strOut .= $clsRpt->writeCell(strLinkRem_Client($lClientID, 'Remove this client\'s record', true, true));
         $strOut .= $clsRpt->writeCell($client->strSafeNameLF, 150);
         $strOut .= $clsRpt->writeCell($client->strClientAgeBDay.'<br>'.$client->enumGender, 200);
         $strOut .= $clsRpt->writeCell(htmlspecialchars($client->strLocation)
                                    .'<br>'.htmlspecialchars($client->strLocCountry), 130);
         $strOut .= $clsRpt->writeCell(htmlspecialchars($client->curStat_strStatus), '');

         $strOut .= $clsRpt->writeCell(strSponsorSummaryViaCID(true, $client));
         $strOut .= $clsRpt->closeRow();
      }

      $strOut .= $clsRpt->closeReport();

      return($strOut);

   }

   private function strAttribRptSponsor(&$sRpt, $strLimit, $strAttrib, $strLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $genumDateFormat;

      $this->load->model('sponsorship/msponsorship', 'clsSpon');
      $params = array('enumStyle' => 'enpRptC');
      $clsRpt = new generic_rpt($params);
//      $this->load->helper('dl_util/email_web');

      $this->clsSpon->sponsorInfoGenericViaWhere(" AND sp_lAttributedTo $strAttrib ", $strLimit);

      $strOut  = '<br>'.$clsRpt->openReport();
      $strOut .= $clsRpt->writeTitle('Sponsorships '.$strLabel, '', '', 7);
      $strOut .= $clsRpt->openRow(true);
      $strOut .=
          $clsRpt->writeLabel('SponsorID',  60)
         .$clsRpt->writeLabel('Active',     90)
         .$clsRpt->writeLabel('Sponsor',   150)
         .$clsRpt->writeLabel('Address')
         .$clsRpt->writeLabel('Client')
         .$clsRpt->writeLabel('Commitment')
         .$clsRpt->closeRow();

      foreach ($this->clsSpon->sponInfo as $sponRec){
         $lSponID    = $sponRec->lKeyID;
         $lFID       = $sponRec->lForeignID;

         if ($sponRec->bInactive){
            $strIStyle = 'color: #999;';
            $strActiveDates = 'sponsor dates: '.date($genumDateFormat, $sponRec->dteStart).' - '
                                               .date($genumDateFormat, $sponRec->dteInactive);
         }else {
            $strIStyle = '';
            $strActiveDates = 'sponsor since: '.date($genumDateFormat, $sponRec->dteStart);
         }

         $strOut .= $clsRpt->openRow(true);
         $strOut .= $clsRpt->writeCell(
                               strLinkView_Sponsorship($lSponID, 'View sponsorship record', true)
                              .'&nbsp;'.str_pad($lSponID, 5, '0', STR_PAD_LEFT), 60, $strIStyle);

            //---------------------------
            // active state
            //---------------------------
         $strOut .= $clsRpt->writeCell($strActiveDates, '', $strIStyle);

         if ($sponRec->bSponBiz){
            $strLinkFID = 'business ID: '.strLinkView_BizRecord($lFID, 'View business record', true);
         }else {
            $strLinkFID = 'people ID: '.strLinkView_PeopleRecord($lFID, 'View people record', true);
         }
         $strLinkFID .= str_pad($lFID, 5, '0', STR_PAD_LEFT);

            //---------------------------
            // sponsor name
            //---------------------------
         $strHonoree = '';
         if ($sponRec->bHonoree){
            $lHFID = $sponRec->lHonoreeID;
            if ($sponRec->bHonBiz){
               $strLinkHFID = 'business ID: '.strLinkView_BizRecord($lHFID, 'View business record', true);
            }else {
               $strLinkHFID = 'people ID: '.strLinkView_PeopleRecord($lHFID, 'View people record', true);
            }
            $strLinkHFID .= str_pad($lHFID, 5, '0', STR_PAD_LEFT);

            $strHonoree = '<br><br>Honoree: '.$sponRec->strHonSafeNameLF.'<br>'.$strLinkHFID;
         }

         $strContact =
                 '<b>'.$sponRec->strSponSafeNameLF.'</b>'
                .'<br>'
                .$strLinkFID.$strHonoree.'<br>
                program: '.htmlspecialchars($sponRec->strSponProgram);
         $strOut .= $clsRpt->writeCell($strContact, '', $strIStyle);

            //---------------------------
            // address / contact
            //---------------------------
         $strAddr =
                 strBuildAddress(
                        $sponRec->strSponAddr1, $sponRec->strSponAddr2,   $sponRec->strSponCity,
                        $sponRec->strSponState, $sponRec->strSponCountry, $sponRec->strSponZip,
                        true);
         if ($sponRec->strSponEmail.'' != ''){
            $strAddr .= strBuildEmailLink($sponRec->strSponEmail, '<br>', false, ' style="'.$strIStyle.'"');
         }
         $strPhone = strPhoneCell($sponRec->strSponPhone, $sponRec->strSponPhone, true, true);
         if ($strPhone!=''){
            $strAddr .= '<br>'.$strPhone;
         }
         $strOut .= $clsRpt->writeCell($strAddr, '', $strIStyle);

            //---------------------------
            // client
            //---------------------------
         $lClientID = $sponRec->lClientID;
         if (is_null($lClientID)){
            if ($sponRec->bInactive){
               $strClientInfo = '<i>Client not set</i>';
            }else {
               $strClientInfo = '<i>Client not set</i><br>'
                            .strLinkAdd_ClientToSpon($lSponID, 'Add client to sponsorship', false);
            }
         }else {
            $strClientInfo = '<b>'.$sponRec->strClientSafeNameLF.'</b><br>'
                            .'client ID: '.strLinkView_ClientRecord($lClientID, 'View client record', true)
                            .str_pad($lClientID, 5, '0', STR_PAD_LEFT).'<br>'
                            .htmlspecialchars($sponRec->strLocation).'<br>'
                            .'birth/age: '.$sponRec->strClientAgeBDay;
            $strClientInfo .= '<br>'.htmlspecialchars($sponRec->strLocation);
         }
         $strOut .= $clsRpt->writeCell($strClientInfo, '', $strIStyle);

            //---------------------------
            // financial commitment
            //---------------------------
         $strOut .= $clsRpt->writeCell(
                 $sponRec->strCommitACOCurSym.' '.number_format($sponRec->curCommitment, 2).' '
                .$sponRec->strCommitACOFlagImg.'<br>'
                .strLinkView_SponsorFinancials($lSponID, 'View sponsorship financials', true)
                .strLinkView_SponsorFinancials($lSponID, 'View financials', false), '', $strIStyle);

         $strOut .= $clsRpt->closeRow();
      }

      $strOut .= $clsRpt->closeReport();
      return($strOut);
   }

   private function strAttribRptGift(&$sRpt, $strLimit, $strAttrib, $strLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->load->model('donations/mdonations', 'clsGifts');
      $this->load->model('admin/madmin_aco', 'clsACO');

      $this->clsGifts->sqlLimit      = $strLimit;
      $this->clsGifts->sqlExtraSort  = 'ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID, gi_dteDonation, gi_lKeyID ';
      $this->clsGifts->sqlExtraWhere = " AND gi_lAttributedTo $strAttrib ";
      $sqlStr = $this->clsGifts->strGiftSQL();
      $strTitle = '<tr><td class="enpRptTitle" colspan="7">Donations '.$strLabel.'</td></tr>';

      $this->clsGifts->loadGifts();
      return($this->clsGifts->strDonationViewTable($sqlStr, false, true, $strTitle));
   }


}