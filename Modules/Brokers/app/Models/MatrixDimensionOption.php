<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Brokers\Models\Matrix;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\MatrixHeader;

class MatrixDimensionOption extends Model
{
    protected $fillable = [
        'matrix_id',
        'broker_id',
        'matrix_header_id',
    ];

    protected $casts = [
        'value' => 'array',
        'public_value' => 'array',
    ];

    /**
     * Get the matrix that owns the header option.
     */
    public function matrix(): BelongsTo
    {
        return $this->belongsTo(Matrix::class);
    }

    /**
     * Get the broker that owns the header option.
     */
    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }
  
    public function matrixDimension(): BelongsTo
    {
        return $this->belongsTo(MatrixDimension::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(MatrixHeader::class,'option_id');
    }

   

    public function optionSlug(): string
    {
        return $this->option->slug;
    }

    public function optionTitle(): string
    {
        return $this->option->title;
    }
    
} 