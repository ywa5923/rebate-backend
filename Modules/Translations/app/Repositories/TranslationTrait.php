<?php

namespace Modules\Translations\Repositories;

use Modules\Translations\Models\Translation;


trait TranslationTrait
{

    function all()
    {
        return Translation::all();
    }

    function create(array $data)
    {
        return Translation::create($data);
    }
    function update(array $data, $id)
    {
        $user = Translation::findOrFail($id);
        $user->update($data);
        return $user;
    }

    function delete($id)
    {
        $user = Translation::findOrFail($id);
        $user->delete();
    }

    function find($id)
    {
        return Translation::findOrFail($id);
    }
}
