<?php
/*---------------------------------------------------------------------
 copyright (c) 2013-2014 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.

  arrayCopy                   (array $array)
  dumpRequest                 ($bDumpRequestArray=true, $bDumpSessionArray=true, $bDumpServerArray=false)
  lDecryptID                  ($strKeyID, $Seed)
  strEncryptID                ($lKeyID, $Seed)
  hex_2_bin                   ($strHex)

  strPrepNow                  ()
  strPrepDateTime             ($lTimeStamp)
  strPrepDate                 ($lTimeStamp)
  make_seed                   ()

  strPrepStr                  ($myStr)
  strEscMysqlQuote            ($myStr)
  echoT                       ($strOut)
  strLoad_REQ                 ($strReqVarName, $bTrim=true, $bHideErr=false)

  dteServerAdjusted           ($dteOriginal)
  dteServerAdjusted_C2S       ($dteOriginal)

  screamForHelpSQL            ($sqlStr)
  screamForHelp               ($strMsg)
  whereAmI                    ()
  traceFilepath               ($strFile, $strLine)

  openWrapperTable            ($bShowBorder=false)
  closeWrapperTable           ()

  strNormalizeCase            ($strTest)

  bFileExtensionOkayForUpload ($strFileName, $bImage, $bVideo, $bGeneral, &$strExtension){){

  strBuildAddress             ($strAddress1, $strAddress2,
  strXlateCurrency            ($enumCurrency)

  strUpFirst                  ($strTest)
  strQuoteFix                 ($str)

  strPhoneCell                ($strPhone, $strCell)
  strXlateContext             ($enumContextType, $bCap=true)
  strBuildName                ($bLastFirst, $strTitle, $strPreferred, $strFName, $strLName, $strMName)
  strBuildEmailLink           ($strTestEmail, $strLabel, $bIncludeLabIfBlank, $strClassTag='')

  setOnFormError              (&$displayData, $strErr='');

  strImgTagBlackLine          ($lWidth, $lHeight)
  clearReturnPaths            ()

  setCFormErrorMessage        ($strFieldName, $strErrorMessage)
  phpVersionTestDL            ()

  dteMySQLDate2Unix           ($strMySQLDate)
---------------------------------------------------------------------*/

function arrayCopy( array $array ) {
//---------------------------------------------------------------------
// thanks to kolkabes at googlemail dot com
// http://php.net/manual/en/ref.array.php
//---------------------------------------------------------------------
   $result = array();
   foreach( $array as $key => $val ) {
      if( is_array( $val ) ) {
          $result[$key] = arrayCopy( $val );
      } elseif ( is_object( $val ) ) {
          $result[$key] = clone $val;
      } else {
          $result[$key] = $val;
      }
   }
   return $result;
}

function dumpRequest($bDumpRequestArray=true, $bDumpSessionArray=true, $bDumpServerArray=false) {
//-------------------------------------------------------------------
// developer utility - dump useful information about the
// request array, session array, and server array
//-------------------------------------------------------------------
   if ($bDumpRequestArray) {
      echoT('<b>REQUEST<br></b><pre>'); print_r($_REQUEST); echoT('</pre>');
//      dump_REQUEST_array();
   }

   if ($bDumpSessionArray) {
      echoT('<b>SESSION<br></b><pre>'); print_r($_SESSION); echoT('</pre>');
//      dump_SESSION_array();
   }

   if ($bDumpServerArray) {
      echoT('<b>SERVER<br></b><pre>'); print_r($_SERVER); echoT('</pre>');
//      dump_SERVER_array();
   }

   @ob_flush(); @flush();
}

function dump_REQUEST_array() {
//-------------------------------------------------------------------
// dump info about the request array
//-------------------------------------------------------------------
   echo('<br><u><b>REQUEST</b></u><br>');
   echo('count='.count($_REQUEST)."<br>\n");
   while (list ($key, $val) = each ($_REQUEST)) {
      if (is_array($val)) {
         echo('<b>'.$key.'</b> (array)<br>'.nl2br(print_r($val, true)));
      }else {
         echo "<b>$key</b> = ".htmlspecialchars($val)."<br>\n";
      }
   }
   reset($_REQUEST);  // resets the array pointer
}

function dump_SESSION_array() {
//-------------------------------------------------------------------
//
//-------------------------------------------------------------------

      //--------------------------------------------------------------
      // if the program hasn't started the session yet, start it here
      // or it will not be possible to dump the session variables
      //--------------------------------------------------------------
   if (!defined(session_id())){
      @session_start();
   }

   echo('<br><u><b>SESSION</u></b><br>');
   while (list ($key, $val) = each ($_SESSION)) {
      if (is_array($val)) {
         echo(nl2br(print_r($val, true)));
      }else {
         echo "<b>$key</b> = ".htmlspecialchars($val)."<br>\n";
      }
   }
   reset($_SESSION);
}

function dump_SERVER_array() {
//-------------------------------------------------------------------
//
//-------------------------------------------------------------------

   echo('<br><u><b>SERVER</b></u><br>');
   while (list ($key, $val) = each ($_SERVER)) {
      if (is_array($val)) {
         echo(nl2br(print_r($val, true)));
      }else {
         echo "<b>$key</b> = ".htmlspecialchars($val)."<br>\n";
      }
   }
   reset($_SERVER);
}

function echoT($strOut) {
//---------------------------------------------------------------
// trim each line of output while preserving line breaks
//---------------------------------------------------------------
//   $strOutArray = explode("\n", $strOut);
//   $strOut = '';
//   foreach ($strOutArray as $strLine){
//      echo(trim($strLine)."\n");
//   }
   echo(strTrimAllLines($strOut));
}

function strTrimAllLines($strToTrim){
//---------------------------------------------------------------
// trim each line of output while preserving line breaks
//---------------------------------------------------------------
   $strOut = '';
   $strOutArray = explode("\n", $strToTrim);
   foreach ($strOutArray as $strLine){
      $strOut .= trim($strLine)."\n";
   }
   return($strOut);
}

function lDecryptID($strKeyID, $Seed) {
//------------------------------------------------------------------
//  if the mcrypt package is not available, the original key is
//  returned as an integer.
//------------------------------------------------------------------
//echo('CB_ENABLE_MCRYPT='.(CB_ENABLE_MCRYPT?'true':'false').'<br>');
   if (CB_ENABLE_MCRYPT) {
         // keys converted to hex equivalen
      $strKeyID = hex_2_bin($strKeyID);
      srand(1);
      $iv = mcrypt_create_iv (mcrypt_get_iv_size (ENCRYPT_MODE, MCRYPT_MODE_ECB), MCRYPT_RAND);
      $key = $Seed;
      $strHold = mcrypt_decrypt (ENCRYPT_MODE, $key, $strKeyID, MCRYPT_MODE_ECB, $iv);
      return((integer)$strHold);
   }else {
      return((integer)$strKeyID);
   }
}

function strEncryptID($lKeyID, $Seed) {
//------------------------------------------------------------------
//  if the mcrypt package is not available, the key is returned
//  as a string. It is strongly recommended that the mcrypt
//  package be installed and keys encrypted when sent in the
//  http stream.
//------------------------------------------------------------------
   if (CB_ENABLE_MCRYPT) {
      srand(1);
      $iv = mcrypt_create_iv (mcrypt_get_iv_size (ENCRYPT_MODE, MCRYPT_MODE_ECB), MCRYPT_RAND);
      $key = $Seed;
      $text = (string)$lKeyID;
      $cryptText = mcrypt_encrypt (ENCRYPT_MODE, $key, $text, MCRYPT_MODE_ECB, $iv);
      return(bin2hex($cryptText));
   }else {
      return((string)$lKeyID);
   }
}

function hex_2_bin($strHex){
//-------------------------------------------------------------------------
//
//-------------------------------------------------------------------------
   $strOut = '';

   for ($idx=0; $idx<strlen($strHex); $idx+=2 ) {
      $strOut .= chr(hexdec(substr($strHex, $idx, 2)));
   }
   return($strOut);
}

function strPrepNow() {
//-------------------------------------------------------------------------
//  return the current date/time in mySQL format
//-------------------------------------------------------------------------
   return(strPrepStr(date('Y-m-d H:i:s')));
}

function strPrepDateTime($lTimeStamp) {
//-------------------------------------------------------------------------
//  return the specified date/time in mySQL format
//-------------------------------------------------------------------------
   if (is_null($lTimeStamp)) {
      return('NULL');
   }else {
      return(strPrepStr(date('Y-m-d H:i:s', $lTimeStamp)));
   }
}

function strPrepDate($lTimeStamp) {
//-------------------------------------------------------------------------
//  return the specified date only in mySQL format
//-------------------------------------------------------------------------
   if (is_null($lTimeStamp)) {
      return('NULL');
   }else {
      return(strPrepStr(date('Y-m-d', $lTimeStamp)));
   }
}

function strPrepTime($lTimeStamp) {
//-------------------------------------------------------------------------
//  return the specified time in mySQL format
//-------------------------------------------------------------------------
   if (is_null($lTimeStamp)) {
      return('NULL');
   }else {
      return(strPrepStr(date('H:i:s', $lTimeStamp)));
   }
}

function make_seed() {
//-------------------------------------------------------------------------
// seed with microseconds - from the php manual
// example: mt_srand(make_seed());
//-------------------------------------------------------------------------
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

function strPrepStr($myStr, $lMaxLen=null, $strQuote="'") {
//-------------------------------------------------------------------------
// part of the defense against the dark arts
//-------------------------------------------------------------------------
//   return(xss_clean('\'' . addslashes($myStr) . '\''));
   if (!is_null($lMaxLen)){
      if (strlen($myStr) > $lMaxLen){
         $myStr = substr($myStr, 0, $lMaxLen);
      }
   }
   return($strQuote.addslashes($myStr).$strQuote);
}

function strEscMysqlQuote($myStr){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   return(str_replace('`', '\'', $myStr));
}

function screamForHelpSQL($sqlStr) {
//----------------------------------------------------------------------
//
//----------------------------------------------------------------------
   global $gbDev;
   if ($gbDev){
      echoT('<pre class="debug"><br>'.$sqlStr.'</pre><br>');
   }
}

function screamForHelp($strMsg, $bRecNotFound=false, $strUserErr='') {
//----------------------------------------------------------------------
//
//----------------------------------------------------------------------
   global $gbDev;
   if ($bRecNotFound){
      $strUserErr = 'The record you requested was not found. It may have been deleted.<br>'.$strUserErr;
   }else {
      if ($strUserErr==''){
         $strUserErr = 'This page is currently under construction.';
      }
   }

   echoT('
      <table class="error">
         <tr>
            <td class="error">
               <img src="'.DL_IMAGEPATH.'/misc/errorIcon.png" title="Error!" border="0"><br>'
               .$strUserErr.'
            </td>
         </tr>
      </table><br><br>');

   if ($gbDev){
      whereAmI();

      echoT(
          '<blockquote><font face="Courier New">
           <font color="RED">
              <b>An error has occurred while processing your form!<br><br>

              This is an unexpected situation and should be reviewed by technical support.</b><br><br>
           </font>

           We apologize for this inconvenience.<br><br>
           <br>');

         //-------------------------------------------------------
         // include key version info for users to cut and paste
         //-------------------------------------------------------
      echoT('<table width="80%" class="errorReport">');

         //------------------------------
         // reported error
         //------------------------------
      echoT(
         '<tr>
              <td class="errorReport" nowrap valign="top">
                 <b>Reported Error:</b>
              </td>
              <td class="errorReport">'
                 .$strMsg.'
              </td>
          </tr>');

         //------------------------------
         // mysql version
         //------------------------------
      $CI = &get_instance();
      echoT(
         '<tr>
              <td class="errorReport" nowrap valign="top">
                 <b>mysql version:</b>
              </td>
              <td class="errorReport">'
                 .mysqli_get_server_info($CI->db->conn_id).'
              </td>
          </tr>');

         //------------------------------
         // php version
         //------------------------------
      echoT(
         '<tr>
              <td class="errorReport" nowrap valign="top">
                 <b>php version:</b>
              </td>
              <td class="errorReport">'
                 .phpversion().'
              </td>
          </tr>');

         //------------------------------
         // http server version
         //------------------------------
      echoT(
         '<tr>
              <td class="errorReport" nowrap valign="top">
                 <b>http server:</b>
              </td>
              <td class="errorReport">'
                 .$_SERVER['SERVER_SOFTWARE'].'
              </td>
          </tr>');

         //------------------------------
         // http user agent
         //------------------------------
      echoT(
         '<tr>
              <td class="errorReport" nowrap valign="top">
                 <b>http user agent:</b>
              </td>
              <td class="errorReport">'
                 .$_SERVER['HTTP_USER_AGENT'].'
              </td>
          </tr>');

         //------------------------------
         // request URI
         //------------------------------
      echoT(
         '<tr>
              <td class="errorReport" nowrap valign="top">
                 <b>request URI:</b>
              </td>
              <td class="errorReport">'
                 .htmlspecialchars($_SERVER['REQUEST_URI']).'
              </td>
          </tr>');

         //------------------------------
         // code version
         //------------------------------
      echoT(
         '<tr>
              <td class="errorReport" nowrap valign="top">
                 <b>Code Version:</b>
              </td>
              <td class="errorReport">'
                 .number_format(CSNG_CODE_VERSION,3).'
              </td>
          </tr>');

         //------------------------------
         // schema level
         //------------------------------
      echoT(
         '<tr>
              <td class="errorReport" nowrap valign="top">
                 <b>DB Schema Level:</b>
              </td>
              <td class="errorReport">'
                 .number_format(CSNG_DB_VERSION,3).'
              </td>
          </tr>');

      echoT('</table>');
      echoT('</blockquote><br>');

      echoT('<pre>');
      debug_print_backtrace();
      echoT('</pre>');
   }
   exit;
}

function dteServerAdjusted($dteOriginal){
//------------------------------------------------------------------
// modify the time by the server offset
//
// for example, time() returns the server time, dteServerAdjusted(time())
// returns the user's local time
//
// This routine adjusts for Server -> Client
//------------------------------------------------------------------
   return ($dteOriginal+CL_SERVEROFFSET_SEC);
}

function dteServerAdjusted_C2S($dteOriginal){
//------------------------------------------------------------------
// modify the time by the server offset
//
// for example, time() returns the server time, dteServerAdjusted(time())
// returns the user's local time
//
// This routine adjusts for Client -> Server
//------------------------------------------------------------------
   return ($dteOriginal-CL_SERVEROFFSET_SEC);
}

function whereAmI(){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $objTrace = debug_backtrace();
   echo('<table border="1">'."\n");
   echo(
       '<tr>'."\n"
         .'<td align="center" bgcolor="#eeeeee"><b>Function</b></td>'."\n"
         .'<td align="center" bgcolor="#eeeeee"><b>Line</b></td>'."\n"
         .'<td align="center" bgcolor="#eeeeee"><b>File</b></td>'."\n"
      .'</tr>'."\n");

   foreach ($objTrace as $objTraceLine) {
      echo('<tr>'."\n");

      echo('<td bgcolor="#ffffff">'.@$objTraceLine['function'].'</td>'."\n");
      echo('<td bgcolor="#ffffff">'.@$objTraceLine['line'].'</td>'."\n");
      echo('<td bgcolor="#ffffff">'.@$objTraceLine['file'].'</td>'."\n");

      echo('</tr>'."\n");
   }
   echo('</table><br><br>'."\n");

/*----------------------  cool, but lots of output
   debug_print_backtrace();
------------------*/
}

function openWrapperTable($bShowBorder=false){
//---------------------------------------------------------
// open wrapper table
//---------------------------------------------------------
   echo('<table width="100%" '.($bShowBorder ? 'border="1" ' : '').'>'
      .'<tr>'
         .'<td width="100%" align="center">'."\n");
}

function closeWrapperTable(){
//---------------------------------------------------------
// close wrapper table
//---------------------------------------------------------
   echo('</td></tr></table>'."\n");
}

function bSQLError($strNote='', $sqlStr='#unknown#') {
//-------------------------------------------------------------------
// return true if SQL error and print an error message
//-------------------------------------------------------------------
   global $gbDev;
   $strHold = mysql_error();
   $iLen = strlen($strHold);
   if ($iLen>0 && $gbDev) {
      echo("\n<font color=\"red\">\n****<br>\n**** mysql error: ".$strHold."\n<br>****</font><br>\n");
      if (strlen($strNote)>0){
         echo($strNote."<br>\n");
      }
      if (CB_SHOWSQL_ON_ERROR) {
         echo("\nsqlStr=<br>".nl2br($sqlStr)."\n<br><br>");
      }
   }
   return($iLen>0);
}

function load_JS_BuildEmailScript(){
//---------------------------------------------------------------
// insert a simple java script routine
//---------------------------------------------------------------
   echoT('
      <SCRIPT LANGUAGE="JavaScript">
      function buildEmailAddr(sUser, sSite) {
         document.write(\'<a href=\\"mailto:\' + sUser + \'@\' + sSite + \'\\">\');
         document.write(sUser + \'@\' + sSite + \'</a>\');
      }
      // End -->
      </SCRIPT>
   ');
}

function strBuildAddress(
               $strAddress1, $strAddress2, $strCity,
               $strState,    $strCountry,  $strZip,
               $bHTML_BR,    $bRemHTMLChars=true){
//------------------------------------------------------------------
//  if $bHTML_BR, insert HTML break tags (<br>)
//------------------------------------------------------------------
   $strBreak = ($bHTML_BR?"<br>\n":"\n");
   $strBuildAddress = '';
   if ($bRemHTMLChars) {
      $strAddress1 = htmlspecialchars($strAddress1);
      $strAddress2 = htmlspecialchars($strAddress2);
      $strCity     = htmlspecialchars($strCity    );
      $strState    = htmlspecialchars($strState   );
      $strCountry  = htmlspecialchars($strCountry );
      $strZip      = htmlspecialchars($strZip     );
   }
   if (strlen($strAddress1.'')>0) {
      $strBuildAddress = $strAddress1.$strBreak;
   }
   if (strlen($strAddress2.'')>0) {
      $strBuildAddress .= $strAddress2.$strBreak;
   }
   if (strlen($strCity)>0){
      $strBuildAddress .= $strCity.', ';
   }
   $strBuildAddress .= $strState.' '.$strZip;
   $strUC_Country = strtoupper($strCountry);
   if (! ( ($strUC_Country=='USA')
         ||($strUC_Country=='UNITED STATES')
         ||($strUC_Country=='')
         ||($strUC_Country=='AMERICA')) ){
      $strBuildAddress .= ' '.$strCountry;
   }
   return(trim($strBuildAddress));
}

function strXlateCurrency($enumCurrency){
//------------------------------------------------------------------
//
//------------------------------------------------------------------
   switch ($enumCurrency){
      case 'dollar': return('$');       break;
      case 'euro'  : return('&#128;');  break;
      case 'pound' : return('&pound;'); break;
      case 'yen'   : return('&yen;');   break;
      case 'rupee' : return('&#8360;'); break;
      default      : return('#err#');   break;
   }
}

function traceFilepath($strFile){
//------------------------------------------------------------------
//
//------------------------------------------------------------------
   global $gbAjaxResponse;

      // no debug trace on ajax response
   if ($gbAjaxResponse) return;


   if (@$_SESSION[CS_NAMESPACE.'_bToggleFileTrace']){
      $strFile = substr($strFile, -50);
      echoT('<font class="trace"><b>file:</b> ...'.$strFile."<br></font>");
   }
}

function strFormValue($strSetValue, $strDefaultVal){
//------------------------------------------------------------------
//
//------------------------------------------------------------------
   if ($strSetValue.''=='') {
      return($strDefaultVal);
   }else {
      return($strSetValue);
   }
}

function strUpFirst($strTest){
//---------------------------------------------------------------------
// capitalize first letter
//---------------------------------------------------------------------
   $lLen = strlen($strTest.'');
   if ($lLen==0){
      return('');
   }elseif ($lLen==1){
      return(strtoupper($strTest));
   }else {
      return(strtoupper(substr($strTest, 0, 1)).substr($strTest, 1));
   }
}

function strQuoteFix($str){
//---------------------------------------------------------------------
//  http://php.net/manual/fr/function.chr.php
//  http://codeigniter.com/forums/viewthread/228704/
//  http://webdesign.about.com/od/localization/l/blhtmlcodes-ascii.htm
//---------------------------------------------------------------------
   $str = str_replace(chr(145), "'", $str);    // left single quote
   $str = str_replace(chr(146), "'", $str);    // right single quote
   $str = str_replace(chr(147), '"', $str);    // left double quote
   $str = str_replace(chr(148), '"', $str);    // right double quote
   return($str);
}

function strPhoneCell($strPhone, $strCell, $bHTMLEscape=true, $bUseBreak=false){
   $strPhoneOut = '';
   if ($bHTMLEscape){
      $strPhone = htmlspecialchars($strPhone);
      $strCell  = htmlspecialchars($strCell);
   }
   if ($strPhone.'' != ''){
      $strPhoneOut = $strPhone;
   }
   if ($strCell.'' != ''){
      if ($strPhoneOut != '') $strPhoneOut .= ($bUseBreak ? '<br>' : ' / ');
      $strPhoneOut .= $strCell.' (cell)';
   }
   return($strPhoneOut);
}

function strXlateContext($enumContextType, $bCap=true, $bPlural=true){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if ($bCap){
      if ($bPlural){
         switch ($enumContextType){
            case CENUM_CONTEXT_BIZ:           $strContext = 'Businesses/Organizations';       break;
            case CENUM_CONTEXT_BIZCONTACT:    $strContext = 'Business/Organization Contacts'; break;
            case CENUM_CONTEXT_CLIENT:        $strContext = 'Clients';                        break;
            case CENUM_CONTEXT_GIFT:          $strContext = 'Donations';                      break;
            case CENUM_CONTEXT_GIFTHON:       $strContext = 'Honorariums';                    break;
            case CENUM_CONTEXT_GIFTMEM:       $strContext = 'Memorials';                      break;
            case CENUM_CONTEXT_HOUSEHOLD:     $strContext = 'Households';                     break;
            case CENUM_CONTEXT_LOCATION:      $strContext = 'Client Locations';               break;
            case CENUM_CONTEXT_REMINDER:      $strContext = 'Reminders';                      break;
            case CENUM_CONTEXT_SPONSORPAY:    $strContext = 'Sponsorship Payments';           break;
            case CENUM_CONTEXT_SPONSORPACKET: $strContext = 'Sponsor Packets';                break;
            case CENUM_CONTEXT_STAFF:         $strContext = 'Staff';                          break;
            case CENUM_CONTEXT_USER:          $strContext = 'Users';                          break;
            case CENUM_CONTEXT_PEOPLE:        $strContext = 'People';                         break;
            case CENUM_CONTEXT_SPONSORSHIP:   $strContext = 'Sponsorships';                   break;
            case CENUM_CONTEXT_VOLUNTEER:     $strContext = 'Volunteers';                     break;

            case CENUM_CONTEXT_STAFF_TS_LOCATIONS: $strContext = 'Staff timesheets: Locations';  break;
            case CENUM_CONTEXT_STAFF_TS_PROJECTS:  $strContext = 'Staff timesheets: Projects';   break;
            default:
               screamForHelp($enumContextType.': Invalid Context Type</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
               break;
         }
      }else {
         switch ($enumContextType){
            case CENUM_CONTEXT_BIZ:           $strContext = 'Businesses/Organization';       break;
            case CENUM_CONTEXT_BIZCONTACT:    $strContext = 'Business/Organization Contact'; break;
            case CENUM_CONTEXT_CLIENT:        $strContext = 'Client';                        break;
            case CENUM_CONTEXT_GIFT:          $strContext = 'Donation';                      break;
            case CENUM_CONTEXT_GIFTHON:       $strContext = 'Honorarium';                    break;
            case CENUM_CONTEXT_GIFTMEM:       $strContext = 'Memorial';                      break;
            case CENUM_CONTEXT_HOUSEHOLD:     $strContext = 'Household';                     break;
            case CENUM_CONTEXT_LOCATION:      $strContext = 'Client Location';               break;
            case CENUM_CONTEXT_REMINDER:      $strContext = 'Reminder';                      break;
            case CENUM_CONTEXT_SPONSORPAY:    $strContext = 'Sponsorship Payment';           break;
            case CENUM_CONTEXT_SPONSORPACKET: $strContext = 'Sponsor Packet';                break;
            case CENUM_CONTEXT_STAFF:         $strContext = 'Staff';                         break;
            case CENUM_CONTEXT_USER:          $strContext = 'User';                          break;
            case CENUM_CONTEXT_PEOPLE:        $strContext = 'People';                        break;
            case CENUM_CONTEXT_SPONSORSHIP:   $strContext = 'Sponsorship';                   break;
            case CENUM_CONTEXT_VOLUNTEER:     $strContext = 'Volunteer';                     break;

            case CENUM_CONTEXT_STAFF_TS_LOCATIONS: $strContext = 'Staff timesheets: Location';  break;
            case CENUM_CONTEXT_STAFF_TS_PROJECTS:  $strContext = 'Staff timesheets: Project';   break;
            default:
               screamForHelp($enumContextType.': Invalid Context Type</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
               break;
         }
      }
   }else {
      if ($bPlural){
         switch ($enumContextType){
            case CENUM_CONTEXT_BIZ:           $strContext = 'businesses/organizations';       break;
            case CENUM_CONTEXT_BIZCONTACT:    $strContext = 'business/organization contacts'; break;
            case CENUM_CONTEXT_CLIENT:        $strContext = 'clients';                        break;
            case CENUM_CONTEXT_GIFT:          $strContext = 'donations';                      break;
            case CENUM_CONTEXT_GIFTHON:       $strContext = 'honorariums';                    break;
            case CENUM_CONTEXT_GIFTMEM:       $strContext = 'memorials';                      break;
            case CENUM_CONTEXT_HOUSEHOLD:     $strContext = 'households';                     break;
            case CENUM_CONTEXT_LOCATION:      $strContext = 'client locations';               break;
            case CENUM_CONTEXT_REMINDER:      $strContext = 'reminders';                      break;
            case CENUM_CONTEXT_SPONSORPAY:    $strContext = 'sponsorship payments';           break;
            case CENUM_CONTEXT_SPONSORPACKET: $strContext = 'sponsor packets';                break;
            case CENUM_CONTEXT_STAFF:         $strContext = 'staff';                          break;
            case CENUM_CONTEXT_USER:          $strContext = 'users';                          break;
            case CENUM_CONTEXT_PEOPLE:        $strContext = 'people';                         break;
            case CENUM_CONTEXT_SPONSORSHIP:   $strContext = 'sponsorships';                   break;
            case CENUM_CONTEXT_VOLUNTEER:     $strContext = 'volunteers';                     break;

            case CENUM_CONTEXT_STAFF_TS_LOCATIONS: $strContext = 'staff timesheets: locations';  break;
            case CENUM_CONTEXT_STAFF_TS_PROJECTS:  $strContext = 'staff timesheets: projects';   break;
            default:
               screamForHelp($enumContextType.': Invalid Context Type</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
               break;
         }
      }else {
         switch ($enumContextType){
            case CENUM_CONTEXT_BIZ:           $strContext = 'businesses/organization';       break;
            case CENUM_CONTEXT_BIZCONTACT:    $strContext = 'business/organization contact'; break;
            case CENUM_CONTEXT_CLIENT:        $strContext = 'client';                        break;
            case CENUM_CONTEXT_GIFT:          $strContext = 'donation';                      break;
            case CENUM_CONTEXT_GIFTHON:       $strContext = 'honorarium';                    break;
            case CENUM_CONTEXT_GIFTMEM:       $strContext = 'memorial';                      break;
            case CENUM_CONTEXT_HOUSEHOLD:     $strContext = 'household';                     break;
            case CENUM_CONTEXT_LOCATION:      $strContext = 'client location';               break;
            case CENUM_CONTEXT_REMINDER:      $strContext = 'reminder';                      break;
            case CENUM_CONTEXT_SPONSORPAY:    $strContext = 'sponsorship payment';           break;
            case CENUM_CONTEXT_SPONSORPACKET: $strContext = 'sponsor packet';                break;
            case CENUM_CONTEXT_STAFF:         $strContext = 'staff';                         break;
            case CENUM_CONTEXT_USER:          $strContext = 'user';                          break;
            case CENUM_CONTEXT_PEOPLE:        $strContext = 'people';                        break;
            case CENUM_CONTEXT_SPONSORSHIP:   $strContext = 'sponsorship';                   break;
            case CENUM_CONTEXT_VOLUNTEER:     $strContext = 'volunteer';                     break;

            case CENUM_CONTEXT_STAFF_TS_LOCATIONS: $strContext = 'staff timesheets: location';  break;
            case CENUM_CONTEXT_STAFF_TS_PROJECTS:  $strContext = 'staff timesheets: project';   break;
            default:
               screamForHelp($enumContextType.': Invalid Context Type</b><br>error on <b>line:</b> '.__LINE__.'<br><b>file:</b> '.__FILE__.'<br><b>function:</b> '.__FUNCTION__);
               break;
         }
      }
   }
   return($strContext);
}

function strBuildName($bLastFirst, $strTitle, $strPreferred, $strFName, $strLName, $strMName){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if (($strPreferred != $strFName) && ($strPreferred != '')){
      $strPreferred = ' ('.$strPreferred.')';
   }else {
      $strPreferred = '';
   }

   if ($strTitle !== '') $strTitle .= ' ';
   if ($strMName !== '') $strMName  = ' '.$strMName;

   if ($bLastFirst){
      $strName = $strLName.', '.$strTitle.$strFName.$strMName.$strPreferred;
   }else {
      $strName = $strTitle.$strFName.$strMName.' '.$strLName.$strPreferred;
   }
   return($strName);
}


function strBuildEmailLink($strTestEmail, $strLabel, $bIncludeLabIfBlank,
                $strClassTag=''){
//------------------------------------------------------------------
//
//------------------------------------------------------------------
   if (strlen($strTestEmail)>0) {
      $strFormatEmail =
               $strLabel
              .'<a '.$strClassTag.' href="mailto:'.$strTestEmail.'">'.$strTestEmail.'</a>';
   }else {
      if ($bIncludeLabIfBlank) {
         $strFormatEmail = $strLabel;
      }else {
         $strFormatEmail = '';
      }
   }
   return($strFormatEmail);
}

function setOnFormError(&$displayData, $strErr=''){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   if ($strErr=='') $strErr = 'A problem was detected in this form. Please see messages below.';

   $displayData['strErrOnForm'] = $strErr;
}

function strImgTagBlackLine($lWidth, $lHeight){
//---------------------------------------------------------------------
// height / width in pixels
//---------------------------------------------------------------------
   return(
         '<img src="'.base_url().'images/layout/separatorBlack.jpg"
            width="'.$lWidth.'px" height="'.$lHeight.'px" alt="---"
            style="vertical-align:text-top;" />'."\n");
}




/* ----------------------------------------------------------------
           C U S T O M   H E L P E R   R O U T I N E S
 ---------------------------------------------------------------- */

   function customClient_AAYHF_01(&$strInnerAAYHF, &$strFieldsAAYHF, &$bAAYHF_Beacon){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strInnerAAYHF = $strFieldsAAYHF = ''; $bAAYHF_Beacon = false;
      $bAAYHF_Beacon = CB_AAYHF && bInUserGroup('Workforce Dev. Center');
      if ($bAAYHF_Beacon){

            // using a subquery in the inner join allows for the inclusion of
            // at most one record from the child table
            // http://stackoverflow.com/questions/12526194/mysql-inner-join-select-only-one-row-from-second-table
         $strInnerAAYHF = '
            LEFT JOIN (
               SELECT
                  bbbTab.uf000029_lForeignKey AS bTabClientID,
                  bbbTab.uf000029_lKeyID,
                  bbbTab.uf000029_000464,
                  bbbTab.uf000029_000465,
                  bbbTab.uf000029_000466,
                  bbbTab.uf000029_000467,
                  bbbTab.uf000029_000468,
                  bbbTab.uf000029_000469

               FROM uf_000029 AS bbbTab
               GROUP BY bbbTab.uf000029_lForeignKey
               ) AS bbTab
               ON bTabClientID=cr_lKeyID
            ';

            $strFieldsAAYHF = ',
                bbTab.uf000029_lKeyID AS lBeaconEID,
                bbTab.uf000029_000464 AS bFelony,
                bbTab.uf000029_000465 AS bMisdemeanor,
                bbTab.uf000029_000466 AS bOnParole,
                bbTab.uf000029_000467 AS bOnProbation,
                bbTab.uf000029_000468 AS bViolentOffense,
                bbTab.uf000029_000469 AS bSexualOffender ';
      }
   }

   function addClientFields_AAYHF_Beacon($bAAYHF_Beacon, &$client, &$row){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bAAYHF_Beacon){
         $client->bFelony          = $row->bFelony;
         $client->bMisdemeanor     = $row->bMisdemeanor;
         $client->bOnParole        = $row->bOnParole;
         $client->bOnProbation     = $row->bOnProbation;
         $client->bViolentOffense  = $row->bViolentOffense;
         $client->bSexualOffender  = $row->bSexualOffender ;
         $client->lBeaconEID       = $row->lBeaconEID;
         $client->bEnrolledBeacon  = $bEnrolled = !(is_null($client->lBeaconEID));
         if ($bEnrolled){
            $client->strFlagsTable =
               '<table class="enpRpt">
                  <tr>
                     <td class="enpRpt" style="background-color: #fffd4a; text-align: center; width: 7px;">'
                        .($client->bMisdemeanor ? '<b>M</b>' : '&nbsp;').'
                     </td>
                     <td class="enpRpt" style="background-color: #f3c62b; text-align: center; width: 7px;">'
                        .($client->bOnProbation ? '<b>Pr</b>' : '&nbsp;').'
                     </td>
                     <td class="enpRpt" style="background-color: #ffac4a; text-align: center; width: 7px;">'
                        .($client->bOnParole ? '<b>P</b>' : '&nbsp;').'
                     </td>
                     <td class="enpRpt" style="color: #fff; background-color: #ff0000; text-align: center; width: 7px;">'
                        .($client->bFelony ? '<b>F</b>' : '&nbsp;').'
                     </td>
                     <td class="enpRpt" style="color: #fff; background-color: #ff0000; text-align: center; width: 7px;">'
                        .($client->bViolentOffense ? '<b>V</b>' : '&nbsp;').'
                     </td>
                     <td class="enpRpt" style="color: #fff; background-color: #ff0000; text-align: center; width: 7px;">'
                        .($client->bSexualOffender ? '<b>S</b>' : '&nbsp;').'
                     </td>
                  </tr>
                </table>';
         }
      }
   }

   function clearReturnPaths(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $_SESSION[CS_NAMESPACE.'rpEnrollAddEdit']     =
      $_SESSION[CS_NAMESPACE.'rpPTablesAddEdit']    =
      $_SESSION[CS_NAMESPACE.'rpAttendanceAddEdit'] = '';
   }

   function setCFormErrorMessage($strFieldName, $strErrorMessage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $gErrMessages;

      if (isset($eErrMessages[$strFieldName])){
         $gErrMessages[$strFieldName] .= '<br>';
      }else {
         $gErrMessages[$strFieldName] = '';
      }
      $gErrMessages[$strFieldName] .= '<div class="formError">'.$strErrorMessage.'</div>';
   }

   function phpVersionTestDL(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $MinVersion = explode('.',  PHP_MIN_DL_VERSION);
      $lMinVersion = $MinVersion[0] * 10000 + $MinVersion[1] * 100 + $MinVersion[2];

         // http://php.net/phpversion
      if (!defined('PHP_VERSION_ID')) {
          $version = explode('.', PHP_VERSION);
          define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
      }
      if (PHP_VERSION_ID < $lMinVersion){
         echoT('****** PHP Version Error *******<br>
                Delightful Labor requires php version '.PHP_MIN_DL_VERSION.' or greater.<br><br>
                It appears that you are running version '.PHP_VERSION);
         die;
      }
   }

   function dteMySQLDate2Unix($strMySQLDate){
   //-------------------------------------------------------------------------
   // convert a mySQL timestring to a unix timestamp
   // mySQL timestring format: yyyy-mm-dd
   //
   // if the time string is null, return null
   //-------------------------------------------------------------------------
      if (is_null($strMySQLDate)) {
         return(null);
      }

      return(mktime (0,0,0,
                      (integer)substr($strMySQLDate, 5, 2),
                      (integer)substr($strMySQLDate, 8, 2),
                      (integer)substr($strMySQLDate, 0, 4))
             );
   }
?>