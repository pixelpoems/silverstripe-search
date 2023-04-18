<?php
namespace Pixelpoems\FuseSearch\Tasks;

use DNADesign\Elemental\Models\BaseElement;
use Page;
use Pixelpoems\FuseSearch\Controllers\SearchController;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use TractorCow\Fluent\Model\Locale;
use TractorCow\Fluent\State\FluentState;

class PopulateSearch extends BuildTask
{
    protected $title = 'Populate Search';

    protected $description = 'Crate, Re-Create and prepare the silverstripe fuse search index at each run.';

    private static string $segment = "fuse-search-populate";

    private static array $exclude_elements = [];

    private static string $path = '/_resources/search/';

    private static array $prevent_lang_from_index = []; // e.g. 'de_AT'

    private static array $index_keys = [
        'title'
    ];

    /**
     * @inheritDoc
     */
    public function run($request)
    {
        $config = Config::inst()->get(SearchController::class);

        if($config['enable_fluent']) {
            $this->log('Fluent is enabled.<br /><hr />');

            $prevent_lang_from_index = $this->config()->get('prevent_lang_from_index');

            if($prevent_lang_from_index) {
                $locales = Locale::get()->exclude(['Locale' => $prevent_lang_from_index]);
                $this->log('There are some languages prevented from indexing: ' . implode(', ', $prevent_lang_from_index) . '<br /><hr />');
            } else {
                $locales = Locale::get();
            }

            foreach ($locales as $locale) {
                FluentState::singleton()->withState(function(FluentState $state) use ($locale, $config) {
                    $state->setLocale($locale->Locale);
                    $locale = str_replace('-', '_', $locale->Locale);
                    $this->populate($config, $locale . '.json', $locale);
                });
            }
        } else {
            $this->populate($config);
        }
        $this->log('Successfully written search index!');
    }

    private function populate($config, string $fileName = null, string $locale = null)
    {
        $data = $this->getData(Page::class);

        if($config['enable_elemental']) {
            $exclude_elements = (array) $this->config()->get('exclude_elements');
            $availableElementClasses = ClassInfo::subclassesFor(BaseElement::class);

            foreach ($availableElementClasses as $class) {
                if($class !== BaseElement::class) {
                    /** @var BaseElement $inst */
                    $inst = singleton($class);

                    if (!in_array($class, $exclude_elements ?? []) && $inst->canCreate()) {
                        $elements = $this->getData($class);
                        $data = array_merge($data, $elements);
                    }
                }
            }
        }

        if(!$fileName) $fileName = 'index.json';
        $fileName = $this->getPath() . $fileName;
        $this->log('Data Entities: ' . count($data));
        $this->writeSearchFile($data, $fileName, $locale);
    }

    private function getData($class): array
    {
        $objects = Versioned::get_by_stage($class, 'Live');

        if($class === Page::class) {
            $objects = Versioned::get_by_stage($class, 'Live')
                ->filter(['ShowInSearch' => true]);
        }

        $data = [];
        foreach($objects as $object) {
            $data[] = DataObject::get_by_id($class, $object->ID)->getSearchIndexData();
        }
        return $data;
    }

    private function log($msg) {
        echo $msg . '<br />';
    }

    private function writeSearchFile($data, string $fileName, string $locale = null)
    {
        // Check if folder exists
        if(!is_dir($this->getPath())) {
            mkdir($this->getPath(), 0755, true);
        }

        // Check if file exists and clean content
        if(file_exists($fileName)) file_put_contents($fileName, '');

        $file = fopen($fileName, 'w');
        fwrite($file, json_encode($data));
        fclose($file);

        if($locale) $this->log('<b>' . $locale . '</b>: SUCCESS');
        else $this->log('SUCCESS');

        $this->log($fileName . '<br /><hr />');
    }

    private function getPath(): string
    {
        $path = $this->config()->get('path');
        return PUBLIC_PATH . $path;
    }
}
