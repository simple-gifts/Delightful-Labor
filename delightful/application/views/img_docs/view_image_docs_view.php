<?php


   if ($lNumImageDocs == 0){
      echoT('<i>There are no '.($bImage ? 'images' : 'documents').' available for viewing.</i><br>');
      return;
   }

   showImageDocumentInfo($enumEntryType, $enumContextType, $lFID, 
                         ($bImage ? 'Image ' : 'Document ').' libaray for '.$strContextName,         
                         $imageDocs,       $lNumImageDocs, 
                         $lNumImageDocs, 600,              false,
                         false);




