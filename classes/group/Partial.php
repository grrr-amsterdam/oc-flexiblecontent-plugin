<?php
namespace GrrrAmsterdam\FlexibleContent\Classes\Group;

use Cms\Classes\Controller as CmsController;

class Partial {

    protected $_properties;

    protected $_alias;

    public function __construct(string $alias, array $properties)
    {
        $this->_properties = $properties;
    }

    public function render(CmsController $controller): string
    {
        return $controller->renderPartial($this->_getPartialName(), $this->_properties);
    }

    public function getAlias() {
        return $this->_alias;
    }

    protected function _getPartialName(): string
    {
        return 'flexible-content/' . $this->_properties['_group'] . '.htm';
    }
}
