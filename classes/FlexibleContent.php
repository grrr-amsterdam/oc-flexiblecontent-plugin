<?php
namespace GrrrAmsterdam\FlexibleContent\Classes;

use Garp\Functional as f;
use GrrrAmsterdam\FlexibleContent\Classes\Group\Partial;
use GrrrAmsterdam\FlexibleContent\Classes\Group\Component;
use Cms\Classes\Controller as CmsController;
use Cms\Classes\Theme;

class FlexibleContent {

    protected $flexible_content;

    function __construct(array $flexible_content)
    {
        $this->flexible_content = $this->prepareFlexibleContent($flexible_content);
    }

    public function prepareFlexibleContent(array $flexibleContent): array {
        $transformer = new FieldValueTransformer(new Config(Theme::getActiveTheme()));
        return $transformer->transform($flexibleContent);
    }

    public function render(CmsController $controller) {
        return f\reduce(
            function ($markup, $group) use ($controller): string {
                return $markup . $group->render($controller);
            },
            '',
            $this->getGroups()
        );
    }

    public function addComponents(CmsController $controller) {
        $groups = $this->getGroups();

        $components = f\filter(
            function($group) {
                return $group instanceof Component;
            },
            $groups
        );

        f\map(
            function($component) use ($controller) {
                $controller->addComponent(
                    $component->getComponentName(),
                    $component->getAlias(),
                    $component->getProperties()
                );
            },
            $components
        );
    }

    public function getGroups(): array
    {
        return f\reduce(
            function ($list, $group) {
                return f\concat(
                    $list,
                    [$this->getGroupObject($group, $list)]
                );
            },
            [],
            $this->flexible_content
        );
    }

    public function getGroupObject(array $group, array $list)
    {
        $componentName = str_replace('component_', '', $group['_group']);
        $alias = $this->_getUniqueComponentName($componentName, $list);
        return strpos(f\prop('_group', $group), 'component_') === 0 ?
            new Component($alias, $group) :
            new Partial($alias, $group);
    }


    protected function _getUniqueComponentName(string $name, array $list): string
    {
        $aliasEquals = f\compose(f\equals($name), f\call('getAlias', []));
        $count = count(f\filter($aliasEquals, $list));
        return $name . ($count ? '_' . $count : '');
    }
}
