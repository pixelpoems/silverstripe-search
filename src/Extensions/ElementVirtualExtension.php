<?php

namespace Pixelpoems\Search\Extensions;

use SilverStripe\Core\Extension;

class ElementVirtualExtension extends Extension
{
    public function addSearchData($data)
    {
       // Gets Search Index Data of Linked Element
        return $this->owner->LinkedElement()->getSearchIndexData();
    }
}
