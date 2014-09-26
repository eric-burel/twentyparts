<?php

// TODO : implement color name: white, grey, green etc etc ...

namespace framework\utility;

class Color {

    protected $_color = array('R', 'G', 'B');

    /**
     * Constructor expects 1 or 3 arguments : 1 = hexadecimal format (#0080FF or #08F) , 3 = rgb format (0, 128, 255)
     * when passing 3 arguments, specify each RGB component (from 0-255) individually.<br />
     */
    public function __construct($color = null) {
        $args = func_get_args();
        if (sizeof($args) == 0 || is_null($color)) {
            $this->_color = array('R' => 255, 'G' => 255, 'B' => 255);// default TODO must be setted
        } else if (sizeof($args) == 1) {
            if (substr($color, 0, 1) == '#')
                $color = substr($color, 1);
            if (strlen($color) != 3 && strlen($color) != 6)
                throw new \Exception('Invalid Hexadecimal color code');

            $this->_constructHexadecimal($color);
        } else if (count($args) == 3)
            $this->_constructRGB((int)$args[0], (int)$args[1], (int)$args[2]);
        else
            throw new \Exception('Color constructor expects 0, 1 or 3 arguments; ' . count($args) . ' given');
    }

    public function getColor($color = null) {
        if ($color) {
            if ($color == 'R')
                return $this->_color['R'];
            if ($color == 'G')
                return $this->_color['G'];
            if ($color == 'B')
                return $this->_color['B'];
            throw new \Exception('Invalid color');
        }

        return $this->_color;
    }

    protected function _constructRGB($red, $green, $blue) {
        if ($red < 0)
            $red = 0;
        if ($red > 255)
            $red = 255;
        if ($green < 0)
            $green = 0;
        if ($green > 255)
            $green = 255;
        if ($blue < 0)
            $blue = 0;
        if ($blue > 255)
            $blue = 255;

        $this->_color = array('R' => $red, 'G' => $green, 'B' => $blue);
    }

    protected function _constructHexadecimal($color) {
        if (strlen($color) == 3) {// see http://www.december.com/html/spec/color3hex1.html
            $red = str_repeat(substr($color, 0, 1), 2);
            $green = str_repeat(substr($color, 1, 1), 2);
            $blue = str_repeat(substr($color, 2, 1), 2);
        } else {
            $red = substr($color, 0, 2);
            $green = substr($color, 2, 2);
            $blue = substr($color, 4, 2);
        }

        $this->_color = array('R' => hexdec($red), 'G' => hexdec($green), 'B' => hexdec($blue));
    }

}

?>
