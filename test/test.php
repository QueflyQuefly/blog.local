<?php
session_start();
$file_functions = join(DIRECTORY_SEPARATOR, array(dirname(__DIR__), 'functions', 'functions.php'));
require_once $file_functions;


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

/* $text = "И нет сомнений, что представители современных социальных резервов лишь добавляют фракционных разногласий и описаны максимально подробно. Не следует, однако, забывать, что высокое качество позиционных исследований напрямую зависит от позиций, занимаемых участниками в отношении поставленных задач. Но курс на социально-ориентированный национальный проект создаёт необходимость включения в производственный план целого ряда внеочередных мероприятий с учётом комплекса системы массового участия. Но семантический разбор внешних противодействий выявляет срочную потребность вывода текущих активов. Как уже неоднократно упомянуто, предприниматели в сети интернет описаны максимально подробно. Как принято считать, элементы политического процесса неоднозначны и будут ассоциативно распределены по отраслям. Господа, граница обучения кадров предопределяет высокую востребованность стандартных подходов. Не следует, однако, забывать, что выбранный нами инновационный путь обеспечивает широкому кругу (специалистов) участие в формировании стандартных подходов.";

function isNounForTag($string){
    $groups = ['а','ь'];

    $string=mb_strtolower($string);
    $words=explode(' ',$string);
    //print_r($words);
    foreach ($words as $wk=>$w) {
        $lastSymbol = mb_substr($w, -1);
        
        foreach ($groups as $g) {
            if (mb_strlen($w) > 5) {
                if (mb_strtoupper($lastSymbol) === mb_strtoupper($g)) {
                    $word = "#" . $w;
                    $nouns[] = $word;
                    $nouns = array_unique($nouns);
                }
            }
        }
    }
    return $nouns;
}
$chat[1] = isNounForTag($text);
var_dump($chat);
foreach ($chat[1] as $tag) {
    echo $tag;
} */

//insertToPosts('1', 'vvvv', '1', 'jdbvjkvfdjvb');

//var_dump(isUserChangesComRating('1@gmail.com', 11));
/* try {
    $sql = "SELECT login_want_subscribe FROM subscriptions WHERE login = '5@gmail.com'";
    $stmt = $db->query($sql);
    while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $toEmail = $result['login_want_subscribe'];
        $message = "Новый пост от $author: blog.local/viewsinglepost.php?viewpostById=$id \n $name";
        mail($toEmail, 'Новый пост', $message);
    }
} catch (PDOException $e) {
    echo $e->getMessage();
} */
/* $to  = "drotov.mihailo@gmail.com" ; 

$subject = "Заголовок письма"; 

$message = "<p><a href='blog.local/viewsinglepost.php?viewPostById=9'>Перейти</a></p><p>Текст письма</p> </br> <b>1-ая строчка </b> </br><i>2-ая строчка </i> </br>";
$headers  = "Content-type: text/html; charset=utf-8 \r\n"; 
$headers = "From: prostoblog.local@gmail.com\r\n"; 
$headers .= "Reply-To: prostoblog.local@gmail.com\r\n"; 

mail($to, $subject, $message, $headers); */

//var_dump(getMoreTalkedPosts());

$sql = "INSERT INTO posts (name, login, author, date, content, rating) VALUES ('Пизанская башня - это лучшее место, где я когда-либо бывал', '278@gmail.com', 'Вячеслав Георгиевский', '1638045477', 'С давних времен люди не зная, что там за дальше, отправлялись в путешествие, их манила неизведанность, тайна, любопытство. И это было достаточно опасно, но несмотря на это, открывались новые города, страны, моря, океаны, материки. Сейчас современный человек знает многое, но отправляясь в путешествие, он по-прежнему открывает перед собой удивительный и неповторимый мир. Путешествие одно из самых любимых занятий большинства людей. А многие так любят путешествовать. Все просто, когда человек путешествует, он познает окружающий мир и самого себя. На земле очень много необычных уголков, красивых мест, которые заставляют пережить потрясающие эмоции, чувства. В путешествиях я знакомлюсь с новой культурой, обычаями и образом жизни проживающих там людей. Например, я вижу, что в Париже местные жители могут часами сидеть в кафе и пить маленькую чашку кофе, во Вьетнаме все ездят на мотобайках, а в Китае по вечерам много людей выходит в парки, где поют и танцуют. Все эти особенности их жизни очень интересно наблюдать.', '0');";
if (!$db->exec($sql)) {
    echo $sql;
}
$sql = 'INSERT INTO users (login, fio, password, date, rights) VALUES ("278@gmail.com", "Вячеслав Георгиевский", "$2y$10$ar5e.5mg5zuucOONwd9yX.pYMg/BA1opPpKJki60mDEQey2p9WEyy", 1638045477, "user");';
if (!$db->exec($sql)) {
    echo $sql;
}