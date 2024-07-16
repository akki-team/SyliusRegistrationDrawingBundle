<?php

namespace Akki\SyliusRegistrationDrawingBundle\Helpers;

use InvalidArgumentException;

class MbHelper
{

    public static function mb_str_pad(string $input, int $pad_length, string $pad_string = ' ', int $pad_type = STR_PAD_RIGHT): string
    {
        if (!in_array($pad_type, [STR_PAD_LEFT, STR_PAD_RIGHT, STR_PAD_BOTH], true)) {
            throw new InvalidArgumentException('Invalid value for argument $pad_type');
        }

        // Total number of characters we need to fill
        $gap = $pad_length - mb_strlen($input);

        // Bail early if the input is already at or above the target length
        if ($gap < 1) {
            return $input;
        }

        // Determine the number of characters we need to prepend on the left
        if ($pad_type === STR_PAD_BOTH) {
            $left_gap = $gap / 2;
        } elseif ($pad_type === STR_PAD_LEFT) {
            $left_gap = $gap;
        } else {
            $left_gap = 0;
        }

        // Build the padding string left of the input
        $pad_string_length = mb_strlen($pad_string);
        $left_padding = mb_substr(str_repeat($pad_string, ceil($left_gap/$pad_string_length)), 0, $left_gap);

        // Build the padding string right of the input
        $right_gap = $gap - mb_strlen($left_padding);
        $right_padding = mb_substr(str_repeat($pad_string, ceil($right_gap/$pad_string_length)), 0, $right_gap);

        return $left_padding . $input . $right_padding;
    }

}
