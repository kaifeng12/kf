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
		// var index=$.msg.loading();
		// $.post(controller+'/delete',{id:mid},function(res){
		// 	if(msg=='1'){
		// 		$.msg.success('删除成功',1,function(){
		// 			self.parent().parent().remove();
		// 		})	
		// 	}else{
		// 		$.msg.error('删除失败',2);
		// 	}
		// });


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
	


});