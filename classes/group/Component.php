<?php
namespace GrrrAmsterdam\FlexibleContent\Classes\Group;

use Cms\Classes\Controller as CmsController;

class Component {

    protected $_properties;

    protected $_alias;

    public function __construct(string $alias, array $properties)
    {
        $this->_properties = $properties;
        $this->_alias = $alias;
    }

    public function render(CmsController $controller): string
    {
        return $controller->renderComponent($this->getAlias(), $this->_properties);
    }

    public function getAlias() {
        return $this->_alias;
    }

    public function getProperties() {
        return $this->_properties;
    }

    public function getComponentName() {
        return str_replace('component_', '', $this->_properties['_group']);
    }

}
