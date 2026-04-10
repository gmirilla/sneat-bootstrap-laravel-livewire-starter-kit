
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
        <div class="card-header">LIST OF AGENTS</div>
        <div class="card-body">
            <table class="table table-striped table-sm" id="agentlist">
                <thead>
                    <th>S/N</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>State</th>
                    <th>Credit Assigned</th>
                    <th>Credit Used</th>
                    <th>Action</th>
                </thead>
                <tbody>
                    @forelse ($agents as $agent )
                
                    <tr>
                        <form action="{{route('agentprofile')}}" method="GET">
                        <td>#</td>
                         <td>{{$agent->name}}</td>
                            <td>{{$agent->email}}</td>
                          <td>{{$agent->status}}</td>
                          <td>{{$agent->noallocated}}</td>
                        <td>{{$agent->noused}}</td>
                           <td><input type="text" name=uid hidden value="{{$agent->id}}" id="uid">
                            <button type="submit" class="btn btn-primary">View</button></td>
                            </form>
                    </tr>
                
                        
                    @empty
                        <tr>
                            <td>No User has been assigned as an Agent</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

</div>
        <script>
            new DataTable('#agentlist', {
                dom: 'Bfrtip', // Adds the button controls
                buttons: [{
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        title: 'Agent List',

                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Export to PDF',
                        title: 'Policy List',
                        orientation: 'landscape', // optional
                        pageSize: 'A4' // optional
                    }
                ]
            });
        </script>
</x-layouts.app>