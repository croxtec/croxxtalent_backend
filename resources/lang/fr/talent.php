<?php

return [
    'application' => [
        'submitted' => 'Votre candidature a été soumise avec succès',
        'error' => 'Impossible de traiter la demande de candidature',
    ],
    
    'saved' => [
        'success' => 'La campagne a été enregistrée avec succès',
        'error' => 'Impossible de traiter la demande d\'enregistrement',
    ],
    
    'campaign' => [
        'not_found' => 'Campagne introuvable ou non publiée',
    ],
    
    'notifications' => [
        'campaign_application_title' => 'Candidature à la campagne',
        'campaign_application_message' => 'Un talent a postulé pour la campagne :title',
    ],
    
    'validation' => [
        'campaign_id_required' => 'L\'ID de la campagne est requis',
    ],

    'goals'  => [
        'created' => 'Nouvel objectif futur créé avec succès',
        'updated' => 'Objectif mis à jour avec succès',
        'not_found' => 'Objectif introuvable',
        'create_error' => 'Impossible de créer l\'objectif',
        'update_error' => 'Impossible de mettre à jour l\'objectif',
    ],

    'job_invitation' => [
        'sent' => 'Une invitation a été envoyée à :name',
        'archived' => 'Invitation d\'emploi archivée avec succès',
        'unarchived' => 'Invitation d\'emploi désarchivée avec succès',
        'deleted' => 'Invitation d\'emploi supprimée avec succès',
        'not_found' => 'Invitation d\'emploi introuvable',
        'create_error' => 'Impossible d\'envoyer l\'invitation d\'emploi',
        'archive_error' => 'Impossible d\'archiver l\'invitation d\'emploi',
        'unarchive_error' => 'Impossible de désarchiver l\'invitation d\'emploi',
        'delete_error' => 'Impossible de supprimer l\'invitation d\'emploi',
        'delete_restricted' => 'L\'enregistrement ":name" ne peut pas être supprimé car il est associé à :count autre(s) enregistrement(s). Vous pouvez l\'archiver à la place.',
        'notification_title' => 'Invitation d\'emploi',
        'notification_message' => 'Vous avez une nouvelle invitation/offre d\'emploi de :name',
    ],

];