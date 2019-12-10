<?php
namespace GrrrAmsterdam\FlexibleContent\Classes;

use Garp\Functional as f;
use Lang;
use System\Classes\PluginManager;

/**
 * This should be extracted to a Component class
 * Now it is used for field configuration
 */
class ComponentGroup {

    const DEFAULT_ICON = 'icon-puzzle-piece';

    protected $component;

    public function __construct(\Cms\Classes\ComponentBase $component)
    {
        $this->component = $component;
    }

    public function config()
    {
        return [
            'name'        => Lang::get($this->component->componentDetails()['name']),
            'description' => Lang::get($this->component->componentDetails()['description']),
            'icon'        => $this->_getIcon(),
            'fields'      => $this->_composeFields(),
        ];
    }

    protected function _composeFields()
    {
        return f\reduce_assoc(
            function ($o, $config, $name) {
                return f\prop_set(
                    $name,
                    $this->_composeFieldConfigFromPropertyConfig($name, $config),
                    $o
                );
            },
            [],
            $this->component->defineProperties()
        );
    }

    protected function _composeFieldConfigFromPropertyConfig($name, $config)
    {
        $out = [
            'span' => 'left'
        ];

        $out['label'] = Lang::get($config['title']);
        $out['comment'] = Lang::get(f\prop('description', $config));
        $out['type'] = $this->_extractFieldTypeFromProperty($config);
        $out['required'] = !!f\prop('required', $config);

        if ($config['type'] === 'dropdown') {
            $out['options'] = $this->_getDropdownOptions($name, $config);
        }
        if ($config['type'] === 'datepicker') {
            $out['mode'] = f\prop('mode', $config) ?: 'datetime';
        }
        $out['placeholder'] = f\prop('placeholder', $config);
        $out['dependsOn'] = f\prop('depends', $config);
        $out['trigger'] = f\prop('trigger', $config);

        $out['readOnly'] = f\prop('readOnly', $config);
        $out['hidden'] = f\prop('hidden', $config);

        if (f\prop('default', $config)) {
            $out['default'] = f\prop('default', $config);
            $out['attributes'] = [
                // Due to an October bug where default values are not honoured in Repeater fields, we
                // have to hack our way around this, using Javascript.
                // @see assets/javascript/repeater.js
                'data-default-value' => f\prop('default', $config)
            ];
        }
        return $out;
    }

    protected function _getDropdownOptions(string $name, array $config): array
    {
        $getOptionsMethod = 'get' . ucfirst($name) . 'Options';
        return f\prop('options', $config) ?:
            $this->component->$getOptionsMethod();
    }

    protected function _extractFieldTypeFromProperty($property): string
    {
        $propertyType = f\prop('type', $property);
        if (!$propertyType || $propertyType === 'string') {
            return 'text';
        }
        return $propertyType;
    }

    protected function _getIcon(): string
    {
        $plugin = $this->_getPluginByComponent();
        if (!$plugin) {
            return self::DEFAULT_ICON;
        }
        $pluginDetails = $plugin->pluginDetails();
        return isset($pluginDetails['icon'])
            ? $pluginDetails['icon']
            : self::DEFAULT_ICON;
    }

    protected function _getPluginByComponent(): \System\Classes\PluginBase
    {
        $pluginManager = PluginManager::instance();
        $plugins = $pluginManager->getPlugins();
        return f\find(
            function ($plugin) {
                if (!is_array($plugin->registerComponents())) {
                    return false;
                }
                $componentClassNames = array_keys($plugin->registerComponents());
                return in_array(
                    get_class($this->component),
                    $componentClassNames
                );
            },
            $plugins
        );
    }
}
