<?php
//读取文件
function rfile($filename)
{
	try 
	{
		$myfile = fopen($filename, "r");
		$retval=fread($myfile,filesize($filename));
		return $retval;
	}
	catch (\Exception $e)
	{
		return "";
    }
}

//保存文件
function sfile($filename,$content)
{
	return file_put_contents($filename,$content);
}

//检查目录是否存在，不存在则建立目录
function checkdir($sPath)
{
	$dir=$sPath;
	if(!is_dir($dir))
	{
		if(isblank($dir)==false)
		{
			$sdir=substr($dir,0,strrpos($dir,"/"));
			checkdir($sdir);
			mkdir($dir);
		}
	}
}