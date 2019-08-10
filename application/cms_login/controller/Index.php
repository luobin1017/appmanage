<?php
namespace app\cms_login\controller;
use think\Controller;
use think\Validate;
use think\Db;

class Index extends Controller
{
    public function index()
    {
		return $this->fetch();
    }
	
	 public function login()
    {
		$data = input('post.');

		/*$rules = [
				'username' => 'require',
                'password' => 'require',
            ];
            $message = [
                'username.require' => '必须输入用户名！',
				'password.require' => '必须输入密码！',
            ];
		$validate = new Validate($rules, $message);
		if (!$validate->check($data))// 验证不通过
		{
			return "error:".$validate->getError();
			exit(0);
		}*/
		$rs=Db::name('cms_admin')->where('username', $data["username"])->where('password',md5($data["password"]))->select();
		$num_rows=count($rs);
		if($num_rows<=0)
		{
			echo  json_encode(array("code"=>100));
			//return "error:用户登录信息错误，请返回重新输入！";
			exit(0);
		}
		else
		{	
			//更新数据库中记录
			$updata = [
                'lastlogin'=>'',
				'lastip'=>'',
                'thislogin'=>'',
				'thisip'=>'',
				'logincode'=>''
            ];
			//dump($result);
			$updata['lastlogin']=$rs[0]["thislogin"];
			$updata['lastip']=$rs[0]["thisip"];
			$updata['thislogin']=date('Y-m-d H:i:s', time());
			$updata['thisip']=getip();
			$updata['logincode']=md5($updata['thislogin']);
			$result = db('cms_admin')->where('username',$data["username"])->update($updata);
			if($result)
			{
				//设置Cookies并中转到TMS首页
				cookie('username',$data["username"]);
				cookie('code',$updata["logincode"]);
				cookie('user_permissions_group',$rs[0]['permissions_group']);
				return ["code"=>200];
			}else
			{
				return "error:更新登录信息失败，请与管理员联系！";
			}
		}
    }
	
	public function logout()
    {
		cookie('username', null);
		cookie('jobs',null);
		cookie('code',null);
		cookie('user_permissions_group',null);
		gologin();
	}
	
}