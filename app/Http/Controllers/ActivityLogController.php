<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Menampilkan daftar log aktivitas
     * Hanya dapat diakses oleh Admin
     */
    public function index(Request $request)
    {
        // Query builder dengan eager loading user
        $query = ActivityLog::with('user');

        // Fitur Filter berdasarkan Action (opsional)
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Fitur Filter berdasarkan User (opsional)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Fitur Pencarian berdasarkan Description
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        // Urutkan dari yang terbaru, paginate 15 per halaman
        $logs = $query->latest()->paginate(15)->withQueryString();

        // Ambil daftar action unik untuk dropdown filter
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('activity_logs.index', compact('logs', 'actions'));
    }
}
