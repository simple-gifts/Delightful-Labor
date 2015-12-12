<?php
echoT('
<div class="login">
   <div class="loginWelcome">
      <img src="'.base_url().'images/layout/dl_logo02.png"><br>
      <img src="'.base_url().'images/layout/dl_logo03.png">
   </div>
   <h3>Please log in:</h3>');
   
   if ($this->session->flashdata('error')){
      echo('<div class="error" style="width: 300px;">'.$this->session->flashdata('error')
          .'</div>');
   }
   $udata = array('name' => 'username', 'id' => 'u', 'size' => 15);
   $pdata = array('name' => 'password', 'id' => 'p', 'size' => 15);

   $attributes = array('name' => 'frmLogin', 'id' => 'frmAddEdit');
   echo(form_open('login', $attributes));
   echo('<p><label for="u">Username</label><br />'
         .form_input($udata).'</p>
         <p><label for="p">Password</lable><br />'
         .form_password($pdata).'</p>'
         .'<input type="submit" name="cmdSubmit" value="Log In"
               onclick="this.disabled=1; this.form.submit();"
               style="text-align: center; width: 95pt;"
               class="btn"
               onmouseover="this.className=\'btn btnhov\'"
               onmouseout="this.className=\'btn\'">'
         .form_close());
   echo('<script type="text/javascript">frmAddEdit.u.focus();</script>');

   echoT('</div>');
