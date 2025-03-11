<?php
namespace Modules\Translations\Repositories;
use App\Repositories\RepositoryInterface;

use Modules\Translations\Models\LocaleResource;
use Illuminate\Database\Eloquent\Collection;

use Modules\Translations\Transformers\LocaleResourceCollection;
use Illuminate\Contracts\Database\Eloquent\Builder;


class LocaleResourceRepository implements RepositoryInterface
{
    use LocaleResourceTrait;

    public function getLocaleMessages(array $langCondition,array $zoneCondition, array $whereConditions, array $whereInConditions)
    {
        $queryBuilder = LocaleResource::with(["translations" => function (Builder $query) use ($langCondition) {
            /** @var Illuminate\Contracts\Database\Eloquent\Builder   $query */
            $query->where(...$langCondition);
        }]);

        $queryBuilder->where(function ($query) use ($zoneCondition) {
            $query->where(... $zoneCondition)
            ->orWhere("is_invariant",1);
        });

        // Apply additional where conditions for key and section params if present
        foreach ($whereConditions as $k => $v) {
            $queryBuilder->where(...$v);
        }
        // Apply whereIn conditions if section param have multiple values
        foreach ($whereInConditions as $k => $v) {
            $queryBuilder->whereIn(...$v);
        }

        return new LocaleResourceCollection($queryBuilder->get());

     
    }
}