<?php
function mygetdatabase($name,$content)
{
	$temp=strcut($content,"'".$name."'",",",2);
	if($temp!="")$temp=strcut($temp,"'","'",2);
	return $temp;
}

function mysetconfig($name,$value,$content)
{
	$temp=strcut($content,$name,"\r\n",1);

	if($temp!="")
	{
		$temp1=$name."=".$value."\r\n";
		$content=str_replace($temp,$temp1,$content);
	}
	return $content;
}

function mygetconfig($name,$content)
{
	$value=strcut($content,$name,"\r\n",1);

	if($value!="")
	{
		$value=strcut($value,"=","\r\n",2);
	}
	return $value;
}

?>