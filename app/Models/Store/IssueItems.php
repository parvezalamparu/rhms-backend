<?php

namespace App\Models\Store;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IssueItems extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'issue_no',
        'requisition_no',
        'issue_to',
        'generated_by',
        'issue_date',
    ];

    protected static function boot()
    {
        parent::boot();

        // uuid generate
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }

            // issue no generate
            if (empty($model->issue_no)) {

                $today = now()->setTimezone('Asia/Kolkata')->format('ymd'); // yymmdd format
                $prefix = "ISN-$today-";
    
                // Fetch last issue number for today (lock prevents duplicates)
                $last = IssueItems::where('issue_no', 'like', "$prefix%")
                    ->orderBy('issue_no', 'desc')
                    ->lockForUpdate()
                    ->first();
    
                if ($last) {
                    // Extract last 3 digits and increment
                    $lastNumber = intval(substr($last->issue_no, -3));
                    $next = $lastNumber + 1;
                } else {
                    $next = 1;
                }
    
                // Format number to 3 digits
                $model->issue_no = $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
            }
        });
    }


    public function details()
    {
        return $this->hasMany(IssueItemDetails::class, 'issue_no', 'issue_no');
    }
}
