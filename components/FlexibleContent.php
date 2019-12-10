<?php
namespace GrrrAmsterdam\FlexibleContent\Components;

use Cms\Classes\ComponentBase;

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
        $flexibleContent = new \Grrr\FlexibleContent\Classes\FlexibleContent(
            $this->property('flexibleContent') ?: []
        );
        $flexibleContent->addComponents($this->controller);

        return $flexibleContent->render($this->controller);
    }
}
