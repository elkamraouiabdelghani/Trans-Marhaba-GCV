<x-guest-layout>
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="max-w-3xl w-full text-center">
            <div class="mb-4">
                <i class="bi bi-clock-history text-sky-500" style="font-size: 6rem;"></i>
            </div>
            <div class="text-6xl font-bold text-sky-600 mb-3">419</div>
            <h1 class="text-2xl font-semibold">Session Expirée</h1>
            <p class="mt-2 text-gray-600">Votre session a expiré pour des raisons de sécurité. Veuillez vous reconnecter.</p>

            <div class="mt-6 text-left">
                <div class="rounded-md border border-sky-100 bg-sky-50 p-4">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-shield-check text-sky-600 text-xl"></i>
                        <div>
                            <strong>Pourquoi cette erreur ?</strong>
                            <ul class="mt-2 list-disc pl-5 text-sm text-gray-700">
                                <li>Votre session a expiré après une période d'inactivité</li>
                                <li>Le token de sécurité a expiré</li>
                                <li>Une nouvelle session est nécessaire pour continuer</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-primary text-white">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Se Reconnecter
                </a>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md border border-gray-300 hover:bg-gray-50 text-gray-800">
                    <i class="bi bi-house me-2"></i>
                    Retour à l'Accueil
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>


