<div
    x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        isDrawing: false,
        hasSignature: false,
        mode: 'draw', // 'draw' or 'upload'
        canvas: null,
        ctx: null,
        lastX: 0,
        lastY: 0,

        init() {
            this.$nextTick(() => {
                this.initCanvas();
                if (this.state) {
                    this.hasSignature = true;
                    this.loadExistingSignature(this.state);
                }
            });

            // Watch for external state changes
            this.$watch('state', (val) => {
                if (!val) {
                    this.hasSignature = false;
                    this.clearCanvasOnly();
                } else if (!this.hasSignature) {
                    this.hasSignature = true;
                    this.loadExistingSignature(val);
                }
            });
        },

        initCanvas() {
            this.canvas = this.$refs.sigCanvas;
            if (!this.canvas) return;

            this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
            this.resizeCanvas();

            window.addEventListener('resize', () => {
                if (!this.hasSignature) {
                    this.resizeCanvas();
                }
            });
        },

        resizeCanvas() {
            if (!this.canvas) return;
            const rect = this.canvas.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) return;

            const dpr = window.devicePixelRatio || 1;
            this.canvas.width = rect.width * dpr;
            this.canvas.height = rect.height * dpr;
            this.ctx.scale(dpr, dpr);

            this.ctx.lineWidth = 2.5;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.ctx.strokeStyle = '#0f172a';
        },

        getPointerPos(e) {
            const rect = this.canvas.getBoundingClientRect();
            const clientX = e.clientX ?? (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
            const clientY = e.clientY ?? (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        },

        startDrawing(e) {
            e.preventDefault();
            this.isDrawing = true;
            try {
                if (e.pointerId && this.canvas.setPointerCapture) {
                    this.canvas.setPointerCapture(e.pointerId);
                }
            } catch (err) {}

            const pos = this.getPointerPos(e);
            this.lastX = pos.x;
            this.lastY = pos.y;

            this.ctx.beginPath();
            this.ctx.arc(this.lastX, this.lastY, 1.25, 0, Math.PI * 2);
            this.ctx.fillStyle = '#0f172a';
            this.ctx.fill();

            this.ctx.beginPath();
            this.ctx.moveTo(this.lastX, this.lastY);
        },

        draw(e) {
            if (!this.isDrawing) return;
            e.preventDefault();
            const pos = this.getPointerPos(e);

            this.ctx.beginPath();
            this.ctx.moveTo(this.lastX, this.lastY);
            this.ctx.lineTo(pos.x, pos.y);
            this.ctx.stroke();

            this.lastX = pos.x;
            this.lastY = pos.y;
            this.hasSignature = true;
        },

        stopDrawing(e) {
            if (!this.isDrawing) return;
            this.isDrawing = false;
            try {
                if (e && e.pointerId && this.canvas.releasePointerCapture) {
                    this.canvas.releasePointerCapture(e.pointerId);
                }
            } catch (err) {}
            this.saveSignatureState();
        },

        saveSignatureState() {
            if (!this.canvas || !this.hasSignature) return;
            const dataUrl = this.canvas.toDataURL('image/png');
            this.state = dataUrl;
        },

        clear() {
            this.clearCanvasOnly();
            this.hasSignature = false;
            this.state = null;
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = '';
            }
        },

        clearCanvasOnly() {
            if (!this.canvas || !this.ctx) return;
            const dpr = window.devicePixelRatio || 1;
            this.ctx.clearRect(0, 0, this.canvas.width / dpr, this.canvas.height / dpr);
        },

        loadExistingSignature(src) {
            if (!src || !this.canvas) return;
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                this.clearCanvasOnly();
                const dpr = window.devicePixelRatio || 1;
                const cw = this.canvas.width / dpr;
                const ch = this.canvas.height / dpr;

                const scale = Math.min((cw * 0.85) / img.width, (ch * 0.85) / img.height, 1);
                const w = img.width * scale;
                const h = img.height * scale;
                const x = (cw - w) / 2;
                const y = (ch - h) / 2;

                this.ctx.drawImage(img, x, y, w, h);
                this.hasSignature = true;
            };
            img.src = src;
        },

        handleFileUpload(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (!file.type.match('image.*')) {
                alert('Please select an image file (PNG, JPG, or SVG).');
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                const dataUrl = event.target.result;
                this.state = dataUrl;
                this.loadExistingSignature(dataUrl);
            };
            reader.readAsDataURL(file);
        },

        switchMode(targetMode) {
            this.mode = targetMode;
            if (targetMode === 'draw') {
                this.$nextTick(() => {
                    this.resizeCanvas();
                    if (this.state) {
                        this.loadExistingSignature(this.state);
                    }
                });
            }
        }
    }"
    class="sigpad-root"
>
    <!-- Scoped CSS for Bulletproof Filament Rendering -->
    <style>
        .sigpad-root {
            width: 100%;
            box-sizing: border-box;
            font-family: inherit;
        }
        .sigpad-root *, .sigpad-root *::before, .sigpad-root *::after {
            box-sizing: border-box;
        }

        .sigpad-card {
            background: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.35);
            color: #f8fafc;
            width: 100%;
        }
        :root:not(.dark) .sigpad-card,
        html:not(.dark) .sigpad-card {
            background: #ffffff;
            border-color: #e2e8f0;
            color: #0f172a;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        }

        .sigpad-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
            padding-bottom: 14px;
            border-bottom: 1px solid #1e293b;
        }
        :root:not(.dark) .sigpad-header,
        html:not(.dark) .sigpad-header {
            border-bottom-color: #f1f5f9;
        }

        .sigpad-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sigpad-icon-badge {
            width: 32px;
            height: 32px;
            min-width: 32px;
            max-width: 32px;
            min-height: 32px;
            max-height: 32px;
            border-radius: 10px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.28);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            box-shadow: 0 2px 6px rgba(16, 185, 129, 0.15);
        }
        .sigpad-title {
            font-size: 14.5px;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            letter-spacing: -0.01em;
        }
        :root:not(.dark) .sigpad-title,
        html:not(.dark) .sigpad-title {
            color: #0f172a;
        }
        .sigpad-captured-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            color: #10b981;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            padding: 2px 9px;
            border-radius: 9999px;
            margin-left: 4px;
        }
        .sigpad-subtitle {
            font-size: 12px;
            color: #94a3b8;
            margin: 4px 0 0 0;
            line-height: 1.45;
        }
        :root:not(.dark) .sigpad-subtitle,
        html:not(.dark) .sigpad-subtitle {
            color: #64748b;
        }

        /* Pill Mode Switcher */
        .sigpad-mode-switcher {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
        }
        :root:not(.dark) .sigpad-mode-switcher,
        html:not(.dark) .sigpad-mode-switcher {
            background: #f1f5f9;
            border-color: #e2e8f0;
        }
        .sigpad-mode-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
            color: #94a3b8;
            background: transparent;
            user-select: none;
            white-space: nowrap;
        }
        :root:not(.dark) .sigpad-mode-btn,
        html:not(.dark) .sigpad-mode-btn {
            color: #64748b;
        }
        .sigpad-mode-btn:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
        }
        :root:not(.dark) .sigpad-mode-btn:hover,
        html:not(.dark) .sigpad-mode-btn:hover {
            color: #0f172a;
            background: rgba(0, 0, 0, 0.04);
        }
        .sigpad-mode-btn.active {
            background: #10b981 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 10px rgba(16, 185, 129, 0.35);
        }
        .sigpad-mode-btn svg {
            width: 14px !important;
            height: 14px !important;
            min-width: 14px !important;
            min-height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
            display: inline-block !important;
            stroke: currentColor !important;
            stroke-width: 2.2 !important;
            fill: none !important;
            vertical-align: middle;
        }

        /* 1. Canvas Pad Container */
        .sigpad-canvas-box {
            position: relative;
            width: 100%;
            height: 220px;
            min-height: 220px;
            background: #ffffff;
            border: 2px solid #cbd5e1;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04);
            touch-action: none;
        }
        .sigpad-canvas-box:hover {
            border-color: #94a3b8;
        }
        .sigpad-canvas-element {
            position: relative;
            z-index: 10;
            width: 100%;
            height: 100%;
            display: block;
            cursor: crosshair;
            touch-action: none;
        }

        /* Dotted Signature Baseline */
        .sigpad-waterline {
            position: absolute;
            left: 32px;
            right: 32px;
            bottom: 40px;
            border-bottom: 1.5px dashed #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            pointer-events: none;
            font-size: 11px;
            font-family: monospace;
            color: #94a3b8;
            padding: 0 4px 4px 4px;
            user-select: none;
            z-index: 4;
        }

        /* Initial Helper Placeholder */
        .sigpad-watermark {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            user-select: none;
            color: #94a3b8;
            text-align: center;
            padding: 16px;
            z-index: 5;
        }
        .sigpad-watermark-icon {
            font-size: 24px;
            opacity: 0.45;
            margin-bottom: 4px;
        }
        .sigpad-watermark-text {
            font-size: 12.5px;
            font-weight: 600;
            color: #64748b;
        }
        .sigpad-watermark-sub {
            font-size: 10.5px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* Floating Clear Button */
        .sigpad-clear-btn {
            position: absolute;
            bottom: 12px;
            left: 14px;
            z-index: 20;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 13px;
            background: rgba(15, 23, 42, 0.88);
            color: #ffffff;
            font-size: 11.5px;
            font-weight: 700;
            border-radius: 9px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(6px);
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }
        .sigpad-clear-btn:hover {
            background: #0f172a;
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-1px);
        }
        .sigpad-clear-btn:active {
            transform: translateY(0);
        }
        .sigpad-clear-btn svg {
            width: 13px !important;
            height: 13px !important;
            color: #f43f5e;
            stroke-width: 2.5 !important;
            fill: none !important;
        }

        /* 2. Upload Box */
        .sigpad-upload-box {
            position: relative;
            width: 100%;
            height: 220px;
            min-height: 220px;
            border: 2px dashed #475569;
            border-radius: 14px;
            background: #0b1120;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        :root:not(.dark) .sigpad-upload-box,
        html:not(.dark) .sigpad-upload-box {
            background: #f8fafc;
            border-color: #cbd5e1;
        }
        .sigpad-upload-box:hover {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.04);
        }
        .sigpad-upload-icon-wrap {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: rgba(16, 185, 129, 0.12);
            color: #10b981;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 10px;
            transition: transform 0.2s;
        }
        .sigpad-upload-box:hover .sigpad-upload-icon-wrap {
            transform: scale(1.08);
        }
        .sigpad-upload-title {
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 3px;
        }
        :root:not(.dark) .sigpad-upload-title,
        html:not(.dark) .sigpad-upload-title {
            color: #0f172a;
        }
        .sigpad-upload-sub {
            font-size: 11px;
            color: #94a3b8;
        }

        .sigpad-preview-card {
            max-height: 120px;
            max-width: 280px;
            background: #ffffff;
            border-radius: 10px;
            padding: 8px 14px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sigpad-preview-img {
            max-height: 90px;
            max-width: 250px;
            object-fit: contain;
        }

        /* Footer */
        .sigpad-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 14px;
            padding-top: 10px;
            border-top: 1px solid #1e293b;
            font-size: 11.5px;
            color: #94a3b8;
        }
        :root:not(.dark) .sigpad-footer,
        html:not(.dark) .sigpad-footer {
            border-top-color: #f1f5f9;
            color: #64748b;
        }
        .sigpad-footer-info {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .sigpad-footer-info svg {
            width: 14px !important;
            height: 14px !important;
            color: #10b981;
            stroke-width: 2 !important;
            fill: none !important;
            flex-shrink: 0;
        }
        .sigpad-remove-btn {
            background: none;
            border: none;
            color: #f43f5e;
            font-size: 11.5px;
            font-weight: 700;
            text-decoration: underline;
            cursor: pointer;
            padding: 0;
            transition: color 0.15s;
        }
        .sigpad-remove-btn:hover {
            color: #e11d48;
        }
    </style>

    <!-- Outer Card Container -->
    <div class="sigpad-card">
        
        <!-- Header: Add your signature + Subtitle + Mode Switcher -->
        <div class="sigpad-header">
            <div>
                <div class="sigpad-title-wrap">
                    <div class="sigpad-icon-badge">
                        ✍️
                    </div>
                    <h3 class="sigpad-title">
                        Add your signature
                    </h3>
                    <template x-if="hasSignature">
                        <span class="sigpad-captured-badge">
                            ✓ E-Sign Captured
                        </span>
                    </template>
                </div>
                <p class="sigpad-subtitle">
                    Sign using your finger tips or mouse on the box below, or upload your signature image.
                </p>
            </div>

            <!-- Mode Switcher: Draw vs Upload -->
            <div class="sigpad-mode-switcher">
                <button
                    type="button"
                    @click="switchMode('draw')"
                    :class="mode === 'draw' ? 'active' : ''"
                    class="sigpad-mode-btn"
                >
                    <svg viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span>Draw Signature</span>
                </button>
                <button
                    type="button"
                    @click="switchMode('upload')"
                    :class="mode === 'upload' ? 'active' : ''"
                    class="sigpad-mode-btn"
                >
                    <svg viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    <span>Upload Image</span>
                </button>
            </div>
        </div>

        <!-- 1. Interactive Drawing Canvas Pad -->
        <div x-show="mode === 'draw'" style="position: relative;">
            <div class="sigpad-canvas-box">
                
                <!-- Background subtle dotted water-line guide -->
                <div class="sigpad-waterline">
                    <span>Signature Line</span>
                    <span>✍️</span>
                </div>

                <!-- Canvas element -->
                <canvas
                    x-ref="sigCanvas"
                    @pointerdown="startDrawing($event)"
                    @pointermove="draw($event)"
                    @pointerup="stopDrawing($event)"
                    @pointercancel="stopDrawing($event)"
                    @pointerleave="stopDrawing($event)"
                    class="sigpad-canvas-element"
                ></canvas>

                <!-- Initial Helper placeholder overlay (disappears when drawn) -->
                <div
                    x-show="!hasSignature"
                    class="sigpad-watermark"
                >
                    <span class="sigpad-watermark-icon">✏️</span>
                    <span class="sigpad-watermark-text">Sign using your finger tips or mouse on the box</span>
                    <span class="sigpad-watermark-sub">Smooth, responsive digital signature</span>
                </div>

                <!-- Floating Clear Button in corner -->
                <button
                    type="button"
                    x-show="hasSignature"
                    @click="clear()"
                    class="sigpad-clear-btn"
                    title="Clear and re-sign"
                >
                    <svg viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Clear</span>
                </button>
            </div>
        </div>

        <!-- 2. File Upload Tab Mode -->
        <div x-show="mode === 'upload'" style="display: none;">
            <div 
                @click="$refs.fileInput.click()" 
                class="sigpad-upload-box"
            >
                <input
                    type="file"
                    x-ref="fileInput"
                    @change="handleFileUpload($event)"
                    accept="image/png, image/jpeg, image/jpg, image/webp, image/svg+xml"
                    style="display: none;"
                />

                <template x-if="!hasSignature">
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <div class="sigpad-upload-icon-wrap">
                            📁
                        </div>
                        <span class="sigpad-upload-title">
                            Click to upload your signature image
                        </span>
                        <span class="sigpad-upload-sub">
                            Supports PNG, JPG, or SVG (Transparent background recommended)
                        </span>
                    </div>
                </template>

                <template x-if="hasSignature">
                    <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                        <div class="sigpad-preview-card">
                            <img :src="state" alt="Signature Preview" class="sigpad-preview-img" />
                        </div>
                        <span style="font-size: 12px; font-weight: 700; color: #10b981;">
                            ✓ Signature file loaded
                        </span>
                        <span style="font-size: 10.5px; color: #94a3b8; text-decoration: underline; margin-top: 3px;">
                            Click to choose a different file
                        </span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer Guidance & Info -->
        <div class="sigpad-footer">
            <div class="sigpad-footer-info">
                <svg viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>The e-sign will be positioned directly <b>above your requester name</b> on the printable form.</span>
            </div>

            <template x-if="hasSignature">
                <button
                    type="button"
                    @click="clear()"
                    class="sigpad-remove-btn"
                >
                    Remove Signature
                </button>
            </template>
        </div>

    </div>
</div>
