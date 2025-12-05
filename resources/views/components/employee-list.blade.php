<div>
    <!-- Nothing worth having comes easy. - Theodore Roosevelt -->
    <table>
        @foreach ($employees as $employee)
            <tr>
                <td>{{ $employee->full_name }}</td>
                <td>{{ $employee->position }}</td>
            </tr>
        @endforeach
    </table>
</div>


