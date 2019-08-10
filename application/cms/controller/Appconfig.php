<?php
namespace app\cms\controller;
use think\Controller;

class Appconfig extends Controller
{
    public function index()
    {

		return $this->fetch();
    }

}