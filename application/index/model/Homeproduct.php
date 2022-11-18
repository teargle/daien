<?php

namespace app\index\model;
use think\Model;
use think\Db;
use think\Config;

/**
 * 
 */
class Homeproduct extends Model
{
	protected $table = "dn_homeproduct";
	
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

    public function get_homeproduct ( $offset, $limit ) {
        $list = Db::query('select * from dn_homeproduct where status = \'A\' order by `rank` desc limit ' . $offset . ',' . $limit);
        return $list ;
    }

    public function get_homeproduct_by_id ( $id ) {
        $list = Db::query('select * from dn_homeproduct where status = \'A\' and id = ' . $id );
        return $list ? $list [0] : [] ;
    }

    public function get_homeproduct_by_category_id ( $category_id ) {
        $list = Db::query('select * from dn_homeproduct where status = \'A\' and category_id = ' . $category_id );
        return $list ? $list [0] : [] ;
    }

    public function get_count( ) {
        $num = Db::query('select count(*) num from dn_homeproduct where status = \'A\'' );
        return $num [0] ['num'] ;
    }

    public function insert_homeproduct ($status, $title, $img_url, $description, $url, $product_ids, $category_id, $rank) {
        Db::query("insert into dn_homeproduct (`status`, `title`, `img_url`, `description`, `url`, `product_ids`, `category_id`, `rank` ) values 
            ('{$status}', '{$title}', '{$img_url}', '{$description}', '{$url}', '{$product_ids}', '{$category_id}', {$rank}) ") ;
        $id = Db::name("dn_product")->getLastInsID();
        return $this->get_homeproduct_by_id($id) ;
    }

    public function update_homeproduct ($id , $status = null, $title = null, $img_url = null, 
                $description = null, $url = null, $product_ids = null, $category_id = null,$rank = null) {
        $str = "" ;
        $str .= "`status` = '" . ($status ? $status : 'A') . "'" ;
        if( $title ) $str .= ", title = '" . $title . "'" ;
        if( $img_url ) $str .= ", img_url = '" . $img_url . "'";
        if( $url ) $str .= ", url = '" . $url . "'" ;
        if( $description ) $str .= ", description = '" . $description . "'";
        if( $product_ids ) $str .= ", product_ids = '" . $product_ids . "'";
        if( $category_id ) $str .= ", category_id = " . $category_id ;
        if( $rank ) $str .= ", `rank` = " . $rank ;
        return Db::query( "update dn_homeproduct set {$str} where id = " . $id ) ; 
    }

    public function delete_homeproduct ($id) {
        return Db::query( "update dn_homeproduct set `status` = 'X' where id = " . $id ) ;
    }
}