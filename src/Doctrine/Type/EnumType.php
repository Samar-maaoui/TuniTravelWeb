<?php

namespace App\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\StringType;

class EnumType extends StringType
{
    const ENUM = 'enum'; // Nom du type personnalisé

    /**
     * Convertit la valeur de la base de données vers une valeur PHP
     *
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // Vérifie si la valeur est dans l'énumération acceptée
        if (!in_array($value, ['ACTIVE', 'INACTIVE', 'PENDING'])) {
            throw new \InvalidArgumentException("Valeur ENUM invalide");
        }
        return $value;
    }

    /**
     * Définit la déclaration SQL pour l'énumération
     *
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('ACTIVE', 'INACTIVE', 'PENDING')"; // Définition des valeurs ENUM dans la base de données
    }

    /**
     * Convertit la valeur PHP en valeur pour la base de données
     *
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, ['ACTIVE', 'INACTIVE', 'PENDING'])) {
            throw new \InvalidArgumentException("Valeur ENUM invalide");
        }
        return $value;
    }

    /**
     * Retourne le nom du type personnalisé
     *
     * @return string
     */
    public function getName()
    {
        return self::ENUM;
    }
}
