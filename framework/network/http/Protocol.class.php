<?php

namespace framework\network\http;

class Protocol {

    const PROTOCOL_VERSION_0_9 = '0.9';
    const PROTOCOL_VERSION_1_0 = '1.0';
    const PROTOCOL_VERSION_1_1 = '1.1';
    const PROTOCOL_VERSION_2_0 = '2.0';

    protected static $_protocolVersionList = array(
        self::PROTOCOL_VERSION_0_9,
        self::PROTOCOL_VERSION_1_0,
        self::PROTOCOL_VERSION_1_1,
        self::PROTOCOL_VERSION_2_0
    );
    protected static $_requestOrder = array('REQUEST');

    public static function isValid($httpProtocolVersion) {
        if (!is_string($httpProtocolVersion))
            throw new \Exception('httpProtocolVersion parameter must be a string');
        return in_array($httpProtocolVersion, self::$_protocolVersionList);
    }

}
