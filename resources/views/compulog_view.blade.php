@if(count($dbNames) == 0)
    <p>No databases found starting with 'supcompta'.</p>
@else
    <form action="{{ route('your_action_here') }}" method="POST">
        @csrf
        <label for="databaseSelect">Select Database:</label>
        <select name="database" id="databaseSelect">
            @foreach ($dbNames as $dbName)
                <option value="{{ $dbName }}">{{ $dbName }}</option>
            @endforeach
        </select>
        <button type="submit">Submit</button>
    </form>
@endif
