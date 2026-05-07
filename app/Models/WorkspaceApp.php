<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkspaceApp extends Model
{
     protected $fillable = [
        'name',
        'category',
        'url',
        'logo',
        'workspace_tab',
        'created_by',
    ];
}
