<?php namespace GrrrAmsterdam\FlexibleContent\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Backend\Facades\Backend;
use Garp\Functional as f;
use Illuminate\Database\Eloquent\Collection;
use October\Rain\Database\Model;

class ModelObject extends FormWidgetBase {

    protected $defaultAlias = 'fc-model-object';

    public $modelClass;

    public $nameFrom = 'name';

    public $emptyOption;

    public ?string $searchEndpoint = null;

    public string $optionLabelFrom = 'title';

    public function init() {
        $this->fillFromConfig([
            'modelClass',
            'nameFrom',
            'emptyOption',
            'searchEndpoint',
        ]);
        parent::init();
    }

    public function loadAssets()
    {
        $assetsPath = '/plugins/grrramsterdam/flexiblecontent/formwidgets/modelobject/assets';
        $this->addJs($assetsPath ."/js/modelobject.js", [
            "build" => "Grrr.FlexibleContent",
            "defer" => true,
        ]);
    }

    public function render() {
        $this->vars['id'] = $this->getId();
        $this->vars['name'] = $this->getFieldName();
        $this->vars['value'] = $this->getLoadValue();

        $this->vars['options'] = $this->getFieldOptions($this->vars['value']);
        $this->vars['emptyOption'] = $this->emptyOption;
        $this->vars['searchEndpoint'] = $this->getSearchEndpoint();
        $this->vars['nameFrom'] = $this->nameFrom;
        return $this->makePartial('fc-object-field');
    }


    public function getLoadValue() {
        $value = parent::getLoadValue();
        return $value ? f\prop('id', $value) : $value;
    }

    public function getSaveValue($value) {
        return [
            'modelClass' => $this->modelClass,
            'id' => $value
        ];
    }

    /**
     * Get field options for select
     *
     * @param string $currentValue  The current value of the field (the id of the model)
     *                              to be able to set the selected option
     * @return array
     */
    protected function getFieldOptions(?string $currentValue): array {
        $model = new $this->modelClass;
        $items = $this->searchEndpoint
            ? $this->getLatestItemsIncludingCurrent($model, $currentValue)
            : $model->all();

        $nameFrom = $this->nameFrom;
        $options = $items->mapWithKeys(function ($item) use ($nameFrom) {
            $primaryKey = $item->getKeyName();
            return [$item->$primaryKey => $item->$nameFrom];
        });
        return $options->toArray();
    }

    protected function getSearchEndpoint() {
        if (!$this->searchEndpoint) {
            return null;
        }
        return Backend::url($this->searchEndpoint);
    }

    protected function getLatestItemsIncludingCurrent(Model $model, ?string $currentValue): Collection {
        $primaryKey = $model->getKeyName();

        $items = $model->where($primaryKey, $currentValue)->get();
        return $items->merge($model->limit(10)->get());
    }
}
