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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('translations::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('translations::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('translations::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
