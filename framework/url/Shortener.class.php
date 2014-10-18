<?php

namespace framework\url;

class Shortener {

    public static function factory($shortener, $identifiers = array()) {
        if (!is_string($shortener))
            throw new \Exception('Shortener parameter must be a string');

        if (class_exists('framework\url\shorteners\\' . $shortener))
            $shortenerClass = 'framework\url\shorteners\\' . $shortener;
        else
            $shortenerClass = $shortener;

        $inst = new \ReflectionClass($shortenerClass);
        if (!in_array('framework\\url\\IShortener', $inst->getInterfaceNames()))
            throw new \Exception('Shortener class must be implement framework\url\IShortener');


        return $inst->newInstance($identifiers);
    }

}

?>