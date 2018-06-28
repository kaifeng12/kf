$().ready(function(e) {
	$('.log').bind('click',function(){
		location.href=module+'Log/logedit/id/'+this.id;
	});
	$('.dropdown:eq(1)').toggle(function(){
		$('.log').css({'margin':'0px','margin-right':'8px'});
		$(':checkbox').css('display','inline');
		$('button').css('display','inline');
		$(this).html('取消删除');
	},function(){
		$('.log').css({'margin':'11px','margin-right':'0px'});
		$(':checkbox').css('display','none');
		$('button').css('display','none');
		$(this).html('删除文章');	
	});
});




