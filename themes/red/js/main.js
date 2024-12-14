function closeHeaderNotification(n) {
	$('[data-header-notification="' + n + '"]').hide();
}

var lockHorizontalScroll = false;
function horizontalScroll(elem, direction, amount) {
	if (lockHorizontalScroll) {
		return false;
	}
	var leftPos = $('#' + elem).scrollLeft();
	var maxW = $('#' + elem).outerWidth();
	var totalW = leftPos + amount;

	if (elem == 'feedStories') {
		if (leftPos == 0 && direction == 'right') {
			$('.stories-left-btn').show();
		}
		if (leftPos <= 650 && direction == 'left' || leftPos == 0 && direction == 'left') {
			$('.stories-left-btn').hide();
		}
	}
	if (elem == 'feedPopulars') {
		if (leftPos == 0 && direction == 'right') {
			$('.populars-left-btn').show();
		}
		if (leftPos <= 600 && direction == 'left' || leftPos == 0 && direction == 'left') {
			$('.populars-left-btn').hide();
		}
	}

	if (direction == 'right') {
		$('#' + elem).animate({ scrollLeft: leftPos + amount }, 400);
	} else {
		$('#' + elem).animate({ scrollLeft: leftPos - amount }, 400);
	}
	lockHorizontalScroll = true;
	setTimeout(function () {
		lockHorizontalScroll = false;
	}, 250)
}


//LOAD STORIES
var loadedStories = [];
loadedStories['totalDiscoverStories'] = 0;
function loadStories(custom = '') {
	if (loadedStories['totalDiscoverStories'] > 0 && url == 'feed') {
		loadedStories.stories.forEach(function (story, index) {
			index = index + 1;
			$('#story' + index).attr('data-story-loaded', '1');
			if (custom.indexOf("user") != -1) {
				$('#story' + index).attr('onclick', 'openStoryProfile(story' + index + ',' + story.id + ',"' + story.date + '")');
			} else {
				$('#story' + index).attr('onclick', 'openStoryDiscover(story' + index + ',' + story.id + ')');
			}

			$('#story' + index).find('.loading-story').removeClass('loading-story');
			$('#story' + index).find('.avatar__img').css('background-image', 'url(' + story.thumb + ')');
			$('#story' + index).find('.avatar__story-border').addClass('avatar__story-border--active');
			setTimeout(function () {
				$('[data-story-loaded=0]').fadeOut();
			}, 250)
		});
	} else {
		$('#feedStories').show();
		$.getJSON(aUrl, { action: 'loadStories', looking: user_info.s_gender, custom: custom }, function (data) {
			totalDiscoverStories = data.totalDiscoverStories;

			if (data.result == 'empty') {
				$('#feedStories').hide();
			}

			if (data.totalDiscoverStories > 0) {
				if (url == 'feed') {
					loadedStories = data;
				}
				if (url == 'profile') {
					$('#profile-stories-total').text(data.totalDiscoverStories);
				}

				if (data.totalDiscoverStories > 4) {
					$('#stories-right-btn').show();
				}

				data.stories.forEach(function (story, index) {
					index = index + 1;
					$('#story' + index).attr('data-story-loaded', '1');
					if (custom.indexOf("user") != -1) {
						$('#story' + index).attr('onclick', 'openStoryProfile(story' + index + ',' + story.id + ',"' + story.date + '")');
					} else {
						$('#story' + index).attr('onclick', 'openStoryDiscover(story' + index + ',' + story.id + ')');
					}

					$('#story' + index).find('.loading-story').removeClass('loading-story');
					$('#story' + index).find('.avatar__img').css('background-image', 'url(' + story.thumb + ')');
					$('#story' + index).find('.avatar__story-border').addClass('avatar__story-border--active');

				});
			} else {
				if (url == 'profile') {
					$('#profile-stories').hide();
				}
				if (url == 'feed') {
					setTimeout(function () {
						$('.br__stories').css('height', '40px');
					}, 450)
				}
			}

			setTimeout(function () {
				$('[data-story-loaded=0]').fadeOut();
			}, 250)
		});
	}
}

//LOAD POPULARS
var loadedPopular = [];
function loadPopular(cat = 'top') {
	if (loadedPopular.length > 0 && cat == 'top') {
		loadedPopular.forEach(function (popular, index) {
			$('#popularUser' + index).attr('data-popular-loaded', '1');
			//$('#popularUser'+index).attr('onclick','openStoryDiscover(story'+index+','+story.id+')');
			$('#popularUser' + index).find('a').attr('onclick', 'goToProfile(event,' + popular.id + ')');
			$('#popularUser' + index).find('a').attr('href', site_config.site_url + popular.username);

			$('#popularUser' + index).find('.loading-story').removeClass('loading-story');
			preloadImagesRed([popular.photo_big], function () {
				$('#popularUser' + index).find('.loading-popular').remove();
				$('#popularUser' + index).find('.m').prepend('<img class="m__cover m__cover--image" src="' + popular.photo_big + '" alt="' + popular.username + ' ' + site_config.name + '">');
			});
			$('#popularUser' + index).find('.avatar__img').css('background-image', 'url(' + popular.photo + ')');
			$('#popularUser' + index).find('.avatar__story-border').addClass('avatar__story-border--popular');
			$('#popularUser' + index).find('.displayname__name').text(popular.username);
		});
	} else {
		$.getJSON(aUrl, { action: 'loadPopulars', category: cat }, function (data) {
			if (cat == 'top') {
				loadedPopular = data;
			}
			data.forEach(function (popular, index) {
				$('#popularUser' + index).attr('data-popular-loaded', '1');
				//$('#popularUser'+index).attr('onclick','openStoryDiscover(story'+index+','+story.id+')');
				$('#popularUser' + index).find('a').attr('onclick', 'goToProfile(event,' + popular.id + ')');
				$('#popularUser' + index).find('a').attr('href', site_config.site_url + popular.username);

				$('#popularUser' + index).find('.loading-story').removeClass('loading-story');
				preloadImagesRed([popular.photo_big], function () {
					$('#popularUser' + index).find('.loading-popular').remove();
					$('#popularUser' + index).find('.m').prepend('<img class="m__cover m__cover--image" src="' + popular.photo_big + '" alt="' + popular.username + ' ' + site_config.name + '">');
				});
				$('#popularUser' + index).find('.avatar__img').css('background-image', 'url(' + popular.photo + ')');
				$('#popularUser' + index).find('.avatar__story-border').addClass('avatar__story-border--popular');
				$('#popularUser' + index).find('.displayname__name').text(popular.username);
			});
		});
	}
}

//LOAD CREATORS
var loadModelsPage = 0;
var loadedCreators = [];
var noMoreModels = false;
function loadModels(cat = 'trending', limit = 0) {
	var loadFromUrl = false;
	if (currentDiscoverPage != 0) {
		loadFromUrl = true;
		limit = currentDiscoverPage;
		currentDiscoverPage = 0;
	}
	if (loadedCreators.length > 0 && cat == 'trending' && loadModelsPage == 0) {
		loadedCreators.forEach(function (model, index) {
			if (loadModelsPage > 0) {
				index = 20 * loadModelsPage + index;
			}
			$('#model' + index).attr('data-model-loaded', '1');
			$('#model' + index).find('a').attr('onclick', 'goToProfile(event,' + model.id + ')');
			$('#model' + index).find('a').attr('href', site_config.site_url + model.username);

			$('#model' + index).find('.loading-story').removeClass('loading-story');
			preloadImagesRed([model.photo_big], function () {
				$('#model' + index).find('.loading-feed').remove();
				$('#model' + index).find('.a').prepend('<img class="a__cover a__cover--image" src="' + model.photo_big + '" alt="' + model.username + ' ' + site_config.name + '">');
			});
			$('#model' + index).find('.avatar__img').css('background-image', 'url(' + model.photo + ')');
			$('#model' + index).find('.avatar__story-border').addClass('avatar__story-border--popular');
			$('#model' + index).find('.displayname__name').text(model.username);
			$('#model' + index).find('.m__followers').append(model.media);
		});

		setTimeout(function () {
			if (loadFromUrl) {
				loadModelsPage = limit;
				loadFromUrl = false;
			}
		}, 250)
	} else {
		$.getJSON(aUrl, { action: 'loadModels', category: cat, limit: limit }, function (data) {
			if (cat == 'trending' && loadModelsPage == 0) {
				loadedCreators = data;
			}

			if (data.length < 20) {
				noMoreModels = true;
				for (var i = 20 - 1; i >= data.length; i--) {
					$('#model' + i).fadeOut();
				}
			}

			if (data.length == 0) {
				$('[data-model-loaded=0]').fadeOut('slow');
				setTimeout(function () {
					$('#nothingYet').show();
				}, 250)
			}

			data.forEach(function (model, index) {
				if (loadModelsPage > 0) {
					index = 20 * loadModelsPage + index;
				}
				$('#model' + index).attr('data-model-loaded', '1');
				$('#model' + index).find('a').attr('onclick', 'goToProfile(event,' + model.id + ')');
				$('#model' + index).find('a').attr('href', site_config.site_url + model.username);

				$('#model' + index).find('.loading-story').removeClass('loading-story');
				preloadImagesRed([model.photo_big], function () {
					$('#model' + index).find('.loading-feed').remove();
					$('#model' + index).find('.a').prepend('<img class="a__cover a__cover--image" src="' + model.photo_big + '" alt="' + model.username + ' ' + site_config.name + '">');
				});
				$('#model' + index).find('.avatar__img').css('background-image', 'url(' + model.photo + ')');
				$('#model' + index).find('.avatar__story-border').addClass('avatar__story-border--popular');
				$('#model' + index).find('.displayname__name').text(model.username);
				$('#model' + index).find('.m__followers').append(model.media);
			});

			setTimeout(function () {
				if (loadFromUrl) {
					loadModelsPage = limit;
					loadFromUrl = false;
				}
				//$('[data-model-loaded=0]').fadeOut();
			}, 250)
		});
	}

}

//LOAD POSTS
var loadPostsPage = 0;
var loadedPosts = [];
var noMorePosts = false;
function loadPosts(cat = 'new', limit = 0) {
	if (loadedPosts.length > 0 && cat == 'new' && loadPostsPage == 0) {
		loadedPosts.forEach(function (post, index) {
			if (loadPostsPage > 0) {
				index = 40 * loadPostsPage + index;
			}
			$('#post' + index).attr('data-post-loaded', '1');

			if (isMobile) {
				$('#post' + index).find('a').attr('onclick', 'goTo("post",' + post.data.id + ')');
				$('#post' + index).find('a').attr('href', 'javascript:;');
			} else {
				$('#post' + index).find('a').attr('onclick', '');
				$('#post' + index).find('a').attr('href', site_config.site_url + 'post/' + post.data.id);
			}

			$('#post' + index).find('.loading-story').removeClass('loading-story');

			var showImage = post.data.post_src;

			if (post.data.post_type == 'gallery') {
				var gallery = showImage.split(",");
				showImage = gallery[0];
				$('#post' + index).find('.a__top-right-block').append(`
	                    <svg class="icon icon-album-2 a__top-right-block-icon">
	                        <use xlink:href="#icons-album-2"></use>
	                    </svg> 					
					`);
			}

			if (post.data.post_type == 'video') {
				showImage = post.data.post_poster;
				$('#post' + index).find('.a__top-right-block').append(`
	                    <svg class="icon icon-album-2 a__top-right-block-icon">
	                        <use xlink:href="#icons-play"></use>
	                    </svg> 					
					`);
			}

			if (post.data.post_premium == 'Yes') {
				showImage = post.data.post_blur;
				$('#post' + index).find('.a').append(`
						<div class="B B--style--regular">
						    <div class="B__icon-wrapper">
						        <svg class="icon icon-lock-1 B__icon"><use xlink:href="#icons-lock-1"></use></svg>
						    </div>
						    <div class="B__label"></div>
						</div>					
					`);

			}

			preloadImagesRed([showImage], function () {
				$('#post' + index).find('.loading-feed').remove();
				$('#post' + index).find('.a').prepend('<img class="a__cover a__cover--image" src="' + showImage + '" alt="' + post.username + ' ' + site_config.name + '">');
			});
			$('#post' + index).find('.avatar__img').css('background-image', 'url(' + post.photo + ')');
			$('#post' + index).find('.avatar__story-border').addClass('avatar__story-border--popular');
			$('#post' + index).find('.displayname__name').text(post.username);
		});

		setTimeout(function () {
			loadPostsPage = limit;
		}, 250)
	} else {
		$.getJSON(aUrl, { action: 'loadPosts', category: cat, limit: limit }, function (data) {
			if (cat == 'new' && loadPostsPage == 0) {
				loadedPosts = data;
			}

			if (data.length < 40) {
				noMorePosts = true;
			}

			if (data.length == 0) {
				$('[data-post-loaded=0]').fadeOut('slow');
				setTimeout(function () {
					$('#nothingYet').show();
				}, 250)
			}

			data.forEach(function (post, index) {
				if (loadPostsPage > 0) {
					index = 40 * loadPostsPage + index;
				}

				$('#post' + index).attr('data-post-loaded', '1');

				if (isMobile) {
					$('#post' + index).find('a').attr('onclick', 'goTo("post",' + post.data.id + ')');
					$('#post' + index).find('a').attr('href', 'javascript:;');
				} else {
					$('#post' + index).find('a').attr('onclick', '');
					$('#post' + index).find('a').attr('href', site_config.site_url + 'post/' + post.data.id);
				}

				$('#post' + index).find('.loading-story').removeClass('loading-story');

				var showImage = post.data.post_src;

				if (post.data.post_type == 'gallery') {
					var gallery = showImage.split(",");
					showImage = gallery[0];
					$('#post' + index).find('.a__top-right-block').append(`
	                    <svg class="icon icon-album-2 a__top-right-block-icon">
	                        <use xlink:href="#icons-album-2"></use>
	                    </svg> 					
					`);
				}

				if (post.data.post_type == 'video') {
					showImage = post.data.post_poster;
					$('#post' + index).find('.a__top-right-block').append(`
	                    <svg class="icon icon-album-2 a__top-right-block-icon">
	                        <use xlink:href="#icons-play"></use>
	                    </svg> 					
					`);
				}

				if (post.data.post_premium == 'Yes') {
					showImage = post.data.post_blur;
					$('#post' + index).find('.a').append(`
						<div class="B B--style--regular">
						    <div class="B__icon-wrapper">
						        <svg class="icon icon-lock-1 B__icon"><use xlink:href="#icons-lock-1"></use></svg>
						    </div>
						    <div class="B__label"></div>
						</div>					
					`);

				}

				preloadImagesRed([showImage], function () {
					$('#post' + index).find('.loading-feed').remove();
					$('#post' + index).find('.a').prepend('<img class="a__cover a__cover--image" src="' + showImage + '" alt="' + post.username + ' ' + site_config.name + '">');
				});
				$('#post' + index).find('.avatar__img').css('background-image', 'url(' + post.photo + ')');
				$('#post' + index).find('.avatar__story-border').addClass('avatar__story-border--popular');
				$('#post' + index).find('.displayname__name').text(post.username);
			});

			setTimeout(function () {
				loadPostsPage = limit;
				if (noMorePosts) {
					$('[data-post-loaded=0]').remove();
				}
			}, 250)
		});
	}
}

var currentProfileMedia = [];
function loadProfileMedia(user, tab = 'all', type = "all") {
	$.getJSON(aUrl, { action: 'loadProfileMedia', user: user, type: type, tab: tab }, function (data) {
		currentProfileMedia = data;
		data.forEach(function (media, index) {
			$('#profileMedia' + index).attr('data-profile-media-loaded', '1');
			$('#profileMedia' + index).attr('data-media-paid', media.private);
			$('#profileMedia' + index).attr('data-media-id', media.id);
			var isPhoto = 1;
			var isVideo = 0;
			if (media.video == 1) {
				isPhoto = 0;
				isVideo = 1;
			}
			$('#profileMedia' + index).attr('data-media-photo', isPhoto);
			$('#profileMedia' + index).attr('data-media-video', isVideo);
			preloadImagesRed([media.thumb], function () {
				$('#profileMedia' + index).find('.loading-media').remove();
				$('#profileMedia' + index).html(media.html);
				refreshFsLightbox();
			});
		});

		setTimeout(function () {
			$('[data-profile-media-loaded=0]').fadeOut();

		}, 250)
	});
}

function profileSettigsTab(action) {

	$('[data-settings-tab]').removeClass('tab-interactive-active-ds');
	$('[data-settings-tab="' + action + '"]').addClass('tab-interactive-active-ds');
	$('[data-setting-page]').hide();
	$('[data-setting-page="' + action + '"]').show();
	$('#profile-settings-section').text($('[data-settings-tab="' + action + '"]').text());

	if (action == 'documents' && user_info.creator_pending == 'Pending') {
		$('#notificationModalHeader').text('Become creator request sent succesfully');
		$('#notificationModalText').text('Thank you ' + user_info.username + ' for sending us your information, our verification team will review your information shortly');
		openModal('notification');
		$('#notificationModalBtn').attr('onclick', 'closeModal("notification");profileSettigsTab("settings")');
	}

	if (action == 'documents' && user_info.creator_pending == 'Denied') {
		$('#notificationModalHeader').text('Your request was denied');
		$('#notificationModalText').text('Sorry ' + user_info.username + ' but we couldnt approve you as creator in our site, feel free to try again if you place');
		openModal('notification');
		$('#notificationModalBtn').attr('onclick', 'closeModal("notification");');
		$.get(aUrl, { action: 'removeUserVerification' }, function (data) {
		});
		user_info.creator_pending = 'noData';
	}

}

function profileMediaTab(action) {
	$('[data-profile-tab]').removeClass('tab-interactive-active-ds');
	$('[data-profile-tab="' + action + '"]').addClass('tab-interactive-active-ds');

	if (isMobile) {
		$('body').scrollTop(595);
	} else {
		$('body').scrollTop(655);
	}


	if (action == 'feed') {
		$('#feed').show();
		$('#media').hide();
	} else {
		$('#feed').hide();
		$('#media').show();
		if (profileTotalMedia == 0) {
			$('#noMedia').show();
		}
	}
}

var tipAmount = '';
var tipMsg = '';
function addMoneyTabs(action) {
	$('[data-pay-tab]').removeClass('radio-pill-controls__item--active');
	$('[data-pay-tab="' + action + '"]').addClass('radio-pill-controls__item--active');
	$('#iframePayment').attr('src', site_config.site_url + 'pay/index.php?type=credits&package=' + action);
}

function sendTipTabs(amount) {
	tipAmount = amount;
	$('[data-tip-tab]').removeClass('radio-pill-controls__item--active');
	$('[data-tip-tab="' + amount + '"]').addClass('radio-pill-controls__item--active');
	$('#sendTipBtn').attr('disabled', false);
	$('#sendTipBtn').text('Send ' + plugins['settings']['currencySymbol'] + amount);
	$('#sendTipBtn').removeClass('subscribeBtnDisabled');
}

function sendTipCustomMsg(val) {
	tipMsg = val;
	if (val.length == 0) {
		$('.textarea__placeholder').show();
	} else {
		$('.textarea__placeholder').hide();
	}
}

function payoutNote(val) {
	if (val.length == 0) {
		$('.textarea__placeholder').show();
	} else {
		$('.textarea__placeholder').hide();
	}
}

function sendTipNow() {
	var a = tipAmount,
		m = tipMsg;
	if (a == '') {
		return false;
	}
	if (a < 1) {
		return false;
	}

	if (user_info.guest == 1) {
		closeModal('tip');
		openModal('register');
		return false;
	}

	if (user_info.credits < parseInt(a)) {
		sendToast('Not enought balance', 'Please add money to your account');
		openModal('founds');
		return false;
	} else {
		closeModal('tip');

		if (url == 'live') {
			if (tipMsg == '') {
				sendLiveMessage('fake', 'I just send you $' + tipAmount + ' tip', user_info.username);
			} else {
				sendLiveMessage('fake', tipMsg, user_info.username);
			}

			return false;
		}

		$.getJSON(aUrl, { action: 'getUserId', username: tipUsername }, function (data) {
			var messageVal = m;
			var tipID = data.id;
			var message = user_info.id + '[message]' + tipID + '[message]' + messageVal + '[message]credits' + '[message]' + a;
			var send = user_info.id + '[rt]' + tipID + '[rt]' + user_info.profile_photo + ',[rt]' + user_info.first_name + '[rt]' + messageVal + '[rt]credits[rt]' + a;

			user_info.credits = user_info.credits - a;
			user_info.credits = parseInt(user_info.credits);

			$('.user-balance').text(plugins['settings']['currencySymbol'] + user_info.credits);

			$.get(gUrl, { action: 'message', query: send });
			$.get(aUrl, { action: 'sendMessage', query: message });

			var me = Math.floor(Math.random() * 10000000);
			var message2 = messageVal;
			var msgTime = formatAMPM(new Date);
			if (messageVal.length > 0) {
				messageVal = `<strong style="color:#19ff42">I sent you a ` + plugins['settings']['currencySymbol'] + a + ` tip</strong><br><br>` + message2;
			} else {
				messageVal = `<strong style="color:#19ff42">I sent you a ` + plugins['settings']['currencySymbol'] + a + ` tip</strong>`;
			}

			var newMessage = `
		    <div data-me="`+ me + `" class="base-message-wrapper base-message-wrapper--mobile base-message-wrapper--position--right">
		        <div class="base-message-wrapper__content">
		            <div class="base-message-wrapper__body-wrapper">
		                <div class="base-message own-text-message" style="background:rgb(26 245 0 / 28%)">
		                    `+ messageVal + `
		                    <span class="message-indicators text-message-indicators">
		                        <span class="message-indicators__content">`+ msgTime + `
		                            <svg class="icon icon-check-4 read-icon icon--default-size">
		                            	<use xlink:href="#icons-check-4"></use>
		                            </svg>
		                        </span>
		                    </span>
		                </div>
		            </div>
		        </div>
		    </div>`;


			//if its first message
			if (loadedChats[tipID] === undefined) {
				loadedConversations[tipID] = 'Loaded';
				loadedConversations[tipID]['unread'] = 0;
				$('#new-chat').clone().each(function (i) {
					this.id = "newchat" + i;
				}).prependTo("#new-chats");
				$('#new-chat').hide();
				reloadUserConversations(user_info.id);
			}

			$('[data-chat-lastm=' + tipID + ']').html(messageVal);

			var addMessageToArray = loadedChats[tipID]['chat'] + newMessage;
			loadedChats[tipID]['chat'] = addMessageToArray;
			loadedChats[tipID]['new'] = loadedChats[tipID]['new'] + 1;

			$('#chat-messages').append(newMessage);

			$('#chat-message').text("");

			sendToast('Tip sent', 'You have succesfully sent ' + plugins['settings']['currencySymbol'] + tipAmount);

		});
	}
}

function subscriptionPrice(days, price) {
	$('[data-sub]').removeClass('o--state--selected');
	$('[data-sub]').find('.checkbox-ds__box').removeClass('checkbox-ds__box--checked');
	$('[data-sub]').addClass('o--state--not-selected');
	$('[data-sub="' + days + '"]').removeClass('o--state--not-selected');
	$('[data-sub="' + days + '"]').addClass('o--state--selected');
	$('[data-sub="' + days + '"]').find('.checkbox-ds__box').addClass('checkbox-ds__box--checked');

	$('#subscribeBtn').removeClass('subscribeBtnDisabled');
	$('#subscribeBtn').attr('disabled', false);
	$('#subscribeBtn').text('subscribeBtnDisabled');
	$('#subscribeBtn').html(`
		Subscribe for `+ plugins['settings']['currencySymbol'] + `<span id="subPrice" style="margin-right:5px"></span> / <span id="subDays" style="margin-right:5px;margin-left:5px"></span> days`);
	$('#subDays').text(days);
	$('#subPrice').text(price);

	if (user_info.credits < price) {
		$('#subscribeBtn').on("click", function () {
			goTo("settings", "wallet");
		});
	} else {
		$('#subscribeBtn').on("click", function () {
			subscribeNow(days, price);
		});
	}
}


function subscribeNow(days, price) {
	$('#subscribeBtn').attr('disabled', true);
	$('#subscribeBtn').addClass('subscribeBtnDisabled');
	$.getJSON(aUrl, { action: 'subscribe', u1: user_info.id, u2: sTo, days: days, price: price, reason: 'sub' }, function (data) {
		setTimeout(function () {
			if (url != 'profile') {
				goToProfile('noEvent', sTo);
				closeModal('premium');
			} else {
				window.location.reload();
			}
		}, 250);
	});
}



function unsubNow(creator) {
	$('#unsubModalBtn').attr('disabled', true);
	$('#unsubModalBtn').addClass('subscribeBtnDisabled');
	$.getJSON(aUrl, { action: 'subscribe', u1: user_info.id, u2: creator, days: 0, price: 0, reason: 'unsub' }, function (data) {
		setTimeout(function () {
			if (url != 'profile') {
				goToProfile('noEvent', creator);
				closeModal('unsub');
			} else {
				window.location.reload();
			}
		}, 250);
	});
}

function profileMediaPill(action) {
	$('[data-profile-pill]').removeClass('pill-control__item--active');
	$('[data-profile-pill="' + action + '"]').addClass('pill-control__item--active');
	$('#noPrivateMedia').hide();
	if (action == 'free') {
		$('[data-media-paid=0]').show();
		$('[data-media-paid=1]').hide();
		$('.user-media-content-sub-title__title-count').text(profileTotalMediaFree);
	} else if (action == 'paid') {
		$('[data-media-paid=1]').show();
		$('[data-media-paid=0]').hide();
		$('.user-media-content-sub-title__title-count').text(profileTotalMediaPrivate);
		if (profileTotalMediaPrivate == 0) {
			$('#noPrivateMedia').show();
		}
	} else if (action == 'photos') {
		$('[data-media-photo=1]').show();
		$('[data-media-photo=0]').hide();
		$('.user-media-content-sub-title__title-count').text(profileTotalPhotos);
	} else if (action == 'videos') {
		$('[data-media-video=1]').show();
		$('[data-media-video=0]').hide();
		$('.user-media-content-sub-title__title-count').text(profileTotalVideos);
	} else {
		$('[data-media-paid=1]').show();
		$('[data-media-paid=0]').show();
		$('.user-media-content-sub-title__title-count').text(profileTotalMedia);
	}
	var target = $('#media');
	$('html,body').animate({
		scrollTop: target.offset().top - 100
	}, 100);
}




function feedPopularLoad(cat) {
	$('#feedPopular').html('');
	$('#feedPopular').animate({ scrollLeft: 0 }, 400);
	$('.bLL__tab').removeClass('bLL__tab--active');
	$('.populars-left-btn').hide();
	$('[data-home-tab="' + cat + '"]').addClass('bLL__tab--active');
	for (let i = 0; i <= 10; i++) {
		$('#feedPopular').append(`
	  	<div class="bK__item" id="popularUser`+ i + `" data-popular-loaded="0">
            <div class="m m--clickable">
                <div class="m__cover m__cover--image loading-popular"></div>
                <a class="m__cover" href="javascript:;"></a>
                <a class="username-inline-card white-color-text" href="javascript:;">
                    <div class="username-inline-card__avatar-wrapper username-inline-card__avatar-wrapper--space-size--small">
                        <span class="avatar" style="--avatar-size: 40px;">
                            <span class="avatar__story-border"></span>
                            <span class="avatar__img loading-story"></span>
                        </span>
                    </div>
                    <div>
                        <div class="displayname displayname--size--small-x">
                            <div class="displayname__name displayname__name--text-overflow"></div>
                        </div>
                        <span class="m__followers">
                            <svg class="icon icon-people indent-right-small-6" style="min-width: 16px; height: 16px; width: 16px;">
                                <use xlink:href="#icons-people"></use>
                            </svg>
                            2421
                        </span>
                    </div>
                </a>
            </div>
        </div>`);
	}
	loadPopular(cat);
}

//SEARCH CREATOR
var isSearching = false;
function searchCreator(search) {
	if (search.length < 2) {
		$('#searchResults').hide();
		$('#searchResultContent').html('');
		$('#searchClose').hide();
		return false;
	} else {
		$('#searchResultContent').html('');
		$('#searchResults').show();
		$('#searchClose').show();
	}
	if (!isSearching) {
		isSearching = true;
		$.getJSON(aUrl, { action: 'searchCreators', search: search }, function (data) {
			isSearching = false;
			data.forEach(function (model, index) {
				$('#searchResultContent').append(`
		            <div class="header-search-result-item">
		                <a id="searchCreator`+ model.id + `" class="username-inline-card username-inline-card--spaced username-inline-card--full-width white-color-text" href="javascript:;">
		                    <div class="username-inline-card__avatar-wrapper username-inline-card__avatar-wrapper--space-size--medium">
		                        <span class="avatar" style="--avatar-size: 32px; --avatar-border-size: 0px;">
		                            <span class="avatar__img" style="background-image: url('`+ model.photo + `');"></span>
		                        </span>
		                    </div>
		                    <div class="username-inline-card__username-wrapper">
		                        <div class="display-full-name">
		                            <div class="displayname displayname--size--small">
		                                <div class="displayname__name displayname__name--text-overflow">`+ model.username + `</div></div>
		                            <span class="display-full-name__nickname display-full-name__nickname--text-overflow">@`+ model.username + `</span>
		                        </div>
		                    </div>
		                </a>
		        </div>`);
				$('#searchCreator' + model.id).attr('onclick', 'goToProfile(event,' + model.id + ');hideSearch("' + model.username + '")');
			});
		});
	}
}

function hideSearch(username) {
	$('#searchInput').val(username);
	$('#searchResults').hide();
	$('#searchResultContent').html('');
	if (username.length == 0) {
		$('#searchClose').hide();
	}
}



//LOAD REELS
function loadReels(cat = 'trending', limit = 0) {
	$.getJSON(aUrl, { action: 'loadReels', category: cat, limit: limit }, function (data) {
		data.forEach(function (model, index) {
			if (loadModelsPage > 0) {
				index = 20 * loadModelsPage + index;
			}
			$('#model' + index).attr('data-model-loaded', '1');
			$('#model' + index).find('a').attr('onclick', 'openModal("live_pc")');

			$('#model' + index).find('.loading-story').removeClass('loading-story');
			preloadImagesRed([model.thumb], function () {
				$('#model' + index).find('.loading-feed').remove();
				$('#model' + index).find('.a').prepend('<img class="a__cover a__cover--image" src="' + model.thumb + '" alt="' + model.username + ' ' + site_config.name + '">');
			});
		});

		setTimeout(function () {
			$('[data-model-loaded=0]').fadeOut();
		}, 250)
	});
}

//LIVE MODElS
function loadLiveModels(cat = 'trending', limit = 0) {
	$.getJSON(aUrl, { action: 'loadLiveModels', category: cat, limit: limit }, function (data) {
		$('#totalLive').text(data.total);
		loadingPage = false;
		data.model.forEach(function (model, index) {
			if (loadModelsPage > 0) {
				index = 50 * loadModelsPage + index;
			}
			$('#model' + index).attr('data-model-loaded', '1');
			$('#model' + index).find('a').attr('data-stream-id', index);
			$('#model' + index).find('a').attr('data-stream-preview', model.photo_big);
			$('#model' + index).find('a').attr('onclick', 'openLiveModel(' + index + ',"' + model.hls + '","' + model.username + '","' + model.photo_big + '","' + model.caption + '",' + model.viewers + ')');

			$('#model' + index).find('.loading-story').removeClass('loading-story');
			preloadImagesRed([model.photo_big], function () {
				$('#model' + index).find('.loading-live').remove();
				$('#model' + index).find('.loading-feed').remove();
				$('#model' + index).find('.a').prepend('<img class="a__cover a__cover--image" src="' + model.photo_big + '" alt="' + model.username + ' ' + site_config.name + '">');
			});
			$('#model' + index).find('.avatar__img').css('background-image', 'url(' + model.photo + ')');
			$('#model' + index).find('.avatar__story-border').addClass('avatar__story-border--popular');
			$('#model' + index).find('.displayname__name').text(model.username);
		});

		setTimeout(function () {
			//$('[data-model-loaded=0]').fadeOut();
		}, 250)
	});
}

function filterModels(cat, loadmore = false) {
	var min = 0;
	var max = 19;
	if (loadmore) {
		min = loadModelsPage * 20;
		max = loadModelsPage * 20 + 19;
	} else {
		loadModelsPage = 0;
		$('#modelsResult').html('');
		$('#modelsResult').animate({ scrollLeft: 0 }, 400);
		$('.accent-color-text').text(cat + ' models');
		$('.pill-control__item').removeClass('pill-control__item--active');
		$('[data-filter-model-tab="' + cat + '"]').addClass('pill-control__item--active');
	}

	for (let i = min; i <= max; i++) {
		var cs = '';
		var x = i - (20 * loadModelsPage);
		if (x == 2 || x == 5 || x == 14 || x == 15) {
			cs = 'style="grid-column: span 2 / auto;"';
		}
		$('#modelsResult').append(`
		<div class="bL__item discoverModels" id="model`+ i + `" data-model-loaded="0" ` + cs + `>
		    <div class="a a--hover a--clickable a--top-shadow">
		        <div class="a__cover a__cover--image loading-feed"></div>
		        <a class="a__cover" href="#"></a>
		        <div class="a__bottom-shadow"></div>
		        <div class="a__controls">
		            <a class="username-inline-card white-color-text" href="#">
		                <div class="username-inline-card__avatar-wrapper username-inline-card__avatar-wrapper--space-size--small">
		                    <span class="avatar" style="--avatar-size: 48px; --avatar-border-size: 2px;">
		                        <span class="avatar__story-border"></span>                                    
		                        <span class="avatar__img loading-story"></span>
		                    </span>
		                </div>
		                <div>
		                    <div class="displayname displayname--size--small-x">
		                        <div class="displayname__name displayname__name--text-overflow"></div>
		                    </div>
                            <span class="m__followers"><svg class="icon icon-people indent-right-small-6" style="min-width: 16px; height: 16px; width: 16px;">
                                <use xlink:href="#icons-album"></use>
                            </svg></span>  		                    
		                </div>
		            </a>
		        </div>
		    </div>
		</div>`);
	}
	loadingMoreContent = false;
	loadModels(cat, loadModelsPage);
}


function filterPosts(cat, loadmore = false) {
	var min = 0;
	var max = 39;
	if (loadmore) {
		min = loadPostsPage * 40;
		max = loadPostsPage * 40 + 39;
	} else {
		loadPostsPage = 0;
		$('#postsResult').html('');
		$('#postsResult').animate({ scrollLeft: 0 }, 400);
		$('.accent-color-text').text(cat + ' posts');
		$('.pill-control__item').removeClass('pill-control__item--active');
		$('[data-filter-post-tab="' + cat + '"]').addClass('pill-control__item--active');
	}

	for (let i = min; i <= max; i++) {
		$('#postsResult').append(`
		<div class="bL__item " id="post`+ i + `" data-post-loaded="0" >
            <div class="a a--hover a--clickable a--top-shadow">
                <div class="a__cover a__cover--image loading-feed"></div>
                <a class="a__cover" href="#"></a>
                <div class="a__bottom-shadow"></div>

                <div class="a__top-right-block">
                </div>

                <div class="a__bottom-avatar" >
                    <a class="username-inline-card white-color-text" href="javascript:;">
                        <div class="username-inline-card__avatar-wrapper username-inline-card__avatar-wrapper--space-size--small">
                            <span class="avatar" style="--avatar-size: 28px; --avatar-border-size: 2px;">
                                <span class="avatar__img loading-story"></span>
                            </span>
                        </div>
                        <div class="username-inline-card__username-wrapper">
                            <div class="displayname displayname--size--small-x">
                                <div class="displayname__name displayname__name--text-overflow"></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div> `);
	}
	loadingMoreContent = false;
	loadPosts(cat, loadPostsPage);
}

function loadMoreLiveModels(loadmore = false) {
	var min = 0;
	var max = 49;
	if (loadmore) {
		min = loadModelsPage * 50;
		max = loadModelsPage * 50 + 49;
	} else {
		loadModelsPage = 0;
		$('#modelsResult').html('');
		$('#modelsResult').animate({ scrollLeft: 0 }, 400);
	}

	for (let i = min; i <= max; i++) {
		$('#modelsResult').append(`
        <div class="bL__item" id="model`+ i + `" data-model-loaded="0">
            <div class="a a--hover a--clickable a--top-shadow" style="height:200px">
                <div class="live-badge">Live</div>
                <div class="a__cover a__cover--image loading-feed"></div>
                <a class="a__cover" href="javascript:;"></a>
                <div class="a__bottom-shadow"></div>
                <div class="a__controls">
                    <a class="username-inline-card white-color-text" href="javascript:;">
                        <div class="username-inline-card__avatar-wrapper username-inline-card__avatar-wrapper--space-size--small">
                            <span class="avatar" style="--avatar-size: 30px; --avatar-border-size: 2px;">
                                <span class="avatar__story-border"></span>                                    
                                <span class="avatar__img loading-story"></span>
                            </span>
                        </div>
                        <div>
                            <div class="displayname displayname--size--small-x">
                                <div class="displayname__name displayname__name--text-overflow"></div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>`);
		if (isMobile) {
			$('.bL').addClass('bL--squad');
			$('.bL__item').addClass('bL__item--squad');
			$('.a--clickable').addClass('a--squad');
		}
	}

	loadingMoreContent = false;
	loadLiveModels('trending', loadModelsPage);
}

function preloadImagesRed(urls, allImagesLoadedCallback) {
	var loadedCounter = 0;
	var toBeLoadedNumber = urls.length;
	urls.forEach(function (url) {
		preloadImage(url, function () {
			loadedCounter++;
			if (loadedCounter == toBeLoadedNumber) {
				allImagesLoadedCallback();
			}
		});
	});
	function preloadImage(url, anImageLoadedCallback) {
		var img = new Image();
		img.onload = anImageLoadedCallback;
		img.src = url;
	}
}

function toggleDropdown(dropdown) {
	if ($('[data-dropdown=' + dropdown + ']').hasClass('css-popover-content-bottomright-shown')) {
		$('[data-dropdown=' + dropdown + ']').removeClass('css-popover-content-bottomright-shown');
	} else {
		$('[data-dropdown]').removeClass('css-popover-content-bottomright-shown');
		$('[data-dropdown=' + dropdown + ']').toggleClass('css-popover-content-bottomright-shown');
	}
}

function showUserMenu() {
	if (user_info.guest == 1) {
		goTo('landing');
	} else {
		if ($('#header-user-notification-dropdown').is(':visible')) {
			$('#header-user-notification-dropdown').hide();
		}
		$('#header-user-dropdown').toggle();
	}
}

function showUserNotifications() {
	if (user_info.guest == 1) {
		goTo('landing');
	} else {
		if ($('#header-user-dropdown').is(':visible')) {
			$('#header-user-dropdown').hide();
		}
		$('#header-user-notification-dropdown').toggle();
	}
}


function sendToast(title = "", body = "", photo = "", time = 3000, onclick = 'javascript:;') {
	$('.toast-notifications').show();
	$('#notificationTitle').text(title);
	$('#notificationIconPhoto').hide();

	if (body != '') {
		$('#notificationBody').show();
		$('#notificationBody').text(body);
	} else {
		$('#notificationBody').hide();
	}

	if (photo != '') {
		$('#notificationIconPhoto').show();
		$('#notificationPhoto').css('background-image', 'url(' + photo + ')');
	}
	$('.toast-notifications').attr('onclick', onclick);
	setTimeout(function () {
		$('.toast-notifications').fadeOut();
	}, time);
}

function playFeedVid(fvid) {
	feedPlayers[fvid].play();
	$('#fvid' + fvid + '_btn').hide();
}

function copyPostLink(post) {
	var postUrl = site_config.site_url + 'post/' + post;
	navigator.clipboard.writeText(postUrl);
	sendToast('Post link copied!');
	toggleDropdown(post);
}

function copyShareLink() {
	var postUrl = $('#share-url').text();
	navigator.clipboard.writeText(postUrl);
	sendToast('Post link copied!');
}

function shareModal(action, val, photo) {
	if (action == 'post') {
		var shareUrl = site_config.site_url + 'post/' + val;
		shareUrl = shareUrl.replace("https://", "");
		$('#share-url').text(shareUrl);
	} else {
		var shareUrl = site_config.site_url + val;
		shareUrl = shareUrl.replace("https://", "");
		$('#share-url').text(shareUrl);
	}

	$('#share-modal-photo').css('background-image', 'url(' + photo + ')');
	openModal('share');
	$('[data-share="twitter"]').attr('href', 'https://twitter.com/intent/tweet?text=Subscribe to see all private content and live chat with content creators models - ' + shareUrl);
	$('[data-share="telegram"]').attr('href', 'https://t.me/share/url?text=Subscribe to see all private content and live chat with content creators models&url=' + shareUrl);
	$('[data-share="whatsapp"]').attr('href', 'https://wa.me/?text=Subscribe to see all private content and live chat with content creators models - ' + shareUrl);
	$('[data-share="facebook"]').attr('href', 'https://www.facebook.com/sharer.php?u=Subscribe to see all private content and live chat with content creators models - ' + shareUrl);
	$('[data-share="messenger"]').attr('href', 'fb-messenger://share/?link=' + shareUrl + '&app_id=123456789');
	$('[data-share="email"]').attr('href', 'mailto:?subject=Subscribe to see all private content and live chat with content creators models - ' + shareUrl + '&body=Subscribe to see all private content and live chat with content creators models - ' + shareUrl);
	if (isMobile) {
		$('[data-share="messenger"]').show();
	} else {
		$('[data-share="messenger"]').hide();
	}
}

var loadingPage = false;
function goTo(go, custom = '') {
	if (loadingPage) {
		console.log('loading page wait');
		return false;
	}

	if (go == 'live' && currentLiveStream != '') {
		closeModal();
		return false;
	}

	if ($('#header-user-notification-dropdown').is(':visible')) {
		$('#header-user-notification-dropdown').hide();
	}
	if ($('#header-user-dropdown').is(':visible')) {
		$('#header-user-dropdown').hide();
	}

	closeModal();

	loadingPage = true;
	$('#data-content').css("opacity", "0.5");
	$('[data-header-menu]').removeClass('active');
	$('[data-header-menu="' + go + '"]').addClass('active');
	$('body').scrollTop(0);
	ajaxLoad(1);

	userActivityDetected();

	if (go == 'landing' && user_info.guest == 0) {
		go = 'feed';
	}


	$.ajax({
		url: request_source() + '/user.php',
		data: {
			action: go,
			custom: custom
		},
		type: "post",
		success: function (response) {
			window.history.pushState("goTo", site_title(), site_config.site_url + go);
			$('#data-content').html(response);
			$('body').scrollTop(0);
			$('#data-content').css("opacity", "1");
			ajaxLoad(2);

			current_page = go;
			if (go == 'feed') {
				loadFeedPage = 0;
				feedCustomFilter = '';
				loadPopular('top');

				if (user_info.guest == 1) {
					loadUserFeed();
					loadStories();
				} else {
					feedMax = true;
					loadUserFeed('favs');
					loadStories('favs');
				}
			}

			if (go == 'discover') {
				loadModelsPage = 0;
				noMoreModels = false;

				if (custom != '') {
					$('[data-discover-tab]').removeClass('tab-interactive-active-ds');
					$('[data-discover-tab="' + custom + '"]').addClass('tab-interactive-active-ds');
					$('[data-tabs-discover]').hide();
					$('[data-tabs-discover="user"]').show();
					loadModels(custom);
					if (custom == 'following') {
						$('#discoverHeader').text('Following models');
					}
					if (custom == 'subscribed') {
						$('#discoverHeader').text('Subscribed models');
					}
				} else {
					$('[data-tabs-discover]').hide();
					$('[data-tabs-discover="regular"]').show();
					loadModels('trending');
				}
			}

			if (go == 'posts') {
				loadPostsPage = 0;
				noMorePosts = false;
				if (custom != '') {
					$('[data-discover-tab]').removeClass('tab-interactive-active-ds');
					$('[data-discover-tab="' + custom + '"]').addClass('tab-interactive-active-ds');
					$('[data-tabs-discover]').hide();
					$('[data-tabs-discover="user"]').show();
					loadPosts(custom);
					$('#discoverHeader').text('Liked posts');

				} else {
					$('[data-tabs-discover]').hide();
					$('[data-tabs-discover="regular"]').show();
					loadPosts('new');
				}

				$('[data-header-menu="discover"]').addClass('active');
			}

			if (go == 'post') {
				loadUserFeed('post-' + custom);
				noMorePosts = true;
			}

			if (go == 'reels') {
				loadModelsPage = 0;
				loadReels('trending');
			}

			if (go == 'settings') {
				var total_bio_length = document.getElementById("bio-message").value.length;
				document.getElementById("bio-length").innerText = total_bio_length;

				if (custom == 'creator') {
					profileSettigsTab('documents');
				}
				if (custom == 'wallet') {
					profileSettigsTab('wallet');
				}
			}

			if (go == 'live') {
				if (isMobile) {
					$('.bL').addClass('bL--squad');
					$('.bL__item').addClass('bL__item--squad');
					$('.a--clickable').addClass('a--squad');
				} else {
					$('.bL').removeClass('bL--squad');
					$('.bL__item').removeClass('bL__item--squad');
					$('.a--clickable').removeClass('a--squad');
				}
				loadModelsPage = 0;
				loadLiveModels('trending');
			}

			if (go == 'landing') {
				$('#register').submit(function (e) {
					e.preventDefault();
					var findme = "Error";
					$.ajax({
						data: $(this).serialize(),
						url: request_source() + '/user.php',
						type: 'post',
						beforeSend: function () {
							$("#create-acc").html(site_lang[275].text);
							$('.register-error').hide();
						},
						success: function (response) {
							if (response.indexOf(findme) > -1) {
								response = response.replace('Error', '');
								$('.register-error').text(response);
								$('.register-error').show();
								$("#create-acc").html(site_lang[8].text);
							} else {
								window.location.reload();
							}
						}
					});
				});
			}

		},
		complete: function () {
			if (go != 'live' && go != 'feed') {
				loadingPage = false;
			}
		}
	});
}

function goToProfile(e, uid, action = '') {


	if (e != 'noEvent') {
		if (e.button == 1 || e.button == 2) {
			return false;
		} else {
			e.target.href = 'javascript:;';
			e.stopPropagation();
			e.preventDefault();
		}
	}

	if (url == 'profile' && currentModal != 'messages') {
		if (uid == profile_info.id && profile_info.id != user_info.id) {
			if (action == 'subscribe') {
				setTimeout(function () {
					$('#subscribeBtnProfile').click();
				}, 500);
			}
			return false;
		}
	}

	if (currentModal == 'messages') {
		closeModal('messages');
	}

	if ($('#header-user-notification-dropdown').is(':visible')) {
		$('#header-user-notification-dropdown').hide();
	}
	if ($('#header-user-dropdown').is(':visible')) {
		$('#header-user-dropdown').hide();
	}

	if (url == 'chat') {
		closeModal();
	}

	profileFeedMax = true;
	loadFeedPage = 0;

	$('#data-content').css("opacity", "0.5");
	$('[data-header-menu]').removeClass('active');

	ajaxLoad(1);


	$.ajax({
		url: request_source() + '/user.php',
		data: {
			action: 'profile',
			id: uid
		},
		type: "post",
		success: function (response) {
			$('#data-content').html(response);
			$('#data-content').css("opacity", "1");
			ajaxLoad(2);
			$('body').scrollTop(0);
			$("meta[property='og\\:url']").attr("content", site_config.site_url + profile_info.username);
			$("meta[property='og\\:title']").attr("content", profile_info.name + ", " + profile_info.age + " | " + site_config.name);
			$("meta[property='og\\:image']").attr("content", profile_info.profile_photo);

			window.history.pushState("profile", profile_info.name + ", " + profile_info.age + " | " + site_title(), site_config.site_url + profile_info.username);

			url = 'profile';

			if (user_info.id != uid && profile_info.creator == 1 || user_info.id == uid && user_info.creator == 1) {
				loadUserFeed('user-' + profile_info.id);
				loadStories('user-' + profile_info.id);
				loadProfileMedia(profile_info.id);
				$('.common-sign-up-grid__right-image').css('background-image', 'url(' + profile_info.profile_photo_big + ')');
				$('#registerModalModel').text(profile_info.username);

				if (user_info.guest == 0) {
					setTimeout(function () {
						//sendAutoResp(profile_info.id,user_info.id,'visit');
					}, 50);
				}
			}

			if (action == 'subscribe') {
				setTimeout(function () {
					$('#subscribeBtnProfile').click();
				}, 500);
			}
		},
	});
}

switch (url) {

	case "profile":
		$("meta[property='og\\:url']").attr("content", site_config.site_url + profile_info.username);
		$("meta[property='og\\:title']").attr("content", profile_info.name + ", " + profile_info.age + " | " + site_config.name);
		$("meta[property='og\\:image']").attr("content", profile_info.profile_photo);

		window.history.pushState("profile", profile_info.name + ", " + profile_info.age + " | " + site_title(), site_config.site_url + profile_info.username);

		updateSeo('profile');
		userActivityDetected();

		if (plugins['story']['enabled'] == "Yes") {
			storyLoader(profile_info.story, profile_info.stories, profile_info.status_info);
		}
		loadUserFeed('user-' + profile_info.id);
		loadStories('user-' + profile_info.id);
		loadProfileMedia(profile_info.id);

		if (user_info.guest == 0 && user_info.id != profile_info.id) {
			setTimeout(function () {
				//sendAutoResp(profile_info.id,user_info.id,'visit');
			}, 50);
		}

		//liveCountDown('Nov 5, 2023 15:37:25');

		$('.common-sign-up-grid__right-image').css('background-image', 'url(' + profile_info.profile_photo_big + ')');
		$('#registerModalModel').text(profile_info.username);
		break;

	case 'feed':
		loadPopular('top');
		if (user_info.guest == 1) {
			loadStories();
			loadUserFeed();
		} else {
			loadUserFeed('favs');
			loadStories('favs');
		}
		$('[data-header-menu="feed"]').addClass('active');
		break;

	case 'discover':
		loadModels('trending');
		$('[data-header-menu="discover"]').addClass('active');
		break;


	case 'posts':
		loadPosts('new');
		$('[data-header-menu="discover"]').addClass('active');
		break;

	case 'reels':
		loadReels('top');
		$('[data-header-menu="reels"]').addClass('active');
		break;

	case 'live':
		loadLiveModels();
		$('[data-header-menu="live"]').addClass('active');
		break;

	case 'post':
		loadUserFeed(postId);
		noMorePosts = true;
		break;

	case 'settings':
		var total_bio_length = document.getElementById("bio-message").value.length;
		document.getElementById("bio-length").innerText = total_bio_length;
		break;

	case 'landing':
		$('[data-header-menu]').removeClass('active');
		break;

	default:

		break;

}


function updateProfileData(val, col) {
	if (col == 'bio') {
		var total_bio_length = document.getElementById("bio-message").value.length;
		document.getElementById("bio-length").innerText = total_bio_length;
	}

	if (col == 'name' || col == 'bio') {
		$('#updateBioBtn').attr('disabled', false);
	}

	if (col == 'instagram' || col == 'twitter' || col == 'snapchat' || col == 'tiktok' || col == 'telegram') {
		$('#updateSocialBtn').attr('disabled', false);
	}

	var dataSettings = 'data-settings-info';
	if (val == 'updateBio') {
		$('#updateBioBtn').attr('disabled', true);
	}

	if (val == 'updateSocial') {
		$('#updateSocialBtn').attr('disabled', true);
		dataSettings = 'data-settings-social';
	}

	if (val == 'updateSocial' || val == 'updateBio') {
		$('[' + dataSettings + ']').each(function (e) {
			var col = $(this).attr('data-settings');
			var val = $(this).val();
			var data = user_info.id + '[data]' + col + '[data]' + val;
			$.get(aUrl, { action: 'updateUserData', query: data }, function (data) {
			});
		});
		setTimeout(function () {
			sendToast('Profile information updated');
		}, 250);
	}

	if (val == 'updateEmail') {
		var val = $('[data-settings="email"]').val();
		if (val == user_info.email) {
			return false;
		}
		var data = user_info.id + '[data]email[data]' + val;
		$.getJSON(aUrl, { action: 'updateUserData', query: data }, function (data) {
			if (data.OK != 'OK') {
				sendToast(data.reason);
			} else {
				user_info.email = val;
				$('[data-header-notification="email"]').show();
				sendToast('Email updated succesfully');
			}
		});
	}

	if (val == 'updatePassowrd') {
		var val = $('[data-settings="pass"]').val();
		var data = user_info.id + '[data]pass[data]' + val;
		$.getJSON(aUrl, { action: 'updateUserData', query: data }, function (data) {
			if (data.OK != 'OK') {
				sendToast(data.reason);
			} else {
				sendToast('Password updated succesfully');
			}
		});
	}

}

function updateCreatorPrice(price, days, type) {
	$.getJSON(aUrl, { action: 'updateCreatorPrice', uid: user_info.id, days: days, price: price, type: type }, function (data) {
		if (data.OK == 'OK') {
			sendToast('Successfully updated subscription price for ' + days + ' days!');
		}
	});
}

function updateCreatorPayoutMethod(method, type) {
	var val = '';
	if (type == 'email') {
		val = $('#payoutEmailInput').val();
		if (!validateEmail(val)) {
			sendToast('Please write a valid email');
			return false;
		}
	} else {
		val = $('#payoutDetailsInput').val();
		if (val.length > 0) {
			$('.textareaplaceholder').hide();
		} else {
			$('.textareaplaceholder').show();
		}
	}
	$.getJSON(aUrl, { action: 'updateCreatorPayoutMethod', uid: user_info.id, pmethod: method, val: val }, function (data) {
		if (data.OK == 'OK') {

		}
	});
}


var loadingMoreContent = false;
document.addEventListener('DOMContentLoaded', function (e) {
	document.addEventListener('scroll', function (e) {
		let documentHeight = document.body.scrollHeight;
		let currentScroll = window.scrollY + window.innerHeight;
		// When the user is [modifier]px from the bottom, fire the event.
		var modifier = 150;
		if (url == 'feed' || url == 'profile') {
			modifier = 300;
		}
		if (currentScroll + modifier > documentHeight && !loadingMoreContent) {
			if (url == 'discover') {
				if (!noMoreModels) {
					loadingMoreContent = true;
					loadModelsPage++;
					var cat = $('.pill-control__item--active').attr('data-filter-model-tab');
					filterModels(cat, true);
				}
			}

			if (url == 'posts') {
				if (!noMorePosts) {
					loadingMoreContent = true;
					loadPostsPage++;
					var cat = $('.pill-control__item--active').attr('data-filter-post-tab');
					filterPosts(cat, true);
				}
			}

			if (url == 'feed') {
				if (!feedMax) {
					loadingMoreContent = true;
					loadFeedPage++;
					loadMoreFeed();
				}
			}
			if (url == 'live') {
				loadingMoreContent = true;
				loadModelsPage++;
				loadMoreLiveModels(true);
			}
			if (url == 'profile') {
				console.log(profileFeedMax);
				if (!profileFeedMax) {
					loadingMoreContent = true;
					loadFeedPage++;
					loadMoreFeed();
				}
			}
		}
	})
})

var currentModal = '';
var sTo = 0;
function openModal(modal, c = '') {

	if ($('#header-user-notification-dropdown').is(':visible')) {
		$('#header-user-notification-dropdown').hide();
	}
	if ($('#header-user-dropdown').is(':visible')) {
		$('#header-user-dropdown').hide();
	}

	if (url != 'profile' && modal == 'register') {
		goTo('landing');
	} else {
		if (modal == 'messages') {
			if (user_info.guest == 1) {
				if (url == 'profile') {
					modal = 'register';
					$('[data-modal="' + modal + '"]').show();
					currentModal = modal;
				} else {
					goTo('landing');
				}
			} else {

				if (currentModal == 'live_mobile') {
					closeModal('live_mobile');
				}

				$('[data-header-menu]').removeClass('active');
				$('[data-header-menu="' + modal + '"]').addClass('active');
				$('body').scrollTop(0);
				$('body').css('overflow-y', 'hidden');
				document.body.style.overflow = 'hidden';

				if (c == 'menu') {
					if (isMobile) {
						$('.eU__left-part').css('display', 'block');
						$('.eU__right-part').addClass('hideFlex');
						document.body.style.overflow = 'hidden';
					}
					openChat(c);
				} else {
					openChat(c);
				}

				$('[data-modal="' + modal + '"]').show();
				currentModal = modal;

			}
		} else {
			$('[data-modal="' + modal + '"]').show();
			currentModal = modal;

			if (modal == 'premium') {

				if (url != 'profile') {
					const data = c.split(",");
					goToProfile('noEvent', data[0], 'subscribe');
				} else {
					const data = c.split(",");
					const uid = parseInt(data[0]);
					const username = data[1];
					const photo = data[2];

					sTo = uid;

					$('#subscriptionUsername').text('@' + username);
					$('#subscriptionPhoto,#subscriptionPhoto2').css('background-image', 'url(' + photo + ')');
				}
			}

			if (modal == 'unsub') {
				$('#unsubModalBtn').attr('onclick', '');
				const data = c.split(",");
				const uid = parseInt(data[0]);
				const username = data[1];
				const photo = data[2];

				$('#unsubModalUsername').text(username);
				$('#unsubModalBtn').attr('onclick', 'unsubNow(' + uid + ')');
			}

			if (modal == 'founds') {
				if (user_info.guest == 1) {
					closeModal('founds');
					closeModal('premium');
					openModal('register');
				} else {
					document.body.style.overflow = 'hidden';
				}
			}

		}
	}

}

function closeModal(modal = "") {
	if (modal != '') {
		currentModal = modal;
	}
	$('[data-modal="' + currentModal + '"]').hide();

	var closingModal = currentModal;
	if (currentModal == 'live_pc' || currentModal == 'live_mobile') {
		streamHLS.stopLoad();
		currentLiveStream.destroy();
		currentLiveStream = '';
		setTimeout(function () {
			$('.streamingWrapper').html('');
			$('.streamingWrapperMobile').html('');
		}, 500);
		$('body').css('overflow-y', 'auto');
		clearInterval(randomLiveComment);
	}

	if (currentModal == 'founds') {
		document.body.style.overflow = 'auto';
	}

	if (currentModal == 'premium') {
		$('#subscribeBtn').addClass('subscribeBtnDisabled');
		$('#subscribeBtn').html(`
		Choose a subscription plan`);
		$('[data-sub]').removeClass('o--state--selected');
		$('[data-sub]').find('.checkbox-ds__box').removeClass('checkbox-ds__box--checked');
		$('[data-sub]').addClass('o--state--not-selected');
		$('#subscribeBtn').attr('onclick', '');
	}

	if (currentModal == 'publish') {
		$('.emoji-input').html('');
		$('.showEmoji').hide();
	}

	if (currentModal == 'tip') {
		$('#sendTipBtn').attr('disabled', true);
		$('#sendTipBtn').text('Select amount');
		$('#sendTipBtn').addClass('subscribeBtnDisabled');
		$('[data-tip-tab]').removeClass('radio-pill-controls__item--active');
		$('.textarea__placeholder').show();
	}

	if (currentModal == 'messages') {
		$('[data-header-menu]').removeClass('active')
		$('body').css('overflow-y', 'auto');
		$('[data-creator-chat]').removeClass('bDc--selected');
		current_user_id = 0;
		document.body.style.overflow = 'auto';
	}

	currentModal = '';

	if ($('[data-modal="messages"]').is(':visible')) {
		if (closingModal == 'tip') {
			currentModal = 'messages';
		}
	}

	if (closingModal == 'login' && url == 'live') {
		currentModal = 'live_pc';
	}

}

var currentLiveStream = '';
var streamHLS = '';
function openLiveModel(index, hls, username, photo, caption, viewers) {

	$('.streamingWrapper').html('');
	$('.streamingWrapperMobile').html('');
	if (streamHLS != '') {
		streamHLS.stopLoad();
	}
	if (currentLiveStream != '') {
		currentLiveStream.destroy();
	}
	var live = 'desktop';
	if (!isMobile) {
		openModal('live_pc');
		currentModal = 'live_pc';
		sendRndLiveComment();
		$('#live-comments').html('');
	} else {
		openModal('live_mobile');
		live = 'mobile';
		currentModal = 'live_mobile';
	}

	if (user_info.guest == 1) {
		$('#liveCommentSignIn').show();
	}

	$('body').css('overflow-y', 'hidden');

	$('.streamerPhoto').css('background-image', 'url(' + photo + ')');
	$('.media-gallery__thumb--active').css('background-image', 'url(' + photo + ')');
	$('.streamOpenPreview').each(function (e) {
		index++;
		var i = index;
		var preview = $('[data-stream-id=' + i + ']').attr('data-stream-preview');
		var onclick = $('[data-stream-id=' + i + ']').attr('onclick');
		$(this).css('background-image', 'url(' + preview + ')');
		$(this).attr('onclick', onclick);
	})
	$('.streamerName').text(username);
	$('.streamerCaption').text(caption);
	$('.streamerViewers').text(viewers);

	$('#sendLiveGift,#sendLiveGiftMobile').attr('onclick', 'showSendTip(`' + username + '`,`' + photo + '`)');

	if (!isMobile) {
		$('.streamingWrapper').html(`
	        <video id='livevideo-`+ live + `' class="video-player__video video-player__video" style="object-fit: contain;" controls="hide">
	           <source src='`+ hls + `' title='720p' type='application/x-mpegURL'>
	        </video>
		`);
	} else {
		$('.streamingWrapperMobile').html(`
	        <video id='livevideo-`+ live + `' class="video-player__video video-player__video" style="object-fit: contain;" controls="hide">
	           <source src='`+ hls + `' title='720p' type='application/x-mpegURL'>
	        </video>
		`);
	}

	currentLiveStream = fluidPlayer(
		'livevideo-' + live,
		{
			layoutControls: {
				fillToContainer: true,
				autoPlay: true,
			},
			modules: {
				onAfterInitHls: (hls) => {
					streamHLS = hls;
				},
			}
		}
	);

}

function followModel(event, model, val, username, photo) {
	$.get(aUrl, { action: 'game_like', uid1: user_info.id, uid2: model, uid3: val });

	if (user_info.guest == 1) {
		if (url == 'profile') {
			modal = 'register';
			$('[data-modal="' + modal + '"]').show();
			currentModal = modal;
		} else {
			goTo('landing');
		}
	} else {
		if (val == 1) {
			$(event).attr('onclick', 'followModel(this,' + model + ',0,"' + username + '","' + photo + '")');
			$(event).find('svg').css('color', '#ffb509');
			$(event).find('span').css('color', '#ffb509');
			$(event).find('span').text('Unfollow');
			sendToast('New Following', 'Now you are following ' + username, "'" + photo + "'");
			sendAutoResp(model, user_info.id, 'follow');
		} else {
			$(event).attr('onclick', 'followModel(this,' + model + ',1,"' + username + '","' + photo + '")');
			$(event).find('svg').css('color', '#fff');
			$(event).find('span').css('color', '#fff');
			$(event).find('span').text('Follow');
		}
	}
}

function savePost(event, model, val, username, photo) {
	$.get(aUrl, { action: 'game_like', uid1: user_info.id, uid2: model, uid3: val });
	if (val == 1) {
		$(event).attr('onclick', 'followModel(this,' + model + ',0,"' + username + '","' + photo + '")');
		$(event).find('svg').css('color', '#ffb509');
		$(event).find('span').css('color', '#ffb509');
		$(event).find('span').text('Unfollow');
		sendToast('New Following', 'Now you are following ' + username, "'" + photo + "'");
	} else {
		$(event).attr('onclick', 'followModel(this,' + model + ',1,"' + username + '","' + photo + '")');
		$(event).find('svg').css('color', '#fff');
		$(event).find('span').css('color', '#fff');
		$(event).find('span').text('Follow');
	}
}


$(window).ready(function (e) {
	fixResposive();
	$(window).on('resize', function () { fixResposive(); });
});

//var isMobile = Math.min(window.screen.width, window.screen.height) < 768 || navigator.userAgent.indexOf("Mobi") > -1;
var isMobile = Math.min(window.screen.width, window.screen.height) < 770;

function fixResposive() {
	isMobile = Math.min(window.screen.width, window.screen.height) < 770;
	if (isMobile) {
		$("div[class*='--type--big-desktop']").each(function (e) {
			var classList = $(this).attr("class");
			// Creating class array by splitting class list string
			var classArr = classList.split(/\s+/);
			var currentElem = $(this);
			$.each(classArr, function (index, value) {
				if (value.indexOf("--type--big-desktop") >= 0) {
					$(currentElem).removeClass(value);
					var newClass = value.replace("--type--big-desktop", "--type--compact-phone");
					$(currentElem).addClass(newClass);
				}
			});
		});
		$('html').attr('style', "--tap-bar-height:60px;");
		$('[data-responsive="desktop"]').hide();
		$('[data-responsive="mobile"]').show();
		$('.mobile-menu').addClass('mobile-menu-visible');

		if (url == 'live') {
			$('.bL').addClass('bL--squad');
			$('.bL__item').addClass('bL__item--squad');
			$('.a--clickable').addClass('a--squad');
		}
	} else {
		$("div[class*='--type--compact-phone']").each(function (e) {
			var classList = $(this).attr("class");
			// Creating class array by splitting class list string
			var classArr = classList.split(/\s+/);
			var currentElem = $(this);
			$.each(classArr, function (index, value) {
				if (value.indexOf("--type--compact-phone") >= 0) {
					$(currentElem).removeClass(value);
					var newClass = value.replace("--type--compact-phone", "--type--big-desktop");
					$(currentElem).addClass(newClass);
				}
			});
		});
		$('html').attr('style', "");
		$('[data-responsive="mobile"]').hide();
		$('[data-responsive="desktop"]').show();

		if (url == 'live') {
			$('.bL').removeClass('bL--squad');
			$('.bL__item').removeClass('bL__item--squad');
			$('.a--clickable').removeClass('a--squad');
		}
	}

	$('#main-header').show();
}

var tipUsername = '';
function showSendTip(username, photo) {
	$('#tip-avatar').css('background-image', 'url(' + photo + ')');
	$('#tip-username').text(username);
	tipUsername = username;
	openModal('tip');
}

function showSuscribe(username, photo, prices) {
	$('#tip-avatar').css('background-image', 'url(' + photo + ')');
	$('#tip-username').text(username);
	openModal('tip');
}

$(document).on('keydown', function (event) {
	if (event.key == "Escape") {
		if (url == 'live') {
			if (!isMobile) {
				closeModal('live_pc');
			}
		}
	}
});

function isEmpty(obj) {
	for (var key in obj) {
		if (obj.hasOwnProperty(key))
			return false;
	}
	return true;
}


function togglePswd(id) {
	var check = $('#' + id).attr('type');
	if (check == 'password') {
		$('#' + id).attr('type', 'text');
	} else {
		$('#' + id).attr('type', 'password');
	}
}

$('#login-now').on('click', function () {
	$('#login').submit();
	return false;
});

$('#recover-pswd').on('click', function () {
	$('[data-login-input]').hide();
	$('[data-recover-input]').show();
	$('#login_action').val('recover');
	$("#login-now").html('Recover password');
});

$('#login-account').on('click', function () {
	$('[data-login-input]').show();
	$('[data-recover-input]').hide();
	$('#login_action').val('login');
	$("#login-now").html('Login now');
});

$('#registerModal').submit(function (e) {
	e.preventDefault();
	var findme = "Error";
	$.ajax({
		data: $(this).serialize(),
		url: request_source() + '/user.php',
		type: 'post',
		beforeSend: function () {
			$("#create-acc").html(site_lang[275].text);
			$('.register-error').hide();
		},
		success: function (response) {
			if (response.indexOf(findme) > -1) {
				response = response.replace('Error', '');
				$('.register-error').text(response);
				$('.register-error').show();
				$("#create-acc").html(site_lang[8].text);
			} else {
				window.location.reload();
			}
		}
	});
});

$('#register').submit(function (e) {
	e.preventDefault();
	var findme = "Error";
	$.ajax({
		data: $(this).serialize(),
		url: request_source() + '/user.php',
		type: 'post',
		beforeSend: function () {
			$("#create-acc").html(site_lang[275].text);
			$('.register-error').hide();
		},
		success: function (response) {
			if (response.indexOf(findme) > -1) {
				response = response.replace('Error', '');
				$('.register-error').text(response);
				$('.register-error').show();
				$("#create-acc").html(site_lang[8].text);
			} else {
				window.location.reload();
			}
		}
	});
});

$('#login').submit(function (e) {
	e.preventDefault();
	var findme = "Error";
	var action = $('#login_action').val();
	$('.login-error').hide();
	$.ajax({
		data: $(this).serialize(),
		url: request_source() + '/user.php',
		type: 'post',
		beforeSend: function () {
			$("#login-now").html(site_lang[275].text);
			$('#login-error').hide();
		},
		success: function (response) {
			if (response.indexOf(findme) > -1) {
				response = response.replace('Error', '');
				$('.login-error').text(response);
				$('.login-error').show();
				if (action == 'recover') {
					$("#login-now").html('Recover password');
				} else {
					$("#login-now").html('Login now');
				}
			} else {
				if (action == 'login') {
					window.location.reload();
				} else {
					$('.login-error').text(site_lang[341].text);
					$('.login-error').show();
					$("#login-now").attr('disabled', true);
				}
			}
		}
	});
});



function adminUpdatePhoto(uid, pid, video, type, photo) {
	if (video == 1 && type == 'profile' || video == 1 && type == 'discover') {
		alert("Video can be added only as cover");
		return false;
	}
	$.get(aUrl, { action: 'updateCreatorPhoto', uid: uid, pid: pid, type: type });
	if (type == 'cover') {
		sendToast('Cover photo updated!');
		$('body').scrollTop(0);
		if (video == 1) {
			setTimeout(function () {
				window.location.reload();
			}, 1500)

		} else {
			$('#creator_cover_photo').attr('src', photo);
		}
	}
	if (type == 'profile') {
		sendToast('Profile photo updated!');
		$('body').scrollTop(0);
		$('#creator_profile_photo').css('background-image', 'url(' + photo + ')');
		$('#creator_profile_photo2').css('background-image', 'url(' + photo + ')');
	}
	if (type == 'discover') {
		$('#creator_discover_photo').attr('src', photo);
		sendToast('Discover photo updated!');
	}
}

function moderatorDeleteProfile(id) {
	swal({
		title: site_lang[204]['text'],
		text: site_lang[205]['text'],
		confirmButtonText: site_lang[206]['text'],
		type: "warning",
		showCancelButton: true,
	},
		function () {
			$.ajax({
				data: {
					action: 'delete_profile',
					uid: id
				},
				url: request_source() + '/user.php',
				type: 'post',
				beforeSend: function () {
				},
				success: function (response) {
					window.location.href = site_config.site_url;
				}
			});
		});
}


var loadedChats = [];
$('#chat-send').click(function (e) {
	sendMessage();
});

if (plugins['chat']['spamPrevention'] == 'Yes') {

}

$("#chat-message").on('keydown', function (e) {
	if (e.keyCode == 13) {
		e.preventDefault();
		sendMessage();
	}
});

$('#send-photo').on('click', function (e) {
	if (plugins['verification']['phone_msg'] == 'Yes' && user_info.sms_verified == 0) {
		$('#verify-phone-modal').show();
		return false;
	}
	e.preventDefault();
	document.getElementById("uploadContent").click();
	upType = 9;
});

$("#photo-to-send").change(function () {
	$("#sendPhoto").submit();
});

$('#sendPhoto').submit(function () {
	$(this).ajaxSubmit(sendPhoto);
	return false;
});

var r_id = $('#r_id').val();

$("#chat-message").on('keydown', function (e) {
	refreshTypingStatus(r_id)
});


function sendMessage() {
	var r_id = $('#r_id').val();
	var messageVal = $("#chat-message").html().replace(/&nbsp;/g, '');
	var mob = 0;
	if (mobile == true) {
		mob = 1;
	}

	var me = Math.floor(Math.random() * 10000000);
	if (messageVal.length == 0) { return false };
	var str = $("#chat-message").html();
	if (($.trim(str)).length == 0) { return false };


	if (plugins['verification']['phone_msg'] == 'Yes' && user_info.sms_verified == 0) {
		return false;
	}

	if (!allowedToSendMsg) {
		$('#subscribeChatBtn').click();
		$("#chat-message").html('');
		return false;
	}

	/*
	if(plugins['chat']['creditsPerMessageEnabled'] == 'Yes'){
				if(user_info.credits < plugins['chat']['creditsPerMessage']){ 
						openWidget("purchaseCredits");
						return false;
				}	
				var data = [];
				data.name = '';
				data.icon = '';
				data.message = site_lang[610].text+' '+plugins['chat']['creditsPerMessage']+' ' + site_lang[73].text;
			  
		if(plugins['chat']['creditsPerMessageGender'] == user_info.gender){
			updateCredits(user_info.id,plugins['chat']['creditsPerMessage'],1,'Credits for send chat message');
			pushNotif(data,1);
		}
		if(plugins['chat']['creditsPerMessageGender'] == allG){
			updateCredits(user_info.id,plugins['chat']['creditsPerMessage'],1,'Credits for send chat message');
			pushNotif(data,1);
		}	
		if(plugins['chat']['transferCreditsMessageToReciever'] == 'Yes' && plugins['chat']['creditsPerMessageGender'] == user_info.gender){
						updateCredits(r_id,plugins['chat']['creditsPerMessage'],2,'Credits for message recieved');                
				}
		if(plugins['chat']['transferCreditsMessageToReciever'] == 'Yes' && plugins['chat']['creditsPerMessageGender'] == allG){
						updateCredits(r_id,plugins['chat']['creditsPerMessage'],2,'Credits for message recieved');                
				}            
	} */

	$('.eU__header-text').hide();

	//before send
	var message2 = messageVal;
	var msgTime = formatAMPM(new Date);
	var newMessage = `
    <div data-me="`+ me + `" class="base-message-wrapper base-message-wrapper--mobile base-message-wrapper--position--right">
        <div class="base-message-wrapper__content">
            <div class="base-message-wrapper__body-wrapper">
                <div class="base-message own-text-message">
                    `+ messageVal + `
                    <span class="message-indicators text-message-indicators">
                        <span class="message-indicators__content">`+ msgTime + `
                            <svg class="icon icon-check-4 read-icon icon--default-size">
                            	<use xlink:href="#icons-check-4"></use>
                            </svg>
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>`;


	//if its first message
	var checkNewMessage = $('.base-message-wrapper').length;
	if (checkNewMessage == 0) {
		loadedConversations[r_id] = 'Loaded';
		loadedConversations[r_id]['unread'] = 0;
		$('#new-chat').clone().each(function (i) {
			this.id = "newchat" + i;
		}).prependTo("#new-chats");
		$('#new-chat').hide();
	}

	$('[data-chat-lastm=' + r_id + ']').html(messageVal);


	var addMessageToArray = loadedChats[r_id]['chat'] + newMessage;
	loadedChats[r_id]['chat'] = addMessageToArray;
	loadedChats[r_id]['new'] = loadedChats[r_id]['new'] + 1;

	$('#chat-messages').append(newMessage);

	$('#chat-message').text("");
	if (plugins['chat']['spamPrevention'] == 'Yes') {

	}

	if (plugins['aiautoresponder']['enabled'] == 'Yes') {
		sendAutoResp(r_id, user_info.id);
	}

	orderChats(r_id);

	var abajo = document.getElementById("chat-messages").scrollHeight;
	$(".bzz").scrollTop(abajo);

	//orderChats(r_id);

	//send translated message
	if (profile_info.lang_prefix != user_info.lang_prefix && profile_info.fake == 0 && plugins['chat']['translate'] == 'Yes') {
		var tto = profile_info.lang_prefix;
		var tfrom = user_info.lang_prefix;
		var translateTo = tto.replace('-', ' ');
		var translateFrom = tfrom.replace('-', ' ');
		const data = JSON.stringify({
			"text": messageVal,
			"source": translateFrom,
			"target": translateTo
		});

		const xhr = new XMLHttpRequest();
		xhr.withCredentials = true;

		xhr.addEventListener("readystatechange", function () {
			if (this.readyState === this.DONE) {
				var translation = this.responseText;
				var t = JSON.parse(translation);
				var message = user_info.id + '[message]' + r_id + '[message]' + t['translations']['translation'] + '[message]text[message]translated[message]' + messageVal;
				var send = user_info.id + '[rt]' + r_id + '[rt]' + user_info.profile_photo + '[rt]' + user_info.first_name + '[rt]' + t['translations']['translation'] + '[rt]text';
				$.get(gUrl, { action: 'message', query: send });
				$.get(aUrl, { action: 'sendMessage', query: message });
			}
		});

		xhr.open("POST", "https://translate-plus.p.rapidapi.com/translate");
		xhr.setRequestHeader("content-type", "application/json");
		xhr.setRequestHeader("x-rapidapi-host", "translate-plus.p.rapidapi.com");
		xhr.setRequestHeader("x-rapidapi-key", plugins['chat']['translate_key']);

		xhr.send(data);
	} else {

		//send message
		var message = user_info.id + '[message]' + r_id + '[message]' + messageVal + '[message]text';
		var send = user_info.id + '[rt]' + r_id + '[rt]' + user_info.profile_photo + '[rt]' + user_info.first_name + '[rt]' + messageVal + '[rt]text';

		$.get(gUrl, { action: 'message', query: send });
		$.get(aUrl, { action: 'sendMessage', query: message });
		setTimeout(function () {
			if (checkNewMessage == 0) {
				reloadUserConversations(user_info.id);
			}
		}, 250);
	}
}

function sendLiveMessage(type = 'real', msg = '', sender = '') {
	if (type == 'real') {
		var messageVal = $("#live-message").text().replace(/&nbsp;/g, '');
		var me = Math.floor(Math.random() * 10000000);
		if (messageVal.length == 0) { return false };
		var str = $("#live-message").html();
		if (($.trim(str)).length == 0) { return false };
		sender = user_info.username;
		if (user_info.guest == 1) {
			return false;
		}

	} else {
		var messageVal = msg;
	}



	//before send
	var msgTime = formatAMPM(new Date);
	var username = sender;
	var newMessage = `

        <div class="X">
            <div class="X__right" style="padding: 0;">
                <div class="X__name">
                    <a class="X__username opacity-hover" href="javascript:;">
                        <div class="displayname displayname--size--small-xx">
                            <div class="displayname__name displayname__name--text-overflow">`+ username.toUpperCase() + `</div>
                        </div>
                    </a>
                    <div class="X__date">`+ msgTime + `</div>
                </div>
                <div class="X__content">`+ messageVal + `</div>
            </div>
        </div>`;

	$('#live-comments').append(newMessage);

	$('#live-message').text("");


	var abajo = document.getElementById("live-comments").scrollHeight;
	$(".livecommentscontainer").scrollTop(abajo);

}


function loadConversation(uid, c = 0) {

	$('[data-creator-chat]').removeClass('bDc--selected');
	$('[data-creator-chat=' + uid + ']').addClass('bDc--selected');
	$('#conversation-page').removeClass('hideFlex');
	$('.eU__empty-state').hide();

	if (isMobile) {
		$('.eU__left-part').css('display', 'none');
		$('.eU__right-part').removeClass('hideFlex');
	}

	var value = $('[data-creator-chat=' + uid + ']').attr('data-unread');
	$('[data-creator-chat=' + uid + ']').attr('data-unread', 0);

	$('[data-chat-unread-count=' + uid + ']').hide();
	$('[data-chat-unread-count=' + uid + ']').text(0);

	if (value > 0) {
		var a = parseInt($('.has-unread-msg').first().text());
		var cc = parseInt(a) - parseInt(value);

		$('.has-unread-msg').text(cc);
		if (cc <= 0) {
			$('.has-unread-msg').hide();
		}
		$.ajax({
			data: {
				action: "read",
				id: uid
			},
			url: request_source() + '/chat.php',
			type: 'post',
			dataType: 'JSON',
			success: function (response) {
			},
		});
	}

	$('.iswriting').hide();
	$('[data-current-chat="chat"]').html('');

	if (!isMobile) {
		$('#chat-message').focus();
	}

	ajaxLoad(1);
	current_user_id = uid;
	current_chat(current_user_id);
	if (loadedChats[uid] === undefined) {
		$.ajax({
			url: request_source() + '/api.php',
			data: {
				action: "loadConversation",
				id: uid
			},
			type: "GET",
			dataType: 'JSON',
			success: function (response) {
				loadChat(response);
				loadedChats[uid] = response;
				ajaxLoad(2);
			},
			complete: function () {
			}
		});
	} else {
		ajaxLoad(2);
		loadChat(loadedChats[uid], 'Yes');
	}
}


function reloadConversation(uid) {
	$.ajax({
		url: request_source() + '/api.php',
		data: {
			action: "loadConversation",
			id: uid
		},
		type: "GET",
		dataType: 'JSON',
		success: function (response) {
			loadedChats[uid] = response;
			orderChats(uid);
		},
		complete: function () {
		}
	});
}

function reloadUserConversations(uid) {
	$.ajax({
		url: request_source() + '/api.php',
		data: {
			action: "reloadConversations",
			id: uid
		},
		type: "GET",
		dataType: 'JSON',
		success: function (response) {
			$('#conversation-list').html(`
                <div class="bDc bDc--menu--bottom bDc--mode--page" id="new-chat" data-creator-chat="0" data-unread="0" style="display:none">
                    <div class="bDc__avatar-wrapper">
                        <span class="avatar" style="--avatar-border-size: 0px;">
                            <span class="avatar__img" 
                            style="background-image: url()"></span>
                            <span class="user-status-icon" title="Online"></span>
                        </span>
                    </div>
                    <div class="bDc__content">
                        <div class="bDc__name">
                            <div class="displayname">
                                <div class="displayname__name displayname__name--text-overflow">
                                </div>
                            </div>
                        </div>
                        <div class="bDc__text">
                            <span class="personal-notifications-message-item-body" data-chat-lastm></span>
                        </div>
                    </div>
                    <span class="bDc__new" data-chat-unread-count style="display:none">0</span>
                </div>
                <div id="new-chats"></div>
                <div id="userConversations"></div>
			`);

			$('#userConversations').html(response.chats);
			orderChats(uid);
		},
		complete: function () {
		}
	});
}


var allowedToSendMsg = false;
function loadChat(response, fast) {
	if (fast != 'Yes') {
		fast = "No";
	}
	profile_info = response.user;
	if (response.blocked == 1) {
		alert(profile_info.username + ' ' + site_lang[325]['text']);
		$('[data-dating-modal="chat"]').hide();
	}

	$('#r_id').val(profile_info.id);
	$('#rid').val(profile_info.id);
	$('[data-current-chat="photo"]').css('background-image', 'url(' + profile_info.profile_photo + ')');
	$('[data-current-chat="name"]').text(profile_info.username);
	$('#chat-message').attr("placeholder", "Message @" + profile_info.username);
	$('[data-current-chat="name"]').text(profile_info.username);
	$('[data-current-chat="name"]').attr('onClick', 'goToProfile(' + "'noEvent'" + ',' + profile_info.id + ')');
	$('#send-tip-from-chat').attr('onClick', 'showSendTip("' + profile_info.username + '","' + profile_info.profile_photo + '")');
	$('.only-premium-message-username').text(profile_info.username);
	$('#only-premium-message-photo').css('background-image', 'url(' + profile_info.profile_photo + ')');

	if (response.subscriptor == 0) {
		$('#only-premium-message').show();
		allowedToSendMsg = false;
	} else {
		$('#only-premium-message').hide();
		allowedToSendMsg = true;
	}

	if (profile_info.status_info == 1) {
		$('[data-current-chat="online"]').show();
	}

	$('[data-current-chat="chat"]').html(response.chat);

	var abajo = document.getElementById("chat-messages").scrollHeight + 250;
	$(".bzz").scrollTop(abajo);

	if (fast == 'No') {
		setTimeout(function () {
			$('[data-current-chat="sender"]').fadeIn();
		}, 50);
	} else {
		$('[data-current-chat="sender"]').show();
	}

	//suscribebtn
	var onclickC = profile_info.id + ',' + profile_info.username + ',"' + profile_info.profile_photo + '"';
	var onclick = "closeModal('messages');goToProfile(event," + profile_info.id + ",'subscribe');";
	$('#subscribeChatBtn').attr('onClick', onclick);

	refreshFsLightbox();
	$('[data-current-chat="moderation"]').attr('onClick', 'reportUser(' + profile_info.id + ',"' + profile_info.username + '","' + profile_info.profile_photo + '")');
}

var totalUnread = 0;
function orderChats(first = '') {
	if (first == '') {
		first = 0;
	}
	$('[data-creator-chat]').each(function () {
		var unread = $(this).attr('data-unread');
		var uid = $(this).attr('data-creator-chat');

		if (unread > 0 && first == 0) {
			totalUnread = parseInt(unread) + parseInt(totalUnread);
			$(this).prependTo('#conversation-list');
		}
		if (first != 0) {
			if (!$('[data-dating-modal="chat"]').is(':visible')) {
				$('[data-creator-chat=' + first + ']').prependTo('#conversation-list');
			}
		}
	});

	if (totalUnread > 0 && first == 0) {
		$('.has-unread-msg').show().text(totalUnread);
	}
}

function preloadedConversations() {
	$('[data-creator-chat]').each(function () {
		var unread = $(this).attr('data-unread');
		var uid = $(this).attr('data-creator-chat');
		if (uid > 0) {
			loadedConversations[uid] = 'Loaded';
			loadedConversations[uid]['unread'] = unread;
		}
	});
}


function openChat(c = '') {
	url = 'chat';

	if ($('.bDc').length == 1) {
		$('.eU__header-text').show();
	} else {
		$('.eU__header-text').hide();
	}

	$('#new-chat').hide();

	if (c == 'menu') {
		$('.eU__empty-state').show();
		$('#conversation-page').addClass('hideFlex');
	}

	if (c.includes(",")) {
		const data = c.split(",");
		const uid = parseInt(data[0]);

		if (loadedConversations[uid] !== undefined) {

			loadConversation(uid, loadedConversations[uid]['unread']);
		} else {

			$('#conversation-page').addClass('hideFlex');
			$('.eU__empty-state').show();

			$('#new-chat').show();

			$('#new-chat').find('.avatar__img').css('background-image', 'url(' + data[2] + ')');
			$('#new-chat').find('.displayname__name--text-overflow').text(data[1]);
			$('#new-chat').find('.personal-notifications-message-item-body').attr('data-chat-lastm', uid);
			$('#new-chat').find('.personal-notifications-message-item-body').attr('data-chat-lastm', uid);
			$('#new-chat').find('.bDc__new').attr('data-chat-unread-count', uid);

			$('#new-chat').attr('data-creator-chat', uid);
			$('#new-chat').attr('onClick', 'loadConversation(' + uid + ',0)');

			orderChats(uid);
			loadConversation(uid, 0);

		}
	} else {
		orderChats(c);
	}
}

setTimeout(function () {
	orderChats();
	preloadedConversations();
}, 100);



function formatAMPM(date) {
	var hours = date.getHours();
	var minutes = date.getMinutes();
	var ampm = hours >= 12 ? 'PM' : 'AM';
	hours = hours % 12;
	hours = hours ? hours : 12; // the hour '0' should be '12'
	minutes = minutes < 10 ? '0' + minutes : minutes;
	var strTime = hours + ':' + minutes + ' ' + ampm;
	return strTime;
}


function liveCountDown(date = '') {
	var countDownDate = new Date(date).getTime();
	var x = setInterval(function () {
		var now = new Date().getTime();
		var distance = countDownDate - now;

		var days = Math.floor(distance / (1000 * 60 * 60 * 24));
		var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
		var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
		var seconds = Math.floor((distance % (1000 * 60)) / 1000);

		document.getElementById("liveCountDown").innerHTML = days + "d " + hours + "h "
			+ minutes + "m " + seconds + "s ";

		if (distance < 0) {
			clearInterval(x);
			document.getElementById("liveCountDown").innerHTML = "LIVE NOW";
		}
	}, 1000);
}

var randomLiveComment;
var randomLCI = 0;
var randomLiveComments;
$.getJSON(aUrl, { action: 'loadLiveComments' }, function (data) {
	randomLiveComments = data;
});
function sendRndLiveComment() {
	var x = 0;
	randomLiveComment = setInterval(function () {
		sendLiveMessage('fake', randomLiveComments[x]['comment'], randomLiveComments[x]['sender']);
		x++;
	}, 3000);
}

$(document).keyup(function (e) {
	if (e.key === "Escape") { // escape key maps to keycode `27`
		if (url == 'chat' && currentModal == 'messages') {
			closeModal();
		}
	}

	if (e.key === "Enter") { // escape key maps to keycode `27`
		if (url == 'live' && currentModal == 'live_pc') {
			sendLiveMessage();
		}
	}
});


function delMedia(pid, uid) {
	$.get(aUrl, { action: 'deleteMedia', pid: pid, uid: uid });
	$('[data-media-id=' + pid + ']').remove();
}

function deleteProfile() {
	swal({
		title: site_lang[204]['text'],
		text: site_lang[205]['text'],
		confirmButtonText: site_lang[206]['text'],
		type: "warning",
		showCancelButton: true,
	},
		function () {
			$.ajax({
				data: {
					action: 'delete_profile',
					uid: user_info.id
				},
				url: request_source() + '/user.php',
				type: 'post',
				beforeSend: function () {
				},
				success: function (response) {
					window.location.href = site_config.site_url;
				}
			});
		});
}

function setCookie(cname, cvalue, exdays) {
	const d = new Date();
	d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
	let expires = "expires=" + d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function deleteCookie(cname) {
	const d = new Date();
	d.setTime(d.getTime() + (24 * 60 * 60 * 1000));
	let expires = "expires=" + d.toUTCString();
	document.cookie = cname + "=;" + expires + ";path=/";
}

function getCookie(cname) {
	let name = cname + "=";
	let decodedCookie = decodeURIComponent(document.cookie);
	let ca = decodedCookie.split(';');
	for (let i = 0; i < ca.length; i++) {
		let c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
}

function acceptCookieConsent() {
	deleteCookie('user_cookie_consent');
	setCookie('user_cookie_consent', 1, 365);
	document.getElementById("cookieDiv").style.display = "none";
}

let cookie_consent = getCookie("user_cookie_consent");
let age_consent = getCookie("user_age_consent");
if (cookie_consent != "") {

} else {
	/*
		$('#cookieDiv').html(`
		<div class="eb" >
				<svg class="icon icon-cookie eb__icon-cookie" style="min-width: 18px; height: 18px; width: 18px;"><use xlink:href="#icons-cookie"></use></svg>
				<div class="eb__description"><span>By using this website, you agree to our <a class="eb__learn-more" rel="nofollow" href="cookies">Cookies Policy</a>. We use cookies to deliver our services.</span></div>
				<button class="btn btn-default btn-large" onclick="acceptCookieConsent()" type="button">OK</button>
		</div>`); */
}

if (age_consent == "") {
	openModal('age');
}

let cookie_visit_msg = getCookie("user_first_msg");
if (cookie_visit_msg == "" && user_info.guest == 0) {
	deleteCookie('user_first_msg');
	setCookie('user_first_msg', 1, 30);
	loadProfileMedia(516);
	sendAutoResp(516, user_info.id, 'visit');
}

//hide header scroll down
$(function () {
	var lastScrollTop = 0, delta = 20;
	$(window).scroll(function (event) {
		var st = $(this).scrollTop();

		if (Math.abs(lastScrollTop - st) <= delta)
			return;
		if ((st > lastScrollTop) && (lastScrollTop > 0) && isMobile) {
			// downscroll code
			$(".header-top").css("top", "-70px");

		} else {
			// upscroll code
			$(".header-top").css("top", "0px");
		}
		lastScrollTop = st;
	});
});

function ageRestricted(action) {
	if (action == 'Yes') {
		closeModal('age');
		deleteCookie('user_age_consent');
		setCookie('user_age_consent', 1, 365);
	} else {
		window.location.href = 'https://google.com';
	}
}

function validateEmail(email) {
	var re = /\S+@\S+\.\S+/;
	return re.test(email);
}

function selectDocument(type, name) {
	$('#documents-selection').hide();
	$('#documents-upload').show();
	$('#selectedDocumentName').text(name);
	if (type == 'back') {
		$('#documents-selection').show();
		$('#documents-upload').hide();
	}
}

var documentId = '';
function uploadDocument(id) {
	upType = 25;
	documentId = id;
	document.getElementById("uploadContent").click();
}

function createDocumentPreview(file, fileContents, id) {
	var $previewElement = '';
	switch (file.type) {
		case 'image/png':
		case 'image/jpeg':
		case 'image/gif':
			$(id + '-text').text('uploading...');
			$(id).removeClass('tat--with-image-complete');
			$(id).addClass('tat--with-image');
			$(id).css('background-image', 'url(' + fileContents + ')');
			break;
		case 'video/mp4':
		case 'video/webm':
		case 'video/ogg':
			break;
		default:
			break;
	}

}

var becomeCreator = {};
becomeCreator['action'] = 'becomeCreator';
becomeCreator['document-front-side'] = '';
becomeCreator['document-back-side'] = '';
becomeCreator['document-selfie'] = '';
becomeCreator['uid'] = user_info.id;

function updateBecomeCreatorData(col, val) {

}

function submitBecomeCreatorRequest() {
	$('[data-become-creator]').each(function (e) {
		var col = $(this).attr('data-become-creator');
		var val = $(this).val();
		var required = $(this).attr('data-required');
		if (val == '' || val == undefined) {
			if (required == 1) {
				sendToast('Please fill all the information');
				return false;
			}
		}

		if (becomeCreator['document-front-side'] == '' || becomeCreator['document-back-side'] == '' || becomeCreator['document-selfie'] == '') {
			sendToast('Please upload your documents');
			return false;
		}

		becomeCreator[col] = val;
	});

	$.getJSON(aUrl, becomeCreator, function (data) {
		if (data.result == 'ERROR') {
			sendToast(data.reason);
		} else {
			$('#notificationModalHeader').text('Become creator request sent succesfully');
			$('#notificationModalText').text('Thank you ' + user_info.username + ' for sending us your information, our verification team will review your information shortly');
			openModal('notification');
		}
	});
}