$().ready(function(e) {
    $('article').bind('click',function(){
		var id=this.id;
		top.location.href=controller+'/read/id/'+id;
	});
});