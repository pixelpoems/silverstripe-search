# Advanced Configuration

## Enable Search on DataObjects

If you want to add data of a DataObject, you can add text like described in the [Configuration](configuration.md) section when indexing a page or an element. Here you can add e.g. the `title` and `content` within the index process of a single page along with all other objects.

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

## Elemental Integration

To enable indexing of Elemental blocks, add the following to your configuration yml:

```yml
Pixelpoems\Search\Services\SearchConfig:
  enable_elemental: true
```

Furthermore, you can use `exclude_elements_from_index` to prevent specific Element classes from being indexed:

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

After adding the extension, you can use the `updateSearchIndexData` hook to specify your index data.

### Enable Virtual Element Indexing

If you use Virtual Elements from DNADesign and you want to index the Connected Data for this element, you can add the
following Extension to the base "ElementVirtual" Class. This will handle the default indexing with the "Linked Element" data:

```yml
DNADesign\ElementalVirtual\Model\ElementVirtual:
  extensions:
    - Pixelpoems\Search\Extensions\ElementVirtualExtension
```

## Fluent Integration

To enable Fluent localization within the index and search process, add the following to your configuration yml:

```yml
Pixelpoems\Search\Services\SearchConfig:
  enable_fluent: true
```

If you enabled Fluent through the config, the `Populate Search Task` will create an index file for every locale. To prevent
a locale from being indexed, you can add the Locale title within the static variable `exclude_locale_from_index` like this:

```yml
Pixelpoems\Search\Services\SearchConfig:
  exclude_locale_from_index:
    - 'de_AT'
    - 'de_DE'
```

By default, your index files are named `{locale}.json`, e.g. `de_AT.json`.

## Toggle Configuration for Inline Search

The inline search can be configured to show/hide the search bar with a toggle button based on screen size. This is useful for responsive designs where you want to save space on smaller screens.

The toggle behavior is controlled by three configuration options:

```yml
Pixelpoems\Search\Services\SearchConfig:
  enable_toggle_sm: true  # Enable toggle on small screens (below breakpoint)
  enable_toggle_lg: false # Enable toggle on large screens (above breakpoint)
  sm_lg_breakpoint: 768   # Breakpoint in pixels for small/large screens
```

- `enable_toggle_sm`: When `true`, the search bar will be hidden by default on small screens (below the breakpoint) and a toggle button will appear to show/hide it.
- `enable_toggle_lg`: When `true`, the search bar will be hidden by default on large screens (above the breakpoint) and a toggle button will appear to show/hide it.
- `sm_lg_breakpoint`: The screen width in pixels that defines the boundary between small and large screens (default: 768px).

Example configurations:

```yml
# Show toggle button only on mobile devices
Pixelpoems\Search\Services\SearchConfig:
  enable_toggle_sm: true
  enable_toggle_lg: false
  sm_lg_breakpoint: 768
```

```yml
# Show toggle button on all screen sizes
Pixelpoems\Search\Services\SearchConfig:
  enable_toggle_sm: true
  enable_toggle_lg: true
  sm_lg_breakpoint: 768
```

```yml
# Disable toggle functionality (search always visible)
Pixelpoems\Search\Services\SearchConfig:
  enable_toggle_sm: false
  enable_toggle_lg: false
```

To use the toggle configuration, make sure to include the `$SearchToggleAttr` in your inline search template if you overwrite it:

```ss
<div class="search-holder search-holder__inline" $SearchToggleAttr>
    .....
</div>
```