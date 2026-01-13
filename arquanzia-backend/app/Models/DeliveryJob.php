<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryJob extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'delivery_email_id',
        'book_id',
        'chapter_id',
        'format_sent',
        'status',
        'error_message',
        'attempts',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryEmail(): BelongsTo
    {
        return $this->belongsTo(DeliveryEmail::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public static function getRecentForUser(string $userId, int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('user_id', $userId)
            ->with(['book', 'chapter', 'deliveryEmail'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
