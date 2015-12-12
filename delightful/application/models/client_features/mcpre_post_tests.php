<?php
/*---------------------------------------------------------------------
// copyright (c) 2014-2015 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------
      $this->load->model('client_features/mcpre_post_tests', 'cpptests');
      $this->load->model('admin/mpermissions',               'perms');
---------------------------------------------------------------------*/

class mcpre_post_tests extends CI_Model{

   public
      $lNumPPTests, $pptests, $sqlWhere, $sqlOrder,
      $lNumQuestions, $questions, $sqlQWhere, $sqlQOrder,
      $sqlClientTestsWhere, $sqlClientTestsOrder,
      $sqlScoringWhere,     $sqlScoringOrder;

	function __construct(){
		parent::__construct();

      $this->lNumPPTests = $this->pptests = null;
      $this->sqlWhere = $this->sqlOrder = '';

      $this->lNumQuestions = $this->questions = null;
      $this->sqlQWhere = $this->sqlQOrder = '';

      $this->sqlClientTestsWhere = $this->sqlClientTestsOrder = '';
      $this->sqlScoringWhere     = $this->sqlScoringOrder     = '';
	}

   /* ------------------------------------------------------------------
                       T E S T S
      ------------------------------------------------------------------ */

   function loadPPCatsAndTests(&$lNumCats, &$ppCats, $bUserAccessible){
   //---------------------------------------------------------------------
   // if $bUserAccessible, the test must be published and not hidden
   //---------------------------------------------------------------------
         // load the pre/post test categories
      $clist = new mlist_generic;
      $clist->enumListType = CENUM_LISTTYPE_CPREPOSTCAT;
      $clist->genericLoadList();
      $lNumInList = $clist->lNumInList;   // we love scalars

         // add the "no category" to the bottom
      $lNumCats = $lNumInList + 1;
      $clist->listItems[$lNumInList] = new stdClass;
      $liLast = &$clist->listItems[$lNumInList];
      $liLast->lKeyID       = null;
      $liLast->strListItem  = '(no category)';
      $liLast->lSortIDX     = 9999;
      $liLast->enumListType = CENUM_LISTTYPE_CPREPOSTCAT;
      $liLast->bRetired     = false;

         // load the Pre/Post tests by category
      if ($clist->lNumInList > 0){
         foreach($clist->listItems as $li){
            $this->loadPPTestsViaCat($li->lKeyID, $li->lNumPPTests, $li->pptests, $bUserAccessible);
         }
      }
      $this->loadPPTestsViaCat(null, $liLast->lNumPPTests, $liLast->pptests, $bUserAccessible);
      $ppCats = arrayCopy($clist->listItems);
   }

   function loadPPTestsViaPPTID($lPPTID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND cpp_lKeyID=$lPPTID ";
      $this->loadPPTests();
   }

   function loadPPTestsViaCat($lCatKeyID, &$lNumPPTests, &$pptests, $bUserAccessible=false){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (is_null($lCatKeyID)){
         $this->sqlWhere = " AND cpp_lTestCat IS NULL ";
      }else {
         $this->sqlWhere = " AND cpp_lTestCat=$lCatKeyID ";
      }
      if ($bUserAccessible){
         $this->sqlWhere .= " AND cpp_bPublished AND NOT cpp_bHidden ";
      }
      $this->loadPPTestsGeneric($lNumPPTests, $pptests);
   }

   function loadPPTestsAvailToClient(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlWhere = " AND cpp_bPublished AND NOT cpp_bHidden ";
      $this->loadPPTests();
   }

   function loadPPTests($bShowHidden=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->loadPPTestsGeneric($this->lNumPPTests, $this->pptests, $bShowHidden);
   }

   function loadPPTestsGeneric(&$lNumPPTests, &$ppTests, $bShowHidden=true){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $cperms = new mpermissions;

      $ppTests = array();

      if ($bShowHidden){
         $strHidden = '';
      }else {
         $strHidden = ' AND NOT cpp_bHidden ';
      }

      if ($this->sqlOrder == ''){
         $strOrder = ' cpp_strTestName, cpp_lKeyID ';
      }else {
         $strOrder = $this->sqlOrder;
      }

      $sqlStr =
        "SELECT
            cpp_lKeyID, cpp_strTestName, cpp_strDescription, cpp_bPublished,
            cpp_lTestCat, lgen_strListItem,
            cpp_bHidden, cpp_bRetired,
            cpp_lOriginID, cpp_lLastUpdateID,

            UNIX_TIMESTAMP(cpp_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(cpp_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM cpp_tests
            INNER JOIN admin_users   AS uc ON uc.us_lKeyID = cpp_lOriginID
            INNER JOIN admin_users   AS ul ON ul.us_lKeyID = cpp_lLastUpdateID
            LEFT  JOIN lists_generic       ON cpp_lTestCat = lgen_lKeyID

         WHERE NOT cpp_bRetired $this->sqlWhere $strHidden
         ORDER BY $strOrder;";

      $query = $this->db->query($sqlStr);
      $lNumPPTests = $numRows = $query->num_rows();
//      $this->ppTests = array();
      if ($numRows==0) {
         $ppTests[0] = new stdClass;
         $pptest = &$ppTests[0];
         $pptest->lKeyID             =
         $pptest->strTestName        =
         $pptest->strDescription     =
         $pptest->lTestCatID         =
         $pptest->strPPTestCat       =
         $pptest->bPublished         =
         $pptest->bHidden            =
         $pptest->bRetired           =

         $pptest->lOriginID          =
         $pptest->lLastUpdateID      =

         $pptest->dteOrigin          =
         $pptest->dteLastUpdate      =
         $pptest->ucstrFName         =
         $pptest->ucstrLName         =
         $pptest->ulstrFName         =
         $pptest->ulstrLName         =

         $pptest->lNumPerms          = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $ppTests[$idx] = new stdClass;
            $pptest = &$ppTests[$idx];

            $pptest->lKeyID             = $lPPTestID = (int)$row->cpp_lKeyID;
            $pptest->strTestName        = $row->cpp_strTestName;
            $pptest->strDescription     = $row->cpp_strDescription;
            $pptest->lTestCatID         = $row->cpp_lTestCat;
            $pptest->strPPTestCat       = $row->lgen_strListItem.'';
            $pptest->bPublished         = (boolean)$row->cpp_bPublished;
            $pptest->bHidden            = (boolean)$row->cpp_bHidden;
            $pptest->bRetired           = (boolean)$row->cpp_bRetired;

            $pptest->lOriginID          = (int)$row->cpp_lOriginID;
            $pptest->lLastUpdateID      = (int)$row->cpp_lLastUpdateID;

            $pptest->dteOrigin          = $row->dteOrigin;
            $pptest->dteLastUpdate      = $row->dteLastUpdate;
            $pptest->ucstrFName         = $row->strUCFName;
            $pptest->ucstrLName         = $row->strUCLName;
            $pptest->ulstrFName         = $row->strULFName;
            $pptest->ulstrLName         = $row->strULLName;

               // user-group permissions for this program
            $pptest->lNumPerms = $cperms->lGroupPerms($lPPTestID, CENUM_CONTEXT_CPREPOST, $pptest->perms);

            $pptest->lNumQuest          = $this->lNumQuestsViaPPTID($lPPTestID);

            ++$idx;
         }
      }
   }

   function addNewPrePostTest(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $pptest = &$this->pptests[0];

      $sqlStr =
         'INSERT INTO cpp_tests
          SET '.$this->sqlCommonAddEditPPTest().",
             cpp_lOriginID = $glUserID,
             cpp_dteOrigin = NOW(),
             cpp_bRetired  = 0;";
      $query = $this->db->query($sqlStr);
      $pptest->lKeyID = $lKeyID = $this->db->insert_id();
      return($lKeyID);
   }

   function updatePrePost(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $pptest = &$this->pptests[0];

      $sqlStr =
         'UPDATE cpp_tests
          SET '.$this->sqlCommonAddEditPPTest()."
          WHERE cpp_lKeyID = $pptest->lKeyID;";
      $query = $this->db->query($sqlStr);
   }

   private function sqlCommonAddEditPPTest(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $pptest = &$this->pptests[0];

      return(
         'cpp_strTestName    = '.strPrepStr($pptest->strTestName).',
          cpp_strDescription = '.strPrepStr($pptest->strDescription).',
          cpp_bPublished     = '.($pptest->bPublished ? '1' : '0').',
          cpp_bHidden        = '.($pptest->bHidden    ? '1' : '0').",
          cpp_lTestCat       = $pptest->lTestCatID,
          cpp_lLastUpdateID  = $glUserID,
          cpp_dteLastUpdate  = NOW() ");
   }

   function changeVisibility($lPPTestID, $bNewVisState){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
         'UPDATE cpp_tests
          SET cpp_bHidden='.($bNewVisState ? '1' : '0').",
             cpp_lLastUpdateID = $glUserID
          WHERE cpp_lKeyID = $lPPTestID;";
      $query = $this->db->query($sqlStr);
   }

   function strHTMLPPTestSummaryDisplay(){
   //---------------------------------------------------------------------
   //  caller must first load the test
   //---------------------------------------------------------------------
      $params = array('enumStyle' => 'terse');
      $clsRpt = new generic_rpt($params);
      $strOut = '';

      $pptest = &$this->pptests[0];
      $lPPTestID = $pptest->lKeyID;

      $clsRpt->setEntrySummary();
      $strOut .= $clsRpt->openReport();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Pre/Post Test:', '115pt')
         .$clsRpt->writeCell(
                    htmlspecialchars($pptest->strTestName).'&nbsp;'
                   .strLinkView_CPPTestView($lPPTestID, 'View pre/post test', true))
         .$clsRpt->closeRow();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Category:')
         .$clsRpt->writeCell(htmlspecialchars($pptest->strPPTestCat), '350pt;')
         .$clsRpt->closeRow();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Pre/Post Test ID:', '115pt')
         .$clsRpt->writeCell(str_pad($lPPTestID, 5, '0', STR_PAD_LEFT))
         .$clsRpt->closeRow();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Description:')
         .$clsRpt->writeCell(nl2br(htmlspecialchars($pptest->strDescription)), '350pt;')
         .$clsRpt->closeRow();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Questions:')
         .$clsRpt->writeCell($pptest->lNumQuest.'&nbsp;'
                   .strLinkView_CPPQuestions($lPPTestID,'View questions', true), '')
         .$clsRpt->closeRow();

      $strOut .=
          $clsRpt->openRow()
         .$clsRpt->writeLabel('Status:')
         .$clsRpt->writeCell( ($pptest->bHidden    ? 'Hidden' : 'Visible').' / '
                             .($pptest->bPublished ? 'Published' : 'Not published'), '350pt;')
         .$clsRpt->closeRow();

      $strOut .=
         $clsRpt->closeReport();
      return($strOut);
   }





   /* ------------------------------------------------------------------
                       Q U E S T I O N S
      ------------------------------------------------------------------ */
   function lNumQuestsViaPPTID($lPPTID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
           "SELECT COUNT(*) AS lNumRecs
            FROM cpp_questions
            WHERE NOT cpq_bRetired AND cpq_lPrePostID=$lPPTID;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }

   function loadQuestionsViaPPTID($lPPTID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlQWhere = " AND cpq_lPrePostID=$lPPTID ";
      $this->loadQuestions();
   }

   function loadQuestionsViaQuestID($lQuestID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlQWhere = " AND cpq_lKeyID=$lQuestID ";
      $this->loadQuestions();
   }

   function loadQuestions(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->sqlQOrder == ''){
         $strQOrder = ' cpp_strTestName, cpp_lKeyID, cpq_lSortIDX, cpq_strQuestion, cpq_lKeyID ';
      }else {
         $strQOrder = $this->sqlQOrder;
      }

      $sqlStr =
        "SELECT
            cpq_lKeyID, cpq_lPrePostID, cpq_lSortIDX,
            cpq_strQuestion, cpq_strAnswer,
            cpq_bRetired, cpq_lOriginID, cpq_lLastUpdateID,

            cpp_lKeyID, cpp_strTestName, cpp_strDescription, cpp_bPublished,
            cpp_bHidden,

            UNIX_TIMESTAMP(cpp_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(cpp_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM cpp_questions
            INNER JOIN cpp_tests           ON cpp_lKeyID   = cpq_lPrePostID
            INNER JOIN admin_users   AS uc ON uc.us_lKeyID = cpq_lOriginID
            INNER JOIN admin_users   AS ul ON ul.us_lKeyID = cpq_lLastUpdateID

         WHERE NOT cpq_bRetired $this->sqlQWhere
         ORDER BY $strQOrder;";

      $query = $this->db->query($sqlStr);
      $this->lNumQuestions = $numRows = $query->num_rows();
      $this->questions = array();
      if ($numRows==0) {
         $this->questions[0] = new stdClass;
         $quest = &$this->questions[0];

         $quest->lKeyID             =
         $quest->lSortIDX           =
         $quest->strQuestion        =
         $quest->strAnswer          =

         $quest->lPPTestID          =
         $quest->strTestName        =
         $quest->strDescription     =
         $quest->bPublished         =
         $quest->bHidden            =
         $quest->bRetired           =

         $quest->lOriginID          =
         $quest->lLastUpdateID      =

         $quest->dteOrigin          =
         $quest->dteLastUpdate      =
         $quest->ucstrFName         =
         $quest->ucstrLName         =
         $quest->ulstrFName         =
         $quest->ulstrLName         =

         $quest->lNumPerms          = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $this->questions[$idx] = new stdClass;
            $quest = &$this->questions[$idx];

            $quest->lKeyID             = (int)$row->cpq_lKeyID;
            $quest->lSortIDX           = (int)$row->cpq_lSortIDX;
            $quest->strQuestion        = $row->cpq_strQuestion;
            $quest->strAnswer          = $row->cpq_strAnswer;

            $quest->lPPTestID          = (int)$row->cpq_lPrePostID;
            $quest->strTestName        = $row->cpp_strTestName;
            $quest->strDescription     = $row->cpp_strDescription;
            $quest->bPublished         = (boolean)$row->cpp_bPublished;
            $quest->bHidden            = (boolean)$row->cpp_bHidden;

            $quest->lOriginID          = (int)$row->cpq_lOriginID;
            $quest->lLastUpdateID      = (int)$row->cpq_lLastUpdateID;

            $quest->dteOrigin          = $row->dteOrigin;
            $quest->dteLastUpdate      = $row->dteLastUpdate;
            $quest->ucstrFName         = $row->strUCFName;
            $quest->ucstrLName         = $row->strUCLName;
            $quest->ulstrFName         = $row->strULFName;
            $quest->ulstrLName         = $row->strULLName;

            ++$idx;
         }
      }
   }

   function addNewQuestion($lPPTestID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $quest = &$this->questions[0];

      $udtb = new up_down_top_bottom;
      $udtb->strTable     = 'cpp_questions';
      $udtb->strKeyField  = 'cpq_lKeyID';
      $udtb->strSortField = 'cpq_lSortIDX';

      $opts = array();
      $opts[0] = new stdClass;
      $opts[0]->strFN = 'cpq_lPrePostID';
      $opts[0]->lQualValue = $lPPTestID;

      $sqlStr =
         'INSERT INTO cpp_questions
          SET '.$this->sqlCommonAddEditQuest().',
             cpq_lSortIDX   = '.($udtb->lMaxSortIDX($opts)+1).",
             cpq_lPrePostID = $lPPTestID,
             cpq_lOriginID  = $glUserID,
             cpq_dteOrigin  = NOW(),
             cpq_bRetired   = 0;";
      $query = $this->db->query($sqlStr);
      $quest->lKeyID = $lKeyID = $this->db->insert_id();
      return($lKeyID);
   }

   function updateQuestion(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $quest = &$this->questions[0];

      $sqlStr =
         'UPDATE cpp_questions
          SET '.$this->sqlCommonAddEditQuest()."
          WHERE cpq_lKeyID = $quest->lKeyID;";
      $query = $this->db->query($sqlStr);
   }

   private function sqlCommonAddEditQuest(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $quest = &$this->questions[0];

      return('
           cpq_strQuestion   = '.strPrepStr($quest->strQuestion).',
           cpq_strAnswer     = '.strPrepStr($quest->strAnswer).",
           cpq_lLastUpdateID = $glUserID,
           cpq_dteLastUpdate = NOW() ");
   }

   function removeQuestion($lQuestID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "DELETE FROM cpp_questions
          WHERE cpq_lKeyID=$lQuestID;";
      $query = $this->db->query($sqlStr);
   }



   /* ------------------------------------------------------------------
                       C L I E N T   T E S T I N G
      ------------------------------------------------------------------ */
   function clientTestsViaTestID($lClientID, $lPPTestID, &$lNumTests, &$testInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlClientTestsWhere =
          " AND cptl_lClientID=$lClientID
            AND cptl_lPrePostID=$lPPTestID ";

      $this->sqlClientTestsOrder = ' cptl_dtePreTest, cptl_lKeyID ';
      $this->loadClientTests($lNumTests, $testInfo);
   }

   function loadTestLogViaTestLogID($lTestLogID, &$lNumTests, &$testInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->sqlClientTestsWhere = " AND cptl_lKeyID=$lTestLogID ";
      $this->sqlClientTestsOrder = '';
      $this->loadClientTests($lNumTests, $testInfo);
   }

   function loadClientTests(&$lNumTests, &$testInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($this->sqlClientTestsOrder == ''){
         $strOrder = ' cptl_dtePreTest, cptl_lKeyID ';
      }else {
         $strOrder = $this->sqlClientTestsOrder;
      }

      $sqlStr =
        "SELECT
            cptl_lKeyID, cptl_lPrePostID, cptl_lClientID,
            cptl_dtePreTest, cptl_dtePostTest,

            cptl_lOriginID, cptl_lLastUpdateID,
            UNIX_TIMESTAMP(cptl_dteOrigin)     AS dteOrigin,
            UNIX_TIMESTAMP(cptl_dteLastUpdate) AS dteLastUpdate,
            uc.us_strFirstName AS strUCFName, uc.us_strLastName AS strUCLName,
            ul.us_strFirstName AS strULFName, ul.us_strLastName AS strULLName

         FROM cpp_test_log
            INNER JOIN admin_users AS uc ON uc.us_lKeyID=cptl_lOriginID
            INNER JOIN admin_users AS ul ON ul.us_lKeyID=cptl_lLastUpdateID

         WHERE NOT cptl_bRetired $this->sqlClientTestsWhere
         ORDER BY $strOrder;";

      $query = $this->db->query($sqlStr);
      $lNumTests = $numRows = $query->num_rows();
      $testInfo = array();
      if ($numRows==0) {
         $testInfo[0] = new stdClass;
         $testTaken = &$testInfo[0];

         $testTaken->lKeyID        =
         $testTaken->lPrePostID    =
         $testTaken->lClientID     =
         $testTaken->dtePreTest    =
         $testTaken->dtePostTest   =

         $testTaken->lOriginID     =
         $testTaken->lLastUpdateID =
         $testTaken->dteOrigin     =
         $testTaken->dteLastUpdate =
         $testTaken->strUCFName    =
         $testTaken->strUCLName    =
         $testTaken->strULFName    =
         $testTaken->strULLName    = null;
      }else {
         $idx = 0;
         foreach ($query->result() as $row){
            $testInfo[$idx] = new stdClass;
            $testTaken = &$testInfo[$idx];

            $testTaken->lKeyID        = $lTestLogID = (int)$row->cptl_lKeyID;
            $testTaken->lPrePostID    = (int)$row->cptl_lPrePostID;
            $testTaken->lClientID     = (int)$row->cptl_lClientID;
            $testTaken->dtePreTest    = dteMySQLDate2Unix($row->cptl_dtePreTest);
            $testTaken->dtePostTest   = dteMySQLDate2Unix($row->cptl_dtePostTest);

            $this->clientScoreSummaryViaTestTaken($lTestLogID, true,  $testTaken->lNumRightPre,  $testTaken->lNumWrongPre);
            $this->clientScoreSummaryViaTestTaken($lTestLogID, false, $testTaken->lNumRightPost, $testTaken->lNumWrongPost);

            $testTaken->lOriginID     = (int)$row->cptl_lOriginID;
            $testTaken->lLastUpdateID = (int)$row->cptl_lLastUpdateID;
            $testTaken->dteOrigin     = (int)$row->dteOrigin;
            $testTaken->dteLastUpdate = (int)$row->dteLastUpdate;
            $testTaken->strUCFName    = $row->strUCFName;
            $testTaken->strUCLName    = $row->strUCLName;
            $testTaken->strULFName    = $row->strULFName;
            $testTaken->strULLName    = $row->strULLName;

            ++$idx;
         }
      }
   }

   function lAddTestLogEntry(&$testInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
        'INSERT INTO cpp_test_log
         SET '.$this->sqlCommonTestLogEntry($testInfo).",
            cptl_lPrePostID = $testInfo->lPrePostID,
            cptl_lClientID  = $testInfo->lClientID,
            cptl_bRetired   = 0,
            cptl_lOriginID  = $glUserID,
            cptl_dteOrigin  = NOW();";
      $query = $this->db->query($sqlStr);
      $testInfo->lKeyID = $lKeyID = $this->db->insert_id();
      return($lKeyID);
   }

   function updateTestLogEntry($lTestLogID, &$testInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      $sqlStr =
        'UPDATE cpp_test_log
         SET '.$this->sqlCommonTestLogEntry($testInfo)."
         WHERE cptl_lKeyID=$lTestLogID;";
      $query = $this->db->query($sqlStr);
   }

   private function sqlCommonTestLogEntry(&$testInfo){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      global $glUserID;
      return('
           cptl_dtePreTest    = '.strPrepDate($testInfo->dtePreTest).',
           cptl_dtePostTest   = '.strPrepDate($testInfo->dtePostTest).",
           cptl_lLastUpdateID = $glUserID,
           cptl_dteLastUpdate = NOW() ");
   }

   function removeClientTestResults($lTestLogID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $this->removeTestResults($lTestLogID);

      $sqlStr =
        "DELETE FROM cpp_test_log
         WHERE cptl_lKeyID=$lTestLogID;";
      $query = $this->db->query($sqlStr);
   }


   /* ------------------------------------------------------------------
                       S C O R I N G
      ------------------------------------------------------------------ */
   function clientScoreSummaryViaTestTaken($lTestLogID, $bPreTest, &$lNumRight, &$lNumWrong){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strBase = " AND cptr_lTestLogID = $lTestLogID AND ".($bPreTest ? '' : 'NOT ').' cptr_bPreTest ';

      $this->sqlScoringWhere = $strBase." AND cptr_bAnswerCorrect ";
      $lNumRight = $this->lLoadScoreSummary();

      $this->sqlScoringWhere = $strBase." AND NOT cptr_bAnswerCorrect ";
      $lNumWrong = $this->lLoadScoreSummary();
   }

   function lLoadScoreSummary(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRightWrong
         FROM cpp_test_results
         WHERE 1 $this->sqlScoringWhere;";
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRightWrong);
   }
/*
*/

   function loadClientScores($lTestLogID, $lPPTestID, &$lNumQuests, &$QandA){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT
            cpq_lKeyID, cpq_lPrePostID,
            cpq_lSortIDX, cpq_strQuestion, cpq_strAnswer
         FROM  cpp_questions
         WHERE NOT cpq_bRetired
            AND cpq_lPrePostID=$lPPTestID
         ORDER BY cpq_lSortIDX, cpq_lKeyID;";

      $query = $this->db->query($sqlStr);
      $lNumQuests = $query->num_rows();
      $QandA = array();
      if ($lNumQuests > 0) {
         $idx = 0;
         foreach ($query->result() as $row){
            $QandA[$idx] = new stdClass;
            $quest = &$QandA[$idx];

            $quest->lKeyID             = $lQuestID = (int)$row->cpq_lKeyID;
            $quest->lSortIDX           = (int)$row->cpq_lSortIDX;
            $quest->strQuestion        = $row->cpq_strQuestion;
            $quest->strAnswer          = $row->cpq_strAnswer;
            $quest->lTestLogID         = $lTestLogID;

            $quest->bPreTestRight      = $this->bAnswerCorrect($lQuestID, $lTestLogID, true);
            $quest->bPostTestRight     = $this->bAnswerCorrect($lQuestID, $lTestLogID, false);
            ++$idx;
         }
      }
   }

   function bAnswerCorrect($lQuestID, $lTestLogID, $bPreTest){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT cptr_bAnswerCorrect
         FROM cpp_test_results
         WHERE cptr_lQuestionID=$lQuestID
            AND cptr_lTestLogID=$lTestLogID
            AND ".($bPreTest ? '' : 'NOT').' cptr_bPreTest;';
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(false);
      }else {
         $row = $query->row();
         return((boolean)$row->cptr_bAnswerCorrect);
      }
   }

   function removeTestResults($lTestLogID){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "DELETE FROM cpp_test_results
          WHERE cptr_lTestLogID=$lTestLogID;";
      $query = $this->db->query($sqlStr);
   }

   function saveTestResults($lTestLogID, &$QandA){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
         // out with the old
      $this->removeTestResults($lTestLogID);

         // in with the new
      if (count($QandA)==0) return;
      $strInsert =
           'INSERT INTO cpp_test_results (cptr_lQuestionID, cptr_lTestLogID, cptr_bPreTest, cptr_bAnswerCorrect)
               VALUES ';
      foreach ($QandA as $QA){
         $strInsert .=
            "\n($QA->lKeyID,  $lTestLogID,  '1',   ".($QA->bPreTestRight  ? '1' : '0')."),"
           ."\n($QA->lKeyID,  $lTestLogID,  '0',   ".($QA->bPostTestRight ? '1' : '0')."),";
      }
      $strInsert = substr($strInsert, 0, (strlen($strInsert)-1)).';';
      $query = $this->db->query($strInsert);
   }

   function buildScoringTempTable($strTmpTableName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr = "DROP TABLE IF EXISTS $strTmpTableName;";
      $query = $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TEMPORARY TABLE IF NOT EXISTS $strTmpTableName (
         -- CREATE TABLE IF NOT EXISTS $strTmpTableName (
           tmp_lKeyID      int(11) NOT NULL AUTO_INCREMENT,
           tmp_lClientID   int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to client table',
           tmp_lQuestID    int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table cpp_questions',
           tmp_lTestLogID  int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to table cpp_test_log',
           tmp_bPreTest    tinyint(1) NOT NULL DEFAULT '0',
           tmp_bCorrect    tinyint(1) NOT NULL DEFAULT '0',

           PRIMARY KEY (tmp_lKeyID),
           KEY tmp_lClientID  (tmp_lClientID),
           KEY tmp_lQuestID   (tmp_lQuestID),
           KEY tmp_lTestLogID (tmp_lTestLogID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $query = $this->db->query($sqlStr);
   }

   function populateScoringTempTable($strTmpTableName, $lPPTestID, $strBetween){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
         "INSERT INTO $strTmpTableName
           (tmp_lClientID, tmp_lQuestID, tmp_lTestLogID, tmp_bPreTest, tmp_bCorrect)
            SELECT cptl_lClientID, cptr_lQuestionID, cptl_lKeyID, cptr_bPreTest, cptr_bAnswerCorrect
            FROM cpp_test_log
               INNER JOIN cpp_test_results ON cptl_lKeyID=cptr_lTestLogID
            WHERE NOT cptl_bRetired
               AND cptl_lPrePostID=$lPPTestID
               AND ((cptl_dtePreTest  $strBetween) OR (cptl_dtePostTest $strBetween));";
      $query = $this->db->query($sqlStr);

/*
INSERT [LOW_PRIORITY | HIGH_PRIORITY] [IGNORE]
    [INTO] tbl_name
    [PARTITION (partition_name,...)]
    [(col_name,...)]
    SELECT ...
    [ ON DUPLICATE KEY UPDATE
      col_name=expr
        [, col_name=expr] ... ]
*/

   }

   /* ------------------------------------------------------------------
                       R E P O R T S
      ------------------------------------------------------------------ */

   function strBuildTestDDL($strDDLName, $bAddBlank, $lMatchID){
   //---------------------------------------------------------------------
   // assumes user has called loadPPTests
   //---------------------------------------------------------------------
      $strOut = '<select name="'.$strDDLName.'">'."\n";
      if ($bAddBlank) $strOut .= '<option value="-1">&nbsp;</option>'."\n";
      foreach ($this->pptests as $pptest){
         $lTestID = $pptest->lKeyID;
         $strOut .= '<option value="'.$lTestID.'" '.($lMatchID == $lTestID ? 'SELECTED' : '').'>'
               .htmlspecialchars($pptest->strTestName).'</option>'."\n";
      }
      $strOut .= '</select>'."\n";
      return($strOut);
   }

   function strPrePostTestReportExport(&$sRpt, $bReport){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bReport){
         return($this->strPrePostReport($sRpt));
      }else {
         return($this->strPrePostExport($sRpt));
      }
   }

   function strPrePostReport($sRpt){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $ufSchema = new muser_schema;
      $ufSchema->loadUFSchema();
      if (CB_AAYHF) {
         $ufSchema->tableInfoViaUserTableName('Demographics', $demoTableInfo);
         $lDTableID = $demoTableInfo->lTableID;
         $ufSchema->fieldInfoViaUserFieldName($lDTableID, 'Race', $demoTableInfo->fiRace);
         $ufSchema->fieldInfoViaUserFieldName($lDTableID, 'Ethnicity', $demoTableInfo->fiEthnicity);
         $ufSchema->fieldInfoViaUserFieldName($lDTableID, 'Age Range', $demoTableInfo->fiAgeRange);
         $ufSchema->fieldInfoViaUserFieldName($lDTableID, 'Current Grade Level', $demoTableInfo->fiGradeLevel);
      }else {
         $demoTableInfo = null;
      }
      $cClientSearch = new mclient_search;

      $strOut = '';
      $lPPTestID  = $sRpt->lPPTestID;
      $strBetween = $sRpt->strBetween;

         // load test and questions
      $this->loadPPTestsViaPPTID($lPPTestID);
      $pptest = &$this->pptests[0];
      $this->loadQuestionsViaPPTID($lPPTestID);

      $strOut .= $this->strPrePostRptHeader($sRpt, $demoTableInfo);

         // individual questions
      $strTmpTableName          = 'tmp_scores';
      $strTmpClientPopTableName = 'tmp_client_pop';
      $this->buildScoringTempTable($strTmpTableName);
      $this->populateScoringTempTable($strTmpTableName, $lPPTestID, $strBetween);

         // all clients for this test and testing window
      $cClientSearch->createClientPopulationTempTable($strTmpClientPopTableName);
      $strInner =
            "INNER JOIN $strTmpTableName ON cr_lKeyID = tmp_lClientID ";
      $strWhere = '';
      $cClientSearch->populateClientPopulationTable($strTmpClientPopTableName, $strWhere, $strInner);
      $this->generatePerQuestionScores($strTmpTableName, $strTmpClientPopTableName);
      $strOut .= $this->strQResultsDetail('Test Results, By Question');

      return($strOut);
   }

   function strQResultsDetail($strLabel){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut =
         '<table class="enpRpt">
            <tr>
               <td class="enpRptTitle" colspan="7">'.htmlspecialchars($strLabel).'
               </td>
            </tr>';
      $strOut .=
           '<tr>
               <td class="enpRptLabel" rowspan="2">
                  &nbsp;
               </td>
               <td class="enpRptLabel" rowspan="2" style="vertical-align: middle;">
                  Question/Answer
               </td>
               <td class="enpRptLabel" rowspan="1" colspan="2" style="text-align: center;">
                  Pre-Test
               </td>
               <td class="enpRptLabel" rowspan="1" colspan="2" style="text-align: center;">
                  Post-Test
               </td>
               <td class="enpRptLabel" rowspan="2" style="text-align: center;vertical-align: middle;">
                  Improvement
               </td>
            </tr>
            <tr>
               <td class="enpRptLabel" >
                  # Correct
               </td>
               <td class="enpRptLabel" >
                  % Correct
               </td>
               <td class="enpRptLabel" >
                  # Correct
               </td>
               <td class="enpRptLabel" >
                  % Correct
               </td>
            </tr>
            ';

         // each question becomes a row
      $idx = 1;
      $lTotPreRight  = $lTotPreWrong  = $lTotPostRight = $lTotPostWrong = 0;
      foreach ($this->questions as $QA){
         $lTotPreRight  += $QA->score->lPreTestRight;
         $lTotPreWrong  += $QA->score->lPreTestWrong;
         $lTotPostRight += $QA->score->lPostTestRight;
         $lTotPostWrong += $QA->score->lPostTestWrong;

         $lTotAnswers = $QA->score->lPreTestRight  + $QA->score->lPreTestWrong; // +
//                        $QA->score->lPostTestRight + $QA->score->lPostTestWrong;
         if ($lTotAnswers > 0){
            $sngImprovement = (($QA->score->lPostTestRight - $QA->score->lPreTestRight) / $lTotAnswers)*100;
            $strImprove = number_format($sngImprovement, 1).'%';
            $strPreTestRight = number_format(($QA->score->lPreTestRight/
                                    ($QA->score->lPreTestRight+$QA->score->lPreTestWrong))*100, 1).'%';
            $strPostTestRight = number_format(($QA->score->lPostTestRight/
                            ($QA->score->lPostTestRight+$QA->score->lPostTestWrong))*100, 1).'%';
         }else {
            $strImprove       =
            $strPreTestRight  =
            $strPostTestRight = 'n/a';
         }

         $strOut .=
            '<tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;">'
                  .$idx.'
               </td>';
         $strOut .=
              '<td class="enpRpt" style="text-align: left; width: 300pt;"><b>Q: </b>'
                  .nl2br(htmlspecialchars($QA->strQuestion)).'<br><br><b><i>A: </b>'
                  .nl2br(htmlspecialchars($QA->strAnswer)).'</i>
               </td>';

            // pre-test
         $strOut .=
              '<td class="enpRpt" style="text-align: center;">'
                 .number_format($QA->score->lPreTestRight).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                 .$strPreTestRight.'
               </td>';

            // post-test
         $strOut .=
              '<td class="enpRpt" style="text-align: center;">'
                 .number_format($QA->score->lPostTestRight).'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                 .$strPostTestRight.'
               </td>';

            // improvement
         $strOut .= '
            <td class="enpRpt" style="text-align: center; background-color: #d1dee0;"><b>'
               .$strImprove.'</b>
            </td>';
         ++$idx;
      }

         // total
      if ($lTotAnswers > 0){
         $strImprovement = number_format((
                          ($lTotPostRight - $lTotPreRight)/($lTotPreRight + $lTotPreWrong))*100).'%';
         $strRightPre  = number_format($lTotPreRight/($lTotPreRight+$lTotPreWrong)*100, 1).'%';
         $strRightPost = number_format($lTotPostRight/($lTotPostRight+$lTotPostWrong)*100, 1).'%';
      }else {
         $strImprovement =
         $strRightPre    =
         $strRightPost   = 'n/a';
      }
      $strOut .=
         '<tr>
            <td class="enpRptLabel" colspan="2">
               Total
            </td>
            <td class="enpRpt" style="text-align: center;"><b>'
               .number_format($lTotPreRight).'
            </td>
            <td class="enpRpt" style="text-align: center;"><b>'
               .$strRightPre.'</b>
            </td>
            <td class="enpRpt" style="text-align: center;"><b>'
               .number_format($lTotPostRight).'
            </td>
            <td class="enpRpt" style="text-align: center;"><b>'
               .$strRightPost.'</b>
            </td>
            <td class="enpRpt" style="text-align: center; background-color: #8bb9c1;"><b>'
               .$strImprovement.'</b>
            </td>
            ';

      $strOut .= '</table><br>';
      return($strOut);
   }

   function generatePerQuestionScores($strTmpTableName, $strTmpClientPopTableName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $idx = 0;
      foreach ($this->questions as $QA){
         $lQuestID = $QA->lKeyID;
         $QA->score = new stdClass;
         $QA->score->lPreTestRight  = $this->lNumViaQIDClientPop($lQuestID, true,  true,  $strTmpTableName, $strTmpClientPopTableName);
         $QA->score->lPreTestWrong  = $this->lNumViaQIDClientPop($lQuestID, true,  false, $strTmpTableName, $strTmpClientPopTableName);
         $QA->score->lPostTestRight = $this->lNumViaQIDClientPop($lQuestID, false, true,  $strTmpTableName, $strTmpClientPopTableName);
         $QA->score->lPostTestWrong = $this->lNumViaQIDClientPop($lQuestID, false, false, $strTmpTableName, $strTmpClientPopTableName);

         ++$idx;
      }
   }

   function lNumViaQIDClientPop($lQuestID, $bPreTest, $bAnswerRight, $strTmpTableName, $strTmpClientPopTableName){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
          "SELECT COUNT(*) AS lNumRecs
           FROM $strTmpTableName
              INNER JOIN client_records            ON tmp_lClientID  = cr_lKeyID
              INNER JOIN $strTmpClientPopTableName ON tmpc_lClientID = cr_lKeyID
           WHERE tmp_lQuestID=$lQuestID
              AND ".($bAnswerRight ? '' : 'NOT').' tmp_bCorrect
              AND '.($bPreTest     ? '' : 'NOT').' tmp_bPreTest;';
      $query = $this->db->query($sqlStr);
      $row = $query->row();
      return((int)$row->lNumRecs);
   }


   function strPrePostRptHeader($sRpt, $demoTable){
   //-----------------------------------------------------------------------
   // header
   //-----------------------------------------------------------------------
      $lPPTestID = $sRpt->lPPTestID;
      $pptest = &$this->pptests[0];

      $lNumTestsTot = $this->lNumTestsTaken($lPPTestID, $sRpt->strBetween);

      $strOut =
            '<table>
               <tr>
                  <td><b>
                     Test Name:
                  </td>
                  <td>'
                     .htmlspecialchars($pptest->strTestName).'
                  </td>
               </tr>
               <tr>
                  <td><b>
                     Date Range:
                  </td>
                  <td>'
                     .$sRpt->strDateRange.'
                  </td>
               </tr>
               <tr>
                  <td><b>
                     # Tests Taken:
                  </td>
                  <td>'
                     .$lNumTestsTot.'
                  </td>
               </tr>';
      if (is_null($demoTable)){
         $strInner =
               'INNER JOIN client_records ON cr_lKeyID = cptl_lClientID '."\n";
      }else {

         $strTDemo = $demoTable->strDataTableName;
         $strInner =
               'INNER JOIN client_records ON cr_lKeyID = cptl_lClientID
                INNER JOIN '.$strTDemo .' ON cr_lKeyID = '.$demoTable->strDataTableFID."\n";

            // grouped by race
         $strInnerGroup = $strInner.'LEFT JOIN uf_ddl ON ufddl_lKeyID='.$demoTable->fiRace->strFieldNameInternal."\n";
         $this->testsTakenGrouped($lPPTestID, $sRpt->strBetween, '', $strInnerGroup, $lNumGroups, $groups);
         $strOut .= $this->ppRptTotViaGroup('Tests Taken, by Race', $lNumGroups, $groups);

            // grouped by ethnicity
         $strInnerGroup = $strInner.'LEFT JOIN uf_ddl ON ufddl_lKeyID='.$demoTable->fiEthnicity->strFieldNameInternal."\n";
         $this->testsTakenGrouped($lPPTestID, $sRpt->strBetween, '', $strInnerGroup, $lNumGroups, $groups);
         $strOut .= $this->ppRptTotViaGroup('Tests Taken, by Ethnicity', $lNumGroups, $groups);

            // grouped by age
         $strInnerGroup = $strInner.'LEFT JOIN uf_ddl ON ufddl_lKeyID='.$demoTable->fiAgeRange->strFieldNameInternal."\n";
         $this->testsTakenGrouped($lPPTestID, $sRpt->strBetween, '', $strInnerGroup, $lNumGroups, $groups);
         $strOut .= $this->ppRptTotViaGroup('Tests Taken, by Age Range', $lNumGroups, $groups);

            // grouped by school (elementary/middle/high)
         $strInnerGroup = $strInner.'LEFT JOIN uf_ddl ON ufddl_lKeyID='.$demoTable->fiGradeLevel->strFieldNameInternal."\n";
         $this->testsTakenGrouped($lPPTestID, $sRpt->strBetween, '', $strInnerGroup, $lNumGroups, $groups);
         $strOut .= $this->ppRptTotViaGroup('Tests Taken, by School Age', $lNumGroups, $groups);
      }

         // grouped by gender
      $strInnerGroup = '';//$strInner.'LEFT JOIN uf_ddl ON ufddl_lKeyID='.$demoTable->fiGradeLevel->strFieldNameInternal."\n";
      $this->testsTakenViaGender($lPPTestID, $sRpt->strBetween, '', $strInnerGroup, $lNumGroups, $groups);
      $strOut .= $this->ppRptTotViaGroup('Tests Taken, by Gender', $lNumGroups, $groups);

      $strOut .= '</table><br>';
      return($strOut);
   }

   function lNumTestsTaken($lTestID, $strBetween){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs
         FROM cpp_test_log
         WHERE
               NOT cptl_bRetired
            AND cptl_lPrePostID=$lTestID
            AND ((cptl_dtePreTest $strBetween) OR (cptl_dtePostTest $strBetween));";
      $query = $this->db->query($sqlStr);
      if ($query->num_rows() == 0){
         return(false);
      }else {
         $row = $query->row();
         return((int)$row->lNumRecs);
      }
   }

   function testsTakenGrouped($lTestID, $strBetween, $strWhere, $strInner, &$lNumGroups, &$groups){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumGroups = 0;
      $groups = null;

      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs, ufddl_strDDLEntry, ufddl_lKeyID
         FROM cpp_test_log
            $strInner
         WHERE
               NOT cptl_bRetired
            AND cptl_lPrePostID=$lTestID
            AND ((cptl_dtePreTest $strBetween) OR (cptl_dtePostTest $strBetween))
            $strWhere
         GROUP BY ufddl_lKeyID
         ORDER BY ufddl_strDDLEntry, ufddl_lKeyID;";
      $query = $this->db->query($sqlStr);
      $lNumGroups = $query->num_rows();
      if ($lNumGroups > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $groups[$idx] = new stdClass;
            $group = &$groups[$idx];
            $group->lNumRecs     = (int)$row->lNumRecs;
            $group->strDDLEntry  = $row->ufddl_strDDLEntry;
            if (is_null($group->strDDLEntry)) $group->strDDLEntry = '(no data)';
            $group->ufddl_lKeyID = (int)$row->ufddl_lKeyID;

            ++$idx;
         }
      }
   }

   function testsTakenViaGender($lTestID, $strBetween, $strWhere, $strInner, &$lNumGroups, &$groups){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lNumGroups = 0;
      $groups = null;

      $sqlStr =
        "SELECT COUNT(*) AS lNumRecs, cr_enumGender
         FROM cpp_test_log
            INNER JOIN client_records ON cptl_lClientID=cr_lKeyID
         WHERE
               NOT cptl_bRetired
            AND cptl_lPrePostID=$lTestID
            AND ((cptl_dtePreTest $strBetween) OR (cptl_dtePostTest $strBetween))
            $strWhere
         GROUP BY cr_enumGender
         ORDER BY cr_enumGender;";
      $query = $this->db->query($sqlStr);
      $lNumGroups = $query->num_rows();
      if ($lNumGroups > 0){
         $idx = 0;
         foreach ($query->result() as $row){
            $groups[$idx] = new stdClass;
            $group = &$groups[$idx];
            $group->lNumRecs     = (int)$row->lNumRecs;
            $group->strDDLEntry  = $row->cr_enumGender;
            if (is_null($group->strDDLEntry)) $group->strDDLEntry = '(unknown)';
//            $group->ufddl_lKeyID = (int)$row->ufddl_lKeyID;

            ++$idx;
         }
      }
   }

   private function ppRptTotViaGroup($strLabel, $lNumGroups, &$groups){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $strOut = '';
      if ($lNumGroups <= 0) return('');

      $strOut .= '<tr><td colspan="2" style="border-top: 12px solid #fff; border-bottom: 1px solid black;"><b>'.$strLabel.'</b></td></tr>';
      foreach ($groups as $group){
         $strOut .= '
            <tr>
               <td><b>'.htmlspecialchars($group->strDDLEntry).'
               </td>
               <td style="text-align: left;">'.htmlspecialchars($group->lNumRecs).'
               </td>
            </tr>';
      }
      return($strOut);
   }


   function lNumRecsPPTestDirReport(&$sRpt,
                                    $bUseLimits,     $lStartRec,    $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if ($bUseLimits){
         $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";
      }else {
         $strLimit = '';
      }

      $lPPTestID = $sRpt->lPPTestID;

      $sqlStr =
           "SELECT DISTINCT cptl_lClientID
            FROM cpp_test_log
            WHERE NOT cptl_bRetired
               AND cptl_lPrePostID=$lPPTestID
            ORDER BY cptl_lClientID
            $strLimit;";
      $query = $this->db->query($sqlStr);
      return($query->num_rows());
   }

   function strPrePostClientDirReportExport(
                              $sRpt,    $reportID,
                              $bReport, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $lPPTestID = $sRpt->lPPTestID;
      $this->loadPPTestsViaPPTID($lPPTestID);

      if ($bReport){
         return($this->strPrePostClientDirReport($lPPTestID, $sRpt, $lStartRec, $lRecsPerPage));
      }else {
         return($this->strPrePostClientDirExport($sRpt));
      }
   }

   function strPrePostClientDirReport($lPPTestID, &$sRpt, $lStartRec, $lRecsPerPage){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      $pptest = &$this->pptests[0];
      $strTestSafeName = htmlspecialchars($pptest->strTestName);

      $strLimit = " LIMIT $lStartRec, $lRecsPerPage ";

      $strTT = 'tmpCPPDir';
      $sqlStr = "DROP TABLE IF EXISTS $strTT;";
      $query = $this->db->query($sqlStr);

      $sqlStr =
        "CREATE TEMPORARY TABLE IF NOT EXISTS $strTT (
         -- CREATE TABLE IF NOT EXISTS $strTT (
           tmp_lKeyID      int(11) NOT NULL AUTO_INCREMENT,
           tmp_lClientID   int(11) NOT NULL DEFAULT '0' COMMENT 'Foreign key to client table',

           PRIMARY KEY (tmp_lKeyID),
           KEY tmp_lClientID  (tmp_lClientID)
         ) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
      $query = $this->db->query($sqlStr);

      $sqlStr =
         "INSERT INTO $strTT
           (tmp_lClientID)
            SELECT DISTINCT cptl_lClientID
            FROM cpp_test_log
            WHERE NOT cptl_bRetired
               AND cptl_lPrePostID=$lPPTestID
            ORDER BY cptl_lClientID
            $strLimit;";
      $query = $this->db->query($sqlStr);

      $sqlStr =
         "SELECT cr_lKeyID, cr_strFName, cr_strLName
          FROM client_records
             INNER JOIN $strTT ON tmp_lClientID=cr_lKeyID
          ORDER BY cr_strLName, cr_strFName, cr_lKeyID;";

      $query = $this->db->query($sqlStr);

      $lNumRows = $query->num_rows();
      if ($lNumRows == 0){
         return('<br><i>There are no clients in your database who have taken test <b>'.$strTestSafeName.'</b>.</i><br><br>');
      }

      $strOut =
          '<table class="enpRptC">
             <tr>
                <td colspan="3" class="enpRptTitle">'
                   .$strTestSafeName.'
                </td>
             </tr>
             <tr>
                <td class="enpRptLabel">
                   &nbsp;
                </td>
                <td class="enpRptLabel">
                   &nbsp;
                </td>
                <td class="enpRptLabel">
                   Name
                </td>
             </tr>';

      $idx = 1;
      foreach ($query->result() as $row){
         $lClientID = $row->cr_lKeyID;
         $strOut .=
            '<tr class="makeStripe">
               <td class="enpRpt" style="text-align: center;">'
                  .$idx.'
               </td>
               <td class="enpRpt" style="text-align: center;">'
                  .str_pad($lClientID, 5, '0', STR_PAD_LEFT).'&nbsp;'
                  .strLinkView_ClientRecord($lClientID, 'View client record', true).'
               </td>
               <td class="enpRpt">'
                  .htmlspecialchars($row->cr_strLName.', '.$row->cr_strFName).'
               </td>
             </tr>';
         ++$idx;
      }

      $strOut .= '</table>';
      return($strOut);
   }




}





