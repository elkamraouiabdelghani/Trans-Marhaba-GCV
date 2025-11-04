<x-guest-layout>
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="max-w-3xl w-full text-center">
            <div class="mb-4">
                <i class="bi bi-exclamation-octagon text-red-600" style="font-size: 6rem;"></i>
            </div>
            <div class="text-6xl font-bold text-red-700 mb-3">500</div>
            <h1 class="text-2xl font-semibold">Erreur Interne du Serveur</h1>
            <p class="mt-2 text-gray-600">Une erreur inattendue s'est produite. Veuillez réessayer dans quelques minutes.</p>

            <div class="mt-6 text-left">
                <div class="rounded-md border border-yellow-100 bg-yellow-50 p-4">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-tools text-yellow-600 text-xl"></i>
                        <div>
                            <strong>Que se passe-t-il ?</strong>
                            <ul class="mt-2 list-disc pl-5 text-sm text-gray-700">
                                <li>Le serveur rencontre un problème temporaire</li>
                                <li>Notre équipe technique travaille à résoudre le problème</li>
                                <li>Veuillez réessayer dans quelques minutes</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                <button onclick="location.reload()" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-primary text-white">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Réessayer
                </button>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md border border-gray-300 hover:bg-gray-50 text-gray-800">
                    <i class="bi bi-house me-2"></i>
                    Retour à l'Accueil
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>


