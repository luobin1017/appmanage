<?php
	function myupload_maxsize($size="")
	{
		if($size==""){return cookie('upload_size','');}
		else{cookie('upload_size',$size);}
	}
	
	function myupload_fileext($ext="")
	{
		if($ext==""){cookie('upload_ext');}
		else{cookie('upload_ext',$ext);}
	}

	//上传文件，返回值说明，0，上传成功，1，上传失败，2，文件类型不对，3，文件超过指定尺寸
	function myupload_save($file, $savefilename)
	{
		//global $maxsize,$fileext;
		if ($_FILES[$file]["error"] > 0)
		{
			return 1;
		}
		//echo $_FILES["file"]["size"];
		$ext=myupload_get_extension($file);
		if(myupload_fileext()!=""&&strpos(myupload_fileext(),"|".strtoupper($ext)."|") === false){return 2;}
		if(myupload_maxsize()>0&&$_FILES[$file]["size"]>myupload_maxsize()){return 3;}
		//mydump($_FILES[$file]["tmp_name"].$savefilename);
		//$savefilename="1.jpg";
		//echo $savefilename;
		$savefilename=$savefilename;
		move_uploaded_file($_FILES[$file]["tmp_name"],$savefilename);
		return 0;
	}
	
	//获取文件名称
	function myupload_get_filename($file)
	{
		return $_FILES[$file]["name"];
	}
	//获取文件扩展名
	function myupload_get_extension($file)
	{
		$temp=explode(".", $_FILES[$file]["name"]);
		$fileext=end($temp);
		return $fileext;
	}
	
	//生成随机文件名
	function myupload_randomname($file, $chars = '0123456789')
	{
		$length=4;
		$ext = myupload_get_extension($file);
		$hash = date("YmdHi");
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++)
		{
			$hash .= $chars[mt_rand(0, $max)];
		}
		return $hash.".".$ext;
	}
?>