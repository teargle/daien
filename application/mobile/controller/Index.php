<?php
namespace app\mobile\controller;

use \think\Controller;
use \think\View;
use \think\Log;
use \think\Request;

use app\index\model\Dict;
use app\index\model\Category;
use app\index\model\Product;
use app\index\model\Intro;
use app\index\model\Project;
use app\index\model\News;
use app\index\model\Cooperate;

class Index extends Controller
{
    //用户访问的语言
    private $lang = null;
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
    private $product_limit = 6;
    // 工程案例每页展示个数
    private $project_limit = 9;
    // 新闻展示数量
    private $news_limit = 5;

    public function __construct()
    {
        //初始化用户浏览器
        parent::__construct();
        if( !is_mobile_browser() ) {
            $this->redirect("/");
            exit;
        }
        $this->init();
    }

    private function init() {
        $this->cid = !empty($_GET['cid']) ? $_GET['cid'] : 2 ;
        $this->tid = !empty($_GET['tid']) ? $_GET['tid'] : ($this->cid == 1 ? 1 : 0) ;
        $this->pid = !empty($_GET['pid']) ? $_GET['pid'] : 0 ;
        $this->did = !empty($_GET['did']) ? $_GET['did'] : 0 ;
        $this->page = !empty($_GET['page']) ? $_GET['page'] : 1 ;
        // 默认值
        $this->assign('tid' , $this->tid );
        $this->assign('did' , $this->did );
        $this->assign('cid' , $this->cid );
        $this->assign('pid' , $this->pid );
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
        $this->assign('home' , $home);
    }

    private function _get_category_info() {
        $category = new Category ;

        $cates = $category->get_category( $this->cid ) ;
        $this->assign('cates' , $cates);

        if( $this->did ) {
           $c = $category->get_category_info( $this->did ) ;
           $this->assign('c' , $c);
           if( $c ['parent'] == 2 ) {
                $cates = $category->get_category( $this->did );
                $this->assign('cates' , $cates);
           }
        }

        
        $category_title = $category->get_main_category( $this->cid );
        $this->assign('crumbs_title' , $category_title [0] ['title']);
        $this->assign('crumbs_url' , "/mobile/index?cid={$this->cid}" );
        

        if( $this->did ) {
            $main_category = $category->get_main_category( $this->did );
            $this->assign('crumbs_sub_title' , $main_category [0] ['title']);
            $this->assign('crumbs_sub_url' , "/mobile/index?cid={$this->cid}&did={$this->did}" );
        }
    }

    private function _get_product_by_category() {
        if( $this->cid != 2 ) {
            return true ;
        }

        $product = new Product ;
        if( ! empty( $this->pid ) ) {
            $product_detail = $product->get_product_by_id( $this->pid ) ;
            $this->assign('product_detail' , $product_detail );
        } else {
            $offset = ($this->page - 1)  * $this->product_limit ;
            $products = $product->get_product_by_category( $this->did, $offset , $this->product_limit );
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
        $intro = new Intro;
        $intro = $intro->get_info_by_id ($this->tid) ;

        $this->assign('crumbs_next_title' , $intro ['title']);

        $this->assign('intro' , $intro );
        $this->assign('tid' , $this->tid );
    }

    private function _get_project_info() {
        if( $this->cid != 4 ) {
            return true ;
        }
        if( empty($this->did) ) return true;
        $project = new Project ;
        if( ! empty( $this->pid ) ) {
            $project_detail = $project->get_project_by_id( $this->pid ) ;
            $this->assign('project_detail' , $project_detail );
        } else {
            $offset = ($this->page - 1)  * $this->project_limit ;
            $projects = $project->get_project_by_category( $this->did, $offset , $this->project_limit );
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
        
        if( $this->pid ) {
            $news_detail = $News->get_news_by_id( $this->pid ) ;
            $this->assign('news_detail' , $news_detail );
        } else {
            $news = $News->get_news_by_category( $this->cid, 0, $this->news_limit ) ;
            foreach ($news as &$n ) {
                $n ['img_url'] = empty($n ['img_url']) ? '/static/img/20220811145853.jpg' : $n ['img_url'];
            }
            $this->assign('news' , $news );
        }

    }

    public function _get_cooperate_info() {
        $Cooperate = new Cooperate;
        $where = " `status` = 'A' ";
        $cooperates = $Cooperate->get_cooperate($where, 'id desc', 0, 100) ;
        $this->assign('cooperates' , $cooperates );
    }
}