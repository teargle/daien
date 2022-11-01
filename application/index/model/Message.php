<?php

namespace app\index\model;
use think\Model;
use think\Db;
use think\Config;
/**
 * 
 */
class Message extends Model
{
	protected $table = "dn_message";
	
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

    public function saveInfo($linkinfo, $message, $ip) {
        Db::query("insert into dn_message (`linkinfo`,`msginfo`, `ip`) values ('{$linkinfo}','{$message}','{$ip}' ) ");
        $id = Db::name("dn_message")->getLastInsID();
        return $id ;
    }

    public function get_info_by_ip ( $ip ) {
        $info = Db::query('select * from dn_message where ip = :ip order by id desc limit 1 ', ['ip' => $ip] );
        return $info ? $info [0] : null;
    }

    
}