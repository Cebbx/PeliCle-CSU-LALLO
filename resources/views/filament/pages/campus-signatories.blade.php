<x-filament-panels::page>
    <style>
        .signatory-container {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .signatory-header-card {
            background: linear-gradient(135deg, #0d1322 0%, #080c14 100%);
            border: 1px solid #1a2438;
            border-radius: 12px;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
        }

        .signatory-header-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .signatory-icon-badge {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #60a5fa;
            font-size: 22px;
            flex-shrink: 0;
        }

        .signatory-title {
            font-size: 18px;
            font-weight: 800;
            color: #ffffff;
            margin: 0;
            letter-spacing: -0.01em;
        }

        .signatory-subtitle {
            font-size: 12.5px;
            color: #94a3b8;
            margin-top: 3px;
        }

        .rbac-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
        }

        .signatory-card {
            background: #0b101b;
            border: 1px solid #1e293b;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .card-header-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 14px;
            margin-bottom: 16px;
            border-bottom: 1px solid #1e293b;
        }

        .card-header-bar h3 {
            font-size: 15px;
            font-weight: 700;
            color: #f1f5f9;
            margin: 0;
        }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .custom-input {
            width: 100%;
            background: #111827;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 10px 14px;
            color: #ffffff;
            font-size: 13.5px;
            transition: all 0.2s ease;
        }

        .custom-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .preview-box {
            background: #ffffff;
            color: #0f172a;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .save-btn {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            padding: 12px 28px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
            transition: all 0.2s ease;
        }

        .save-btn:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.45);
        }

        .save-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>

    <div class="signatory-container">
        <!-- 1. Header Banner -->
        <div class="signatory-header-card">
            <div class="signatory-header-left">
                <div class="signatory-icon-badge">
                    🖋️
                </div>
                <div>
                    <h2 class="signatory-title">Campus Administration & Document Signatories</h2>
                    <p class="signatory-subtitle">
                        Centralized management for official signatories across all Trip Tickets, Travel Orders, Withdrawal Slips, and Analytics Reports.
                    </p>
                </div>
            </div>
            <div>
                <span class="rbac-tag">
                    🛡️ RBAC Protected • Admin Only
                </span>
            </div>
        </div>

        <form wire:submit.prevent="save">
            <!-- 2. Grid of Signatories -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <!-- General Services Officer (GSO) -->
                <div class="signatory-card">
                    <div class="card-header-bar">
                        <span class="text-xl">🛠️</span>
                        <div>
                            <h3>General Services Officer</h3>
                            <p class="text-[11px] text-gray-400">Recommending / Prepared By</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="field-label">GSO Officer Name</label>
                            <input 
                                type="text" 
                                wire:model.defer="gso_name" 
                                class="custom-input"
                                placeholder="e.g. JOEL A. TUMAMAO" 
                                required
                            />
                            @error('gso_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="field-label">Official Designation / Position</label>
                            <input 
                                type="text" 
                                wire:model.defer="gso_title" 
                                class="custom-input"
                                placeholder="General Services Officer" 
                                required
                            />
                            @error('gso_title') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-2 text-[11px] text-amber-400 bg-amber-950/40 p-2.5 rounded border border-amber-900/60">
                            💡 Signs the <strong>Prepared By</strong> &amp; <strong>Recommending Approval</strong> on vehicle dispatches.
                        </div>
                    </div>
                </div>

                <!-- Campus Executive Officer (CEO) -->
                <div class="signatory-card">
                    <div class="card-header-bar">
                        <span class="text-xl">🏛️</span>
                        <div>
                            <h3>Campus Executive Officer</h3>
                            <p class="text-[11px] text-gray-400">Approving Authority (CEO)</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="field-label">CEO Full Name & Academic Titles</label>
                            <input 
                                type="text" 
                                wire:model.defer="ceo_name" 
                                class="custom-input"
                                placeholder="e.g. ENGR. JAMES B. CABILDO, PHD" 
                                required
                            />
                            @error('ceo_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="field-label">Official Designation / Position</label>
                            <input 
                                type="text" 
                                wire:model.defer="ceo_title" 
                                class="custom-input"
                                placeholder="Campus Executive Officer" 
                                required
                            />
                            @error('ceo_title') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="pt-2 text-[11px] text-blue-400 bg-blue-950/40 p-2.5 rounded border border-blue-900/60">
                            💡 Signs the <strong>Approved By</strong> box on Trip Tickets, Travel Orders, and Fuel Slips.
                        </div>
                    </div>
                </div>

            </div>

            <!-- 3. Real-Time Document Preview Box -->
            <div class="signatory-card mt-5">
                <div class="card-header-bar">
                    <span class="text-xl">📄</span>
                    <div>
                        <h3>Official Print Document Preview</h3>
                        <p class="text-[11px] text-gray-400">How the signature block will appear on generated CSU Lal-lo documents</p>
                    </div>
                </div>

                <div class="preview-box">
                    <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-3 pb-2 border-b border-gray-200">
                        Cagayan State University Lal-lo Campus • Official Document Sign-Off
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                        <!-- GSO Box -->
                        <div class="text-left border-l-2 border-amber-500 pl-3">
                            <span class="text-[10px] text-gray-500 font-semibold block uppercase">Prepared By / Recommending:</span>
                            <span class="text-[13px] font-extrabold text-gray-900 uppercase block mt-1">
                                {{ $gso_name ?: 'JOEL A. TUMAMAO' }}
                            </span>
                            <span class="text-[11px] text-gray-600 font-medium block">
                                {{ $gso_title ?: 'General Services Officer' }}
                            </span>
                        </div>

                        <!-- CEO Box -->
                        <div class="text-left border-l-2 border-blue-600 pl-3">
                            <span class="text-[10px] text-gray-500 font-semibold block uppercase">Approved By:</span>
                            <span class="text-[13px] font-extrabold text-gray-900 uppercase block mt-1">
                                {{ $ceo_name ?: 'ENGR. JAMES B. CABILDO, PHD, ASEAN ENGR.' }}
                            </span>
                            <span class="text-[11px] text-gray-600 font-medium block">
                                {{ $ceo_title ?: 'Campus Executive Officer' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Save Changes Button -->
            <div class="flex items-center justify-between pt-4 mt-2">
                <span class="text-xs text-gray-400">
                    💡 Only users with the <strong>Admin</strong> role can view and submit this page.
                </span>
                <button type="submit" class="save-btn" wire:loading.attr="disabled">
                    <span wire:loading.remove>💾 Save &amp; Update Signatories</span>
                    <span wire:loading>⏳ Updating Signatories...</span>
                </button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
