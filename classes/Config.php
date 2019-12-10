<?php
namespace GrrrAmsterdam\FlexibleContent\Classes;

use Garp\Functional as f;
use File;
use Cms\Classes\Theme;
use Illuminate\Contracts\FileSystem\FileNotFoundException;

class Config {

    const GROUPS_PATH = '/flexible-content/groups.yaml';

    protected $theme;
    protected $yaml;
    protected $cached;

    function __construct(Theme $theme)
    {
        $this->theme = $theme;
        $this->yaml = new \October\Rain\Parse\Yaml;
    }

    public function getTheme()
    {
        return $this->_theme;
    }

    public function partials()
    {
        return $this->_getConfig(self::GROUPS_PATH);
    }

    protected function _getConfig($filePath)
    {
        if (!f\prop($filePath, $this->cached)) {
            try {
                $file = File::get($this->theme->getPath() . $filePath);
                $this->cached[$filePath] = $this->yaml->parse($file);
            } catch (FileNotFoundException $e) {
                $this->cached[$filePath] = [];
            }
        }
        return f\prop($filePath, $this->cached);
    }

}
