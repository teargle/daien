<?php

namespace app\index\model;
use think\Model;
use think\Db;
use think\Config;

/**
 * 
 */
class Intro extends Model
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

    public function get_info_by_id( $id = 1) {
        $intros = Db::query('select * from dn_intro where id = :id', ['id' => $id]);
        return $intros [0] ;
    }
 
    public function get_info() {
        $intros = Db::query('select * from dn_intro');
        return $intros ;
    }

    public function get_info_by_name( $name ) {
        $intros = Db::query('select * from dn_intro where name=:name', ['name' => $name]);
        return $intros [0] ;
    }

    public function saveInfo ($name, $description) {
        $intros = Db::query('update dn_intro set description=:description where name=:name', ['description' => $description , 'name' => $name]);
        return true ;
    }
}