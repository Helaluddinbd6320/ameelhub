<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisputeEvidence extends Model
{
    /**
     * FIX: Eloquent convention অনুযায়ী DisputeEvidence মডেলের জন্য
     * ডিফল্ট টেবিল নাম হয় 'dispute_evidence' (singular) — কারণ
     * "evidence" শব্দটি Laravel এর inflector uncountable noun হিসেবে
     * treat করে (plural করলেও 'evidence' ই থাকে)।
     *
     * কিন্তু migration এ টেবিল বানানো হয়েছে 'dispute_evidences' (plural)
     * নামে। তাই এই mismatch এর কারণে
     * "Table 'dispute_evidence' doesn't exist" error আসছিল।
     *
     * নিচের লাইনটি explicit ভাবে সঠিক টেবিল নাম বলে দিচ্ছে।
     */
    protected $table = 'dispute_evidences';

    public $timestamps = false; // শুধু created_at আছে (migration onCreate default)

    protected $fillable = [
        'milestone_id',
        'uploaded_by_id',
        'uploaded_by_role',
        'file_path',
        'file_type',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(JobDealMilestone::class, 'milestone_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}