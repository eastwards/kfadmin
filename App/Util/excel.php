<?php
class Excel
{
	/**
	  * 
	  * 生成Excel 表格
	  * @param array $header 头部
	  * @param array $body 内容
	  * @param string $filename 文件名
	*/
	public static function expcsv_html($header, $body, $filename = null, $multilist = false) {

		if (empty($header) || !is_array($header)) {
			echo '400';
			return '400';
		}
		if ($multilist == false) {
			$content = self::made_table($header, $body);
		} else {
			$contetn = "";
			foreach ($body as $k => $row)
			{
				$content .= "<h3>{$k}</h3>";
				$content .= self::made_table($header, $row);
			}
		}
		$html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" 
			xmlns:x="urn:schemas-microsoft-com:office:excel" 
			xmlns="http://www.w3.org/TR/REC-html40"> 
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
			<html> 
			<head> 
			<meta http-equiv="Content-type" content="text/html;charset=UTF-8" /> 
			<style type="text/css">
			td {
			text-align: center;
			}
			</style>
			</head> 
			<body> 
			<div id="Classeur1_16681" align=center x:publishsource="Excel"> 
			'.$content.'
			</div> 
			</body> 
			</html> ';
		if (empty($filename)) {
			$filename = date('YmdHis').".xls";
		}else{
            $filename = iconv("UTF-8", "GB2312//IGNORE", $filename);
        }
		self::expcsv($filename, $html); 
	}



	/**
	  * 生成表格格式 
	  * 
	  * @param unknown_type $header
	  * @param unknown_type $body
	  * return table
	*/
	private static function made_table($header, $body) {
		$thead = "<thead><tr>";
		foreach ($header as $v) {
			$thead .= "<th style='border:1px  solid #A2A2A2; height:30px'>{$v}</th>";
		}
		$thead .= "</tr></thead>";
		$tbody = "<tbody>";
		if (empty($body)) {
			$len = count($header);
			$tbody .= "<tr><td colspan='{$len}'>没有数据</td></tr>";
		} else {
			foreach ($body as $row) {
				$tbody .= "<tr>";
				foreach ($row as $k => $v) {
					$tbody .= "<td style='border:1px  solid #A2A2A2; height:30px'>{$v}</td>";	
				}
				$tbody .= "</tr>";
			}
		}
		$tbody .= "</tbody>";
		$table = '<table x:str cellpadding=0 cellspacing=0 border=0 width=100% style="border-collapse: collapse; border:1px  solid #A2A2A2 "> 
		'.$thead.$tbody.'
		</table> '; 
		return $table;
	}
	/**
	* 表格导出方法
	* Enter description here ...
	* @param unknown_type $filename 导出的文件名
	* @param unknown_type $datastring 导出的字符串
	*/
	private static function expcsv($filename,$datastring)
	{
		header("Content-Type:application/vnd.ms-excel; charset=UTF-8");
		header("Accept-Ranges:bytes");
		header("Content-Disposition:attachment;filename=".$filename);  //$filename导出的文件名
		header("Pragma: no-cache");
		header("Expires: 0");
		echo $datastring;
		exit;
	}
}
?>