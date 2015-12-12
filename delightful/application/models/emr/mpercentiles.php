<?php
/*---------------------------------------------------------------------
// Delightful Labor
// copyright (c) 2013 Database Austin
//
// author: John Zimmerman
//
// This software is provided under the GPL.
// Please see http://www.gnu.org/copyleft/gpl.html for details.
  ---------------------------------------------------------------------
      $this->load->model('emr/mpercentiles', 'percentiles');
  ---------------------------------------------------------------------

---------------------------------------------------------------------*/


class mpercentiles extends CI_Model{
   public $pRecord;


   function __construct() {
   //-----------------------------------------------------------------------
   // constructor
   //-----------------------------------------------------------------------
		parent::__construct();
      $this->pRecord = new stdClass;
   }

   
   
}
