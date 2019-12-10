<?php namespace GrrrAmsterdam\FlexibleContent\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Garp\Functional as f;

class ModelObject extends FormWidgetBase {

    protected $defaultAlias = 'fc-model-object';

    public $modelClass;

    public $nameFrom = 'name';

    public $emptyOption;

    public function init() {
        $this->fillFromConfig([
            'modelClass',
            'nameFrom',
            'emptyOption',
        ]);
        parent::init();
    }

    public function render() {
        $this->vars['id'] = $this->getId();
        $this->vars['name'] = $this->getFieldName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['options'] = $this->getFieldOptions();
        $this->vars['emptyOption'] = $this->emptyOption;

        return $this->makePartial('fc-object-field');
    }

    protected function getFieldOptions() {
        $model = new $this->modelClass;
        $items = $model->all();
        $nameFrom = $this->nameFrom;
        $options = $items->mapWithKeys(function ($item) use ($nameFrom) {
            return [$item->id => $item->$nameFrom];
        });
        return $options->toArray();
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

}
