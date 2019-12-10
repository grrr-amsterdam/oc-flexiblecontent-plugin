<?php namespace GrrrAmsterdam\FlexibleContent;

use Model;
use Event;
use Backend;
use Cms\Classes\Theme;
use Garp\Functional as f;
use Cms\Classes\Controller;
use System\Classes\PluginBase;
use GrrrAmsterdam\FlexibleContent\Classes\Config;
use GrrrAmsterdam\FlexibleContent\Classes\GroupManager;
use Backend\Classes\Controller as BackendController;
use October\Rain\Exception\ValidationException;

/**
 * FlexibleContent Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'FlexibleContent',
            'description' => 'No description provided yet...',
            'author'      => 'Grrr',
            'icon'        => 'icon-leaf'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
        $this->loadLocalization();
        $this->validateRepeaterFields();
        $this->extendForms();
        $this->extendModels();
        $this->addAssets();
    }

    public function registerComponents() {
        return [
            'GrrrAmsterdam\FlexibleContent\Components\FlexibleContent' => 'flexibleContent'
        ];
    }

    public function registerFormWidgets() {
        return [
            'GrrrAmsterdam\FlexibleContent\FormWidgets\ModelObject' => 'fc-model-object',
        ];
    }

    protected function loadLocalization() {
        $theme = Theme::getActiveTheme();
        if (isset($this->app['translator'])) {
            $this->loadTranslationsFrom(
                $theme->getPath() . '/flexible-content/lang', $theme->getId()
            );
        }
    }

    protected function validateRepeaterFields()
    {
        f\map(function($model) {
            $model::extend(function($model) {
                $model->bindEvent('model.beforeValidate', function() use ($model) {

                    $flexibleContent = $model[static::getFlexColumn($model)];
                    if (!is_array($flexibleContent)) {
                        return;
                    }
                    $groupManager = new GroupManager(new Config(Theme::getActiveTheme()));
                    $groups = $groupManager->getGroupsConfig();
                    $this->_addValidationRulesForFlexibleContent($model, $groups, $flexibleContent);
                });
            });

        }, $this->getModels());
    }

    protected function extendForms() {

        Event::listen('backend.form.extendFields', function($widget)  {
            if (!$this->modelShouldBeExtended($widget->model)) {
                return;
            }
            if (!$this->controllerShouldBeExtended($widget->getController())) {
                return;
            }
            if ($widget->getContext() !== 'create' && $widget->getContext() !== 'update' ) {
                return;
            }

            $groupManager = new GroupManager(
                new Config(Theme::getActiveTheme())
            );
            $groups = $groupManager->getGroupsConfig();

            $flexColumn = static::getFlexColumn($widget->model);

            $widget->addTabFields(
                [
                    $flexColumn => [
                        'label' => 'Flexible Content',
                        'oc.commentPosition' => '',
                        'prompt' => 'Add new section',
                        'span' => 'full',
                        'type' => 'repeater',
                        'groups' => $groups,
                        'tab' => 'Flex',
                        'containerAttributes' => [
                            'data-flexible-content-container' => true
                        ]
                    ]
                ],
                'primary'
            );
        });

    }

    public static function getFlexColumn(Model $model) {
        $modelName = get_class($model);
        $getFlexColumnName = f\compose(
            f\either(f\prop('column'), 'flexible_content'),
            f\find(f\prop_equals('class', $modelName))
        );
        return $getFlexColumnName(config('grrramsterdam.flexiblecontent::models'));
    }

    public function extendModels() {
        $models = collect($this->getModels());
        $models->each(function ($modelClassName) {
            $modelClassName::extend(function ($model) {
                $model->addJsonable(static::getFlexColumn($model));
                $model->bindEvent('model.beforeSave', function () use ($model) {
                    Event::fire('flexibleContent.beforeSave', [&$model]);
                });
            });
        });
    }

    public function addAssets() {
        Event::listen('backend.page.beforeDisplay', function($controller, $action, $params) {
            // dd($controller);
            if ($this->controllerShouldBeExtended($controller)) {
                $controller->addJs(
                    '/plugins/grrramsterdam/flexiblecontent/assets/javascript/repeater.js'
                );
                $controller->addCss(
                    '/plugins/grrramsterdam/flexiblecontent/assets/css/repeater.css'
                );
            }
        });
    }

    protected function getModels() {
        return f\map(f\prop('class'), config('grrramsterdam.flexiblecontent::models'));
    }

    protected function getApplicableControllers() {
        return f\flatten(f\map(f\prop('controller'), config('grrramsterdam.flexiblecontent::models')));
    }

    protected function controllerShouldBeExtended(BackendController $controller) {
        return array_search(
            get_class($controller),
            $this->getApplicableControllers()
        ) !== false;
    }

    protected function modelShouldBeExtended($model) {
        return array_search(get_class($model), $this->getModels()) !== false;
    }

    protected function modelUsesTrait(Model $model, string $trait): bool {
        $uses = class_uses($model);
        return in_array($trait, $uses);
    }

    protected function _addValidationRulesForFlexibleContent(Model $model, array $groups, array $flexibleContent)
    {
        $model->rules = f\reduce_assoc(
            $this->_foldValidationRules($model, $flexibleContent),
            $model->rules,
            $groups
        );
    }

    protected function _foldValidationRules(Model $model, array $flexibleContent): \Closure
    {
        return function ($rules, $groupConfig, $groupName) use ($model, $flexibleContent) {
            $requiredFields = f\filter(
                f\prop_equals('required', true),
                $groupConfig['fields']
            );
            if (!count($requiredFields)) {
                return $rules;
            }
            $indexes = $this->_getGroupIndexes($groupName, $flexibleContent);
            return f\concat(
                $rules,
                $this->_getRulesForNestedFields($model, f\keys($requiredFields), $indexes)
            );
        };
    }

    /**
     * Find the groups in a flexible_content field matching the given $groupName.
     *
     * @param string $groupName
     * @param array  $content   The flexible_content field.
     * @return array The indexes.
     */
    protected function _getGroupIndexes(string $groupName, array $content): array
    {
        return f\keys_where(f\prop_equals('_group', $groupName), $content);
    }

    /**
     * Create an array of validation rules for nested fields in a repeater field.
     * For example: flexible_content.0.title.
     * The index is dependant on the type of group in the given content.
     *
     * @param array $fieldNames Names of required fields in the group.
     * @param array $indexes    Indexes where this group is used in the input.
     * @return array            Usable valdiation rules to be concatted unto $this->rules.
     */
    protected function _getRulesForNestedFields(Model $model, array $fieldNames, array $indexes): array
    {
        return f\reduce(
            function ($rules, $index) use ($model, $fieldNames) {
                // Prefix all fields. Example result: flexible_content.0.title
                $keys = f\map(
                    f\partial('sprintf', static::getFlexColumn($model) . '.%d.%s', $index),
                    $fieldNames
                );
                return f\concat($rules, array_fill_keys($keys, "required"));
            },
            [],
            $indexes
        );
    }
}
