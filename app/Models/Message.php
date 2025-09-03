<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory;
    use SoftDeletes; // Active les suppressions douces

    protected $fillable = ['text_message', 'user_id', 'file_id', 'societe_id', 'folder_id', 'is_read', 'parent_id', 'commentaire'];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function societe()
    {
        return $this->belongsTo(Societe::class);
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }
    public function parent()
    {
        return $this->belongsTo(Message::class, 'parent_id');
    }

     public function replies()
    {
        return $this->hasMany(Message::class, 'parent_id');
    }
}
