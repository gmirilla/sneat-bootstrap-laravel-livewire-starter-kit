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
        NIIP COLORS UPLOADING
    </div>
    <div class="card-body">
        <form action="{{route('importcolor')}}" method="post" enctype="multipart/form-data">
            @csrf
        <div class="col-auto mb-3">
            <input type="file" name="vcolorimport"  required class="form-control" id='colorimport'>
        </div>
        <button type="submit" class="btn btn-primary">Import</button>
        </form>
    </div>
    <div class="card-body">
        <table class="table table-striped" id='color'>
            <thead>
                <thead>
                <th>S/no</th>
                <th>NIIP ID</th>
                <th>Color</th>
                <th>Actions</th>
            </thead>
            <tbody>
            @forelse ($colors as $color)
                <tr>
                <td></td>
                <td>{{$color->colorid}}</td>
                <td>{{$color->color}}</td>
                <td></td>
            </tr>
                
            @empty
                <tr>
                    <td></td>
                    <td> No color Recorded in System</td>
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
  new DataTable('#color');
</script>
</x-layouts.app>