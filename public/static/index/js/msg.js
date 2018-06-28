$().ready(function(e) {
	$('.hf').bind('click',function(){
		var hui=$(this).parent().next().next();
		if(hui.css('display')=='none'){
			$('.hf').html('回复');
			$('.hui').css('display','none');
			hui.css('display','block');						
			$(this).html('取消回复');	
		}else if(hui.css('display')!=='none'){
			hui.css('display','none');						
			$(this).html('回复');	
		}
	});

	
	$('.huifu').bind('click',function(){
		if(!$('.sma_yuan').html()){
			alert("请先登录");
			return false;
		}
		var val=$(this).prev().children().eq(0).children().eq(0).val();	//获得用户在textarea里输入的值
		
		if(val==''){
			return false;	
		}
		$(this).prev()[0].submit();
	});
	
	//发表留言
	$('.liuyan').bind('click',function(){
		if(!$('.sma_yuan').html()){
			alert("请先登录");
			return false;
			//exit;
		}
		var val=$(this).prev().children().eq(0).children().eq(0).val();	//获得用户在textarea里输入的值
		
		if(val==''){
			return false;	
		}
		$(this).prev()[0].submit();
	});


	$('.pinglun').bind('click',function(){
		
		if(!$('.sma_yuan').html()){
			alert("请先登录");
			return false;
			//exit;
		}
		var val=$(this).prev().children().eq(0).children().eq(0).val();	//获得用户在textarea里输入的值
		
		if(val==''){
			return false;	
		}
		$(this).prev()[0].submit();
	});
	
	$('.logout').bind('click',function(){
		var url=$('.logout').attr('data-url');
		var data='';
		var self=$(this);
		var htm='<img src="/static/index/images/qlogin.png" style="border-radius:50%; width:40px; height:40px; background-size:cover;" ><a href="https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101450092&redirect_uri=http://www.likaifeng.xyz/Home/Msg/callback?state=12{$log.id}"><div style="padding-top:20px; display:inline-block">用qq登录评论</div></a>';		
		$.get(url,data,function(msg){
			if(msg=='1'){
				self.after(htm);
				self.prev().remove();
				self.remove();
			}
		});
	});

});