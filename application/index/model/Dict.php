<?php

namespace app\index\model;
use think\Model;
use think\Db;
use think\Config;

/**
 * 
 */
class Dict extends Model
{
	protected $table = "dn_dict";
	
	//自定义初始化
    protected function initialize()
    {
        //需要调用`Model`的`initialize`方法
        parent::initialize();
        //TODO:自定义的初始化
    }

    //自定义初始化
    protected static function init()
    {
        //TODO:自定义的初始化
        Db::connect( Config::get("database") ); 
    }

    public function get_info( $model = 'home') {
        $list = Db::query('select * from dn_dict where model = :model', ['model' => $model]);
        return $list ;
    }

}