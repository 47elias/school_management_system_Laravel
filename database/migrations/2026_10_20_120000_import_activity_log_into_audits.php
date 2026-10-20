<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyConnection = config('activitylog.database_connection');
        $legacyTable = config('activitylog.table_name', 'activity_log');
        $auditConnection = config('audit.drivers.database.connection');
        $auditTable = config('audit.drivers.database.table', 'audits');

        if (!Schema::connection($legacyConnection)->hasTable($legacyTable)
            || !Schema::connection($auditConnection)->hasTable($auditTable)) {
            return;
        }

        $morphPrefix = config('audit.user.morph_prefix', 'user');
        $legacyDb = DB::connection($legacyConnection);
        $auditDb = DB::connection($auditConnection);

        $legacyDb->table($legacyTable)
            ->whereNotNull('subject_type')
            ->whereNotNull('subject_id')
            ->orderBy('id')
            ->chunkById(500, function ($activities) use ($auditDb, $auditTable, $morphPrefix) {
                foreach ($activities as $activity) {
                    $properties = json_decode((string) ($activity->properties ?? ''), true);
                    $properties = is_array($properties) ? $properties : [];

                    $oldValues = $properties['old'] ?? $properties['old_values'] ?? [];
                    $newValues = $properties['attributes'] ?? $properties['new'] ?? $properties['new_values'] ?? [];

                    if (!is_array($oldValues)) {
                        $oldValues = ['value' => $oldValues];
                    }
                    if (!is_array($newValues)) {
                        $newValues = ['value' => $newValues];
                    }
                    $sensitiveFields = ['password', 'remember_token', 'face_descriptor'];
                    $oldValues = array_diff_key($oldValues, array_flip($sensitiveFields));
                    $newValues = array_diff_key($newValues, array_flip($sensitiveFields));
                    if ($oldValues === [] && $newValues === [] && $properties !== []) {
                        $newValues = array_diff_key($properties, array_flip($sensitiveFields));
                    }

                    $description = trim((string) ($activity->description ?? ''));
                    $logName = trim((string) ($activity->log_name ?? ''));
                    $legacyTag = 'Imported from Spatie activity log';
                    if ($logName !== '') {
                        $legacyTag .= ' · ' . $logName;
                    }
                    if ($description !== '') {
                        $legacyTag .= ' · ' . $description;
                    }

                    $auditDb->table($auditTable)->insert([
                        $morphPrefix . '_type' => $activity->causer_type ?? null,
                        $morphPrefix . '_id' => $activity->causer_id ?? null,
                        'event' => $activity->event ?: 'updated',
                        'auditable_type' => $activity->subject_type,
                        'auditable_id' => $activity->subject_id,
                        'old_values' => json_encode($oldValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'new_values' => json_encode($newValues, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'url' => null,
                        'ip_address' => null,
                        'user_agent' => null,
                        'tags' => mb_substr($legacyTag, 0, 255),
                        'created_at' => $activity->created_at ?? now(),
                        'updated_at' => $activity->updated_at ?? $activity->created_at ?? now(),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Imported audit history is retained if this migration is rolled back.
    }
};
