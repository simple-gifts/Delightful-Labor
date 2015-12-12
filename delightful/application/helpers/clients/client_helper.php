<?php
/*
      $this->load->helper('clients/client');
*/

   function sqlQualClientViaStatus(
                       &$sqlWhere, &$strInner,        $bTestInDir,
                       $bInDir,    $bTestSponsorable, $bSponsorable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strInner =
          ' INNER JOIN client_status               ON csh_lClientID   = cr_lKeyID
            INNER JOIN lists_client_status_entries ON csh_lStatusID   = cst_lKeyID '."\n";

      $sqlWhere =
            ' -- ---------------------------------------
              -- subquery to find most current status
              -- ---------------------------------------
            AND csh_lKeyID=(SELECT csh_lKeyID
                            FROM client_status
                            WHERE csh_lClientID=cr_lKeyID
                               AND NOT csh_bRetired
                            ORDER BY csh_dteStatusDate DESC, csh_lKeyID DESC
                            LIMIT 0,1) ';
      if ($bTestInDir)       $sqlWhere .= ' AND '.($bInDir       ? '' : ' NOT ').' cst_bShowInDir ';
      if ($bTestSponsorable) $sqlWhere .= ' AND '.($bSponsorable ? '' : ' NOT ').' cst_bAllowSponsorship ';
      $sqlWhere .= "\n";
   }

   function initClientReportDisplay(&$displayData){
      $displayData['showFields'] = new stdClass;
      $displayData['showFields']->bLocationDDL    = false;
      $displayData['showFields']->bClientID       = true;
      $displayData['showFields']->bRemClient      = true;
      $displayData['showFields']->bName           = true;
      $displayData['showFields']->bAgeGender      = true;
      $displayData['showFields']->bLocation       = true;
      $displayData['showFields']->bStatus         = true;
      $displayData['showFields']->bSponsors       = bAllowAccess('showSponsors');
   }

   function strClientStatusCatTable(&$statCats, &$statCatsEntries, $bShowLinks, $bCenterTable){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lCols = $bShowLinks ? 5 : 4;
      $strOut =
        '<table class="enpRpt'.($bCenterTable ? 'C' : '').'">
            <tr>
               <td class="enpRptTitle" colspan="'.$lCols.'">
                  Client Status Categories
               </td>
            </tr>';

      $strOut .=
           '<tr>
               <td class="enpRptLabel" style="vertical-align: middle;">
                  Category ID
               </td>';
      if ($bShowLinks){
         $strOut .=
              '<td class="enpRptLabel" style="vertical-align: middle;">
                  &nbsp;
               </td>';
      }
      $strOut .=
              '<td class="enpRptLabel" style="vertical-align: middle;">
                  Category
               </td>
               <td class="enpRptLabel" style="width: 200pt;">
                  Description
               </td>
               <td class="enpRptLabel" style="vertical-align: middle;">
                  Entries
               </td>
            </tr>';

      foreach ($statCats as $clsCat){
         $lKeyID = $clsCat->lKeyID;

         if ($bShowLinks && !$clsCat->bProtected){
            $strLinkEdit    = strLinkEdit_ClientStatCat($lKeyID, 'Edit client status category', true).' ';
            $strLinkEntries = strLinkView_ClientStatEntries($lKeyID, 'View/edit/add entries', true).' '
                             .strLinkView_ClientStatEntries($lKeyID, 'View/edit/add entries', false);
            $strLinkRemove  = strLinkRem_ClientCatEntry($lKeyID, 'Remove this status category', true, true);

         }else {
            $strLinkEdit    = '';
            $strLinkEntries = '';
            $strLinkRemove  = strCantDelete('Default category can not be removed');
         }
         $strOut .=
              '<tr class="makeStripe">
                  <td class="enpRpt" style="text-align: center; vertical-align: top;  width: 70pt;">'
                     .$strLinkEdit
                     .str_pad($lKeyID, 5, '0', STR_PAD_LEFT).'
                  </td>';
         if ($bShowLinks){
            $strOut .=
                 '<td class="enpRpt" style="vertical-align: top;">'
                     .$strLinkRemove.'
                  </td>';
         }
         $strOut .=
                 '<td class="enpRpt" style="vertical-align: top;">'
                     .htmlspecialchars($clsCat->strCatName).'
                  </td>
                  <td class="enpRpt" style="vertical-align: top; width: 200pt;">'
                     .nl2br(htmlspecialchars($clsCat->strDescription)).'
                  </td>
                  <td class="enpRpt" style="text-align: left; vertical-align: top; width: 200pt;">';

         if (is_null($statCatsEntries[$lKeyID][0]->lKeyID)){
            $strOut .= '<i>No entries</i><br>';
         }else {
            $strOut .= '<ul style="list-style-type: square; display:inline; margin-left: 0; padding: 0pt;">';
            foreach ($statCatsEntries[$lKeyID] as $clsE){
               $strOut .= '<li style="margin-left: 20px;">'.htmlspecialchars($clsE->strStatusEntry).'</li>';
            }
            $strOut .= '</ul>';
         }
         $strOut .=
                    $strLinkEntries.'
                  </td>
               </tr>';
      }
      $strOut .= '</table>';
      return($strOut);
   }

   function showStatusHistory(
                       $bCenterTable,     $bShowLinks, &$clientStatus,
                       $lNumClientStatus, $lClientID, $strWidth){
   //------------------------------------------------------------------------
   //
   //------------------------------------------------------------------------
      global $genumDateFormat;

      $params = array('enumStyle' => 'enpRpt'.($bCenterTable ? 'C' : ''));
      $clsRpt = new generic_rpt($params);

      echoT($clsRpt->openReport($strWidth));
      echoT($clsRpt->openRow(true)
           .$clsRpt->writeLabel('StatusID'));
      if ($bShowLinks){
         echoT(
            $clsRpt->writeLabel('&nbsp;', 10));
      }
      echoT(
            $clsRpt->writeLabel('Status', 150)
           .$clsRpt->writeLabel('Date', 100)
           .$clsRpt->writeLabel('Flags', 200)
           .$clsRpt->writeLabel('Notes')
           .$clsRpt->closeRow()
           );

      $idx = 1;
      foreach ($clientStatus as $clsCStat){
         $lStatRecID = $clsCStat->lKeyID;
         $strFlags = '';
         if ($clsCStat->bAllowSponsorship){
            $strFlags .= '* sponsorship allowed<br>';
         }else {
            $strFlags .= '* sponsorship <i>not</i> allowed<br>';
         }
         if ($clsCStat->bShowInDir){
            $strFlags .= '* shown in directory<br>';
         }else {
            $strFlags .= '* <i>not</i> shown in directory<br>';
         }
         if ($clsCStat->bIncludeNotesInPacket){
            $strFlags .= '<b>* Packet Notes</b><br>';
         }

         if ($lNumClientStatus == $idx){
            $strLinkRem = '&nbsp;';
         }else {
            $strLinkRem = strLinkRem_ClientStat($lClientID, $lStatRecID, 'Remove this status record', true, true);
         }

         if ($bShowLinks){
            $strLinkEdit = strLinkEdit_ClientStat($lClientID, $lStatRecID, 'Edit status record', true).' ';
         }else {
            $strLinkEdit = '';
         }

         echoT($clsRpt->openRow(true)
              .$clsRpt->writeCell(
                          $strLinkEdit
                         .str_pad($lStatRecID, 5, '0', STR_PAD_LEFT), 30, $strStyleExtra='text-align: center;'));
         if ($bShowLinks){
            echoT(
               $clsRpt->writeCell($strLinkRem, 10));
         }
         echoT(
               $clsRpt->writeCell(htmlspecialchars($clsCStat->strStatus).'<br><i>'
                                .htmlspecialchars($clsCStat->strCatName).'</i>')
              .$clsRpt->writeCell(date($genumDateFormat, $clsCStat->dteStatus))
              .$clsRpt->writeCell($strFlags)
              .$clsRpt->writeCell(nl2br(htmlspecialchars($clsCStat->strStatusTxt)), 350)
              .$clsRpt->closeRow()
            );
         ++$idx;
      }
      echoT($clsRpt->closeReport('<br>'));
   }

   function clientBaseInfoForm(&$local, $enumSource, $lClientID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gbDateFormatUS, $gstrFormatDatePicker, $gdteNow;
   
      $displayData = array();
      $displayData['enumSource'] = $enumSource;
      $displayData['lClientID']  = $lClientID;

      $local->load->helper('dl_util/time_date');

         // validation rules
      $local->form_validation->set_error_delimiters('<div class="formError">', '</div>');
		$local->form_validation->set_rules('ddlLocation',      'Location',          'trim|required|callback_addClientVerifyLoc');
		$local->form_validation->set_rules('ddlClientStatCat', 'Status Category',   'trim|required|callback_addClientVerifySCat');
		$local->form_validation->set_rules('ddlClientVoc',     'Client Vocabulary', 'trim|required|callback_addClientVerifyVoc');
      if ($enumSource == 'clientXfer'){
         $local->form_validation->set_rules('txtEDate',      'Effective date', 'trim|required|callback_clientRecVerifyEffDateValid');
      }
      
		if ($local->form_validation->run() == FALSE){
         $local->load->library('generic_form');

         $local->load->helper ('dl_util/web_layout');
         $local->load->model  ('clients/mclient_locations',  'clsLoc');
         $local->load->model  ('clients/mclient_status',     'clsClientStat');
         $local->load->model  ('clients/mclient_vocabulary', 'clsClientV');
         $local->load->library('util/dl_date_time', '', 'clsDateTime');
         $local->load->model  ('clients/mclients', 'clsClients');
         $local->load->model  ('img_docs/mimage_doc',   'clsImgDoc');
         $local->load->helper ('img_docs/image_doc');
         $local->load->helper ('img_docs/link_img_docs');

         $local->clsLoc->loadAllLocations();
         $local->clsClientStat->loadClientStatCats(true, false, null);

         $strWarningLoc = '<font color="red">You will need to add one or more locations before adding clients.</font>';
         $strWarningSC  = '<font color="red">Before adding a new client, you will need to create<br>'
                        .' one or more status categories.</font>';
         $strLocFailMsg = $strStatFailMsg = '';
         $displayData['bFail'] = $bFail =
                  $local->clsLoc->bTestNoLocations      ($strWarningLoc, $strLocFailMsg) ||
                  $local->clsClientStat->bTestNoStatCats($strWarningSC,  $strStatFailMsg);
         $displayData['strFailMsg'] = $strLocFailMsg.'<br>'.$strStatFailMsg;

         if (!$bFail){
            if (validation_errors()==''){
               $displayData['strLocationDDL']      = $local->clsLoc->strDDLAllLocations(-1);
               $displayData['strClientStatCatDDL'] = $local->clsClientStat->strClientStatCatDDL(true, -1);
               $displayData['strClientVocDDL']     = $local->clsClientV->strClientVocDDL(true, -1);
               if ($enumSource == 'clientXfer'){
                  $displayData['strEDate'] = date($gstrFormatDatePicker, $gdteNow);               
               }
            }else {
               setOnFormError($displayData);
               $displayData['strLocationDDL']      = $local->clsLoc->strDDLAllLocations(set_value('ddlLocation'));
               $displayData['strClientStatCatDDL'] = $local->clsClientStat->strClientStatCatDDL(true, set_value('ddlClientStatCat'));
               $displayData['strClientVocDDL']     = $local->clsClientV->strClientVocDDL(true, set_value('ddlClientVoc'));
               if ($enumSource == 'clientXfer'){
                  $displayData['strEDate'] = set_value('txtEDate');               
               }
            }
         }

            //--------------------------
            // breadcrumbs
            //--------------------------
         switch ($enumSource){
            case 'clientXfer':
               $displayData['pageTitle'] = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                    .' | '.anchor('clients/client_record/view/'.$lClientID, 'Client Record', 'class="breadcrumb"')
                                    .' | Transfer Client';
                                    
                  //-------------------------------------
                  // load the client summary block
                  //-------------------------------------
               $params = array('enumStyle' => 'terse');
               $local->load->library('generic_rpt', $params, 'generic_rpt');
               $local->clsClients->loadClientsViaClientID($lClientID);
               $displayData['contextSummary'] = $local->clsClients->strClientHTMLSummary(0);
               break;
            case 'addNew':
               $displayData['pageTitle'] = anchor('main/menu/client', 'Clients', 'class="breadcrumb"')
                                      .' | Add new client';
               break;
            default:
               screamForHelp($enumSource.': invalid form type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);         
               break;
         }

         $displayData['title']          = CS_PROGNAME.' | Clients';
         $displayData['nav']            = $local->mnav_brain_jar->navData();

         $displayData['mainTemplate']   = 'client/client_new_s1_view';
         $local->load->vars($displayData);
         $local->load->view('template');
      }else {
         $lLocID     = (integer)$_POST['ddlLocation'];
         $lStatCatID = (integer)$_POST['ddlClientStatCat'];
         $lVocID     = (integer)$_POST['ddlClientVoc'];
         if ($enumSource == 'clientXfer'){
            $strEDate = trim($_POST['txtEDate']);
            MDY_ViaUserForm($strEDate, $lMon, $lDay, $lYear, $gbDateFormatUS);
            $dteEDate = strtotime($lMon.'/'.$lDay.'/'.$lYear);         
         }
         
         switch ($enumSource){
            case 'clientXfer':
               redirect('clients/client_record/xfer2/'.$lClientID.'/'.$lLocID.'/'.$lStatCatID.'/'.$lVocID.'/'.$dteEDate);
               break;
            case 'addNew':
               redirect('clients/client_rec_add_edit/addNewClientS2/0/'.$lLocID.'/'.$lStatCatID.'/'.$lVocID);
               break;
            default:
               screamForHelp($enumSource.': invalid form type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);         
               break;
         }
      }



   }
