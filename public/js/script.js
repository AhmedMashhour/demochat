function user_status(class1,class2){
    var stat=['online','offline','dnd','bys'];
    $.each(stat,function (k,v) {
        $('.'+class1).removeClass(v);
    });
    $('.'+class1).addClass(class2);
}

$(document).ready(function(){
    var my_list=[];
    $('.user').each(function () {
        var uid=$(this).attr('uid');
       my_list.push(uid);
    });
    var my_status=$('.select_status').val();
     var socket=io.connect('http://localhost:5550',
         {query:'user_id='+user_id+'&username='+username+'&my_list='+my_list.join(',')+'&status='+my_status}
         );
        var array_emit=['is_online','iam_online','new_status','iam_offline'];
        $.each(array_emit,function (k,v) {
            socket.on(v,function (data) {
                user_status(data.user_id,data.status);
            });
        });
        socket.on('request_status',function (data) {
            socket.emit('response_status',{
                to_user:data.user_id,
                my_status:$('.select_status').val()
            });
        });

     socket.on('connect',function (data) {
		$('.user').each(function () {
			var uid=$(this).attr('uid');
			socket.emit('check_online',{
				user_id:'user_'+uid
			});
        });
     });
     $(document).on('change','.select_status',function () {
        var stat=$('.select_status').val();
        socket.emit('change_status',{
           status: stat
        });
     });
	 var arr = []; // List of users
	
	$(document).on('click', '.msg_head', function() {	
		var chatbox = $(this).parents().attr("rel") ;
		$('[rel="'+chatbox+'"] .msg_wrap').slideToggle('slow');
		return false;
	});
	
	
	$(document).on('click', '.close', function() {	
		var chatbox = $(this).parents().parents().attr("rel") ;
		$('[rel="'+chatbox+'"]').hide();
		arr.splice($.inArray(chatbox, arr), 1);
		displayChatBox();
		return false;
	});

    $(document).on('change', '.file', function(evt) {


        evt.preventDefault();

        var file=new FormData();

        var read= $(this).parents().parents().parents().parents().attr("rel") ;
        var file_data=$('.file').prop('files')[0];
        var receiver=read.substring(5);
        file.append('sender',user_id);
        file.append('receiver',receiver);
        file.append('message',file_data);
        file.append('type','file');
        $.ajaxSetup({headers:{'X-CSRF-Token':tok}});
        $.ajax(
            {
                url:ur+'/send_message',
                type:'post',
                dataType:'json',
                async:false,
                cache:false,
                contentType:false,
                enctype:'multipart/form-data',
                processData:false,
                data:file,
                success:function (data) {
                    alert(data)
                }

            }
        );

        socket.emit('send_private_msg',{
            message:'image',
            to:read,
            type:'file'

        });
    });

	function private_chatbox(username,userID)
    {
        if ($.inArray(userID, arr) != -1)
        {
            arr.splice($.inArray(userID, arr), 1);
        }
        arr.unshift(userID);
            var ms='';


        chatPopup =  '<div class="msg_box box'+userID+'" style="right:270px" rel="'+ userID+'">'+
            '<div class="msg_head">'+username +
            '<div class="close">x</div> </div>'+
            '<div class="msg_wrap"> <div class="msg_body">	<div class="msg_push"></div> </div>'+
            '<div class="msg_footer"><span class="broadcast"></span>'+
            '<form enctype="multipart/form-data" action="'+ur+'/send_message" method="post" id="user_form"><input type="file" ' +//'onchange="this.form.submit()"'+
            'class="file btn btn-success"  style="width: 20px;height: 10px;overflow: hidden;font-size: 0px" name="message">' +
            //'<input type="hidden" name="_token" value="'+tok+'">'+'<input type="hidden" name="type" value="file">'+
            '</form>'+
            '<textarea class="msg_input" rows="4"></textarea></div> 	</div> 	</div>' ;
        if (!$('.msg_box').hasClass('box'+userID)) {
            $("body").append(chatPopup);

        }

        displayChatBox();
        var xx=0;
        var receiver=userID.substring(5);
        $.ajax(
            {
                url:ur+'/get_message',
                type:'post',
                dataType:'json',
                data:{_token:tok,user_id:receiver,me:user_id},
                success:function (data) {


                    for (var i=0;i<data['iss'].length;i++) {
                        if (data['iss'][i][4] == 'text') {
                            if (data['iss'][i][3] == 'yes') {
                                var textClass = 'msg-right';
                                var theName = myname;
                            } else {
                                var textClass = 'msg-left';
                                var theName = username;
                            }
                            $('<div class="' + textClass + '">' + theName + ':' + data['iss'][i][1] + '</div>').insertBefore('[rel="' + userID + '"] .msg_push');
                            $('.msg_body').scrollTop($('.msg_body')[0].scrollHeight);
                        }
                        else if (data['iss'][i][4] == 'image')
                        {
                            if (data['iss'][i][3] == 'yes') {
                                var textClass = 'msg-right';
                                var theName = myname;
                            } else {
                                var textClass = 'msg-left';
                                var theName = username;
                            }
                            $('<div class="' + textClass + ' i'+xx+'">' + theName + ':</div>').insertBefore('[rel="' + userID + '"] .msg_push');

                            $.ajax(
                                {
                                    url:ur+'/return_image',
                                    type:'post',
                                    dataType:'json',
                                    data:{_token:tok,ur:data['iss'][i][1],index:xx},
                                    success:function (data_image) {
                                    $('.i'+data_image['index']).append(data_image['io']);
                                    }
                                }
                            );
                            xx+=1;
                            $('.msg_body').scrollTop($('.msg_body')[0].scrollHeight);
                        }

                    }
                }

            }
        );
    }

	$(document).on('click', '#sidebar-user-box', function() {
	 var userID = $(this).attr("uid");
	 var username = $(this).children().text() ;
        private_chatbox(username,"user_"+userID);
	});
	socket.on('new_private_msg',function (data) {
            if (!$('.msg_box').hasClass('box'+data.from_uid)) {
                private_chatbox(data.username, data.from_uid);
            }
            xx=0;
        $('.box'+data.from_uid+' .broadcast').html('');
            if (data.type=='text') {
                if (data.whois == 'user_' + user_id) {
                    var textClass = 'msg-right';
                } else {
                    var textClass = 'msg-left';
                }
                $('<div class="' + textClass + '">' + data.username + ':' + data.message + '</div>').insertBefore('[rel="' + data.from_uid + '"] .msg_push');
                $('.msg_body').scrollTop($('.msg_body')[0].scrollHeight);
            }else if (data.type=='file')
            {
                if (data.whois == 'user_' + user_id) {
                    var textClass = 'msg-right';
                    var theName = myname;
                } else {
                    var textClass = 'msg-left';
                    var theName = username;
                }
                $('<div class="' + textClass + ' i'+xx+'">' + theName + ':</div>').insertBefore('[rel="' + data.from_uid + '"] .msg_push');
                $.ajax(
                    {
                        url:ur+'/return_image',
                        type:'post',
                        dataType:'json',
                        data:{_token:tok,ur:data['iss'][i][1]},
                        success:function (imge) {
                            $('.i'+imge['index']).append(imge['io']);

                        }
                    }
                );
                xx+=1;
                $('.msg_body').scrollTop($('.msg_body')[0].scrollHeight);

            }
    });
    $(document).on('click', '.msg_wrap' , function(e) {
        var read=$(this).parents().attr("rel");
        var receiver=read.substring(5);
        $.ajax(
            {
                url:ur+'/readed',
                type:'post',
                dataType:'json',
                data:{_token:tok,sender:user_id,receiver:receiver},
                success:function (data) {

                }

            }
        );
    });

	$(document).on('keypress', 'textarea' , function(e) {
        var chatbox = $(this).parents().parents().parents().attr("rel") ;
        var receiver=chatbox.substring(5);
        if (e.keyCode == 13 ) { 		
            var msg = $(this).val();		
			$(this).val('');
			if(msg.trim().length != 0){
                $.ajax(
                    {
                        url:check_tables,
                        type:'post',
                        dataType:'json',
                        data:{_token:tok,sender:user_id,receiver:receiver,type:'text'},
                        success:function (data) {
                       if (data['is']=='not found')
                       {
                           $.ajax(
                               {
                            url:add_tables,
                           type:'post',
                           dataType:'json',
                            data:{_token:tok,sender:user_id,receiver:receiver},
                           success:function (data) {
                               $.ajax(
                                   {
                                       url:send_message,
                                       type:'post',
                                       dataType:'json',
                                       data:{_token:tok,sender:user_id,receiver:receiver,message:msg,type:'text'},
                                       success:function (data) {
                                           console.log(data);
                                       }
                                   }
                               );
                           }
                       }
                       );
                       }else {

                           $.ajax(
                               {
                                   url:send_message,
                                   type:'post',
                                   dataType:'json',
                                   data:{_token:tok,sender:user_id,receiver:receiver,message:msg,type:'text'},
                                   success:function (data) {
                                   }
                               }
                           );

                       }
                }
            }
            );
			socket.emit('send_private_msg',{
			    message:msg,
                to:chatbox,
                type:'text'

			});
			//$('<div class="msg-right">'+username+':'+msg+'</div>').insertBefore('[rel="'+chatbox+'"] .msg_push');
			//$('.msg_body').scrollTop($('.msg_body')[0].scrollHeight);
			}
        }else{
            socket.emit('broadcast_private',{
                to:chatbox,
                username:username
            });
        }
    });

	socket.on('new_broadcast',function (data) {
       $('.box'+data.from+' .broadcast').html('<span style="font-size: 10px;float: left">'+data.username+'</span><img src="'+typing+'"/>');
        setTimeout(function () {
            $('.box'+data.from+' .broadcast').html('');
        },5000);
    });
		
    
	function displayChatBox(){ 
	    i = 270 ; // start position
		j = 260;  //next position
		
		$.each( arr, function( index, value ) {  
		   if(index < 4){
	         $('[rel="'+value+'"]').css("right",i);
			 $('[rel="'+value+'"]').show();
		     i = i+j;			 
		   }
		   else{
			 $('[rel="'+value+'"]').hide();
		   }
        });		
	}
	
	
	
	
});