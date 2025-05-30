<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.js"></script>
<x-layouts.app>
     
        @if ($errors->any())
  <div class="alert alert-danger">
      <ul>
          @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
          @endforeach
      </ul>
  </div>
@endif
<div>
<div class="card">
    <div class="card-header">
        NIIP VEHICLE USE UPLOADING
    </div>
    <div class="card-body">
        <form action="{{route('importvuse')}}" method="post" enctype="multipart/form-data">
            @csrf
        <div class="col-auto mb-3">
            <input type="file" name="vuseimport"  required class="form-control" id='vuseimport'>
        </div>
        <button type="submit" class="btn btn-primary">Import</button>
        </form>
    </div>
    <div class="card-body">
        <table class="table table-striped" id='vuses'>
            <thead>
                <thead>
                <th>S/no</th>
                <th>NIIP ID</th>
                <th>Purpose</th>
                <th>Actions</th>
            </thead>
            <tbody>
            @forelse ($vuses as $vuse)
                <tr>
                <td></td>
                <td>{{$vuse->niipuseid}}</td>
                <td>{{$vuse->usename}}</td>
                <td></td>
            </tr>
                
            @empty
                <tr>
                    <td></td>
                    <td> No vuse Recorded in System</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
<script>
  new DataTable('#vuse');
</script>
</x-layouts.app>