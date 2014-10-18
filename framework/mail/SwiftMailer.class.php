<?php

namespace framework\mail;

use framework\Autoloader;

class SwiftMailer {

    use \framework\pattern\Singleton;

    protected static $_pathTmp = PATH_CACHE;

    protected function __construct() {
        // Add Namespace, for autoloading class with name "Swift" ...
        Autoloader::addNamespace('Swift', PATH_VENDORS . DS . 'SwiftMailer' . DS);

        //Load in dependency maps
        $this->_loadDependencyMaps();

        //Load in global library preferences
        $this->_preferences();
    }

    protected function _loadDependencyMaps() {
        $this->_cacheDeps();
        $this->_mimeDeps();
        $this->_messageDeps();
        $this->_transportDeps();
    }

    protected function _preferences() {
        // Sets the default charset so that setCharset() is not needed elsewhere
        \Swift_Preferences::getInstance()->setCharset('utf-8');
        // Without these lines the default caching mechanism is "array" but this uses a lot of memory
        // If possible, use a disk cache to enable attaching large attachments etc.
        // You can override the default temporary directory by setting the TMPDIR environment variable.
//        if (function_exists('sys_get_temp_dir') && is_writable(sys_get_temp_dir())) {
//            \Swift_Preferences::getInstance()
//                    ->setTempDir(sys_get_temp_dir())
//                    ->setCacheType('disk');
//        }

        \Swift_Preferences::getInstance()
                ->setTempDir(self::getPathTmp())
                ->setCacheType('disk');

        \Swift_Preferences::getInstance()->setQPDotEscape(false);
    }

    public static function setPathTmp($path, $forceCreate = true) {
        if ($forceCreate && !is_dir($path)) {
            if (!mkdir($path, 0775, true))
                throw new \Exception('Error on creating "' . $path . '" directory');
        }else {
            if (!is_dir($path))
                throw new \Exception('Directory "' . $path . '" do not exists');
        }
        if (!is_writable($path))
            throw new \Exception('Directory "' . $path . '" is not writable');
        self::$_pathTmp = realpath($path) . DS;
    }

    public static function getPathTmp() {
        return self::$_pathTmp;
    }

    protected function _cacheDeps() {
        \Swift_DependencyContainer::getInstance()
                ->register('cache')
                ->asAliasOf('cache.array')
                ->register('tempdir')
                ->asValue('/tmp')
                ->register('cache.null')
                ->asSharedInstanceOf('Swift_KeyCache_NullKeyCache')
                ->register('cache.array')
                ->asSharedInstanceOf('Swift_KeyCache_ArrayKeyCache')
                ->withDependencies(array('cache.inputstream'))
                ->register('cache.disk')
                ->asSharedInstanceOf('Swift_KeyCache_DiskKeyCache')
                ->withDependencies(array('cache.inputstream', 'tempdir'))
                ->register('cache.inputstream')
                ->asNewInstanceOf('Swift_KeyCache_SimpleKeyCacheInputStream');
    }

    protected function _mimeDeps() {
        $swift_mime_types = array(
            'aif' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'avi' => 'video/avi',
            'bmp' => 'image/bmp',
            'bz2' => 'application/x-bz2',
            'csv' => 'text/csv',
            'dmg' => 'application/x-apple-diskimage',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'eml' => 'message/rfc822',
            'aps' => 'application/postscript',
            'exe' => 'application/x-ms-dos-executable',
            'flv' => 'video/x-flv',
            'gif' => 'image/gif',
            'gz' => 'application/x-gzip',
            'hqx' => 'application/stuffit',
            'htm' => 'text/html',
            'html' => 'text/html',
            'jar' => 'application/x-java-archive',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'm3u' => 'audio/x-mpegurl',
            'm4a' => 'audio/mp4',
            'mdb' => 'application/x-msaccess',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'mov' => 'video/quicktime',
            'mp3' => 'audio/mpeg',
            'mp4' => 'video/mp4',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'odg' => 'vnd.oasis.opendocument.graphics',
            'odp' => 'vnd.oasis.opendocument.presentation',
            'odt' => 'vnd.oasis.opendocument.text',
            'ods' => 'vnd.oasis.opendocument.spreadsheet',
            'ogg' => 'audio/ogg',
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'ps' => 'application/postscript',
            'rar' => 'application/x-rar-compressed',
            'rtf' => 'application/rtf',
            'tar' => 'application/x-tar',
            'sit' => 'application/x-stuffit',
            'svg' => 'image/svg+xml',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'ttf' => 'application/x-font-truetype',
            'txt' => 'text/plain',
            'vcf' => 'text/x-vcard',
            'wav' => 'audio/wav',
            'wma' => 'audio/x-ms-wma',
            'wmv' => 'audio/x-ms-wmv',
            'xls' => 'application/excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xml' => 'application/xml',
            'zip' => 'application/zip');

        \Swift_DependencyContainer::getInstance()
                ->register('properties.charset')
                ->asValue('utf-8')
                ->register('mime.grammar')
                ->asSharedInstanceOf('Swift_Mime_Grammar')
                ->register('mime.message')
                ->asNewInstanceOf('Swift_Mime_SimpleMessage')
                ->withDependencies(array(
                    'mime.headerset',
                    'mime.qpcontentencoder',
                    'cache',
                    'mime.grammar',
                    'properties.charset'
                ))
                ->register('mime.part')
                ->asNewInstanceOf('Swift_Mime_MimePart')
                ->withDependencies(array(
                    'mime.headerset',
                    'mime.qpcontentencoder',
                    'cache',
                    'mime.grammar',
                    'properties.charset'
                ))
                ->register('mime.attachment')
                ->asNewInstanceOf('Swift_Mime_Attachment')
                ->withDependencies(array(
                    'mime.headerset',
                    'mime.base64contentencoder',
                    'cache',
                    'mime.grammar'
                ))
                ->addConstructorValue($swift_mime_types)
                ->register('mime.embeddedfile')
                ->asNewInstanceOf('Swift_Mime_EmbeddedFile')
                ->withDependencies(array(
                    'mime.headerset',
                    'mime.base64contentencoder',
                    'cache',
                    'mime.grammar'
                ))
                ->addConstructorValue($swift_mime_types)
                ->register('mime.headerfactory')
                ->asNewInstanceOf('Swift_Mime_SimpleHeaderFactory')
                ->withDependencies(array(
                    'mime.qpheaderencoder',
                    'mime.rfc2231encoder',
                    'mime.grammar',
                    'properties.charset'
                ))
                ->register('mime.headerset')
                ->asNewInstanceOf('Swift_Mime_SimpleHeaderSet')
                ->withDependencies(array('mime.headerfactory', 'properties.charset'))
                ->register('mime.qpheaderencoder')
                ->asNewInstanceOf('Swift_Mime_HeaderEncoder_QpHeaderEncoder')
                ->withDependencies(array('mime.charstream'))
                ->register('mime.charstream')
                ->asNewInstanceOf('Swift_CharacterStream_NgCharacterStream')
                ->withDependencies(array('mime.characterreaderfactory', 'properties.charset'))
                ->register('mime.bytecanonicalizer')
                ->asSharedInstanceOf('Swift_StreamFilters_ByteArrayReplacementFilter')
                ->addConstructorValue(array(array(0x0D, 0x0A), array(0x0D), array(0x0A)))
                ->addConstructorValue(array(array(0x0A), array(0x0A), array(0x0D, 0x0A)))
                ->register('mime.characterreaderfactory')
                ->asSharedInstanceOf('Swift_CharacterReaderFactory_SimpleCharacterReaderFactory')
                ->register('mime.qpcontentencoder')
                ->asNewInstanceOf('Swift_Mime_ContentEncoder_QpContentEncoder')
                ->withDependencies(array('mime.charstream', 'mime.bytecanonicalizer'))
                ->register('mime.7bitcontentencoder')
                ->asNewInstanceOf('Swift_Mime_ContentEncoder_PlainContentEncoder')
                ->addConstructorValue('7bit')
                ->addConstructorValue(true)
                ->register('mime.8bitcontentencoder')
                ->asNewInstanceOf('Swift_Mime_ContentEncoder_PlainContentEncoder')
                ->addConstructorValue('8bit')
                ->addConstructorValue(true)
                ->register('mime.base64contentencoder')
                ->asSharedInstanceOf('Swift_Mime_ContentEncoder_Base64ContentEncoder')
                ->register('mime.rfc2231encoder')
                ->asNewInstanceOf('Swift_Encoder_Rfc2231Encoder')
                ->withDependencies(array('mime.charstream'));
        unset($swift_mime_types);
    }

    protected function _messageDeps() {
        \Swift_DependencyContainer::getInstance()
                ->register('message.message')
                ->asNewInstanceOf('Swift_Message')
                ->register('message.mimepart')
                ->asNewInstanceOf('Swift_MimePart');
    }

    protected function _transportDeps() {
        \Swift_DependencyContainer::getInstance()
                ->register('transport.smtp')
                ->asNewInstanceOf('Swift_Transport_EsmtpTransport')
                ->withDependencies(array(
                    'transport.buffer',
                    array('transport.authhandler'),
                    'transport.eventdispatcher'
                ))
                ->register('transport.sendmail')
                ->asNewInstanceOf('Swift_Transport_SendmailTransport')
                ->withDependencies(array(
                    'transport.buffer',
                    'transport.eventdispatcher'
                ))
                ->register('transport.mail')
                ->asNewInstanceOf('Swift_Transport_MailTransport')
                ->withDependencies(array('transport.mailinvoker', 'transport.eventdispatcher'))
                ->register('transport.loadbalanced')
                ->asNewInstanceOf('Swift_Transport_LoadBalancedTransport')
                ->register('transport.failover')
                ->asNewInstanceOf('Swift_Transport_FailoverTransport')
                ->register('transport.spool')
                ->asNewInstanceOf('Swift_Transport_SpoolTransport')
                ->withDependencies(array('transport.eventdispatcher'))
                ->register('transport.null')
                ->asNewInstanceOf('Swift_Transport_NullTransport')
                ->withDependencies(array('transport.eventdispatcher'))
                ->register('transport.mailinvoker')
                ->asSharedInstanceOf('Swift_Transport_SimpleMailInvoker')
                ->register('transport.buffer')
                ->asNewInstanceOf('Swift_Transport_StreamBuffer')
                ->withDependencies(array('transport.replacementfactory'))
                ->register('transport.authhandler')
                ->asNewInstanceOf('Swift_Transport_Esmtp_AuthHandler')
                ->withDependencies(array(
                    array(
                        'transport.crammd5auth',
                        'transport.loginauth',
                        'transport.plainauth'
                    )
                ))
                ->register('transport.crammd5auth')
                ->asNewInstanceOf('Swift_Transport_Esmtp_Auth_CramMd5Authenticator')
                ->register('transport.loginauth')
                ->asNewInstanceOf('Swift_Transport_Esmtp_Auth_LoginAuthenticator')
                ->register('transport.plainauth')
                ->asNewInstanceOf('Swift_Transport_Esmtp_Auth_PlainAuthenticator')
                ->register('transport.eventdispatcher')
                ->asNewInstanceOf('Swift_Events_SimpleEventDispatcher')
                ->register('transport.replacementfactory')
                ->asSharedInstanceOf('Swift_StreamFilters_StringReplacementFilterFactory');
    }

}

?>