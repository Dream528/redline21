<?php
if($mobile == true){
	$sm['content'] = getMobilePage($page);
} else {
	$sm['content'] = getPage($page);
}