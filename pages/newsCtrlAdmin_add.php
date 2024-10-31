<?php
/*
If you want to contribute to this plugin, please download the source code.

This code has been compiled. It is significantly easier to edit the source code than this compiled version.


*/

require_once dirname(dirname(__FILE__)) . '/ne_includes.php';



include dirname(__FILE__) . '/views/newsCtrlAdmin/add.phtml';

if(ne_sessionHelper_formHasErrors()) ne_sessionHelper_clearErrors();


?>
