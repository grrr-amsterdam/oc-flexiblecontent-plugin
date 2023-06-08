<?php namespace GrrrAmsterdam\FlexibleContent\Behaviors;

use Backend\Classes\Controller;
use Illuminate\Database\Eloquent\Builder;
use October\Rain\Database\Model;
use October\Rain\Extension\ExtensionBase;

class HasSearchableSelectOptions extends ExtensionBase {

    protected $parent;

    public function __construct(Controller $parent) {
        $this->parent = $parent;
    }

    public function searchSelectOptions() {
        $searchQuery = request()->get('term');
        $optionLabelFrom = request()->get('name_from', 'title');

        $model = $this->parent->formCreateModelObject();

        $dbQuery = $model->newQuery();

        $items = $dbQuery->when($searchQuery, function (Builder $query) use ($searchQuery) {
            return $this->parent->selectOptionsQuery($query, $searchQuery);
        })->limit(10)->get();

        // Results are expected to be in the format { id: 1, text: 'foo' }
        $results = $items->map(function (Model $item) use ($optionLabelFrom) {
            $primaryKey = $item->getKeyName();
            return [
                'id' => $item->$primaryKey,
                'text' => $item->$optionLabelFrom,
            ];
        });
        $data = [
            'results' => $results->toArray(),
            "pagination" => [
                // TODO: implement pagination
                "more" => false,
            ],
        ];
        return response()->json($data);
    }

    public function selectOptionsQuery(Builder $query, string $searchQuery): Builder {
        return $query->where('title', 'like', '%' . $searchQuery . '%');
    }

}
