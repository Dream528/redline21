var aiarUrl = request_source()+'/aiautoresponder.php';
var ai_r_i = plugins['aiautoresponder']['responder_interval'].split("-");
var autores_i1 = ai_r_i[0]*1000;
var autores_i2 = ai_r_i[1]*1000;

function sendAutoResp(s,r,action='respond') {
    var rnd = Math.floor(Math.random() * (autores_i2 - autores_i1) + autores_i1);
    setTimeout(function() {
        $.getJSON(aiarUrl, { action: action, uid1: s, uid2: r }, function(data) {
            if (data && typeof data === 'object' && data.send) {
                if (data.send === 'YES') {
					var message = s+'[message]'+r+'[message]'+data.msg+'[message]text';
					var send = s+'[rt]'+r+'[rt]'+data.photo+'[rt]'+data.name+'[rt]'+data.msg+'[rt]text';
					$.get( gUrl, {action: 'message', query: send} );		
					$.get( aUrl, {action: 'sendMessage', query: message} );  

					setTimeout(function(){
						if(currentProfileMedia.length > 0){
							if(action == 'follow' || action == 'visit'){
								var mediaType = 'gallery';
								var sendThisMedia = '';		
								shuffle(currentProfileMedia);	
								var maxGallery = 0;
								if (Math.random() >= 0.5){
									mediaType = 'image';
								}

								currentProfileMedia.forEach(function(media,index) {
									if(media.private > 0){
										if(maxGallery < 4 && mediaType == 'gallery'){
											sendThisMedia+= media.media+'[image]';
											maxGallery++;	
										}
										if(mediaType == 'image'){
											sendThisMedia = media.media;
										}
									}														
									if(media.video == 1 && media.private > 0){
										mediaType = 'video';
										sendThisMedia = media.media;
									}							
								});

								if(sendThisMedia.slice(-1) == ']'){
									sendThisMedia = sendThisMedia.slice(0, -7);
								}
								var send = s+'[rt]'+r+'[rt]'+data.photo+'[rt]'+data.name+'[rt]'+sendThisMedia+'[rt]'+mediaType;
								$.get( gUrl, {action: 'message', query: send} );									
								var freeMedia = s+'[message]'+r+'[message]'+sendThisMedia+'[message]'+mediaType;
								$.get( aUrl, {action: 'sendMessage', query: freeMedia} );
								setTimeout(function(){
									reloadUserConversations(user_info.id);
									sendAutoResp(s,user_info.id,'giftFromModel');
								},250);														
							}
						}

						if(action == 'giftFromModel'){
							setTimeout(function(){
								reloadUserConversations(user_info.id);
							},250);									
						}

					},500);
                } else if (data.send === 'NO' && data.error) {
                }
            }
        }).fail(function(jqXHR, textStatus, errorThrown) {
        });
    }, rnd);
}


function shuffle(array) {
  let currentIndex = array.length,  randomIndex;

  // While there remain elements to shuffle.
  while (currentIndex > 0) {

    // Pick a remaining element.
    randomIndex = Math.floor(Math.random() * currentIndex);
    currentIndex--;

    // And swap it with the current element.
    [array[currentIndex], array[randomIndex]] = [
      array[randomIndex], array[currentIndex]];
  }

  return array;
}