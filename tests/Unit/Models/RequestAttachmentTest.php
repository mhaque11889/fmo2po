<?php

namespace Tests\Unit\Models;

use App\Models\RequestAttachment;
use App\Models\RequirementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesTestData;

class RequestAttachmentTest extends TestCase
{
    use RefreshDatabase, CreatesTestData;

    public function test_attachment_has_fillable_attributes(): void
    {
        $request = $this->createPendingRequest();
        $attachment = RequestAttachment::factory()
            ->forRequest($request)
            ->create([
                'original_filename' => 'test.pdf',
                'file_type' => 'pdf',
                'mime_type' => 'application/pdf',
                'file_size' => 1024,
            ]);

        $this->assertEquals('test.pdf', $attachment->original_filename);
        $this->assertEquals('pdf', $attachment->file_type);
        $this->assertEquals('application/pdf', $attachment->mime_type);
        $this->assertEquals(1024, $attachment->file_size);
    }

    public function test_attachment_belongs_to_requirement_request(): void
    {
        $request = $this->createPendingRequest();
        $attachment = $this->createAttachment($request);

        $this->assertInstanceOf(RequirementRequest::class, $attachment->requirementRequest);
        $this->assertEquals($request->id, $attachment->requirementRequest->id);
    }

    public function test_attachment_belongs_to_uploader(): void
    {
        $user = $this->createFmoUser();
        $request = $this->createPendingRequest($user);
        $attachment = $this->createAttachment($request, $user);

        $this->assertInstanceOf(User::class, $attachment->uploader);
        $this->assertEquals($user->id, $attachment->uploader->id);
    }

    public function test_is_image_returns_true_for_image_type(): void
    {
        $request = $this->createPendingRequest();
        $attachment = RequestAttachment::factory()
            ->forRequest($request)
            ->image()
            ->create();

        $this->assertTrue($attachment->isImage());
        $this->assertFalse($attachment->isPdf());
    }

    public function test_is_pdf_returns_true_for_pdf_type(): void
    {
        $request = $this->createPendingRequest();
        $attachment = RequestAttachment::factory()
            ->forRequest($request)
            ->pdf()
            ->create();

        $this->assertTrue($attachment->isPdf());
        $this->assertFalse($attachment->isImage());
    }

    public function test_extension_accessor_returns_lowercase_extension(): void
    {
        $request = $this->createPendingRequest();

        $jpgAttachment = RequestAttachment::factory()
            ->forRequest($request)
            ->create(['original_filename' => 'Photo.JPG']);

        $pdfAttachment = RequestAttachment::factory()
            ->forRequest($request)
            ->create(['original_filename' => 'Document.PDF']);

        $this->assertEquals('jpg', $jpgAttachment->extension);
        $this->assertEquals('pdf', $pdfAttachment->extension);
    }

    public function test_human_file_size_accessor_formats_bytes(): void
    {
        $request = $this->createPendingRequest();

        $smallAttachment = RequestAttachment::factory()
            ->forRequest($request)
            ->withSize(500)
            ->create();

        $this->assertEquals('500 B', $smallAttachment->human_file_size);
    }

    public function test_human_file_size_accessor_formats_kilobytes(): void
    {
        $request = $this->createPendingRequest();

        $kbAttachment = RequestAttachment::factory()
            ->forRequest($request)
            ->withSize(2048)
            ->create();

        $this->assertEquals('2 KB', $kbAttachment->human_file_size);
    }

    public function test_human_file_size_accessor_formats_megabytes(): void
    {
        $request = $this->createPendingRequest();

        $mbAttachment = RequestAttachment::factory()
            ->forRequest($request)
            ->withSize(2 * 1024 * 1024)
            ->create();

        $this->assertEquals('2 MB', $mbAttachment->human_file_size);
    }

    public function test_full_path_accessor_returns_storage_path(): void
    {
        Storage::fake('local');

        $request = $this->createPendingRequest();
        $attachment = RequestAttachment::factory()
            ->forRequest($request)
            ->create(['file_path' => 'attachments/1/test.pdf']);

        $fullPath = $attachment->full_path;

        $this->assertStringContainsString('attachments/1/test.pdf', $fullPath);
    }

    public function test_secure_url_accessor_returns_route(): void
    {
        $request = $this->createPendingRequest();
        $attachment = RequestAttachment::factory()
            ->forRequest($request)
            ->create();

        $url = $attachment->secure_url;

        $this->assertStringContainsString('/attachments/', $url);
        $this->assertStringContainsString((string) $attachment->id, $url);
    }

    public function test_factory_image_state_creates_jpeg_attachment(): void
    {
        $request = $this->createPendingRequest();
        $attachment = RequestAttachment::factory()
            ->forRequest($request)
            ->image()
            ->create();

        $this->assertEquals('image', $attachment->file_type);
        $this->assertEquals('image/jpeg', $attachment->mime_type);
        $this->assertStringEndsWith('.jpg', $attachment->original_filename);
    }

    public function test_factory_png_state_creates_png_attachment(): void
    {
        $request = $this->createPendingRequest();
        $attachment = RequestAttachment::factory()
            ->forRequest($request)
            ->png()
            ->create();

        $this->assertEquals('image', $attachment->file_type);
        $this->assertEquals('image/png', $attachment->mime_type);
        $this->assertStringEndsWith('.png', $attachment->original_filename);
    }

    public function test_factory_gif_state_creates_gif_attachment(): void
    {
        $request = $this->createPendingRequest();
        $attachment = RequestAttachment::factory()
            ->forRequest($request)
            ->gif()
            ->create();

        $this->assertEquals('image', $attachment->file_type);
        $this->assertEquals('image/gif', $attachment->mime_type);
        $this->assertStringEndsWith('.gif', $attachment->original_filename);
    }
}
