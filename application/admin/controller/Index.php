<?php
namespace app\admin\controller;
use \think\View;
use \think\Log;
use think\Request;
use think\Session;

class Index extends Common
{

	public function __construct()
	{
		parent::__construct();
		$this->init();
	}

	/**
	 *  初始化页面基本属性
	 */
	public function init () 
	{
		View::share('title','欢迎来到岱恩');
	}

    public function index()
    {
    	$request = Request::instance();
        $username = $request->get('username');
        $password = $request->get('password');
        if( $username && $password ) {
	        if( $username == ADMIN_USERNAME && $password == ADMIN_PASSWORD) {
	        	Session::set('islogin','DN');
	        	$this->redirect('/admin/manage/index');
	        } else {
	        	View::share("msg", "用户名或密码错误");
	        }
	    }
        return view('admin@index/index');
    }

    public function manage() {
    	return view('admin@index/manage');
    }
}
