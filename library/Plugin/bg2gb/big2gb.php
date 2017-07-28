<?php
// <copyright file="Big2gb.php" company="Www.LeTV.com">
//     Copyright (c) LeTV.com. All rights reserved.
// </copyright>
// <summary>简繁体转换</summary>
// <Create Author="wxliu" Time="2012.01.06"></Create>
// <Revision>1.0</Revision>
// <Modify>
//  wxliu 2012.01.06 创建
// </Modify>

/*
require_once "bg2gb/Big2gb.php";    
$big2gb     =new Big2gb;  
$string_big5 = '你好嗎我愛你hello';
//繁体 转换 简体
echo $big2gb->chg_utfcode($string_big5,'gb2312');
//简体 转换 繁体
echo $big2gb->chg_utfcode('看看电影也不错','big5');
*/

define("_FILE_DIR" , dirname(__FILE__) );
 class big2gb{
	function chg_utfcode($str,$charset='big5'){
		if ($charset=='big5')
		{
			$fd = fopen(_FILE_DIR."/gb2big.map",'r');
			$str1 = fread($fd,filesize(_FILE_DIR."/gb2big.map"));
		}
		else 
		{
			$fd = fopen(_FILE_DIR."/big2gb.map",'r');
			$str1 = fread($fd,filesize(_FILE_DIR."/big2gb.map"));
		}
		fclose($fd);
		
		// convert to unicode and map code
		$chg_utf = array();
		for ($i=0;$i<strlen($str1);$i=$i+4)
		{
			$ch1=ord(substr($str1,$i,1))*256;
			$ch2=ord(substr($str1,$i+1,1));
			$ch1=$ch1+$ch2;
			$ch3=ord(substr($str1,$i+2,1))*256;
			$ch4=ord(substr($str1,$i+3,1));
			$ch3=$ch3+$ch4;
			$chg_utf[$ch1]=$ch3;
		}
		
		// convert to UTF-8
		$outstr='';
		for ($k=0;$k<strlen($str);$k++)
		{
			$ch=ord(substr($str,$k,1));
			if ($ch<0x80)
			{
				$outstr.=substr($str,$k,1);
			}
			else
			{
				if ($ch>0xBF && $ch<0xFE)
				{
					if ($ch<0xE0) {
						$i=1;
						$uni_code=$ch-0xC0;
					} elseif ($ch<0xF0)	{
						$i=2;
						$uni_code=$ch-0xE0;
					} elseif ($ch<0xF8)	{
						$i=3;
						$uni_code=$ch-0xF0;
					} elseif ($ch<0xFC)	{
						$i=4;
						$uni_code=$ch-0xF8;
					} else {
						$i=5;
						$uni_code=$ch-0xFC;
					}
				}
	
				$ch1=substr($str,$k,1);
				for ($j=0;$j<$i;$j++)
				{
					$ch1 .= substr($str,$k+$j+1,1);
					$ch=ord(substr($str,$k+$j+1,1))-0x80;
					$uni_code=$uni_code*64+$ch;
				}
				
				if (!$chg_utf[$uni_code])
				{
					$outstr.=$ch1;
				}
				else
				{
					$outstr.=$this->uni2utf($chg_utf[$uni_code]);
				}
				$k += $i;
			}
		}
		return $outstr;
	}

	// Return utf-8 character
	function uni2utf($uni_code)
	{
		if ($uni_code<0x80) return chr($uni_code);
		$i=0;
		$outstr='';
		while ($uni_code>63) // 2^6=64
		{
			$outstr=chr($uni_code%64+0x80).$outstr;
			$uni_code=floor($uni_code/64);
			$i++;
		}
		switch($i)
		{
			case 1:
				$outstr=chr($uni_code+0xC0).$outstr;break;
			case 2:
				$outstr=chr($uni_code+0xE0).$outstr;break;
			case 3:
				$outstr=chr($uni_code+0xF0).$outstr;break;
			case 4:
				$outstr=chr($uni_code+0xF8).$outstr;break;
			case 5:
				$outstr=chr($uni_code+0xFC).$outstr;break;
			default:
				echo "unicode error!!";exit;
		}
		return $outstr;
	}
}