
$(function() {
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
		//var val=$(this).prev().children().eq(0).val();		//获得用户在textarea里输入的值
		$(this).prev()[0].submit();
	});
	
	$('a:contains(删除)').bind('click',function(){
		if(window.confirm('确定删除？')){
			$.post(controller+'/delete',{m_id:this.id},function(msg){
				if(msg=='1'){
					
					window.location.reload();
				}else{
					alert('删除失败');
					window.location.reload();	
				}
			});
				
		}else return false;
		
	});
});
