<?php

namespace Modules\Translations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Translations\Services\LocaleResourceQueryParser;
use Modules\Translations\Services\LocaleResourceService;
use Modules\Translations\Transformers\LocaleResourceCollection;

class LocaleResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LocaleResourceQueryParser $queryParser,Request $request,LocaleResourceService $localeResourceService):LocaleResourceCollection
    {
       return $localeResourceService->process($queryParser->parse($request));
    }

   
}
