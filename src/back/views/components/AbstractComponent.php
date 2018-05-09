<?php
use Katzgrau\KLogger\Logger as Logger;

class AbstractComponent
{
    private static $mustache;
    protected $logger;

    public function __construct()
    {
        $this->logger = new Logger(AU_CONFIG['log.file'], AU_CONFIG['log.level']);
    }


    public static function getEngine()
    {
        if (is_null(AbstractComponent::$mustache)) {
            AbstractComponent::$mustache = new Mustache_Engine([
                'loader' => new Mustache_Loader_FilesystemLoader(realpath(__DIR__ . '/../')),
                'pragmas' => [Mustache_Engine::PRAGMA_FILTERS],
                'helpers' => array(
                    'isEmpty' => function ($text) {
                        return $text;
                    },
                    'array' => [
                        'join' => function ($value) {
                            return implode(',', $value);
                        }
                    ]
                ),
                'partials' => [
                    'emailForm' => ''
                ]
            ]);
        }
        return AbstractComponent::$mustache;
    }

    public function build() {
        return '';
    }
}