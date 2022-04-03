#!/usr/local/bin/php
<?php
if(php_sapi_name() !== "cli") {
    die();
}

if($argv[1] === "migrate") {
    echo "Migrating config file...";

    define('L_EXEC', true);
    require_once dirname(__FILE__).'/configManager.php';
    // Remember to also change path in index.php
    $configManager = new LandingpageConfigManager("/data/config.yaml");
    if(!$configManager->migrate()) {
        echo "ERROR";
        exit(1);
    }
    echo "OK";
    exit(0);
}
else {
    echo "Usage: {$argv[0]} migrate\n";
    exit(1);
}