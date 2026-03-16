<?php

namespace Tests\Feature\Controllers;

use App\Models\RequirementRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class ReportsControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_fmo_admin_can_access_reports(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        $response = $this->actingAs($fmoAdmin)->get('/reports');

        $response->assertStatus(200);
        $response->assertViewIs('reports.index');
    }

    public function test_po_admin_can_access_reports(): void
    {
        $poAdmin = $this->createPoAdmin();

        $response = $this->actingAs($poAdmin)->get('/reports');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_reports(): void
    {
        $superAdmin = $this->createSuperAdmin();

        $response = $this->actingAs($superAdmin)->get('/reports');

        $response->assertStatus(200);
    }

    public function test_fmo_user_cannot_access_reports(): void
    {
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->get('/reports');

        $response->assertStatus(403);
    }

    public function test_po_user_cannot_access_reports(): void
    {
        $poUser = $this->createPoUser();

        $response = $this->actingAs($poUser)->get('/reports');

        $response->assertStatus(403);
    }

    public function test_reports_shows_all_requests(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $this->createPendingRequest();
        $this->createApprovedRequest();
        $this->createCompletedRequest();

        $response = $this->actingAs($fmoAdmin)->get('/reports');

        $response->assertStatus(200);
        $requests = $response->viewData('requests');
        $this->assertEquals(3, $requests->total());
    }

    public function test_reports_can_filter_by_status(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $this->createPendingRequest();
        $this->createPendingRequest();
        $this->createApprovedRequest();

        $response = $this->actingAs($fmoAdmin)->get('/reports?status=pending');

        $response->assertStatus(200);
        $requests = $response->viewData('requests');
        $this->assertEquals(2, $requests->total());
    }

    public function test_reports_can_filter_by_date_range(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        // Create a request and manually set old created_at
        $oldRequest = RequirementRequest::create([
            'created_by' => $fmoAdmin->id,
            'item' => 'Old Item',
            'qty' => 1,
            'location' => 'Building A',
            'status' => 'pending',
        ]);
        // Use query builder to update timestamp (bypasses fillable)
        RequirementRequest::where('id', $oldRequest->id)
            ->update(['created_at' => now()->subDays(5)]);

        // Create a request for today
        $newRequest = RequirementRequest::create([
            'created_by' => $fmoAdmin->id,
            'item' => 'New Item',
            'qty' => 1,
            'location' => 'Building B',
            'status' => 'pending',
        ]);

        // Filter by today's date - should only show the new request
        $response = $this->actingAs($fmoAdmin)->get('/reports?date_from=' . now()->format('Y-m-d'));

        $response->assertStatus(200);
        $requests = $response->viewData('requests');
        // The filter should exclude the old request
        $this->assertFalse($requests->pluck('id')->contains($oldRequest->id));
        $this->assertTrue($requests->pluck('id')->contains($newRequest->id));
    }

    public function test_reports_shows_status_counts(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $this->createPendingRequest();
        $this->createPendingRequest();
        $this->createApprovedRequest();
        $this->createCompletedRequest();

        $response = $this->actingAs($fmoAdmin)->get('/reports');

        $response->assertStatus(200);
        $statusCounts = $response->viewData('statusCounts');

        $this->assertEquals(4, $statusCounts['all']);
        $this->assertEquals(2, $statusCounts['pending']);
        $this->assertEquals(1, $statusCounts['approved']);
        $this->assertEquals(1, $statusCounts['completed']);
    }

    // ==================== EXPORT TESTS ====================

    public function test_fmo_admin_can_export_csv(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $this->createPendingRequest();

        $response = $this->actingAs($fmoAdmin)->get('/reports/export?format=csv');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition'));
    }

    public function test_fmo_admin_can_export_excel(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $this->createPendingRequest();

        $response = $this->actingAs($fmoAdmin)->get('/reports/export?format=excel');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/vnd.ms-excel', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('.xls', $response->headers->get('Content-Disposition'));
    }

    public function test_export_respects_status_filter(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $pending = $this->createPendingRequest();
        $this->createApprovedRequest();

        $response = $this->actingAs($fmoAdmin)->get('/reports/export?format=csv&status=pending');

        $response->assertStatus(200);
        // The response should only contain the pending request data
        $content = $response->streamedContent();
        $this->assertStringContainsString($pending->item, $content);
    }

    public function test_export_respects_date_filter(): void
    {
        $fmoAdmin = $this->createFmoAdmin();

        $oldRequest = $this->createPendingRequest();
        $oldRequest->update(['created_at' => now()->subDays(10)]);

        $newRequest = $this->createPendingRequest();

        $response = $this->actingAs($fmoAdmin)->get('/reports/export?format=csv&date_from=' . now()->format('Y-m-d'));

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString($newRequest->item, $content);
    }

    public function test_fmo_user_cannot_export(): void
    {
        $fmoUser = $this->createFmoUser();

        $response = $this->actingAs($fmoUser)->get('/reports/export');

        $response->assertStatus(403);
    }

    public function test_export_includes_request_details(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $fmoUser = $this->createFmoUser(['name' => 'John Creator']);
        $request = $this->createPendingRequest($fmoUser, [
            'item' => 'Unique Test Item',
            'qty' => 42,
            'location' => 'Special Building',
        ]);

        $response = $this->actingAs($fmoAdmin)->get('/reports/export?format=csv');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Unique Test Item', $content);
        $this->assertStringContainsString('42', $content);
        $this->assertStringContainsString('Special Building', $content);
        $this->assertStringContainsString('John Creator', $content);
    }
}
