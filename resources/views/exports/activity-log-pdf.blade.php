<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Журнал действий</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }
        h1 {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .old-value {
            color: #d32f2f;
        }
        .new-value {
            color: #388e3c;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #999;
        }
    </style>
</head>
<body>
    <h1>Журнал действий</h1>

    @if($filters)
    <div style="margin-bottom: 15px; font-size: 9px; color: #666;">
        Фильтры:
        @if(!empty($filters['from'])) Период с {{ $filters['from'] }} @endif
        @if(!empty($filters['to'])) по {{ $filters['to'] }} @endif
        @if(!empty($filters['type'])) | Тип: {{ $filters['type'] }} @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Дата и время</th>
                <th>Пользователь</th>
                <th>Тип</th>
                <th>Действие</th>
                <th>Старые значения</th>
                <th>Новые значения</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ $log->id }}</td>
                <td>{{ $log->created_at->format('d.m.Y H:i') }}</td>
                <td>{{ $log->causer?->name ?? 'Система' }}</td>
                <td>{{ $log->log_name }}</td>
                <td>{{ $log->description }}</td>
                <td class="old-value">
                    @php
                        $changes = $log->attribute_changes ?? $log->properties;
                        $oldValues = '';
                        if ($changes) {
                            $hasNested = isset($changes['attributes']) || isset($changes['new']) || isset($changes['old']);
                            $oldData = $hasNested ? ($changes['old'] ?? []) : $changes;
                            $oldValues = json_encode($oldData, JSON_UNESCAPED_UNICODE);
                        }
                    @endphp
                    {{ $oldValues ?: '-' }}
                </td>
                <td class="new-value">
                    @php
                        $newValues = '';
                        if ($changes) {
                            $hasNested = isset($changes['attributes']) || isset($changes['new']) || isset($changes['old']);
                            $newData = $hasNested ? ($changes['attributes'] ?? $changes['new'] ?? []) : $changes;
                            $newValues = json_encode($newData, JSON_UNESCAPED_UNICODE);
                        }
                    @endphp
                    {{ $newValues ?: '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="no-data">Записи не найдены</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
