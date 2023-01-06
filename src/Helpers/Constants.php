<?php

namespace Akki\SyliusRegistrationDrawingBundle\Helpers;

class Constants
{
    public const CSV_FORMAT = 'CSV';
    public const FIXED_LENGTH_FORMAT = 'LONGUEUR FIXE';

    public const OUTPUT_FORMATS = [
        self::CSV_FORMAT,
        self::FIXED_LENGTH_FORMAT
    ];

    public const PERIODICITY_WEEKLY = 'HEBDOMADAIRE';
    public const PERIODICITY_MONTHLY = 'MENSUEL';

    public const PERIODICITY = [
        self::PERIODICITY_WEEKLY,
        self::PERIODICITY_MONTHLY
    ];

    public const DAYS = ['LUNDI', 'MARDI', 'MERCREDI', 'JEUDI', 'VENDREDI', 'SAMEDI', 'DIMANCHE'];

    public const SSH_MODE = 'SSH';
    public const SFTP_MODE = 'SFTP';

    public const SENDING_METHODS = [
        self::SSH_MODE,
        self::SFTP_MODE
    ];
}
