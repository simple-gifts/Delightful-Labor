<?php
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2015 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('admin/mtime_zones', 'cTZ');
---------------------------------------------------------------------*/
class mtime_zones extends CI_Model{

   public $lNumTZ, $tz;


   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();

      $this->lNumTZ = 0;
      $this->tz = array();
   }

   function loadTimeZones(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT tz_lKeyID, tz_strTimeZone, tz_lTZ_Const, tz_bTopList
         FROM lists_tz
         WHERE 1
         ORDER BY tz_bTopList DESC, tz_lKeyID;';
      $query = $this->db->query($sqlStr);
      $this->lNumTZ = $numRows = $query->num_rows();

      if ($numRows==0) {
         echo('<font face="monospace" style="font-size: 8pt;">'.__FILE__.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br(htmlspecialchars($sqlStr))."<br><br></font>\n");
         screamForHelp('UNEXPECTED EOF - Time Zone Table: <br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->tz[$idx] = new stdClass;
            $tzl = &$this->tz[$idx];
            $tzl->lKeyID      = (int)$row->tz_lKeyID;
            $tzl->strTimeZone = $row->tz_strTimeZone;
            $tzl->lTZ_Const   = (int)$row->tz_lTZ_Const;
            $tzl->bTopList    = (bool)$row->tz_bTopList;

            ++$idx;
         }
      }
   }
   
   function strTimeZoneViaID($lTZID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT tz_strTimeZone
         FROM lists_tz
         WHERE tz_lKeyID=$lTZID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return($row->tz_strTimeZone);
   }

   function strTimeZoneDDL($strDDLName, $strID, $lMatchID, $bIncludeBlank){
   //---------------------------------------------------------------------
   // caller must first call $this->loadTimeZones
   //---------------------------------------------------------------------
      $strOut = '<select name="'.$strDDLName.'" id="'.$strID.'">'."\n";
      if ($bIncludeBlank) $strOut .= '<option value="-1">&nbsp;</option>'."\n";

      $bFullList = false;
      foreach ($this->tz as $tzl){
         if (!$bFullList && !$tzl->bTopList){
            $strOut .= '<option value="-1">&dash;&dash;&dash;&dash;&dash;&dash;&dash;</option>'."\n";
            $bFullList = true;
         }
         $strSel = ($lMatchID == $tzl->lKeyID ? ' SELECTED ' : '');
         $strOut .= '<option value="'.$tzl->lKeyID.'" '.$strSel.'>'.htmlspecialchars($tzl->strTimeZone).'</option>'."\n";
      }

      $strOut .= '</select>'."\n";
      return($strOut);
   }



}