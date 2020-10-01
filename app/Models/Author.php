<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'id',
        'name',
        'last_name',
    ];

    protected $dates = ['deleted_at'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function scopeSearch($query, $filter){
        return $query->where('id', 'like', '%'.$filter.'%')
                     ->orwhere('name', 'like', '%'.$filter.'%')
                     ->orWhere('last_name', 'like', '%'.$filter.'%');
    }
}
