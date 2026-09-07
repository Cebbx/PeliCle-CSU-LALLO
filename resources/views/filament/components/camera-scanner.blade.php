<div
    x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        isStreaming: false,
        isLoading: false,
        devices: [],
        selectedDeviceId: '',
        errorMessage: '',
        facingMode: 'environment',
        flashActive: false,
        sharpnessScore: 0,
        isBlurry: false,
        showZoomModal: false,

        async init() {
            if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
                try {
                    const allDevices = await navigator.mediaDevices.enumerateDevices();
                    this.devices = allDevices.filter(d => d.kind === 'videoinput');
                } catch (e) {
                    console.warn('Device enumeration error:', e);
                }
            }
        },

        async startCamera() {
            this.errorMessage = '';
            this.isLoading = true;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.errorMessage = 'Hindi suportado ng browser ang live camera access. Maaari mong gamitin ang Upload File tab o ang file button sa ibaba.';
                this.isLoading = false;
                return;
            }

            try {
                this.stopCamera();

                const constraints = {
                    video: this.selectedDeviceId 
                        ? { deviceId: { exact: this.selectedDeviceId }, width: { ideal: 1920 }, height: { ideal: 1080 } }
                        : { facingMode: { ideal: this.facingMode }, width: { ideal: 1920 }, height: { ideal: 1080 } },
                    audio: false
                };

                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                this.$refs.video.srcObject = stream;
                await this.$refs.video.play();
                this.isStreaming = true;

                try {
                    const allDevices = await navigator.mediaDevices.enumerateDevices();
                    this.devices = allDevices.filter(d => d.kind === 'videoinput');
                } catch (e) {}
            } catch (err) {
                console.error('Camera access error:', err);
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    this.errorMessage = 'Kailangan ng Camera Permission: Paki-click ang camera icon sa browser address bar para payagan ang camera.';
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    this.errorMessage = 'Walang nakitang camera sa device na ito. Pakisaksak ang webcam o mag-upload ng file.';
                } else {
                    this.errorMessage = 'Hindi mabuksan ang camera (' + (err.message || 'Error') + '). Pakisubukan ulit.';
                }
            } finally {
                this.isLoading = false;
            }
        },

        capture() {
            if (!this.isStreaming) return;

            const video = this.$refs.video;
            const canvas = this.$refs.canvas;

            const width = video.videoWidth || 1280;
            const height = video.videoHeight || 720;

            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, width, height);

            this.flashActive = true;
            setTimeout(() => { this.flashActive = false; }, 200);

            this.sharpnessScore = this.calculateSharpness(ctx, width, height);
            this.isBlurry = this.sharpnessScore < 60;

            const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
            this.state = dataUrl;

            this.stopCamera();
        },

        calculateSharpness(ctx, width, height) {
            try {
                const sampleCanvas = document.createElement('canvas');
                sampleCanvas.width = 160;
                sampleCanvas.height = 120;
                const sampleCtx = sampleCanvas.getContext('2d');
                sampleCtx.drawImage(ctx.canvas, 0, 0, 160, 120);
                const imgData = sampleCtx.getImageData(0, 0, 160, 120);
                const d = imgData.data;

                const gray = new Float32Array(160 * 120);
                for (let i = 0, j = 0; i < d.length; i += 4, j++) {
                    gray[j] = d[i] * 0.299 + d[i+1] * 0.587 + d[i+2] * 0.114;
                }

                let mean = 0;
                let count = 0;
                const laplacian = [];
                for (let y = 1; y < 119; y++) {
                    for (let x = 1; x < 159; x++) {
                        const idx = y * 160 + x;
                        const lap = Math.abs(
                            4 * gray[idx] - gray[idx - 1] - gray[idx + 1] - gray[idx - 160] - gray[idx + 160]
                        );
                        laplacian.push(lap);
                        mean += lap;
                        count++;
                    }
                }
                mean /= count;

                let variance = 0;
                for (let i = 0; i < count; i++) {
                    variance += (laplacian[i] - mean) * (laplacian[i] - mean);
                }
                variance /= count;

                return Math.round(variance);
            } catch (e) {
                return 100;
            }
        },

        retake() {
            this.state = null;
            this.sharpnessScore = 0;
            this.isBlurry = false;
            this.$nextTick(() => {
                this.startCamera();
            });
        },

        clear() {
            this.state = null;
            this.sharpnessScore = 0;
            this.isBlurry = false;
            this.stopCamera();
        },

        async switchCamera() {
            this.stopCamera();
            await this.startCamera();
        },

        async toggleFacingMode() {
            this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
            this.selectedDeviceId = '';
            this.stopCamera();
            await this.startCamera();
        },

        stopCamera() {
            if (this.$refs.video && this.$refs.video.srcObject) {
                const stream = this.$refs.video.srcObject;
                const tracks = stream.getTracks();
                tracks.forEach(track => track.stop());
                this.$refs.video.srcObject = null;
            }
            this.isStreaming = false;
        },

        handleNativeFileInput(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                this.state = event.target.result;
                this.stopCamera();

                const img = new Image();
                img.onload = () => {
                    const tempCanvas = document.createElement('canvas');
                    tempCanvas.width = img.width;
                    tempCanvas.height = img.height;
                    const tempCtx = tempCanvas.getContext('2d');
                    tempCtx.drawImage(img, 0, 0);
                    this.sharpnessScore = this.calculateSharpness(tempCtx, img.width, img.height);
                    this.isBlurry = this.sharpnessScore < 60;
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        },

        destroy() {
            this.stopCamera();
        }
    }"
    x-init="init()"
    x-on:unmount.window="destroy()"
    class="doc-scanner-wrapper"
>
    <!-- Scoped CSS for bulletproof styling -->
    <style>
        .doc-scanner-wrapper {
            width: 100%;
            font-family: inherit;
        }
        .doc-scanner-card {
            background: #0b1120;
            border: 1px solid #1e293b;
            border-radius: 16px;
            padding: 24px 20px;
            text-align: center;
            color: #f8fafc;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            margin: 0 auto;
        }
        .doc-scanner-icon-badge {
            width: 48px;
            height: 48px;
            min-width: 48px;
            max-width: 48px;
            min-height: 48px;
            max-height: 48px;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.28);
            color: #10b981;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px auto;
        }
        .doc-scanner-icon-badge svg {
            width: 24px !important;
            height: 24px !important;
            min-width: 24px !important;
            max-width: 24px !important;
            min-height: 24px !important;
            max-height: 24px !important;
            display: block;
        }
        .doc-scanner-title {
            font-size: 15px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 6px 0;
            letter-spacing: -0.01em;
        }
        .doc-scanner-subtitle {
            font-size: 12.5px;
            color: #94a3b8;
            margin: 0 auto 18px auto;
            line-height: 1.5;
            max-width: 380px;
        }
        .doc-scanner-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff !important;
            font-size: 13px;
            font-weight: 600;
            padding: 9px 20px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
            text-decoration: none;
        }
        .doc-scanner-btn-primary:hover {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
        }
        .doc-scanner-btn-primary svg {
            width: 17px !important;
            height: 17px !important;
            min-width: 17px !important;
            max-width: 17px !important;
        }
        .doc-scanner-divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #64748b;
            font-size: 11px;
            margin: 16px 0 12px 0;
        }
        .doc-scanner-divider::before, .doc-scanner-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #1e293b;
        }
        .doc-scanner-divider:not(:empty)::before {
            margin-right: 12px;
        }
        .doc-scanner-divider:not(:empty)::after {
            margin-left: 12px;
        }
        .doc-scanner-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            background: #1e293b;
            color: #cbd5e1;
            font-size: 11.5px;
            font-weight: 500;
            padding: 6px 13px;
            border-radius: 8px;
            border: 1px solid #334155;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .doc-scanner-btn-secondary:hover {
            background: #334155;
            color: #ffffff;
        }
        .doc-scanner-btn-secondary svg {
            width: 15px !important;
            height: 15px !important;
        }
        /* Viewfinder styles */
        .doc-viewfinder-box {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            border-radius: 14px;
            overflow: hidden;
            background: #020617;
            border: 1px solid #334155;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4);
        }
        .doc-viewfinder-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background: #0f172a;
            border-bottom: 1px solid #1e293b;
        }
        .doc-viewfinder-video {
            width: 100%;
            height: 300px;
            object-fit: cover;
            display: block;
            background: #000000;
        }
        .doc-viewfinder-bottombar {
            padding: 12px;
            background: #0f172a;
            border-top: 1px solid #1e293b;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .doc-shutter-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white !important;
            font-weight: 700;
            font-size: 13px;
            padding: 9px 22px;
            border-radius: 9999px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
            transition: all 0.15s ease;
        }
        .doc-shutter-btn:active {
            transform: scale(0.96);
        }
        .doc-shutter-btn svg {
            width: 18px !important;
            height: 18px !important;
        }
        /* Framing Brackets */
        .corner-bracket {
            position: absolute;
            width: 22px;
            height: 22px;
            border-color: #10b981;
            border-style: solid;
        }
        .corner-tl { top: 14px; left: 14px; border-width: 3px 0 0 3px; border-top-left-radius: 6px; }
        .corner-tr { top: 14px; right: 14px; border-width: 3px 3px 0 0; border-top-right-radius: 6px; }
        .corner-bl { bottom: 14px; left: 14px; border-width: 0 0 3px 3px; border-bottom-left-radius: 6px; }
        .corner-br { bottom: 14px; right: 14px; border-width: 0 3px 3px 0; border-bottom-right-radius: 6px; }
        /* Preview Card */
        .doc-preview-box {
            background: #0b1120;
            border: 1px solid #1e293b;
            border-radius: 14px;
            padding: 16px;
            max-width: 500px;
            margin: 0 auto;
            color: #f8fafc;
        }
        .doc-preview-img-wrap {
            position: relative;
            max-height: 280px;
            overflow: hidden;
            border-radius: 8px;
            background: #020617;
            border: 1px solid #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .doc-preview-img {
            max-height: 280px;
            width: 100%;
            object-fit: contain;
            display: block;
        }
    </style>

    <!-- Hidden Canvas for frame capture -->
    <canvas x-ref="canvas" style="display: none;"></canvas>

    <!-- 1. CAPTURED PREVIEW STATE -->
    <template x-if="state">
        <div class="doc-preview-box">
            
            <!-- Status Header -->
            <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #1e293b; padding-bottom: 10px; margin-bottom: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></span>
                    <span style="font-size: 13px; font-weight: 700; color: #10b981; text-transform: uppercase; letter-spacing: 0.04em;">✓ Dokumento Na-Scan</span>
                </div>
                <span style="font-size: 11px; color: #64748b; font-family: monospace;">Handa nang i-save</span>
            </div>

            <!-- Smart Sharpness / Blur Detection Alert -->
            <template x-if="isBlurry">
                <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); border-radius: 10px; padding: 10px 12px; margin-bottom: 12px; display: flex; align-items: flex-start; gap: 8px; text-align: left;">
                    <span style="font-size: 16px; line-height: 1;">⚠️</span>
                    <div style="font-size: 11.5px; color: #fde68a; line-height: 1.4;">
                        <strong style="color: #fbbf24; display: block; margin-bottom: 2px;">Medyo Malabo ang Pagkaka-scan</strong>
                        Pakitingnan kung malinaw at nababasa ang pirma ni CEO. Kung malabo, pindutin ang <b>"Kuhanan Ulit (Retake)"</b> sa ibaba.
                    </div>
                </div>
            </template>

            <template x-if="!isBlurry && sharpnessScore > 0">
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px; padding: 6px 12px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; font-size: 11.5px; color: #34d399;">
                    <span>✓ Malinaw at maayos ang kuha</span>
                    <span style="font-family: monospace; font-size: 10px; color: #059669;">Quality: Good</span>
                </div>
            </template>

            <!-- Image Snapshot Frame with Click to Zoom -->
            <div
                @click="showZoomModal = true"
                class="doc-preview-img-wrap"
                title="Pindutin para i-zoom"
            >
                <img :src="state" alt="CEO Signed Document Scan" class="doc-preview-img" />
                <div style="position: absolute; bottom: 6px; right: 6px; background: rgba(0,0,0,0.75); color: #34d399; font-size: 10px; padding: 2px 8px; border-radius: 4px; font-family: monospace;">
                    🔍 Pindutin para i-zoom
                </div>
            </div>

            <!-- Action Controls for Captured State -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-top: 12px; padding-top: 10px; border-top: 1px solid #1e293b;">
                <span style="font-size: 11px; color: #64748b;">
                    Siguraduhing kita ang lagda ni CEO.
                </span>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <button
                        type="button"
                        @click="showZoomModal = true"
                        style="padding: 5px 10px; font-size: 11.5px; font-weight: 600; color: #38bdf8; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.3); border-radius: 6px; cursor: pointer;"
                    >
                        🔍 Zoom
                    </button>
                    <button
                        type="button"
                        @click="retake()"
                        style="padding: 5px 10px; font-size: 11.5px; font-weight: 600; color: #f59e0b; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 6px; cursor: pointer;"
                    >
                        🔄 Kuhanan Ulit
                    </button>
                    <button
                        type="button"
                        @click="clear()"
                        style="padding: 5px 10px; font-size: 11.5px; font-weight: 600; color: #ef4444; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; cursor: pointer;"
                    >
                        ✕ Alisin
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- FULLSCREEN / ZOOM PREVIEW MODAL -->
    <template x-if="showZoomModal && state">
        <div
            style="position: fixed; inset: 0; z-index: 999999; background: rgba(0, 0, 0, 0.88); backdrop-filter: blur(4px); display: flex; flex-direction: column; align-items: center; justify-content: space-between; padding: 16px;"
            @keydown.escape.window="showZoomModal = false"
        >
            <div style="width: 100%; max-width: 720px; display: flex; align-items: center; justify-content: space-between; color: white; border-bottom: 1px solid #334155; padding-bottom: 10px;">
                <span style="font-size: 14px; font-weight: 700; color: #34d399;">📄 Preview ng Dokumento</span>
                <button
                    type="button"
                    @click="showZoomModal = false"
                    style="background: #1e293b; color: white; border: 1px solid #475569; padding: 4px 10px; border-radius: 6px; font-size: 12px; cursor: pointer;"
                >
                    ✕ Isara
                </button>
            </div>

            <div style="flex: 1; display: flex; align-items: center; justify-content: center; overflow: auto; padding: 12px 0;">
                <img :src="state" alt="Full Preview" style="max-height: 72vh; max-width: 90vw; object-fit: contain; border-radius: 8px; border: 1px solid #475569; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);" />
            </div>

            <div style="width: 100%; max-width: 720px; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #334155; padding-top: 10px;">
                <span style="font-size: 12px; color: #94a3b8;">Nababasa ba ang pirma ng CEO?</span>
                <button
                    type="button"
                    @click="showZoomModal = false"
                    style="background: #059669; color: white; border: none; padding: 6px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer;"
                >
                    ✓ Ayos Na, Gamitin Ito
                </button>
            </div>
        </div>
    </template>

    <!-- 2. LIVE CAMERA STREAMING STATE -->
    <template x-if="!state && isStreaming">
        <div class="doc-viewfinder-box">
            
            <!-- Shutter Flash Overlay -->
            <div
                x-show="flashActive"
                x-transition:leave="transition ease-out duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                style="position: absolute; inset: 0; background: white; z-index: 50; pointer-events: none;"
            ></div>

            <!-- Top bar -->
            <div class="doc-viewfinder-topbar">
                <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: #f87171; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); padding: 2px 8px; border-radius: 9999px;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #ef4444;"></span>
                    LIVE CAMERA
                </span>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <!-- Device Selector (if multiple cameras available) -->
                    <template x-if="devices.length > 1">
                        <select
                            x-model="selectedDeviceId"
                            @change="switchCamera()"
                            style="font-size: 11px; background: #1e293b; color: #cbd5e1; border: 1px solid #334155; border-radius: 6px; padding: 3px 6px; max-width: 140px;"
                        >
                            <template x-for="(d, idx) in devices" :key="d.deviceId">
                                <option :value="d.deviceId" x-text="d.label || ('Camera ' + (idx + 1))"></option>
                            </template>
                        </select>
                    </template>

                    <!-- Flip Camera Button -->
                    <button
                        type="button"
                        @click="toggleFacingMode()"
                        title="Palitan ang camera (harap / likod)"
                        style="background: #1e293b; color: #cbd5e1; border: 1px solid #334155; border-radius: 6px; padding: 4px 7px; cursor: pointer; display: flex; align-items: center;"
                    >
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>

                    <!-- Close Camera Button -->
                    <button
                        type="button"
                        @click="stopCamera()"
                        style="font-size: 11px; font-weight: 600; background: #1e293b; color: #94a3b8; border: 1px solid #334155; border-radius: 6px; padding: 4px 8px; cursor: pointer;"
                    >
                        ✕ Isara
                    </button>
                </div>
            </div>

            <!-- Video Viewfinder Area with Document Guidelines -->
            <div style="position: relative; width: 100%; overflow: hidden; background: #000;">
                <video
                    x-ref="video"
                    autoplay
                    playsinline
                    muted
                    class="doc-viewfinder-video"
                ></video>

                <!-- Corner Brackets Overlay -->
                <div class="corner-bracket corner-tl"></div>
                <div class="corner-bracket corner-tr"></div>
                <div class="corner-bracket corner-bl"></div>
                <div class="corner-bracket corner-br"></div>

                <!-- Center Guide label -->
                <div style="position: absolute; top: 12px; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.65); border: 1px solid rgba(16,185,129,0.3); color: #34d399; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 9999px; pointer-events: none; white-space: nowrap;">
                    📄 I-sentro ang papel sa loob ng border
                </div>
            </div>

            <!-- Bottom Shutter Action Bar -->
            <div class="doc-viewfinder-bottombar">
                <button
                    type="button"
                    @click="capture()"
                    class="doc-shutter-btn"
                >
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>KUHANAN NG LITRATO (CAPTURE)</span>
                </button>
            </div>
        </div>
    </template>

    <!-- 3. INACTIVE CAMERA / LAUNCH SCREEN STATE -->
    <template x-if="!state && !isStreaming">
        <div class="doc-scanner-card">
            
            <!-- Compact Icon Badge (Explicit 48px) -->
            <div class="doc-scanner-icon-badge">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>

            <!-- Title & Subtitle -->
            <h3 class="doc-scanner-title">
                Live Camera Scanner
            </h3>
            <p class="doc-scanner-subtitle">
                Direktang picturan ang pirmadong Trip Ticket o dokumento gamit ang iyong webcam o cellphone camera.
            </p>

            <!-- Error alert if camera permission failed -->
            <template x-if="errorMessage">
                <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.4); border-radius: 8px; padding: 8px 12px; margin-bottom: 14px; font-size: 11.5px; color: #fca5a5; text-align: left;">
                    <span x-text="errorMessage"></span>
                </div>
            </template>

            <!-- Primary Action: Open Camera Button -->
            <button
                type="button"
                @click="startCamera()"
                :disabled="isLoading"
                class="doc-scanner-btn-primary"
            >
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span x-text="isLoading ? 'Binubuksan ang Camera...' : '📷 Buksan ang Camera / Scanner'"></span>
            </button>

            <!-- Divider -->
            <div class="doc-scanner-divider">O KAYA</div>

            <!-- Alternative: Native Device Camera / File Picker -->
            <div>
                <label class="doc-scanner-btn-secondary">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Pumili ng Larawan mula sa Gallery / Device</span>
                    <input
                        type="file"
                        accept="image/*"
                        capture="environment"
                        @change="handleNativeFileInput($event)"
                        style="display: none;"
                    />
                </label>
            </div>
        </div>
    </template>
</div>
