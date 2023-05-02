<?php
namespace Pixelpoems\FuseSearch\Services;

use SilverStripe\Core\Config\Config;
use Pixelpoems\FuseSearch\Tasks\PopulateSearch;

class SearchService
{
    private static bool $enable_fluent = false;
    private static bool $enable_elemental = false;

    static function getSearchKeysForTemplate(): string
    {
        $keys = Config::inst()->get(PopulateSearch::class, 'index_keys');
        $keys = array_unique($keys);
        $keys = json_encode(array_values($keys));
        return htmlspecialchars($keys);
    }
}
