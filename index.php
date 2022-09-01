<?php

$xml = simplexml_load_file(__DIR__."/fittinga_full.xml");

$rate = 60.24;

$content = file_get_contents(__DIR__."/array.txt");
$content = explode("\n", $content);

foreach ($content as $row) {
	$row = explode("\t", $row);
	
	foreach ($xml->shop->offers->offer as $key => $value) {		
		if ($value->model == $row[2]) {

			$value->currencyId = "RUB";
			$value->oldprice = (string)((int)$value->oldprice * $rate);
			
			if ($row[3] > 0) {
				$value->price = (string)($row[3] * $rate);
			} else {
				$value->price = (string)((int)$value->price * $rate);
			}
		}
	}
}

$new = fopen("newFile.xml", "w");
fwrite($new, $xml->asXML());