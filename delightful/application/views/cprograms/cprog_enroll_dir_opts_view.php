<?php

   echoT('<br>'
      .strLinkView_CProgDirEnrollment($lCProgID, !$bActive, 'Show '.($bActive ? 'inactive' : 'active').' enrollment', true).'&nbsp;'
      .strLinkView_CProgDirEnrollment($lCProgID, !$bActive, 'Show '.($bActive ? 'inactive' : 'active').' enrollment', false).'<br>');


   openBlock('Enrollment ('.($bActive ? 'active' : 'inactive').'): '
              .htmlspecialchars($cprog->strProgramName), '');

   closeBlock();

// -------------------------------------
echo('<font class="debug">'.substr(__FILE__, strrpos(__FILE__, '\\'))
   .': '.__LINE__.'<br>$cprog   <pre>');
echo(htmlspecialchars( print_r($cprog, true))); echo('</pre></font><br>');
// -------------------------------------


