<?php
namespace GrrrAmsterdam\FlexibleContent\Components;

use Cms\Classes\ComponentBase;
use Grrr\FlexibleContent\Classes\FlexibleContent as FlexibleContentClass;

class FlexibleContent extends ComponentBase {

    public function componentDetails() {
        return [
            'name' => 'Flexible Content',
            'description' => 'Renders Flexible Content on a Page'
        ];
    }

    public function defineProperties() {
        return [
            'flexibleContent' => [
                'title' => 'FlexibleContent',
                'type' => 'text'
            ]
        ];
    }

    public function onRender() {
        $flexibleContent = new FlexibleContentClass(
            $this->property('flexibleContent') ?: []
        );
        $flexibleContent->addComponents($this->controller);

        return $flexibleContent->render($this->controller);
    }
}
