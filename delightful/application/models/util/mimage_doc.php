<?php
/*---------------------------------------------------------------------
// copyright (c) 2014-2015
// Austin, Texas 78759
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
  ---------------------------------------------------------------------
      $this->load->model('util/mimage_doc', 'clsImgDoc');
---------------------------------------------------------------------

---------------------------------------------------------------------*/


class mimage_doc extends CI_Model{

   public
      $bDebug;

   public $sqlWhereExtra, $sqlSort, $sqlLimit, $lNumImageDocs, $imageDocs, $bLoadContext;


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->sqlWhereExtra = $this->sqlSort = $this->sqlLimit = '';
      $this->lNumImageDocs = 0; $this->imageDocs = null;

      $this->bLoadContext = true;
   }

   function lNumImageDocsViaEntryContextFID($enumEntryType, $enumContextType, $lFID){
      $sqlStr =
         'SELECT COUNT(*) AS lNumRecs
          FROM docs_images
          WHERE NOT di_bRetired
             AND di_enumEntryType   = '.strPrepStr($enumEntryType).'
             AND di_enumContextType = '.strPrepStr($enumContextType)."
             AND di_lForeignID = $lFID;";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(0);
      }else {
         $row = $query->row();
         return((integer)$row->lNumRecs);
      }
   }

   function loadDocImageInfoViaEntryContextFID($enumEntryType, $enumContextType, $lFID){
      $this->sqlWhereExtra = ' AND di_enumEntryType   = '.strPrepStr($enumEntryType).'
                               AND di_enumContextType = '.strPrepStr($enumContextType)."
                               AND di_lForeignID = $lFID ";
      $this->sqlSort  = ' ORDER BY di_bProfile DESC, di_dteDocImage DESC, di_lKeyID DESC ';
      $this->loadDocImageGeneric();
   }

   function enumContextViaID($lDocImageID){
      return($this->singleFieldViaID('di_enumContextType', $lDocImageID));
   }

   function lForeignIDViaID($lDocImageID){
      return($this->singleFieldViaID('di_lForeignID', $lDocImageID));
   }

   function enumEntryTypeViaID($lDocImageID){
      return($this->singleFieldViaID('di_enumEntryType', $lDocImageID));
   }

   private function singleFieldViaID($strFieldName, $lDocImageID){
      $sqlStr =
        "SELECT $strFieldName
         FROM docs_images
         WHERE di_lKeyID=$lDocImageID;";

      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(null);
      }else {
         $row = $query->row();
         return($row->$strFieldName);
      }
   }

   function loadDocImageInfoViaID($lDocImageID){
      $this->sqlWhereExtra = " AND di_lKeyID = $lDocImageID ";
      $this->sqlSort  = '';
      $this->loadDocImageGeneric();
   }

   function loadDocImageInfoViaContextFID($enumContextType, $lFID){
      $this->sqlWhereExtra = ' AND di_enumContextType = '.strPrepStr($enumContextType)."
                               AND di_lForeignID = $lFID ";
      $this->sqlSort  = ' ORDER BY di_bProfile DESC, di_dteDocImage DESC, di_lKeyID ';
      $this->loadDocImageGeneric();
   }

   function loadProfileImage($enumContextType, $lFID){
   //---------------------------------------------------------------------
   // If profile bit not set, return most recently uploaded image
   //---------------------------------------------------------------------
      $this->sqlWhereExtra = ' AND di_enumEntryType   = '.strPrepStr(CENUM_IMGDOC_ENTRY_IMAGE).'
                               AND di_enumContextType = '.strPrepStr($enumContextType)."
                               AND di_lForeignID = $lFID ";
      $this->sqlSort  = ' ORDER BY di_bProfile DESC, di_lKeyID DESC ';
      $this->sqlLimit = ' LIMIT 0,1 ';
      $this->loadDocImageGeneric();
   }

   function loadDocImageGeneric(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->imageDocs = array();
      if ($this->sqlSort.'' == '') $this->sqlSort = ' ORDER BY di_bProfile DESC, di_dteDocImage DESC, di_lKeyID ';
      $sqlStr =
         "SELECT
            di_lKeyID, di_enumEntryType, di_enumContextType,
            di_lForeignID, di_strCaptionTitle, di_strDescription,
            di_dteDocImage, di_bProfile,
            di_strUserFN, di_strSystemFN, di_strSystemThumbFN,
            di_strPath, di_bRetired,
            di_lOriginID, di_lLastUpdateID,
            UNIX_TIMESTAMP(di_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(di_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName
         FROM docs_images
            INNER JOIN admin_users  AS uc ON uc.us_lKeyID=di_lOriginID
            INNER JOIN admin_users  AS ul ON ul.us_lKeyID=di_lLastUpdateID
         WHERE NOT di_bRetired $this->sqlWhereExtra
         $this->sqlSort
         $this->sqlLimit;";

      $query = $this->db->query($sqlStr);
      $this->lNumImageDocs = $lNumRows = $query->num_rows();
      if ($lNumRows == 0){
         $this->imageDocs[0] = new stdClass;
         $imgDoc = &$this->imageDocs[0];
         $imgDoc->lKeyID           =
         $imgDoc->enumEntryType    =
         $imgDoc->enumContextType  =
         $imgDoc->lForeignID       =
         $imgDoc->strCaptionTitle  =
         $imgDoc->strDescription   =
         $imgDoc->dteDocImage      =
         $imgDoc->dteMysqlDocImage =
         $imgDoc->bProfile         =
         $imgDoc->strUserFN        =
         $imgDoc->strSystemFN      =
         $imgDoc->strSystemThumbFN =
         $imgDoc->strPath          =
         $imgDoc->bRetired         =
         $imgDoc->lOriginID        =
         $imgDoc->lLastUpdateID    =
         $imgDoc->dteOrigin        =
         $imgDoc->LastUpdate       =
         $imgDoc->strUCFName       =
         $imgDoc->strUCLName       =
         $imgDoc->strULFName       =
         $imgDoc->strULLName       =
         $imgDoc->imageSize        =
         $imgDoc->sngAspectRatio   = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row)   {
            $this->imageDocs[$idx] = new stdClass;
            $imgDoc = &$this->imageDocs[$idx];

            $imgDoc->lKeyID           = (int)$row->di_lKeyID;
            $imgDoc->enumEntryType    = $row->di_enumEntryType;
            $imgDoc->enumContextType  = $enumContextType = $row->di_enumContextType;
            $imgDoc->lForeignID       = $lFID = (int)$row->di_lForeignID;
            $imgDoc->strCaptionTitle  = $row->di_strCaptionTitle;
            $imgDoc->strDescription   = $row->di_strDescription;
            $imgDoc->dteDocImage      = dteMySQLDate2Unix($row->di_dteDocImage);
            $imgDoc->dteMysqlDocImage = $row->di_dteDocImage;
            $imgDoc->bProfile         = $row->di_bProfile;
            $imgDoc->strUserFN        = $row->di_strUserFN;
            $imgDoc->strSystemFN      = $row->di_strSystemFN;
            $imgDoc->strSystemThumbFN = $strThumbFN = $row->di_strSystemThumbFN;
            $imgDoc->strPath          = $strPath = $row->di_strPath;
            $imgDoc->bRetired         = (boolean)$row->di_bRetired;
            $imgDoc->lOriginID        = (int)$row->di_lOriginID;
            $imgDoc->lLastUpdateID    = (int)$row->di_lLastUpdateID;
            $imgDoc->dteOrigin        = (int)$row->dteOrigin;
            $imgDoc->LastUpdate       = (int)$row->dteLastUpdate;
            $imgDoc->strUCFName       = $row->strUCFName;
            $imgDoc->strUCLName       = $row->strUCLName;
            $imgDoc->strULFName       = $row->strULFName;
            $imgDoc->strULLName       = $row->strULLName;
            if ($this->bLoadContext) $this->loadNameViaContextFID($imgDoc, $enumContextType, $lFID);

            if ($imgDoc->enumEntryType==CENUM_IMGDOC_ENTRY_IMAGE){
               $imgDoc->imageSize = @getimagesize($strPath.'/'.$strThumbFN);

               if (is_null($imgDoc->imageSize) || $imgDoc->imageSize===false){
                  $imgDoc->sngAspectRatio = null;
               }else {
                  $imgDoc->sngAspectRatio = $imgDoc->imageSize[0]/$imgDoc->imageSize[1];
               }
            }else {
               $imgDoc->imageSize      = null;
               $imgDoc->sngAspectRatio = null;
            }
            ++$idx;
         }
      }
   }

   function loadNameViaContextFID($clsID, $enumContextType, $lFID){
      switch ($enumContextType){
         case CENUM_CONTEXT_AUCTION:
            $cAuction = new mauctions;
            $cAuction->loadAuctionByAucID($lFID);
            $clsID->strName = $cAuction->auctions[0]->strAuctionName;
            break;

         case CENUM_CONTEXT_AUCTIONITEM:
            $cItem = new mitems;
            $cItem->loadItemViaItemID($lFID);
            $clsID->strName = $cItem->items[0]->strItemName;
            break;

         case CENUM_CONTEXT_AUCTIONPACKAGE:
            $cPackage = new mpackages;
            $cPackage->loadPackageByPacID($lFID);
            $clsID->strName = $cPackage->packages[0]->strPackageName;
            break;

         case CENUM_CONTEXT_BIZ:
            $clsBiz = new mbiz;
            $clsBiz->loadBizRecsViaBID($lFID);
            $clsID->strName = $clsBiz->bizRecs[0]->strSafeName;
            break;

         case CENUM_CONTEXT_CLIENT:
            $clsClient = new mclients;
            $clsClient->loadClientsViaClientID($lFID);
            $clsID->strName = $clsClient->clients[0]->strFName.' '.$clsClient->clients[0]->strLName;
            break;

         case CENUM_CONTEXT_LOCATION:
            $clsLoc = new mclient_locations;
            $clsLoc->loadLocationRec($lFID);
            $clsID->strName = $clsLoc->strLocation;
            break;

         case CENUM_CONTEXT_ORGANIZATION:
            $clsOrg = new morganization;
            $clsOrg->lChapterID = $lFID;
            $clsOrg->loadChapterInfo();
            $clsID->strName = $clsOrg->chapterRec->strSafeChapterName;
            break;

         case CENUM_CONTEXT_PEOPLE:
            $clsPeople = new mpeople;
            $clsPeople->loadPeopleViaPIDs($lFID, false, false);
            $clsID->strName = $clsPeople->people[0]->strFName.' '.$clsPeople->people[0]->strLName;
            break;

         case CENUM_CONTEXT_SPONSORSHIP:
            $clsSpon = new msponsorship;
            $clsSpon->sponsorInfoViaID($lFID);
            $clsID->strName = $clsSpon->sponInfo[0]->strSponSafeNameFL;
            break;

         case CENUM_CONTEXT_VOLUNTEER:
            $clsVol = new mvol;
            $clsVol->loadVolRecsViaVolID($lFID, true);
            $clsID->strName = $clsVol->volRecs[0]->strSafeName;
            break;

         case CENUM_CONTEXT_HOUSEHOLD:
         default:
            screamForHelp($enumContextType.': not implemented yet<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
            break;
      }
   }

   function insertDocImageRec(){
      global $glUserID;
      $imgDoc = &$this->imageDocs[0];
      $sqlStr =
         'INSERT INTO docs_images
          SET '. $this->sqlCommonAddUpdate().',
             di_enumEntryType            = '.strPrepStr($imgDoc->enumEntryType  ).',
             di_enumContextType          = '.strPrepStr($imgDoc->enumContextType).',
             di_strUserFN                = '.strPrepStr($imgDoc->strUserFN       ).',
             di_strSystemFN              = '.strPrepStr($imgDoc->strSystemFN     ).',
             di_strSystemThumbFN         = '.(is_null($imgDoc->strSystemThumbFN) ?
                                              'NULL' : strPrepStr($imgDoc->strSystemThumbFN)).',
             di_strPath                  = '.strPrepStr($imgDoc->strPath         ).",
             di_lForeignID               = $imgDoc->lForeignID,
             di_lOriginID                = $glUserID,
             di_bRetired                 = 0,
             di_dteOrigin               = NOW();";

      $query = $this->db->query($sqlStr);
      $this->imageDocs[0]->lKeyID = $lKeyID = $this->db->insert_id();
      return($lKeyID);
   }

   function updateDocImageRec($lImgDocID){
      $sqlStr =
         'UPDATE docs_images
          SET '. $this->sqlCommonAddUpdate()."
          WHERE di_lKeyID = $lImgDocID;";
      $query = $this->db->query($sqlStr);
   }

   private function sqlCommonAddUpdate(){
      global $glUserID;
      $imgDoc = &$this->imageDocs[0];
      return('
         di_strCaptionTitle          = '.strPrepStr($imgDoc->strCaptionTitle ).',
         di_strDescription           = '.strPrepStr($imgDoc->strDescription  ).',
         di_dteDocImage              = '.strPrepStr($imgDoc->dteMysqlDocImage).',
         di_bProfile                 = '.($imgDoc->bProfile ? '1' : '0'      ).",
         di_lLastUpdateID            = $glUserID,
         di_dteLastUpdate            = NOW() ");
   }

   function setProfileFlag($lImgDocID, $enumContextType, $lFID){
      global $glUserID;

         // first clear profile flag for this FID
      $this->clearProfileFlag($lImgDocID, $enumContextType, $lFID);

         // now set the flag
      $sqlStr =
        "UPDATE docs_images
         SET di_bProfile=1, di_lLastUpdateID=$glUserID
         WHERE di_lKeyID = $lImgDocID;";
      $this->db->query($sqlStr);
   }

   function clearProfileFlag($lImgDocID, $enumContextType, $lFID){
      global $glUserID;

         // first clear profile flag for this FID
      $sqlStr =
        "UPDATE docs_images
         SET di_bProfile=0, di_lLastUpdateID=$glUserID
         WHERE di_enumEntryType=".strPrepStr(CENUM_IMGDOC_ENTRY_IMAGE).'
            AND di_enumContextType='.strPrepStr($enumContextType)."
            AND di_lForeignID=$lFID
            AND di_lKeyID != $lImgDocID
            AND di_bProfile = 1
            AND not di_bRetired;";
      $this->db->query($sqlStr);
   }

   function removeImageDoc($lImageDocID){
      global $glUserID;

      $sqlStr =
        "SELECT di_enumEntryType, di_enumContextType, di_lForeignID,
            di_strSystemFN, di_strSystemThumbFN, di_strPath
         FROM docs_images
         WHERE di_lKeyID=$lImageDocID;";

      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         screamForHelp($lImageDocID.': imageID/Unexpected EOF<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }else {
         $row = $query->row();
         $enumEntryType   = $row->di_enumEntryType;
         $enumContextType = $row->di_enumContextType;
         $lFID            = $row->di_lForeignID;
         $strFN           = $row->di_strSystemFN;
         $strThumb        = $row->di_strSystemThumbFN;
         $strPath         = $row->di_strPath;
         @unlink($strPath.'/'.$strFN);
         if ($strThumb.'' != ''){
            @unlink($strPath.'/'.$strThumb);
         }
         $sqlStr =
             "UPDATE docs_images
              SET
                 di_bRetired      = 1,
                 di_lLastUpdateID = $glUserID
               WHERE di_lKeyID=$lImageDocID;";
         $this->db->query($sqlStr);
      }
   }

   function strImageDocTerseTable($opts, &$lNumObjects){
   /* -------------------------------------------------------------------------------
      $opts->enumEntryType:      CENUM_IMGDOC_ENTRY_IMAGE, CENUM_IMGDOC_ENTRY_PDF
      $opts->enumContextType:    CENUM_CONTEXT_ACCOUNT, ... CENUM_CONTEXT_BIZ, CENUM_CONTEXT_BIZCONTACT ...
      $opts->lFID
      $opts->lCellHeight         image cell height, in pixels
      $opts->lCellWidth          image cell width, in pixels
      $opts->lBorderWidth:       in pixels, null for no border
      $opts->lCellsPerRow
      $opts->bShowCaption
      $opts->bShowDescription
      $opts->bShowDate
      $opts->bShowOriginalFN
      $opts->bAddRadioSelect:    FID is used as value
      $opts->strRadioFieldName
      $opts->lMatchID
      $opts->bShowNone
      $opts->strShowNoneLabel
   ------------------------------------------------------------------------------- */
      global $genumDateFormat;
      if ((int)$opts->lCellsPerRow <= 0){
         return('ERROR: cells per row not valid');
      }
      $bPDF = $opts->enumEntryType == CENUM_IMGDOC_ENTRY_PDF;
      if ($bPDF){
         $strImageLink = '<img src="'.DL_IMAGEPATH.'/misc/pdfIcon.png" border="0" title="Open PDF document in new window">';
      }else {
         $strImageLink = '';
      }

      $cID = new mimage_doc;
      $cID->loadDocImageInfoViaEntryContextFID($opts->enumEntryType, $opts->enumContextType, $opts->lFID);
      $lNumObjects = $cID->lNumImageDocs;
      $strOut = "\n\n";

      if ($lNumObjects > 0){

         $strBorder = (is_null($opts->lBorderWidth) ? '' : ' border: '.$opts->lBorderWidth.'px solid black; ');
         $strOut = "\n\n".'<table style="'.$strBorder.'">'."\n";
         $cellIDX = $opts->lCellsPerRow;
         for ($idx=0; $idx<$lNumObjects; ++$idx){
            $imageDoc = &$cID->imageDocs[$idx];
            $strWidth = 'width: '.$opts->lCellWidth.'px; ';

            if ($cellIDX >= $opts->lCellsPerRow){
               if ($idx > 0){
                  $strOut .= '</tr>'."\n";
               }
               $strOut .= '<tr>'."\n";
               $cellIDX = 0;
            }

            if (!$bPDF){
               if ($imageDoc->sngAspectRatio > 0){
                  $strStyle = 'style="width: '.$opts->lCellWidth.'px;" ';
               }else {
                  $strStyle = 'style="height: '.$opts->lCellHeight.'px;" ';
               }
               $strImageLink =  strImageHTMLTag($opts->enumContextType, $opts->enumEntryType,
                                   $opts->lFID, $imageDoc->strSystemThumbFN,
                                  'View in new window', false, $strStyle)."\n";
            }
            $strFullElement =
                   strLinkHTMLTag($opts->enumContextType, $opts->enumEntryType, $opts->lFID, $imageDoc->strSystemFN,
                            'View in new window', true, '').$strImageLink.'</a>'."\n";


            $strOut .= '<td style="'.$strBorder.$strWidth.' vertical-align: top;">'."\n"
                           .'<table cellpadding="0" cellspacing="0" border="0">'."\n";

            if ($opts->bAddRadioSelect){
               $strOut .= '<tr><td style="vertical-align: top;">'
                             .'<input type="radio" name="'.$opts->strRadioFieldName.'" '
                                  .'value="'.$imageDoc->lKeyID.'" '
                                  .($imageDoc->lKeyID == $opts->lMatchID ? 'checked' : '').'> Select</td></tr>'."\n";
            }
            $strOut .=
                               '<tr>
                                   <td style="vertical-align: top; height: '.$opts->lCellHeight.'px;">'
                           .$strFullElement.'</td></tr>'."\n";


            if ($opts->bShowCaption){
               $strOut .= '<tr><td style="vertical-align: top;"><b>'.htmlspecialchars($imageDoc->strCaptionTitle)
                             .'</b></td></tr>'."\n";
            }
            if ($opts->bShowDate){
               $strOut .= '<tr><td style="vertical-align: top;">'
                             .date($genumDateFormat, $imageDoc->dteDocImage)
                             .'</td></tr>'."\n";
            }
            if ($opts->bShowOriginalFN){
               $strOut .= '<tr><td style="vertical-align: top;">'.htmlspecialchars($imageDoc->strUserFN)
                             .'</td></tr>'."\n";
            }
            if ($opts->bShowDescription){
               $strOut .= '<tr><td style="vertical-align: top;">'.htmlspecialchars($imageDoc->strDescription)
                             .'</td></tr>'."\n";
            }

            $strOut .= '</table></td>'."\n\n\n";
            ++$cellIDX;
         }
         if ($opts->bShowNone){
            if ($cellIDX >= $opts->lCellsPerRow){
               if ($idx > 0){
                  $strOut .= '</tr>'."\n";
               }
               $strOut .= '<tr>'."\n";
               $cellIDX = 0;
            }

            $strOut .= '<td style="'.$strBorder.$strWidth.' vertical-align: top;">'
                          .'<input type="radio" name="'.$opts->strRadioFieldName.'" '
                               .'value="-1" '
                               .($opts->lMatchID == -1 ? 'checked' : '').'> '.$opts->strShowNoneLabel.'</td>';
         }
         $strOut .= '</table>';
      }
      return($strOut);
   }

   function strCatalogPath($enumContextType, $enumEntryType, $lFID){
   //---------------------------------------------------------------------
   //  images and pdfs are stored in
   //   ./catalog/$enumContextType/xx
   //  where xx is the last two digits of the foreign ID. There will be
   //  a max of 100 subdirectories
   //---------------------------------------------------------------------
      $this->testCatalogDirPathTop();
      $this->testCatalogDirContext($enumContextType);
      $this->testCatalogDirEntry($enumContextType, $enumEntryType);
      $this->testCatalogDirFID($enumContextType, $enumEntryType, $lFID);
      return('./catalog/'.$enumContextType.'/'.$enumEntryType.'/'.strCatalogFID_Directory($lFID));
   }

   private function testCatalogDirPathTop(){
      $strTopCat = './catalog';
      $fInfo = get_file_info($strTopCat);
      if ($fInfo===false){
         if (mkdir($strTopCat)===false){
            screamForHelp('Unable to create catalog directory!: <br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         }else {
            write_file($strTopCat.'/index.html', '<html>Hello, world!</html>');
         }
      }
   }

   private function testCatalogDirContext($enumContextType){
      $strDir = './catalog/'.$enumContextType;
      $fInfo = get_file_info($strDir);
      if ($fInfo===false){
         if (mkdir($strDir)===false){
            screamForHelp('Unable to create catalog directory!: <br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         }else {
            write_file($strDir.'/index.html', '<html>Hello, world!</html>');
         }
      }
   }

   private function testCatalogDirEntry($enumContextType, $enumEntryType){
      $strDir = './catalog/'.$enumContextType.'/'.$enumEntryType;
      $fInfo = get_file_info($strDir);
      if ($fInfo===false){
         if (mkdir($strDir)===false){
            screamForHelp('Unable to create catalog directory!: <br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         }else {
            write_file($strDir.'/index.html', '<html>Hello, world!</html>');
         }
      }
   }

   private function testCatalogDirFID($enumContextType, $enumEntryType, $lFID){
      $strDir = './catalog/'.$enumContextType.'/'.$enumEntryType.'/'.strCatalogFID_Directory($lFID);
      $fInfo = get_file_info($strDir);
      if ($fInfo===false){
         if (mkdir($strDir)===false){
            screamForHelp('Unable to create catalog directory!: <br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
         }else {
            write_file($strDir.'/index.html', '<html>Hello, world!</html>');
         }
      }
   }

   public function transferUploadFile($strDestinationPath, $strFN, $strThumbFN, $bResize, $bCreateThumb){

      $strDestination = $strDestinationPath.'/'.$strFN;
      if (rename ('./upload/'.$strFN, $strDestination)===false){
         screamForHelp('Unable to move file '.$strFN.'<br>error on line '.__LINE__.',<br>file '.__FILE__.',<br>function '.__FUNCTION__);
      }

      if ($bResize){
         $config = array();
         $config['image_library']  = CSTR_IMAGE_LIBRARY;
         $config['source_image']	  = $strDestination;
         $config['create_thumb']   = false;
         $config['maintain_ratio'] = TRUE;
         $config['width']	        = CI_IMGDOC_RESIZE_MAXDIMENSION;
         $config['height']         = CI_IMGDOC_RESIZE_MAXDIMENSION;

         $this->image_lib->initialize($config);
         $this->image_lib->resize();
      }

         // note - codeIgnitor overwrites the original image and renames it;
         // must make a copy before creating the thumb
      if ($bCreateThumb){
         $strThumbDestination = $strDestinationPath.'/'.$strThumbFN;
         copy ($strDestination, $strThumbDestination);
         $config = array();
         $config['image_library']  = CSTR_IMAGE_LIBRARY;
         $config['source_image']	  = $strThumbDestination;
         $config['create_thumb']   = TRUE;
         $config['thumb_marker']   = '';
         $config['maintain_ratio'] = TRUE;
         $config['width']          = CI_IMGDOC_THUMB_MAXDIMENSION;
         $config['height']         = CI_IMGDOC_THUMB_MAXDIMENSION;

         $this->image_lib->initialize($config);
         $this->image_lib->resize();
      }
   }


   public function xferImageDocViaEntryContextFID($enumEntryType, $enumContextType, $lNewFID, $lOldFID){
   //---------------------------------------------------------------------
   // transfer images/documents from the old FID to the new FID
   //   * move to new directory (if required)
   //   * update database record to reflect change
   //---------------------------------------------------------------------
      $this->loadDocImageInfoViaEntryContextFID($enumEntryType, $enumContextType, $lOldFID);
      if ($this->lNumImageDocs == 0) return;

      $strNewPath = $this->strCatalogPath($enumContextType, $enumEntryType, $lNewFID);
      $strOldPath = $this->strCatalogPath($enumContextType, $enumEntryType, $lOldFID);

      foreach ($this->imageDocs as $imgDoc){
         $lKeyID = $imgDoc->lKeyID;

            // move file if it directory changes
         if ($strNewPath != $strOldPath){
            rename($strOldPath.'/'.$imgDoc->strSystemFN, $strNewPath.'/'.$imgDoc->strSystemFN);
            if (!is_null($imgDoc->strSystemThumbFN)){
               rename($strOldPath.'/'.$imgDoc->strSystemThumbFN, $strNewPath.'/'.$imgDoc->strSystemThumbFN);
            }
         }

         $sqlStr =
           "UPDATE docs_images
            SET di_lForeignID=$lNewFID,
               di_strPath=".strPrepStr($strNewPath)."
            WHERE di_lKeyID = $lKeyID;";
         $query = $this->db->query($sqlStr);
      }
   }


}

