<?php

namespace App\Livewire;

use App\Exceptions\WalletException;
use App\Models\Worker;
use App\Services\ContactRevealService;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ContactRevealBox extends Component
{
    public Worker $worker;

    /** @var array<string, string|null> phone_type => revealed value (null হলে masked দেখাবে) */
    public array $revealed = [];

    public ?string $errorMessage = null;
    public ?string $processingType = null;

    protected array $labels = [
        'primary'  => 'প্রাইমারি ফোন',
        'whatsapp' => 'হোয়াটসঅ্যাপ নম্বর',
        'saudi'    => 'সৌদি নম্বর',
    ];

    public function mount(Worker $worker): void
    {
        $this->worker = $worker;
        $this->loadRevealedState();
    }

    protected function loadRevealedState(): void
    {
        if (!auth()->check()) {
            return;
        }

        $service = app(ContactRevealService::class);

        foreach (array_keys($this->labels) as $type) {
            $this->revealed[$type] = $service->getRevealedPhone($this->worker, $type, auth()->user());
        }
    }

    public function reveal(string $phoneType): void
    {
        $this->errorMessage = null;

        if (!auth()->check()) {
            $this->redirectRoute('login');
            return;
        }

        $this->processingType = $phoneType;

        try {
            app(ContactRevealService::class)->reveal($this->worker, $phoneType, auth()->user());
            $this->loadRevealedState();
        } catch (WalletException $e) {
            $this->errorMessage = 'আপনার Wallet এ পর্যাপ্ত ব্যালেন্স নেই। অনুগ্রহ করে রিচার্জ করুন।';
        } catch (ValidationException $e) {
            $this->errorMessage = collect($e->errors())->flatten()->first();
        } finally {
            $this->processingType = null;
        }
    }

    public function getAvailableTypesProperty(): array
    {
        $types = [];
        foreach ($this->labels as $type => $label) {
            $value = match ($type) {
                'primary'  => $this->worker->phone_primary,
                'whatsapp' => $this->worker->phone_whatsapp,
                'saudi'    => $this->worker->phone_saudi,
            };

            if (filled($value)) {
                $types[$type] = $label;
            }
        }

        return $types;
    }

    public function render()
    {
        return view('livewire.contact-reveal-box');
    }
}