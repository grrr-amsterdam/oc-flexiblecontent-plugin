# Flexible Content for OctoberCMS

Inspired by the the Advanced Custom Fields PRO plugin for Wordpress, this plugin allows you to
create flexible content blocks for your OctoberCMS website. This plugin will take care of the
editing part, and the rendering part.

This package also contains a [flexible model object field](#flexible-model-object-field) 'fc-model-object' for searchable
select (Select2) that allows you to select a model object and use it in your flexible
content block, without having an eloquent relation to the model.

## Installation

```bash
composer require grrramsterdam/flexiblecontent-plugin
```

## Usage

You can add flexible content blocks by adding them to themes/your-theme/flexible-content/groups.yaml.
This is essentially the `groups` option a [repeater](https://docs.octobercms.com/1.x/backend/forms.html#repeater) field. Each group must specify a unique key and the definition supports the
following options. name, description, icon en fields.

Example:

```yaml
text:
  name: Text
  description: A simple text block
  icon: icon-align-left
  fields:
    text:
      label: Text
      type: richeditor
text_media:
  name: Text with media
  description: A text block with media
  icon: icon-align-left
  fields:
    text:
      label: Text
      type: richeditor
    media:
      label: Media
      type: mediafinder
```

## Flexible model object field

With this field you can create a searchable model object field that can be used in your flexible content block. You can use this field to select a model object without having an eloquent relation to the model.

### Usage

```yaml
fields:
  model_object:
    label: Pick example
    type: fc-model-object
    model: Grrr\FlexibleContent\Models\ExampleModel
    nameFrom: title
    emptyOption: "Select an example item"
    # searchEndpoint: grrr/project-plugin/example-models/search-select-options
```

### Asynchronous select options

By default an `all()` query will be done for the model to dynamically populate the select options.
But this can be a memory issue very quickly. You can specify a `searchEndpoint` option. This should
be a route to an OctoberCMS backend controller that returns a JSON response with the following
format:

```json
{
  "results": [
    {
      "id": 1,
      "text": "Example item 1"
    },
    {
      "id": 2,
      "text": "Example item 2"
    }
  ]
}
```

This packages provides a behaviour `GrrrAmsterdam\FlexibleContent\Behaviors\HasSearchableSelectOptions`
that you can use in a controller that also uses the FormController behaviour. Register it in a
controller like this:

```php
// [plugin path]/controllers/Examples.php

public $implement = [
    'Backend\Behaviors\FormController',
    'GrrrAmsterdam\FlexibleContent\Behaviors\HasSearchableSelectOptions',
];
```

Be the default the search query will be done on the `title` attribute of the model. You can overwrite
the `getSearchableSelectOptions()` method on your controller to change this behaviour.

This behaviour will add a route to the controller with the following backend path:
`{vendor}/{plugin}/{controller}/search-select-options`. You can use this path as the `searchEndpoint`
option.
