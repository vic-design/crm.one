<?php

namespace App\Exports;

use App\Models\Activity;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class ActivityLogExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected array $filters;

    /**
     * Summary of __construct
     * @param array $filters
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Summary of collection
     * @return \Illuminate\Database\Eloquent\Collection<int, SpatieActivity>|\Illuminate\Support\Collection<int, \stdClass>
     */
    public function collection(): Collection
    {
        $query = SpatieActivity::with(['causer', 'subject'])->latest();

        if (!empty($this->filters['from'])) {
            $query->whereDate('created_at', '>=', $this->filters['from']);
        }
        if (!empty($this->filters['to'])) {
            $query->whereDate('created_at', '<=', $this->filters['to']);
        }
        if (!empty($this->filters['type'])) {
            $query->where('log_name', $this->filters['type']);
        }

        return $query->get();
    }

    /**
     * Summary of headings
     * @return string[]
     */
    public function headings(): array
    {
        return [
            'ID',
            'Дата и время',
            'Пользователь',
            'Тип',
            'Действие',
            'Старые значения',
            'Новые значения',
        ];
    }

    /**
     * Summary of map
     * @param mixed $activity
     * @return array<bool|mixed|string>
     */
    public function map($activity): array
    {
        $changes = $activity->attribute_changes ?? $activity->properties;
        $oldValues = '';
        $newValues = '';

        if ($changes) {
            $hasNested = isset($changes['attributes']) || isset($changes['new']) || isset($changes['old']);
            $newData = $hasNested ? ($changes['attributes'] ?? $changes['new'] ?? []) : $changes;
            $oldData = $hasNested ? ($changes['old'] ?? []) : [];

            $oldValues = json_encode($oldData, JSON_UNESCAPED_UNICODE);
            $newValues = json_encode($newData, JSON_UNESCAPED_UNICODE);
        }

        return [
            $activity->id,
            $activity->created_at->format('d.m.Y H:i'),
            $activity->causer?->name ?? 'Система',
            $activity->log_name,
            $activity->description,
            $oldValues,
            $newValues,
        ];
    }

    /**
     * Summary of styles
     * @param Worksheet $sheet
     * @return array[]
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
