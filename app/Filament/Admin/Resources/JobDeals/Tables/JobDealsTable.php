<?php

namespace App\Filament\Admin\Resources\JobDeals\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobDealsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // ── Step 10.8b Fix: N+1 এড়াতে relations + milestone count এক কোয়েরিতে লোড ──
            ->modifyQueryUsing(fn ($query) => $query
                ->with(['worker', 'agent', 'jobPost'])
                ->withCount([
                    'milestones as released_milestones_count' => fn ($q) => $q->where('status', 'admin_released'),
                ])
            )
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                ImageColumn::make('worker.photo')
                    ->label('ছবি')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->worker->full_name_en ?? 'Worker')),

                TextColumn::make('worker.full_name_bn')
                    ->label('Worker')
                    ->description(fn ($record) => $record->worker->full_name_en ?? null)
                    ->searchable()
                    ->url(fn ($record) => $record->worker ? route('workers.show', $record->worker->uuid) : null)
                    ->color('primary')
                    ->openUrlInNewTab(),

                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->searchable()
                    ->url(fn ($record) => $record->agent ? route('agents.show', $record->agent->uuid ?? $record->agent->id) : null)
                    ->color('primary')
                    ->openUrlInNewTab(),

                TextColumn::make('jobPost.job_title')
                    ->label('জব')
                    ->limit(30)
                    ->url(fn ($record) => $record->jobPost ? route('jobs.show', $record->jobPost->uuid) : null)
                    ->color('primary')
                    ->openUrlInNewTab(),

                TextColumn::make('agent_fee_sar')
                    ->label('মোট ফি (SAR)')
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('chapai_commission_sar')
                    ->label('কমিশন (SAR)')
                    ->money('SAR'),

                TextColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed' => 'info',
                        'working'   => 'warning',
                        'completed' => 'success',
                        'disputed'  => 'danger',
                        'cancelled', 'refunded' => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'confirmed' => 'নিশ্চিত হয়েছে',
                        'working'   => 'কাজ চলমান',
                        'completed' => 'সম্পন্ন',
                        'disputed'  => 'বিরোধ চলমান',
                        'cancelled' => 'বাতিল',
                        'refunded'  => 'রিফান্ড হয়েছে',
                        default     => $state,
                    }),

                // ── Step 10.8b Fix: এখন withCount() থেকে আসা কলাম ব্যবহার করছে, নতুন query চালাচ্ছে না ──
                TextColumn::make('released_milestones_count')
                    ->label('মাইলস্টোন')
                    ->state(fn ($record) => $record->released_milestones_count . '/3 released'),

                TextColumn::make('created_at')
                    ->label('তৈরি হয়েছে')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('বিস্তারিত')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.job-deals.view', $record)),
            ])
            ->defaultSort('created_at', 'desc');
    }
}