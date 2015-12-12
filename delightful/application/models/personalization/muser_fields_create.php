<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2011-2014 by Database Austin
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//---------------------------------------------------------------------
// Functions to support the User-Defined Personalized Fields
---------------------------------------------------------------------
      $this->load->model('personalization/muser_fields',        'clsUF');
      $this->load->model('personalization/muser_fields_create', 'clsUFC');

   __construct            ()
   updateUFTable          ()
   lAddNewUFTable         ()
   createAllRecs          ()
   strUFTablesCommon      ()
   createSingleEmptyRec   ($clsTable, $lKeyID)
   lMaxFieldSortIDX       ($lTableID)

   addNewField            ()
   addCheckboxField       ()
   addDateTimeField       ($bDateOnly)
   addTextField           ($lLength)
   addIntegerField        ()
   addDDLField            ()
   addCurrencyField       ()

   strCommonFieldSQL      ()
   updateField            ()
   strSetInternalFieldName($lTableID, $lFieldID)

   removeField            ()
   removeUFTable          ($lTableID)

---------------------------------------------------------------------*/

class muser_fields_create extends muser_fields{

   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();
   }

   public function updateUFTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_null($this->lTableID)) screamForHelp('Class not initialized!<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      $sqlStr =
          'UPDATE uf_tables
           SET '.$this->strUFTablesCommon().',
              pft_bHidden = '.($this->bHidden ? '1' : '0')."
           WHERE pft_lKeyID=$this->lTableID;";

      $query = $this->db->query($sqlStr);
   }

   public function lAddNewUFTable(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
          'INSERT INTO uf_tables
           SET '.$this->strUFTablesCommon().',
               pft_enumAttachType = '.strPrepStr($this->enumTType).',
               pft_bMultiEntry    = '.($this->bMultiEntry ? '1' : '0').",
               pft_bHidden        = 0,
               pft_bRetired       = 0,
               pft_lOriginID      = $glUserID,
               pft_dteOrigin      = NOW();";
      $this->db->query($sqlStr);
      $this->lTableID = $lTableID = $this->db->insert_id();

         //--------------------------------------------------------------------------------------
         // for new tables, create the actual database table. The table name is derived from the
         // keyID of the master table record entry
         //--------------------------------------------------------------------------------------
      $this->strENPTableName = $this->strGenUF_TableName($lTableID);
      $sqlStr =
          'UPDATE uf_tables
           SET
              pft_strDataTableName='.strPrepStr($this->strENPTableName)."
           WHERE (pft_lKeyID=$lTableID);";
      $this->db->query($sqlStr);

         //--------------------------
         // create the empty table
         //--------------------------
      $strKeyFNPrefix = $this->strGenUF_KeyFieldPrefix($lTableID);
      $strPrimaryKey  = $strKeyFNPrefix.'_lKeyID';
      $strForeignKey  = $strKeyFNPrefix.'_lForeignKey';
      $strRecEntered  = $strKeyFNPrefix.'_bRecordEntered';

      if ($this->bMultiEntry){
         $strExtraFields = "
            ".$strKeyFNPrefix."_bRetired      tinyint(1) NOT NULL DEFAULT '0',
            ".$strKeyFNPrefix."_lOriginID     int(11)    NOT NULL DEFAULT '0',
            ".$strKeyFNPrefix."_dteOrigin     datetime NOT NULL,
            ".$strKeyFNPrefix."_lLastUpdateID int(11) NOT NULL DEFAULT '0',
            ".$strKeyFNPrefix."_dteLastUpdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            ";
         $strExtraKeys = ",
                KEY ".$strKeyFNPrefix."_lOriginID     (".$strKeyFNPrefix."_lOriginID),
                KEY ".$strKeyFNPrefix."_dteOrigin     (".$strKeyFNPrefix."_dteOrigin),
                KEY ".$strKeyFNPrefix."_lLastUpdateID (".$strKeyFNPrefix."_lLastUpdateID),
                KEY ".$strKeyFNPrefix."_dteLastUpdate (".$strKeyFNPrefix."_dteLastUpdate)
                ";
      }else {
         $strExtraFields = "
            ".$strKeyFNPrefix."_lOriginID     int(11)    NOT NULL DEFAULT '0',
            ".$strKeyFNPrefix."_dteOrigin     datetime NOT NULL,
            ".$strKeyFNPrefix."_lLastUpdateID int(11) NOT NULL DEFAULT '0',
            ".$strKeyFNPrefix."_dteLastUpdate timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            ";
         $strExtraKeys = ",
                KEY ".$strKeyFNPrefix."_lOriginID     (".$strKeyFNPrefix."_lOriginID),
                KEY ".$strKeyFNPrefix."_dteOrigin     (".$strKeyFNPrefix."_dteOrigin),
                KEY ".$strKeyFNPrefix."_lLastUpdateID (".$strKeyFNPrefix."_lLastUpdateID),
                KEY ".$strKeyFNPrefix."_dteLastUpdate (".$strKeyFNPrefix."_dteLastUpdate)
                ";
      }

      $sqlStr =
          "DROP TABLE IF EXISTS $this->strENPTableName;";
      $this->db->query($sqlStr);

      $sqlStr =
          "CREATE TABLE $this->strENPTableName (
              $strPrimaryKey INT NOT NULL AUTO_INCREMENT,
              $strForeignKey INT(11) NOT NULL default '0',
              $strRecEntered TINYINT(1) NOT NULL default '0',
              $strExtraFields

              PRIMARY KEY                ($strPrimaryKey),
              ".($this->bMultiEntry ? '' : 'UNIQUE')." KEY $strForeignKey ($strForeignKey)
              $strExtraKeys
           )
           ENGINE=MyISAM;";
      $this->db->query($sqlStr);

      if (!$this->bMultiEntry) $this->createAllRecs();
      return($lTableID);
   }

   function createAllRecs(){
   //---------------------------------------------------------------------
   // for a new table, create a record for each foreign key in the
   // master table
   //---------------------------------------------------------------------
      $strForeignKeyField  = $this->strGenUF_KeyFieldPrefix($this->lTableID).'_lForeignKey';
      $strTableName        = $this->strENPTableName;

      switch ($this->enumTType) {
         case CENUM_CONTEXT_PEOPLE:
         case CENUM_CONTEXT_BIZ:
            $sqlStr =
                "INSERT INTO $strTableName ($strForeignKeyField)
                 SELECT pe_lKeyID FROM people_names
                 WHERE NOT pe_bRetired AND ".($this->enumTType=='Business' ? '' : ' NOT ').' pe_bBiz;';
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $sqlStr =
                "INSERT INTO $strTableName ($strForeignKeyField)
                 SELECT sp_lKeyID FROM sponsor
                 WHERE NOT sp_bRetired;";
            break;

         case CENUM_CONTEXT_CLIENT:
            $sqlStr =
                "INSERT INTO $strTableName ($strForeignKeyField)
                 SELECT cr_lKeyID FROM client_records
                 WHERE NOT cr_bRetired;";
            break;

         case CENUM_CONTEXT_LOCATION:
            $sqlStr =
                "INSERT INTO $strTableName ($strForeignKeyField)
                 SELECT cl_lKeyID FROM client_location
                 WHERE NOT cl_bRetired;";
            break;

         case CENUM_CONTEXT_GIFT:
            $sqlStr =
                "INSERT INTO $strTableName ($strForeignKeyField)
                 SELECT gi_lKeyID FROM gifts
                 WHERE NOT gi_bRetired;";
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            $sqlStr =
                "INSERT INTO $strTableName ($strForeignKeyField)
                 SELECT vol_lKeyID FROM volunteers
                 WHERE NOT vol_bRetired;";
            break;

         default:
            screamForHelp($this->enumTType.': Invalid personalized table type, error on line '.__LINE__.', file '.__FILE__.', function '.__FUNCTION__);
            break;
      }

      $query = $this->db->query($sqlStr);
   }

   public function createSingleEmptyRec($clsTable, $lKeyID){
   //---------------------------------------------------------------------
   // create personalized record for a given foreign ID/table
   //---------------------------------------------------------------------
      if (!$clsTable->bMultiEntry){
         $this->createSingleUFRow($clsTable->lKeyID, $clsTable->strDataTableName,
                                  $clsTable->strFieldPrefix, $lKeyID);
      }
   }

   private function strUFTablesCommon(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      return(
        '
        pft_bCollapsibleHeadings    = '.($this->bCollapsibleHeadings ? '1' : '0').',
        pft_bCollapseDefaultHide    = '.($this->bCollapseDefaultHide ? '1' : '0').',
                                    
        pft_bReadOnly               = '.($this->bReadOnly ? '1' : '0').',
        pft_bAlertIfNoEntry         = '.($this->bAlertNoDataEntry ? '1' : '0').',
        pft_strAlertMsg             = '.strPrepStr($this->strAlert).',

        pft_strVerificationModule = '.strPrepStr($this->strVerificationModule).',
        pft_strVModEntryPoint     = '.strPrepStr($this->strVModEntryPoint).',
        
        pft_strUserTableName        = '.strPrepStr($this->strUserTableName).',
        pft_strDescription          = '.strPrepStr($this->strTableDescription).",
        pft_lLastUpdateID           = $glUserID ");
   }

   function lMaxFieldSortIDX($lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "SELECT MAX(pff_lSortIDX) AS lMaxSortIDX
          FROM uf_fields
          WHERE pff_lTableID=$lTableID;";
          
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();

      if ($numRows==0) {
         return(0);
      }else {
         $row = $query->row();
         $lMaxSortIDX = $row->lMaxSortIDX;
         if (is_null($lMaxSortIDX)){
            return(0);
         }else {
            return($lMaxSortIDX);
         }
      }
   }
   
   function setUFieldSortIDX($lFieldID, $lSortIDX){
      $sqlStr =
         "UPDATE uf_fields
          SET pff_lSortIDX=$lSortIDX
          WHERE pff_lKeyID=$lFieldID;";
      $query = $this->db->query($sqlStr);   
   }

   function addNewField(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;

      if (is_null($this->lTableID)) screamForHelp('Class not initialized!<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);

      $sqlStr =
        "INSERT INTO uf_fields
         SET
            pff_lSortIDX         = ".($this->lMaxFieldSortIDX($this->lTableID)+1).",
            pff_lTableID         = $this->lTableID,
            pff_enumFieldType    = ".strPrepStr($this->fields[0]->enumFieldType).",
            pff_lDDLDefault      = NULL,
            pff_bConfigured      = 0,
            pff_lOriginID        = $glUserID,
            pff_dteOrigin        = NOW(), "
            .$this->strCommonFieldSQL().';';

      $query = $this->db->query($sqlStr);
      $this->fields[0]->pff_lKeyID = $lFieldID = $this->db->insert_id();

      $this->fields[0]->strFieldNameInternal = $this->strSetInternalFieldName($this->lTableID, $lFieldID);

         //---------------------------------------------------------------------
         // create the new field in the personalized table based on field type
         //---------------------------------------------------------------------
      switch ($this->fields[0]->enumFieldType){
         case CS_FT_CHECKBOX:
            $this->addCheckboxField();
            break;
         case CS_FT_DATE:
            $this->addDateTimeField(true);
            break;
         case CS_FT_DATETIME:
            $this->addDateTimeField(false);
            break;

         case CS_FT_TEXTLONG:
            $this->addTextField(-1);
            break;

         case CS_FT_TEXT255:
            $this->addTextField(255);
            break;

         case CS_FT_TEXT80:
            $this->addTextField(80);
            break;

         case CS_FT_TEXT20:
            $this->addTextField(20);
            break;

         case CS_FT_CLIENTID:
         case CS_FT_INTEGER:
            $this->addIntegerField();
            break;

         case CS_FT_CURRENCY:
            $this->addCurrencyField();
            break;

         case CS_FT_DDLMULTI:
         case CS_FT_DDL:
            $this->addDDLField();
            break;

         case CS_FT_LOG:   // no entry required
            break;

         case CS_FT_HEADING:   // no entry required
            break;

         default:
            screamForHelp($this->fields[0]->enumFieldType.': field type not implemented yet<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($lFieldID);
   }

   private function addCheckboxField(){
   //---------------------------------------------------------------------
   // create the checkbox field in the personalized table
   //---------------------------------------------------------------------
      $sqlStr =
         'ALTER TABLE '.$this->strENPTableName.'
              ADD '.$this->fields[0]->strFieldNameInternal.' TINYINT(1) DEFAULT \''
                .($this->fields[0]->pff_bCheckDef ? '1' : '0').'\' NOT NULL;';
      $query = $this->db->query($sqlStr);
   }

   private function addDateTimeField($bDateOnly){
   //---------------------------------------------------------------------
   // create the date field in the personalized table
   //---------------------------------------------------------------------
      $sqlStr =
         'ALTER TABLE '.$this->strENPTableName.'
              ADD '.$this->fields[0]->strFieldNameInternal.' DATE'.($bDateOnly ? '' : 'TIME').' DEFAULT NULL;';
      $query = $this->db->query($sqlStr);
   }

   private function addTextField($lLength){
   //---------------------------------------------------------------------
   // create the text field in the personalized table
   //---------------------------------------------------------------------
      if ($lLength <= 0){
         $sqlStr =
            'ALTER TABLE '.$this->strENPTableName.'
                 ADD '.$this->fields[0]->strFieldNameInternal.' TEXT NOT NULL;';
      }else {
         $sqlStr =
            'ALTER TABLE '.$this->strENPTableName.'
                 ADD '.$this->fields[0]->strFieldNameInternal.' varchar('.$lLength.')
                           NOT NULL DEFAULT '.strPrepStr($this->fields[0]->pff_strTxtDef).';';
      }
      $query = $this->db->query($sqlStr);
   }

   private function addIntegerField(){
   //---------------------------------------------------------------------
   // create the integer field in the personalized table
   //---------------------------------------------------------------------
      $sqlStr =
         'ALTER TABLE '.$this->strENPTableName.'
              ADD '.$this->fields[0]->strFieldNameInternal.' int(11)
                        NOT NULL DEFAULT '.strPrepStr($this->fields[0]->pff_lDef).' ';

      $query = $this->db->query($sqlStr);
   }

   private function addDDLField(){
   //---------------------------------------------------------------------
   // create the DDL field in the personalized table
   //---------------------------------------------------------------------
      $sqlStr =
         'ALTER TABLE '.$this->strENPTableName.'
              ADD '.$this->fields[0]->strFieldNameInternal.' int(11) DEFAULT NULL;';

      $query = $this->db->query($sqlStr);
   }

   private function addCurrencyField(){
   //---------------------------------------------------------------------
   // create the currency field in the personalized table
   //---------------------------------------------------------------------
      $sqlStr =
         'ALTER TABLE '.$this->strENPTableName.'
              ADD '.$this->fields[0]->strFieldNameInternal.' decimal(10,2)
                        NOT NULL DEFAULT '.strPrepStr($this->fields[0]->pff_curDef).' ';

      $query = $this->db->query($sqlStr);
   }

   private function strCommonFieldSQL(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $ufield = &$this->fields[0];
      return(
          ' 
            pff_strFieldNameUser = '.strPrepStr($ufield->pff_strFieldNameUser).',
            pff_strFieldNotes    = '.strPrepStr($ufield->strFieldNotes).',
            pff_bCheckDef        = '.strDBValueConvert_BOOL  ($ufield->pff_bCheckDef).',
            pff_curDef           = '.strDBValueConvert_SNG   ($ufield->pff_curDef, 2).',
            pff_strTxtDef        = '.strDBValueConvert_String($ufield->pff_strTxtDef).',
            pff_lDef             = '.strDBValueConvert_INT($ufield->pff_lDef).",
            pff_lCurrencyACO     = ".(integer)$ufield->pff_lCurrencyACO.",
            pff_bPrefilled       = ".($ufield->bPrefilled    ? '1' : '0').",
            pff_bHidden          = ".($ufield->pff_bHidden   ? '1' : '0').",
            pff_bRequired        = ".($ufield->pff_bRequired ? '1' : '0').",
            pff_lLastUpdateID    = $glUserID,
            pff_dteLastUpdate    = NOW() ");
   }

   public function updateField(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
          'UPDATE uf_fields
           SET '.$this->strCommonFieldSQL().'
           WHERE pff_lKeyID = '.$this->fields[0]->pff_lKeyID.';';
      $query = $this->db->query($sqlStr);
   }

   function strSetInternalFieldName($lTableID, $lFieldID){
   //---------------------------------------------------------------------
   // do the Tulsa turn-around; internal field name based on new keyID.
   // This routine must be called immediately after doing an insert of
   // a new field.
   //---------------------------------------------------------------------
      $strFieldInternal = $this->strGenUF_FieldName($lTableID, $lFieldID);
      $sqlStr =
         'UPDATE uf_fields
          SET pff_strFieldNameInternal='.strPrepStr($strFieldInternal)."
          WHERE pff_lKeyID=$lFieldID;";

      $query = $this->db->query($sqlStr);
      return($strFieldInternal);
   }

   function removeField(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $ufield   = &$this->fields[0];
      $lFieldID = $ufield->pff_lKeyID;
      
      $enumFT = $ufield->enumFieldType;

         //--------------------------------------------------------
         // if the field is a DDL, remove list items
         //--------------------------------------------------------
      if (($enumFT == CS_FT_DDL) || ($enumFT == CS_FT_DDLMULTI)){
         $sqlStr =
             'DELETE FROM uf_ddl
              WHERE ufddl_lFieldID='.$lFieldID.';';
         $this->db->query($sqlStr);
      }
      
         //--------------------------------------------------------
         // for multi-ddl, remove entries from uf_ddl_multi
         //--------------------------------------------------------
      if (($enumFT == CS_FT_DDL) || ($enumFT == CS_FT_DDLMULTI)){
         $sqlStr =
             'DELETE FROM uf_ddl_multi
              WHERE pdm_lFieldID='.$lFieldID.';';
         $this->db->query($sqlStr);
      }

         //---------------------------------------------
         // if this is a log field, remove log entries
         //---------------------------------------------
      if ($enumFT == 'Log'){
         $sqlStr =
             'DELETE FROM uf_logs '
            .'WHERE uflog_lFieldID='.$lFieldID.';';
         $this->db->query($sqlStr);
      }
      $this->removeFieldCore($ufield->pff_lKeyID, $ufield->enumFieldType, $this->strENPTableName, $ufield->strFieldNameInternal);
   }

   function removeFieldCore($pff_lKeyID, $enumFieldType, $strENPTableName, $strFieldNameInternal){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         //-------------------------------------------
         // remove the entry from the fields table
         //-------------------------------------------
      $sqlStr =
         'DELETE FROM uf_fields WHERE pff_lKeyID='.$pff_lKeyID.';';
      $query = $this->db->query($sqlStr);

         //-------------------------------------------
         // remove field from personalized table
         //-------------------------------------------
      if (!($enumFieldType==CS_FT_LOG || $enumFieldType==CS_FT_HEADING)){
         $sqlStr =
            'ALTER TABLE '.$strENPTableName.' DROP '.$strFieldNameInternal.';';
         $query = $this->db->query($sqlStr);
      }
   }

   function removeUFTable($lTableID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strTable    = $this->strGenUF_TableName($lTableID);

      $sqlStr = "DROP TABLE IF EXISTS $strTable;";
      $query = $this->db->query($sqlStr);

         // remove field entries for this table
      $sqlStr = "DELETE FROM uf_fields WHERE pff_lTableID=$lTableID;";
      $query = $this->db->query($sqlStr);

         // remove the table reference
      $sqlStr = "DELETE FROM uf_tables WHERE pft_lKeyID=$lTableID;";
      $query = $this->db->query($sqlStr);
   }

   function transferField($lTableFromID, $lTableToID, &$ufieldFrom, &$ufieldTo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strTableFrom = $this->strGenUF_TableName($lTableFromID);
      $strTableTo   = $this->strGenUF_TableName($lTableToID);

      $strFromFIDFN = $this->strGenUF_ForeignIDFN($lTableFromID);
      $strToFIDFN   = $this->strGenUF_ForeignIDFN($lTableToID);

      $enumType = $ufieldFrom->enumFieldType;

         // transfer the data
      if (!($enumType==CS_FT_HEADING || $enumType==CS_FT_LOG)){
         $sqlStr = "
            UPDATE $strTableTo
               INNER JOIN $strTableFrom ON $strToFIDFN = $strFromFIDFN
            SET $ufieldTo->strFieldNameInternal = $ufieldFrom->strFieldNameInternal;";
         $query = $this->db->query($sqlStr);
      }

         // for log entries, transfer the table and field info
      if ($enumType==CS_FT_LOG){
         $sqlStr = "
            UPDATE uf_logs
            SET uflog_lFieldID=$ufieldTo->pff_lKeyID
            WHERE uflog_lFieldID=$ufieldFrom->pff_lKeyID;";
         $query = $this->db->query($sqlStr);
      }

         // for ddl entries, transfer the field ID
      if ($enumType==CS_FT_DDL){
         $sqlStr = "
            UPDATE uf_ddl
            SET ufddl_lFieldID = $ufieldTo->pff_lKeyID
            WHERE ufddl_lFieldID = $ufieldFrom->pff_lKeyID";
         $query = $this->db->query($sqlStr);
      }
   }


}

?>