function revertManage( block ) {
	$(".manage-item").addClass('hidden');
	$('.' + block).removeClass('hidden');
}

function resetTable(href) {
  $("#btn-new").removeClass("hidden");
  $("#btn-new a").attr('href', href);
  $("#manage_list").bootstrapTable('destroy');
  $("#manage_list").html('');

  $(".home_content").addClass('hidden');
  $(".setting_content").addClass('hidden');
  $("#content").addClass("col-md-10");
  $("#home-setting").addClass('hidden');
}

function resetContent( content ) {
  $("#btn-new").addClass('hidden');
  $("#manage_list").bootstrapTable('destroy');
  $("#manage_list").html('');

  $("#home_setting").removeClass('hidden').addClass('col-md-10');
  $("#content").removeClass("col-md-10");

  $(".home_content").addClass('hidden');
  $(".intro_content").addClass('hidden');
  $(".setting_content").addClass('hidden');
  $(".category_content").addClass('hidden');

  $("." + content).removeClass('hidden');
}

var maxMobileBannerId = 0;
var maxWebBannerId = 0;
function revertIndex() {
  resetContent("home_content") ;
  
  $.post("/admin/manage/home", {} ,
      function(data){
        data = $.parseJSON(data);
        if( data.result ) {
          var dict = data.obj ;
          $.each(dict , function ( idx , item) {
            if(item ['name'] == 'banner_main_title') {
              $("input[name='banner_main_title']").val(item ['value']);
            }
            if(item ['name'] == 'banner_sub_title') {
              $("input[name='banner_sub_title']").val(item ['value']);
            }
            var arr = item ['name'].split('__');
            if( arr [0] == 'banner_web_img' ) {
              maxWebBannerId = maxWebBannerId > parseInt(arr[1]) ? maxWebBannerId : parseInt(arr [1]);
              addBannerImg('web', maxWebBannerId, item ['value'], item ['url']);
            }
            if( arr [0] == 'banner_mobile_img' ) { 
              maxMobileBannerId = maxMobileBannerId > parseInt(arr[1]) ? maxMobileBannerId : parseInt(arr [1]);
              addBannerImg('mobile', maxMobileBannerId, item ['value'], item ['url']);
            }
            if(item['name'] == 'st_practice' ) {
              $("input[name='st_practice']").val(item ['value']);
            }
            if(item['name'] == 'st_market' ) {
              $("input[name='st_market']").val(item ['value']);
            }
            if(item['name'] == 'st_sale' ) {
              $("input[name='st_sale']").val(item ['value']);
            }
            if(item['name'] == 'st_think' ) {
              $("input[name='st_think']").val(item ['value']);
            }
          });
        } else {
          console.log( "加载失败" );
        }
  });
}

function revertProduct() {
resetTable('/admin/manage/edit_product/id/0');
$("#manage_list").bootstrapTable({ // 对应table标签的id
      url: "/admin/manage/product", // 获取表格数据的url
      cache: false, // 设置为 false 禁用 AJAX 数据缓存， 默认为true
      striped: true,  //表格显示条纹，默认为false
      pagination: true, // 在表格底部显示分页组件，默认false
      pageList: [10, 20], // 设置页面可以显示的数据条数
      pageSize: 10, // 页面数据条数
      pageNumber: 1, // 首页页码
      sidePagination: 'server', // 设置为服务器端分页
      queryParams: function (params) { // 请求服务器数据时发送的参数，可以在这里添加额外的查询参数，返回false则终止请求
     	 // Add params
     	 return {
              pageSize: params.limit, // 每页要显示的数据条数
              offset: params.offset, // 每页显示数据的开始行号
              sort: params.sort, // 要排序的字段
              sortOrder: params.order, // 排序规则
          }
      },
      sortName: 'id', // 要排序的字段
      sortOrder: 'desc', // 排序规则
      columns: [
          {
              field: 'id', // 返回json数据中的name
              title: 'ID', // 表格表头显示文字
              align: 'center', // 左右居中
              valign: 'middle' // 上下居中
          }, {
              field: 'title',
              title: '名称',
              align: 'left',
              valign: 'middle'
          }, {
              field: 'category_title',
              title: '分类',
              align: 'left',
              valign: 'middle'
          }, {
               title: "操作",
              align: 'center',
              valign: 'middle',
              width: 160, // 定义列的宽度，单位为像素px
              formatter: function (value, row, index) {
                  return '<a href="/admin/manage/edit_product/id/'+row.id+'" class="btn btn-default btn-lg" role="button">编辑</a>';
              }
          }
      ],
      onLoadSuccess: function(data){  //加载成功时执行
            console.info("加载成功");
            console.log(data);
      },
      onLoadError: function(){  //加载失败时执行
            console.info("加载数据失败");
      }

}) ;
}


function revertProject() {
resetTable('/admin/manage/edit_project/id/0');
$("#manage_list").bootstrapTable({ // 对应table标签的id
      url: "/admin/manage/project", // 获取表格数据的url
      cache: false, // 设置为 false 禁用 AJAX 数据缓存， 默认为true
      striped: true,  //表格显示条纹，默认为false
      pagination: true, // 在表格底部显示分页组件，默认false
      pageList: [10, 20], // 设置页面可以显示的数据条数
      pageSize: 10, // 页面数据条数
      pageNumber: 1, // 首页页码
      sidePagination: 'server', // 设置为服务器端分页
      queryParams: function (params) { // 请求服务器数据时发送的参数，可以在这里添加额外的查询参数，返回false则终止请求
       // Add params
       return {
              pageSize: params.limit, // 每页要显示的数据条数
              offset: params.offset, // 每页显示数据的开始行号
              sort: params.sort, // 要排序的字段
              sortOrder: params.order, // 排序规则
          }
      },
      sortName: 'id', // 要排序的字段
      sortOrder: 'desc', // 排序规则
      columns: [
          {
              field: 'id', // 返回json数据中的name
              title: 'ID', // 表格表头显示文字
              align: 'center', // 左右居中
              valign: 'middle' // 上下居中
          }, {
              field: 'title',
              title: '名称',
              align: 'left',
              valign: 'middle'
          }, {
              field: 'category_title',
              title: '分类',
              align: 'left',
              valign: 'middle'
          }, {
               title: "操作",
              align: 'center',
              valign: 'middle',
              width: 160, // 定义列的宽度，单位为像素px
              formatter: function (value, row, index) {
                  return '<a href="/admin/manage/edit_project/id/'+row.id+'" class="btn btn-default btn-lg" role="button">编辑</a>';
              }
          }
      ],
      onLoadSuccess: function(data){  //加载成功时执行
            console.info("加载成功");
            console.log(data);
      },
      onLoadError: function(){  //加载失败时执行
            console.info("加载数据失败");
      }

}) ;
}


function revertNews() {
resetTable('/admin/manage/edit_news/id/0');
$("#manage_list").bootstrapTable({ // 对应table标签的id
      url: "/admin/manage/news", // 获取表格数据的url
      cache: false, // 设置为 false 禁用 AJAX 数据缓存， 默认为true
      striped: true,  //表格显示条纹，默认为false
      pagination: true, // 在表格底部显示分页组件，默认false
      pageList: [10, 20], // 设置页面可以显示的数据条数
      pageSize: 10, // 页面数据条数
      pageNumber: 1, // 首页页码
      sidePagination: 'server', // 设置为服务器端分页
      queryParams: function (params) { // 请求服务器数据时发送的参数，可以在这里添加额外的查询参数，返回false则终止请求
       // Add params
       return {
              pageSize: params.limit, // 每页要显示的数据条数
              offset: params.offset, // 每页显示数据的开始行号
              sort: params.sort, // 要排序的字段
              sortOrder: params.order, // 排序规则
          }
      },
      sortName: 'id', // 要排序的字段
      sortOrder: 'desc', // 排序规则
      columns: [
          {
              field: 'id', // 返回json数据中的name
              title: 'ID', // 表格表头显示文字
              align: 'center', // 左右居中
              valign: 'middle' // 上下居中
          }, {
              field: 'title',
              title: '名称',
              align: 'left',
              valign: 'middle'
          }, {
              field: 'category_title',
              title: '分类',
              align: 'left',
              valign: 'middle'
          }, {
               title: "操作",
              align: 'center',
              valign: 'middle',
              width: 160, // 定义列的宽度，单位为像素px
              formatter: function (value, row, index) {
                  return '<a href="/admin/manage/edit_news/id/'+row.id+'" class="btn btn-default btn-lg" role="button">编辑</a>';
              }
          }
      ],
      onLoadSuccess: function(data){  //加载成功时执行
            console.info("加载成功");
            console.log(data);
      },
      onLoadError: function(){  //加载失败时执行
            console.info("加载数据失败");
      }

}) ;
}


function revertCooperate() {
resetTable('/admin/manage/edit_cooperate/id/0');
$("#manage_list").bootstrapTable({ // 对应table标签的id
      url: "/admin/manage/cooperate", // 获取表格数据的url
      cache: false, // 设置为 false 禁用 AJAX 数据缓存， 默认为true
      striped: true,  //表格显示条纹，默认为false
      pagination: true, // 在表格底部显示分页组件，默认false
      pageList: [10, 20], // 设置页面可以显示的数据条数
      pageSize: 10, // 页面数据条数
      pageNumber: 1, // 首页页码
      sidePagination: 'server', // 设置为服务器端分页
      queryParams: function (params) { // 请求服务器数据时发送的参数，可以在这里添加额外的查询参数，返回false则终止请求
       // Add params
       return {
              pageSize: params.limit, // 每页要显示的数据条数
              offset: params.offset, // 每页显示数据的开始行号
              sort: params.sort, // 要排序的字段
              sortOrder: params.order, // 排序规则
          }
      },
      sortName: 'id', // 要排序的字段
      sortOrder: 'desc', // 排序规则
      columns: [
          {
              field: 'id', // 返回json数据中的name
              title: 'ID', // 表格表头显示文字
              align: 'center', // 左右居中
              valign: 'middle' // 上下居中
          }, {
              field: 'title',
              title: '名称',
              align: 'left',
              valign: 'middle'
          }, {
               title: "操作",
              align: 'center',
              valign: 'middle',
              width: 160, // 定义列的宽度，单位为像素px
              formatter: function (value, row, index) {
                  return '<a href="/admin/manage/edit_cooperate/id/'+row.id+'" class="btn btn-default btn-lg" role="button">编辑</a>';
              }
          }
      ],
      onLoadSuccess: function(data){  //加载成功时执行
            console.info("加载成功");
            console.log(data);
      },
      onLoadError: function(){  //加载失败时执行
            console.info("加载数据失败");
      }

}) ;
}


$(document).ready(function() {
    $('#short_desc').trumbowyg();
    $('#description').trumbowyg();
    $('#description_en').trumbowyg();
    $('#main_body').trumbowyg();
});


function revertCategory() {
  resetContent( "category_content" ) ;
  $.post("/admin/manage/get_category", {} , function(data){
        data = $.parseJSON(data);
        if( data.result ) {
          $("#firstclass").html("") ;
          $("#firstclass").append("<option value=-1>增加一级分类</option>");
          $.each(data.obj [0], function(index, e){
             $("#firstclass").append("<option value='" + e.id + "'> " + e.title + "</option>") ;
          });

          $("#secondclass").html("") ;
          $("#secondclass").append("<option value=-1>增加二级分类</option>") ;
          $("#secondclass").append("<option value=0>修改一级分类</option>") ;
          $.each(data.obj [1], function(index, e){
             $("#secondclass").append("<option value='" + e.id + "'> " + e.title + "</option>") ;
          });
        }
  });
}

function revertNews() {
resetTable('/admin/manage/edit_news/id/0');
$("#manage_list").bootstrapTable({ // 对应table标签的id
      url: "/admin/manage/news", // 获取表格数据的url
      cache: false, // 设置为 false 禁用 AJAX 数据缓存， 默认为true
      striped: true,  //表格显示条纹，默认为false
      pagination: true, // 在表格底部显示分页组件，默认false
      pageList: [10, 20], // 设置页面可以显示的数据条数
      pageSize: 10, // 页面数据条数
      pageNumber: 1, // 首页页码
      sidePagination: 'server', // 设置为服务器端分页
      queryParams: function (params) { // 请求服务器数据时发送的参数，可以在这里添加额外的查询参数，返回false则终止请求
       // Add params
       return {
              pageSize: params.limit, // 每页要显示的数据条数
              offset: params.offset, // 每页显示数据的开始行号
              sort: params.sort, // 要排序的字段
              sortOrder: params.order, // 排序规则
          }
      },
      sortName: 'id', // 要排序的字段
      sortOrder: 'desc', // 排序规则
      columns: [
          {
              field: 'id', // 返回json数据中的name
              title: 'ID', // 表格表头显示文字
              align: 'center', // 左右居中
              valign: 'middle' // 上下居中
          }, {
              field: 'title',
              title: '名称',
              align: 'left',
              valign: 'middle'
          }, {
               title: "操作",
              align: 'center',
              valign: 'middle',
              width: 160, // 定义列的宽度，单位为像素px
              formatter: function (value, row, index) {
                  return '<a href="/admin/manage/edit_news/id/'+row.id+'" class="btn btn-default btn-lg" role="button">编辑</a>';
              }
          }
      ],
      onLoadSuccess: function(){  //加载成功时执行
            console.info("加载成功");
      },
      onLoadError: function(){  //加载失败时执行
            console.info("加载数据失败");
      }

}) ;
}

/**
 * 加载基本配置信息
 */
function revertSetting() {
  resetContent( "setting_content" ) ;
  $.post("/admin/manage/setting", {} , function(data){
        data = $.parseJSON(data);
        if( data.result ) {
          $("img[name='logo']").attr("src",data.obj.logo);
          $("input[name='logo']").val(data.obj.logo);
          $("img[name='qcode']").attr("src",data.obj.qcode);
          $("input[name='qcode']").val(data.obj.qcode);
          $("input[name='name']").val(data.obj.name);
          $("input[name='name_en']").val(data.obj.name_en);
          $("input[name='zipcode']").val(data.obj.zipcode);
          $("input[name='phone']").val(data.obj.phone);
          $("input[name='tel']").val(data.obj.tel);

          $("input[name='address']").val(data.obj.address);
          $("input[name='address_en']").val(data.obj.address_en);
          $("input[name='fax']").val(data.obj.fax);
          $("input[name='web']").val(data.obj.web);
          $("input[name='email']").val(data.obj.email);

        } else {
          console.log( "加载失败" );
        }
  });
}

/**
 * 加载公司简介
 */
function revertIntro() {
  resetContent( "intro_content" ) ;
  $.post("/admin/manage/intro", {} , function(data){
        data = $.parseJSON(data);
        if( data.result ) {
          $("#intro-select").html("") ;
          $.each(data.obj, function(index, e){
             $("#intro-select").append("<option value='" + e.name + "'> " + e.title + "</option>") ;
          });
          console.log( data.obj [1] );
          $("#description").html(data.obj[1].description) ;
          $("#description_en").html(data.obj[1].description_en) ;
        } else {
          console.log( "加载失败" );
        }
  });
}


function revertFeature() {
  resetTable('/admin/manage/edit_feature/id/0');
  $("#manage_list").bootstrapTable({ // 对应table标签的id
        url: "/admin/manage/feature", // 获取表格数据的url
        cache: false, // 设置为 false 禁用 AJAX 数据缓存， 默认为true
        striped: true,  //表格显示条纹，默认为false
        pagination: true, // 在表格底部显示分页组件，默认false
        pageList: [10, 20], // 设置页面可以显示的数据条数
        pageSize: 10, // 页面数据条数
        pageNumber: 1, // 首页页码
        sidePagination: 'server', // 设置为服务器端分页
        queryParams: function (params) { // 请求服务器数据时发送的参数，可以在这里添加额外的查询参数，返回false则终止请求
         // Add params
         return {
                pageSize: params.limit, // 每页要显示的数据条数
                offset: params.offset, // 每页显示数据的开始行号
                sort: params.sort, // 要排序的字段
                sortOrder: params.order, // 排序规则
            }
        },
        sortName: 'id', // 要排序的字段
        sortOrder: 'desc', // 排序规则
        columns: [
            {
                field: 'id', // 返回json数据中的name
                title: 'ID', // 表格表头显示文字
                align: 'center', // 左右居中
                valign: 'middle' // 上下居中
            }, {
                field: 'title',
                title: '名称',
                align: 'left',
                valign: 'middle'
            }, {
                 title: "操作",
                align: 'center',
                valign: 'middle',
                width: 160, // 定义列的宽度，单位为像素px
                formatter: function (value, row, index) {
                    return '<a href="/admin/manage/edit_feature/id/'+row.id+'" class="btn btn-default btn-lg" role="button">编辑</a>';
                }
            }
        ],
        onLoadSuccess: function(){  //加载成功时执行
              console.info("加载成功");
        },
        onLoadError: function(){  //加载失败时执行
              console.info("加载数据失败");
        }

  }) ;
}

function uploadImg( formData, cb) {
  $.ajax({
      url:"/admin/manage/upload",
      type:"POST",
      cache:false,
      data:formData,
      processData:false,
      contentType:false,
      dataType : 'json',
      success:function(data){
          cb( data );
      },
      fail:function(data){
          bootbox.alert("上传失败",function() {});
      }
  });
}


function revertPictureList() {
  $("#manage_list").bootstrapTable({ // 对应table标签的id
        url: "/admin/manage/pictureList", // 获取表格数据的url
        cache: false, // 设置为 false 禁用 AJAX 数据缓存， 默认为true
        striped: true,  //表格显示条纹，默认为false
        pagination: true, // 在表格底部显示分页组件，默认false
        pageList: [10, 20], // 设置页面可以显示的数据条数
        pageSize: 10, // 页面数据条数
        pageNumber: 1, // 首页页码
        sidePagination: 'server', // 设置为服务器端分页
        queryParams: function (params) { // 请求服务器数据时发送的参数，可以在这里添加额外的查询参数，返回false则终止请求
         // Add params
         return {
                pageSize: params.limit, // 每页要显示的数据条数
                offset: params.offset, // 每页显示数据的开始行号
                sort: params.sort, // 要排序的字段
                sortOrder: params.order, // 排序规则
            }
        },
        sortName: 'id', // 要排序的字段
        sortOrder: 'desc', // 排序规则
        columns: [
            {
                field: 'id', // 返回json数据中的name
                title: 'ID', // 表格表头显示文字
                align: 'center', // 左右居中
                valign: 'middle' // 上下居中
            }, {
                field: 'url',
                title: '地址',
                align: 'left',
                valign: 'middle'
            }, {
                title: "图片",
                align: 'center',
                valign: 'middle',
                width: 160, // 定义列的宽度，单位为像素px
                formatter: function (value, row, index) {
                    return '<img src="'+row.url+'" style="height:50px;" />';
                }
            }, {
                title: "操作",
                align: 'center',
                valign: 'middle',
                width: 160, // 定义列的宽度，单位为像素px
                formatter: function (value, row, index) {
                    var pa = row.url.split("/");
                    return '<a href="javascript:deletpic(\'' + pa [4] + '\',\'' + pa [5] +'\')" class="btn btn-default btn-lg" role="button">删除</a>';
                }
            }
        ],
        onLoadSuccess: function(){  //加载成功时执行
              console.info("加载成功");
        },
        onLoadError: function(){  //加载失败时执行
              console.info("加载数据失败");
        }

  }) ;
}

function deletpic( d, img ) {
  $.post("/admin/manage/deletepic", {
    'dt' : d,
    'img' : img
  } , function(data){
        data = $.parseJSON(data);
        if( data.result ) {
          window.location = "/admin/manage/index?main=pictureList";
        } else {
          console.log( "加载失败" );
        }
  });
}