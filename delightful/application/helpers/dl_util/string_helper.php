<?php
/*---------------------------------------------------------------------
 copyright (c) 2004-2014 by Database Austin

 This software is provided under the GPL.
 Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('dl_util/string');
---------------------------------------------------------------------
  splitLongWords           ($strTest, $lMaxLen, $strStringEnd)
  strHighlightPhrases      ($strText, $strPhraseList, $strStartTag, $strEndTag)
  sngCurrencyStringToFloat ($strCurrency)
---------------------------------------------------------------------*/

function strShortenString($strIn, $lMaxChars, $bAddDots){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $lCnt = strlen($strIn);
   if ($lCnt <= $lMaxChars) return($strIn);
   $strOut = substr($strIn, 0, $lMaxChars);
   if (($lCnt > $lMaxChars) && $bAddDots) $strOut .= '...';
   return($strOut);
}

function splitLongWords($strTest, $lMaxLen, $strStringEnd){
//---------------------------------------------------------------------
// used to split really long words in a string (for example, an
// embedded URL). Useful when formatting table entries.
//
// Typically $strStringEnd = "\r\n" or "<br>'
//---------------------------------------------------------------------
   $objWords = explode(' ', $strTest);
   $strReturn = '';
   $lCount = count($objWords);
   for ($idx=0; $idx < $lCount; ++$idx) {
      $strHold = $objWords[$idx];
      if (strlen($strHold)> $lMaxLen){
         $strHold = chunk_split($strHold, $lMaxLen, $strStringEnd);
      }
      $strReturn .= $strHold.' ';
   }
   return($strReturn);
}

function sngCurrencyStringToFloat($strCurrency){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strCurrency = str_replace('$', '', $strCurrency);
   $strCurrency = str_replace(',', '', $strCurrency);
   return((float)$strCurrency);
}

function strDigitsToPhoneNumber($strPhone){
//---------------------------------------------------------------------
//
//---------------------------------------------------------------------
   $strPhone = $strPhone.'';

   if (is_numeric($strPhone)){
      if (strlen($strPhone)==10){
         return('('.substr($strPhone, 0, 3).') '
                   .substr($strPhone, 3, 3).'-'
                   .substr($strPhone, 6));
      }else {
         return($strPhone);
      }
   }else {
      return($strPhone);
   }

   $strCurrency = str_replace('$', '', $strCurrency);
   $strCurrency = str_replace(',', '', $strCurrency);
   return((float)$strCurrency);
}

function strString2ASCII($strString, $strSep){
//---------------------------------------------------------------------
// useful for creating a codeigniter url entry
//---------------------------------------------------------------------
   $sArray = str_split($strString);
   $strOut = '';
   $lCnt = count($sArray);
   if ($lCnt > 0){
      for ($idx=0; $idx<$lCnt; ++$idx){
         $sArray[$idx] = ord($sArray[$idx]);
      }
      $strOut = implode($strSep, $sArray);
   }
   return($strOut);
}

function strASCII2String2($strASCII, $strSep){
//---------------------------------------------------------------------
// useful for creating a codeigniter url entry
//---------------------------------------------------------------------
   $sArray = explode($strSep, $strASCII);
   $strOut = '';
   $lCnt = count($sArray);
   if ($lCnt > 0){
      for ($idx=0; $idx<$lCnt; ++$idx){
         $strOut .= chr($sArray[$idx]);
      }
   }
   return($strOut);
}

function safeHighlight($needleArray, $strLeftTag, $strRightTag, $haystack, $encoding = 'utf-8'){
/*----------------------------------------------------------------------
grf at post dot cz
http://us3.php.net/manual/en/function.stripos.php
----------------------------------------------------------------------*/
    // encoding
    $e = $encoding;

    // oh, no needles
    if( !is_array( $needleArray))
        return $haystack;

    // empty keys throw-off, only unique, reindex
    $nA = array_values(
            array_unique(
                array_diff( $needleArray, array(''))
            )
        );

    // needle count
    if( !($nC = count( $nA)))
        return $haystack; // nothing to hl

    // shear length
    if( !(($rLL = mb_strlen( $rL = $strLeftTag, $e))
    + ($rRL = mb_strlen( $rR = $strRightTag, $e))))
        return $haystack; // no shears

    // subject length
    if( !($sL = mb_strlen( $s = $haystack, $e)))
        return null; // empty subject

    // subject in lowercase (we need to aviod
    // using mb_stripos due to PHP version)
    $sW = mb_strtolower( $s, $e);

    // masking ~ 0=not changed, 1=changed
    $m = str_repeat( '0', $sL);

    // loop for each needle
    for( $n=0; $n<$nC; $n++)
    {

        // needle string loWercase
        $nW = mb_strtolower( $nA[ $n], $e);

        $o = 0; // offset
        $nL = mb_strlen( $nW, $e); // needle length

        // search needle
        while( false !== ($p = mb_strpos( $sW, $nW, $o, $e)))
        {
            // oh hurrey, needle found on $p position

            // is founded needle already modified? (in full-length)
            for( $q=$p; $q<($p+$nL); $q++)
                if( $m[ $q])
                {
                    // ai, caramba. already modified, jump over
                    $o+= $nL;

                    // continue for while() loop - not for for() loop!
                    continue 2;
                }

            // explode subject and mask into three parts
            // partA|needle|partB
            $sE[0] = mb_substr( $s, 0, $p, $e);
            $sE[1] = mb_substr( $s, $p, $nL, $e);
            $sE[2] = mb_substr( $s, $p+$nL, $sL-$p-$nL, $e);

            // mask
            // partA|partB (needle not needed)
            $mE[0] = mb_substr( $m, 0, $p, $e);
            $mE[1] = mb_substr( $m, $p+$nL, $sL-$p-$nL, $e);

            // apply shears
            $sE[1] = $rL.$sE[1].$rR;

            // update sunject length
            $sL+= $rLL + $rRL;

            // update mask
            $m = $mE[0] . str_repeat( '1', $rLL + $nL + $rRL) . $mE[1];

            // implode into a subject
            $s = implode( $sE);

            // update lowercase subject
            $sW = mb_strtolower( $s, $e);

            // increase offset
            $o+= $rLL + $nL + $rRL;

            // end of string reached
            if( $o>=$sL)
                break;

        } // while()

    } // for( $n=0; $n<$nC; $n++)

    // oouu yeaaa, kick the subject out of the function
    return $s;

} // function safeHighlight()
/****************************************
*    END: SAFE HIGHLIGHT
****************************************/


?>