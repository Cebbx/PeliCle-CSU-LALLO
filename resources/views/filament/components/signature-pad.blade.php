<div
    x-data="{
        state: $wire.$entangle('{{ $getStatePath() }}'),
        isDrawing: false,
        hasSignature: false,
        mode: 'draw', // 'draw' or 'upload'
        history: [],
        canvas: null,
        ctx: null,
        lastX: 0,
        lastY: 0,

        // Fullscreen state & canvas
        isFullscreen: false,
        fsHistory: [],
        fsCanvas: null,
        fsCtx: null,
        isFsDrawing: false,
        lastFsX: 0,
        lastFsY: 0,

        init() {
            this.$nextTick(() => {
                this.initCanvas();
                if (this.state) {
                    this.hasSignature = true;
                    this.loadExistingSignature(this.state);
                    this.history = [this.state];
                }
            });

            // Watch for external state changes
            this.$watch('state', (val) => {
                if (!val) {
                    this.hasSignature = false;
                    this.history = [];
                    this.clearCanvasOnly();
                } else if (!this.hasSignature) {
                    this.hasSignature = true;
                    this.loadExistingSignature(val);
                    if (this.history.length === 0) {
                        this.history = [val];
                    }
                }
            });
        },

        initCanvas() {
            this.canvas = this.$refs.sigCanvas;
            if (!this.canvas) return;

            this.ctx = this.canvas.getContext('2d', { willReadFrequently: true });
            this.resizeCanvas();

            window.addEventListener('resize', () => {
                if (!this.isFullscreen && !this.hasSignature) {
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

            this.ctx.lineWidth = 3.8;
            this.ctx.lineCap = 'round';
            this.ctx.lineJoin = 'round';
            this.ctx.strokeStyle = '#000000';
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
            this.ctx.arc(this.lastX, this.lastY, 1.9, 0, Math.PI * 2);
            this.ctx.fillStyle = '#000000';
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
            this.pushHistory();
        },

        pushHistory() {
            if (!this.canvas || !this.hasSignature) return;
            const dataUrl = this.canvas.toDataURL('image/png');
            if (this.history.length > 25) this.history.shift();
            this.history.push(dataUrl);
        },

        undo() {
            if (!this.canvas || !this.ctx) return;
            if (this.history.length === 0) {
                this.clear();
                return;
            }
            this.history.pop(); // Remove current stroke
            if (this.history.length > 0) {
                const prev = this.history[this.history.length - 1];
                this.loadExistingSignature(prev);
                this.state = prev;
            } else {
                this.clear();
            }
        },

        saveSignatureState() {
            if (!this.canvas || !this.hasSignature) return;
            const dataUrl = this.canvas.toDataURL('image/png');
            this.state = dataUrl;
        },

        clear() {
            this.clearCanvasOnly();
            this.hasSignature = false;
            this.history = [];
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
            this.loadExistingSignatureToCanvas(src, this.canvas, this.ctx);
        },

        loadExistingSignatureToCanvas(src, canvas, ctx) {
            if (!src || !canvas || !ctx) return;
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                const dpr = window.devicePixelRatio || 1;
                ctx.clearRect(0, 0, canvas.width / dpr, canvas.height / dpr);
                const cw = canvas.width / dpr;
                const ch = canvas.height / dpr;

                const scale = Math.min((cw * 0.88) / img.width, (ch * 0.88) / img.height, 1);
                const w = img.width * scale;
                const h = img.height * scale;
                const x = (cw - w) / 2;
                const y = (ch - h) / 2;

                ctx.drawImage(img, x, y, w, h);
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
                this.history = [dataUrl];
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
        },

        /* Fullscreen E-Signature Methods */
        openFullscreen() {
            this.mode = 'draw';
            this.isFullscreen = true;
            this.fsHistory = this.state ? [this.state] : [];
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                this.initFsCanvas();
                if (this.state) {
                    this.loadExistingSignatureToCanvas(this.state, this.fsCanvas, this.fsCtx);
                }
            });
        },

        closeFullscreen(save = true) {
            if (save && this.fsCanvas && this.hasSignature) {
                const dataUrl = this.fsCanvas.toDataURL('image/png');
                this.state = dataUrl;
                this.history.push(dataUrl);
            }
            this.isFullscreen = false;
            document.body.style.overflow = '';
            this.$nextTick(() => {
                this.resizeCanvas();
                if (this.state) {
                    this.loadExistingSignature(this.state);
                }
            });
        },

        initFsCanvas() {
            this.fsCanvas = this.$refs.sigFsCanvas;
            if (!this.fsCanvas) return;
            this.fsCtx = this.fsCanvas.getContext('2d', { willReadFrequently: true });
            this.resizeFsCanvas();
        },

        resizeFsCanvas() {
            if (!this.fsCanvas) return;
            const rect = this.fsCanvas.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) return;

            const dpr = window.devicePixelRatio || 1;
            this.fsCanvas.width = rect.width * dpr;
            this.fsCanvas.height = rect.height * dpr;
            this.fsCtx.scale(dpr, dpr);

            this.fsCtx.lineWidth = 5.5;
            this.fsCtx.lineCap = 'round';
            this.fsCtx.lineJoin = 'round';
            this.fsCtx.strokeStyle = '#000000';
        },

        getFsPointerPos(e) {
            const rect = this.fsCanvas.getBoundingClientRect();
            const clientX = e.clientX ?? (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
            const clientY = e.clientY ?? (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        },

        startFsDrawing(e) {
            e.preventDefault();
            this.isFsDrawing = true;
            try {
                if (e.pointerId && this.fsCanvas.setPointerCapture) {
                    this.fsCanvas.setPointerCapture(e.pointerId);
                }
            } catch (err) {}

            const pos = this.getFsPointerPos(e);
            this.lastFsX = pos.x;
            this.lastFsY = pos.y;

            this.fsCtx.beginPath();
            this.fsCtx.arc(this.lastFsX, this.lastFsY, 2.75, 0, Math.PI * 2);
            this.fsCtx.fillStyle = '#000000';
            this.fsCtx.fill();

            this.fsCtx.beginPath();
            this.fsCtx.moveTo(this.lastFsX, this.lastFsY);
        },

        drawFs(e) {
            if (!this.isFsDrawing) return;
            e.preventDefault();
            const pos = this.getFsPointerPos(e);

            this.fsCtx.beginPath();
            this.fsCtx.moveTo(this.lastFsX, this.lastFsY);
            this.fsCtx.lineTo(pos.x, pos.y);
            this.fsCtx.stroke();

            this.lastFsX = pos.x;
            this.lastFsY = pos.y;
            this.hasSignature = true;
        },

        stopFsDrawing(e) {
            if (!this.isFsDrawing) return;
            this.isFsDrawing = false;
            try {
                if (e && e.pointerId && this.fsCanvas.releasePointerCapture) {
                    this.fsCanvas.releasePointerCapture(e.pointerId);
                }
            } catch (err) {}
            this.pushFsHistory();
        },

        pushFsHistory() {
            if (!this.fsCanvas || !this.hasSignature) return;
            const dataUrl = this.fsCanvas.toDataURL('image/png');
            if (this.fsHistory.length > 25) this.fsHistory.shift();
            this.fsHistory.push(dataUrl);
            this.state = dataUrl;
        },

        undoFs() {
            if (!this.fsCanvas || !this.fsCtx) return;
            if (this.fsHistory.length === 0) {
                this.clearFs();
                return;
            }
            this.fsHistory.pop();
            if (this.fsHistory.length > 0) {
                const prev = this.fsHistory[this.fsHistory.length - 1];
                this.loadExistingSignatureToCanvas(prev, this.fsCanvas, this.fsCtx);
                this.state = prev;
            } else {
                this.clearFs();
            }
        },

        clearFs() {
            if (!this.fsCanvas || !this.fsCtx) return;
            const dpr = window.devicePixelRatio || 1;
            this.fsCtx.clearRect(0, 0, this.fsCanvas.width / dpr, this.fsCanvas.height / dpr);
            this.fsHistory = [];
            this.clear();
        }
    }"
    class="sigpad-root"
>
    <style>
        [x-cloak] {
            display: none !important;
        }

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

        /* Header Actions */
        .sigpad-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* Fullscreen Trigger Button */
        .sigpad-fullscreen-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 10px;
            border: 1px solid #0284c7;
            background: #0284c7;
            color: #ffffff;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.35);
            user-select: none;
            white-space: nowrap;
        }
        .sigpad-fullscreen-btn:hover {
            background: #0369a1;
            border-color: #0369a1;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.45);
        }
        .sigpad-fullscreen-btn svg {
            width: 14px !important;
            height: 14px !important;
            stroke-width: 2.3 !important;
            fill: none !important;
        }

        /* Mode & Orientation Switchers */
        .sigpad-switch-group {
            display: inline-flex;
            background: #1e293b;
            border-radius: 10px;
            padding: 3px;
            border: 1px solid #334155;
            gap: 2px;
        }
        :root:not(.dark) .sigpad-switch-group,
        html:not(.dark) .sigpad-switch-group {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        .sigpad-switch-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 11px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
            white-space: nowrap;
        }
        :root:not(.dark) .sigpad-switch-btn,
        html:not(.dark) .sigpad-switch-btn {
            color: #475569;
        }
        .sigpad-switch-btn.active {
            background: #3b82f6;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.35);
        }
        .sigpad-switch-btn:hover:not(.active) {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.05);
        }
        :root:not(.dark) .sigpad-switch-btn:hover:not(.active) {
            color: #0f172a;
            background: #e2e8f0;
        }
        .sigpad-switch-btn svg {
            width: 13px !important;
            height: 13px !important;
            stroke-width: 2 !important;
            fill: none !important;
        }

        /* 1. Canvas Interactive Drawing Box */
        .sigpad-canvas-box {
            position: relative;
            background: #ffffff;
            border-radius: 14px;
            border: 2px dashed #cbd5e1;
            height: 190px;
            width: 100%;
            overflow: hidden;
            transition: all 0.25s ease;
            cursor: crosshair;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.04);
            touch-action: none;
        }
        .sigpad-canvas-box:hover {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .sigpad-canvas-element {
            position: relative;
            z-index: 10;
            width: 100%;
            height: 100%;
            display: block;
            touch-action: none;
        }

        /* Waterline guide */
        .sigpad-waterline {
            position: absolute;
            left: 24px;
            right: 24px;
            bottom: 36px;
            border-bottom: 1.5px dashed #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            pointer-events: none;
            font-size: 11px;
            font-family: monospace;
            color: #94a3b8;
            padding-bottom: 4px;
            user-select: none;
            z-index: 2;
        }

        /* Corner Fullscreen Badge inside Canvas */
        .sigpad-canvas-fs-badge {
            position: absolute;
            top: 10px;
            right: 12px;
            z-index: 20;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 11px;
            background: rgba(15, 23, 42, 0.88);
            color: #38bdf8;
            font-size: 11px;
            font-weight: 700;
            border-radius: 8px;
            border: 1px solid rgba(56, 189, 248, 0.4);
            backdrop-filter: blur(6px);
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }
        .sigpad-canvas-fs-badge:hover {
            background: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
            transform: scale(1.04);
        }

        /* Floating Undo & Clear Actions (Top-left of canvas) */
        .sigpad-canvas-controls {
            position: absolute;
            top: 10px;
            left: 12px;
            z-index: 20;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .sigpad-btn-undo {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            background: rgba(14, 165, 233, 0.12);
            color: #0284c7;
            border: 1px solid rgba(14, 165, 233, 0.35);
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            backdrop-filter: blur(4px);
            user-select: none;
        }
        .sigpad-btn-undo:hover {
            background: #0284c7;
            color: #ffffff;
        }
        .sigpad-btn-clear {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            background: rgba(244, 63, 94, 0.12);
            color: #f43f5e;
            border: 1px solid rgba(244, 63, 94, 0.35);
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            backdrop-filter: blur(4px);
            user-select: none;
        }
        .sigpad-btn-clear:hover {
            background: #f43f5e;
            color: #ffffff;
        }

        /* Watermark placeholder hint */
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
            z-index: 3;
            text-align: center;
            padding: 12px;
        }

        /* 2. File Upload Box */
        .sigpad-upload-box {
            background: #ffffff;
            border: 2px dashed #cbd5e1;
            border-radius: 14px;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s ease;
            min-height: 190px;
            user-select: none;
        }
        .sigpad-upload-box:hover {
            border-color: #3b82f6;
            background: #f8fafc;
        }

        /* Footer */
        .sigpad-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid #1e293b;
            font-size: 11.5px;
            color: #94a3b8;
            flex-wrap: wrap;
            gap: 10px;
        }
        :root:not(.dark) .sigpad-footer,
        html:not(.dark) .sigpad-footer {
            border-top-color: #f1f5f9;
            color: #64748b;
        }

        /* ========================================================= */
        /* FULLSCREEN MODAL OVERLAY                                  */
        /* ========================================================= */
        .sigpad-fs-modal {
            display: none !important;
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            z-index: 9999999 !important;
            background: rgba(15, 23, 42, 0.97) !important;
            backdrop-filter: blur(14px) !important;
            padding: 14px 18px !important;
            box-sizing: border-box !important;
            flex-direction: column !important;
        }
        :root:not(.dark) .sigpad-fs-modal,
        html:not(.dark) .sigpad-fs-modal {
            background: rgba(241, 245, 249, 0.98) !important;
        }
        .sigpad-fs-modal.is-open {
            display: flex !important;
        }

        .sigpad-fs-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            flex-shrink: 0;
            flex-wrap: wrap;
        }
        :root:not(.dark) .sigpad-fs-header,
        html:not(.dark) .sigpad-fs-header {
            border-bottom-color: #cbd5e1;
        }

        .sigpad-fs-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            flex-wrap: wrap;
        }

        .sigpad-fs-canvas-box {
            position: relative;
            flex: 1;
            width: 100%;
            min-height: 0;
            background: #ffffff;
            border: 2.5px solid #38bdf8;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
            touch-action: none;
            transition: all 0.25s ease;
        }
        .sigpad-fs-canvas-element {
            position: relative;
            z-index: 10;
            width: 100%;
            height: 100%;
            display: block;
            cursor: crosshair;
            touch-action: none;
        }
        .sigpad-fs-waterline {
            position: absolute;
            left: 36px;
            right: 36px;
            bottom: 48px;
            border-bottom: 2px dashed #94a3b8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            pointer-events: none;
            font-size: 12px;
            font-family: monospace;
            font-weight: 700;
            color: #64748b;
            padding-bottom: 6px;
            user-select: none;
            z-index: 4;
        }
    </style>

    <!-- Outer Card Container -->
    <div class="sigpad-card">

        <!-- Header: Title + Subtitle + Action Switchers -->
        <div class="sigpad-header">
            <div>
                <div class="sigpad-title-wrap">
                    <div class="sigpad-icon-badge">✍️</div>
                    <h3 class="sigpad-title">Requester Electronic Signature</h3>
                    <template x-if="hasSignature">
                        <span class="sigpad-captured-badge">✓ E-Sign Captured</span>
                    </template>
                </div>
                <p class="sigpad-subtitle">
                    Pumirma gamit ang daliri, stylus pen, o mouse.
                </p>
            </div>

            <!-- Controls: Fullscreen + Mode -->
            <div class="sigpad-header-actions">
                <!-- 1. Full Screen Trigger Button -->
                <button
                    type="button"
                    @click="openFullscreen()"
                    class="sigpad-fullscreen-btn"
                    title="Pumirma sa Full Screen"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                    <span>Full Screen</span>
                </button>

                <!-- 2. Draw vs Upload Switcher -->
                <div class="sigpad-switch-group">
                    <button
                        type="button"
                        @click="switchMode('draw')"
                        :class="mode === 'draw' ? 'active' : ''"
                        class="sigpad-switch-btn"
                    >
                        <span>Draw</span>
                    </button>
                    <button
                        type="button"
                        @click="switchMode('upload')"
                        :class="mode === 'upload' ? 'active' : ''"
                        class="sigpad-switch-btn"
                    >
                        <span>Upload</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 1. Interactive Drawing Canvas Pad -->
        <div x-show="mode === 'draw'" style="position: relative;">
            <div class="sigpad-canvas-box">
                <!-- Dotted Baseline Guide -->
                <div class="sigpad-waterline">
                    <span>Signature Line</span>
                    <span>✍️ E-Sign</span>
                </div>

                <!-- Floating Controls (Undo & Clear) -->
                <div class="sigpad-canvas-controls">
                    <!-- Undo Button -->
                    <button
                        type="button"
                        x-show="hasSignature && history.length > 0"
                        @click="undo()"
                        class="sigpad-btn-undo"
                        title="I-undo ang huling linya o stroke"
                    >
                        <svg viewBox="0 0 24 24" stroke="currentColor" style="width: 13px; height: 13px; stroke-width: 2.2; fill: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                        </svg>
                        <span>Undo</span>
                    </button>

                    <!-- Clear Button -->
                    <button
                        type="button"
                        x-show="hasSignature"
                        @click="clear()"
                        class="sigpad-btn-clear"
                        title="Burahin ang buong pirma"
                    >
                        <svg viewBox="0 0 24 24" stroke="currentColor" style="width: 12px; height: 12px; stroke-width: 2.2; fill: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Clear</span>
                    </button>
                </div>

                <!-- Floating Fullscreen Corner Shortcut -->
                <button
                    type="button"
                    @click="openFullscreen()"
                    class="sigpad-canvas-fs-badge"
                    title="Pumirma sa Full Screen"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" style="width: 12px; height: 12px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                    <span>Full Screen</span>
                </button>

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

                <!-- Helper watermark overlay when canvas is empty -->
                <div
                    x-show="!hasSignature"
                    class="sigpad-watermark"
                >
                    <span style="font-size: 26px; margin-bottom: 4px; opacity: 0.75;">✍️</span>
                    <span style="font-size: 13px; font-weight: 600; color: #64748b;">Iguhit ang inyong pirma dito sa kahon</span>
                    <span style="font-size: 11px; color: #94a3b8; margin-top: 3px;">Pindutin ang Full Screen para sa mas malawak na pirma</span>
                </div>
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
                        <div style="font-size: 32px; margin-bottom: 6px;">📁</div>
                        <span style="font-size: 13px; font-weight: 700; color: #0f172a;">Pindutin para mag-upload ng image ng inyong pirma</span>
                        <span style="font-size: 11px; color: #64748b; margin-top: 3px;">Tumatanggap ng PNG, JPG, o SVG</span>
                    </div>
                </template>

                <template x-if="hasSignature">
                    <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 16px; margin-bottom: 8px;">
                            <img :src="state" alt="Signature Preview" style="max-height: 90px; object-fit: contain;" />
                        </div>
                        <span style="font-size: 12px; font-weight: 700; color: #10b981;">✓ Naka-upload na ang pirma</span>
                        <span style="font-size: 10.5px; color: #94a3b8; text-decoration: underline; margin-top: 3px;">Pindutin para palitan ng ibang image</span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer Guidance & Info -->
        <div class="sigpad-footer">
            <div style="display: inline-flex; align-items: center; gap: 6px;">
                <svg viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px; color: #3b82f6; fill: none;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Ang pirma ay kusang ilalagay sa opisyal na printable documents. May <b>Undo</b> button kung nagkamali sa guhit.</span>
            </div>

            <template x-if="hasSignature">
                <button
                    type="button"
                    @click="clear()"
                    style="background: none; border: none; color: #f43f5e; font-size: 11.5px; font-weight: 700; text-decoration: underline; cursor: pointer;"
                >
                    Tanggalin ang Pirma
                </button>
            </template>
        </div>

    </div>

    <!-- Fullscreen Signature Pad Modal Overlay -->
    <div
        x-cloak
        x-show="isFullscreen"
        :class="{ 'is-open': isFullscreen }"
        class="sigpad-fs-modal"
        @keydown.escape.window="closeFullscreen(false)"
    >
        <!-- Modal Top Bar -->
        <div class="sigpad-fs-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="sigpad-icon-badge">✍️</div>
                <div>
                    <h3 style="font-size: 15px; font-weight: 800; margin: 0; color: #ffffff;">
                        Electronic Signature &mdash; Full Screen Mode
                    </h3>
                    <p style="font-size: 11px; color: #94a3b8; margin: 2px 0 0 0;">
                        Pumirma nang malinaw sa buong screen. May Undo at Clear controls.
                    </p>
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="sigpad-fs-actions">
                <!-- Fullscreen Undo Button -->
                <button
                    type="button"
                    x-show="hasSignature && fsHistory.length > 0"
                    @click="undoFs()"
                    style="display: inline-flex; align-items: center; gap: 5px; padding: 7px 13px; background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.35); border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer;"
                    title="I-undo ang huling linya"
                >
                    <svg viewBox="0 0 24 24" stroke="currentColor" style="width: 13px; height: 13px; stroke-width: 2.2; fill: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                    </svg>
                    <span>Undo</span>
                </button>

                <!-- Fullscreen Clear Button -->
                <button
                    type="button"
                    x-show="hasSignature"
                    @click="clearFs()"
                    style="display: inline-flex; align-items: center; gap: 5px; padding: 7px 13px; background: rgba(244, 63, 94, 0.15); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.35); border-radius: 9px; font-size: 12px; font-weight: 700; cursor: pointer;"
                    title="Burahin ang pirma"
                >
                    <svg viewBox="0 0 24 24" stroke="currentColor" style="width: 13px; height: 13px; stroke-width: 2.2; fill: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Clear</span>
                </button>

                <!-- Done / Save E-Sign Button -->
                <button
                    type="button"
                    @click="closeFullscreen(true)"
                    style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: #10b981; color: #ffffff; border: 1px solid #10b981; border-radius: 9px; font-size: 12.5px; font-weight: 800; cursor: pointer; box-shadow: 0 2px 10px rgba(16, 185, 129, 0.4);"
                    title="I-save ang pirma at isara"
                >
                    <svg viewBox="0 0 24 24" stroke="currentColor" style="width: 14px; height: 14px; stroke-width: 2.5; fill: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Done / Save E-Sign</span>
                </button>

                <!-- Exit / Close Button -->
                <button
                    type="button"
                    @click="closeFullscreen(false)"
                    style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 9px; color: #94a3b8; font-size: 16px; font-weight: 700; cursor: pointer;"
                    title="Isara (Esc)"
                >
                    ✕
                </button>
            </div>
        </div>

        <!-- Fullscreen Canvas Box -->
        <div class="sigpad-fs-canvas-box">
            <div class="sigpad-fs-waterline">
                <span>Signature Baseline</span>
                <span>✍️ Fullscreen Mode</span>
            </div>

            <canvas
                x-ref="sigFsCanvas"
                @pointerdown="startFsDrawing($event)"
                @pointermove="drawFs($event)"
                @pointerup="stopFsDrawing($event)"
                @pointercancel="stopFsDrawing($event)"
                @pointerleave="stopFsDrawing($event)"
                class="sigpad-fs-canvas-element"
            ></canvas>

            <div
                x-show="!hasSignature"
                style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none; user-select: none; color: #94a3b8; text-align: center; padding: 20px; z-index: 5;"
            >
                <span style="font-size: 34px; opacity: 0.6; margin-bottom: 6px;">✏️</span>
                <span style="font-size: 15px; font-weight: 700; color: #475569;">Iguhit ang inyong pirma dito sa malawak na screen</span>
                <span style="font-size: 12px; color: #94a3b8; margin-top: 3px;">Pumirma gamit ang daliri, stylus, o mouse</span>
            </div>
        </div>

        <!-- Modal Footer Help Tip -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-top: 8px; padding-top: 6px; font-size: 11px; color: #94a3b8; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span>💡</span>
                <span>Gamitin ang daliri o stylus pen para pumirma sa buong screen. May <b>Undo</b> button kung nais burahin ang huling stroke.</span>
            </div>
            <div>
                <span>Pindutin ang <b>Done / Save E-Sign</b> kapag tapos na.</span>
            </div>
        </div>
    </div>
</div>
