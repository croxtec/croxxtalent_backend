<?php

return [

    'employee' => [
        'created' => "Employé créé avec succès.",
        'imported' => "Données importées avec succès.",
        'updated' => "Employé « :name » mis à jour avec succès.",
        'status_updated' => "Statut de l'employé « :name » mis à jour avec succès.",
        'account_created' => ":name' créé avec succès. Veuillez consulter votre boîte de réception pour confirmer votre compte. Si vous ne voyez pas l'e-mail, vérifiez vos spams.",
        'archived' => "Employé « :name » archivé avec succès.",
        'restored' => "Employé « :name » restauré avec succès.",
        'import_failed' => "Impossible de télécharger le fichier, veuillez réessayer.",
        'employee_exists' => "L'employé existe déjà.",
    ],

    'supervisor' => [
        'already_exists' => "Certains superviseurs existent déjà.",
        'created' => "Superviseurs ajoutés avec succès",
        'removed' => "Superviseur supprimé avec succès",
    ],

    'department' => [
        'created' => "Service « :title » créé avec succès",
        'updated' => "Service mis à jour avec succès",
        'not_found' => "Service introuvable",
        'archived' => "Service « :title » archivé successfully",
        'restored' => "Service ':title' restauré avec succès",
        'deleted' => "Le service ':title' a été supprimé avec succès.",
        'cannot_delete' => "Le service ':title' n'a pas pu être supprimé car il est associé à d'autres enregistrements ':relatedRecordsCount'. Vous pouvez l'archiver.",
    ],

    'competency' => [
        'generated' => "La cartographie des compétences et la configuration des KPI ont été générées avec succès.",
        'generation_error' => "Une erreur s'est produite lors de la génération de la cartographie des compétences et de la configuration des KPI. Veuillez réessayer ultérieurement.",
        'add_competency' => "Mapping des compétences et des KPI enregistrés avec succès.",
        'mapping_error' => "Erreur lors de l'enregistrement du mapping des compétences.",
        'matched' => "Compétence correspondante.",
        'invalid_faq_type' => "Type de FAQ non valide.",
        'faq_reviewed' => "FAQ : le faqType a été marqué comme révisé",
    ]

];