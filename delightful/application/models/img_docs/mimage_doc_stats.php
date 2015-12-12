<?php
/*---------------------------------------------------------------------
// copyright (c) 2014
// Austin, Texas 78759
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
  ---------------------------------------------------------------------
      $this->load->model('img_docs/mimage_doc_stats', 'cIDStats');
---------------------------------------------------------------------

---------------------------------------------------------------------*/


class mimage_doc_stats extends mimage_doc{

   public $econtextImg, $econtextDoc,
      $tagsImage, $tagsDocs, $cIDTags,
      $bDebug;

   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      imgDocTags\loadImgContexts($this->econtextImg);
      imgDocTags\loadDocContexts($this->econtextDoc);

      $this->cIDTags = new mimg_doc_tags;
   }

   function lTotImages(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlWhere = 'AND di_enumEntryType='.strPrepStr(CENUM_IMGDOC_ENTRY_IMAGE).' ';
      return($this->lImageDocCnt($sqlWhere));
   }

   function lTotDocs(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlWhere = 'AND di_enumEntryType='.strPrepStr(CENUM_IMGDOC_ENTRY_PDF).' ';
      return($this->lImageDocCnt($sqlWhere));
   }

   function lImageDocCnt($sqlWhere){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM docs_images
         WHERE NOT di_bRetired $sqlWhere;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }

   function lImagesGrouped(&$lNumContextGroups, &$contextGroups){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlWhere = 'AND di_enumEntryType='.strPrepStr(CENUM_IMGDOC_ENTRY_IMAGE).' ';
      $this->lGroupedImageDocCnt($sqlWhere, 'di_enumContextType',
                            $lNumContextGroups, $contextGroups);
   }

   function lDocsGrouped(&$lNumContextGroups, &$contextGroups){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlWhere = 'AND di_enumEntryType='.strPrepStr(CENUM_IMGDOC_ENTRY_PDF).' ';
      $this->lGroupedImageDocCnt($sqlWhere, 'di_enumContextType',
                            $lNumContextGroups, $contextGroups);
   }

   function lGroupedImageDocCnt($sqlWhere, $strGroupFN, &$lNumGroups, &$groups){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs, $strGroupFN AS strGroup
         FROM docs_images
         WHERE NOT di_bRetired $sqlWhere
         GROUP BY $strGroupFN
         ORDER BY $strGroupFN;";
      $query = $this->db->query($sqlStr);

      $groups = array();
      $query = $this->db->query($sqlStr);
      $lNumGroups = $query->num_rows();
      if ($lNumGroups > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $groups[$idx] = new stdClass;
            $grp = &$groups[$idx];

            $grp->lNumRecs = (int)$row->lNumRecs;
            $grp->strGroup = $row->strGroup;

            ++$idx;
         }
      }
   }

   function loadImageTags(&$tagsImage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $tagsImage = array();
      $idx = 0;
      foreach ($this->econtextImg as $econtext){
         $tagsImage[$idx] = new stdClass;
         $tagC = &$tagsImage[$idx];
         $tagC->econtext = $econtext;
         $this->cIDTags->loadImgDocsViaContext($econtext, $tagC->lNumTags, $tagC->tags);

         ++$idx;
      }
   }

   function loadDocTags(&$tagsDocs){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $tagsDocs = array();
      $idx = 0;
      foreach ($this->econtextDoc as $econtext){
         $tagsDocs[$idx] = new stdClass;
         $tagC = &$tagsDocs[$idx];
         $tagC->econtext = $econtext;
         $this->cIDTags->loadImgDocsViaContext($econtext, $tagC->lNumTags, $tagC->tags);

         ++$idx;
      }
   }

   function strTagTableHeaderViaTag($lNumParentRecs, $tag){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '<br>
         <table>
            <tr>
               <td>
                  <b>Tag Type:</b>
               </td>
               <td>'
                  .$tag->strIDType.' / '
                  .strLabelViaContextType($tag->enumParentContext, true, true).'
               </td>
            </tr>
            <tr>
               <td>
                  <b>Tag:</b>
               </td>
               <td>'
                  .htmlspecialchars($tag->strDDLEntry).'
               </td>
            </tr>
            <tr>
               <td>
                  <b># Parent Records:</b>
               </td>
               <td>'
                  .number_format($lNumParentRecs).'
               </td>
            </tr>
         </table><br>';
      if ($lNumParentRecs == 0) return($strOut);

      return($strOut);
   }

}

