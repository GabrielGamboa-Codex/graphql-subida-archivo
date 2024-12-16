<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = "files";
    public $timestamps = false;
    protected $fillable = [
        'filename',
        'path',
        'mimetype'
    ];
}
