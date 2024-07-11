<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider', 'video_id', 'title', 'keywords', 'views', 'rate', 'url', 'embed_url', 'length_sec', 'length_min', 'default_thumb', 'added',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_video');
    }

    public function thumbs()
    {
        return $this->hasMany(Thumb::class);
    }
}
