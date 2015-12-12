<?php
/*---------------------------------------------------------------------
  Delightful Labor!

  copyright (c) 2014 by Database Austin

  This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->helper('img_docs/img_doc_tags');
      $this->load->model('img_docs/mimg_doc_tags',  'cidTags');
---------------------------------------------------------------------*/

class mimg_doc_tags extends CI_Model{

   public $sqlWhere;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();

      $this->sqlWhere = '';
   }

   function loadImgDocsViaContext($enumContext, &$lNumTags, &$tags){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere =
          ' AND dit_enumContext = '.strPrepStr($enumContext).' ';
      $this->loadImgDocTags($lNumTags, $tags);
   }

   function loadSingleTag($lDIT_ID, &$lNumTags, &$tagInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere =
          " AND dit_lKeyID = $lDIT_ID ";
      $this->loadImgDocTags($lNumTags, $tagInfo);
   }

   function loadImgDocTagsForDDL($enumContext, $lImgDocID, &$lNumTags, &$tags){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // first, load the tags for the specific Image/Doc ID
      $this->loadImageDocTagsViaImageDocID($lImgDocID, $lNumSel, $tagsSelected);

         // now load all the tags for the context
      $this->loadImgDocsViaContext($enumContext, $lNumTags, $tags);

         // now determine which tags have been selected
      if ($lNumTags > 0){
         foreach ($tags as $tag){
            $tag->bSelected = false;
            if ($lNumSel > 0){
               $lTagID = $tag->lTagID;
               foreach ($tagsSelected as $ts){
                  if ($ts->lDDLID == $lTagID){
                     $tag->bSelected = true;
                     break;
                  }
               }
            }
         }
      }
   }

   function loadImgDocTags(&$lNumTags, &$tags, $bHideRetired=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $tags = array();

      if ($bHideRetired){
         $sqlHide = ' AND NOT dit_bRetired ';
      }else {
         $sqlHide = '';
      }

      $sqlStr =
           "SELECT
               dit_lKeyID, dit_enumContext, dit_strDDLEntry,
               dit_lSortIDX, dit_bRetired
            FROM doc_img_tag_ddl
            WHERE 1 $this->sqlWhere $sqlHide
            ORDER BY dit_lSortIDX, dit_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumTags = $query->num_rows();
      if ($lNumTags == 0){
         $tags[0] = new stdClass;
         $tag = &$tags[0];
         $tag->lTagID            =
         $tag->enumContext       =
         $tag->strDDLEntry       =
         $tag->lSortIDX          =
         $tag->strIDType         = 
         $tag->enumEntryType     = 
         $tag->enumParentContext =          
         $tag->bRetired          = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row)   {
            $tags[$idx] = new stdClass;
            $tag = &$tags[$idx];

            $tag->lTagID      = (int)$row->dit_lKeyID;
            $tag->enumContext = $row->dit_enumContext;
            $tag->strDDLEntry = $row->dit_strDDLEntry;
            $tag->lSortIDX    = (int)$row->dit_lSortIDX;
            
               // contextual info
            $tag->strIDType         = imgDocTags\strXlateImgDocType($tag->enumContext, $tag->bImage);
            $tag->enumEntryType     = $tag->bImage ? CENUM_IMGDOC_ENTRY_IMAGE : CENUM_IMGDOC_ENTRY_PDF;
            $tag->enumParentContext = imgDocTags\xlateContextViaTagType($tag->enumContext);
            
            $tag->bRetired    = (boolean)$row->dit_bRetired;

            ++$idx;
         }
      }
   }

   function loadImageDocTagsViaImageDocID($lImgDocID, &$lNumTags, &$tags){
   //---------------------------------------------------------------------
   // find what tags have been selected for a specific image/document
   //---------------------------------------------------------------------
      $tags = array();
      $sqlStr =
        "SELECT
            dim_lKeyID, dim_lImgDocID, dim_lDDLID,
            dit_enumContext, dit_strDDLEntry, dit_lSortIDX, dit_bRetired
         FROM doc_img_tag_ddl_multi
            INNER JOIN doc_img_tag_ddl ON dim_lDDLID=dit_lKeyID
         WHERE dim_lImgDocID=$lImgDocID
         ORDER BY dit_lSortIDX, dit_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumTags = $query->num_rows();
      if ($lNumTags == 0){
         $tags[0] = new stdClass;
         $tag = &$tags[0];
         $tag->lMultiKeyID =
         $tag->lImgDocID   =
         $tag->lDDLID      =
         $tag->enumContext =
         $tag->strDDLEntry =
         $tag->lSortIDX    =
         $tag->bRetired    = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row)   {
            $tags[$idx] = new stdClass;
            $tag = &$tags[$idx];

            $tag->lMultiKeyID = (int)$row->dim_lKeyID;
            $tag->lImgDocID   = (int)$row->dim_lImgDocID;
            $tag->lDDLID      = (int)$row->dim_lDDLID;
            $tag->enumContext = $row->dit_enumContext;
            $tag->strDDLEntry = $row->dit_strDDLEntry;
            $tag->lSortIDX    = (int)$row->dit_lSortIDX;
            $tag->bRetired    = (bool)$row->dit_bRetired;

            ++$idx;
         }
      }
   }

   function lMaxSortIDX($enumContext){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'SELECT MAX(dit_lSortIDX) AS lMaxSI
         FROM doc_img_tag_ddl
         WHERE NOT dit_bRetired
         AND dit_enumContext='.strPrepStr($enumContext).';';
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      if (is_null($row->lMaxSI)){
         return(-1);
      }else {
         return((int)$row->lMaxSI);
      }
   }

   function addTagEntry($strDDLEntry, $enumContext, $lSortIDX){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'INSERT INTO doc_img_tag_ddl
         SET
            dit_enumContext = '.strPrepStr($enumContext).',
            dit_strDDLEntry = '.strPrepStr($strDDLEntry).',
            dit_lSortIDX    = '.(int)$lSortIDX.',
            dit_bRetired = 0;';
      $query = $this->db->query($sqlStr);
      return($this->db->insert_id());
   }

   function updateTagEntry($strDDLEntry, $lDIT_ID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        'UPDATE doc_img_tag_ddl
         SET
            dit_strDDLEntry = '.strPrepStr($strDDLEntry)."
         WHERE dit_lKeyID=$lDIT_ID;";
      $query = $this->db->query($sqlStr);
   }

   function removeImgDocTag($lDIT_ID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "UPDATE doc_img_tag_ddl
         SET dit_bRetired = 1
         WHERE dit_lKeyID=$lDIT_ID;";
      $query = $this->db->query($sqlStr);
   }

   function setTagIDsViaImgDocID($lImgDocID, &$tagIDs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // out with the old
      $this->removeImageTags($lImgDocID);

         // in with the new
      if (count($tagIDs) > 0){
         $sqlBase = "INSERT INTO doc_img_tag_ddl_multi SET dim_lImgDocID=$lImgDocID, dim_lDDLID=";
         foreach ($tagIDs as $tid){
            $query = $this->db->query($sqlBase.$tid.';');
         }
      }
   }

   function strImgDocTagsUL($lImgDocID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      $this->loadImageDocTagsViaImageDocID($lImgDocID, $lNumTags, $tags);
      if ($lNumTags == 0){
         $strOut = '<i>No tags</i>';
      }else {
         $strOut = '<ul style="margin-top: 0px; margin-bottom: 0px; ">'."\n";
         foreach ($tags as $tag){
            $strOut .= '<li style="margin-left: -20px;">'.htmlspecialchars($tag->strDDLEntry).'</li>'."\n";
         }
         $strOut .= '</ul>'."\n";
      }
      return($strOut);
   }

   function removeImageTags($lImageDocID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "DELETE FROM doc_img_tag_ddl_multi WHERE dim_lImgDocID=$lImageDocID;";
      $query = $this->db->query($sqlStr);
   }

   function lNumImgDocTagsViaTagID($lTagID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumImgDocs
         FROM doc_img_tag_ddl_multi
            INNER JOIN docs_images ON dim_lImgDocID=di_lKeyID
         WHERE dim_lDDLID=$lTagID
            AND NOT di_bRetired;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumImgDocs);
   }


}

