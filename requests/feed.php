<?php
header('Content-Type: application/json');
require_once('../assets/includes/core.php');

if(isset($sm['user']['id'])){
    $uid = $sm['user']['id'];
} else {
    $uid = 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($_GET['action']) {
        case 'randomPostGenerator':
            $feedPosts = getSelectedArray('id','users','where fake = 1 and age BETWEEN 18 and 48','rand()','LIMIT 0,10'); 
            $i=0;
            foreach ($feedPosts as $f) { 
                global $mysqli;

                $isVideo = rand(0,10);
                if($isVideo > 5){
                    $video = 1;
                } else {
                    $video = 0;
                }

                $isPrivate = rand(0,10);
                if($isPrivate > 5){
                    $private = 1;
                } else {
                    $private = 0;
                }                

                $photo = $mysqli->query("SELECT photo,blur FROM users_photos where u_id = ".$f['id']." AND video = ".$video." ORDER BY rand() LIMIT 1");
                if ($photo->num_rows > 0) {
                    $p = $photo->fetch_object();
                    $likes = rand(1860,1424200);
                    $time = time()-$likes;

                    $type = 'image';
                    if($video == 1){
                        $type = 'video';
                    }
                    $premium = 'No';
                    if($private == 1){
                        $premium = 'Yes';
                    }                    
                    $cols = 'uid,post_type,post_price,post_src,post_meta,post_premium,post_blur,likes,post_time,visible';
                    $randomCaption = getData('feed_random_captions','caption','order by rand() limit 1');
                    $vals = $f['id'].',"'.$type.'",0,"'.$p->photo.'","'.$randomCaption.'","'.$premium.'",
                    "'.$p->blur.'",'.$likes.','.$time.',1';
                    insertData('feed',$cols,$vals);  
                    $i++;
                }
            }
            $arr = array();
            $arr['OK'] = 'Yes';
            echo json_encode($arr);
        break;

        case 'randomDiscoverImage':
            $feedPosts = getSelectedArray('id','users','where fake = 1','rand()','LIMIT 0,1000'); 
            $i=0;
            foreach ($feedPosts as $f) { 
                global $mysqli;
                $photo = $mysqli->query("SELECT id FROM users_photos where u_id = ".$f['id']." AND video = 0 ORDER BY rand() LIMIT 1");
                if ($photo->num_rows > 0) {
                    $p = $photo->fetch_object();
                    updateData('users_photos','discover',1,'WHERE id ='.$p->id);
                }
            }
            $arr = array();
            $arr['OK'] = 'Yes';
            echo json_encode($arr);
        break;

        case 'randomProfileImage':
            $feedPosts = getSelectedArray('id','users','where fake = 1','rand()','LIMIT 0,1000'); 
            $i=0;
            foreach ($feedPosts as $f) { 
                global $mysqli;
                $photo = $mysqli->query("SELECT id FROM users_photos where u_id = ".$f['id']." AND video = 0 ORDER BY rand() LIMIT 1");
                if ($photo->num_rows > 0) {
                    $p = $photo->fetch_object();
                    updateData('users_photos','profile',1,'WHERE id ='.$p->id);
                }
            }
            $arr = array();
            $arr['OK'] = 'Yes';
            echo json_encode($arr);
        break;


        case 'loadFeed':
            $arr = array();
            $arr['html']= '';
            $feed = array();
            $order = 'post_time DESC';
            $uid = secureEncode($_GET['uid']);
            $looking = getData('users','s_gender','where id ="'.$uid.'"');
            $filter = 'WHERE visible = 1 AND uid <> '.$sm['user']['id'];            
            $customFilter = secureEncode($_GET['customFilter']);

            $limit = $_GET['limit']*24;

            if(strpos($customFilter, 'post') !== false){
                $post = explode('-',$customFilter);
                $customFilter = '';
                $filter = 'WHERE visible = 1 AND id = '.$post[1];
            }

            if(strpos($customFilter, 'user') !== false){
                $post = explode('-',$customFilter);
                $customFilter = '';
                $filter = 'WHERE visible = 1 AND uid = '.$post[1];
            }

            if($customFilter == '' || $customFilter == 'all'){
                $feed = getArray('feed',$filter,$order,'LIMIT '.$limit.',25');
            } else {
                if($customFilter == 'favs'){
                    $query = $mysqli->query("
                        SELECT *
                        FROM feed f

                        INNER JOIN users_likes favs 
                            ON f.uid = favs.u2 
                            AND favs.u1 = ".$sm['user']['id']."

                        UNION

                        SELECT  *
                        FROM  feed f
                            INNER JOIN  users_subscriptions subs 
                            ON f.uid = subs.u2 
                            AND subs.u1 = ".$sm['user']['id']."  

                        ORDER BY post_time DESC
                        LIMIT ".$limit.",25
                    ");
                    if(isset($query->num_rows) && !empty($query->num_rows)){
                        while($row = $query->fetch_assoc()){
                            $feed[] = $row;
                        }       
                    }  
                }   

                if($customFilter == 'liked'){
                    $data = getArray('feed_likes','WHERE uid = '.$sm['user']['id'],'time DESC','LIMIT 0,300');
                    if(!empty($data)){
                        foreach ($data as $d) {
                            $feed[] = getDataArray('feed','id = '.$d['fid']);
                        }
                    }    
                }   

                if($customFilter == 'saved'){
                    $data = getArray('users_feed_favs','WHERE uid = '.$sm['user']['id'],'time DESC','LIMIT 0,300');
                    if(!empty($data)){
                        foreach ($data as $d) {
                            $feed[] = getDataArray('feed','id = '.$d['fid']);
                        }
                    }
                } 
                if($customFilter == 'purchased'){
                    $data = getArray('users_feed_purchases','WHERE uid = '.$sm['user']['id'],'time DESC','LIMIT 0,300');
                    if(!empty($data)){
                        foreach ($data as $d) {
                            $feed[] = getDataArray('feed','id = '.$d['fid']);
                        }
                    } else {
                        $arr['html'] = 'empty';
                    }                    
                }

                if($customFilter == 'me'){
                    $filter = 'WHERE visible = 1 AND uid = '.$uid; 
                    $feed = getArray('feed',$filter,$order,'LIMIT 0,25');
                }

            }

            $x = 0;
            if(!empty($feed)){
                foreach ($feed as $f) {
                    $username = getData('users','username','where id ="'.$f['uid'].'"');
                    $city = getData('users','city','where id ="'.$f['uid'].'"');
                    $country = getData('users','country','where id ="'.$f['uid'].'"');
                    $age = getData('users','age','where id ="'.$f['uid'].'"'); 
                    $profile_photo = profilePhoto($f['uid']);
                    $checkLiked = getData('feed_likes','fid','where fid ='.$f['id'].' AND uid = '.$sm['user']['id']);

                    $checkPurchased = getData('users_feed_purchases','uid','where fid ='.$f['id'].' AND uid = '.$sm['user']['id']);                 

                    $checkSub = isSub($sm['user']['id'],$f['uid']); 

                    $purchased = 'No';
                    $subscriber = 'No';

                    $liked = 'style="display:none;color:#ff3130"';
                    $noliked = 'style="display:block"';
                    $likeAction = 1;
                    if($checkLiked != 'noData'){
                        $liked = 'style="display:block;color:#ff3130"';
                        $noliked = 'style="display:none"';
                        $likeAction = 0;
                    }

                    if($checkPurchased != 'noData'){
                        $purchased = 'Yes';
                    } 

                    if($f['uid'] == $sm['user']['id']){
                        $purchased = 'Yes';
                        $subscriber = 'Yes';
                    }

                    if($checkSub > 0){
                        $subscriber = 'Yes';
                    }

                    $cp = getData('users_subscriptions_prices','amount','WHERE days = '.$sm['config']['subscriptions'][0]['days'].' AND uid = '.$f['uid']);
                    if($cp != 'noData'){
                        $sm['config']['subscriptions'][0]['amount'] = $cp;
                    } else {
                        $sm['config']['subscriptions'] = subscriptionsPrice();
                    }                    

                    $saved = 'display:block';
                    $removesaved = 'display:none';
                    $favs = getData('users_feed_favs','fid','WHERE uid = '.$sm['user']['id'].' AND fid = '.$f['id']);
                    if($favs != 'noData'){
                        $favs = 'favorited';
                        $saved = 'display:none';
                        $removesaved = 'display:block';
                    }

                    $premium = '';
                    $premiumPost = '';
                    $premiumPostLabel = '';
                    $postType = $f['post_type'];

                    if($f['post_type'] == 'image'){
                        $postMedia = '                               
                        <div class="S S--clickable">
                            <a data-fslightbox="feedImage'.$f['id'].'" href="'.$f['post_src'].'">
                            <div class="image-swiper">
                                    <img
                                    alt="Exclusive feed post by '.$username.'"
                                    decoding="async"
                                    class="image-swiper__item-image image-swiper__item-image--size--cover"
                                    src="'.$f['post_src'].'"
                                />
                            </div>
                            </a>
                        </div>
                        ';    
                        $premiumPostLabel = '
                        <span class="feed__post-label">
                            <span class="P">
                                <svg class="icon icon-pin" style="height: 16px; width: 16px;"><use xlink:href="#icons-lens"></use></svg>
                                    Image
                            </span>
                        </span>
                        ';                    
                    }

                    if($f['post_type'] == 'video'){
                        $postMedia = '                               
                        <div class="S S--clickable">
                            <div class="image-swiper" style="height:660px">
                                <div class="y__blur" style="background-image: url('.$f['post_blur'].');"></div>
                                <div class="video-player video-player--desktop">
                                    <video
                                        id="fvid'.$f['id'].'"
                                        playsinline=""
                                        class="video-player__video video-player__video--visible"
                                        poster="'.$f['post_poster'].'"
                                        loop
                                    ><source src="'.$f['post_src'].'" type="video/mp4"></video>

                                    <button class="video-player__play" type="button" id="fvid'.$f['id'].'_btn" onclick="playFeedVid('.$f['id'].')">
                                        <svg class="icon icon-play video-player__main-control-icon video-player__main-control-icon--play"><use xlink:href="#icons-play"></use></svg>
                                    </button>                                    
                                </div>
                            </div>
                        </div>
                        ';
                        $premiumPostLabel = '
                        <span class="feed__post-label">
                            <span class="P">
                                <svg class="icon icon-pin" style="height: 16px; width: 16px;"><use xlink:href="#icons-play"></use></svg>
                                    Video
                            </span>
                        </span>
                        ';                        
                    }


                    if($f['post_premium'] == 'Yes' && $subscriber == 'No'){
                        $postType = 'private';
                        $postMedia = '
                        <div class="y y--locked">
                            <div class="image-swiper" style="height: 361.12px;">
                                <div class="image-swiper__scroll" style="height: 411.12px;">
                                    <div class="image-swiper__item" style="width: 642px; height: 361.12px; left: 500px;">
                                        <img alt="" decoding="async" class="image-swiper__item-image image-swiper__item-image--size--cover" src="'.$profile_photo.'" />
                                    </div>
                                    <span class="image-swiper__scroll-space" style="width: 1642px;"></span>
                                </div>
                            </div>
                            <div class="t t--fill--locked" style="background-image: url('.$f['post_blur'].');">
                                <svg class="icon icon-discount t__icon t__icon--size--big">
                                <use xlink:href="#icons-diamond"></use>
                                </svg>
                                <div class="t__title">Subscribe to '.$username.' for unlock!</div>
                                <button class="content-lock-button btn btn-apply btn-large" onclick="openModal('."'premium'".','."'".$f['uid'].','.$username.','.$profile_photo."'".')" type="button"><strong>'.$username.' subscription from '.$sm['plugins']['settings']['currencySymbol'].$sm['config']['subscriptions'][0]['amount'].'</strong></button>
                            </div>
                        </div>
                        '.$premiumPostLabel.'                         
                        ';
                    }

                    if($f['post_price'] > 0 && $purchased == 'No'){
                        $postType = 'private';
                        $postMedia = '
                        <div class="y y--locked">
                            <div class="image-swiper" style="height: 361.12px;">
                                <div class="image-swiper__scroll" style="height: 411.12px;">
                                    <div class="image-swiper__item" style="width: 642px; height: 361.12px; left: 500px;">
                                        <img alt="" decoding="async" class="image-swiper__item-image image-swiper__item-image--size--cover" src="'.$profile_photo.'" />
                                    </div>
                                    <span class="image-swiper__scroll-space" style="width: 1642px;"></span>
                                </div>
                            </div>
                            <div class="t t--fill--locked" style="background-image: url('.$f['post_blur'].');">
                                <svg class="icon icon-discount t__icon t__icon--size--big" style="color: rgb(255 255 255 / 76%);">
                                <use xlink:href="#icons-lock-1"></use>
                                </svg>
                                <div class="t__title" onclick="openModal('."'purchase_post'".','."'".$f['uid'].','.$username.','.$profile_photo."'".','."'".$f['id'].','.$f['post_price']."'".')" style="cursor:pointer" >Pay '.$sm['plugins']['settings']['currencySymbol'].$f['post_price'].' for unlock!</div>
                            </div>
                        </div>
                        '.$premiumPostLabel.'                         
                        ';
                    }                    

                    $privatePostIcon = '';

                    $storyFrom = $sm['plugins']['story']['days'];
                    $time = time(); 
                    $extra = 86400 * $storyFrom;
                    $storyFrom = $time - $extra;            
                    $storiesFilter = 'where uid = '.$f['uid'].' and storyTime >'.$storyFrom.' and deleted = 0';
                    $openStory = selectC('users_story',$storiesFilter);
                    $profilePhotoBorder = 'border: 2px solid #fff';
                    if($openStory > 0){
                        $profilePhotoBorder = 'border: 2px solid #e22d48';
                    }
                    $multipleSRC = 'No';

                    if(strpos($f['post_src'], ',') !== false){
                        $gallery = explode(',', $f['post_src']);
                        $multipleSRC = 'Yes';
                        $postType = 'gallery';
                        $totalGallery = count($gallery) - 3;

                        $blur1 = getData('users_photos','blur','where photo = "'.$gallery[1].'"');
                        $blur2 = getData('users_photos','blur','where photo = "'.$gallery[2].'"');
                        $showMorePhotosCount = '';
                        if($totalGallery > 0){
                            $showMorePhotosCount = '
                            <div class="g__shadow"></div>
                            <span class="position-relative white-color-text-opacity-6 text-s32-w600">
                                +'.$totalGallery.'
                            </span>
                            ';
                        }

                        $htmltag1 = 'img';
                        if(isVideo($gallery[0])){
                            $htmltag1 = 'video';
                        }
                        $htmltag2 = 'img';
                        if(isVideo($gallery[1])){
                            $htmltag2 = 'video';
                        }
                        $htmltag3 = 'img';
                        if(isVideo($gallery[2])){
                            $htmltag3 = 'video';
                        }                          

                        if($f['post_premium'] == 'Yes' && $subscriber == 'No'){
                        $postMedia = '
                            <div class="g">
                                <div class="g__item g__item--main">
                                    <'.$htmltag1.' class="g__img" src="'.$gallery[0].'">
                                    <div class="g__shadow"></div>
                                    <div class="g__join">
                                    <svg class="icon icon-diamond" style="min-width: 48px; height: 48px; width: 48px;"><use xlink:href="#icons-diamond"></use></svg>
                                    <span style="max-width: 140px;">Unlock all '.count($gallery).' media</span>
                                    <button class="btn btn-apply btn-medium" type="button" onclick="openModal('."'premium'".','."'".$f['uid'].','.$username.','.$profile_photo."'".')">
                                        Subscribe to '.$username.' from '.$sm['plugins']['settings']['currencySymbol'].$sm['config']['subscriptions'][0]['amount'].'
                                    </button>
                                    <button type="button" class="g__view-button">
                                        <svg class="icon icon-spy-on" style="min-width: 12px; height: 12px; width: 12px;">
                                            <use xlink:href="#icons-spy-on"></use>
                                        </svg>
                                        View gallery
                                    </button>
                                    </div>
                                </div>
                                <div class="g__item">
                                    <'.$htmltag2.' class="g__img" src="'.$blur1.'" alt="">
                                    <div class="g__shadow"></div>
                                    <div class="g__lock">
                                        <svg class="icon icon-lock-1" style="min-width: 32px; height: 32px; width: 32px;"><use xlink:href="#icons-lock-1"></use>
                                        </svg>
                                        <span style="text-align:center">Subscribe to Unlock</span>
                                    </div>
                                </div>
                                <div class="g__item">
                                    <'.$htmltag3.' class="g__img" src="'.$blur2.'" alt="">
                                    '.$showMorePhotosCount.'
                                </div>
                            </div>
                            ';
                        } else if($f['post_price'] > 0  && $purchased == 'No'){
                        $postMedia = '
                            <div class="g">
                                <div class="g__item g__item--main">
                                    <'.$htmltag1.' class="g__img" src="'.$gallery[0].'">
                                    <div class="g__shadow"></div>
                                    <div class="g__join">
                                    <svg class="icon icon-diamond" style="min-width: 48px; height: 48px; width: 48px;"><use xlink:href="#icons-diamond"></use></svg>
                                    <span style="max-width: 140px;">Unlock all '.count($gallery).' media</span>
                                    <button class="btn btn-apply btn-medium" type="button" onclick="openModal('."'premium'".','."'".$f['uid'].','.$username.','.$profile_photo."'".')">
                                        Pay '.$sm['plugins']['settings']['currencySymbol'].$f['post_price'].' for unlock!
                                    </button>
                                    <button type="button" class="g__view-button">
                                        <svg class="icon icon-spy-on" style="min-width: 12px; height: 12px; width: 12px;">
                                            <use xlink:href="#icons-spy-on"></use>
                                        </svg>
                                        View gallery
                                    </button>
                                    </div>
                                </div>
                                <div class="g__item">
                                    <'.$htmltag2.' class="g__img" src="'.$blur1.'" alt="">
                                    <div class="g__shadow"></div>
                                    <div class="g__lock">
                                        <svg class="icon icon-lock-1" style="min-width: 32px; height: 32px; width: 32px;"><use xlink:href="#icons-lock-1"></use>
                                        </svg>
                                        <span style="text-align:center">Subscribe to Unlock</span>
                                    </div>
                                </div>
                                <div class="g__item">
                                    <'.$htmltag3.' class="g__img" src="'.$blur2.'" alt="">
                                    '.$showMorePhotosCount.'
                                </div>
                            </div>
                            ';
                        } else {                                              

                        $postMedia = '
                            <div class="g">
                                    <div class="g__item g__item--main">
                                        <a data-fslightbox="gallery'.$f['id'].'" href="'.$gallery[0].'">
                                            <'.$htmltag1.' class="g__img" src="'.$gallery[0].'">
                                        </a>
                                    </div>
                                    <div class="g__item">
                                        <a data-fslightbox="gallery'.$f['id'].'" href="'.$gallery[1].'">
                                            <'.$htmltag2.' class="g__img" src="'.$gallery[1].'" alt="">
                                        </a>
                                    </div>
                                    <div class="g__item">
                                        <a data-fslightbox="gallery'.$f['id'].'" href="'.$gallery[2].'">
                                            <'.$htmltag3.' class="g__img" src="'.$gallery[2].'" alt="">
                                            '.$showMorePhotosCount.'
                                        </a>
                                    </div>
                                ';
                                $e = 0;
                                foreach ($gallery as $img) {
                                    if($e > 2){
                                        $postMedia.='
                                            <a data-fslightbox="gallery'.$f['id'].'" href="'.$img.'" style="display:none" ><img src="'.$img.'"></a>
                                        ';
                                    }
                                    $e++;
                                }
                        $postMedia.= '</div>';
                        }

                    }

                    $hideMeChat = '';
                    if($f['uid'] == $uid){
                        $hideMeChat = 'display:none;';
                    }

                    $hideComments = '';
                    if($premium != ''){
                        $hideComments = 'style="display:none"';
                    }

                    $arr['feed_user'][$f['uid']] = array(
                      "id" => $f['uid'],
                      "name" => $username,                     
                      "age" => $age,
                      "city" => $city,                    
                      "photo" => $profile_photo,
                    );

                    $postContent = '
                    <div class="tile text-post-tile">
                        <div class="feed-my-club__header">
                            <div class="menu-button-dots css-popover">
                                <div>
                                    <button type="button" class="menu-button-dots__menu-button menu-button-dots__menu-button--size--normal menu-button-dots__menu-button--theme--light" onclick="toggleDropdown('.$f['id'].')">
                                        <div class="menu-button-dots__icon-wrapper">
                                            <svg class="icon icon-three-dots menu-icon icon--default-size"><use xlink:href="#icons-three-dots"></use></svg>
                                        </div>
                                    </button>
                                </div>
                                <div class="css-popover-content-bottomright" data-dropdown="'.$f['id'].'">
                                    <ul class="c c--style--default">
                                        ';
                                        if($f['uid'] == $sm['user']['id']){
                                           $postContent.= '
                                        <li>
                                            <div class="C__menu-item C__menu-item--split" onclick="deleteFeed('.$f['id'].');toggleDropdown('.$f['id'].')">
                                                <svg class="icon icon-wrong-2 C__menu-item-icon icon--default-size"><use xlink:href="#icons-wrong-2"></use></svg>Delete
                                            </div>
                                        </li>';                                           
                                        } else {
                                            $postContent.= '
                                        <li>
                                            <div class="C__menu-item C__menu-item--split" onclick="openModal('."'report_post'".');toggleDropdown('.$f['id'].')">
                                                <svg class="icon icon-wrong-2 C__menu-item-icon icon--default-size"><use xlink:href="#icons-wrong-2"></use></svg>Report
                                            </div>
                                        </li>';
                                        }
                                        $postContent.= '
                                        <li onclick="copyPostLink('.$f['id'].')">
                                            <div class="C__menu-item">
                                                <svg class="icon icon-chain-link C__menu-item-icon icon--default-size"><use xlink:href="#icons-chain-link"></use></svg>
                                                    Copy Post Link
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="tile-header">
                            <div>
                                <a class="avatar-wrapper" href="javascript:;" onmousedown="goToProfile(event,'.$f['uid'].')">
                                    <span class="avatar model-avatar" style="--avatar-size: 48px; --avatar-border-size: 0px;">
                                        <span class="avatar__img" style="background-image: url('.$profile_photo.');"><span class="user-status-icon online" title="Online"></span></span>
                                    </span>
                                </a>
                            </div>
                            <div class="content-wrapper">
                                <div class="content">
                                    <div class="displayname displayname--size--small">
                                        <div class="displayname__name">
                                            <a class="white-color-text opacity-hover" href="'.$sm['config']['site_url'].$username.'" onmousedown="goToProfile(event,'.$f['uid'].')">
                                            '.$username.'</a>
                                        </div>                                               
                                    </div>
                                    <a class="posted-at" onmousedown="goToProfile(event,'.$f['uid'].')" href="'.$sm['config']['site_url'].$username.'">'.date('M d, Y',$f['post_time']).'</a>                                             
                                </div>
                            </div>
                        </div>
                        <div class="text-post">
                            '.$postMedia.'
                            <p class="text">
                                '.$f['post_meta'].'
                            </p>                        
                        </div>
                        <div class="tile-footer">
                            <div class="content">
                                <div class="left-wrapper">
                                    <button class="likes-counter likes-counter--clickable likes-counter--size--medium a11y-button" type="button" onclick="likeFeed('.$f['id'].','.$likeAction.')" id="like-feed-btn-'.$f['id'].'">
                                        <svg id="unlike-'.$f['id'].'" class="icon icon-heart like-icon like-icon--size--medium" '.$noliked.' >
                                            <use xlink:href="#icons-heart"></use>
                                        </svg>
                                        <svg id="like-'.$f['id'].'" class="icon icon-heart like-icon like-icon--size--medium" '.$liked.'>
                                            <use xlink:href="#icons-heart-fill" ></use>
                                        </svg>                                                                                  
                                        '.thousandsCurrencyFormat($f['likes']).'
                                    </button>

                                    <button type="button" class="likes-counter--size--medium flex-align-center a11y-button" 
                                        onclick="openModal('."'messages'".','."'".$f['uid'].','.$username.','.$profile_photo."'".')">
                                        <svg class="icon icon-chat indent-right-small-6" style="height: 20px; width: 20px;"><use xlink:href="#icons-chat"></use></svg>
                                        Chat
                                    </button>

                                    <div class="tile-button tile-button--size--regular tile-button--color--regular" onclick="showSendTip('."'".$username."'".','."'".$profile_photo."'".')">
                                        <svg class="icon icon-gift tile-button__icon tile-button__icon--size--regular"><use xlink:href="#icons-gift"></use></svg><span class="tile-button__text">Send Tip</span>
                                    </div>

                                </div>
                                <div class="right-wrapper">
                                    <div class="tile-footer__bookmark" onclick="shareModal('."'post'".','.$f['id'].','."'".$profile_photo."'".')">
                                        <svg class="icon icon-forward_link icon--default-size">
                                        <use xlink:href="#icons-forward_link"></use></svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    ';

                    $arr['feed'][$x]['post'] = $postContent;
                    $arr['feed'][$x]['type'] = $postType;
                    $arr['feed'][$x]['media'] = $f['post_src'];
                    $arr['feed'][$x]['id'] = $f['id'];
                    $arr['html'].=' 
                        <div class="feed-post" id="postId'.$f['id'].'">
                            '.$postContent.'
                        </div> 
                    ';

                    $x++;
                }   
            } else {
                $arr['html'] = 'empty';
            }    
            echo json_encode($arr);
        break;

        case 'loadFeedComments':
            $fid = secureEncode($_GET['fid']);
            $arr = array();
            $cFilter = 'WHERE fid = '.$fid;
            $comments = getArray('feed_comments',$cFilter,'time DESC','LIMIT 0,65'); 
            foreach ($comments as $c) {
                $c['photo'] = profilePhoto($c['uid']);
                $c['username'] = getData('users','username','WHERE id = '.$c['uid']);
                $c['liked'] = getData('feed_comments_likes','cid','WHERE cid = '.$c['id'].' AND uid = '.$uid);
                $arr[] = $c;
            }           
            echo json_encode($arr);
        break;

        case 'removeFeed':
            $fid = secureEncode($_GET['fid']);
            $mediaId = getData('feed','media_id','where id= '.$fid);
            $uid = secureEncode($_GET['uid']);
            $arr = array();
            $time = time();
            deleteData('feed','WHERE uid = '.$uid.' AND id = '.$fid);
            deleteData('feed_comments','WHERE fid = '.$fid);
            deleteData('feed_likes','WHERE fid = '.$fid);            
            deleteData('users_feed_favs','WHERE fid = '.$fid);
            deleteData('users_feed_purchases','WHERE fid = '.$fid);
            deleteData('users_feed_purchases','WHERE fid = '.$fid);
            deleteData('users_photos','WHERE id = '.$mediaId);
            $arr['OK'] = 'OK';
            echo json_encode($arr);
        break;        

        case 'like_comment':
            $query = secureEncode($_GET['query']);
            $data = explode(',',$query);
            $time = time();
            $uid = $data[0];
            $cid = $data[1];
            $action = $data[2];
            $time = time();
            if($action == 1){
                $query = "INSERT INTO feed_comments_likes (cid,uid,time) VALUES ('".$cid."', '".$uid."', '".$time."')";
            } else {
                $query = "DELETE FROM feed_comments_likes WHERE cid = '".$cid."' AND uid = '".$uid."'";
            }

            $mysqli->query($query);             
        break;

        case 'post_fav':
            $arr = array();
            $time = time();
            $query = secureEncode($_GET['query']);
            $data = explode(',',$query);
            $uid = $data[0];    
            $post = $data[1];
            $action = $data[2];

            if($action == 'add'){
                $cols = 'uid,fid,time';
                $vals = $uid.','.$post.',"'.$time.'"';
                insertData('users_feed_favs',$cols,$vals);      
            } else {
                deleteData('users_feed_favs','WHERE uid = '.$uid.' AND fid = '.$post);
            }
            $arr['OK'] = 'OK';
            echo json_encode($arr);             
        break;

        case 'post_purchase':
            $arr = array();
            $time = time();
            $query = secureEncode($_GET['query']);
            $data = explode(',',$query);
            $uid = $data[0];    
            $post = $data[1];
            $action = $data[2];

            if($action == 'purchase'){
                $cols = 'uid,fid,time';
                $vals = $uid.','.$post.',"'.$time.'"';
                insertData('users_feed_purchases',$cols,$vals);     
            } else {
                deleteData('users_feed_purchases','WHERE uid = '.$uid.' AND fid = '.$post);
            }
            $arr['OK'] = 'OK';
            echo json_encode($arr);             
        break;   



    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($_POST['action']) {       
        case 'uploadFeedMedia':
            $time = time();
            $arr = array();

            $gallerySrc = '';
            if(isset($_POST['create_post'])){

                $blur = '';
                $media = '';
                $video = '';
                $thumb = '';
                $uid = secureEncode($_POST['uid']);
                $type = secureEncode($_POST['type']);
                $message = secureEncode($_POST['message']);
                $price = secureEncode($_POST['price']);
                
                $post_type = 'image';
                $private = 0;
                if($type == 'subs'){
                    $private = 1;
                }

                if(!empty($_POST['media'])){
                    $media = $_POST['media'];
                    if(count($media) > 1){
                        $post_type = 'gallery';
                        foreach ($media as $m) {
                            $media = secureEncode($m['path']);              
                            $video = secureEncode($m['video']);  
                            $blur = secureEncode($m['blur']);
                            $thumb = secureEncode($m['thumb']);
                            $gallerySrc.=$media.',';   

                            if($video == 1){
                                $blur = getData('users_photos','blur','where u_id ='.$uid.' ORDER BY rand()');
                                $thumb = getData('users_photos','thumb','where blur = "'.$blur.'"');
                            }

                            $cols_user_photos = 'u_id,photo,thumb,private,video,time,blur,model,posted';
                            $vals_user_photos = $uid.',"'.$media.'","'.$thumb.'",'.$private.','.$video.',"'.$time.'","'.$blur.'",1,1';
                            insertData('users_photos',$cols_user_photos,$vals_user_photos);
                        }  
                        $gallerySrc = rtrim($gallerySrc, ",");
                        $media = $gallerySrc;                                               
                    } else {
                        $media = secureEncode($_POST['media'][0]['path']);              
                        $video = secureEncode($_POST['media'][0]['video']);  
                        $blur = secureEncode($_POST['media'][0]['blur']);
                        $thumb = secureEncode($_POST['media'][0]['thumb']);
                        if($video == 1){
                            $post_type = 'video';

                            $blur = getData('users_photos','blur','where u_id ='.$uid.' ORDER BY rand()');
                            $thumb = getData('users_photos','thumb','where blur = "'.$blur.'"');

                        } else {
                            $post_type = 'image';
                        }

                        $cols_user_photos = 'u_id,photo,thumb,private,video,time,blur,model,posted';
                        $vals_user_photos = $uid.',"'.$media.'","'.$thumb.'",'.$private.','.$video.',"'.$time.'","'.$blur.'",1,1';
                        insertData('users_photos',$cols_user_photos,$vals_user_photos);

                    }
                } else {
                    $post_type = 'text';
                }

                $private = 'No';
                if($type == 'subs'){
                    $private = 'Yes';
                }

                $comments = 'No';

                $mediaId = getData('users_photos','id','where u_id = '.$uid.' ORDER BY ID DESC LIMIT 1');

                $cols = 'uid,post_type,post_price,post_src,post_meta,post_premium,post_time,visible,post_blur,post_disable_comments,media_id';
                $vals = $uid.',"'.$post_type.'",'.$price.',"'.$media.'","'.$message.'","'.$private.'","'.$time.'",1,"'.$blur.'","'.$comments.'",'.$mediaId;
                insertData('feed',$cols,$vals); 

            }

            echo json_encode($arr);          
        break;

        case 'feedLike':

            $arr = array();
            $user = secureEncode($_POST['user']);
            $feed = secureEncode($_POST['fid']);
            $motive = secureEncode($_POST['motive']);
            $count = secureEncode($_POST['count']);     ;              
            if($motive == 'like'){
                $cols = 'fid,uid,time';
                $vals = $feed.','.$user.','.time();
                insertData('feed_likes',$cols,$vals);
            } else {
                $delete = 'WHERE fid = '.$feed.' AND uid = '.$user;
                deleteData('feed_likes',$delete);
            }
            $count = getData('feed','likes','where id ='.$feed);
            
            if($motive == 'remove'){
                $count = $count - 1;
            } else {
                $count = $count + 1;
            }
            updateData('feed','likes',$count,'WHERE id ='.$feed);

            $arr['OK'] = 'Yes';
            echo json_encode($arr);     
        break;

        case 'feedComment':

            $arr = array();
            $user = secureEncode($_POST['user']);
            $feed = secureEncode($_POST['fid']);
            $motive = secureEncode($_POST['motive']);
            $comment = secureEncode($_POST['comment']);

            if($motive == 'comment'){
                $cols = 'fid,uid,comment,time';
                $vals = $feed.','.$user.',"'.$comment.'",'.time();
                insertData('feed_comments',$cols,$vals);
            }

            $arr['OK'] = 'Yes';
            echo json_encode($arr);      
        break;      

        default:

        break;
    }
}