<?php
/*---------------------------------------------------------------------
// Copyright (c) 2013 Database Austin
//
// Delightful Labor!
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('reports/search');
---------------------------------------------------------------------*/

  //---------------------------------------
  // comparision types
  //---------------------------------------
define('CL_SRCH_EQ',              1);     // Is Equal To
define('CL_SRCH_NEQ',             2);     // Is Not Equal To
define('CL_SRCH_GT',              3);     // Greater Than
define('CL_SRCH_GE',              4);     // Greater Than or Equal
define('CL_SRCH_LT',              5);     // Less Than
define('CL_SRCH_LE',              6);     // Less Than or Equal

   // date comparitors
define('CL_SRCH_DEQ',            11);     // Is On
define('CL_SRCH_DNEQ',           12);     // Is Not On
define('CL_SRCH_DGT',            13);     // After
define('CL_SRCH_DGE',            14);     // On or After
define('CL_SRCH_DLT',            15);     // Before
define('CL_SRCH_DLE',            16);     // Before or On

   // text-specific comparitors
define('CL_SRCH_STR_START',      17);  // starts with
define('CL_SRCH_STR_NOTSTART',   18);  // does not start with
define('CL_SRCH_STR_END',        19);  // ends with
define('CL_SRCH_STR_NOTEND',     20);  // does not end with
define('CL_SRCH_STR_CONTAINS',   21);  // contains the string
define('CL_SRCH_STR_NOTCONTAIN', 22);  // does not contain the string


  //---------------------------------------
  // comparision values
  //---------------------------------------
define('CL_SRCH_CHK_YES', 3);   // Yes
define('CL_SRCH_CHK_NO',  4);   // No


  //---------------------------------------
  // booleans
  //---------------------------------------
define('CL_SRCH_BOOL_AND', 5);   // And
define('CL_SRCH_BOOL_OR',  6);   // Or
define('CL_SRCH_BOOL_NOT', 7);   // Not

  //---------------------------------------
  // string equivalents of the comparitors
  //---------------------------------------
define('CS_SRCH_EQ',             'Is Equal To');
define('CS_SRCH_NEQ',            'Is Not Equal To');
define('CS_SRCH_GT',             'Greater Than');
define('CS_SRCH_GE',             'Greater Than or Equal');
define('CS_SRCH_LT',             'Less Than');
define('CS_SRCH_LE',             'Less Than or Equal');

define('CS_SRCH_DEQ',            'Is On');
define('CS_SRCH_DNEQ',           'Is Not On');
define('CS_SRCH_DGT',            'After');
define('CS_SRCH_DGE',            'On or After');
define('CS_SRCH_DLT',            'Before');
define('CS_SRCH_DLE',            'On or Before');

define('CS_SRCH_STR_START',      'Starts With');
define('CS_SRCH_STR_NOTSTART',   'Doesn\'t Start With');
define('CS_SRCH_STR_END',        'Ends With');
define('CS_SRCH_STR_NOTEND',     'Doesn\'t End With');
define('CS_SRCH_STR_CONTAINS',   'Contains');
define('CS_SRCH_STR_NOTCONTAIN', 'Does Not Contain');

define('CS_SRCH_CHK_YES',        'Yes');
define('CS_SRCH_CHK_NO',         'No');

   // psuedo-table IDs for system tables
define('CL_STID_CLIENT',     -1);   // client_records
define('CL_STID_GIFTS',      -2);   // gifts
define('CL_STID_PEOPLEBIZ',  -3);   // people/business
define('CL_STID_PEOPLE',     -4);   // people
define('CL_STID_BIZ',        -5);   // business



   function crptLabelSearchType($enumSearchType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      switch ($enumSearchType){

         case CL_SRCH_EQ  :   $strOut = CS_SRCH_EQ ; break;
         case CL_SRCH_NEQ :   $strOut = CS_SRCH_NEQ; break;
         case CL_SRCH_GT  :   $strOut = CS_SRCH_GT ; break;
         case CL_SRCH_GE  :   $strOut = CS_SRCH_GE ; break;
         case CL_SRCH_LT  :   $strOut = CS_SRCH_LT ; break;
         case CL_SRCH_LE  :   $strOut = CS_SRCH_LE ; break;

            // date comparitors
         case CL_SRCH_DEQ   : $strOut = CS_SRCH_DEQ;   break;
         case CL_SRCH_DNEQ  : $strOut = CS_SRCH_DNEQ;  break;
         case CL_SRCH_DGT   : $strOut = CS_SRCH_DGT;   break;
         case CL_SRCH_DGE   : $strOut = CS_SRCH_DGE;   break;
         case CL_SRCH_DLT   : $strOut = CS_SRCH_DLT;   break;
         case CL_SRCH_DLE   : $strOut = CS_SRCH_DLE;   break;

            // text-specific comparitors
         case CL_SRCH_STR_START      :  $strOut = CS_SRCH_STR_START;      break;
         case CL_SRCH_STR_NOTSTART   :  $strOut = CS_SRCH_STR_NOTSTART;   break;
         case CL_SRCH_STR_END        :  $strOut = CS_SRCH_STR_END;        break;
         case CL_SRCH_STR_NOTEND     :  $strOut = CS_SRCH_STR_NOTEND;     break;
         case CL_SRCH_STR_CONTAINS   :  $strOut = CS_SRCH_STR_CONTAINS;   break;
         case CL_SRCH_STR_NOTCONTAIN :  $strOut = CS_SRCH_STR_NOTCONTAIN; break;
         default:
            screamForHelp($enumSearchType.': unknown search comparison<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
      return($strOut);
   }

   function bSupportedCReportFieldType($enumType){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      return(! ($enumType==CS_FT_DDLMULTI ||
                $enumType==CS_FT_LOG      ||
                $enumType==CS_FT_HEADING    ));
   }

   function crptFieldPropsParentTable($lTableID, $strFN,
                &$strTableName, &$enumFType, &$strUserFN, &$enumAttachType){
   //---------------------------------------------------------------------
   // If a field is from a parent table (client, gifts, people, etc),
   // the field properties must be manually set.
   //---------------------------------------------------------------------
      switch ($lTableID){
         case CL_STID_CLIENT:
            $strTableName = 'Client Table';
            $enumAttachType = null;
            crptFieldPropsClient($strFN, $enumFType, $strUserFN);
            break;
         default:
            screamForHelp($lTableID.': invalid table ID<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function crptFieldPropsClient($strFN, &$enumFType, &$strUserFN){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gclsChapterVoc;

      switch ($strFN){
         case 'cr_lKeyID'        : $enumFType = CS_FT_ID          ; $strUserFN = 'Client ID'              ; break;
         case 'cr_dteEnrollment' : $enumFType = CS_FT_DATE        ; $strUserFN = 'Date Enrolled'          ; break;
         case 'cr_strLName'      : $enumFType = CS_FT_TEXT        ; $strUserFN = 'Last Name'              ; break;
         case 'cr_strFName'      : $enumFType = CS_FT_TEXT        ; $strUserFN = 'First Name'             ; break;
         case 'cr_strMName'      : $enumFType = CS_FT_TEXT        ; $strUserFN = 'Middle Name'            ; break;
         case 'cr_dteBirth'      : $enumFType = CS_FT_DATE        ; $strUserFN = 'Birth Date'             ; break;
         case 'cr_enumGender'    : $enumFType = CS_FT_TEXT        ; $strUserFN = 'Gender'                 ; break;
         case 'cr_strAddr1'      : $enumFType = CS_FT_TEXT        ; $strUserFN = 'Address 1'              ; break;
         case 'cr_strAddr2'      : $enumFType = CS_FT_TEXT        ; $strUserFN = 'Address 2'              ; break;
         case 'cr_strCity'       : $enumFType = CS_FT_TEXT        ; $strUserFN = 'City'                   ; break;
         case 'cr_strState'      : $enumFType = CS_FT_TEXT        ; $strUserFN = $gclsChapterVoc->vocState; break;
         case 'cr_strCountry'    : $enumFType = CS_FT_TEXT        ; $strUserFN = 'Country'                ; break;
         case 'cr_strZip'        : $enumFType = CS_FT_TEXT        ; $strUserFN = $gclsChapterVoc->vocZip  ; break;
         case 'cr_strPhone'      : $enumFType = CS_FT_TEXT        ; $strUserFN = 'Phone'                  ; break;
         case 'cr_strCell'       : $enumFType = CS_FT_TEXT        ; $strUserFN = 'Cell'                   ; break;
         case 'cr_strEmail'      : $enumFType = CS_FT_TEXT        ; $strUserFN = 'Email'                  ; break;
         case 'cr_lLocationID'   : $enumFType = CS_FT_DDL_SPECIAL ; $strUserFN = 'Location'               ; break;
         case 'cr_lStatusCatID'  : $enumFType = CS_FT_DDL_SPECIAL ; $strUserFN = 'Status Category'        ; break;
         case 'cr_lVocID'        : $enumFType = CS_FT_DDL_SPECIAL ; $strUserFN = 'Vocabulary'             ; break;
         case 'cr_lMaxSponsors'  : $enumFType = CS_FT_INTEGER     ; $strUserFN = 'Max # of Sponsors'      ; break;
         case 'cr_strBio'        : $enumFType = CS_FT_TEXT        ; $strUserFN = 'Bio'                    ; break;

         default:
            screamForHelp($strFN.': invalid field ID<br>error on line  <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }



/*
   //---------------------------------------------
   // sponsor and other special search types
   //---------------------------------------------
define('CL_SRCH_SPON_ALL',               1);    // find all sponsors
define('CL_SRCH_SPON_VIA_FAC',           2);    // find all sponsors at a facility
define('CL_SRCH_PEOPLEGIFT_CUM',         3);    // cumulative donation
define('CL_SRCH_PEOPLEGIFT_DEADBEAT',    4);    // deadbeat donor search
define('CL_SRCH_PEOPLEGIFT_NOGIFTSINCE', 5);    // no donations since

*/
?>