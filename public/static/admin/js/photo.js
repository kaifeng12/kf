$().ready(function(e) {
	//上传照片
	$('.edit .button:eq(0)').bind('click',function(){

		$('.box').toggle();
	});
	
	
	if(json[0]=='0') return;
	changPhoto(json);
	
	var page=$('.thumbs').attr('name');

			
	//设置ajax，换一批图片数据
	
	//上一页功能
	$('.thumbs img:eq(0)').bind('click',function(){
		var data={'page':page,'act':'prev','group':group};		
		$.get(controller+'photolist',data,function(msg){
			if(msg!=='0'){
				changPhoto(msg);
				$('.thumbs').attr('name',page--);
			}
		},'json');
	});
	
	$('.thumbs img:eq(1)').bind('click',function(){
		var data={'page':page,'act':'next','group':group};		
		$.get(controller+'photolist',data,function(msg){	
			if(msg!=='0'){
				changPhoto(msg);
				$('.thumbs').attr('name',page++);			//当前页数加1
			}
		},'json');
	});



	//删除
	$('.edit .button:eq(1)').bind('click',function(){
		
		$('.delete').toggle();
	});

//<input type="checkbox" value="1" name="p_id[]">
			
});


//获得图片json数据后依次加到对应地方
function changPhoto(json){
	$('#im').attr('src',json[0].p_path);			//显示第一张图片
	
	//先清除图片地址和清楚绑定的事件
	$('.thumb').each(function(i){
        $(this).css('background-image','');
		$(this).unbind();
    });
	
	//设置删除图片按钮数据
	if($(':checkbox').length>0){
		$(':checkbox').remove();	
	}
	//再添加地址和事件，实现刷新
	$.each(json,function(i,t) {
        $('.thumb:eq('+i+')').css('background-image','url('+t.p_thumb+')');
		$('.thumb:eq('+i+')').bind('click',function(){
			$('#im').attr('src',t.p_path);
		});
		
		//设置删除图片按钮数据
		$('#sub').before('<input type="checkbox" value="'+t.p_id+'" name="p_id[]">')
		
    });
	
	

	
		
}
