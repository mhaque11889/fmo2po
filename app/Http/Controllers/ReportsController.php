<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // --- Base filtered query (for the All Requests tab) ---
        $query = RequirementRequest::with(['category', 'creator', 'approver', 'assignee', 'assigner', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query
            ->orderByRaw("FIELD(priority, 'urgent', 'normal')")
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // --- Status counts (global, ignores filters for overview cards) ---
        $counts = RequirementRequest::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = [
            'all'                  => $counts->sum(),
            'group_pending'        => $counts['group_pending'] ?? 0,
            'pending'              => $counts['pending'] ?? 0,
            'approved'             => $counts['approved'] ?? 0,
            'rejected'             => $counts['rejected'] ?? 0,
            'assigned'             => $counts['assigned'] ?? 0,
            'in_progress'          => $counts['in_progress'] ?? 0,
            'completed'            => $counts['completed'] ?? 0,
            'cancelled'            => $counts['cancelled'] ?? 0,
            'clarification_needed' => $counts['clarification_needed'] ?? 0,
        ];

        // --- Priority counts ---
        $priorityCounts = RequirementRequest::selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority');

        $priorityData = [
            'urgent' => $priorityCounts['urgent'] ?? 0,
            'normal' => $priorityCounts['normal'] ?? 0,
        ];

        // --- Category breakdown (date-filtered, no status filter) ---
        $catQuery = RequirementRequest::query();
        if ($request->filled('date_from')) {
            $catQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $catQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $catStatusCounts = $catQuery
            ->select('category_id', 'status', DB::raw('COUNT(*) as total'))
            ->groupBy('category_id', 'status')
            ->get()
            ->groupBy('category_id');

        $categories = Category::orderBy('sort_order')->orderBy('name')->get();

        $categoryStats = $categories->map(function ($cat) use ($catStatusCounts) {
            $rows = $catStatusCounts->get($cat->id, collect());
            $byStatus = $rows->pluck('total', 'status');
            $total = $rows->sum('total');
            $completed = $byStatus['completed'] ?? 0;
            return [
                'id'          => $cat->id,
                'name'        => $cat->name,
                'total'       => $total,
                'pending'     => ($byStatus['pending'] ?? 0) + ($byStatus['group_pending'] ?? 0),
                'approved'    => $byStatus['approved'] ?? 0,
                'assigned'    => ($byStatus['assigned'] ?? 0) + ($byStatus['in_progress'] ?? 0),
                'completed'   => $completed,
                'rejected'    => ($byStatus['rejected'] ?? 0) + ($byStatus['cancelled'] ?? 0),
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        })->filter(fn($row) => $row['total'] > 0)->values();

        // --- Trend: requests per month for last 12 months ---
        $trendBase = RequirementRequest::query();
        if ($request->filled('date_from')) {
            $trendBase->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $trendBase->whereDate('created_at', '<=', $request->date_to);
        }

        $trendRaw = $trendBase
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->pluck('total', 'period');

        // Build 12-month labels regardless of data gaps
        $trendLabels = [];
        $trendValues = [];
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $trendLabels[] = now()->subMonths($i)->format('M Y');
            $trendValues[] = $trendRaw[$key] ?? 0;
        }

        // --- User activity ---
        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;

        $fmoUsers = User::whereIn('role', ['fmo_user', 'fmo_admin'])
            ->where('is_active', true)
            ->withCount([
                'createdRequests as submitted' => function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo)   $q->whereDate('created_at', '<=', $dateTo);
                },
                'createdRequests as completed_count' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('status', 'completed');
                    if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo)   $q->whereDate('created_at', '<=', $dateTo);
                },
                'createdRequests as pending_count' => function ($q) use ($dateFrom, $dateTo) {
                    $q->whereIn('status', ['pending', 'group_pending']);
                    if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo)   $q->whereDate('created_at', '<=', $dateTo);
                },
                'createdRequests as rejected_count' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('status', 'rejected');
                    if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo)   $q->whereDate('created_at', '<=', $dateTo);
                },
            ])
            ->orderByDesc('submitted')
            ->get();

        $poUsers = User::whereIn('role', ['po_user', 'po_admin'])
            ->where('is_active', true)
            ->withCount([
                'assignedRequests as assigned_count' => function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo)   $q->whereDate('created_at', '<=', $dateTo);
                },
                'assignedRequests as in_progress_count' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('status', 'in_progress');
                    if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo)   $q->whereDate('created_at', '<=', $dateTo);
                },
                'assignedRequests as completed_count' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('status', 'completed');
                    if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo)   $q->whereDate('created_at', '<=', $dateTo);
                },
            ])
            ->orderByDesc('assigned_count')
            ->get();

        return view('reports.index', compact(
            'requests',
            'statusCounts',
            'priorityData',
            'categories',
            'categoryStats',
            'trendLabels',
            'trendValues',
            'fmoUsers',
            'poUsers'
        ));
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');

        $query = RequirementRequest::with(['category', 'creator', 'approver', 'assignee', 'assigner', 'items']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($format === 'excel') {
            return $this->exportExcel($query);
        }

        return $this->exportCsv($query);
    }

    private function exportCsv($query)
    {
        $filename = 'fmo2po_reports_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID', 'Priority', 'Category', 'Items', 'Total Qty', 'Location', 'Status',
                'Created By', 'Created At', 'Approved By', 'Approved At', 'Approval Remarks',
                'Assigned To', 'Assigned By', 'Assigned At', 'Progress Remarks', 'Progress At',
                'Completion Remarks', 'Completed At',
            ]);

            $query->with(['category', 'creator', 'approver', 'assignee', 'assigner', 'items'])
                ->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('created_at', 'desc')
                ->chunkById(1000, function ($rows) use ($file) {
                    foreach ($rows as $req) {
                        fputcsv($file, [
                            $req->id,
                            ucfirst($req->priority),
                            $req->category->name ?? '',
                            $req->display_item,
                            $req->total_qty,
                            $req->location,
                            ucfirst(str_replace('_', ' ', $req->status)),
                            $req->creator->name ?? '',
                            $req->created_at?->format('Y-m-d H:i:s'),
                            $req->approver->name ?? '',
                            $req->approved_at?->format('Y-m-d H:i:s'),
                            $req->rejection_remarks,
                            $req->assignee->name ?? '',
                            $req->assigner->name ?? '',
                            $req->assigned_at?->format('Y-m-d H:i:s'),
                            $req->progress_remarks,
                            $req->progress_at?->format('Y-m-d H:i:s'),
                            $req->completion_remarks,
                            $req->completed_at?->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportExcel($query)
    {
        $filename = 'fmo2po_reports_' . now()->format('Y-m-d_His') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($query) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"></head>';
            echo '<body><table border="1">';
            echo '<tr style="background-color: #4F46E5; color: white; font-weight: bold;">';
            foreach (['ID','Priority','Category','Items','Total Qty','Location','Status','Created By','Created At','Approved By','Approved At','Approval Remarks','Assigned To','Assigned By','Assigned At','Progress Remarks','Progress At','Completion Remarks','Completed At'] as $col) {
                echo '<th>' . $col . '</th>';
            }
            echo '</tr>';

            $query->with(['category', 'creator', 'approver', 'assignee', 'assigner', 'items'])
                ->orderByRaw("FIELD(priority, 'urgent', 'normal')")->orderBy('created_at', 'desc')
                ->chunkById(1000, function ($rows) {
                    foreach ($rows as $req) {
                        echo '<tr>';
                        echo '<td>' . $req->id . '</td>';
                        echo '<td style="' . ($req->priority === 'urgent' ? 'color:#dc2626;font-weight:bold;' : '') . '">' . ucfirst($req->priority) . '</td>';
                        echo '<td>' . htmlspecialchars($req->category->name ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($req->display_item) . '</td>';
                        echo '<td>' . $req->total_qty . '</td>';
                        echo '<td>' . htmlspecialchars($req->location) . '</td>';
                        echo '<td>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $req->status))) . '</td>';
                        echo '<td>' . htmlspecialchars($req->creator->name ?? '') . '</td>';
                        echo '<td>' . ($req->created_at?->format('Y-m-d H:i:s') ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($req->approver->name ?? '') . '</td>';
                        echo '<td>' . ($req->approved_at?->format('Y-m-d H:i:s') ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($req->rejection_remarks ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($req->assignee->name ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($req->assigner->name ?? '') . '</td>';
                        echo '<td>' . ($req->assigned_at?->format('Y-m-d H:i:s') ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($req->progress_remarks ?? '') . '</td>';
                        echo '<td>' . ($req->progress_at?->format('Y-m-d H:i:s') ?? '') . '</td>';
                        echo '<td>' . htmlspecialchars($req->completion_remarks ?? '') . '</td>';
                        echo '<td>' . ($req->completed_at?->format('Y-m-d H:i:s') ?? '') . '</td>';
                        echo '</tr>';
                    }
                });

            echo '</table></body></html>';
        };

        return Response::stream($callback, 200, $headers);
    }
}
