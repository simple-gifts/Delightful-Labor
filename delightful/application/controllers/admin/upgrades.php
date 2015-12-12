<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class upgrades extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   function upgrade(){
   //---------------------------------------------------------------------
   //
   //---------------------------------------------------------------------
      if (!bTestForURLHack('adminOnly')) return;

      $displayData = array();
      $displayData['status'] = '';
      $displayData['bSuccess'] = true;

      $this->load->model('admin/muser_accts', 'clsUser');
      $this->load->model('admin/mupgrade', 'clsUpgrade');
      $this->load->model('admin/mupgrade_01_007', 'mup01_007');
      $this->load->model('personalization/muser_fields',        'clsUF');
      $this->load->model('personalization/muser_fields_create', 'clsUFC');
      $this->load->helper('admin/upgrade_tz');

      $this->clsUser->versionLevel();
      $displayData['versionInfo'] = &$this->clsUser->versionInfo;
      $strCurrentVersion = $this->clsUser->versionInfo->dbVersion;

      switch ($strCurrentVersion){
         case '0.900':
            $displayData['status'] .= $this->clsUpgrade->upgrade_00_900_to_00_901();
            $displayData['status'] .= $this->clsUpgrade->upgrade_00_901_to_00_902();
            $displayData['status'] .= $this->clsUpgrade->upgrade_00_902_to_01_000();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_000_to_01_001();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_001_to_01_002();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_002_to_01_003();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_003_to_01_004();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_004_to_01_005();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_005_to_01_006();
            $displayData['status'] .= $this->mup01_007->upgrade_01_006_to_01_007();
            $displayData['status'] .= $this->mup01_007->upgrade_01_007_to_01_008();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_008_to_01_009();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '0.901':
            $displayData['status'] .= $this->clsUpgrade->upgrade_00_901_to_00_902();
            $displayData['status'] .= $this->clsUpgrade->upgrade_00_902_to_01_000();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_000_to_01_001();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_001_to_01_002();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_002_to_01_003();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_003_to_01_004();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_004_to_01_005();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_005_to_01_006();
            $displayData['status'] .= $this->mup01_007->upgrade_01_006_to_01_007();
            $displayData['status'] .= $this->mup01_007->upgrade_01_007_to_01_008();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_008_to_01_009();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '0.902':
            $displayData['status'] .= $this->clsUpgrade->upgrade_00_902_to_01_000();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_000_to_01_001();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_001_to_01_002();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_002_to_01_003();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_003_to_01_004();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_004_to_01_005();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_005_to_01_006();
            $displayData['status'] .= $this->mup01_007->upgrade_01_006_to_01_007();
            $displayData['status'] .= $this->mup01_007->upgrade_01_007_to_01_008();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_008_to_01_009();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.000':
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_000_to_01_001();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_001_to_01_002();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_002_to_01_003();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_003_to_01_004();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_004_to_01_005();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_005_to_01_006();
            $displayData['status'] .= $this->mup01_007->upgrade_01_006_to_01_007();
            $displayData['status'] .= $this->mup01_007->upgrade_01_007_to_01_008();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_008_to_01_009();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.001':
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_001_to_01_002();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_002_to_01_003();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_003_to_01_004();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_004_to_01_005();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_005_to_01_006();
            $displayData['status'] .= $this->mup01_007->upgrade_01_006_to_01_007();
            $displayData['status'] .= $this->mup01_007->upgrade_01_007_to_01_008();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_008_to_01_009();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.002':
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_002_to_01_003();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_003_to_01_004();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_004_to_01_005();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_005_to_01_006();
            $displayData['status'] .= $this->mup01_007->upgrade_01_006_to_01_007();
            $displayData['status'] .= $this->mup01_007->upgrade_01_007_to_01_008();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_008_to_01_009();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.003':
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_003_to_01_004();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_004_to_01_005();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_005_to_01_006();
            $displayData['status'] .= $this->mup01_007->upgrade_01_006_to_01_007();
            $displayData['status'] .= $this->mup01_007->upgrade_01_007_to_01_008();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_008_to_01_009();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.004':
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_004_to_01_005();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_005_to_01_006();
            $displayData['status'] .= $this->mup01_007->upgrade_01_006_to_01_007();
            $displayData['status'] .= $this->mup01_007->upgrade_01_007_to_01_008();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_008_to_01_009();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.005':
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_005_to_01_006();
            $displayData['status'] .= $this->mup01_007->upgrade_01_006_to_01_007();
            $displayData['status'] .= $this->mup01_007->upgrade_01_007_to_01_008();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_008_to_01_009();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.006':
            $displayData['status'] .= $this->mup01_007->upgrade_01_006_to_01_007();
            $displayData['status'] .= $this->mup01_007->upgrade_01_007_to_01_008();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_008_to_01_009();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.007':
            $displayData['status'] .= $this->mup01_007->upgrade_01_007_to_01_008();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_008_to_01_009();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.008':
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_008_to_01_009();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.009':
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_009_to_01_010();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.010':
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_010_to_01_011();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.011':
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_011_to_01_012();
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
         case '1.012':
            $displayData['status'] .= $this->clsUpgrade->upgrade_01_012_to_01_013();
            break;
            
            
         default:
            $displayData['bSuccess'] = false;
            $displayData['status'] = '<font color="red"><b>UNSUPPORTED UPGRADE '.$strCurrentVersion.'</b></font>';
            break;
      }
      $this->load->view('upgrade_db_progress_view', $displayData);
   }



}
