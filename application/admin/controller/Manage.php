<?php
namespace app\admin\controller;
use think\Controller;
use \think\View;
use \think\Log;
use think\Request;

use app\index\model\Product;
use app\index\model\Category;
use app\index\model\Intro;
use app\index\model\Dict;
use app\index\model\Project;
use app\index\model\News;
use app\index\model\Cooperate;
use app\index\model\I18n;

define("UPLOAD_IMAGE_PATH", "/home/daien/imgs/") ;
//define("UPLOAD_IMAGE_PATH", "D:/wnmp/www/uploads/") ;
define("PRODUCT_CATEGORY" , 2);
define("NEWS_CATEGORY" , 3);
define("PROJECT_CATEGORY" , 4);
 

class Manage extends Common
{

	public function __construct()
	{
		parent::__construct();
		$this->_init();
	}

	private function _init() {
        $request = Request::instance();
        $main = $request->get('main') ;
        View::share('main',ucfirst($main));
    }

	public function index() {
		return view('admin@manage/index');
	}

	public function product() {
		$request = Request::instance();
		$where = "`status` = 'A'" ;
		$orderby = $request->get('sort') ;
		$limit = $request->get('pageSize');
		$start = $request->get('offset') ; 
		$product = new Product ;
		$products = $product->get_product($where, "id asc", $start, $limit);
		$count = $product->get_count($where);
        $category = new Category;
        $cates = $category->get_all_with_products( ) ;
        $cates = array_combine(array_column($cates, 'id'), $cates);
        
        foreach ($products as $key => &$value) {
            $value ['category_title'] = $cates [$value ['category_id']] ['title'];
            $value ['title'] = trim(strip_tags($value ['title']));
        }
        
		$data = [
				'total' => $count , 
				'rows' => $products,
		] ;
        echo json_encode($data) ;
        exit;
	}

	public function edit_product ( $id = 0) { 
		$product = null;
        $fcategory = 0 ;
		if( $id ) {
			$Product = new Product;
	        $product = $Product->get_product_by_id($id) ;
            if( $product ) {
                $product ['title_en'] =  $product ['description_en'] = "" ;
                $I18n = new I18n;
                $i18ninfo = $I18n->get_info( 'dn_product', 'en-us', 'title', $id );
                if( $i18ninfo ) {
                    $product ['title_en'] = $i18ninfo ['text'];
                }
                $i18ninfo = $I18n->get_info( 'dn_product', 'en-us', 'description', $id );
                if( $i18ninfo ) {
                    $product ['description_en'] = $i18ninfo ['text'];
                }

                // 归类
                $fcategory = !empty($product ['category_id']) ? $product ['category_id'] : null;
            }
    	}
        View::share('fcategory', $fcategory );
        $this->_get_parent_category();
        View::share('product',$product);
    	return view('admin@manage/product');
	}

	private function _get_category($parent) {
        $Category = new Category ;
        $cates = $Category->get_category($parent) ;
        $cates = array_combine(array_column($cates, 'id'), $cates);
        View::share('cates',$cates);
    }

    private function _get_parent_category () {
        $Category = new Category ;
        $categorys = $Category->get_category( PRODUCT_CATEGORY ) ;
        $categorys = array_combine(array_column($categorys, 'id'), $categorys);
        View::share('categorys',$categorys);
    }

    function saveProduct() {
    	$request = Request::instance();
    	$post = $request->post();
    	$data = [] ;
    	foreach( $post ['params'] as $param ) {
    		$data [$param['name']] = $param ['value'];
    	}
    	$Product = new Product;
        $data ['category_id'] = isset($data ['secondclass']) ? $data ['secondclass'] : 0;
        if( empty( $data ['category_id'] ) ) {
            echo $this->output_json ( false , "失败, 没有分类" , null) ; 
            exit ;
        }
        
		if(array_key_exists('id', $data)) {
			$Product->update_product($data ['id'], 'A', $data ['title'], $data ['category_id'], $data ['img_url'], $data ['description']);
            $id = $data ['id'];
		} else {
			$product = $Product->insert_product('A', $data ['title'], $data ['category_id'], $data ['img_url'], $data ['description']);
            $id = $product ['id'];
		}

        // 多语言
        $title_en = $data ['title_en'];
        $description_en = $data ['description_en'];
        $this->saveI18n( 'dn_product', 'en-us', 'description', $id, $description_en ) ;
        $this->saveI18n( 'dn_product', 'en-us', 'title', $id, $title_en ) ;

		echo $this->output_json ( true , "OK" , null) ;
    }

    private function output_json($success = true , $message = '' , $obj = array() ) {
    	$data = [
    		'result' => $success,
    		'message' => $message,
    		'obj' => $obj
    	] ;
    	return json_encode($data) ;
    }

    public function delProduct () {
    	$request = Request::instance();
    	$id = $request->param('id');
    	if( ! $id ) {
    		echo $this->output_json(false, "ERROR param" ) ;
    	}
    	$product = new Product ;
    	$product->delete_product( $id );
    	echo $this->output_json ( true , "OK" , null);
    }

    public function get_category () {
        $request = Request::instance();
        $parent = $request->post('id') ;
        $category = new Category ;
        $cates [0] = $category->get_category( PRODUCT_CATEGORY ) ;
        if( $parent ) {
            $cates [1] = $category->get_category( $parent ) ;
        }
        echo $this->output_json(true, "", $cates ) ;
    }

    public function get_category_info() {
        $request = Request::instance();
        $secondclass = $request->post('secondclass');
        $firstclass = $request->post('firstclass');
        $id = $secondclass != -1 && $secondclass != 0 ? $secondclass : $firstclass;
        $category = new Category ;
        $cates = $category->get_category_info( $id ) ;

        $I18n = new I18n;
        $i18ninfo = $I18n->get_info('dn_category', 'en-us', 'title', $id ) ;
        $cates ['title_en'] = $cates ['description_en'] = "";
        if( $i18ninfo ) {
            $cates ['title_en'] = $i18ninfo ['text'];
        }
        $i18ninfo = $I18n->get_info('dn_category', 'en-us', 'description', $id ) ;
        if( $i18ninfo ) {
            $cates ['description_en'] = $i18ninfo ['text'];
        }
        $result [0] = $cates;
        if( $firstclass ) {
            $result [1] = $category->get_category( $firstclass ) ;
        }

        echo $this->output_json(true, "", $result ) ;
    }

    public function saveCategoryProduct() {
        $request = Request::instance();
        $secondclass = $request->post('secondclass');
        $firstclass = $request->post('firstclass') ;

        if( !($firstclass == -1 || $secondclass == -1) ) {
            $this->updateCategoryProduct();
            exit ;
        }
        $title = $request->post('title');
        $rank = $request->post('rank');
        $img_url = $request->post('img_url');
        $description = $request->post('description');
        if( empty($title) || empty($rank) ) {
            echo $this->output_json(false, "请输入标题和排序" ) ;
            exit ;
        }

        $parent_id = empty($firstclass) || $firstclass == -1 ? PRODUCT_CATEGORY : $firstclass;
        $category = new Category ;
        $result = $category->saveCategory($parent_id, $title, $rank, $img_url, $description) ;
        
        $id = $result ['id'];
        $link = "/?cid=" . PRODUCT_CATEGORY . "&did=" . $id ;
        $category->updateCategoryLink( $result ['id'], $link ) ;
        
        //保存多语言信息
        $title_en = $request->post('title_en');
        $description_en = $request->post('description_en');
        $this->saveI18n( 'dn_category', 'en-us', 'description', $id, $description_en ) ;
        $this->saveI18n( 'dn_category', 'en-us', 'title',  $id, $title_en ) ;

        echo $this->output_json(true, "OK", null ) ;
    }

    public function updateCategoryProduct() {
        $request = Request::instance();
        $secondclass = $request->post('secondclass');
        $firstclass = $request->post('firstclass') ;
        $id = empty($secondclass) ? $firstclass : $secondclass ;
        $title = $request->post('title');
        $rank = $request->post('rank');
        $img_url = $request->post('img_url');
        $description = $request->post('description');
        if( $id == PRODUCT_CATEGORY || empty($title) || empty($rank) ) {
            echo $this->output_json(false, "请输入标题和排序,或者选择种类" ) ;
            exit ;
        }
        $category = new Category ;
        $category->modifyCategory( $id, $title, $rank, $img_url, $description ) ;

        // 多语言
        $title_en = $request->post('title_en');
        $description_en = $request->post('description_en');
        $this->saveI18n( 'dn_category', 'en-us', 'description',  $id, $description_en ) ;
        $this->saveI18n( 'dn_category', 'en-us', 'title',  $id, $title_en ) ;

        echo $this->output_json(true, "修改成功", null ) ;
    }
    

    function deleteCategoryProduct() {
    	$request = Request::instance();
    	$id = $request->param('id');
    	if( ! $id ) {
    		echo $this->output_json(false, "ERROR param" ) ;
    	}
    	$Category = new Category;
        $cates = $Category->get_category( $id ) ;
        if( $cates ) {
            $c = $Category->get_category_info( $id ) ;
            echo $this->output_json ( false , $c ['title'] . "有下级分类不能删除" , null) ;
        } else {
    		$Category->where('id='.$id)->delete();
    		echo $this->output_json ( true , "OK" , null) ;
        }
    }

    public function test() {
    	return view('admin@manage/test');
    }

    public function intro() {
    	$request = Request::instance();
		$intro = new Intro ;
		$intros = $intro->get_info() ;

        $I18n = new I18n;
        $I18n->replace_info( $intros, 'dn_intro', 'en-us', 'description', 'description_en');
        echo $this->output_json ( true , "OK" , $intros) ;
    }

    public function getIntro() {
        $request = Request::instance();
        $intro = new Intro ;
        $name = $request->get('name');
    	$introinfo = $intro->get_info_by_name( $name ) ;

        $I18n = new I18n ;
        $info = $I18n->get_info( 'dn_intro', 'en-us', 'description', $introinfo ['id'] );
        if ( $info ) {
            $introinfo ['description_en'] = $info ['text'];
        }

        echo $this->output_json ( true , "OK" , $introinfo) ;
    }

    function saveIntro() {
    	$request = Request::instance();
    	$post = $request->post();
    	$intro = new Intro;
        $intro->saveInfo($post ['name'], $post ['description']) ;

        if( ! empty( $post ['description_en'] ) ) {
            $I18n = new I18n ;
            $introinfo = $intro->get_info_by_name( $post ['name'] ) ;
            $this->saveI18n( 'dn_intro', 'en-us', 'description', $introinfo ['id'], $post ['description_en'] ) ;
        }


		echo $this->output_json ( true , "OK" , null) ;
    }

    public function home() {
        $dict = new Dict ;
        $home = $dict->field('name,value,extra_1')->where('model' , 'home')->select() ;
        foreach( $home as &$h ) {
            $h_extra = $h ['extra_1'] ? json_decode( $h ['extra_1'], true) : "" ;
            $h ['url'] = $h_extra ? $h_extra ['url'] : "";
        }
        echo $this->output_json ( true , "OK" , $home) ;
        exit;
    }

    public function saveIndex() {
        $request = Request::instance();
        $post = $request->post();
        $dict = new Dict ;
        $result = true;
        foreach ($post as $key => $value) {
            if( empty( $value ) ) continue ;

            $record = $dict->get( [
                'name' => $key,
                'model' => $key == 'setting_web_logo' ? 'setting' : 'home'
            ]) ;
            if( $record ) {
                if( $record ['value'] != $value ) {
                    $result = $dict->save(['value' => $value] , [
                        'id' => $record ['id']
                    ]);
                }
            } else {
                $dict = new Dict ;
                $dict->name = $key;
                $dict->value = $value;
                $dict->model = $key == 'setting_web_logo' ? 'setting' : 'home';
                $result = $dict->save();
            }
        }
        if( $result ) {
            echo $this->output_json ( true , "OK" , null) ;
        } else {
            echo $this->output_json ( false , "更新失败" , null) ;
        }
    }

    public function saveHomeBanner() {
        $request = Request::instance();
        $dict = new Dict ;
        $name = $request->post('name') ;
        $value = $request->post('value');
        $url = $request->post('url') ;
        $extra_1 = json_encode(["url" => $url]) ;
        $record = $dict->get( [
            'name' => $name,
            'model' => 'home'
        ]) ;
        if( $record ) {
            $result = $dict->save(['value' => $value, "extra_1" => $extra_1] , [
                'id' => $record ['id']
            ]);
        } else {
            $dict->name = $name;
            $dict->value = $value;
            $dict->model = 'home';
            $dict->extra_1 = $extra_1;
            $result = $dict->save();
        }
        if( $result ) {
            echo $this->output_json ( true , "OK" , null) ;
        } else {
            echo $this->output_json ( false , "更新失败" , null) ;
        }
    }

    public function News() {
        $request = Request::instance();
        $where = "`status` = 'A'" ;
        $orderby = $request->get('sort') ;
        $limit = $request->get('pageSize');
        $start = $request->get('offset') ; 
        $News = new News ;
        $news = $News->get_news($where, "id desc", $start, $limit);
        $count = $News->get_count($where);
        $category = new Category;
        $cate = $category->get_category_info( NEWS_CATEGORY ) ;
        
        foreach ($news as $key => &$value) {
            $value ['category_title'] = $cate ['title'];
            $value ['title'] = trim(strip_tags($value ['title']));
        }
        
        $data = [
                'total' => $count , 
                'rows' => $news,
        ] ;
        echo json_encode($data) ;
        exit;
    }

    public function edit_news($id = 0) {
        $data = null;
        if( $id ) {
            $news = new News;
            $data = $news->get_news_by_id($id) ;
        }
        View::share('news',$data);
        return view('admin@manage/news');
    }

    public function setting() {
        $dict = new Dict ;
        $something = $dict->get_info('home');
        $something = array_column($something, 'value' , 'name');

        $I18n = new I18n ;
        $info = $I18n->get_info( 'dn_dict', 'en-us', 'name', 2 );
        if ( $info ) {
            $something ['name_en'] = $info ['text'];
        }

        $info = $I18n->get_info( 'dn_dict', 'en-us', 'address', 6 );
        if ( $info ) {
            $something ['address_en'] = $info ['text'];
        }

        echo $this->output_json ( true , "OK" , $something) ;
    }

    public function saveSetting() {
        $request = Request::instance();
        $post = $request->post();
        $dict = new Dict ;
        $result = true;
        foreach ($post as $key => $value) {
            if( empty( $value ) ) continue ;
            if( $key == 'name_en' ) continue;
            $dict->save(['value' => $value] , [
                'name' => $key
            ]);
        }
        if( !empty( $post ['name_en'] ) ) {
            $this->saveI18n( 'dn_dict', 'en-us', 'name', 2, $post ['name_en'] );
        }
        if( !empty( $post ['address_en'] ) ) {
            $this->saveI18n( 'dn_dict', 'en-us', 'address', 6, $post ['address_en'] );
        }

        echo $this->output_json ( true , "OK" , null) ;
        
    }
    
    public function feature() {
        $request = Request::instance();
        $orderby = $request->get('sort') ;
        $limit = $request->get('pageSize');
        $start = $request->get('offset') ; 
        $feature = new Feature ;
        $features = $feature->limit($start,$limit)->order($orderby)->select() ;
        $count = $feature->count();
        $data = [
                'total' => $count , 
                'rows' =>$features
        ] ;
        echo json_encode($data) ;
        exit;
    }

    public function edit_feature( $id = 0) {
        $ftur = null;
        if( $id ) {
            $feature = new Feature;
            $ftur = $feature->get($id) ;
        }
        View::share('feature',$ftur);
        return view('admin@manage/feature');
    }

    public function saveFeatures() {
        $request = Request::instance();
        $data = $request->post();
        $feature = new Feature;
        if(array_key_exists('id', $data)) {
            $feature->save($data , ['id' => $data ['id']]);
        } else {
            $feature->save($data);
        }
        echo $this->output_json ( true , "OK" , null) ;
    }

    public function delfeatures ( $id ) {
        $request = Request::instance();
        $id = $request->param('id');
        if( ! $id ) {
            echo $this->output_json(false, "ERROR param" ) ;
        }
        $feature = new Feature;
        $feature->where('id='.$id)->delete();
        echo $this->output_json ( true , "OK" , null) ;
    }

    public function saveNews() {
        $request = Request::instance();
        $data = $request->post();
        $news = new News;
        if(array_key_exists('id', $data)) {
            $news->update_news($data ['id'], 'A', $data ['title'], $data ['img_url'], NEWS_CATEGORY, $data ['description'] );
        } else {
            $news->insert_news('A', $data ['title'], $data ['img_url'], NEWS_CATEGORY, $data ['description']);
        }
        echo $this->output_json ( true , "OK" , null) ;
    }

    public function delNews ( $id ) {
        $request = Request::instance();
        $id = $request->param('id');
        if( ! $id ) {
            echo $this->output_json(false, "ERROR param" ) ;
        }
        $news = new News;
        $news->delete_news( $id );
        echo $this->output_json ( true , "OK" , null) ;
    }

    public function upload () {
        $request = Request::instance();
        $names = explode('.', $_FILES["file"]["name"]);
        $extension = end ( $names );
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        if(! in_array(strtolower($extension), $allowedExts) ) {
            echo $this->output_json ( false , "不支持的文件" , null) ;
            exit ;

        }
        
        $name = uniqid() . "." . $extension ;
        $path = UPLOAD_IMAGE_PATH . date('Y-m-d') . '/' ;
        if( ! is_dir ( $path ) ) {
            mkdir ($path , 0777 ) ;
        }
        move_uploaded_file($_FILES["file"]["tmp_name"], $path . $name );
        $url = $request->domain() . '/img/' . date('Y-m-d') . "/" . $name ;
        echo $this->output_json ( true , "success" , ['url' => $url]);
    }

    public function delBannerImg( ) {
        $request = Request::instance();
        $name = $request->param('name');
        if( ! $name ) {
            echo $this->output_json(false, "ERROR param" ) ;
        }
        Dict::destroy(['name' => $name]);
        echo $this->output_json ( true , "OK" , null) ;
    }

    public function project() {
        $request = Request::instance();
        $where = "`status` = 'A'" ;
        $orderby = $request->get('sort') ;
        $limit = $request->get('pageSize');
        $start = $request->get('offset') ; 
        $project = new Project ;
        $projects = $project->get_project($where, "id asc", $start, $limit);
        $count = $project->get_count($where);
        $category = new Category;
        $cates = $category->get_category( PROJECT_CATEGORY ) ;
        $cates = array_combine(array_column($cates, 'id'), $cates);
        
        foreach ($projects as $key => &$value) {
            $value ['category_title'] = $cates [$value ['category_id']] ['title'];
            $value ['title'] = trim(strip_tags($value ['title']));
        }
        
        $data = [
                'total' => $count , 
                'rows' => $projects,
        ] ;
        echo json_encode($data) ;
        exit;
    }

    public function edit_project($id = 0) {
        $product = null;
        $fcategory = 0 ;
        if( $id ) {
            $Project = new Project;
            $project = $Project->get_project_by_id($id) ;

            $project ['title_en'] = $project ['description_en'] = "" ;

            // 归类
            $Category = new Category ;
            $category = $Category->get_category_info( $project ['category_id'] ) ;
        }
        View::share('category', $category );
        View::share('project',$project);
        return view('admin@manage/project');
    }

    public function saveproject () {
        $request = Request::instance();
        $post = $request->post();
        $data = [] ;
        foreach( $post ['params'] as $param ) {
            $data [$param['name']] = $param ['value'];
        }
        $Project = new Project;
        $Category = new Category;
        $project_categorys = $Category->get_category( PROJECT_CATEGORY ) ;
        $category_id = $project_categorys [0] ['id'] ;

        if(array_key_exists('id', $data)) {
            $Project->update_project($data ['id'], 'A', $data ['title'], $category_id, $data ['img_url'], $data ['description']);
            $id = $data ['id'];
        } else {
            $project = $Project->insert_project('A', $data ['title'], $category_id, $data ['img_url'], $data ['description']);
            $id = $prodject ['id'] ;
        }

        // 多语言
        $this->saveI18n( 'dn_project', 'en-us', 'description',  $id, $data ['description_en'] ) ;
        $this->saveI18n( 'dn_project', 'en-us', 'title',  $id, $data ['title_en'] ) ;

        echo $this->output_json ( true , "OK" , null) ;
    }

    public function delproject() {
        $request = Request::instance();
        $id = $request->param('id');
        if( ! $id ) {
            echo $this->output_json(false, "ERROR param" ) ;
        }
        $Project = new Project ;
        $Project->delete_project( $id );
        echo $this->output_json ( true , "OK" , null);
    }

    public function cooperate() {
        $request = Request::instance();
        $where = "`status` = 'A'" ;
        $orderby = $request->get('sort') ;
        $limit = $request->get('pageSize');
        $start = $request->get('offset') ; 
        $cooperate = new Cooperate ;
        $cooperates = $cooperate->get_cooperate($where, "id asc", $start, $limit);
        $count = $cooperate->get_count($where);
        
        $data = [
                'total' => $count , 
                'rows' => $cooperates,
        ] ;
        echo json_encode($data) ;
        exit;
    }

    public function edit_cooperate($id = 0) {
        $cooperate = null;
        if( $id ) {
            $Cooperate = new Cooperate;
            $cooperate = $Cooperate->get_cooperate_by_id($id) ;
        }
        View::share('cooperate',$cooperate);
        return view('admin@manage/cooperate');
    }

    public function savecooperate () {
        $request = Request::instance();
        $post = $request->post();
        $data = [] ;
        foreach( $post ['params'] as $param ) {
            $data [$param['name']] = $param ['value'];
        }
        $Cooperate = new Cooperate;

        if(array_key_exists('id', $data)) {
            $Cooperate->update_cooperate($data ['id'], 'A', $data ['title'], $data ['img_url'], $data ['link']);
        } else {
            $Cooperate->insert_cooperate('A', $data ['title'], $data ['img_url'], $data ['link']);
        }
        echo $this->output_json ( true , "OK" , null) ;
    }

    public function delcooperate() {
        $request = Request::instance();
        $id = $request->param('id');
        if( ! $id ) {
            echo $this->output_json(false, "ERROR param" ) ;
        }
        $Cooperate = new Cooperate;
        $Cooperate->delete_cooperate( $id );
        echo $this->output_json ( true , "OK" , null);
    }

    public function saveI18n( $table, $lang, $column, $target_id, $text ) {
        if( empty($text)) return true ;
        $text = addslashes($text) ;
        $I18n = new I18n ;
        $i18ninfo = $I18n->get_info( $table, $lang, $column, $target_id ) ;
        if( $i18ninfo ) {
            $I18n->updateI18n($i18ninfo ['id'], $text);
        } else {
            $I18n->saveI18n( $table, $column, $lang, $target_id, $text );
        }
    }

    public function pictureList() {
        $request = Request::instance();
        $start = $request->get('offset') / 10 ;
        
        $dirs = scandir( UPLOAD_IMAGE_PATH ) ;

        $section = $dirs;
        foreach( $section as $k => $v ) {
            $section [$k] = date("Y-m", strtotime($v) );
        }
        $usection = array_unique( $section );
        rsort( $usection );
        if( empty($usection [$start]) ) {
            $data = [
                'total' => 1, 
                'rows' => [],
            ]; exit ;
        }
        $month = $usection [$start];

        $imgs = [] ;
        $imgs_num = 1 ;
        $total = 0;
        foreach( $dirs as $key => $dir ) {
            if($dir == "." || $dir == "..") {
                continue;
            }
            if( date("Y-m", strtotime($dir) ) != $month  ) { 
                $imgfiles = scandir( UPLOAD_IMAGE_PATH . $dir );
                $total += count($imgfiles) - 2 ;
                continue;
            }
            $imgfiles = scandir( UPLOAD_IMAGE_PATH . $dir );
            foreach( $imgfiles as $imgfile ) {
                if($imgfile == "." || $imgfile == "..") {
                    continue;
                } else {
                    $total++;
                    $img ['url'] = $request->domain() . "/img/" . $dir . "/" . $imgfile;
                    $img ['id'] = $imgs_num++ ;
                    array_push(  $imgs, $img );
                }
            }
        }
        $data = [
                'total' => $total, 
                'rows' => $imgs,
        ] ;
        echo json_encode($data) ;
        exit;
    }

    function deletepic( ) {
        $request = Request::instance();
        $dt = $request->post ('dt') ;
        $img = $request->post ('img') ;
        unlink(UPLOAD_IMAGE_PATH . $dt . '/' . $img ) ;
        echo $this->output_json ( true , "OK" , null);
    }

}