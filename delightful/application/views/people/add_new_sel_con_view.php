<?php
// form setup

$attributes = array('name'     => 'frmSelHH',
                    'onSubmit' => ' return verifySelHHForm(frmSelHH) '
                    );

echoT(form_open('people/people_add_new/selConSelect', $attributes));
echoT('<h3>Household for new person:</h3>');



   //--------------------------------------
   // new household
   //--------------------------------------
$data = array(
    'name'        => 'rdoHH',
    'id'          => 'newH',
    'value'       => 'new',
    'checked'     => TRUE,
    );   
echoT('<p>'. form_radio($data).' <label for="newH">New household</label></p>');

   //--------------------------------------
   // existing household
   //--------------------------------------
$data = array(
    'name'        => 'rdoHH',
    'id'          => 'existingH',
    'value'       => 'existing',
    'checked'     => false,
    );   
echoT('<p>'. form_radio($data).' <label for="existingH">Existing household</label>');

$data = array(
              'name'        => 'txtHH',
              'value'       => '',
              'maxlength'   => '10',
              'size'        => '5',
              'onfocus'     => "setRadioOther(frmSelHH.rdoHH,'existing');"
            );

echoT(form_input($data).' first few letters of household (i.e. "smith")'.'</p>');

//echo "<p><label for='catname'>Name</label><br/>";
//$data = array('name'=>'name','id'=>'catname','size'=>25, 'value' => $category['name']);
//echo form_input($data) ."</p>";

   //--------------------------------------
   // existing household
   //--------------------------------------
$strButtonExtra =
       'name="cmdSubmit" 
        class="btn" 
        onmouseover="this.className=\'btn btnhov\'"
        onmouseout="this.className=\'btn\'" ';  

echo form_submit('submit','Next', $strButtonExtra);
echo form_close();


?>