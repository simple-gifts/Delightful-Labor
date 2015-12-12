<?php
echoT('
<div class="login">
   <div class="loginWelcome">
      <img src="'.base_url().'images/layout/dl_logo02.png"><br>
      <img src="'.base_url().'images/layout/dl_logo03.png">
   </div>
   
   <h3>Configure your Database</h3>');
   
   if ($strErr!=''){
      echoT('<div class="formInstallError">'.$strErr.'</div>');
   }
   
   if ($this->session->flashdata('error')){
      echo('<div class="error" style="width: 300px;">'.$this->session->flashdata('error')
          .'</div>');
   }
   $dbName      = array('name' => 'dbName',     'id' => 'dbName',    
                     'value'=> $formData->dbName,     'size' => 25, 'style'=>'margin-bottom: 4px;');
   $dbHostName  = array('name' => 'dbHostName', 'id' => 'hostName',  
                     'value'=> $formData->dbHostName, 'size' => 25, 'style'=>'margin-bottom: 4px;');
   $dbUserName  = array('name' => 'dbUserName', 'id' => 'dbUserName',
                     'value'=> $formData->dbUserName, 'size' => 25, 'style'=>'margin-bottom: 4px;');
   $pdata       = array('name' => 'dbPassword', 'id' => 'pword',     
                     'value'=> $formData->dbPWord,    'size' => 25, 'style'=>'margin-bottom: 4px;');

   $attributes = array('name' => 'frmDBConfigure', 'id' => 'frmdbConfigure');
   echo(form_open('admin/assign_db/dbform', $attributes));
   echo('
          <p><label for="hostName">Host Name</label><br />'
         .form_input($dbHostName).form_error('dbHostName').'</p>
         
          <p><label for="dbName">Database Name</label><br />'
         .form_input($dbName).form_error('dbName').'</p>
         
          <p><label for="dbUserName">Database User</label><br />'
         .form_input($dbUserName).form_error('dbUserName').'</p>
         
         <p><label for="p">Database User Password</label><br />'
         .form_password($pdata).form_error('dbPassword').'</p>'
         
         .'<input type="submit" name="cmdSubmit" value="Install"
               onclick="this.disabled=1; this.form.submit();"
               style="text-align: center; width: 95pt;"
               class="btn"
               onmouseover="this.className=\'btn btnhov\'"
               onmouseout="this.className=\'btn\'">'
         .form_close());
   echo('<script type="text/javascript">frmdbConfigure.hostName.focus();</script>');

   echoT('</div>');
