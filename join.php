<?php

if (isset($_GET["error"])) {
  print("<h1>${_GET['error']}</h1><p>${_GET['error_description']}");
  http_status_code(400);
  exit();
}

var_dump($GLOBALS);

var_dump($_GLOBALS);

var_dump($GET);

var_dump($POST);

var_dump($SERVER);
/*
array(8) {
  ["_GET"]=> array(2) {
    ["error"]=> string(13) "invalid_scope" 
    ["error_description"]=> string(54) "The requested scope is invalid, unknown, or malformed."
  }
  
  ["_POST"]=> array(0) {}
  
  ["_COOKIE"]=> array(0) {}
  
  ["_FILES"]=> array(0) {}
  
  ["_SERVER"]=> array(35) {
    ["PATH"]=> string(180) "/usr/local/jdk/bin:/usr/kerberos/sbin:/usr/kerberos/bin:/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin:/usr/X11R6/bin:/usr/local/bin:/usr/X11R6/bin:/root/bin:/opt/bin"
    ["PWD"]=> string(25) "/usr/local/cpanel/cgi-sys" 
    ["TZ"]=> string(3) "UTC" 
    ["SHLVL"]=> string(1) "0" 
    ["SCRIPT_NAME"]=> string(9) "/join.php" 
    ["REQUEST_URI"]=> string(106) "/join.php?error=invalid_scope&error_description=The+requested+scope+is+invalid%2C+unknown%2C+or+malformed." 
    ["QUERY_STRING"]=> string(96) "error=invalid_scope&error_description=The+requested+scope+is+invalid%2C+unknown%2C+or+malformed." 
    ["REQUEST_METHOD"]=> string(3) "GET" 
    ["SERVER_PROTOCOL"]=> string(8) "HTTP/1.1" 
    ["GATEWAY_INTERFACE"]=> string(7) "CGI/1.1" 
    ["REMOTE_PORT"]=> string(5) "59674" 
    ["SCRIPT_FILENAME"]=> string(39) "/home/pinproje/public_html/d6r/join.php" 
    ["SERVER_ADMIN"]=> string(32) "webmaster@d6r-org.pinproject.com" 
    ["DOCUMENT_ROOT"]=> string(30) "/home/pinproje/public_html/d6r" 
    ["REMOTE_ADDR"]=> string(13) "198.143.37.14" 
    ["SERVER_PORT"]=> string(2) "80" 
    ["SERVER_ADDR"]=> string(14) "162.144.145.12" 
    ["SERVER_NAME"]=> string(7) "d6r.org" 
    ["SERVER_SOFTWARE"]=> string(6) "Apache" 
    ["SERVER_SIGNATURE"]=> string(0) "" 
    ["HTTP_INCAP_CLIENT_IP"]=> string(14) "73.251.148.239" 
    ["HTTP_X_FORWARDED_FOR"]=> string(14) "73.251.148.239" 
    ["HTTP_INCAP_PROXY_623"]=> string(2) "OK" 
    ["HTTP_ACCEPT_ENCODING"]=> string(4) "gzip" 
    ["HTTP_UPGRADE_INSECURE_REQUESTS"]=> string(1) "1" 
    ["HTTP_CONNECTION"]=> string(5) "close" 
    ["HTTP_ACCEPT_LANGUAGE"]=> string(5) "en-US" 
    ["HTTP_ACCEPT"]=> string(85) "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,* /*;q=0.8" 
    ["HTTP_USER_AGENT"]=> string(65) "Mozilla/5.0 (Android 11; Mobile; rv:89.0) Gecko/89.0 Firefox/89.0" 
    ["HTTP_HOST"]=> string(7) "d6r.org" 
    ["UNIQUE_ID"]=> string(24) "YIqV36KQj@oAAEU5GwUAAABV" 
    ["FCGI_ROLE"]=> string(9) "RESPONDER" 
    ["PHP_SELF"]=> string(9) "/join.php" 
    ["REQUEST_TIME_FLOAT"]=> float(1619695071.2786) 
    ["REQUEST_TIME"]=> int(1619695071)
  }
  
  ["_REQUEST"]=> array(2) {
    ["error"]=> string(13) "invalid_scope" 
    ["error_description"]=> string(54) "The requested scope is invalid, unknown, or malformed."
  }
  
  ["_ENV"]=> array(0) {
  }
  
  ["GLOBALS"]=> *RECURSION*
  }
   NULL NULL NULL NULL 
   */
?>