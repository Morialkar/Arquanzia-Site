<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arquanzia - Connexion requise</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto p-8">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 text-center">
            <h1 class="text-2xl font-bold text-gray-800 mb-4">Bienvenue sur Arquanzia</h1>
            
            <p class="text-gray-600 mb-6">
                Pour accéder à votre espace communautaire, connectez-vous à votre compte boutique ou utilisez un lien magic.
            </p>

            <div class="space-y-4">
                <a href="https://creations-sortilege.com/account/login" 
                   class="block w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 font-medium">
                    Se connecter à la boutique
                </a>
                
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">ou</span>
                    </div>
                </div>

                <a href="https://arquanzia.creations-sortilege.com/login" 
                   class="block w-full bg-white text-indigo-600 py-3 px-4 rounded-lg border border-indigo-600 hover:bg-indigo-50 font-medium">
                    Connexion par email
                </a>
            </div>

            <p class="mt-6 text-xs text-gray-400">
                Vous serez redirigé vers arquanzia.creations-sortilege.com
            </p>
        </div>
    </div>
</body>
</html>
