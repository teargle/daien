<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Lang;
use \think\Cookie;
use \think\Log;

use app\index\model\Dict;
use app\index\model\Category;
use app\index\model\Product;
use app\index\model\Intro;
use app\index\model\Project;
use app\index\model\News;
use app\index\model\Cooperate;

use app\index\model\I18n;

class Index extends Controller
{
    // 大分类
    private $cid = 2;
    // 小分类
    private $did = 0;
    // 单一介绍
    private $tid = 1;
    // 产品ID
    private $pid = 0;
    // 产品当前页
    private $page = 0;

    // 产品每页展示个数
    private $product_limit = 8;
    // 工程案例每页展示个数
    private $project_limit = 9;
    // 新闻展示数量
    private $news_limit = 5;
    // 当前语言
    private $language = 'zh-cn';

    public function __construct()
    {
        //初始化用户浏览器
        parent::__construct();
        if( is_mobile_browser() ) {
            $this->redirect( "/mobile/index");
            exit;
        }
        $this->init();
        // 设置语言
        $this->setLang() ;
    }

    private function init() {
        $this->cid = !empty($_GET['cid']) ? $_GET['cid'] : 2 ;
        $this->tid = !empty($_GET['tid']) ? $_GET['tid'] : 0 ;
        $this->pid = !empty($_GET['pid']) ? $_GET['pid'] : 0 ;
        $this->did = !empty($_GET['did']) ? $_GET['did'] : 14 ;
        $this->page = !empty($_GET['page']) ? $_GET['page'] : 1 ;
        // 默认值
        $this->assign('tid' , $this->tid );
        $this->assign('did' , $this->did );
        $this->assign('cid' , $this->cid );
    }

    private function setLang() {
        Lang::detect(); //检测语言
        $this->language = Cookie::get('think_var') ? Cookie::get('think_var') : 'zh-cn';
        $this->assign('language' , $this->language );
        $file = "../application/index/lang/" . $this->language . ".php" ;
        Lang::load($file);
    }

    public function index()
    {
        $this->_get_company_info() ;
        $this->_get_category_info() ;
        $this->_get_product_by_category() ;
        $this->_get_intro_info() ;

        $this->_get_project_info() ;
        $this->_get_news_info() ;
        $this->_get_cooperate_info() ;
        return $this->fetch('index');
    }

    private function _get_company_info() {
        $dict = new Dict ;
        $home = $dict->get_info( 'home' ) ;
        $home = array_combine(array_column($home, "name"), $home) ;

        if( $this->language == 'en-us' ) {
            $I18n = new I18n ;
            $info = $I18n->get_info( 'dn_dict', 'en-us', 'name', 2 );
            if ( $info ) {
                $home ['name'] ['value'] = $info ['text'];
            }

            $info = $I18n->get_info( 'dn_dict', 'en-us', 'address', 6 );
            if ( $info ) {
                $home ['address'] ['value'] = $info ['text'];
            }
        }

        $this->assign('home' , $home);

    }

    private function _get_category_info() {
        $category = new Category ;
        $i18n = new I18n ;

        $cates = $category->get_category( $this->cid ) ;
        if( $cates ) {
            $i18n->replace_info( $cates, 'dn_category', $this->language, 'title' ) ;
        }
        $this->assign('cates' , $cates);

        
        $category_title = $category->get_main_category( $this->cid );
        $this->assign('title' , $category_title [0] ['title']);
        if( $this->language != 'zh-cn' ) {
            $i18n_info = $i18n->get_info ( 'dn_category', $this->language, 'title', $category_title [0] ['id']  ) ;
            $this->assign('title' , $i18n_info ['text']);
        }

        if( $this->did ) {
            $main_category = $category->get_main_category( $this->did );
            $category = [ 'title' => '' ];
            if( $main_category ) {
                $category = $main_category [0];
                if( $category ) {
                    $i18n_info = $i18n->get_info ( 'dn_category', $this->language, 'title', $category ['id']  ) ;
                    $category ['title'] = $i18n_info ? $i18n_info ['text'] : $category ['title'];
                }
            }
            $this->assign('category' , $category);
        }
    }

    private function _get_product_by_category() {
        if( $this->cid != 2 ) {
            return true ;
        }

        $product = new Product ;
        if( ! empty( $this->pid ) ) {
            $product_detail = $product->get_product_by_id( $this->pid ) ;
            if( $this->language != 'zh-cn' && $product_detail ) {
                $I18n = new I18n;
                $i18ninfo = $I18n->get_info( 'dn_product', $this->language, 'title', $this->pid );
                if( $i18ninfo ) {
                    $product_detail ['title'] = $i18ninfo ['text'];
                }
                $i18ninfo = $I18n->get_info( 'dn_product', $this->language, 'description', $this->pid );
                if( $i18ninfo ) {
                    $product_detail ['description'] = $i18ninfo ['text'];
                }
            }
            $this->assign('product_detail' , $product_detail );
        } else {
            $offset = ($this->page - 1)  * $this->product_limit ;
            $products = $product->get_product_by_category( $this->did, $offset , $this->product_limit );

            if( $this->language != 'zh-cn' && $products ) {
                $I18n = new I18n;
                $I18n->replace_info ($products, 'dn_product', $this->language, 'title' ) ;
                $I18n->replace_info ($products, 'dn_product', $this->language, 'description' ) ;
            }

            $total = $product->get_product_num_by_category( $this->did );
            $total_page = ceil($total / $this->product_limit );
            $this->assign('products' , $products );
            $this->assign('page' , $this->page );
            $this->assign('total' , $total );
            $this->assign('total_page' , $total_page );
        }
    }

    private function _get_intro_info() {
        /** 公司概况  企业文化 人才招聘 联系我们 */
        if( $this->cid != 1 ) {
            return true ;
        }
        
        if( empty($this->tid) ) return true;
        $Intro = new Intro;
        $intro = $Intro->get_info_by_id ($this->tid) ;
        // 文本多语言
        $I18n = new I18n;
        if( $this->language != 'zh-cn') {
            $i18ninfo = $I18n->get_info( 'dn_intro', $this->language, 'description', $intro ['id'] );
            if( $i18ninfo ) {
                $intro ['description'] = $i18ninfo ['text'];
            }
        }
        $this->assign('intro' , $intro );
        $this->assign('tid', $this->tid );

        // 面包屑多语言
        $Category = new Category;
        
        $category = $Category->get_category_info( $intro ['category_id'] );
        if( $this->language != 'zh-cn') {    
            $i18n_info = $I18n->get_info ( 'dn_category', $this->language, 'title', $category ['id']  ) ;
            $category ['title'] = $i18n_info ['text'];
        }
        $this->assign('category' , $category);
    }

    private function _get_project_info() {
        if( $this->cid != 4 ) {
            return true ;
        }

        $this->did = empty($this->did) ? 45 : $this->did;

        $project = new Project ;
        if( ! empty( $this->pid ) ) {
            $project_detail = $project->get_project_by_id( $this->pid ) ;
            if( $this->language != 'zh-cn' ) {
                $I18n = new I18n;
                $i18ninfo = $I18n->get_info( 'dn_project', $this->language, 'title', $this->pid );
                if( $i18ninfo ) {
                    $project_detail ['title'] = $i18ninfo ['text'];
                }
                $i18ninfo = $I18n->get_info( 'dn_project', $this->language, 'description', $this->pid );
                if( $i18ninfo ) {
                    $project_detail ['description'] = $i18ninfo ['text'];
                }
            }
            $this->assign('project_detail' , $project_detail );
        } else {
            $offset = ($this->page - 1)  * $this->project_limit ;
            $projects = $project->get_project_by_category( $this->did, $offset , $this->project_limit );
            if( $this->language != 'zh-cn' ) {
                $I18n = new I18n;
                $I18n->replace_info ($projects, 'dn_project', $this->language, 'title' ) ;
                $I18n->replace_info ($projects, 'dn_project', $this->language, 'description' ) ;
            }
            $total = $project->get_project_num_by_category( $this->did );
            $total_page = ceil($total / $this->project_limit );
            $this->assign('projects' , $projects );
            $this->assign('page' , $this->page );
            $this->assign('total' , $total );
            $this->assign('total_page' , $total_page );
        }


    }

    public function _get_news_info() {
        if( $this->cid != 3 ) {
            return true ;
        }

        $News = new News;
        $I18n = new I18n;

        if( $this->pid ) {
            $news_detail = $News->get_news_by_id( $this->pid ) ;
            $this->assign('news_detail' , $news_detail );
        } else {
            $news = $News->get_news_by_category( $this->cid, 0, $this->news_limit ) ;
            $this->assign('news' , $news );
        }
        
        $Category = new Category;
        $category = $Category->get_category_info( $this->cid );
        if( $this->language != 'zh-cn' ) {
            $i18n_info = $I18n->get_info ( 'dn_category', $this->language, 'title', $category ['id']  ) ;
            $category ['title'] = $i18n_info ? $i18n_info ['text'] : $category ['title'];
        }
        //$category = [ 'title' => '新闻中心'];
        $this->assign('category' , $category );

    }

    public function _get_cooperate_info() {
        $Cooperate = new Cooperate;
        $where = " `status` = 'A' ";
        $cooperates = $Cooperate->get_cooperate($where, 'id desc', 0, 100) ;
        $this->assign('cooperates' , $cooperates );
    }

    public function changeLang( ) {
        $request = Request::instance();
        $lang = $request->post('lang');
        Cookie::forever('think_var', $lang, 3600);
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
}