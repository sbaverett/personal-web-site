<?php
class FRAMEWORK {
  public $_SQL = NULL;
  public $_DESCRIPTION = NULL;
  public $_KEYWORDS = NULL;
  public $_AUTHOR = NULL;
  public $_TITLE = NULL;
  
  public function session() {
    session_set_cookie_params(0);
    session_start();
    return (0);
  }
  
  public function check() {
    $sql["ip"] = $this->secure($_SERVER["REMOTE_ADDR"]);
    $sql["browser"] = $this->secure($_SERVER["HTTP_USER_AGENT"]);
    $sql["path"] = $this->secure($_SERVER["REQUEST_URI"]);
    $sql["method"] = $this->secure($_SERVER["REQUEST_METHOD"]);
    $sql["date"] = $this->secure(gmdate("Y-m-d H:i:s"));
    $sql["uptodate"] = $this->secure(gmdate("Y-m-d H:i:s"));
    $sql["flag"] = $this->secure(0);
    $result = $this->database("INSERT INTO logs VALUES (
      '',
      '".$sql["ip"]."',
      '".$sql["browser"]."',
      '".$sql["path"]."',
      '".$sql["method"]."',
      '".$sql["date"]."',
      '".$sql["uptodate"]."',
      '".$sql["flag"]."'
    )");
    $result = $this->database("SELECT * FROM pages WHERE flag IN (0)");
    while (($row = mysqli_fetch_array($result))) {
      if (strstr(current(explode("?", $_SERVER["REQUEST_URI"])), $row["path"])) {
        $this->_DESCRIPTION = $row["description"];
        $this->_KEYWORDS = $row["keywords"];
        $this->_AUTHOR = $row["author"];
        $this->_TITLE = $row["title"];
      }
    }
    return (0);
  }
  
  public function redirect($url) {
    exit(header("Location: ".$url));
    return (0);
  }
  
  public function format($date) {
    $date = gmdate("F", strtotime($date))." ".gmdate("d", strtotime($date)).", ".gmdate("Y", strtotime($date));
    return ($date);
  }
  
  public function json($data) {
    if (gettype($data) == "string") {
      return (json_decode($data, TRUE));
    }
    if (gettype($data) == "array") {
      return (json_encode($data));
    }
    return (NULL);
  }
  
  public function secure($data) {
    $db = mysqli_connect($this->_SQL["host"], $this->_SQL["user"], $this->_SQL["pass"], $this->_SQL["name"]);
    $result = mysqli_query($db, "SET NAMES 'UTF8'");
    $result = mysqli_real_escape_string($db, stripslashes($data));
    mysqli_close($db);
    return ($result);
  }
  
  public function database($data) {
    $db = mysqli_connect($this->_SQL["host"], $this->_SQL["user"], $this->_SQL["pass"], $this->_SQL["name"]);
    $result = mysqli_query($db, "SET NAMES 'UTF8'");
    $result = mysqli_query($db, $data);
    mysqli_close($db);
    return ($result);
  }
  
  public function fn($func, $a = NULL, $b = NULL, $c = NULL, $d = NULL) {
    return ($a ? $b ? $c ? $d ? $func($a, $b, $c, $d) : $func($a, $b, $c) : $func($a, $b) : $func($a) : $func());
  }
}

$_FRAMEWORK = new FRAMEWORK();
?>
<?php
class API {
  public $_MANDRILL = NULL;

  public function mandrill($message, $subject, $from, $name, $to) { 
    $url = "https://mandrillapp.com/api/1.0/messages/send.json";
    $tmp["key"] = $this->_MANDRILL["key"];
    $tmp["secret"] = $this->_MANDRILL["secret"];
    $tmp["message[text]"] = $message;
    $tmp["message[subject]"] = $subject;
    $tmp["message[from_email]"] = $from;
    $tmp["message[from_name]"] = $name;
    $tmp["message[to][0][email]"] = $to;
    foreach ($tmp as $key => $value) {
      $parameters = isset($parameters) ? $parameters."&".$key."=".$value : $key."=".$value;
    }
    $request = curl_init($url);
    curl_setopt($request, CURLOPT_POST, TRUE);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($request, CURLOPT_POSTFIELDS, $parameters);
    curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE);
    $response = curl_exec($request);
    curl_close($request);
    return ($response);
  }
}

$_API = new API();
$_API->_MANDRILL["key"] = "EGBrVxBr9JrL9g8p2O8Ivw";
$_API->_MANDRILL["secret"] = "";
?>
<?php
class PROCESS {
  public function deliver() {
    global $_FRAMEWORK;
    global $_API;
    
    if (isset($_REQUEST["name"]) && isset($_REQUEST["from"]) && isset($_REQUEST["subject"]) && isset($_REQUEST["message"])) {
      $message = strip_tags($_REQUEST["message"]);
      $subject = strip_tags($_REQUEST["subject"]);
      $from = strip_tags($_REQUEST["from"]);
      $fullname = strip_tags($_REQUEST["name"]);
      $to = strip_tags("averettsam@comcast.net");
      $_API->mandrill($message, $subject, $from, $name, $to);
    }
    return (0);
  }
}

$_PROCESS = new PROCESS();
if (isset($_REQUEST["process"]) && method_exists($_PROCESS, $_REQUEST["process"])) {
  $_PROCESS->$_REQUEST["process"]();
}
?>