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

 'password_reset' => [
        'subject' => 'Code de réinitialisation du mot de passe',
        'greeting' => 'Bonjour :name,',
        'message' => 'Nous avons reçu une demande de réinitialisation de votre mot de passe <a href=":url" target="_blank">:app_name</a>.',
        'code_label' => 'Votre code de réinitialisation du mot de passe est',
        'validity' => 'Ce code est valide pendant 30 minutes ou jusqu\'à ce qu\'un nouveau code soit généré.',
    ],
    'campaign_published' => [
        'subject' => 'Campagne publiée',
        'greeting' => 'Bonjour :name,',
        'message' => 'Votre campagne <b>":title"</b> a été <b style="color: red;">publiée</b>.',
    ],
    'assessment_feedback' => [
        'subject' => 'Votre évaluation est maintenant disponible',
        'greeting' => 'Cher/Chère :name,',
        'message' => 'Votre évaluation intitulée <b>":assessment_name"</b> (Code: :code) a été examinée, et votre retour est maintenant disponible. Cette évaluation est une partie importante de votre développement professionnel et de votre évaluation de performance.',
        'encouragement' => 'Nous vous encourageons à examiner attentivement les commentaires fournis par votre superviseur, qui contiennent des informations précieuses pour guider votre croissance professionnelle. Veuillez prendre le temps de réfléchir aux commentaires et d\'apporter des améliorations là où c\'est nécessaire.',
        'button_text' => 'Voir les commentaires',
        'database_message' => 'Bonjour :name, un superviseur a publié votre évaluation.',
    ],
    'supervisor_goal' => [
        'subject' => 'Un nouvel objectif vous a été assigné',
        'greeting' => 'Cher/Chère :name,',
        'message' => 'Votre superviseur, <b>:supervisor_name</b>, vous a assigné un nouvel objectif : <b>":goal_title"</b>.',
        'instruction' => 'Veuillez vous connecter à la plateforme pour examiner l\'objectif et commencer à travailler pour l\'accomplir. Cet objectif est une partie importante de votre performance et de votre croissance au sein de l\'entreprise.',
        'button_text' => 'Voir l\'objectif',
        'database_message' => 'Votre superviseur, :supervisor_name, vous a assigné un nouvel objectif : ":goal_title".',
    ],
    'employee_invitation_confirmation' => [
        'message' => ':employee_name a accepté avec succès l\'invitation d\'employé. Il/Elle fait maintenant officiellement partie de votre entreprise. Veuillez procéder aux étapes suivantes pour l\'intégration et la fourniture d\'accès.',
    ],
    'otp' => [
        'subject' => 'Mot de passe à usage unique (OTP) pour votre demande',
        'greeting' => 'Bonjour :name,',
        'message' => 'Votre mot de passe à usage unique (OTP) est',
        'validity' => 'Cet OTP est valide pendant 30 minutes ou jusqu\'à ce qu\'un nouvel OTP soit généré.',
    ],
    'welcome_employee' => [
        'talent_subject' => 'Des opportunités passionnantes vous attendent chez :company_name',
        'employee_subject' => 'Bienvenue chez :company_name ! Nous sommes ravis de vous avoir',
    ],
    'welcome_verify_email' => [
        'subject' => 'Vérifiez votre compte sur :app_name',
    ],
    'email_templates' => [
        'greeting' => 'Bonjour :name,',
        'profile_registered' => 'Votre profil a été enregistré avec :app_name.',
        'verify_email_instruction' => 'Cliquez simplement sur le bouton ci-dessous pour vérifier votre adresse e-mail.',
        'verify_button_text' => 'Cliquez ici pour vérifier l\'adresse e-mail',
        'invitation_title' => 'Invitation à rejoindre :company_name - :app_name',
        'join_team_title' => 'Rejoignez l\'équipe de :company_name',
        'talent_invitation_message' => 'Nous sommes ravis de vous inviter à rejoindre officiellement l\'équipe :company_name sur notre plateforme. Votre profil a été lié au système de gestion des employés de l\'entreprise, où vous pourrez accéder aux ressources importantes de l\'entreprise et collaborer avec votre équipe.',
        'talent_verify_instruction' => 'Cliquez simplement sur le bouton ci-dessous pour vérifier votre adresse e-mail et terminer votre processus d\'intégration.',
        'employee_invitation_message' => 'Vous avez été invité(e) à rejoindre :company_name sur notre plateforme ! En rejoignant, vous aurez accès aux outils de l\'entreprise, aux ressources et ferez partie de leur système de gestion des employés.',
        'employee_verify_instruction' => 'Veuillez cliquer sur le bouton ci-dessous pour vérifier votre adresse e-mail et commencer en tant que membre de :company_name.',
        'verify_email_button' => 'Vérifiez votre adresse e-mail',
        'notification_title' => 'Notification - :app_name',
    ],

    'job_invitation' => [
        'accepted_subject' => 'Invitation d\'emploi acceptée par :talent_name',
        'rejected_subject' => 'Invitation d\'emploi rejetée par :talent_name',
        'accepted_message' => 'Votre invitation/offre d\'emploi a été <b style="color: green;">acceptée</b> par <b>:talent_name</b>.',
        'rejected_message' => 'Votre invitation/offre d\'emploi a été <b style="color: red;">rejetée</b> par <b>:talent_name</b>.',
    ],
    
    // Messages de changement de mot de passe
    'password_change' => [
        'subject' => 'Votre mot de passe a été modifié avec succès',
        'notification_message' => 'Votre mot de passe a été modifié avec succès pour votre compte :app_name.',
    ],
    
    // Messages de changement de profil
    'profile_change' => [
        'subject' => 'Informations de profil mises à jour',
        'notification_message' => 'Des modifications ont été apportées aux informations de votre profil :app_name.',
    ],

    'complimentary_close' => [
        'text' => 'Cordialement,',
        'team' => "L'équipe :app_name",
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
];