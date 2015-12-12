<?php //if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class pre_misc extends CI_Controller {

   function __construct(){
      parent::__construct();
      session_start();
      setGlobals($this);
   }








}