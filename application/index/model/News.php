<?php

namespace app\index\model;
use think\Model;
use think\Db;
use think\Config;

/**
 * 
 */
class News extends Model
{
	protected $table = "dn_news";
	
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

    public function get_news_by_category( $category_id = 3, $offset = 0, $limit = 12 ) {
        $list = Db::query('select * from dn_news where `status` = \'A\' and category_id = :category_id order by create_time desc limit ' . $offset . ',' . $limit , ['category_id' => $category_id]);
        return $list ;
    }

    public function get_news_num_by_category( $category_id = 3 ) {
        $total = Db::query('select count(*) as num from dn_news where `status` = \'A\' and category_id = :category_id' , ['category_id' => $category_id]);
        return $total [0] ['num'] ;
    }
 

    public function get_news_by_id ( $id ) {
		$details = Db::query('select * from dn_news where id = :id', ['id' => $id]);
        return $details [0] ;
    }

    public function get_news( $where , $orderby, $offset, $limit ) {
        $list = Db::query('select * from dn_news where status = \'A\' order by create_time desc limit ' . $offset . ',' . $limit);
        return $list ;
    }

    public function get_count( $where ) {
        $num = Db::query('select count(*) num from dn_news where status = \'A\'' );
        return $num [0] ['num'] ;
    }

    public function insert_news($status, $title, $img_url, $category_id, $description) {
        Db::query("insert into dn_news (`status`, `title`, `img_url`, `category_id`, `description`) values 
            ('{$status}', '{$title}', '{$img_url}', {$category_id}, '{$description}') ") ;
        $id = Db::name("dn_news")->getLastInsID();
        return $this->get_news_by_id($id) ;
    }

    public function update_news($id , $status = null, $title = null, $img_url = null, $category_id = null, $description = null) {
        $str = "" ;
        $str .= "`status` = '" . ($status ? $status : 'A') . "'" ;
        if( $title ) $str .= ", title = '" . $title . "'" ;
        if( $img_url ) $str .= ", img_url = '" . $img_url . "'";
        if( $category_id ) $str .= ", category_id = " . $category_id ;
        if( $description ) $str .= ", description = '" . $description . "'";
        return Db::query( "update dn_news set {$str} where id = " . $id ) ; 
    }

    public function delete_news($id) {
        return Db::query( "update dn_news set `status` = 'X' where id = " . $id ) ;
    }
}