        <!--Filter for policy list -->
        <div class="card row gy-2 gx-3 align-items-center mb-3">
            <form action="{{ route('subagent.filterreport') }}" method="post">
                @csrf
                <div class="d-flex flex-row">
                    <div class="card-body">
                        <div>
                            <label for="policytype" class="form-label">Policy Type</label>
                            <select name="policytype" id="policytype" class="form-select">
                                @if ($searchParams['policytype'] ?? false)
                                    <option value="{{ $searchParams['policytype'] }}" selected>
                                        {{ ucwords($searchParams['policytype']) }}</option>
                                @else
                                    <option value="">--Select Policy Type--</option>
                                    @forelse ($products as $product)
                                        <option value="{{ $product }}">{{ ucwords($product) }}</option>
                                    @empty
                                    @endforelse

                                @endif

                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div>
                            <label for="status" class="form-label">Policy Status</label>
                            <select name="status" id="status" class="form-select">
                                @if ($searchParams['status'] ?? false)
                                    <option value="{{ $searchParams['status'] }}" selected>
                                        {{ ucwords($searchParams['status']) }}</option>
                                @else
                                    <option value="">--Select Status--</option>
                                    <option value="approved">Approved</option>
                                    <option value="draft">Draft</option>
                                    <option value="failed">Failed</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div>
                            <label for="status" class="form-label">Date From</label>
                            @if ($searchParams['datefrom'] ?? false)
                                <input type="date" name="datefromfilter" id="datefromfilter" class="form-control"
                                    value="{{ $searchParams['datefrom'] }}" disabled>
                                <input type="date" name="datefrom" id="datefrom" class="form-control"
                                    value="{{ $searchParams['datefrom'] }}" hidden>
                            @else
                                <input type="date" name="datefrom" id="datefrom" class="form-control">
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div>
                            <label for="status" class="form-label">Date To</label>
                            @if ($searchParams['dateto'] ?? false)
                                <input type="date" name="datetofilter" id="datetofilter" class="form-control"
                                    value="{{ $searchParams['dateto'] }}" disabled>
                                <input type="date" name="dateto" id="dateto" class="form-control"
                                    value="{{ $searchParams['dateto'] }}" hidden>
                            @else
                                <input type="date" name="dateto" id="dateto" class="form-control">
                            @endif

                        </div>
                    </div>
                    @if ($user->role=='admin'||$user->role=='superadmin')
                    <div class="card-body">
                        <div>
                            <label for="agent" class="form-label">Agent</label>
                            <select name="agentcode" id="agentcode" class="form-select">

                                    <option value="">--Select Agent--</option>

                                    @forelse ($agentslist as $agent)
                                        <option value="{{ $agent->uid }}">{{ $agent->getuserinfo()->name ?? 'Unknown'}}</option>
                                    @empty
                                    @endforelse
                                    </select>
                        </div>
                    </div>
                        
                    @endif
                    <div class="card-body align-self-end">
                        <div>
                            <button class="btn btn-primary mb-3" type="submit" style="font-size:0.8rem">Apply
                                Filters</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>