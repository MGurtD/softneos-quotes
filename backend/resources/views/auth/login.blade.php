<x-layouts.public title="Accés clients">
    <section class="py-5 bg-light">
        <div class="container py-lg-4">
            <div class="row justify-content-center">
                <div class="col-sm-10 col-md-7 col-lg-5">
                    <div class="card login-card shadow">
                        <div class="card-body p-4 p-md-5">
                            <p class="eyebrow">Àrea de clients</p>
                            <h1 class="h3 mb-4">Accés a SoftNeos Suite</h1>

                            <form method="POST" action="{{ route('login') }}" novalidate>
                                @csrf

                                <div class="mb-3">
                                    <label for="email" class="form-label">Correu electrònic</label>
                                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                                           @class(['form-control', 'is-invalid' => $errors->has('email')])
                                           autocomplete="username" required autofocus
                                           @error('email') aria-describedby="email-error" @enderror>
                                    @error('email')
                                        <div id="email-error" class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label">Contrasenya</label>
                                    <input id="password" name="password" type="password"
                                           @class(['form-control', 'is-invalid' => $errors->has('password')])
                                           autocomplete="current-password" required
                                           @error('password') aria-describedby="password-error" @enderror>
                                    @error('password')
                                        <div id="password-error" class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100">Entra</button>
                            </form>

                            <p class="small text-secondary mt-4 mb-0">Demo: <code>demo@softneos.test</code> / <code>password</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
