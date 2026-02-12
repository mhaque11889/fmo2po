<?php

namespace App\Http\Controllers;

use App\Models\RequestAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Display/download the attachment securely.
     * Only authenticated users who can view the parent request can access attachments.
     */
    public function show(RequestAttachment $attachment): StreamedResponse
    {
        // Check authorization - user must be able to view the parent request
        $user = auth()->user();
        $request = $attachment->requirementRequest;

        $canView = false;

        if ($user->isSuperAdmin() || $user->isFmoAdmin()) {
            $canView = true;
        } elseif ($user->isPoAdmin()) {
            // PO Admin can only view attachments for approved, assigned, in_progress, or completed requests
            $canView = in_array($request->status, ['approved', 'assigned', 'in_progress', 'completed']);
        } elseif ($user->isFmoUser()) {
            $canView = $request->created_by === $user->id;
        } elseif ($user->isPoUser()) {
            $canView = $request->assigned_to === $user->id;
        }

        if (!$canView) {
            abort(403, 'You are not authorized to view this attachment.');
        }

        // Check if file exists
        if (!Storage::disk('local')->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }

        // Stream the file to the browser
        return Storage::disk('local')->response(
            $attachment->file_path,
            $attachment->original_filename,
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => $this->getContentDisposition($attachment),
            ]
        );
    }

    /**
     * Get the content disposition header based on file type.
     * Images and PDFs open in browser, others download.
     */
    private function getContentDisposition(RequestAttachment $attachment): string
    {
        // For images and PDFs, display inline (opens in new tab)
        if ($attachment->isImage() || $attachment->isPdf()) {
            return 'inline; filename="' . $attachment->original_filename . '"';
        }

        // For other files, force download
        return 'attachment; filename="' . $attachment->original_filename . '"';
    }
}
