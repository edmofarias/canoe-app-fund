<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuplicateWarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'fund_id_1',
        'fund_id_2',
        'resolved',
    ];

    protected $casts = [
        'resolved' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function fund1()
    {
        return $this->belongsTo(Fund::class , 'fund_id_1');
    }

    public function fund2()
    {
        return $this->belongsTo(Fund::class , 'fund_id_2');
    }

    public function scopeUnresolved($query)
    {
        return $query->where('resolved', false);
    }
}
