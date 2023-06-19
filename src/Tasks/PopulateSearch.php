<?php
namespace Pixelpoems\Search\Tasks;

use DNADesign\Elemental\Models\BaseElement;
use Page;
use Pixelpoems\Search\Services\SearchConfig;
use Pixelpoems\Search\Services\SearchService;
use ReflectionException;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use TractorCow\Fluent\Extension\FluentExtension;
use TractorCow\Fluent\Extension\FluentVersionedExtension;
use TractorCow\Fluent\Model\Locale;
use TractorCow\Fluent\State\FluentState;

class PopulateSearch extends BuildTask
{
    protected $title = '[SEARCH] Populate';

    protected $description = 'Crate, Re-Create and prepare the silverstripe search index at each run.';

    private static string $segment = "search-populate";

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function run($request)
    {
        if(SearchConfig::isFluentEnabled()) {
            $this->log("##########################################");
            $this->log("Fluent is enabled.");
            $this->log("##########################################\n");

            $exclude_locale_from_index = SearchConfig::getExcludedLocales();

            if($exclude_locale_from_index) {
                $locales = Locale::get()->exclude(['Locale' => $exclude_locale_from_index]);
                $this->log("There are some locales excluded from indexing: \n" . implode(', ', $exclude_locale_from_index));
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

    /**
     * @throws ReflectionException
     */
    private function populate(string $fileName = '', string $locale = null)
    {
        if($locale) $this->log('START POPULATING: ' . $locale . "\n");
        else $this->log("START POPULATING\n");

        $this->populatePageData($fileName, $locale);

        if(SearchConfig::isElementalEnabled()) {
            $this->populateElementData($fileName, $locale);
        }

        if($locale) $this->log($locale . ': SUCCESS' . "\n");
        else $this->log('SUCCESS' . "\n");

        $this->log("##########################################\n");
    }

    private function populatePageData(string $fileName = '', $locale = null)
    {
        $data = $this->getData(Page::class, $locale);

        if(!$fileName) $fileName = SearchService::getIndexFile('index');
        else $fileName = SearchService::getIndexFile($fileName);
        $this->log('Data Entities (Pages): ' . count($data));
        $this->writeSearchFile($data, $fileName);

        $this->log($fileName . "\n");
    }

    /**
     * @throws ReflectionException
     */
    private function populateElementData(string $fileName = '', $locale = null)
    {
        $this->log('ELEMENTS:');

        $data = [];
        $excluded_elements = SearchConfig::getExcludedElements();
        $availableElementClasses = ClassInfo::subclassesFor(BaseElement::class);

        foreach ($availableElementClasses as $class) {
            if($class !== BaseElement::class) {
                if (!in_array($class, $excluded_elements ?? [])) {
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

    private function getData($class, $locale = null)
    {
        if($class === Page::class) {
            $objects = Versioned::get_by_stage($class, 'Live')->filter(['ShowInSearch' => true]);
        } else {
            $objects = Versioned::get_by_stage($class, 'Live');
        }

        $data = [];
        foreach($objects as $object) {
            if(SearchConfig::isFluentEnabled()) {

                if($object->getExtensionInstance(FluentVersionedExtension::class)) {
                    if($object->isPublishedInLocale($locale)) {
                        $data[] = DataObject::get_by_id($class, $object->ID)->getSearchIndexData();
                    }
                } else if ($object->getExtensionInstance(FluentExtension::class)) {
                    if($object->isPublished($locale)) {
                        $data[] = DataObject::get_by_id($class, $object->ID)->getSearchIndexData();
                    }
                } else {
                    $data[] = DataObject::get_by_id($class, $object->ID)->getSearchIndexData();
                }

            } else {
                $data[] = DataObject::get_by_id($class, $object->ID)->getSearchIndexData();
            }
        }
        return $data;
    }

    private function log($msg)
    {
        echo $msg . "\n";
    }

    private function writeSearchFile($data, string $fileName)
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
    }
}
