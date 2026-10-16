<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Журнал действий</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #333;
            line-height: 1.3;
        }
        h1 {
            text-align: center;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .filters {
            margin-bottom: 10px;
            font-size: 7px;
            color: #666;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 3px 4px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 7px;
        }
        .col-id { width: 8%; }
        .col-date { width: 12%; }
        .col-user { width: 12%; }
        .col-type { width: 10%; }
        .col-action { width: 20%; }
        .col-old { width: 19%; }
        .col-new { width: 19%; }
        .json-block {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 7px;
            white-space: pre-wrap;
            word-wrap: break-word;
            margin: 0;
            padding: 2px;
            background-color: #f9f9f9;
            border-radius: 2px;
        }
        .old-value .json-block {
            color: #d32f2f;
        }
        .new-value .json-block {
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

    @if(!empty($filters['from']) || !empty($filters['to']) || !empty($filters['type']))
    <div class="filters">
        Фильтры:
        @if(!empty($filters['from'])) Период с {{ $filters['from'] }} @endif
        @if(!empty($filters['to'])) по {{ $filters['to'] }} @endif
        @if(!empty($filters['type'])) | Тип: {{ $filters['type'] }} @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th class="col-id">ID</th>
                <th class="col-date">Дата и время</th>
                <th class="col-user">Пользователь</th>
                <th class="col-type">Тип</th>
                <th class="col-action">Действие</th>
                <th class="col-old">Старые значения</th>
                <th class="col-new">Новые значения</th>
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
                            $oldValues = json_encode($oldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                        }
                    @endphp
                    @if($oldValues)
                        <pre class="json-block">{{ $oldValues }}</pre>
                    @else
                        -
                    @endif
                </td>
                <td class="new-value">
                    @php
                        $newValues = '';
                        if ($changes) {
                            $hasNested = isset($changes['attributes']) || isset($changes['new']) || isset($changes['old']);
                            $newData = $hasNested ? ($changes['attributes'] ?? $changes['new'] ?? []) : $changes;
                            $newValues = json_encode($newData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                        }
                    @endphp
                    @if($newValues)
                        <pre class="json-block">{{ $newValues }}</pre>
                    @else
                        -
                    @endif
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
