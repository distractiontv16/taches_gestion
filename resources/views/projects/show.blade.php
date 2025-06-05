@extends('layouts.app')
@section('title')
    {{ $project->name }} - Project Details
@endsection
@section('content')
    <div class="container">
        <h2 class="mb-4 shadow-sm p-3 rounded bg-white text-center"> {{ $project->name }}</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
                @if (session('whatsapp_link'))
                    <div class="mt-2">
                        <a href="{{ session('whatsapp_link') }}" target="_blank" class="btn btn-success">
                            <i class="bi bi-whatsapp"></i> Envoyer notification WhatsApp
                        </a>
                    </div>
                @endif
            </div>
        @endif
        <div class="row">
            <div class="col-md-7">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">{{ $project->name }}</h5>
                        <p class="card-text">{{ $project->description }}</p>
                        <p class="card-text"><strong>Start Date:</strong>
                            {{ \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') }}</p>
                        <p class="card-text"><strong>End Date:</strong>
                            {{ \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') }}</p>
                        <p class="card-text"><strong>Status:</strong>
                            @if($project->status == 'not_started')
                                Not Started
                            @elseif($project->status == 'in_progress')
                                In Progress
                            @elseif($project->status == 'completed')
                                Completed
                            @else
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            @endif
                        </p>
                        <p class="card-text"><strong>Budget:</strong> ${{ $project->budget }}</p>

                        <h5 class="mt-4">Project Progress</h5>
                        @php
                            $totalTasks = $project->tasks->count();
                            $completedTasks = $project->tasks->where('status', 'completed')->count();
                            $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
                        @endphp
                        <div class="progress mb-4">
                            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;"
                                aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                {{ round($progress) }}%</div>
                        </div>

                        <a href="{{ route('projects.index') }}" class="btn btn-secondary mt-3">Back to Projects</a>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title"> Team Members </h5>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addMemberModal"> <i class="bi bi-plus-circle"></i> </button>
                        </div>

                        <div class="row">
                            @foreach ($teamMembers as $user)
                                <div class="col-12">
                                    <div class="card mb-3">
                                        <div class="row g-0">
                                            <div class="col-md-12">
                                                <div class="card-body">
                                                    <p class="card-title fw-bolder">{{ $user->name }}</p>
                                                    <p class="card-text">{{ $user->email }}</p>
                                                    <form action="{{ route('projects.removeMember') }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to remove this member?')">
                                                            <i class="bi bi-person-dash"></i> Remove
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMemberModalLabel">Add Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('projects.addMember')}}" method="POST">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $project->id }}">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Select User</label>
                            <select class="form-select" name="user_id" id="user_id" onchange="updateWhatsAppNumber()">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" data-whatsapp="{{ $user->whatsapp_number ?? '' }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="whatsapp_number" class="form-label">WhatsApp Number</label>
                            <input type="text" class="form-control" id="whatsapp_number" name="whatsapp_number" placeholder="+33612345678" required>
                            <div class="form-text">Format international (exemple: +33612345678)</div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Member</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

<script>
    function updateWhatsAppNumber() {
        const selectElement = document.getElementById('user_id');
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const whatsappNumber = selectedOption.getAttribute('data-whatsapp');
        
        document.getElementById('whatsapp_number').value = whatsappNumber || '';
    }
    
    // Initialiser au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        updateWhatsAppNumber();
    });
</script>

<style>
    .whatsapp-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #25d366;
        color: white;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        display: flex;
        justify-content: center;
        align-items: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        z-index: 1000;
        transition: all 0.3s;
    }
    
    .whatsapp-btn:hover {
        transform: scale(1.1);
        background-color: #128C7E;
        color: white;
    }
</style>

@if (session('whatsapp_link'))
<a href="{{ session('whatsapp_link') }}" class="whatsapp-btn" target="_blank" title="Contacter sur WhatsApp">
    <i class="bi bi-whatsapp" style="font-size: 30px;"></i>
</a>
@endif
