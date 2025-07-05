<?php

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
];