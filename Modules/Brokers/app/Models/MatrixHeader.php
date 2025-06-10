<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Translations\Models\Translation;

class MatrixHeader extends Model
{
    protected $fillable = ['title', 'description', 'matrix_id', 'type', 'parent_id'];
    protected $table = 'matrix_headers';

    public function matrix(): BelongsTo
    {
        return $this->belongsTo(Matrix::class);
    }
    public function parent()
    {
        return $this->belongsTo(MatrixHeader::class,'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MatrixHeader::class, 'parent_id');
    }

    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    public function formType(): BelongsTo
    {
        return $this->belongsTo(FormType::class);
    }
}
