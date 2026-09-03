<table>
    <thead>
        <tr>
            @foreach ($exportHeader as $header)
                <th>{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($exportData as $row)
            <tr>
                @foreach ($row as $col)
                    <td>{{ $col }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
