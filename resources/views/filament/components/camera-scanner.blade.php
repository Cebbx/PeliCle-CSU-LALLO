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

        async init() {
            // Check available camera devices if browser supports it
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
                // First stop any existing stream
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

                // Re-enumerate devices after permission is granted so device labels appear
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

            // Export JPEG data URL with 88% quality (high clarity, fast transfer)
            const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
            this.state = dataUrl;

            // Stop camera stream to free hardware
            this.stopCamera();
        },

        retake() {
            this.state = null;
            this.$nextTick(() => {
                this.startCamera();
            });
        },

        clear() {
            this.state = null;
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
            <div class="w-full flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <span class="text-sm font-bold text-emerald-400 uppercase tracking-wide">✓ Dokumento Matagumpay na Na-Scan</span>
                </div>
                <div class="text-xs text-slate-400 font-mono">
                    Ready to Save
                </div>
            </div>

            <!-- Image Snapshot Frame -->
            <div class="w-full max-h-[360px] overflow-hidden rounded-xl border border-slate-700 bg-black flex items-center justify-center relative group">
                <img :src="state" alt="CEO Signed Document Scan" class="max-h-[360px] w-full object-contain rounded-lg" />
                <div class="absolute bottom-2 right-2 bg-black/70 backdrop-blur-sm text-emerald-300 text-[11px] font-mono px-2.5 py-1 rounded-md border border-emerald-500/30">
                    High-Res Scan Attached
                </div>
            </div>

            <!-- Action Controls for Captured State -->
            <div class="w-full flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-800/80">
                <p class="text-xs text-slate-400">
                    Siguraduhing malinaw ang pirma ng CEO bago i-save.
                </p>
                <div class="flex items-center gap-2">
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
                            Panatilihing steady ang kamay
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
