<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_date',
        'event_time',
        'event_name',
        'event_description'
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime:H:i'
    ];

    /**
     * Get events for a specific date
     */
    public static function getEventsForDate($date)
    {
        return self::where('event_date', $date)
            ->orderBy('event_time')
            ->get();
    }

    /**
     * Get all dates that have events
     */
    public static function getDatesWithEvents()
    {
        return self::select('event_date')
            ->distinct()
            ->pluck('event_date')
            ->toArray();
    }

    /**
     * Format time for display
     */
    public function getFormattedTimeAttribute()
    {
        return Carbon::parse($this->event_time)->format('H:i');
    }
}