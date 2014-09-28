<?php

$config = array(
    // optionType => array(options)
    'form' => array(
        'autorun' => false,
        'form' => array(
            'form1' => array(
                'protection' => array(
                    'csrf' => array(
                        'urlReferer' => array('page', 'register'), //routes name
                        'timeValidity' => 600 //second
                    ),
                    'captcha' => array(
                        'dataFile' => '[PATH_DATA]captcha[DS]captcha-full.xml'
                    )
                ),
            ),
        )
    ),
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
