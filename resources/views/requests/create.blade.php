@extends('layouts.app')

@section('title', 'Create Request')

@section('content')
<!-- Animation Overlay -->
<div id="submit-animation-overlay" class="fixed inset-0 hidden" style="z-index: 9999;">
    <div class="absolute inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
    <div class="relative h-full flex flex-col items-center justify-center">
        <!-- Animation Container -->
        <div class="relative w-80 h-48">
            <!-- Document/Envelope Icon -->
            <div id="envelope-icon" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                <!-- Document (transforms to envelope) -->
                <svg id="doc-svg" class="w-20 h-20 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2l5 5h-5V4zM6 20V4h5v7h7v9H6z"/>
                    <path d="M8 12h8v2H8zm0 4h8v2H8z"/>
                </svg>
                <!-- Envelope (initially hidden) -->
                <svg id="envelope-svg" class="w-20 h-20 text-white hidden" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                </svg>
            </div>

            <!-- Trail particles -->
            <div id="trail-container" class="absolute inset-0 pointer-events-none"></div>

            <!-- Destination Icon (FMO Admin) -->
            <div id="destination-icon" class="absolute right-0 top-1/2 -translate-y-1/2 opacity-0">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 bg-indigo-600 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="text-white text-sm mt-2 font-medium">FMO Admin</span>
                </div>
            </div>

            <!-- Success Checkmark -->
            <div id="success-checkmark" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 opacity-0 scale-0">
                <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center">
                    <svg class="w-14 h-14 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Status Text -->
        <div id="status-text" class="mt-8 text-center">
            @if(auth()->user()->isFmoAdmin() || auth()->user()->isSuperAdmin())
                <p id="sending-text" class="text-xl text-white font-medium">Submitting Request...</p>
                <p id="success-text" class="text-xl text-white font-medium hidden">Request Submitted!</p>
            @else
                <p id="sending-text" class="text-xl text-white font-medium">Sending to FMO Admin...</p>
                <p id="success-text" class="text-xl text-white font-medium hidden">Request Sent!</p>
            @endif
        </div>
    </div>
</div>

<style>
    /* Animation Keyframes */
    @keyframes pulse-scale {
        0%, 100% { transform: translate(-50%, -50%) scale(1); }
        50% { transform: translate(-50%, -50%) scale(1.1); }
    }

    @keyframes doc-to-envelope {
        0% { transform: translate(-50%, -50%) scale(1) rotateY(0deg); }
        50% { transform: translate(-50%, -50%) scale(0.8) rotateY(90deg); }
        100% { transform: translate(-50%, -50%) scale(1) rotateY(0deg); }
    }

    @keyframes fly-to-destination {
        0% {
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%) scale(1);
        }
        30% {
            left: 55%;
            top: 35%;
            transform: translate(-50%, -50%) scale(1.1) rotate(-10deg);
        }
        70% {
            left: 75%;
            top: 40%;
            transform: translate(-50%, -50%) scale(0.9) rotate(5deg);
        }
        100% {
            left: 85%;
            top: 50%;
            transform: translate(-50%, -50%) scale(0.6);
            opacity: 0;
        }
    }

    @keyframes fade-in-up {
        0% { opacity: 0; transform: translate(-50%, -50%) scale(0); }
        50% { transform: translate(-50%, -50%) scale(1.2); }
        100% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    }

    @keyframes particle-trail {
        0% { opacity: 1; transform: scale(1); }
        100% { opacity: 0; transform: scale(0); }
    }

    @keyframes destination-appear {
        0% { opacity: 0; transform: translateY(-50%) scale(0.5); }
        100% { opacity: 1; transform: translateY(-50%) scale(1); }
    }

    .animate-pulse-doc {
        animation: pulse-scale 0.8s ease-in-out infinite;
    }

    .animate-transform {
        animation: doc-to-envelope 0.5s ease-in-out forwards;
    }

    .animate-fly {
        animation: fly-to-destination 1s ease-in-out forwards;
    }

    .animate-success {
        animation: fade-in-up 0.5s ease-out forwards;
    }

    .animate-destination {
        animation: destination-appear 0.3s ease-out forwards;
    }

    .particle {
        position: absolute;
        width: 8px;
        height: 8px;
        background: linear-gradient(135deg, #818cf8, #6366f1);
        border-radius: 50%;
        animation: particle-trail 0.5s ease-out forwards;
    }
</style>

<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Create New Requirement Request</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form id="create-request-form" action="{{ route('requests.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label for="item" class="block text-sm font-medium text-gray-700 mb-1">
                    Item <span class="text-red-500">*</span>
                </label>
                <input type="text" name="item" id="item" value="{{ old('item') }}"
                    class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 @error('item') border-red-500 @enderror"
                    required>
                @error('item')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4 grid grid-cols-2 gap-4">
                <div>
                    <label for="qty" class="block text-sm font-medium text-gray-700 mb-1">
                        Quantity <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="qty" id="qty" value="{{ old('qty', 1) }}" min="1"
                        class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 @error('qty') border-red-500 @enderror"
                        required>
                    @error('qty')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="dimensions" class="block text-sm font-medium text-gray-700 mb-1">
                        Dimensions
                    </label>
                    <input type="text" name="dimensions" id="dimensions" value="{{ old('dimensions') }}"
                        placeholder="e.g., 10x20x30 cm"
                        class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 @error('dimensions') border-red-500 @enderror">
                    @error('dimensions')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                    Location <span class="text-red-500">*</span>
                </label>
                <input type="text" name="location" id="location" value="{{ old('location') }}"
                    placeholder="e.g., Building A, Room 101"
                    class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 @error('location') border-red-500 @enderror"
                    required>
                @error('location')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1">
                    Remarks
                </label>
                <textarea name="remarks" id="remarks" rows="3"
                    placeholder="Any additional information..."
                    class="w-full border border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 px-3 py-2 @error('remarks') border-red-500 @enderror">{{ old('remarks') }}</textarea>
                @error('remarks')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- File Attachments -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Attachments
                    <span class="text-gray-500 font-normal">(Optional - Max 2 files, 5MB each)</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition"
                     id="dropzone">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="attachments" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                <span>Upload files</span>
                                <input id="attachments" name="attachments[]" type="file" class="sr-only" multiple accept=".pdf,.jpg,.jpeg,.png,.gif">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PDF, PNG, JPG, GIF up to 5MB each</p>
                    </div>
                </div>
                <!-- Selected files preview -->
                <div id="file-preview" class="mt-3 space-y-2 hidden">
                    <p class="text-sm font-medium text-gray-700">Selected files:</p>
                    <ul id="file-list" class="text-sm text-gray-600 space-y-1"></ul>
                </div>
                @error('attachments')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                @error('attachments.*')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('dashboard') }}"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('attachments');
    const filePreview = document.getElementById('file-preview');
    const fileList = document.getElementById('file-list');
    const dropzone = document.getElementById('dropzone');
    const form = document.getElementById('create-request-form');
    const isFmoAdmin = {{ (auth()->user()->isFmoAdmin() || auth()->user()->isSuperAdmin()) ? 'true' : 'false' }};

    // Animation elements
    const overlay = document.getElementById('submit-animation-overlay');
    const envelopeIcon = document.getElementById('envelope-icon');
    const docSvg = document.getElementById('doc-svg');
    const envelopeSvg = document.getElementById('envelope-svg');
    const destinationIcon = document.getElementById('destination-icon');
    const successCheckmark = document.getElementById('success-checkmark');
    const trailContainer = document.getElementById('trail-container');
    const sendingText = document.getElementById('sending-text');
    const successText = document.getElementById('success-text');

    // Form submission handler
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();

            // Disable submit button to prevent double submission
            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            try {
                const formData = new FormData(form);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    // Server returned non-JSON - fall back to regular form submission
                    const clone = form.cloneNode(true);
                    form.parentNode.replaceChild(clone, form);
                    clone.submit();
                    return;
                }

                const data = await response.json();

                if (response.ok && data.success) {
                    // Show animation
                    playSubmitAnimation(data.redirect);
                } else {
                    // Handle validation errors
                    if (data.errors) {
                        displayValidationErrors(data.errors);
                    } else {
                        alert(data.message || 'An error occurred. Please try again.');
                    }
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Request';
                }
            } catch (error) {
                alert('An error occurred while submitting. Please try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Request';
            }
        });
    }

    function displayValidationErrors(errors) {
        // Clear existing errors
        document.querySelectorAll('.validation-error').forEach(el => el.remove());

        // Display new errors
        for (const [field, messages] of Object.entries(errors)) {
            const input = document.querySelector(`[name="${field}"]`) ||
                          document.querySelector(`[name="${field}[]"]`);
            if (input) {
                const errorDiv = document.createElement('p');
                errorDiv.className = 'mt-1 text-sm text-red-500 validation-error';
                errorDiv.textContent = messages[0];
                input.closest('.mb-4, .mb-6')?.appendChild(errorDiv);
            }
        }
    }

    function playSubmitAnimation(redirectUrl) {
        if (!overlay) {
            // Fallback: just redirect
            sessionStorage.setItem('flash_success', 'Request submitted successfully.');
            window.location.href = redirectUrl;
            return;
        }

        // Show overlay
        overlay.classList.remove('hidden');

        // Reset states
        if (docSvg) docSvg.classList.remove('hidden');
        if (envelopeSvg) envelopeSvg.classList.add('hidden');
        if (successCheckmark) {
            successCheckmark.style.opacity = '0';
            successCheckmark.style.transform = 'translate(-50%, -50%) scale(0)';
            successCheckmark.classList.remove('animate-success');
        }
        if (destinationIcon) {
            destinationIcon.style.opacity = '0';
            destinationIcon.classList.remove('animate-destination');
        }
        if (sendingText) sendingText.classList.remove('hidden');
        if (successText) successText.classList.add('hidden');
        if (envelopeIcon) {
            envelopeIcon.style.cssText = '';
            envelopeIcon.style.display = '';
            envelopeIcon.classList.remove('animate-pulse-doc', 'animate-transform', 'animate-fly');
        }
        if (trailContainer) trailContainer.innerHTML = '';

        if (isFmoAdmin) {
            // Simplified animation for FMO Admin / Super Admin
            // Step 1: Document pulses (0.6s)
            if (envelopeIcon) envelopeIcon.classList.add('animate-pulse-doc');

            setTimeout(() => {
                // Step 2: Transform to envelope (0.5s)
                if (envelopeIcon) {
                    envelopeIcon.classList.remove('animate-pulse-doc');
                    envelopeIcon.classList.add('animate-transform');
                }

                setTimeout(() => {
                    // Swap icons at midpoint of transform
                    if (docSvg) docSvg.classList.add('hidden');
                    if (envelopeSvg) envelopeSvg.classList.remove('hidden');
                }, 250);
            }, 600);

            setTimeout(() => {
                // Step 3: Show success checkmark
                if (envelopeIcon) envelopeIcon.style.display = 'none';
                if (successCheckmark) successCheckmark.classList.add('animate-success');
                if (sendingText) sendingText.classList.add('hidden');
                if (successText) successText.classList.remove('hidden');
            }, 1400);

            setTimeout(() => {
                // Step 4: Redirect
                sessionStorage.setItem('flash_success', 'Request submitted successfully.');
                window.location.href = redirectUrl;
            }, 2200);

        } else {
            // Full animation for FMO User - envelope flies to FMO Admin
            // Step 1: Document pulses (0.8s)
            if (envelopeIcon) envelopeIcon.classList.add('animate-pulse-doc');

            setTimeout(() => {
                // Step 2: Transform to envelope (0.5s)
                if (envelopeIcon) {
                    envelopeIcon.classList.remove('animate-pulse-doc');
                    envelopeIcon.classList.add('animate-transform');
                }

                setTimeout(() => {
                    // Swap icons at midpoint of transform
                    if (docSvg) docSvg.classList.add('hidden');
                    if (envelopeSvg) envelopeSvg.classList.remove('hidden');
                }, 250);
            }, 800);

            setTimeout(() => {
                // Step 3: Show destination
                if (destinationIcon) destinationIcon.classList.add('animate-destination');
            }, 1100);

            setTimeout(() => {
                // Step 4: Fly envelope to destination (1s)
                if (envelopeIcon) {
                    envelopeIcon.classList.remove('animate-transform');
                    envelopeIcon.classList.add('animate-fly');
                }

                // Create trail particles
                createTrailParticles();
            }, 1300);

            setTimeout(() => {
                // Step 5: Show success checkmark
                if (envelopeIcon) envelopeIcon.style.display = 'none';
                if (destinationIcon) destinationIcon.style.opacity = '0';
                if (successCheckmark) successCheckmark.classList.add('animate-success');
                if (sendingText) sendingText.classList.add('hidden');
                if (successText) successText.classList.remove('hidden');
            }, 2300);

            setTimeout(() => {
                // Step 6: Redirect
                sessionStorage.setItem('flash_success', 'Request submitted successfully.');
                window.location.href = redirectUrl;
            }, 3000);
        }
    }

    function createTrailParticles() {
        const positions = [
            { left: '50%', top: '50%', delay: 0 },
            { left: '55%', top: '40%', delay: 100 },
            { left: '60%', top: '35%', delay: 200 },
            { left: '65%', top: '38%', delay: 300 },
            { left: '70%', top: '42%', delay: 400 },
            { left: '75%', top: '45%', delay: 500 },
        ];

        positions.forEach(pos => {
            setTimeout(() => {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = pos.left;
                particle.style.top = pos.top;
                trailContainer.appendChild(particle);

                setTimeout(() => particle.remove(), 500);
            }, pos.delay);
        });
    }

    // File input change handler
    fileInput.addEventListener('change', function() {
        updateFileList(this.files);
    });

    // Drag and drop handlers
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('border-indigo-500', 'bg-indigo-50');
    });

    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('border-indigo-500', 'bg-indigo-50');
    });

    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('border-indigo-500', 'bg-indigo-50');

        const dt = new DataTransfer();
        const files = e.dataTransfer.files;

        // Limit to 2 files
        for (let i = 0; i < Math.min(files.length, 2); i++) {
            dt.items.add(files[i]);
        }

        fileInput.files = dt.files;
        updateFileList(dt.files);
    });

    function updateFileList(files) {
        fileList.innerHTML = '';

        if (files.length > 0) {
            filePreview.classList.remove('hidden');

            if (files.length > 2) {
                alert('Maximum 2 files allowed. Only the first 2 files will be uploaded.');
            }

            for (let i = 0; i < Math.min(files.length, 2); i++) {
                const file = files[i];
                const li = document.createElement('li');
                li.className = 'flex items-center justify-between bg-gray-50 px-3 py-2 rounded';

                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const icon = file.type.includes('pdf') ?
                    '<svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M4 18h12a2 2 0 002-2V6l-4-4H6a2 2 0 00-2 2v12a2 2 0 002 2zm8-14v4h4l-4-4z"/></svg>' :
                    '<svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"/></svg>';

                li.innerHTML = `
                    <span class="flex items-center">
                        ${icon}
                        <span class="truncate max-w-xs">${file.name}</span>
                    </span>
                    <span class="text-gray-500 text-xs ml-2">${fileSize} MB</span>
                `;
                fileList.appendChild(li);
            }
        } else {
            filePreview.classList.add('hidden');
        }
    }
});
</script>
@endsection
