<?php
/*
      $this->load->helper('dl_util/verify_id');
      verifyID($this, $varID, 'image ID');
      verifyIDsViaType($this, CENUM_CONTEXT_PEOPLE, $lFID, false);
*/

function verifyID(&$local, $varID, $enumVerifyType, $bRedirectOnFail=true){
   /*---------------------------------------------------------------------
      another way... Note that get_instance is a CI function, defined in
      system/core/CodeIgniter.php

      from http://stackoverflow.com/questions/4740430/explain-ci-get-instance

      $CI =& get_instance(); // use get_instance, it is less prone to failure in this context.
   ---------------------------------------------------------------------*/
   $bValid = true;
   switch ($enumVerifyType){
      case 'account ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'gifts_accounts', 'ga_lKeyID', 'ga_bRetired');
         break;

      case 'attributed to ID':
         $bValid = vid_bAttributedToIDExists($local, $varID);
         break;

      case 'auction ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'gifts_auctions', 'auc_lKeyID', 'auc_bRetired');
         break;

      case 'auction item ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'gifts_auctions_items', 'ait_lKeyID', 'ait_bRetired');
         break;

      case 'autocharge ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'sponsor_autocharge_log', 'spcl_lKeyID', null);
         break;

      case 'bidsheet ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'gifts_auctions_bidsheets', 'abs_lKeyID', 'abs_bRetired');
         break;

      case 'business ID':
         $bValid = vid_bBizRecExists($local, $varID);
         break;

      case 'business contact ID':
         $bValid = vid_bBizConRecExists($local, $varID);
         break;

      case 'campaign ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'gifts_campaigns', 'gc_lKeyID', 'gc_bRetired');
         break;

      case 'client location ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'client_location', 'cl_lKeyID', 'cl_bRetired');
         break;

      case 'client ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'client_records', 'cr_lKeyID', 'cr_bRetired');
         break;

      case 'client program ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'cprograms', 'cp_lKeyID', 'cp_bRetired');
         break;

      case 'client vocabulary ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'lists_client_vocab', 'cv_lKeyID', 'cv_bRetired');
         break;

      case 'custom form ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'custom_forms', 'cf_lKeyID', 'cf_bRetired');
         break;

      case 'custom report ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'creport_dir', 'crd_lKeyID', 'crd_bRetired');
         break;

      case 'deposit ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'deposit_log', 'dl_lKeyID', 'dl_bRetired');
         break;

      case 'donation ID':
         $bValid = vid_bGiftExists($local, $varID);
         break;

      case 'event ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'vol_events', 'vem_lKeyID', 'vem_bRetired');

         break;

      case 'event date ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'vol_events_dates', 'ved_lKeyID', null);
         break;

      case 'grant ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'grants', 'gr_lKeyID', null);
         break;

      case 'group ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'groups_parent', 'gp_lKeyID', null);
         break;

      case 'honorarium/memorial ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'lists_hon_mem', 'ghm_lKeyID', 'ghm_bRetired');
         break;

      case 'household ID':
      case 'people ID':
         $bValid = vid_bPeopleRecExists($local, $varID);
         break;

      case 'image/document ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'docs_images', 'di_lKeyID', 'di_bRetired');
         break;
         
      case 'inventory cat ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'inv_cats', 'ivc_lKeyID', 'ivc_bRetired');
         break;

      case 'inventory item ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'inv_items', 'ivi_lKeyID', 'ivi_bRetired');
         break;

      case 'organization ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'admin_chapters', 'ch_lKeyID', 'ch_bRetired');
         break;

      case 'package ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'gifts_auctions_packages', 'ap_lKeyID', 'ap_bRetired');
         break;

      case 'people/business ID':
         $bValid = vid_bPBRecExists($local, $varID, false, false);
         break;

      case 'pledge ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'gifts_pledges', 'gp_lKeyID', 'gp_bRetired');
         break;

      case 'pre/post test ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'cpp_tests', 'cpp_lKeyID', 'cpp_bRetired');
         break;

      case 'provider ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'grant_providers', 'gpr_lKeyID', 'gpr_bRetired');
         break;

      case 'relationship ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'people_relationships', 'pr_lKeyID', 'pr_bRetired');
         break;

      case 'relationship entry ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'lists_people_relationships', 'lpr_lKeyID', 'lpr_bRetired');
         break;

      case 'reminder ID':
         $bValid = vid_bReminderIDExists($local, $varID);
         break;

      case 'search term ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'creport_search', 'crs_lKeyID', null);
         break;

      case 'shift ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'vol_events_dates_shifts', 'vs_lKeyID', 'vs_bRetired');
         break;

      case 'sponsor ID':
         $bValid = vid_bSponsorIDExists($local, $varID);
         break;

      case 'sponsorship charge ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'sponsor_charges', 'spc_lKeyID', 'spc_bRetired');
         break;

      case 'sponsor payment ID':
         $bValid = vid_bPaymentExists($local, $varID);
         break;

      case 'sponsorship program ID':
         $bValid = vid_bSponsorProgIDExists($local, $varID);
         break;

      case 'status category ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'client_status_cats', 'csc_lKeyID', 'csc_bRetired');
         break;

      case 'status ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'lists_client_status_entries', 'cst_lKeyID', 'cst_bRetired');
         break;

      case 'status entry ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'client_status', 'csh_lKeyID', 'csh_bRetired');
         break;

      case 'status entry list ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'lists_client_status_entries', 'cst_lKeyID', 'cst_bRetired');
         break;

      case 'user ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'admin_users', 'us_lKeyID', null);
         break;

      case 'volunteer ID':
         $bValid = vid_bVolRecExists($local, $varID);
         break;

      case 'volunteer assignment ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'vol_events_dates_shifts_assign', 'vsa_lKeyID', 'vsa_bRetired');
         break;

      case 'vol. registration ID':
         $bValid = vid_bGenericRecExists($local, $varID, 'vol_reg', 'vreg_lKeyID', 'vreg_bRetired');
         break;

      default:
         screamForHelp($enumVerifyType.': invalid verify type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         break;
   }
   if ($bRedirectOnFail){
      vid_bTestFail($local, $bValid, $enumVerifyType, $varID);
   }
   return($bValid);
}

function vid_bTestFail(&$local, $bValid, $enumVerifyType, $varID){
   if (!$bValid){
      $local->session->set_flashdata('error', '<b>ERROR:</b> The '.$enumVerifyType.' <b>'.htmlspecialchars($varID).'</b> is not valid.</font>');
      redirect('main/menu/home');
   }
}

function verifyIDsViaType(&$local, $enumContext, $lFID, $bTestForZero){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if (!$bTestForZero || ($lFID.'' != '0')){
      switch ($enumContext){
         case CENUM_CONTEXT_ACCOUNT:          verifyID($local, $lFID, 'account ID');          break;
         case CENUM_CONTEXT_AUCTION:          verifyID($local, $lFID, 'auction ID');          break;
         case CENUM_CONTEXT_AUCTIONITEM:      verifyID($local, $lFID, 'auction item ID');     break;
         case CENUM_CONTEXT_AUCTIONPACKAGE:   verifyID($local, $lFID, 'package ID');          break;
         case CENUM_CONTEXT_BIZ:              verifyID($local, $lFID, 'business ID');         break;
         case CENUM_CONTEXT_BIZCONTACT:       verifyID($local, $lFID, 'business contact ID'); break;
         case CENUM_CONTEXT_CAMPAIGN:         verifyID($local, $lFID, 'campaign ID');         break;

         case CENUM_CONTEXT_CLIENT:
         case CENUM_CONTEXT_CPROGRAM:
         case CENUM_CONTEXT_CPROGENROLL:
         case CENUM_CONTEXT_CPROGATTEND:      verifyID($local, $lFID, 'client ID');           break;
         
         case CENUM_CONTEXT_CUSTOMREPORT:     verifyID($local, $lFID, 'custom report ID');    break;
         case CENUM_CONTEXT_CUSTOMREPORTTERM: verifyID($local, $lFID, 'search term ID');      break;
         case CENUM_CONTEXT_GIFT:             verifyID($local, $lFID, 'donation ID');         break;
         case CENUM_CONTEXT_GIFTHON:          verifyID($local, $lFID, 'honorarium ID');       break;
         case CENUM_CONTEXT_GIFTMEM:          verifyID($local, $lFID, 'memorial ID');         break;
         
         case CENUM_CONTEXT_GRANTS:           verifyID($local, $lFID, 'grant ID');            break;
         case CENUM_CONTEXT_GRANTPROVIDER:    verifyID($local, $lFID, 'provider ID');         break;
         
         case CENUM_CONTEXT_HOUSEHOLD:        verifyID($local, $lFID, 'household ID');        break;
         case CENUM_CONTEXT_LOCATION:         verifyID($local, $lFID, 'client location ID');  break;
         case CENUM_CONTEXT_ORGANIZATION:     verifyID($local, $lFID, 'organization ID');     break;
         case CENUM_CONTEXT_PEOPLE:           verifyID($local, $lFID, 'people ID');           break;
         case CENUM_CONTEXT_REMINDER:         verifyID($local, $lFID, 'reminder ID');         break;
         case CENUM_CONTEXT_SPONSORPAY:       verifyID($local, $lFID, 'sponsor payment ID');  break;
         
         case CENUM_CONTEXT_SPONSORSHIP:      verifyID($local, $lFID, 'sponsor ID');          break;
         case CENUM_CONTEXT_STATUSCAT:        verifyID($local, $lFID, 'status category ID');  break;
         case CENUM_CONTEXT_STAFF:
         case CENUM_CONTEXT_USER:             verifyID($local, $lFID, 'user ID');             break;
         case CENUM_CONTEXT_VOLUNTEER:        verifyID($local, $lFID, 'volunteer ID');        break;

         case CENUM_CONTEXT_INVITEM:          verifyID($local, $lFID, 'inventory item ID');   break;
         default:
            screamForHelp($enumContext.': invalid context type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }
}

function vid_bSponsorProgIDExists(&$local, $varID){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if (!is_numeric($varID)) return(false);
   $sqlStr =
     "SELECT sc_lKeyID
      FROM lists_sponsorship_programs
      WHERE
         sc_lKeyID=$varID
         AND NOT sc_bRetired;";
   $query = $local->db->query($sqlStr);
   return($query->num_rows() > 0);
}

function vid_bReminderIDExists(&$local, $varID){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if (!is_numeric($varID)) return(false);
   $sqlStr =
     "SELECT re_lKeyID
      FROM reminders
      WHERE re_lKeyID=$varID
      AND NOT re_bRetired;";
   $query = $local->db->query($sqlStr);
   return($query->num_rows() > 0);
}

function vid_bBizConRecExists(&$local, $lBizConID){
   if (!is_numeric($lBizConID)) return(false);
   $sqlStr =
     'SELECT bc_lKeyID
      FROM biz_contacts
         INNER JOIN people_names ON pe_lKeyID=bc_lContactID
      WHERE bc_lKeyID='.(integer)$lBizConID.'
      AND NOT bc_bRetired
      AND NOT pe_bRetired;';
   $query = $local->db->query($sqlStr);
   return($query->num_rows() > 0);
}

function vid_bGiftExists(&$local, $lGiftID){
   if (!is_numeric($lGiftID)) return(false);
   $sqlStr = "SELECT gi_lKeyID FROM gifts WHERE gi_lKeyID=".(integer)$lGiftID
       ." AND NOT gi_bRetired AND gi_lSponsorID IS NULL;";
   $query = $local->db->query($sqlStr);
   return($query->num_rows() > 0);
}

function vid_bSponsorIDExists(&$local, $lSponID){
   if (!is_numeric($lSponID)) return(false);
   $sqlStr =
        'SELECT sp_lKeyID
         FROM sponsor
            INNER JOIN people_names ON sp_lForeignID=pe_lKeyID
         WHERE
            NOT sp_bRetired
            AND NOT pe_bRetired
            AND sp_lKeyID='.(integer)$lSponID.';';
   $query = $local->db->query($sqlStr);
   return($query->num_rows() > 0);
}

function vid_bPaymentExists(&$local, $lPayID){
   if (!is_numeric($lPayID)) return(false);
   $sqlStr = "SELECT gi_lKeyID FROM gifts WHERE gi_lKeyID=".(integer)$lPayID
       ." AND NOT gi_bRetired AND gi_lSponsorID IS NOT NULL;";
   $query = $local->db->query($sqlStr);
   return($query->num_rows() > 0);
}

function vid_bPeopleRecExists (&$local, $lPID){
  return(vid_bPBRecExists($local, $lPID, false));
}

function vid_bBizRecExists(&$local, $lBID){
  return(vid_bPBRecExists($local, $lBID, true));
}

function vid_bPBRecExists(&$local, $lPBID, $bBiz, $bTestBiz=true){
   if (!is_numeric($lPBID)) return(false);
   $sqlStr = "SELECT pe_lKeyID FROM people_names WHERE pe_lKeyID=".(integer)$lPBID
       ." AND NOT pe_bRetired ".($bTestBiz ? ' AND '.($bBiz ? '' : ' NOT ')." pe_bBiz " : '') .';';
   $query = $local->db->query($sqlStr);
   return($query->num_rows() > 0);
}

function vid_bVolRecExists(&$local, $lVolID){
   if (!is_numeric($lVolID)) return(false);
   $sqlStr =
      "SELECT vol_lKeyID
       FROM volunteers
          INNER JOIN people_names ON pe_lKeyID=vol_lPeopleID
       WHERE vol_lKeyID=$lVolID
          AND NOT vol_bRetired
          AND NOT pe_bRetired;";
   $query = $local->db->query($sqlStr);
   return($query->num_rows() > 0);
}

function vid_bVolViaPIDExists(&$local, $lPID, &$lVolID){
   if (!is_numeric($lPID)) return(false);
   $sqlStr =
      "SELECT vol_lKeyID
       FROM volunteers
          INNER JOIN people_names ON pe_lKeyID=vol_lPeopleID
       WHERE pe_lKeyID=$lPID
          AND NOT vol_bRetired
          AND NOT pe_bRetired;";
   $query = $local->db->query($sqlStr);

   if($query->num_rows() > 0){
      $row = $query->row();
      $lVolID = $row->vol_lKeyID;
      return(true);
   }else {
      return(false);
   }
}

function vid_bUserTableIDExists(&$local, $lUFID, &$enumTabType){
   if (!is_numeric($lUFID)) return(false);
   $sqlStr =
      "SELECT pft_lKeyID, pft_enumAttachType
       FROM uf_tables
       WHERE pft_lKeyID=$lUFID AND NOT pft_bRetired;";
   $query = $local->db->query($sqlStr);

   if($query->num_rows() > 0){
      $row = $query->row();
      $enumTabType = $row->pft_enumAttachType;
      return(true);
   }else {
      return(false);
   }
}

function vid_bGenericRecExists(&$local, $varID, $strTable, $strKeyIDFN, $strRetFN){
   if (!is_numeric($varID)) return(false);
   $sqlStr = "SELECT $strKeyIDFN FROM $strTable
              WHERE $strKeyIDFN=".(integer)$varID.' '
              .(is_null($strRetFN) ? '' : " AND NOT $strRetFN ").';';
   $query = $local->db->query($sqlStr);
   return($query->num_rows() > 0);
}

function vid_bAttributedToIDExists(&$local, $varID){
   if (!is_numeric($varID)) return(false);
   $sqlStr = 'SELECT lgen_lKeyID FROM lists_generic
              WHERE NOT lgen_bRetired AND lgen_lKeyID='.(integer)$varID.' AND lgen_enumListType="attrib";';
   $query = $local->db->query($sqlStr);
   return($query->num_rows() > 0);
}

