<?php
//---------------------------------------------------------------------
// copyright (c) 2012
// Austin, Texas 78759
//
// Serving the Children of India
// 
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
//
// References:
//   Ajax for Web Developers by Kris Hadlock
//
//   http://www.w3schools.com/ajax/
/*---------------------------------------------------------------------
   $this->load->library('js_build/ajax_support');
   $clsAjax = new ajax_support;
---------------------------------------------------------------------*/

class ajax_support{

   public function showCreateXmlHTTPObject(){
   //---------------------------------------------------------------------
   // from http://www.w3schools.com/ajax/
   //---------------------------------------------------------------------
      return('
         <script type="text/javascript">
         var xmlhttp;
         if (window.XMLHttpRequest){ // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp=new XMLHttpRequest();
         }else {  // code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
         }
         </script>');
   }


   public function showCampaignLoadViaAcctID(){
   /*---------------------------------------------------------------------
      Used to load a dependent DDL based on the value of the account DDL

      thanks to http://www.javascriptkit.com/javatutors/selectcontent.shtml

      sample calling sequence...

       <select name="ddlAccts" onChange="showCampaigns(this.value, 'ddlCamps')">
          <option value="36c0280d36815ee9" >Fundraisers</option>
          <option value="ead3a3d2aca047de" >Sponsorship</option>
          <option value="31bd247001e52bf7" >Undirected</option>
       </select>

       <select name="ddlCamps" ID="ddlCamps">
          <option value="xxx">&nbsp;</option>
       </select>
   ---------------------------------------------------------------------*/
      return('
        <script type="text/javascript">
        function showCampaigns(strAcctID, strCampID){
           var xmlCamps, idx, strID, strCampName, xx, bSelected;
           xmlhttp.onreadystatechange=function(){
              if (xmlhttp.readyState==4 && xmlhttp.status==200){
                 xmlCamps = xmlhttp.responseXML.documentElement.getElementsByTagName("camp");

                 var objCamp = document.getElementById(strCampID);

                 objCamp.options.length = 0;
                 for (idx=0; idx < xmlCamps.length; idx++){
                    xx          = xmlCamps[idx].getElementsByTagName("encryptID");
                    strID       = xx[0].firstChild.nodeValue;
                    xx          = xmlCamps[idx].getElementsByTagName("name");
                    strCampName = xx[0].firstChild.nodeValue;
                    xx          = xmlCamps[idx].getElementsByTagName("selected");
                    bSelected   = xx[0].firstChild.nodeValue == \'true\';
                    objCamp.options[objCamp.options.length]=new Option(strCampName, strID, bSelected, false);
                 }
              }
           }
           xmlhttp.open("POST","'.site_url().'/ajax/ajax_campaigns/parseAjaxCampaign/loadViaAcctID/"+strAcctID,true);
           xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
           xmlhttp.overrideMimeType(\'text/xml\');
           xmlhttp.send();
        }
        </script>');
   }
//           xmlhttp.open("POST","../main/mainOpts.php?type=ajax&st=campaign&sst=loadViaAcctID&AID="+strAcctID,true);



    public function peopleBizNames($strFunctionName, $strSelectID){
   /*---------------------------------------------------------------------
      thanks to 
          http://www.w3schools.com/php/php_ajax_livesearch.asp
   ---------------------------------------------------------------------*/
   
      return('
         <script>
         function '.$strFunctionName.'(str){
            var xmlSelect, xmlEntry, strID, strNameAddr, bSelected, objNotFound;
            
            objNotFound = document.getElementById("notFound");

         
            if (str.length==0){ 
               objNotFound.style.visibility="hidden";
              return;
            }
            
            xmlhttp.onreadystatechange=function(){
               if (xmlhttp.readyState==4 && xmlhttp.status==200){
                 var objPepBizDDL = document.getElementById("'.$strSelectID.'");
                 
                 if (xmlhttp.responseText.length==0){
                    objPepBizDDL.options.length = 0;
                    objPepBizDDL.style.visibility="hidden";
                    objNotFound.style.visibility="visible";
                    
                 }else {
                    objPepBizDDL.options.length = 0;
                    objPepBizDDL.style.visibility="visible";
                    objNotFound.style.visibility="hidden";
                    xmlPepBiz = xmlhttp.responseXML.documentElement.getElementsByTagName("name");
                    
                    for (idx=0; idx < xmlPepBiz.length; idx++){
                       xmlEntry    = xmlPepBiz[idx].getElementsByTagName("id");
                       strID       = xmlEntry[0].firstChild.nodeValue;
                       xmlEntry    = xmlPepBiz[idx].getElementsByTagName("nameaddr");
                       strNameAddr = xmlEntry[0].firstChild.nodeValue;
                       bSelected   = false; //xmlEntry[0].firstChild.nodeValue == \'true\';
                       objPepBizDDL.options[objPepBizDDL.options.length]=new Option(strNameAddr, strID, bSelected, false);
                    }
//                    if (xmlPepBiz.length==2){
//                       objPepBizDDL.style.visibility="hidden";
//                       objPepBizDDL.size = "3";
//                       objPepBizDDL.style.visibility="visible";                       
//                    }
                 }
               }
            }
            xmlhttp.open("GET","'.site_url().'/ajax/ajax_names/lookupNamesBiz?search="+encodeURIComponent(str),true);
            xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
            xmlhttp.overrideMimeType(\'text/xml\');
            xmlhttp.send();
         }
         </script>');
   }

   function strPopulateTextFromDDL($strDDLID, $strTxtID){
   //---------------------------------------------------------------------
   // Set the text of the selected item of a ddl to a text box
   //---------------------------------------------------------------------   
      return('
                 <script>
                    function populateSearch(){
                       var objSearchTxt = document.getElementById("'.$strTxtID.'");
                       var objDDL       = document.getElementById("'.$strDDLID.'");
                       objSearchTxt.value = objDDL.options[objDDL.selectedIndex].text;
                    }
                 </script>');
   }












}