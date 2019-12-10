<?php namespace GrrrAmsterdam\FlexibleContent\FormWidgets\ModelObject;

use Garp\Functional as f;

class Transformer {

    public function transform($value) {
        $modelClass = f\prop('modelClass', $value);
        $id = f\prop('id', $value);

        if (!($modelClass && $id)) {
            return null;
        }
        $modelInstance = new $modelClass;
        return $modelInstance->find($id);
    }

}
