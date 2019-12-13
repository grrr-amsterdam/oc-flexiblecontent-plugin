<?php
namespace GrrrAmsterdam\FlexibleContent\Classes;

use File;
use Event;
use Cms\Classes\Theme;
use Garp\Functional as f;
use GrrrAmsterdam\FlexibleContent\Classes\ComponentGroup;

class GroupManager {

    protected $_config;

    public function __construct(Config $config)
    {
        $this->_config = $config;
    }

    public function getGroupsConfig()
    {
        $groupsConfig = array_merge(
            $this->_config->partials(),
            $this->_getComponentGroupConfig()
        );

        $alteredGroupsConfig = Event::fire('flexibleContent.alterGroupsConfig', [$groupsConfig], true);

        return $alteredGroupsConfig ?: $groupsConfig;
    }

    protected function _getComponentGroupConfig()
    {
        $componentManager = \Cms\Classes\ComponentManager::instance();
        return f\reduce(
            function ($o, $component) use ($componentManager) {
                $componentGroup = new ComponentGroup(
                    $componentManager->makeComponent('\\' . $component['class'])
                );
                return f\prop_set(
                    'component_' . $component['name'],
                    $componentGroup->config(),
                    $o
                );
            },
            [],
            config('grrramsterdam.flexiblecontent::components')
        );
    }

}
