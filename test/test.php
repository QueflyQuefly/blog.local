<?php
/* session_start();
$file_functions = join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'functions', 'functions.php'));
require_once $file_functions;
*/

/* $ch = curl_init();

// Установка опций
curl_setopt($ch, CURLOPT_URL, "https://fish-text.ru/get?format=html&number=5");
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);

// Выполнение
curl_exec($ch);

// Закрытие
curl_close($ch);
var_dump($ch); */

/* $ch1 = curl_init();
curl_setopt ($ch1, CURLOPT_URL, 'https://fish-text.ru/get?format=html&number=5' );
curl_setopt($ch1, CURLOPT_HEADER, 0);
curl_setopt($ch1,CURLOPT_VERBOSE,1);
curl_setopt($ch1, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)');
curl_setopt ($ch1, CURLOPT_REFERER,'http://www.google.com');  //just a fake referer
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch1,CURLOPT_POST,0);
curl_setopt($ch1, CURLOPT_FOLLOWLOCATION, 20);

$htmlContent= curl_exec($ch1); */

/* $ch = curl_init("https://fish-text.ru/get?format=html&number=5");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$content = curl_exec($ch);
curl_close($ch);
echo $content; */

/* $arrContextOptions=array(
    "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
    ),
);  

$zag = file_get_contents("https://fish-text.ru/get?format=html&type=title&number=1", false, stream_context_create($arrContextOptions));
echo $zag;

$text = file_get_contents("https://fish-text.ru/get?format=html&type=paragraph&number=6", false, stream_context_create($arrContextOptions));
echo $text;
 */
