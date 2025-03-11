<?php

namespace Modules\Translations\Repositories;

use Modules\Translations\Models\LocaleResource;

trait LocaleResourceTrait
{

    function all()
    {
        return LocaleResource::all();
    }

    function create(array $data)
    {
        return LocaleResource::create($data);
    }
    function update(array $data, $id)
    {
        $locale = LocaleResource::findOrFail($id);
        $locale->update($data);
        return  $locale;
    }

    function delete($id)
    {
        $locale = LocaleResource::findOrFail($id);
        $locale->delete();
    }

    function find($id)
    {
        return LocaleResource::findOrFail($id);
    }
}

