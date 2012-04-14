<?php
if(empty($_POST['email'])) die('fail');
require 'lib/SimpleEmail.php';
require 'lib/preview_view.php';
require 'lib/KafoofPreview.php';
$preview = new KafoofPreview($_POST);
ob_start();
foreach($preview->sections() as $s) kafoof_section($s);
$_POST['body'] = ob_get_clean();
$mail = new SimpleEmail('contact.template');
if(!$mail->send($_POST)) {
  throw new Exception('Email send failed');
}
die('ok');