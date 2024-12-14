<?php
header('Content-Type: application/json');
require_once('../assets/includes/core.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch (secureEncode($_GET['action'])) {
        case 'respond':
            $fake = secureEncode($_GET['uid1']);
            $client = secureEncode($_GET['uid2']);
            $check = getData('users','fake','where id ='.$fake);
            $sub = getData('users_subscriptions','time','where u1 ='.$client.' and u2 ='.$fake);

            if ($check == 1) {
                $apiResponse = answerMessageAI($fake, $client,$sub);
                echo $apiResponse;
            } else {
                echo json_encode(['send' => 'NO']);
            }
        break;

        case 'follow':
            $fake = secureEncode($_GET['uid1']);
            $client = secureEncode($_GET['uid2']);
            $check = getData('users','fake','where id ='.$fake);

            if ($check == 1) {
                $apiResponse = followMessageAI($fake, $client);
                echo $apiResponse;
            } else {
                echo json_encode(['send' => 'NO']);
            }
        break;

        case 'visit':
            $fake = secureEncode($_GET['uid1']);
            $client = secureEncode($_GET['uid2']);
            $check = getData('users','fake','where id ='.$fake);

            if ($check == 1) {
                $apiResponse = visitMessageAI($fake, $client);
                echo $apiResponse;
            } else {
                echo json_encode(['send' => 'NO']);
            }
        break;  

        case 'giftFromModel':
            $fake = secureEncode($_GET['uid1']);
            $client = secureEncode($_GET['uid2']);
            $check = getData('users','fake','where id ='.$fake);

            if ($check == 1) {
                $apiResponse = giftFromModelMessageAI($fake, $client);
                echo $apiResponse;
            } else {
                echo json_encode(['send' => 'NO']);
            }
        break;                

        default:
        break;
    }
}

function followMessageAI($fake,$client){
	global $mysqli,$sm;
	$randomComment = getData('fake_comments','comment','order by rand() limit 1');
	$username = getData('users','username','where id ='.$client);
	$siteName = $sm['config']['name'];
	$message = '{
	    "role": "user",
	    "content": "Hi my name is '.$username.', i just followed your channel here at '.$siteName.', '.$randomComment.'"
	}';

	return answerToConv($fake,$message);
}

function visitMessageAI($fake,$client){
	global $mysqli,$sm;
	$randomComment = getData('fake_comments','comment','order by rand() limit 1');
	$username = getData('users','username','where id ='.$client);
	$siteName = $sm['config']['name'];
	$message = '{
	    "role": "user",
	    "content": "Im '.$username.' and im looking around i just visited your profile at '.$siteName.' very interesting content, '.$randomComment.'"
	}';

	return answerToConv($fake,$message);
}

function giftFromModelMessageAI($fake,$client){
	global $mysqli,$sm;
	$username = getData('users','username','where id ='.$client);
	$siteName = $sm['config']['name'];
	$message = '{
	    "role": "user",
	    "content": "You just sent me a very generous digital media gift and its very interesting, tell me something nice,sexy,horny or sexual to cheer me up"
	}';

	return answerToConv($fake,$message);
}

function answerMessageAI($fake,$client,$sub){	
	global $mysqli,$sm;
	$message = '';

	$select = 'SELECT * FROM (
	  SELECT * 
	  FROM chat 
	  WHERE (r_id = '.$fake.' AND s_id = '.$client.') 
	    OR (r_id = '.$client.' AND s_id = '.$fake.')  
	  ORDER BY id DESC
	  LIMIT 8
	) AS `messages` ORDER by id ASC';

	$query = $mysqli->query($select);
	$i = 0;
	
	if ($query->num_rows > 0) { 
		while($q = $query->fetch_object()){
			$i++;

			$content = strip_tags($q->message);
			$content = str_replace("\r\n", ' ', $content);
			if($q->s_id == $client){
				$message.= '
			      {
			        "role": "user",
			        "content": "'.$content.'"
			      },';				
			} else {
				$message.= '
			      {
			        "role": "assistant",
			        "content": "'.$content.'"
			      },';			
			}
			if($i == $query->num_rows){
				$message = rtrim($message, ',');
			}
		}	
	}
	return answerToConv($fake,$message,$sub);
}

function answerToConv($fake,$conv,$sub='noData') {
    global $sm;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    $mood = $sm['plugins']['aiautoresponder']['responder_mood'];

    if ($mood == 'Random') {
        $moods = getData('plugins_settings', 'setting_options', 'WHERE setting = "responder_mood"');
        $moodsArray = explode(',', $moods);
        unset($moodsArray[11]);
        $r = array_rand($moodsArray, 1);
        $mood = $moodsArray[$r];
    }

    $username = getData('users','username','where id = '.$fake);

    if($sub != 'noData'){
    $prompt = "You are a very powerful sexy girl your name is ".$username." and you are a digital content creator, you like to enjoy life and have fun, traveling, eating exotic foods, going to the beach, partying at night clubs, post explicit content in your feed and talk sexy in the private chat, you like to create good content for your subscribers, the subscription is not free but this user is subscribed to your channel. Answer the conversation with short standard responses be charm and horny, dont answer with hashtags, be human dont answer like a robot, if the user asks for some specific private content for him you must tell him to send you a good tip first and you will make it for him gratefully";
    } else {
    $prompt = "You are a very powerful sexy girl your name is ".$username." and you are a digital content creator, you like to enjoy life and have fun, traveling, eating exotic foods, going to the beach, partying at night clubs you also post explicit content in your feed but not always, you like to create good content for your subscribers, you must convince the user to be a subscriber of your channel do whatever it takes to make the user subscribe to your channel in ".$sm['config']['name'].", the subscription is not free, user must pay for subscribe to your channel. Answer the conversation with short standard responses be realistic and horny, dont answer with hashtags, be human dont answer like a robot";
    }

	curl_setopt($ch, CURLOPT_POSTFIELDS, '{
		"model": "gpt-3.5-turbo",
		"messages": [
		  {
		    "role": "system",
		    "content": "'.$prompt.'"
		  },
		  '.$conv.'
		],
		"temperature": 0.5,
		"top_p": 0.5
	}');

    $headers = [];
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: Bearer ' . $sm['plugins']['aiautoresponder']['secret'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        error_log('Curl error: ' . curl_error($ch));
        curl_close($ch);
        return json_encode(['send' => 'NO', 'error' => curl_error($ch)]);
    }
    curl_close($ch);

    $answer = json_decode($result);
    if ($answer === null || empty($answer->choices[0]->message->content)) {
        return json_encode(['send' => 'NO', 'error' => 'Error API']);
    }

    return json_encode(['send' => 'YES', 'msg' => $answer->choices[0]->message->content, 'name' => $username, 'photo' => profilePhoto($fake)]);
}