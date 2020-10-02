<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable  = [
        'id',
        'tittle',
        'content',
        'image',
        'author_id'
    ];

    protected $dates = ['deleted_at'];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function scopeSearch($query, $filter){
        return $query->where('id', 'like', '%'.$filter.'%')
                     ->orwhere('tittle', 'like', '%'.$filter.'%')
                     ->orWhere('content', 'like', '%'.$filter.'%');
    }
}
