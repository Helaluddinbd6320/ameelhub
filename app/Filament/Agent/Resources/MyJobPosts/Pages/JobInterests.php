<?php

namespace App\Filament\Agent\Resources\MyJobPosts\Pages;

use App\Filament\Agent\Resources\MyJobPosts\MyJobPostsResource;
use App\Models\JobInterest;
use App\Services\JobSelectionService;
use Filament\Actions\Action as TableAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class JobInterests extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = MyJobPostsResource::class;

    protected static ?string $title = 'আবেদনসমূহ';

    protected string $view = 'filament.agent.resources.my-job-posts.pages.job-interests';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        // নিজের পোস্ট করা Job ছাড়া এই পেজে ঢোকা যাবে না
        abort_unless($this->record->posted_by_id === auth()->id(), 403);
    }

    public function getBreadcrumb(): string
    {
        return 'আবেদনসমূহ';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                JobInterest::query()
                    ->where('job_post_id', $this->record->id)
                    ->with(['worker', 'worker.skillCategory'])
            )
            ->columns([
                ImageColumn::make('worker.photo')
                    ->label('ছবি')
                    ->circular()
                    ->defaultImageUrl(
                        fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->worker->full_name_en ?? 'Worker')
                    ),
                TextColumn::make('worker.full_name_bn')
                    ->label('নাম')
                    ->searchable(),
                TextColumn::make('worker.skillCategory.name_bn')
                    ->label('দক্ষতা'),
                TextColumn::make('interest_source')
                    ->label('উৎস')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'worker_self'  => 'Worker নিজে আবেদন করেছে',
                        'agent_nok'    => 'Nok গ্রহণ করে',
                        'agent_select' => 'Agent সরাসরি জমা দিয়েছে',
                        default        => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'worker_self'  => 'info',
                        'agent_nok'    => 'success',
                        'agent_select' => 'warning',
                        default        => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending'  => 'অপেক্ষমান',
                        'selected' => 'Select করা হয়েছে',
                        'rejected' => 'প্রত্যাখ্যাত',
                        'hired'    => 'নিয়োগপ্রাপ্ত',
                        default    => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'pending'  => 'warning',
                        'selected' => 'success',
                        'rejected' => 'danger',
                        'hired'    => 'success',
                        default    => 'gray',
                    }),
                TextColumn::make('interested_at')
                    ->label('আবেদনের সময়')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('interest_source')
                    ->label('উৎস')
                    ->options([
                        'worker_self'  => 'Worker নিজে আবেদন করেছে',
                        'agent_nok'    => 'Nok গ্রহণ করে',
                        'agent_select' => 'Agent সরাসরি জমা দিয়েছে',
                    ]),
                SelectFilter::make('status')
                    ->label('স্ট্যাটাস')
                    ->options([
                        'pending'  => 'অপেক্ষমান',
                        'selected' => 'Select করা হয়েছে',
                        'rejected' => 'প্রত্যাখ্যাত',
                        'hired'    => 'নিয়োগপ্রাপ্ত',
                    ]),
            ])
            ->recordActions([
                TableAction::make('selectWorker')
                    ->label('Select করুন')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalDescription('এই Worker কে এই Job এর জন্য চূড়ান্ত Select করতে চান? Worker কে নোটিফাই করা হবে এবং সাড়া দেওয়ার জন্য নির্দিষ্ট সময় পাবে।')
                    ->visible(fn (JobInterest $record) => $record->status === 'pending')
                    ->action(function (JobInterest $record) {
                        try {
                            app(JobSelectionService::class)->select($record->id, auth()->user());

                            Notification::make()
                                ->title('Worker সফলভাবে Select করা হয়েছে')
                                ->body('Worker কে নোটিফাই করা হয়েছে।')
                                ->success()
                                ->send();
                        } catch (ValidationException $e) {
                            Notification::make()
                                ->title('Select করা যায়নি')
                                ->body(collect($e->errors())->flatten()->first())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }
}