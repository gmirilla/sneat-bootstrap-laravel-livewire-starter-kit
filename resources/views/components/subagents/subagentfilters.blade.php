<style>
    .sf-card {
        background: #fff;
        border: 1px solid #e0e6f0;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,.06);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }
    .sf-header {
        background: rgb(177, 135, 82);
        color: #fff;
        padding: .65rem 1.25rem;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .sf-body {
        padding: 1rem 1.25rem 1.1rem;
    }
    .sf-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: .85rem;
        align-items: end;
    }
    .sf-field label {
        display: block;
        font-size: .72rem;
        font-weight: 700;
        color: #5a6a80;
        text-transform: uppercase;
        letter-spacing: .07em;
        margin-bottom: .3rem;
    }
    .sf-field select,
    .sf-field input[type="date"] {
        width: 100%;
        padding: .45rem .75rem;
        border: 1.5px solid #d0dae8;
        border-radius: 8px;
        font-size: .83rem;
        color: #1a2e3e;
        background: #f8fafc;
        transition: border-color .2s, box-shadow .2s;
        appearance: none;
        -webkit-appearance: none;
    }
    .sf-field select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%235a6a80'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right .75rem center;
        background-color: #f8fafc;
        padding-right: 2rem;
    }
    .sf-field select:focus,
    .sf-field input[type="date"]:focus {
        outline: none;
        border-color: #2d6a9f;
        box-shadow: 0 0 0 3px rgba(45,106,159,.12);
        background: #fff;
    }
    .sf-actions {
        display: flex;
        gap: .5rem;
        align-items: flex-end;
        flex-wrap: wrap;
    }
    .btn-sf-apply {
        padding: .48rem 1.1rem;
        background: rgb(177, 135, 82);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: .8rem;
        font-weight: 700;
        cursor: pointer;
        transition: background .18s;
        white-space: nowrap;
    }
    .btn-sf-apply:hover { background: #1a3c5e; }
    .btn-sf-clear {
        padding: .48rem 1rem;
        background: #fff;
        color: #c0392b;
        border: 1.5px solid #e0c0bd;
        border-radius: 8px;
        font-size: .8rem;
        font-weight: 700;
        cursor: pointer;
        transition: background .18s, color .18s;
        white-space: nowrap;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }
    .btn-sf-clear:hover { background: #fdf0ef; color: #a93226; }

    /* Active filter chips */
    .sf-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        padding: .5rem 1.25rem .75rem;
        border-top: 1px dashed #e0e6f0;
    }
    .sf-chip {
        background: #eaf2fb;
        color: #1a3c5e;
        border: 1px solid #b8d4ed;
        border-radius: 20px;
        font-size: .72rem;
        font-weight: 600;
        padding: .2rem .7rem;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
    }
    .sf-chip-label {
        font-size: .72rem;
        font-weight: 700;
        color: #5a6a80;
        align-self: center;
    }
</style>

@php
    $searchParams = is_array($searchParams) ? $searchParams : [];
    $hasFilter    = !empty(array_filter($searchParams));
@endphp

<div class="sf-card">
    <div class="sf-header">
        <i class="fa fa-sliders"></i> Filter Policies
    </div>

    <div class="sf-body">
        <form action="{{ route('subagent.filterreport') }}" method="POST" id="sfForm">
            @csrf
            <div class="sf-grid">

                {{-- Policy Type --}}
                <div class="sf-field">
                    <label for="sf_policytype">Policy Type</label>
                    <select name="policytype" id="sf_policytype">
                        <option value="">All Types</option>
                        @foreach ($products as $product)
                            <option value="{{ $product }}"
                                {{ ($searchParams['policytype'] ?? '') === $product ? 'selected' : '' }}>
                                {{ ucwords($product) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="sf-field">
                    <label for="sf_status">Status</label>
                    <select name="status" id="sf_status">
                        <option value="">All Statuses</option>
                        <option value="approved"  {{ ($searchParams['status'] ?? '') === 'approved'  ? 'selected' : '' }}>Approved</option>
                        <option value="draft"     {{ ($searchParams['status'] ?? '') === 'draft'     ? 'selected' : '' }}>Draft</option>
                        <option value="failed"    {{ ($searchParams['status'] ?? '') === 'failed'    ? 'selected' : '' }}>Failed</option>
                        <option value="cancelled" {{ ($searchParams['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                {{-- Date From --}}
                <div class="sf-field">
                    <label for="sf_datefrom">Date From</label>
                    <input type="date" name="datefrom" id="sf_datefrom"
                        value="{{ $searchParams['datefrom'] ?? '' }}">
                </div>

                {{-- Date To --}}
                <div class="sf-field">
                    <label for="sf_dateto">Date To</label>
                    <input type="date" name="dateto" id="sf_dateto"
                        value="{{ $searchParams['dateto'] ?? '' }}">
                </div>

                {{-- Agent (admin/superadmin only) --}}
                @if ($user->role === 'admin' || $user->role === 'superadmin')
                    <div class="sf-field">
                        <label for="sf_agentcode">Agent</label>
                        <select name="agentcode" id="sf_agentcode">
                            <option value="">All Agents</option>
                            @foreach ($agentslist as $agent)
                                <option value="{{ $agent->uid }}"
                                    {{ ($searchParams['agentcode'] ?? '') === $agent->uid ? 'selected' : '' }}>
                                    {{ $agent->getuserinfo()->name ?? 'Unknown' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="sf-actions">
                    <button type="submit" class="btn-sf-apply">
                        <i class="fa fa-search"></i> Apply
                    </button>
                    <a href="{{ route('list_policy_subagents') }}" class="btn-sf-clear">
                        <i class="fa fa-times"></i> Clear
                    </a>
                </div>

            </div>
        </form>
    </div>

    {{-- Active filter chips --}}
    @if ($hasFilter)
        <div class="sf-chips">
            <span class="sf-chip-label">Active:</span>
            @if (!empty($searchParams['policytype']))
                <span class="sf-chip"><i class="fa fa-tag"></i> {{ ucwords($searchParams['policytype']) }}</span>
            @endif
            @if (!empty($searchParams['status']))
                <span class="sf-chip"><i class="fa fa-circle"></i> {{ ucwords($searchParams['status']) }}</span>
            @endif
            @if (!empty($searchParams['datefrom']))
                <span class="sf-chip"><i class="fa fa-calendar"></i> From {{ $searchParams['datefrom'] }}</span>
            @endif
            @if (!empty($searchParams['dateto']))
                <span class="sf-chip"><i class="fa fa-calendar"></i> To {{ $searchParams['dateto'] }}</span>
            @endif
            @if (!empty($searchParams['agentcode']))
                <span class="sf-chip"><i class="fa fa-user"></i> Agent filtered</span>
            @endif
        </div>
    @endif
</div>
