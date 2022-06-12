<?php

if(!empty($_GET['id']) && !empty($_GET['token'])) {
	$user = $_GET['id'];
	$token = $_GET['token'];

$text = "api:
	id_user: ".$user."
	token: ".$token."
	path:
	    mail_send_forgot: 'http://127.0.0.1:8001/api/mail/send/forgot'
	    mail_send_deadlines: 'http://127.0.0.1:8001/api/mail/send/deadlines'
	    sms_send_deadlines: 'http://127.0.0.1:8001/api/sms/send/deadlines'
	    help_send: 'http://127.0.0.1:8001/api/help/send'
	    help_load: 'http://127.0.0.1:8001/api/help/load'
";

	// otwarcie pliku do zapisu
	$fp = fopen("../config/api.yaml", "w");
	fputs($fp, $text);
	fclose($fp);
}

if(	!empty($_GET['name_pack']) && 
	!empty($_GET['deadline'])
	!empty($_GET['acounts'])
	!empty($_GET['vehicles'])
	!empty($_GET['drivers'])
	!empty($_GET['division'])
	!empty($_GET['name'])
	!empty($_GET['street'])
	!empty($_GET['code'])
	!empty($_GET['city'])
	!empty($_GET['nip'])
	!empty($_GET['regon'])
	!empty($_GET['email'])
) {

	$name_pack = $_GET['name_pack'];
	$deadline = $_GET['deadline'];
	$acounts = $_GET['acounts'];
	$vehicles = $_GET['vehicles'];
	$drivers = $_GET['drivers'];
	$division = $_GET['division'];
	$name = $_GET['name'];
	$street = $_GET['street'];
	$code = $_GET['code'];
	$city = $_GET['city'];
	$nip = $_GET['nip'];
	$regon = $_GET['regon'];
	$email = $_GET['email'];
	$phone = $_GET['phone'];

$text = "pack:
    name: ".$name_pack."
    deadline: ".$deadline."
    acounts: ".$acounts."
    vehicles: ".$vehicles."
    drivers: ".$drivers."
    division: ".$division."
data:
    name: ".$name."
    street: ".$street."
    code: ".$code."
    city: ".$city."
    nip: '".$nip."'
    regon: '"$regon."'
    email: '".$email."'
    phone: '"$phone."'
notify:
    email_one: '14'
    email_two: '7'
    email_three: '3'
    sms_one: '14'
    sms_two: '7'
    sms_three: '3'
    overview: 'on'
    oil: 'on'
    udt: 'on'
    documents: 'on'
    oc: 'on'
    warranty: 'on'
    mechanic: 'on'
";

	// otwarcie pliku do zapisu
	$fp = fopen("../config/settings.yaml", "w");
	fputs($fp, $text);
	fclose($fp);
}


?>