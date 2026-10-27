<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Момент последней успешной отправки периодического отчёта (см. миграцию
 * create_report_send_log_table). Один ряд на report_key.
 */
class ReportSendLog extends Model
{
    protected $table = 'report_send_log';
    protected $primaryKey = 'report_key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['report_key', 'last_sent_at'];

    protected $casts = [
        'last_sent_at' => 'datetime',
    ];

    public static function lastSentAt(string $reportKey): ?\Illuminate\Support\Carbon
    {
        return static::find($reportKey)?->last_sent_at;
    }

    public static function markSent(string $reportKey, \Illuminate\Support\Carbon $when): void
    {
        static::updateOrCreate(['report_key' => $reportKey], ['last_sent_at' => $when]);
    }
}
