<?php

namespace framework\url;

interface IShortener {

    public function __construct($identifiers);

    public function setApiUrl($url);

    public function getApiUrl();

    public function setApiKey($key);

    public function getApiKey();

    public function shorten($url, $format = 'json');

    public function expand($shortUrlHash, $returnFullReponse = false);
}

?>