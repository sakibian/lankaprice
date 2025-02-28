<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'content', 'image', 'author_id'];

    public function tags() {
        return $this->belongsToMany(Tag::class, 'blog_tag');
    }

    // Automatically generate slug when creating a blog post
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            $blog->slug = Str::slug($blog->title);
        });
    }

    // Relationship: Blog belongs to a User (Author)
    public function author()
    {
        return $this->belongsTo(User::class);
    }
}
