<?php
namespace Pixelpoems\Search\Tasks;

use DNADesign\Elemental\Models\BaseElement;
use Page;
use Pixelpoems\Search\Controllers\SearchController;
use Pixelpoems\Search\Services\SearchService;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use TractorCow\Fluent\Model\Locale;
use TractorCow\Fluent\State\FluentState;

class PopulateSearch extends BuildTask
{
    protected $title = '[SEARCH] Populate';

    protected $description = 'Crate, Re-Create and prepare the silverstripe search index at each run.';

    private static string $segment = "search-populate";

    private static array $exclude_elements = [];

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
            $this->log("##########################################");
            $this->log("Fluent is enabled.");
            $this->log("##########################################\n");

            $prevent_lang_from_index = $this->config()->get('prevent_lang_from_index');

            if($prevent_lang_from_index) {
                $locales = Locale::get()->exclude(['Locale' => $prevent_lang_from_index]);
                $this->log("There are some languages prevented from indexing: \n" . implode(', ', $prevent_lang_from_index));
                $this->log("##########################################\n");

            } else {
                $locales = Locale::get();
            }

            foreach ($locales as $locale) {
                FluentState::singleton()->withState(function(FluentState $state) use ($locale, $config) {
                    $state->setLocale($locale->Locale);
                    $this->populate($config, $locale->Locale, $locale->Locale);
                });
            }
        } else {
            $this->populate($config);

        }
        $this->log('Successfully written search index!');
    }

    private function populate($config, string $fileName = null, string $locale = null)
    {
        if($locale) $this->log('START POPULATING: ' . $locale . "\n");
        else $this->log("START POPULATING\n");

        $this->populatePageData($config, $fileName, $locale);

        if($config['enable_elemental']) {
            $fileName = $this->populateElementData($config, $fileName, $locale);
        }

        if($locale) $this->log($locale . ': SUCCESS' . "\n");
        else $this->log('SUCCESS' . "\n");

        $this->log("##########################################\n");
    }

    private function populatePageData($config, string $fileName = null, string $locale = null)
    {
        $data = $this->getData(Page::class);

        if(!$fileName) $fileName = SearchService::getIndexFile('index');
        $fileName = SearchService::getIndexFile($fileName);
        $this->log('Data Entities (Pages): ' . count($data));
        $this->writeSearchFile($data, $fileName, $locale);

        $this->log($fileName . "\n");
    }

    private function populateElementData($config, string $fileName = null, string $locale = null)
    {
        $this->log('ELEMENTS:');

        $data = [];
        $exclude_elements = (array)$this->config()->get('exclude_elements');
        $availableElementClasses = ClassInfo::subclassesFor(BaseElement::class);

        foreach ($availableElementClasses as $class) {
            if($class !== BaseElement::class) {
                /** @var BaseElement $inst */
                $inst = singleton($class);

                if (!in_array($class, $exclude_elements ?? [])) {
                    $this->log($class);
                    $data = array_merge($data, $this->getData($class));
                }
            }
        }

        if(!$fileName) $fileName = SearchService::getIndexFile('index-elmental');
        $fileName = SearchService::getIndexFile($fileName . '-elmental');
        $this->log('Data Entities (Elements): ' . count($data));
        $this->writeSearchFile($data, $fileName, $locale);

        $this->log($fileName . "\n");

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
        echo $msg . "\n";
    }

    private function writeSearchFile($data, string $fileName, string $locale = null)
    {
        // Check if folder exists
        if(!is_dir(SearchService::getIndexPath())) {
            mkdir(SearchService::getIndexPath(), 0777, true);
        }

        // Check if file exists and clean content
        if(file_exists($fileName)) file_put_contents($fileName, '');

        $file = fopen($fileName, 'w');
        fwrite($file, json_encode($data));
        fclose($file);

        return $fileName;
    }
}
