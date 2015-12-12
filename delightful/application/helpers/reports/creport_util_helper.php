<?php
/*---------------------------------------------------------------------
// Delightful Labor!
//
// copyright (c) 2014 by Database Austin
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
---------------------------------------------------------------------
      $this->load->helper('reports/creport_util');
*/

function loadCReportTypeArray(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $cRptTypes = array();
/*
   cRptTypeEntry($cRptTypes[0], CE_CRPT_GIFTS,           strXlateCRptType(CE_CRPT_GIFTS    ), false);
   cRptTypeEntry($cRptTypes[1], CE_CRPT_GIFTS_HON,       strXlateCRptType(CE_CRPT_GIFTS_HON), false);
   cRptTypeEntry($cRptTypes[2], CE_CRPT_GIFTS_MEM,       strXlateCRptType(CE_CRPT_GIFTS_MEM), false);
   cRptTypeEntry($cRptTypes[3], CE_CRPT_GIFTS_PER,       strXlateCRptType(CE_CRPT_GIFTS_PER), true);

   cRptTypeEntry($cRptTypes[4], CE_CRPT_CLIENTS,         strXlateCRptType(CE_CRPT_CLIENTS        ), false);
   cRptTypeEntry($cRptTypes[5], CE_CRPT_CLIENTS_CPROG,   strXlateCRptType(CE_CRPT_CLIENTS_CPROG  ), true);
   cRptTypeEntry($cRptTypes[6], CE_CRPT_CLIENTS_SPON,    strXlateCRptType(CE_CRPT_CLIENTS_SPON   ), true);
   cRptTypeEntry($cRptTypes[7], CE_CRPT_CLIENTS_SPONPAY, strXlateCRptType(CE_CRPT_CLIENTS_SPONPAY), true);
*/

//   buildCRptClass(CE_CRPT_GIFTS,           $cRptTypes[CE_CRPT_GIFTS          ]);
//   buildCRptClass(CE_CRPT_GIFTS_HON,       $cRptTypes[CE_CRPT_GIFTS_HON      ]);
//   buildCRptClass(CE_CRPT_GIFTS_MEM,       $cRptTypes[CE_CRPT_GIFTS_MEM      ]);
//   buildCRptClass(CE_CRPT_GIFTS_PER,       $cRptTypes[CE_CRPT_GIFTS_PER      ]);                                                        
   buildCRptClass(CE_CRPT_CLIENTS,         $cRptTypes[CE_CRPT_CLIENTS        ]);
//   buildCRptClass(CE_CRPT_PEOPLE,          $cRptTypes[CE_CRPT_PEOPLE        ]);
//   buildCRptClass(CE_CRPT_CLIENTS_CPROG,   $cRptTypes[CE_CRPT_CLIENTS_CPROG  ]);
//   buildCRptClass(CE_CRPT_CLIENTS_SPON,    $cRptTypes[CE_CRPT_CLIENTS_SPON   ]);
//   buildCRptClass(CE_CRPT_CLIENTS_SPONPAY, $cRptTypes[CE_CRPT_CLIENTS_SPONPAY]);
   
   return($cRptTypes);
}

function buildCRptClass($enumCRpt, &$cCRpt){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $cCRpt = new stdClass;
   $cCRpt->bCProg = false;
   $cCRpt->enumCRpt   = $enumCRpt;
   
   switch ($enumCRpt){

         // gifts
      case CE_CRPT_GIFTS:
         $cCRpt->strLabel   = 'Gifts (work in progress)';
         $cCRpt->bIncludeUF = false;
         $cCRpt->enumBaseRptType = CENUM_CONTEXT_GIFT;
         break;

      case CE_CRPT_GIFTS_HON:
         $cCRpt->strLabel   = 'Gifts/Honorariums (work in progress)';
         $cCRpt->bIncludeUF = false;
         $cCRpt->enumBaseRptType = CENUM_CONTEXT_GIFT;
         break;

      case CE_CRPT_GIFTS_MEM:
         $cCRpt->strLabel   = 'Gifts/Memorials (work in progress)';
         $cCRpt->bIncludeUF = false;
         $cCRpt->enumBaseRptType = CENUM_CONTEXT_GIFT;
         break;

      case CE_CRPT_GIFTS_PER:
         $cCRpt->strLabel   = 'Gifts/Personalized Tables (work in progress)';
         $cCRpt->bIncludeUF = true;
         $cCRpt->enumBaseRptType = CENUM_CONTEXT_GIFT;
         break;
         
         // people
      case CE_CRPT_PEOPLE:
         $cCRpt->strLabel   = 'People';
         $cCRpt->bIncludeUF = true;
         $cCRpt->enumBaseRptType = CENUM_CONTEXT_PEOPLE;
         break;

         // clients
      case CE_CRPT_CLIENTS:
         $cCRpt->strLabel   = 'Clients';
         $cCRpt->bIncludeUF = true;
         $cCRpt->enumBaseRptType = CENUM_CONTEXT_CLIENT;
         break;
         
      case CE_CRPT_CLIENTS_CPROG:
         $cCRpt->strLabel   = 'Clients/Client Programs (work in progress)';
         $cCRpt->bIncludeUF = true;
         $cCRpt->bCProg     = true;
         $cCRpt->enumBaseRptType = CENUM_CONTEXT_CLIENT;
         break;
         
      case CE_CRPT_CLIENTS_SPON:
         $cCRpt->strLabel = 'Clients/Sponsors (work in progress)';
         $cCRpt->bIncludeUF = true;
         $cCRpt->enumBaseRptType = CENUM_CONTEXT_CLIENT;
         break;
         
      case CE_CRPT_CLIENTS_SPONPAY:
         $cCRpt->strLabel = 'Clients/Sponsors/Payments (work in progress)';
         $cCRpt->bIncludeUF = true;
         $cCRpt->enumBaseRptType = CENUM_CONTEXT_CLIENT;
         break;
         
      default:
         screamForHelp($enumCRpt.': unknow custom report type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         break;
   }
}

/*

function strXlateCRptType($enumCRpt){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   switch ($enumCRpt){

      case CE_CRPT_GIFTS    :         $strOut = 'Gifts';                       break;
      case CE_CRPT_GIFTS_HON:         $strOut = 'Gifts/Honorariums';           break;
      case CE_CRPT_GIFTS_MEM:         $strOut = 'Gifts/Memorials';             break;
      case CE_CRPT_GIFTS_PER:         $strOut = 'Gifts/Personalized Tables';   break;

      case CE_CRPT_CLIENTS        :   $strOut = 'Clients';                     break;
      case CE_CRPT_CLIENTS_CPROG  :   $strOut = 'Clients/Client Programs';     break;
      case CE_CRPT_CLIENTS_SPON   :   $strOut = 'Clients/Sponsors';            break;
      case CE_CRPT_CLIENTS_SPONPAY:   $strOut = 'Clients/Sponsors/Payments';   break;
      default:
         screamForHelp($enumCRpt.': unknow custom report type<br>error on line <b> -- '.__LINE__.' --</b>,<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         break;
   }
   return($strOut);
}

function cRptTypeEntry(&$cType, $enumCRpt, $strLabel, $bIncludePersonalized){
   $cType = new stdClass;
   $cType->enumCRpt             = $enumCRpt;
   $cType->strLabel             = $strLabel;
   $cType->bIncludePersonalized = $bIncludePersonalized;
   $cType->tables               = cRptTablesViaEnum($enumCRpt);
}

function cRptTablesViaEnum($enumCRpt){

}
*/

function strCRptTypesDDL($cRptTypes, $bAddBlank=false, $enumMatch=null){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
//   $strOut = '<select name="'.$strDDLName.'">'."\n";
   $strOut = '';
   if ($bAddBlank) $strOut .= '<option value="-1">&nbsp;</option>'."\n";
   foreach ($cRptTypes as $cRpt){
      $strOut .= '<option value="'.$cRpt->enumCRpt.'" '.($enumMatch==$cRpt->enumCRpt? 'SELECTED' : '')
                       .' >'.$cRpt->strLabel.'</option>'."\n";
   }
//   $strOut .= '</select>'."\n";
   return($strOut);
}








