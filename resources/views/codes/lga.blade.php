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
        NIIP LGA UPLOADING
    </div>
    <div class="card-body">
        <form action="{{route('importlga')}}" method="post" enctype="multipart/form-data">
            @csrf
        <div class="col-auto mb-3">
            <input type="file" name="lgaimport"  required class="form-control" id='lgaimport'>
        </div>
        <button type="submit" class="btn btn-primary">Import</button>
        </form>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped" id='lgatable'>
            <thead>
                <thead>
                <th>ID</th>
                <th>LGA</th>
                <th>STATE</th>
                <th>Actions</th>
            </thead>
            <tbody>
            @forelse ($lgas as $lga)
                <tr>
                <td>{{$lga->lgaid}}</td>
                <td>{{$lga->lganame}}</td>
                <td>{{$lga->getstate()->statename}}</td>
                <td></td>
            </tr>
                
            @empty
                <tr>
                    <td></td>
                    <td></td>
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
  new DataTable('#lgatable');
</script>
</x-layouts.app>