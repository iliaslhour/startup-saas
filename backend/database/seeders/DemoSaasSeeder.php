<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSaasSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $developerRole = Role::where('slug', 'developer')->first();
        $clientRole = Role::where('slug', 'client')->first();

        $admin = User::updateOrCreate(
            ['email' => 'admin@atlasflow.ma'],
            [
                'name' => 'Ilyass Lhour',
                'password' => Hash::make('12345678'),
            ]
        );

        $developer = User::updateOrCreate(
            ['email' => 'dev@atlasflow.ma'],
            [
                'name' => 'Salma K',
                'password' => Hash::make('12345678'),
            ]
        );

        $client = User::updateOrCreate(
            ['email' => 'client@atlasflow.ma'],
            [
                'name' => 'Ahmed Benali',
                'password' => Hash::make('12345678'),
            ]
        );

        $organization = Organization::updateOrCreate(
            ['name' => 'AtlasFlow'],
            [
                'description' => 'Startup marocaine spécialisée dans les solutions SaaS pour la gestion des ventes et des clients.',
                'owner_id' => $admin->id,
            ]
        );

        $organization->users()->syncWithoutDetaching([
            $admin->id => ['role_id' => $adminRole?->id],
            $developer->id => ['role_id' => $developerRole?->id],
            $client->id => ['role_id' => $clientRole?->id],
        ]);

        $admin->update(['current_organization_id' => $organization->id]);
        $developer->update(['current_organization_id' => $organization->id]);
        $client->update(['current_organization_id' => $organization->id]);

        $project1 = Project::updateOrCreate(
            ['name' => 'CRM AtlasFlow'],
            [
                'organization_id' => $organization->id,
                'created_by' => $admin->id,
                'description' => 'Développement d’un CRM interne pour la gestion des clients.',
                'status' => 'active',
                'start_date' => '2026-04-01',
                'end_date' => '2026-06-30',
            ]
        );

        $project2 = Project::updateOrCreate(
            ['name' => 'Plateforme SaaS V2'],
            [
                'organization_id' => $organization->id,
                'created_by' => $admin->id,
                'description' => 'Nouvelle version de la plateforme avec analytics et dashboard.',
                'status' => 'active',
                'start_date' => '2026-04-05',
                'end_date' => '2026-07-30',
            ]
        );

        Task::updateOrCreate(
            ['title' => 'Créer page login'],
            [
                'organization_id' => $organization->id,
                'project_id' => $project1->id,
                'created_by' => $admin->id,
                'assigned_to' => $developer->id,
                'description' => 'Créer la page login frontend avec validation.',
                'status' => 'todo',
                'priority' => 'high',
                'due_date' => '2026-04-10',
            ]
        );

        Task::updateOrCreate(
            ['title' => 'Implémenter authentification Sanctum'],
            [
                'organization_id' => $organization->id,
                'project_id' => $project1->id,
                'created_by' => $admin->id,
                'assigned_to' => $developer->id,
                'description' => 'Connecter frontend et backend avec Sanctum.',
                'status' => 'in_progress',
                'priority' => 'high',
                'due_date' => '2026-04-12',
            ]
        );

        Task::updateOrCreate(
            ['title' => 'Setup Docker'],
            [
                'organization_id' => $organization->id,
                'project_id' => $project1->id,
                'created_by' => $admin->id,
                'assigned_to' => $admin->id,
                'description' => 'Initialiser docker-compose pour backend et frontend.',
                'status' => 'done',
                'priority' => 'medium',
                'due_date' => '2026-04-06',
            ]
        );

        Task::updateOrCreate(
            ['title' => 'Ajouter module facturation'],
            [
                'organization_id' => $organization->id,
                'project_id' => $project2->id,
                'created_by' => $admin->id,
                'assigned_to' => $developer->id,
                'description' => 'Créer le module de factures avec total automatique.',
                'status' => 'todo',
                'priority' => 'medium',
                'due_date' => '2026-04-20',
            ]
        );

        Task::updateOrCreate(
            ['title' => 'Créer système notifications'],
            [
                'organization_id' => $organization->id,
                'project_id' => $project2->id,
                'created_by' => $admin->id,
                'assigned_to' => $developer->id,
                'description' => 'Créer la logique backend/frontend des notifications.',
                'status' => 'in_progress',
                'priority' => 'medium',
                'due_date' => '2026-04-18',
            ]
        );

        Invoice::updateOrCreate(
            ['invoice_number' => 'INV-2026-1001'],
            [
                'organization_id' => $organization->id,
                'created_by' => $admin->id,
                'client_name' => 'Ahmed Benali',
                'client_email' => 'client@atlasflow.ma',
                'issue_date' => '2026-04-01',
                'due_date' => '2026-04-10',
                'tax_amount' => 100,
                'total_amount' => 1600,
                'status' => 'pending',
            ]
        );

        Invoice::updateOrCreate(
            ['invoice_number' => 'INV-2026-1002'],
            [
                'organization_id' => $organization->id,
                'created_by' => $admin->id,
                'client_name' => 'Ahmed Benali',
                'client_email' => 'client@atlasflow.ma',
                'issue_date' => '2026-04-03',
                'due_date' => '2026-04-12',
                'tax_amount' => 200,
                'total_amount' => 3200,
                'status' => 'paid',
            ]
        );

        Notification::updateOrCreate(
            [
                'user_id' => $developer->id,
                'title' => 'Nouvelle tâche assignée',
            ],
            [
                'organization_id' => $organization->id,
                'type' => 'task_assigned',
                'message' => 'La tâche "Implémenter authentification Sanctum" vous a été assignée.',
                'is_read' => false,
                'read_at' => null,
            ]
        );

        Notification::updateOrCreate(
            [
                'user_id' => $admin->id,
                'title' => 'Projet créé',
            ],
            [
                'organization_id' => $organization->id,
                'type' => 'project_created',
                'message' => 'Le projet "Plateforme SaaS V2" a été créé avec succès.',
                'is_read' => false,
                'read_at' => null,
            ]
        );

        Notification::updateOrCreate(
            [
                'user_id' => $admin->id,
                'title' => 'Facture créée',
            ],
            [
                'organization_id' => $organization->id,
                'type' => 'invoice_created',
                'message' => 'La facture INV-2026-1001 a été créée avec succès.',
                'is_read' => true,
                'read_at' => now(),
            ]
        );
    }
}