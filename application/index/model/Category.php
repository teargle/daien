<?php

namespace app\index\model;
use think\Model;
use think\Db;
use think\Config;
/**
 * 
 */
class Category extends Model
{
	protected $table = "dn_category";
	
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

    public function get_category( $parent = 1) {
        return Db::query('select * from dn_category where parent=:parent', ['parent' => $parent]);
    }

    public function get_category_info( $id ) {
        $categorys = Db::query('select * from dn_category where id=:id', ['id' => $id]);
        return $categorys [0];
    }
 
    public function get_main_category( $id = 1 ) {
        return Db::query('select * from dn_category where id=:id', ['id' => $id]);
    }

    public function updateCategory($id, $parent, $title, $rank, $img_url, $description) {
        return Db::query('update dn_category set title=:title, rank=:rank, parent=:parent , img_url=:img_url, `description`=:description where id=:id', 
            ['id' => $id, 'title'=>$title, 'rank'=>$rank, 'img_url'=>$img_url, 'description'=>$description]);
    }

    public function saveCategory($parent, $title, $rank, $img_url, $description) {
        Db::query("insert into dn_category (`parent`,`title`,`rank`,`img_url`,`description`) values ({$parent},'{$title}',{$rank},'{$img_url}','{$description}') ");
        $id = Db::name("dn_category")->getLastInsID();
        return $this->get_category_info($id) ;
    }

    public function updateCategoryLink( $id, $link ) {
        return Db::query('update dn_category set link=:link where id=:id', ['id' => $id, 'link' => $link]);
    }

    public function modifyCategory($id, $title, $rank, $img_url, $description) {
        return Db::query('update dn_category set title=:title, `rank`=:rank , img_url=:img_url, `description`=:description where id=:id', 
                ['id' => $id, 'title'=>$title, 'rank'=>$rank, 'img_url'=>$img_url, 'description'=>$description] );
    }

    public function get_category_by_product() {
        return Db::query('select c.id,  CONCAT(cc.title,\'-\',c.title) title from dn_category cc 
                            join dn_category c on cc.id = c.parent 
                            where cc.parent = 2');
    }
}