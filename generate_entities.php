<?php

// Charger le fichier JSON 'basecommune.json'
$jsonData = json_decode(file_get_contents('basecommune.json'), true);

// Vérifier si le dossier src/Entity existe, sinon, le créer
if (!file_exists('src/Entity')) {
    mkdir('src/Entity', 0777, true);
}

// Parcourir chaque table dans le fichier JSON
foreach ($jsonData as $table) {
    // S'assurer que l'entrée est bien une table
    if ($table['type'] == 'table') {
        // Utiliser le nom de la table comme nom de la classe
        $className = ucfirst($table['name']);  // Prendre le nom de la table pour créer la classe
        
        $entityCode = "<?php\n\nnamespace App\\Entity;\n\nuse Doctrine\\ORM\\Mapping as ORM;\n\n";
        $entityCode .= "/**\n * @ORM\\Entity()\n * @ORM\\Table(name=\"" . $table['name'] . "\")\n */\n";
        $entityCode .= "class $className\n{\n";
        
        // Vérifier si la table a des données
        if (isset($table['data']) && is_array($table['data']) && !empty($table['data'])) {
            // Parcourir la première ligne de données pour créer les colonnes de l'entité
            foreach ($table['data'][0] as $columnName => $columnValue) {
                // Déterminer le type de la colonne
                $doctrineType = 'string'; // Par défaut, c'est une chaîne
                if (is_numeric($columnValue)) {
                    // Déterminer si c'est un entier ou un float
                    $doctrineType = is_int($columnValue) ? 'integer' : 'float';
                } elseif (strpos($columnName, 'date') !== false) {
                    // Si la colonne contient 'date', on la considère comme un champ datetime
                    $doctrineType = 'datetime';
                }

                // Ajouter la doc pour chaque propriété
                $entityCode .= " /**\n * @ORM\\Column(type=\"$doctrineType\")\n */\n";
                $entityCode .= " private $$columnName;\n\n";
            }
        }
        
        // Ajouter le code de fin de classe
        $entityCode .= "}\n";

        // Écrire le fichier PHP de l'entité dans le dossier src/Entity
        file_put_contents("src/Entity/$className.php", $entityCode);

        echo "Classe d'entité $className générée.\n";
    }
}
?>
