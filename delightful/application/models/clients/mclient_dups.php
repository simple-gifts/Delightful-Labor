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


class mclient_dups extends CI_Model{
   public $uschema;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

         // load personalized client tables
      $this->uschema = new muser_schema;
      $enumAttachTypes = array(
                              CENUM_CONTEXT_CLIENT,
                              CENUM_CONTEXT_CPROGENROLL,
                              CENUM_CONTEXT_CPROGATTEND);
      $this->uschema->loadUFSchemaViaAttachType($enumAttachTypes);
   }

   function consolidateDup($lGoodCID, $lDupCID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

         // transfer client FID in personalized tables; note: this will
         // include client program enrollment and attendance tables; exclude
         // single-entry tables since the master record will replace the dup record
      if ($this->uschema->lNumTables > 0){
         foreach ($this->uschema->schema as $cschema){
            $strDataTableName = $cschema->strDataTableName;
            $strFieldPrefix   = $cschema->strFieldPrefix;
            $strDataTableFID  = $cschema->strDataTableFID;
            if ($cschema->bMultiEntry){

               $sqlStr =
                  "UPDATE $strDataTableName
                   SET $strDataTableFID=$lGoodCID
                   WHERE $strDataTableFID=$lDupCID
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
                           SET uflog_lForeignID=$lGoodCID
                           WHERE
                              uflog_lFieldID=$uf->lFieldID
                              AND uflog_lForeignID=$lDupCID;";
                        $query = $this->db->query($sqlStr);
                     }
                  }
               }
            }
         }
      }

         // pre/post test
      $sqlStr =
        "UPDATE cpp_test_log
         SET cptl_lClientID=$lGoodCID
         WHERE cptl_lKeyID=$lDupCID
            AND NOT cptl_bRetired;";
      $query = $this->db->query($sqlStr);

         // client status
      $sqlStr =
         "UPDATE client_status
          SET csh_lClientID=$lGoodCID
          WHERE csh_lClientID=$lDupCID
            AND NOT csh_bRetired;";
      $query = $this->db->query($sqlStr);

         // supported programs
      $sqlStr =
        "UPDATE client_supported_sponprogs
         SET csp_lClientID=$lGoodCID
         WHERE csp_lClientID=$lDupCID;";
      $query = $this->db->query($sqlStr);

         // sponsorships
      $sqlStr =
        "UPDATE sponsor
         SET sp_lClientID=$lGoodCID
         WHERE NOT sp_bRetired
            AND sp_lClientID=$lDupCID;";
      $query = $this->db->query($sqlStr);

         // retire duplicate
      $sqlStr =
        "UPDATE client_records
         SET
            cr_bRetired=1,
            cr_lLastUpdateID=$glUserID
         WHERE cr_lKeyID=$lDupCID;";
      $query = $this->db->query($sqlStr);

   }



}
