<?php

return [

'accepted' => "L'attribut doit être accepté.",
'active_url' => "L'attribut n'est pas une URL valide.",
'after' => "L'attribut doit être une date postérieure à :date.",
'after_or_equal' => "L'attribut doit être une date postérieure ou égale à :date.",
'alpha' => "L'attribut ne peut contenir que des lettres.",
'alpha_dash' => "L'attribut ne peut contenir que des lettres, des chiffres, des tirets et des traits de soulignement.",
'alpha_num' => "L'attribut ne peut contenir que des lettres et des chiffres.",
'array' => "L'attribut doit être un tableau.",
'before' => "L'attribut doit être une date antérieure à :date.",
'before_or_equal' => "Le :attribute doit être une date antérieure ou égale à :date.",
'between' => [
'numeric' => "L'attribute doit être compris entre :min et :max.",
'file' => "L'attribute doit être compris entre :min et :max kilo-octets.",
'string' => "L'attribute doit être compris entre :min et :max caractères.",
'array' => "L'attribute doit contenir entre :min et :max éléments.",
],
'boolean' => "Le champ :attribute doit être vrai ou faux.",
'confirmed' => "La confirmation de l'attribute ne correspond pas.",
'date' => "L'attribute n'est pas une date valide.",
'date_equals' => "L'attribute doit être une date égale à :date.",
'date_format' => "L'attribute ne correspond pas au format :format.",
'different' => "L'attribut :attribute et l'attribut :other doivent être différents.",
'digits' => "L'attribut :attribute doit être de :digits digits.",
'digits_between' => "L'attribut :attribute doit être compris entre :min et :max digits.",
'dimensions' => "Les dimensions de l'attribut :attribute sont invalides.",
'distinct' => "Le champ :attribute contient une valeur en double.",
'email' => "L'attribut :attribute doit être une adresse e-mail valide.",
'ends_with' => "L'attribut :attribute doit se terminer par l'une des valeurs suivantes : :values.",
'exists' => "L'attribut :attribute sélectionné n'est pas valide.",
'file' => "L'attribut :attribute doit être un fichier.",
'filled' => "Le champ :attribute doit avoir une valeur.",
'gt' => [
'numeric' => "L'attribut :attribute doit être supérieur à :value.",
'file' => "L'attribut :attribute doit être supérieur à :value kilo-octets.",
'string' => "L'attribut :attribute doit être supérieur à :value caractères.",
'array' => "L'attribut :attribute doit contenir plus de :value éléments.",
],
'gte' => [
'numeric' => "L'attribut :attribute doit être supérieur ou égal à :value.",
'file' => "L'attribut :attribute doit être supérieur ou égal à :value kilo-octets.",
'string' => "L'attribut :attribute doit être supérieur ou égal à :value caractères.",
'array' => "L'attribut :attribute doit contenir au moins :value éléments.",
],
'image' => "L'attribut :attribute doit être une image.",
'in' => "L'attribut :attribute sélectionné n'est pas valide.",
'in_array' => "Le champ :attribute n'existe pas dans :other.",
'integer' => "L'attribut :attribute doit être un entier.",
'ip' => "L'attribut :attribute doit être une adresse IP valide.",
'ipv4' => "L'attribut :attribute doit être une adresse IPv4 valide.",
'ipv6' => "L'attribut :attribute doit être une adresse IPv6 valide.",
'json' => "L'attribut :attribute doit être une chaîne JSON valide.",
'lt' => [
'numeric' => "L'attribut :attribute doit être inférieur à :value.",
'file' => "L'attribut :attribute doit être inférieur à :value kilo-octets.",
'string' => "L'attribut :attribute doit être inférieur à :value caractères.",
'array' => "L'attribut :attribute doit avoir moins de :value éléments.",
],
'lte' => [
'numeric' => "Le :attribute doit être inférieur ou égal à :value.",
'file' => "L'attribute doit être inférieur ou égal à :value kilo-octets.",
'string' => "L'attribute doit être inférieur ou égal à :value caractères.",
'array' => "L'attribute ne doit pas contenir plus de :value éléments.",
],
'max' => [
'numeric' => "L'attribute ne doit pas être supérieur à :max.",
'file' => "L'attribute ne doit pas être supérieur à :max kilo-octets.",
'string' => "L'attribute ne doit pas être supérieur à :max caractères.",
'array' => "L'attribute ne doit pas contenir plus de :max éléments.",
],
'mimes' => "L'attribute doit être un fichier de type : :values.",
'mimetypes' => "L'attribute doit être un fichier de type : :values.",
'min' => [
'numeric' => "L'attribut : doit être d'au moins :min.",
'file' => "L'attribut : doit être d'au moins :min kilo-octets.",
'string' => "L'attribut : doit être d'au moins :min caractères.",
'array' => "L'attribut : doit avoir au moins :min éléments.",
],
'not_in' => "L'attribut : sélectionné n'est pas valide.",
'not_regex' => "Le format de l'attribut : n'est pas valide.",
'numeric' => "L'attribut : doit être un nombre.",
'password' => "Le mot de passe est incorrect.",
'present' => "Le champ :attribut : doit être présent.",
'regex' => "Le format de l'attribut : n'est pas valide.",
'required' => "Le champ :attribut : est obligatoire.",
'required_if' => "Le champ :attribute est obligatoire lorsque :other est :value.",
'required_unless' => "Le :aLe champ ttribute est obligatoire sauf si :other est dans :values.",
'required_with' => "Le champ :attribute est obligatoire lorsque :values ​​est présent.",
'required_with_all' => "Le champ :attribute est obligatoire lorsque :values ​​est présent.",
'required_without' => "Le champ :attribute est obligatoire lorsque :values ​​n'est pas présent.",
'required_without_all' => "Le champ :attribute est obligatoire lorsqu'aucun des :values ​​n'est présent.",
'same' => "Les champs :attribute et :other doivent correspondre.",
'size' => [
'numeric' => "L'attribute doit être de :size.",
'file' => "L'attribute doit être de :size kilo-octets.",
'string' => "L'attribute doit être de :size caractères.",
'array' => "L'attribute doit contenir :size items.",
],
'starts_with' => "L'attribut :attribute doit commencer par l'une des valeurs suivantes : :values.",
'string' => "L'attribut :attribute doit être une chaîne.",
'timezone' => "L'attribut :attribute doit être une zone valide.",
'unique' => "L'attribut :attribute a déjà été utilisé.",
'uploaded' => "L'attribut :attribute n'a pas pu être téléchargé.",
'url' => "Le format de l'attribut :attribute n'est pas valide.",
'uuid' => "L'attribut :attribute doit être un UUID valide.",

/*
|--------------------------------------------------------------------------
| Lignes de langage de validation personnalisées
|--------------------------------------------------------------------------
|
| Vous pouvez spécifier ici des messages de validation personnalisés pour les attributs en utilisant la convention « attribute.rule » pour nommer les lignes. Cela permet de spécifier rapidement une ligne de langage personnalisée spécifique pour une règle d'attribut donnée.
| */

'custom' => [
    'attribute-name' => [
    'rule-name' => "custom-message",
],
],

/*
|--------------------------------------------------------------------------
| Attributs de validation personnalisés
|--------------------------------------------------------------------------
|
| Les lignes de langage suivantes permettent de remplacer notre attribut par un paramètre plus lisible, comme « Adresse e-mail » au lieu de « e-mail ». Cela nous permet simplement de rendre notre message plus explicite.
|
*/

'attributes' => [],

];