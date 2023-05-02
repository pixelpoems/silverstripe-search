<?php

namespace Pixelpoems\FuseSearch\Extensions;

use Pixelpoems\FuseSearch\Tasks\PopulateSearch;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataExtension;

class SearchIndexExtension extends DataExtension
{
    public function getSearchIndexData(): array
    {
        $data = [];
        $keys = Config::forClass(PopulateSearch::class)->get('index_keys');

        foreach ($keys as $key) {
            $data[$key] = null;
        }

        $data['title'] = $this->owner->Title;
        $data = $this->owner->addSearchData($data);

        $data['id'] = $this->owner->ID;
        $data['class'] = $this->owner->ClassName;
        return $data;
    }

    public function addSearchData($data) {
        return $data;
    }
}
