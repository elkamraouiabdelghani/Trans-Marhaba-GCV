<x-guest-layout>
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="max-w-3xl w-full text-center">
            <div class="mb-4">
                <i class="bi bi-speedometer2 text-yellow-500" style="font-size: 6rem;"></i>
            </div>
            <div class="text-6xl font-bold text-yellow-600 mb-3">429</div>
            <h1 class="text-2xl font-semibold">Trop de Requêtes</h1>
            <p class="mt-2 text-gray-600">Vous avez dépassé la limite de requêtes autorisées. Veuillez patienter avant de réessayer.</p>

            <div class="mt-6 text-left">
                <div class="rounded-md border border-yellow-100 bg-yellow-50 p-4">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-info-circle text-yellow-600 text-xl"></i>
                        <div>
                            <strong>Pourquoi cette erreur ?</strong>
                            <ul class="mt-2 list-disc pl-5 text-sm text-gray-700">
                                <li>Trop de requêtes ont été envoyées en peu de temps</li>
                                <li>Une limite de sécurité a été atteinte</li>
                                <li>Veuillez attendre quelques minutes avant de réessayer</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                <button id="retryBtn" onclick="location.reload()" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-primary text-white" disabled>
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Réessayer <span id="retryCountdown" class="ml-1">(60s)</span>
                </button>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-md border border-gray-300 hover:bg-gray-50 text-gray-800">
                    <i class="bi bi-house me-2"></i>
                    Retour à l'Accueil
                </a>
            </div>

            <div class="sr-only" aria-live="polite">
                <span id="countdown">60</span>
            </div>
        </div>
    </div>

    <script>
        let timeLeft = 60;
        const retryBtn = document.getElementById('retryBtn');
        const retryCountdown = document.getElementById('retryCountdown');
        const timer = setInterval(() => {
            timeLeft--;
            retryCountdown.textContent = `(${timeLeft}s)`;
            if (timeLeft <= 0) {
                clearInterval(timer);
                retryBtn.disabled = false;
                retryBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Réessayer';
            }
        }, 1000);
    </script>
</x-guest-layout>


