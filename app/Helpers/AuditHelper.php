<?php

namespace App\Helpers;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditHelper
{
    public static function log($action, $table, $recordId, $old = null, $new = null)
    {
        AuditLog::create([
            'user_id'   => Auth::id(),
            'user_name' => Auth::user()->fullname ?? Auth::user()->name ?? null,
            'action'    => $action,
            'table_name'=> $table,
            'record_id' => $recordId,
            'old_data'  => $old,
            'new_data'  => $new,
        ]);
    }
}
