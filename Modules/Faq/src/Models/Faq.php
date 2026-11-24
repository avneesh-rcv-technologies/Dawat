<?php

namespace Modules\Faq\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Blogs\Models\Blog;

class Faq extends Model
{
    protected $fillable = ['question', 'answer', 'sort_order', 'group_name'];

    public function blogs()
    {
        return $this->belongsToMany(Blog::class, 'faq_blog');
    }
}
