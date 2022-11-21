function initSecondClass() {
	if( init_secondclass ) {
		$("#secondclass").val( secondclass ) ;
		init_secondclass = false ;
	}
}

$("#firstclass").change(function (obj) {
		var params = {};
		params.firstclass = $("#firstclass").val();
		console.log( "params.firstclass = " + params.firstclass );
		$.post("/admin/manage/get_category_info", params , function(data){
        data = $.parseJSON(data);
        if( data.result ) {
        	// 设置二级分类
        	if( data.obj [1] ) {
        		$("#secondclass").html("");
        		$.each( data.obj[1],function(index, item) {
        			$("#secondclass").append("<option value=" + item.id + ">" + item.title + "</option>") ;
        		});
				initSecondClass();
        	}
        } else {
          bootbox.alert(data.message,function() {});
        }
  });
});