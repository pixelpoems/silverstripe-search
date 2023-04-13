## WORK IN PROGRESS

# Silverstripe Fuse Search Module
This module provides a fuse.js based search using ajax and configurable indexing.
You can use it in combination with Silverstripe [Elemental](https://github.com/silverstripe/silverstripe-elemental) and [Fluent](https://github.com/tractorcow-farm/silverstripe-fluent). For Elemental and Fluent configuration check the specified documentation below.

* [Requirements](#requirements)
* [Installation](#installation)
* [Configuration](#configuration)
* [Overwrite Template Files](#overwrite-template-files)
* [Inline Search](#inline-search)
* [Enable Search on DataObjects](#enable-search-on-dataobjects)
* [Config to enable Elemental](#config-to-enable-elemental)
* [Config to enable Fluent](#config-to-enable-fluent)


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
By default, your index file is named `index.json` and will be placed at `/public/_resources/search/`

To Update or set the index keys based on the Class you can extend the Class and use the following method to set the values. If you set extra values here, they won't get noticed by the js logic. Only the predefined keys will be recognised. `$data`will contain all preconfigued keys.
```php
public function updateSearchIndexData(array &$data) {
    $data['content'] = $this->owner->Content;
}
```

## Overwrite Template Files
To overwrite the default search templates you can create a `Pixelpoems/FuseSearch` folder within your project templates.
* `Pixelpoems/FuseSearch/Ajax/SearchList.ss` for the rendered Search result.
* `Pixelpoems/FuseSearch/Includes/InlineSearch.ss` for inline Search output.
* `Pixelpoems/FuseSearch/Pages/Layout/SearchPage.ss` for your custom Search Page.

ATTENTION: If you overwrite the templates, make sure that the required js files are included within the templates or will be included via a Controller. Also make sure that the following input is included if you have custom index keys defined in your `PopulateSearch` Task:
```html
<input type="hidden" id="search-index-keys" value="$SearchKeys" />
```

If you need additional Variables within your Ajax SearchList Result Template `Pixelpoems/FuseSearch/Ajax/SearchList.ss` you can extend the `Pixelpoems\FuseSearch\Controllers\SearchController` and update the data with the following hook:
```php
public function updateAjaxTemplateData(&$data) {
    $additionalList = ArrayList::create();
    $data['AdditionalBool'] = true;
    $data['AdditionalList'] = $additionalList
}
```
All the Variables, that are added here can be accessed in your custom `Ajax/SerachList.ss`.

## Inline Search
This module includes an inline search. The listing within the inline search will display up to ten search results and a "See more..." item which navigates to the search page which will display all search results in a list.

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

