<?php namespace GrrrAmsterdam\FlexibleContent\Classes;

use Garp\Functional as f;

class FieldValueTransformer {

    protected $config;

    protected $transformers = [];

    public function __construct(Config $config) {
        $this->config = $config;
    }

    public function transform(array $flexible_content) {
        return f\map(
            function ($group) {
                $group_name = f\prop('_group', $group);
                return f\reduce_assoc(
                    function ($acc, $field_value, $field_name) use ($group_name) {
                        return f\prop_set(
                            $field_name,
                            f\always($this->transformField($group_name, $field_name, $field_value)),
                            $acc
                        );
                    },
                    [],
                    $group
                );
            },
            $flexible_content
        );
    }

    public function transformField(string $group_name, string $field_name, $field_value) {
        $transformerClass = f\prop_in(
            [$group_name, 'fields', $field_name, 'transformerClass'],
            $this->config->partials()
        );
        if (!$transformerClass) {
            return $field_value;
        }
        return (new $transformerClass)->transform($field_value);
    }

}
