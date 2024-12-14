//FEED
var createPost = {};
var feedUrl = request_source()+'/feed.php';
createPost['media'] = [];
createPost['type'] = '';
createPost['price'] = 0;
if(mobileSite === true){
    var user_info = user;
}

function createFeedPost(){
    $('#previewMediaFeed').hide();

    createPost['create_post'] = 'Yes';
    createPost['message'] = $('#postMessage').html();

    console.log(createPost);
    if(createPost['message'] == ''){
        sendToast('Post caption cant be empty');   
         $('#postMessage').focus();
        return false;
    }

    if(createPost['type'] == ''){
        sendToast('Select a post type');   
         $('#postMessage').focus();
        return false;
    }

    if(createPost['media'].length == 0){
        sendToast('Post media cant be empty','Please upload at least one photo or video');   
        return false;
    } 

    if(createPost['media'].length == 2){
        sendToast('Please add 1 media file more','The minimun for make a gallery post are 3 media files');   
        return false;
    } 

    if(createPost['type'] == 'ppv' && createPost['price'] == 0){
        sendToast('Pay per view post price missing','Please set the post price');   
         $('#ppvPostPrice').focus();
        return false;
    }        

    createPost['action'] = 'uploadFeedMedia';
    createPost['uid'] = user_info.id;

    $.ajax({
      type: "POST",
      url: request_source()+'/feed.php',
      data: createPost,
      dataType: 'JSON',
      success: function(response) { 
        setTimeout(function(){
          closeModal('publish');
          loadUserFeed('me');
          sendToast('Post successfully published');
        },200);
      }
    });    
}

function updatePPV(val){
  createPost['price'] = val;
}

function selectPostType(id,type){
  $('[data-post-type]').removeClass('o--state--selected');
  $('[data-post-type]').find('.checkbox-ds__box').removeClass('checkbox-ds__box--checked');
  $('[data-post-type]').addClass('o--state--not-selected');
  $('[data-post-type="'+id+'"]').removeClass('o--state--not-selected');
  $('[data-post-type="'+id+'"]').addClass('o--state--selected');
  $('[data-post-type="'+id+'"]').find('.checkbox-ds__box').addClass('checkbox-ds__box--checked');
  createPost['type'] = type;
  if(type == 'ppv'){
    $('#payPerViewInput').show();
    $('#ppvPostPrice').focus();
  } else {
    $('#payPerViewInput').hide();
  }
}

function likeFeed(fid,liked){

  if(user_info.guest == 1){
    if(url == 'profile'){
      modal = 'register';
      $('[data-modal="'+modal+'"]').show();
      currentModal = modal;
    } else {
      goTo('landing');
    }
  } else {
    if(liked == 0){ //REMOVE LIKE
        $('#unlike-'+fid).show();
        $('#like-'+fid).hide();
        $('#like-feed-btn-'+fid).attr('onclick','likeFeed('+fid+',1)');
        $.ajax({
            url: request_source()+'/feed.php',
            data: {
                action: 'feedLike',
                fid: fid,
                user: user_info.id,
                count: 0,
                motive: 'remove'
            },
            type: "post",
            dataType: 'JSON',
            success: function(response) {
            },
        });
    } else { //ADD LIKE

        $('#unlike-'+fid).hide();
        $('#like-'+fid).show();
        $('#like-feed-btn-'+fid).attr('onclick','likeFeed('+fid+',0)');
        $.ajax({
            url: request_source()+'/feed.php',
            data: {
                action: 'feedLike',
                fid: fid,
                user: user_info.id,
                count: 0,
                motive: 'like'
            },
            type: "post",
            dataType: 'JSON',
            success: function(response) {
            },
        });
    }
  }
}

function publishComment(fid){
    var comment = $('#commentInput'+fid).val();
    if(comment == ''){
        return false;
    }

    var count = parseInt($('[data-feed-comments='+fid+']').text()) + 1;
    $('[data-feed-comments='+fid+']').text(count);

    $('#postComments'+fid).append(`
        <div class="flex">
            <div class="w-10 h-10 rounded-full relative flex-shrink-0 user-profile-photo" style="background-image: url('`+user_info.profile_photo+`')">
            </div>
            <div class="text-gray-700 py-2 px-3 rounded-lg bg-gray-100 h-full relative lg:ml-5 lg:mr-20  dark:bg-gray-800 dark:text-gray-100 ">
                <p class="leading-6 blurred-comment dark:bg-gray-800 dark:text-gray-100">
                    `+comment+`
                </p>
            </div>
        </div>
    `);

    $('#commentInput'+fid).val('');
    $('#postComments'+fid).scrollTop(500000);

    $.ajax({
        url: request_source()+'/feed.php',
        data: {
            action: 'feedComment',
            fid: fid,
            user: user_info.id,
            count: count,
            motive: 'comment',
            comment: comment
        },
        type: "post",
        dataType: 'JSON',
        success: function(response) {
        },
    });

}


var feedPlayers = new Array();

var loadFeedPage = 0;
var feedCustomFilter = '';
var profileFeedMax = false;
var feedMax = false;
function loadUserFeed(filter='',limit=0){

  feedCustomFilter = filter;

  if(filter == 'favs'){
    feedMax = false;
  }

  if (filter.indexOf("user") !=-1) {
    profileFeedMax = false;
  }


  $('#feed-content').show();
  $.getJSON( feedUrl, { action: 'loadFeed',customFilter: filter,uid: user_info.id,limit: limit},function(data){
    if(data.html != ''){
        setTimeout(function(){

          loadingPage = false;
          
          if(data.html == 'empty'){
            $('#feed-content').html('');
            $('#noFeed').show();
            $('#noFeedYet').show();
            feedMax = true;
            profileFeedMax = true;
          } else {
            data.feed.forEach(function(feed,index) {

              if(loadFeedPage > 0){
                index = 24 * loadFeedPage + index;
              }
              if (filter.indexOf("user") !=-1) {
                if(data.feed.length <= 24){
                  profileFeedMax = true;
                }
              }

              if (filter.indexOf("favs") !=-1) {
                if(data.feed.length <= 24){
                  feedMax = true;
                }
              }

              $('[data-loading-feed="'+index+'"]').attr('data-feed-loaded','1');
              $('[data-loading-feed="'+index+'"]').attr('data-feed-id',feed.id);
              
              if(feed.type == 'image'){
                preloadImagesRed([feed.media], function(){
                  $('[data-loading-feed="'+index+'"]').html(feed.post);
                  $('[data-loading-feed="'+index+'"]').html(feed.post);
                  refreshFsLightbox();       
                });   
              } else {
                $('[data-loading-feed="'+index+'"]').html(feed.post);

                if(feed.type == 'video'){
                  feedPlayers[feed.id] = fluidPlayer(
                    'fvid'+feed.id, {
                    "layoutControls": {
                    "controlBar": {
                      "autoHideTimeout": 3,
                      "animated": true,
                      "autoHide": true
                    },
                    "logo": {
                        "imageUrl":'',
                        "position":'top left',
                        "clickUrl":null,
                        "opacity": 0.5
                    },                  
                    "htmlOnPauseBlock": {
                      "html": null,
                      "height": null,
                      "width": null
                    },
                    "autoPlay": false,
                    "loop": false,
                    "mute": true,
                    "allowTheatre": false,
                    "playPauseAnimation": false,
                    "playbackRateEnabled": false,
                    "allowDownload": false,
                    "playButtonShowing": false,
                    "fillToContainer": false,
                    "posterImage": ""
                    },
                    "vastOptions": {
                    "adList": [],
                    "adCTAText": false,
                    "adCTATextPosition": ""
                    }
                  });                
                 
                  setTimeout(function(){
                    feedPlayers[feed.id].on('play', function() {
                      $('#fvid'+feed.id+'_btn').hide();
                      feedPlayers.forEach(function(player,index) {
                        if(index != feed.id){
                          feedPlayers[index].pause();
                        }
                      });
                    });

                    feedPlayers[feed.id].on('pause', function() {
                      $('#fvid'+feed.id+'_btn').show();
                    }); 
                  },100)

                }
                refreshFsLightbox();
              } 

            });

            setTimeout(function(){
              $('[data-feed-loaded=0]').fadeOut();
            },250)  

          }
        },300);      
      }
  });
}

function loadMoreFeed(){
  var min = 0;
  var max = 24;
  min = loadFeedPage * 25;
  max = loadFeedPage * 25 + 24;

  for (let i = min; i <= max; i++) {
    $('#feed-content').append(`
    <div class="feed-post" data-loading-feed="`+i+`" data-feed-loaded="0">
        <div class="tile text-post-tile">
            <div class="feed-my-club__header">
            </div>
            <div class="tile-header">
                <div>
                    <a class="avatar-wrapper" href="javascript:;">
                        <span class="avatar model-avatar" style="--avatar-size: 48px; --avatar-border-size: 0px;">
                            <span class="avatar__img loading-story"></span>
                        </span>
                    </a>
                </div>
                <div class="content-wrapper">

                </div>
            </div>
            <div class="text-post">
                <div class="data-hj-suppress S S--clickable">
                    <div class="image-swiper">
                        <div class="image-swiper__scroll" style="height: 325px;">
                            <div class="image-swiper__item loading-feed" style="width: 100%;height: 325px;">
                            </div>

                        </div>
                    </div>
                </div>
                <div style="position:relative;margin-top:15px;width: 350px;
                height: 30px;">
                    <div class="loading-feed"></div>
                </div>                                                           
            </div>
            <div class="tile-footer">
                <div class="content" style="height:80px;margin-left: 2px;border:none">
                    <div class="left-wrapper">
                        <button class="likes-counter likes-counter--size--medium loading-story" style="width: 32px;">
                        </button>
                        <button class="likes-counter likes-counter--size--medium loading-story" style="width: 24px;height:24px;margin-left: 10px;">
                        </button>
                        <button class="likes-counter likes-counter--size--medium loading-story" style="width: 32px;margin-left: 10px;">
                        </button>                                                     

                    </div>
                    <div class="right-wrapper">
                        <button class="likes-counter likes-counter--size--medium loading-story" style="width: 32px;margin-left: 10px;">
                        </button>      
                    </div>
                </div>
            </div>
        </div>
    </div>`);
  }
  loadingMoreContent = false;
  loadUserFeed(feedCustomFilter,loadFeedPage);
}


function likeComment(cid,action){
  if(action == 1){
    $('[data-comment-id="comment'+action+cid+'"]').hide();
    $('[data-comment-id="comment0'+cid+'"]').show();
  } else {
    $('[data-comment-id="comment'+action+cid+'"]').hide();
    $('[data-comment-id="comment1'+cid+'"]').show();
  }
  var query = user_info.id+','+cid+','+action;
  $.get( feedUrl, {action: 'like_comment', query: query} ,function( data ) {});
}


function closeFeedComments(){
  $(".insta-comments").hide();
  $(".insta-comments-image").hide();
  $(".insta-comments-overlay").hide();
  $("body").removeClass("fixed");
  if(mobileSite){
    $('#feed-content').show();
    $('ion-content').css('overflow-y','auto');
  }   
}

function showFeedComments(fid,caption,photo,media,type){

  $(".insta-comments").show();
  $(".insta-comments-image").show();
  $(".insta-comments-overlay").show();
  $("body").addClass("fixed");

  $('#feed-comments').html('');
  $('#feed-comment').text(caption);
  $('#feed-comment-photo').attr('src',photo);

  if(type == 'video'){
    $('#feed-comment-media').html('<video autoplay muted loop width="100%" height="100%" style="object-fit:cover"><source src="' + media + '"></video>');
  } else {
    $('#feed-comment-media').html('');
    $('#feed-comment-media').css('background-image',`url(`+media+`)`);
  }

  $('#comment-write').focus();
  $('#feedID').val(fid);
  $('.insta-comments').scrollTop(0);
  if(mobileSite){
    $('ion-content').scrollTop(0);
    $('#feed-content').hide();
    $('ion-content').css('overflow-y','hidden');     
  }
  $.getJSON( feedUrl, { action: 'loadFeedComments', fid: fid },function(data){
    if(data.length > 0){
        data.forEach(function(comment) {
          console.log(comment);
          var hideLikeComment = 'style="display:none"';
          var showLikeComment = 'style="display:none"';
          if(comment.liked == 'noData'){
            hideLikeComment = 'style="display:none"';
            showLikeComment = '';
          } else {
            hideLikeComment = '';
            showLikeComment = 'style="display:none"';
          }
          var likeCommentBtn = `
          <button type="button" data-comment-id="comment1`+comment.id+`"
            onclick="likeComment(`+comment.id+`,1)" class="likeIcon" `+showLikeComment+`>
            <svg class="unlike" color="#262626" fill="#262626" height="24" role="img" viewBox="0 0 24 24" width="24">
              <path d="M16.792 3.904A4.989 4.989 0 0121.5
                  9.122c0
                  3.072-2.652 4.959-5.197 7.222-2.512 2.243-3.865
                  3.469-4.303
                  3.752-.477-.309-2.143-1.823-4.303-3.752C5.141
                  14.072 2.5 12.167 2.5 9.122a4.989 4.989 0
                  014.708-5.218 4.21 4.21 0 013.675 1.941c.84
                  1.175.98 1.763 1.12 1.763s.278-.588
                  1.11-1.766a4.17 4.17 0 013.679-1.938m0-2a6.04
                  6.04 0 00-4.797 2.127 6.052 6.052 0
                  00-4.787-2.127A6.985 6.985 0 00.5 9.122c0 3.61
                  2.55 5.827 5.015
                  7.97.283.246.569.494.853.747l1.027.918a44.998
                  44.998 0 003.518 3.018 2 2 0 002.174 0 45.263
                  45.263 0
                  003.626-3.115l.922-.824c.293-.26.59-.519.885-.774
                  2.334-2.025 4.98-4.32 4.98-7.94a6.985 6.985 0
                  00-6.708-7.218z"></path>
            </svg>
          </button>
          <button type="button"  data-comment-id="comment0`+comment.id+`"
            onclick="likeComment(`+comment.id+`,0)" class="likeIcon" `+hideLikeComment+`>
            <svg class="liked" color="#ed4956" fill="#ed4956" height="24" role="img" viewBox="0 0 48 48" width="24">
              <path d="M34.6 3.1c-4.5 0-7.9 1.8-10.6
                  5.6-2.7-3.7-6.1-5.5-10.6-5.5C6 3.1 0 9.6 0
                  17.6c0 7.3 5.4 12 10.6 16.5.6.5 1.3 1.1 1.9
                  1.7l2.3 2c4.4 3.9 6.6 5.9 7.6 6.5.5.3 1.1.5
                  1.6.5s1.1-.2 1.6-.5c1-.6 2.8-2.2
                  7.8-6.8l2-1.8c.7-.6 1.3-1.2 2-1.7C42.7 29.6 48
                  25 48 17.6c0-8-6-14.5-13.4-14.5z"></path>
            </svg>
          </button>`;
          // if(user_info.id == comment.uid){
          //   likeCommentBtn = '';
          // }

          $('#feed-comments').append(`
            <li>
              <div class="comment-like-wrap">
                <div class="comment-wrap">
                  <a href="javascript:;" onclick="goToProfile(`+comment.uid+`);" class="comment-img">
                    <img src="`+comment.photo+`">
                  </a>
                  <div class="comment-content">
                    <p>
                      <a href="javascript:;" onclick="goToProfile(`+comment.uid+`);" class="commenter-name">`+comment.username+`</a>
                      `+comment.comment+`
                    </p>
                  </div>                 
                </div>
                `+likeCommentBtn+`                
              </div>
            </li>
          `);
        });
      }
  });

}

$('#post-comment').submit(function(e) {
  e.preventDefault();

  if(user_info.credits < plugins['fgfeed']['creditsForComment']){
      openWidget("purchaseCredits");
      return false;
  }

  if(plugins['fgfeed']['creditsForComment'] > 0){
    var data = [];
    data.name = '';
    data.icon = user_info.profile_photo;
    data.message = site_lang[610]['text']+' '+plugins['fgfeed']['creditsForComment']+' ' + site_lang[73]['text'];

    updateCredits(user_info.id,plugins['fgfeed']['creditsForComment'],1,'Credits for like post');
    if(mobileSite){
        pushNotifMobile(data,1);
    } else {
        pushNotif(data,1);  
    }
  }

  var comment = $('#comment-write').val();
  if(comment == ''){
      return false;
  }
  $('#comment-write').val('');
  $('#comment-btn').addClass('disable-btn');
  $('#feed-comments').prepend(`
    <li>
      <div class="comment-like-wrap first-commit">
        <div class="comment-wrap">
          <a href="javascript:;" onclick="goToProfile(`+user_info.id+`);" class="comment-img">
            <img src="`+user_info.profile_photo+`">
          </a>
          <div class="comment-content">
            <p>
              <a href="javascript:;" onclick="goToProfile(`+user_info.id+`);" class="commenter-name">`+user_info.username+`</a>
              `+comment+`
            </p>
          </div>
        </div>
      </div>
    </li>
  `);
  $('.insta-comments').scrollTop(0);
  var fid = $('#feedID').val();
  $.ajax({
      url: request_source()+'/feed.php',
      data: {
          action: 'feedComment',
          fid: fid,
          user: user_info.id,
          motive: 'comment',
          comment: comment
      },
      type: "post",
      dataType: 'JSON',
      success: function(response) {

      },
      complete: function (response) {
        addLikeBlockComment(user_info.id);
      },
  });

});

function addLikeBlockComment (fid) {
  $.getJSON( feedUrl, { action: 'firstIDComment', fid: fid },function(data){
    if(data.length > 0){
      data.forEach(function(comment) {
        var hideLikeComment = 'style="display:none"';
        var showLikeComment = 'style="display:none"';
        if(comment.liked == 'noData'){
          hideLikeComment = 'style="display:none"';
          showLikeComment = '';
        } else {
          hideLikeComment = '';
          showLikeComment = 'style="display:none"';
        }
        var likeCommentBtn = `
          <button type="button" data-comment-id="comment1`+comment.id+`"
            onclick="likeComment(`+comment.id+`,1)" class="likeIcon" `+showLikeComment+`>
            <svg class="unlike" color="#262626" fill="#262626" height="24" role="img" viewBox="0 0 24 24" width="24">
              <path d="M16.792 3.904A4.989 4.989 0 0121.5
                  9.122c0
                  3.072-2.652 4.959-5.197 7.222-2.512 2.243-3.865
                  3.469-4.303
                  3.752-.477-.309-2.143-1.823-4.303-3.752C5.141
                  14.072 2.5 12.167 2.5 9.122a4.989 4.989 0
                  014.708-5.218 4.21 4.21 0 013.675 1.941c.84
                  1.175.98 1.763 1.12 1.763s.278-.588
                  1.11-1.766a4.17 4.17 0 013.679-1.938m0-2a6.04
                  6.04 0 00-4.797 2.127 6.052 6.052 0
                  00-4.787-2.127A6.985 6.985 0 00.5 9.122c0 3.61
                  2.55 5.827 5.015
                  7.97.283.246.569.494.853.747l1.027.918a44.998
                  44.998 0 003.518 3.018 2 2 0 002.174 0 45.263
                  45.263 0
                  003.626-3.115l.922-.824c.293-.26.59-.519.885-.774
                  2.334-2.025 4.98-4.32 4.98-7.94a6.985 6.985 0
                  00-6.708-7.218z"></path>
            </svg>
          </button>
          <button type="button"  data-comment-id="comment0`+comment.id+`"
            onclick="likeComment(`+comment.id+`,0)" class="likeIcon" `+hideLikeComment+`>
            <svg class="liked" color="#ed4956" fill="#ed4956" height="24" role="img" viewBox="0 0 48 48" width="24">
              <path d="M34.6 3.1c-4.5 0-7.9 1.8-10.6
                  5.6-2.7-3.7-6.1-5.5-10.6-5.5C6 3.1 0 9.6 0
                  17.6c0 7.3 5.4 12 10.6 16.5.6.5 1.3 1.1 1.9
                  1.7l2.3 2c4.4 3.9 6.6 5.9 7.6 6.5.5.3 1.1.5
                  1.6.5s1.1-.2 1.6-.5c1-.6 2.8-2.2
                  7.8-6.8l2-1.8c.7-.6 1.3-1.2 2-1.7C42.7 29.6 48
                  25 48 17.6c0-8-6-14.5-13.4-14.5z"></path>
            </svg>
          </button>`;

        $('.first-commit').append(likeCommentBtn).removeClass('first-commit');
      })
    }
  })
};

function postFav(post,method){
    var query = user_info.id+','+post+','+method;

    if(method == 'add'){
      $('.our-collection-'+post).addClass('save-collection');
      $('.our-collection-'+post).addClass('moveUp');
      $('.save-'+post).hide();
      $('.remove-'+post).show();
      setTimeout(function () {
        $(".our-collection-"+post).removeClass("moveUp");
      }, 3000);
    } else {
      $('.save-'+post).show();
      $('.remove-'+post).hide();
    }

    $.get( feedUrl, {action: 'post_fav', query: query} ,function( data ) {});
}

function purchasePremiumPost(post){

  if(user_info.credits < plugins['fgfeed']['creditsForPremium']){
      openWidget("purchaseCredits");
      return false;
  }

  var data = [];
  data.name = '';
  data.icon = '';
  data.message = site_lang[610]['text']+' '+plugins['fgfeed']['creditsForPremium']+' ' + site_lang[73]['text'];

  updateCredits(user_info.id,plugins['fgfeed']['creditsForPremium'],1,'Credits for purchase premium post');
  if(mobileSite){
      pushNotifMobile(data,1);
  } else {
      pushNotif(data,1);  
  }

  $('[data-feed-id='+post+']').removeClass('premium-post');
  $('[data-feed-id-premium='+post+']').remove();
  var query = user_info.id+','+post+',purchase';
  $.getJSON( feedUrl, {action: 'post_purchase', query: query} ,function( data ) {

  });
}

function updateFeedMeta(val){
  uploadFeed['meta'] = val;
}


function uploadUserFeed(){
  if(user_info.credits < plugins['fgfeed']['creditsForUpload']){
      openWidget("purchaseCredits");
      return false;
  }

  var data = [];
  data.name = '';
  data.icon = '';
  data.message = site_lang[610]['text']+' '+plugins['fgfeed']['creditsForUpload']+' ' + site_lang[73]['text'];

  updateCredits(user_info.id,plugins['fgfeed']['creditsForUpload'],1,'Credits for purchase premium post');
  if(mobileSite){
      pushNotifMobile(data,1);
  } else {
      pushNotif(data,1);  
  }

  $.ajax({
    url: request_source()+'/feed.php',
    data: uploadFeed,
    type: "post",
    dataType: 'JSON',
    success: function(response) {
      setTimeout(function(){
        window.location.href = site_url()+'feed';
      },1500)

    },
  });
}


function openUploadFGFeed(){
  $("#create-feed-post-overlay").show();
  $("body").addClass("fixed");  
  $('#create-feed-post').show();
  if(mobileSite){
    $('ion-content').css('overflow-y','hidden');
    $('#feed-content').hide();
  }
}
function closeUploadFGFeed(){
  $("#create-feed-post-overlay").hide();
  $("body").removeClass("fixed");  
  $('#create-feed-post').hide();
  if(mobileSite){
    $('ion-content').css('overflow-y','auto');
    $('#feed-content').show();
  }  
}

function uploadFeedMedia(){
  upType = 20;
  document.getElementById("uploadContent").click();
}


function feedSlider($slider,multiple){
  var count = 4;
  var currentSlide;
  var slidesCount;
  var sliderCounter = document.createElement("span");
  sliderCounter.classList.add("slider__counter");
  var maxDots = 6;
  var transformXIntervalNext = -10;
  var transformXIntervalPrev = 10;
  var multipleSlides = false;
  if(multiple == 'Yes'){
    multipleSlides = true;
  }

  $slider.on("init", function (event, slick) {
    $(this)
      .find("ul.slick-dots")
      .wrap("<div class='slick-dots-container'></div>");
    $(this)
      .find("ul.slick-dots li")
      .each(function (index) {
        $(this).addClass("dot-index-" + index);
      });
    $(this).find("ul.slick-dots").css("transform", "translateX(0)");
    setBoundries($(this), "default");
  });

  var transformCount = 0;
  $slider.on("beforeChange", function (event, slick, currentSlide, nextSlide) {
    var totalCount = $(this).find(".slick-dots li").length;
    if (totalCount > maxDots && multiple == 'Yes') {
      if (nextSlide > currentSlide) {
        if (
          $(this)
            .find("ul.slick-dots li.dot-index-" + nextSlide)
            .hasClass("n-small-1")
        ) {
          if (
            !$(this).find("ul.slick-dots li:last-child").hasClass("n-small-1")
          ) {
            transformCount = transformCount + transformXIntervalNext;
            $(this)
              .find("ul.slick-dots li.dot-index-" + nextSlide)
              .removeClass("n-small-1");
            var nextSlidePlusOne = nextSlide + 1;
            $(this)
              .find("ul.slick-dots li.dot-index-" + nextSlidePlusOne)
              .addClass("n-small-1");
            $(this)
              .find("ul.slick-dots")
              .css("transform", "translateX(" + transformCount + "px)");
            var pPointer = nextSlide - 3;
            var pPointerMinusOne = pPointer - 1;
            $(this)
              .find("ul.slick-dots li")
              .eq(pPointerMinusOne)
              .removeClass("p-small-1");
            $(this).find("ul.slick-dots li").eq(pPointer).addClass("p-small-1");
          }
        }
      } else {
        if (
          $(this)
            .find("ul.slick-dots li.dot-index-" + nextSlide)
            .hasClass("p-small-1")
        ) {
          if (
            !$(this).find("ul.slick-dots li:first-child").hasClass("p-small-1")
          ) {
            transformCount = transformCount + transformXIntervalPrev;
            $(this)
              .find("ul.slick-dots li.dot-index-" + nextSlide)
              .removeClass("p-small-1");
            var nextSlidePlusOne = nextSlide - 1;
            $(this)
              .find("ul.slick-dots li.dot-index-" + nextSlidePlusOne)
              .addClass("p-small-1");
            $(this)
              .find("ul.slick-dots")
              .css("transform", "translateX(" + transformCount + "px)");
            var nPointer = currentSlide + 3;
            var nPointerMinusOne = nPointer - 1;
            $(this)
              .find("ul.slick-dots li")
              .eq(nPointer)
              .removeClass("n-small-1");
            $(this)
              .find("ul.slick-dots li")
              .eq(nPointerMinusOne)
              .addClass("n-small-1");
          }
        }
      }
    }
  });

  var updateSliderCounter = function (slick, currentIndex) {
    currentSlide = slick.slickCurrentSlide() + 1;
    slidesCount = slick.slideCount;
    $(sliderCounter).text(currentSlide + "/" + slidesCount);
  };

  $slider.on("init", function (event, slick) {
    if(multiple == 'Yes'){
      $slider.append(sliderCounter);
      updateSliderCounter(slick);
    }
  });

  $slider.on("afterChange", function (event, slick, currentSlide) {
    updateSliderCounter(slick, currentSlide);
  });

  $slider.slick({
    dots: multipleSlides,
    infinite: false,
    arrows: false,
    speed: 800,
    slidesToShow: 1,
    slidesToScroll: 1,
  });
}

function setBoundries(slick, state) {
  if (state === "default") {
    slick.find("ul.slick-dots li").eq(4).addClass("n-small-1");
  }
}

function removeFeedMedia(id){
  $('#gray'+id).remove();
  for (var i = upphotos.length - 1; i >= 0; i--) {
      if (upphotos[i]['tmp_id'] == id) {
          upphotos.splice(i, 1);
      }
  }
  createPost['media'] = upphotos;
}



function createFeedPreview(file,fileContents,id) {
  var $previewElement = '';
  switch (file.type) {
    case 'image/png':
    case 'image/jpeg':
    case 'image/gif':
      $('.upload-file-list').append(`
        <div class="peso uploadingGray" id="gray`+id+`" style="background-image: url(`+fileContents+`);">
            <div class="p__remove-button" onclick="removeFeedMedia('`+id+`')">
                <svg class="icon icon-close-3" style="min-width: 16px; height: 16px; width: 16px;"><use xlink:href="#icons-close-3"></use>
                </svg>
            </div>
        </div>        
      `);
      break;
    case 'video/mp4':
    case 'video/webm':
    case 'video/ogg':
      $('.upload-file-list').append(`
        <div class="peso uploadingGray" id="gray`+id+`">
            <video autoplay muted loop width="100%" height="100%" style="object-fit:cover;border-radius:10px"><source src="`+ fileContents +`" type="` + file.type +`"></video>        
            <div class="p__remove-button" onclick="removeFeedMedia('`+id+`')">
                <svg class="icon icon-close-3" style="min-width: 16px; height: 16px; width: 16px;"><use xlink:href="#icons-close-3"></use>
                </svg>
            </div>
        </div>        
      `);      
      break;
      default:
      break;
  }
  
}

function createRandomPosts(){
  $.ajax({
      url: request_source()+'/feed.php', 
      data: {
          action: 'randomPostGenerator',
      },
      type: "GET",
      dataType: 'JSON',
      success: function(response) {
        loadDataPost()
      },
  });   
}


function deleteFeed(fid){
  var result = confirm("Delete post?");
  if (result) {
      $.ajax({
          url: request_source()+'/feed.php', 
          data: {
              action: 'removeFeed',
              fid: fid,
              uid: user_info.id
          },
          type: "GET",
          dataType: 'JSON',
          success: function(response) {
            $('[data-feed-id='+fid+']').remove();
          },
      });
  }  
}