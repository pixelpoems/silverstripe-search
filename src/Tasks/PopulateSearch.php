<?php
namespace Pixelpoems\Search\Tasks;

use DNADesign\Elemental\Models\BaseElement;
use DNADesign\ElementalVirtual\Model\ElementVirtual;
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

    protected $config;

    /**
     * @inheritDoc
     */
    public function run($request)
    {
        $this->config = Config::inst()->get(SearchController::class);

        if($this->config['enable_fluent']) {
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
                FluentState::singleton()->withState(function(FluentState $state) use ($locale) {
                    $state->setLocale($locale->Locale);
                    $this->populate($locale->Locale, $locale->Locale);
                });
            }
        } else {
            $this->populate();

        }
        $this->log('Successfully written search index!');
    }

    private function populate(string $fileName = '', string $locale = null)
    {
        if($locale) $this->log('START POPULATING: ' . $locale . "\n");
        else $this->log("START POPULATING\n");

        $this->populatePageData($fileName, $locale);

        if($this->config['enable_elemental']) {
            $this->populateElementData($fileName, $locale);
        }

        if($locale) $this->log($locale . ': SUCCESS' . "\n");
        else $this->log('SUCCESS' . "\n");

        $this->log("##########################################\n");
    }

    private function populatePageData(string $fileName = null, $locale = null)
    {
        $data = $this->getData(Page::class, $locale);

        if(!$fileName) $fileName = SearchService::getIndexFile('index');
        else $fileName = SearchService::getIndexFile($fileName);
        $this->log('Data Entities (Pages): ' . count($data));
        $this->writeSearchFile($data, $fileName);

        $this->log($fileName . "\n");
    }

    private function populateElementData(string $fileName = null, $locale)
    {
        $this->log('ELEMENTS:');

        $data = [];
        $exclude_elements = (array)$this->config()->get('exclude_elements');
        $availableElementClasses = ClassInfo::subclassesFor(BaseElement::class);

        foreach ($availableElementClasses as $class) {
            if($class !== BaseElement::class) {
                if (!in_array($class, $exclude_elements ?? [])) {
                    $this->log($class);
                    $data = array_merge($data, $this->getData($class, $locale));
                }
            }
        }

        if(!$fileName) $fileName = SearchService::getIndexFile('index-elemental');
        else $fileName = SearchService::getIndexFile($fileName . '-elemental');
        $this->log('Data Entities (Elements): ' . count($data));
        $this->writeSearchFile($data, $fileName);

        $this->log($fileName . "\n");

    }

    private function getData($class, $locale = null): array
    {
        if($class === Page::class) {
            $objects = Versioned::get_by_stage($class, 'Live')
                ->filter(['ShowInSearch' => true]);
        } else {
            $objects = Versioned::get_by_stage($class, 'Live');
        }

        $data = [];
        foreach($objects as $object) {
            if($this->config['enable_fluent']) {
                if($object->isPublishedInLocale($locale)) {
                    $data[] = DataObject::get_by_id($class, $object->ID)->getSearchIndexData();
                }
            } else {
                $data[] = DataObject::get_by_id($class, $object->ID)->getSearchIndexData();
            }
        }
        return $data;
    }

    private function log($msg) {
        echo $msg . "\n";
    }

    private function writeSearchFile($data, string $fileName): string
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
