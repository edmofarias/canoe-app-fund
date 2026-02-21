<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alias extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'fund_id',
    ];

    public function fund()
    {
        return $this->belongsTo(Fund::class);
    }

    public static function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:aliases,name',
        ];
    }
}
