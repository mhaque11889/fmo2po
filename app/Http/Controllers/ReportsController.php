<?php

namespace App\Http\Controllers;

use App\Models\RequirementRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $query = RequirementRequest::with(['creator', 'approver', 'assignee', 'assigner']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->latest()->paginate(20)->withQueryString();

        // Get counts for each status
        $statusCounts = [
            'all' => RequirementRequest::count(),
            'pending' => RequirementRequest::where('status', 'pending')->count(),
            'approved' => RequirementRequest::where('status', 'approved')->count(),
            'rejected' => RequirementRequest::where('status', 'rejected')->count(),
            'assigned' => RequirementRequest::where('status', 'assigned')->count(),
            'in_progress' => RequirementRequest::where('status', 'in_progress')->count(),
            'completed' => RequirementRequest::where('status', 'completed')->count(),
        ];

        return view('reports.index', compact('requests', 'statusCounts'));
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');

        $query = RequirementRequest::with(['creator', 'approver', 'assignee', 'assigner']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->latest()->get();

        if ($format === 'excel') {
            return $this->exportExcel($requests);
        }

        return $this->exportCsv($requests);
    }

    private function exportCsv($requests)
    {
        $filename = 'fmo2po_reports_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($requests) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID',
                'Item',
                'Description',
                'Dimensions',
                'Quantity',
                'Location',
                'Status',
                'Created By',
                'Created At',
                'Approved By',
                'Approved At',
                'Approval Remarks',
                'Assigned To',
                'Assigned By',
                'Assigned At',
                'Progress Remarks',
                'Progress At',
                'Completion Remarks',
                'Completed At',
            ]);

            foreach ($requests as $request) {
                fputcsv($file, [
                    $request->id,
                    $request->item,
                    $request->description,
                    $request->dimensions,
                    $request->qty,
                    $request->location,
                    ucfirst(str_replace('_', ' ', $request->status)),
                    $request->creator->name ?? '',
                    $request->created_at?->format('Y-m-d H:i:s'),
                    $request->approver->name ?? '',
                    $request->approved_at?->format('Y-m-d H:i:s'),
                    $request->approval_remarks,
                    $request->assignee->name ?? '',
                    $request->assigner->name ?? '',
                    $request->assigned_at?->format('Y-m-d H:i:s'),
                    $request->progress_remarks,
                    $request->progress_at?->format('Y-m-d H:i:s'),
                    $request->completion_remarks,
                    $request->completed_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function exportExcel($requests)
    {
        // For Excel, we'll create a simple XML spreadsheet format
        // This avoids requiring additional packages like PHPSpreadsheet
        $filename = 'fmo2po_reports_' . now()->format('Y-m-d_His') . '.xls';

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($requests) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"></head>';
            echo '<body><table border="1">';

            // Header row
            echo '<tr style="background-color: #4F46E5; color: white; font-weight: bold;">';
            echo '<th>ID</th>';
            echo '<th>Item</th>';
            echo '<th>Description</th>';
            echo '<th>Dimensions</th>';
            echo '<th>Quantity</th>';
            echo '<th>Location</th>';
            echo '<th>Status</th>';
            echo '<th>Created By</th>';
            echo '<th>Created At</th>';
            echo '<th>Approved By</th>';
            echo '<th>Approved At</th>';
            echo '<th>Approval Remarks</th>';
            echo '<th>Assigned To</th>';
            echo '<th>Assigned By</th>';
            echo '<th>Assigned At</th>';
            echo '<th>Progress Remarks</th>';
            echo '<th>Progress At</th>';
            echo '<th>Completion Remarks</th>';
            echo '<th>Completed At</th>';
            echo '</tr>';

            foreach ($requests as $request) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($request->id) . '</td>';
                echo '<td>' . htmlspecialchars($request->item) . '</td>';
                echo '<td>' . htmlspecialchars($request->description ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($request->dimensions ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($request->qty) . '</td>';
                echo '<td>' . htmlspecialchars($request->location) . '</td>';
                echo '<td>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $request->status))) . '</td>';
                echo '<td>' . htmlspecialchars($request->creator->name ?? '') . '</td>';
                echo '<td>' . ($request->created_at?->format('Y-m-d H:i:s') ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($request->approver->name ?? '') . '</td>';
                echo '<td>' . ($request->approved_at?->format('Y-m-d H:i:s') ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($request->approval_remarks ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($request->assignee->name ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($request->assigner->name ?? '') . '</td>';
                echo '<td>' . ($request->assigned_at?->format('Y-m-d H:i:s') ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($request->progress_remarks ?? '') . '</td>';
                echo '<td>' . ($request->progress_at?->format('Y-m-d H:i:s') ?? '') . '</td>';
                echo '<td>' . htmlspecialchars($request->completion_remarks ?? '') . '</td>';
                echo '<td>' . ($request->completed_at?->format('Y-m-d H:i:s') ?? '') . '</td>';
                echo '</tr>';
            }

            echo '</table></body></html>';
        };

        return Response::stream($callback, 200, $headers);
    }
}
