<?php

namespace App\Filament\Agent\Resources\MyJobPosts\Tables;

use App\Models\JobPost;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

use App\Filament\Agent\Resources\MyJobPosts\Pages\BrowseWorkers;
use App\Filament\Agent\Resources\MyJobPosts\Pages\JobInterests;


class MyJobPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ── Step 10.8b Fix: N+1 এড়াতে pending interest count এক কোয়েরিতে লোড ──
            ->modifyQueryUsing(fn ($query) => $query
                ->withCount([
                    'interests as pending_interests_count' => fn ($q) => $q->where('status', 'pending'),
                ])
            )
            ->columns([

                TextColumn::make('job_title')
                    ->label('জব টাইটেল')
                    ->searchable()
                    ->description(fn (JobPost $r) => $r->employer_name),

                TextColumn::make('vacancies')
                    ->label('ভ্যাকেন্সি')
                    ->formatStateUsing(fn (JobPost $r) => "{$r->filled_count} / {$r->vacancies}"),

                TextColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'    => 'gray',
                        'pending'  => 'warning',
                        'active'   => 'success',
                        'paused'   => 'info',
                        'filled'   => 'primary',
                        'closed'   => 'gray',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('close_reason')
                    ->label('বাতিল/বন্ধের কারণ')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('expires_at')
                    ->label('মেয়াদ শেষ')
                    ->date('d M Y'),

                TextColumn::make('created_at')
                    ->label('তৈরির তারিখ')
                    ->dateTime('d M Y')
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('স্ট্যাটাস')
                    ->options([
                        'draft'    => 'Draft',
                        'pending'  => 'Pending',
                        'active'   => 'Active',
                        'paused'   => 'Paused',
                        'closed'   => 'Closed',
                        'filled'   => 'Filled',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('browseWorkers')
                    ->label('Worker খুঁজুন')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('info')
                    ->url(fn ($record) => BrowseWorkers::getUrl(['record' => $record]))
                    ->visible(fn ($record) => in_array($record->status, ['active', 'paused'], true)),

                // ── Step 10.8b Fix: withCount() থেকে আসা কলাম ব্যবহার, নতুন query চালাচ্ছে না ──
                Action::make('viewInterests')
                    ->label('আবেদনসমূহ')
                    ->icon('heroicon-o-inbox-stack')
                    ->color('warning')
                    ->url(fn ($record) => JobInterests::getUrl(['record' => $record]))
                    ->badge(fn (JobPost $r) => $r->pending_interests_count ?: null),

                ViewAction::make(),

                EditAction::make()
                    ->visible(fn (JobPost $r) => in_array($r->status, ['draft', 'pending', 'paused'], true)),

                // ── SUBMIT FOR APPROVAL ──
                Action::make('submit')
                    ->label('অনুমোদনের জন্য পাঠান')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->visible(fn (JobPost $r) => $r->status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('জব পোস্ট জমা দিন')
                    ->modalDescription('এই জব পোস্ট Admin এর কাছে অনুমোদনের জন্য পাঠানো হবে।')
                    ->modalSubmitActionLabel('হ্যাঁ, পাঠান')
                    ->action(function (JobPost $r) {
                        $r->status = 'pending';
                        $r->save();

                        app(NotificationService::class)->jobPosted($r->fresh());

                        Notification::make()
                            ->title('জব পোস্ট জমা দেওয়া হয়েছে')
                            ->body('Admin শীঘ্রই রিভিউ করবে।')
                            ->success()
                            ->send();
                    }),

                // ── PAUSE ──
                Action::make('pause')
                    ->label('Pause')
                    ->icon('heroicon-o-pause-circle')
                    ->color('gray')
                    ->visible(fn (JobPost $r) => $r->status === 'active')
                    ->requiresConfirmation()
                    ->action(fn (JobPost $r) => $r->forceFill(['status' => 'paused'])->save()),

                // ── RESUME ──
                Action::make('resume')
                    ->label('Resume')
                    ->icon('heroicon-o-play-circle')
                    ->color('success')
                    ->visible(fn (JobPost $r) => $r->status === 'paused')
                    ->action(fn (JobPost $r) => $r->forceFill(['status' => 'active'])->save()),

                DeleteAction::make()
                    ->visible(fn (JobPost $r) => in_array($r->status, ['draft', 'rejected'], true)),

            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('কোনো জব পোস্ট নেই')
            ->emptyStateDescription('নতুন জব পোস্ট করতে উপরের বোতামে ক্লিক করুন।')
            ->emptyStateIcon('heroicon-o-briefcase');
    }
}