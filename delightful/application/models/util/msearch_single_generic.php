<?php
/*---------------------------------------------------------------------
 Delightful Labor!

 copyright (c) 2012-2015 by Database Austin
 Austin, Texas

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.

/*---------------------------------------------------------------------
      $this->load->model('util/msearch_single_generic', 'clsSearch');
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class msearch_single_generic extends CI_Model{
   public
       $strSearchTerm, $lNumMatches, $objMatchInfo,
       $clsNavigation,
       $clsNavigationBack, $clsNavigationSearchAgain, $enumSearchType;

      //-----------------------------
      // search table properties
      //-----------------------------
   public
      $strLegendLabel, $strButtonLabel, $strFormTag, $lSearchTableWidth,
      $strWhereExtra, $strFromExtra, $strSelectExtra,
      $extraDisplayFields;

      //-----------------------------
      // search results
      //-----------------------------
   public
      $strNameFragment, $lExcludePID, $searchResults, $lNumSearchResults,
      $bShowRecLink, $bShowSelect, $bShowEnumSearchType, $strDisplayTitle;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      
      $this->strSearchTerm   = $this->lNumMatches       = $this->objMatchInfo      =
      $this->clsNavigation   = $this->clsNavigationBack = $this->clsNavigationSearchAgain =
      $this->enumSearchType  =
      $this->strNameFragment = $this->strSearchLabel    =
      $this->lExcludePID     = $this->strDisplayTitle   = null;

      $this->searchResults       = array();
      $this->lNumSearchResults   = 0;
      $this->bShowKeyID          =
      $this->bShowSelect         =
      $this->bShowLink           =
      $this->bShowEnumSearchType = false;

      $this->strWhereExtra = $this->strFromExtra = $this->strSelectExtra = '';
      
         // some searches allow extra field info to be returned;
         // example: caller sets
         //      [0]->strLabel = 'studentID';
         //      [0]->strFN    = 'uf000059_001110';
      $this->extraDisplayFields = array();
   }

   public function searchHousehold(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->searchPeopleTableGeneric(true, false, CENUM_CONTEXT_HOUSEHOLD, false);
   }

   public function searchPeople(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->searchPeopleTableGeneric(false, false, CENUM_CONTEXT_PEOPLE, false);
   }

   public function searchBiz(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->searchPeopleTableGeneric(false, true, CENUM_CONTEXT_BIZ, false);
   }

   public function searchNewVolunteer(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->searchPeopleTableGeneric(false, false, CENUM_CONTEXT_VOLUNTEER, true);
   }
   
   public function searchExistingVolunteers(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->searchPeopleTableGeneric(false, false, CENUM_CONTEXT_VOLUNTEER, false);
   }

   private function searchPeopleTableGeneric($bHofH, $bBiz, $enumSearchType, $bNewVol){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $clsPeople = new mpeople;
      if ($bHofH){
         $strWhereHofH = ' AND pe_lKeyID=pe_lHouseholdID ';
      }else {
         $strWhereHofH = '';
      }

      $strWhereVol = $strSelectVol = '';
      if ($enumSearchType==CENUM_CONTEXT_VOLUNTEER){
         $strSelectVol = ', vol_lKeyID, vol_bInactive ';
         if ($bNewVol){
            $strInnerVol  = ' LEFT JOIN volunteers ON pe_lKeyID=vol_lPeopleID ';
            $strWhereVol  = ' AND ((vol_lKeyID IS NULL) OR (vol_bInactive)) ';
         }else {
            $strInnerVol  = ' INNER JOIN volunteers ON pe_lKeyID=vol_lPeopleID ';
         }
      }else {
         $strInnerVol = '';
      }

      $strWhereBiz = ' AND '.($bBiz ? '' : ' NOT ').' pe_bBiz ';

      $sqlStr =
        "SELECT
            pe_lKeyID, pe_strFName, pe_strLName, pe_strMName, 
            pe_strPreferredName, pe_strTitle, pe_lHouseholdID,
            pe_strAddr1, pe_strAddr2, pe_strCity, pe_strState,
            pe_strCountry, pe_strZip
            $strSelectVol
         FROM people_names
            $strInnerVol
         WHERE 1
            AND NOT pe_bRetired
            $strWhereHofH
            $strWhereBiz
            $strWhereVol
            $this->strWhereExtra
         ORDER BY pe_strLName, pe_strFName, pe_lKeyID;";
         
      $query = $this->db->query($sqlStr);
         
      $numRows = $query->num_rows();
      if ($numRows > 0){
         $idx = 0;
         foreach ($query->result() as $row){

            $this->searchResults[$this->lNumSearchResults] = new stdClass;
            $clsResult                 = $this->searchResults[$this->lNumSearchResults];
            if ($enumSearchType==CENUM_CONTEXT_VOLUNTEER && !$bNewVol){
               $clsResult->lKeyID         = $row->vol_lKeyID;
            }else {
               $clsResult->lKeyID         = $row->pe_lKeyID;
            }
            $clsResult->enumSearchType = $enumSearchType;
            switch ($enumSearchType){
               case CENUM_CONTEXT_HOUSEHOLD:
                  $clsResult->strResult =
                         '<b>The '.htmlspecialchars($row->pe_strFName.' '.$row->pe_strLName).' Household</b><br>'
                         .strBuildAddress(
                                 $row->pe_strAddr1, $row->pe_strAddr2,   $row->pe_strCity,
                                 $row->pe_strState, $row->pe_strCountry, $row->pe_strZip,
                                 true,                true);
                  break;

               case CENUM_CONTEXT_VOLUNTEER: 
                  $strSafeNameLF = htmlspecialchars(
                                          strBuildName(true,              $row->pe_strTitle, $row->pe_strPreferredName, 
                                                       $row->pe_strFName, $row->pe_strLName, $row->pe_strMName));
               
                  $strFont = $strFontEnd = '';
                  if (!is_null($row->vol_lKeyID) && $row->vol_bInactive){
                     $strFont    = '<font style="color: #777777"><i>';
                     $strFontEnd = '<br>(inactive volunteer)</i></font> ';
                  }
                  $clsResult->strResult =
                         $strFont
                         .'<b>'.$strSafeNameLF."</b><br>\n"
                         .strBuildAddress(
                                 $row->pe_strAddr1, $row->pe_strAddr2,   $row->pe_strCity,
                                 $row->pe_strState, $row->pe_strCountry, $row->pe_strZip,
                                 true,                true)
                         .$strFontEnd;
                  break;

               case CENUM_CONTEXT_PEOPLE:
                  $strHousehold = $clsPeople->strHouseholdNameViaHID($row->pe_lHouseholdID);
                  $strSafeNameLF = htmlspecialchars(
                                          strBuildName(true,              $row->pe_strTitle, $row->pe_strPreferredName, 
                                                       $row->pe_strFName, $row->pe_strLName, $row->pe_strMName));
                  $clsResult->strResult =
                          '<b>'.$strSafeNameLF."</b><br>\n"
                         .'<i>'.htmlspecialchars($strHousehold).'</i><br>'
                         .strBuildAddress(
                                 $row->pe_strAddr1, $row->pe_strAddr2,   $row->pe_strCity,
                                 $row->pe_strState, $row->pe_strCountry, $row->pe_strZip,
                                 true,                true);
                  break;

               case CENUM_CONTEXT_BIZ:
                  $clsResult->strResult =
                         '<b>'.htmlspecialchars($row->pe_strLName).'</b><br>'
                         .strBuildAddress(
                                 $row->pe_strAddr1, $row->pe_strAddr2,   $row->pe_strCity,
                                 $row->pe_strState, $row->pe_strCountry, $row->pe_strZip,
                                 true,                true);
                  break;

               default:
                  screamForHelp($enumSearchType.': invalid switch type<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
                  break;

            }
            ++$this->lNumSearchResults;
            ++$idx;
         }
      }
   }

   public function strHTML_SearchResults(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gstrSeed;
      $strOut = '';

      if ($this->lNumSearchResults==0) {
         $strOut .= '<font color="red">There are no '.$this->strSearchLabel.' records that
                    match your search criteria  <b><i>"'
                   .htmlspecialchars($this->strSearchTerm).'"</b></i></font><br><br>'
                   .strLinkSpecial_SearchAgain($this->strPathSearchAgain, $this->strTitleSearchAgain, true).'&nbsp;'
                   .strLinkSpecial_SearchAgain($this->strPathSearchAgain, $this->strTitleSearchAgain, false);
      }else {
         $strOut .=
            $this->strDisplayTitle.'<br>'
            .strLinkSpecial_SearchAgain($this->strPathSearchAgain, $this->strTitleSearchAgain, true).'&nbsp;'
            .strLinkSpecial_SearchAgain($this->strPathSearchAgain, $this->strTitleSearchAgain, false)
            .'<br><br>
            <table class="enpRpt">';
         $this->clsNavigation = new stdClass;
         foreach ($this->searchResults as $clsFound){
            $lKeyID = $this->clsNavigation->lKey01 = $clsFound->lKeyID;
            if ($this->bShowKeyID){
               if ($this->bShowLink){
                  $strRecLink = $this->strIDLabel.strLinkView_ViaRecType($this->enumSearchType, $lKeyID)
                               .' '.str_pad($lKeyID, 5, '0', STR_PAD_LEFT).'<br>';
               }else {
                  $strRecLink = $this->strIDLabel.' '.str_pad($lKeyID, 5, '0', STR_PAD_LEFT).'<br>';
               }
            }else {
               $strRecLink = '';
            }
         $strOut .= '<tr>';

            if ($this->bShowSelect){
               $strLinkSel = strLinkSpecial_SearchSelect(
                               $this->strPathSelection.$lKeyID, $this->strTitleSelection, true, ' id="sID_'.$lKeyID.'" ');
               $strOut .= '
                  <td class="enpRpt" style="text-align: center;">'
                     .$strLinkSel.'
                  </td>';
            }

            if ($this->bShowEnumSearchType){
               $strOut .= '
                  <td class="enpRpt">'
                     .$this->enumSearchType.'
                  </td>';
            }

            $strOut .= '
                  <td class="enpRpt">'
                     .$strRecLink
                     .$clsFound->strResult.'
                  </td>

               </tr>';
         }
         $strOut .= '</table>';
      }
      return($strOut);
   }

   public function searchPeopleTableForm() {
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->searchPeopleBizTableForm(true);
   }

   public function searchBizTableForm() {
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->searchPeopleBizTableForm(false);
   }

   public function searchClientTableForm(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      echoT(
          '<table class="enpRpt" >
               <tr>
                  <td colspan="2"  class="enpRptTitle">'
                     .$this->strLegendLabel.'
                  </td>
               </tr>'

            .$this->strFormTag.'

              <tr>
                 <td class="enpRpt" align="right" width="'.$this->lSearchTableWidth.'">
                    First few letters of <b>Client\'s Last Name</b>:
                 </td>
                 <td class="enpRpt">
                    <input type="text" size="5" maxlength="20" name="txtSearch" value="" id="txtSearchID">
                    <i><small>(name must be in the Client table)</small></i>
                 </td>
              </tr>

              <tr>
                 <td class="enpRpt" align="center" colspan="2">
                    <input type="submit"
                          name="cmdAdd"
                          value="'.$this->strButtonLabel.'"
                          class="btn"
                             onmouseover="this.className=\'btn btnhov\'"
                             onmouseout="this.className=\'btn\'">
                 </td>');

         //------------------------------------
         // set the focus to this text field
         //------------------------------------
      echoT(
          '<script language="javascript">
              document.getElementById("txtSearchID").focus();
           </script>');

      echoT(
                '</form>
              </tr>
           </table><br><br>');
   }
   
   public function searchPeopleBizTableForm($bPeople){
   //---------------------------------------------------------------------
   // When setting up this form....
   //
   //
   //   $clsJV = new embedJavaVerify;
   //   $clsJV->clearEmbedOpts();
   //   $clsJV->bShow_bVerifyString =
   //   $clsJV->bShow_trim          = true;
   //   $clsJV->loadJavaVerify();
   //
   //   $clsSearch = new search;
   //   $clsSearch->strLegendLabel =
   //        'Search the PEOPLE table for a link to volunteer '.$strFName.' '.$strLName;
   //   $clsSearch->strButtonLabel = 'Click here to search';
   //   $clsSearch->strFormTag =
   //         '<form method="POST" action="../main/mainOpts.php" '
   //              .'name="frmVolSearch" '
   //              .'onSubmit="return verifySimpleSearch(frmVolSearch);"> '
   //            .'<input type="hidden" name="type"    value="vols"> '
   //            .'<input type="hidden" name="subType" value="ptabSearch"> '
   //            .'<input type="hidden" name="vID"     value="'.$strVolID.'"> ';
   //
   //   $clsSearch->lSearchTableWidth = 200;
   //   $clsSearch->searchPeopleTableForm();
   //---------------------------------------------------------------------
   // When responding to this form....
   //  $strSearch   = strLoad_REQ('txtSearch', true, false);
   //---------------------------------------------------------------------
      echoT(
          '<table class="enpRpt" >
               <tr>
                  <td colspan="2"  class="enpRptTitle">'
                     .$this->strLegendLabel.'
                  </td>
               </tr>'

            .$this->strFormTag.'

              <tr>
                 <td class="enpRpt" align="right" width="'.$this->lSearchTableWidth.'">
                    First few letters of <b>'.($bPeople ? 'the last name' : 'the business name').'</b>:
                 </td>
                 <td class="enpRpt">
                    <input type="text" size="5" maxlength="20" name="txtSearch" value="" id="txtSearchID">
                    <i><small>(name must be in the '.($bPeople ? 'People' : 'Business').' table)</small></i>
                 </td>
              </tr>

              <tr>
                 <td class="enpRpt" align="center" colspan="2">
                    <input type="submit"
                          name="cmdAdd"
                          value="'.$this->strButtonLabel.'"
                          class="btn"
                             onmouseover="this.className=\'btn btnhov\'"
                             onmouseout="this.className=\'btn\'">
                 </td>');
// note: don't disable the form on click - messes with the javascript verification                 
//                          onclick="this.disabled=1; this.form.submit();" 

         //------------------------------------
         // set the focus to this text field
         //------------------------------------
      echoT(
          '<script language="javascript">
              document.getElementById("txtSearchID").focus();
           </script>');

      echoT(
                '</form>
              </tr>
           </table><br><br>');
   }

   public function searchClients(&$local){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $strDateFormat = strMysqlDateFormat(false);

      $sqlStr =
           "SELECT
               cr_lKeyID,
               cr_strFName, cr_strLName, cr_dteBirth, cr_enumGender,
               cr_lLocationID,
               DATE_FORMAT(cr_dteBirth, $strDateFormat) AS strClientBDate,
               cl_strLocation, cl_strCountry
               $this->strSelectExtra
            FROM client_records
               INNER JOIN client_location ON cr_lLocationID = cl_lKeyID
               $this->strFromExtra
            WHERE 1
               AND NOT cr_bRetired
               $this->strWhereExtra
            ORDER BY cr_strLName, cr_strFName, cr_lKeyID;";
      $query = $local->db->query($sqlStr);

      $numRows = $query->num_rows();
      if ($numRows > 0){
          foreach ($query->result() as $row)   {

            $this->searchResults[$this->lNumSearchResults] = new stdClass;
            $clsResult                 = $this->searchResults[$this->lNumSearchResults];
            $clsResult->lKeyID         = $row->cr_lKeyID;

            $clsResult->strResult =
                         '<b>'.htmlspecialchars($row->cr_strLName.', '.$row->cr_strFName).'</b><br>'
                         .'birthdate: '.$row->strClientBDate.'<br>'
                         .'gender: '.$row->cr_enumGender.'<br>'
                         .'location: '.htmlspecialchars($row->cl_strLocation.' / '.$row->cl_strCountry);

            if (count($this->extraDisplayFields) > 0){
               foreach ($this->extraDisplayFields as $extra){
                  $strFN = $extra->strFN;
                  $clsResult->strResult .= '<br>'.htmlspecialchars($extra->strLabel).': '
                       .htmlspecialchars($row->$strFN);
               }
            }
            ++$this->lNumSearchResults;
         }
      }
   }

   public function searchSponsors(&$local){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strDateFormat = strMysqlDateFormat(false);
      $sqlStr =
        "SELECT sp_lKeyID,
            sp_lClientID, sp_lSponsorProgramID, sp_bInactive,
            pe_lKeyID, pe_bBiz, pe_strFName, pe_strMName,
            pe_strLName, pe_strAddr1, pe_strAddr2, pe_strCity,
            pe_strState, pe_strCountry, pe_strZip,
            DATE_FORMAT(cr_dteBirth, $strDateFormat) AS strClientBDate, cr_enumGender,
            cr_strFName, cr_strLName, cl_strLocation, cl_strCountry,
            sc_strProgram
         FROM sponsor
            INNER JOIN people_names               ON pe_lKeyID      = sp_lForeignID
            INNER JOIN lists_sponsorship_programs ON sc_lKeyID      = sp_lSponsorProgramID
            LEFT  JOIN client_records             ON cr_lKeyID      = sp_lClientID
            LEFT  JOIN client_location            ON cr_lLocationID = cl_lKeyID
         WHERE NOT pe_bRetired AND NOT sp_bRetired
            $this->strWhereExtra
         ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID;";

      $query = $local->db->query($sqlStr);
      $numRows = $query->num_rows();
      $clsResult = new stdClass;
      $clsResult->strResult = "\n\n\n\n";
      if ($numRows > 0){
          foreach ($query->result() as $row)   {
            $bBiz      = $row->pe_bBiz;
            $lSponID   = $row->sp_lKeyID;
            $bInactive = $row->sp_bInactive;

            $this->searchResults[$this->lNumSearchResults] = new stdClass;
            $clsResult                 = $this->searchResults[$this->lNumSearchResults];
            $clsResult->lKeyID         = $row->sp_lKeyID;

            if ($bInactive){
               $strStart = '<span style="color: #999;">';
               $strStop  = '</span>';
            }else {
               $strStart = 
               $strStop  = '';
            }
            $clsResult->strResult .= $strStart;
            if ($bBiz){
               $clsResult->strResult .=
                      '<b>'.htmlspecialchars($row->pe_strLName).'</b><br>';
            }else {
               $clsResult->strResult .=
                      '<b>'.htmlspecialchars($row->pe_strLName).'</b>, '.htmlspecialchars($row->pe_strFName)."<br>\n";
            }

            $clsResult->strResult .=
                         strBuildAddress(
                                 $row->pe_strAddr1, $row->pe_strAddr2,   $row->pe_strCity,
                                 $row->pe_strState, $row->pe_strCountry, $row->pe_strZip,
                                 true,                true).'<br>'
                                 .'<b>program:</b> '.htmlspecialchars($row->sc_strProgram);


            if (is_null($row->sp_lClientID)){
               $clsResult->strResult .= '<br>Client not set';
            }else {
               $clsResult->strResult .= '<br><br>'
                            .'<b>client:</b> '   .htmlspecialchars($row->cr_strLName.', '.$row->cr_strFName).'<br>'
                            .'<b>birthdate:</b> '.$row->strClientBDate.'<br>'
                            .'<b>gender:</b> '   .$row->cr_enumGender.'<br>'
                            .'<b>location:</b> ' .htmlspecialchars($row->cl_strLocation.' / '.$row->cl_strCountry).'<br>'
                            .'<b>status:</b> '   .($bInactive ? 'Inactive' : 'Active');
            }
            $clsResult->strResult .= $strStop;

            ++$this->lNumSearchResults;
         }
      }      
   }


}
?>