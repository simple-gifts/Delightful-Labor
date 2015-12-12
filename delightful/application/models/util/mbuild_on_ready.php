<?php
//---------------------------------------------------------------------
// copyright (c) 2011
// Austin, Texas
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
/*---------------------------------------------------------------------
   $this->load->model('util/mbuild_on_ready', 'clsOnReady');
   
   sample usage:
      $this->load->model('util/mbuild_on_ready', 'clsOnReady');
      $this->clsOnReady->addOnReadyTableStripes();
      $this->clsOnReady->closeOnReady();
      $displayData['js'] = $this->clsOnReady->strOnReady;
      
      echoT('<tr class="makeStripe">...
   
---------------------------------------------------------------------*/
// screamForHelp('<br>error on <b>line:</b> '.__LINE__.'<br><b>file: </b>'.__FILE__.'<br><b>function: </b>'.__FUNCTION__);
//traceFilepath(__FILE__);

//-----------------------------------------------------------------------
//
//-----------------------------------------------------------------------
class mbuild_on_ready{
   var $strOnReady;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
      $this->strOnReady =
         '<SCRIPT LANGUAGE="JavaScript">'."\n"
        .'   $(document).ready(function(){'."\n";
   }

   function closeOnReady(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->strOnReady .= "});\n"
                          ."</SCRIPT>\n";
   }

   function addOnReadyStatement($strStatement){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->strOnReady .= $strStatement."\n";
   }

   function addOnReadyTableStripes(){
   //-----------------------------------------------------------------------
   //
   //-----------------------------------------------------------------------
      $this->strOnReady .=
//             "$('.makeStripe:even').css(\"background-color\", \"#f4f8ea\");\n";    // works
             "$('.makeStripe:nth-child(odd)').css(\"background-color\", \"#f6f6f3\");\n";    // works
   }


}

?>