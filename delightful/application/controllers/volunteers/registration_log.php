<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class registration_log extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }

   public function view(){
echoT('Work in progress');
   }
   
   
   
}