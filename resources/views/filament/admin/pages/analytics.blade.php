<x-filament-panels::page>
    <x-filament-widgets::widgets
        :widgets="$this->getHeaderWidgets()"
        :columns="[
            'default' => 1,
            'sm' => 2,
            'lg' => 4,
        ]"
    />
</x-filament-panels::page>