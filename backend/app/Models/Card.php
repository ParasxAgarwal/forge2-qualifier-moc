<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Card extends Model
{
    protected $fillable = ['title', 'description', 'list_id', 'position', 'due_date'];

    protected $casts = ['due_date' => 'date'];

    public function list(): BelongsTo
    {
        return $this->belongsTo(BoardList::class, 'list_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'card_tag');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'card_member');
    }
}