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
        self::DELIMITER_SEMICOLON => self::DELIMITER_SEMICOLON,
        self::DELIMITER_COMMA => self::DELIMITER_COMMA
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
        self::SUNDAY => self::SUNDAY
    ];

    public const SFTP_MODE = 'SFTP';
    public const SSH_MODE = 'SSH';

    public const SENDING_METHODS = [
        self::SFTP_MODE => self::SFTP_MODE,
        self::SSH_MODE => self::SSH_MODE
    ];

    public const FIELDS_OPTIONS = ['order', 'position', 'length', 'format', 'selection'];

    public const RETURN_FLOW_FIELDS = [
        'N_client payeur_editeur',
        'N_client destinataire editeur',
        'Premier N° à servir-editeur',
        'Daté Premier N° à servir_editeur',
        'Date installation_editeur',
        'Date annulation (rétractation)_editeur',
        'Dernier N° servi_editeur',
        'Daté Dernier N° servi_editeur',
        'Motif si rejet'
    ];

    public const DATE_TRANSMISSION_FIELD = 'Date transmission';
    public const BILLING_COUNTRY_FIELD = 'Pays payeur';
    public const SHIPPING_COUNTRY_FIELD = 'Pays Destinataire';
}
