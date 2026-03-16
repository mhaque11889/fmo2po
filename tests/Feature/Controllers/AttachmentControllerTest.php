<?php

namespace Tests\Feature\Controllers;

use App\Models\RequestAttachment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class AttachmentControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function createAttachmentWithFile($request, $uploader, string $content = 'test content'): RequestAttachment
    {
        $attachment = $this->createAttachment($request, $uploader, 'image');
        Storage::disk('local')->put($attachment->file_path, $content);

        return $attachment;
    }

    public function test_fmo_user_can_download_own_request_attachment(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);
        $attachment = $this->createAttachmentWithFile($request, $fmoUser);

        $response = $this->actingAs($fmoUser)->get("/attachments/{$attachment->id}");

        $response->assertStatus(200);
    }

    public function test_fmo_user_cannot_download_others_request_attachment(): void
    {
        $fmoUser = $this->createFmoUser();
        $otherUser = $this->createFmoUser();
        $request = $this->createPendingRequest($otherUser);
        $attachment = $this->createAttachmentWithFile($request, $otherUser);

        $response = $this->actingAs($fmoUser)->get("/attachments/{$attachment->id}");

        $response->assertStatus(403);
    }

    public function test_fmo_admin_can_download_any_attachment(): void
    {
        $fmoAdmin = $this->createFmoAdmin();
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);
        $attachment = $this->createAttachmentWithFile($request, $fmoUser);

        $response = $this->actingAs($fmoAdmin)->get("/attachments/{$attachment->id}");

        $response->assertStatus(200);
    }

    public function test_super_admin_can_download_any_attachment(): void
    {
        $superAdmin = $this->createSuperAdmin();
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);
        $attachment = $this->createAttachmentWithFile($request, $fmoUser);

        $response = $this->actingAs($superAdmin)->get("/attachments/{$attachment->id}");

        $response->assertStatus(200);
    }

    public function test_po_admin_can_download_approved_request_attachment(): void
    {
        $poAdmin = $this->createPoAdmin();
        $fmoUser = $this->createFmoUser();
        $request = $this->createApprovedRequest($fmoUser);
        $attachment = $this->createAttachmentWithFile($request, $fmoUser);

        $response = $this->actingAs($poAdmin)->get("/attachments/{$attachment->id}");

        $response->assertStatus(200);
    }

    public function test_po_admin_cannot_download_pending_request_attachment(): void
    {
        $poAdmin = $this->createPoAdmin();
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);
        $attachment = $this->createAttachmentWithFile($request, $fmoUser);

        $response = $this->actingAs($poAdmin)->get("/attachments/{$attachment->id}");

        $response->assertStatus(403);
    }

    public function test_po_user_can_download_assigned_request_attachment(): void
    {
        $poUser = $this->createPoUser();
        $fmoUser = $this->createFmoUser();
        $request = $this->createAssignedRequest($fmoUser, null, $poUser);
        $attachment = $this->createAttachmentWithFile($request, $fmoUser);

        $response = $this->actingAs($poUser)->get("/attachments/{$attachment->id}");

        $response->assertStatus(200);
    }

    public function test_po_user_cannot_download_unassigned_request_attachment(): void
    {
        $poUser = $this->createPoUser();
        $otherPoUser = $this->createPoUser();
        $fmoUser = $this->createFmoUser();
        $request = $this->createAssignedRequest($fmoUser, null, $otherPoUser);
        $attachment = $this->createAttachmentWithFile($request, $fmoUser);

        $response = $this->actingAs($poUser)->get("/attachments/{$attachment->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);
        $attachment = $this->createAttachmentWithFile($request, $fmoUser);

        $response = $this->get("/attachments/{$attachment->id}");

        $response->assertRedirect('/login');
    }

    public function test_returns_404_when_file_does_not_exist(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);
        $attachment = $this->createAttachment($request, $fmoUser);
        // Don't create the actual file

        $response = $this->actingAs($fmoUser)->get("/attachments/{$attachment->id}");

        $response->assertStatus(404);
    }

    public function test_image_attachment_is_displayed_inline(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);
        $attachment = RequestAttachment::factory()
            ->forRequest($request)
            ->uploadedBy($fmoUser)
            ->image()
            ->create();
        Storage::disk('local')->put($attachment->file_path, 'image content');

        $response = $this->actingAs($fmoUser)->get("/attachments/{$attachment->id}");

        $response->assertStatus(200);
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }

    public function test_pdf_attachment_is_displayed_inline(): void
    {
        $fmoUser = $this->createFmoUser();
        $request = $this->createPendingRequest($fmoUser);
        $attachment = RequestAttachment::factory()
            ->forRequest($request)
            ->uploadedBy($fmoUser)
            ->pdf()
            ->create();
        Storage::disk('local')->put($attachment->file_path, 'pdf content');

        $response = $this->actingAs($fmoUser)->get("/attachments/{$attachment->id}");

        $response->assertStatus(200);
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
    }
}
