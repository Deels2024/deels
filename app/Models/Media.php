<?php


declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Media extends Model
{
    protected $guarded = [];
    protected $table = 'media';
    protected $appends = array('type', 'path', 'path_url', 'webp_path_url');

    protected $casts = [
        'cdn_profiles' => 'array',
    ];

    public function media_type()
    {
        $type = explode('/', $this->mime_type);

        return $type[0] ?? $this->mime_type;
    }

    public function media_icon_url()
    {
        $ext = str_replace($this->slug.'.', '', $this->slug_ext);
        if (file_exists("./assets/images/ico/{$ext}.jpg")) {
            return asset("assets/images/ico/{$ext}.jpg");
        }

        return asset('assets/images/ico/default.jpg');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function campaign()
    {
        return Campaign::whereJsonContains('images', $this->id)->first();
    }

    public function getTypeAttribute()
    {
        $type = 'image';
        if (Str::contains($this->mime_type, 'video')) {
            $type = 'video';
        }
        return $type;
    }

    public function getPathAttribute()
    {
        if($this->folder) {
            return $this->folder.'/'. $this->slug_ext;
        }
        return $this->getRawOriginal('path') ?: 'uploads/stories/' . $this->slug_ext;
    }


    public function getPathUrlAttribute()
    {
        if($this->folder) {
            return url($this->folder.'/'. $this->slug_ext);
        }
        return url($this->getRawOriginal('path') ?: 'uploads/stories/' . $this->slug_ext);
    }

    public function getWebpPathUrlAttribute()
    {
        if ($this->getTypeAttribute() !== 'image') {
            return null;
        }

        if ($this->path && Str::endsWith($this->path, '.webp')) {
            return url($this->path);
        }

        $webpSlugExt = $this->slug . '.webp';
        $webpPath = $this->folder
            ? rtrim($this->folder, '/') . '/' . $webpSlugExt
            : 'uploads/stories/' . $webpSlugExt;

        return file_exists(public_path($webpPath)) ? url($webpPath) : null;
    }

}
