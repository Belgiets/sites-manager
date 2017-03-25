<?php



require_once '/usr/local/php5/lib/php/Config/Config.php';


$file = "/etc/apache2/other/dc.ventmanager.com.conf";

$conf = new Config();
$root = $conf->parseConfig($file, 'apache');
if (PEAR::isError($root)) {
    echo 'Error reading config: ' . $root->getMessage() . "\n";
    exit(1);
}

$i = 0;
while ($item = $root->getItem('directive', 'Listen', null, null, $i++)) {
    echo $item->name . ': ' . $item->content . "\n";
}

