<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fund extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'start_year',
        'fund_manager_id',
    ];

    protected $casts = [
        'start_year' => 'integer',
        'deleted_at' => 'datetime',
    ];

    public function fundManager()
    {
        return $this->belongsTo(FundManager::class);
    }

    public function aliases()
    {
        return $this->hasMany(Alias::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }
}
