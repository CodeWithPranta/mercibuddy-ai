<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'favicon',
        'logo',
        'og_image',
        'hero_title',
        'title_text',
        'meta_description',
        'keywords',
        'copyright_text',
    ];
}
