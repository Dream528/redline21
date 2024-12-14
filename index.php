<?php
/* Adult Sexting Software by https://redline21.com/ 
if(!file_exists("assets/includes/config.php")){
	$actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$cb = substr($actual_link, -1);
	if($cb == '/'){
		header('Location: '.$actual_link.'install/');	
	} else {
		header('Location: '.$actual_link.'/install/');	
	}	
	exit;
} */

require_once('assets/includes/core.php');

if (!isset($_GET['page']) && !isset($_GET['social'])) {
	if($sm['user']['guest'] == 1){
		$_GET['page'] = 'landing';
	} else {
		$_GET['page'] = 'feed';
	}	
    
}

if(isset($_GET['page'])){

	if($sm['user']['admin'] == 1 && $_GET['page'] != 'admin' && $_GET['page'] != 'logout'){
		header('Location:'.$sm['config']['site_url'].'admin');
		exit;
	}

	switch ($_GET['page']) {

		case 'verification':
			if ($logged === false ) {
				showLandingPage();
				exit;
			} else {	
				$ussid = secureEncode($_GET['uid']);
				$mysqli->query('UPDATE users set verified = 1 where id = "'.$ussid.'"');
				$pass = secureEncode($_GET['b']);
				welcomeMailNotification($sm['user']['name'],$sm['user']['email'],$pass);
				header('Location:'.$sm['config']['site_url']);
				exit;			
			}
		break;	

		case 'ref':
			setcookie(
			  "ref",
			  secureEncode($_GET['id']),
			  time() + (10 * 365 * 24 * 60 * 60), '/', NULL, 0
			);
			header('Location:'.$sm['config']['site_url']);
			exit();
		break;

		case 'recover':
			if($_GET['id'] != '' && $_GET['code'] != ''){
			$check = checkRecoverCode(secureEncode($_GET['id']),secureEncode($_GET['code']));
				if($check > 0){
					$_SESSION['user'] = secureEncode($_GET['id']);
					header('Location:'.$sm['config']['site_url']);
				} else {
					showLandingPage();
					exit;						
				}
			} else {
				showLandingPage();
				exit;			
			}
		break;

		case 'terms':
			$page = 'legal';
			include('assets/sources/pages.php');
		break;
	
		case 'privacy':	
			$page = 'legal';
			include('assets/sources/pages.php');
		break;

		case 'cookies':
			$page = 'legal';
			include('assets/sources/pages.php');
		break;

		case 'dmca':
			$page = 'legal';
			include('assets/sources/pages.php');
		break;	

		case '2257':
			$page = 'legal';
			include('assets/sources/pages.php');
		break;							

		case 'admin':
			if ($logged !== true || $sm['user']['admin'] == 0) {
				echo getAdministratorPage('login');
				exit;
			}
			$p = '';
			if(isset($_GET['p'])){
				$p = secureEncode($_GET['p']);
			}
			if($p == ''){
				$sm['content'] = getAdministratorPage('main_dashboard');
			} else {
				$sm['content'] = getAdministratorPage($p);	
			}
			echo getAdministratorPage('index');
			exit;
		break;

		case 'cp':
			if ($logged !== true || $sm['user']['admin'] == 0) {
				echo getAdministratorPage('login');
				exit;
			}
			$p = '';
			if(isset($_GET['p'])){
				$p = secureEncode($_GET['p']);
			}
			if($p == ''){
				$sm['content'] = getAdministratorPage('main_dashboard');
			} else {
				$sm['content'] = getAdministratorPage($p);	
			}
			echo getAdministratorPage('index');
			exit;
		break;

		case 'logout':
			include('assets/sources/logout.php');
			exit;
		break;

		case 'feed':
			$page = 'feed';
			include('assets/sources/pages.php');
		break;

		case 'post':
			$page = 'post';
			include('assets/sources/pages.php');
		break;

		case 'settings':
			if($sm['user']['guest'] == 1){
				$page = 'landing';
			} else {
				$page = 'settings';
			}
			include('assets/sources/pages.php');
		break;	

		case 'discover':	
			$page = 'discover';
			include('assets/sources/pages.php');
		break;

		case 'posts':	
			$page = 'posts';
			include('assets/sources/pages.php');
		break;		

		case 'live':	
			$page = 'live';
			include('assets/sources/pages.php');
		break;	

		case 'reels':	
			$page = 'reels';
			include('assets/sources/pages.php');
		break;				

		case 'landing':
			if($sm['user']['guest'] == 1){
				$page = 'landing';
			} else {
				$page = 'feed';
			}
			include('assets/sources/pages.php');
		break;					

		default:
			$pid = secureEncode($_GET['page']);
			$checkUsername = checkIfExist('users','username',$pid);
			$checkId = checkIfExist('users','id',$pid);
			if($checkUsername == 0 && $checkId == 0){
				header('Location:'.$sm['config']['site_url']);
				exit;					
			} else {
				if($pid == $sm['user']['id'] || $pid == $sm['user']['username']){
					$sm['profile'] = $sm['user'];
					if($sm['user']['creator'] == 0){
						$page = 'profile-me';
					} else {
						getUserInfo($pid,1);
						$page = 'profile';	
					}
					
				} else {
					getUserInfo($pid,1);
					if($sm['profile']['creator'] == 0){
						header('Location:'.$sm['config']['site_url']);
					}
					$page = 'profile';
				}
				include('assets/sources/pages.php');
			}
		break;	
	}
}


if(isset($_GET['social'])){
	switch ($_GET['social']) {
		case 'twitter':
			include('assets/sources/twitterconnect.php');
			exit;
		break;	
		case 'google':
			include('assets/sources/googleconnect.php');
			exit;
		break;		
	}	
}


function showLandingPage(){
	global $sm;
	$landing = getLandingPage('landing');	
	if($sm['plugins']['htmlsecurity']['enabled'] == 'Yes'){
		$landing = preg_replace('/\r|\n/','',$landing);
	}
	echo $landing;	
}

$container = getPage('container');	
if($sm['plugins']['htmlsecurity']['enabled'] == 'Yes'){
	$container = preg_replace('/\r|\n/','',$container);
}

echo $container;
$mysqli->close();