<x-guest-layout>
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="max-w-3xl w-full text-center">
            <div class="mb-4">
                <i class="bi bi-exclamation-triangle text-warning" style="font-size: 6rem;"></i>
            </div>
            <div class="text-6xl font-bold text-blue-600 mb-3">404</div>
            <h1 class="text-2xl font-semibold">Page Non Trouvée</h1>
            <p class="mt-2 text-gray-600">Désolé, la page que vous recherchez n'existe pas ou a été déplacée.</p>

            <div class="mt-6 text-left">
                <div class="rounded-md border border-blue-100 bg-blue-50 p-4">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-info-circle text-blue-600 text-xl"></i>
                        <div>
                            <strong>Que pouvez-vous faire ?</strong>
                            <ul class="mt-2 list-disc pl-5 text-sm text-gray-700">
                                <li>Vérifiez l'URL dans la barre d'adresse</li>
                                <li>Utilisez le menu de navigation pour accéder aux sections</li>
                                <li>Retournez à la page d'accueil</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-primary text-white">
                    <i class="bi bi-house me-2"></i>
                    Retour à l'Accueil
                </a>
                <button onclick="history.back()" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md border border-gray-300 hover:bg-gray-50 text-gray-800">
                    <i class="bi bi-arrow-left me-2"></i>
                    Page Précédente
                </button>
            </div>
        </div>
    </div>
</x-guest-layout>

