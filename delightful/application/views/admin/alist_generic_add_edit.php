<?php
   $attributes = array('name' => 'frmGen', 'id' => 'idFormGenAddEdit');
   echo form_open('admin/alists_generic/addEdit/'.$strListType.'/'.$listItem->id, $attributes);
   echoT('
      <br>
      <table class="enpRptC">
           <tr>
              <td class="enpRptTitle" colspan="2" style="text-align: center;">'
                 .$strAddEditTableTitle.'
              </td>
           </tr>');

   echoT(
          '<tr>
              <td class="enpRpt" style="padding-top: 7px;">'
                 .$strAddEditTableItemLabel.'
              </td>
              <td class="enpRpt">');
   $data = array('name'      =>'txtListItem',
                 'id'        =>'listItem',
                 'size'      =>35, 
                 'maxlength' => 60,
                 'value'     => $listItem->name);
   echoT(form_input($data));

   echoT(form_error('txtListItem'));
   echoT('
              </td>
           </tr>');

   echoT(
         '<tr>
              <td align="left" colspan="5" class="enpRpt" style="text-align: center;">
                 <input type="submit" name="cmdAdd"
                       value="'.$strAddEditTableButton.'"
                       onclick="this.disabled=1; this.form.submit();"
                       class="btn"
                          onmouseover="this.className=\'btn btnhov\'"
                          onmouseout="this.className=\'btn\'">
              </td>
              </form>
              <script type="text/javascript">idFormGenAddEdit.listItem.focus();</script>
           </tr>');

   echoT(
      '</table></form><br>');