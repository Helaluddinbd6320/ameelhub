<x-filament-panels::page>
    {{-- Balance Cards --}}
    <div class="grid gap-4 sm:grid-cols-3 mb-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
            <p class="text-xs font-medium text-gray-500 mb-1">{{ __('messages.wallet.withdrawable_balance') }}</p>
            <p class="text-2xl font-bold text-success-600">
                {{ number_format($this->balances['available'], 2) }} SAR
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
            <p class="text-xs font-medium text-gray-500 mb-1">{{ __('messages.wallet.held_balance_escrow') }}</p>
            <p class="text-2xl font-bold text-warning-600">
                {{ number_format($this->balances['held'], 2) }} SAR
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-5">
            <p class="text-xs font-medium text-gray-500 mb-1">{{ __('messages.wallet.total_balance') }}</p>
            <p class="text-2xl font-bold text-gray-950 dark:text-white">
                {{ number_format($this->balances['total'], 2) }} SAR
            </p>
        </div>
    </div>

    {{-- Action Buttons + Helper Text --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
        <div class="flex flex-wrap gap-2">
            <x-filament::button color="success" icon="heroicon-o-arrow-down-tray" wire:click="openRechargeModal">
                {{ __('messages.wallet.send_recharge_request') }}
            </x-filament::button>
            <x-filament::button color="primary" icon="heroicon-o-arrow-up-tray" wire:click="openWithdrawModal">
                {{ __('messages.wallet.send_withdrawal_request') }}
            </x-filament::button>
        </div>
    </div>
    <p class="text-xs text-gray-500 mb-6">
        {{ __('messages.wallet.min_recharge') }}: {{ number_format($this->minRecharge, 2) }} SAR &middot;
        {{ __('messages.wallet.daily_recharge_limit') }}: {{ $this->todayRechargeCount }}/{{ $this->rechargeDailyLimit }} &middot;
        {{ __('messages.wallet.min_withdrawal') }}: {{ number_format($this->minWithdrawal, 2) }} SAR &middot;
        {{ __('messages.wallet.daily_withdrawal_limit') }}: {{ $this->todayWithdrawalCount }}/{{ $this->dailyLimit }} {{ __('messages.wallet.used') }}
    </p>

    {{-- Summary Cards --}}
    <div class="grid gap-4 sm:grid-cols-3 mb-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">{{ __('messages.wallet.total_credit') }}</p>
            <p class="text-lg font-semibold text-success-600">
                +{{ number_format($this->summary['total_credit'], 2) }} SAR
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">{{ __('messages.wallet.total_debit') }}</p>
            <p class="text-lg font-semibold text-danger-600">
                -{{ number_format($this->summary['total_debit'], 2) }} SAR
            </p>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <p class="text-xs font-medium text-gray-500 mb-1">{{ __('messages.wallet.net_change') }}</p>
            <p class="text-lg font-semibold {{ $this->summary['net'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                {{ $this->summary['net'] >= 0 ? '+' : '' }}{{ number_format($this->summary['net'], 2) }} SAR
            </p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-gray-950 dark:text-white">{{ __('messages.wallet.transaction_list') }}</h4>
        <select wire:model.live="filterType" class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-1.5">
            <option value="">{{ __('messages.wallet.all_types') }}</option>
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
                    <th class="px-4 py-3">{{ __('messages.wallet.type') }}</th>
                    <th class="px-4 py-3">{{ __('messages.wallet.amount') }}</th>
                    <th class="px-4 py-3">{{ __('messages.wallet.direction') }}</th>
                    <th class="px-4 py-3">{{ __('messages.wallet.date') }}</th>
                    <th class="px-4 py-3">{{ __('messages.wallet.description') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($this->transactions as $tx)
                    <tr>
                        <td class="px-4 py-3">{{ static::transactionTypeLabel($tx->type) }}</td>
                        <td class="px-4 py-3 font-medium">{{ number_format($tx->amount, 2) }} SAR</td>
                        <td class="px-4 py-3">
                            <x-filament::badge :color="static::transactionDirectionColor($tx->direction)" size="sm">
                                {{ $tx->direction === 'credit' ? __('messages.wallet.credit') : __('messages.wallet.debit') }}
                            </x-filament::badge>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $tx->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            {{ __('messages.wallet.no_transactions') }}
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
                                {{ __('messages.wallet.recharge_modal_title') }}
                            </h4>
                            <p class="text-xs text-gray-500">{{ __('messages.wallet.recharge_modal_subtitle') }}</p>
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
                            {{ __('messages.wallet.amount_sar') }}
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
                            {{ __('messages.wallet.minimum') }} {{ number_format($this->minRecharge, 2) }} SAR
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
                            {{ __('messages.wallet.payment_method_used') }}
                        </label>
                        <select
                            wire:model="rechargePaymentMethod"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-2.5 focus:border-success-500 focus:ring-success-500 transition
                                @error('rechargePaymentMethod') border-danger-400 focus:border-danger-500 focus:ring-danger-500 @enderror"
                        >
                            <option value="">{{ __('messages.wallet.select') }}</option>
                            <option value="bank">{{ __('messages.wallet.bank') }}</option>
                            <option value="bkash">{{ __('messages.wallet.bkash') }}</option>
                            <option value="nagad">{{ __('messages.wallet.nagad') }}</option>
                            <option value="stcpay">{{ __('messages.wallet.stcpay') }}</option>
                            <option value="cash">{{ __('messages.wallet.cash') }}</option>
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
                            {{ __('messages.wallet.reference_number') }}
                            <span class="text-gray-400 font-normal">{{ __('messages.wallet.optional_if_screenshot') }}</span>
                        </label>
                        <input
                            type="text"
                            wire:model="referenceNumber"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-2.5 focus:border-success-500 focus:ring-success-500 transition
                                @error('referenceNumber') border-danger-400 focus:border-danger-500 focus:ring-danger-500 @enderror"
                            placeholder="{{ __('messages.wallet.reference_placeholder') }}"
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
                            {{ __('messages.wallet.payment_proof') }}
                            <span class="text-gray-400 font-normal">{{ __('messages.wallet.optional_if_reference') }}</span>
                        </label>
                        <input
                            type="file"
                            wire:model="proofFile"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-2 file:mr-3 file:rounded-lg file:border-0 file:bg-success-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-success-700 dark:file:bg-success-500/10 dark:file:text-success-400
                                @error('proofFile') border-danger-400 @enderror"
                        />
                        <div wire:loading wire:target="proofFile" class="text-xs text-gray-400 mt-1.5">
                            {{ __('messages.wallet.uploading') }}
                        </div>
                        @if ($proofFile)
                            <p class="flex items-center gap-1 text-xs text-success-600 mt-1.5">
                                @svg('heroicon-o-check-circle', 'h-3.5 w-3.5 shrink-0')
                                {{ $proofFile->getClientOriginalName() }}
                            </p>
                        @endif
                        <p class="text-xs text-gray-500 mt-1.5">{{ __('messages.wallet.file_format_hint') }}</p>
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
                            {!! __('messages.wallet.recharge_info_note', ['used' => '<span class="font-medium text-gray-700 dark:text-gray-300">' . $this->todayRechargeCount . '/' . $this->rechargeDailyLimit . '</span>']) !!}
                        </p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 px-6 py-4 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800">
                    <x-filament::button color="gray" wire:click="closeRechargeModal">
                        {{ __('messages.wallet.cancel') }}
                    </x-filament::button>
                    <x-filament::button
                        color="success"
                        wire:click="submitRecharge"
                        wire:loading.attr="disabled"
                        wire:target="submitRecharge,proofFile"
                    >
                        <span wire:loading.remove wire:target="submitRecharge">{{ __('messages.wallet.send_request') }}</span>
                        <span wire:loading wire:target="submitRecharge">{{ __('messages.wallet.sending') }}</span>
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
                                {{ __('messages.wallet.withdraw_modal_title') }}
                            </h4>
                            <p class="text-xs text-gray-500">{{ __('messages.wallet.withdraw_modal_subtitle') }}</p>
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
                            {{ __('messages.wallet.amount_sar') }}
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
                            {{ __('messages.wallet.minimum') }} {{ number_format($this->minWithdrawal, 2) }} SAR &middot;
                            {{ __('messages.wallet.maximum') }} {{ number_format($this->balances['available'], 2) }} SAR
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
                            {{ __('messages.wallet.method') }}
                        </label>
                        <select
                            wire:model="paymentMethod"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500 transition
                                @error('paymentMethod') border-danger-400 focus:border-danger-500 focus:ring-danger-500 @enderror"
                        >
                            <option value="">{{ __('messages.wallet.select') }}</option>
                            <option value="bank">{{ __('messages.wallet.bank') }}</option>
                            <option value="bkash">{{ __('messages.wallet.bkash') }}</option>
                            <option value="nagad">{{ __('messages.wallet.nagad') }}</option>
                            <option value="stcpay">{{ __('messages.wallet.stcpay') }}</option>
                            <option value="cash">{{ __('messages.wallet.cash') }}</option>
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
                            {{ __('messages.wallet.account_number_details') }}
                        </label>
                        <input
                            type="text"
                            wire:model="accountDetails"
                            class="w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm py-2.5 focus:border-primary-500 focus:ring-primary-500 transition
                                @error('accountDetails') border-danger-400 focus:border-danger-500 focus:ring-danger-500 @enderror"
                            placeholder="{{ __('messages.wallet.account_placeholder') }}"
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
                            {!! __('messages.wallet.withdraw_info_note', ['used' => '<span class="font-medium text-gray-700 dark:text-gray-300">' . $this->todayWithdrawalCount . '/' . $this->dailyLimit . '</span>']) !!}
                        </p>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 px-6 py-4 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800">
                    <x-filament::button color="gray" wire:click="closeWithdrawModal">
                        {{ __('messages.wallet.cancel') }}
                    </x-filament::button>
                    <x-filament::button
                        color="primary"
                        wire:click="submitWithdrawal"
                        wire:loading.attr="disabled"
                        wire:target="submitWithdrawal"
                    >
                        <span wire:loading.remove wire:target="submitWithdrawal">{{ __('messages.wallet.send_request') }}</span>
                        <span wire:loading wire:target="submitWithdrawal">{{ __('messages.wallet.sending') }}</span>
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

</x-filament-panels::page>