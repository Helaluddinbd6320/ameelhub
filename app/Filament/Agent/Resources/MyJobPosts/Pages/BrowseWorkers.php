<?php

namespace App\Filament\Agent\Resources\MyJobPosts\Pages;

use App\Filament\Agent\Resources\MyJobPosts\MyJobPostsResource;
use App\Models\AgentNok;
use App\Models\Setting;
use App\Models\Worker;
use App\Services\NokService;
use Filament\Actions\BulkAction;
use Filament\Actions\Action as TableAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Validation\ValidationException;

class BrowseWorkers extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = MyJobPostsResource::class;

    protected static ?string $title = 'Worker খুঁজুন';

    protected string $view = 'filament.agent.resources.my-job-posts.pages.browse-workers';

    /**
     * Result modal দেখানো হবে কিনা।
     */
    public bool $showBulkNokResultModal = false;

    /**
     * সর্বশেষ Bulk Nok অপারেশনের ফলাফল — result modal এ দেখানোর জন্য।
     *
     * @var array<int, array{worker_id:int, worker_name:string, status:string, reason:?string}>
     */
    public array $bulkNokResults = [];

    /**
     * Step 10.8b Fix: এই Job Post + এই Agent এর সব AgentNok, worker_id দিয়ে keyed —
     * প্রতি row আলাদা query না চালিয়ে একবারই লোড করে মেমোরিতে রাখা হয় (N+1 এড়াতে)।
     */
    protected ?SupportCollection $agentNoksForJob = null;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        // নিজের পোস্ট করা Job ছাড়া এই পেজে ঢোকা যাবে না
        abort_unless($this->record->posted_by_id === auth()->id(), 403);
    }

    public function getBreadcrumb(): string
    {
        return 'Worker খুঁজুন';
    }

    public function closeBulkNokResultModal(): void
    {
        $this->showBulkNokResultModal = false;
        $this->bulkNokResults = [];
    }

    public function getBulkNokSuccessCount(): int
    {
        return collect($this->bulkNokResults)->where('status', 'success')->count();
    }

    public function getBulkNokFailedCount(): int
    {
        return collect($this->bulkNokResults)->where('status', 'failed')->count();
    }

    /**
     * এই Job Post + এই Agent এর জন্য সব AgentNok একবারে লোড করে worker_id দিয়ে key করে
     * memoize করে রাখে। টেবিলের সব row এই একই কালেকশন থেকে read করবে — কোনো অতিরিক্ত
     * DB query ছাড়াই।
     */
    protected function getAgentNoksForJob(): SupportCollection
    {
        return $this->agentNoksForJob ??= AgentNok::where('job_post_id', $this->record->id)
            ->where('agent_id', auth()->id())
            ->get()
            ->keyBy('worker_id');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Worker::query()
                    ->where('status', 'active')
                    ->when(
                        $this->record->skill_category_id,
                        fn (Builder $q) => $q->orderByRaw(
                            'skill_category_id = ? DESC',
                            [$this->record->skill_category_id]
                        )
                    )
            )
            ->columns([
                ImageColumn::make('photo')
                    ->label('ছবি')
                    ->circular()
                    ->defaultImageUrl(
                        fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name_en ?? 'Worker')
                    ),
                TextColumn::make('full_name_bn')
                    ->label('নাম')
                    ->searchable(),
                TextColumn::make('skillCategory.name_bn')
                    ->label('দক্ষতা'),
                TextColumn::make('skill_match')
                    ->label('মিল')
                    ->badge()
                    ->state(fn ($record) => $record->skill_category_id === $this->record->skill_category_id ? 'ম্যাচ' : '—')
                    ->color(fn ($record) => $record->skill_category_id === $this->record->skill_category_id ? 'success' : 'gray'),
                TextColumn::make('experience_years')
                    ->label('মোট অভিজ্ঞতা (বছর)'),
                TextColumn::make('experience_saudi_years')
                    ->label('সৌদি অভিজ্ঞতা (বছর)'),
                TextColumn::make('expected_salary_sar')
                    ->label('প্রত্যাশিত বেতন (SAR)')
                    ->money('SAR', true),
                TextColumn::make('present_location_city')
                    ->label('বর্তমান শহর')
                    ->placeholder('—'),
                // ── Step 10.8b Fix: memoized collection থেকে state/color নিচ্ছে, প্রতি row কোয়েরি না ──
                TextColumn::make('nok_status')
                    ->label('Nok স্ট্যাটাস')
                    ->badge()
                    ->state(fn ($record) => $this->getNokStatusLabel($record))
                    ->color(fn ($record) => $this->getNokStatusColor($record)),
            ])
            ->filters([
                SelectFilter::make('experience_saudi_years')
                    ->label('সৌদি অভিজ্ঞতা (কমপক্ষে)')
                    ->options([
                        1 => '১+ বছর',
                        3 => '৩+ বছর',
                        5 => '৫+ বছর',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['value'] ?? null)) {
                            $query->where('experience_saudi_years', '>=', $data['value']);
                        }
                    }),
                SelectFilter::make('visa_status')
                    ->label('ভিসা স্ট্যাটাস')
                    ->options([
                        'visit'        => 'Visit',
                        'iqama'        => 'Iqama',
                        'free_exit'    => 'Free Exit',
                        'final_exit'   => 'Final Exit',
                        'new_visa'     => 'New Visa',
                        'not_in_saudi' => 'সৌদিতে নেই',
                    ]),
            ])
            ->recordActions([
                TableAction::make('sendNok')
                    ->label('Nok পাঠান')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->schema([
                        Textarea::make('nok_message')
                            ->label('বার্তা (ঐচ্ছিক)')
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->action(function (array $data, Worker $record) {
                        try {
                            app(NokService::class)->send(
                                jobPostId: $this->record->id,
                                workerId: $record->id,
                                message: $data['nok_message'] ?? null,
                                route: 'route_a',
                            );

                            Notification::make()
                                ->title('Nok সফলভাবে পাঠানো হয়েছে')
                                ->success()
                                ->send();
                        } catch (ValidationException $e) {
                            Notification::make()
                                ->title('Nok পাঠানো যায়নি')
                                ->body(collect($e->errors())->flatten()->first())
                                ->danger()
                                ->send();
                        }
                    })
                    // ── Step 10.8b Fix: memoized collection থেকে check, নতুন query না ──
                    ->visible(fn (Worker $record) => ! $this->getAgentNoksForJob()->has($record->id)),
            ])
            ->bulkActions([
                BulkAction::make('sendBulkNok')
                    ->label(fn () => 'Bulk Nok পাঠান (সর্বোচ্চ ' . (int) Setting::get('nok_bulk_max', 5) . ' জন)')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->schema([
                        Textarea::make('nok_message')
                            ->label('বার্তা (ঐচ্ছিক, সবার জন্য একই বার্তা যাবে)')
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->deselectRecordsAfterCompletion()
                    ->action(function (Collection $records, array $data) {
                        $bulkMax = (int) Setting::get('nok_bulk_max', 5);

                        if ($records->count() > $bulkMax) {
                            Notification::make()
                                ->title('অতিরিক্ত নির্বাচন')
                                ->body("একসাথে সর্বোচ্চ {$bulkMax} জন Worker কে Nok পাঠানো যাবে। আপনি {$records->count()} জন নির্বাচন করেছেন — সংখ্যা কমিয়ে আবার চেষ্টা করুন।")
                                ->danger()
                                ->send();

                            return;
                        }

                        $results = app(NokService::class)->sendBulk(
                            jobPostId: $this->record->id,
                            workerIds: $records->pluck('id')->all(),
                            message: $data['nok_message'] ?? null,
                            route: 'route_a',
                        );

                        $this->bulkNokResults = $results;
                        $this->showBulkNokResultModal = true;
                    }),
            ]);
    }

    /**
     * Step 10.8b Fix: আগে এখানে AgentNok::where(...)->first() কল হতো (প্রতি row আলাদা query)।
     * এখন memoized getAgentNoksForJob() কালেকশন থেকে সরাসরি lookup করে।
     */
    private function getNokStatusLabel(Worker $record): string
    {
        $nok = $this->getAgentNoksForJob()->get($record->id);

        return match ($nok?->status) {
            'pending'  => 'পাঠানো হয়েছে (অপেক্ষমান)',
            'accepted' => 'গৃহীত হয়েছে',
            'rejected' => 'প্রত্যাখ্যাত',
            'expired'  => 'মেয়াদোত্তীর্ণ',
            default    => '—',
        };
    }

    private function getNokStatusColor(Worker $record): string
    {
        $nok = $this->getAgentNoksForJob()->get($record->id);

        return match ($nok?->status) {
            'pending'  => 'warning',
            'accepted' => 'success',
            'rejected' => 'danger',
            'expired'  => 'gray',
            default    => 'gray',
        };
    }
}