// Ticket QR scanner and check-in workflow.
document.addEventListener('DOMContentLoaded', () => {
    const page = document.querySelector('[data-ticket-scanner]');
    // The scan view is identified by its controls, so no extra wrapper is required.
    const video = document.getElementById('qr-video');
    const startButton = document.getElementById('start-camera');
    const stopButton = document.getElementById('stop-camera');
    const cameraStatus = document.getElementById('camera-status');
    const cameraPlaceholder = document.getElementById('camera-placeholder');
    const scanFrame = document.getElementById('scan-frame');
    const cameraHelp = document.getElementById('camera-help');
    const form = document.getElementById('manual-scan-form');
    const input = document.getElementById('qr-value');
    const submitButton = document.getElementById('manual-submit');
    const resultBox = document.getElementById('scan-result');
    const resultHeading = document.getElementById('result-heading');
    const resultMessage = document.getElementById('result-message');
    const resultDetails = document.getElementById('result-details');

    if (!video || !startButton || !form) return;

    let stream = null;
    let animationFrame = null;
    let detector = null;
    let processing = false;

    function stopCamera() {
        if (animationFrame !== null) cancelAnimationFrame(animationFrame);
        animationFrame = null;
        if (stream) stream.getTracks().forEach((track) => track.stop());
        stream = null;
        video.srcObject = null;
        cameraPlaceholder.classList.remove('hidden');
        scanFrame.classList.add('hidden');
        cameraStatus.textContent = 'Camera stopped';
        startButton.disabled = false;
        stopButton.disabled = true;
    }

    function showResult(data, isSuccess) {
        resultBox.classList.remove('hidden', 'border-emerald-400/40', 'bg-emerald-400/10', 'text-emerald-100', 'border-amber-400/40', 'bg-amber-400/10', 'text-amber-100', 'border-rose-400/40', 'bg-rose-400/10', 'text-rose-100');
        const palette = data.result === 'checked_in'
            ? ['border-emerald-400/40', 'bg-emerald-400/10', 'text-emerald-100']
            : data.result === 'already_checked_in'
                ? ['border-amber-400/40', 'bg-amber-400/10', 'text-amber-100']
                : ['border-rose-400/40', 'bg-rose-400/10', 'text-rose-100'];
        resultBox.classList.add(...palette);
        resultHeading.textContent = data.result === 'checked_in' ? '✓ Valid ticket — checked in'
            : data.result === 'already_checked_in' ? 'Already checked in' : 'Invalid ticket';
        resultMessage.textContent = data.message || 'Unable to verify this ticket.';
        resultDetails.replaceChildren();

        if (data.ticket) {
            const fields = [
                ['Attendee', data.ticket.attendee],
                ['Event', data.ticket.event],
                ['Ticket category', data.ticket.category],
                ['Ticket code', data.ticket.ticket_code],
                ['Checked in at', data.ticket.checked_in_at],
            ];
            fields.forEach(([label, value]) => {
                if (!value) return;
                const wrapper = document.createElement('div');
                const term = document.createElement('dt');
                const detail = document.createElement('dd');
                term.className = 'text-xs opacity-60';
                term.textContent = label;
                detail.className = 'mt-1 break-all font-medium';
                detail.textContent = value;
                wrapper.append(term, detail);
                resultDetails.append(wrapper);
            });
            resultDetails.classList.remove('hidden');
        } else {
            resultDetails.classList.add('hidden');
        }
    }

    async function verifyAndCheckIn(value) {
        if (processing) return;
        const qrValue = String(value || '').trim();
        if (!qrValue) return;
        processing = true;
        submitButton.disabled = true;
        submitButton.textContent = 'Checking…';

        try {
            const response = await fetch(form.action || window.location.pathname.replace(/\/$/, '') + '/check-in', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ qr_value: qrValue }),
            });
            const data = await response.json();
            showResult(data, response.ok);
            if (data.result === 'checked_in') input.value = '';
        } catch (error) {
            showResult({ result: 'invalid', message: 'Network error. Please try again.' }, false);
        } finally {
            processing = false;
            submitButton.disabled = false;
            submitButton.textContent = 'Verify & check in';
        }
    }

    async function scanFrameLoop() {
        if (!stream || !detector || processing) return;
        if (video.readyState >= HTMLMediaElement.HAVE_CURRENT_DATA) {
            try {
                const codes = await detector.detect(video);
                if (codes.length && codes[0].rawValue) {
                    const value = codes[0].rawValue;
                    stopCamera();
                    input.value = value;
                    await verifyAndCheckIn(value);
                    return;
                }
            } catch (error) {
                cameraHelp.textContent = 'Unable to read QR from camera. Try adjusting the distance or use manual entry.';
            }
        }
        animationFrame = requestAnimationFrame(scanFrameLoop);
    }

    startButton.addEventListener('click', async () => {
        if (!('mediaDevices' in navigator) || !navigator.mediaDevices.getUserMedia) {
            cameraHelp.textContent = 'Camera access is unavailable in this browser or connection. Use manual QR entry instead.';
            return;
        }
        if (!('BarcodeDetector' in window)) {
            cameraHelp.textContent = 'This browser does not support built-in QR detection. Use Chrome on HTTPS or enter the QR content manually.';
            return;
        }
        try {
            detector = new BarcodeDetector({ formats: ['qr_code'] });
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
            video.srcObject = stream;
            await video.play();
            cameraPlaceholder.classList.add('hidden');
            scanFrame.classList.remove('hidden');
            cameraStatus.textContent = 'Camera active — point at QR';
            startButton.disabled = true;
            stopButton.disabled = false;
            cameraHelp.textContent = 'Hold the QR code steady inside the frame.';
            scanFrameLoop();
        } catch (error) {
            stopCamera();
            cameraHelp.textContent = error.name === 'NotAllowedError'
                ? 'Camera permission was denied. Allow camera access in browser settings or use manual entry.'
                : 'Could not start the camera. Check that no other app is using it, or use manual entry.';
        }
    });

    stopButton.addEventListener('click', stopCamera);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        verifyAndCheckIn(input.value);
    });
    window.addEventListener('pagehide', stopCamera);
});
