<?php
// resources/lang/fr/notifications.php
return [
    'supervisor' => [
        'page_title' => 'Notification de superviseur',
        'added' => [
            'subject' => 'Nouveau superviseur assigné',
            'greeting' => 'Bonjour :name,',
            'message' => 'Un nouveau superviseur vous a été assigné. Veuillez consulter les détails ci-dessous et nous contacter si vous avez des questions.',
            'footer' => 'Si vous avez des questions, veuillez contacter votre service RH.',
        ],
        'removed' => [
            'subject' => 'Superviseur retiré',
            'greeting' => 'Bonjour :name,',
            'message' => 'Votre superviseur a été retiré. Veuillez contacter votre service RH si vous avez des questions concernant ce changement.',
            'footer' => 'Si vous avez des questions, veuillez contacter votre service RH.',
        ],
        'details' => 'Détails du superviseur',
        'name' => 'Nom',
        'department' => 'Département',
        'job_code' => 'Code emploi',
        'view_dashboard' => 'Voir le tableau de bord',
        'body' => 'Votre superviseur :name du code emploi :job_code a été mis à jour.',
    ],
    
    'job_invitation' => [
        'page_title' => 'Invitation d\'emploi',
        'subject' => 'Nouvelle invitation d\'emploi',
        'greeting' => 'Bonjour :name,',
        'message' => 'Vous avez une nouvelle invitation/offre d\'emploi de :employer_name.',
        'interview_details' => ':employer_name a programmé un entretien avec vous à :interview_time',
        'login_instruction' => 'Veuillez vous connecter à votre compte :app_name pour voir les détails et accepter l\'offre.',
        'database_message' => 'Vous avez une nouvelle invitation/offre d\'emploi de <b>:employer_name</b>.',
        'accepted_subject' => 'Invitation d\'emploi acceptée par :talent_name',
        'rejected_subject' => 'Invitation d\'emploi rejetée par :talent_name',
        'accepted_message' => 'Votre invitation/offre d\'emploi a été <b style="color: green;">acceptée</b> par <b>:talent_name</b>.',
        'rejected_message' => 'Votre invitation/offre d\'emploi a été <b style="color: red;">rejetée</b> par <b>:talent_name</b>.',
    ],
    
    'goal_reminder' => [
        'page_title' => 'Rappel d\'objectif',
        'subject' => 'Rappel : Votre objectif ":goal_title" nécessite une attention',
        'greeting' => 'Bonjour :name,',
        'intro' => 'Ceci est un rappel concernant l\'objectif qui vous a été assigné : ":goal_title".',
        'purpose' => 'Cet objectif a été fixé pour vous aider à réaliser des progrès significatifs dans votre rôle et à contribuer au succès de l\'entreprise.',
        'metric_label' => 'Métrique d\'objectif :',
        'progress_message' => 'Alors que l\'échéance approche, nous vous encourageons à revoir les progrès que vous avez accomplis jusqu\'à présent. Atteindre cet objectif contribuera à votre développement personnel et garantira que l\'équipe atteint ses objectifs.',
        'support_message' => 'Si vous avez besoin d\'aide ou de précisions, n\'hésitez pas à contacter votre superviseur. Continuez vos efforts, nous sommes convaincus que vous atteindrez cet objectif !',
        'view_button' => 'Voir votre objectif',
        'database_message' => 'Bonjour :name, ceci est un rappel concernant votre objectif ":goal_title". Veuillez revoir vos progrès et prendre les mesures nécessaires pour atteindre l\'objectif avant l\'échéance.',
    ],
    
    'assessment_published' => [
        'page_title' => 'Notification d\'évaluation',
        'subject' => 'Nouvelle évaluation assignée : :assessment_title',
        'greeting' => 'Bonjour :name,',
        'supervisor_intro' => 'Vous avez été assigné comme superviseur pour la nouvelle évaluation intitulée ":assessment_name" par votre entreprise.',
        'employee_intro' => 'Une nouvelle évaluation intitulée ":assessment_name" vous a été assignée par votre entreprise.',
        'importance_message' => 'Cette évaluation est une partie importante de votre développement et de votre évaluation de performance. Veuillez vous connecter à la plateforme pour compléter l\'évaluation.',
        'access_button' => 'Accéder à votre évaluation',
        'supervisor_message' => 'Cher :employee_name, vous avez été assigné comme superviseur pour la nouvelle évaluation intitulée ":assessment_name" par votre entreprise.',
        'employee_message' => 'Cher :employee_name, une nouvelle évaluation intitulée ":assessment_name" vous a été assignée par votre entreprise.',
    ],
    
    'project_assigned' => [
        'page_title' => 'Assignation de projet',
        'subject' => 'Nouvelle assignation de projet : :project_title',
        'greeting' => 'Bonjour :name,',
        'lead_intro' => 'Vous avez été assigné comme chef d\'équipe pour le nouveau projet intitulé ":project_title" par votre entreprise.',
        'employee_intro' => 'Vous avez été assigné à un nouveau projet intitulé ":project_title" par votre entreprise.',
        'contribution_message' => 'Vous avez été ajouté à l\'équipe de ce projet et votre contribution est essentielle à son succès. Veuillez vous connecter à la plateforme pour voir les détails du projet et les tâches qui vous sont assignées.',
        'details_label' => 'Détails du projet :',
        'title_label' => 'Titre :',
        'start_date_label' => 'Date de début :',
        'end_date_label' => 'Fin prévue :',
        'view_button' => 'Voir les détails du projet',
        'lead_message' => 'Cher :employee_name, vous avez été assigné comme chef d\'équipe pour le nouveau projet intitulé ":project_title" par votre entreprise.',
        'employee_message' => 'Cher :employee_name, vous avez été assigné à un nouveau projet intitulé ":project_title" par votre entreprise.',
    ],

    'password_reset' => [
        'page_title' => 'Réinitialisation de mot de passe',
        'subject' => 'Code de réinitialisation de mot de passe',
        'greeting' => 'Bonjour :name,',
        'message' => 'Nous avons reçu une demande de réinitialisation de votre mot de passe pour <a href=":url" target="_blank">:app_name</a>.',
        'code_label' => 'Votre code de réinitialisation de mot de passe est',
        'validity' => 'Ce code est valable pendant 30 minutes ou jusqu\'à ce qu\'un nouveau code soit généré.',
    ],
    
    'campaign_published' => [
        'page_title' => 'Campagne publiée',
        'subject' => 'Campagne publiée',
        'greeting' => 'Bonjour :name,',
        'message' => 'Votre campagne <b>":title"</b> a été <b style="color: red;">publiée</b>.',
    ],
    
    'assessment_feedback' => [
        'page_title' => 'Feedback d\'évaluation',
        'subject' => 'Votre feedback d\'évaluation est maintenant disponible',
        'greeting' => 'Cher :name,',
        'message' => 'Votre évaluation intitulée <b>":assessment_name"</b> (Code: :code) a été revue et votre feedback est maintenant disponible. Cette évaluation est une partie importante de votre développement professionnel et de votre évaluation de performance.',
        'encouragement' => 'Nous vous encourageons à examiner attentivement le feedback fourni par votre superviseur, qui contient des informations précieuses pour guider votre croissance professionnelle. Prenez le temps de réfléchir au feedback et d\'apporter des améliorations si nécessaire.',
        'button_text' => 'Voir le feedback',
        'database_message' => 'Bonjour :name, un superviseur a publié votre feedback d\'évaluation.',
    ],
    
    'supervisor_goal' => [
        'page_title' => 'Assignation d\'objectif par superviseur',
        'subject' => 'Un nouvel objectif vous a été assigné',
        'greeting' => 'Cher :name,',
        'message' => 'Votre superviseur, <b>:supervisor_name</b>, vous a assigné un nouvel objectif : <b>":goal_title"</b>.',
        'instruction' => 'Veuillez vous connecter à la plateforme pour revoir l\'objectif et commencer à travailler pour l\'atteindre. Cet objectif est une partie importante de votre performance et de votre croissance au sein de l\'entreprise.',
        'button_text' => 'Voir l\'objectif',
        'database_message' => 'Votre superviseur, :supervisor_name, vous a assigné un nouvel objectif : ":goal_title".',
    ],
    
    'employee_invitation_confirmation' => [
        'page_title' => 'Confirmation d\'invitation d\'employé',
        'message' => ':employee_name a accepté avec succès l\'invitation d\'employé. Il fait maintenant officiellement partie de votre entreprise. Veuillez procéder aux prochaines étapes pour l\'intégration et la provision des accès.',
    ],
    
    'otp' => [
        'page_title' => 'Mot de passe à usage unique',
        'subject' => 'Mot de passe à usage unique (OTP) pour votre demande',
        'greeting' => 'Bonjour :name,',
        'message' => 'Votre mot de passe à usage unique (OTP) est',
        'validity' => 'Ce OTP est valable pendant 30 minutes ou jusqu\'à ce qu\'un nouveau OTP soit généré.',
    ],

    'welcome_employee' => [
        'page_title' => 'Notification de bienvenue',
        'talent_subject' => 'Des opportunités passionnantes vous attendent chez :company_name',
        'employee_subject' => 'Bienvenue chez :company_name ! Nous sommes ravis de vous compter parmi nous',
    ],
    
    'welcome_verify_email' => [
        'page_title' => 'Vérification d\'email',
        'subject' => 'Vérifiez votre compte sur :app_name',
    ],
    
    'email_templates' => [
        'greeting' => 'Bonjour :name,',
        'profile_registered' => 'Votre profil a été enregistré avec :app_name.',
        'verify_email_instruction' => 'Cliquez simplement sur le bouton ci-dessous pour vérifier votre adresse email.',
        'verify_button_text' => 'Cliquez ici pour vérifier l\'adresse email',
        'invitation_title' => 'Invitation à rejoindre :company_name - :app_name',
        'join_team_title' => 'Rejoignez l\'équipe de :company_name',
        'talent_invitation_message' => 'Nous sommes ravis de vous inviter à rejoindre officiellement l\'équipe de :company_name sur notre plateforme. Votre profil a été lié au système de gestion des employés de l\'entreprise, où vous pourrez accéder aux ressources importantes de l\'entreprise et collaborer avec votre équipe.',
        'talent_verify_instruction' => 'Cliquez simplement sur le bouton ci-dessous pour vérifier votre adresse email et terminer votre processus d\'intégration.',
        'employee_invitation_message' => 'Vous avez été invité à rejoindre :company_name sur notre plateforme ! En vous inscrivant, vous aurez accès aux outils et ressources de l\'entreprise et ferez partie de leur système de gestion des employés.',
        'employee_verify_instruction' => 'Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse email et commencer en tant que membre de :company_name.',
        'verify_email_button' => 'Vérifier votre adresse email',
        'notification_title' => 'Notification - :app_name',
    ],

    'password_change' => [
        'page_title' => 'Changement de mot de passe',
        'subject' => 'Votre mot de passe a été changé avec succès',
        'notification_message' => 'Votre mot de passe a été changé avec succès pour votre compte :app_name.',
    ],

    'profile_change' => [
        'page_title' => 'Mise à jour de profil',
        'subject' => 'Informations de profil mises à jour',
        'notification_message' => 'Des modifications ont été apportées aux informations de votre profil :app_name.',
    ],

    'complimentary_close' => [
        'text' => 'Cordialement,',
        'team' => 'L\'équipe :app_name',
    ],

    'geo_location' => [
        'title' => 'Quand et où cela s\'est produit :',
        'date' => 'Date :',
        'ip' => 'IP :',
        'browser' => 'Navigateur :',
        'os' => 'Système d\'exploitation :',
        'location' => 'Localisation approximative :',
        'security_warning' => 'Vous n\'êtes pas à l\'origine de cette action ?',
        'security_action' => 'Nous vous recommandons de réinitialiser immédiatement votre mot de passe.',
    ],

    'employee_goal_submission' => [
        'subject' => 'Objectif soumis pour examen',
        'email_title' => 'Examen de soumission d\'objectif',
        'greeting' => 'Bonjour :name,',
        'message' => ':employee_name a soumis un objectif pour votre examen.<br><strong>Objectif:</strong> :goal_title<br><strong>Évaluation de l\'employé:</strong> :status',
        'employee_comment' => 'Commentaire de l\'employé',
        'instruction' => 'Veuillez examiner et fournir vos commentaires sur cette soumission d\'objectif.',
        'button_text' => 'Examiner l\'objectif',
        'database_message' => ':employee_name a soumis ":goal_title" pour examen (marqué comme :status)',
    ],
    
    'supervisor_goal_review' => [
        'subject' => 'Examen d\'objectif terminé',
        'email_title' => 'Résultat de l\'examen d\'objectif',
        'greeting' => 'Bonjour :name,',
        'message' => 'Votre superviseur :supervisor_name a examiné votre soumission d\'objectif pour ":goal_title".',
        'your_assessment' => 'Votre évaluation',
        'supervisor_decision' => 'Décision du superviseur',
        'supervisor_comment' => 'Commentaires du superviseur',
        'instruction' => 'Vous pouvez consulter les détails complets de l\'objectif et les commentaires en utilisant le bouton ci-dessous.',
        'button_text' => 'Voir les détails de l\'objectif',
        'database_message' => ':supervisor_name a :action votre objectif ":goal_title"',
        'actions' => [
            'approved' => 'approuvé',
            'modified' => 'examiné et modifié'
        ]
    ],
    
    'supervisor_goal' => [
        'subject' => 'Nouvelle attribution d\'objectif',
        'greeting' => 'Bonjour :name,',
        'message' => 'Votre superviseur :supervisor_name vous a attribué un nouvel objectif:<br><strong>:goal_title</strong>',
        'instruction' => 'Veuillez examiner votre nouvel objectif et commencer à travailler pour l\'atteindre.',
        'button_text' => 'Voir l\'objectif',
        'database_message' => ':supervisor_name vous a attribué un nouvel objectif: :goal_title',
    ],
];