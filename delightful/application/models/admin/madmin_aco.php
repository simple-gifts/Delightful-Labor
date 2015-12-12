<?php
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2012-2015 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------

   __construct           ()
   loadCountries         ($bInUseOnly, $bDefaultFirst, $bSingleCountry, $lACOID)
   clearInUse            ()
   addInUse              ($lACOID, $bDefault)
   strFlagImage          ($strFlag, $strCountryName)
   strACO_Radios         ($lDefault, $strRadioName)
   lLoadDefaultCountryID ()

--------
      $this->load->model('admin/madmin_aco', 'clsACO');
---------------------------------------------------------------------*/
class madmin_aco extends CI_Model{


   public $lPID, $enumACO;

   public $strCountryIcon, $strCurrencySymbol, $countryAccess, $lCountryAccessCnt;

   public $lNumCountries, $countries;

   function __construct(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
		parent::__construct();

      $this->lPID = $this->enumACO =
      $this->strCountryIcon = $this->strCurrencySymbol = null;

      $this->lNumCountries = $this->countries = null;
   }

   function loadCountries($bInUseOnly, $bDefaultFirst, $bSingleCountry, $lACOID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bSingleCountry){
         $strWhereCountry = " AND aco_lKeyID=$lACOID ";
      }else {
         $strWhereCountry = '';
      }
      $sqlStr =
        "SELECT
            aco_lKeyID, aco_strFlag, aco_strName, aco_bDefault,
            aco_strCurrencySymbol, aco_bInUse
         FROM admin_aco
         WHERE 1 $strWhereCountry "
            .($bInUseOnly ? ' AND aco_bInUse ' : '').'
         ORDER BY '.($bDefaultFirst ? 'aco_bDefault DESC, ': '').' aco_strName;';
      $query = $this->db->query($sqlStr);
      $this->lNumCountries = $numRows = $query->num_rows();

      if ($numRows==0) {
         echo('<font face="monospace" style="font-size: 8pt;">'.__FILE__.' Line: <b>'.__LINE__.":</b><br><b>\$sqlStr=</b><br>".nl2br(htmlspecialchars($sqlStr))."<br><br></font>\n");
         screamForHelp($this->myg->gbDev, 'UNEXPECTED EOF<br></b>error on <b>line: </b>'.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
      }else {
         $this->countries = array();
         $idx = 0;
         foreach ($query->result() as $row){
            $this->countries[$idx] = new stdClass;

            $this->countries[$idx]->lKeyID            = (int)$row->aco_lKeyID;
            $this->countries[$idx]->strFlag           = $row->aco_strFlag;
            $this->countries[$idx]->strName           = $row->aco_strName;
            $this->countries[$idx]->strCurrencySymbol = $row->aco_strCurrencySymbol;
            $this->countries[$idx]->bInUse            = (boolean)$row->aco_bInUse;
            $this->countries[$idx]->bDefault          = (boolean)$row->aco_bDefault;
            $this->countries[$idx]->strFlagImg        =
                      '<img src="'.DL_IMAGEPATH.'/flags/'
                               .$row->aco_strFlag.'" alt="flag icon" title="'.$row->aco_strName.'">';
            ++$idx;
         }
      }
   }

   function loadACOViaFieldID($lFieldID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           "SELECT pff_lCurrencyACO
            FROM uf_fields
            WHERE pff_lKeyID=$lFieldID";
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows == 0){
         screamForHelp($lFieldID.': invalid field ID<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }
      $row = $query->row();
      $this->loadCountries(false, true, true, (int)$row->pff_lCurrencyACO);
   }

   function setACOClassViaFieldID($lFieldID, &$cacoClass){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadACOViaFieldID($lFieldID);
      $cacoClass = new stdClass;
      $cacoC = &$this->countries[0];

      $cacoClass->lKeyID            = $cacoC->lKeyID;
      $cacoClass->strFlag           = $cacoC->strFlag;
      $cacoClass->strName           = $cacoC->strName;
      $cacoClass->strCurrencySymbol = $cacoC->strCurrencySymbol;
      $cacoClass->bInUse            = $cacoC->bInUse;
      $cacoClass->bDefault          = $cacoC->bDefault;
      $cacoClass->strFlagImg        = $cacoC->strFlagImg;
   }

   public function clearInUse(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'UPDATE admin_aco
          SET aco_bInUse=0, aco_bDefault=0;';
      $query = $this->db->query($sqlStr);
   }

   public function addInUse($lACOID, $bDefault){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         'UPDATE admin_aco
          SET
             aco_bInUse=1,
             aco_bDefault='.($bDefault ? '1' : '0')."
          WHERE aco_lKeyID=$lACOID;";
      $query = $this->db->query($sqlStr);
   }

   public function strFlagImage($strFlag, $strCountryName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return('<img src="'.DL_IMAGEPATH.'/flags/'.$strFlag.'" alt="flag icon" title="'.$strCountryName.'">');
   }

   public function strACO_Radios($lDefault, $strRadioName, $bAll=false){
   //---------------------------------------------------------------------
   // id for All option = -1
   //---------------------------------------------------------------------
      global $gstrSeed;
      $this->loadCountries(true, true, false, null);
      $strOut = '<table cellspacing="4" cellpadding="0"><tr>';
      if ($bAll){
         if ($lDefault==-1){
            $strChecked = 'checked';
            $strStyle   = ' style="border: 1px solid; padding: 0px 6px 2px 2px; "';
         }else {
            $strChecked = '';
            $strStyle   = '';
         }
         $strOut .=
            '<td '.$strStyle.'>'
           .'<input type="radio" name="'.$strRadioName.'" value="-1" '
               .$strChecked.'>All</td>'."\n";
      }

      foreach ($this->countries as $clsCountry){
         $lKeyID = $clsCountry->lKeyID;
         if ($lDefault==$lKeyID){
            $strChecked = 'checked';
            $strStyle   = ' style="border: 1px solid; padding: 0px 6px 2px 2px; "';
         }else {
            $strChecked = '';
            $strStyle   = '';
         }
         $strOut .=
            '<td '.$strStyle.'>'
           .'<input type="radio" name="'.$strRadioName.'" value="'.$lKeyID.'" '
               .$strChecked.'>'.$clsCountry->strFlagImg."</td>\n";
      }
      return($strOut.'</tr></table>');
   }

   public function lLoadDefaultCountryID(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT aco_lKeyID
         FROM admin_aco
         WHERE aco_bDefault;';
      $query = $this->db->query($sqlStr);
      $numRows = $query->num_rows();
      if ($numRows==0) {
         return(null);
      }else {
         $row = $query->row();
         return((int)$row->aco_lKeyID);
      }
   }


}


?>