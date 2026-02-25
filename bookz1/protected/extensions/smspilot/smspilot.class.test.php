<?php
// v1.7
include('smspilot.class.php');

if (isset($_GET['check'])) { // Проверка статуса

	$sms = new SMSPilot( $_GET['apikey'] );
	
	$result = '<h3>Результат проверки статуса SMS</h3>';
	
	if ($sms->check( $_GET['check'] )) {
		
		$result .= '<table border="1"><tr><th>id</th><th>phone</th><th>price</th><th>status</th></tr>';
		
		foreach( $sms->status as $s)
			$result .= '<tr><td>'.$s['id'].'</td><td>'.$s['phone'].'</td><td>'.$s['price'].'</td><td>'.$s['status'].'</td></tr>';
			
		$result .= '</table><br /><br />';
	}
	else
		$result .= '<span style="color: red">Ошибка! ' .$sms->error .'</span>';
}

if (isset($_POST['sms'])) { // Отправки SMS и заодно проверка баланса
	
	$sms = new SMSPilot( $_POST['apikey'] );
	
	$sms->from = $_POST['from'];
	
	$result = '<h3>Результат отправки SMS</h3>';
	
	if ( $sms->send($_POST['phone'], $_POST['sms']) ) { // сообщение отправилось?
		
		$result .= 'Ваше сообщение успешно отправлено, ответ сервера: '.$sms->success;
		
		$ids = array();
		foreach( $sms->status as $s )
			$ids[] = $s['id'];
			
		$result .= '<br />ID(s) сообщения(ий)=<a href="?check='.implode(',',$ids).'&apikey='.$_POST['apikey'].'">'.implode(',',$ids).'</a>';
		
	} else	
		$result .= '<span style="color: red">Ошибка! ' .$sms->error .'</span>';
	
	
	if ( !empty($sms->info) ) {
		
		$result .= '<h3>Информация о пользователе: '.$sms->success.'</h3><pre>';
		
		foreach( $sms->info as $k => $v)
			$result .= "$k = $v\n";
		
		$result .= '</pre>';
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SMS Pilot - Отправка СМС используем класс</title>
<style type="text/css">
<!--
body,td,th {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 13px;
}
a:link {
	color: #06C;
	text-decoration: underline;
}
a:visited {
	text-decoration: underline;
	color: #06C;
}
a:hover {
	text-decoration: none;
	color: #06C;
}
a:active {
	text-decoration: underline;
	color: #06C;
}
-->
</style></head>

<body>
<h1>Пример работы со шлюзом отправки СМС, SMSPilot. PHP класс.</h1>
<p><a href="http://www.smspilot.ru/apikey.php">http://www.smspilot.ru/apikey.php</a></h2>
<?php echo (isset($result)) ? $result : ''; ?>
<form action="?send" method="post">
API-ключ<br />
<input type="text" name="apikey" size="80" value="<?php echo (isset($_POST['apikey'])) ? $_POST['apikey'] : ''; ?>" /> <a href="http://www.smspilot.ru/apikey.php" target="_blank">что это?</a><br />
<br />
Телефон:<br />
<input type="text" name="phone" size="60" value="<?php echo (isset($_POST['phone'])) ? $_POST['phone'] : ''; ?>" /> можно несколько через запятую<br />
<br />
Текст:<br />
<textarea cols="60" rows="6" name="sms"><?php echo (isset($_POST['sms'])) ? $_POST['sms'] : ''; ?></textarea><br />
<br />
Отправитель:<br />
<input type="text" name="from" value="<?php echo (isset($_POST['from'])) ? $_POST['from'] : ''; ?>" /> можно оставить пустым<br />
<br />
<input type="submit" value="Отправить SMS" />
</form>
</body>
</html>