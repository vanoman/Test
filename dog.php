<?php
// обновились
$homedir = $_SERVER['DOCUMENT_ROOT'];
require_once $homedir.'/config.php';
require_once $homedir.'/Classes/PHPWord.php';

$id = $_REQUEST['id'];
$action = $_REQUEST['action'];
$root_dir = $_SERVER['DOCUMENT_ROOT'];
$dog_dir = 'docs/contracts';

$months = array('','января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');

if ($action=='add')
{

	$query = "SELECT * FROM pers_main WHERE id_anketa=$id";
	$result = mysql_query($query);
	if (mysql_num_rows($result)) $row = mysql_fetch_array($result);

	$query = "SELECT * FROM pers_passport WHERE id_anketa=$id";
	$result = mysql_query($query);
	if (mysql_num_rows($result)) $row+= mysql_fetch_array($result);
	
	$query = "SELECT * FROM pers_status WHERE id_anketa=$id";
	$result = mysql_query($query);
	if (mysql_num_rows($result)) $row+= mysql_fetch_array($result);

	$query = "SELECT a.*, b.grazd_name FROM pers_address as a LEFT JOIN grazdanstvo as b ON a.country=b.grazd_id WHERE a.id_anketa=$id";
	$result = mysql_query($query);
	if (mysql_num_rows($result)) $row+= mysql_fetch_array($result);
	
	if ($row['domitory'])
	{
		$query = "SELECT name FROM domitory WHERE id=".$row['domitory'];
		$result = mysql_query($query);
		list($domitory) = mysql_fetch_array($result);
	}
	
	if ($row['object'])
	{
		$query = "SELECT address FROM objects WHERE id=".$row['object'];
		$result = mysql_query($query);
		list($place) = mysql_fetch_array($result);
	}
	

	$fio = "{$row['lastname']} {$row['firstname']} {$row['patronymic']}" ;
	$row['give_date'] = preg_replace('/(\d{4})\-(\d{2})\-(\d{2})/',"$3.$2.$1",$row['give_date']);


	$address = (($row['country']) ? "{$row['grazd_name']}":'-')
.(($row['region']) ? ", {$row['region']} {$row['region_socr']}":'-')
.(($row['district']) ? ", {$row['district']} {$row['district_socr']}":'')
.(($row['city']) ? ", {$row['city']} {$row['city_socr']}":'')
.(($row['np']) ? ", {$row['np']} {$row['np_socr']}":'')
.(($row['street']) ? ", {$row['street']} {$row['street_socr']}":'').(($row['house']) ? ", д.{$row['house']}":'')
.(($row['building']) ? ", к.{$row['building']}":'').(($row['flat']) ? ", кв.{$row['flat']}":'');


	$PHPWord = new PHPWord();
	$document = $PHPWord->loadTemplate($root_dir.'/docs/dog.docx');

	$query = "SELECT id FROM contracts WHERE id_anketa=$id";
	$result = mysql_query($query);
	$num = mysql_num_rows($result);
	$num++;
	$dog = $id;
	$dognum.= "$dog-$num"; 
	
	$datenow = sprintf("«%s» %s %s",date('d'),$months[(int)date('m')],date('Y')); 
	
	$date = date('Y-m-d');
	$day = date('d');
	
	if ($row['object']==9)
	{
		if ($day<=15)
		$timeend = strtotime("$date +15 day - $day day +1 month");
		else
		$timeend = strtotime("$date - $day day +2 month");
	}
	else
	{ 
		if ($day<=7) 
		$timeend = strtotime("$date - $day day +1 day +1 month ");
		elseif ($day>=8 && $day<=22)
		$timeend = strtotime("$date +15 day - $day day +1 month");
		elseif ($day>=23)
		$timeend = strtotime("$date - $day day +1 day +2 month");
	}

	$dateend = sprintf("«%s» %s %s",date('d',$timeend),$months[(int)date('m',$timeend)],date('Y',$timeend)); 
	if(preg_match("/\D/",$row['rate']))
	$rate = preg_replace("/(.*)\.(.*)/","$1 рублей $2 копеек",$row['rate']);
	else $rate = "{$row['rate']} рублей 00 копеек";
	
	$phone = "{$row['phone_home']}, {$row['phone_mobile']}";
	$phone = trim($phone,',');
	
	$document->setValue('dognum', $dognum);
	$document->setValue('rate', $rate);
	$document->setValue('place', $place);
	$document->setValue('domitory', $domitory);
	$document->setValue('datenow', $datenow);
	$document->setValue('dateend', $dateend);
	$document->setValue('fio', $fio);
	$document->setValue('phone', $phone);
	$document->setValue('number', $row['number']);
	$document->setValue('series', $row['series']);
	$document->setValue('give', $row['give']);
	$document->setValue('gdate', $row['give_date']);
	$document->setValue('address', $address);

	$dir = $root_dir."/$dog_dir/$id/";
	if (!opendir($dir)) mkdir($dir,0777);
	$filename = "dog$id-$num.docx";
	$file =  "$dir/$filename";
	$document->save($file);
	chmod($file, 0777);
	
	$query = "INSERT INTO contracts (id_anketa,filename) VALUES ($id,'$filename')";
	mysql_query($query);
	
}


$query = "SELECT id,filename,date_format(date,'%d.%m.%Y %H:%i:%s') as date_change FROM contracts WHERE id_anketa=$id ORDER BY date DESC";
$result = mysql_query($query);



if ($action=='edit')
{
	$id_dog = $_REQUEST['id_dog'];
	$query = "SELECT id_anketa,filename FROM contracts WHERE id={$id_dog}";
	$result = mysql_query($query);
	$row = mysql_fetch_array($result);
	
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="'.$row['filename'].'"');
	$file = $root_dir.'/'.$dog_dir.'/'.$row['id_anketa'].'/'.$row['filename'];
	readfile($file); 
}
else
{
	print <<<END
	<html>
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Договора анкеты (ID:$id)</title>
	<style>


	#dogs {
	font-family:Arial;
	font-size:13px;
	}

	#dogs td {
	text-align:center;
	}
	</style>
	</head>
	<body>
	<form method="post">
	<input type="submit" value="Сформировать договор"> <input type="button" value="Закрыть окно" onclick="window.close()">
	<input type="hidden" name="action" value="add">
	<input type="hidden" name="id" value="$id">
	</form>
END;
	if (mysql_num_rows($result))
	{
		print <<<END
		<table id="dogs" cellpadding="5" border="1" style="border-collapse:collapse">
		<tr bgcolor="#EEEEEE">
		<th>Файл договора</th>
		<th>Дата</th>
		<th>Действие</th>
		</tr>
END;


		while($row = mysql_fetch_array($result))
		{
			print <<<END
			<tr>
			<td>{$row['filename']}</td>
			<td>{$row['date_change']}</td>
			<td><a href="{$_SERVER['SCRIPT_NAME']}?action=edit&id_dog={$row['id']}">Открыть/Скачать</a></td>
			</tr>
END;
		}

		print <<<END
		</table>
		</body>
		</html>
END;

	}

}





?>