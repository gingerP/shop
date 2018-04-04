<?php

class AbstractComponent
{
    private static $mustache;

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
                )
            ]);
        }
        return AbstractComponent::$mustache;
    }
}