<?php
ini_set('memory_limit', '1024M');

$site = "";

$options = getopt("f:");

if (!isset($options['f'])) {
    die('Установите необходимый файл параметр -f, например, -f="file.xml".');
}

if (empty($options['f'])) {
    die('Параметр -f не может быть пустым.');
}

$file = $options['f'];

$fileinfo = pathinfo($file);

if (!isset($fileinfo['extension'])) {
    die('Отсутствует расширение файла.');
}

if (empty($fileinfo['extension'])) {
    die('Расширение файла не может быть пустым.');
}

if ($fileinfo['extension'] !== "xml") {
    die('Расширение файла не подходит для выполнения скрипта.');
}

try {
    $xml = simplexml_load_file(__DIR__ . "/$file");

    if (is_bool($xml) and $xml === false) {
        throw new Exception("");
    }
} catch (Exception | Throwable $exception) {
    die("При загрузке файла произошла ошибка.");
}
try {
    $link = new mysqli();

    if (is_bool($link) and $link === false) {
        throw new Exception("");
    }
} catch (Exception | Throwable $exception) {
    die("При подключении к базе данных произошла ошибка");
}

$currency_file = fopen($site . 'curr_usd.txt', 'r');

if (is_bool($currency_file) and $currency_file === false) {
    die('Ошибка открытия удалённого файла "curr_usd.txt"');
}

$currency = stream_get_contents($currency_file);

if (is_bool($currency) and $currency === false) {
    die('Ошибка чтения "curr_usd.txt"');
}

foreach ($xml->shop->offers->offer as $key => $value) {
    if (isset($value->model)) {
        $request = $link->query("
            SELECT `value` FROM `modx_site_content` 
            INNER JOIN `modx_site_tmplvar_contentvalues` 
                ON `modx_site_content`.`id` = `modx_site_tmplvar_contentvalues`.`contentid` 
            WHERE `pagetitle` = '".$value->model."'
        ");
        $response = mysqli_fetch_array($request, MYSQLI_ASSOC);

        $value->price = number_format(($response['value'] * $currency), 1, ',', '');
    }
}

$new = fopen(__DIR__ . "/$file", "w");
fwrite($new, $xml->asXML());

file_put_contents(__DIR__ . "/execute-time.log", date('d.m.Y H:i:s') . "\n", FILE_APPEND);

die("Скрипт для файла \"$file\" в updatePrice.php был выполнен.\n");
