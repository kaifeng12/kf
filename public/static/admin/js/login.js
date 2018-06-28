$().ready(function(e) {
    $(':button').bind('click',function(){
		if($(':text:eq(1)').val()==''){
			alert('请输入验证码');
			return false;
		}
		
		//用户名或密码为空显示提示信息
		if($(':text:eq(0)').val()== ''){
			$('.umsg').css('display','block');
			return false;	
		}
		
		if($(':password').val()== ''){
			$('.pmsg').css('display','block');	
			return false;
		}
		
		//用户验证
		var name=$(':text:eq(0)').val();
		var pwd=$(':password').val();
		var capt=$(':text:eq(1)').val();
		var rem=$(':checkbox').val();
		var data={
			name: name,
			pwd : pwd,
			capt: capt,
			rem : rem
		}
		$.post(controller+'/checklogin',data,function(msg){
			if(msg=='-1'){
				alert('验证码错误');
				return false;
			}else if(msg=='0'){
				alert('用户名或密码错误！');
			}else if(msg.length=='1'){
				location.href=module+'/admin/home';	
			}
			
		});
		
		
	});
	
	//用户重新开始输入时，提示信息隐藏
	$(':text').bind('focus',function(){
		$('.umsg').css('display','none');
	});
	
	$(':password').bind('focus',function(){
		$('.pmsg').css('display','none');
	});

	

});