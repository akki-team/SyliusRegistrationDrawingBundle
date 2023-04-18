<?php

namespace Akki\SyliusRegistrationDrawingBundle\Helpers;

use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Order\Model\OrderInterface;

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

    public const EN_DAYS = [
        'LUNDI' => 'monday',
        'MARDI' => 'tuesday',
        'MERCREDI' => 'wednesday',
        'JEUDI' => 'thursday',
        'VENDREDI' => 'friday',
        'SAMEDI' => 'saturday',
        'DIMANCHE' => 'sunday'
    ];

    public const SFTP_MODE = 'SFTP';
    public const SSH_MODE = 'SSH';

    public const SENDING_METHODS = [
        self::SFTP_MODE => self::SFTP_MODE,
        self::SSH_MODE => self::SSH_MODE
    ];

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

    public const OFFER_TYPE_FIELD = 'Type d\'offre';
    public const DATE_TRANSMISSION_FIELD = 'Date transmission';
    public const BILLING_COUNTRY_FIELD = 'Pays payeur';
    public const SHIPPING_COUNTRY_FIELD = 'Pays Destinataire';
    public const OFFER_AMOUNT_FIELD = 'Montant de l\'offre TTC';
    public const MOVEMENT_TYPE_FIELD = 'Type mouvement';
    public const PAYER_CIVILITY_FIELD = 'Civilité Payeur';
    public const RECIPIENT_CIVILITY_FIELD = 'Civilité Destinataire';
    public const PAYER_EMAIL_OPT_IN_FIELD = 'Opt-in Email payeur';
    public const RECIPIENT_EMAIL_OPT_IN_FIELD = 'Opt-in Email Destinataire : Optout par défaut';
    public const KM_PURCHASE_ID_FIELD = 'Id achat KM';

    public const ADL_OFFER_TYPE = 'ADL';
    public const ADD_OFFER_TYPE = 'ADD';

    public const CURRENCY_NUMBER_FORMAT = 'NOMBRE';
    public const CURRENCY_HUNDREDTH_FORMAT = 'CENTIEME';

    public const CURRENCY_FORMATS = [
        self::CURRENCY_NUMBER_FORMAT => self::CURRENCY_NUMBER_FORMAT,
        self::CURRENCY_HUNDREDTH_FORMAT => self::CURRENCY_HUNDREDTH_FORMAT
    ];

    public const CURRENCY_COMMA_DELIMITER = ',';
    public const CURRENCY_POINT_DELIMITER = '.';

    public const CURRENCY_DELIMITERS = [
        self::CURRENCY_COMMA_DELIMITER => self::CURRENCY_COMMA_DELIMITER,
        self::CURRENCY_POINT_DELIMITER => self::CURRENCY_POINT_DELIMITER
    ];

    public const BOOLEANS_VALUES = ['0', '1'];

    public const SUBSTITUABLE_FIELDS = [
        0 => [
            'field' => self::MOVEMENT_TYPE_FIELD,
            'values' => [
                OrderInterface::STATE_CART,
                OrderInterface::STATE_NEW,
                OrderInterface::STATE_CANCELLED,
                OrderInterface::STATE_FULFILLED,
                OrderPaymentTransitions::TRANSITION_REFUND
            ]
        ],
        1 => [
            'field' => self::PAYER_CIVILITY_FIELD,
            'values' => [
                CustomerInterface::MALE_GENDER,
                CustomerInterface::FEMALE_GENDER,
                CustomerInterface::UNKNOWN_GENDER
            ]
        ],
        2 => [
            'field' => self::RECIPIENT_CIVILITY_FIELD,
            'values' => [
                CustomerInterface::MALE_GENDER,
                CustomerInterface::FEMALE_GENDER,
                CustomerInterface::UNKNOWN_GENDER
            ]
        ],
        3 => [
            'field' => self::PAYER_EMAIL_OPT_IN_FIELD,
            'values' => self::BOOLEANS_VALUES
        ],
        4 => [
            'field' => self::RECIPIENT_EMAIL_OPT_IN_FIELD,
            'values' => self::BOOLEANS_VALUES
        ],
        5 => [
            'field' => self::OFFER_TYPE_FIELD,
            'values' => [
                self::ADL_OFFER_TYPE,
                self::ADD_OFFER_TYPE,
            ]
        ]
    ];

}
