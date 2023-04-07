# silverstripe-fuse-search
This module provides a fuse.js based search using ajax and configurable indexing.
You can use it in combination with Silverstripe [Elemental](https://github.com/silverstripe/silverstripe-elemental) and [Fluent](https://github.com/tractorcow-farm/silverstripe-fluent). For Elemental and Fluent configuration check the specified documentation below.

## Requirements
* Silverstripe CMS ^4.0
* Silverstripe Framework ^4.0
* Versioned Admin ^1.0

## Installation
```
composer require pixelpoems/silverstripe-fuse-search
```

This module includes
* `Populate Search Task` - Task to create or update the search index
* `Search Page` - Separate Search Page
* `Inline Search` - Template Include

## Configuration
Without any special configurations all pages will be indexed, which have the "Show in Search" Checkbox checked. For indexing you have to run the `PopulateSearch` Task.

Following variables can be used to configure your search index:
```yml
Pixelpoems\FuseSearch\Tasks\PopulateSearch:
  path: './_resources/search/' # Default Path to save the index file
  keys: # Default keys witch will be populated within the json index file
    - title # Title will be added by default if nothing else is defined
```
By default, your index file is named `search-index.json`.

To Update or set the index keys based on the Class you can extend the Class and use the following method to set the values. If you set extra values here, they wont get noticed by the js logic. Only the predefined keys will be recognised. `$data`will contain all preconfigued keys.
```php
public function updateSearchIndexData(array &$data) {
    $data['content'] = $this->owner->Content;
}
```

## Override Template Files

## Enable Search on DataObjects
TODO

## Config to enable Elemental
To enable indexing elemental add the following to your configuration yml:
```yml
Pixelpoems\FuseSearch\Controllers\SearchController:
  enable_elemental: true
```

Furthermore, you can use `exclude_elements` to prevent specific Element Classes from being indexed:
```yml
Pixelpoems\FuseSearch\Controllers\SearchController:
  exclude_elements:
    -  Namespace\Elements\Element
```

And add the `SearchIndexExtension` to the Base Element Model:
```yml
DNADesign\Elemental\Models\BaseElement:
  extensions:
    - Pixelpoems\FuseSearch\Extensions\SearchIndexExtension`
```
After adding the extension you can use the `updateSearchIndexData` hook to specify your index data.


## Config to enable Fluent
To enable fluent within the index and search process add the following to your configuration yml:
```yml
Pixelpoems\FuseSearch\Controllers\SearchController:
  enable_fluent: true
```
If you enabled fluent threw the config the `Populate Search Task` will create an index file for every locale. To prevent a locale from beeing indexed you can add the Locale title within the static variable `prevent_lang_from_index` like this:

```yml
Pixelpoems\FuseSearch\Tasks\PopulateSearch:
  prevent_lang_from_index:
    - 'de_AT'
    - 'de_DE'
```
By default, your index files are named `{locale}.json`, e.g. `de_AT.json`.


## Reporting Issues
Please [create an issue](https://github.com/pixelpoems/silverstripe-fuse-search/issues) for any bugs you've found, or features you're missing.

