<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $rootEmail = config('app.root_admin_email');

        $team = User::firstOrCreate(
            ['email' => $rootEmail],
            ['handle' => 'team', 'email' => $rootEmail]
        );

        $posts = [
            [
                'audience' => 'public',
                'title' => 'Bienvenue sur Arquanzia!',
                'preview_text' => 'Découvrez notre univers fantastique et rejoignez notre communauté.',
                'content_full' => "Bienvenue dans l'univers d'Arquanzia!\n\nNous sommes ravis de vous accueillir sur notre nouvelle plateforme communautaire. Ici, vous trouverez des mises à jour exclusives, des aperçus de nos créations, et bien plus encore.\n\nRestez connectés pour ne rien manquer!",
            ],
            [
                'audience' => 'connected',
                'title' => 'Merci de faire partie de la communauté',
                'preview_text' => 'Un message spécial pour nos membres connectés.',
                'content_full' => "Merci d'avoir créé un compte!\n\nEn tant que membre de notre communauté, vous avez accès à du contenu exclusif et pouvez interagir avec nos publications.\n\nN'hésitez pas à commenter et réagir à nos posts!",
            ],
            [
                'audience' => 'vip',
                'title' => 'Accès VIP: Nouveautés en avant-première',
                'preview_text' => 'Contenu exclusif réservé à nos membres VIP.',
                'content_full' => "Cher membre VIP,\n\nVoici un aperçu exclusif de ce qui arrive prochainement:\n\n- Nouvelle collection en préparation\n- Événement spécial à venir\n- Offres exclusives\n\nMerci pour votre soutien!",
            ],
            [
                'audience' => 'reader',
                'title' => 'Chapitre exclusif: Les origines',
                'preview_text' => 'Un chapitre spécial pour nos lecteurs fidèles.',
                'content_full' => "Chapitre Bonus: Les Origines d'Arquanzia\n\nIl y a bien longtemps, dans un monde où la magie coulait comme des rivières d'étoiles...\n\n[Suite du chapitre disponible uniquement pour les lecteurs]",
            ],
        ];

        foreach ($posts as $postData) {
            Post::firstOrCreate(
                ['title' => $postData['title']],
                array_merge($postData, ['author_user_id' => $team->id])
            );
        }
    }
}
