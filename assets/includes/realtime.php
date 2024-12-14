<?php
/* BellooRed Software by https://belloored.com */
$check_bar = substr($site_url, -1);
if($check_bar != '/'){
    $site_url = $site_url.'/'; 
}

$mysqli = new mysqli($db_host, $db_username, $db_password,$db_name);
if (mysqli_connect_errno()) {
    exit(mysqli_connect_error());
}

function checkUnreadMessages($uid){
    global $mysqli;
    $query = $mysqli->query("SELECT count(id) as total FROM chat WHERE r_id = '".$uid."' AND seen = 0");    
    $total = $query->fetch_assoc();
    return $total['total']; 
}

function profilePhoto($uid,$big=0) {
    global $mysqli,$site_url;
    $uid = secureEncode($uid);
    $photo = $mysqli->query("SELECT photo,thumb FROM users_photos where u_id = '".$uid."' and profile = 1 order by id asc LIMIT 1");
    if($photo->num_rows == 1) {
        $profile = $photo->fetch_object();  
        if($big == 1){
            $profile_photo = $profile->photo;
        } else {
            $profile_photo = $profile->thumb;
        }
        
    } else {
        $profile_photo = $site_url."themes/default/images/no_user.png";
    }
    return $profile_photo;
}

function pluginsData($p,$val) {
    global $mysqli;
    $config = $mysqli->query("SELECT setting_val FROM plugins_settings_values where plugin = '".$p."' and setting = '".$val."'");
    $result = $config->fetch_object();
    return $result->setting_val;
}

function getLang($id,$langid) {
    global $mysqli;
    $config = $mysqli->query("SELECT text FROM site_lang where id = '".$id."' and lang_id = '".$langid."'");
    $result = $config->fetch_object();
    return $result->text;
}

function getUserLang($id) {
    global $mysqli;
    $config = $mysqli->query("SELECT lang FROM users where id = '".$id."'");
    $result = $config->fetch_object();
    return $result->lang;
}


function pusherData($val) {
    global $mysqli;
    $config = $mysqli->query("SELECT setting_val FROM plugins_settings_values where plugin = 'pusher' and setting = '".$val."'");
    $result = $config->fetch_object();
    return $result->setting_val;
}

 
function secureEncode($string) {
    $string = trim($string);
    $string = htmlspecialchars($string, ENT_QUOTES);
    $string = str_replace('\\r\\n', '<br>',$string);
    $string = str_replace('\\r', '<br>',$string);
    $string = str_replace('\\n\\n', '<br>',$string);
    $string = str_replace('\\n', '<br>',$string);
    $string = str_replace('\\n', '<br>',$string);
    $string = stripslashes($string);
    $string = str_replace('&amp;#', '&#',$string);
    return $string;
}

function requestPage($page_url='',$type='',$sm='') {
    global $sm;
    if($type == 'Desktop'){
        $theme = getData('settings','setting_val','WHERE setting = "desktopTheme"');
    }
    $page = '../themes/' . $theme . '/layout/' . $page_url . '.phtml';
    $page_content = '';
    ob_start();
    include($page);
    $page_content = ob_get_contents();
    ob_end_clean();
    return $page_content;
}

function getData($table,$col,$filter=''){
    global $mysqli;
    $q = $mysqli->query("SELECT $col FROM $table $filter");
    $r = $q->fetch_object();
    return  $r->$col;   
}

function getNewMessages($uid1,$uid2) {
    global $mysqli;
    $result = 0;
    $query = $mysqli->query("SELECT count(*) as total FROM chat where r_id = '".$uid1."' and s_id = '".$uid2."' and seen = 0");
    $total = $query->fetch_assoc();
    if($total['total'] >= 1){
        $result = $total['total'];
    }
    return $result;
}

function getUserFriends($uid){  
    global $mysqli;
    $friends = '';
    $arr[] = $uid;
    $today = date('w'); 
    $query2 = $mysqli->query("SELECT DISTINCT s_id,id FROM chat WHERE r_id = '".$uid."' and seen <= 1  ORDER BY id DESC");
    if ($query2->num_rows > 0) { 
        while($result2 = $query2->fetch_object()){
            if (!in_array($result2->s_id, $arr)){
                $arr[] = $result2->s_id;                        
                
                $friendId = $result2->s_id;
                $fake = getData('users','fake','where id ='.$friendId);
                $online_day = getData('users','online_day','where id ='.$friendId);
                $last_access = getData('users','last_access','where id ='.$friendId);               

                $new = getNewMessages($uid,$friendId);

                $friends.='
                <div class="brick sb-friends" id="user'.$friendId.'" onclick="rightChatLink('.$friendId.','.getNewMessages($uid,$friendId).')" data-chat="'.$friendId.'" style="cursor:pointer;"  >
                    <a href="javascript:;"  data-uid="'.$friendId.'"  data-message="'.getNewMessages($uid,$friendId).'">
                        <div class="brick-img profile-photo"  style="cursor:pointer;border-radius:50%" data-src="'.profilePhoto($friendId).'"></div>';
                        if($last_access+300 >= time() || $fake == 1 && $online_day == $today){
                            $friends.='<div class="onlineFriendRight"></div>';
                        }
                        if($new > 0){
                            $friends.='<div class="mark mark--red" id="mark'.$friendId.'" style="right:-5px;top:-2px;">'.$new.'</div>';
                        }
                $friends.='                 
                    </a>
                </div>
                ';              
            }
        }   
    }
    $query2 = $mysqli->query("SELECT DISTINCT r_id,id FROM chat WHERE s_id = '".$uid."' and notification <= 1 ORDER BY id DESC");
    if ($query2->num_rows > 0) { 
        while($result2 = $query2->fetch_object()){
            if (!in_array($result2->r_id, $arr)){
                $arr[] = $result2->r_id;

                $friendId = $result2->r_id;
                $fake = getData('users','fake','where id ='.$friendId);
                $online_day = getData('users','online_day','where id ='.$friendId);
                $last_access = getData('users','last_access','where id ='.$friendId);               

                $new = getNewMessages($uid,$friendId);

                $friends.='
                <div class="brick sb-friends" id="user'.$friendId.'" onclick="rightChatLink('.$friendId.','.getNewMessages($uid,$friendId).')" data-chat="'.$friendId.'" style="cursor:pointer;"  >
                    <a href="javascript:;"  data-uid="'.$friendId.'"  data-message="'.getNewMessages($uid,$friendId).'">
                        <div class="brick-img profile-photo"  style="cursor:pointer;border-radius:50%" data-src="'.profilePhoto($friendId).'"></div>';
                        if($last_access+300 >= time() || $fake == 1 && $online_day == $today){
                            $friends.='<div class="onlineFriendRight"></div>';
                        }
                        if($new > 0){
                            $friends.='<div class="mark mark--red" id="mark'.$friendId.'" style="right:-5px;top:-2px;">'.$new.'</div>';
                        }
                $friends.='                 
                    </a>
                </div>
                ';  
            }
        }   
    }
    return $friends;
}

function getChat($uid1,$uid2){  
    global $mysqli,$sm;
    $chat = '';
    $text = "";
    $next = 0;
    $last = 0;
    
    $datePrev = '';
    $dateShow = ''; 

    $spotlight = $mysqli->query("SELECT * FROM chat WHERE s_id = '".$uid1."' and r_id = '".$uid2."'
                                OR r_id = '".$uid1."' and s_id = '".$uid2."' ORDER BY id ASC");
    if ($spotlight->num_rows > 0) { 
        while($spotl = $spotlight->fetch_object()){
            $m = $spotl->message;
            $m = clearMessageBR($m);
            $message = $spotl->message;
            $continue = true;

            $content = $m;

            $bgcolorstyle = '';
            if($spotl->credits == 0){
                $content = clearMessageBR($content);
                $message = clearMessageBR($message);    
            } else {
                $bgcolorstyle = 'style="background:rgb(26 245 0 / 28%);"';
            }
            
            if (strpos($message, '/emoji/') !== false) {
                $message = htmlspecialchars_decode($message,ENT_NOQUOTES);
                $message = str_replace( '&lt;', '<', $message );
                $message = str_replace( '&gt;', '>', $message );
                $message = str_replace( '&amp;', '', $message );
                $message = str_replace( '&quot;', '"', $message );              
            }           
            
            $bgcolor='counterpart-base-message counterpart-text-message';
            if($uid1 == $spotl->s_id) {
                $bgcolor='own-text-message';
            }
            $content = '
            <div class="base-message '.$bgcolor.'" '.$bgcolorstyle.'>
                '.$message.'
                <span class="message-indicators text-message-indicators">
                    <span class="message-indicators__content">'.date("h:i A",$spotl->time).'
                        <svg class="icon icon-check-4 read-icon icon--default-size"><use xlink:href="#icons-check-4"></use>
                        </svg>
                    </span>
                </span>
            </div>
            ';

            $lightboxStart = '';
            $lightboxEnd = '';
            if($spotl->photo == 1){ //PHOTO

                $content_body = '';
                $img = $message;
                if (strpos($message, '[msg]') !== false) {
                    $msg = explode('[msg]', $message);    
                    $img = $msg[0];
                    $content_body = '
                        <div class="bGc__body">'.$msg[1].'</div>
                    ';              
                }

                $postMedia = '
                    <a data-fslightbox="gallery'.$spotl->id.'" href="'.$img.'" class="bEc__item" style="background-image: url('.$img.'); grid-area: span 2 / span 2 / auto / auto;">
                    </a>
                ';
               
                $privateButton = '';
                if($spotl->private > 1 && $spotl->access == 0){
                    $imgBlur = getData('users_photos','blur','WHERE photo = "'.$img.'"');
                    $privateButton = '
                        <button class="bIc" type="button">
                            <svg class="icon icon-lock bIc__icon icon--default-size">
                            <use xlink:href="#icons-lock"></use>
                            </svg>
                            <div class="bIc__label">
                                <span>
                                    Unlock for <strong>'.$sm['plugins']['settings']['currencySymbol'].' '.$spotl->private.'</strong>
                                </span>
                            </div>
                        </button>
                    ';  
                    $postMedia = '
                        <div class="bEc__item" style="background-image: url('.$imgBlur.');grid-area: span 2 / span 2 / auto / auto;">
                        </div>
                    ';                                  
                }

                $content = '
                <div class="base-message base-message-clickable counterpart-base-message bGc" style="    max-width: 260px;">
                    <div class="bGc__gallery" style="min-width:220px">
                        <div class="bFc">
                            <div class="bEc">
                                '.$postMedia.'
                            </div>
                        </div>
                        '.$privateButton.'
                    </div>
                    <div class="bGc__footer">
                        <div class="bGc__counters"><span>1 photo</span></div>
                        '.$content_body.'
                        <span class="message-indicators"><span class="message-indicators__content">'.date("h:i A",$spotl->time).'<svg class="icon icon-read read-icon icon--default-size"><use xlink:href="#icons-read"></use></svg></span></span>
                    </div>
                </div>';
            }    

            if($spotl->photo == 2){ //VIDEO

                $content_body = '';
                $img = $message;
                if (strpos($message, '[msg]') !== false) {
                    $msg = explode('[msg]', $message);    
                    $img = $msg[0];
                    $content_body = '
                        <div class="bGc__body">'.$msg[1].'</div>
                    ';              
                }

                $imgThumb = getData('users_photos','thumb','WHERE photo = "'.$img.'"');

                $postMedia = '
                    <a data-fslightbox="gallery'.$spotl->id.'" href="'.$img.'" class="bEc__item" style="background-image: url('.$imgThumb.'); grid-area: span 2 / span 2 / auto / auto;">
                    </a>
                ';
               
                $privateButton = '';
                if($spotl->private > 1 && $spotl->access == 0){
                    $imgBlur = getData('users_photos','blur','WHERE photo = "'.$img.'"');
                    $privateButton = '
                        <button class="bIc" type="button">
                            <svg class="icon icon-lock bIc__icon icon--default-size">
                            <use xlink:href="#icons-lock"></use>
                            </svg>
                            <div class="bIc__label">
                                <span>
                                    Unlock for <strong>'.$sm['plugins']['settings']['currencySymbol'].' '.$spotl->private.'</strong>
                                </span>
                            </div>
                        </button>
                    ';  
                    $postMedia = '
                        <div class="bEc__item" style="background-image: url('.$imgBlur.');grid-area: span 2 / span 2 / auto / auto;">
                        </div>
                    ';                                  
                }

                $content = '
                <div class="base-message base-message-clickable counterpart-base-message bGc" style="    max-width: 260px;">
                    <div class="bGc__gallery" style="min-width:220px">
                        <div class="bFc">
                            <div class="bEc">
                                '.$postMedia.'
                            </div>
                        </div>
                        '.$privateButton.'
                        <div class="bGc__play-icon"><svg class="icon icon-play" style="min-width: 16px; height: 16px; width: 16px;"><use xlink:href="#icons-play"></use></svg></div>
                    </div>
                    <div class="bGc__footer">
                        <div class="bGc__counters"><span>1 video</span></div>
                        '.$content_body.'
                        <span class="message-indicators"><span class="message-indicators__content">'.date("h:i A",$spotl->time).'<svg class="icon icon-read read-icon icon--default-size"><use xlink:href="#icons-read"></use></svg></span></span>
                    </div>
                </div>';
            }     

            if($spotl->photo == 3){ //GALLERY
                $content_body = '';
                if (strpos($message, '[msg]') !== false) {
                    $msg = explode('[msg]', $message);    
                    $content_body = '
                        <div class="bGc__body">'.$msg[1].'</div>
                    ';
                    $gallery = explode('[image]', $msg[0]);         
                } else {
                    $gallery = explode('[image]', $message);
                }
                
                $totalGallery = count($gallery) - 3;

                $showMorePhotosCount = '';
                if($totalGallery > 0){
                    $showMorePhotosCount = '
                    <div class="g__shadow"></div>
                    <span class="position-relative white-color-text-opacity-6 text-s32-w600">
                        +'.$totalGallery.'
                    </span>
                    ';
                }

                $img1 = $gallery[0];
                $img2 = $gallery[1];
                $img3 = $gallery[2];

                $postMedia = '
                    <a data-fslightbox="gallery'.$spotl->id.'" href="'.$img1.'" class="bEc__item" style="background-image: url('.$img1.'); grid-row: span 2 / auto;">
                    </a>
                    <a data-fslightbox="gallery'.$spotl->id.'" href="'.$img2.'" class="bEc__item" style="background-image: url('.$img2.');">
                    </a>
                    <a data-fslightbox="gallery'.$spotl->id.'" href="'.$img3.'" class="bEc__item" style="background-image: url('.$img3.');">
                        <span class="white-color-text text-s32-w600">
                        +'.$totalGallery.'
                        </span>
                    </a>
                ';

                $e = 0;
                foreach ($gallery as $img) {
                    if($e > 2){
                        $postMedia.='
                            <a data-fslightbox="gallery'.$spotl->id.'" href="'.$img.'" style="display:none" ><img src="'.$img.'"></a>
                        ';
                    }
                    $e++;
                }                

                $privateButton = '';
                if($spotl->private > 1 && $spotl->access == 0){
                    $img1 = getData('users_photos','blur','WHERE photo = "'.$gallery[0].'"');
                    $img2 = getData('users_photos','blur','WHERE photo = "'.$gallery[1].'"');
                    $img3 = getData('users_photos','blur','WHERE photo = "'.$gallery[2].'"');
                    $privateButton = '
                        <button class="bIc" type="button">
                            <svg class="icon icon-lock bIc__icon icon--default-size">
                            <use xlink:href="#icons-lock"></use>
                            </svg>
                            <div class="bIc__label">
                                <span>
                                    Unlock for <strong>'.$sm['plugins']['settings']['currencySymbol'].' '.$spotl->private.'</strong>
                                </span>
                            </div>
                        </button>
                    ';  
                    $postMedia = '
                        <div class="bEc__item" style="background-image: url('.$img1.'); grid-row: span 2 / auto;">
                        </div>
                        <div class="bEc__item" style="background-image: url('.$img2.');">
                        </div>
                        <div class="bEc__item" style="background-image: url('.$img3.');">
                            <span class="white-color-text text-s32-w600">
                            +'.$totalGallery.'
                            </span>
                        </div>
                    ';                                  
                }

                $content = '
                <div class="base-message base-message-clickable counterpart-base-message bGc">
                    <div class="bGc__gallery">
                        <div class="bFc">
                            <div class="bEc">
                                '.$postMedia.'
                            </div>
                        </div>
                        '.$privateButton.'
                    </div>
                    <div class="bGc__footer">
                        <div class="bGc__counters"><span>'.count($gallery).' photos</span></div>
                        '.$content_body.'
                        <span class="message-indicators"><span class="message-indicators__content">'.date("h:i A",$spotl->time).'<svg class="icon icon-read read-icon icon--default-size"><use xlink:href="#icons-read"></use></svg></span></span>
                    </div>
                </div>';
               
            }                 

            if($spotl->gift >= 1){
                $content = '<div class="message__pic_" style="cursor:pointer;border:none">
                    <img  src="'.$m.'" />
                </div>';
            }                

            $dateShow = date("F j, Y",$spotl->time);
            if($dateShow != $datePrev){
                $dateShow = date("d.m.Y",$spotl->time);
                $chat.='<div class="announcement-message date-message">'.$dateShow.'</div>';
            }

            if($continue == true){
                if($uid1 == $spotl->s_id) {
                    if($spotl->translated == 1){
                        $content = $spotl->translated_text;
                    }
                    $chat.='<div class="base-message-wrapper base-message-wrapper--position--right msg-me">
                            <div class="base-message-wrapper__content">
                                <div class="base-message-wrapper__header"></div>
                                <div class="base-message-wrapper__body-wrapper">
                                '.$content.'
                                </div>
                            </div>
                        </div>';                    
                    $text = "";
                } else {
                    $mysqli->query("UPDATE chat set seen = 1 where s_id = '".$uid2."' and r_id = '".$uid1."'"); 
                    $chat.='<div class="base-message-wrapper base-message-wrapper--position--left msg-you">
                            <div class="base-message-wrapper__content">
                                <div class="base-message-wrapper__header"></div>
                                <div class="base-message-wrapper__body-wrapper">
                                '.$content.'
                                </div>
                            </div>
                        </div>';                    
                    $text = "";
                }
            }
            $datePrev = date("F j, Y",$spotl->time);
        }   
    }
    return $chat;
}

function getUserConversations($uid){    
    global $mysqli,$sm;
    $friends = '';
    $arr[] = $uid;
    $today = date('w'); 
    $query = $mysqli->query("SELECT id,s_id,r_id,seen,notification,gif,gift,time FROM chat WHERE r_id = '".$uid."' || s_id = '".$uid."' order by id desc");
    $i= 0;
    if ($query->num_rows > 0) { 
        while($result = $query->fetch_object()){
            if (!in_array($result->s_id, $arr)){
                $arr[] = $result->s_id;                     
                $friendId = $result->s_id;
                $fake = getData('users','fake','where id ='.$friendId);
                $name = getData('users','username','where id ='.$friendId);
                $photo = profilePhoto($friendId);
                $online_day = getData('users','online_day','where id ='.$friendId);
                $last_access = getData('users','last_access','where id ='.$friendId);               
                $new = getNewMessages($uid,$friendId);
                $i++;
                $delay = $i * 50;
                $last_m = clearMessageBR(getLastMessageMobileApp($uid,$friendId));
                $last_m_time = getLastMessageMobileSentTime($uid,$friendId);

                if (strpos($last_m, '/gifts/') !== false) {
                    $gift = 1;
                }
                if (strpos($last_m, 'giphy') !== false) {
                    $gift = 1;
                }
                if (strpos($last_m, '.jpg') !== false) {
                    $gift = 1;
                }
                if (strpos($last_m, '.png') !== false) {
                    $gift = 1;
                }
            

                $online = '';
                if($fake == 1 && $online_day == $today){
                    $online = 'online';
                }
                $unread = checkUnreadMessagesCount($uid,$friendId);
                $totalm = '<span data-chat-unread-count="'.$friendId.'" class="bDc__new" style="display:none">'.$unread.'</span>';
                $unreadclass = '';
                if($unread > 0){
                    $unreadclass = 'unread';
                    $totalm = '<span data-chat-unread-count="'.$friendId.'" class="bDc__new">'.$unread.'</span>';
                }
                $friends.='
                    <div class="bDc bDc--menu--bottom bDc--mode--page" data-creator-chat="'.$friendId.'" data-unread="'.$unread.'" data-creator-chat-photo="'.$photo.'" data-creator-chat-username="'.$name.'" onclick="loadConversation('.$friendId.','.$unread.')">
                        <div class="bDc__avatar-wrapper">
                            <span class="avatar" style="--avatar-border-size: 0px;">
                                <span class="avatar__img" 
                                style="background-image: url('.$photo.')"></span>
                                <span class="user-status-icon" title="Online"></span>
                            </span>
                        </div>
                        <div class="bDc__content">
                            <div class="bDc__name">
                                <div class="displayname">
                                    <div class="displayname__name displayname__name--text-overflow">
                                        '.$name.'
                                    </div>
                                </div>
                                <time datetime="'.$last_m_time.'" class="bDc__time">
                                    '.date("M d",$last_m_time).'
                                </time>
                            </div>
                            <div class="bDc__text">
                                <span class="personal-notifications-message-item-body" data-chat-lastm="'.$friendId.'">'.$last_m.'</span>
                            </div>
                        </div>
                        '.$totalm.'
                    </div>
                ';              
            }
            if (!in_array($result->r_id, $arr)){
                $arr[] = $result->r_id;                     
                $friendId = $result->r_id;
                $fake = getData('users','fake','where id ='.$friendId);
                $name = getData('users','username','where id ='.$friendId);
                $photo = profilePhoto($friendId);
                $online_day = getData('users','online_day','where id ='.$friendId);
                $last_access = getData('users','last_access','where id ='.$friendId);               
                $new = getNewMessages($uid,$friendId);
                $i++;
                $delay = $i * 50;
                $last_m = clearMessageBR(getLastMessageMobileApp($uid,$friendId));
                $last_m_time = getLastMessageMobileSentTime($uid,$friendId);

                if (strpos($last_m, '/gifts/') !== false) {
                    $gift = 1;
                }
                if (strpos($last_m, 'giphy') !== false) {
                    $gift = 1;
                }
                if (strpos($last_m, '.jpg') !== false) {
                    $gift = 1;
                }
                if (strpos($last_m, '.png') !== false) {
                    $gift = 1;
                }
                $online = '';
                if($last_access+300 >= time() || $fake == 1 && $online_day == $today){
                    $online = 'online';
                }
                $unread = checkUnreadMessagesCount($uid,$friendId);
                $totalm = '<span data-chat-unread-count="'.$friendId.'" class="bDc__new" style="display:none">'.$unread.'</span>';
                $unreadclass = '';
                if($unread > 0){
                    $unreadclass = 'unread';
                    $totalm = '<span data-chat-unread-count="'.$friendId.'" class="bDc__new">'.$unread.'</span>';
                }
                $friends.='
                    <div class="bDc bDc--menu--bottom bDc--mode--page" data-creator-chat="'.$friendId.'" data-unread="'.$unread.'" onclick="loadConversation('.$friendId.','.$unread.')">
                        <div class="bDc__avatar-wrapper">
                            <span class="avatar" style="--avatar-border-size: 0px;">
                                <span class="avatar__img" 
                                style="background-image: url('.$photo.')"></span>
                                <span class="user-status-icon" title="Online"></span>
                            </span>
                        </div>
                        <div class="bDc__content">
                            <div class="bDc__name">
                                <div class="displayname">
                                    <div class="displayname__name displayname__name--text-overflow">
                                        '.$name.'
                                    </div>
                                </div>
                                <div></div>
                                <time datetime="'.$last_m_time.'" class="bDc__time">
                                    '.date("M d",$last_m_time).'
                                </time>
                            </div>
                            <div class="bDc__text">
                                <span class="personal-notifications-message-item-body" data-chat-lastm="'.$friendId.'">'.$last_m.'</span>
                            </div>
                        </div>
                        '.$totalm.'
                    </div>
                ';                  
            }           
        }   
    }
    return $friends;
}

function getLastMessageMobileSentTime($uid1,$uid2){ 
    global $mysqli,$sm;
    $message = 0;
    $spotlight = $mysqli->query("SELECT time FROM chat WHERE r_id = '".$uid1."' and s_id = '".$uid2."' OR s_id = '".$uid1."' and r_id = '".$uid2."' ORDER BY id DESC LIMIT 1");
    if ($spotlight->num_rows > 0) { 
        while($spotl = $spotlight->fetch_object()){
            $message = $spotl->time;            
        }   
    }
    return $message;
}

function checkUnreadMessagesCount($rid,$sid){
    global $mysqli;
    $query = $mysqli->query("SELECT count(id) as total FROM chat WHERE r_id = '".$rid."' AND s_id = '".$sid."' AND seen = 0 order by id desc ");    
    $total = $query->fetch_assoc();
    return $total['total']; 
}