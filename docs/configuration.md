# Configuration

## Basic Configuration

Without any special configurations, all pages and subclasses of page which have the "Show in Search" checkbox checked will be indexed. For indexing, you have to run the `PopulateSearch` Task (More details: [Populate Task](#populate-task)).

The following variables can be used to configure your search index:

```yml
Pixelpoems\Search\Services\SearchConfig:
  index_keys: # Default keys which will be populated within the json index file
    - title # Title will be added by default if nothing else is defined
    - content # Additional index key
```

Those keys are only for separating data within the index (more or less for your own structure - these keys won't be
displayed in the search result!). The search prioritizes the content based on the keys - `title` has the first priority
in this case and is therefore also displayed first in the result.

By default, your index file is named `index.json` and will be placed at `/search/` within your project root directory.
Add `/search` to your `.gitignore` to prevent index files from being pushed to your git repository.

## Available Config Variables

Every config can be made via the `Pixelpoems\Search\Services\SearchConfig` class:

| Name                        | Default     | Description |
|-----------------------------|-------------|-------------|
| enable_default_style        | `true`      | Enable default CSS styles |
| index_keys                  | `['title']` | Keys to be indexed in the search |
| enable_fluent               | `false`     | Enable Fluent localization support |
| exclude_locale_from_index   | `[]`        | Locales to exclude from indexing |
| enable_elemental            | `false`     | Enable Elemental blocks support |
| exclude_elements_from_index | `[]`        | Element classes to exclude from indexing |
| max_results_inline          | `10`        | Maximum results shown in inline search |
| enable_toggle_sm            | `true`      | Enable toggle button on small screens |
| enable_toggle_lg            | `false`     | Enable toggle button on large screens |
| sm_lg_breakpoint            | `768`       | Breakpoint in pixels for small/large screens |

Example configuration:

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

## Customizing Index Data

To update or set the index keys based on the Class, you can extend the Class and use the following method to set the
values. If you set extra values here, they won't get noticed by the js logic. Only the predefined keys will be
recognized. `$data` will contain all preconfigured keys.

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

```
/dev/tasks/Pixelpoems-Search-Tasks-PopulateSearch
```

```shell
vendor/bin/sake tasks:Pixelpoems-Search-Tasks-PopulateSearch
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
    $dataObjects = [
        Product::class
    ];

    // $dataObjects = ClassInfo::subclassesFor(DataObject::class);

    foreach ($dataObjects as $dataObject) {
        $additionalObjectData = $this->owner->getData($dataObject, $locale);

        // If you want to add some additional Filters to the request you can do it like this
        // $additionalData = $this->owner->getData($dataObject, $locale, ['isActive' => true]);

        $this->owner->log('Data Entities (DataObject / '. $dataObject . '): ' . count($additionalObjectData));

        // Add your additional data to the given array to return this data!
        $additionalData = array_merge($additionalData, $additionalObjectData);
    }

    $this->owner->log($pageIndexFileName . "\n");
}
```

Your Data will be saved within `index.json` or `locale.json`.

**ATTENTION:** Make sure your DataObject has a `Link` function, so that it can be linked within the search result.

If you want to populate custom data without a DataObject you can add a `link` key to your custom array:

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