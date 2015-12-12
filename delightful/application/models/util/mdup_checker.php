<?php
/*---------------------------------------------------------------------
// Delightful Labor
// copyright (c) 2014 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
  ---------------------------------------------------------------------
      $this->load->model('util/mdup_checker', 'cDupChecker');
---------------------------------------------------------------------*/


class mdup_checker extends CI_Model{

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
   }

   public function findSimilarClientNames($strFName, $strLName, &$lNumMatches, &$matches){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumMatches = 0;
      $matches = array();
      $sqlStr =
        'SELECT
            cr_lKeyID, cr_strFName, cr_strLName, cr_enumGender, cr_dteBirth,
            cr_strAddr1, cr_strAddr2, cr_strCity, cr_strState, cr_strCountry,
            cr_strZip, cr_strPhone, cr_strCell, cr_strEmail,

            UNIX_TIMESTAMP(cr_dteOrigin) AS dteOrigin,
            UNIX_TIMESTAMP(cr_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM client_records
            INNER JOIN admin_users  AS uc ON uc.us_lKeyID=cr_lOriginID
            INNER JOIN admin_users  AS ul ON ul.us_lKeyID=cr_lLastUpdateID

         WHERE ( cr_strFName SOUNDS LIKE '.strPrepStr($strFName).' AND
                 cr_strLName SOUNDS LIKE '.strPrepStr($strLName).'
                )
            AND NOT cr_bRetired
         ORDER BY cr_strLName, cr_strFName, cr_lKeyID;';

      $query = $this->db->query($sqlStr);
      $lNumMatches = $query->num_rows();
      if ($lNumMatches > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $matches[$idx] = new stdClass;
            $match = &$matches[$idx];

            $match->lKeyID            = (int)$row->cr_lKeyID;
            $match->strFName          = $row->cr_strFName;
            $match->strLName          = $row->cr_strLName;
            $match->strSafeName       =
                          htmlspecialchars($row->cr_strFName.' '.$row->cr_strLName);
            $match->strSafeNameLF     =
                          htmlspecialchars($row->cr_strLName.', '.$row->cr_strFName);
            $match->mdteBirth          = $row->cr_dteBirth;
            $match->strAddr1           = $row->cr_strAddr1;
            $match->strAddr2           = $row->cr_strAddr2;
            $match->strCity            = $row->cr_strCity;
            $match->strState           = $row->cr_strState;
            $match->strCountry         = $row->cr_strCountry;
            $match->strZip             = $row->cr_strZip;
            $match->strPhone           = $row->cr_strPhone;
            $match->strCell            = $row->cr_strCell;
            $match->strEmail           = $row->cr_strEmail;
            $match->strEmailFormatted  = strBuildEmailLink($match->strEmail, '', false, '');
            $match->strAddress         =
                        strBuildAddress(
                                 $match->strAddr1, $match->strAddr2,   $match->strCity,
                                 $match->strState, $match->strCountry, $match->strZip,
                                 true);
            $match->dteOrigin                     = $row->dteOrigin;
            $match->dteLastUpdate                 = $row->dteLastUpdate;
            $match->ucstrFName                    = $row->strUCFName;
            $match->ucstrLName                    = $row->strUCLName;
            $match->ulstrFName                    = $row->strULFName;
            $match->ulstrLName                    = $row->strULLName;

            ++$idx;
         }
      }
   }

   function findSimilarPeopleNames($strFName, $strLName, &$lNumMatches, &$matches){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->findSimilarPeopleBizNames(true, $strFName, $strLName, $lNumMatches, $matches);
   }

   function findSimilarBizNames($strName,&$lNumMatches, &$matches){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->findSimilarPeopleBizNames(false, '', $strName, $lNumMatches, $matches);
   }

   function findSimilarPeopleBizNames($bPeople, $strFName, $strLName, &$lNumMatches, &$matches){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bPeople) {
         $strWhereExtra = ' AND pe_strFName SOUNDS LIKE '.strPrepStr($strFName)
                    .' AND NOT pe_bBiz ';
      }else {
         $strWhereExtra = ' AND pe_bBiz ';
      }
      
      $lNumMatches = 0;
      $matches = array();
      $sqlStr =
        'SELECT
            pe_lKeyID, pe_strFName, pe_strLName, pe_enumGender,
            pe_strAddr1, pe_strAddr2, pe_strCity, pe_strState, pe_strCountry,
            pe_strZip, pe_strPhone, pe_strCell, pe_strEmail,

            UNIX_TIMESTAMP(pe_dteOrigin) AS dteOrigin,
            UNIX_TIMESTAMP(pe_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM people_names
            INNER JOIN admin_users  AS uc ON uc.us_lKeyID=pe_lOriginID
            INNER JOIN admin_users  AS ul ON ul.us_lKeyID=pe_lLastUpdateID

         WHERE  pe_strLName SOUNDS LIKE '.strPrepStr($strLName).' '.$strWhereExtra.'               
            AND NOT pe_bRetired
         ORDER BY pe_strLName, pe_strFName, pe_lKeyID;';

      $query = $this->db->query($sqlStr);
      $lNumMatches = $query->num_rows();
      if ($lNumMatches > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $matches[$idx] = new stdClass;
            $match = &$matches[$idx];

            $match->lKeyID            = (int)$row->pe_lKeyID;
            $match->strFName          = $row->pe_strFName;
            $match->strLName          = $row->pe_strLName;
            $match->strSafeName       =
                          htmlspecialchars($row->pe_strFName.' '.$row->pe_strLName);
            $match->strSafeNameLF     =
                          htmlspecialchars($row->pe_strLName.', '.$row->pe_strFName);
            $match->strAddr1           = $row->pe_strAddr1;
            $match->strAddr2           = $row->pe_strAddr2;
            $match->strCity            = $row->pe_strCity;
            $match->strState           = $row->pe_strState;
            $match->strCountry         = $row->pe_strCountry;
            $match->strZip             = $row->pe_strZip;
            $match->strPhone           = $row->pe_strPhone;
            $match->strCell            = $row->pe_strCell;
            $match->strEmail           = $row->pe_strEmail;
            $match->strEmailFormatted  = strBuildEmailLink($match->strEmail, '', false, '');
            $match->strAddress         =
                        strBuildAddress(
                                 $match->strAddr1, $match->strAddr2,   $match->strCity,
                                 $match->strState, $match->strCountry, $match->strZip,
                                 true);
            $match->dteOrigin                     = $row->dteOrigin;
            $match->dteLastUpdate                 = $row->dteLastUpdate;
            $match->ucstrFName                    = $row->strUCFName;
            $match->ucstrLName                    = $row->strUCLName;
            $match->ulstrFName                    = $row->strULFName;
            $match->ulstrLName                    = $row->strULLName;

            ++$idx;
         }
      }
   }
   

   function strClientDupWarning(&$cprograms, $strCheckBoxName, $lNumMatches, $matches){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strWarning = '';

      $cprograms->loadClientPrograms(true);
      $bTestCProgs = $cprograms->lNumCProgs > 0;
      $strWarning =
          '<br><div class="formError" style="width: 400pt;">
             <b><i>Delightful Labor</i></b> has detected '.$lNumMatches.' client'
                .($lNumMatches==1 ? '' : 's').' with similar names to your new client.<br><br>
                To prevent duplicate client entry, please verify that your
                new client <b><i>is not</i></b> in the database.<br>
            <table style="border:2px solid #ff0000; border-collapse:collapse;">
               <tr>
                  <td class="enpRptTitle" colspan="4" style="background-color: #ffd3d3;">
                     Clients already in the database
                  </td>
               </tr>
               <tr>
                  <td class="enpRptLabel" style="background-color: #ffd3d3;">
                     Client ID
                  </td>
                  <td class="enpRptLabel" style="background-color: #ffd3d3;">
                     Name
                  </td>
                  <td class="enpRptLabel" style="background-color: #ffd3d3;">
                     Address
                  </td>';
      if ($bTestCProgs){
         $strWarning .= '
               <td class="enpRptLabel" style="background-color: #ffd3d3;">
                  Enrolled In
               </td>';
      }
      $strWarning .= '</tr>';

      foreach ($matches as $match){
         $lClientID = $match->lKeyID;

         $strWarning .= '
               <tr>
                  <td class="enpRpt" style="text-align: center;background-color: #fff5f5;">'
                     .str_pad($lClientID, 6, '0', STR_PAD_LEFT).'&nbsp;'
                     .strLinkView_ClientRecord($lClientID, 'View client record', true).'
                  </td>
                  <td class="enpRpt" style="background-color: #fff5f5;">'
                     .$match->strSafeNameLF.'
                  </td>
                  <td class="enpRpt" style="background-color: #fff5f5;">'
                     .$match->strAddress.'
                  </td>';
         if ($bTestCProgs){
            $cprograms->cprogramEnrollmentsViaClientID($lClientID, $lNumEnrolls, $enrollments);
            if ($lNumEnrolls == 0){
               $strEs = 'n/a';
            }else {
               $strEs = '<ul style="margin: 0px;">';
               foreach ($enrollments as $enroll){
                  $strEs .= '<li style="margin-left: -20pt;">'.htmlspecialchars($enroll->strCProgName).'</li>';
               }
               $strEs .= '</ul>';
            }
            $strWarning .= '
                  <td class="enpRpt" style="background-color: #fff5f5;">'
                     .$strEs.'
                  </td>';
         }
         $strWarning .= '</tr>';
      }
      $strWarning .= '
            </table><br>
            <div style="width: 300pt; border: 1px solid black; padding: 3px 3px 6px 3px;
                background-color: #fff; vertical-align: middle;">
            <input type="checkbox" name="'.$strCheckBoxName.'" value="true">
            I have verified that this <b>IS NOT</b> a duplicate entry<br>
            </div>
           </div>';
      return($strWarning);
   }

   function strPeopleBizDupWarning($bPeople, $strCheckBoxName, $lNumMatches, $matches){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strWarning = '';
      $strLabel1 = $bPeople ? 'people' : 'business/organization';
      $strLabel2 = $bPeople ? 'People' : 'Biz/Org';

      $strWarning =
          '<br><div class="formError" style="width: 400pt;">
             <b><i>Delightful Labor</i></b> has detected '.$lNumMatches.' '.$strLabel1.'
                records with similar names to your new entry.<br><br>
                To prevent duplicate records, please verify that your
                new '.$strLabel1.' record <b><i>is not</i></b> in the database.<br>
            <table style="border:2px solid #ff0000; border-collapse:collapse;">
               <tr>
                  <td class="enpRptTitle" colspan="3" style="background-color: #ffd3d3;">
                     '.$strLabel2.' records in the database
                  </td>
               </tr>
               <tr>
                  <td class="enpRptLabel" style="background-color: #ffd3d3;">
                     '.$strLabel2.' ID
                  </td>
                  <td class="enpRptLabel" style="background-color: #ffd3d3;">
                     Name
                  </td>
                  <td class="enpRptLabel" style="background-color: #ffd3d3;">
                     Address
                  </td>';
      $strWarning .= '</tr>';

      foreach ($matches as $match){
         $lKeyID = $match->lKeyID;
         if ($bPeople){
            $strLink = strLinkView_PeopleRecord($lKeyID, 'View people record', true);
         }else {
            $strLink = strLinkView_BizRecord($lKeyID, 'View business/organization record', true);
         }

         $strWarning .= '
               <tr>
                  <td class="enpRpt" style="text-align: center;background-color: #fff5f5;">'
                     .str_pad($lKeyID, 6, '0', STR_PAD_LEFT).'&nbsp;'
                     .$strLink.'
                  </td>
                  <td class="enpRpt" style="background-color: #fff5f5;">'
                     .$match->strSafeNameLF.'
                  </td>
                  <td class="enpRpt" style="background-color: #fff5f5;">'
                     .$match->strAddress.'
                  </td>';
         $strWarning .= '</tr>';
      }
      $strWarning .= '
            </table><br>
            <div style="width: 300pt; border: 1px solid black; padding: 3px 3px 6px 3px;
                background-color: #fff; vertical-align: middle;">
            <input type="checkbox" name="'.$strCheckBoxName.'" value="true">
            I have verified that this <b>IS NOT</b> a duplicate entry<br>
            </div>
           </div>';
      return($strWarning);
   }




}

