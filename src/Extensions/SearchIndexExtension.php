<?php

namespace Pixelpoems\Search\Extensions;

use SilverStripe\Core\Extension;
use Pixelpoems\Search\Services\SearchConfig;
use Pixelpoems\Search\Services\SearchService;

class SearchIndexExtension extends Extension
{
    public function getSearchIndexData()
    {
        $data = [];
        foreach (SearchConfig::getSearchKeys() as $key) {
            $data[$key] = null;
        }

        $data['title'] = $this->owner->Title;

        $this->owner->extend('updateSearchIndexData',$data);
        $data = $this->owner->addSearchData($data);

        foreach ($data as $key => $item) {
            if(gettype($item) === 'array') {
                $item = implode(' ', $item);
            }

            if (!$item) {
                continue;
            }

            $data[$key] = SearchService::escapeHTML($item);
        }

        $data['id'] = $this->owner->ID;
        $data['class'] = $this->owner->ClassName;
        return $data;
    }

    public function addSearchData($data)
    {
        return $data;
    }
}
