$().ready(function(e) {

	if(json[0]=='0') return;
	changPhoto(json);
	
	var page=$('.thumbs').attr('name');

			
	//设置ajax，换一批图片数据
	
	//上一页功能
	$('.thumbs img:eq(0)').bind('click',function(){
		var data={'page':page,'act':'prev','group':group};		
		$.get(action,data,function(msg){
			if(msg!=='0'){
				changPhoto(msg);
				$('.thumbs').attr('name',page--);
			}
		},'json');
	});
	
	$('.thumbs img:eq(1)').bind('click',function(){
		var data={'page':page,'act':'next','group':group};		
		$.get(action,data,function(msg){	
			if(msg!=='0'){
				changPhoto(msg);
				$('.thumbs').attr('name',page++);			//当前页数加1
			}
		},'json');
	});


			
});


//获得图片json数据后依次加到对应地方
function changPhoto(json){
	$('#im').attr('src',json[0].path);			//显示第一张图片
	
	//先清除图片地址和清楚绑定的事件
	$('.thumb').each(function(i){
        $(this).css('background-image','');
		$(this).unbind();
    });
	

	//再添加地址和事件，实现刷新
	$.each(json,function(i,t) {
        $('.thumb:eq('+i+')').css('background-image','url('+t.thumb+')');
		$('.thumb:eq('+i+')').bind('click',function(){
			$('#im').attr('src',t.path);
		});
		
		
    });
	
	

	
		
}
