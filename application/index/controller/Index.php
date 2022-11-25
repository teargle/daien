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
use app\index\model\Message;
use app\index\model\I18n;
use app\index\model\Homeproduct;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Index extends Controller
{
    // 是否首页
    private $homepage = 1;
    // 是否是产品中心首页
    private $product_center = 0 ;
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
    private $product_limit = 20;
    // 工程案例每页展示个数
    private $project_limit = 9;
    // 新闻展示数量
    private $news_limit = 5;
    // 当前语言
    private $language = 'zh-cn';

    private $product_category = 2;
    //搜索产品
    private $search = "";

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
        $this->homepage = empty($_GET['cid']) ? 1 : 0;
        $this->product_center = !empty( $_GET['cid'] ) && $_GET['cid'] == 2 && empty($_GET['did']) ? 1 : 0;
        $this->cid = !empty($_GET['cid']) ? $_GET['cid'] : 2 ;
        $this->tid = !empty($_GET['tid']) ? $_GET['tid'] : 0 ;
        $this->pid = !empty($_GET['pid']) ? $_GET['pid'] : 0 ;
        $this->did = !empty($_GET['did']) ? $_GET['did'] : 14 ;
        $this->page = !empty($_GET['page']) ? $_GET['page'] : 1 ;

        $this->search = !empty($_GET['search']) ? $_GET['search'] : "" ;

        // 默认值
        $this->assign('homepage' , $this->homepage );
        $this->assign('product_center' , $this->product_center );
        $this->assign('tid' , $this->tid );
        $this->assign('did' , $this->did );
        $this->assign('cid' , $this->cid );
        $this->assign('page' , $this->page );
        $this->assign('search' , $this->search );
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
        $this->_get_banner();
        $this->_get_company_info() ;
        $this->_get_category_info() ;
        $this->_get_product_by_category() ;
        $this->_get_intro_info() ;

        $this->_get_project_info() ;
        $this->_get_news_info() ;
        $this->_get_cooperate_info() ;
        return $this->fetch('index');
    }

    private function _get_banner() {
        $banners = [
            '/static/img/banner_0.jpg',
            '/static/img/banner_1.jpg',
            '/static/img/banner_2.jpg',
            '/static/img/banner_3.jpg'
        ] ;
        $this->assign('banners' , $banners);
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

        $webs = explode(";", $home ['web']['value']) ;
        $home ['web']['values'] = $webs;
        
        $this->assign('home' , $home);

    }

    private function _get_category_info() {
        $category = new Category ;
        $i18n = new I18n ;

        $cations = [] ;
        if( $this->cid == $this->product_category ) {
            $cates = $category->get_all_with_products( $this->cid ) ;
            if( $cates ) {
                $i18n->replace_info( $cates, 'dn_category', $this->language, 'title' ) ;
            }
            
            foreach( $cates as $cate ) {
                if( $cate ['parent'] == $this->product_category ) {
                    $cations [$cate ['id']] = $cate ;
                } else {
                    if( empty($cations [$cate ['parent']] ['sub']) ) {
                        $cations [$cate ['parent']] ['sub'] = [];
                    } 
                    array_push($cations [$cate ['parent']] ['sub'], $cate);
                }
            }
        } else {
            $cates = $category->get_category( $this->cid ) ;
            if( $cates ) {
                $i18n->replace_info( $cates, 'dn_category', $this->language, 'title' ) ;
            }
            $cations = $cates;
        }
        $this->assign('cates' , $cations);

        
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

    private function _get_product_by_category () {
        if( $this->cid != 2 ) {
            return true ;
        }
        $I18n = new I18n;
        $Product = new Product ;
        $offset = ($this->page - 1)  * $this->product_limit ;
        if( ! empty( $this->pid ) ) {
            $product_detail = $Product->get_product_by_id( $this->pid ) ;
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
            // 记录点击次数
            $Product->update_product_pv( $this->pid ) ;
            $this->assign('product_detail' , $product_detail );
        } else {
            
            $category = new Category ;
            $cate = [] ;
            if( !empty($this->did) && $this->did != 14 && $this->did != -1) {
                // 14 表示首页， -1 表示推荐商品
                $cate = $category->get_category_info( $this->did );
            }

            $total = 0 ;
            if( $cate && $cate['parent'] == $this->product_category) {
                $cates = $category->get_category( $this->did ) ;
                $cate_ids = array_column( $cates, "id" ) ;
                
                $projects = [];
                if( $cate_ids ) {
                    $products = $Product->get_product_by_categorys( $cate_ids, $offset , $this->product_limit );
                    $total = $Product->get_product_num_by_categorys( $cate_ids );
                }
            } else if ( !empty($this->search) ) {
                $products = $Product->get_product_by_title ( $this->search );
                $m_ids = array_column($products, 'id') ;
                $products = $I18n->get_product_with_i18n_by_title ( $this->search );
                $n_ids = array_column($products, 'target_id') ;
                $ids = array_merge($m_ids, $n_ids) ;
                $ids = array_unique($ids) ;
                $products = $Product->get_product_by_ids ($ids, $offset , $this->product_limit) ;

                $total = count($ids);
            } else if( $this->product_center == 1 ) {
                $products = $Product->get_product_by_recommend ( $offset , $this->product_limit );
                $total = $Product->get_count ( ['status' => 'A'] );
            } else if ( $this->did == 14 ) {
                // 约定： 产品分类不会小于45， 如果有那么一定是14. 即首页
                $News = new News;
                $where = ['status' => 'A' ] ;
                $news = $News->get_news($where, '', 0, 2 ) ;
                if( $this->language != 'zh-cn' && $news ) {
                    $I18n->replace_info ($news, 'dn_news', $this->language, 'title' ) ;
                    $I18n->replace_info ($news, 'dn_news', $this->language, 'description' ) ;
                }
                $Homeproduct = new Homeproduct;
                $homeproducts = $Homeproduct->get_homeproduct ( 0, 100 );
                if( $this->language != 'zh-cn' && $homeproducts ) {
                    $I18n->replace_info ($homeproducts, 'dn_homeproduct', $this->language, 'title' ) ;
                    $I18n->replace_info ($homeproducts, 'dn_homeproduct', $this->language, 'description' ) ;
                }
                 // 产品的子产品图
                $Product = new Product;
                foreach ( $homeproducts as &$hp ) {
                    if( ! empty( $hp ['product_ids'] ) ) {
                        $products = $Product->get_product_by_ids( explode(",", $hp ['product_ids'] ) , 0, 100 ) ;
                        if( $products ) {
                            $hp ['imgs'] = [] ;
                            foreach( $products as $p ) {
                                array_push($hp ['imgs'], [
                                    'url' => '?cid=2&did=' . $p ['category_id'] . '&pid=' . $p ['id'],
                                    'img' => $p ['img_url']
                                ]) ;
                            }
                        }
                    }
                }
                $homearea = [
                    'products' => $homeproducts,
                    'num' => count($homeproducts),
                    'news' => $news
                ] ;
                $this->assign( 'homearea' , $homearea );
                $products = [] ;
            } else {
                // 小分类 查询该类所有商品
                $products = $Product->get_product_by_category ( $this->did, $offset , $this->product_limit );
                $total = $Product->get_product_num_by_category ( $this->did );
                // 查询该类是否有介绍信息
                $Homeproduct = new Homeproduct;
                $homeproduct = $Homeproduct->get_homeproduct_by_category_id( $this->did );
                $homeproducts = [$homeproduct] ; 
                if( $this->language != 'zh-cn' && $homeproduct ) {
                    $I18n->replace_info ( $homeproducts, 'dn_homeproduct', $this->language, 'title' ) ;
                    $I18n->replace_info ( $homeproducts, 'dn_homeproduct', $this->language, 'description' ) ;
                }
                if ( $homeproduct && $homeproducts ) {
                    $this->assign('homeproduct' , array_slice($homeproducts, 0, 1) );
                } else {
                    $this->assign('homeproduct' , [ 'img_url' => '' , 'description' => ''] );
                }
            }
            
            if( $this->language != 'zh-cn' && $products ) {
                $I18n->replace_info ($products, 'dn_product', $this->language, 'title' ) ;
                $I18n->replace_info ($products, 'dn_product', $this->language, 'description' ) ;
            }

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
        $intro ['description'] = str_replace( "\\", "", $intro ['description']) ;
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

            $total = $project->get_project_num_by_category( $this->did );
        
            if( $this->language != 'zh-cn' && !empty($projects) ) {
                $I18n = new I18n;
                $I18n->replace_info ($projects, 'dn_project', $this->language, 'title' ) ;
                $I18n->replace_info ($projects, 'dn_project', $this->language, 'description' ) ;
            }
            
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
            if( $this->language != 'zh-cn' && $news_detail ) {
                $i18n_info = $I18n->get_info ( 'dn_news', $this->language, 'title', $news_detail ['id']  ) ;
                $news_detail ['title'] = $i18n_info ? $i18n_info ['text'] : $news_detail ['title'];
                $i18n_info = $I18n->get_info ( 'dn_news', $this->language, 'description', $news_detail ['id']  ) ;
                $news_detail ['description'] = $i18n_info ? $i18n_info ['text'] : $news_detail ['description'];
            }
            $this->assign('news_detail' , $news_detail );
        } else {
            $news = $News->get_news_by_category( $this->cid, 0, $this->news_limit ) ;

            if( $this->language != 'zh-cn' && $news ) {
                foreach ( $news as &$n ) {
                    $i18n_info = $I18n->get_info ( 'dn_news', $this->language, 'title', $n ['id']  ) ;
                    $n ['title'] = $i18n_info ? $i18n_info ['text'] : $n ['title'];
                    $i18n_info = $I18n->get_info ( 'dn_news', $this->language, 'description', $n ['id']  ) ;
                    $n ['description'] = $i18n_info ? $i18n_info ['text'] : $n ['description'];
                }
            }

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

    public function message() {
        $request = Request::instance();
        $post = $request->post();

        $linkinfo = empty($post ['linkinfo']) ? "" : $post ['linkinfo'];
        $msginfo = empty($post ['msginfo']) ? "" : $post ['msginfo'];
        $ip = $request->ip();

        $Message = new Message;
        $info = $Message->get_info_by_ip( $ip );

        if( $info && strtotime($info ['create_time']) + 1 > time() ) {
            echo $this->output_json ( false , "不要频繁留言", $post) ;
            exit ;
        }
        $msg_id = $Message->saveInfo( $linkinfo, $msginfo, $ip );
        $error = $this->_send_email( "from : " . $linkinfo . "<br/>message : " . $msginfo ) ;
        echo $this->output_json ( true , $error, $post) ;
    }

    public function _send_email($content) {
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.163.com';                         //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'teargle@163.com';                     //SMTP username
            $mail->Password   = 'JUFDNJODJLBHSPRC';                    //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('teargle@163.com', 'xionglei' );
            $mail->addAddress( 'shdaien@163.com', 'fory' ) ;
            $mail->addAddress( 'xiong.lei@juneben.com', 'teargle');     //Add a recipient

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = '[important] a carstom from web';
            $mail->Body    = $content;

            $mail->send();
        } catch (Exception $e) {
            return print_r( $e );
        }
    }

    public function get_products_by_cate_id (  ) {
        $request = Request::instance();
        $post = $request->post();

        $category_id = $post ['did'];
        $page = empty($post ['page']) ? $post ['page'] - 1 : 0;
        if( empty($category_id) ) {
            echo $this->output_json ( true , "没有分类", null) ;
        }

        $Category = new Category;
        $Product = new Product;
        $I18n = new I18n;
        $homeproduct = [] ;
        if( $category_id == -1 ) {
            $products = $Product->get_product_by_recommend( $page , $this->product_limit );
            $total = $Product->get_count( ['status' => 'A'] );
        } else if( $category_id ) {
            $cates = $Category->get_category( $category_id ) ;
            $cate_ids = array_column( $cates, "id" ) ;
            
            $products = [ ];
            if( $cate_ids ) {
                $products = $Product->get_product_by_categorys( $cate_ids, $page , $this->product_limit );
                $total = $Product->get_product_num_by_categorys( $cate_ids );
            } else {
                $products = $Product->get_product_by_category ( $category_id, $page , $this->product_limit );
                $total = $Product->get_product_num_by_category ( $category_id );
            }

            $Homeproduct = new Homeproduct;
            $homeproduct = $Homeproduct->get_homeproduct_by_category_id( $category_id );
            $homeproducts = [$homeproduct] ; 
            if( $this->language != 'zh-cn' && $homeproduct ) {
                $I18n->replace_info ( $homeproducts, 'dn_homeproduct', $this->language, 'title' ) ;
                $I18n->replace_info ( $homeproducts, 'dn_homeproduct', $this->language, 'description' ) ;
            }
        }

        if( $this->language != 'zh-cn' && $products ) {
            $I18n->replace_info ($products, 'dn_product', $this->language, 'title' ) ;
            $I18n->replace_info ($products, 'dn_product', $this->language, 'description' ) ;
        }

        echo $this->output_json ( true , "", [
            'products' => $products,
            'page' => $page,
            'total' => $total,
            'total_page' => ceil($total / $this->product_limit ),
            'homeproduct' => $homeproduct
        ]) ; 
    }
}