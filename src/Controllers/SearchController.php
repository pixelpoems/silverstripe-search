<?php

namespace Pixelpoems\FuseSearch\Controllers;

use DNADesign\Elemental\Models\BaseElement;
use Page;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use TractorCow\Fluent\State\FluentState;

class SearchController extends Controller
{
    private static bool $enable_fluent = false;
    private static bool $enable_elemental = false;

    private static array $allowed_actions = [
        'result'
    ];

    public function result(HTTPRequest $request): DBHTMLText|bool|string
    {
        if($this->config()->get('enable_fluent') && $request->getVar('locale')) {
            $requestHTMLLocale = Convert::raw2sql($request->getVar('locale'));
            $locale = str_replace('-', '_', $requestHTMLLocale);
        } else $locale = null;

        $data = json_decode($request->getBody());

        if($this->config()->get('enable_fluent') && $locale) {
            $list = FluentState::singleton()->withState(function (FluentState $state) use ($locale, $data) {
                $state->setLocale($locale);

                return $this->getData($data);
            });
        } else {
            $list = $this->getData($data);
        }

        $this->extend('updateList', $list);

        return $this->generateResponse($locale, $list);
    }

    private function getData($data): ArrayList
    {
        $list = ArrayList::create();
        foreach ($data as $item) {
            if(isset($item->class)) {
                $entity = DataObject::get($item->class)->byID($item->id);
                if($entity) $list->push($entity);
            }
        }
        return $list;
    }

    private function generateResponse($locale, $list)
    {
        if($this->config()->get('enable_fluent')) {
            return FluentState::singleton()->withState(function(FluentState $state) use ($locale, $list) {
                $state->setLocale($locale);

                return $this->customise([
                    'List' => $list
                ])->renderWith('Pixelpoems\\FuseSearch\\Ajax\\SearchList');
            });
        } else {
            return $this->customise([
                'List' => $list
            ])->renderWith('Pixelpoems\\FuseSearch\\Ajax\\SearchList');
        }
    }
}
