<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

const VK_TOKEN 	= '...';
const VK_CONF 	= '...';
const VK_KEY 	= '...';

const COLOR_NEGATIVE = 'negative';
const COLOR_POSITIVE = 'positive';
const COLOR_DEFAULT = 'default';
const COLOR_PRIMARY = 'primary';

function getBtn($label, $color, $payload = '') {
	return [
		'action' => [
			'type' => 'text',
			"payload" => json_encode($payload, JSON_UNESCAPED_UNICODE),
			'label' => $label
		],
		'color' => $color
	];
}

function myLog($data) {
	file_put_contents('log.txt', $data."\n", FILE_APPEND | LOCK_EX);
}

function getIdLastMessage() {
	$myfile = fopen("id_last_message.txt", "r") or die("Unable to open file!");
	$id =  fgets($myfile);
	fclose($myfile);
	return $id;
}

function setIdLastMessage($id) {
	$myFile2 = "id_last_message.txt";
	$myFileLink2 = fopen($myFile2, 'w+') or die("Can't open file.");
	fwrite($myFileLink2, $id);
	fclose($myFileLink2);
}

function arrayFilterByDate($data,$date){
	$res='';
	for ($i=0; $i<count($data);$i++){
		if($data[$i]['datefrom'] == $date)
			$res .= " ". $data[$i]['race'] . " \t " .$data[$i]['datefrom'].  " " .$data[$i]['timeform'].  " \t " .$data[$i]['cityfrom'].  " > " .$data[$i]['cityto']. "\n";		
	}
	return $res;
}

if (!isset($_REQUEST)) { 
	myLog("REQUEST IS NULL");
	return; 
} 
$schedule = array 
(
	array(
		"race"		=> "HZ-1751",
		"cityfrom"	=> "Муданьцзян",
		"cityto"	=> "Владивосток",
		"tipe"		=> "DHC-8-402",
		"timeform"	=> "19:15",        
		"timeto"	=> "22:30",        
		"datefrom"	=> "07.04.19",
		"dateto"	=> "24.10.19"
	),
	array(
		"race"		=> "HZ-1755",
		"cityfrom"	=> "Владивосток",
		"cityto"	=> "Яньцзи",
		"tipe"		=> "DHC-8-402",
		"timeform"	=> "19:15",        
		"timeto"	=> "18:05",        
		"datefrom"	=> "08.04.19",
		"dateto"	=> "21.05.19",
	),
	array(
		"race"		=> "HZ-1755",
		"cityfrom"	=> "Владивосток",
		"cityto"	=> "Яньцзи",
		"tipe"		=> "DHC-8-402",
		"timeform"	=> "18:50",        
		"timeto"	=> "17:40",        
		"datefrom"	=> "03.04.19",
		"dateto"	=> "25.10.19",
	),
	array(
		"race"		=> "HZ-3602",
		"cityfrom"	=> "Южно-Сахалинск",
		"cityto"	=> "Ноглики",
		"tipe"		=> "DHC-8-300",
		"timeform"	=> "09:00",        
		"timeto"	=> "10:50",        
		"datefrom"	=> "03.04.19",
		"dateto"	=> "23.10.19",
	)
);

$data = json_decode(file_get_contents('php://input')); 
myLog(json_encode($data));

switch ($data->type) { 
	case 'confirmation': 
	echo VK_CONF; 
	break; 

	case 'message_new': 
	myLog("NEW MESSAGE");
	$user_id 	= $data->object->from_id; 
	$message_id	= $data->object->id; 		
	$text 		= $data->object->text; 		
	$payload 	= $data->object->payload ?? '';
	$user_info 	= json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&access_token=19eea604cb4c02da3c25086f17cec9a82859d362e1676dbc6f3713873aa9ade4f824bc071215b00803d83&v=5.0"));
	$user_name 	= $user_info->response[0]->first_name;
	myLog($user_id . " - " . $text);
	myLog(json_encode($user_info));	
	if ($payload) {
		$payload = json_decode($payload, true);
	}

	$mess = "Здравствуйте ".$user_name.", выберите вопрос из нижнего меню";
	$kbd = [
		'one_time' => false,
		'buttons' => [
			[getBtn("Расписание", COLOR_PRIMARY, 'SCHEDULE')],
			[getBtn("Правила перевозки", COLOR_PRIMARY, 'RULE')],
			[getBtn("Бронирования перевозки", COLOR_PRIMARY, 'BRONE')],
			[getBtn("Проверка наличия мест", COLOR_PRIMARY, 'CHECK')],
		]
	];

	if ($payload === 'SCHEDULE') {			
		$mess = "Онлайн-табло: https://www.flyaurora.ru/information/passengers/flight-info/ \nНапишите дату вылета(дд.мм.гг)";
		$kbd = [
			'one_time' => false,
			'buttons' => [
				[getBtn("Вылет на сегодня", COLOR_POSITIVE, 'FLY')],
				[getBtn("Прилет на сегодня", COLOR_NEGATIVE, 'ARRIVE')],
				[getBtn("Назад", COLOR_NEGATIVE, 'BACK')],
			]
		];
	}

	if(preg_match("/\d{2}.\d{2}.\d{2}/", $text)) 
	{ 		
		$mess = arrayFilterByDate($schedule, $text);
		$kbd = [
			'one_time' => false,
			'buttons' => [
				[getBtn("Вылет на сегодня", COLOR_POSITIVE, 'FLY')],
				[getBtn("Прилет на сегодня", COLOR_NEGATIVE, 'ARRIVE')],
				[getBtn("Назад", COLOR_NEGATIVE, 'BACK')],
			]
		];
	}  

	if ($payload === 'FLY') {
		$date = date('d.m.y');
		$mess = arrayFilterByDate($schedule, $date);
		$kbd = [
			'one_time' => false,
			'buttons' => [
				[getBtn("Вылет на сегодня", COLOR_POSITIVE, 'FLY')],
				[getBtn("Прилет на сегодня", COLOR_NEGATIVE, 'ARRIVE')],
				[getBtn("Назад", COLOR_NEGATIVE, 'BACK')],
			]
		];
	}

	if ($payload === 'ARRIVE') {
		$date = date('d.m.y');
		$mess = arrayFilterByDate($schedule, $date);
		$kbd = [
			'one_time' => false,
			'buttons' => [
				[getBtn("Вылет на сегодня", COLOR_POSITIVE, 'FLY')],
				[getBtn("Прилет на сегодня", COLOR_NEGATIVE, 'ARRIVE')],
				[getBtn("Назад", COLOR_NEGATIVE, 'BACK')],
			]
		];
	}

	if ($payload === 'RULE') {
		$mess = "Выберите вопрос из нижнего меню";
		$kbd = [
			'one_time' => false,
			'buttons' => [
				[getBtn("Внутрирегиональным", COLOR_PRIMARY, 'INREGION')],
				[getBtn("Межрегиональным", COLOR_PRIMARY, 'BETREGION')],
				[getBtn("Международным", COLOR_PRIMARY, 'INTERNATIONAL')],
				[getBtn("Назад", COLOR_NEGATIVE, 'BACK')],
			]
		];
	}

	if ($payload === 'BACK') {
		$mess = "Выберите вопрос из нижнего меню";
	}

	if ($payload === 'INREGION' or $payload === 'BETREGION' or $payload === 'INTERNATIONAL' ) {
		$mess = "Ознакомьтесь: https://vk.com/doc521353275_496156049?hash=3bd2287e4f923fbc18&dl=e5817d86e2ccb2ec6d ";
		$kbd = [
			'one_time' => false,
			'buttons' => [
				[getBtn("Назад", COLOR_NEGATIVE, 'BACK')],
			]
		];
	}

	if ($payload === 'BRONE') {
		$mess = "Связаться с оператором: https://www.flyaurora.ru/directum/treatment/";
	}

	if ($payload === 'CHECK') {
		$mess = "Связаться с оператором: https://www.flyaurora.ru/directum/treatment/";			
	}		

	$request_params = array( 
		'message' 		=> $mess, 
		'user_id' 		=> $user_id, 
		'access_token'	=> VK_TOKEN, 
		'keyboard' 		=> json_encode($kbd, JSON_UNESCAPED_UNICODE),
		'read_state' 	=> 1,
		'v' 			=> '5.0' 
	);
	$get_params 	= http_build_query($request_params); 
	$send_messge	= "https://api.vk.com/method/messages.send?".$get_params;	
	$last_messaage_id = getIdLastMessage();
	myLog($last_messaage_id);
	if( !empty($message_id) && $message_id > $last_messaage_id ){
		setIdLastMessage($message_id);
		file_get_contents($send_messge); 			
	}
	echo "ok";
	myLog("\n\n\n");
	break; 	
} 