<?php
namespace app\cms\controller;
use think\Controller;
use think\Db;
use app\cms\controller\Mail;
use PHPExcel;

class Userinfo extends Controller
{
    public function index()
    {
		/*$menu=getmenu(0);
		$this->assign('menu',$menu);*/
        if(checkpermissions_b('1002')==false)
        {
            return $this->fetch('public/hint');
        }
        if(isset(input()['brandcode'])){
            cookie('brandcode',input()['brandcode']);
        }

        //搜索条件
        /*$key=array("0"=>'中国','1'=>'美国','2'=>'英国');
        for($i=0;$i<10;$i++){
            $data = array(
                'email'=>rand('100000000000','999999999999').'@qq.com',
                'password'=>'e10adc3949ba59abbe56e057f20f883e',
                'location'=>$key[rand(0,2)],
                'regtime'=>date("Y-m-d H:i:s",strtotime("-".rand('1','5')."years",time())),
                'brandcode'=>rand('1','3'),
                'isemail'=>rand('0','1'),
            );
            db('app_customer')->insert($data);
        }
        mydump($data);*/
        //dump(cookie('brandcode'));
        $input = input();
        $sqlwhere=array("","");
        $email=input("email");
        $sqlwhere=sqlwhereand("邮箱",$sqlwhere,"email",$email,1,1);
        $country=input("country");
        $sqlwhere=sqlwhereand("国家",$sqlwhere,"country",$country,1,1);
        $isemail=input("isemail");
        $sqlwhere=sqlwhereand("接收状态",$sqlwhere,"isemail",$isemail,0,1);
        $makedate1=input("makedate1");
        $makedate2=input("makedate2");
        $sqlwhere=sqlwheredate("反馈日期",$sqlwhere,"regtime",$makedate1,$makedate2);
        $pagesize = 10;
        //dump($sqlwhere);
        //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
        $subsql1 = db('country_mobile_prefix')->field('country,mobile_prefix,area')->group('mobile_prefix')->buildSql();
        //mydump($subsql1);

        $list = db('app_customer')->field('id,email,location,isemail,intime,brandcode,cc.country,cc.mobile_prefix,cc.area')->alias('c')->leftJoin([$subsql1=>'cc'],"c.location=cc.mobile_prefix")
            ->where('brandcode',$_COOKIE['brandcode'])->where($sqlwhere[0])->order("id desc")->paginate($pagesize, false,
                ['query' => request()->param(),
                    'type'     => 'layui',
                    'var_page' => 'page',]);
        //mydump($list);
        $page = $list->render();
        //  页数量
        $this->assign('pagesize',$pagesize);
        //  总数据
        $this->assign('total',$list->total());
        //  总页数
        $total= ceil($list->total() / $pagesize);
        $this->assign('totalPage', $total);
        //  当前页
        $pagenow =input('page', 1);
        $this->assign('pagenow', $pagenow);
        $this->assign('arr',$list);
        $this->assign('sqlwhere', $sqlwhere[1]);
        $this->assign('sqlandwhere', $sqlwhere[0]);
        $this->assign('page', $page);
		return $this->fetch();
    }
    //导出excel
    public function daochu(){
        $sqlwhere=array("","");
        $email=input("email");
        $email = input("email")==200?'':input("email");
        $sqlwhere=sqlwhereand("邮箱",$sqlwhere,"email",$email,1,1);
        $location=input("location");
        $location = input("location")==200?'':input("location");
        $sqlwhere=sqlwhereand("国家",$sqlwhere,"location",$location,1,1);
        $isemail=input("isemail");
        $isemail = input("isemail")==200?'':input("isemail");
        $sqlwhere=sqlwhereand("接收状态",$sqlwhere,"isemail",$isemail,0,1);
        $makedate1=input("makedate1");
        $makedate2=input("makedate2");
        $makedate1 = input("makedate1")==200?'':input("makedate1");
        $makedate2 = input("makedate2")==200?'':input("makedate2");
        $sqlwhere=sqlwheredate("反馈日期",$sqlwhere,"regtime",$makedate1,$makedate2);
        //mydump($sqlwhere);
        //$list = Db::table("bts_pi")->order("no desc")->paginate($pagesize, false, ['query' => request()->param(),]);
        $xlsData = db('app_customer')->where('brandcode',$_COOKIE['brandcode'])->where($sqlwhere[0])->select();
        //$xlsData = Db('app_customer')->select();
        require_once 'extend/Classes/PHPExcel.php';
        //require_once 'vendor/phpqrcode/phpqrcode.php';
        /*Vendor('Classes.PHPExcel');//调用类库,路径是基于vendor文件夹的
        Vendor('Classes.PHPExcel.Worksheet.Drawing');
        Vendor('Classes.PHPExcel.Writer.Excel2007');*/
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');

        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I,J");
        $arrHeader = array('邮箱','IP注册时所在国家','注册时间','接受邮件');
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        };
        //填充表格信息
        foreach($xlsData as $k=>$v){
            $k +=2;
            $objActSheet->setCellValue('A'.$k,$v['email']);
            $objActSheet->setCellValue('B'.$k, $v['location']);
            $objActSheet->setCellValue('C'.$k, $v['regtime']);
            $objActSheet->setCellValue('D'.$k, $v['isemail'] == 1?'是':'否');
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
        $width = array(10,15,20,25,30);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth($width[4]);
        $objActSheet->getColumnDimension('B')->setWidth($width[2]);
        $objActSheet->getColumnDimension('C')->setWidth($width[2]);
        $objActSheet->getColumnDimension('D')->setWidth($width[2]);
        /*$objActSheet->getColumnDimension('E')->setWidth($width[1]);
        $objActSheet->getColumnDimension('F')->setWidth($width[1]);
        $objActSheet->getColumnDimension('G')->setWidth($width[1]);
        $objActSheet->getColumnDimension('H')->setWidth($width[1]);
        $objActSheet->getColumnDimension('I')->setWidth($width[1]);*/
        $outfile = "用户信息列表.xlsx";
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }
    public function daochupart()
    {
        $get = $_GET['allid'];
        $newget = trim($get,',');
        $sql = "SELECT * from app_customer WHERE  id IN ({$newget})";
        $xlsData = db('app_customer')->query($sql);
        require_once 'extend/Classes/PHPExcel.php';
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I,J");
        $arrHeader = array('邮箱','IP注册时所在国家','注册时间','接受邮件');
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        };
        //填充表格信息
        foreach($xlsData as $k=>$v){
            $k +=2;
            $objActSheet->setCellValue('A'.$k,$v['email']);
            $objActSheet->setCellValue('B'.$k, $v['location']);
            $objActSheet->setCellValue('C'.$k, $v['regtime']);
            $objActSheet->setCellValue('D'.$k, $v['isemail'] == 1?'是':'否');
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
        $width = array(10,15,20,25,30);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth($width[4]);
        $objActSheet->getColumnDimension('B')->setWidth($width[2]);
        $objActSheet->getColumnDimension('C')->setWidth($width[2]);
        $objActSheet->getColumnDimension('D')->setWidth($width[2]);
        /*$objActSheet->getColumnDimension('E')->setWidth($width[1]);
        $objActSheet->getColumnDimension('F')->setWidth($width[1]);
        $objActSheet->getColumnDimension('G')->setWidth($width[1]);
        $objActSheet->getColumnDimension('H')->setWidth($width[1]);
        $objActSheet->getColumnDimension('I')->setWidth($width[1]);*/
        $outfile = "用户信息列表.xlsx";
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }

    //用户图标显示
    public function picshow()
    {
        if(checkpermissions_b('1005')==false)
        {
            return $this->fetch('public/hint');
        }

        //获取上周起始时间戳和结束时间戳
        $beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
        $endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
        //上周用户增长量
        $montime_start_last = $beginLastweek;$montime_end_last = mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
        $tuetime_start_last = strtotime('+1 days',$beginLastweek);$tuetime_end_last = strtotime('+2 days',$beginLastweek)-1;
        $webtime_start_last = strtotime('+2 days',$beginLastweek);$webtime_end_last = strtotime('+3 days',$beginLastweek)-1;
        $thutime_start_last = strtotime('+3 days',$beginLastweek);$thutime_end_last = strtotime('+4 days',$beginLastweek)-1;
        $fritime_start_last = strtotime('+4 days',$beginLastweek);$fritime_end_last = strtotime('+5 days',$beginLastweek)-1;
        $statime_start_last = strtotime('+5 days',$beginLastweek);$statime_end_last = strtotime('+6 days',$beginLastweek)-1;
        $suntime_start_last = strtotime('+6 days',$beginLastweek);$suntime_end_last = strtotime('+7 days',$beginLastweek)-1;

        //mydump($tuetime_start);
        $lrs1 = db('app_customer')->where("regtimestrap between $montime_start_last and $montime_end_last")->count();
        $lrs2 = db('app_customer')->where("regtimestrap between $tuetime_start_last and $tuetime_end_last")->count();
        $lrs3 = db('app_customer')->where("regtimestrap between $webtime_start_last and $webtime_end_last")->count();
        $lrs4 = db('app_customer')->where("regtimestrap between $thutime_start_last and $thutime_end_last")->count();
        $lrs5 = db('app_customer')->where("regtimestrap between $fritime_start_last and $fritime_end_last")->count();
        $lrs6 = db('app_customer')->where("regtimestrap between $statime_start_last and $statime_end_last")->count();
        $lrs7 = db('app_customer')->where("regtimestrap between $suntime_start_last and $suntime_end_last")->count();

        //获取本周起始时间戳和结束时间戳
        $start_time = mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));
        $end_time = mktime(23,59,59,date('m'),date('d')-date('w')+7,date('Y'));
        //最近一周用户增长量
        $time = time();//1558937143 regtimestrap between 1558938221 and 1559543021 1559138221
        $montime_start = $start_time;$montime_end = mktime(23,59,59,date('m'),date('d')-date('w')+1,date('Y'));
        $tuetime_start = strtotime('+1 days',$montime_start);$tuetime_end = strtotime('+2 days',$montime_start)-1;
        $webtime_start = strtotime('+2 days',$montime_start);$webtime_end = strtotime('+3 days',$montime_start)-1;
        $thutime_start = strtotime('+3 days',$montime_start);$thutime_end = strtotime('+4 days',$montime_start)-1;
        $fritime_start = strtotime('+4 days',$montime_start);$fritime_end = strtotime('+5 days',$montime_start)-1;
        $statime_start = strtotime('+5 days',$montime_start);$statime_end = strtotime('+6 days',$montime_start)-1;
        $suntime_start = strtotime('+6 days',$montime_start);$suntime_end = strtotime('+7 days',$montime_start)-1;

        //mydump($tuetime_start);
        $rs1 = db('app_customer')->where("regtimestrap between $montime_start and $montime_end")->count();
        $rs2 = db('app_customer')->where("regtimestrap between $tuetime_start and $tuetime_end")->count();
        $rs3 = db('app_customer')->where("regtimestrap between $webtime_start and $webtime_end")->count();
        $rs4 = db('app_customer')->where("regtimestrap between $thutime_start and $thutime_end")->count();
        $rs5 = db('app_customer')->where("regtimestrap between $fritime_start and $fritime_end")->count();
        $rs6 = db('app_customer')->where("regtimestrap between $statime_start and $statime_end")->count();
        $rs7 = db('app_customer')->where("regtimestrap between $suntime_start and $suntime_end")->count();
        //dump(Db::table('app_customer')->getLastSql());
        //mydump($rs);

        //最近月周用户增长量
        $oneweek =  strtotime('-7 days',time());
        $twoweek =  strtotime('-14 days',time());
        $threeweek =  strtotime('-21 days',time());
        $fourweek =  strtotime('-28 days',time());
        $week1 = db('app_customer')->where("regtimestrap between $oneweek and $time")->count();
        $week2 = db('app_customer')->where("regtimestrap between $twoweek and $time")->count();
        $week3 = db('app_customer')->where("regtimestrap between $threeweek and $time")->count();
        $week4 = db('app_customer')->where("regtimestrap between $fourweek and $time")->count();

        //最近一季用户增长量
        $onequarter =  strtotime('-30 days',time());
        $twoquarter =  strtotime('-60 days',time());
        $threequarter =  strtotime('-90 days',time());
        $quarter1 = db('app_customer')->where("regtimestrap between $onequarter and $time")->count();
        $quarter2 = db('app_customer')->where("regtimestrap between $twoquarter and $time")->count();
        $quarter3 = db('app_customer')->where("regtimestrap between $threequarter and $time")->count();

        //最近半年用户增长量
        $oneyear =  strtotime('-30 days',time());
        $twoyear =  strtotime('-60 days',time());
        $threeyear =  strtotime('-90 days',time());
        $fouryear =  strtotime('-120 days',time());
        $fiveyear =  strtotime('-150 days',time());
        $sixyear =  strtotime('-180 days',time());
        $year =  strtotime('-365 days',time());
        $year1 = db('app_customer')->where("regtimestrap between $oneyear and $time")->count();
        $year2 = db('app_customer')->where("regtimestrap between $twoyear and $time")->count();
        $year3 = db('app_customer')->where("regtimestrap between $threeyear and $time")->count();
        $year4 = db('app_customer')->where("regtimestrap between $fouryear and $time")->count();
        $year5 = db('app_customer')->where("regtimestrap between $fiveyear and $time")->count();
        $year6 = db('app_customer')->where("regtimestrap between $sixyear and $time")->count();
        $year = db('app_customer')->where("regtimestrap between $year and $time")->count();

        //百分比
        $percent = $week1+$quarter1+$quarter3+$year6+$year;
        $percent1 = $week1/$percent;
        $percent2 = $quarter1/$percent;
        $percent3 = $quarter3/$percent;
        $percent4 = $year6/$percent;
        $percent5 = $year/$percent;
        //mydump($percent1.'--'.$percent2.'--'.$percent3.'--'.$percent4.'--'.$percent5);
        //
        $this->assign('rs1',$rs1);
        $this->assign('rs2',$rs2);
        $this->assign('rs3',$rs3);
        $this->assign('rs4',$rs4);
        $this->assign('rs5',$rs5);
        $this->assign('rs6',$rs6);
        $this->assign('rs7',$rs7);
        //
        $this->assign('lrs1',$lrs1);
        $this->assign('lrs2',$lrs2);
        $this->assign('lrs3',$lrs3);
        $this->assign('lrs4',$lrs4);
        $this->assign('lrs5',$lrs5);
        $this->assign('lrs6',$lrs6);
        $this->assign('lrs7',$lrs7);
        //
        $this->assign('week1',$week1);
        $this->assign('week2',$week2);
        $this->assign('week3',$week3);
        $this->assign('week4',$week4);
        //
        $this->assign('quarter1',$quarter1);
        $this->assign('quarter2',$quarter2);
        $this->assign('quarter3',$quarter3);
        //
        $this->assign('year1',$year1);
        $this->assign('year2',$year2);
        $this->assign('year3',$year3);
        $this->assign('year4',$year4);
        $this->assign('year5',$year5);
        $this->assign('year6',$year6);
        $this->assign('year',$year);
         //
        $this->assign('percent1',round($percent1*100,2).'%');
        $this->assign('percent2',round($percent2*100,2).'%');
        $this->assign('percent3',round($percent3*100,2).'%');
        $this->assign('percent4',round($percent4*100,2).'%');
        $this->assign('percent5',round($percent5*100,2).'%');

        return $this->fetch();
    }

}