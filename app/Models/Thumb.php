<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thumb extends Model
{
    use HasFactory;

    protected $fillable = ['video_id', 'size', 'width', 'height', 'src'];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
