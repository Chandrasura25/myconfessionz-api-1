<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'count',
        'post_id',
        'shareable_id',
        'shareable_type'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function shareable()
    {
        return $this->morphTo();
    }
}
