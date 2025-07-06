<?php
// resources/lang/fr/notifications.php
return [
    'supervisor' => [
        'added' => [
            'subject' => 'Nouveau Superviseur Assigné',
            'greeting' => 'Bonjour :name,',
            'message' => 'Un nouveau superviseur vous a été assigné. Veuillez examiner les détails ci-dessous et nous contacter si vous avez des questions.',
            'footer' => 'Si vous avez des questions, veuillez contacter votre département RH.',
        ],
        'removed' => [
            'subject' => 'Superviseur Retiré',
            'greeting' => 'Bonjour :name,',
            'message' => 'Votre superviseur a été retiré. Veuillez contacter votre département RH si vous avez des questions concernant ce changement.',
            'footer' => 'Si vous avez des questions, veuillez contacter votre département RH.',
        ],
        'details' => 'Détails du Superviseur',
        'name' => 'Nom',
        'department' => 'Département',
        'job_code' => 'Code d\'Emploi',
        'view_dashboard' => 'Voir le Tableau de Bord',
        'body' => 'Votre superviseur :name de :job_code a été mis à jour.',
    ],
    
    'job_invitation' => [
        'page_title' => 'Invitation d\'Emploi',
        'subject' => 'Nouvelle Invitation d\'Emploi',
        'greeting' => 'Bonjour :name,',
        'message' => 'Vous avez une nouvelle invitation/offre d\'emploi de :employer_name.',
        'interview_details' => ':employer_name a programmé un entretien avec vous à :interview_time',
        'login_instruction' => 'Veuillez vous connecter à votre compte :app_name pour voir les détails et accepter l\'offre.',
        'database_message' => 'Vous avez une nouvelle invitation/offre d\'emploi de <b>:employer_name</b>.',
    ],
    
    'goal_reminder' => [
        'page_title' => 'Rappel d\'Objectif',
        'subject' => 'Rappel : Votre Objectif ":goal_title" Nécessite Attention',
        'greeting' => 'Bonjour :name,',
        'intro' => 'Ceci est un rappel amical concernant l\'objectif qui vous a été assigné : ":goal_title".',
        'purpose' => 'Cet objectif a été fixé pour vous aider à réaliser des progrès significatifs dans votre rôle et contribuer au succès de l\'entreprise.',
        'metric_label' => 'Métrique de l\'Objectif :',
        'progress_message' => 'Alors que la date limite approche, nous vous encourageons à examiner les progrès que vous avez réalisés jusqu\'à présent. L\'accomplissement de cet objectif vous aidera dans votre développement personnel et assurera que l\'équipe atteigne ses objectifs.',
        'support_message' => 'Si vous avez besoin d\'aide ou de clarification, n\'hésitez pas à contacter votre superviseur. Continuez à avancer, et nous sommes confiants que vous atteindrez cet objectif !',
        'view_button' => 'Voir Votre Objectif',
        'database_message' => 'Bonjour :name, ceci est un rappel concernant votre objectif ":goal_title". Veuillez examiner vos progrès et prendre les mesures nécessaires pour atteindre l\'objectif avant la date limite.',
    ],
    
    'assessment_published' => [
        'page_title' => 'Notification d\'Évaluation',
        'subject' => 'Nouvelle Évaluation Assignée : :assessment_title',
        'greeting' => 'Bonjour :name,',
        'supervisor_intro' => 'Vous avez été assigné comme superviseur pour la nouvelle évaluation intitulée ":assessment_name" par votre entreprise.',
        'employee_intro' => 'Vous avez été assigné une nouvelle évaluation intitulée ":assessment_name" par votre entreprise.',
        'importance_message' => 'Cette évaluation est une partie importante de votre développement et évaluation de performance. Veuillez vous connecter à la plateforme pour compléter l\'évaluation.',
        'access_button' => 'Accéder à Votre Évaluation',
        'supervisor_message' => 'Cher :employee_name, vous avez été assigné comme superviseur pour la nouvelle évaluation intitulée ":assessment_name" par votre entreprise.',
        'employee_message' => 'Cher :employee_name, vous avez été assigné une nouvelle évaluation intitulée ":assessment_name" par votre entreprise.',
    ],
    
    'project_assigned' => [
        'page_title' => 'Assignation de Projet',
        'subject' => 'Nouvelle Assignation de Projet : :project_title',
        'greeting' => 'Bonjour :name,',
        'lead_intro' => 'Vous avez été assigné comme chef d\'équipe pour le nouveau projet intitulé ":project_title" par votre entreprise.',
        'employee_intro' => 'Vous avez été assigné à un nouveau projet intitulé ":project_title" par votre entreprise.',
        'contribution_message' => 'Vous avez été ajouté à cette équipe de projet et votre contribution est essentielle à son succès. Veuillez vous connecter à la plateforme pour voir les détails du projet et vos tâches assignées.',
        'details_label' => 'Détails du Projet :',
        'title_label' => 'Titre :',
        'start_date_label' => 'Date de Début :',
        'end_date_label' => 'Achèvement Prévu :',
        'view_button' => 'Voir les Détails du Projet',
        'lead_message' => 'Cher :employee_name, vous avez été assigné comme chef d\'équipe pour le nouveau projet intitulé ":project_title" par votre entreprise.',
        'employee_message' => 'Cher :employee_name, vous avez été assigné à un nouveau projet intitulé ":project_title" par votre entreprise.',
    ],
];