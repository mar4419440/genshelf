<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function getFullPathAttribute()
    {
        $path = [$this->name];
        $current = $this;

        // Traverse up the tree
        while ($current->parent_id) {
            if (!$current->relationLoaded('parent')) {
                $current->load('parent');
            }
            $current = $current->parent;
            if (!$current) break;
            array_unshift($path, $current->name);
        }

        return implode(' > ', $path);
    }
}
