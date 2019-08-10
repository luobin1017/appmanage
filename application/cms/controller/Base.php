<?php
namespace app\tms\controller;
use think\Controller;
use think\Db;
use think\Cache;

class Base extends Controller
{

    public $option;//产品分类下拉选项

    public $product_search;//

    public function _initialize()
    {
        if (Cache::get('product_option')) {
            $this->option = Cache::get('product_option');
            //dump($this->option);
        }else{
			$list=array();
			$list=getpclassarray("bts_product_class",0,"id","enclassname,cnclassname",$list,0);
            //缓存所有产品类型下拉菜单
            Cache::set('product_option', $list, 3600 * 24);
        }
    }
}
