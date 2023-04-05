<?php
namespace Pixelpoems\FuseSearch\Tasks;

use SilverStripe\Dev\BuildTask;
use SilverStripe\Versioned\Versioned;
use TractorCow\Fluent\Model\Locale;
use TractorCow\Fluent\State\FluentState;

class PopulateSearch extends BuildTask
{
    /** @var string Task title */
    protected $title = 'Populate Search';

    /** @var string Task description */
    protected $description = 'Re-create and prepare the search json at each run.';

    private static string $segment = "PopulateSearch";

    /**
     * @inheritDoc
     */
    public function run($request)
    {
        $locales = Locale::get();

        foreach ($locales as $locale) {
            FluentState::singleton()->withState(function(FluentState $state) use ($locale) {
                $state->setLocale($locale->Locale);

                $pages = Versioned::get_by_stage('Page', 'Live')
                    ->filter(['ShowInSearch' => true]);

                $pageData = [];
                foreach($pages as $page) {
                    $pageData[] = $this->preparePageData($page);
                }

                $this->writeSearchFile($pageData, $locale->Locale);
            });
        }
    }

    public function log($msg) {
        echo $msg . '<br />';
    }

    private function preparePageData($page): array
    {
        return [
            'id' => $page->ID,
            'title' => $page->Title,
            'b2bTitle' => $page->B2BTitle,
            'summary' => $page->Summary
        ];
    }

    private function writeSearchFile($data, string $locale)
    {
        $locale = str_replace('-', '_', $locale);
        $path = './_resources/search/';
        $fileName = $path . $locale . '.json';

        // Check if folder exists
        if(!is_dir($path)) mkdir($path, 0755, true);

        // Check if file exists and clean content
        if(file_exists($fileName)) file_put_contents($fileName, '');

        $file = fopen($fileName, 'w');
        fwrite($file, json_encode($data));
        fclose($file);

        $this->log('<b>' . $locale . '</b>: SUCCESS');
        $this->log($fileName . '<br /><hr /><br />');
    }
}
