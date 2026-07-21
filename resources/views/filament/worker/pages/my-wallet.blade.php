<x-filament-panels::page>
    {{-- Balance Cards --}}
    <div class="grid gap-4 sm:grid-cols-3 mb-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
            <p class="text-xs font-medium text-gray-500 mb-1">উত্তোলনযোগ্য ব্যালেন্স</p>
            <p class="text-2xl font-bold text-success-600">
                {{ number_format($this->balances['available'], 2) }} SAR
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
            <p class="text-xs font-medium text-gray-500 mb-1">হোল্ড ব্যালেন্স (এসক্রো)</p>
            <p class="text-2xl font-bold text-warning-600">
                {{ number_format($this->balances['held'], 2) }} SAR
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
            <p class="text-xs font-medium text-gray-500 mb-1">মোট ব্যালেন্স</p>
            <p class="text-2xl font-bold text-gray-950 dark:text-white">
                {{ number_format($this->balances['total'], 2) }} SAR
            </p>
        </div>
    </div>

    {{-- Action Buttons + Helper Text --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
        <div class="flex flex-wrap gap-2">
            <x-filament::button color="success" icon="heroicon-o-arrow-down-tray" wire:click="openRechargeModal">
                Recharge Request পাঠান
            </x-filament::button>
            <x-filament::button color="primary" icon="heroicon-o-arrow-up-tray" wire:click="openWithdrawModal">
                Withdrawal Request পাঠান
            </x-filament::button>
        </div>
    </div>
    <p class="text-xs text-gray-500 mb-6">
        সর্বনিম্ন রিচার্জ: {{ number_format($this->minRecharge, 2) }} SAR &middot;
        দৈনিক রিচার্জ সীমা: {{ $this->todayRechargeCount }}/{{ $this->rechargeDailyLimit }} &middot;
        সর্বনিম্ন উত্তোলন: {{ number_format($this->minWithdrawal, 2) }} SAR &middot;
        দৈনিক উত্তোলন সীমা: {{ $this->todayWithdrawalCount }}/{{ $this->dailyLimit }} ব্যবহার হয়েছে
    </p>

    {{-- Summary Cards --}}
    <div class="grid gap-4 sm:grid-cols-3 mb-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">মোট জমা</p>
            <p class="text-lg font-semibold text-success-600">
                +{{ number_format($this->summary['total_credit'], 2) }} SAR
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">মোট কর্তন</p>
            <p class="text-lg font-semibold text-danger-600">
                -{{ number_format($this->summary['total_debit'], 2) }} SAR
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">নীট পরিবর্তন</p>
            <p class="text-lg font-semibold {{ $this->summary['net'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                {{ $this->summary['net'] >= 0 ? '+' : '' }}{{ number_format($this->summary['net'], 2) }} SAR
            </p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-gray-950 dark:text-white">লেনদেনের তালিকা</h4>
        <select wire:model.live="filterType" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-1.5">
            <option value="">সব ধরন</option>
            @foreach($this->availableTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Transaction Table --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr class="text-left text-xs font-medium text-gray-500">
                    <th class="px-4 py-3">ধরন</th>
                    <th class="px-4 py-3">পরিমাণ</th>
                    <th class="px-4 py-3">দিক</th>
                    <th class="px-4 py-3">তারিখ</th>
                    <th class="px-4 py-3">বিবরণ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($this->transactions as $tx)
                    <tr>
                        <td class="px-4 py-3">{{ static::transactionTypeLabel($tx->type) }}</td>
                        <td class="px-4 py-3 font-medium">{{ number_format($tx->amount, 2) }} SAR</td>
                        <td class="px-4 py-3">
                            <x-filament::badge :color="static::transactionDirectionColor($tx->direction)" size="sm">
                                {{ $tx->direction === 'credit' ? 'জমা' : 'কর্তন' }}
                            </x-filament::badge>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $tx->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            কোনো লেনদেন পাওয়া যায়নি।
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->transactions->links() }}
    </div>

    {{-- Recharge Modal --}}
    @if($isRecharging)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 backdrop-blur-sm p-4"
            wire:key="recharge-modal"
        >
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 shadow-2xl ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10">
                            @svg('heroicon-o-arrow-down-tray', 'h-5 w-5 text-success-600 dark:text-success-400')
                        </div>
                        <div>
                            <h4 class="text-base font-semibold text-gray-950 dark:text-white">
                                Recharge Request
                            </h4>
                            <p class="text-xs text-gray-500">টাকা পাঠানোর পর নিচের তথ্য দিন</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeRechargeModal"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition"
                    >
                        @svg('heroicon-o-x-mark', 'h-5 w-5')
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5 space-y-5">

                    {{-- Amount --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-950 dark:text-white mb-1.5">
                            পরিমাণ (SAR)
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                step="0.01"
                                wire:model="rechargeAmount"
                                class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-2.5 pr-14 focus:border-success-500 focus:ring-success-500 transition
                                    @error('rechargeAmount') border-danger-400 focus:border-danger-500 focus:ring-danger-500 @enderror"
                                placeholder="0.00"
                            />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-400">SAR</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5">
                            সর্বনিম্ন {{ number_format($this->minRecharge, 2) }} SAR
                        </p>
                        @error('rechargeAmount')
                            <p class="flex items-center gap-1 text-xs text-danger-600 mt-1.5">
                                @svg('heroicon-o-exclamation-circle', 'h-3.5 w-3.5 shrink-0')
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Payment Method --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-950 dark:text-white mb-1.5">
                            যে মাধ্যমে টাকা পাঠিয়েছেন
                        </label>
                        <select
                            wire:model="rechargePaymentMethod"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-2.5 focus:border-success-500 focus:ring-success-500 transition
                                @error('rechargePaymentMethod') border-danger-400 focus:border-danger-500 focus:ring-danger-500 @enderror"
                        >
                            <option value="">নির্বাচন করুন</option>
                            <option value="bank">ব্যাংক</option>
                            <option value="bkash">বিকাশ</option>
                            <option value="nagad">নগদ</option>
                            <option value="stcpay">STC Pay</option>
                            <option value="cash">ক্যাশ</option>
                        </select>
                        @error('rechargePaymentMethod')
                            <p class="flex items-center gap-1 text-xs text-danger-600 mt-1.5">
                                @svg('heroicon-o-exclamation-circle', 'h-3.5 w-3.5 shrink-0')
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Reference Number --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-950 dark:text-white mb-1.5">
                            রেফারেন্স / ট্রানজেকশন নম্বর
                            <span class="text-gray-400 font-normal">(ঐচ্ছিক যদি স্ক্রিনশট দেন)</span>
                        </label>
                        <input
                            type="text"
                            wire:model="referenceNumber"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-2.5 focus:border-success-500 focus:ring-success-500 transition
                                @error('referenceNumber') border-danger-400 focus:border-danger-500 focus:ring-danger-500 @enderror"
                            placeholder="যেমন: bKash TrxID 9XY7Z2AB1C"
                        />
                        @error('referenceNumber')
                            <p class="flex items-center gap-1 text-xs text-danger-600 mt-1.5">
                                @svg('heroicon-o-exclamation-circle', 'h-3.5 w-3.5 shrink-0')
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Proof File Upload --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-950 dark:text-white mb-1.5">
                            পেমেন্টের প্রুফ (স্ক্রিনশট)
                            <span class="text-gray-400 font-normal">(ঐচ্ছিক যদি রেফারেন্স নম্বর দেন)</span>
                        </label>
                        <input
                            type="file"
                            wire:model="proofFile"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-2 file:mr-3 file:rounded-lg file:border-0 file:bg-success-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-success-700 dark:file:bg-success-500/10 dark:file:text-success-400
                                @error('proofFile') border-danger-400 @enderror"
                        />
                        <div wire:loading wire:target="proofFile" class="text-xs text-gray-400 mt-1.5">
                            আপলোড হচ্ছে...
                        </div>
                        @if ($proofFile)
                            <p class="flex items-center gap-1 text-xs text-success-600 mt-1.5">
                                @svg('heroicon-o-check-circle', 'h-3.5 w-3.5 shrink-0')
                                {{ $proofFile->getClientOriginalName() }}
                            </p>
                        @endif
                        <p class="text-xs text-gray-500 mt-1.5">JPG, PNG অথবা PDF, সর্বোচ্চ 5MB</p>
                        @error('proofFile')
                            <p class="flex items-center gap-1 text-xs text-danger-600 mt-1.5">
                                @svg('heroicon-o-exclamation-circle', 'h-3.5 w-3.5 shrink-0')
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Info Box --}}
                    <div class="flex items-start gap-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 px-4 py-3">
                        @svg('heroicon-o-information-circle', 'h-4 w-4 text-gray-400 mt-0.5 shrink-0')
                        <p class="text-xs text-gray-500 leading-relaxed">
                            দৈনিক সীমা: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $this->todayRechargeCount }}/{{ $this->rechargeDailyLimit }}</span> ব্যবহার হয়েছে।
                            Admin যাচাই করে অনুমোদন দিলে টাকা আপনার Wallet এ যোগ হবে।
                        </p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 px-6 py-4 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800">
                    <x-filament::button color="gray" wire:click="closeRechargeModal">
                        বাতিল
                    </x-filament::button>
                    <x-filament::button
                        color="success"
                        wire:click="submitRecharge"
                        wire:loading.attr="disabled"
                        wire:target="submitRecharge,proofFile"
                    >
                        <span wire:loading.remove wire:target="submitRecharge">অনুরোধ পাঠান</span>
                        <span wire:loading wire:target="submitRecharge">পাঠানো হচ্ছে...</span>
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    {{-- Withdrawal Modal --}}
    @if($isWithdrawing)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/60 backdrop-blur-sm p-4"
            wire:key="withdraw-modal"
        >
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-gray-900 shadow-2xl ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-50 dark:bg-primary-500/10">
                            @svg('heroicon-o-arrow-up-tray', 'h-5 w-5 text-primary-600 dark:text-primary-400')
                        </div>
                        <div>
                            <h4 class="text-base font-semibold text-gray-950 dark:text-white">
                                Withdrawal Request
                            </h4>
                            <p class="text-xs text-gray-500">টাকা উত্তোলনের জন্য নিচের তথ্য দিন</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeWithdrawModal"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition"
                    >
                        @svg('heroicon-o-x-mark', 'h-5 w-5')
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5 space-y-5">

                    {{-- Amount --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-950 dark:text-white mb-1.5">
                            পরিমাণ (SAR)
                        </label>
                        <div class="relative">
                            <input
                                type="number"
                                step="0.01"
                                wire:model="amount"
                                class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-2.5 pr-14 focus:border-primary-500 focus:ring-primary-500 transition
                                    @error('amount') border-danger-400 focus:border-danger-500 focus:ring-danger-500 @enderror"
                                placeholder="0.00"
                            />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-medium text-gray-400">SAR</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5">
                            সর্বনিম্ন {{ number_format($this->minWithdrawal, 2) }} SAR &middot;
                            সর্বোচ্চ {{ number_format($this->balances['available'], 2) }} SAR
                        </p>
                        @error('amount')
                            <p class="flex items-center gap-1 text-xs text-danger-600 mt-1.5">
                                @svg('heroicon-o-exclamation-circle', 'h-3.5 w-3.5 shrink-0')
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Payment Method --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-950 dark:text-white mb-1.5">
                            মাধ্যম
                        </label>
                        <select
                            wire:model="paymentMethod"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500 transition
                                @error('paymentMethod') border-danger-400 focus:border-danger-500 focus:ring-danger-500 @enderror"
                        >
                            <option value="">নির্বাচন করুন</option>
                            <option value="bank">ব্যাংক</option>
                            <option value="bkash">বিকাশ</option>
                            <option value="nagad">নগদ</option>
                            <option value="stcpay">STC Pay</option>
                            <option value="cash">ক্যাশ</option>
                        </select>
                        @error('paymentMethod')
                            <p class="flex items-center gap-1 text-xs text-danger-600 mt-1.5">
                                @svg('heroicon-o-exclamation-circle', 'h-3.5 w-3.5 shrink-0')
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Account Details --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-950 dark:text-white mb-1.5">
                            অ্যাকাউন্ট নম্বর / বিবরণ
                        </label>
                        <input
                            type="text"
                            wire:model="accountDetails"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500 transition
                                @error('accountDetails') border-danger-400 focus:border-danger-500 focus:ring-danger-500 @enderror"
                            placeholder="যেমন: 01700000000"
                        />
                        @error('accountDetails')
                            <p class="flex items-center gap-1 text-xs text-danger-600 mt-1.5">
                                @svg('heroicon-o-exclamation-circle', 'h-3.5 w-3.5 shrink-0')
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Info Box --}}
                    <div class="flex items-start gap-2 rounded-xl bg-gray-50 dark:bg-gray-800/60 px-4 py-3">
                        @svg('heroicon-o-information-circle', 'h-4 w-4 text-gray-400 mt-0.5 shrink-0')
                        <p class="text-xs text-gray-500 leading-relaxed">
                            দৈনিক সীমা: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $this->todayWithdrawalCount }}/{{ $this->dailyLimit }}</span> ব্যবহার হয়েছে।
                            সক্রিয় বিরোধ (dispute) থাকলে withdrawal সম্ভব নয়।
                        </p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 px-6 py-4 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800">
                    <x-filament::button color="gray" wire:click="closeWithdrawModal">
                        বাতিল
                    </x-filament::button>
                    <x-filament::button
                        color="primary"
                        wire:click="submitWithdrawal"
                        wire:loading.attr="disabled"
                        wire:target="submitWithdrawal"
                    >
                        <span wire:loading.remove wire:target="submitWithdrawal">অনুরোধ পাঠান</span>
                        <span wire:loading wire:target="submitWithdrawal">পাঠানো হচ্ছে...</span>
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

</x-filament-panels::page>