<?php
/*---------------------------------------------------------------------
   Delightful Labor
   copyright (c) 2012-2013 Database Austin

   author: John Zimmerman

   This software is provided under the GPL.
   Please see http://www.gnu.org/copyleft/gpl.html for details.
---------------------------------------------------------------------*/

class install extends CI_CONTROLLER {

   function __construct(){
      parent::__construct();
      session_start();
   }

   function index(){
      redirect('admin/assign_db/dbform');
   }
}
   