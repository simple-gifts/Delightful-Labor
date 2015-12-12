<br>

<?php

   echoT(form_open($search->formLink));
   
   if (isset($search->formHidden)){
      foreach ($search->formHidden as $key=>$value){
         echoT(form_hidden($key, $value));   
      }
   }

   searchPeopleBizTableForm(!$search->bBiz, $search);   
 

   function searchPeopleBizTableForm($bPeople, $search){
   //---------------------------------------------------------------------
   // When setting up this form....
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
           '<table class="enpRptC" >
               <tr>
                  <td colspan="2"  class="enpRptTitle">'
                     .$search->strLegendLabel.'
                  </td>
               </tr>
               <tr>
                  <td class="enpRpt" align="right" width="'.$search->lSearchTableWidth.'">
                     First few letters of <b>'.($bPeople ? 'the last name' : 'the business name').'</b>:
                  </td>
                  <td class="enpRpt">
                     <input type="text" size="5" maxlength="20" name="txtSearch" value="" id="txtSearchID">
                     <i><small>(name must be in the '.($bPeople ? 'People' : 'Business').' table)</small></i>'
                     .form_error('txtSearch').'
                  </td>
               </tr>

               <tr>
                 <td class="enpRpt" align="center" colspan="2">
                    <input type="submit"
                          name="cmdAdd"
                          value="'.$search->strButtonLabel.'"
                          onclick="this.disabled=1; this.form.submit();"
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
   
   
   
   
   
   
   
/*   
   echoT(form_hidden('HMType', $search->enumHMType));
   
      // if mail contact, include memorial ID
   if ($search->enumHMType=='mc'){
      echoT(form_hidden('HMID', $search->lHMID));
   }

*/

?>