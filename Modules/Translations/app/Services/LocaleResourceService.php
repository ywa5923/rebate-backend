<?php

namespace Modules\Translations\Services;
use App\Utilities\BaseQueryParser;
use Modules\Translations\Transformers\LocaleResourceCollection;
use Modules\Translations\Repositories\LocaleResourceRepository;

class LocaleResourceService
{
    public function __construct(public LocaleResourceRepository $repository) {}


    /**
     * Processes the query parameters to retrieve locale messages.
     *
     * @param BaseQueryParser $queryParser Instance of query parser containing request parameters.
     * 
     * @return ?LocaleResourceCollection Returns a collection of locale resources or null.
     * 
     * @throws \InvalidArgumentException If the 'lang' or 'zone' parameter is missing.
     */


    public function process(BaseQueryParser $queryParser): ?LocaleResourceCollection
    {
        //$params=$queryParser->getWhereParams();
        $langCondition =  $queryParser->extractWhereParam("lang");
        $zoneCondition =  $queryParser->extractWhereParam("zone");

        if (empty($langCondition)) {
            throw new \InvalidArgumentException("The 'lang' parameter is required.");
        }

        return $this->repository->getLocaleMessages(
            $langCondition,
            $zoneCondition,
            $queryParser->getWhereParams(),
            $queryParser->getWhereInParams()
        );
    }
}
