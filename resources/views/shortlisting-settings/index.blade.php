@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="page-inner">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="page-title">Shortlisting Scoring Settings</h4>
        </div>

        @if(session('success'))
            <div class="alert alert-success shadow-sm rounded">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger shadow-sm rounded">
                <ul class="mb-0">
                    @foreach($errors->all() as $error) 
                        <li>{{ $error }}</li> 
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Configure Scoring Weights</h4>
                <small>Total weight for skills, experience, and education should equal 100%. Bonus is additional points.</small>
            </div>
            <div class="card-body">
                <form action="{{ route('shortlisting-settings.update') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Skills Weight (%)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" 
                                   name="skills_weight" 
                                   value="{{ old('skills_weight', $setting->skills_weight) }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Experience Weight (%)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" 
                                   name="experience_weight" 
                                   value="{{ old('experience_weight', $setting->experience_weight) }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Education Weight (%)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" 
                                   name="education_weight" 
                                   value="{{ old('education_weight', $setting->education_weight) }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold">Qualification Bonus (Points)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" 
                                   name="qualification_bonus" 
                                   value="{{ old('qualification_bonus', $setting->qualification_bonus) }}" required>
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-rounded">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
