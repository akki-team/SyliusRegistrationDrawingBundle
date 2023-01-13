<?php

namespace Akki\SyliusRegistrationDrawingBundle\Helpers;

class Constants
{
    public const CSV_FORMAT = 'CSV';
    public const FIXED_LENGTH_FORMAT = 'LONGUEUR FIXE';

    public const OUTPUT_FORMATS = [
        self::CSV_FORMAT => self::CSV_FORMAT,
        self::FIXED_LENGTH_FORMAT => self::FIXED_LENGTH_FORMAT
    ];

    public const PERIODICITY_WEEKLY = 'HEBDOMADAIRE';
    public const PERIODICITY_MONTHLY = 'MENSUEL';

    public const PERIODICITY = [
        self::PERIODICITY_WEEKLY => self::PERIODICITY_WEEKLY,
        self::PERIODICITY_MONTHLY => self::PERIODICITY_MONTHLY
    ];

    public const DELIMITER_COMMA = ',';
    public const DELIMITER_SEMICOLON = ';';

    public const DELIMITERS = [
        self::DELIMITER_COMMA => self::DELIMITER_COMMA,
        self::DELIMITER_SEMICOLON => self::DELIMITER_SEMICOLON,
    ];

    public const MONDAY = 'LUNDI';
    public const TUESDAY = 'MARDI';
    public const WEDNESDAY = 'MERCREDI';
    public const THURSDAY = 'JEUDI';
    public const FRIDAY = 'VENDREDI';
    public const SATURDAY = 'SAMEDI';
    public const SUNDAY = 'DIMANCHE';

    public const DAYS = [
        self::MONDAY => self::MONDAY,
        self::TUESDAY => self::TUESDAY,
        self::WEDNESDAY => self::WEDNESDAY,
        self::THURSDAY => self::THURSDAY,
        self::FRIDAY => self::FRIDAY,
        self::SATURDAY => self::SATURDAY,
        self::SUNDAY => self::SUNDAY,
    ];

    public const SSH_MODE = 'SSH';
    public const SFTP_MODE = 'SFTP';

    public const SENDING_METHODS = [
        self::SSH_MODE => self::SSH_MODE,
        self::SFTP_MODE => self::SFTP_MODE
    ];
}
