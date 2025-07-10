<?php

return [

    'assessment' => [
        'created' => "Évaluation créée avec succès",
        'store_error' => "Échec de la création de l'évaluation",
        'updated' => "Évaluation mise à jour avec succès",
        'published' => "Évaluation « :name » publiée avec succès",
        'already_published' => "Évaluation « :name » déjà publiée",
        'archived' => "Évaluation « :name » archivée avec succès",
        'unarchived' => "Évaluation « :name » désarchivée avec succès",
        'not_found' => "Évaluation introuvable",
    ],

    'questions' => [
        'generated' => "Questions d'évaluation générées avec succès",
        'created' => "Question d'évaluation créée",
        'create_error' => "Échec de la création de la question d'évaluation",
        'updated' => "Question d'évaluation mise à jour",
        'update_error' => "Échec de la mise à jour de la question d'évaluation",
        'not_found' => "Question d'évaluation introuvable",
        'archived' => "Question archivée",
        'restored' => "Question restaurée",
        'deleted' => "Évaluation « :name » supprimée avec succès",
        'delete_error' => "L'enregistrement « :name » ne peut pas être supprimé car il est associé à :count autres enregistrements. Vous pouvez l'archiver.",
    ],

    'feedbacks' => [
        'already_submitted' => "Vous avez déjà soumis cette évaluation",
        'submitted' => "Évaluation soumise",
        'cannot_delete' => "Évaluation déjà soumise",
        'score_messages' => [
            'exceptional' => "Exceptionnel ! Vous avez obtenu :score sur :total points, ce qui représente un fantastique :percentage%% ! Vous êtes un expert dans ce domaine !",
            'fantastic' => "Fantastique ! Vous avez obtenu :score sur :total points, ce qui représente un impressionnant :percentage%%. Continuez à pousser, vous êtes presque au sommet !",
            'great_job' => "Excellent travail ! Vous avez obtenu :score sur :total points, ce qui représente un excellent :percentage%%. Tu t'en sors bien, continue comme ça !",
            'good_effort' => "Bon effort ! Tu as obtenu :score sur :total points, soit :percentage%%. Il y a encore du potentiel, continue à perfectionner tes compétences !",
            'getting_there' => "Tu y arrives ! Tu as obtenu :score sur :total points, soit :percentage%%. Continue à t'entraîner et tu verras encore des progrès.",
            'decent_try' => "Bon essai ! Tu as obtenu :score sur :total points, soit :percentage%%. Concentre-toi sur tes points faibles pour obtenir de meilleurs résultats la prochaine fois.",
            'keep_going' => "Continue ! Tu as obtenu :score sur :total points, soit :percentage%%. La pratique t'aidera à y arriver, n'abandonne pas !",
            'learning_experience' => "Une expérience enrichissante ! Vous avez obtenu :score sur :total points, soit :percentage%%. Continuez à travailler et vous progresserez rapidement.",
            'persist' => "Ne vous inquiétez pas, vous avez obtenu :score sur :total points, soit :percentage%%. Persévérez et vous obtiendrez de meilleurs résultats avec plus de pratique.",
        ],
    ],

    'training' => [
        'participants_added' => 'Des employés ont été ajoutés à cette formation.',
        'lessons_retrieved' => 'Leçons récupérées.',
        'lesson_cloned' => 'Leçon clonée.',
        'updated' => 'Formation mise à jour.',
        'archived' => 'Formation archivée.',
        'unarchived' => 'Formation restaurée.',
    ],

    'lessons' => [
        'exists' => "Leçon déjà disponible.",
        'created' => "Nouvelle leçon ajoutée.",
        'create_error' => "Erreur lors de la création de la leçon.",
        'upload_error' => "Échec du téléchargement de la vidéo.",
        'updated' => "Leçon mise à jour.",
        'archived' => "Leçon archivée",
        'restored' => "Leçon restaurée",
    ],

    'resources' => [
        'uploaded' => "Ressources de la leçon téléchargées",
        'upload_error' => "Erreur lors du téléchargement des ressources de la leçon",
        'deleted' => "Ressource supprimée",
        'delete_error' => "Erreur lors de la suppression de la ressource",
    ],

    'projects' => [
        'fetched' => "Structure de l'équipe et projets récupérés",
        'overview' => "Aperçu du projet récupéré",
        'created' => "Projet créé",
        'create_error' => "Impossible de terminer la création du projet",
        'team_updated' => "Équipe mise à jour",
        'team_removed' => "Membre de l'équipe supprimé",
        'team_not_found' => "Membre de l'équipe introuvable",
        'updated' => "Projet mis à jour avec succès",
        'archived' => "Projet archivé avec succès",
        'restored' => "Projet restauré avec succès",
        // Nouvelles traductions de jalons
        'milestone_updated' => 'Jalon mis à jour avec succès',
        'milestone_not_found' => 'Jalon introuvable',
        'milestone_update_error' => 'Échec de la mise à jour du jalon',
    ],

    'goals' => [
        'created' => "Tâche créée avec succès",
        'updated' => "Tâche mise à jour avec succès",
        'not_found' => "Tâche introuvable",
        'update_error' => "Échec de la mise à jour de l'objectif",
        'competencies_added' => "Compétences ajoutées avec succès",
        'competency_removed' => "Compétence supprimée avec succès",
        'employees_assigned' => "Employés affectés avec succès",
        'employee_unassigned' => "Employé désaffecté avec succès",
        'archived' => "Projet archivé avec succès",
        'restored' => "Projet restauré avec succès",
    ],

    'comments' => [
        'employee_not_found' => "Fiche employé introuvable",
        'added' => "Commentaire ajouté avec succès",
        'updated' => "Commentaire mis à jour avec succès",
        'deleted' => "Commentaire supprimé avec succès",
        'not_found' => "Commentaire introuvable",
        'update_error' => "Échec de la mise à jour du commentaire",
        'delete_error' => "Échec de la suppression du commentaire",
        'unauthorized' => "Non autorisé à effectuer cette action sur le commentaire",
    ],

    'activities' => [
        'goal_created' => "Tâche créée : :title",
        'employee_assigned' => "Employé affecté à la tâche",
        'competency_added' => "Compétence ajoutée à la tâche",
        'goal_updated' => "Tâche mise à jour : :fields changed",
        'competency_removed' => "Compétence supprimée de la tâche",
        'employee_removed' => "Employé supprimé de la tâche",
        'goal_archived' => "Tâche archivée",
        'goal_restored' => "Tâche restaurée",
        'comment_added' => "Commentaire ajouté à l'objectif",
        'comment_updated' => "Commentaire mis à jour",
        'comment_deleted' => "Commentaire supprimé de l'objectif",
    ],

    'campaigns' => [
        'created' => "Campagne créée avec succès",
        'create_error' => "Impossible de terminer la création de la campagne",
        'updated' => "Campagne mise à jour avec succès",
        'archived' => "Campagne archivée avec succès",
        'published' => "Campagne publiée avec succès",
        'closed' => "Campagne fermée avec succès",
        'restored' => "Campagne restaurée avec succès",
        'deleted' => "Campagne supprimée avec succès",
        'delete_error' => 'L\'enregistrement ":name" ne peut pas être supprimé car il est associé à :count autres enregistrements. Vous pouvez l\'archiver à la place.',
        'multi_deleted' => ":count campaigns deleted successfully",
    ],

    'candidates' => [
        'rated' => "Le candidat a été évalué",
        'invited' => "Une invitation a été envoyée à :name",
        'invite_error' => "Impossible de terminer la demande d'invitation",
        'scored' => "Entretien noté avec succès",
        'withdrawn' => "La candidature a été retirée avec succès",
    ],
];
