<?php
class SimpleTemplate
{
  var $data, $str;
  function __construct($path=null,$data=null)
  {
    $this->data = $data;
    if($path)
    {
      if($path{0}!='/') $path = $_SERVER['DOCUMENT_ROOT'] . '/' . $path;
      if(!($this->str = file_get_contents($path))) user_error("SimpleTemplate couldn't find file: $path",E_ERROR);
    }
  }
  
  function render($data = null, $str = '')
  {
    if($data) $this->data = $data;
    if(!$str && $this->str) $str = $this->str;
    return preg_replace_callback('/\{\{([\S]+?)\}\}/',array($this,'callback'),$str);
  }
  
  protected function callback($res)
  {
    $datum = empty($this->data[$res[1]]) ? '' : $this->data[$res[1]];
    return is_array($datum) ? implode(', ',$datum) : $datum;
  }
}

class SimpleEmail extends SimpleTemplate
{
  public $lastHeaders;
  function send($data = null, $str = '')
  {
    if(!$data) $data = $this->data;
    if(!$str) $str = $this->str;
    $str = preg_split("/\r?\n\r?\n/", $str, 2);
    
    // handle headers, preventing injection
    $headers = $str[0];
    $safedata = $data;
    foreach($data as $key=>$val) if(is_string($val) && strpos($val,"\n")!==false) $safedata[$key] = '';
    $headers = $this->render($safedata,$headers);
    $headers = preg_split("/\r?\n/",$headers);
    $headout = array();
    $this->lastHeaders = array();
    $to = $subject = '';
    foreach($headers as $line)
    {
      $header = explode(":",$line,2);
      if(strtolower($header[0])=='to') $to = $header[1];
      elseif(strtolower($header[0])=='subject') $subject = $header[1];
      else $headout[] = $line;
      $this->lastHeaders[strtolower($header[0])] = trim($header[1]);
    }
    $body = $this->render($data,$str[1]);
    if(empty($this->lastHeaders['message-id'])) {
      $msgid = '<'.md5($to.$body).'@'.$_SERVER['HTTP_HOST'].'>';
      $this->lastHeaders['message-id'] = $msgid;
      $headout[] = "Message-ID: $msgid";
    }
    return mail($to,$subject,$body,implode("\r\n",$headout));
  }
}

if (get_magic_quotes_gpc()) {
  function undoMagicQuotes($array, $topLevel=true) {
    $newArray = array();
    foreach($array as $key => $value) {
      if (!$topLevel) {
        $key = stripslashes($key);
      }
      if (is_array($value)) {
        $newArray[$key] = undoMagicQuotes($value, false);
      }
      else {
        $newArray[$key] = stripslashes($value);
      }
    }
    return $newArray;
  }
  $_GET = undoMagicQuotes($_GET);
  $_POST = undoMagicQuotes($_POST);
  $_COOKIE = undoMagicQuotes($_COOKIE);
  $_REQUEST = undoMagicQuotes($_REQUEST);
}
?>