# Customization

## Overwrite Template Files

To overwrite the default search templates you can create a `Pixelpoems/Search` folder within your project templates.

* `Pixelpoems/Search/Ajax/SearchResultList.ss` for the rendered Search result.
* `Pixelpoems/Search/Includes/InlineSearch.ss` for inline Search output.
* `Pixelpoems/Search/Pages/Layout/SearchPage.ss` for your custom Search Page.

**ATTENTION:** If you overwrite the templates, make sure that the required js files are included within the templates or
will be included via a Controller and the CSS Classes and IDs are there as well. Make sure that the inline search is
wrapped within an element with the classes `search-holder inline-search-holder` and within a `search-holder` class on
the search page template!

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

All the variables that are added here can be accessed in your custom `Ajax/SearchList.ss`.

## Modify Search Results

There are multiple hooks to modify the search result before sending the generated list to the template.

Add an extension to SearchService:
```yml
Pixelpoems\Search\Services\SearchService:
  extensions:
    - Namespace\Extensions\SearchServiceExtension
```

### Modify List BEFORE Limiting Result

Use the hook `updateSearchResultBeforeLimit` for instance to filter the results on a specific Multisite if you use e.g. https://github.com/symbiote/silverstripe-multisites:

```php
public function updateSearchResultBeforeLimit(&$list): void
{
    $currentSiteID = Multisites::inst()->getCurrentSiteId();
    $list = $list->filter(['SiteID' => $currentSiteID]);
}
```

The limitation of the list (for Inline Search) will be added after the hook.

### Modify List AFTER Limiting Result

Use the hook `updateSearchResultAfterLimit` for instance to filter the results after the limitation has been added to the list:

```php
public function updateSearchResultAfterLimit(&$list): void
{
    // Do some extra filter or attachment here
}
```

This hook will only be called on a request that is made by the inline search!

If you want to modify the result after limitation for inline search AND on the Search page, use the hook `updateSearchResult`:

```php
public function updateSearchResult(&$list): void
{
    // Do some extra filter or attachment here
}
```