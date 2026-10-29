@if(session('success'))
    <div class="onboarding-form-message onboarding-form-message-success" role="status">
        <p>{{ session('success') }}</p>
    </div>
@endif
@if($errors->any())
    <div class="onboarding-form-message onboarding-form-message-error" id="step-form-errors" role="alert" aria-labelledby="step-form-errors-title" tabindex="-1">
        <p class="onboarding-form-message-title" id="step-form-errors-title">Please check your corrections.</p>
        @if(count($errors->all()) === 1)
            <p>{{ $errors->first() }}</p>
        @else
            <ul class="onboarding-form-error-list">
                @foreach(array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
