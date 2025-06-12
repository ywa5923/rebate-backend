<?php

namespace Modules\Brokers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Brokers\Models\Matrix;
use Modules\Brokers\Models\Broker;
use Modules\Brokers\Models\MatrixHeader;

class MatrixHeaderOption extends Model
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

    /**
     * Get the matrix header that owns the header option.
     */
    public function matrixHeader(): BelongsTo
    {
        return $this->belongsTo(MatrixHeader::class);
    }

    public function OptionHeader(): BelongsTo
    {
        return $this->belongsTo(MatrixHeader::class,'sub_option_id');
    }

   

    public function optionHeaderSlug(): string
    {
        return $this->OptionHeader->slug;
    }

    public function optionHeaderTitle(): string
    {
        return $this->OptionHeader->title;
    }
    
} 