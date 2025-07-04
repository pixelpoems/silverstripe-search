# Silverstripe Search Module

[![stability-beta](https://img.shields.io/badge/stability-beta-33bbff.svg)](https://github.com/mkenney/software-guides/blob/master/STABILITY-BADGES.md#beta)

This module provides a silverstripe search using ajax and configurable indexing.
You can use it in combination with Silverstripe [Elemental](https://github.com/silverstripe/silverstripe-elemental)
and [Fluent](https://github.com/tractorcow-farm/silverstripe-fluent). For Elemental and Fluent configuration check the
specified documentation below.

* [Requirements](#requirements)
* [Installation](#installation)
* [Configuration](#configuration)
* [Populate Task](#populate-task)
* [Overwrite Template Files](#overwrite-template-files)
* [Inline Search](#inline-search)
* [Enable Search on DataObjects](#enable-search-on-dataobjects)
* [Config to enable Elemental](#config-to-enable-elemental)
* [Config to enable Fluent](#config-to-enable-fluent)
* [Available Config Variables](#available-config-variables)

## Requirements

* Silverstripe CMS ^6.0
* Silverstripe Framework ^6.0

## Installation

```
composer require pixelpoems/silverstripe-search
```

This module includes

* `Populate Search Task` - Task to create or update the search index
* `Search Page` - Separate Search Page
* `Inline Search` - Template Include

## Configuration

Without any special configurations all pages and subclasses of page, which have the "Show in Search" Checkbox checked,
will be indexed. For indexing you have to run the `PopulateSearch` Task (More Details: [Populate Task](#populate-task)).

Following variables can be used to configure your search index:

```yml
Pixelpoems\Search\Services\SearchConfig:
  index_keys: # Default keys witch will be populated within the json index file
    - title # Title will be added by default if nothing else is defined
    - content # Additional index key
```

Those keys are only for separating data within the index (more or less for your own structure - this keys won't be
displayed in the search result!). The search prioritises the content based on the keys - `title` has the first priority
in this case and is therefore also displayed first in the result.

By default, your index file is named `index.json` and will be placed at `/search/` within your project root directory.
Add `/search` to your `.gitignore` to prevent index files from being pushed to your git repository.

To Update or set the index keys based on the Class you can extend the Class and use the following method to set the
values. If you set extra values here, they won't get noticed by the js logic. Only the predefined keys will be
recognised. `$data`will contain all preconfigured keys.

```php
public function updateSearchIndexData(array &$data)
{
    $data['content'] = $this->owner->Content;
}
```

To update the data without extension e.g. on a new PageType you can add the following function to your new Page Type
with your custom list, which should be indexed:

```php
public function getList()
{
    return DataObject::get();
}

public function addSearchData($data)
{
    $tags[] = $this->getList()->map()->values();

    // Make sure other tags do not get overwritten by this function
    if($data['tags']) $data['tags'] = array_merge($data['tags'], $tags);
    else $data['tags'] = $tags;

    return $data;
}
```

To add multiple values e.g. Tags or something similar you can do like this:

```php
public function addSearchData($data)
{
    $tags = [];

    // Add Name of an HasOne relation
    if($this->HasOneID) {
        $tags[] = $this->HasOne()->Name;
    }

    // Add Names of an HasMany relations
    if($this->HasMany()->exists()) {
        foreach ($this->HasMany() as $item) {
            $tags[] = $item->Name;
        }
    }

    // Make sure other tags do not get overwritten by this function
    if($data['tags']) $data['tags'] = array_merge($data['tags'], $tags);
    else $data['tags'] = $tags;

    return $data;
}
```

You can use the `SearchService::escapeHTML($string)` function to escape your content before adding it to the index.

## Populate Task

To create or update the search index use the "Search Populate" Task:

```/dev/tasks/search-populate```

```shell
php vendor/silverstripe/framework/cli-script.php dev/tasks/search-populate
```

This Task will create based on your configuration an `index.json` and an `index-elemental.json` (if elemental is
enabled) or `locale.json` and `locale-elemental.json` (if fluent is enabled).

You can extend the search population and add additional Data like that:
```yml
Pixelpoems\Search\Services\PopulateService:
  extensions:
    - Namespace\PopulateSearchExtension
```

```php
public function populateAdditionalData($pageIndexFileName, $locale, &$additionalData)
{
    $dataObjects = ClassInfo::subclassesFor(DataObject::class);
    foreach ($dataObjects as $dataObject) {
        $additionalData = $this->owner->getData($dataObject, $locale);

        // If you want to add some additional Filters to the request you can do it like this
        // $additionalData = $this->owner->getData($dataObject, $locale, ['isActive' => true]);

        $this->owner->log('Data Entities (DataObject / '. $dataObject . '): ' . count($additionalData));

        // Add your additional data to the given array to return this data!
        $additionalData = array_merge($additionalData, $additionalData);
    }

    $this->owner->log($pageIndexFileName . "\n");
}
```
Your Data will be saved within `index.json` or `locale.json`

ATTENTION: Make sure your DataObject has a Link function, so that it can be linked within the search result.

If you want to populate custom data without an DataObject you can add a "link" key to your custom array:
```php
public function populateAdditionalData($pageIndexFileName, $locale, &$additionalData)
{
    $additionalData[] = [
        'title' => 'Title',
        'id' => 123456789, # Make sure to add an identifier here
        'content' => 'Content',
        'link' => '/link-to-your-site',
    ];

    $this->owner->log($pageIndexFileName . "\n");
}
```
This will generate a custom Array Data with your context - in the ideal case the array contains the keys `id`, `link` and your defined `index_keys`.

## Overwrite Template Files

To overwrite the default search templates you can create a `Pixelpoems/Search` folder within your project templates.

* `Pixelpoems/Search/Ajax/SearchResultList.ss` for the rendered Search result.
* `Pixelpoems/Search/Includes/InlineSearch.ss` for inline Search output.
* `Pixelpoems/Search/Pages/Layout/SearchPage.ss` for your custom Search Page.

_ATTENTION: If you overwrite the templates, make sure that the required js files are included within the templates or
will be included via a Controller and the CSS Classes and IDs are there as well. Make sure that the inline search is
wrapped within an element with the classes `search-holder inline-search-holder` and within a `search-holder` class on
the search page template!_

If you need additional Variables within your Ajax Search Result List Template `Pixelpoems/Search/Ajax/SearchResultList.ss` you
can extend the `Pixelpoems/Search/Controllers/SearchController` and update the data with the following hook:

```php
public function updateAjaxTemplateData(&$data)
{
    $additionalList = ArrayList::create();
    $data['AdditionalBool'] = true;
    $data['AdditionalList'] = $additionalList
}
```

All the Variables, that are added here can be accessed in your custom `Ajax/SerachList.ss`.

## Inline Search

This module includes an inline search. The listing within the inline search will display up to ten search results and
a "See more..." item which navigates to the search page which will display all search results in a list.

Include the InlineSearch Template within your template:
```ss
<% include Pixelpoems\Search\Includes\InlineSearch %>
```

## Enable Search on DataObjects

If you want to add data of an DataObject you can add text like described in the [Configuration](#configuration) section indexing a page or an element.
Here you can add e.g. the `title` and `content` within the index process of a single page along to all other objects.

```php
public function addSearchData($data)
{
    $data = [];

    foreach (DataObject::get() as $dataObject) {
        $data[] = $dataObject->Title . ' '. $dataObject->Content;
    }

    $data['dataObjects'] = implode(' ', $data);

    return $data;
}
```

## Config to enable Elemental

To enable indexing elemental add the following to your configuration yml:

```yml
Pixelpoems\Search\Services\SearchConfig:
  enable_elemental: true
```

Furthermore, you can use `exclude_elements_from_index` to prevent specific Element Classes from being indexed:

```yml
Pixelpoems\Search\Services\SearchConfig:
  exclude_elements_from_index:
    - Namespace\Elements\Element
```

And add the `SearchIndexExtension` to the Base Element Model:

```yml
DNADesign\Elemental\Models\BaseElement:
  extensions:
    - Pixelpoems\Search\Extensions\SearchIndexExtension
```

After adding the extension you can use the `updateSearchIndexData` hook to specify your index data.

### Enable Virtual Element Indexing

If you use Virtual Elements from DNADesign and you want to index the Connected Data for this element you can add the
following Extension to the base "ElementVirtual" Class, this will handle the default indexing with the "Linked Element"
Data.

```yml
DNADesign\ElementalVirtual\Model\ElementVirtual:
  extensions:
    - Pixelpoems\Search\Extensions\ElementVirtualExtension
```

## Config to enable Fluent

To enable fluent within the index and search process add the following to your configuration yml:

```yml
Pixelpoems\Search\Services\SearchConfig:
  enable_fluent: true
```

If you enabled fluent threw the config the `Populate Search Task` will create an index file for every locale. To prevent
a locale from beeing indexed you can add the Locale title within the static variable `exclude_locale_from_index` like
this:

```yml
Pixelpoems\Search\Services\SearchConfig:
  exclude_locale_from_index:
    - 'de_AT'
    - 'de_DE'
```

By default, your index files are named `{locale}.json`, e.g. `de_AT.json`.

## Modify Search Result
There are multiple hooks to modify the search result before sending the generated list to the template:

Add an extension to SearchService:
```yml
Pixelpoems\Search\Services\SearchService:
  extensions:
    - Namespace\Extensions\SearchServiceExtension
```

### Modify List BEFORE limiting result

Use the hook `updateSearchResultBeforeLimit` for instance to filter the results on a specific Mulitsite if you use e.g. https://github.com/symbiote/silverstripe-multisites
```php
    public function updateSearchResultBeforeLimit(&$list): void
    {
        $currentSiteID = Multisites::inst()->getCurrentSiteId();
        $list = $list->filter(['SiteID' => $currentSiteID]);
    }
```
The limitation of the list (for Inline Search) will be added after the hook.


### Modify List AFTER limiting result
Use the hook `updateSearchResultAfterLimit` for instance to filter the results after the limitation has been added to the list.
```php
    public function updateSearchResultAfterLimit(&$list): void
    {
        // Do some extra filter or attachment here
    }
```
This will hook will only be called on a request that is made by the inline search!
If you want to modify the result after limitation for inline search AND on the Search page use the hook `updateSearchResult`:
```php
    public function updateSearchResult(&$list): void
    {
        // Do some extra filter or attachment here
    }
```

## Available Config Variables

Every config can be made via the `Pixelpoems\Search\Services\SearchConfig` class:

| Name                        | Default     |
|-----------------------------|-------------|
| enable_default_style        | `true`      |
| index_keys                  | `['title']` |
| enable_fluent               | `false`     |
| exclude_locale_from_index   | `[]`        |
| enable_elemental            | `false`     |
| exclude_elements_from_index | `[]`        |
| max_results_inline          | `10`        |

```yml
---
Name: my-search-config
---

Pixelpoems\Search\Services\SearchConfig:
  enable_default_style: false # Disables default styles
  index_keys:
    - title
    - content
  enable_fluent: true
  exclude_locale_from_index:
    - 'de_AT'
    - 'de_DE'
  enable_elemental: true
  exclude_elements_from_index:
    - 'Namespace\Elements\Element'
  max_results_inline: 10
```

## Reporting Issues

Please [create an issue](https://github.com/pixelpoems/silverstripe-search/issues) for any bugs you've found, or
features you're missing.

## Credits
Search and Close icons from Feather Icons - https://feathericons.com/

