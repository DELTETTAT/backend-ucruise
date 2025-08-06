<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HiringTemplate extends Model
{
    use HasFactory;
    protected $table = 'hiring_templates';

    protected $fillable = [
        'template_name',
        'title',
        'status',
        'header_image',
        'background_image',
        'watermark',
        'footer_image',
        'name',
        'phone',
        'email',
        'date_of_issue',
        'content',
        'watermarkOpacity',
        'watermarkPosition',
        'headerImagePosition',
        'footerImagePosition',
        'template_type',
        'header_image_scale',
        'footer_image_scale',
        'icon_files',
        'icon_positions',
    ];
    public const PAGINATE = 10;
}
