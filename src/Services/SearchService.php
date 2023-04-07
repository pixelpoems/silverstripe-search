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
        $keys = json_encode($keys);
        return htmlspecialchars($keys);
    }
}
