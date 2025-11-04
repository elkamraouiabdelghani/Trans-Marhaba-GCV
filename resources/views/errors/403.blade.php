<x-guest-layout>
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="max-w-3xl w-full text-center">
            <div class="mb-4">
                <i class="bi bi-shield-exclamation text-yellow-500" style="font-size: 6rem;"></i>
            </div>
            <div class="text-6xl font-bold text-red-600 mb-3">403</div>
            <h1 class="text-2xl font-semibold">Accès Refusé</h1>
            <p class="mt-2 text-gray-600">Vous n'avez pas les permissions nécessaires pour accéder à cette ressource.</p>

            <div class="mt-6 text-left">
                <div class="rounded-md border border-yellow-100 bg-yellow-50 p-4">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-info-circle text-yellow-600 text-xl"></i>
                        <div>
                            <strong>Pourquoi cette erreur ?</strong>
                            <ul class="mt-2 list-disc pl-5 text-sm text-gray-700">
                                <li>Vous n'êtes pas connecté à votre compte</li>
                                <li>Votre compte n'a pas les droits d'accès requis</li>
                                <li>La session a expiré</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-primary text-white">
                        <i class="bi bi-house me-2"></i>
                        Retour à l'Accueil
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-primary text-white">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Se Connecter
                    </a>
                @endauth
                <button onclick="history.back()" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md border border-gray-300 hover:bg-gray-50 text-gray-800">
                    <i class="bi bi-arrow-left me-2"></i>
                    Page Précédente
                </button>
            </div>
        </div>
    </div>
</x-guest-layout>


