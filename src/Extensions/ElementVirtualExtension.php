<?php

namespace Pixelpoems\Search\Extensions;

use SilverStripe\ORM\DataExtension;

class ElementVirtualExtension extends DataExtension
{
    public function addSearchData($data)
    {
       // Gets Search Index Data of Linked Element
        return $this->owner->LinkedElement()->getSearchIndexData();
    }
}
