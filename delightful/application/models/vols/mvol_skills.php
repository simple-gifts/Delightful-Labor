<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2012 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->model('vols/mvol_skills', 'clsVolSkills');
---------------------------------------------------------------------*/

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mvol_skills extends CI_Model{
   public
       $lVolID, $lNumSingleVolSkills, $singleVolSkills;


   public function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->lVolID = $this->lNumSingleVolSkills = $this->singleVolSkills = null;
   }

   public function loadSingleVolSkills(){
   //-----------------------------------------------------------------------
   // load the skill list for a single volunteer
   //-----------------------------------------------------------------------
      $this->loadVolSkills(false);
   }

   public function loadSkillsPlusVolSkills(){
   //-----------------------------------------------------------------------
   // load the skill list for a single volunteer, plus all other skills
   //-----------------------------------------------------------------------
      $this->loadVolSkills(true);
   }

   public function loadVolSkills($bIncludeAllSkills, $lSkillIDList=null){
   //-----------------------------------------------------------------------
   // to load a list of skills:
   //    set $this->lVolID = -1, then call loadVolSkills(true)
   //-----------------------------------------------------------------------
      $this->singleVolSkills = array();

      if (is_null($this->lVolID)) screamForHelp('$this->lVolID: Class not initialized<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      if ($bIncludeAllSkills){
         $strJoinType = " LEFT JOIN vol_skills ON ((lgen_lKeyID=vs_lSkillID) AND (vs_lVolID=$this->lVolID)) ";
      }else {
         $strJoinType = " INNER JOIN vol_skills ON ((lgen_lKeyID=vs_lSkillID) AND (vs_lVolID=$this->lVolID)) ";
      }

      $strWhere = '';
      if (!is_null($lSkillIDList)){
         $strWhere = ' AND lgen_lKeyID IN ('.implode(',', $lSkillIDList).') ';
      }

      $sqlStr =
        "SELECT
            lgen_lKeyID, lgen_strListItem, lgen_bRetired,
            vs_lKeyID, vs_lVolID, vs_lSkillID, vs_Notes
         FROM lists_generic
            $strJoinType
         WHERE 1 $strWhere
            AND lgen_enumListType='volSkills'
         ORDER BY lgen_strListItem, vs_lKeyID;";

      $query = $this->db->query($sqlStr);
      $this->lNumSingleVolSkills = $numRows = $query->num_rows();

      if ($numRows==0) {
         $this->singleVolSkills[0] = new stdClass;
         $skill = &$this->singleVolSkills[0];
         $skill->lKeyID         =
         $skill->lVolID         =
         $skill->lSkillID       =
         $skill->Notes          =
         $skill->strSkill       = null;
         $skill->bVolHasSkill   = false;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            if (!is_null($row->vs_lKeyID) || !$row->lgen_bRetired){
               $this->singleVolSkills[$idx] = new stdClass;
               $skill = &$this->singleVolSkills[$idx];
               $skill->bVolHasSkill   = $bHasSkill = !is_null($row->vs_lKeyID);
               $skill->lKeyID         = $row->vs_lKeyID;
               $skill->lVolID         = $row->vs_lVolID;
               $skill->lSkillID       = $row->lgen_lKeyID;
               $skill->Notes          = $row->vs_Notes;
               $skill->strSkill       = $row->lgen_strListItem;
               ++$idx;
            }
         }
      }
   }

   public function clearVolSkills(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_null($this->lVolID)) screamForHelp('$this->lVolID: Class not initialized<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);

      $sqlStr = "DELETE FROM vol_skills WHERE vs_lVolID=$this->lVolID;";

      $query = $this->db->query($sqlStr);
   }

   public function setVolSkill($lSkillID){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      if (is_null($this->lVolID)) screamForHelp('$this->lVolID: Class not initialized<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
      $sqlStr =
         "INSERT INTO vol_skills
          SET
             vs_lVolID   = $this->lVolID,
             vs_lSkillID = $lSkillID,
             vs_Notes    = '';";
      $query = $this->db->query($sqlStr);
   }


      /* -----------------------------------------------------------------------
                                 R E P O R T S
      --------------------------------------------------------------------------*/
   function lNumRecsInReport(
                         &$skillIDs,  $bShowAny,  $bIncludeInactive,
                         $bUseLimits, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strSkillListIDs = implode(',', $skillIDs);
      $lNumSkills   = count($skillIDs);

      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      if ($bShowAny){
         $sqlStr =
              'SELECT DISTINCT vol_lKeyID
               FROM volunteers
                  INNER JOIN  vol_skills  ON vol_lKeyID  = vs_lVolID
                  INNER JOIN people_names ON pe_lKeyID   = vol_lPeopleID
               WHERE vs_lSkillID IN ('.$strSkillListIDs.')
                  AND NOT pe_bRetired
                  AND NOT vol_bRetired '
                  .($bIncludeInactive ? '' : ' AND NOT vol_bInactive ')
               .$strLimit.';';
      }else {
            // all selected skills
         $sqlStr =
              'SELECT vol_lKeyID, COUNT(vol_lKeyID) AS lNumRecs
               FROM volunteers
                  INNER JOIN  vol_skills  ON vol_lKeyID  = vs_lVolID
                  INNER JOIN people_names ON pe_lKeyID   = vol_lPeopleID
               WHERE vs_lSkillID IN ('.$strSkillListIDs.')
                  AND NOT pe_bRetired
                  AND NOT vol_bRetired '
                  .($bIncludeInactive ? '' : ' AND NOT vol_bInactive ').'
               GROUP BY vol_lKeyID
               HAVING COUNT(vol_lKeyID)='.$lNumSkills.'
               ORDER BY vol_lKeyID '
               .$strLimit.';';
      }
      $query = $this->db->query($sqlStr);
     return($query->num_rows());
   }


   function strJobSkillsReportPage(
                             $skillIDs, $bShowAny,  $bIncludeInactive,
                             $bReport,  $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->db->query('DROP TABLE IF EXISTS tmpGroupMatch;');

      $strOut = $strExport = '';

      $strOut   = 'Volunteers who possess <b>'.($bShowAny ? 'any' : 'all')
                  .'</b> of the following skills:<br>';
      $this->lVolID = -1;
      $this->loadVolSkills(true, $skillIDs);
      $strSkillsReviewed = '';
      if ($this->lNumSingleVolSkills > 0){
         if ($bReport) $strOut .= '<ul>';
         foreach($this->singleVolSkills as $skill){
            if ($bReport){
               $strOut .= '<li>'.htmlspecialchars($skill->strSkill).'</li>';
            }else {
               $strSkillsReviewed .= "\n* ".$skill->strSkill;
            }
         }
         if ($bReport) $strOut .= '</ul>';
      }

         // create temporary table to hold foreign ID of report results
      $sqlStr =
           'CREATE TEMPORARY TABLE tmpGroupMatch (
              gm_lKeyID     int(11) NOT NULL AUTO_INCREMENT,
              gm_lForeignID int(11) NOT NULL DEFAULT \'0\',
              PRIMARY KEY       (gm_lKeyID),
              KEY gm_lForeignID (gm_lForeignID)
            ) ENGINE=MyISAM;';
      $this->db->query($sqlStr);

      $strSkillsIDs = implode(',', $skillIDs);
      $lNumSkills   = count($skillIDs);
      if ($bReport){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      if ($bShowAny){
         $sqlSelect =
              'SELECT DISTINCT vol_lKeyID AS lForeignID
               FROM volunteers
                  INNER JOIN  vol_skills  ON vol_lKeyID  = vs_lVolID
                  INNER JOIN people_names ON pe_lKeyID   = vol_lPeopleID
               WHERE 1
                  AND vs_lSkillID IN ('.$strSkillsIDs.')
                  AND NOT pe_bRetired
                  AND NOT vol_bRetired '
                  .($bIncludeInactive ? '' : ' AND NOT vol_bInactive ').'
               ORDER BY pe_strLName, pe_strFName, pe_lKeyID '
               .$strLimit.';';
      }else {
            // member of all selected groups
         $sqlSelect =
              'SELECT vol_lKeyID AS lForeignID
               FROM volunteers
                  INNER JOIN  vol_skills  ON vol_lKeyID  = vs_lVolID
                  INNER JOIN people_names ON pe_lKeyID   = vol_lPeopleID
               WHERE 1
                  AND vs_lSkillID IN ('.$strSkillsIDs.')
                  AND NOT pe_bRetired
                  AND NOT vol_bRetired '
                  .($bIncludeInactive ? '' : ' AND NOT vol_bInactive ').'
               GROUP BY vol_lKeyID
               HAVING COUNT(vol_lKeyID)='.$lNumSkills.'
               ORDER BY pe_strLName, pe_strFName, pe_strMName, pe_lKeyID '
                  .$strLimit.';';
      }

      $sqlStr =
         "INSERT INTO tmpGroupMatch
            (gm_lForeignID)
            $sqlSelect;";
      $query = $this->db->query($sqlStr);
      $lNumRows = $this->db->affected_rows();

      if ($lNumRows==0){
         return($strOut.'<br><br><i>There are no records that match your search criteria.</i>');
      }

      if ($bReport){
         $strOut .= $this->strJobSkillsRptHTML();
         return($strOut);
      }else {
         $strExport = $this->strJobSkillsRptExport($strSkillsReviewed, $bShowAny, $bIncludeInactive);
         return($strExport);
      }
   }

   private function strJobSkillsRptHTML(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterVoc;
      
      $strOut =
              '<table class="enpRptC">
                 <tr>
                    <td class="enpRptLabel">
                       volunteerID
                    </td>
                    <td class="enpRptLabel">
                       peopleID
                    </td>
                    <td class="enpRptLabel">
                       Name
                    </td>
                    <td class="enpRptLabel">
                       Address
                    </td>
                    <td class="enpRptLabel">
                       Phone/Email
                    </td>
                    <td class="enpRptLabel">'
                       .htmlspecialchars($gclsChapterVoc->vocJobSkills).'
                    </td>
                 </tr>'."\n";
      $sqlStr =
         "SELECT
             vol_lKeyID, pe_lKeyID, vol_bInactive,
             pe_strLName, pe_strFName, pe_strAddr1, pe_strAddr2,
             pe_strCity, pe_strState, pe_strCountry, pe_strZip, pe_strPhone, pe_strCell, pe_strEmail
          FROM tmpGroupMatch
             INNER JOIN volunteers   ON vol_lKeyID    = gm_lForeignID
             INNER JOIN people_names ON vol_lPeopleID = pe_lKeyID
          ORDER BY gm_lKeyID;";
      $query = $this->db->query($sqlStr);
      foreach ($query->result() as $row){
         $lVolID = $this->lVolID = $row->vol_lKeyID;
         $lPID   = $row->pe_lKeyID;
         $this->loadVolSkills(false);

         if ($row->vol_bInactive){
            $strInactive = '<br>(inactive)';
         }else {
            $strInactive = '';
         }
         if ($row->pe_strEmail.'' == ''){
            $strEmail = '';
         }else {
            $strEmail = '<br>'.mailto($row->pe_strEmail, $row->pe_strEmail);
         }

            /*
               tip for preventing that pesky line break before a list
               http://stackoverflow.com/questions/1682873/how-do-i-prevent-a-line-break-occurring-before-an-unordered-list
               ul.errorlist {list-style-type: none; display:inline; margin-left: 0; padding-left: 0;}
               ul.errorlist li {display: inline; color:red; font-size: 0.8em; margin-left: 0px; padding-left: 10px;}
            */
         $strSkillList = '<ul style="list-style-type: square; display:inline; margin-left: 0; padding-left: 0;">';
         foreach ($this->singleVolSkills as $skill){
            $strSkillList .= '<li style="margin-left: 20px; padding-left: 3px;">'
                .htmlspecialchars($skill->strSkill).'</li>';
         }
         $strSkillList .= '</ul>';
         $strOut .=
              '<tr>
                  <td class="enpRpt" style="width: 65px;">'
                     .strLinkView_Volunteer($lVolID, 'View volunteer record', true).'&nbsp;'
                     .str_pad($lVolID, 5, '0', STR_PAD_LEFT).$strInactive.'
                  </td>
                  <td class="enpRpt" style="width: 65px;">'
                     .strLinkView_PeopleRecord($lPID, 'View people record', true).'&nbsp;'
                     .str_pad($lPID, 5, '0', STR_PAD_LEFT).'
                  </td>
                  <td class="enpRpt" style="width: 160px;">'
                     .htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName).'
                  </td>
                  <td class="enpRpt" style="width: 200px;">'
                       .strBuildAddress(
                                 $row->pe_strAddr1, $row->pe_strAddr2,   $row->pe_strCity,
                                 $row->pe_strState, $row->pe_strCountry, $row->pe_strZip,
                                 true).'
                  </td>
                  <td class="enpRpt" style="width: 120px;">'
                     .htmlspecialchars(strPhoneCell($row->pe_strPhone, $row->pe_strCell)).$strEmail.'
                  </td>
                  <td class="enpRpt" style="width: 200px;">'
                     .$strSkillList.'
                  </td>
               </tr>'."\n";
         $strOut .= '</tr>'."\n";
      }

      $strOut .= '</table>'."\n";
      return($strOut);
   }

   private function strJobSkillsRptExport($strSkillsReviewed, $bShowAny, $bIncludeInactive){
      global $genumDateFormat;
      $sqlStr =
         'SELECT '.strExportFields_Vol().'
          FROM tmpGroupMatch
             INNER JOIN volunteers   ON vol_lKeyID=gm_lForeignID
             INNER JOIN people_names AS peepTab ON peepTab.pe_lKeyID = vol_lPeopleID
          ORDER BY gm_lKeyID;';
      $query = $this->db->query($sqlStr);
      $rptExport = $this->dbutil->csv_from_result($query);
      if ($this->config->item('dl_addExportRptInfo')){
         $rptExport .=
             strPrepStr(
                  CS_PROGNAME." export\n"
                 .'Created '.date($genumDateFormat.' H:i:s e')."\n"
                 .'Volunteers who possess '.($bShowAny ? 'any' : 'all')
                 .' of these skills: '.$strSkillsReviewed, null, '"');
      }
      return($rptExport);
   }


}



