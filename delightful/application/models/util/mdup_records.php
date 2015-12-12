<?php
/*---------------------------------------------------------------------
 Delightful Labor
 copyright (c) 2014 Database Austin

 author: John Zimmerman

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model  ('clients/mclient_dups',       'cDups');
---------------------------------------------------------------------*/


class mdup_records extends CI_Model{
   public $uschema, $imgDoc;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

         // load personalized client tables
      $this->uschema = new muser_schema;
   }

   function consolidateDup($enumContext, $lGoodID, $dupIDs){
   //---------------------------------------------------------------------
   // note: currently group membership is not transferred
   //---------------------------------------------------------------------
      $strInDupIDs = ' IN ('.implode(', ', $dupIDs).') ';
      $this->imgDoc = new mimage_doc;

      switch ($enumContext){
         case CENUM_CONTEXT_CLIENT:
            $enumAttachTypes = array(
                                    CENUM_CONTEXT_CLIENT,
                                    CENUM_CONTEXT_CPROGENROLL,
                                    CENUM_CONTEXT_CPROGATTEND);
            $this->uschema->loadUFSchemaViaAttachType($enumAttachTypes);
            $this->consolidateDupClients($lGoodID, $strInDupIDs, $dupIDs);
            break;

         case CENUM_CONTEXT_PEOPLE:
            $enumAttachTypes = array(CENUM_CONTEXT_PEOPLE);
            $this->uschema->loadUFSchemaViaAttachType($enumAttachTypes);
            $this->consolidateDupPeople($lGoodID, $strInDupIDs, $dupIDs);
            break;

         case CENUM_CONTEXT_BIZ:
            $enumAttachTypes = array(CENUM_CONTEXT_BIZ);
            $this->uschema->loadUFSchemaViaAttachType($enumAttachTypes);
            $this->consolidateDupBiz($lGoodID, $strInDupIDs, $dupIDs);
            break;

         default:
            screamForHelp($enumContext.': invalid context for duplicate record consolidation<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function consolidateDupClients($lGoodID, $strInDupIDs, $dupIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $this->transferUF_FID($lGoodID, $strInDupIDs);

      $this->transferDocsImages(CENUM_CONTEXT_CLIENT, $lGoodID, $dupIDs);
      $this->transferReminders (CENUM_CONTEXT_CLIENT, $lGoodID, $strInDupIDs);

         // pre/post test
      $sqlStr =
        "UPDATE cpp_test_log
         SET cptl_lClientID=$lGoodID
         WHERE cptl_lKeyID $strInDupIDs
            AND NOT cptl_bRetired;";
      $query = $this->db->query($sqlStr);

         // EMR Measurements
      $sqlStr =
        "UPDATE emr_measurements
         SET meas_lClientID=$lGoodID
         WHERE meas_lClientID $strInDupIDs
            AND NOT meas_bRetired;";
      $query = $this->db->query($sqlStr);

         // client status
      $sqlStr =
         "UPDATE client_status
          SET csh_lClientID=$lGoodID
          WHERE csh_lClientID $strInDupIDs
            AND NOT csh_bRetired;";
      $query = $this->db->query($sqlStr);

         // supported programs
      $sqlStr =
        "UPDATE client_supported_sponprogs
         SET csp_lClientID=$lGoodID
         WHERE csp_lClientID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // sponsorships
      $sqlStr =
        "UPDATE sponsor
         SET sp_lClientID=$lGoodID
         WHERE NOT sp_bRetired
            AND sp_lClientID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // retire duplicate
      $sqlStr =
        "UPDATE client_records
         SET
            cr_bRetired=1,
            cr_lLastUpdateID=$glUserID
         WHERE cr_lKeyID $strInDupIDs;";
      $query = $this->db->query($sqlStr);
   }

   function transferDocsImages($enumContextType, $lGoodID, $dupIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      foreach ($dupIDs as $dID){
         $this->imgDoc->xferImageDocViaEntryContextFID(CENUM_IMGDOC_ENTRY_IMAGE, $enumContextType, $lGoodID, $dID);
         $this->imgDoc->xferImageDocViaEntryContextFID(CENUM_IMGDOC_ENTRY_PDF,   $enumContextType, $lGoodID, $dID);
      }
   }

   function transferReminders($enumContextType, $lGoodID, $strInDupIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "UPDATE reminders
         SET re_lForeignID=$lGoodID
         WHERE NOT re_bRetired
            AND re_enumSource=".strPrepStr($enumContextType)."
            AND re_lForeignID $strInDupIDs;";
      $query = $this->db->query($sqlStr);
   }

   function transferUF_FID($lGoodID, $strInDupIDs){
   //---------------------------------------------------------------------
   // transfer client FID in personalized tables; note: this will
   // include client program enrollment and attendance tables; exclude
   // single-entry tables since the master record will replace the dup record
   //---------------------------------------------------------------------
      if ($this->uschema->lNumTables > 0){
         foreach ($this->uschema->schema as $cschema){
            $strDataTableName = $cschema->strDataTableName;
            $strFieldPrefix   = $cschema->strFieldPrefix;
            $strDataTableFID  = $cschema->strDataTableFID;
            if ($cschema->bMultiEntry){

               $sqlStr =
                  "UPDATE $strDataTableName
                   SET $strDataTableFID=$lGoodID
                   WHERE $strDataTableFID $strInDupIDs
                      AND NOT $strFieldPrefix".'_bRetired;';
               $query = $this->db->query($sqlStr);
            }else {
                  // see if any of the single-entry tables have log fields; if so,
                  // transfer log entries to the good client
               if ($cschema->lNumFields > 0){
                  foreach ($cschema->fields as $uf){
                     if ($uf->enumFieldType==CS_FT_LOG){
                        $sqlStr =
                          "UPDATE uf_logs
                           SET uflog_lForeignID=$lGoodID
                           WHERE
                              uflog_lFieldID=$uf->lFieldID
                              AND uflog_lForeignID $strInDupIDs;";
                        $query = $this->db->query($sqlStr);
                     }
                  }
               }
            }
         }
      }
   }

   function consolidateDupBiz($lGoodID, $strInDupIDs, $dupIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $this->transferUF_FID($lGoodID, $strInDupIDs);
      $this->transferDocsImages(CENUM_CONTEXT_BIZ, $lGoodID, $dupIDs);
      $this->transferReminders (CENUM_CONTEXT_BIZ, $lGoodID, $strInDupIDs);

         // business contacts
      $sqlStr =
        "UPDATE biz_contacts
         SET bc_lBizID=$lGoodID
         WHERE bc_lBizID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // donations
      $sqlStr =
        "UPDATE gifts
         SET gi_lForeignID=$lGoodID
         WHERE gi_lForeignID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // gift pledges
      $sqlStr =
        "UPDATE gifts_pledges
         SET gp_lForeignID=$lGoodID
         WHERE gp_lForeignID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // gift pledges
      $sqlStr =
        "UPDATE gifts_pledges
         SET gp_lForeignID=$lGoodID
         WHERE gp_lForeignID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // sponsorship
      $sqlStr =
        "UPDATE sponsor
         SET sp_lForeignID=$lGoodID
         WHERE sp_lForeignID $strInDupIDs;";
      $query = $this->db->query($sqlStr);
      $sqlStr =
        "UPDATE sponsor
         SET sp_lHonoreeID=$lGoodID
         WHERE sp_lHonoreeID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // retire duplicate
      $sqlStr =
        "UPDATE people_names
         SET pe_bRetired=1
         WHERE pe_lKeyID $strInDupIDs;";
      $query = $this->db->query($sqlStr);
   }

   function consolidateDupPeople($lGoodID, $strInDupIDs, $dupIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      $this->transferUF_FID($lGoodID, $strInDupIDs);
      $this->transferDocsImages(CENUM_CONTEXT_PEOPLE, $lGoodID, $dupIDs);
      $this->transferReminders (CENUM_CONTEXT_PEOPLE, $lGoodID, $strInDupIDs);

         // business contacts
      $sqlStr =
        "UPDATE biz_contacts
         SET bc_lContactID=$lGoodID
         WHERE bc_lContactID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // donations
      $sqlStr =
        "UPDATE gifts
         SET gi_lForeignID=$lGoodID
         WHERE gi_lForeignID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // auctions - bid winners
      $sqlStr =
        "UPDATE gifts_auctions_packages
         SET ap_lBidWinnerID=$lGoodID
         WHERE ap_lBidWinnerID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // gift pledges
      $sqlStr =
        "UPDATE gifts_pledges
         SET gp_lForeignID=$lGoodID
         WHERE gp_lForeignID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // hon/mem references
      $sqlStr =
        "UPDATE lists_hon_mem
         SET ghm_lFID=$lGoodID
         WHERE ghm_lFID $strInDupIDs;";
      $query = $this->db->query($sqlStr);
      $sqlStr =
        "UPDATE lists_hon_mem
         SET ghm_lMailContactID=$lGoodID
         WHERE ghm_lMailContactID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // people relationships
      $sqlStr =
        "UPDATE people_relationships
         SET pr_lPerson_A_ID=$lGoodID
         WHERE pr_lPerson_A_ID $strInDupIDs;";
      $query = $this->db->query($sqlStr);
      $sqlStr =
        "UPDATE people_relationships
         SET pr_lPerson_B_ID=$lGoodID
         WHERE pr_lPerson_B_ID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // sponsorship
      $sqlStr =
        "UPDATE sponsor
         SET sp_lForeignID=$lGoodID
         WHERE sp_lForeignID $strInDupIDs;";
      $query = $this->db->query($sqlStr);
      $sqlStr =
        "UPDATE sponsor
         SET sp_lHonoreeID=$lGoodID
         WHERE sp_lHonoreeID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // transfer volunteers
      $this->consolidateDupVol($lGoodID, $dupIDs);

         // transfer household info
      $sqlStr =
        "UPDATE people_names
         SET pe_lHouseholdID=$lGoodID
         WHERE pe_lHouseholdID $strInDupIDs;";
      $query = $this->db->query($sqlStr);

         // transfer vol accounts w/people records
      $sqlStr =
        "UPDATE admin_users
         SET us_lPeopleID=$lGoodID
         WHERE us_lPeopleID $strInDupIDs;";
      $query = $this->db->query($sqlStr);


         // retire duplicate
      $sqlStr =
        "UPDATE people_names
         SET pe_bRetired=1
         WHERE pe_lKeyID $strInDupIDs;";
      $query = $this->db->query($sqlStr);


   }

   function consolidateDupVol($lGoodID, $dupIDs){
   //---------------------------------------------------------------------
   // this is a bit tricky: if a duplicate already has a volunteer
   // record, all the shift assignments must be transfered to "good" volunteer
   // record
   //---------------------------------------------------------------------
         // get the volID of the "good" ID
      $lVolID = $this->lVolIDViaPID($lGoodID);

      foreach ($dupIDs as $dupID){
         $lDupVolID = $this->lVolIDViaPID($dupID);

         if (!is_null($lDupVolID)){
            if (is_null($lVolID)){
                  // case where a duplicate has a volunteer record but "good"
                  // record doesn't - switch the peopleID of the dup volunteer record
                  // to the "good" peopleID
               $lVolID = $lDupVolID;
               $sqlStr = "UPDATE volunteers SET vol_lPeopleID=$lGoodID WHERE vol_lKeyID=$lVolID;";
               $query = $this->db->query($sqlStr);
            }else {
                  // set shift assignments to "good" volunteer record
               $sqlStr =
                   "UPDATE vol_events_dates_shifts_assign
                    SET vsa_lVolID=$lVolID WHERE vsa_lVolID=$lDupVolID;";

               $query = $this->db->query($sqlStr);

                  // transfer any images/docs
               $this->transferDocsImages(CENUM_CONTEXT_VOLUNTEER, $lGoodID, $dupIDs);

                  // disable the duplicate vol record
               $sqlStr = "UPDATE volunteers SET vol_bRetired=1 WHERE vol_lKeyID=$lDupVolID;";
               $query = $this->db->query($sqlStr);
            }
         }
      }
   }

   private function lVolIDViaPID($lPID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "SELECT vol_lKeyID FROM volunteers WHERE vol_lPeopleID=$lPID LIMIT 0,1;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         $lVolID = null;
      }else {
         $row = $query->row();
         $lVolID = (int)$row->vol_lKeyID;
      }
      return($lVolID);
   }


}







