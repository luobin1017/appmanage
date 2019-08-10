<?php
// +----------------------------------------------------------------------
// | API接口
// +----------------------------------------------------------------------
namespace app\api\controller;
use think\Controller;

class Test extends Controller
{
    public function index(){
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_SYNC);
        $ret = $client->connect("0.0.0.0", 9501);
        if(empty($ret)){
            echo 'error!connect to swoole_server failed';
        } else {
            $client->send('blue');
        }
    }

}