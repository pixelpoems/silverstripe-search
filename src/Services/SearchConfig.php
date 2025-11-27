<?php

namespace Pixelpoems\Search\Services;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use TractorCow\Fluent\Model\Locale;

class SearchConfig
{
    use Configurable;

    private static bool $enable_default_style = true;

    private static array $index_keys = [
        'title'
    ];

    private static bool $enable_fluent = false;

    private static array $exclude_locale_from_index = [];
     // e.g. ['de_AT']
    private static bool $enable_elemental = false;

    private static array $exclude_elements_from_index = [];
     // e.g. ['ElementContent::class']
    private static int $max_results_inline = 10;

    public static function getIsDefaultStyleEnabled()
    {
        return Config::forClass(self::class)->get('enable_default_style');
    }

    public static function getSearchKeys()
    {
        return array_unique(Config::forClass(self::class)->get('index_keys'));
    }

    public static function isFluentEnabled()
    {
        return Config::forClass(self::class)->get('enable_fluent') && class_exists('TractorCow\Fluent\Model\Locale') && Locale::get()->exists();
    }

    public static function isElementalEnabled()
    {
        return Config::forClass(self::class)->get('enable_elemental');
    }

    public static function getExcludedElements()
    {
        if (self::isElementalEnabled()) {
            return Config::forClass(self::class)->get('exclude_elements_from_index');
        }

        return [];
    }

    public static function getExcludedLocales()
    {
        if (self::isFluentEnabled()) {
            return Config::forClass(self::class)->get('exclude_locale_from_index');
        }

        return [];
    }

    public static function getMaxResultsInline()
    {
        return Config::forClass(self::class)->get('max_results_inline');
    }
}
