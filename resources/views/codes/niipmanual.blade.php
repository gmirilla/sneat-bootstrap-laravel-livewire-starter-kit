<x-layouts.app>
    <div class="container">
        <h1>NIIP Manual Import</h1>
        <form action="{{ route('niipmanual.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="file" class="form-label">Upload Excel File</label>
                <input type="file" class="form-control" id="file" name="file" accept=".xlsx, .xls">
            </div>
            <button type="submit" class="btn btn-primary">Import</button>
        </form>
    </div>
</x-layouts.app>