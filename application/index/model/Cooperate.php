<?php

namespace app\index\model;
use think\Model;
use think\Db;
use think\Config;

/**
 * 
 */
class Cooperate extends Model
{
	protected $table = "dn_cooperate";
	
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

    public function get_cooperate_by_id ( $id ) {
		$details = Db::query('select * from dn_cooperate where id = :id', ['id' => $id]);
        return $details [0] ;
    }

    public function get_cooperate( $where , $orderby, $offset, $limit ) {
        $list = Db::query('select * from dn_cooperate where status = \'A\' order by id asc limit ' . $offset . ',' . $limit);
        return $list ;
    }

    public function get_count( $where ) {
        $num = Db::query('select count(*) num from dn_cooperate where status = \'A\'' );
        return $num [0] ['num'] ;
    }

    public function insert_cooperate($status, $title, $img_url, $link) {
        return Db::query("insert into dn_cooperate (`status`, `title`, `img_url`, `link`) values 
            ('{$status}', '{$title}', '{$img_url}', '{$link}') ") ;
    }

    public function update_cooperate($id , $status = null, $title = null, $img_url = null, $link = null) {
        $str = "" ;
        $str .= "`status` = '" . ($status ? $status : 'A') . "'" ;
        if( $title ) $str .= ", title = '" . $title . "'" ;
        if( $img_url ) $str .= ", img_url = '" . $img_url . "'";
        if( $link ) $str .= ", link = '" . $link . "'";
        return Db::query( "update dn_cooperate set {$str} where id = " . $id ) ; 
    }

    public function delete_cooperate($id) {
        return Db::query( "update dn_cooperate set `status` = 'X' where id = " . $id ) ;
    }
}