<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            @foreach ($columns as $label)
                <th style="text-align: left; padding: 4px; border-bottom: 1px solid #000;">{{ $label }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                @foreach ($columns as $key => $label)
                    <td style="padding: 4px; border-bottom: 1px solid #ddd;">{{ $row[$key] ?? '' }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
