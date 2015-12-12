<?php



   $params = array('enumStyle' => 'terse', 'clsRpt');
   $clsRpt = new generic_rpt($params);
   $clsRpt->strWidthLabel = '130pt';

   $attFields = new stdClass;
   $attFields->lTableWidth  = 900;
   $attFields->bStartOpen   = false;

   $attributes = new stdClass;
   $attributes->lTableWidth  = 1200;
   $attributes->bStartOpen   = false;



   foreach ($cprogs as $cp){
      $lCPID = $cp->lKeyID;
      if ($cp->bHidden){
         $strLabel = '<font style="color: #aaa;">'.htmlspecialchars($cp->strProgramName).'</font>';
      }else {
         $strLabel = htmlspecialchars($cp->strProgramName);
      }

      $attributes->divID        = 'clientServices'.$lCPID;
      $attributes->divImageID   = 'clientServicesDivImg'.$lCPID;
      $attributes->bCloseDiv = false;
      openBlock($strLabel, '', $attributes);

      echoT($clsRpt->openReport());
      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Client Program ID:', '', 'vertical-align: bottom;')
         .$clsRpt->writeCell(str_pad($cp->lKeyID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('lEnrollmentTableID:', '', 'vertical-align: bottom;')
         .$clsRpt->writeCell($cp->lEnrollmentTableID)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('lAttendanceTableID:', '', 'vertical-align: bottom;')
         .$clsRpt->writeCell($cp->lAttendanceTableID)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('strEnrollmentTable:', '', 'vertical-align: bottom;')
         .$clsRpt->writeCell($cp->strEnrollmentTable)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('strETableFNPrefix:', '', 'vertical-align: bottom;')
         .$clsRpt->writeCell($cp->strETableFNPrefix)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('strAttendanceTable:', '', 'vertical-align: bottom;')
         .$clsRpt->writeCell($cp->strAttendanceTable)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('strATableFNPrefix:', '', 'vertical-align: bottom;')
         .$clsRpt->writeCell($cp->strATableFNPrefix)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('lActivityFieldID:', '', 'vertical-align: bottom;')
         .$clsRpt->writeCell($cp->lActivityFieldID)
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('strActivityFN:', '', 'vertical-align: bottom;')
         .$clsRpt->writeCell($cp->strActivityFN)
         .$clsRpt->closeRow  ());

      echoT($clsRpt->closeReport());


         // Fields
      $attFields->divID        = 'cpFields'.$lCPID;
      $attFields->divImageID   = 'cpFieldsDivImg'.$lCPID;
      $attFields->lTableWidth  = 800;

      $attFields->bCloseDiv = false;
      openBlock('Fields', '', $attFields);

      echoT($clsRpt->openReport());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Attendance Fields:', '140', 'vertical-align: top;')
         .$clsRpt->writeCell(strCPFields($cp->afields).'<br><br>')
         .$clsRpt->closeRow  ());

      echoT(
          $clsRpt->openRow   ()
         .$clsRpt->writeLabel('Enrollment Fields:', '140', 'vertical-align: top;')
         .$clsRpt->writeCell(strCPFields($cp->efields))
         .$clsRpt->closeRow  ());


      echoT($clsRpt->closeReport());


      $attFields->bCloseDiv = true;
      closeBlock($attFields);


      $attributes->bCloseDiv = true;
      closeBlock($attributes);

   }

   function strCPFields($cpfields){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '
            <table border="1">
               <tr>
                  <td style="background-color: #ccc; width: 40pt;">Field ID</td>
                  <td style="background-color: #ccc; width: 200pt;">Field Name</td>
                  <td style="background-color: #ccc;">Internal Name</td>
                  <td style="background-color: #ccc; width: 60pt;">Type</td>
               </tr>';

         foreach ($cpfields as $cpfield){
            $strOut .= '
               <tr>
                  <td style="text-align: center;">'.$cpfield->pff_lKeyID.'</td>
                  <td  style="width: 200pt;">'.htmlspecialchars($cpfield->pff_strFieldNameUser).'</td>
                  <td >'.htmlspecialchars($cpfield->strFieldNameInternal).'</td>
                  <td >'.htmlspecialchars($cpfield->enumFieldType).'</td>
               </tr>';
         }
         $strOut .= '</table>';
      return($strOut);
   }

/* -------------------------------------
echo('<br><br><font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$cprogs   <pre>');
echo(htmlspecialchars( print_r($cprogs, true))); echo('</pre></font><br>');
// ------------------------------------- */

