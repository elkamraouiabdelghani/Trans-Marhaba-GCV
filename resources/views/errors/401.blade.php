<x-guest-layout>
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="max-w-3xl w-full text-center">
            <div class="mb-4">
                <i class="bi bi-lock text-orange-500" style="font-size: 6rem;"></i>
            </div>
            <div class="text-6xl font-bold text-orange-600 mb-3">401</div>
            <h1 class="text-2xl font-semibold">Non Authentifié</h1>
            <p class="mt-2 text-gray-600">Veuillez vous connecter pour continuer.</p>

            <div class="mt-6 text-left">
                <div class="rounded-md border border-orange-100 bg-orange-50 p-4">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-info-circle text-orange-600 text-xl"></i>
                        <div>
                            <strong>Astuce</strong>
                            <ul class="mt-2 list-disc pl-5 text-sm text-gray-700">
                                <li>Connectez-vous avec vos identifiants</li>
                                <li>Si vous avez oublié votre mot de passe, utilisez la fonction de réinitialisation</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-primary text-white">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Se Connecter
                </a>
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md border border-gray-300 hover:bg-gray-50 text-gray-800">
                    Accueil
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>


