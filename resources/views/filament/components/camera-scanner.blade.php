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

            // Shutter flash effect
            this.flashActive = true;
            setTimeout(() => { this.flashActive = false; }, 200);

            // Calculate sharpness/blurriness score using edge contrast
            this.sharpnessScore = this.calculateSharpness(ctx, width, height);
            this.isBlurry = this.sharpnessScore < 60;

            // Export JPEG data URL with 88% quality
            const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
            this.state = dataUrl;

            // Stop camera stream to free hardware
            this.stopCamera();
        },

        calculateSharpness(ctx, width, height) {
            try {
                // Downscale to 160x120 for instant calculation
                const sampleCanvas = document.createElement('canvas');
                sampleCanvas.width = 160;
                sampleCanvas.height = 120;
                const sampleCtx = sampleCanvas.getContext('2d');
                sampleCtx.drawImage(ctx.canvas, 0, 0, 160, 120);
                const imgData = sampleCtx.getImageData(0, 0, 160, 120);
                const d = imgData.data;

                // Convert to grayscale luminance
                const gray = new Float32Array(160 * 120);
                for (let i = 0, j = 0; i < d.length; i += 4, j++) {
                    gray[j] = d[i] * 0.299 + d[i+1] * 0.587 + d[i+2] * 0.114;
                }

                // Laplacian edge filter variance
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
                console.warn('Sharpness check skipped:', e);
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

                // Check sharpness of uploaded image via an Image object
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
    class="w-full"
>
    <!-- Hidden Canvas for frame capture -->
    <canvas x-ref="canvas" class="hidden"></canvas>

    <!-- 1. CAPTURED PREVIEW STATE -->
    <template x-if="state">
        <div class="bg-slate-900 border border-emerald-500/40 rounded-2xl p-4 shadow-xl text-slate-100 flex flex-col items-center gap-4">
            
            <!-- Status Header -->
            <div class="w-full flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <span class="text-sm font-bold text-emerald-400 uppercase tracking-wide">✓ Dokumento Na-Scan</span>
                </div>
                <div class="text-xs text-slate-400 font-mono">
                    Ready to Save
                </div>
            </div>

            <!-- SMART SHARPNESS & BLUR DETECTION ALERT -->
            <template x-if="isBlurry">
                <div class="w-full bg-amber-500/15 border border-amber-500/50 rounded-xl p-3 flex items-start gap-3 text-amber-200 text-xs shadow-inner">
                    <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <strong class="font-bold text-amber-300 block text-sm">⚠️ Babala: Medyo Malabo ang Pagkaka-scan (Blurry)</strong>
                        <span class="mt-0.5 block leading-relaxed text-amber-100/90">
                            Maaaring tanggihan ng Admin o Guard kung hindi malinaw ang pirma ng CEO. Pindutin ang <b>"Kuhanan Ulit (Retake)"</b> sa ibaba at i-steady ang kamay o lumapit sa maliwanag na ilaw.
                        </span>
                    </div>
                </div>
            </template>

            <template x-if="!isBlurry && sharpnessScore > 0">
                <div class="w-full bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-3 py-2 flex items-center justify-between text-emerald-300 text-xs">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="font-semibold">Malinaw at Nababasa ang Dokumento (High Quality)</span>
                    </div>
                    <span class="text-[10px] text-emerald-400/80 font-mono">Clarity: Good</span>
                </div>
            </template>

            <!-- Image Snapshot Frame with Click to Zoom -->
            <div
                @click="showZoomModal = true"
                class="w-full max-h-[340px] overflow-hidden rounded-xl border border-slate-700 bg-black flex items-center justify-center relative group cursor-pointer"
                title="Pindutin para i-preview nang malaki"
            >
                <img :src="state" alt="CEO Signed Document Scan" class="max-h-[340px] w-full object-contain rounded-lg transition-transform group-hover:scale-[1.02]" />
                
                <!-- Hover Zoom Overlay Hint -->
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <div class="bg-slate-900/90 border border-slate-700 text-white text-xs px-3 py-1.5 rounded-lg flex items-center gap-1.5 shadow-xl">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                        </svg>
                        <span>Pindutin para i-zoom at basahin ang pirma</span>
                    </div>
                </div>

                <div class="absolute bottom-2 right-2 bg-black/70 backdrop-blur-sm text-emerald-300 text-[11px] font-mono px-2.5 py-1 rounded-md border border-emerald-500/30">
                    High-Res Scan
                </div>
            </div>

            <!-- Action Controls for Captured State -->
            <div class="w-full flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-800/80">
                <div class="flex items-center gap-1.5 text-xs text-slate-400">
                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Siguraduhing kita ang lagda at stamp ni CEO bago i-save.</span>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="showZoomModal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-sky-300 bg-sky-500/10 hover:bg-sky-500/20 border border-sky-500/30 rounded-lg transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        🔍 Tingnan nang Buo
                    </button>
                    <button
                        type="button"
                        @click="retake()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 rounded-lg transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Kuhanan Ulit (Retake)
                    </button>
                    <button
                        type="button"
                        @click="clear()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-red-400 bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 rounded-lg transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Alisin
                    </button>
                </div>
            </div>
        </div>
    </template>

    <!-- FULLSCREEN / ZOOM PREVIEW MODAL -->
    <template x-if="showZoomModal && state">
        <div
            class="fixed inset-0 z-[99999] bg-black/90 backdrop-blur-md flex flex-col items-center justify-between p-4 sm:p-6"
            @keydown.escape.window="showZoomModal = false"
        >
            <!-- Top bar -->
            <div class="w-full max-w-4xl flex items-center justify-between text-white border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-emerald-400">📄 Dokumento Full Preview</span>
                    <span class="text-xs text-slate-400 font-mono">(Suriin kung malinaw ang pirma ng CEO)</span>
                </div>
                <button
                    type="button"
                    @click="showZoomModal = false"
                    class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-lg border border-slate-700 transition-colors"
                >
                    ✕ Isara Preview
                </button>
            </div>

            <!-- Image Viewport -->
            <div class="flex-1 w-full max-w-4xl flex items-center justify-center overflow-auto my-3">
                <img :src="state" alt="Full Preview" class="max-h-[75vh] max-w-full object-contain rounded-xl border border-slate-700 shadow-2xl" />
            </div>

            <!-- Bottom bar -->
            <div class="w-full max-w-4xl flex items-center justify-between border-t border-slate-800 pt-3">
                <p class="text-xs text-slate-300">
                    Kung malabo o maling form, pindutin ang <b>"Kuhanan Ulit"</b> bago i-save.
                </p>
                <button
                    type="button"
                    @click="showZoomModal = false"
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-lg transition-colors"
                >
                    ✓ Ayos na, I-save Ko Na
                </button>
            </div>
        </div>
    </template>

    <!-- 2. LIVE CAMERA STREAMING STATE -->
    <template x-if="!state && isStreaming">
        <div class="bg-slate-950 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl relative flex flex-col items-center">
            
            <!-- Shutter Flash Overlay -->
            <div
                x-show="flashActive"
                x-transition:leave="transition ease-out duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0 bg-white z-50 pointer-events-none"
            ></div>

            <!-- Header Controls Bar -->
            <div class="w-full bg-slate-900/90 backdrop-blur-md px-4 py-2.5 flex items-center justify-between border-b border-slate-800 z-30">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-500/20 text-red-400 border border-red-500/30">
                        <span class="h-2 w-2 rounded-full bg-red-500 animate-ping"></span>
                        LIVE CAMERA
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <!-- Device Selector (if multiple cameras available) -->
                    <template x-if="devices.length > 1">
                        <select
                            x-model="selectedDeviceId"
                            @change="switchCamera()"
                            class="text-xs bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-2.5 py-1 focus:ring-emerald-500 focus:border-emerald-500 max-w-[150px] sm:max-w-[200px] truncate"
                        >
                            <template x-for="(d, idx) in devices" :key="d.deviceId">
                                <option :value="d.deviceId" x-text="d.label || ('Camera ' + (idx + 1))"></option>
                            </template>
                        </select>
                    </template>

                    <!-- Flip Camera Button (Front/Back toggle for mobile) -->
                    <button
                        type="button"
                        @click="toggleFacingMode()"
                        title="Palitan ang camera (harap / likod)"
                        class="p-1.5 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-lg text-slate-300 hover:text-white transition-colors cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>

                    <!-- Close Camera Button -->
                    <button
                        type="button"
                        @click="stopCamera()"
                        class="text-xs font-semibold px-2.5 py-1 bg-slate-800 hover:bg-red-500/20 text-slate-400 hover:text-red-300 border border-slate-700 hover:border-red-500/40 rounded-lg transition-colors cursor-pointer"
                    >
                        ✕ Isara
                    </button>
                </div>
            </div>

            <!-- Video Viewfinder Area with Document Guidelines -->
            <div class="relative w-full aspect-[4/3] sm:aspect-[16/10] max-h-[420px] bg-black flex items-center justify-center overflow-hidden">
                <video
                    x-ref="video"
                    autoplay
                    playsinline
                    muted
                    class="w-full h-full object-cover"
                ></video>

                <!-- Document Framing Bracket Overlay -->
                <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center p-6 sm:p-8">
                    <div class="w-full h-full border-2 border-dashed border-emerald-400/40 rounded-2xl relative flex flex-col justify-between p-3">
                        <!-- Top-Left Corner Bracket -->
                        <div class="absolute -top-1 -left-1 w-8 h-8 border-t-4 border-l-4 border-emerald-400 rounded-tl-xl shadow-[0_0_12px_rgba(52,211,153,0.8)]"></div>
                        <!-- Top-Right Corner Bracket -->
                        <div class="absolute -top-1 -right-1 w-8 h-8 border-t-4 border-r-4 border-emerald-400 rounded-tr-xl shadow-[0_0_12px_rgba(52,211,153,0.8)]"></div>
                        <!-- Bottom-Left Corner Bracket -->
                        <div class="absolute -bottom-1 -left-1 w-8 h-8 border-b-4 border-l-4 border-emerald-400 rounded-bl-xl shadow-[0_0_12px_rgba(52,211,153,0.8)]"></div>
                        <!-- Bottom-Right Corner Bracket -->
                        <div class="absolute -bottom-1 -right-1 w-8 h-8 border-b-4 border-r-4 border-emerald-400 rounded-br-xl shadow-[0_0_12px_rgba(52,211,153,0.8)]"></div>

                        <!-- Guide Label -->
                        <div class="mx-auto bg-black/60 backdrop-blur-md px-3 py-1 rounded-full text-[11px] font-semibold text-emerald-300 border border-emerald-500/30">
                            📄 I-sentro ang pirmadong papel sa loob ng border
                        </div>

                        <!-- Subtle bottom hint -->
                        <div class="mx-auto text-[10px] text-white/70 font-mono tracking-wider">
                            Panatilihing steady ang kamay para hindi lumabo
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Shutter Action Bar -->
            <div class="w-full bg-slate-900/95 backdrop-blur-md p-4 flex items-center justify-center border-t border-slate-800">
                <button
                    type="button"
                    @click="capture()"
                    class="group inline-flex items-center gap-2.5 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-95 text-white font-bold text-sm sm:text-base rounded-full shadow-[0_0_25px_rgba(16,185,129,0.35)] transition-all cursor-pointer"
                >
                    <div class="w-6 h-6 rounded-full border-2 border-white flex items-center justify-center">
                        <div class="w-3.5 h-3.5 rounded-full bg-white group-hover:scale-110 transition-transform"></div>
                    </div>
                    <span>KUHANAN NG LITRATO (CAPTURE)</span>
                </button>
            </div>
        </div>
    </template>

    <!-- 3. INACTIVE CAMERA / LAUNCH SCREEN STATE -->
    <template x-if="!state && !isStreaming">
        <div class="bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 border border-slate-800/80 rounded-2xl p-6 text-center text-slate-100 shadow-xl relative overflow-hidden">
            <!-- Decorative background glow -->
            <div class="absolute -top-16 -right-16 w-32 h-32 bg-emerald-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -bottom-16 -left-16 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="max-w-md mx-auto flex flex-col items-center gap-4 relative z-10">
                <!-- Icon badge -->
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shadow-[0_0_25px_rgba(16,185,129,0.15)]">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>

                <div>
                    <h3 class="text-base font-bold text-white tracking-tight">
                        Live Camera & Document Scanner
                    </h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm">
                        Gamitin ang iyong laptop webcam o cellphone camera para direktang kuhanan ng litrato ang dokumentong may pirma ng CEO.
                    </p>
                </div>

                <!-- Error alert if camera permission failed -->
                <template x-if="errorMessage">
                    <div class="w-full bg-red-950/40 border border-red-800/60 rounded-xl p-3 text-xs text-red-300 text-left flex items-start gap-2">
                        <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span x-text="errorMessage"></span>
                    </div>
                </template>

                <!-- Primary Action: Open Camera Button -->
                <button
                    type="button"
                    @click="startCamera()"
                    :disabled="isLoading"
                    class="inline-flex items-center justify-center gap-2.5 px-6 py-3 bg-emerald-600 hover:bg-emerald-500 active:scale-95 text-white font-bold text-sm rounded-xl shadow-lg shadow-emerald-600/30 hover:shadow-emerald-500/50 transition-all cursor-pointer w-full sm:w-auto"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span x-text="isLoading ? 'Binubuksan ang Camera...' : '📷 Buksan ang Camera / Start Scanner'"></span>
                </button>

                <!-- Alternative: Native Device Camera / File Picker -->
                <div class="w-full pt-3 mt-1 border-t border-slate-800/80 flex flex-col items-center gap-2">
                    <span class="text-[11px] text-slate-500 font-medium">O kaya pumili ng litrato mula sa iyong device / gallery:</span>
                    <label class="inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-semibold text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-white border border-slate-700 rounded-lg cursor-pointer transition-colors">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Pumili ng Larawan</span>
                        <input
                            type="file"
                            accept="image/*"
                            capture="environment"
                            @change="handleNativeFileInput($event)"
                            class="hidden"
                        />
                    </label>
                </div>
            </div>
        </div>
    </template>
</div>
