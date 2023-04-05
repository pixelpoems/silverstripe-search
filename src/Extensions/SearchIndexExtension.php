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
        $extra_keys = Config::forClass(PopulateSearch::class)->get('index_keys');

        foreach ($extra_keys as $key) {
            $data[$key] = null;
        }

        $data['title'] = $this->owner->Title;

        $this->owner->extend('updateSearchIndexData',$data);

        $data['id'] = $this->owner->ID;
        $data['class'] = $this->owner->ClassName;
        return $data;
    }
}
