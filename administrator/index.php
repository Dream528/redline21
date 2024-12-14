<?php
/* RedLine21 By Alfred - support@redline21.com - https://www.redline21.com/ */

$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];	
header('Location: '.$actual_link.'/admin');
return true;
