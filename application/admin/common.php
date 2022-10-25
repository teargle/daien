<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Session;
class Common extends Controller
{
	public function __construct()
	{
		 $login = Session::get('islogin');
		 if( $login != 'DN' ) {
		 	$request = Request::instance();
		 	$module = $request->module() ;
		 	$controller = $request->controller();
		 	$action = $request->action();
		 	if ( !($module == 'admin' && $controller == 'index' && $action == 'index') ) {
		 		redirect('/admin/index/index') ;
		 	}
		 }
	}

}

define("ADMIN_USERNAME" , 'admin');
define("ADMIN_PASSWORD" , '4621@daien+');