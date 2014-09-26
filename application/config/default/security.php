<?php

$config = array(
    'sniffer' => array(
        'autorun' => true,
        'trapName' => 'badbottrap',
        'badCrawlerFile' => '[PATH_DATA]sniffer[DS]crawlerBad.xml',
        'goodCrawlerFile' => '[PATH_DATA]sniffer[DS]crawlerGood.xml',
        'logBadCrawler' => true,
        'logGoodCrawler' => true,
        'logUnknownCrawler' => true
    )
);
?>
