<?php

// Need to be completed with : http://www.iana.org/assignments/media-types

namespace framework\utility;

class MimeType {

    protected static $_application = array(
        'EDI-X12',
        'EDIFACT',
        'javascript',
        'octet-stream',
        'ogg',
        'pdf',
        'xhtml+xml',
        'x-shockwave-flash',
        'json',
        'xml',
        'zip'
    );
    protected static $_audio = array(
        'mpeg',
        'x-ms-wma',
        'vnd.rn-realaudio',
        'x-wav'
    );
    protected static $_image = array(
        'gif',
        'jpeg',
        'png',
        'tiff',
        'vnd.microsoft.icon',
        'svg+xml'
    );
    protected static $_multipart = array(
        'mixed',
        'alternative',
        'related',
    );
    protected static $_text = array(
        'css',
        'csv',
        'html',
        'javascript',
        'plain',
        'xml'
    );
    protected static $_video = array(
        'mpeg',
        'mp4',
        'quicktime',
        'x-ms-wmv',
        'x-msvideo',
        'x-flv'
    );
    //application
    protected static $_vnd = array(
        'vnd.oasis.opendocument.text',
        'vnd.oasis.opendocument.spreadsheet',
        'vnd.oasis.opendocument.presentation',
        'vnd.oasis.opendocument.graphics',
        'vnd.ms-excel',
        'vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'vnd.ms-powerpoint',
        'msword',
        'vnd.openxmlformats-officedocument.wordprocessingml.document',
        'vnd.mozilla.xul+xml'
    );

    public static function getApplication($withVnd = true) {
        if ($withVnd)
            return array_merge(self::$_application, self::$_vnd);

        return self::$_application;
    }

    public static function getAudio() {
        return self::$_audio;
    }

    public static function getImage() {
        return self::$_image;
    }

    public static function getMultipart() {
        return self::$_multipart;
    }

    public static function getText() {
        return self::$_text;
    }

    public static function getVideo() {
        return self::$_video;
    }

    public static function getVnd() {
        return self::$_vnd;
    }

}

?>
