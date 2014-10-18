<?php

// SEE http://en.wikipedia.org/wiki/List_of_HTTP_status_codes for more informations

namespace framework\network\http;

abstract class ResponseCode {

    // [Informational 1xx]

    const CODE_CONTINUE = 100;
    const CODE_SWITCHING_PROTOCOLS = 101;
    const CODE_PROCESSING = 102;
    // [Successful 2xx]
    const CODE_OK = 200;
    const CODE_CREATED = 201;
    const CODE_ACCEPTED = 202;
    const CODE_NONAUTHORITATIVE_INFORMATION = 203;
    const CODE_NO_CONTENT = 204;
    const CODE_RESET_CONTENT = 205;
    const CODE_PARTIAL_CONTENT = 206;
    const CODE_MULTISTATUS = 207;
    const CODE_ALREADY_REPORTED = 208;
    const CODE_IM_USED = 226;
    const CODE_AUTHENTICATION_SUCCESSFUL = 226;
    // [Redirection 3xx]
    const CODE_MULTIPLE_CHOICES = 300;
    const CODE_MOVED_PERMANENTLY = 301;
    const CODE_FOUND = 302;
    const CODE_SEE_OTHER = 303;
    const CODE_NOT_MODIFIED = 304;
    const CODE_USE_PROXY = 305;
    const CODE_SWITCH_PROXY = 306;
    const CODE_TEMPORARY_REDIRECT = 307;
    const CODE_PERMANENT_REDIRECT = 308;
    // [Client Error 4xx]
    const CODE_BAD_REQUEST = 400;
    const CODE_UNAUTHORIZED = 401;
    const CODE_PAYMENT_REQUIRED = 402;
    const CODE_FORBIDDEN = 403;
    const CODE_NOT_FOUND = 404;
    const CODE_METHOD_NOT_ALLOWED = 405;
    const CODE_NOT_ACCEPTABLE = 406;
    const CODE_PROXY_AUTHENTICATION_REQUIRED = 407;
    const CODE_REQUEST_TIMEOUT = 408;
    const CODE_CONFLICT = 409;
    const CODE_GONE = 410;
    const CODE_LENGTH_REQUIRED = 411;
    const CODE_PRECONDITION_FAILED = 412;
    const CODE_REQUEST_ENTITY_TOO_LARGE = 413;
    const CODE_REQUEST_URI_TOO_LONG = 414;
    const CODE_UNSUPPORTED_MEDIA_TYPE = 415;
    const CODE_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const CODE_EXPECTATION_FAILED = 417;
    const CODE_IM_A_TEAPOT = 418;
    const CODE_ENCHANCE_YOUR_CALM = 420;
    const CODE_UNPROCESSABLE_ENTITY = 422;
    const CODE_LOCKED = 423;
    const CODE_FAILED_DEPENDENCY = 424;
    const CODE_UNORDERED_COLLECTION = 425;
    const CODE_UPGRADE_REQUIRED = 426;
    const CODE_PRECONDITION_REQUIRED = 428;
    const CODE_TOO_MANY_REQUESTS = 429;
    const CODE_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    const CODE_NO_RESPONSE = 444;
    const CODE_RETRY_WITH = 449;
    const CODE_BLOCKED_BY_WINDOWS_PARENTAL_CONTROLS = 450;
    const CODE_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
    const CODE_REQUEST_HEADER_TOO_LARGE = 494;
    const CODE_CERT_ERROR = 495;
    const CODE_NO_CERT = 496;
    const CODE_HTTP_TO_HTTPS = 497;
    const CODE_CLIENT_CLOSED_REQUEST = 499;
    // [Server Error 5xx]
    const CODE_INTERNAL_SERVER_ERROR = 500;
    const CODE_NOT_IMPLEMENTED = 501;
    const CODE_BAD_GATEWAY = 502;
    const CODE_SERVICE_UNAVAILABLE = 503;
    const CODE_GATEWAY_TIMEOUT = 504;
    const CODE_VERSION_NOT_SUPPORTED = 505;
    const CODE_VARIANT_ALSO_NEGOTIATES = 506;
    const CODE_INSUFFICIENT_STORAGE = 507;
    const CODE_LOOP_DETECTED = 508;
    const CODE_BANDWIDTH_LIMIT_EXCEEDDED = 509;
    const CODE_NOT_EXTENTED = 510;
    const CODE_NETWORK_AUTHENTIFICATION_REQUIRED = 511;
    const CODE_ACCESS_DENIED = 531;
    const CODE_NETWORK_READ_TIMEOUT_ERROR = 598;
    const CODE_NETWORK_CONNECT_TIMEOUT_ERROR = 599;

    protected static $_messages = array(
        // [Informational 1xx]
        100 => '100 Continue',
        101 => '101 Switching Protocols',
        101 => '102 Processing',
        // [Successful 2xx]
        200 => '200 OK',
        201 => '201 Created',
        202 => '202 Accepted',
        203 => '203 Non-Authoritative Information',
        204 => '204 No Content',
        205 => '205 Reset Content',
        206 => '206 Partial Content',
        207 => '207 Multi-Status',
        208 => '208 Already Reported',
        226 => '226 IM Used',
        230 => 'Authentication Successful',
        // [Redirection 3xx]
        300 => '300 Multiple Choices',
        301 => '301 Moved Permanently',
        302 => '302 Found',
        303 => '303 See Other',
        304 => '304 Not Modified',
        305 => '305 Use Proxy',
        306 => '306 Switch Proxy',
        307 => '307 Temporary Redirect',
        308 => '308 Permanent Redirect',
        // [Client Error 4xx]
        400 => '400 Bad Request',
        401 => '401 Unauthorized',
        402 => '402 Payment Required',
        403 => '403 Forbidden',
        404 => '404 Not Found',
        405 => '405 Method Not Allowed',
        406 => '406 Not Acceptable',
        407 => '407 Proxy Authentication Required',
        408 => '408 Request Timeout',
        409 => '409 Conflict',
        410 => '410 Gone',
        411 => '411 Length Required',
        412 => '412 Precondition Failed',
        413 => '413 Request Entity Too Large',
        414 => '414 Request-URI Too Long',
        415 => '415 Unsupported Media Type',
        416 => '416 Requested Range Not Satisfiable',
        417 => '417 Expectation Failed',
        418 => '418 I\'m a teapot',
        420 => '420 Enhance Your Calm (Twitter)',
        422 => '422 Unprocessable Entity',
        423 => '423 Locked',
        424 => '424 Failed Dependency',
        425 => '425 Unordered Collection (Internet draft)',
        426 => '426 Upgrade Required',
        428 => '428 Precondition Required',
        429 => '429 Too Many Requests',
        431 => '431 Request Header Fields Too Large',
        444 => '444 No Response (Nginx)',
        449 => '449 Retry With (Microsoft)',
        450 => '450 Blocked by Windows Parental Controls (Microsoft)',
        451 => '451 Unavailable For Legal Reasons (Internet draft)',
        494 => '494 Request Header Too Large (Nginx)',
        495 => '495 Cert Error (Nginx)',
        496 => '496 No Cert (Nginx)',
        497 => '497 HTTP to HTTPS (Nginx)',
        499 => '499 Client Closed Request (Nginx)',
        // [Server Error 5xx]
        500 => '500 Internal Server Error',
        501 => '501 Not Implemented',
        502 => '502 Bad Gateway',
        503 => '503 Service Unavailable',
        504 => '504 Gateway Timeout',
        505 => '505 HTTP Version Not Supported',
        506 => '506 Variant Also Negotiates',
        507 => '507 Insufficient Storage',
        508 => '508 Loop Detected',
        509 => '509 Bandwidth Limit Exceeded (Apache bw/limited extension)',
        510 => '510 Not Extended',
        511 => '511 Network Authentication Required',
        531 => '531 Access Denied',
        598 => '598 Network read timeout error (Unknown)',
        599 => '599 Network connect timeout error (Unknown)'
    );

    public static function getMessage($code, $checkCode = true) {
        if (!is_int($code))
            throw new \Exception('HTTP Response code must be an integer');
        if (!is_bool($checkCode))
            throw new \Exception('checkCode parameter must be an boolean');

        if ($checkCode && !self::isValid($code))
            throw new \Exception('HTTP Response code :  "' . $code . '" doesn\'t exist');

        return self::$_messages[$code];
    }

    public static function isValid($code) {
        return array_key_exists((string) $code, self::$_messages);
    }

    public static function isError($code) {
        return is_numeric($code) && $code >= self::HTTP_BAD_REQUEST;
    }

    public static function canHaveBody($code) {
        return
                // True if not in 100s
                ($code < self::CODE_CONTINUE || $code >= self::CODE_OK) && // and not 204 NO CONTENT
                $code != self::CODE_NO_CONTENT && // and not 304 NOT MODIFIED
                $code != self::CODE_NOT_MODIFIED;
    }

}

?>
