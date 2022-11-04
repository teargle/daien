<?php

namespace app\index\model;
use think\Model;
use think\Db;
use think\Config;

/**
 * 
 */
class I18n extends Model
{
	protected $table = "dn_i18n";
	
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

    public function get_info( $table, $lang, $column, $target_id ) {
        $i18n = Db::query("select * from dn_i18n where `table` = :table and `lang`=:lang and `column`=:column and `target_id`=:target_id", [
            'table' => "{$table}",
            'lang' => "{$lang}",
            'column' => "{$column}",
            'target_id' => $target_id
        ]);
        return $i18n ? $i18n [0] : null;
    }

    public function replace_info( &$targets, $table, $lang, $src_column, $desc_column = null) {
        $ids = implode(",",array_column($targets , 'id')) ;
        $targets = array_combine( array_column($targets, 'id') , $targets);
        $infos = Db::query("select * from dn_i18n where `table` = :table and `lang`=:lang and `column`=:column and `target_id` in ( {$ids} )", [
            'table' => "{$table}",
            'lang' => "{$lang}",
            'column' => "{$src_column}"
        ]);
        foreach( $infos as $info ) {
            $desc_column = $desc_column ? $desc_column : $src_column ;
            $targets [$info ['target_id']] [$desc_column] = $info ['text'] ;
        }
        return true ;
    }

    public function saveI18n( $table, $column, $lang, $target_id, $text ) {
        $text = addslashes($text) ;
        return Db::query( "insert into dn_i18n (`table`,`column`,`lang`,`target_id`,`text` ) values ('{$table}', '{$column}', '{$lang}', {$target_id}, '{$text}') " );
    }

    public function updateI18n( $id, $text ) {
        $text = addslashes($text) ;
        if( $text ) $str .= "text = '" . $text . "'" ;
        return Db::query( "update dn_i18n set {$str} where id={$id} ");
    }
 
}