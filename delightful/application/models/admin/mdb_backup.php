<?php
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2015 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('admin/mdb_backup', 'cDBBackup');
---------------------------------------------------------------------*/
class mdb_backup extends CI_Model{


   function strBackup($prefs){
   //---------------------------------------------------------------------
   //  based on the codeIgniter system\database\drivers\mysql:: _backup($params = array())
   //
   // this version of codeIgniter down now provide a backup utility for 
   // mysqli.
   //
   // Also, this adds a block size option, which greatly reduces the
   // size of the output file.
   //---------------------------------------------------------------------
      global $gstrFName, $gstrLName, $gstrUserName, $gdteNow;

      $lPrefBlockSize = $prefs['blockSize'];
      if ($lPrefBlockSize <= 0) $lPrefBlockSize = 1;
      $strCommie = '-- --------------------------------------------------------------'."\n";
      $strOut = $strCommie
                .'-- Delightful Labor Database Backup'."\n"
                .'-- Created by '.$gstrFName.' '.$gstrLName.' ('.$gstrUserName.') on '.date('Y-m-d H:i:s', $gdteNow)."\n"
                .$strCommie;
      $tables = $this->db->list_tables();

      foreach ($tables as $table){
            // Get the table schema
         $query = $this->db->query("SHOW CREATE TABLE `".$this->db->database.'`.`'.$table.'`');

         // Write out the table schema
         $strOut .= $strCommie.'-- TABLE STRUCTURE FOR: '.$table."\n".$strCommie;

         if ($prefs['add_drop'] == TRUE){
            $strOut .= 'DROP TABLE IF EXISTS '.$table.';'."\n"."\n";
         }

         $idx = 0;
         $result = $query->result_array();
         foreach ($result[0] as $val){
            if ($idx++ % 2){
               $strOut .= $val.';'."\n"."\n";
            }
         }

            // Grab all the data from the current table
         $query = $this->db->query("SELECT * FROM $table");

         $lNumRows = $query->num_rows();
         if ($lNumRows > 0){

               // Fetch the field names and determine if the field is an
               // integer type.  We use this info to decide whether to
               // surround the data with quotes or not
            $idx = 0;
            $field_str = '';
            $is_int = array();
            $intTypes = array(MYSQLI_TYPE_DECIMAL, MYSQLI_TYPE_TINY, MYSQLI_TYPE_SHORT,
                              MYSQLI_TYPE_LONG, MYSQLI_TYPE_LONGLONG, MYSQLI_TYPE_INT24);
            while ($field = mysqli_fetch_field($query->result_id)){
               // Most versions of MySQL store timestamp as a string
               $is_int[$idx] = (in_array(
                               //  strtolower(mysqli_field_type($query->result_id, $idx)),
                                 (int)$field->type,
                                 $intTypes,
                                 TRUE)
                                 ) ? TRUE : FALSE;

               // Create a string of field names
               $field_str .= '`'.$field->name.'`, ';
               $idx++;
            }

               // Trim off the end comma
            $field_str = preg_replace( "/, $/" , "" , $field_str);

               // Build the insert string
            $lBlockCnt = 0;
            $lRowCnt = 0;
            foreach ($query->result_array() as $row){
               $val_str = '';
               if ($lBlockCnt == 0){
                  $strOut .= 'INSERT INTO '.$table.' ('.$field_str.")\nVALUES\n";
               }

               $jidx = 0;
               foreach ($row as $v) {
                     // Is the value NULL?
                  if ($v === NULL) {
                     $val_str .= 'NULL';
                  }else {
                        // Escape the data if it's not an integer
                     if ($is_int[$jidx] == FALSE){
                        $val_str .= $this->db->escape($v);
                     }else {
                        $val_str .= $v;
                     }
                  }

                     // Append a comma
                  $val_str .= ', ';
                  $jidx++;
               }

                  // Remove the comma at the end of the string
               $val_str = preg_replace( "/, $/" , "" , $val_str);
               ++$lBlockCnt;
               ++$lRowCnt;
               if (($lBlockCnt == $lPrefBlockSize) || ($lRowCnt==$lNumRows)){
                  $strEndChar = ";\n";
                  $lBlockCnt = 0;
               }else {
                  $strEndChar = ",\n";
               }

                  // Build the INSERT string
               $strOut .= '('.$val_str.')'.$strEndChar;
            }
            $strOut .= "\n"."\n";
         }
      }

         // Was a Gzip file requested?
      if ($prefs['format'] == 'gzip'){
         return(gzencode($strOut));
      }

         // Was a text file requested?
      if ($prefs['format'] == 'txt')    {
         return($strOut);
      }

         // Was a Zip file requested?
      if ($prefs['format'] == 'zip'){
            // Load the Zip class and output it
         $CI =& get_instance();
         $CI->load->library('zip');
         $CI->zip->add_data($prefs['filename'], $strOut);
         return($CI->zip->get_zip());
      }
   }


}
